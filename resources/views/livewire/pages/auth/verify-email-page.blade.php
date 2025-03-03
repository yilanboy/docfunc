<x-layouts.layout-auth>
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
        <x-icon.person-check class="w-6" />
        <span class="ml-4">驗證 Email</span>
      </div>

      <x-card class="mt-4 w-full overflow-hidden sm:max-w-md">
        <div class="mb-4 text-gray-600 dark:text-gray-50">
          {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
          <div class="mb-4 font-medium text-emerald-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
          </div>
        @endif

        <div class="mt-4 flex items-center justify-between">

          <x-button
            type="button"
            wire:click="sendVerification"
          >
            {{ __('Resend Verification Email') }}
          </x-button>

          <button
            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
            type="button"
            wire:click="$dispatch('logout')"
          >
            {{ __('Log Out') }}
          </button>
        </div>
      </x-card>
    </div>

  </div>
</x-layouts.layout-auth>
