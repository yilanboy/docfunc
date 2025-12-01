<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use MarkdownConverter;

    public Comment $comment;

    public function mount(int $id): void
    {
        $this->comment = Comment::query()
            ->with(['user', 'post', 'children'])
            ->findOr($id, fn() => abort(404));
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

        // Check a comment is not deleted
        if (is_null($comment)) {
            $this->dispatch(event: 'toast', status: 'danger', message: '該留言已被刪除！');

            $this->redirect(url: route('root'), navigate: true);

            return;
        }

        $this->authorize('destroy', $comment);

        $comment->delete();

        $this->dispatch(event: 'toast', status: 'success', message: '成功刪除留言！');

        $this->redirect(url: route('root'), navigate: true);
    }

    public function render()
    {
        $user = $this->comment->user_id ? $this->comment->user->name : '訪客';

        return $this->view()->title($user . '的留言');
    }
};
?>

@assets
  @vite('resources/ts/shiki.ts')
@endassets

@script
  <script>
    Alpine.data('commentsShowPage', () => ({
      observers: [],
      openEditCommentModal() {
        this.$dispatch('open-edit-comment-modal', {
          comment: {
            groupName: this.$el.dataset.commentGroupName,
            id: this.$el.dataset.commentId,
            body: this.$el.dataset.commentBody
          }
        });
      },
      openCreateCommentModal() {
        this.$dispatch('open-create-comment-modal', {
          parentId: this.$el.dataset.commentId,
          replyTo: this.$el.dataset.commentUserName
        });
      },
      async init() {
        await highlightAllInElement(this.$root);

        let commentsObserver = await highlightObserver(this.$root)
        this.observers.push(commentsObserver);
      },
      destroy() {
        this.observers.forEach((observer) => {
          observer.disconnect();
        });
      }
    }));
  </script>
@endscript

{{-- 文章列表 --}}
<x-layouts.main>
  <div
    class="container mx-auto grow"
    x-data="commentsShowPage"
  >
    <div class="flex items-stretch justify-center">
      <div class="flex w-full max-w-3xl flex-col items-center justify-start px-2 xl:px-0">
        <div class="flex w-full items-center justify-end text-zinc-500 md:justify-between dark:text-zinc-400">
          <span class="hidden md:inline">「{{ $comment->post->title }}」的留言</span>

          <div class="flex gap-2 hover:text-zinc-600 hover:dark:text-zinc-300">
            <x-icons.file-earmark-richtext class="w-4" />
            <a href="{{ route('posts.show', ['id' => $comment->post->id, 'slug' => $comment->post->slug]) }}">返回文章</a>
          </div>
        </div>

        <x-dashed-card class="mt-6 w-full">
          <div class="flex flex-col">
            <div class="flex items-center space-x-4 text-base">
              @if ($comment->user_id !== null)
                <a
                  href="{{ route('users.show', ['id' => $comment->user_id]) }}"
                  wire:navigate
                >
                  <img
                    class="size-10 rounded-full hover:ring-2 hover:ring-blue-400"
                    src="{{ $comment->user->gravatar_url }}"
                    alt="{{ $comment->user->name }}"
                  >
                </a>

                <span class="dark:text-zinc-50">{{ $comment->user->name }}</span>
              @else
                <x-icons.question-circle-fill class="size-10 text-zinc-300 dark:text-zinc-500" />

                <span class="dark:text-zinc-50">訪客</span>
              @endif

              <time
                class="hidden text-zinc-400 md:block"
                datetime="{{ date('d-m-Y', strtotime($comment->created_at)) }}"
              >{{ date('Y 年 m 月 d 日', strtotime($comment->created_at)) }}</time>

              @if ($comment->created_at->toString() !== $comment->updated_at->toString())
                <span class="text-zinc-400">(已編輯)</span>
              @endif
            </div>

            <div class="rich-text">
              {!! $this->convertToHtml($comment->body) !!}
            </div>

            <div class="flex items-center justify-end gap-6 text-base text-zinc-400">
              @auth
                @if (auth()->id() === $comment->user_id)
                  <button
                    class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                    type="button"
                    x-on:click="$dispatch('open-edit-comment-modal', {
                      listName: 'comments-show-page',
                      id: @js($comment['id']),
                      body: @js($comment['body'])
                    })"
                  >
                    <x-icons.pencil class="w-4" />
                    <span class="ml-2">編輯</span>
                  </button>
                @endif

                @if (in_array(auth()->id(), [$comment->user_id, $comment->post->user_id]))
                  <button
                    class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                    type="button"
                    wire:click="destroyComment({{ $comment->id }})"
                    wire:confirm="你確定要刪除該留言？"
                  >
                    <x-icons.trash class="w-4" />
                    <span class="ml-2">刪除</span>
                  </button>
                @endif
              @endauth

              @if ($comment->parent_id === null)
                <button
                  class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                  type="button"
                  x-on:click="$dispatch('open-create-comment-modal', {
                    parentId: @js($comment->id),
                    replyTo: @js($comment->user === null ? '訪客' : $comment->user->name)
                  })"
                >
                  <x-icons.reply-fill class="w-4" />
                  <span class="ml-2">回覆</span>
                </button>
              @endif
            </div>
          </div>
        </x-dashed-card>

        @if ($comment->parent_id === null)
          <div class="w-full">
            <livewire:comments.children-list
              :parent-id="$comment->id"
              :post-user-id="$comment->post->user_id"
              :children-count="$comment->children->count()"
              :key="$comment->id . '-comment-children'"
            />
          </div>
        @endif
      </div>

      @if ($comment->parent_id === null)
        <livewire:comments.create-modal :post-id="$comment->post->id" />
      @endif

      @auth
        <livewire:comments.edit-modal />
      @endauth
    </div>
  </div>
</x-layouts.main>
