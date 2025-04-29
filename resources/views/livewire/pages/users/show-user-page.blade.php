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
    <div class="animate-fade-in flex flex-col items-center justify-start px-4">
      {{-- user information, posts and comments --}}
      <div
        class="w-full max-w-3xl"
        x-data="userShowTabs"
      >
        <nav
          class="relative z-0 mb-6 inline-grid w-full select-none grid-cols-3 items-center justify-center rounded-xl bg-zinc-300/50 p-1 text-zinc-500 dark:bg-zinc-500/30 dark:text-zinc-50"
        >
          @foreach (UserInfoOptions::cases() as $userInfoTab)
            <button
              class="relative z-20 inline-flex w-full cursor-pointer items-center justify-center whitespace-nowrap px-4 py-2 text-sm font-medium transition-all"
              id="{{ $userInfoTab->value }}-tab-button"
              type="button"
              x-on:click="tabButtonClicked($el)"
              {{-- update url query parameter in livewire --}}
              wire:click="changeTab('{{ $userInfoTab }}')"
              wire:key="{{ $userInfoTab->value }}-tab-button"
            >
              <x-dynamic-component
                class="w-4"
                :component="$userInfoTab->iconComponentName()"
              />
              <span class="ml-2">{{ $userInfoTab->label() }}</span>
            </button>
          @endforeach

          <div
            class="absolute left-0 z-10 h-full w-1/2 duration-300 ease-out"
            x-ref="tabMarker"
            x-cloak
          >
            <div class="h-full w-full rounded-[calc(var(--radius-xl)-(--spacing(1)))] bg-zinc-50 dark:bg-zinc-800">
            </div>
          </div>
        </nav>

        @foreach (UserInfoOptions::cases() as $userInfoTab)
          <div
            id="{{ $userInfoTab->value }}-content"
            x-cloak
            x-show="tabContentActive($el)"
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
