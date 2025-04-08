<?php

use App\Livewire\Pages\Users\DestroyUserPage;
use App\Mail\DestroyUser;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

// covers(DestroyUserPage::class);

describe('destroy user', function () {
    beforeEach(function () {
        $this->destroyUserConfirmationRouteName = 'users.destroy';
        $this->urlValidMinutes = 5;
    });

    test('non-logged-in users cannot access the destroy user page', function () {
        $user = User::factory()->create();

        get(route('settings.users.destroy', ['id' => $user->id]))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    });

    test('users can access the destroy user page', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        get(route('settings.users.destroy', ['id' => $user->id]))
            ->assertSuccessful();
    });

    test('Users cannot access other users\' destroy user pages', function () {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $this->actingAs($user);

        get(route('settings.users.destroy', ['id' => $anotherUser->id]))
            ->assertForbidden();
    });

    test('destroy user route name must be users.destroy', function () {
        $user = loginAsUser();

        livewire(DestroyUserPage::class, ['id' => $user->id])
            ->assertSet('destroyUserConfirmationRouteName', $this->destroyUserConfirmationRouteName)
            ->assertSet('urlValidMinutes', $this->urlValidMinutes);
    });

    test('schedule the task of sending the \'destroy user\' email in the queue', function () {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(DestroyUserPage::class, ['id' => $user->id])
            ->call('sendDestroyEmail', user: $user)
            ->assertDispatched('info-badge', status: 'success', message: '已寄出信件！');

        Mail::assertQueued(DestroyUser::class);
    });

    test('users can destroy their accounts', function () {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->actingAs($user);

        $destroyUserLink = URL::temporarySignedRoute(
            $this->destroyUserConfirmationRouteName,
            now()->addMinutes(5),
            ['user' => $user->id]
        );

        get($destroyUserLink)->assertStatus(302);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    test('if the link is no longer available, users cannot destroy their account', function () {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->actingAs($user);

        $destroyUserLink = URL::temporarySignedRoute(
            $this->destroyUserConfirmationRouteName,
            now()->addMinutes(5),
            ['user' => $user->id]
        );

        // 讓時間經過 6 分鐘，使連結失效
        $this->travel(6)->minutes();

        get($destroyUserLink)->assertStatus(401);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    });

    // if the user has been destroyed, the user's posts should also be destroyed
    // and user id of comments should be set to null
    test('if the user has been destroyed, the user\'s posts and comments should also be destroyed', function () {
        $user = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $user->id]);

        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'user_id' => $user->id]);

        $user->delete();

        // the posts should be destroyed in the database by constraint
        // the user id of comments should be updated in the database by constraint
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'user_id' => null]);
    });
});
