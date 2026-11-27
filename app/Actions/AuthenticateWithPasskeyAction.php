<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Passkey;
use App\Models\User;
use App\Services\Serializer;
use JsonException;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

final readonly class AuthenticateWithPasskeyAction
{
    public function __construct(
        private Serializer $serializer,
    ) {}

    public function handle(string $answerJson, string $optionsJson, string $host): ?Passkey
    {
        try {
            $publicKeyCredential = $this->serializer->fromJson($answerJson, PublicKeyCredential::class);

            if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
                return null;
            }

            $decoded = json_decode($answerJson, true, flags: JSON_THROW_ON_ERROR);

            if (! is_array($decoded) || ! isset($decoded['rawId']) || ! is_string($decoded['rawId'])) {
                return null;
            }

            $rawId = $decoded['rawId'];

            $passkey = Passkey::query()
                ->where('credential_id', $rawId)
                ->where('owner_type', User::class)
                ->first();

            if (! $passkey) {
                return null;
            }

            $credentialRecord = $this->serializer->fromJson(
                json_encode($passkey->data, JSON_THROW_ON_ERROR),
                CredentialRecord::class
            );

            $publicKeyCredentialRequestOptions = $this->serializer->fromJson(
                $optionsJson,
                PublicKeyCredentialRequestOptions::class
            );

            $credentialRecord = AuthenticatorAssertionResponseValidator::create(
                (new CeremonyStepManagerFactory)->requestCeremony()
            )->check(
                credentialRecord: $credentialRecord,
                authenticatorAssertionResponse: $publicKeyCredential->response,
                publicKeyCredentialRequestOptions: $publicKeyCredentialRequestOptions,
                host: $host,
                userHandle: null,
            );

            $updatedData = $this->serializer->toArray($credentialRecord);
        } catch (JsonException|SerializerExceptionInterface|AuthenticatorResponseVerificationException|InvalidDataException) {
            return null;
        }

        $passkey->update([
            'data'         => $updatedData,
            'last_used_at' => now(),
        ]);

        return $passkey;
    }
}
