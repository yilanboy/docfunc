{{-- user edit side men --}}
<x-card class="flex w-full flex-col items-center justify-center md:w-60 xl:w-80 dark:text-zinc-50">
  <div class="flex w-full flex-col space-y-1">
    <a
      class="flex items-center rounded-md p-2 hover:bg-zinc-200 dark:text-zinc-50 dark:hover:bg-zinc-700"
      href="{{ route('settings.users.edit', ['id' => auth()->id()]) }}"
      wire:current="bg-zinc-200 dark:bg-zinc-700"
      wire:navigate
    >
      <x-icons.person-lines class="w-5" />
      <span class="ml-2">編輯個人資料</span>
    </a>

    <a
      class="flex items-center rounded-md p-2 hover:bg-zinc-200 dark:text-zinc-50 dark:hover:bg-zinc-700"
      href="{{ route('settings.users.password.edit', ['id' => auth()->id()]) }}"
      wire:current="bg-zinc-200 dark:bg-zinc-700"
      wire:navigate
    >
      <x-icons.file-earmark-lock class="w-5" />
      <span class="ml-2">修改密碼</span>
    </a>

    <a
      class="flex items-center rounded-md p-2 hover:bg-zinc-200 dark:text-zinc-50 dark:hover:bg-zinc-700"
      href="{{ route('settings.users.passkeys.edit', ['id' => auth()->id()]) }}"
      wire:current="bg-zinc-200 dark:bg-zinc-700"
      wire:navigate
    >
      <x-icons.fingerprint class="w-5" />
      <span class="ml-2">密碼金鑰</span>
    </a>

    <a
      class="flex items-center rounded-md p-2 hover:bg-zinc-200 dark:text-zinc-50 dark:hover:bg-zinc-700"
      href="{{ route('settings.users.destroy', ['id' => auth()->id()]) }}"
      wire:current="bg-zinc-200 dark:bg-zinc-700"
      wire:navigate
    >
      <x-icons.person-x class="w-5" />
      <span class="ml-2">刪除帳號</span>
    </a>
  </div>
</x-card>
