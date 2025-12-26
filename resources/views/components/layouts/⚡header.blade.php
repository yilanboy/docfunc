<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Livewire\Actions\Logout;
use App\Models\Category;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

new class extends Component {
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

@assets
  <style>
    :root {
      --animation-duration: 1s;
      --circle-in-start-x: right;
      --circle-in-start-y: top;
    }

    /* view transition */
    ::view-transition-old(root) {
      animation-delay: var(--animation-duration);
    }

    ::view-transition-new(root) {
      animation: circle-in var(--animation-duration);
    }

    @keyframes circle-in {
      from {
        clip-path: circle(5% at var(--circle-in-start-x) var(--circle-in-start-y));
      }

      to {
        clip-path: circle(100% at 50% 50%);
      }
    }
  </style>
@endassets

@script
  <script>
    Alpine.data('layoutsHeaderPart', () => ({
      html: document.documentElement,
      // the dropdown only shows in mobile
      dropdownMenuIsOpen: false,
      profileMenuIsOpen: false,
      switchTheme() {
        const rect = this.$el.getBoundingClientRect();

        const centerX = rect.left + (rect.width / 2);
        const centerY = rect.top + (rect.height / 2);

        const root = document.documentElement;
        root.style.setProperty('--circle-in-start-x', `${centerX}px`);
        root.style.setProperty('--circle-in-start-y', `${centerY}px`);

        const updateTheme = () => {
          if (this.html.getAttribute('data-theme') === 'light') {
            this.html.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
          } else {
            this.html.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
          }
        }

        if (!document.startViewTransition) {
          updateTheme()

          return
        }

        document.startViewTransition(() => {
          updateTheme()
        });
      }
    }));
  </script>
@endscript

<header
  class="z-20 mb-6"
  id="header"
  x-data="layoutsHeaderPart"
>
  <div
    class="relative hidden h-20 w-full items-center justify-center bg-zinc-50 transition-all duration-300 lg:flex dark:bg-zinc-800"
    id="desktop-header"
  >
    {{-- logo --}}
    <a
      class="absolute inset-y-1/2 left-4 flex items-center"
      href="{{ route('root') }}"
      wire:navigate
    >
      <img
        class="block size-8 dark:hidden"
        src="{{ asset('images/icon/logo.svg') }}"
        alt="logo"
      >
      <img
        class="hidden size-8 dark:block"
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

    <div class="absolute inset-y-1/2 right-6 flex items-center space-x-5">

      {{-- search --}}
      <livewire:search />

      {{-- light / dark mode toggle --}}
      <button
        class="group relative flex size-12 cursor-pointer items-center justify-center overflow-hidden"
        type="button"
        aria-label="Toggle Dark Mode"
        x-on:click="switchTheme"
      >
        <x-icons.sun
          class="absolute inset-x-auto top-3 size-6 text-amber-400 transition-all duration-500 group-hover:text-amber-500 dark:top-full"
        />

        <x-icons.moon-stars
          class="absolute inset-x-auto -top-full size-6 text-[#f6f1d5] transition-all duration-500 group-hover:text-[#ddd8bf] dark:top-3"
        />
      </button>

      @guest
        @if ($showRegisterButton)
          <a
            class="flex h-10 items-center justify-center rounded-lg border-2 border-gray-600 bg-transparent px-3 text-gray-600 transition duration-150 hover:border-transparent hover:bg-gray-600 hover:text-zinc-50"
            href="{{ route('register') }}"
            wire:navigate
          >
            註冊
          </a>
        @endif

        <a
          class="flex h-10 items-center justify-center rounded-lg border-2 border-emerald-600 bg-transparent px-3 text-emerald-600 transition duration-150 hover:border-transparent hover:bg-emerald-600 hover:text-zinc-50"
          href="{{ route('login') }}"
          wire:navigate
        >
          <x-icons.door-open class="w-5" />
          <span class="ml-2">登入</span>
        </a>
      @endguest

      @auth
        {{-- notification --}}
        <span class="relative inline-flex rounded-md">
          <a
            class="flex size-12 items-center justify-center rounded-lg text-xl text-zinc-500 transition duration-150 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
            href="{{ route('notifications.index') }}"
            aria-label="Notifications"
            wire:navigate
          >
            <x-icons.bell class="size-6" />
          </a>

          @if (auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute right-2 top-2 -mr-1 -mt-1 flex h-3 w-3">
              <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex h-3 w-3 rounded-full bg-red-500"></span>
            </span>
          @endif
        </span>

        <div class="relative flex items-center justify-center">
          {{-- headshot --}}
          <div>
            <button
              class="focus:outline-hidden flex cursor-pointer rounded-full bg-zinc-800 text-sm focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400"
              id="desktop-user-menu-button"
              type="button"
              x-on:click="profileMenuIsOpen = !profileMenuIsOpen"
              x-on:keydown.escape.window="profileMenuIsOpen = false"
            >
              <span class="sr-only">Open user menu</span>
              <img
                class="size-12 rounded-full"
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
    class="bg-zinc-50 lg:hidden dark:bg-zinc-800"
    id="mobile-header"
  >
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
      <div class="relative flex h-[4.5rem] items-center justify-between">
        <div class="absolute inset-y-0 left-0 flex items-center">
          {{-- category dropdown menu toggle --}}
          <button
            class="inline-flex items-center justify-center rounded-md p-2 text-zinc-700"
            type="button"
            aria-controls="mobile-menu"
            aria-expanded="false"
            x-on:click="dropdownMenuIsOpen = !dropdownMenuIsOpen"
          >
            <span class="sr-only">Open main menu</span>
            {{-- close category dropdown menu icon --}}
            <div
              class="text-3xl text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
              x-cloak
              x-show="!dropdownMenuIsOpen"
            >
              <x-icons.list class="w-7" />
            </div>
            {{-- open category dropdown menu icon --}}
            <div
              class="text-xl text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
              x-cloak
              x-show="dropdownMenuIsOpen"
            >
              <x-icons.x class="w-7" />
            </div>
          </button>
        </div>

        <div class="mx-auto hidden items-center md:flex">
          <img
            class="block size-10 dark:hidden"
            src="{{ asset('images/icon/logo.svg') }}"
            alt="logo"
          >
          <img
            class="hidden size-10 dark:block"
            src="{{ asset('images/icon/dark-logo.svg') }}"
            alt="logo"
          >
          <span class="ml-3 font-mono text-xl font-bold dark:text-zinc-50">
            {{ config('app.name') }}
          </span>
        </div>

        <div class="absolute inset-y-0 right-0 flex items-center space-x-8">
          {{-- light / dark mode toggle --}}
          <button
            type="button"
            aria-label="Toggle Dark Mode"
            x-on:click="switchTheme"
          >
            <x-icons.sun class="w-5 text-amber-400 hover:text-amber-500 dark:hidden" />

            <x-icons.moon-stars class="hidden w-5 text-[#f6f1d5] hover:text-[#ddd8bf] dark:block" />
          </button>

          @guest
            @if ($showRegisterButton)
              <a
                class="rounded-md border-2 border-gray-400 bg-transparent px-4 py-2 text-gray-400 hover:border-transparent hover:bg-gray-400 hover:text-zinc-50"
                href="{{ route('register') }}"
                wire:navigate
              >
                註冊
              </a>
            @endif

            <a
              class="rounded-md border-2 border-emerald-600 bg-transparent px-4 py-2 text-emerald-600 hover:border-transparent hover:bg-emerald-600 hover:text-zinc-50"
              href="{{ route('login') }}"
              wire:navigate
            >
              登入
            </a>
          @endguest

          @auth
            {{-- notification --}}
            <div class="relative inline-flex rounded-md">
              <a
                class="rounded-full text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
                href="{{ route('notifications.index') }}"
                wire:navigate
              >
                <x-icons.bell class="w-5" />
              </a>

              @if (auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute right-2 top-2 -mr-1 -mt-1 flex h-3 w-3">
                  <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                  <span class="relative inline-flex h-3 w-3 rounded-full bg-red-500"></span>
                </span>
              @endif
            </div>

            <div class="relative">
              {{-- headshot --}}
              <div>
                <button
                  class="focus:outline-hidden flex rounded-full bg-zinc-800 text-sm focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400"
                  id="mobile-user-menu-button"
                  type="button"
                  aria-expanded="false"
                  aria-haspopup="true"
                  x-on:click="profileMenuIsOpen = !profileMenuIsOpen"
                  x-on:keydown.escape.window="profileMenuIsOpen = false"
                >
                  <span class="sr-only">Open user menu</span>
                  <img
                    class="size-10 rounded-full"
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
      class="space-y-1 px-2 pb-3 pt-2 lg:hidden"
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
