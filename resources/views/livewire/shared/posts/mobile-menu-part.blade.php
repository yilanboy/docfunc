<div
  class="flex items-center justify-end gap-6 rounded-md text-base text-zinc-400 xl:hidden"
>
  <a
    class="flex items-center text-green-600 hover:text-green-700 dark:text-green-500 dark:hover:text-green-600"
    href="{{ route('posts.edit', ['id' => $postId]) }}"
  >
    <x-icons.pencil class="w-4" />
    <span class="ml-2">編輯</span>
  </a>

  <button
    class="flex items-center text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-600"
    type="button"
    wire:confirm="你確定要刪除文章嗎？（7 天之內可以還原）"
    wire:click="destroy({{ $postId }})"
  >
    <x-icons.trash class="w-4" />
    <span class="ml-2">刪除</span>
  </button>
</div>
