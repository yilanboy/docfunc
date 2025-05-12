@script
  <script>
    Alpine.data('commentGroup', () => ({
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

@php
  use App\Enums\CommentOrderOptions;
@endphp

<div
  class="comment-group w-full"
  x-data="commentGroup"
>
  @foreach ($comments as $comment)
    <x-dashed-card
      class="comment-card mt-6"
      wire:key="{{ $comment['id'] }}-comment-card-{{ $comment['updated_at'] }}"
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
          {!! $this->removeHeadingInHtml($this->convertToHtml($comment['body'])) !!}
        </div>

        <div class="flex items-center justify-end gap-6 text-base text-zinc-400">
          @auth
            @if (auth()->id() === $comment['user_id'])
              <button
                class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
                data-comment-group-name="{{ $commentGroupName }}"
                data-comment-id="{{ $comment['id'] }}"
                data-comment-body="{{ $comment['body'] }}"
                type="button"
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
                wire:click="destroyComment({{ $comment['id'] }})"
                wire:confirm="你確定要刪除該留言？"
              >
                <x-icons.trash class="w-4" />
                <span class="ml-2">刪除</span>
              </button>
            @endif
          @endauth

          @if ($currentLayer < $maxLayer)
            <button
              class="flex cursor-pointer items-center hover:text-zinc-500 dark:hover:text-zinc-300"
              data-comment-id="{{ $comment['id'] }}"
              data-comment-user-name="{{ is_null($comment['user_name']) ? '訪客' : $comment['user_name'] }}"
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

    @if ($currentLayer < $maxLayer)
      <div
        class="relative pl-4 before:absolute before:left-0 before:top-0 before:h-full before:w-1 before:rounded-full before:bg-emerald-400/20 before:contain-none md:pl-8 dark:before:bg-indigo-500/20"
        wire:key="{{ $comment['id'] }}-children"
      >
        <livewire:shared.comments.group-part
          :post-id="$postId"
          :post-user-id="$postUserId"
          :max-layer="$maxLayer"
          :current-layer="$currentLayer + 1"
          :parent-id="$comment['id']"
          :comment-group-name="$comment['id'] . '-new-comment-group'"
          :key="$comment['id'] . '-new-comment-group'"
        />

        {{-- If this comment has no sub-messages,
                  do not render the next level of sub-comment list to avoid redundant SQL queries. --}}
        @if ($comment['children_count'] > 0)
          <livewire:shared.comments.list-part
            :post-id="$postId"
            :post-user-id="$postUserId"
            :max-layer="$maxLayer"
            :current-layer="$currentLayer + 1"
            :parent-id="$comment['id']"
            :comment-list-name="$comment['id'] . '-comment-list'"
            :order="CommentOrderOptions::OLDEST"
            :key="$comment['id'] . '-comment-list'"
          />
        @endif
      </div>
    @endif
  @endforeach
</div>
