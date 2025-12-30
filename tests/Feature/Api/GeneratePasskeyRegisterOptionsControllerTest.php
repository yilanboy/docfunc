<?php

use App\Models\User;
use App\Services\Serializer;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('it returns register options', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->getJson(route('passkeys.register-options'))
        ->assertStatus(200)
        ->assertJsonStructure([
            'rp',
            'user',
            'challenge',
            'pubKeyCredParams',
            'authenticatorSelection',
            'excludeCredentials',
        ]);
});

test('it returns error when user is not authenticated', function () {
    getJson(route('passkeys.register-options'))
        ->assertStatus(401);
});

test('it returns 500 and logs error when serialization fails', function () {
    $user = User::factory()->create();

    $serializerException = new class extends Exception implements SerializerExceptions {};

    // 模擬 Serializer 拋出例外
    $serializerMock = Mockery::mock(Serializer::class);
    $serializerMock->shouldReceive('toJson')
        ->andThrow(new $serializerException('Serialization failed'));

    $this->app->instance(Serializer::class, $serializerMock);

    Log::shouldReceive('error')
        ->once()
        ->with('Webauthn 註冊選項序列化失敗', Mockery::on(function ($context) use ($user) {
            return $context['user_id'] === $user->id && $context['exception'] === 'Serialization failed';
        }));

    actingAs($user)
        ->getJson(route('passkeys.register-options'))
        ->assertStatus(400)
        ->assertJson([
            'error' => '伺服器發生錯誤，無法序列化註冊選項。',
        ]);
});
