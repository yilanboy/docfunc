<?php

declare(strict_types=1);

use App\Livewire\Actions\Logout;
use App\Models\Category;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

new class extends Component
{
    public Collection $categories;

    public bool $showRegisterButton;

    public function mount(): void
    {
        $this->categories = Cache::remember('categories', now()->addDay(), function () {
            return Category::all(['id', 'name', 'icon']);
        });

        $this->showRegisterButton = SettingService::isRegisterAllowed();
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->dispatch('toast', status: 'success', message: '成功登出！');

        $this->redirect(route('login'), navigate: true);
    }
};
?>

@script
<script>
    Alpine.data('layoutsHeaderPart', () => ({
        html: document.documentElement,
        // the dropdown only shows in mobile
        dropdownMenuIsOpen: false,
        profileMenuIsOpen: false,
        switchTheme() {
            if (this.html.getAttribute('data-theme') === 'light') {
                this.html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                this.html.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        }
    }));
</script>
@endscript

@php
    $hasUnreadNotifications = auth()->check() && auth()->user()->unreadNotifications()->exists();
@endphp

<header
    class="z-20 mb-6"
    id="header"
    x-data="layoutsHeaderPart"
>
    <div
        class="hidden relative justify-center items-center w-full transition-all duration-300 lg:flex h-18 bg-zinc-50 dark:bg-zinc-800"
        id="desktop-header"
    >
        {{-- logo --}}
        <a
            class="flex absolute left-4 inset-y-1/2 items-center"
            href="{{ route('root') }}"
            wire:navigate
        >
            <img
                class="block dark:hidden size-8"
                src="{{ asset('images/icon/logo.svg') }}"
                alt="logo"
            >
            <img
                class="hidden dark:block size-8"
                src="{{ asset('images/icon/dark-logo.svg') }}"
                alt="logo"
            >
            <span class="ml-3 font-mono text-2xl font-bold dark:text-zinc-50">{{ config('app.name') }}</span>
        </a>

        <nav class="flex space-x-6">
            <x-skew-underline-link
                :link="route('posts.index')"
                {{-- make sure both url are decode in aws lambda --}}
                :selected="urldecode(request()->url()) === urldecode(route('posts.index'))"
            >
                全部文章
            </x-skew-underline-link>

            @foreach ($categories as $category)
                <x-skew-underline-link
                    :link="$category->link_with_name"
                    :selected="urldecode(request()->url()) === urldecode($category->link_with_name)"
                    :icon="$category->icon"
                    wire:key="category-{{ $category->id }}"
                >
                    {{ $category->name }}
                </x-skew-underline-link>
            @endforeach
        </nav>

        <div class="flex absolute right-6 inset-y-1/2 items-center space-x-5">

            {{-- search --}}
            <livewire:search />

            {{-- light / dark mode toggle --}}
            <button
                class="flex overflow-hidden relative justify-center items-center cursor-pointer group size-12"
                type="button"
                aria-label="Toggle Dark Mode"
                x-on:click="switchTheme"
            >
                <x-icons.sun
                    class="absolute top-3 inset-x-auto text-amber-400 transition-all duration-500 dark:top-full group-hover:text-amber-500 size-6"
                />

                <x-icons.moon-stars
                    class="absolute inset-x-auto -top-full transition-all duration-500 dark:top-3 size-6 text-[#f6f1d5] group-hover:text-[#ddd8bf]"
                />
            </button>

            @guest
                @if ($showRegisterButton)
                    <a
                        class="flex justify-center items-center px-3 h-10 text-gray-600 bg-transparent rounded-lg border-2 border-gray-600 transition duration-150 hover:bg-gray-600 hover:border-transparent hover:text-zinc-50"
                        href="{{ route('register') }}"
                        wire:navigate
                    >
                        註冊
                    </a>
                @endif

                <a
                    class="flex justify-center items-center px-3 h-10 text-emerald-600 bg-transparent rounded-lg border-2 border-emerald-600 transition duration-150 hover:bg-emerald-600 hover:border-transparent hover:text-zinc-50"
                    href="{{ route('login') }}"
                    wire:navigate
                >
                    <x-icons.door-open class="w-5" />
                    <span class="ml-2">登入</span>
                </a>
            @endguest

            @auth
                {{-- notification --}}
                <span class="inline-flex relative rounded-md">
          <a
              class="flex justify-center items-center text-xl rounded-lg transition duration-150 size-12 text-zinc-500 dark:text-zinc-400 dark:hover:text-zinc-50 hover:text-zinc-900"
              href="{{ route('notifications.index') }}"
              aria-label="Notifications"
              wire:navigate
          >
            <x-icons.bell class="size-6" />
          </a>

          @if ($hasUnreadNotifications)
                        <span class="flex absolute top-2 right-2 -mt-1 -mr-1 w-3 h-3">
              <span class="inline-flex absolute w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
              <span class="inline-flex relative w-3 h-3 bg-red-500 rounded-full"></span>
            </span>
                    @endif
        </span>

                <div class="flex relative justify-center items-center">
                    {{-- headshot --}}
                    <div>
                        <button
                            class="flex text-sm rounded-full cursor-pointer focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400 bg-zinc-800 focus:outline-hidden"
                            id="desktop-user-menu-button"
                            type="button"
                            x-on:click="profileMenuIsOpen = !profileMenuIsOpen"
                            x-on:keydown.escape.window="profileMenuIsOpen = false"
                        >
                            <span class="sr-only">Open user menu</span>
                            <img
                                class="rounded-full size-12"
                                src="{{ auth()->user()->gravatar_url }}"
                                alt=""
                            >
                        </button>
                    </div>

                    {{-- profile menu --}}
                    <x-dropdown.menu
                        class="absolute right-0 top-16"
                        x-cloak=""
                        x-show="profileMenuIsOpen"
                        x-on:click.outside="profileMenuIsOpen = false"
                        x-transition.origin.top.right=""
                    >
                        <x-dropdown.link href="{{ route('posts.create') }}">
                            <x-icons.pencil class="w-4" />
                            <span class="ml-2">新增文章</span>
                        </x-dropdown.link>

                        <x-dropdown.link href="{{ route('users.show', ['id' => auth()->id()]) }}">
                            <x-icons.info-circle class="w-4" />
                            <span class="ml-2">個人資訊</span>
                        </x-dropdown.link>

                        <x-dropdown.link href="{{ route('settings.users.edit', ['id' => auth()->id()]) }}">
                            <x-icons.geer-fill class="w-4" />
                            <span class="ml-2">設定</span>
                        </x-dropdown.link>

                        <x-dropdown.button
                            wire:confirm="你確定要登出嗎？"
                            wire:click="logout"
                        >
                            <x-icons.box-arrow-left class="w-4" />
                            <span class="ml-2">登出</span>
                        </x-dropdown.button>
                    </x-dropdown.menu>
                </div>
            @endauth
        </div>
    </div>

    <div
        class="lg:hidden bg-zinc-50 dark:bg-zinc-800"
        id="mobile-header"
    >
        <div class="px-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex relative justify-between items-center h-[4.5rem]">
                <div class="flex absolute inset-y-0 left-0 items-center">
                    {{-- category dropdown menu toggle --}}
                    <button
                        class="inline-flex justify-center items-center p-2 rounded-md text-zinc-700"
                        type="button"
                        aria-controls="mobile-menu"
                        aria-expanded="false"
                        x-on:click="dropdownMenuIsOpen = !dropdownMenuIsOpen"
                    >
                        <span class="sr-only">Open main menu</span>
                        {{-- close category dropdown menu icon --}}
                        <div
                            class="text-3xl text-zinc-500 dark:text-zinc-400 dark:hover:text-zinc-50 hover:text-zinc-900"
                            x-cloak
                            x-show="!dropdownMenuIsOpen"
                        >
                            <x-icons.list class="w-7" />
                        </div>
                        {{-- open category dropdown menu icon --}}
                        <div
                            class="text-xl text-zinc-500 dark:text-zinc-400 dark:hover:text-zinc-50 hover:text-zinc-900"
                            x-cloak
                            x-show="dropdownMenuIsOpen"
                        >
                            <x-icons.x class="w-7" />
                        </div>
                    </button>
                </div>

                <div class="hidden items-center mx-auto md:flex">
                    <img
                        class="block dark:hidden size-10"
                        src="{{ asset('images/icon/logo.svg') }}"
                        alt="logo"
                    >
                    <img
                        class="hidden dark:block size-10"
                        src="{{ asset('images/icon/dark-logo.svg') }}"
                        alt="logo"
                    >
                    <span class="ml-3 font-mono text-xl font-bold dark:text-zinc-50">
            {{ config('app.name') }}
          </span>
                </div>

                <div class="flex absolute inset-y-0 right-0 items-center space-x-8">
                    {{-- light / dark mode toggle --}}
                    <button
                        type="button"
                        aria-label="Toggle Dark Mode"
                        x-on:click="switchTheme"
                    >
                        <x-icons.sun class="w-5 text-amber-400 dark:hidden hover:text-amber-500" />

                        <x-icons.moon-stars class="hidden w-5 dark:block text-[#f6f1d5] hover:text-[#ddd8bf]" />
                    </button>

                    @guest
                        @if ($showRegisterButton)
                            <a
                                class="py-2 px-4 text-gray-400 bg-transparent rounded-md border-2 border-gray-400 hover:bg-gray-400 hover:border-transparent hover:text-zinc-50"
                                href="{{ route('register') }}"
                                wire:navigate
                            >
                                註冊
                            </a>
                        @endif

                        <a
                            class="py-2 px-4 text-emerald-600 bg-transparent rounded-md border-2 border-emerald-600 hover:bg-emerald-600 hover:border-transparent hover:text-zinc-50"
                            href="{{ route('login') }}"
                            wire:navigate
                        >
                            登入
                        </a>
                    @endguest

                    @auth
                        {{-- notification --}}
                        <div class="inline-flex relative rounded-md">
                            <a
                                class="rounded-full text-zinc-500 dark:text-zinc-400 dark:hover:text-zinc-50 hover:text-zinc-900"
                                href="{{ route('notifications.index') }}"
                                wire:navigate
                            >
                                <x-icons.bell class="w-5" />
                            </a>

                            @if ($hasUnreadNotifications)
                                <span class="flex absolute top-2 right-2 -mt-1 -mr-1 w-3 h-3">
                  <span
                      class="inline-flex absolute w-full h-full bg-red-400 rounded-full opacity-75 animate-ping"></span>
                  <span class="inline-flex relative w-3 h-3 bg-red-500 rounded-full"></span>
                </span>
                            @endif
                        </div>

                        <div class="relative">
                            {{-- headshot --}}
                            <div>
                                <button
                                    class="flex text-sm rounded-full focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400 bg-zinc-800 focus:outline-hidden"
                                    id="mobile-user-menu-button"
                                    type="button"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    x-on:click="profileMenuIsOpen = !profileMenuIsOpen"
                                    x-on:keydown.escape.window="profileMenuIsOpen = false"
                                >
                                    <span class="sr-only">Open user menu</span>
                                    <img
                                        class="rounded-full size-10"
                                        src="{{ auth()->user()->gravatar_url }}"
                                        alt=""
                                    >
                                </button>
                            </div>

                            {{-- profile menu --}}
                            <x-dropdown.menu
                                class="absolute right-0 top-12"
                                x-cloak=""
                                x-show="profileMenuIsOpen"
                                x-on:click.outside="profileMenuIsOpen = false"
                                x-transition.origin.top.right=""
                            >
                                <x-dropdown.link href="{{ route('posts.create') }}">
                                    <x-icons.pencil class="w-4" />
                                    <span class="ml-2">新增文章</span>
                                </x-dropdown.link>

                                <x-dropdown.link href="{{ route('users.show', ['id' => auth()->id()]) }}">
                                    <x-icons.info-circle class="w-4" />
                                    <span class="ml-2">個人資訊</span>
                                </x-dropdown.link>

                                <x-dropdown.link href="{{ route('settings.users.edit', ['id' => auth()->id()]) }}">
                                    <x-icons.geer-fill class="w-4" />
                                    <span class="ml-2">設定</span>
                                </x-dropdown.link>

                                <x-dropdown.button
                                    wire:confirm="你確定要登出嗎？"
                                    wire:click="logout"
                                >
                                    <x-icons.box-arrow-left class="w-4" />
                                    <span class="ml-2">登出</span>
                                </x-dropdown.button>
                            </x-dropdown.menu>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        {{-- category dropdown menu --}}
        <nav
            class="px-2 pt-2 pb-3 space-y-1 lg:hidden"
            x-cloak
            x-show="dropdownMenuIsOpen"
            x-collapse
        >

            @php
                $inIndexPage = urldecode(request()->url()) === urldecode(route('posts.index'));
            @endphp

            <a
                href="{{ route('posts.index') }}"
                @if ($inIndexPage) aria-current="page" @endif
                @class([
                    'flex items-center px-3 py-2 rounded-md font-medium',
                    'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-50' => $inIndexPage,
                    'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-zinc-50' => !$inIndexPage,
                ])
                wire:navigate
            >
                <x-icons.home class="w-4" />
                <span class="ml-2">全部文章</span>
            </a>

            @foreach ($categories as $category)
                @php
                    $inCategoryPage = urldecode(request()->url()) === urldecode($category->link_with_name);
                @endphp
                <a
                    href="{{ $category->link_with_name }}"
                    @if ($inCategoryPage) aria-current="page" @endif
                    @class([
                        'block px-3 py-2 rounded-md font-medium flex items-center',
                        'bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-50' => $inCategoryPage,
                        'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-zinc-50' => !$inCategoryPage,
                    ])
                    wire:navigate
                >
                    <div class="w-4">{!! $category->icon !!}</div>
                    <span class="ml-2">{{ $category->name }}</span>
                </a>
            @endforeach
        </nav>
    </div>
</header>
