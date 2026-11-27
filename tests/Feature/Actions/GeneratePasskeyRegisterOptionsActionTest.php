<?php

declare(strict_types=1);

use App\Actions\GeneratePasskeyRegisterOptionsAction;
use App\Models\User;
use App\Services\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

test('it generates passkey registration options json for a user', function () {
    $user = User::factory()->create(['name' => 'Allen']);

    $json = app(GeneratePasskeyRegisterOptionsAction::class)->handle($user);
    $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBeArray()
        ->toHaveKeys([
            'rp',
            'user',
            'challenge',
            'pubKeyCredParams',
            'authenticatorSelection',
            'excludeCredentials',
        ])
        ->and($decoded['user']['name'])->toBe('Allen')
        ->and($decoded['user']['displayName'])->toBe('Allen')
        ->and($decoded['authenticatorSelection']['userVerification'])->toBe('required')
        ->and($decoded['authenticatorSelection']['residentKey'])->toBe('required');
});

test('it propagates serializer exceptions when generating registration options', function () {
    $user = User::factory()->create();

    $serializerException = new class extends Exception implements SerializerExceptionInterface {};
    $serializerMock = Mockery::mock(Serializer::class);
    $serializerMock->shouldReceive('toJson')
        ->once()
        ->andThrow(new $serializerException('Serialization failed'));

    $action = new GeneratePasskeyRegisterOptionsAction($serializerMock);

    expect(fn () => $action->handle($user))->toThrow($serializerException::class);
});
