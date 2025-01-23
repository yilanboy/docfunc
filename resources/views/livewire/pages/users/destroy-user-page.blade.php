<x-layouts.layout-main>
  <div class="container mx-auto flex-1">
    <div class="flex flex-col items-start justify-center gap-6 px-4 md:flex-row">
      <x-member-centre.side-menu />

      <x-card class="flex w-full flex-col justify-center gap-6 md:max-w-2xl">
        <div class="space-y-4">
          <h1 class="w-full text-center text-2xl dark:text-gray-50">刪除帳號</h1>
          <hr class="h-0.5 border-0 bg-gray-300 dark:bg-gray-700">
        </div>

        {{-- 說明 --}}
        <div class="flex flex-col items-start justify-center">
          <span class="dark:text-gray-50">很遺憾您要離開...</span>
          <span class="dark:text-gray-50">如果您確定要刪除帳號，請點選下方的按鈕並收取信件</span>
        </div>

        <div
          class="relative ml-4 flex items-center rounded-md border-none bg-red-300/20 px-4 py-2 text-red-500 before:absolute before:-left-4 before:top-0 before:h-full before:w-1.5 before:rounded-sm before:bg-red-500 before:contain-none dark:text-red-400 dark:before:bg-red-400"
        >
          <x-icon.exclamation-triangle class="w-5" />
          <span class="ml-2">請注意！您撰寫的文章與留言都會一起刪除，而且無法恢復！</span>
        </div>

        {{-- 寄出刪除帳號信件 --}}
        <div class="w-full">
          <button
            class="focus:outline-hidden focus:ring-3 inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 uppercase tracking-widest text-gray-50 ring-red-300 transition duration-150 ease-in-out hover:bg-red-700 focus:border-red-900 active:bg-red-900 disabled:opacity-25"
            type="button"
            wire:confirm="您確定要寄出刪除帳號信件嗎？"
            wire:click="sendDestroyEmail({{ $user->id }})"
          >
            寄出刪除帳號信件
          </button>
        </div>
      </x-card>
    </div>
  </div>
</x-layouts.layout-main>
