@assets
  @vite('resources/ts/webauthn.ts')
@endassets

@script
  <script>
    Alpine.data('updatePasskeyPage', () => ({
      name: $wire.entangle('name'),
      passkey: $wire.entangle('passkey'),
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

        const response = await fetch('/api/passkeys/generate-register-options');
        const optionsJSON = await response.json();

        try {
          this.passkey = JSON.stringify(await startRegistration({
            optionsJSON
          }))
        } catch (e) {
          this.$wire.dispatch('info-badge', {
            status: 'danger',
            message: '註冊失敗，請重新註冊'
          });

          return;
        }

        this.$wire.store()
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

        <div
          class="relative ml-4 rounded-md border-none bg-emerald-300/20 px-4 py-2 text-emerald-500 before:absolute before:-left-4 before:top-0 before:h-full before:w-1.5 before:rounded-sm before:bg-emerald-500 before:contain-none dark:text-emerald-400 dark:before:bg-emerald-400"
        >
          註冊密碼金鑰後，將無法使用密碼進行登入
        </div>

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
          class="divide-y divide-gray-200 dark:divide-gray-700"
          role="list"
        >
          @foreach ($user->passkeys as $passkey)
            <li class="flex justify-between gap-x-6 py-5">
              <div class="flex min-w-0 gap-x-4">
                @if (in_array('usb', $passkey->data['transports']))
                  <x-icon.usb-drive class="size-12 flex-none dark:text-gray-50" />
                @else
                  <x-icon.fingerprint class="size-12 flex-none dark:text-gray-50" />
                @endif
                <div class="min-w-0 flex-auto">
                  <p class="text-sm/6 font-semibold text-gray-900 dark:text-gray-50">
                    {{ $passkey->name }}
                  </p>
                  <p class="mt-1 flex truncate text-xs/5 text-gray-500 dark:text-gray-400">
                    建立於 {{ $passkey->created_at->diffForHumans() }}
                  </p>
                </div>
              </div>
              <div class="flex shrink-0 items-center gap-x-6">
                <div class="hidden sm:flex sm:flex-col sm:items-end">
                  <p class="text-sm/6 text-gray-900 dark:text-gray-50">
                    {{ implode(' / ', $passkey->data['transports']) }}
                  </p>
                  <p class="mt-1 text-xs/5 text-gray-500 dark:text-gray-400">
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
                  class="-m-2.5 block cursor-pointer p-2.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-50"
                  type="button"
                  wire:click="destroy({{ $passkey->id }})"
                  wire:confirm="你確定要刪除這個密碼金鑰嗎？"
                >
                  <span class="sr-only">開啟編輯選單</span>
                  <x-icon.x class="size-6" />
                </button>

              </div>
            </li>
          @endforeach
        </ul>

        <div class="flex items-center justify-end">
          <x-button form="passkey">
            <x-icon.save class="w-5" />
            <span class="ml-2">新增密碼金鑰</span>
          </x-button>
        </div>
      </x-card>
    </div>
  </div>
</x-layouts.layout-main>
