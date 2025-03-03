<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use App\Rules\Captcha;
use App\Services\SettingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('註冊')]
class RegisterPage extends Component
{
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
        abort_if(! SettingService::isRegisterAllowed(), 503);

        $validated = $this->validate([
            'name' => ['required', 'string', 'regex:/^[A-Za-z0-9\-\_\s]+$/u', 'between:3,25', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'captchaToken' => ['required', new Captcha],
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['password'] = Hash::make($validated['password']);
        unset($validated['captchaToken']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect('verify-email', navigate: true);
    }
}
