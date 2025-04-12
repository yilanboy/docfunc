<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Serializer;
use Illuminate\Http\Request;
use Illuminate\Support\Uri;
use Session;
use Str;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class GeneratePasskeyRegisterOptionsController extends Controller
{
    /**
     * @throws InvalidDataException
     */
    public function __invoke(Request $request): string
    {
        $relatedPartyEntity = new PublicKeyCredentialRpEntity(
            name: config('app.name'),
            id: Uri::of(config('app.url'))->host()
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            name: $request->user()->name,
            id: (string) $request->user()->id,
            displayName: $request->user()->name,
            icon: null
        );

        $authenticatorSelectionCriteria = AuthenticatorSelectionCriteria::create(
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
        );

        $options = new PublicKeyCredentialCreationOptions(
            rp: $relatedPartyEntity,
            user: $userEntity,
            challenge: Str::random(),
            authenticatorSelection: $authenticatorSelectionCriteria
        );

        $options = Serializer::make()->toJson($options);

        Session::flash('passkey-registration-options', $options);

        return $options;
    }
}
