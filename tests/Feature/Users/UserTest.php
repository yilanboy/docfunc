<?php

use App\Livewire\Shared\Users\PostsPart;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;

use function Pest\Laravel\get;

describe('user', function () {
    test('guest can view user profile', function ($tabQueryString) {
        $user = User::factory()->create();

        get(route('users.show', ['id' => $user->id, 'tab' => $tabQueryString]))
            ->assertStatus(200)
            ->assertSeeLivewire(PostsPart::class);
    })->with([
        'information',
        'posts',
        'comments',
    ]);

    test('user can view own profile', function ($tabQueryString) {
        $user = User::factory()->create();

        $this->actingAs($user);

        get(route('users.show', ['id' => $user->id, 'tab' => $tabQueryString]))
            ->assertStatus(200)
            ->assertSeeLivewire(PostsPart::class);
    })->with([
        'information',
        'posts',
        'comments',
    ]);

    test('user can see own posts in posts tab', function () {
        $user = User::factory()
            ->has(Post::factory()->count(3)->state(
                new Sequence(
                    ['title' => 'post 1'],
                    ['title' => 'post 2'],
                    ['title' => 'post 3'],
                )
            ))
            ->create();

        $this->actingAs($user);

        get(route('users.show', ['id' => $user->id, 'tab' => 'posts']))
            ->assertOk()
            ->assertSeeText([
                'post 1',
                'post 2',
                'post 3',
            ]);
    });

    test('user can see soft deleted post in posts tab', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $post->delete();

        get(route('users.show', ['id' => $post->user->id, 'tab' => 'posts']))
            ->assertSuccessful()
            ->assertSeeText('已刪除');
    });

    test('guest can\'t see others soft deleted post in posts tab', function () {
        $post = Post::factory()->create();

        $post->delete();

        get(route('users.show', ['id' => $post->user->id, 'tab' => 'posts']))
            ->assertSuccessful()
            ->assertDontSeeText('文章將於6天後刪除');
    });

    test('user can see own comments in posts tab', function () {
        $user = User::factory()
            ->has(Comment::factory()->count(3)->state(
                new Sequence(
                    ['body' => 'comment 1'],
                    ['body' => 'comment 2'],
                    ['body' => 'comment 3'],
                )
            ))
            ->create();

        $this->actingAs($user);

        get(route('users.show', ['id' => $user->id, 'tab' => 'comments']))
            ->assertOk()
            ->assertSeeText([
                'comment 1',
                'comment 2',
                'comment 3',
            ]);
    });
});
