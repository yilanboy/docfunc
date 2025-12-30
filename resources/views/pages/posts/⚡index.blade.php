<?php

declare(strict_types=1);

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $title = Route::currentRouteName() === 'root' ? config('app.name') : '所有文章';

        return $this->view()->title($title);
    }
};
?>

<x-layouts.main>
    {{-- 文章列表 --}}
    <div class="container mx-auto grow">
        <div class="grid grid-cols-3 gap-6 px-2 mx-auto max-w-3xl lg:px-0 xl:max-w-5xl">
            <div class="col-span-3 xl:col-span-2">
                {{-- 文章列表 --}}
                <livewire:posts.list />
            </div>

            <div class="hidden xl:block xl:col-span-1">
                {{-- 文章列表側邊欄 --}}
                <livewire:posts.home-sidebar />
            </div>
        </div>
    </div>
</x-layouts.main>
