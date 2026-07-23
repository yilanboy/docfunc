<?php

use App\Models\Category;
use App\Models\Post;

it('has posts', function () {
    $category = Category::factory()
        ->has(Post::factory()->count(3))
        ->create();

    expect($category->posts)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Post::class);
});

it('has link with name', function () {
    $category = Category::factory()->create();

    expect($category->link_with_name)
        ->toBe(route('categories.show', [
            'id' => $category->id,
            'name' => $category->name,
        ]));
})->group('has link with name');

it('marks the seeded categories as default', function () {
    expect(Category::whereIn('id', [1, 2, 3])->pluck('is_default'))
        ->each->toBeTrue();
});

it('defaults is_default to false for new categories', function () {
    $category = Category::factory()->create();

    expect($category->is_default)->toBeFalse();
});
