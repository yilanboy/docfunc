<?php

declare(strict_types=1);

use App\Actions\AuthenticateWithPasskeyAction;
use App\Models\Passkey;
use App\Models\User;

function createStoredUserPasskey(User $user): Passkey
{
    return $user->passkeys()->create([
        'name'          => 'passkey-1',
        'credential_id' => 'VJzplgZvT6WiyhirG1BR0g',
        'data'          => [
            'publicKeyCredentialId' => 'VJzplgZvT6WiyhirG1BR0g',
            'type'                  => 'public-key',
            'transports'            => ['internal', 'hybrid'],
            'attestationType'       => 'none',
            'trustPath'             => [],
            'aaguid'                => 'd548826e-79b4-db40-a3d8-11116f7e8349',
            'credentialPublicKey'   => 'pQECAyYgASFYIDmVeb2lam-IwR_x-0t93x_2abq32kmh9AJotixqT06hIlggDOCXVrQQC7yV2AGW1uSbx3gQTrFidMx5YW9ERQfkyLE',
            'userHandle'            => 'MQ',
            'counter'               => 0,
            'backupEligible'        => true,
            'backupStatus'          => true,
            'uvInitialized'         => true,
        ],
    ]);
}

function validPasskeyAuthenticationOptions(?string $rpId = 'docfunc.test'): string
{
    $options = [
        'challenge'        => 'OWZrbjlQR3FmMklJS3ZkYg',
        'allowCredentials' => [],
    ];

    if ($rpId !== null) {
        $options['rpId'] = $rpId;
    }

    return json_encode($options, JSON_THROW_ON_ERROR);
}

function validPasskeyAssertionJson(): string
{
    return json_encode([
        'id'                      => 'VJzplgZvT6WiyhirG1BR0g',
        'rawId'                   => 'VJzplgZvT6WiyhirG1BR0g',
        'response'                => [
            'authenticatorData' => 'a9ml1beJcKvWG7RE0SDYK4T4BGHPjPXzV-lTEeVjiGYdAAAAAA',
            'clientDataJSON'    => 'eyJ0eXBlIjoid2ViYXV0aG4uZ2V0IiwiY2hhbGxlbmdlIjoiT1dacmJqbFFSM0ZtTWtsSlMzWmtZZyIsIm9yaWdpbiI6Imh0dHBzOi8vZG9jZnVuYy50ZXN0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ',
            'signature'         => 'MEUCIQD_JBxx5UnwFrsML3wOgo6Pfe5bZh2DTEtBg148k-z4mQIgJ6HGBTtLJwy2IVkIXB4X2MWhKq5l4HjVNx4PQ3dLDFk',
            'userHandle'        => 'MQ',
        ],
        'type'                    => 'public-key',
        'clientExtensionResults'  => [],
        'authenticatorAttachment' => 'platform',
    ], JSON_THROW_ON_ERROR);
}

test('it authenticates a valid passkey assertion and updates last_used_at', function () {
    $user = User::factory()->create(['name' => 'Allen']);
    $storedPasskey = createStoredUserPasskey($user);

    $authenticatedPasskey = app(AuthenticateWithPasskeyAction::class)->handle(
        answerJson: validPasskeyAssertionJson(),
        optionsJson: validPasskeyAuthenticationOptions(),
        host: 'docfunc.test',
    );

    expect($authenticatedPasskey)->toBeInstanceOf(Passkey::class)
        ->and($authenticatedPasskey->is($storedPasskey))->toBeTrue()
        ->and($authenticatedPasskey->fresh()->last_used_at)->not->toBeNull();
});

test('it returns null when credential is missing', function () {
    $passkey = app(AuthenticateWithPasskeyAction::class)->handle(
        answerJson: validPasskeyAssertionJson(),
        optionsJson: validPasskeyAuthenticationOptions(),
        host: 'docfunc.test',
    );

    expect($passkey)->toBeNull();
});

test('it returns null when assertion payload or host verification fails', function (string $host, string $answerJson, ?string $rpId) {
    $user = User::factory()->create(['name' => 'Allen']);
    $storedPasskey = createStoredUserPasskey($user);

    $passkey = app(AuthenticateWithPasskeyAction::class)->handle(
        answerJson: $answerJson,
        optionsJson: validPasskeyAuthenticationOptions($rpId),
        host: $host,
    );

    expect($passkey)->toBeNull()
        ->and($storedPasskey->fresh()->last_used_at)->toBeNull();
})->with([
    'invalid json'  => ['docfunc.test', '{invalid-json', 'docfunc.test'],
    'missing rawId' => ['docfunc.test', '{"type":"public-key"}', 'docfunc.test'],
    'rpId mismatch' => ['docfunc.test', validPasskeyAssertionJson(), 'wrong.test'],
    'host mismatch' => ['wrong.test', validPasskeyAssertionJson(), null],
]);
