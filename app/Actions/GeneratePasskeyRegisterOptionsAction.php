<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\Serializer;
use Cose\Algorithms;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

final readonly class GeneratePasskeyRegisterOptionsAction
{
    public function __construct(
        private Serializer $serializer,
    ) {}

    /**
     * @throws InvalidDataException
     * @throws SerializerExceptionInterface
     */
    public function handle(User $user): string
    {
        // 建立一個信賴方實體
        // id 是網站的網域名稱
        $relatedPartyEntity = new PublicKeyCredentialRpEntity(
            name: config('app.name'),
            id: Uri::of(config('app.url'))->host()
        );

        // 建立一個用戶實體
        // id 必須是唯一的，通常是用戶的 ID 或 UUID
        // 需要注意的是，name 不可以使用用戶的敏感資訊，例如 email 或電話號碼
        $userEntity = new PublicKeyCredentialUserEntity(
            name: $user->name,
            id: (string) $user->id,
            displayName: $user->name
        );

        // 驗證裝置的設定
        // 沒有偏好任何平台，並且要求使用者的金鑰必須支援可探索的憑證
        // 目前可探索的憑證已經是主流，如果這裡沒有強制要求，你的 YubiKey 會無法使用
        $authenticatorSelectionCriteria = AuthenticatorSelectionCriteria::create(
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
        );

        $publicKeyCredentialParametersList = [
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256K),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_PS256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ED256),
        ];

        // 註冊金鑰的選項，前端會使用這些選項來顯示註冊金鑰的 UI
        // challenge 是一個隨機的字串，用來防止重送攻擊
        $options = new PublicKeyCredentialCreationOptions(
            rp: $relatedPartyEntity,
            user: $userEntity,
            challenge: Str::random(),
            pubKeyCredParams: $publicKeyCredentialParametersList,
            authenticatorSelection: $authenticatorSelectionCriteria
        );

        return $this->serializer->toJson($options);
    }
}
