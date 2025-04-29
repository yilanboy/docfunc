@script
  <script>
    Alpine.data('userPosts', () => ({
      currentYear: @entangle('currentPostsYear').live,
      dropdownIsOpen: false,
      toggleDropdown() {
        this.dropdownIsOpen = !this.dropdownIsOpen;
      },
      showCurrentYearText() {
        return this.currentYear + ' 年的文章';
      },
      closeDropdown() {
        this.dropdownIsOpen = false;
      },
      switchPostsByYear() {
        this.currentYear = this.$el.getAttribute('data-year');
        this.dropdownIsOpen = false;
      },
      showPostsByYear() {
        return this.currentYear === this.$el.getAttribute('data-year');
      }
    }));
  </script>
@endscript

<div x-data="userPosts">
  @if (!empty($this->postsGroupByYear))
    {{-- 會員文章 --}}
    <x-card class="relative w-full text-lg">
      <div class="relative mb-6 flex justify-end">
        <button
          class="focus:outline-hidden inline-flex w-full cursor-pointer items-center justify-center rounded-md border border-zinc-200 bg-zinc-50 px-4 py-2 text-lg font-medium transition-colors hover:bg-neutral-100 focus:bg-white focus:ring-2 focus:ring-neutral-200/60 focus:ring-offset-2 active:bg-white disabled:pointer-events-none disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-50 dark:hover:bg-zinc-700 dark:focus:bg-zinc-600 dark:focus:ring-zinc-600 dark:focus:ring-offset-zinc-800 dark:active:bg-zinc-600"
          type="button"
          x-on:click="toggleDropdown"
          x-text="showCurrentYearText"
        >
        </button>

        <x-dropdown.menu
          class="absolute right-0 top-12 z-10"
          x-cloak=""
          x-show="dropdownIsOpen"
          x-on:click.away="closeDropdown"
          x-transition:enter="ease-out duration-200"
          x-transition:enter-start="-translate-y-2"
          x-transition:enter-end="translate-y-0"
        >
          @foreach (array_keys($this->postsGroupByYear) as $year)
            <x-dropdown.button
              data-year="{{ $year }}"
              x-on:click="switchPostsByYear"
              wire:key="switch-to-{{ $year }}-posts"
            >
              <span>{{ $year }}</span>
            </x-dropdown.button>
          @endforeach
        </x-dropdown.menu>
      </div>

      @foreach ($this->postsGroupByYear as $year => $posts)
        <div
          class="rounded-md border border-zinc-200 bg-zinc-50 duration-200 dark:border-zinc-700 dark:bg-zinc-800"
          data-year="{{ $year }}"
          x-cloak
          x-show="showPostsByYear"
          x-transition
          wire:key="{{ $year . '-posts' }}"
        >
          <livewire:shared.users.posts-group-by-year
            :key="$year . '-posts'"
            :user-id="$userId"
            :posts="$posts"
            :year="$year"
          />
        </div>
      @endforeach
    </x-card>
  @else
    <x-card class="flex h-32 items-center justify-center text-zinc-400 dark:text-zinc-600">
      <x-icons.exclamation-circle class="w-6" />
      <span class="ml-2">目前還沒有發佈任何文章喔！</span>
    </x-card>
  @endif
</div>
