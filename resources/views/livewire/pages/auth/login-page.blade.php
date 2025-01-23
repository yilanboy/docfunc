@script
  <script>
    Alpine.data('login', () => ({
      submitIsEnabled: false,
      captchaSiteKey: @js(config('services.captcha.site_key')),
      submitIsDisabled() {
        return this.submitIsEnabled === false;
      },
      informationOnSubmitButton() {
        return this.submitIsEnabled ? '登入' : '驗證中';
      },
      init() {
        turnstile.ready(() => {
          turnstile.render(this.$refs.turnstileBlock, {
            sitekey: this.captchaSiteKey,
            callback: (token) => {
              this.$wire.set('captchaToken', token);
              this.submitIsEnabled = true;
            }
          });
        });
      }
    }));
  </script>
@endscript

<x-layouts.layout-auth x-data="login">
  <div class="fixed left-5 top-5">
    <a
      class="flex items-center text-2xl text-gray-400 transition duration-150 ease-in hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-50"
      href="{{ route('root') }}"
      wire:navigate
    >
      <x-icon.arrow-left-circle class="w-6" />
      <span class="ml-2">返回文章列表</span>
    </a>
  </div>

  <div class="container mx-auto">

    <div class="flex min-h-screen flex-col items-center justify-center px-4">
      {{-- 頁面標題 --}}
      <div class="flex items-center fill-current text-2xl text-gray-700 dark:text-gray-50">
        <x-icon.door-open class="w-6" />
        <span class="ml-4">登入</span>
      </div>

      {{-- 登入表單 --}}
      <x-card class="mt-4 w-full space-y-6 overflow-hidden sm:max-w-md">

        {{-- Session 狀態訊息 --}}
        <x-auth-session-status :status="session('status')" />

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form
          id="login"
          wire:submit="store"
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

          <div class="mt-12 flex justify-between">
            {{-- 記住我 --}}
            <label
              class="inline-flex items-center"
              for="remember-me"
            >
              <input
                class="form-checkbox dark:text-lividus-500 dark:focus:border-lividus-700 dark:focus:ring-lividus-800/50 rounded-sm border-gray-300 text-emerald-400 focus:border-emerald-300 focus:ring-3 focus:ring-emerald-200/50"
                id="remember-me"
                name="remember"
                type="checkbox"
                wire:model="remember"
              >
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-50">{{ __('Remember me') }}</span>
            </label>

            <div
              class="hidden"
              wire:ignore
              x-ref="turnstileBlock"
            ></div>

            <div>
              @if (Route::has('password.request'))
                <a
                  class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
                  href="{{ route('password.request') }}"
                  wire:navigate
                >
                  {{ __('Forgot your password?') }}
                </a>
              @endif

              <x-button
                class="ml-3"
                x-bind:disabled="submitIsDisabled"
              >
                <x-icon.animate-spin
                  class="mr-2 h-5 w-5 text-gray-50"
                  x-cloak
                  x-show="submitIsDisabled"
                />
                <span x-text="informationOnSubmitButton"></span>
              </x-button>
            </div>
          </div>
        </form>
      </x-card>
    </div>
  </div>
</x-layouts.layout-auth>
