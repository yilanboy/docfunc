<?php

declare(strict_types=1);

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function render()
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        auth()->user()->unreadNotifications->markAsRead();

        return $this->view()->with(compact('notifications'))->title('我的通知');
    }
};
?>

{{-- 通知列表 --}}
<x-layouts.main>
  <div class="container mx-auto grow">
    <div class="flex items-start justify-center px-4 xl:px-0">

      <div class="flex w-full flex-col items-center justify-center space-y-6 md:w-[700px]">
        {{-- 頁面標題 --}}
        <div class="flex items-center justify-center fill-current text-2xl text-zinc-700 dark:text-zinc-50">
          <x-icons.bell class="w-6" />
          <span class="ml-4">我的通知</span>
        </div>

        {{-- 通知列表 --}}
        @forelse ($notifications as $notification)
          <x-card
            class="flex w-full cursor-pointer flex-col justify-between md:flex-row"
            wire:key="notification-{{ $notification->id }}"
          >
            {{-- 通知內容 --}}
            <div class="flex w-full flex-col justify-between">
              {{-- 文章標題 --}}
              <div class="mt-2 space-x-2 md:mt-0">

                <span class="dark:text-zinc-50">在你的文章中</span>
                <a
                  class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-50"
                  href="{{ $notification->data['post_link'] }}"
                  wire:navigate
                >
                  {{ $notification->data['post_title'] }}
                </a>
                <span class="dark:text-zinc-50">有了新的言</span>
              </div>

              {{-- 通知時間 --}}
              <div class="mt-4 flex items-center text-sm text-zinc-400">
                <x-icons.clock class="w-4" />
                <span
                  class="ml-2"
                  title="{{ $notification->created_at }}"
                >
                  {{ $notification->created_at->diffForHumans() }}
                </span>
              </div>
            </div>

          </x-card>

        @empty
          <x-card class="flex h-24 w-full items-center justify-center dark:text-zinc-50">
            <span>沒有消息通知！</span>
          </x-card>
        @endforelse

        <div>
          {{ $notifications->links() }}
        </div>
      </div>

    </div>
  </div>
</x-layouts.main>
