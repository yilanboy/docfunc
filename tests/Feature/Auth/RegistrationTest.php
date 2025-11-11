<?php

use App\Models\Setting;
use App\Models\User;

use function Pest\Laravel\get;

beforeEach(function () {
    Setting::query()
        ->where('key', 'allow_register')
        ->firstOrFail()
        ->update(['value' => true]);
});

test('registration screen can be rendered', function () {
    $registerSetting = Setting::query()
        ->where('key', 'allow_register')
        ->firstOrFail();

    expect($registerSetting->value)->toBeTrue();

    get('/register')->assertStatus(200);
});

test('guest can register', function () {
    Livewire::test('pages::auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertRedirect('/verify-email');

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'name'  => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('password will be hashed', function () {
    Livewire::test('pages::auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertRedirect('/verify-email');

    $this->assertAuthenticated();

    $user = User::query()
        ->where('email', 'test@example.com')
        ->firstOrFail();

    expect(Hash::check('Password101', $user->password))->toBeTrue();
});

test('name is required', function () {
    Livewire::test('pages::auth.register')
        ->set('name', '')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['name' => 'required']);
});

// name must be unique
test('name must be unique', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
    ]);

    Livewire::test('pages::auth.register')
        ->set('name', $user->name)
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['name' => 'unique']);
});

test('the number of characters in the name must be between 3 and 25.', function (string $name) {
    Livewire::test('pages::auth.register')
        ->set('name', $name)
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['name' => 'between']);
})->with([
    'ya',
    'ThisIsAVeryLongNameThatExceedsTheMaximumNumberOfCharacters',
]);

// name must be alphanumeric, '-' and '_'
test('name must be alphanumeric, \'-\' and \'_\'', function (string $name) {
    Livewire::test('pages::auth.register')
        ->set('name', $name)
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['name' => 'regex']);
})->with([
    'Allen@', 'Allen$', 'Allen#', 'Allen%', 'Allen^',
    'Allen&', 'Allen*', 'Allen(', 'Allen)', 'Allen=',
    'Allen+', 'Allen[', 'Allen]', 'Allen{', 'Allen}',
    'Allen|', 'Allen\\', 'Allen:', 'Allen;', 'Allen"',
    'Allen\'', 'Allen<', 'Allen>', 'Allen,', 'Allen.',
    'Allen?', 'Allen/', 'Allen~', 'Allen`', 'Allen!',
]);

// name input will be trimmed
test('name input will be trimmed', function () {
    Livewire::test('pages::auth.register')
        ->set('name', ' Test User ')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register');

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
    ]);
});

test('email is required', function () {
    Livewire::test('pages::auth.register')
        ->set('name', 'Test User')
        ->set('email', '')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be valid', function () {
    Livewire::test('pages::auth.register')
        ->set('name', 'Test User')
        ->set('email', 'wrongEmail')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['email' => 'email']);
});

test('email must be unique', function () {
    $user = User::factory()->create([
        'name'  => 'Test User',
        'email' => 'test@example.com',
    ]);

    Livewire::test('pages::auth.register')
        ->set('name', 'Test User 2')
        ->set('email', $user->email)
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

test('password is required', function () {
    Livewire::test('pages::auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', '')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['password' => 'required']);
});

test('password must be confirmed', function () {
    Livewire::test('pages::auth.register')
        ->set('name', 'Allen')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password102')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('password must be at least 8 characters, mixed case, numbers and letters', function (string $password) {
    Livewire::test('pages::auth.register')
        ->set('name', 'Allen')
        ->set('email', 'test@example.com')
        ->set('password', $password)
        ->set('password_confirmation', $password)
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors();
})->with([
    'password', 'PASSWORD', 'Password', 'password101',
    'PASSWORD101', '12345678', 'abcdefgh',
]);

test('guests cannot visit the registration page when registration is not allowed', function () {
    Setting::query()
        ->where('key', 'allow_register')
        ->firstOrFail()
        ->update(['value' => false]);

    get(route('register'))->assertStatus(503);
});

test('guests cannot see the register button', function () {
    Setting::query()
        ->where('key', 'allow_register')
        ->firstOrFail()
        ->update(['value' => false]);

    Livewire::test('layouts.header')->assertDontSeeText('註冊');
});
