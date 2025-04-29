<x-layouts.layout-auth>
  <div class="fixed left-5 top-5">
    <a
      class="flex items-center text-2xl text-zinc-400 transition duration-150 ease-in hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-50"
      href="{{ route('login') }}"
      wire:navigate
    >
      <x-icons.arrow-left-circle class="w-6" />
      <span class="ml-2">返回登入</span>
    </a>
  </div>

  <div class="container mx-auto">
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
      {{-- 頁面標題 --}}
      <div class="flex items-center fill-current text-2xl text-zinc-700 dark:text-zinc-50">
        <x-icons.question-circle class="w-6" />
        <span class="ml-4">忘記密碼</span>
      </div>

      <x-card class="mt-4 w-full space-y-6 overflow-hidden sm:max-w-md">
        <div class="text-zinc-600 dark:text-zinc-50">
          {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        {{-- Session 狀態訊息 --}}
        <x-auth-session-status :status="session('status')" />

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form wire:submit="sendPasswordResetLink">

          {{-- 信箱 --}}
          <x-floating-label-input
            name="email"
            type="text"
            :id="'email'"
            :placeholder="'電子信箱'"
            required
            autofocus
            wire:model="email"
          />

          <div class="mt-6 flex items-center justify-end">
            <x-button>
              {{ __('Email Password Reset Link') }}
            </x-button>
          </div>
        </form>
      </x-card>
    </div>
  </div>
</x-layouts.layout-auth>
