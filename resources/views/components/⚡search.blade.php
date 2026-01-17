<?php

declare(strict_types=1);

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Json;
use Livewire\Component;

new class extends Component
{
    #[Json]
    public function search(string $query): Collection
    {
        return Post::search($query)
            ->take(10)
            ->get();
    }
};
?>

@script
<script>
    Alpine.data('globalSearch', () => ({
        searchBox: {
            isOpen: false,
            query: '',
            isLoading: false
        },
        posts: [],
        openSearchBox() {
            this.searchBox.isOpen = true;
            this.$nextTick(() => {
                this.$refs.searchBox.focus();
            });
        },
        onSearchBoxInput() {
            if (this.searchBox.query.length < 2) {
                this.posts = [];

                return;
            }

            this.$wire.search(this.searchBox.query)
                .then(data => {
                    this.posts = data;
                    this.searchBox.isLoading = false;
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

            // when the query is changed, into the loading state immediately
            this.$watch('searchBox.query', () => {
                this.searchBox.isLoading = true;
            });
        }
    }));
</script>
@endscript

<search x-data="globalSearch">
    {{-- 搜尋按鈕 --}}
    <button
        id="search-button"
        class="hidden gap-2 justify-between items-center p-2 text-sm rounded-lg cursor-pointer xl:flex group bg-zinc-200 text-zinc-500 dark:bg-zinc-600 dark:text-zinc-400"
        type="button"
        aria-label="Search"
        x-on:click="openSearchBox"
        x-on:keydown.window.prevent.cmd.k="openSearchBox"
        x-on:keydown.window.prevent.ctrl.k="openSearchBox"
        x-on:keydown.window.escape="searchBox.isOpen = false"
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
        x-show="searchBox.isOpen"
    >
        <div class="flex justify-center items-end p-4 min-h-full text-center sm:items-center sm:p-0">

            {{-- modal --}}
            <div
                class="fixed inset-0 transition-opacity bg-zinc-500/75 backdrop-blur-sm"
                x-show="searchBox.isOpen"
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
                        x-show="searchBox.isOpen"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-on:click.outside="searchBox.isOpen = false"
                        x-trap.noscroll="searchBox.isOpen"
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
                                x-ref="searchBox"
                                x-model="searchBox.query"
                                x-on:input.debounce.750ms="onSearchBoxInput"
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
                        <div
                            x-cloak
                            x-show="searchBox.query.length >= 2 && searchBox.isLoading === false"
                            class="p-2 mt-4 w-full rounded-xl bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-50"
                        >
                            <div
                                x-cloak
                                x-show="posts.length > 0"
                            >
                                <div class="flex justify-center items-center">搜尋結果</div>

                                <hr class="my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

                                <ul>
                                    <template x-for="post in posts" x-bind:key="post.id">
                                        <li>
                                            <a class="flex items-start p-2 text-left rounded-md dark:text-zinc-50 dark:hover:bg-zinc-600 hover:bg-zinc-200"
                                               x-bind:href="post.link_with_slug"
                                               wire:navigate
                                            >
                                                <span class="flex items-center mr-2 h-lh">
                                                    <x-icons.caret-right class="w-4" />
                                                </span>
                                                <span x-text="post.title"></span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <div x-cloak x-show="posts.length === 0">
                                <div class="flex justify-center items-center h-16">
                                    <span>抱歉... 找不到 "</span>
                                    <span class="font-semibold" x-text="searchBox.query"></span>
                                    <span>" 的相關文章</span>
                                </div>
                            </div>

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

                    </div>
                </div>
            </div>

        </div>
    </div>
</search>
