<x-layouts.layout-auth>
  <div class="fixed left-5 top-5">
    <a
      class="block text-2xl font-semibold text-gray-400 transition duration-150 ease-in hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-50"
      href="{{ route('login') }}"
      wire:navigate
    >
      <i class="bi bi-arrow-left-circle-fill"></i>
      <span class="ml-2">返回登入</span>
    </a>
  </div>

  <div class="container mx-auto">
    {{-- google recaptcha --}}
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>

    <div class="flex min-h-screen flex-col items-center justify-center px-4">
      {{-- 頁面標題 --}}
      <div class="fill-current text-2xl text-gray-700 dark:text-gray-50">
        <i class="bi bi-person-plus-fill"></i><span class="ml-4">註冊</span>
      </div>

      <x-card class="mt-4 w-full space-y-6 overflow-hidden sm:max-w-md">

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <form
          id="register"
          x-data="{
              recaptchaSiteKey: @js(config('services.recaptcha.site_key'))
          }"
          x-on:submit.prevent="
                grecaptcha.ready(function() {
                    grecaptcha.execute(recaptchaSiteKey, { action: 'submit' })
                        .then(function(response) {
                            // set livewire property 'recaptcha' value
                            $wire.set('recaptcha', response);

                            // submit the form and call the livewire method 'store'
                            $wire.store();
                        });
                });
            "
        >
          {{-- 會員名稱 --}}
          <div>
            <x-floating-label-input
              name="name"
              type="text"
              value="{{ old('name') }}"
              :id="'name'"
              :placeholder="'會員名稱 (只能使用英文、數字、_ 或是 -)'"
              required
              autofocus
              wire:model="name"
            />
          </div>

          {{-- 信箱 --}}
          <div class="mt-6">
            <x-floating-label-input
              name="email"
              type="text"
              value="{{ old('email') }}"
              :id="'email'"
              :placeholder="'電子信箱'"
              required
              wire:model="email"
            />
          </div>

          {{-- 密碼 --}}
          <div class="mt-6">
            <x-floating-label-input
              name="password"
              type="password"
              :id="'password'"
              :placeholder="'密碼'"
              required
              wire:model="password"
            />
          </div>

          {{-- 確認密碼 --}}
          <div class="mt-6">
            <x-floating-label-input
              name="password_confirmation"
              type="password"
              :id="'password_confirmation'"
              :placeholder="'確認密碼'"
              required
              wire:model="password_confirmation"
            />
          </div>

          <div class="mt-6 flex items-center justify-end">
            <a
              class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
              href="{{ route('login') }}"
              wire:navigate
            >
              {{ __('Already registered?') }}
            </a>

            <x-button class="ml-4">
              {{ __('Register') }}
            </x-button>
          </div>
        </form>
      </x-card>
    </div>

  </div>
</x-layouts.layout-auth>