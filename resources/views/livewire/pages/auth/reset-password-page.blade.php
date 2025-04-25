<x-layouts.layout-auth>
  <div class="container mx-auto">
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
      {{-- 頁面標題 --}}
      <div class="flex items-center fill-current text-2xl text-gray-700 dark:text-gray-50">
        <x-icons.question-circle class="w-6" />
        <span class="ml-4">重設密碼</span>
      </div>

      <x-card class="mt-4 w-full space-y-6 overflow-hidden sm:max-w-md">
        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form wire:submit="resetPassword">
          {{-- 信箱 --}}
          <x-floating-label-input
            id="email"
            type="text"
            placeholder="電子信箱"
            required
            readonly
            wire:model="email"
          />

          {{-- 密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="password"
            type="password"
            placeholder="新密碼"
            required
            autofocus
            wire:model="password"
          />

          {{-- 確認密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="password_confirmation"
            type="password"
            placeholder="確認新密碼"
            required
            wire:model="password_confirmation"
          />

          <div class="mt-6 flex items-center justify-end">
            <x-button>
              {{ __('Reset Password') }}
            </x-button>
          </div>
        </form>
      </x-card>
    </div>
  </div>
</x-layouts.layout-auth>
