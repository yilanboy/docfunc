<?php

use App\Livewire\Pages\Posts\EditPage as PostsEditPage;
use App\Livewire\Shared\Users\PostsGroupByYear;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ContentService;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

describe('edit post', function () {
    test('visitors cannot access the edit pages of other people\'s post', function () {
        $post = Post::factory()->create();

        get(route('posts.edit', ['id' => $post->id]))
            ->assertRedirect(route('login'));
    });

    test('authors can access the edit page of their post', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        get(route('posts.edit', ['id' => $post->id]))
            ->assertSuccessful();
    });

    test('users cannot access the edit page of other people\'s post', function () {
        $post = Post::factory()->create();

        loginAsUser();

        get(route('posts.edit', ['id' => $post->id]))
            ->assertForbidden();
    });

    test('authors can update their posts', function ($categoryId) {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $newTitle = str()->random(4);
        $newBody = str()->random(500);
        $newPrivateStatus = (bool) rand(0, 1);

        $newTagCollection = Tag::factory()->count(3)->create();

        $newTagsJson = $newTagCollection
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->toJson();

        livewire(PostsEditPage::class, ['id' => $post->id])
            ->set('form.title', $newTitle)
            ->set('form.category_id', $categoryId)
            ->set('form.tags', $newTagsJson)
            ->set('form.body', $newBody)
            ->set('form.is_private', $newPrivateStatus)
            ->call('save', post: $post)
            ->assertHasNoErrors();

        $post->refresh();

        $contentService = app(ContentService::class);

        $newTagIdsArray = $newTagCollection
            ->map(fn ($item) => $item->id)
            ->all();

        expect($post)
            ->title->toBe($newTitle)
            ->slug->toBe($contentService->makeSlug($newTitle))
            ->category_id->toBe($categoryId)
            ->body->toBe($newBody)
            ->excerpt->toBe($contentService->makeExcerpt($newBody))
            ->is_private->toBe($newPrivateStatus)
            ->and($post->tags->pluck('id')->toArray())->toBe($newTagIdsArray);
    })->with('defaultCategoryIds');

    test('users can update the private status of their posts in user info page', function ($privateStatus) {
        $post = Post::factory()->create([
            'is_private' => $privateStatus,
            'created_at' => now(),
        ]);

        loginAsUser($post->user);

        livewire(PostsGroupByYear::class, [
            'year' => now()->year,
            'userId' => $post->user_id,
            'posts' => $post->all(),
        ])
            ->call('privateStatusToggle', $post->id)
            ->assertHasNoErrors()
            ->assertDispatched(
                'toast',
                status: 'success',
                // if original status is true, then the message should be '文章狀態已切換為公開'
                // because the status is toggled to false
                message: $privateStatus ? '文章狀態已切換為公開' : '文章狀態已切換為私人',
            );

        $post->refresh();

        expect($post->is_private)->toBe(! $privateStatus);
    })->with([true, false]);

    test('toggle private status won\'t touch timestamp', function ($privateStatus) {
        $post = Post::factory()->create([
            'is_private' => $privateStatus,
            'created_at' => now(),
        ]);

        $oldUpdatedAt = $post->updated_at;

        loginAsUser($post->user);

        livewire(PostsGroupByYear::class, [
            'year' => now()->year,
            'userId' => $post->user_id,
            'posts' => $post->all(),
        ])
            ->call('privateStatusToggle', $post->id);

        $post->refresh();

        $newUpdatedAt = $post->updated_at;

        expect($oldUpdatedAt)->toEqual($newUpdatedAt);
    })->with([true, false]);
});
