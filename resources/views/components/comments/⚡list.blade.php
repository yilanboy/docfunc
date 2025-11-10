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

new class extends Component {
    use MarkdownConverter;

    #[Locked]
    public int $postId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public ?int $parentId = null;

    #[Locked]
    public int $perPage = 10;

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
    public array $rootComments = [];

    public bool $showMoreButtonIsActive = true;

    public function mount(): void
    {
        $this->showMoreComments();
    }

    private function getComments(): array
    {
        $comments = Comment::query()
            ->select(['comments.id', 'comments.user_id', 'comments.body', 'comments.created_at', 'comments.updated_at', 'users.name as user_name', 'users.email as user_email'])
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
            ->whereNotIn('comments.id', array_keys($this->rootComments))
            ->where('comments.post_id', $this->postId)
            // When parent id is not null,
            // it means this comment list is children of another comment.
            ->where('comments.parent_id', $this->parentId)
            // Plus-one is needed here because we need to determine whether there is a next page.
            ->limit($this->perPage + 1)
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

    private function updateShowMoreButtonStatus(array $comments): void
    {
        if (count($comments) <= $this->perPage) {
            $this->showMoreButtonIsActive = false;
        }
    }

    public function showMoreComments(): void
    {
        $comments = $this->getComments();

        $this->updateShowMoreButtonStatus($comments);

        $comments = array_slice($comments, 0, $this->perPage, true);

        $this->rootComments = $this->rootComments + $comments;
    }
};
?>

{{-- 留言列表 --}}
<div class="w-full">
  @foreach ($rootComments as $comment)
    <x-dashed-card
      class="mt-6"
      wire:key="comment-card-{{ $comment['id'] }}-{{ $comment['updated_at'] }}"
    >
      <div class="flex flex-col">
        <div class="flex items-center space-x-4 text-base">
          @if (!is_null($comment['user_id']))
            <a
              href="{{ route('users.show', ['id' => $comment['user_id']]) }}"
              wire:navigate
            >
              <img
                class="size-10 rounded-full hover:ring-2 hover:ring-blue-400"
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
            class="hidden text-zinc-400 md:block"
            datetime="{{ date('d-m-Y', strtotime($comment['created_at'])) }}"
          >{{ date('Y 年 m 月 d 日', strtotime($comment['created_at'])) }}</time>

          @if ($comment['created_at'] !== $comment['updated_at'])
            <span class="text-zinc-400">(已編輯)</span>
          @endif
        </div>

        <div class="rich-text">
          {!! $this->convertToHtml($comment['body']) !!}
        </div>

        <div class="flex items-center justify-end gap-6 text-base text-zinc-400">
          @auth
            @if (auth()->id() === $comment['user_id'])
              <button
                class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                data-comment-id="{{ $comment['id'] }}"
                data-comment-body="{{ $comment['body'] }}"
                type="button"
                {{-- TODO: edit comment --}}
                x-on:click="openEditCommentModal"
              >
                <x-icons.pencil class="w-4" />
                <span class="ml-2">編輯</span>
              </button>
            @endif

            @if (in_array(auth()->id(), [$comment['user_id'], $postUserId]))
              <button
                class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                type="button"
                {{-- TODO: destroy comment --}}
                wire:click="destroyComment({{ $comment['id'] }})"
                wire:confirm="你確定要刪除該留言？"
              >
                <x-icons.trash class="w-4" />
                <span class="ml-2">刪除</span>
              </button>
            @endif
          @endauth

          <button
            class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
            data-comment-id="{{ $comment['id'] }}"
            data-comment-user-name="{{ is_null($comment['user_name']) ? '訪客' : $comment['user_name'] }}"
            type="button"
            {{-- TODO: create comment --}}
            x-on:click="openCreateCommentModal"
          >
            <x-icons.reply-fill class="w-4" />
            <span class="ml-2">回覆</span>
          </button>
        </div>
      </div>
    </x-dashed-card>
  @endforeach

  @if ($showMoreButtonIsActive)
    <div class="mt-6 flex w-full items-center justify-center">
      <span
        class="flex gap-2 text-sm text-emerald-600 dark:text-zinc-50"
        type="button"
        wire:intersect="showMoreComments"
      >
        <x-icons.animate-spin class="size-5" />
        <span>顯示更多留言</span>
      </span>
    </div>
  @endif
</div>
