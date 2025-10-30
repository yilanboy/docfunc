<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\Captcha;
use App\Services\SettingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('註冊')] class extends Component {
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $captchaToken = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        abort_if(!SettingService::isRegisterAllowed(), 503);

        $validated = $this->validate([
            'name' => ['required', 'string', 'regex:/^[A-Za-z0-9\-\_\s]+$/u', 'between:3,25', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'captchaToken' => ['required', new Captcha()],
        ]);

        $validated['name'] = trim($validated['name']);

        $user = User::create(Arr::only($validated, ['name', 'email', 'password']));

        event(new Registered($user));

        Auth::login($user);

        $this->redirect('verify-email', navigate: true);
    }
};
?>

@script
  <script>
    Alpine.data('authRegisterPage', () => ({
      submitIsEnabled: false,
      captchaSiteKey: @js(config('services.captcha.site_key')),
      submitIsDisabled() {
        return this.submitIsEnabled === false;
      },
      informationOnSubmitButton() {
        return this.submitIsEnabled ? '註冊' : '驗證中';
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

<x-layouts.auth x-data="authRegisterPage">
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
        <x-icons.person-plus class="w-6" />
        <span class="ml-4">註冊</span>
      </div>

      <x-card class="mt-4 w-full space-y-6 overflow-hidden sm:max-w-md">

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form
          id="register"
          wire:submit="register"
        >
          {{-- 會員名稱 --}}
          <x-floating-label-input
            id="name"
            type="text"
            value="{{ old('name') }}"
            placeholder="會員名稱 (只能使用英文、數字、_ 或是 -)"
            required
            autofocus
            wire:model="name"
          />

          {{-- 信箱 --}}
          <x-floating-label-input
            class="mt-6"
            id="email"
            type="text"
            value="{{ old('email') }}"
            placeholder="電子信箱"
            required
            wire:model="email"
          />

          {{-- 密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="password"
            type="password"
            placeholder="密碼"
            required
            wire:model="password"
          />

          {{-- 確認密碼 --}}
          <x-floating-label-input
            class="mt-6"
            id="password_confirmation"
            type="password"
            placeholder="確認密碼"
            required
            wire:model="password_confirmation"
          />

          <div
            class="hidden"
            wire:ignore
            x-ref="turnstileBlock"
          ></div>

          <div class="mt-6 flex items-center justify-end">
            <a
              class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-50"
              href="{{ route('login') }}"
              wire:navigate
            >
              {{ __('Already registered?') }}
            </a>

            <x-button
              class="ml-4"
              x-bind:disabled="submitIsDisabled"
            >
              <x-icons.animate-spin
                class="mr-2 h-5 w-5 text-zinc-50"
                x-cloak
                x-show="submitIsDisabled"
              />
              <span x-text="informationOnSubmitButton"></span>
            </x-button>
          </div>
        </form>
      </x-card>
    </div>
  </div>
</x-layouts.auth>
