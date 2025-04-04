@assets
  @vite('resources/ts/webauthn.ts')
@endassets

@script
  <script>
    Alpine.data('login', () => ({
      submitIsEnabled: false,
      captchaSiteKey: @js(config('services.captcha.site_key')),
      answer: null,
      browserSupportsWebAuthn,
      submitIsDisabled() {
        return this.submitIsEnabled === false;
      },
      informationOnSubmitButton() {
        return this.submitIsEnabled ? '登入' : '驗證中';
      },
      async loginWithPasskey() {
        const response = await fetch('/api/passkeys/authenticate');
        const optionsJSON = await response.json();

        this.answer = await startAuthentication({
          optionsJSON
        });
        this.$wire.answer = JSON.stringify(this.answer);

        this.$wire.loginWithPasskey()
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

        if (!this.browserSupportsWebAuthn()) {
          this.$wire.dispatch('info-badge', {
            status: 'danger',
            message: '不支援 WebAuthn'
          });
        }
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

            <div
              class="hidden"
              wire:ignore
              x-ref="turnstileBlock"
            ></div>

            @if (Route::has('password.request'))
              <a
                class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
                href="{{ route('password.request') }}"
                wire:navigate
              >
                {{ __('Forgot your password?') }}
              </a>
            @endif
          </div>

          <x-button
            class="mt-6 w-full"
            x-bind:disabled="submitIsDisabled"
          >
            <x-icon.animate-spin
              class="mr-2 h-5 w-5 text-gray-50"
              x-cloak
              x-show="submitIsDisabled"
            />
            <span x-text="informationOnSubmitButton"></span>
          </x-button>
        </form>

        {{-- Passkey login --}}
        <div
          x-cloak
          x-show="browserSupportsWebAuthn"
        >
          <div class="relative">
            <div
              class="absolute inset-0 flex items-center"
              aria-hidden="true"
            >
              <div class="w-full border-t border-gray-200 dark:border-gray-500"></div>
            </div>
            <div class="relative flex justify-center text-base font-medium">
              <span class="bg-gray-50 px-6 text-gray-900 dark:bg-gray-800 dark:text-gray-50">或者使用</span>
            </div>
          </div>

          <div class="mt-6">
            <button
              class="shadow-xs flex w-full cursor-pointer items-center justify-center gap-3 rounded-xl bg-gray-50 px-4 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-100 focus-visible:ring-transparent dark:bg-gray-800 dark:text-gray-50 dark:ring-gray-700 dark:hover:bg-gray-700"
              type="button"
              x-on:click="loginWithPasskey"
            >
              <x-icon.fingerprint class="size-5" />
              <span>密碼金鑰</span>
            </button>
          </div>
        </div>
      </x-card>
    </div>
  </div>
</x-layouts.layout-auth>
