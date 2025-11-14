<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

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

test('after the user clicks the load more button, they can see more replies', function () {
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

test('orders root comments by popular, then by latest and oldest when changed', function () {
    $user = loginAsUser();
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Create three root comments with deterministic bodies
    $c1 = Comment::factory()->create([
        'post_id'    => $post->id,
        'body'       => 'C1 - oldest',
        'created_at' => now()->subMinutes(3),
    ]);

    $c2 = Comment::factory()->create([
        'post_id'    => $post->id,
        'body'       => 'C2 - middle',
        'created_at' => now()->subMinutes(2),
    ]);

    $c3 = Comment::factory()->create([
        'post_id'    => $post->id,
        'body'       => 'C3 - newest',
        'created_at' => now()->subMinute(),
    ]);

    // Popularity: C2 has 2 replies, C1 has 1 reply, C3 has 0
    Comment::factory()->count(2)->create(['parent_id' => $c2->id, 'post_id' => $post->id]);
    Comment::factory()->count(1)->create(['parent_id' => $c1->id, 'post_id' => $post->id]);

    $page = $this->visit($post->link_with_slug);

    // Default on the board is POPULAR
    $page->assertSeeIn('div#root-comment-list > div.comment-card:nth-child(1)', $c2->body);

    // Change to Latest (由新到舊)
    $page->click('排序依據')
        ->click('由新到舊');

    $page->assertSeeIn('div#root-comment-list > div.comment-card:nth-child(1)', $c3->body);

    // Change to Oldest (由舊到新)
    $page->click('排序依據')
        ->click('由舊到新');

    $page->assertSeeIn('div#root-comment-list > div:nth-child(1)', $c3->body);
});

test('children replies load in pages and the load more button hides when finished', function () {
    $post = Post::factory()->create();

    $parent = Comment::factory()->create([
        'post_id' => $post->id,
        'body'    => 'Parent comment for pagination',
    ]);

    // Create > PER_PAGE (10) children: 15
    $bodies = collect(range(1, 15))->map(fn ($i) => "Child #{$i}");
    foreach ($bodies as $body) {
        Comment::factory()->create([
            'post_id'   => $post->id,
            'parent_id' => $parent->id,
            'body'      => $body,
        ]);
    }

    $page = $this->visit($post->link_with_slug);

    // Open children list
    $page->click('15 則回覆');

    // After first open (loads 10), the "顯示更多回覆" button should be visible
    $page->assertSee('顯示更多回覆');

    // Load remaining children
    $page->click('顯示更多回覆');

    // Button hides when no more
    $page->assertDontSee('顯示更多回覆');

    // Spot-check that both early and late children are visible
    $page->assertSee('Child #1')
        ->assertSee('Child #15');
});

test('replying to a root comment shows reply-to label and renders under that parent', function () {
    $author = loginAsUser();
    $post = Post::factory()->create(['user_id' => $author->id]);

    // Create a root comment by another user with a known name
    $targetUser = User::factory()->create(['name' => '小明']);
    Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $targetUser->id,
        'body'    => 'Root from 小明',
    ]);

    $page = $this->visit($post->link_with_slug);

    // Open reply modal via the parent's 回覆 button
    $page->click('回覆')
        ->assertSee('回覆 小明 的留言')
        ->fill('create-comment-body', 'Hi 小明，我來回覆你了')
        ->click('#create-comment-submit-button')
        ->assertSee('Hi 小明，我來回覆你了');
});

test('editing own comment updates content and shows edited flag', function () {
    $user = loginAsUser();
    $post = Post::factory()->create(['user_id' => $user->id]);

    Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'body'    => 'Original content',
    ]);

    $page = $this->visit($post->link_with_slug);

    $page->click('button.edit-comment-button')
        ->fill('edit-comment-body', 'Updated content')
        ->click('更新')
        ->assertSee('Updated content')
        ->assertSee('(已編輯)');
});
