<x-layouts.layout-main>
  <div class="container mx-auto flex-1">
    <div class="flex flex-col items-start justify-center px-4 md:flex-row xl:px-0">
      <x-member-centre.side-menu />

      <x-card class="mt-6 flex w-full flex-col justify-center space-y-6 md:mt-0 md:w-[700px]">
        <h1 class="w-full text-center text-2xl dark:text-gray-50">修改密碼</h1>
        <hr class="my-4 h-0.5 border-0 bg-gray-300 dark:bg-gray-700">

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form
          class="w-full"
          wire:submit="update({{ $user->id }})"
        >
          {{-- 舊密碼 --}}
          <x-floating-label-input
            id="current_password"
            type="password"
            placeholder="舊密碼"
            wire:model="current_password"
            required
          />

          {{-- 新密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="new_password"
            type="password"
            placeholder="新密碼"
            wire:model="new_password"
            required
          />

          {{-- 確認新密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="new_password_confirmation"
            type="password"
            placeholder="確認新密碼"
            wire:model="new_password_confirmation"
            required
          />

          <div class="mt-6 flex items-center justify-end">
            {{-- 儲存按鈕 --}}
            <x-button>
              <x-icon.save class="w-5" />
              <span class="ml-2">修改密碼</span>
            </x-button>
          </div>
        </form>
      </x-card>
    </div>
  </div>
</x-layouts.layout-main>
