<?php

use App\Http\Livewire\Users\Edit\ChangePassword;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\get;

uses(LazilyRefreshDatabase::class);

test('non-logged-in users cannot access the update password page', function () {
    $user = User::factory()->create();

    get(route('users.changePassword', $user->id))
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('users can access the update password page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    get(route('users.changePassword', $user->id))
        ->assertSuccessful();
});

test('users can update their passwords', function () {
    $oldPassword = 'Password101';
    $newPassword = 'NewPassword101';

    $user = User::factory()->create(['password' => bcrypt($oldPassword)]);

    $this->actingAs($user);

    Livewire::test(ChangePassword::class, ['user' => $user])
        ->set('current_password', $oldPassword)
        ->set('new_password', $newPassword)
        ->set('new_password_confirmation', $newPassword)
        ->call('update')
        ->assertDispatchedBrowserEvent('info-badge', ['status' => 'success', 'message' => '密碼更新成功！']);

    $user->refresh();

    expect(Hash::check($newPassword, $user->password))->toBeTrue();
});

test('can\'t update password with wrong old password', function () {
    $oldPassword = 'Password101';
    $wrongPassword = 'WrongPassword101';
    $newPassword = 'NewPassword101';

    $user = User::factory()->create(['password' => bcrypt($oldPassword)]);

    $this->actingAs($user);

    Livewire::test(ChangePassword::class, ['user' => $user])
        ->set('current_password', $wrongPassword)
        ->set('new_password', $newPassword)
        ->set('new_password_confirmation', $newPassword)
        ->call('update')
        ->assertHasErrors('current_password');

    $user->refresh();

    expect(Hash::check($oldPassword, $user->password))->toBeTrue();
});

test('can\'t update password if the "new password" is different from the "confirm new password"', function () {
    $oldPassword = 'Password101';
    $newPassword = 'NewPassword101';
    $wrongNewPasswordConfirmation = 'NewPassword102';

    $user = User::factory()->create(['password' => bcrypt($oldPassword)]);

    $this->actingAs($user);

    Livewire::test(ChangePassword::class, ['user' => $user])
        ->set('current_password', $oldPassword)
        ->set('new_password', $newPassword)
        ->set('new_password_confirmation', $wrongNewPasswordConfirmation)
        ->call('update')
        ->assertHasErrors('new_password');

    $user->refresh();

    expect(Hash::check($oldPassword, $user->password))->toBeTrue();
});