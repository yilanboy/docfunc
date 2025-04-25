<div class="sticky top-1/2 flex -translate-y-1/2 flex-col space-y-2">
  {{-- Home --}}
  <x-tooltip
    :tooltip-text="'返回首頁'"
    :tooltip-position="'right'"
  >
    <a
      class="group flex h-14 w-14 cursor-pointer items-center justify-center text-gray-500 dark:text-gray-400"
      href="{{ route('posts.index') }}"
      role="button"
      wire:navigate
    >
      <x-icons.home class="w-6 text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125" />
    </a>
  </x-tooltip>

  <!-- Facebook share button -->
  <x-tooltip
    :tooltip-text="'分享至 FB'"
    :click-text="'好耶！'"
    :tooltip-position="'right'"
  >
    <button
      class="group flex h-14 w-14 cursor-pointer items-center justify-center text-gray-500 dark:text-gray-400"
      data-sharer="facebook"
      data-hashtag="{{ config('app.name') }}"
      data-url="{{ request()->fullUrl() }}"
      type="button"
    >
      <x-icons.facebook
        class="w-6 text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125" />
    </button>
  </x-tooltip>

  <!-- x share button -->
  <x-tooltip
    :tooltip-text="'分享至 X'"
    :click-text="'稍等...'"
    :tooltip-position="'right'"
  >
    <button
      class="group flex h-14 w-14 cursor-pointer items-center justify-center text-gray-500 dark:text-gray-400"
      data-sharer="x"
      data-title="{{ $postTitle }}"
      data-hashtags="{{ config('app.name') }}"
      data-url="{{ request()->fullUrl() }}"
      type="button"
    >
      <x-icons.twitter-x
        class="w-6 text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125"
      />
    </button>
  </x-tooltip>

  <!-- Copy link button -->
  <x-tooltip
    :tooltip-text="'複製連結'"
    :click-text="'好囉！'"
    :tooltip-position="'right'"
  >
    <button
      class="group flex h-14 w-14 cursor-pointer items-center justify-center text-gray-500 dark:text-gray-400"
      data-clipboard="{{ urldecode(request()->fullUrl()) }}"
      type="button"
    >
      <x-icons.link-45deg
        class="w-6 text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125"
      />
    </button>
  </x-tooltip>

  {{-- 編輯文章 --}}
  @if (auth()->id() === $authorId)
    <div class="h-[2px] w-14 bg-gray-300 dark:bg-gray-600"></div>

    <x-tooltip
      :tooltip-text="'編輯文章'"
      :tooltip-position="'right'"
    >
      <a
        class="group flex h-14 w-14 cursor-pointer items-center justify-center text-gray-500 dark:text-gray-400"
        href="{{ route('posts.edit', ['id' => $postId]) }}"
        role="button"
        wire:navigate
      >
        <x-icons.pencil-square
          class="w-6 text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125"
        />
      </a>
    </x-tooltip>

    {{-- 刪除 --}}
    <x-tooltip
      :tooltip-text="'刪除文章'"
      :tooltip-position="'right'"
    >
      <button
        class="group flex h-14 w-14 cursor-pointer items-center justify-center text-gray-500 dark:text-gray-400"
        type="button"
        title="刪除文章"
        wire:confirm="你確定要刪除文章嗎？（7 天之內可以還原）"
        wire:click="destroy({{ $postId }})"
      >
        <x-icons.trash
          class="w-6 text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125" />
      </button>
    </x-tooltip>
  @endif
</div>
