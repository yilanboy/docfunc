<?php

namespace App\Livewire\Shared\Comments;

use App\Enums\CommentOrder;
use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Comments extends Component
{
    #[Locked]
    public int $postId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public int $maxLayer = 2;

    #[Locked]
    public int $commentCounts;

    #[Locked]
    public CommentOrder $order = CommentOrder::POPULAR;

    #[On('update-comments-count')]
    public function updateCommentsCount(): void
    {
        $this->commentCounts = Comment::query()
            ->where('post_id', $this->postId)
            ->count();
    }

    public function changeOrder(CommentOrder $order): void
    {
        $this->order = $order;
    }

    public function render(): View
    {
        return view('livewire.shared.comments.comments');
    }
}
