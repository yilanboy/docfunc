<?php

use App\Livewire\Pages\Users\UpdatePasswordPage;
use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

describe('user password', function () {
    test('non-logged-in users cannot access the update password page', function () {
        $user = User::factory()->create();

        get(route('users.password', ['id' => $user->id]))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    });

    test('users cannot access the others update password page', function () {
        $user = User::factory()->create();

        loginAsUser();

        get(route('users.password', ['id' => $user->id]))
            ->assertStatus(403);
    });

    test('users can access the update password page', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        get(route('users.password', ['id' => $user->id]))
            ->assertSuccessful();
    });

    test('users can update their passwords', function () {
        $oldPassword = 'Password101';
        $newPassword = 'NewPassword101';

        $user = User::factory()->create(['password' => bcrypt($oldPassword)]);

        $this->actingAs($user);

        livewire(UpdatePasswordPage::class, ['id' => $user->id])
            ->set('current_password', $oldPassword)
            ->set('new_password', $newPassword)
            ->set('new_password_confirmation', $newPassword)
            ->call('update', user: $user)
            ->assertDispatched('info-badge', status: 'success', message: '密碼更新成功！');

        $user->refresh();

        expect(Hash::check($newPassword, $user->password))->toBeTrue();
    });

    test('can\'t update password with wrong old password', function () {
        $oldPassword = 'Password101';
        $wrongPassword = 'WrongPassword101';
        $newPassword = 'NewPassword101';

        $user = User::factory()->create(['password' => bcrypt($oldPassword)]);

        $this->actingAs($user);

        livewire(UpdatePasswordPage::class, ['id' => $user->id])
            ->set('current_password', $wrongPassword)
            ->set('new_password', $newPassword)
            ->set('new_password_confirmation', $newPassword)
            ->call('update', user: $user)
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

        livewire(UpdatePasswordPage::class, ['id' => $user->id])
            ->set('current_password', $oldPassword)
            ->set('new_password', $newPassword)
            ->set('new_password_confirmation', $wrongNewPasswordConfirmation)
            ->call('update', user: $user)
            ->assertHasErrors('new_password');

        $user->refresh();

        expect(Hash::check($oldPassword, $user->password))->toBeTrue();
    });
});
