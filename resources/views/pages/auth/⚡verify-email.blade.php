<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('驗證電子郵件')] class extends Component {
    public function mount(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('root', absolute: false), navigate: true);
        }
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('root', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
};
?>

<x-layouts.auth>
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
        <x-icons.person-check class="w-6" />
        <span class="ml-4">驗證 Email</span>
      </div>

      <x-card class="mt-4 w-full overflow-hidden sm:max-w-md">
        <div class="mb-4 text-zinc-600 dark:text-zinc-50">
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
            class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-50"
            type="button"
            wire:click="$dispatch('logout')"
          >
            {{ __('Log Out') }}
          </button>
        </div>
      </x-card>
    </div>

  </div>
</x-layouts.auth>
