<?php

declare(strict_types=1);

use App\Models\Category;
use Livewire\Component;

new class extends Component {
    public Category $category;

    public function mount(int $id): void
    {
        $this->category = Category::findOrFail($id);
    }

    public function render()
    {
        // because name is optional, we can't use route parameters
        if (!empty($this->category->name) && $this->category->name !== request()->name) {
            redirect()->to($this->category->link_with_name);
        }

        return $this->view()->title($this->category->name);
    }
};
?>

{{-- 文章列表 --}}
<x-layouts.main>
  <div class="container mx-auto grow">
    <div class="mx-auto grid max-w-3xl grid-cols-3 gap-6 px-2 lg:px-0 xl:max-w-5xl">
      <div class="col-span-3 xl:col-span-2">
        {{-- 文章列表 --}}
        <livewire:shared.posts.list-part
          :categoryId="$category->id"
          :badge="$category->name . '：' . $category->description"
        />
      </div>

      <div class="hidden xl:col-span-1 xl:block">
        {{-- 文章列表側邊欄 --}}
        <livewire:shared.posts.home-sidebar-part />
      </div>
    </div>
  </div>
</x-layouts.main>
