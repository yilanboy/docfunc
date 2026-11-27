<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GeneratePasskeyRegisterOptionsAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Webauthn\Exception\InvalidDataException;

class GeneratePasskeyRegisterOptionsController extends Controller
{
    public function __invoke(#[CurrentUser] User $user, GeneratePasskeyRegisterOptionsAction $generateOptions): JsonResponse|string
    {
        try {
            $optionsJson = $generateOptions->handle($user);
        } catch (InvalidDataException|SerializerExceptionInterface $e) {
            Log::error('Webauthn 註冊選項序列化失敗', [
                'user_id'   => $user->id,
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
