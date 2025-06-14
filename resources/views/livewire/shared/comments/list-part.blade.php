{{-- 留言列表 --}}
<div class="w-full">
  @foreach ($commentsList as $comments)
    <livewire:shared.comments.group-part
      :post-id="$postId"
      :post-user-id="$postUserId"
      :current-level="$currentLevel"
      :parent-id="$parentId"
      :comments="$comments"
      :comment-group-name="array_key_first($comments) . '-comment-group'"
      :key="array_key_first($comments) . '-comment-group'"
    />
  @endforeach

  @if ($showMoreButtonIsActive)
    <div class="mt-6 flex w-full items-center justify-center">
      <button
        class="shadow-xs cursor-pointer rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-600 hover:bg-emerald-100 dark:bg-gray-700 dark:text-zinc-50 dark:hover:bg-gray-600"
        type="button"
        wire:click="showMoreComments"
        wire:retain
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
