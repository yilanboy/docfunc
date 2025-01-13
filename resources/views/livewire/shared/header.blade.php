@script
  <script>
    Alpine.data('header', () => ({
      html: document.documentElement,
      // dropdown only show in mobile
      dropdownMenuIsOpen: false,
      profileMenuIsOpen: false,
      switchTheme() {
        if (this.html.classList.contains('dark')) {
          this.html.classList.remove('dark');
          localStorage.setItem('mode', 'light');
        } else {
          this.html.classList.add('dark');
          localStorage.setItem('mode', 'dark');
        }
      },
      toggleDropdownMenu() {
        this.dropdownMenuIsOpen = !this.dropdownMenuIsOpen;
      },
      dropdownMenuIsClose() {
        return this.dropdownMenuIsOpen !== true;
      },
      closeDropdownMenu() {
        this.dropdownMenuIsOpen = false;
      },
      toggleProfileMenu() {
        this.profileMenuIsOpen = !this.profileMenuIsOpen;
      },
      closeProfileMenu() {
        this.profileMenuIsOpen = false;
      }
    }));
  </script>
@endscript

<nav
  class="z-20 mb-6"
  id="header"
  x-data="header"
>
  <div
    class="dark:shadow-lividus-700/50 relative hidden h-20 w-full items-center justify-center bg-gray-50 shadow-lg shadow-emerald-100 transition-all duration-300 lg:flex dark:bg-gray-800"
    id="desktop-header"
  >
    {{-- logo --}}
    <a
      class="absolute inset-y-1/2 left-4 flex items-center"
      href="{{ route('root') }}"
      wire:navigate
    >
      <img
        class="size-10"
        src="{{ asset('images/icon/icon.png') }}"
        alt="logo"
      >
      <span class="ml-3 font-mono text-2xl font-bold dark:text-gray-50">{{ config('app.name') }}</span>
    </a>

    <div class="flex space-x-6">
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
    </div>

    <div class="absolute inset-y-1/2 right-6 flex items-center space-x-5">

      {{-- search --}}
      <livewire:shared.search />

      {{-- light / dark mode toggle --}}
      <button
        class="group relative flex size-12 items-center justify-center overflow-hidden"
        type="button"
        aria-label="Toggle Dark Mode"
        x-on:click="switchTheme"
      >
        <x-icon.sun
          class="absolute inset-x-auto top-3 size-6 text-amber-400 transition-all duration-300 group-hover:text-amber-500 dark:top-full"
        />

        <x-icon.moon-stars
          class="absolute inset-x-auto -top-full size-6 text-[#f6f1d5] transition-all duration-300 group-hover:text-[#ddd8bf] dark:top-3"
        />
      </button>

      @guest
        @if ($showRegisterButton)
          <a
            class="border-lividus-600 text-lividus-600 hover:bg-lividus-600 flex h-10 items-center justify-center rounded-lg border-2 bg-transparent px-3 transition duration-150 hover:border-transparent hover:text-gray-50"
            href="{{ route('register') }}"
            wire:navigate
          >
            註冊
          </a>
        @endif

        <a
          class="flex h-10 items-center justify-center rounded-lg border-2 border-emerald-600 bg-transparent px-3 text-emerald-600 transition duration-150 hover:border-transparent hover:bg-emerald-600 hover:text-gray-50"
          href="{{ route('login') }}"
          wire:navigate
        >
          <x-icon.door-open class="w-5" />
          <span class="ml-2">登入</span>
        </a>
      @endguest

      @auth
        {{-- notification --}}
        <span class="relative inline-flex rounded-md">
          <a
            class="flex size-12 items-center justify-center rounded-lg text-xl text-gray-500 transition duration-150 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-50"
            href="{{ route('notifications.index') }}"
            aria-label="Notifications"
            wire:navigate
          >
            <x-icon.bell class="size-6" />
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
              class="flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400"
              id="desktop-user-menu-button"
              type="button"
              x-on:click="toggleProfileMenu"
              x-on:keydown.escape.window="closeProfileMenu"
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
          <div
            class="absolute right-0 top-16 mt-2 w-48 rounded-md bg-gray-50 p-2 ring-1 ring-black/20 dark:bg-gray-800 dark:text-gray-50 dark:ring-gray-600/20"
            x-cloak
            x-show="profileMenuIsOpen"
            x-on:click.outside="closeProfileMenu"
            x-transition.origin.top.right
          >
            <a
              class="flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
              href="{{ route('posts.create') }}"
              wire:navigate
            >
              <x-icon.pencil class="w-4" />
              <span class="ml-2">新增文章</span>
            </a>

            <a
              class="flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
              href="{{ route('users.show', ['id' => auth()->id()]) }}"
              wire:navigate
            >
              <x-icon.info-circle class="w-4" />
              <span class="ml-2">個人資訊</span>
            </a>

            <a
              class="flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
              href="{{ route('users.edit', ['id' => auth()->id()]) }}"
              wire:navigate
            >
              <x-icon.person-circle class="w-4" />
              <span class="ml-2">會員中心</span>
            </a>

            <button
              class="flex w-full items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
              type="button"
              wire:confirm="你確定要登出嗎？"
              wire:click="logout"
            >
              <x-icon.box-arrow-left class="w-4" />
              <span class="ml-2">登出</span>
            </button>
          </div>
        </div>
      @endauth
    </div>
  </div>

  <div
    class="bg-gray-50 lg:hidden dark:bg-gray-800"
    id="mobile-header"
  >
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
      <div class="relative flex h-[4.5rem] items-center justify-between">
        <div class="absolute inset-y-0 left-0 flex items-center">
          {{-- category dropdown menu toggle --}}
          <button
            class="inline-flex items-center justify-center rounded-md p-2 text-gray-700"
            type="button"
            aria-controls="mobile-menu"
            aria-expanded="false"
            x-on:click="toggleDropdownMenu"
          >
            <span class="sr-only">Open main menu</span>
            {{-- close category dropdown menu icon --}}
            <div
              class="text-3xl text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-50"
              x-cloak
              x-show="dropdownMenuIsClose"
            >
              <x-icon.list class="w-7" />
            </div>
            {{-- open category dropdown menu icon --}}
            <div
              class="text-xl text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-50"
              x-cloak
              x-show="dropdownMenuIsOpen"
            >
              <x-icon.x class="w-7" />
            </div>
          </button>
        </div>

        <div class="mx-auto flex items-center">
          <img
            class="hidden size-10 md:inline-block"
            src="{{ asset('images/icon/icon.png') }}"
            alt="logo"
          >
          <span class="ml-3 hidden font-mono text-xl font-bold md:block dark:text-gray-50">
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
            <x-icon.sun class="w-5 text-amber-400 hover:text-amber-500 dark:hidden" />

            <x-icon.moon-stars class="hidden w-5 text-[#f6f1d5] hover:text-[#ddd8bf] dark:block" />
          </button>

          @guest
            @if ($showRegisterButton)
              <a
                class="border-lividus-400 text-lividus-400 hover:bg-lividus-400 rounded-md border-2 bg-transparent px-4 py-2 hover:border-transparent hover:text-gray-50"
                href="{{ route('register') }}"
                wire:navigate
              >
                註冊
              </a>
            @endif

            <a
              class="rounded-md border-2 border-emerald-600 bg-transparent px-4 py-2 text-emerald-600 hover:border-transparent hover:bg-emerald-600 hover:text-gray-50"
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
                class="rounded-full text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-50"
                href="{{ route('notifications.index') }}"
                wire:navigate
              >
                <x-icon.bell class="w-5" />
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
                  class="flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400"
                  id="mobile-user-menu-button"
                  type="button"
                  aria-expanded="false"
                  aria-haspopup="true"
                  x-on:click="toggleProfileMenu"
                  x-on:keydown.escape.window="closeProfileMenu"
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
              <div
                class="absolute right-0 mt-2 w-48 rounded-md bg-gray-50 p-2 text-gray-700 ring-1 ring-black/20 dark:bg-gray-800 dark:text-gray-50 dark:ring-gray-600/20"
                role="menu"
                aria-orientation="vertical"
                aria-labelledby="user-menu-button"
                tabindex="-1"
                x-cloak
                x-show="profileMenuIsOpen"
                x-on:click.outside="closeProfileMenu"
                x-transition.origin.top.right
              >
                <a
                  class="flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
                  href="{{ route('posts.create') }}"
                  role="menuitem"
                  tabindex="-1"
                  wire:navigate
                >
                  <x-icon.pencil class="w-4" />
                  <span class="ml-2">新增文章</span>
                </a>

                <a
                  class="flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
                  href="{{ route('users.show', ['id' => auth()->id()]) }}"
                  role="menuitem"
                  tabindex="-1"
                  wire:navigate
                >
                  <x-icon.info-circle class="w-4" />
                  <span class="ml-2">個人資訊</span>
                </a>

                <a
                  class="flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
                  href="{{ route('users.edit', ['id' => auth()->id()]) }}"
                  role="menuitem"
                  tabindex="-1"
                  wire:navigate
                >
                  <x-icon.person-circle class="w-4" />
                  <span class="ml-2">會員中心</span>
                </a>

                <button
                  class="flex w-full items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700"
                  type="button"
                  role="menuitem"
                  tabindex="-1"
                  wire:confirm="你確定要登出嗎？"
                  wire:click="logout"
                >
                  <x-icon.box-arrow-left class="w-4" />
                  <span class="ml-2">登出</span>
                </button>
              </div>
            </div>
          @endauth
        </div>
      </div>
    </div>

    {{-- category dropdown menu --}}
    <div
      class="lg:hidden"
      x-cloak
      x-show="dropdownMenuIsOpen"
      x-collapse
    >
      <div class="space-y-1 px-2 pb-3 pt-2">

        @php
          $inIndexPage = urldecode(request()->url()) === urldecode(route('posts.index'));
        @endphp

        <a
          href="{{ route('posts.index') }}"
          @if ($inIndexPage) aria-current="page" @endif
          @class([
              'flex items-center px-3 py-2 rounded-md font-medium',
              'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-50' => $inIndexPage,
              'text-gray-500 dark:text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-gray-50' => !$inIndexPage,
          ])
          wire:navigate
        >
          <x-icon.home class="w-4" />
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
                'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-50' => $inCategoryPage,
                'text-gray-500 dark:text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-gray-50' => !$inCategoryPage,
            ])
            wire:navigate
          >
            <div class="w-4">{!! $category->icon !!}</div>
            <span class="ml-2">{{ $category->name }}</span>
          </a>
        @endforeach
      </div>
    </div>
  </div>
</nav>
