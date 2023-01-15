<?php

use App\Http\Livewire\Users\Edit\DeleteUser;
use App\Mail\DestroyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\get;

uses(LazilyRefreshDatabase::class);

test('non-logged-in users cannot access the delete user page', function () {
    $user = User::factory()->create();

    get(route('users.delete', $user->id))
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('users can access the delete user page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    get(route('users.delete', $user->id))
        ->assertSuccessful();
});

test('schedule the task of sending the \'delete user\' email in the queue', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DeleteUser::class, ['user' => $user])
        ->call('sendDestroyEmail');

    Mail::assertQueued(DestroyUser::class);
});

test('users can delete their accounts', function () {
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', ['id' => $user->id]);

    $this->actingAs($user);

    $destroyUserLink = URL::temporarySignedRoute(
        'users.destroy',
        now()->addMinutes(5),
        ['user' => $user->id]
    );

    get($destroyUserLink)->assertStatus(302);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('if the link is no longer available, users cannot delete their account', function () {
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', ['id' => $user->id]);

    $this->actingAs($user);

    $destroyUserLink = URL::temporarySignedRoute(
        'users.destroy',
        now()->addMinutes(5),
        ['user' => $user->id]
    );

    // 讓時間經過 6 分鐘，使連結失效
    $this->travel(6)->minutes();

    get($destroyUserLink)->assertStatus(401);

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
