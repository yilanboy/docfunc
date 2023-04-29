<div
  x-data="{ editMenuIsOpen: false }"
  class="relative xl:hidden"
>
  <div>
    <button
      x-on:click="editMenuIsOpen = ! editMenuIsOpen"
      x-on:click.outside="editMenuIsOpen = false"
      x-on:keydown.escape.window="editMenuIsOpen = false"
      type="button"
      class="text-2xl text-gray-400 hover:text-gray-700 focus:text-gray-700 dark:hover:text-gray-50 dark:focus:text-gray-50"
      aria-expanded="false"
      aria-haspopup="true"
    >
      <i class="bi bi-three-dots-vertical"></i>
    </button>
  </div>

  <div
    x-cloak
    x-show="editMenuIsOpen"
    x-transition.origin.top.right
    class="absolute right-0 z-10 mt-2 w-48 rounded-md bg-gray-50 p-2 text-gray-700 shadow-lg ring-1 ring-black ring-opacity-20 dark:bg-gray-700 dark:text-gray-50 dark:ring-gray-500"
    role="menu"
    aria-orientation="vertical"
    tabindex="-1"
  >
    {{-- 編輯文章 --}}
    <a
      href="{{ route('posts.edit', ['id' => $postId]) }}"
      role="menuitem"
      tabindex="-1"
      class="block rounded-md px-4 py-2 hover:bg-gray-200 active:bg-gray-100 dark:hover:bg-gray-600"
    >
      <i class="bi bi-pencil-fill"></i><span class="ml-2">編輯</span>
    </a>

    {{-- 軟刪除 --}}
    <button
      onclick="confirm('你確定要刪除文章嗎？（7 天之內可以還原）') || event.stopImmediatePropagation()"
      wire:click="deletePost({{ $postId }})"
      type="button"
      role="menuitem"
      tabindex="-1"
      class="flex w-full items-start rounded-md px-4 py-2 hover:bg-gray-200 active:bg-gray-100 dark:hover:bg-gray-600"
    >
      <i class="bi bi-file-earmark-x-fill"></i><span class="ml-2">刪除標記</span>
    </button>
  </div>
</div>
