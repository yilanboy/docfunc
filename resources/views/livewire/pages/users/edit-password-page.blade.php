<x-layouts.layout-main>
  <div class="container mx-auto flex-1">
    <div class="flex flex-col items-start justify-center gap-6 px-4 md:flex-row">
      <x-users.member-center-side-menu />

      <x-card class="flex w-full flex-col justify-center gap-6 md:max-w-2xl">
        <div class="space-y-4">
          <h1 class="w-full text-center text-2xl dark:text-gray-50">修改密碼</h1>
          <hr class="h-0.5 border-0 bg-gray-300 dark:bg-gray-700">
        </div>

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form
          class="w-full space-y-6"
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
            id="new_password"
            type="password"
            placeholder="新密碼"
            wire:model="new_password"
            required
          />

          {{-- 確認新密碼 --}}
          <x-floating-label-input
            id="new_password_confirmation"
            type="password"
            placeholder="確認新密碼"
            wire:model="new_password_confirmation"
            required
          />

          <div class="flex items-center justify-end">
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
