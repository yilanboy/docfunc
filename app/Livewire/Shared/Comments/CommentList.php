<?php

namespace App\Livewire\Shared\Comments;

use App\Enums\CommentOrder;
use App\Models\Comment;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class CommentList extends Component
{
    #[Locked]
    public int $postId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public int $maxLayer = 2;

    #[Locked]
    public int $currentLayer = 1;

    #[Locked]
    public ?int $parentId = null;

    #[Locked]
    public ?array $comments = null;

    #[Locked]
    public int $perPage = 10;

    /**
     * This value will be either the root-comment-list or [comment id]-comment-list,
     * The comment list name is used as the event name to add new comment ids to $newCommentIds.
     */
    #[Locked]
    public string $commentListName = 'root-comment-list';

    #[Locked]
    public CommentOrder $order = CommentOrder::LATEST;

    /**
     * Comments list array, the format is like:
     *
     * @var array<int, array<int, array{
     *     'id': int,
     *     'user_id': int|null,
     *     'body': string,
     *     'created_at': string,
     *     'updated_at': string,
     *     'parent_id': int|null,
     *     'children_count': int,
     *     'user': array{'id': int, 'name': string, 'gravatar_url': string}|null,
     *     'children': array
     * }>>
     * >
     */
    public array $commentsList = [];

    public bool $showMoreButtonIsActive = true;

    /**
     * Recording new comments that created by user.
     *
     * @var array<int>
     */
    public array $newCommentIds = [];

    public function mount(): void
    {
        $comments = is_null($this->comments) ? $this->getComments() : $this->comments;

        $this->updateCommentsList($comments);
        $this->updateShowMoreButtonStatus($comments);
    }

    #[Renderless]
    #[On('append-new-id-to-{commentListName}')]
    public function appendNewIdToNewCommentIds(int $id): void
    {
        $this->newCommentIds[] = $id;
    }

    private function getComments(int $skip = 0): array
    {
        $comments = Comment::query()
            ->select(['id', 'user_id', 'body', 'created_at', 'updated_at', 'parent_id'])
            // Use a sub query to generate children_count column,
            // this line must be after select method
            ->withCount('children')
            ->when($this->order === CommentOrder::LATEST, function (Builder $query) {
                $query->latest('id');
            })
            ->when($this->order === CommentOrder::OLDEST, function (Builder $query) {
                $query->oldest('id');
            })
            ->when($this->order === CommentOrder::POPULAR, function (Builder $query) {
                $query->orderByDesc('children_count');
            })
            // Don't show new comments, avoid showing duplicate comments,
            // New comments have already showed in new comment group.
            ->whereNotIn('id', $this->newCommentIds)
            ->where('post_id', $this->postId)
            // When parent id is not null,
            // it means this comment list is children of another comment.
            ->where('parent_id', $this->parentId)
            ->skip($skip)
            // Plus one is needed here because we need to determine whether there is a next page.
            ->take($this->perPage + 1)
            ->with('user:id,name,email')
            ->when($this->maxLayer > $this->currentLayer, function (Builder $query) {
                $query->with([
                    'children' => function (Builder $query) {
                        $query
                            ->select(['id', 'user_id', 'body', 'created_at', 'updated_at', 'parent_id'])
                            ->with('user:id,name,email')
                            ->oldest('id')
                            ->limit($this->perPage + 1);
                    }
                ]);
            })
            ->get()
            ->keyBy('id')
            ->toArray();

        // Livewire will save data in frontend, so we need to remove sensitive data
        $callback = function (array $comment) {
            if (! is_null($comment['user'])) {
                $comment['user']['gravatar_url'] = get_gravatar($comment['user']['email']);
                unset($comment['user']['email']);
            }

            if ($this->maxLayer > $this->currentLayer) {
                // Change children key to their id
                $ids = array_column($comment['children'], 'id');
                $comment['children'] = array_combine($ids, $comment['children']);

                foreach ($comment['children'] as &$child) {
                    if (! is_null($child['user'])) {
                        $child['user']['gravatar_url'] = get_gravatar($child['user']['email']);
                        unset($child['user']['email']);
                    }
                }
            }

            return $comment;
        };

        return array_map($callback, $comments);
    }

    private function updateCommentsList(array $comments): void
    {
        if (count($comments) > 0) {
            $this->commentsList[] = array_slice($comments, 0, $this->perPage, true);
        }
    }

    private function updateShowMoreButtonStatus(array $comments): void
    {
        if (count($comments) <= $this->perPage) {
            $this->showMoreButtonIsActive = false;
        }
    }

    public function showMoreComments(int $skip = 0): void
    {
        $comments = $this->getComments($skip);

        $this->updateCommentsList($comments);
        $this->updateShowMoreButtonStatus($comments);
    }

    public function render(): View
    {
        return view('livewire.shared.comments.comment-list');
    }
}
