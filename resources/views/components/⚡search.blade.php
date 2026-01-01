<?php

declare(strict_types=1);

use App\Models\Post;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public function render()
    {
        $results = collect();

        if (strlen($this->search) >= 2) {
            $results = Post::search($this->search)->take(10)->get();
        }

        return $this->view([
            'results' => $results,
        ]);
    }
};
?>

@script
<script>
    Alpine.data('globalSearchPart', () => ({
        searchBar: {
            isOpen: false
        },
        openSearchBar() {
            this.searchBar.isOpen = true;
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

<search x-data="globalSearchPart">
    {{-- 搜尋按鈕 --}}
    <button
        class="hidden gap-2 justify-between items-center p-2 text-sm rounded-lg cursor-pointer xl:flex group bg-zinc-200 text-zinc-500 dark:bg-zinc-600 dark:text-zinc-400"
        type="button"
        aria-label="Search"
        x-on:click="openSearchBar"
        x-on:keydown.window.prevent.cmd.k="openSearchBar"
        x-on:keydown.window.prevent.ctrl.k="openSearchBar"
        x-on:keydown.window.escape="searchBar.isOpen = false"
    >
        <x-icons.search
            class="transition duration-300 size-4 dark:group-hover:text-zinc-50 group-hover:text-zinc-900" />

        <span class="transition duration-300 dark:group-hover:text-zinc-50 group-hover:text-zinc-900">搜尋</span>

        <kbd
            class="inline-flex items-center py-1 px-2 rounded-sm font-noto-sans bg-zinc-300 dark:bg-zinc-500 dark:text-zinc-200"
            x-ref="searchShortcut"
            wire:ignore
        ></kbd>
    </button>

    {{-- search moodal --}}
    <div
        class="overflow-y-auto fixed inset-0 z-30"
        role="dialog"
        aria-labelledby="modal-title"
        aria-modal="true"
        x-cloak
        x-show="searchBar.isOpen"
    >
        <div class="flex justify-center items-end p-4 min-h-full text-center sm:items-center sm:p-0">

            {{-- modal --}}
            <div
                class="fixed inset-0 transition-opacity bg-zinc-500/75 backdrop-blur-sm"
                x-show="searchBar.isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-description="Background backdrop, show/hide based on modal state."
            ></div>

            {{-- search form --}}
            <div class="overflow-y-auto fixed inset-0 z-10">
                <div class="flex justify-center items-end p-4 min-h-full text-center sm:items-start sm:p-0">
                    <div
                        class="inline-block mt-16 w-full max-w-md transition-all"
                        x-show="searchBar.isOpen"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-on:click.outside="searchBar.isOpen = false"
                        x-trap.noscroll="searchBar.isOpen"
                    >
                        {{-- search form --}}
                        <div class="relative">
                            <label
                                class="hidden"
                                for="searchBox"
                            >搜尋</label>

                            <input
                                class="py-2 px-10 w-full text-xl rounded-xl border dark:placeholder-white focus:border-indigo-300 outline-hidden border-zinc-400 bg-zinc-50 placeholder-zinc-400 dark:bg-zinc-800 dark:text-zinc-50 focus:ring-3 focus:ring-indigo-200/50"
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
                                class="absolute top-3 right-3 w-5 h-5 text-zinc-700 dark:text-zinc-50"
                                wire:loading
                            />
                        </div>

                        {{-- 搜尋結果列表 --}}
                        @if (strlen($search) >= 2)
                            <div
                                class="p-2 mt-4 w-full rounded-xl bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-50"
                                wire:transition
                            >
                                @if ($results->count() > 0)
                                    <div class="flex justify-center items-center">搜尋結果</div>

                                    <hr class="my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

                                    <ul>
                                        @foreach ($results as $result)
                                            <li wire:key="search-result-{{ $result->id }}">
                                                <a class="flex items-start p-2 text-left rounded-md dark:text-zinc-50 dark:hover:bg-zinc-600 hover:bg-zinc-200"
                                                   href="{{ $result->link_with_slug }}"
                                                   wire:navigate
                                                >
                                                      <span class="flex items-center mr-2 h-lh">
                                                        <x-icons.caret-right class="w-4" />
                                                      </span>
                                                    {{ $result->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex justify-center items-center h-16">
                                        抱歉...找不到相關文章
                                    </div>
                                @endif

                                <hr class="my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

                                {{-- Algolia Logo --}}
                                <div class="flex justify-center items-center w-full">
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
