@script
  <script>
    Alpine.data('homeSideMenu', () => ({
      copyWebFeedUrl() {
        navigator.clipboard.writeText(this.$el.getAttribute('href')).then(
          () => this.$refs.subscriptionText.innerText = '複製成功',
          () => this.$refs.subscriptionText.innerText = '複製失敗'
        );

        setTimeout(() => this.$refs.subscriptionText.innerText = '訂閱文章', 2000);
      }
    }));
  </script>
@endscript

<div
  class="space-y-6"
  x-data="homeSideMenu"
>
  {{-- 介紹 --}}
  <x-card class="group dark:text-gray-50">
    <p
      class="bg-linear-to-r font-jetbrains-mono w-full from-green-500 via-emerald-500 to-teal-500 bg-clip-text text-center text-xl font-semibold text-transparent dark:border-white dark:from-indigo-500 dark:via-violet-500 dark:to-purple-500">
      echo 'Hello World';
    </p>

    <hr class="my-4 h-0.5 border-0 bg-gray-300 dark:bg-gray-700">

    <span class="group-gradient-underline-grow leading-relaxed">
      嘗試用部落格來紀錄自己學習的過程，與生活上的大小事。此部落格使用 TALL Stack 所開發🚀
    </span>

    <div class="mt-8 flex items-center justify-center">
      <a
        class="before:bg-lividus-600 hover:before:scale-10 dark:bg-lividus-600 group relative flex w-full items-center justify-center overflow-hidden rounded-lg bg-emerald-500 px-4 py-2 [transform:translateZ(0)] before:absolute before:left-1/2 before:top-1/2 before:h-8 before:w-8 before:-translate-x-1/2 before:-translate-y-1/2 before:scale-0 before:rounded-full before:opacity-0 before:transition before:duration-700 before:ease-in-out hover:before:opacity-100 dark:before:bg-emerald-500"
        href="{{ route('posts.create') }}"
        wire:navigate
      >
        <div class="relative z-0 flex items-center text-gray-200 transition duration-700 ease-in-out">
          <x-icon.pencil class="w-5" />
          <span class="ml-2">新增文章</span>
        </div>
      </a>
    </div>
  </x-card>

  {{-- 熱門標籤 --}}
  @if ($popularTags->count())
    <x-card class="dark:text-gray-50">
      <div class="flex items-center justify-center">
        <x-icon.tags class="w-5" />
        <span class="ml-2">熱門標籤</span>
      </div>

      <hr class="my-4 h-0.5 border-0 bg-gray-300 dark:bg-gray-700">

      <div class="flex flex-wrap">
        @foreach ($popularTags as $popularTag)
          <x-tag :href="route('tags.show', ['id' => $popularTag->id])">
            {{ $popularTag->name }}
          </x-tag>
        @endforeach
      </div>
    </x-card>
  @endif

  {{-- 學習資源推薦 --}}
  @if ($links->count())
    <x-card class="dark:text-gray-50">
      <div class="flex items-center justify-center">
        <x-icon.file-earmark-code class="w-5" />
        <span class="ml-2">學習資源推薦</span>
      </div>

      <hr class="my-4 h-0.5 border-0 bg-gray-300 dark:bg-gray-700">

      <div class="flex flex-col">
        @foreach ($links as $link)
          <a
            class="flex items-center rounded-md p-2 hover:bg-gray-200 dark:text-gray-50 dark:hover:bg-gray-700"
            href="{{ $link->link }}"
            target="_blank"
            rel="nofollow noopener noreferrer"
          >
            <span class="mr-2 flex h-[1lh] items-center">
              <x-icon.link-45deg class="w-5" />
            </span>
            {{ $link->title }}
          </a>
        @endforeach
      </div>
    </x-card>
  @endif

  <a
    class="focus:outline-hidden focus:ring-3 inline-flex w-full items-center justify-center rounded-lg border border-transparent bg-zinc-500 px-4 py-2 tracking-widest text-gray-50 ring-zinc-300 transition duration-150 ease-in-out hover:bg-zinc-600 focus:border-zinc-700 active:bg-zinc-700 dark:bg-zinc-600 dark:ring-zinc-800 dark:hover:bg-zinc-500"
    href="{{ route('feeds.main') }}"
    x-on:click.prevent="copyWebFeedUrl"
    target="_blank"
    rel="nofollow noopener"
  >
    <x-icon.rss class="w-5" />
    <span
      class="ml-2"
      x-ref="subscriptionText"
    >訂閱文章</span>
  </a>
</div>
