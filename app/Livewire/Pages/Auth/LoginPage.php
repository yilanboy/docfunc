<?php

namespace App\Livewire\Pages\Auth;

use App\Models\Passkey;
use App\Rules\Captcha;
use App\Support\Serializer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

#[Title('登入')]
class LoginPage extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public string $captchaToken = '';

    public string $answer = '';

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captchaToken' => ['required', new Captcha()]
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->dispatch('info-badge', status: 'success', message: '登入成功！');

        $this->redirectIntended(route('root', absolute: false), navigate: true);
    }

    public function loginWithPasskey(): void
    {
        $data = $this->validate(['answer' => ['required', 'json']]);

        $publicKeyCredential = Serializer::make()
            ->fromJson($data['answer'], PublicKeyCredential::class);

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            $this->dispatch('info-badge', status: 'danger', message: 'Invalid passkey response.');
        }

        $passkey = Passkey::firstWhere('credential_id', $publicKeyCredential->rawId);

        if (! $passkey) {
            $this->dispatch('info-badge', status: 'danger', message: 'This passkey is not valid.');

            return;
        }

        $publicKeyCredentialSource = Serializer::make()
            ->fromJson($passkey->data, PublicKeyCredentialSource::class);

        $publicKeyCredentialRequestOptions = Serializer::make()->fromJson(
            Session::get('passkey-authentication-options'),
            PublicKeyCredentialRequestOptions::class,
        );

        try {
            AuthenticatorAssertionResponseValidator::create(
                new CeremonyStepManagerFactory()->requestCeremony()
            )->check(
                publicKeyCredentialSource: $publicKeyCredentialSource,
                authenticatorAssertionResponse: $publicKeyCredential->response,
                publicKeyCredentialRequestOptions: $publicKeyCredentialRequestOptions,
                host: request()->getHost(),
                userHandle: null,
            );
        } catch (Throwable) {
            $this->dispatch('info-badge', status: 'success', message: 'This passkey is not valid.');

            return;
        }

        Auth::loginUsingId($passkey->user_id);
        Session::regenerate();

        $this->dispatch('info-badge', status: 'success', message: '登入成功！');

        $this->redirectIntended(route('root', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
