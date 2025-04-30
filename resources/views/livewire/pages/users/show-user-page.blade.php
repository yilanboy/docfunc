@php
  use App\Enums\UserInfoOptions;
@endphp

@assets
  {{-- highlight code block style --}}
  @vite('node_modules/highlight.js/styles/atom-one-dark.css')
  {{-- highlight code block --}}
  @vite('resources/ts/highlight.ts')
@endassets

@script
  <script>
    // tab can only be 'information', 'posts', 'comments'
    Alpine.data('showUserPage', () => ({
      currentTab: @js($tabSelected),
      contentIsActive(content) {
        return this.currentTab === content.id.replace('-content', '');
      }
    }));
  </script>
@endscript

{{-- user information page --}}
<x-layouts.layout-main>
  <div
    class="container mx-auto flex-1"
    x-data="showUserPage"
  >
    <div class="animate-fade-in flex flex-col items-center justify-start px-4">
      {{-- user information, posts and comments --}}
      <div class="w-full max-w-3xl">
        <x-tabs.nav
          class="mb-6"
          x-model="currentTab"
          :tab-init="$tabSelected"
        >
          @foreach (UserInfoOptions::cases() as $userInfoTab)
            <x-tabs.button
              :tab-value="$userInfoTab->value"
              {{-- update url query parameter in livewire --}}
              wire:click="changeTab('{{ $userInfoTab }}')"
              wire:key="{{ $userInfoTab->value }}-tab-button"
            >
              <x-dynamic-component
                class="w-3"
                :component="$userInfoTab->iconComponentName()"
              />
              <span>{{ $userInfoTab->label() }}</span>
            </x-tabs.button>
          @endforeach
        </x-tabs.nav>

        @foreach (UserInfoOptions::cases() as $userInfoTab)
          <div
            id="{{ $userInfoTab->value }}-content"
            x-cloak
            x-show="contentIsActive($el)"
            x-transition:enter.duration.300ms
            wire:key="{{ $userInfoTab->value }}-content"
          >
            <livewire:dynamic-component
              :is="$userInfoTab->livewireComponentName()"
              :user-id="$user->id"
              :key="$userInfoTab->value . '-content'"
            />
          </div>
        @endforeach
      </div>
    </div>
  </div>
</x-layouts.layout-main>
