<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

class Serializer
{
    public static function make(): Serializer
    {
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();

        /** @var SymfonySerializer $serializer */
        $serializer = new WebauthnSerializerFactory($attestationStatementSupportManager)
            ->create();

        return new self($serializer);
    }

    public function __construct(
        protected SymfonySerializer $serializer,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function toJson(mixed $value): string
    {
        return $this->serializer->serialize(
            $value,
            'json',
            [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true, // Highly recommended!
                JsonEncode::OPTIONS                        => JSON_THROW_ON_ERROR, // Optional
            ]
        );
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $desiredClass
     * @return T
     *
     * @throws ExceptionInterface
     */
    public function fromJson(string $value, string $desiredClass): mixed
    {
        return $this
            ->serializer
            ->deserialize($value, $desiredClass, 'json');
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function toArray(mixed $value): array
    {
        $normalized = $this->serializer->normalize($value, 'json');

        return is_array($normalized) ? $normalized : (array) $normalized;
    }
}
