<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\CreatePasskeyMail;
use App\Models\Passkey;
use App\Models\User;
use App\Services\Serializer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

final readonly class CreatePasskeyAction
{
    public function __construct(
        private Serializer $serializer,
    ) {}

    public function handle(User $user, string $name, string $passkeyJson, string $optionsJson, string $host): ?Passkey
    {
        try {
            $publicKeyCredential = $this->serializer->fromJson($passkeyJson, PublicKeyCredential::class);

            if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
                return null;
            }

            $publicKeyCredentialCreationOptions = $this->serializer->fromJson(
                $optionsJson,
                PublicKeyCredentialCreationOptions::class
            );

            $publicKeyCredentialSource = AuthenticatorAttestationResponseValidator::create(
                (new CeremonyStepManagerFactory)->creationCeremony()
            )->check(
                authenticatorAttestationResponse: $publicKeyCredential->response,
                publicKeyCredentialCreationOptions: $publicKeyCredentialCreationOptions,
                host: $host,
            );

            $sourceArray = $this->serializer->toArray($publicKeyCredentialSource);
        } catch (Throwable) {
            return null;
        }

        $passkey = DB::transaction(fn (): Passkey => $user->passkeys()->create([
            'name'          => $name,
            'credential_id' => $sourceArray['publicKeyCredentialId'],
            'data'          => $sourceArray,
        ]));

        Mail::to($user)->queue(new CreatePasskeyMail(passkeyName: $name));

        return $passkey;
    }
}
