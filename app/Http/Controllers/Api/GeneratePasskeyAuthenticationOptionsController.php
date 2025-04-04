<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Serializer;
use Illuminate\Http\Request;
use Session;
use Str;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class GeneratePasskeyAuthenticationOptionsController extends Controller
{
    /**
     * @throws InvalidDataException
     */
    public function __invoke(): string
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: $this->challenge(),
            rpId: $this->hostname(),
            allowCredentials: [],
        );

        $options = Serializer::make()->toJson($options);

        Session::flash('passkey-authentication-options', $options);

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
