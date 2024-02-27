<div class="mr-0 w-full space-y-6 md:w-[700px] xl:mr-6">

  {{-- 文章排序 --}}
  <div class="flex w-full flex-col-reverse text-sm md:flex-row md:justify-between">

    <nav class="flex w-full space-x-1 rounded-xl p-1 dark:text-gray-50 md:w-auto">

      {{-- prettier-ignore-start --}}
      @php
        $tabs = [
          ['value' => 'latest', 'text' => '最新文章'],
          ['value' => 'recent', 'text' => '最近更新'],
          ['value' => 'comment', 'text' => '最多留言'],
        ];
      @endphp
      {{-- prettier-ignore-end --}}

      @foreach ($tabs as $tab)
        <button
          type="button"
          wire:click.prevent="orderChange('{{ $tab['value'] }}')"
          @class([
              'flex w-1/3 md:w-auto items-center px-4 py-2 transition duration-300 rounded-lg ',
              'bg-gray-50 dark:bg-gray-800' => $order === $tab['value'],
              'hover:bg-gray-50 dark:hover:bg-gray-800' => $order !== $tab['value'],
          ])
        >
          @switch($tab['value'])
            @case('recent')
              <x-icon.wrencn class="w-4" />
            @break

            @case('comment')
              <x-icon.chat-square-text class="w-4" />
            @break

            @default
              <x-icon.stars class="w-4" />
          @endswitch

          <span class="ml-2">{{ $tab['text'] }}</span>
        </button>
      @endforeach
    </nav>

    {{-- 文章分類訊息-桌面裝置 --}}
    @if ($categoryId)
      <div
        class="mb-0 hidden items-center justify-end border-b-2 border-gray-900 pb-2 pl-6 dark:border-gray-50 dark:text-gray-50 md:flex"
      >
        <span>{{ $categoryName }}：{{ $categoryDescription }}</span>
      </div>
    @endif

    {{-- 文章標籤訊息-桌面裝置 --}}
    @if ($tagId)
      <div
        class="mb-0 hidden items-center justify-end border-b-2 border-gray-900 pb-2 pl-6 dark:border-gray-50 dark:text-gray-50 md:flex"
      >
        <span>標籤：{{ $tagName }}</span>
      </div>
    @endif
  </div>

  {{-- 文章列表 --}}
  @forelse($posts as $post)
    <x-card class="group relative z-0 flex cursor-pointer flex-col justify-between overflow-hidden">
      <div class="absolute -bottom-16 -right-3 size-56 rotate-12 text-green-200 dark:text-lividus-800">
        {!! $post->category->icon !!}
      </div>

      {{-- 文章連結 --}}
      <a
        class="absolute inset-0 z-20 block"
        href="{{ $post->link_with_slug }}"
        title="{{ $post->title }}"
        wire:navigate
      ></a>

      {{-- 文章標題 --}}
      <h1 class="group-gradient-underline-grow z-10 mt-2 w-fit text-xl dark:text-gray-50 md:mt-0">
        {{ $post->title }}
      </h1>

      {{-- 文章大綱 --}}
      <div class="z-10 mt-2 text-base text-gray-500">
        {{ $post->excerpt }}
      </div>

      {{-- 文章標籤 --}}
      @if ($post->tags_count > 0)
        <div class="z-20 mt-2 flex w-fit flex-wrap items-center text-base">
          <x-icon.tags class="mr-1 w-4 text-green-300 dark:text-lividus-600" />

          @foreach ($post->tags as $tag)
            <x-tag :href="route('tags.show', ['tag' => $tag->id])">
              {{ $tag->name }}
            </x-tag>
          @endforeach
        </div>
      @endif

      {{-- 文章相關資訊 --}}
      <div class="mt-2 hidden space-x-2 text-base text-neutral-500 md:flex md:items-center">
        {{-- 文章分類資訊 --}}
        <div class="flex items-center">
          <div class="w-4">{!! $post->category->icon !!}</div>

          <span class="ml-2">{{ $post->category->name }}</span>
        </div>

        <div>&bull;</div>

        {{-- 文章作者資訊 --}}
        <div class="flex items-center">
          <x-icon.person class="w-4" />
          <span class="ml-2">{{ $post->user->name }}</span>
        </div>

        <div>&bull;</div>

        {{-- 文章發布時間 --}}
        <div class="flex items-center">
          <x-icon.clock class="w-4" />
          <span class="ml-2">{{ $post->created_at->diffForHumans() }}</span>
        </div>

        <div>&bull;</div>

        {{-- 文章留言數 --}}
        <div class="flex items-center">
          <x-icon.chat-square-text class="w-4" />
          <span class="ml-2">{{ $post->comment_counts }}</span>
        </div>
      </div>
    </x-card>

  @empty
    <x-card
      class="flex h-36 w-full items-center justify-center transition duration-150 ease-in hover:-translate-x-2 hover:shadow-xl dark:text-gray-50"
    >
      <span>Whoops！此分類底下還沒有文章，趕緊寫一篇吧！</span>
    </x-card>
  @endforelse

  {{ $posts->onEachSide(1)->links() }}
</div>
