<?php

use App\Models\Comment;
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

test('if a comment has replies, you can see a show more button below it', function () {
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'post_id' => $post->id,
    ]);

    Comment::factory()->count(5)->create([
        'parent_id' => $comment->id,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page->assertSee($comment->children()->count().' 則回覆');
});

test('after clicking the show more button, if there are more replies, you can see a show more button below it', function () {
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'post_id' => $post->id,
    ]);

    Comment::factory()->count(20)->create([
        'parent_id' => $comment->id,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page
        ->click($comment->children()->count().' 則回覆')
        ->assertSee('顯示更多留言');
});

test('after the user clicks the show more button, they can see more replies', function () {
    $post = Post::factory()->create();

    $comment = Comment::factory()->create([
        'post_id' => $post->id,
    ]);

    $bodyOne = 'Hello World! This is my first comment.';
    $bodyTwo = 'Hello World! This is my second comment.';
    $bodyThree = 'Hello World! This is my third comment.';

    Comment::factory()->create([
        'body'      => $bodyOne,
        'parent_id' => $comment->id,
    ]);

    Comment::factory()->create([
        'body'      => $bodyTwo,
        'parent_id' => $comment->id,
    ]);

    Comment::factory()->create([
        'body'      => $bodyThree,
        'parent_id' => $comment->id,
    ]);

    $page = $this->visit($post->link_with_slug);

    $page
        ->click($comment->children()->count().' 則回覆')
        ->assertSee($bodyOne)
        ->assertSee($bodyTwo)
        ->assertSee($bodyThree);
});
