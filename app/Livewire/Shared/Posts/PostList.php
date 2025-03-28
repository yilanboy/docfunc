<?php

namespace App\Livewire\Shared\Posts;

use App\Enums\PostOrder;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    public ?int $categoryId = null;

    public ?string $categoryName = null;

    public ?string $categoryDescription = null;

    public ?int $tagId = null;

    public ?string $tagName = null;

    #[Url]
    public string $order = PostOrder::LATEST->value;

    public function changeOrder(PostOrder $newOrder): void
    {
        $this->order = $newOrder->value;

        $this->resetPage();
    }

    public function render(): View
    {
        $posts = Post::query()
            ->withCount('tags') // 計算標籤數目
            ->when($this->categoryId, function ($query) {
                return $query->where('category_id', $this->categoryId);
            })
            ->when($this->tagId, function ($query) {
                return $query->whereHas('tags', function ($query) {
                    $query->where('tag_id', $this->tagId);
                });
            })
            ->where('is_private', false)
            ->withOrder($this->order)
            ->with('user', 'category', 'tags') // 預加載防止 N+1 問題
            ->paginate(10)
            ->withQueryString();

        return view('livewire.shared.posts.post-list', compact('posts'));
    }
}
