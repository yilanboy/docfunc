<?php

namespace App\Livewire\Shared\Comments;

use App\Enums\CommentOrderOptions;
use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class BoardPart extends Component
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
    public CommentOrderOptions $order = CommentOrderOptions::POPULAR;

    #[On('update-comments-count')]
    public function updateCommentsCount(): void
    {
        $this->commentCounts = Comment::query()
            ->where('post_id', $this->postId)
            ->count();
    }

    public function changeOrder(CommentOrderOptions $order): void
    {
        $this->order = $order;
    }

    public function render(): View
    {
        return view('livewire.shared.comments.board-part');
    }
}
