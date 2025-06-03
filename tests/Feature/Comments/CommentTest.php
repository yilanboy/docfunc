<?php

use App\Models\Comment;
use App\Models\Post;

use function Pest\Laravel\get;

describe('comment tests', function () {
    test('user can view a post with comments', function () {
        $user = loginAsUser();
        $post = Post::factory()->create();

        Comment::factory(10)->create(['post_id' => $post->id, 'user_id' => $user->id]);

        get($post->link_with_slug)
            ->assertStatus(200);
    });

    test('guest can view a post with anonymous comments', function () {
        $post = Post::factory()->create();

        Comment::factory(10)->create(['post_id' => $post->id, 'user_id' => null]);

        get($post->link_with_slug)
            ->assertStatus(200);
    });

    test('user can view a post with comments ans its children', function () {
        $user = loginAsUser();
        $post = Post::factory()->create();

        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);
        Comment::factory(2)->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
        ]);

        get($post->link_with_slug)
            ->assertStatus(200);
    });

    test('guest can view a post with comments ans its children', function () {
        $post = Post::factory()->create();

        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => null]);
        Comment::factory(2)->create([
            'post_id' => $post->id,
            'user_id' => null,
            'parent_id' => $comment->id,
        ]);

        get($post->link_with_slug)
            ->assertStatus(200);
    });

    test('user can visit comments show page', function () {
        $user = loginAsUser();
        $post = Post::factory()->create();

        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        get(route('comments.show', $comment->id))
            ->assertStatus(200)
            ->assertSee($comment->body);
    });

    test('user can anonymous visit comments show page', function () {
        $post = Post::factory()->create();

        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => null]);

        get(route('comments.show', $comment->id))
            ->assertStatus(200)
            ->assertSee($comment->body);
    });

    test('user can visit comments show page with its children', function () {
        $user = loginAsUser();
        $post = Post::factory()->create();

        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);
        Comment::factory(2)->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
        ]);

        get(route('comments.show', $comment->id))
            ->assertStatus(200)
            ->assertSee($comment->body);
    });

    test('guest can visit comments show page with its children', function () {
        $post = Post::factory()->create();

        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => null]);
        Comment::factory(2)->create([
            'post_id' => $post->id,
            'user_id' => null,
            'parent_id' => $comment->id,
        ]);

        get(route('comments.show', $comment->id))
            ->assertStatus(200)
            ->assertSee($comment->body);
    });
});
