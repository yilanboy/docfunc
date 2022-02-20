<div
  x-data="{
      commentId: 0,
      commentTo: '',
      commentBoxOpen: false
    }"
  @set-comment-box-open.window="commentBoxOpen = $event.detail.open"
  @set-comment-id.window="commentId = $event.detail"
  @set-comment-to.window="commentTo = $event.detail"
  @comment-box-focus.window="$nextTick(() => { $refs.commentBox.focus() })"
  class="w-full xl:w-2/3"
>

  <div class="flex justify-between mt-6">
    {{-- 顯示留言數目 --}}
    <span class="flex items-center dark:text-gray-50">
      <i class="bi bi-chat-square-text-fill"></i>
      <span class="ml-2">{{ $commentCount }} 則留言</span>
    </span>

    <button
      x-on:click="
        commentBoxOpen = true
        commentId = 0
        commentTo = '回覆此文章'
        $nextTick(() => { $refs.commentBox.focus() })
      "
      type="button"
      class="group [transform:translateZ(0)] px-6 py-3 rounded-xl bg-emerald-500 overflow-hidden relative before:absolute before:bg-blue-600 before:bottom-0 before:left-0 before:h-full before:w-full before:origin-[100%_100%] before:scale-x-0 hover:before:origin-[0_0] hover:before:scale-x-100 before:transition before:ease-in-out before:duration-500"
    >
      <span class="relative z-0 text-xl font-semibold text-gray-200 transition duration-500 ease-in-out">
        <i class="bi bi-pencil-fill"></i><span class="ml-2">新增留言</span>
      </span>
    </button>
  </div>

  @auth
    {{-- 留言表單 Modal --}}
    <div
      x-cloak
      x-show="commentBoxOpen"
      x-on:keydown.window.escape="commentBoxOpen = false"
      class="fixed inset-0 z-20 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        x-cloak
        x-show="commentBoxOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
      >
        <div
          class="fixed inset-0 transition-opacity bg-gray-500/75 -z-10"
          aria-hidden="true"
        ></div>

        {{-- This element is to trick the browser into centering the modal contents. --}}
        <span
          class="hidden sm:inline-block sm:align-middle sm:h-screen"
          aria-hidden="true"
        >&#8203;</span>

        {{-- 留言表單 --}}
        <div
          x-cloak
          x-show="commentBoxOpen"
          x-transition:enter="ease-out duration-300"
          x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
          x-transition:leave="ease-in duration-200"
          x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
          x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          x-on:click.outside="commentBoxOpen = false"
          x-trap.noscroll="commentBoxOpen"
          class="inline-block p-5 overflow-hidden text-left align-bottom transition-all rounded-lg shadow-xl bg-gray-50 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-700"
        >
          {{-- 留言提示 --}}
          <div class="text-gray-700 dark:text-gray-50">
            <div x-text="commentTo"></div>
          </div>

          <div class="mt-5 sm:flex sm:items-start">
            <label
              for="commentBox"
              class="hidden"
            >留言內容</label>
            <textarea
              id="commentBox"
              x-ref="commentBox"
              wire:model.lazy="content"
              placeholder="分享你的想法 ~"
              rows="5"
              class="w-full border border-gray-300 rounded-md shadow-sm form-textarea focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-600 dark:text-gray-50 dark:placeholder-white"
            ></textarea>
          </div>

          <div class="mt-5 sm:flex sm:flex-row-reverse">
            <button
              x-on:click="$wire.store(commentId)"
              type="button"
              class="inline-flex justify-center w-full px-4 py-2 text-base font-medium bg-blue-600 border border-transparent rounded-md shadow-sm text-gray-50 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              留言
            </button>
            <button
              x-on:click="commentBoxOpen = false"
              type="button"
              class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 border border-gray-300 rounded-md shadow-sm bg-gray-50 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-500 dark:text-gray-50 dark:hover:bg-gray-600"
            >
              取消
            </button>

            <div class="flex justify-start flex-1">
              <div class="flex items-center justify-center">
                @error('content') <span class="text-red-400">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endauth
</div>