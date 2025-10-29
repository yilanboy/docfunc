<?php

declare(strict_types=1);

use App\Models\Post;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * @property-read array<int, array<int, Post>> $groupPostsByYear
 */
new class extends Component {
    public int $userId;

    // This will be set from the url parameter
    #[Url(as: 'current-posts-year')]
    public string $currentPostsYear = '';

    /**
     * Get the post-list of the user. This list will be grouped by year.
     * The first year will be the latest year
     * format: [2021 => [Post, Post, ...], 2020 => [Post, Post, ...], ...]
     *
     * @return array<int, non-empty-list<Post>> $postsGroupByYear
     */
    #[Computed]
    public function groupPostsByYear(): array
    {
        $posts = Post::whereUserId($this->userId)
            ->when(
                auth()->id() === $this->userId,
                function ($query) {
                    return $query->withTrashed();
                },
                function ($query) {
                    return $query->where('is_private', false);
                },
            )
            ->with('category')
            ->latest()
            ->get();

        $postsGroupByYear = [];

        foreach ($posts as $post) {
            $year = $post->created_at->format('Y');

            if (!isset($postsGroupByYear[$year])) {
                // php array will convert the numeric string key to int
                $postsGroupByYear[$year] = [];
            }

            $postsGroupByYear[$year][] = $post;
        }

        return $postsGroupByYear;
    }

    public function mount(): void
    {
        if (!array_key_exists($this->currentPostsYear, $this->groupPostsByYear)) {
            $this->currentPostsYear = (string) array_key_first($this->groupPostsByYear);
        }
    }
};
?>

@script
  <script>
    Alpine.data('usersPostsPart', () => ({
      currentYear: $wire.entangle('currentPostsYear').live,
      dropdownIsOpen: false,
      showCurrentYearText() {
        return `${this.currentYear} 年的文章`;
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

<div x-data="usersPostsPart">
  @if (!empty($this->groupPostsByYear))
    {{-- 會員文章 --}}
    <x-card class="relative w-full text-lg">
      <div class="relative mb-6 flex justify-end">
        <button
          class="focus:outline-hidden inline-flex w-full cursor-pointer items-center justify-center rounded-md border border-zinc-200 bg-zinc-50 px-4 py-2 text-lg font-medium transition-colors hover:bg-neutral-100 focus:bg-white focus:ring-2 focus:ring-neutral-200/60 focus:ring-offset-2 active:bg-white disabled:pointer-events-none disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-50 dark:hover:bg-zinc-700 dark:focus:bg-zinc-600 dark:focus:ring-zinc-600 dark:focus:ring-offset-zinc-800 dark:active:bg-zinc-600"
          type="button"
          x-on:click="dropdownIsOpen = !dropdownIsOpen"
          x-text="showCurrentYearText"
        >
        </button>

        <x-dropdown.menu
          class="absolute right-0 top-12 z-10"
          x-cloak=""
          x-show="dropdownIsOpen"
          x-on:click.away="dropdownIsOpen = false"
          x-transition:enter="ease-out duration-200"
          x-transition:enter-start="-translate-y-2"
          x-transition:enter-end="translate-y-0"
        >
          @foreach (array_keys($this->groupPostsByYear) as $year)
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

      @foreach ($this->groupPostsByYear as $year => $posts)
        <div
          class="rounded-md border border-zinc-200 bg-zinc-50 duration-200 dark:border-zinc-700 dark:bg-zinc-800"
          data-year="{{ $year }}"
          x-cloak
          x-show="showPostsByYear"
          x-transition
          wire:key="{{ $year . '-posts' }}"
        >
          <livewire:users.group-posts-by-year
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
