<?php

use App\Livewire\Shared\Posts\PostDesktopMenu;
use App\Livewire\Shared\Posts\PostMobileMenu;
use App\Livewire\Shared\Users\PostsGroupByYear;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

use function Pest\Livewire\livewire;

// covers(PostMobileMenu::class, PostDesktopMenu::class, PostsGroupByYear::class);

describe('destroy post', function () {
    test('author can soft delete own post in desktop show post page', function () {
        $post = Post::factory()->create();

        $this->actingAs(User::find($post->user_id));

        livewire(PostDesktopMenu::class, [
            'postId' => $post->id,
            'postTitle' => $post->title,
            'authorId' => $post->user_id,
        ])
            ->call('destroy', $post->id)
            ->assertDispatched('info-badge', status: 'success', message: '成功刪除文章！')
            ->assertRedirect(route('users.show', [
                'id' => $post->user_id,
                'tab' => 'posts',
                'current-posts-year' => $post->created_at->format('Y'),
            ]));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    test('guest cannot delete others\' post in desktop show post page', function () {
        $post = Post::factory()->create();

        livewire(PostDesktopMenu::class, [
            'postId' => $post->id,
            'postTitle' => $post->title,
            'authorId' => $post->user_id,
        ])
            ->call('destroy', $post->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('user cannot delete others\' post in desktop show post page', function () {
        $post = Post::factory()->create();

        // Login as another user
        loginAsUser();

        livewire(PostDesktopMenu::class, [
            'postId' => $post->id,
            'postTitle' => $post->title,
            'authorId' => $post->user_id,
        ])
            ->call('destroy', $post->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('author can soft delete own post in mobile show post page', function () {
        $post = Post::factory()->create();

        loginAsUser(User::find($post->user_id));

        livewire(PostMobileMenu::class, ['postId' => $post->id])
            ->call('destroy', $post->id)
            ->assertDispatched('info-badge', status: 'success', message: '成功刪除文章！')
            ->assertRedirect(route('users.show', [
                'id' => $post->user_id,
                'tab' => 'posts',
                'current-posts-year' => $post->created_at->format('Y'),
            ]));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    test('guest cannot delete others\' post in mobile show post page', function () {
        $post = Post::factory()->create();

        livewire(PostMobileMenu::class, ['postId' => $post->id])
            ->call('destroy', $post->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('user cannot delete others\' post in mobile show post page', function () {
        $post = Post::factory()->create();

        loginAsUser();

        livewire(PostMobileMenu::class, ['postId' => $post->id])
            ->call('destroy', $post->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('author can soft delete own post in user information post card', function () {
        $post = Post::factory()->create();

        loginAsUser(User::find($post->user_id));

        livewire(PostsGroupByYear::class, [
            'posts' => [$post],
            'userId' => $post->user_id,
            'year' => $post->created_at->format('Y'),
        ])
            ->call('destroy', $post->id)
            ->assertDispatched('info-badge', status: 'success', message: '文章已刪除');

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    test('guest cannot delete others\' post in user information post card', function () {
        $post = Post::factory()->create();

        livewire(PostsGroupByYear::class, [
            'posts' => [$post],
            'userId' => $post->user_id,
            'year' => $post->created_at->format('Y'),
        ])
            ->call('destroy', $post->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('user cannot delete others\' post in user information post card', function () {
        $post = Post::factory()->create();

        loginAsUser();

        livewire(PostsGroupByYear::class, [
            'posts' => [$post],
            'userId' => $post->user_id,
            'year' => $post->created_at->format('Y'),
        ])
            ->call('destroy', $post->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('author can restore deleted post', function () {
        $user = loginAsUser();

        $post = Post::factory()->create([
            'title' => 'This is a test post title',
            'user_id' => $user->id,
            'category_id' => 1,
            'deleted_at' => now(),
        ]);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        livewire(PostsGroupByYear::class, [
            'posts' => [$post],
            'userId' => $post->user_id,
            'year' => $post->created_at->format('Y'),
        ])
            ->call('restore', $post->id)
            ->assertDispatched('info-badge', status: 'success', message: '文章已恢復');

        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    });

    test('soft delete and restore won\'t touch timestamp', function () {
        $user = loginAsUser();

        $post = Post::factory()->create([
            'title' => 'This is a test post title',
            'user_id' => $user->id,
            'category_id' => 1,
            'deleted_at' => now(),
        ]);

        $oldUpdatedAt = $post->updated_at;

        livewire(PostsGroupByYear::class, [
            'posts' => [$post],
            'userId' => $post->user_id,
            'year' => $post->created_at->format('Y'),
        ])
            ->call('restore', $post->id);

        $post->refresh();

        $newUpdatedAt = $post->updated_at;

        expect($oldUpdatedAt)->toEqual($newUpdatedAt);
    });

    test('users cannot restore other users\' post', function () {
        $user = loginAsUser();

        $author = User::factory()->create();

        $post = Post::factory()->create([
            'title' => 'This is a test post title',
            'user_id' => $author->id,
            'category_id' => 1,
            'deleted_at' => now(),
        ]);

        livewire(PostsGroupByYear::class, [
            'posts' => [$post],
            'userId' => $user->id,
            'year' => $post->created_at->format('Y'),
        ])
            ->call('restore', $post->id)
            ->assertForbidden();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    });

    test('prune the stale post', function () {
        $user = User::factory()->create();

        Post::factory()->create([
            'title' => 'This is a stale post',
            'user_id' => $user->id,
            'category_id' => 1,
            'deleted_at' => now()->subDays(31),
        ]);

        Post::factory()->create([
            'title' => 'This is a normal post',
            'user_id' => $user->id,
            'category_id' => 1,
        ]);

        $this->artisan('model:prune');

        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseHas('posts', [
            'title' => 'This is a normal post',
            'category_id' => 1,
        ]);
    });

    // if the post has been deleted, the post's comments should also be deleted
    test('if the post has been deleted, the post\'s comments should also be deleted', function () {
        $user = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $user->id]);

        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);

        $post->forceDelete();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });
});
