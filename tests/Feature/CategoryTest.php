<?php

use App\Models\Category;

use function Pest\Laravel\get;

test('guest can visit category show page', function () {
    $category = Category::find(rand(1, 3));

    get(route('categories.show', ['id' => $category->id, 'name' => $category->name]))
        ->assertStatus(200);
});

test('visit category show page and url don\'t include slug', function () {
    $category = Category::find(rand(1, 3));

    get(route('categories.show', ['id' => $category->id]))
        ->assertRedirect(
            route('categories.show', [
                'id' => $category->id,
                'name' => $category->name,
            ])
        );
});
