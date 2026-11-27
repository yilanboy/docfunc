<?php

declare(strict_types=1);

use App\Actions\CreateCommentAction;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewComment;
use Illuminate\Support\Facades\Notification;

it('creates a comment and notifies the post author when commenter is different from author', function () {
    Notification::fake();

    $post = Post::factory()->create();
    $commenter = User::factory()->create();

    $comment = app(CreateCommentAction::class)->handle($post, [
        'user_id' => $commenter->id,
        'parent_id' => null,
        'body' => 'Great article!',
    ]);

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->post_id->toBe($post->id)
        ->user_id->toBe($commenter->id)
        ->parent_id->toBeNull()
        ->body->toBe('Great article!');

    Notification::assertSentTo($post->user, NewComment::class);
});

it('does not notify the post author when commenting on their own post', function () {
    Notification::fake();

    $post = Post::factory()->create();

    $comment = app(CreateCommentAction::class)->handle($post, [
        'user_id' => $post->user_id,
        'parent_id' => null,
        'body' => 'Author self reply',
    ]);

    expect($comment->user_id)->toBe($post->user_id);

    Notification::assertNothingSent();
});

it('notifies the post author when a guest leaves a comment', function () {
    Notification::fake();

    $post = Post::factory()->create();
    $parentComment = Comment::factory()->create(['post_id' => $post->id]);

    $comment = app(CreateCommentAction::class)->handle($post, [
        'user_id' => null,
        'parent_id' => $parentComment->id,
        'body' => 'Guest reply content',
    ]);

    expect($comment)
        ->user_id->toBeNull()
        ->parent_id->toBe($parentComment->id)
        ->body->toBe('Guest reply content');

    Notification::assertSentTo($post->user, NewComment::class);
});
