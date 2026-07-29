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
                isLoading: false,
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

                this.$wire.search(this.searchBox.query).then((data) => {
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
            },
        }));
    </script>
@endscript

<search x-data="globalSearch">
    {{-- 搜尋按鈕 --}}
    <button
        id="search-button"
        class="group hidden cursor-pointer items-center justify-between gap-2 rounded-lg bg-zinc-200 p-2 text-sm text-zinc-500 xl:flex dark:bg-zinc-600 dark:text-zinc-400"
        type="button"
        aria-label="Search"
        x-on:click="openSearchBox"
        x-on:keydown.window.prevent.cmd.k="openSearchBox"
        x-on:keydown.window.prevent.ctrl.k="openSearchBox"
        x-on:keydown.window.escape="searchBox.isOpen = false"
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
        x-show="searchBox.isOpen"
    >
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            {{-- modal --}}
            <div
                class="fixed inset-0 bg-zinc-500/75 backdrop-blur-sm transition-opacity"
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
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-start sm:p-0">
                    <div
                        class="mt-16 inline-block w-full max-w-lg transition-all"
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
                            <label class="hidden" for="searchBox">搜尋</label>

                            <input
                                class="w-full rounded-xl border border-zinc-400 bg-zinc-50 px-10 py-2 text-xl placeholder-zinc-400 outline-hidden focus:border-indigo-300 focus:ring-3 focus:ring-indigo-200/50 dark:bg-zinc-800 dark:text-zinc-50 dark:placeholder-white"
                                id="search-box"
                                type="text"
                                x-ref="searchBox"
                                x-model="searchBox.query"
                                x-on:input.debounce.750ms="onSearchBoxInput"
                                autocomplete="off"
                                placeholder="搜尋文章"
                            />

                            <div class="absolute top-3.5 left-3 text-lg text-zinc-400 dark:text-zinc-50">
                                <x-icons.search class="w-5" />
                            </div>

                            <x-icons.animate-spin
                                class="absolute top-3 right-3 h-5 w-5 text-zinc-700 dark:text-zinc-50"
                                wire:loading
                            />
                        </div>

                        {{-- 搜尋結果列表 --}}
                        <div
                            id="search-result"
                            x-cloak
                            x-show="searchBox.query.length >= 2 && searchBox.isLoading === false"
                            class="mt-4 w-full rounded-xl bg-zinc-50 p-2 dark:bg-zinc-800 dark:text-zinc-50"
                        >
                            <div x-cloak x-show="posts.length > 0">
                                <div class="flex items-center justify-center">搜尋結果</div>

                                <hr class="mx-4 my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700" />

                                <ul>
                                    <template x-for="post in posts" x-bind:key="post.id">
                                        <li>
                                            <a
                                                class="flex items-start rounded-md p-2 text-left hover:bg-zinc-200 dark:text-zinc-50 dark:hover:bg-zinc-600"
                                                x-bind:href="post.link_with_slug"
                                                wire:navigate
                                            >
                                                <span class="mr-2 flex h-lh items-center">
                                                    <x-icons.caret-right class="w-4" />
                                                </span>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-base font-semibold" x-text="post.title"></span>
                                                    <span
                                                        class="line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400"
                                                        x-show="post.excerpt"
                                                        x-text="post.excerpt"
                                                    ></span>
                                                </div>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <div x-cloak x-show="posts.length === 0">
                                <div class="flex h-16 items-center justify-center">
                                    <span>抱歉... 找不到 "</span>
                                    <span class="font-semibold" x-text="searchBox.query"></span>
                                    <span>" 的相關文章</span>
                                </div>
                            </div>

                            <hr class="mx-4 my-2 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700" />

                            {{-- Algolia Logo --}}
                            <div class="flex w-full items-center justify-center">
                                <a href="https://www.algolia.com" target="_blank" rel="nofollow noopener noreferrer">
                                    {{-- Light Mode Algolia Logo --}}
                                    <img
                                        class="inline-block dark:hidden"
                                        src="{{ asset('images/icon/search-by-algolia-light-background.png') }}"
                                        alt="Search by Algolia"
                                    />

                                    {{-- Dark Mode Algolia Logo --}}
                                    <img
                                        class="hidden dark:inline-block"
                                        src="{{ asset('images/icon/search-by-algolia-dark-background.png') }}"
                                        alt="Search by Algolia"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</search>
