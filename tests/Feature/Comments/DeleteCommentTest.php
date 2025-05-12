<?php

use App\Livewire\Shared\Comments\GroupPart;
use App\Models\Comment;
use App\Models\User;

use function Pest\Livewire\livewire;

test('the author can delete his comment', function () {
    $comment = Comment::factory()->create();

    Livewire::actingAs(User::find($comment->user_id));

    livewire(GroupPart::class, [
        'postId' => $comment->post_id,
        'postUserId' => $comment->post->user_id,
        'maxLayer' => 2,
        'currentLayer' => 1,
        'commentGroupName' => 1,
    ])
        ->call('destroyComment', id: $comment->id)
        ->assertDispatched('update-comments-count')
        ->assertDispatched('toast',
            status: 'success',
            message: '成功刪除留言！',
        );

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('post author can delete other users comment', function () {
    $comment = Comment::factory()->create();

    Livewire::actingAs(User::find($comment->post->user_id));

    livewire(GroupPart::class, [
        'postId' => $comment->post_id,
        'postUserId' => $comment->post->user_id,
        'maxLayer' => 2,
        'currentLayer' => 1,
        'commentGroupName' => 1,
    ])
        ->call('destroyComment', id: $comment->id)
        ->assertDispatched('update-comments-count')
        ->assertDispatched('toast',
            status: 'success',
            message: '成功刪除留言！',
        );

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

it('will show alert when user want to delete the deleted comment', function () {
    $comment = Comment::factory()->create();
    $commentId = $comment->id;
    $postId = $comment->post_id;
    $postAuthorId = $comment->post->user_id;

    $comment->delete();

    livewire(GroupPart::class, [
        'postId' => $postId,
        'postUserId' => $postAuthorId,
        'maxLayer' => 2,
        'currentLayer' => 1,
        'commentGroupName' => 1,
    ])
        ->call('destroyComment', id: $commentId)
        ->assertDispatched('toast', status: 'danger', message: '該留言已被刪除！');
});
