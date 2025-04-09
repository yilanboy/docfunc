<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Serializer;
use Illuminate\Support\Uri;
use Session;
use Str;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialRequestOptions;

class GeneratePasskeyAuthenticationOptionsController extends Controller
{
    /**
     * @throws InvalidDataException
     */
    public function __invoke(): string
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: Str::random(),
            rpId: Uri::of(config('app.url'))->host(),
            allowCredentials: [],
        );

        $options = Serializer::make()->toJson($options);

        Session::flash('passkey-authentication-options', $options);

        return $options;
    }
}
