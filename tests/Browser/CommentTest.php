<?php

use App\Models\Post;

test('comment form can be submitted', function () {
    $user = loginAsUser();
    $post = Post::factory()->create([
        'user_id' => $user->id,
    ]);

    $page = $this->visit($post->link_with_slug);

    $message = 'Hello World! This is my first comment.';

    $page->click('新增留言')
        ->fill('create-comment-body', $message)
        ->click('#create-comment-submit-button')
        ->assertSee($message);

    $message = 'Hello World! This is my second comment.';

    $page->click('新增留言')
        ->fill('create-comment-body', $message)
        ->click('#create-comment-submit-button')
        ->assertSee($message);

    $message = 'Hello World! This is my third comment.';

    $page->click('新增留言')
        ->fill('create-comment-body', $message)
        ->click('#create-comment-submit-button')
        ->assertSee($message);
});
