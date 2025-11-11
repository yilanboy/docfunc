<?php

use App\Models\Post;

test('user can see post outline', function () {
    $body = <<<'HTML'
    <h2>This is post-title 1</h2>
    <p>This is post-body 1</p>
    <h2>This is post-title 2</h2>
    <p>This is post-body 2</p>
    HTML;


    $post = Post::factory()->create([
        'body' => $body,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page->assertSee('目錄')
        ->assertSeeLink('This is post-title 1')
        ->assertSeeLink('This is post-title 2');
});

test('user cannot see post outline, if there is no heading', function () {
    $body = <<<'HTML'
    <p>This is a post-body</p>
    HTML;

    $post = Post::factory()->create([
        'body' => $body,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page->assertDontSee('目錄');
});
