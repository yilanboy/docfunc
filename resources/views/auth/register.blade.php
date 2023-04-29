@section('title', '註冊')

{{-- Google reCAPTCHA --}}
@push('script')
  <script>
    document.getElementById("register").addEventListener("submit", function(event) {
      event.preventDefault();
      grecaptcha.ready(function() {
        grecaptcha.execute("{{ config('services.recaptcha.site_key') }}", {
            action: "submit"
          })
          .then(function(response) {
            document.getElementById("g-recaptcha-response").value = response;
            document.getElementById("register").submit();
          });
      });
    });
  </script>
@endpush

<x-app-layout>
  <div class="container mx-auto max-w-7xl">
    <div class="flex items-center justify-center px-4 xl:px-0">

      <div class="flex w-full flex-col items-center justify-center">
        {{-- 頁面標題 --}}
        <div class="fill-current text-2xl text-gray-700 dark:text-gray-50">
          <i class="bi bi-person-plus-fill"></i><span class="ml-4">註冊</span>
        </div>

        <x-card class="mt-4 w-full space-y-6 overflow-hidden sm:max-w-md">

          {{-- 驗證錯誤訊息 --}}
          <x-auth-validation-errors :errors="$errors" />

          <form
            id="register"
            method="POST"
            action="{{ route('register') }}"
          >
            @csrf

            {{-- reCAPTCHA --}}
            <input
              type="hidden"
              class="g-recaptcha"
              name="g-recaptcha-response"
              id="g-recaptcha-response"
            >

            {{-- 會員名稱 --}}
            <div>
              <x-floating-label-input
                :type="'text'"
                :name="'name'"
                :placeholder="'會員名稱 (只能使用英文、數字、_ 或是 -)'"
                :value="old('name')"
                required
                autofocus
              ></x-floating-label-input>
            </div>

            {{-- 信箱 --}}
            <div class="mt-6">
              <x-floating-label-input
                :type="'text'"
                :name="'email'"
                :placeholder="'電子信箱'"
                :value="old('email')"
                required
              ></x-floating-label-input>
            </div>

            {{-- 密碼 --}}
            <div class="mt-6">
              <x-floating-label-input
                :type="'password'"
                :name="'password'"
                :placeholder="'密碼'"
                required
              >
              </x-floating-label-input>
            </div>

            {{-- 確認密碼 --}}
            <div class="mt-6">
              <x-floating-label-input
                :type="'password'"
                :name="'password_confirmation'"
                :placeholder="'確認密碼'"
                required
              >
              </x-floating-label-input>
            </div>

            <div class="mt-6 flex items-center justify-end">
              <a
                href="{{ route('login') }}"
                class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
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
  </div>
</x-app-layout>
