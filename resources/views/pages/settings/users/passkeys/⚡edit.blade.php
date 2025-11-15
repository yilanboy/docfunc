<?php

declare(strict_types=1);

use App\Mail\CreatePasskeyMail;
use App\Models\User;
use App\Services\CustomCounterChecker;
use App\Services\Serializer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;

new class extends Component {
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

        $publicKeyCredential = $serializer->fromJson($data['passkey'], PublicKeyCredential::class);

        if (!$publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $options = Session::get('passkey-registration-options');

        if (!$options) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialCreationOptions = $serializer->fromJson($options, PublicKeyCredentialCreationOptions::class);

        $csmFactory = new CeremonyStepManagerFactory();
        $csmFactory->setCounterChecker(new CustomCounterChecker());

        try {
            $publicKeyCredentialSource = AuthenticatorAttestationResponseValidator::create($csmFactory->requestCeremony())->check(authenticatorAttestationResponse: $publicKeyCredential->response, publicKeyCredentialCreationOptions: $publicKeyCredentialCreationOptions, host: request()->getHost());
        } catch (Throwable) {
            $this->dispatch('toast', status: 'danger', message: '密碼金鑰無效');

            return;
        }

        $publicKeyCredentialSourceArray = $serializer->toArray($publicKeyCredentialSource);

        request()
            ->user()
            ->passkeys()
            ->create([
                'name' => $data['name'],
                'credential_id' => $publicKeyCredentialSourceArray['publicKeyCredentialId'],
                'data' => $publicKeyCredentialSourceArray,
            ]);

        $this->reset('name', 'passkey');

        Mail::to($this->user)->queue(new CreatePasskeyMail(passkeyName: $data['name']));

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
};
?>

@assets
  @vite('resources/ts/webauthn.ts')
@endassets

@script
  <script>
    Alpine.data('settingsUsersPasskeysEditPage', () => ({
      passkey: {
        optionEndpoint: $wire.optionEndpoint,
      },
      name: '',
      browserSupportsWebAuthn,
      async register() {
        if (!this.browserSupportsWebAuthn()) {
          this.$wire.$dispatch('toast', {
            status: 'danger',
            message: '不支援 WebAuthn'
          });

          return;
        }

        if (this.name === '') {
          this.$wire.$dispatch('toast', {
            status: 'danger',
            message: '請輸入密碼金鑰名稱'
          });

          return;
        }

        const response = await fetch(this.passkey.optionEndpoint);
        const optionsJSON = await response.json();

        try {
          this.$wire.passkey = JSON.stringify(await startRegistration({
            optionsJSON
          }));
        } catch (e) {
          this.$wire.$dispatch('toast', {
            status: 'danger',
            message: '註冊失敗，請重新註冊'
          });

          return;
        }

        this.$wire.name = this.name;
        this.$wire.store();
      },
      init() {
        this.$wire.$on('reset-passkey-name', () => {
          this.name = '';
        });
      }
    }));
  </script>
@endscript

<x-layouts.main x-data="settingsUsersPasskeysEditPage">
  <div class="container mx-auto grow">
    <div class="flex flex-col items-start justify-center gap-6 px-4 md:flex-row">
      <x-users.member-center-side-menu />

      <x-card class="flex w-full flex-col justify-center gap-6 md:max-w-2xl">
        <div class="space-y-4">
          <h1 class="w-full text-center text-2xl dark:text-zinc-50">密碼金鑰</h1>
          <hr class="h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">
        </div>

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

        <x-quotes.success>
          註冊密碼金鑰後，將無法使用密碼進行登入
        </x-quotes.success>

        <form
          id="passkey"
          x-on:submit.prevent="register"
        >
          <x-floating-label-input
            id="name"
            type="text"
            placeholder="密碼金鑰名稱"
            x-model="name"
          />
        </form>

        <ul
          class="divide-y divide-zinc-200 dark:divide-zinc-700"
          role="list"
        >
          @foreach ($user->passkeys as $passkey)
            <li class="flex justify-between gap-x-6 py-5">
              <div class="flex min-w-0 gap-x-4">
                @if (in_array('usb', $passkey->data['transports']))
                  <x-icons.usb-drive-fill class="size-12 flex-none dark:text-zinc-50" />
                @else
                  <x-icons.fingerprint class="size-12 flex-none dark:text-zinc-50" />
                @endif
                <div class="min-w-0 flex-auto">
                  <p class="text-sm/6 font-semibold text-zinc-900 dark:text-zinc-50">
                    {{ $passkey->name }}
                  </p>
                  <p class="mt-1 flex truncate text-xs/5 text-zinc-500 dark:text-zinc-400">
                    建立於 {{ $passkey->created_at->diffForHumans() }}
                  </p>
                </div>
              </div>
              <div class="flex shrink-0 items-center gap-x-6">
                <div class="hidden sm:flex sm:flex-col sm:items-end">
                  <p class="text-sm/6 text-zinc-900 dark:text-zinc-50">
                    {{ strtoupper(implode(' / ', $passkey->data['transports'])) }}
                  </p>
                  <p class="mt-1 text-xs/5 text-zinc-500 dark:text-zinc-400">
                    @if ($passkey->last_used_at)
                      上次使用於
                      <time datetime="{{ $passkey->last_used_at }}">
                        {{ $passkey->last_used_at->diffForHumans() }}
                      </time>
                    @else
                      尚未使用
                    @endif
                  </p>
                </div>
                <button
                  class="-m-2.5 block cursor-pointer p-2.5 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
                  type="button"
                  wire:click="destroy({{ $passkey->id }})"
                  wire:confirm="你確定要刪除這個密碼金鑰嗎？"
                >
                  <span class="sr-only">開啟編輯選單</span>
                  <x-icons.x class="size-6" />
                </button>

              </div>
            </li>
          @endforeach
        </ul>

        <div class="flex items-center justify-end">
          <x-button form="passkey">
            <x-icons.save class="w-5" />
            <span class="ml-2">新增密碼金鑰</span>
          </x-button>
        </div>
      </x-card>
    </div>
  </div>
</x-layouts.main>
