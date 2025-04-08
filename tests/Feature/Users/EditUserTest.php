<?php

use App\Livewire\Pages\Settings\Users\EditUserPage;
use App\Models\User;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

describe('edit user', function () {
    test('non-logged-in users cannot access the user edit page', function () {
        $user = User::factory()->create();

        get(route('settings.users.edit', ['id' => $user->id]))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    });

    test('users can access their own user edit page', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        get(route('settings.users.edit', ['id' => $user->id]))
            ->assertSuccessful();
    });

    test('users cannot access other people\'s user edit pages', function () {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.users.edit', ['id' => $otherUser->id]))
            ->assertStatus(403);
    });

    test('users can update their own information', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(EditUserPage::class, [
            'id' => $user->id,
            'name' => $user->name,
            'introduction' => $user->introduction,
        ])
            ->set('name', 'New_legal_name')
            ->set('introduction', fake()->realText(120))
            ->call('update', user: $user)
            ->assertDispatched('toast', status: 'success', message: '個人資料更新成功');
    });

    test('if the name format is not correct, the name cannot be updated', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(EditUserPage::class, [
            'id' => $user->id,
            'name' => $user->name,
            'introduction' => $user->introduction,
        ])
            ->set('name', 'Wrong Format Name')
            ->set('introduction', fake()->realText(120))
            ->call('update', user: $user)
            ->assertHasErrors('name');
    });

    test('if the number of words in the introduction exceeds the limit, the introduction cannot be updated',
        function () {
            $user = User::factory()->create();

            $this->actingAs($user);

            livewire(EditUserPage::class, [
                'id' => $user->id,
                'name' => $user->name,
                'introduction' => $user->introduction,
            ])
                ->set('name', 'New_legal_name')
                ->set('introduction', fake()->words(500, true))
                ->call('update', user: $user)
                ->assertHasErrors('introduction');
        });
});
