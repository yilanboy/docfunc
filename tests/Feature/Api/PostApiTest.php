<?php

use App\Models\Post;

use function Pest\Laravel\get;

test('we can get the latest posts', function () {
    Post::factory(6)->create();

    get(route('api.posts'))
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'excerpt', 'created_at', 'updated_at', 'url'],
            ],
        ])
        ->assertSuccessful();
});
