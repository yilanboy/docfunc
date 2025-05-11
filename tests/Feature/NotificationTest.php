<?php

use App\Livewire\Pages\Notifications\IndexPage as NotificationsIndexPage;
use App\Livewire\Shared\Comments\CreateCommentModal;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewComment;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

test('you will receive a notification when there is a comment on your post', function () {
    Notification::fake();

    $post = Post::factory()->create();

    $user = User::factory()->create();

    $this->actingAs($user);

    livewire(CreateCommentModal::class, ['postId' => $post->id])
        ->set('form.body', fake()->realText(100))
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save');

    $author = User::find($post->user_id);

    Notification::assertSentTo(
        [$author], NewComment::class
    );
});

test('you will see a red ping animation on notification icon when there is a comment on your post', function () {
    config()->set('queue.default', 'sync');

    $post = Post::factory()->create();

    $user = User::factory()->create();

    $this->actingAs($user);

    livewire(CreateCommentModal::class, ['postId' => $post->id])
        ->set('form.body', fake()->realText(100))
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save');

    $author = User::find($post->user_id);

    expect($author->unreadNotifications->count())
        ->toBe(1);

    $this->actingAs($author);

    // assert user can see notification button has a red ping animation
    get(route('root'))
        ->assertDontSee(<<<'HTML'
            <span class="absolute flex w-3 h-3 -mt-1 -mr-1 top-2 right-2">
                <span class="absolute inline-flex w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                <span class="relative inline-flex w-3 h-3 bg-red-500 rounded-full"></span>
            </span>
        HTML, false);

    get(route('notifications.index'))->assertDontSeeText('沒有消息通知！');
});

test('if you reply to your own post, there will be no notification', function () {
    Notification::fake();

    $post = Post::factory()->create();

    $author = User::find($post->user_id);

    $this->actingAs($author);

    livewire(CreateCommentModal::class, ['postId' => $post->id])
        ->set('form.body', fake()->realText(100))
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save');

    Notification::assertNothingSent();
});

test('you can clear unread notifications if you visit the notification page', function () {
    config()->set('queue.default', 'sync');

    $post = Post::factory()->create();

    $user = User::factory()->create();

    $this->actingAs($user);

    livewire(CreateCommentModal::class, ['postId' => $post->id])
        ->set('form.body', fake()->realText(100))
        ->set('captchaToken', 'fake-captcha-response')
        ->call('save');

    $author = User::find($post->user_id);

    $this->actingAs($author);

    livewire(NotificationsIndexPage::class);

    $author->refresh();

    expect($author->unreadNotifications->count())
        ->toBe(0);
});
