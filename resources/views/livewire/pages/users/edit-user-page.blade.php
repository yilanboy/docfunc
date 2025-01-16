<x-layouts.layout-main>
  <div class="container mx-auto flex-1">
    <div class="flex flex-col items-start justify-center px-4 md:flex-row xl:px-0">
      <x-member-centre.side-menu />

      <x-card class="mt-6 flex w-full flex-col justify-center space-y-6 md:mt-0 md:w-[700px]">
        <h1 class="w-full text-center text-2xl dark:text-gray-50">編輯個人資料</h1>
        <hr class="my-4 h-0.5 border-0 bg-gray-300 dark:bg-gray-700">

        <div class="flex flex-col items-center justify-center">
          {{-- 大頭貼照片 --}}
          <div>
            <img
              class="h-48 w-48 rounded-full"
              src="{{ $user->gravatar_url }}"
              alt="{{ $name }}"
            >
          </div>

          <div class="mt-4 flex dark:text-gray-50">
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
          class="w-full"
          wire:submit="update({{ $user->id }})"
        >
          {{-- 信箱 --}}
          <div>
            <label
              class="text-gray-600 dark:text-gray-50"
              for="email"
            >信箱</label>

            @php
              $emailLength = strlen($user->email);
              $startToMask = round($emailLength / 4);
              $maskLength = ceil($emailLength / 2);
            @endphp

            <input
              class="mt-2 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 placeholder-slate-400 invalid:border-pink-500 invalid:text-pink-600 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500 focus:invalid:border-pink-500 focus:invalid:ring-pink-500 disabled:border-slate-200 disabled:bg-slate-50 disabled:text-slate-500 dark:disabled:border-slate-500 dark:disabled:bg-slate-700 dark:disabled:text-slate-400"
              id="email"
              name="email"
              type="text"
              value="{{ str()->mask($user->email, '*', $startToMask, $maskLength) }}"
              placeholder="信箱"
              disabled
            >
          </div>

          {{-- 會員名稱 --}}
          <div class="mt-6">
            <label
              class="text-gray-600 dark:text-gray-50"
              for="name"
            >會員名稱 (只能使用英文、數字、_ 或是 -)</label>

            <input
              class="form-input mt-2 w-full rounded-md border border-gray-300 text-lg focus:border-indigo-300 focus:ring focus:ring-indigo-200/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-50 dark:placeholder-gray-50"
              id="name"
              name="name"
              type="text"
              value="{{ old('name', $name) }}"
              wire:model.blur="name"
              placeholder="給自己取個有趣的暱稱吧！"
              required
            >
          </div>

          {{-- 會員自介 --}}
          <div class="mt-6">
            <label
              class="text-gray-600 dark:text-gray-50"
              for="introduction"
            >個人介紹 (最多 80 個字)</label>

            <textarea
              class="form-textarea mt-2 w-full resize-none rounded-md border border-gray-300 text-lg focus:border-indigo-300 focus:ring focus:ring-indigo-200/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-50 dark:placeholder-white"
              id="introduction"
              name="introduction"
              wire:model.blur="introduction"
              placeholder="介紹一下你自己吧！"
              rows="5"
            >{{ old('introduction', $introduction) }}</textarea>
          </div>

          <div class="mt-6 flex items-center justify-end">
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
