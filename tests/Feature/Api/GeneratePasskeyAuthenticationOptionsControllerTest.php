<?php

declare(strict_types=1);


use App\Services\Serializer;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

test('it returns authentication options', function () {
    $this
        ->getJson(route('passkeys.authentication-options'))
        ->assertStatus(200)
        ->assertJsonStructure([
            'challenge',
            'rpId',
            'allowCredentials' => [],
        ]);
});

test('it returns 400 and logs error when serialization fails in returning authentication options', function () {
    $serializerException = new class extends Exception implements SerializerExceptionInterface {};

    // 模擬 Serializer 拋出例外
    $serializerMock = Mockery::mock(Serializer::class);
    $serializerMock->shouldReceive('toJson')
        ->andThrow(new $serializerException('Serialization failed'));

    $this->app->instance(Serializer::class, $serializerMock);

    Log::shouldReceive('error')
        ->once()
        ->with('Webauthn 認證選項序列化失敗', Mockery::on(function ($context) {
            return $context['exception'] === 'Serialization failed';
        }));

    $this
        ->getJson(route('passkeys.authentication-options'))
        ->assertStatus(400)
        ->assertJson([
            'error' => '發生錯誤，無法序列化認證選項。',
        ]);
});
