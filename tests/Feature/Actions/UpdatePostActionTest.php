<?php

declare(strict_types=1);

use App\Actions\UpdatePostAction;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ContentService;

it('updates a post with purified body, slug, excerpt, and syncs tags without changing author', function () {
    $post = Post::factory()->create();
    $originalUserId = $post->user_id;

    $oldTags = Tag::factory()->count(2)->create();
    $post->tags()->attach($oldTags->pluck('id'));

    $newCategory = Category::factory()->create();
    $newTags = Tag::factory()->count(2)->create();

    $newTagsJson = $newTags
        ->map(fn (Tag $tag): array => ['id' => $tag->id, 'value' => $tag->name])
        ->toJson();

    $rawBody = '<p>Updated content</p><script>alert(1)</script>'.str_repeat('b', 250);
    $expectedBody = ContentService::getPurifiedBody($rawBody);
    $expectedSlug = ContentService::getSlug('Updated Post Title');
    $expectedExcerpt = ContentService::getExcerpt($expectedBody);

    $updatedPost = app(UpdatePostAction::class)->handle($post, [
        'category_id' => $newCategory->id,
        'cover_image_url' => 'https://example.com/new-cover.webp',
        'is_private' => true,
        'title' => 'Updated Post Title',
        'tags' => $newTagsJson,
        'body' => $rawBody,
    ]);

    $updatedPost->refresh();

    expect($updatedPost)
        ->id->toBe($post->id)
        ->user_id->toBe($originalUserId)
        ->category_id->toBe($newCategory->id)
        ->cover_image_url->toBe('https://example.com/new-cover.webp')
        ->is_private->toBeTrue()
        ->title->toBe('Updated Post Title')
        ->body->toBe($expectedBody)
        ->body->not->toContain('<script>')
        ->slug->toBe($expectedSlug)
        ->excerpt->toBe($expectedExcerpt)
        ->and($updatedPost->tags->pluck('id')->all())
        ->toEqualCanonicalizing($newTags->pluck('id')->all());
});

it('detaches existing tags when updating with empty tags json', function () {
    $post = Post::factory()->create();
    $tags = Tag::factory()->count(2)->create();
    $post->tags()->attach($tags->pluck('id'));

    $updatedPost = app(UpdatePostAction::class)->handle($post, [
        'category_id' => $post->category_id,
        'cover_image_url' => null,
        'is_private' => false,
        'title' => 'Cleared Tags Post',
        'tags' => '',
        'body' => '<p>Body content</p>',
    ]);

    expect($updatedPost->fresh()->tags)->toHaveCount(0);
});
