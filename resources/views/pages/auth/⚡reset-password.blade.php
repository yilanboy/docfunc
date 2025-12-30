<?php

declare(strict_types=1);

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('重設密碼')]
class extends Component
{
    // token will be passed in the URL,
    // and auto binding will take care of it
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->query('email');
    }

    /**
     * Handle an incoming new password request.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token'    => 'required',
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful, we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise, we will parse the error and return the response.
        $status = Password::reset($this->only('email', 'password', 'password_confirmation', 'token'), function ($user) {
            $user
                ->forceFill([
                    'password'       => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])
                ->save();

            event(new PasswordReset($user));
        });

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error, we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
};
?>

<x-layouts.auth>
    <div class="container mx-auto">
        <div class="flex flex-col justify-center items-center px-4 min-h-screen">
            {{-- 頁面標題 --}}
            <div class="flex items-center text-2xl fill-current text-zinc-700 dark:text-zinc-50">
                <x-icons.question-circle class="w-6" />
                <span class="ml-4">重設密碼</span>
            </div>

            <x-card class="overflow-hidden mt-4 space-y-6 w-full sm:max-w-md">
                {{-- 驗證錯誤訊息 --}}
                <x-auth-validation-errors :errors="$errors" />

                <form wire:submit="resetPassword">
                    {{-- 信箱 --}}
                    <x-floating-label-input
                        id="email"
                        type="text"
                        placeholder="電子信箱"
                        required
                        readonly
                        wire:model="email"
                    />

                    {{-- 密碼 --}}
                    <x-floating-label-input
                        class="mt-6"
                        id="password"
                        type="password"
                        placeholder="新密碼"
                        required
                        autofocus
                        wire:model="password"
                    />

                    {{-- 確認密碼 --}}
                    <x-floating-label-input
                        class="mt-6"
                        id="password_confirmation"
                        type="password"
                        placeholder="確認新密碼"
                        required
                        wire:model="password_confirmation"
                    />

                    <div class="flex justify-end items-center mt-6">
                        <x-button>
                            {{ __('Reset Password') }}
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.auth>
