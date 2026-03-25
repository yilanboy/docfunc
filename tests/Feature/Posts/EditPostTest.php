<?php

use App\Models\Post;
use App\Models\Tag;
use App\Services\ContentService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\get;

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

        Livewire::test('pages::posts.edit', ['id' => $post->id])
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
            ->slug->toBe($contentService->getSlug($newTitle))
            ->category_id->toBe($categoryId)
            ->body->toBe($newBody)
            ->excerpt->toBe($contentService->getExcerpt($newBody))
            ->is_private->toBe($newPrivateStatus)
            ->and($post->tags->pluck('id')->toArray())->toBe($newTagIdsArray);
    })->with('defaultCategoryIds');

    test('users can update the private status of their posts in user info page', function ($privateStatus) {
        $post = Post::factory()->create([
            'is_private' => $privateStatus,
            'created_at' => now(),
        ]);

        loginAsUser($post->user);

        Livewire::test('users.group-posts-by-year', [
            'year'   => now()->year,
            'userId' => $post->user_id,
            'posts'  => $post->all(),
        ])
            ->call('privateStatusToggle', $post->id)
            ->assertHasNoErrors()
            ->assertDispatched(
                'toast',
                status: 'success',
                // if the original status is true, then the message should be '文章狀態已切換為公開'
                // because the status is toggled to false
                message: $privateStatus ? '文章狀態已切換為公開' : '文章狀態已切換為私人',
            );

        $post->refresh();

        expect($post->is_private)->toBe(! $privateStatus);
    })->with([true, false]);

    it('can get auto save key property', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->assertSet('autoSaveKey', 'auto_save_user_'.$post->user_id.'_edit_post_'.$post->id);
    });

    it('can auto save the editing post to cache', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $autoSaveKey = 'auto_save_user_'.$post->user_id.'_edit_post_'.$post->id;

        if (Cache::has($autoSaveKey)) {
            Cache::pull($autoSaveKey);
        }

        expect(Cache::has($autoSaveKey))->toBeFalse();

        $newTitle = str()->random(4);
        $newCategoryId = $post->category_id;
        $newTags = Tag::factory()->count(3)->create()
            ->map(fn ($tag) => ['id' => $tag->id, 'value' => $tag->name])
            ->toJson(JSON_UNESCAPED_UNICODE);
        $newBody = str()->random(500);

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->set('form.title', $newTitle)
            ->set('form.category_id', $newCategoryId)
            ->set('form.tags', $newTags)
            ->set('form.body', $newBody);

        expect(Cache::has($autoSaveKey))
            ->toBeTrue()
            ->and(json_decode(Cache::get($autoSaveKey), true))
            ->toBe([
                'category_id' => $newCategoryId,
                'is_private'  => $post->is_private,
                'preview_url' => $post->preview_url,
                'title'       => $newTitle,
                'tags'        => $newTags,
                'body'        => $newBody,
            ]);
    });

    it('can restore auto saved data when editing post', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $autoSaveKey = 'auto_save_user_'.$post->user_id.'_edit_post_'.$post->id;

        $cachedTitle = str()->random(10);
        $cachedBody = str()->random(600);

        Cache::put(
            $autoSaveKey,
            json_encode([
                'category_id' => $post->category_id,
                'is_private'  => true,
                'preview_url' => null,
                'title'       => $cachedTitle,
                'tags'        => '',
                'body'        => $cachedBody,
            ], JSON_UNESCAPED_UNICODE),
            now()->addMonth()
        );

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->assertSet('form.title', $cachedTitle)
            ->assertSet('form.body', $cachedBody)
            ->assertSet('form.is_private', true);
    });

    it('shows hasAutoSave as true when auto save data exists', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $autoSaveKey = 'auto_save_user_'.$post->user_id.'_edit_post_'.$post->id;

        Cache::put(
            $autoSaveKey,
            json_encode([
                'category_id' => $post->category_id,
                'is_private'  => $post->is_private,
                'preview_url' => $post->preview_url,
                'title'       => str()->random(10),
                'tags'        => '',
                'body'        => str()->random(500),
            ], JSON_UNESCAPED_UNICODE),
            now()->addMonth()
        );

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->assertSet('hasAutoSave', true);
    });

    it('shows hasAutoSave as false when no auto save data exists', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->assertSet('hasAutoSave', false);
    });

    it('can restore from database and clears auto save cache', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $autoSaveKey = 'auto_save_user_'.$post->user_id.'_edit_post_'.$post->id;

        Cache::put(
            $autoSaveKey,
            json_encode([
                'category_id' => $post->category_id,
                'is_private'  => $post->is_private,
                'preview_url' => $post->preview_url,
                'title'       => str()->random(10),
                'tags'        => '',
                'body'        => str()->random(500),
            ], JSON_UNESCAPED_UNICODE),
            now()->addMonth()
        );

        expect(Cache::has($autoSaveKey))->toBeTrue();

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->call('restoreFromDatabase')
            ->assertRedirect(route('posts.edit', ['id' => $post->id]));

        expect(Cache::has($autoSaveKey))->toBeFalse();
    });

    it('clears auto save cache after successfully updating post', function () {
        $post = Post::factory()->create();

        loginAsUser($post->user);

        $autoSaveKey = 'auto_save_user_'.$post->user_id.'_edit_post_'.$post->id;

        Cache::put(
            $autoSaveKey,
            json_encode([
                'category_id' => $post->category_id,
                'is_private'  => $post->is_private,
                'preview_url' => $post->preview_url,
                'title'       => $post->title,
                'tags'        => '',
                'body'        => $post->body,
            ], JSON_UNESCAPED_UNICODE),
            now()->addMonth()
        );

        expect(Cache::has($autoSaveKey))->toBeTrue();

        Livewire::test('pages::posts.edit', ['id' => $post->id])
            ->set('form.title', str()->random(4))
            ->set('form.body', str()->random(500))
            ->call('save', post: $post)
            ->assertHasNoErrors();

        expect(Cache::has($autoSaveKey))->toBeFalse();
    });

    test('toggle private status won\'t touch timestamp', function ($privateStatus) {
        $post = Post::factory()->create([
            'is_private' => $privateStatus,
            'created_at' => now(),
        ]);

        $oldUpdatedAt = $post->updated_at;

        loginAsUser($post->user);

        Livewire::test('users.group-posts-by-year', [
            'year'   => now()->year,
            'userId' => $post->user_id,
            'posts'  => $post->all(),
        ])
            ->call('privateStatusToggle', $post->id);

        $post->refresh();

        $newUpdatedAt = $post->updated_at;

        expect($oldUpdatedAt)->toEqual($newUpdatedAt);
    })->with([true, false]);
});
