<?php

namespace App\Livewire\Shared\Comments;

use App\Livewire\Forms\CommentForm;
use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class EditCommentModal extends Component
{
    use AuthorizesRequests;
    use MarkdownConverter;

    public CommentForm $form;

    public bool $previewIsEnabled = false;

    /**
     * @throws AuthorizationException
     */
    public function save(Comment $comment, string $groupName): void
    {
        $this->authorize('update', $comment);

        $this->form->update($comment);

        $this->dispatch(event: 'close-edit-comment-modal');

        $this->dispatch(
            event: 'update-comment-in-'.$groupName,
            id: $comment->id,
            body: $comment->body,
            updatedAt: $comment->updated_at,
        );
    }

    public function render(): View
    {
        return view('livewire.shared.comments.edit-comment-modal');
    }
}
