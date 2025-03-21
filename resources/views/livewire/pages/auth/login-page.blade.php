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

        // Availability of `window.PublicKeyCredential` means WebAuthn is usable.
        // `isUserVerifyingPlatformAuthenticatorAvailable` means the feature detection is usable.
        // `isConditionalMediationAvailable` means the feature detection is usable.
        if (window.PublicKeyCredential &&
          PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable &&
          PublicKeyCredential.isConditionalMediationAvailable) {
          // Check if user verifying platform authenticator is available.
          Promise.all([
            PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable(),
            PublicKeyCredential.isConditionalMediationAvailable()
          ]).then(results => {
            if (results.every(r => r === true)) {
              // Display "Create a new passkey" button
              console.log('support passkey');
            }
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

          <div class="mt-12 flex items-center justify-between">

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
