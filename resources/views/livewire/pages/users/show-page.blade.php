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
      tab: '',
      contentIsActive(content) {
        return this.tab === content.id.replace('-content', '');
      },
      tabButtonClicked(tabButton) {
        this.tabRepositionMarker(tabButton);
        this.tab = tabButton.id.replace('-tab-button', '');

        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        urlParams.set('tab', this.tab);

        window.history.replaceState(null, null, '?' + urlParams.toString());
      },
      tabRepositionMarker(tabButton) {
        this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
        this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
        this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
      },
      init() {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        this.tab = urlParams.get('tab') ?? 'information'

        const tabSelectedButtons = document.getElementById(this.tab + '-tab-button');
        this.tabRepositionMarker(tabSelectedButtons);
      }
    }));
  </script>
@endscript

{{-- user information page --}}
<x-layouts.layout-main>
  <div
    class="container mx-auto grow"
    x-data="showUserPage"
  >
    <div class="animate-fade-in flex flex-col items-center justify-start px-4">
      {{-- user information, posts and comments --}}
      <div class="w-full max-w-3xl">
        <x-tabs.nav class="mb-6">
          @foreach (UserInfoOptions::cases() as $userInfoTab)
            <x-tabs.button
              id="{{ $userInfoTab->value . '-tab-button' }}"
              x-on:click="tabButtonClicked($el)"
              wire:key="{{ $userInfoTab->value }}-tab-button"
            >
              <x-dynamic-component
                class="w-3"
                :component="$userInfoTab->iconComponentName()"
              />
              <span>{{ $userInfoTab->label() }}</span>
            </x-tabs.button>
          @endforeach

          <x-tabs.tab-marker
            x-ref="tabMarker"
            x-cloak
          />
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
