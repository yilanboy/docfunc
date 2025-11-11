<?php

use App\Models\Passkey;
use App\Models\User;

use function Pest\Laravel\get;

test('login screen can be rendered', function () {
    get('/login')
        ->assertSuccessful()
        ->assertSee('<title>登入</title>', false);
});

test('users can authenticate using the login screen', function () {
    $password = 'Password101';

    $user = User::factory()->create([
        'password' => bcrypt($password),
    ]);

    // use request() will cause livewire tests fail
    // https://github.com/livewire/livewire/issues/936
    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', $password)
        ->call('login')
        ->assertDispatched('toast', status: 'success', message: '登入成功！')
        ->assertRedirect('/');

    $this->assertAuthenticated();
});

test('email is required', function () {
    User::factory()->create();

    Livewire::test('pages::auth.login')
        ->set('email', '')
        ->set('password', 'Password101')
        ->call('login')
        ->assertHasErrors(['email' => 'required']);
});

test('password is required', function () {
    User::factory()->create();

    Livewire::test('pages::auth.login')
        ->set('email', 'email@examle.com')
        ->set('password', '')
        ->call('login')
        ->assertHasErrors(['password' => 'required']);
});

test('email must be a valid email address', function () {
    Livewire::test('pages::auth.login')
        ->set('email', 'wrongEmail')
        ->set('password', 'Password101')
        ->call('login')
        ->assertHasErrors(['email' => 'email']);
});

test('users cannot authenticate with an invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correctPassword101'),
    ]);

    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrongPassword101')
        ->call('login');

    $this->assertGuest();
});

test('a logged-in user can log out', function () {
    loginAsUser();

    Livewire::test('layouts.header')
        ->call('logout');

    $this->assertGuest();
});

test('users cannot log in if they have a passkey', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correctPassword101'),
    ]);

    Passkey::factory()
        ->create(['owner_id' => $user->id]);

    Livewire::test('pages::auth.login')
        ->set('email', $user->email)
        ->set('password', 'correctPassword101')
        ->call('login')
        ->assertSeeText('您的帳號已註冊密碼金鑰，請使用密碼金鑰進行登入');

    $this->assertGuest();
});

test('users can authenticate using passkey', function () {
    $user = User::factory()->create([
        'name' => 'Allen',
    ]);

    $user->passkeys()->create([
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

    Session::flash('passkey-authentication-options', json_encode([
        'challenge'        => 'OWZrbjlQR3FmMklJS3ZkYg',
        'rpId'             => Uri::of(config('app.url'))->host(),
        'allowCredentials' => [],
    ]));

    Livewire::test('pages::auth.login', [
        'answer' => json_encode([
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
        ]),
    ])
        ->call('loginWithPasskey')
        ->assertDispatched('toast', status: 'success', message: '登入成功！');
});
