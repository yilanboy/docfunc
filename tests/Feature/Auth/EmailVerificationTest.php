<?php

use App\Livewire\Pages\Auth\VerifyEmailPage;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

use function Pest\Livewire\livewire;

test('email verification screen can be rendered', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user)
        ->get('/verify-email')
        ->assertStatus(200);
});

test('if user has already verified email, redirect user to previous page', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/verify-email')
        ->assertRedirectToRoute('root');
});

test('email can be verified', function () {
    Event::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect('/?verified=1');

    Event::assertDispatched(Verified::class);

    $this->assertTrue($user->fresh()->hasVerifiedEmail());
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    $this->assertFalse($user->fresh()->hasVerifiedEmail());
});

test('if user has already verified email, verified link will redirect user to previous page', function () {
    Event::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect('/?verified=1');

    Event::assertNotDispatched(Verified::class);
});

test('user can ask to resend verified email', function () {
    Event::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $this->actingAs($user);

    livewire(VerifyEmailPage::class)
        ->call('sendVerification');

    Event::assertNotDispatched(Verified::class);
});
