<?php

namespace App\Livewire\Forms;

use App\Models\Post;
use App\Services\ContentService;
use App\Services\FormatTransferService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Livewire\Form;

class PostForm extends Form
{
    const int BODY_MAX_CHARACTER = 20_000;

    public int $user_id;

    public int $category_id = 1;

    public ?string $preview_url = null;

    public bool $is_private = false;

    public string $title = '';

    public string $tags = '';

    public string $body = '';

    public string $slug = '';

    public string $excerpt = '';

    public function validatePost(): void
    {
        Validator::make(
            [
                'user_id' => $this->user_id,
                'title' => $this->title,
                'category_id' => $this->category_id,
                // validate body text character count
                'body' => preg_replace('/[\r\n]/u', '', strip_tags($this->body)),
            ],
            [
                'user_id' => ['required', 'numeric'],
                'title' => ['required', 'min:4', 'max:50'],
                'category_id' => ['required', 'numeric', 'exists:categories,id'],
                'body' => ['required', 'min:500', 'max:'.self::BODY_MAX_CHARACTER],
            ],
            [
                'user_id.required' => '請登入後再發布文章',
                'user_id.numeric' => '使用者資料錯誤',
                'title.required' => '請填寫標題',
                'title.min' => '標題至少 4 個字元',
                'title.max' => '標題至多 50 個字元',
                'category_id.required' => '請選擇文章分類',
                'category_id.numeric' => '分類資料錯誤',
                'category_id.exists' => '分類不存在',
                'body.required' => '請填寫文章內容',
                'body.min' => '文章內容至少 500 個字元',
                'body.max' => '文章內容字數已超過 '.self::BODY_MAX_CHARACTER.' 個字元',
            ]
        )->validate();
    }

    public function autoSave(string $key): void
    {
        Cache::put(
            $key,
            json_encode([
                'category_id' => $this->category_id,
                'is_private' => $this->is_private,
                'preview_url' => $this->preview_url,
                'title' => $this->title,
                'tags' => $this->tags,
                'body' => $this->body,
            ], JSON_UNESCAPED_UNICODE),
            now()->addDays(7)
        );
    }

    public function setDataFromAutoSave(string $key): bool
    {
        if (Cache::has($key)) {
            $autoSavePostData = json_decode(Cache::get($key), true);

            $this->category_id = $autoSavePostData['category_id'];
            $this->is_private = $autoSavePostData['is_private'];
            $this->preview_url = $autoSavePostData['preview_url'];
            $this->title = $autoSavePostData['title'];
            $this->tags = $autoSavePostData['tags'];
            $this->body = $autoSavePostData['body'];

            return true;
        }

        return false;
    }

    public function clearAutoSave(string $key): void
    {
        Cache::forget($key);
    }

    public function setSlug(): void
    {
        $this->slug = ContentService::makeSlug($this->title);
    }

    public function setBody(): void
    {
        $this->body = ContentService::htmlPurifier($this->body);
    }

    public function setExcerpt(): void
    {
        $this->excerpt = ContentService::makeExcerpt($this->body);
    }

    public function setPost(Post $post): void
    {
        $this->category_id = $post->category_id;
        $this->is_private = $post->is_private;
        $this->preview_url = $post->preview_url;
        $this->title = $post->title;
        $this->tags = $post->tags_json;
        $this->body = $post->body;
    }

    public function store(): Post
    {
        $this->validatePost();

        $this->setSlug();
        $this->setBody();
        $this->setExcerpt();

        $post = Post::create(
            $this->except('tags')
        );

        // create new tags relation with post in database
        $post->tags()->attach(
            app(FormatTransferService::class)->tagsJsonToTagIdsArray($this->tags)
        );

        return $post;
    }

    public function update(Post $post): void
    {
        $this->validatePost();

        $this->setSlug();
        $this->setBody();
        $this->setExcerpt();

        $post->update(
            $this->except('tags')
        );

        // update tags relation with post in database
        $post->tags()->sync(
            app(FormatTransferService::class)->tagsJsonToTagIdsArray($this->tags)
        );
    }
}
