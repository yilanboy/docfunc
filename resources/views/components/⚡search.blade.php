<?php

declare(strict_types=1);

use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public string $search = '';

    public function render()
    {
        $results = collect();

        if (strlen($this->search) >= 2) {
            $results = Post::search($this->search)->take(10)->get();
        }

        return $this->view()->with([
            'results' => $results,
        ]);
    }
};
?>

@script
  <script>
    Alpine.data('search', () => ({
      searchBarIsOpen: false,
      openSearchBar() {
        this.searchBarIsOpen = true;
        this.$nextTick(() => {
          this.$refs.searchBar.focus();
        });
      },
      setShortcutKeyDisplayByOS() {
        let userAgentInfo = navigator.userAgent.toLowerCase();

        if (userAgentInfo.includes('mac')) {
          this.$refs.searchShortcut.textContent = '⌘ K';
        } else {
          this.$refs.searchShortcut.textContent = 'Ctrl K';
        }
      },
      init() {
        this.setShortcutKeyDisplayByOS();
      }
    }));
  </script>
@endscript

<search x-data="search">
  {{-- 搜尋按鈕 --}}
  <button
    class="group hidden cursor-pointer items-center justify-between gap-2 rounded-lg bg-zinc-200 p-2 text-sm text-zinc-500 xl:flex dark:bg-zinc-600 dark:text-zinc-400"
    type="button"
    aria-label="Search"
    x-on:click="openSearchBar"
    x-on:keydown.window.prevent.cmd.k="openSearchBar"
    x-on:keydown.window.prevent.ctrl.k="openSearchBar"
    x-on:keydown.window.escape="searchBarIsOpen = false"
  >
    <x-icons.search class="size-4 transition duration-300 group-hover:text-zinc-900 dark:group-hover:text-zinc-50" />

    <span class="transition duration-300 group-hover:text-zinc-900 dark:group-hover:text-zinc-50">搜尋</span>

    <kbd
      class="font-noto-sans inline-flex items-center rounded-sm bg-zinc-300 px-2 py-1 dark:bg-zinc-500 dark:text-zinc-200"
      x-ref="searchShortcut"
      wire:ignore
    ></kbd>
  </button>

  {{-- search moodal --}}
  <div
    class="fixed inset-0 z-30 overflow-y-auto"
    role="dialog"
    aria-labelledby="modal-title"
    aria-modal="true"
    x-cloak
    x-show="searchBarIsOpen"
  >
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

      {{-- modal --}}
      <div
        class="fixed inset-0 bg-zinc-500/75 backdrop-blur-sm transition-opacity"
        x-show="searchBarIsOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-description="Background backdrop, show/hide based on modal state."
      ></div>

      {{-- search form --}}
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-start sm:p-0">
          <div
            class="mt-16 inline-block w-full max-w-md transition-all"
            x-show="searchBarIsOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-on:click.outside="searchBarIsOpen = false"
            x-trap.noscroll="searchBarIsOpen"
          >
            {{-- search form --}}
            <div class="relative">
              <label
                class="hidden"
                for="searchBox"
              >搜尋</label>
              <input
                class="outline-hidden focus:ring-3 w-full rounded-xl border border-zinc-400 bg-zinc-50 px-10 py-2 text-xl placeholder-zinc-400 focus:border-indigo-300 focus:ring-indigo-200/50 dark:bg-zinc-800 dark:text-zinc-50 dark:placeholder-white"
                id="searchBox"
                type="text"
                x-ref="searchBar"
                wire:model.live.debounce.500ms="search"
                autocomplete="off"
                placeholder="搜尋文章"
              />

              <div class="absolute left-3 top-3.5 text-lg text-zinc-400 dark:text-zinc-50">
                <x-icons.search class="w-5" />
              </div>

              <x-icons.animate-spin
                class="absolute right-3 top-3 h-5 w-5 text-zinc-700 dark:text-zinc-50"
                wire:loading
              />
            </div>

            {{-- 搜尋結果列表 --}}
            @if (strlen($search) >= 2)
              <div
                class="mt-4 w-full rounded-xl bg-zinc-50 p-2 ring-1 ring-black/20 dark:bg-zinc-800 dark:text-zinc-50"
                wire:transition
              >
                @if ($results->count() > 0)
                  <div class="flex items-center justify-center">搜尋結果</div>

                  <hr class="my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

                  <ul>
                    @foreach ($results as $result)
                      <li>
                        <a
                          class="flex items-start rounded-md p-2 text-left hover:bg-zinc-200 dark:text-zinc-50 dark:hover:bg-zinc-600"
                          href="{{ $result->link_with_slug }}"
                          wire:navigate
                        >
                          <span class="mr-2 flex h-[1lh] items-center">
                            <x-icons.caret-right class="w-4" />
                          </span>
                          {{ $result->title }}
                        </a>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <div class="flex h-16 items-center justify-center">
                    抱歉...找不到相關文章
                  </div>
                @endif

                <hr class="my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

                {{-- Algolia Logo --}}
                <div class="flex w-full items-center justify-center">
                  <a
                    href="https://www.algolia.com"
                    target="_blank"
                    rel="nofollow noopener noreferrer"
                  >
                    {{-- Light Mode Algolia Logo --}}
                    <img
                      class="inline-block dark:hidden"
                      src="{{ asset('images/icon/search-by-algolia-light-background.png') }}"
                      alt="Search by Algolia"
                    >

                    {{-- Dark Mode Algolia Logo --}}
                    <img
                      class="hidden dark:inline-block"
                      src="{{ asset('images/icon/search-by-algolia-dark-background.png') }}"
                      alt="Search by Algolia"
                    >
                  </a>
                </div>
              </div>
            @endif

          </div>
        </div>
      </div>

    </div>
  </div>
</search>
