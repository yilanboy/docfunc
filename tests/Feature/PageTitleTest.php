<?php

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\get;

describe('page titles', function () {
    test('home page has the default title', function () {
        get('/')
            ->assertOk()
            ->assertSee('<title>'.e(config('app.name')).'</title>', false);
    });

    test('posts index page has the correct title', function () {
        get(route('posts.index'))
            ->assertOk()
            ->assertSee('<title>所有文章</title>', false);
    });

    test('login page has the correct title', function () {
        get(route('login'))
            ->assertOk()
            ->assertSee('<title>登入</title>', false);
    });

    test('register page has the correct title', function () {
        Setting::query()->where('key', 'allow_register')->firstOrFail()->update(['value' => true]);
        Cache::forget('setting:allow_register');

        get(route('register'))
            ->assertOk()
            ->assertSee('<title>註冊</title>', false);
    });

    test('forgot password page has the correct title', function () {
        get(route('password.request'))
            ->assertOk()
            ->assertSee('<title>忘記密碼</title>', false);
    });

    test('reset password page has the correct title', function () {
        $user = User::factory()->create();

        get(route('password.reset', ['token' => 'fake-token', 'email' => $user->email]))
            ->assertOk()
            ->assertSee('<title>重設密碼</title>', false);
    });

    test('verify email page has the correct title', function () {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('<title>驗證電子郵件</title>', false);
    });

    test('posts create page has the correct title', function () {
        loginAsUser();

        get(route('posts.create'))
            ->assertOk()
            ->assertSee('<title>新增文章</title>', false);
    });

    test('posts edit page has the correct title', function () {
        $user = loginAsUser();
        $post = Post::factory()->create(['user_id' => $user->id]);

        get(route('posts.edit', ['id' => $post->id]))
            ->assertOk()
            ->assertSee('<title>編輯文章</title>', false);
    });

    test('posts show page has the correct title', function () {
        $post = Post::factory()->create();

        get($post->link_with_slug)
            ->assertOk()
            ->assertSee('<title>'.e($post->title).'</title>', false);
    });

    test('notifications index page has the correct title', function () {
        loginAsUser();

        get(route('notifications.index'))
            ->assertOk()
            ->assertSee('<title>我的通知</title>', false);
    });

    test('settings users edit page has the correct title', function () {
        $user = loginAsUser();

        get(route('settings.users.edit', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('<title>會員中心 - 編輯個人資料</title>', false);
    });

    test('settings users destroy page has the correct title', function () {
        $user = loginAsUser();

        get(route('settings.users.destroy', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('<title>會員中心 - 刪除帳號</title>', false);
    });

    test('settings users password edit page has the correct title', function () {
        $user = loginAsUser();

        get(route('settings.users.password.edit', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('<title>會員中心 - 更改密碼</title>', false);
    });

    test('settings users passkeys edit page falls back to the default title', function () {
        $user = loginAsUser();

        get(route('settings.users.passkeys.edit', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('<title>'.e(config('app.name')).'</title>', false);
    });

    test('categories show page has the correct title', function () {
        $category = Category::find(rand(1, 3));

        get(route('categories.show', ['id' => $category->id, 'name' => $category->name]))
            ->assertOk()
            ->assertSee('<title>'.e($category->name).'</title>', false);
    });

    test('tags show page has the correct title', function () {
        $tag = Tag::all()->random();

        get(route('tags.show', ['id' => $tag->id]))
            ->assertOk()
            ->assertSee('<title>'.e($tag->name).'</title>', false);
    });

    test('users show page has the correct title', function () {
        $user = User::factory()->create();

        get(route('users.show', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('<title>'.e($user->name.' 的個人資訊').'</title>', false);
    });

    test('comments show page has the correct title for a comment with a user', function () {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        get(route('comments.show', $comment->id))
            ->assertOk()
            ->assertSee('<title>'.e($user->name.'的留言').'</title>', false);
    });

    test('comments show page has the correct title for an anonymous comment', function () {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => null]);

        get(route('comments.show', $comment->id))
            ->assertOk()
            ->assertSee('<title>訪客的留言</title>', false);
    });
});
