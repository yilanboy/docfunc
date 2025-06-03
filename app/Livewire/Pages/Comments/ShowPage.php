<?php

namespace App\Livewire\Pages\Comments;

use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowPage extends Component
{
    use MarkdownConverter;

    public Comment $comment;

    public function mount(int $id): void
    {
        $this->comment = Comment::query()->with(['user', 'post', 'children'])->find($id);
    }

    #[On('update-comment-in-comments-show-page')]
    public function updateComment(int $id, string $body, string $updatedAt): void
    {
        $this->comment->id = $id;
        $this->comment->body = $body;
        $this->comment->updated_at = $updatedAt;
    }

    public function destroyComment(int $id): void
    {
        $comment = Comment::find(id: $id, columns: ['id', 'user_id', 'post_id']);

        // Check comment is not deleted
        if (is_null($comment)) {
            $this->dispatch(event: 'toast', status: 'danger', message: '該留言已被刪除！');

            $this->redirect(url: route('root'), navigate: true);

            return;
        }

        $this->authorize('destroy', $comment);

        $comment->delete();

        $this->dispatch(event: 'update-comments-count');

        $this->dispatch(event: 'toast', status: 'success', message: '成功刪除留言！');

        $this->redirect(url: route('root'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pages.comments.show-page');
    }
}
