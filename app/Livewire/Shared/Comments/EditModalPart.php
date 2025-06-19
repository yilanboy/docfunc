<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Comments;

use App\Livewire\Forms\CommentForm;
use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;
use Livewire\Component;

class EditModalPart extends Component
{
    use MarkdownConverter;

    public CommentForm $form;

    /**
     * @throws Exception
     */
    public function mount(): void
    {
        if (! auth()->check()) {
            throw new Exception(message: 'Edit modal part component requires authentication.');
        }
    }

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
        return view('livewire.shared.comments.edit-modal-part');
    }
}
