<div class="isolate mb-6 inline-flex w-full items-center justify-end rounded-md text-base text-zinc-400 xl:hidden">
  <a
    class="relative inline-flex items-center rounded-l-lg bg-zinc-50 px-4 py-2 text-zinc-400 ring-1 ring-inset ring-zinc-300 hover:bg-zinc-100 focus:z-10 dark:bg-zinc-800 dark:ring-zinc-600 dark:hover:bg-zinc-700"
    href="{{ route('posts.edit', ['id' => $postId]) }}"
  >
    <x-icons.pencil class="w-4" />
    <span class="ml-2">編輯</span>
  </a>

  <button
    class="relative -ml-px inline-flex cursor-pointer items-center rounded-r-lg bg-zinc-50 px-4 py-2 text-zinc-400 ring-1 ring-inset ring-zinc-300 hover:bg-zinc-100 focus:z-10 dark:bg-zinc-800 dark:ring-zinc-600 dark:hover:bg-zinc-700"
    type="button"
    wire:confirm="你確定要刪除文章嗎？（7 天之內可以還原）"
    wire:click="destroy({{ $postId }})"
  >
    <x-icons.trash class="w-4" />
    <span class="ml-2">刪除</span>
  </button>
</div>
