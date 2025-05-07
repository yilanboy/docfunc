<?php

namespace App\Livewire\Pages\Auth;

ini_set('json.exceptions', '1');

use App\Models\Passkey;
use App\Services\Serializer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
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

    public string $answer = '';

    #[Locked]
    public string $optionEndpoint = '';

    public function mount(): void
    {
        $this->optionEndpoint = route('passkeys.authentication-options');
    }

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // If the user has PassKeys, he won't be able to log in using just the password
        if (Auth::user()->passkeys()->count() > 0) {
            session()->flash('status', '您的帳號已註冊密碼金鑰，請使用密碼金鑰進行登入');

            Auth::logout();

            return;
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->dispatch('toast', status: 'success', message: '登入成功！');

        $this->redirectIntended(route('root', absolute: false), navigate: true);
    }

    public function loginWithPasskey(): void
    {
        $data = $this->validate(['answer' => ['required', 'json']]);

        $this->ensureIsNotRateLimited();

        $serializer = Serializer::make();

        $publicKeyCredential = $serializer
            ->fromJson($data['answer'], PublicKeyCredential::class);

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $rawId = json_decode($data['answer'], true)['rawId'];

        $passkey = Passkey::firstWhere('credential_id', $rawId);

        if (! $passkey) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialSource = $serializer
            ->fromJson(json_encode($passkey->data), PublicKeyCredentialSource::class);

        $options = Session::get('passkey-authentication-options');

        if (! $options) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialRequestOptions = $serializer->fromJson(
            $options,
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
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $passkey->update([
            'last_used_at' => now(),
        ]);

        Auth::loginUsingId(id: $passkey->user_id, remember: true);

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->dispatch('toast', status: 'success', message: '登入成功！');

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
