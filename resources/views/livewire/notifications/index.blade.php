@section('title', '我的通知')

{{-- 通知列表 --}}
<div class="container mx-auto max-w-7xl flex-1">
  <div class="flex items-start justify-center px-4 xl:px-0">

    <div class="flex w-full flex-col items-center justify-center space-y-6 md:w-2/3 xl:w-1/2">
      {{-- 頁面標題 --}}
      <div class="fill-current text-2xl text-gray-700 dark:text-gray-50">
        <i class="bi bi-bell-fill"></i><span class="ml-4">我的通知</span>
      </div>

      {{-- 通知列表 --}}
      @forelse ($notifications as $notification)
        <x-card
          x-data="cardLink"
          x-on:click="directToCardLink($event, $refs)"
          class="flex w-full cursor-pointer flex-col justify-between md:flex-row"
        >
          {{-- 通知內容 --}}
          <div class="flex w-full flex-col justify-between">
            {{-- 文章標題 --}}
            <div class="mt-2 space-x-2 md:mt-0">

              <span class="dark:text-gray-50">在你的文章中</span>
              <a
                x-ref="cardLinkUrl"
                class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-50"
                href="{{ $notification->data['post_link'] }}"
              >
                {{ $notification->data['post_title'] }}
              </a>
              <span class="dark:text-gray-50">有了新的言</span>
            </div>

            {{-- 通知時間 --}}
            <div class="mt-4 text-sm text-gray-400">
              <i class="bi bi-clock-fill"></i>
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
        <x-card class="flex h-24 w-full items-center justify-center dark:text-gray-50">
          <span>沒有消息通知！</span>
        </x-card>
      @endforelse

      <div>
        {{ $notifications->links() }}
      </div>
    </div>

  </div>
</div>
