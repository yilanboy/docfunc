<?php

declare(strict_types=1);

use App\Actions\CreatePasskeyAction;
use App\Mail\CreatePasskeyMail;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function validPasskeyRegistrationOptions(User $user, ?string $rpId = 'docfunc.test'): string
{
    $rp = ['name' => config('app.name')];

    if ($rpId !== null) {
        $rp['id'] = $rpId;
    }

    return json_encode([
        'challenge'              => 'SlhvbldrUGpJeldEUHNrSA',
        'rp'                     => $rp,
        'user'                   => [
            'id'          => 'MQ',
            'name'        => $user->name,
            'displayName' => $user->name,
        ],
        'pubKeyCredParams'       => [],
        'authenticatorSelection' => [
            'userVerification' => 'required',
            'residentKey'      => 'required',
        ],
        'excludeCredentials'     => [],
    ], JSON_THROW_ON_ERROR);
}

function validPasskeyRegistrationJson(): string
{
    return json_encode([
        'id'                      => 'VJzplgZvT6WiyhirG1BR0g',
        'rawId'                   => 'VJzplgZvT6WiyhirG1BR0g',
        'response'                => [
            'attestationObject'  => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YViUa9ml1beJcKvWG7RE0SDYK4T4BGHPjPXzV-lTEeVjiGZdAAAAANVIgm55tNtAo9gREW9-g0kAEFSc6ZYGb0-losoYqxtQUdKlAQIDJiABIVggOZV5vaVqb4jBH_H7S33fH_ZpurfaSaH0Ami2LGpPTqEiWCAM4JdWtBALvJXYAZbW5JvHeBBOsWJ0zHlhb0RFB-TIsQ',
            'clientDataJSON'     => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiU2xodmJsZHJVR3BKZWxkRVVITnJTQSIsIm9yaWdpbiI6Imh0dHBzOi8vZG9jZnVuYy50ZXN0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ',
            'transports'         => ['internal', 'hybrid'],
            'publicKeyAlgorithm' => -7,
            'publicKey'          => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEOZV5vaVqb4jBH_H7S33fH_ZpurfaSaH0Ami2LGpPTqEM4JdWtBALvJXYAZbW5JvHeBBOsWJ0zHlhb0RFB-TIsQ',
            'authenticatorData'  => 'a9ml1beJcKvWG7RE0SDYK4T4BGHPjPXzV-lTEeVjiGZdAAAAANVIgm55tNtAo9gREW9-g0kAEFSc6ZYGb0-losoYqxtQUdKlAQIDJiABIVggOZV5vaVqb4jBH_H7S33fH_ZpurfaSaH0Ami2LGpPTqEiWCAM4JdWtBALvJXYAZbW5JvHeBBOsWJ0zHlhb0RFB-TIsQ',
        ],
        'type'                    => 'public-key',
        'clientExtensionResults'  => [],
        'authenticatorAttachment' => 'platform',
    ], JSON_THROW_ON_ERROR);
}

test('it creates a passkey and queues notification email', function () {
    Mail::fake();

    $user = User::factory()->create(['name' => 'Allen']);

    $passkey = app(CreatePasskeyAction::class)->handle(
        user: $user,
        name: 'MacBook Passkey',
        passkeyJson: validPasskeyRegistrationJson(),
        optionsJson: validPasskeyRegistrationOptions($user),
        host: 'docfunc.test',
    );

    expect($passkey)->toBeInstanceOf(Passkey::class)
        ->and($passkey->name)->toBe('MacBook Passkey')
        ->and($passkey->credential_id)->toBe('VJzplgZvT6WiyhirG1BR0g')
        ->and($user->passkeys()->count())->toBe(1);

    Mail::assertQueued(CreatePasskeyMail::class, fn (CreatePasskeyMail $mail): bool => $mail->passkeyName === 'MacBook Passkey');
});

test('it returns null when passkey payload or host verification fails', function (string $host, string $passkeyJson, ?string $rpId) {
    Mail::fake();

    $user = User::factory()->create(['name' => 'Allen']);

    $passkey = app(CreatePasskeyAction::class)->handle(
        user: $user,
        name: 'Invalid Passkey',
        passkeyJson: $passkeyJson,
        optionsJson: validPasskeyRegistrationOptions($user, $rpId),
        host: $host,
    );

    expect($passkey)->toBeNull()
        ->and($user->passkeys()->count())->toBe(0);

    Mail::assertNothingQueued();
})->with([
    'invalid json'   => ['docfunc.test', '{invalid-json', 'docfunc.test'],
    'rpId mismatch'  => ['docfunc.test', validPasskeyRegistrationJson(), 'wrong.test'],
    'host mismatch'  => ['wrong.test', validPasskeyRegistrationJson(), null],
]);
