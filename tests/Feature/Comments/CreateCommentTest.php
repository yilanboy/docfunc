<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

use function Pest\Faker\fake;
use function Pest\Laravel\get;

test('non-logged-in users can leave an anonymous comment', function () {
    $post = Post::factory()->create();

    $body = fake()->words(5, true);

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.body', $body)
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save')
        ->assertDispatched('create-comment-in-root-list')
        ->assertDispatched('toast',
            status: 'success',
            message: '成功新增留言！',
        );

    $this->assertDatabaseHas('comments', [
        'body' => $body,
    ]);

    get($post->link_with_slug)
        ->assertSee($body);
});

test('logged-in users can leave a comment', function () {
    $this->actingAs(User::factory()->create());

    $post = Post::factory()->create();

    $body = fake()->words(5, true);

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.body', $body)
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save')
        ->assertDispatched('create-comment-in-root-list')
        ->assertDispatched('toast',
            status: 'success',
            message: '成功新增留言！',
        );

    $this->assertDatabaseHas('comments', [
        'body' => $body,
    ]);

    get($post->link_with_slug)
        ->assertSee($body);
});

test('the message must be at least 5 characters long', function () {
    $post = Post::factory()->create();

    $body = str()->random(4);

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.body', $body)
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save')
        ->assertHasErrors(['form.body' => 'min:5'])
        ->assertSeeText('留言內容至少 5 個字元');
});

test('the message must be less than 2000 characters', function () {
    $post = Post::factory()->create();

    $body = str()->random(2001);

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.body', $body)
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save')
        ->assertHasErrors(['form.body' => 'max:2000'])
        ->assertSeeText('留言內容最多 2000 個字元');
});

test('the message must have the captcha token', function () {
    $post = Post::factory()->create();

    $body = fake()->words(5, true);

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.body', $body)
        ->call('save')
        ->assertHasErrors()
        ->assertSeeText('未完成驗證');
});

it('can see the comment preview', function () {
    $this->actingAs(User::factory()->create());

    $post = Post::factory()->create();

    $body = <<<'MARKDOWN'
    # Title

    This is a **comment**

    Show a list

    - item 1
    - item 2
    - item 3
    MARKDOWN;

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.body', $body)
        ->set('captchaToken', 'fake-captcha-response')
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

it('can reply to another user\'s comment', function () {
    $this->actingAs(User::factory()->create());

    $post = Post::factory()->create();
    $comment = Comment::factory()->create(['post_id' => $post->id]);

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.parent_id', $comment->id)
        ->set('form.body', 'Hello World!')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save')
        ->assertDispatched('create-comment-in-comment-'.$comment->id.'-children-list')
        ->assertDispatched('toast',
            status: 'success',
            message: '成功新增留言！',
        );
});

it('shows an alert when the user tries to reply to a deleted post', function () {
    $this->actingAs(User::factory()->create());

    $post = Post::factory()->create();
    $postId = $post->id;

    $post->delete();

    Livewire::test('comments.create-modal', ['postId' => $postId])
        ->set('form.body', 'Hello World!')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save')
        ->assertDispatched(event: 'toast', status: 'danger', message: '無法回覆！文章已被刪除！');
});

it('will show alert, when user want to reply to deleted comment', function () {
    $this->actingAs(User::factory()->create());

    $post = Post::factory()->create();
    $comment = Comment::factory()->create(['post_id' => $post->id]);
    $commentId = $comment->id;

    $comment->delete();

    Livewire::test('comments.create-modal', ['postId' => $post->id])
        ->set('form.parent_id', $comment->id)
        ->set('form.body', 'Hello World!')
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save', parentId: $commentId)
        ->assertDispatched(event: 'toast', status: 'danger', message: '無法回覆！留言已被刪除！');
});
