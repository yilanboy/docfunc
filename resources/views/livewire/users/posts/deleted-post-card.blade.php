<x-card class="flex flex-col justify-between border-2 border-red-400 group md:flex-row">
  {{-- 文章 --}}
  <div class="flex flex-col justify-between w-full">
    <span class="text-red-400">文章將於{{ $post->deleted_at->addDays(7)->diffForHumans() }}刪除</span>

    {{-- 文章標題 --}}
    <span class="mt-2 text-xl font-semibold md:mt-0 dark:text-gray-50">
      <span>{{ $post->title }}</span>
    </span>

    {{-- 文章相關資訊 --}}
    <div class="flex items-center mt-2 space-x-2 text-base text-neutral-400">
      {{-- 文章分類資訊 --}}
      <div>
        <span title="{{ $post->category->name }}">
          <i class="{{ $post->category->icon }}"></i><span class="ml-2">{{ $post->category->name }}</span>
        </span>
      </div>
      <div>&bull;</div>
      {{-- 文章發布時間 --}}
      <div>
        <span title="文章發布於：{{ $post->created_at->toDateString() }}">
          <i class="bi bi-clock-fill"></i><span class="ml-2">{{ $post->created_at->diffForHumans() }}</span>
        </span>
      </div>
      <div>&bull;</div>
      <div>
        {{-- 文章留言數 --}}
        <span>
          <i class="bi bi-chat-square-text-fill"></i><span class="ml-2">{{ $post->comment_count }}</span>
        </span>
      </div>
    </div>
  </div>

  {{-- 還原文章隱藏表單 --}}
  <form
    id="restore-post-{{ $post->id }}"
    action="{{ route('posts.restore', ['id' => $post->id]) }}"
    method="POST"
    class="hidden"
  >
    @csrf
  </form>

  <div class="flex items-center mt-2 md:mt-0">
    {{-- 還原文章 --}}
    <button
      x-on:click.stop="
        if (confirm('您確定恢復此文章嗎？')) {
          document.getElementById('restore-post-{{ $post->id }}').submit()
        }
      "
      type="button"
      class="inline-flex items-center justify-center w-10 h-10 transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md text-gray-50 hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300"
    >
      <i class="bi bi-arrow-counterclockwise"></i>
    </button>
  </div>
</x-card>
