<?php

declare(strict_types=1);

use App\Actions\RegisterUserAction;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

test('it registers a user, trims name, hashes password, and dispatches Registered event', function () {
    Event::fake([Registered::class]);

    $user = app(RegisterUserAction::class)->handle([
        'name'     => '  Allen Lin  ',
        'email'    => 'allen@example.com',
        'password' => 'Password101',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Allen Lin')
        ->and($user->email)->toBe('allen@example.com')
        ->and(Hash::check('Password101', $user->password))->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id'    => $user->id,
        'name'  => 'Allen Lin',
        'email' => 'allen@example.com',
    ]);

    Event::assertDispatched(Registered::class, fn (Registered $event): bool => $event->user->is($user));
});
