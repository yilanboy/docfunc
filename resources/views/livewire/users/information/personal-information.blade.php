{{-- 會員基本資訊 --}}
<div class="grid w-full grid-cols-6 gap-6 dark:text-gray-50">
  <x-card class="col-span-6 flex flex-col items-center justify-between md:col-span-2">
    {{-- 大頭貼 --}}
    <img
      class="h-36 w-36 rounded-full"
      src="{{ $user->gravatar_url }}"
      alt="{{ $user->name }}"
    >

    {{-- 會員名稱 --}}
    <span class="mt-2 flex items-center justify-center text-3xl font-semibold">
      {{ $user->name }}
    </span>

    <span class="mt-2 text-xs">
      註冊於 {{ $user->created_at->format('Y / m / d') . '（' . $user->created_at->diffForHumans() . '）' }}
    </span>
  </x-card>

  <x-card class="col-span-6 dark:text-gray-50 md:col-span-4">
    <h3 class="mb-3 w-full border-b-2 border-black pb-3 text-2xl font-semibold dark:border-white">
      <span class="ml-2">個人簡介</span>
    </h3>

    @if ($user->introduction)
      <p class="flex w-full items-center justify-start whitespace-pre-wrap">{{ $user->introduction }}</p>
    @else
      <p class="flex w-full items-center justify-center whitespace-pre-wrap">目前尚無個人簡介～</p>
    @endif
  </x-card>

  <x-card class="col-span-6 dark:text-gray-50">
    <h3 class="mb-3 w-full border-b-2 border-black pb-3 text-2xl font-semibold dark:border-white">
      <span class="ml-2">各類文章統計</span>
    </h3>

    <div class="grid grid-cols-12 gap-1">
      @foreach ($categories as $category)
        <div class="col-span-12">
          {{ $category->name }}
        </div>
        <div class="col-span-11 flex items-center">

          @php
            $barWidth = $category->posts->count() ? (int) (($category->posts->count() / $user->posts->count()) * 100) : 0.2;
          @endphp

          <div style="width: {{ $barWidth }}%">
            <div
              class="h-4 animate-grow-width rounded-sm bg-gradient-to-r from-emerald-400 to-blue-400 transition-all duration-300"
            ></div>
          </div>

        </div>
        <div class="col-span-1 flex items-center justify-end text-lg font-semibold text-sky-500">
          {{ $category->posts->count() }}
        </div>
      @endforeach
    </div>
  </x-card>

  <x-card class="col-span-6 flex flex-col items-start justify-between dark:text-gray-50 md:col-span-2">
    <div class="w-full text-left text-2xl">文章總數</div>
    <div class="count-up w-full text-center text-8xl font-semibold text-sky-500">{{ $user->posts->count() }}</div>
    <div class="w-full text-right text-2xl">篇</div>
  </x-card>

  <x-card class="col-span-6 flex flex-col items-start justify-between dark:text-gray-50 md:col-span-2">
    <div class="w-full text-left text-2xl">今年寫了</div>
    <div class="count-up w-full text-center text-8xl font-semibold text-sky-500">{{ $user->posts_count_in_this_year }}
    </div>
    <div class="w-full text-right text-2xl">篇</div>
  </x-card>

  <x-card class="col-span-6 flex flex-col items-start justify-between dark:text-gray-50 md:col-span-2">
    <div class="w-full text-left text-2xl">留言回覆</div>
    <div class="count-up w-full text-center text-8xl font-semibold text-sky-500">{{ $user->comments->count() }}</div>
    <div class="w-full text-right text-2xl">次</div>
  </x-card>
</div>
