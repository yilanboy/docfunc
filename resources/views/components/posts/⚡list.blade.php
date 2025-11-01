<?php

declare(strict_types=1);

use App\Enums\PostOrderOptions;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public ?int $categoryId = null;

    public ?int $tagId = null;

    public string $badge = '全部文章';

    #[Url]
    public string $order = PostOrderOptions::LATEST->value;

    public function changeOrder(PostOrderOptions $newOrder): void
    {
        $this->order = $newOrder->value;

        $this->resetPage();
    }

    public function render(): View
    {
        $posts = Post::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'excerpt', 'slug', 'created_at'])
            ->withCount('tags') // 計算標籤數目
            ->when($this->categoryId, function ($query) {
                return $query->where('category_id', $this->categoryId);
            })
            ->when($this->tagId, function ($query) {
                return $query->whereHas('tags', function ($query) {
                    $query->where('tag_id', $this->tagId);
                });
            })
            ->where('is_private', false)
            ->withOrder($this->order)
            ->with(['user:id,name', 'category:id,icon,name', 'tags:id,name']) // 預加載防止 N+1 問題
            ->paginate(10)
            ->withQueryString();

        return $this->view(compact('posts'));
    }
};
?>

@script
  <script>
    Alpine.data('postsListPart', () => ({
      order: '',
      tabButtonClicked(tabButton) {
        this.tabRepositionMarker(tabButton);
        this.order = tabButton.id.replace('-tab-button', '');

        this.$wire.changeOrder(this.order);
      },
      tabRepositionMarker(tabButton) {
        this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
        this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
        this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
      },
      init() {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        this.order = urlParams.get('order') ?? 'latest'

        const tabSelectedButtons = document.getElementById(this.order + '-tab-button');
        this.tabRepositionMarker(tabSelectedButtons);
      }
    }));
  </script>
@endscript

<div
  class="space-y-6"
  x-data="postsListPart"
>
  {{-- Sort --}}
  <div class="flex w-full text-sm md:flex-row md:justify-between">
    <x-tabs.nav
      class="md:w-fit"
      wire:ignore
    >
      @foreach (PostOrderOptions::cases() as $postOrder)
        <x-tabs.button
          id="{{ $postOrder->value . '-tab-button' }}"
          x-on:click="tabButtonClicked($el)"
          wire:key="{{ $postOrder->value }}-tab-button"
        >
          <x-dynamic-component
            class="w-3"
            :component="$postOrder->iconComponentName()"
            wire:loading.class="hidden"
            wire:target="changeOrder('{{ $postOrder->value }}')"
          />

          <x-icons.animate-spin
            class="hidden w-3"
            wire:loading.class.remove="hidden"
            wire:target="changeOrder('{{ $postOrder->value }}')"
          />
          <span>{{ $postOrder->label() }}</span>
        </x-tabs.button>
      @endforeach

      <x-tabs.tab-marker
        x-ref="tabMarker"
        x-cloak
      />
    </x-tabs.nav>

    {{-- Class badge --}}
    <div
      class="hidden items-center justify-center rounded-xl bg-zinc-50 px-3 py-1.5 md:flex dark:bg-zinc-800 dark:text-zinc-50"
    >{{ $badge }}</div>
  </div>

  {{-- Post List --}}
  @forelse($posts as $post)
    <x-card class="group relative isolate grid cursor-pointer grid-cols-1 gap-4 overflow-hidden">
      {{-- Category icon in background --}}
      <div
        class="absolute -bottom-16 -right-4 z-0 size-56 rotate-12 text-zinc-200/60 transition-all duration-300 group-hover:-bottom-4 group-hover:-right-0 dark:text-zinc-700/60"
      >
        {!! $post->category->icon !!}
      </div>

      {{-- Post card link --}}
      <a
        class="absolute inset-0 z-20"
        href="{{ $post->link_with_slug }}"
        title="{{ $post->title }}"
        wire:navigate
      ></a>

      {{-- Title --}}
      <div class="z-10">
        <h1 class="group-gradient-underline-grow inline text-xl font-semibold dark:text-zinc-50">
          {{ $post->title }}
        </h1>
      </div>

      {{-- Excerpt --}}
      <div class="z-10 text-base leading-relaxed text-zinc-500">
        {{ $post->excerpt }}
      </div>

      {{-- Tags --}}
      @if ($post->tags_count > 0)
        <div class="z-20 flex w-fit flex-wrap items-center text-base">
          <x-icons.tags class="dark:text-lividus-700 mr-1 w-4 text-emerald-200" />

          @foreach ($post->tags as $tag)
            <x-tag :href="route('tags.show', ['id' => $tag->id])">
              {{ $tag->name }}
            </x-tag>
          @endforeach
        </div>
      @endif

      <div class="z-10 hidden space-x-2 text-base text-neutral-500 md:flex md:items-center">
        {{-- Category --}}
        <div class="flex items-center">
          <div class="w-4">{!! $post->category->icon !!}</div>

          <span class="ml-2">{{ $post->category->name }}</span>
        </div>

        <div>&bull;</div>

        {{-- Author --}}
        <div class="flex items-center">
          <x-icons.person class="w-4" />
          <span class="ml-2">{{ $post->user->name }}</span>
        </div>

        <div>&bull;</div>

        {{-- Published time --}}
        <div class="flex items-center">
          <x-icons.clock class="w-4" />
          <time
            class="ml-2"
            datetime="{{ $post->created_at->toDateString() }}"
          >{{ $post->created_at->diffForHumans() }}</time>
        </div>

        <div>&bull;</div>

        {{-- Comments --}}
        <div class="flex items-center">
          <x-icons.chat-square-text class="w-4" />
          <span class="ml-2">{{ $post->comments_count }}</span>
        </div>
      </div>
    </x-card>

  @empty
    <x-card
      class="flex h-36 w-full items-center justify-center transition duration-150 ease-in hover:-translate-x-2 dark:text-zinc-50"
    >
      <span>Whoops！此分類底下還沒有文章，趕緊寫一篇吧！</span>
    </x-card>
  @endforelse

  {{ $posts->onEachSide(1)->links() }}
</div>
