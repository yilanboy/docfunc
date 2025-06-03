@assets
  {{-- highlight code block style --}}
  @vite('node_modules/highlight.js/styles/atom-one-dark.css')
  {{-- highlight code block --}}
  @vite('resources/ts/highlight.ts')
@endassets

@script
  <script>
    Alpine.data('commentsShowPage', () => ({
      observers: [],
      highlightCodeInCommentCard() {
        this.$root
          .querySelectorAll('pre code:not(.hljs)')
          .forEach((element) => {
            hljs.highlightElement(element);
          });
      },
      openEditCommentModal() {
        this.$dispatch('open-edit-comment-modal', {
          groupName: this.$el.dataset.commentGroupName,
          id: this.$el.dataset.commentId,
          body: this.$el.dataset.commentBody
        });
      },
      openCreateCommentModal() {
        this.$dispatch('open-create-comment-modal', {
          parentId: this.$el.dataset.commentId,
          replyTo: this.$el.dataset.commentUserName
        });
      },
      init() {
        let commentsObserver = new MutationObserver(() => {
          this.highlightCodeInCommentCard();
        });

        commentsObserver.observe(this.$root, {
          childList: true,
          subtree: true,
          attributes: true
        });

        this.observers.push(commentsObserver);

        this.highlightCodeInCommentCard();
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
<x-layouts.layout-main>
  <div
    class="container mx-auto"
    x-data="commentsShowPage"
  >
    <div class="flex items-stretch justify-center">
      <div class="flex w-full max-w-3xl flex-col items-center justify-start px-2 xl:px-0">
        <div
          class="flex w-full items-center justify-end gap-2 text-zinc-500 hover:text-zinc-600 dark:text-zinc-400 hover:dark:text-zinc-300"
        >
          <x-icons.file-earmark-richtext class="w-4" />
          <a href="{{ route('posts.show', ['id' => $comment->post->id, 'slug' => $comment->post->slug]) }}">返回文章</a>
        </div>

        <x-dashed-card class="mt-6 w-full">
          <div class="flex flex-col">
            <div class="flex items-center space-x-4 text-base">
              @if (!is_null($comment->user_id))
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

              @if ($comment->created_at !== $comment->updated_at)
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
                    data-comment-group-name="comments-show-page"
                    data-comment-id="{{ $comment->id }}"
                    data-comment-body="{{ $comment->body }}"
                    type="button"
                    x-on:click="openEditCommentModal"
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

              @if ($comment->hierarchy->level < config('comments.max_level'))
                <button
                  class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                  data-comment-id="{{ $comment->id }}"
                  data-comment-user-name="{{ is_null($comment->user_id) ? '訪客' : $comment->user->name }}"
                  type="button"
                  x-on:click="openCreateCommentModal"
                >
                  <x-icons.reply-fill class="w-4" />
                  <span class="ml-2">回覆</span>
                </button>
              @endif
            </div>
          </div>
        </x-dashed-card>

        @if ($comment->hierarchy->level < config('comments.max_level'))
          <div
            class="relative w-full pl-4 before:absolute before:bottom-0 before:left-0 before:top-6 before:w-1 before:rounded-full before:bg-emerald-400/20 before:contain-none md:pl-8 dark:before:bg-indigo-500/20"
          >
            {{-- new root comment will show here --}}
            <livewire:shared.comments.group-part
              :post-id="$comment->post->id"
              :post-user-id="$comment->post->user_id"
              :parent-id="$comment->id"
              :current-level="$comment->hierarchy->level + 1"
              :comment-group-name="$comment->id . '-new-comment-group'"
            />

            @if ($comment->children->count() > 0)
              {{-- root comment list --}}
              <livewire:shared.comments.list-part
                :post-id="$comment->post->id"
                :post-user-id="$comment->post->user_id"
                :parent-id="$comment->id"
                :current-level="$comment->hierarchy->level + 1"
                :comment-list-name="$comment->id . '-comment-list'"
              />
            @endif
          </div>
        @endif
      </div>

      @if ($comment->hierarchy->level < config('comments.max_level'))
        {{-- create comment modal --}}
        <livewire:shared.comments.create-modal-part :post-id="$comment->post->id" />
      @endif

      @auth
        {{-- edit comment modal --}}
        <livewire:shared.comments.edit-modal-part />
      @endauth
    </div>
  </div>
</x-layouts.layout-main>
