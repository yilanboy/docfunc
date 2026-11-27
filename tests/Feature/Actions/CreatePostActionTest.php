<?php

declare(strict_types=1);

use App\Actions\CreatePostAction;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\ContentService;

it('creates a post with purified body, slug, excerpt, and attached tags', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $tags = Tag::factory()->count(3)->create();

    $tagsJson = $tags
        ->map(fn (Tag $tag): array => ['id' => $tag->id, 'value' => $tag->name])
        ->toJson();

    $rawBody = '<p>Hello <strong>World</strong></p><script>alert("xss")</script>'.str_repeat('a', 250);
    $expectedBody = ContentService::getPurifiedBody($rawBody);
    $expectedSlug = ContentService::getSlug('My First Post!');
    $expectedExcerpt = ContentService::getExcerpt($expectedBody);

    $post = app(CreatePostAction::class)->handle([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'cover_image_url' => 'https://example.com/cover.webp',
        'is_private' => true,
        'title' => 'My First Post!',
        'tags' => $tagsJson,
        'body' => $rawBody,
    ]);

    expect($post)
        ->toBeInstanceOf(Post::class)
        ->user_id->toBe($user->id)
        ->category_id->toBe($category->id)
        ->cover_image_url->toBe('https://example.com/cover.webp')
        ->is_private->toBeTrue()
        ->title->toBe('My First Post!')
        ->body->toBe($expectedBody)
        ->body->not->toContain('<script>')
        ->slug->toBe($expectedSlug)
        ->excerpt->toBe($expectedExcerpt)
        ->and($post->tags->pluck('id')->all())
        ->toEqualCanonicalizing($tags->pluck('id')->all());
});

it('creates a post without tags when tags json is empty', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = app(CreatePostAction::class)->handle([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'cover_image_url' => null,
        'is_private' => false,
        'title' => 'Post Without Tags',
        'tags' => '',
        'body' => '<p>Plain content</p>',
    ]);

    expect($post)
        ->cover_image_url->toBeNull()
        ->is_private->toBeFalse()
        ->and($post->tags)->toHaveCount(0);
});
