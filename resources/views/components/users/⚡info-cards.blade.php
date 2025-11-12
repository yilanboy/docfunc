<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Livewire\Component;

new class extends Component {
    public string $userId;

    public function render()
    {
        $user = User::find($this->userId)->loadCount([
            'posts as posts_count_in_this_year' => function ($query) {
                $query->whereYear('created_at', date('Y'));
            },
        ]);

        $commentCountsInAllPosts = Comment::query()->join('posts', 'comments.post_id', '=', 'posts.id')->where('posts.user_id', $user->id)->count();

        $categories = Category::with([
            'posts' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            },
        ])->get();

        return $this->view([
            'user' => $user,
            'categories' => $categories,
            'commentCountsInAllPosts' => $commentCountsInAllPosts,
        ]);
    }
};
?>

@script
  <script>
    Alpine.data('usersInfoCardsPart', () => ({
      init() {
        document.querySelectorAll('.count-up').forEach((countUp) => {
          let from = 0;
          let to = Number(countUp.textContent);

          if (to > 999) {
            to = 999;
          }

          if (from === to) {
            return;
          }

          let counter = setInterval(() => {
            countUp.textContent = String(from);

            if (from === to) {
              clearInterval(counter);
            }

            from++;
          }, 1000 * (1 / to));
        });
      }
    }));
  </script>
@endscript

{{-- 會員基本資訊 --}}
<div
  class="grid w-full grid-cols-6 gap-6 dark:text-zinc-50"
  x-data="usersInfoCardsPart"
>
  <div
    class="bg-linear-to-br p-(--card-padding) rounded-(--card-radius) col-span-6 from-green-500 via-emerald-500 to-teal-500 [--card-padding:--spacing(1)] [--card-radius:var(--radius-2xl)] md:col-span-2 dark:from-indigo-500 dark:via-violet-500 dark:to-purple-500"
  >
    <div
      class="flex flex-col items-center justify-between rounded-[calc(var(--card-radius)-var(--card-padding))] bg-zinc-50 p-5 dark:bg-zinc-800"
    >
      {{-- 大頭貼 --}}
      <img
        class="size-36 rounded-full"
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
    </div>
  </div>

  <x-card class="col-span-6 md:col-span-4 dark:text-zinc-50">
    <h2 class="w-full text-2xl">個人簡介</h2>
    <hr class="my-4 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

    @if ($user->introduction)
      <p class="flex w-full items-center justify-start whitespace-pre-wrap">{{ $user->introduction }}</p>
    @else
      <p class="flex w-full items-center justify-center whitespace-pre-wrap">目前尚無個人簡介～</p>
    @endif
  </x-card>

  <x-card class="col-span-6 dark:text-zinc-50">
    <h2 class="w-full text-2xl">各類文章統計</h2>
    <hr class="my-4 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">

    <div class="grid grid-cols-12 gap-2">
      @foreach ($categories as $category)
        <div class="col-span-12 flex items-center">
          <div class="w-4">{!! $category->icon !!}</div>
          <span class="ml-2">{{ $category->name }}</span>
        </div>
        <div class="col-span-11 flex items-center">
          @php
            $barWidth = $category->posts->count()
                ? (int) (($category->posts->count() / $user->posts->count()) * 100)
                : 0.2;
          @endphp

          <div style="width: {{ $barWidth }}%">
            <div
              class="animate-grow-width rounded-xs bg-linear-to-r h-4 from-green-500 via-emerald-500 to-teal-500 transition-all duration-300 dark:border-white dark:from-indigo-500 dark:via-violet-500 dark:to-purple-500"
            >
            </div>
          </div>
        </div>

        <div class="col-span-1 flex items-center justify-end text-lg font-semibold text-teal-500 dark:text-purple-500">
          {{ $category->posts->count() }}
        </div>
      @endforeach
    </div>
  </x-card>

  <x-card class="col-span-6 flex flex-col items-start justify-between md:col-span-2 dark:text-zinc-50">
    <div class="w-full text-left text-2xl">文章總數</div>
    <div class="count-up w-full text-center text-8xl font-semibold text-teal-500 dark:text-purple-500">
      {{ $user->posts->count() }}</div>
    <div class="w-full text-right text-2xl">篇</div>
  </x-card>

  <x-card class="col-span-6 flex flex-col items-start justify-between md:col-span-2 dark:text-zinc-50">
    <div class="w-full text-left text-2xl">今年寫了</div>
    <div class="count-up w-full text-center text-8xl font-semibold text-teal-500 dark:text-purple-500">
      {{ $user->posts_count_in_this_year }}
    </div>
    <div class="w-full text-right text-2xl">篇</div>
  </x-card>

  <x-card class="col-span-6 flex flex-col items-start justify-between md:col-span-2 dark:text-zinc-50">
    <div class="w-full text-left text-2xl">文章總留言</div>
    <div class="count-up w-full text-center text-8xl font-semibold text-teal-500 dark:text-purple-500">
      {{ $commentCountsInAllPosts }}</div>
    <div class="w-full text-right text-2xl">篇</div>
  </x-card>
</div>
