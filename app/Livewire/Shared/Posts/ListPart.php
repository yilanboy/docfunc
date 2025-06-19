<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Posts;

use App\Enums\PostOrderOptions;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ListPart extends Component
{
    use WithPagination;

    public ?int $categoryId = null;

    public ?int $tagId = null;

    public string $badge = '全部文章';

    #[Url]
    public string $order = PostOrderOptions::LATEST->value;

    public function changeOrder(PostOrderOptions $newOrder): void
    {
        $this->order = $newOrder->value;

        $this->resetPage();
    }

    public function render(): View
    {
        $posts = Post::query()
            ->select([
                'id',
                'category_id',
                'user_id',
                'title',
                'excerpt',
                'slug',
                'created_at',
            ])
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
            ->with(['user:id,name', 'category:id,icon,name', 'tags:id,name']) // 預加載防止 N+1 問題
            ->paginate(10)
            ->withQueryString();

        return view('livewire.shared.posts.list-part', compact('posts'));
    }
}
