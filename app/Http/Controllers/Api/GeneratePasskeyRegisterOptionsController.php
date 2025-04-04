<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Serializer;
use Illuminate\Http\Request;
use Session;
use Str;
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
            id: $this->hostname(),
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            name: $request->user()->email,
            id: $request->user()->id,
            displayName: $request->user()->name,
        );

        $options = new PublicKeyCredentialCreationOptions(
            rp: $relatedPartyEntity,
            user: $userEntity,
            challenge: $this->challenge(),
        );

        $options = Serializer::make()->toJson($options);

        Session::flash('passkey-registration-options', $options);

        return $options;
    }

    protected function challenge(): string
    {
        return Str::random();
    }

    protected function hostname(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }
}
