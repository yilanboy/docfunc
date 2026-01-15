<?php

declare(strict_types=1);

use App\Enums\CommentOrderOptions;
use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    use MarkdownConverter;

    private const int PER_PAGE = 10;

    #[Locked]
    public int $postId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public CommentOrderOptions $order = CommentOrderOptions::LATEST;

    /**
     * The array of comments list, the format is below:
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
    public array $comments = [];

    public array $loadingLabel = [
        'is_visible' => true,
    ];

    public function mount(): void
    {
        $this->loadMoreComments();
    }

    private function getComments(): array
    {
        $comments = Comment::query()
            ->select([
                'comments.id', 'comments.user_id', 'comments.body', 'comments.created_at', 'comments.updated_at',
                'users.name as user_name', 'users.email as user_email'
            ])
            // Use a sub query to generate a children_count column,
            // this line must be after the select method
            ->withCount('children')
            ->join('users', 'comments.user_id', '=', 'users.id', 'left')
            ->when($this->order === CommentOrderOptions::LATEST, function (Builder $query) {
                $query->latest('comments.id');
            })
            ->when($this->order === CommentOrderOptions::OLDEST, function (Builder $query) {
                $query->oldest('comments.id');
            })
            ->when($this->order === CommentOrderOptions::POPULAR, function (Builder $query) {
                $query->orderByDesc('children_count');
            })
            // Don't show new comments, avoid showing duplicate comments,
            // New comments have already showed in a new comment group.
            ->whereNotIn('comments.id', array_keys($this->comments))
            ->where('comments.post_id', $this->postId)
            // When parent id is not null,
            // it means this comment list is children of another comment.
            ->where('comments.parent_id', null)
            // Plus-one is needed here because we need to determine whether there is a next page.
            ->limit(self::PER_PAGE + 1)
            ->get()
            ->keyBy('id')
            ->toArray();

        // Livewire will save data in the frontend, so we need to remove sensitive data
        $callback = function (array $comment): array {
            $comment['user_gravatar_url'] = is_null($comment['user_email']) ? null : get_gravatar($comment['user_email']);
            unset($comment['user_email']);

            return $comment;
        };

        return array_map($callback, $comments);
    }

    public function loadMoreComments(): void
    {
        $comments = $this->getComments();

        if (count($comments) <= self::PER_PAGE) {
            $this->loadingLabel['is_visible'] = false;
        }

        $comments = array_slice($comments, 0, self::PER_PAGE, true);

        $this->comments += $comments;
    }

    #[On('create-comment-in-root-list')]
    public function createComment(array $comment): void
    {
        $this->comments = [$comment['id'] => $comment] + $this->comments;
    }

    #[On('update-comment-in-root-list')]
    public function updateComment(int $id, string $body, string $updatedAt): void
    {
        $this->comments[$id]['body'] = $body;
        $this->comments[$id]['updated_at'] = $updatedAt;
    }

    public function destroyComment(int $id): void
    {
        $comment = Comment::find(id: $id, columns: ['id', 'user_id', 'post_id']);

        // Check the comment is not deleted
        if ($comment === null) {
            $this->dispatch(event: 'toast', status: 'danger', message: '該留言已被刪除！');

            return;
        }

        $this->authorize('destroy', $comment);

        $comment->delete();

        unset($this->comments[$id]);

        $this->dispatch(event: 'toast', status: 'success', message: '成功刪除留言！');
    }
};
?>

@script
<script>
    Alpine.data('rootCommentList', () => ({
        loadMoreComments() {
            let y = window.scrollY;

            this.$wire.loadMoreComments().then(() => {
                // make sure window.scrollTo will execute after the DOM is updated
                this.$nextTick(() => {
                    window.scrollTo({
                        top: y,
                        behavior: 'instant'
                    });
                });
            });
        }
    }));
</script>
@endscript

{{-- 留言列表 --}}
<div
    class="w-full"
    id="root-comment-list"
    data-test-id="comments.root-list"
    x-data="rootCommentList"
>
    @foreach ($comments as $comment)
        <x-dashed-card
            class="mt-6 comment-card"
            data-test-id="comments.card"
            wire:key="comment-card-{{ $comment['id'] }}-{{ $comment['updated_at'] }}"
        >
            <div class="flex flex-col">
                <div class="flex items-center space-x-4 text-base">
                    @if ($comment['user_id'] !== null)
                        <a
                            href="{{ route('users.show', ['id' => $comment['user_id']]) }}"
                            wire:navigate
                        >
                            <img
                                class="rounded-full hover:ring-2 hover:ring-blue-400 size-10"
                                src="{{ $comment['user_gravatar_url'] }}"
                                alt="{{ $comment['user_name'] }}"
                            >
                        </a>

                        <span class="dark:text-zinc-50">{{ $comment['user_name'] }}</span>
                    @else
                        <x-icons.question-circle-fill class="size-10 text-zinc-300 dark:text-zinc-500" />

                        <span class="dark:text-zinc-50">訪客</span>
                    @endif

                    <time
                        class="hidden md:block text-zinc-400"
                        datetime="{{ date('d-m-Y', strtotime($comment['created_at'])) }}"
                    >{{ date('Y 年 m 月 d 日', strtotime($comment['created_at'])) }}</time>

                    @if ($comment['created_at'] !== $comment['updated_at'])
                        <span class="text-zinc-400">(已編輯)</span>
                    @endif
                </div>

                <div class="rich-text">
                    {!! $this->convertToHtml($comment['body']) !!}
                </div>

                <div class="flex gap-6 justify-end items-center text-base text-zinc-400">
                    @auth
                        @if (auth()->id() === $comment['user_id'])
                            <button
                                class="flex items-center cursor-pointer dark:hover:text-zinc-300 hover:text-zinc-500"
                                data-test-id="comments.card.edit"
                                type="button"
                                x-on:click="$dispatch('open-edit-comment-modal', {
                  listName: 'root-list',
                  id: @js($comment['id']),
                  body: @js($comment['body'])
                })"
                            >
                                <x-icons.pencil class="w-4" />
                                <span class="ml-2">編輯</span>
                            </button>
                        @endif

                        @if (in_array(auth()->id(), [$comment['user_id'], $postUserId]))
                            <button
                                class="flex items-center cursor-pointer dark:hover:text-zinc-300 hover:text-zinc-500"
                                data-test-id="comments.card.delete"
                                type="button"
                                wire:click="destroyComment({{ $comment['id'] }})"
                                wire:confirm="你確定要刪除該留言？"
                            >
                                <x-icons.trash class="w-4" />
                                <span class="ml-2">刪除</span>
                            </button>
                        @endif
                    @endauth

                    <button
                        class="flex items-center cursor-pointer dark:hover:text-zinc-300 hover:text-zinc-500"
                        data-test-id="comments.card.reply"
                        type="button"
                        x-on:click="$dispatch('open-create-comment-modal', {
              parentId: @js($comment['id']),
              replyTo: @js($comment['user_name'] === null ? '訪客' : $comment['user_name'])
            })"
                    >
                        <x-icons.reply-fill class="w-4" />
                        <span class="ml-2">回覆</span>
                    </button>
                </div>
            </div>
        </x-dashed-card>

        <livewire:comments.children-list
            :parent-id="$comment['id']"
            :post-user-id="$postUserId"
            :children-count="$comment['children_count']"
            :key="$comment['id'] . '-comment-children'"
        />
    @endforeach

    <div
        class="flex justify-center items-center mt-6 w-full"
        wire:show="loadingLabel['is_visible']"
    >
    <span
        class="flex gap-2 text-sm text-emerald-600 dark:text-zinc-50"
        type="button"
        x-intersect="loadMoreComments"
    >
      <x-icons.animate-spin class="size-5" />
      <span>顯示更多留言</span>
    </span>
    </div>
</div>
