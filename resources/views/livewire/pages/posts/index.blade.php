{{-- 文章列表 --}}
<x-layouts.layout-main>
  <div class="container mx-auto">
    <div class="mx-auto grid max-w-3xl grid-cols-3 gap-6 px-2 lg:px-0 xl:max-w-5xl">
      <div class="col-span-3 xl:col-span-2">
        {{-- 文章列表 --}}
        <livewire:shared.posts.posts
          :currentUrl="url()->current()"
          :categoryId="$category->id ?? 0"
          :categoryName="$category->name ?? ''"
          :categoryDescription="$category->description ?? ''"
          :tagId="$tag->id ?? 0"
          :tagName="$tag->name ?? ''"
        />
      </div>

      <div class="hidden xl:col-span-1 xl:block">
        {{-- 文章列表側邊欄 --}}
        <livewire:shared.posts.home-side-menu />
      </div>
    </div>
  </div>
</x-layouts.layout-main>
