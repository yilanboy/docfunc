@php
  use App\Enums\UserInfoTab;
@endphp

@assets
  {{-- highlight code block style --}}
  @vite('node_modules/highlight.js/styles/atom-one-dark.css')
  <style>
    .dark .hljs {
      background: #171717;
    }
  </style>

  {{-- highlight code block --}}
  @vite('resources/ts/highlight.ts')
@endassets

@script
  <script>
    // tab can only be 'information', 'posts', 'comments'
    Alpine.data('userShowTabs', () => ({
      tabSelected: @js($tabSelected),
      tabButtonClicked(tabButton) {
        this.tabSelected = tabButton.id.replace('-tab-button', '');
        this.tabRepositionMarker(tabButton);
      },
      tabRepositionMarker(tabButton) {
        this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
        this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
        this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
      },
      tabContentActive(tabContent) {
        return this.tabSelected === tabContent.id.replace('-content', '');
      },
      init() {
        let tabSelectedButtons = document.getElementById(this.tabSelected + '-tab-button');
        this.tabRepositionMarker(tabSelectedButtons);
      }
    }));
  </script>
@endscript

{{-- user information page --}}
<x-layouts.layout-main>
  <div class="container mx-auto flex-1">
    <div class="flex animate-fade-in flex-col items-center justify-start px-4">
      {{-- user information, posts and comments --}}
      <div
        class="w-full max-w-3xl"
        x-data="userShowTabs"
      >
        <div
          class="relative z-0 mb-6 inline-grid h-10 w-full select-none grid-cols-3 items-center justify-center rounded-lg border border-gray-100 bg-white p-1 text-gray-500 dark:border-gray-800 dark:bg-gray-700 dark:text-gray-50"
        >
          @foreach (UserInfoTab::cases() as $userInfoTab)
            <button
              class="relative z-20 inline-flex h-8 w-full cursor-pointer items-center justify-center whitespace-nowrap rounded-md px-3 text-sm font-medium transition-all"
              id="{{ $userInfoTab->value }}-tab-button"
              type="button"
              x-on:click="tabButtonClicked($el)"
              {{-- update url query parameter in livewire --}}
              wire:click="changeTab('{{ $userInfoTab }}')"
              wire:key="{{ $userInfoTab->value . '-tab-button' }}"
            >
              <x-dynamic-component
                class="w-4"
                :component="$userInfoTab->iconComponentName()"
              />
              <span class="ml-2">{{ $userInfoTab->tabText() }}</span>
            </button>
          @endforeach

          <div
            class="absolute left-0 z-10 h-full w-1/2 duration-300 ease-out"
            x-ref="tabMarker"
            x-cloak
          >
            <div class="h-full w-full rounded-md bg-gray-100 dark:bg-gray-800"></div>
          </div>
        </div>

        @foreach (UserInfoTab::cases() as $userInfoTab)
          <div
            id="{{ $userInfoTab->value }}-content"
            x-cloak
            x-show="tabContentActive($el)"
            x-transition:enter.duration.300ms
            wire:key="{{ $userInfoTab->value . '-content' }}"
          >
            <livewire:dynamic-component
              :key="$userInfoTab->value . '-content'"
              :is="$userInfoTab->livewireComponentName()"
              :user-id="$userId"
            />
          </div>
        @endforeach
      </div>
    </div>
  </div>
</x-layouts.layout-main>