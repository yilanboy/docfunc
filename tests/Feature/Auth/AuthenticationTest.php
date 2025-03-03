<?php

use App\Livewire\Pages\Auth\LoginPage;
use App\Livewire\Shared\Header;
use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

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
    livewire(LoginPage::class)
        ->set('email', $user->email)
        ->set('password', $password)
        ->set('captchaToken', 'fake-captcha-response')
        ->call('login')
        ->assertDispatched('info-badge', status: 'success', message: '登入成功！')
        ->assertRedirect('/');

    $this->assertAuthenticated();
});

test('email is required', function () {
    User::factory()->create();

    livewire(LoginPage::class)
        ->set('email', '')
        ->set('password', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('login')
        ->assertHasErrors(['email' => 'required']);
});

test('password is required', function () {
    User::factory()->create();

    livewire(LoginPage::class)
        ->set('email', 'email@examle.com')
        ->set('password', '')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('login')
        ->assertHasErrors(['password' => 'required']);
});

test('captcha token is required', function () {
    User::factory()->create();

    livewire(LoginPage::class)
        ->set('email', 'allen@example.com')
        ->set('password', 'Password101')
        ->set('captchaToken', '')
        ->call('login')
        ->assertHasErrors(['captchaToken' => 'required']);
});

test('captcha challenge failed', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['response' => false]),
    ]);

    User::factory()->create();

    livewire(LoginPage::class)
        ->set('email', 'allen@example.com')
        ->set('password', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('login')
        ->assertHasErrors(['captchaToken']);
});

test('email must be a valid email address', function () {
    livewire(LoginPage::class)
        ->set('email', 'wrongEmail')
        ->set('password', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('login')
        ->assertHasErrors(['email' => 'email']);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correctPassword101'),
    ]);

    livewire(LoginPage::class)
        ->set('email', $user->email)
        ->set('password', 'wrongPassword101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('login');

    $this->assertGuest();
});

test('login user can logout', function () {
    loginAsUser();

    livewire(Header::class)
        ->call('logout');

    $this->assertGuest();
});
