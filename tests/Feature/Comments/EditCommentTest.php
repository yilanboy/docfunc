<?php

use App\Livewire\Shared\Comments\EditModalPart;
use App\Models\Comment;
use App\Models\User;

use function Pest\Livewire\livewire;

test('logged-in users can update their comments', function () {
    $oldBody = 'old comment';
    $commentGroupName = '1-comment-group';

    $comment = Comment::factory()->create(['body' => $oldBody]);

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);

    loginAsUser($comment->user_id);

    $body = 'new comment';

    livewire(EditModalPart::class)
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentGroupName)
        ->assertDispatched('close-edit-comment-modal')
        ->assertDispatched('update-comment-in-'.$commentGroupName);

    $this->assertDatabaseHas('comments', ['body' => $body]);
});

test('the updated message must be at least 5 characters long', function () {
    $oldBody = 'old comment';
    $commentGroupName = '1-comment-group';

    $comment = Comment::factory()->create(['body' => $oldBody]);

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);

    loginAsUser($comment->user_id);

    $body = str()->random(4);

    livewire(EditModalPart::class)
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentGroupName)
        ->assertHasErrors(['form.body' => 'min:5'])
        ->assertSeeText('留言內容至少 5 個字元');

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);
});

test('the updated message must be less than 2000 characters', function () {
    $oldBody = 'old comment';
    $commentGroupName = '1-comment-group';

    $comment = Comment::factory()->create(['body' => $oldBody]);

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);

    loginAsUser($comment->user_id);

    $body = str()->random(2001);

    livewire(EditModalPart::class)
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentGroupName)
        ->assertHasErrors(['form.body' => 'max:2000'])
        ->assertSeeText('留言內容最多 2000 個字元');

    $this->assertDatabaseHas('comments', ['body' => $oldBody]);
});

test('users can\'t update others\' comments', function () {
    $comment = Comment::factory()->create();
    $commentGroupName = '1-comment-group';

    loginAsUser();

    $body = 'new comment';

    livewire(EditModalPart::class)
        ->set('form.body', $body)
        ->call('save', $comment->id, $commentGroupName)
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

    livewire(EditModalPart::class)
        ->set('form.body', $body)
        ->set('previewIsEnabled', true)
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
