{{-- user edit side men --}}
<x-card class="flex flex-col justify-center items-center w-full md:w-60 xl:w-80 dark:text-zinc-50">
    <div class="flex flex-col space-y-1 w-full">
        <a
            class="flex items-center p-2 rounded-md dark:text-zinc-50 dark:hover:bg-zinc-700 hover:bg-zinc-200"
            href="{{ route('settings.users.edit', ['id' => auth()->id()]) }}"
            wire:current="bg-zinc-200 dark:bg-zinc-700"
            wire:navigate
        >
            <x-icons.person-lines class="w-5" />
            <span class="ml-2">編輯個人資料</span>
        </a>

        <a
            class="flex items-center p-2 rounded-md dark:text-zinc-50 dark:hover:bg-zinc-700 hover:bg-zinc-200"
            href="{{ route('settings.users.password.edit', ['id' => auth()->id()]) }}"
            wire:current="bg-zinc-200 dark:bg-zinc-700"
            wire:navigate
        >
            <x-icons.file-earmark-lock class="w-5" />
            <span class="ml-2">修改密碼</span>
        </a>

        <a
            class="flex items-center p-2 rounded-md dark:text-zinc-50 dark:hover:bg-zinc-700 hover:bg-zinc-200"
            href="{{ route('settings.users.passkeys.edit', ['id' => auth()->id()]) }}"
            wire:current="bg-zinc-200 dark:bg-zinc-700"
            wire:navigate
        >
            <x-icons.fingerprint class="w-5" />
            <span class="ml-2">密碼金鑰</span>
        </a>

        <a
            class="flex items-center p-2 rounded-md dark:text-zinc-50 dark:hover:bg-zinc-700 hover:bg-zinc-200"
            href="{{ route('settings.users.destroy', ['id' => auth()->id()]) }}"
            wire:current="bg-zinc-200 dark:bg-zinc-700"
            wire:navigate
        >
            <x-icons.person-x class="w-5" />
            <span class="ml-2">刪除帳號</span>
        </a>
    </div>
</x-card>
