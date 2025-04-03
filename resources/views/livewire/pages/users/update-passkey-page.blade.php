@assets
  @vite('resources/ts/webauthn.ts')
@endassets

@script
  <script>
    Alpine.data('updatePasskeyPage', () => ({
      name: '',
      passkey: null,
      browserSupportsWebAuthn,
      async register() {
        if (!this.browserSupportsWebAuthn()) {
          this.$wire.dispatch('info-badge', {
            status: 'danger',
            message: '不支援 WebAuthn'
          });

          return;
        }

        if (this.name === '') {
          this.$wire.dispatch('info-badge', {
            status: 'danger',
            message: '請輸入密碼金鑰名稱'
          });

          return;
        }

        const response = await fetch('/api/passkeys/register');
        const optionsJSON = await response.json();

        try {
          this.passkey = await startRegistration({
            optionsJSON
          });
        } catch (e) {
          this.$wire.dispatch('info-badge', {
            status: 'danger',
            message: '註冊失敗，請重新註冊'
          });

          return;
        }

        this.$wire.name = this.name;
        this.$wire.passkey = JSON.stringify(this.passkey);
        this.$wire.store()
        this.name = '';
        this.passkey = null;
      }
    }));
  </script>
@endscript

<x-layouts.layout-main x-data="updatePasskeyPage">
  <div class="container mx-auto flex-1">
    <div class="flex flex-col items-start justify-center gap-6 px-4 md:flex-row">
      <x-users.member-center-side-menu />

      <x-card class="flex w-full flex-col justify-center gap-6 md:max-w-2xl">
        <div class="space-y-4">
          <h1 class="w-full text-center text-2xl dark:text-gray-50">密碼金鑰</h1>
          <hr class="h-0.5 border-0 bg-gray-300 dark:bg-gray-700">
        </div>

        {{-- 驗證錯誤訊息 --}}
        <x-auth-validation-errors :errors="$errors" />

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

        @foreach ($user->passkeys as $passkey)
          <div class="flex justify-between rounded-md bg-gray-100 p-4">
            <div class="flex flex-col gap-2">
              <span>{{ $passkey->name }}</span>
              <span>{{ $passkey->created_at->diffForHumans() }}</span>
            </div>
            <button
              class="focus:outline-hidden focus:ring-3 inline-flex cursor-pointer items-center justify-center rounded-xl border border-transparent bg-red-600 px-4 py-2 uppercase tracking-widest text-gray-50 ring-red-300 transition duration-150 ease-in-out hover:bg-red-700 focus:border-red-700 active:bg-red-600 disabled:opacity-25"
              type="button"
              wire:click="destroy({{ $passkey->id }})"
              wire:confirm="你確定要刪除這個密碼金鑰嗎？"
            >
              刪除
            </button>
          </div>
        @endforeach

        <div class="flex items-center justify-end">
          {{-- 儲存按鈕 --}}
          <x-button form="passkey">
            <x-icon.save class="w-5" />
            <span class="ml-2">儲存</span>
          </x-button>
        </div>
      </x-card>
    </div>
  </div>
</x-layouts.layout-main>
