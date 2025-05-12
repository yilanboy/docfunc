<?php

namespace App\Livewire\Shared\Users;

use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CommentsPart extends Component
{
    use MarkdownConverter;
    use WithPagination;

    #[Locked]
    public int $userId;

    public function render(): View
    {
        // get the comments from this user
        $comments = Comment::whereUserId($this->userId)
            ->select(['id', 'created_at', 'post_id', 'body'])
            ->whereHas('post', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with('post:id,title,slug')
            ->latest()
            ->paginate(10, ['*'], 'comments-page')
            ->withQueryString();

        // convert the body from markdown to html
        $comments->getCollection()->transform(function ($comment) {
            $comment->body = $this->convertToHtml($comment->body);

            return $comment;
        });

        return view('livewire.shared.users.comments-part', ['comments' => $comments]);
    }
}
