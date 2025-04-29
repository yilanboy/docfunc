@script
  <script>
    Alpine.data('commentList', () => ({
      listeners: [],
      currentScrollY: 0,
      showMoreComments() {
        // Calculate how many comment cards are at the bottom of this comment list.
        const skip = this.$root.querySelectorAll(
          '& > div.comment-group > div.comment-card'
        ).length;

        this.$wire.showMoreComments(skip);
      },
      init() {
        this.listeners.push(
          this.$wire.$hook('commit.prepare', () => {
            this.currentScrollY = window.scrollY;
          }),
          this.$wire.$hook('morph.updated', () => {
            // make sure scroll position will update after dom updated
            queueMicrotask(() => {
              window.scrollTo({
                top: this.currentScrollY,
                behavior: 'instant'
              });
            });
          })
        );
      },
      destroy() {
        this.listeners.forEach((listener) => {
          listener();
        });
      }
    }));
  </script>
@endscript

{{-- 留言列表 --}}
<div
  class="w-full"
  x-data="commentList"
>
  @foreach ($commentsList as $comments)
    <livewire:shared.comments.comment-group
      :post-id="$postId"
      :post-user-id="$postUserId"
      :max-layer="$maxLayer"
      :current-layer="$currentLayer"
      :parent-id="$parentId"
      :comments="$comments"
      :comment-group-name="array_key_first($comments) . '-comment-group'"
      :key="array_key_first($comments) . '-comment-group'"
    />
  @endforeach

  @if ($showMoreButtonIsActive)
    <div class="mt-6 flex w-full items-center justify-center">
      <button
        class="shadow-xs rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-600 hover:bg-emerald-100 dark:bg-gray-700 dark:text-zinc-50 dark:hover:bg-gray-600"
        type="button"
        x-on:click="showMoreComments"
      >
        <x-icons.animate-spin
          class="mr-2 size-5"
          wire:loading
        />
        <span>顯示更多留言</span>
      </button>
    </div>
  @endif
</div>
