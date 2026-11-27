<?php

declare(strict_types=1);

use App\Actions\GeneratePasskeyAuthenticationOptionsAction;
use App\Services\Serializer;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

test('it generates passkey authentication options json', function () {
    Config::set('app.url', 'https://docfunc.test');

    $json = app(GeneratePasskeyAuthenticationOptionsAction::class)->handle();
    $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBeArray()
        ->toHaveKeys(['challenge', 'rpId', 'allowCredentials'])
        ->and($decoded['rpId'])->toBe('docfunc.test')
        ->and($decoded['allowCredentials'])->toBe([]);
});

test('it propagates serializer exceptions when generating authentication options', function () {
    $serializerException = new class extends Exception implements SerializerExceptionInterface {};
    $serializerMock = Mockery::mock(Serializer::class);
    $serializerMock->shouldReceive('toJson')
        ->once()
        ->andThrow(new $serializerException('Serialization failed'));

    $action = new GeneratePasskeyAuthenticationOptionsAction($serializerMock);

    expect(fn () => $action->handle())->toThrow($serializerException::class);
});
