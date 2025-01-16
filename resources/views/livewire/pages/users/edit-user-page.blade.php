<x-layouts.layout-main>
  <div class="container mx-auto flex-1">
    <div class="flex flex-col items-start justify-center gap-6 px-4 md:flex-row">
      <x-member-centre.side-menu />

      <x-card class="flex w-full flex-col justify-center gap-6 md:max-w-2xl">
        <div class="space-y-4">
          <h1 class="w-full text-center text-2xl dark:text-gray-50">編輯個人資料</h1>
          <hr class="h-0.5 border-0 bg-gray-300 dark:bg-gray-700">
        </div>

        <div class="flex flex-col items-center justify-center gap-4">
          {{-- 大頭貼照片 --}}
          <img
            class="size-48 rounded-full"
            src="{{ $user->gravatar_url }}"
            alt="{{ $name }}"
          >

          <div class="flex dark:text-gray-50">
            <span class="mr-2">個人圖像由</span>
            <a
              class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
              href="https://zh-tw.gravatar.com/"
              target="_blank"
              rel="nofollow noopener noreferrer"
            >Gravatar</a>
            <span class="ml-2">提供</span>
          </div>
        </div>

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form
          class="w-full space-y-6"
          wire:submit="update({{ $user->id }})"
        >
          @php
            $emailLength = strlen($user->email);
            $startToMask = round($emailLength / 4);
            $maskLength = ceil($emailLength / 2);
          @endphp

          <x-floating-label-input
            id="email"
            type="text"
            value="{{ str()->mask($user->email, '*', $startToMask, $maskLength) }}"
            placeholder="信箱"
            disabled
          />

          <x-floating-label-input
            id="name"
            type="text"
            value="{{ old('name', $name) }}"
            wire:model.blur="name"
            placeholder="你的名字 (只能使用英文、數字、_ 或是 -)"
            required
            autofocus
          />

          <x-floating-label-textarea
            id="introduction"
            name="introduction"
            wire:model.blur="introduction"
            placeholder="介紹一下你自己吧！ (最多 80 個字)"
            rows="5"
          >{{ old('introduction', $introduction) }}</x-floating-label-textarea>

          <div class="flex items-center justify-end">
            {{-- 儲存按鈕 --}}
            <x-button>
              <x-icon.save class="w-5" />
              <span class="ml-2">儲存</span>
            </x-button>
          </div>
        </form>
      </x-card>
    </div>
  </div>
</x-layouts.layout-main>
