<?php

use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use MarkdownConverter;

    public const int PER_PAGE = 10;

    #[Locked]
    public int $parentId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public int $childrenCount;

    /**
     * The array of comments list, the format is below:
     *
     * @var array<int, array{
     *     'id': int,
     *     'user_id': int|null,
     *     'body': string,
     *     'created_at': string,
     *     'updated_at': string,
     *     'user_name': string|null,
     *     'user_gravatar_url': string|null,
     * }>
     */
    public array $comments = [];

    public array $loadMoreButton = [
        'is_active' => false,
        'label' => '',
    ];

    public function mount(): void
    {
        if ($this->childrenCount > 0) {
            $this->loadMoreButton['is_active'] = true;
            $this->loadMoreButton['label'] = $this->childrenCount . ' 則回覆';
        }
    }

    private function getComments(): array
    {
        $comments = Comment::query()
            ->select(['comments.id', 'comments.user_id', 'comments.body', 'comments.created_at', 'comments.updated_at', 'users.name as user_name', 'users.email as user_email'])
            // this line must be after the select method
            ->join('users', 'comments.user_id', '=', 'users.id', 'left')
            // Don't show new comments, avoid showing duplicate comments,
            // New comments have already showed in a new comment group.
            ->whereNotIn('comments.id', array_keys($this->comments))
            ->where('comments.parent_id', $this->parentId)
            // Plus-one is needed here because we need to determine whether there is a next page.
            ->limit(self::PER_PAGE + 1)
            ->orderBy('comments.created_at')
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

    public function loadMoreChildren(): void
    {
        $comments = $this->getComments();

        if (count($comments) <= self::PER_PAGE) {
            $this->loadMoreButton['is_active'] = false;
        }

        $this->loadMoreButton['label'] = '顯示更多回覆';

        $comments = array_slice($comments, 0, self::PER_PAGE, true);

        $this->comments += $comments;
    }

    #[On('create-comment-in-comment-{parentId}-children-list')]
    public function createComment(array $comment): void
    {
        $this->comments = [$comment['id'] => $comment] + $this->comments;
    }

    #[On('update-comment-in-comment-{parentId}-children-list')]
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

<div
  class="relative pl-4 before:absolute before:bottom-0 before:left-0 before:top-0 before:w-1 before:rounded-full before:bg-emerald-400/20 before:contain-none md:pl-8 dark:before:bg-indigo-500/20"
  data-test-id="comments.children-list"
>
  @foreach ($comments as $comment)
    <x-dashed-card
      class="mt-6"
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
                data-test-id="comments.card.edit"
                type="button"
                x-on:click="$dispatch('open-edit-comment-modal', {
                  listName: @js('comment-' . $parentId . '-children-list'),
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
                class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
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
        </div>
      </div>
    </x-dashed-card>
  @endforeach

  <button
    class="dark:hover:bg-zinc mt-6 flex cursor-pointer items-center gap-2 rounded-full px-4 py-2 text-base hover:bg-zinc-300/80 dark:text-zinc-50 dark:hover:bg-zinc-800"
    data-test-id="comments.children.load-more"
    wire:show="loadMoreButton['is_active']"
    wire:click="loadMoreChildren"
  >
    <span wire:text="loadMoreButton['label']"></span>

    <x-icons.caret-down-fill class="in-data-loading:hidden size-4" />
    <x-icons.animate-spin class="in-data-loading:inline-block hidden size-5" />
  </button>
</div>
