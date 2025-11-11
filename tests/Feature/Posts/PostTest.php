<?php

use App\Enums\PostOrderOptions;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\FormatTransferService;
use Livewire\Livewire;

use function Pest\Faker\fake;
use function Pest\Laravel\get;

// covers(Posts::class);

describe('home page', function () {
    test('user can access the home page ', function () {
        get('/')->assertStatus(200);
    });

    test('posts index can be rendered', function () {
        $user = loginAsUser();

        Post::factory(10)->create([
            'category_id' => rand(1, 3),
            'user_id'     => $user->id,
        ]);

        get(route('posts.index'))
            ->assertStatus(200)
            ->assertSeeLivewire('posts.list');
    });

    it('will be redirected if the slug is not in the URL', function () {
        $post = Post::factory()->create([
            'slug' => fake()->word(),
        ]);

        get(route('posts.show', ['id' => $post->id]))
            ->assertRedirect();
    });

    test('category can filter posts', function () {
        Post::factory()->create([
            'title'       => 'this post belongs to category one',
            'category_id' => 1,
        ]);

        Livewire::test('posts.list', [
            'categoryId' => 1,
        ])->assertViewHas('posts', function ($posts) {
            return $posts->count() === 1;
        });

        Livewire::test('posts.list', [
            'categoryId' => 2,
        ])->assertViewHas('posts', function ($posts) {
            return $posts->count() === 0;
        });
    });

    test('tag can filter posts', function () {
        $tagOnePost = Post::factory()->create();

        $tagOneJsonString = Tag::query()
            ->where('id', 1)
            ->get()
            ->map(fn($tag) => ['id' => $tag->id, 'value' => $tag->name])
            ->toJson(JSON_UNESCAPED_UNICODE);

        $tagOnePost->tags()->attach(
            app(FormatTransferService::class)->tagsJsonToTagIdsArray($tagOneJsonString)
        );

        Livewire::test('posts.list', [
            'tagId' => 1,
        ])->assertViewHas('posts', function ($posts) {
            return $posts->count() === 1;
        });

        Livewire::test('posts.list', [
            'tagId' => 2,
        ])->assertViewHas('posts', function ($posts) {
            return $posts->count() === 0;
        });
    });

    test('order query string filters correctly', function (string $queryString, string $title) {
        $user = User::factory()->create();

        Post::factory()->create([
            'title'      => 'this post is updated recently',
            'user_id'    => $user->id,
            'created_at' => now()->subDays(20),
            'updated_at' => now(),
        ]);

        Post::factory()->create([
            'title'      => 'this post is the latest',
            'user_id'    => $user->id,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(5),
        ]);

        Post::factory()
            ->has(Comment::factory()->count(5))
            ->create([
                'title'      => 'this post has the most comments',
                'user_id'    => $user->id,
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ]);

        Livewire::withQueryParams(['order' => $queryString])
            ->test('posts.list')
            ->assertViewHas('posts', function ($posts) use ($title) {
                return $posts->first()->title === $title;
            });
    })->with([
        ['latest', 'this post is the latest'],
        ['recent', 'this post is updated recently'],
        ['comment', 'this post has the most comments'],
    ]);

    test('user can change order', function (string $order, string $title) {
        $user = User::factory()->create();

        Post::factory()->create([
            'title'      => 'this post is updated recently',
            'user_id'    => $user->id,
            'created_at' => now()->subDays(20),
            'updated_at' => now(),
        ]);

        Post::factory()->create([
            'title'      => 'this post is the latest',
            'user_id'    => $user->id,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(5),
        ]);

        Post::factory()
            ->has(Comment::factory()->count(5))
            ->create([
                'title'      => 'this post has the most comments',
                'user_id'    => $user->id,
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ]);

        Livewire::test('posts.list', ['categoryId' => 0, 'tagId' => 0])
            ->call('changeOrder', $order)
            ->assertViewHas('posts', function ($posts) use ($title) {
                return $posts->first()->title === $title;
            });
    })->with([
        ['latest', 'this post is the latest'],
        ['recent', 'this post is updated recently'],
        ['comment', 'this post has the most comments'],
    ]);

    test('author can visit own private post', function () {
        $user = User::factory()->create();

        $post = Post::factory()->make([
            'user_id'    => $user->id,
            'is_private' => true,
        ]);

        $post->save();

        $this->actingAs($user)
            ->get($post->link_with_slug)
            ->assertStatus(200)
            ->assertSee($post->title)
            ->assertSee($post->body);
    });

    test('the private post don\'t show in home page', function () {
        $post = Post::factory()->make([
            'is_private' => true,
        ]);

        $post->save();

        get(route('root'))
            ->assertStatus(200)
            ->assertDontSee($post->title);

        get(route('posts.index'))
            ->assertStatus(200)
            ->assertDontSee($post->title);
    });

    test('guest can\'t visit the private post', function () {
        $post = Post::factory()->make([
            'is_private' => true,
        ]);

        $post->save();

        get($post->link_with_slug)
            ->assertStatus(403);
    });

    test('user can\' visit the owner\'s private post', function () {
        $user = User::factory()->create();

        $post = Post::factory()->make([
            'is_private' => true,
        ]);

        $post->save();

        $this->actingAs($user)
            ->get($post->link_with_slug)
            ->assertStatus(403);
    });

    it('displays the preview image', function () {
        $post = Post::factory()->create();

        get($post->link_with_slug)
            ->assertStatus(200)
            ->assertSee($post->preview_url);
    });

    it('displays the default preview, assuming the preview is not set', function () {
        $post = Post::factory()->create([
            'preview_url' => '',
        ]);

        $defaultPreviewUrl = 'https://blobs.docfunc.com/share.jpg';

        get($post->link_with_slug)
            ->assertStatus(200)
            ->assertSee($defaultPreviewUrl);
    });

    test('not showing the thumbnail on top of the post', function () {
        $post = Post::factory()->create([
            'preview_url' => '',
        ]);

        get($post->link_with_slug)
            ->assertOk()
            ->assertDontSee('post-thumbnail');
    });

    it('displays the thumbnail on top of the post', function () {
        $post = Post::factory()->create([
            'preview_url' => 'https://example.com/preview.jpg',
        ]);

        get($post->link_with_slug)
            ->assertOk()
            ->assertSee('post-thumbnail')
            ->assertSee($post->preview_url);
    });

    it('reset the page if order is changed', function () {
        Post::factory()->count(18)->create([
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        $latestPost = Post::factory()->create([
            'created_at' => now(),
            'updated_at' => now()->subDays(30),
        ]);

        $latestUpdatedPost = Post::factory()->create([
            'created_at' => now()->subDays(30),
            'updated_at' => now(),
        ]);

        Livewire::test('posts.list')
            ->set('order', PostOrderOptions::LATEST->value)
            ->assertSee($latestPost->title)
            ->assertDontSee($latestUpdatedPost->title)
            ->call('changeOrder', PostOrderOptions::RECENT->value)
            ->assertSee($latestUpdatedPost->title)
            ->assertDontSee($latestPost->title);
    });
});
