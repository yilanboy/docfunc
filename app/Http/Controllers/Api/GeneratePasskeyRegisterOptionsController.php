<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Serializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptions;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class GeneratePasskeyRegisterOptionsController extends Controller
{
    public function __invoke(Request $request, Serializer $serializer): JsonResponse|string
    {
        // 建立一個信賴方實體
        // id 是網站的網域名稱
        $relatedPartyEntity = new PublicKeyCredentialRpEntity(
            name: config('app.name'),
            id: Uri::of(config('app.url'))->host()
        );

        try {
            // 建立一個用戶實體
            // id 必須是唯一的，通常是用戶的 ID 或 UUID
            // 需要注意的是，name 不可以使用用戶的敏感資訊，例如 email 或電話號碼
            $userEntity = new PublicKeyCredentialUserEntity(
                name: $request->user()->name,
                id: (string) $request->user()->id,
                displayName: $request->user()->name
            );
        } catch (InvalidDataException $e) {
            Log::error('無法建立 Webauthn 用戶實體', [
                'user_id'   => $request->user()->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '無法建立金鑰註冊選項，請稍後再試。',
            ], 500);
        }

        // 驗證裝置的設定
        // 沒有偏好任何平台，並且要求使用者的金鑰必須支援可探索的憑證
        // 目前可探索的憑證已經是主流，如果這裡沒有強制要求，你的 YubiKey 會無法使用
        $authenticatorSelectionCriteria = AuthenticatorSelectionCriteria::create(
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
        );

        try {
            // 註冊金鑰的選項，前端會使用這些選項來顯示註冊金鑰的 UI
            // challenge 是一個隨機的字串，用來防止重送攻擊
            $options = new PublicKeyCredentialCreationOptions(
                rp: $relatedPartyEntity,
                user: $userEntity,
                challenge: Str::random(),
                authenticatorSelection: $authenticatorSelectionCriteria
            );
        } catch (InvalidDataException $e) {
            Log::error('無法建立 Webauthn 註冊選項', [
                'user_id'   => $request->user()->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '無法建立金鑰註冊選項，請稍後再試。',
            ], 400);
        }

        try {
            // 將 $options 物件進行序列化，轉換為 JSON 字串
            $optionsJson = $serializer->toJson($options);
        } catch (SerializerExceptions  $e) {
            Log::error('Webauthn 註冊選項序列化失敗', [
                'user_id'   => $request->user()->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => '伺服器發生錯誤，無法序列化註冊選項。',
            ], 400);
        }

        // 將 $options 儲存在 Flash Session 中，好讓我們在下一步驟中使用
        // 當用戶傳回公開金鑰憑證後，我們需要將 $options 從 Session 取出，用來驗證用戶的憑證
        Session::flash('passkey-registration-options', $optionsJson);

        return $optionsJson;
    }
}
