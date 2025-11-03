<?php

use App\Mail\CreatePasskeyMail;
use App\Models\User;
use Illuminate\Support\Uri;

test('passkey can be registered', function () {
    Mail::fake();

    $user = User::factory()->create([
        'name' => 'Allen',
    ]);

    loginAsUser($user);

    Session::flash('passkey-registration-options', json_encode([
        'challenge'              => 'SlhvbldrUGpJeldEUHNrSA',
        'rp'                     => [
            'id'   => Uri::of(config('app.url'))->host(),
            'name' => config('app.name'),
        ],
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
    ]));

    Livewire::test('pages::settings.users.passkeys.edit', [
        'id'      => $user->id,
        'name'    => 'passkey-1',
        'passkey' => json_encode([
            'id'                      => 'VJzplgZvT6WiyhirG1BR0g',
            'rawId'                   => 'VJzplgZvT6WiyhirG1BR0g',
            'response'                => [
                'attestationObject'  => 'o2NmbXRkbm9uZWdhdHRTdG10oGhhdXRoRGF0YViUa9ml1beJcKvWG7RE0SDYK4T4BGHPjPXzV-lTEeVjiGZdAAAAANVIgm55tNtAo9gREW9-g0kAEFSc6ZYGb0-losoYqxtQUdKlAQIDJiABIVggOZV5vaVqb4jBH_H7S33fH_ZpurfaSaH0Ami2LGpPTqEiWCAM4JdWtBALvJXYAZbW5JvHeBBOsWJ0zHlhb0RFB-TIsQ',
                'clientDataJSON'     => 'eyJ0eXBlIjoid2ViYXV0aG4uY3JlYXRlIiwiY2hhbGxlbmdlIjoiU2xodmJsZHJVR3BKZWxkRVVITnJTQSIsIm9yaWdpbiI6Imh0dHBzOi8vZG9jZnVuYy50ZXN0IiwiY3Jvc3NPcmlnaW4iOmZhbHNlfQ',
                'transports'         => [
                    'internal',
                    'hybrid',
                ],
                'publicKeyAlgorithm' => -7,
                'publicKey'          => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEOZV5vaVqb4jBH_H7S33fH_ZpurfaSaH0Ami2LGpPTqEM4JdWtBALvJXYAZbW5JvHeBBOsWJ0zHlhb0RFB-TIsQ',
                'authenticatorData'  => 'a9ml1beJcKvWG7RE0SDYK4T4BGHPjPXzV-lTEeVjiGZdAAAAANVIgm55tNtAo9gREW9-g0kAEFSc6ZYGb0-losoYqxtQUdKlAQIDJiABIVggOZV5vaVqb4jBH_H7S33fH_ZpurfaSaH0Ami2LGpPTqEiWCAM4JdWtBALvJXYAZbW5JvHeBBOsWJ0zHlhb0RFB-TIsQ',
            ],
            'type'                    => 'public-key',
            'clientExtensionResults'  => [],
            'authenticatorAttachment' => 'platform',
        ]),
    ])
        ->call('store')
        ->assertDispatched('toast', status: 'success', message: '成功建立密碼金鑰！')
        ->assertDispatched('reset-passkey-name');

    expect($user->passkeys)->toHaveCount(1);

    Mail::assertQueued(CreatePasskeyMail::class);
});
