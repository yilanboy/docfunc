<?php

namespace App\Support;


use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

class Serializer
{
    public static function make(): Serializer
    {
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();

        $serializer = new WebauthnSerializerFactory($attestationStatementSupportManager)
            ->create();

        return new self($serializer);
    }

    public function __construct(
        protected SymfonySerializer $serializer,
    ) {
    }

    public function toJson(mixed $value): string
    {
        return $this->serializer->serialize($value, 'json');
    }

    public function fromJson(string $value, string $desiredClass)
    {
        return $this
            ->serializer
            ->deserialize($value, $desiredClass, 'json');
    }
}
