<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('忘記密碼')]
class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
};
?>

<x-layouts.auth>
    <div class="fixed top-5 left-5">
        <a
            class="flex items-center text-2xl transition duration-150 ease-in text-zinc-400 dark:text-zinc-400 dark:hover:text-zinc-50 hover:text-zinc-600"
            href="{{ route('login') }}"
            wire:navigate
        >
            <x-icons.arrow-left-circle class="w-6" />
            <span class="ml-2">返回登入</span>
        </a>
    </div>

    <div class="container mx-auto">
        <div class="flex flex-col justify-center items-center px-4 min-h-screen">
            {{-- 頁面標題 --}}
            <div class="flex items-center text-2xl fill-current text-zinc-700 dark:text-zinc-50">
                <x-icons.question-circle class="w-6" />
                <span class="ml-4">忘記密碼</span>
            </div>

            <x-card class="overflow-hidden mt-4 space-y-6 w-full sm:max-w-md">
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

                    <div class="flex justify-end items-center mt-6">
                        <x-button>
                            {{ __('Email Password Reset Link') }}
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.auth>
