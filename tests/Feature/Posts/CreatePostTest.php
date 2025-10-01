<?php

use App\Livewire\Pages\Posts\CreatePage as PostsCreatePage;
use App\Livewire\Shared\Posts\UploadPreviewImagePart;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\ContentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

// covers(CreatePostPage::class);

describe('create post', function () {
    test('guest cannot visit create post page', function () {
        get(route('posts.create'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    });

    test('authenticated user can visit create post page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('posts.create'))
            ->assertSuccessful();
    });

    test('authenticated user can create post', function ($categoryId) {
        $user = loginAsUser();

        $title = str()->random(4);
        $body = str()->random(500);
        $privateStatus = (bool) rand(0, 1);

        $tagCollection = Tag::factory()->count(3)->create();

        $tagsJson = $tagCollection
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->toJson();

        $contentService = app(ContentService::class);

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->set('form.title', $title)
            ->set('form.category_id', $categoryId)
            ->set('form.tags', $tagsJson)
            ->set('form.body', $body)
            ->set('form.is_private', $privateStatus)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('posts.show', [
                'id'   => 1,
                'slug' => $contentService->makeSlug($title),
            ]))
            ->assertDispatched('toast', status: 'success', message: '成功新增文章！');

        $post = Post::latest()->first();

        $tagIdsArray = $tagCollection
            ->map(fn ($item) => $item->id)
            ->all();

        expect(Cache::has('auto_save_user_'.$user->id.'_create_post'))->toBeFalse()
            ->and($post)
            ->title->toBe($title)
            ->body->toBe($body)
            ->category_id->toBe($categoryId)
            ->user_id->toBe($user->id)
            ->slug->toBe($contentService->makeSlug($title))
            ->excerpt->toBe($contentService->makeExcerpt($body))
            ->is_private->toBe($privateStatus)
            ->and($post->tags->pluck('id')->toArray())->toBe($tagIdsArray);
    })->with('defaultCategoryIds');

    test('title at least 4 characters', function () {
        loginAsUser();

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->set('form.title', str()->random(3))
            ->set('form.category_id', Category::pluck('id')->random())
            ->set('form.body', str()->random(500))
            ->call('save')
            ->assertHasErrors(['title' => 'min:4']);
    });

    test('title at most 50 characters', function () {
        loginAsUser();

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->set('form.title', str()->random(51))
            ->set('form.category_id', Category::pluck('id')->random())
            ->set('form.body', str()->random(500))
            ->call('save')
            ->assertHasErrors(['title' => 'max:100']);
    });

    test('body at least 500 characters', function () {
        loginAsUser();

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->set('form.title', str()->random(4))
            ->set('form.category_id', Category::pluck('id')->random())
            ->set('form.body', str()->random(499))
            ->call('save')
            ->assertHasErrors(['body' => 'min:500']);
    });

    test('body at most 20000 characters', function () {
        loginAsUser();

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->set('form.title', str()->random(4))
            ->set('form.category_id', Category::pluck('id')->random())
            ->set('form.body', str()->random(20001))
            ->call('save')
            ->assertHasErrors(['body' => 'max:20000']);
    });

    it('can check image type', function () {
        $file = UploadedFile::fake()->create('document.pdf', 512);

        livewire(UploadPreviewImagePart::class)
            ->set('image', $file)
            ->assertHasErrors('image');
    });

    it('can check image size', function () {
        $file = UploadedFile::fake()->image('image.jpg')->size(1025);

        livewire(UploadPreviewImagePart::class)
            ->set('image', $file)
            ->assertHasErrors('image');
    });

    it('can upload image', function () {
        Storage::fake();

        $image = UploadedFile::fake()->image('fake_image.jpg');

        livewire(UploadPreviewImagePart::class)
            ->set('image', $image)
            ->assertHasNoErrors()
            ->assertSeeHtml('id="image-url"');

        expect(Storage::disk()->allFiles())->not->toBeEmpty();
    });

    it('can\'t upload non image', function () {
        Storage::fake();

        $file = UploadedFile::fake()->create('document.pdf', 512);

        livewire(UploadPreviewImagePart::class)
            ->set('image', $file)
            ->assertHasErrors('image')
            ->assertDontSeeHtml('id="upload-image"');
    });

    it('can get auto save key property', function () {
        $user = loginAsUser();

        $this->actingAs($user);

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])->assertSet('autoSaveKey', 'auto_save_user_'.$user->id.'_create_post');
    });

    it('can auto save the post to cache', function () {
        $user = loginAsUser();

        $autoSaveKey = 'auto_save_user_'.$user->id.'_create_post';

        // clean the redis data, like refresh database
        if (Cache::has($autoSaveKey)) {
            Cache::pull($autoSaveKey);
        }

        expect(Cache::has($autoSaveKey))->toBeFalse();

        $title = str()->random(4);
        $categoryId = Category::pluck('id')->random();
        $tags = Tag::inRandomOrder()
            ->limit(5)
            ->get()
            ->map(fn ($tag) => ['id' => $tag->id, 'value' => $tag->name])
            ->toJson(JSON_UNESCAPED_UNICODE);
        $body = str()->random(500);

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->set('form.title', $title)
            ->set('form.category_id', $categoryId)
            ->set('form.tags', $tags)
            ->set('form.body', $body);

        expect(Cache::has($autoSaveKey))
            ->toBeTrue()
            ->and(json_decode(Cache::get($autoSaveKey), true))
            // it will compare the order of the arrangement
            ->toBe([
                'category_id' => $categoryId,
                'is_private'  => false, // default value
                'preview_url' => null,
                'title'       => $title,
                'tags'        => $tags,
                'body'        => $body,
            ]);
    });

    it('can get data from cache', function () {
        $user = loginAsUser();

        $autoSaveKey = 'auto_save_user_'.$user->id.'_create_post';

        // clean the redis data, like refresh database
        if (Cache::has($autoSaveKey)) {
            Cache::pull($autoSaveKey);
        }

        expect(Cache::has($autoSaveKey))->toBeFalse();

        $title = str()->random(4);
        $categoryId = Category::pluck('id')->random();
        $tags = Tag::inRandomOrder()
            ->limit(5)
            ->get()
            ->map(fn ($tag) => ['id' => $tag->id, 'value' => $tag->name])
            ->toJson(JSON_UNESCAPED_UNICODE);
        $body = str()->random(500);

        Cache::put(
            $autoSaveKey,
            json_encode([
                'category_id' => $categoryId,
                'is_private'  => false,
                'preview_url' => null,
                'title'       => $title,
                'tags'        => $tags,
                'body'        => $body,
            ], JSON_UNESCAPED_UNICODE),
            now()->addDays(7)
        );

        livewire(PostsCreatePage::class, [
            'categories' => Category::all(['id', 'name']),
        ])
            ->assertSet('form.title', $title)
            ->assertSet('form.is_private', false)
            ->assertSet('form.category_id', $categoryId)
            ->assertSet('form.tags', $tags)
            ->assertSet('form.body', $body);
    });
});
