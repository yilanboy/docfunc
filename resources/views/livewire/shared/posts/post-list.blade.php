@php
  use App\Enums\PostOrder;
@endphp

@script
  <script>
    // Tab can only be 'information', 'posts', 'comments'
    Alpine.data('postsTabs', () => ({
      tabSelected: @js($order),
      tabButtonClicked(tabButton) {
        this.tabSelected = tabButton.id.replace('-tab-button', '');
        this.tabRepositionMarker(tabButton);
      },
      tabRepositionMarker(tabButton) {
        this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
        this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
        this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
      },
      tabContentActive(tabContent) {
        return this.tabSelected === tabContent.id.replace('-content', '');
      },
      init() {
        let tabSelectedButtons = document.getElementById(this.tabSelected + '-tab-button');
        this.tabRepositionMarker(tabSelectedButtons);
      }
    }));
  </script>
@endscript

<div
  class="space-y-6"
  x-data="postsTabs"
>
  {{-- Sort --}}
  <div class="flex w-full text-sm md:flex-row md:justify-between">
    <nav
      class="relative z-0 inline-grid w-full select-none grid-cols-3 items-center justify-center rounded-lg text-gray-500 md:w-fit dark:text-gray-50"
      wire:ignore
    >
      @foreach (PostOrder::cases() as $postOrder)
        <button
          class="relative z-20 inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium"
          id="{{ $postOrder->value }}-tab-button"
          type="button"
          x-on:click="tabButtonClicked($el)"
          {{-- Update url query parameter in livewire --}}
          wire:click="changeOrder('{{ $postOrder }}')"
          wire:key="{{ $postOrder->value }}-tab-button"
        >
          <x-dynamic-component
            class="w-4"
            :component="$postOrder->iconComponentName()"
          />
          <span>{{ $postOrder->label() }}</span>
        </button>
      @endforeach

      <div
        class="absolute left-0 z-10 h-full w-fit duration-300 ease-out"
        x-ref="tabMarker"
        x-cloak
      >
        <div class="h-full w-full rounded-md bg-gray-100 dark:bg-gray-800"></div>
      </div>
    </nav>

    {{-- Class --}}
    <div
      class="dark:bg-lividus-700 hidden items-center justify-center rounded-lg bg-emerald-200 px-4 py-2 text-emerald-800 md:flex dark:text-gray-50"
    >
      @if ($categoryId)
        {{ $categoryName }}：{{ $categoryDescription }}
      @elseif($tagId)
        標籤：{{ $tagName }}
      @else
        全部文章
      @endif
    </div>
  </div>

  {{-- Post List --}}
  @forelse($posts as $post)
    <x-card class="group relative z-0 grid cursor-pointer grid-cols-1 gap-4 overflow-hidden">
      {{-- Category icon in background --}}
      <div
        class="dark:text-lividus-800 absolute -bottom-16 -right-4 size-56 rotate-12 text-emerald-200 transition-all duration-300 group-hover:-bottom-4 group-hover:-right-0"
      >
        {!! $post->category->icon !!}
      </div>

      {{-- Post card link --}}
      <a
        class="absolute inset-0 z-20 block"
        href="{{ $post->link_with_slug }}"
        title="{{ $post->title }}"
        wire:navigate
      ></a>

      {{-- Title --}}
      <div class="z-10">
        <h1 class="group-gradient-underline-grow inline text-xl font-semibold dark:text-gray-50">
          {{ $post->title }}
        </h1>
      </div>

      {{-- Excerpt --}}
      <div class="z-10 text-base leading-relaxed text-gray-500">
        {{ $post->excerpt }}
      </div>

      {{-- Tags --}}
      @if ($post->tags_count > 0)
        <div class="z-20 flex w-fit flex-wrap items-center text-base">
          <x-icon.tags class="dark:text-lividus-700 mr-1 w-4 text-emerald-300" />

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
          <x-icon.person class="w-4" />
          <span class="ml-2">{{ $post->user->name }}</span>
        </div>

        <div>&bull;</div>

        {{-- Published time --}}
        <div class="flex items-center">
          <x-icon.clock class="w-4" />
          <time
            class="ml-2"
            datetime="{{ $post->created_at->toDateString() }}"
          >{{ $post->created_at->diffForHumans() }}</time>
        </div>

        <div>&bull;</div>

        {{-- Comments --}}
        <div class="flex items-center">
          <x-icon.chat-square-text class="w-4" />
          <span class="ml-2">{{ $post->comments_count }}</span>
        </div>
      </div>
    </x-card>

  @empty
    <x-card
      class="flex h-36 w-full items-center justify-center transition duration-150 ease-in hover:-translate-x-2 dark:text-gray-50"
    >
      <span>Whoops！此分類底下還沒有文章，趕緊寫一篇吧！</span>
    </x-card>
  @endforelse

  {{ $posts->onEachSide(1)->links() }}
</div>
