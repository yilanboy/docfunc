<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Settings\Users\Passkeys;

use App\Models\User;
use App\Services\CustomCounterChecker;
use App\Services\Serializer;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

class EditPage extends Component
{
    public User $user;

    public string $name = '';

    public string $passkey = '';

    #[Locked]
    public string $optionEndpoint = '';

    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);
        $this->optionEndpoint = route('passkeys.register-options');

        $this->authorize('update', $this->user);
    }

    public function store(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'passkey' => ['required', 'json'],
        ]);

        $serializer = Serializer::make();

        $publicKeyCredential = $serializer
            ->fromJson($data['passkey'], PublicKeyCredential::class);

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $options = Session::get('passkey-registration-options');

        if (! $options) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialCreationOptions = $serializer->fromJson(
            $options,
            PublicKeyCredentialCreationOptions::class,
        );

        $csmFactory = new CeremonyStepManagerFactory;
        $csmFactory->setCounterChecker(new CustomCounterChecker);

        try {
            $publicKeyCredentialSource = AuthenticatorAttestationResponseValidator::create(
                $csmFactory->requestCeremony()
            )->check(
                authenticatorAttestationResponse: $publicKeyCredential->response,
                publicKeyCredentialCreationOptions: $publicKeyCredentialCreationOptions,
                host: request()->getHost(),
            );
        } catch (Throwable) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialSourceArray = $serializer->toArray(
            $publicKeyCredentialSource
        );

        request()->user()->passkeys()->create([
            'name' => $data['name'],
            'credential_id' => $publicKeyCredentialSourceArray['publicKeyCredentialId'],
            'data' => $publicKeyCredentialSourceArray,
        ]);

        $this->reset('name', 'passkey');

        $this->dispatch('toast', status: 'success', message: '成功建立密碼金鑰！');
        $this->dispatch('reset-passkey-name');
    }

    public function destroy(int $passkeyId): void
    {
        $passkey = $this->user->passkeys()->findOrFail($passkeyId);

        $this->authorize('delete', $passkey);

        $passkey->delete();

        $this->dispatch('toast', status: 'success', message: '成功刪除密碼金鑰！');
    }

    public function render()
    {
        return view('livewire.pages.settings.users.passkeys.edit-page');
    }
}
