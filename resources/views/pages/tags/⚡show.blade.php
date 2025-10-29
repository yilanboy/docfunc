<?php

declare(strict_types=1);

use App\Models\Tag;
use Livewire\Component;

new class extends Component {
    public Tag $tag;

    public function mount(int $id): void
    {
        $this->tag = Tag::findOrFail($id);
    }

    public function render()
    {
        return $this->view()->title($this->tag->name);
    }
};
?>

{{-- 文章列表 --}}
<x-layouts.main>
  <div class="container mx-auto grow">
    <div class="mx-auto grid max-w-3xl grid-cols-3 gap-6 px-2 lg:px-0 xl:max-w-5xl">
      <div class="col-span-3 xl:col-span-2">
        {{-- 文章列表 --}}
        <livewire:posts.list
          :tagId="$tag->id"
          :badge="'標籤：' . $tag->name"
        />
      </div>

      <div class="hidden xl:col-span-1 xl:block">
        {{-- 文章列表側邊欄 --}}
        <livewire:posts.home-sidebar />
      </div>
    </div>
  </div>
</x-layouts.main>
