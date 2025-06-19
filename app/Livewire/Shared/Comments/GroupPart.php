<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Comments;

use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class GroupPart extends Component
{
    use MarkdownConverter;

    #[Locked]
    public int $postId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public int $currentLevel = 1;

    #[Locked]
    public ?int $parentId = null;

    /**
     * Use this comment group name as a dynamic event name.
     * Other components can use this name to refresh specific comment group.
     *
     * There are three types of comment group names:
     *
     * - 'root-new-comment-group' for the new comment with no parent id (top layer).
     * - '[command id]-new-comment-group' for new comment with parent id (second layer or more).
     * - '[commend id]-comment-group' for normal comment group.
     */
    #[Locked]
    public string $commentGroupName;

    /**
     *  Comments array, the format is like:
     *
     * @var array<int, array{
     *     'id': int,
     *     'user_id': int|null,
     *     'body': string,
     *     'created_at': string,
     *     'updated_at': string,
     *     'children_count': int,
     *     'user_name': string|null,
     *     'user_gravatar_url': string|null,
     * }>
     */
    #[Locked]
    public array $comments = [];

    #[On('create-new-comment-to-{commentGroupName}')]
    public function createComment(array $comment): void
    {
        $this->comments = [$comment['id'] => $comment] + $this->comments;
    }

    #[On('update-comment-in-{commentGroupName}')]
    public function updateComment(int $id, string $body, string $updatedAt): void
    {
        $this->comments[$id]['body'] = $body;
        $this->comments[$id]['updated_at'] = $updatedAt;
    }

    public function destroyComment(int $id): void
    {
        $comment = Comment::find(id: $id, columns: ['id', 'user_id', 'post_id']);

        // Check comment is not deleted
        if (is_null($comment)) {
            $this->dispatch(event: 'toast', status: 'danger', message: '該留言已被刪除！');

            return;
        }

        $this->authorize('destroy', $comment);

        $comment->delete();

        unset($this->comments[$id]);

        $this->dispatch(event: 'update-comments-count');

        $this->dispatch(event: 'toast', status: 'success', message: '成功刪除留言！');
    }

    public function render(): View
    {
        return view('livewire.shared.comments.group-part');
    }
}
