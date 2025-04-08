<?php

namespace App\Livewire\Pages\Settings\Users;

use App\Models\User;
use App\Services\CustomCounterChecker;
use App\Services\Serializer;
use Livewire\Component;
use Session;
use Throwable;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

class EditPasskeysPage extends Component
{
    public User $user;

    public string $name = '';

    public string $passkey = '';

    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);

        $this->authorize('update', $this->user);
    }

    public function store(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'passkey' => ['required', 'json'],
        ]);

        $publicKeyCredential = Serializer::make()
            ->fromJson($data['passkey'], PublicKeyCredential::class);

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            $this->dispatch('info-badge', status: 'danger', message: '密碼金鑰無效');
        }

        $publicKeyCredentialCreationOptions = Serializer::make()->fromJson(
            Session::get('passkey-registration-options'),
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
            $this->dispatch('info-badge', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialSourceJson = json_decode(
            json: Serializer::make()->toJson($publicKeyCredentialSource),
            associative: true
        );

        request()->user()->passkeys()->create([
            'name' => $data['name'],
            'credential_id' => $publicKeyCredentialSourceJson['publicKeyCredentialId'],
            'data' => $publicKeyCredentialSourceJson,
        ]);

        $this->dispatch('info-badge', status: 'success', message: '成功建立密碼金鑰！');

        $this->reset('name', 'passkey');
    }

    public function destroy(int $passkeyId): void
    {
        $passkey = $this->user->passkeys()->findOrFail($passkeyId);

        $this->authorize('delete', $passkey);

        $passkey->delete();

        $this->dispatch('info-badge', status: 'success', message: '成功刪除密碼金鑰！');
    }

    public function render()
    {
        return view('livewire.pages.settings.users.edit-passkeys-page');
    }
}
