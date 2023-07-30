<?php

namespace App\Http\Livewire\Posts;

use App\Http\Traits\Livewire\PostForm;
use App\Models\Category;
use App\Models\Post;
use App\Services\ContentService;
use App\Services\FileService;
use App\Services\FormatTransferService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use PostForm;
    use AuthorizesRequests;
    use WithFileUploads;

    protected ContentService $contentService;

    protected FormatTransferService $formatTransferService;

    protected FileService $fileService;

    public Collection $categories;

    public Post $post;

    public function boot(
        ContentService $contentService,
        FormatTransferService $formatTransferService,
        FileService $fileService
    ): void {
        $this->contentService = $contentService;
        $this->formatTransferService = $formatTransferService;
        $this->fileService = $fileService;
    }

    public function mount(Post $post): void
    {
        $this->authorize('update', $post);

        $this->autoSaveKey = 'auto_save_user_'.auth()->id().'_edit_post_'.$post->id;

        $this->post = $post;
        $this->categories = Category::all(['id', 'name']);

        if (! $this->setDataFromAutoSave($this->autoSaveKey)) {
            $this->category_id = $post->category_id;
            $this->is_private = $post->is_private;
            $this->preview_url = $post->preview_url;
            $this->title = $post->title;
            $this->body = $post->body;
            $this->tags = $post->tags_json;
        }
    }

    public function update()
    {
        $this->validatePost();

        // upload image
        if ($this->image) {
            $this->preview_url = $this->fileService->uploadImageToCloud($this->image);
        }

        $this->body = $this->contentService->htmlPurifier($this->body);

        $this->post->update([
            'title' => $this->title,
            'slug' => $this->contentService->makeSlug($this->title),
            'is_private' => (bool) $this->is_private,
            'category_id' => $this->category_id,
            'body' => $this->body,
            'excerpt' => $this->contentService->makeExcerpt($this->body),
            'preview_url' => $this->preview_url,
        ]);

        $this->post->tags()->sync(
            $this->formatTransferService->tagsJsonToTagIdsArray($this->tags)
        );

        $this->clearAutoSave($this->autoSaveKey);

        return redirect()
            ->to($this->post->link_with_slug)
            ->with('alert', ['status' => 'success', 'message' => '成功更新文章！']);
    }

    public function render()
    {
        return view('livewire.posts.edit');
    }
}
