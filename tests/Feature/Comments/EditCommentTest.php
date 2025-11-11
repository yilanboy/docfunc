<?php

use App\Models\Comment;
use App\Models\User;


test('edit comment modal part will throw an error if not logged in', function () {
    Livewire::test('comments.edit-modal');
})->throws(Exception::class);

test('edit comment modal part can be rendered by logged in users', function () {
    loginAsUser();
    Livewire::test('comments.edit-modal');
})->throwsNoExceptions();

test('logged-in users can update their comments', function () {
    $oldBody = 'old comment';
    $commentListName = 'root-list';

    $comment = Comment::factory()->create(['body' => $oldBody]);

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);

    loginAsUser($comment->user_id);

    $body = 'new comment';

    Livewire::test('comments.edit-modal')
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentListName)
        ->assertDispatched('close-edit-comment-modal')
        ->assertDispatched('update-comment-in-'.$commentListName);

    $this->assertDatabaseHas('comments', ['body' => $body]);
});

test('the updated message must be at least 5 characters long', function () {
    $oldBody = 'old comment';
    $commentListName = 'comment-1-children-list';

    $comment = Comment::factory()->create(['body' => $oldBody]);

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);

    loginAsUser($comment->user_id);

    $body = str()->random(4);

    Livewire::test('comments.edit-modal')
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentListName)
        ->assertHasErrors(['form.body' => 'min:5'])
        ->assertSeeText('留言內容至少 5 個字元');

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);
});

test('the updated message must be less than 2000 characters', function () {
    $oldBody = 'old comment';
    $commentListName = 'comment-1-children-list';

    $comment = Comment::factory()->create(['body' => $oldBody]);

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);

    loginAsUser($comment->user_id);

    $body = str()->random(2001);

    Livewire::test('comments.edit-modal')
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentListName)
        ->assertHasErrors(['form.body' => 'max:2000'])
        ->assertSeeText('留言內容最多 2000 個字元');

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);
});

test('users can\'t update others\' comments', function () {
    $comment = Comment::factory()->create();
    $commentListName = 'comment-1-children-list';

    loginAsUser();

    $body = 'new comment';

    Livewire::test('comments.edit-modal')
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentListName)
        ->assertForbidden();

    expect(Comment::find($comment->id, ['body']))
        ->body->not->toBe($body);
});

it('can see the comment preview', function () {
    $comment = Comment::factory()->create();

    Livewire::actingAs(User::find($comment->user_id));

    $body = <<<'MARKDOWN'
    # Title

    This is a **comment**

    Show a list

    - item 1
    - item 2
    - item 3
    MARKDOWN;

    Livewire::test('comments.edit-modal')
        ->set('form.body', $body)
        ->assertSeeHtmlInOrder([
            '<p>Title</p>',
            '<p>This is a <strong>comment</strong></p>',
            '<p>Show a list</p>',
            '<ul>',
            '<li>item 1</li>',
            '<li>item 2</li>',
            '<li>item 3</li>',
            '</ul>',
        ]);
});
