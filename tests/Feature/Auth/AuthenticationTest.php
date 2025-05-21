<?php

use App\Livewire\Pages\Auth\LoginPage;
use App\Livewire\Shared\HeaderPart;
use App\Models\Passkey;
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
        ->call('login')
        ->assertDispatched('toast', status: 'success', message: '登入成功！')
        ->assertRedirect('/');

    $this->assertAuthenticated();
});

test('email is required', function () {
    User::factory()->create();

    livewire(LoginPage::class)
        ->set('email', '')
        ->set('password', 'Password101')
        ->call('login')
        ->assertHasErrors(['email' => 'required']);
});

test('password is required', function () {
    User::factory()->create();

    livewire(LoginPage::class)
        ->set('email', 'email@examle.com')
        ->set('password', '')
        ->call('login')
        ->assertHasErrors(['password' => 'required']);
});

test('email must be a valid email address', function () {
    livewire(LoginPage::class)
        ->set('email', 'wrongEmail')
        ->set('password', 'Password101')
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
        ->call('login');

    $this->assertGuest();
});

test('login user can logout', function () {
    loginAsUser();

    livewire(HeaderPart::class)
        ->call('logout');

    $this->assertGuest();
});

test("users can't login if they has a passkey", function () {
    $user = User::factory()->create([
        'password' => bcrypt('correctPassword101'),
    ]);

    Passkey::factory()
        ->create(['user_id' => $user->id]);

    livewire(LoginPage::class)
        ->set('email', $user->email)
        ->set('password', 'correctPassword101')
        ->call('login')
        ->assertSeeText('您的帳號已註冊密碼金鑰，請使用密碼金鑰進行登入');

    $this->assertGuest();
});
