<?php

use App\Livewire\Pages\Auth\RegisterPage;
use App\Livewire\Shared\HeaderPart;
use App\Models\Setting;
use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

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
    livewire(RegisterPage::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertRedirect('/verify-email');

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('name is required', function () {
    livewire(RegisterPage::class)
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

    livewire(RegisterPage::class)
        ->set('name', $user->name)
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['name' => 'unique']);
});

test('the number of characters in the name must be between 3 and 25.', function (string $name) {
    livewire(RegisterPage::class)
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
    livewire(RegisterPage::class)
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
    livewire(RegisterPage::class)
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
    livewire(RegisterPage::class)
        ->set('name', 'Test User')
        ->set('email', '')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be valid', function () {
    livewire(RegisterPage::class)
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
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    livewire(RegisterPage::class)
        ->set('name', 'Test User 2')
        ->set('email', $user->email)
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

test('password is required', function () {
    livewire(RegisterPage::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', '')
        ->set('password_confirmation', 'Password101')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['password' => 'required']);
});

test('password must be confirmed', function () {
    livewire(RegisterPage::class)
        ->set('name', 'Allen')
        ->set('email', 'test@example.com')
        ->set('password', 'Password101')
        ->set('password_confirmation', 'Password102')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('register')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('password must be at least 8 characters, mixed case, numbers and letters', function (string $password) {
    livewire(RegisterPage::class)
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

test('guest can not visit register page when register is not allowed', function () {
    Setting::query()
        ->where('key', 'allow_register')
        ->firstOrFail()
        ->update(['value' => false]);

    get(route('register'))->assertStatus(503);
});

test('guest can not see register button', function () {
    Setting::query()
        ->where('key', 'allow_register')
        ->firstOrFail()
        ->update(['value' => false]);

    livewire(HeaderPart::class)->assertDontSeeText('註冊');
});
