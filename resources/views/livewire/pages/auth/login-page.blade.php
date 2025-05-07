@assets
  @vite('resources/ts/webauthn.ts')
@endassets

@script
  <script>
    Alpine.data('login', () => ({
      passkey: {
        optionEndpoint: @js($optionEndpoint),
        answer: $wire.entangle('answer'),
      },
      browserSupportsWebAuthn,
      async loginWithPasskey() {
        if (!this.browserSupportsWebAuthn()) {
          this.$wire.dispatch('toast', {
            status: 'danger',
            message: '不支援 WebAuthn'
          });

          return;
        }

        const response = await fetch(this.passkey.optionEndpoint);
        const optionsJSON = await response.json();

        try {
          this.passkey.answer = JSON.stringify(await startAuthentication({
            optionsJSON
          }))
        } catch (error) {
          this.$wire.dispatch('toast', {
            status: 'danger',
            message: '登入失敗，請稍後再試'
          });

          return;
        }

        this.$wire.loginWithPasskey()
      }
    }));
  </script>
@endscript

<x-layouts.layout-auth x-data="login">
  <div class="fixed left-5 top-5">
    <a
      class="flex items-center text-2xl text-zinc-400 transition duration-150 ease-in hover:text-zinc-600 dark:text-zinc-400 dark:hover:text-zinc-50"
      href="{{ route('root') }}"
      wire:navigate
    >
      <x-icons.arrow-left-circle class="w-6" />
      <span class="ml-2">返回文章列表</span>
    </a>
  </div>

  <div class="container mx-auto">
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
      {{-- 頁面標題 --}}
      <div class="flex items-center fill-current text-2xl text-zinc-700 dark:text-zinc-50">
        <x-icons.door-open class="w-6" />
        <span class="ml-4">登入</span>
      </div>

      {{-- 登入表單 --}}
      <x-card class="mt-4 w-full overflow-hidden sm:max-w-md">
        {{-- Session 狀態訊息 --}}
        <x-auth-session-status
          class="mb-6"
          :status="session('status')"
        />

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors
          class="mb-6"
          :errors="$errors"
        />

        <form
          id="login"
          wire:submit="login"
        >
          {{-- 信箱 --}}
          <x-floating-label-input
            id="email"
            type="text"
            placeholder="電子信箱"
            wire:model="email"
            required
            autofocus
          />

          {{-- 密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="password"
            type="password"
            placeholder="密碼"
            wire:model="password"
            required
          />

          <div class="mt-6 flex items-center justify-between">
            <x-checkbox
              id="remember"
              name="remember"
              wire:model="remember"
            >
              記住我
            </x-checkbox>

            @if (Route::has('password.request'))
              <a
                class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-50"
                href="{{ route('password.request') }}"
                wire:navigate
              >
                {{ __('Forgot your password?') }}
              </a>
            @endif
          </div>

          <x-button class="mt-6 w-full">登入</x-button>
        </form>

        {{-- Passkey login --}}
        <div class="relative mt-6">
          <div
            class="absolute inset-0 flex items-center"
            aria-hidden="true"
          >
            <div class="w-full border-t border-zinc-200 dark:border-zinc-500"></div>
          </div>
          <div class="relative flex justify-center text-base font-medium">
            <span class="bg-zinc-50 px-6 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-50">或者</span>
          </div>
        </div>

        <div class="mt-6">
          <button
            class="shadow-xs flex w-full cursor-pointer items-center justify-center gap-3 rounded-xl bg-zinc-50 px-4 py-2 text-zinc-900 ring-1 ring-inset ring-zinc-300 hover:bg-zinc-100 focus-visible:ring-transparent active:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-50 dark:ring-zinc-700 dark:hover:bg-zinc-700 dark:active:bg-zinc-800"
            type="button"
            x-on:click="loginWithPasskey"
          >
            <x-icons.fingerprint class="size-5" />
            <span>使用密碼金鑰</span>
          </button>
        </div>
      </x-card>
    </div>
  </div>
</x-layouts.layout-auth>
