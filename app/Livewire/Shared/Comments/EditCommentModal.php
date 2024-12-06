<?php

namespace App\Livewire\Shared\Comments;

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

    public bool $previewIsEnabled = false;

    public string $body = '';

    protected function rules(): array
    {
        return [
            'body' => ['required', 'min:5', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'body.required' => '請填寫留言內容',
            'body.min' => '留言內容至少 5 個字元',
            'body.max' => '留言內容至多 2000 個字元',
        ];
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Comment $comment, string $groupName): void
    {
        $this->authorize('update', $comment);

        $this->validate();

        $comment->update([
            'body' => $this->body,
        ]);

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
