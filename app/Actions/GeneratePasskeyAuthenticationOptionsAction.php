<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\Serializer;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialRequestOptions;

final readonly class GeneratePasskeyAuthenticationOptionsAction
{
    public function __construct(
        private Serializer $serializer,
    ) {}

    /**
     * @throws InvalidDataException
     * @throws SerializerExceptionInterface
     */
    public function handle(): string
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: Str::random(),
            rpId: Uri::of(config('app.url'))->host(),
            allowCredentials: [],
        );

        return $this->serializer->toJson($options);
    }
}
