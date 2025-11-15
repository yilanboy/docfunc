<?php

declare(strict_types=1);

use App\Enums\CommentOrderOptions;
use App\Models\Comment;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public int $postId;

    #[Locked]
    public int $postUserId;

    #[Locked]
    public int $commentCounts;

    #[Locked]
    public CommentOrderOptions $order = CommentOrderOptions::POPULAR;

    public function changeOrder(CommentOrderOptions $order): void
    {
        $this->order = $order;
    }
};
?>

@script
  <script>
    Alpine.data('commentsBoardPart', () => ({
      observers: [],
      orderDropdownIsOpen: false,
      changeOrder() {
        this.$wire.changeOrder(this.$el.dataset.orderValue);
        this.orderDropdownIsOpen = false;
      },
      init() {
        let highlightCommentObserver = highlightObserver(this.$root);
        this.observers.push(highlightCommentObserver);

        highlightAllInElement(this.$root);
      },
      destroy() {
        this.observers.forEach((observer) => {
          observer.disconnect();
        });
      }
    }));
  </script>
@endscript

<div
  class="w-full"
  x-data="commentsBoardPart"
>
  <div class="mt-6 w-full">
    <div class="flex justify-between">
      {{-- show comments count --}}
      <div class="flex items-center justify-center gap-6">
        <div class="flex items-center gap-2 dark:text-zinc-50">
          <x-icons.chat-square-text class="size-5" />
          <span wire:text="commentCounts"></span>
          則留言
        </div>

        <div class="relative inline-block text-left">
          <button
            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 text-zinc-900 dark:text-zinc-50"
            data-test-id="comments.order.toggle"
            type="button"
            x-on:click="orderDropdownIsOpen = true"
          >
            <x-icons.animate-spin
              class="size-5"
              wire:loading
              wire:target="changeOrder"
            />
            <x-icons.filter-left
              class="size-5"
              wire:loading.remove
              wire:target="changeOrder"
            />
            <span>排序依據</span>
          </button>

          <div
            class="focus:outline-hidden absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-zinc-50 shadow-lg ring-1 ring-black/5 dark:bg-zinc-800 dark:ring-white/20"
            x-show="orderDropdownIsOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            x-on:click.outside="orderDropdownIsOpen = false"
          >
            <div class="w-full py-1">
              @foreach (CommentOrderOptions::cases() as $commentOrder)
                <button
                  data-order-value="{{ $commentOrder->value }}"
                  data-test-id="comments.order.option"
                  type="button"
                  @class([
                      'flex w-full justify-start px-4 py-2 cursor-pointer',
                      'bg-zinc-200 text-zinc-900 outline-hidden dark:bg-zinc-600 dark:text-zinc-50' =>
                          $order === $commentOrder,
                      'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700' =>
                          $order !== $commentOrder,
                  ])
                  x-on:click="changeOrder"
                  wire:key="{{ $commentOrder->value }}-comment-order"
                >{{ $commentOrder->label() }}</button>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <button
        class="before:bg-lividus-600 dark:bg-lividus-700 group relative cursor-pointer overflow-hidden rounded-xl bg-emerald-600 px-6 py-2 [transform:translateZ(0)] before:absolute before:bottom-0 before:left-0 before:h-full before:w-full before:origin-[100%_100%] before:scale-x-0 before:transition before:duration-500 before:ease-in-out hover:before:origin-[0_0] hover:before:scale-x-100 dark:before:bg-emerald-700"
        type="button"
        x-on:click="$dispatch('open-create-comment-modal', {
          parentId: null,
          replyTo: ''
        })"
      >
        <div class="relative z-0 flex items-center text-lg text-zinc-200 transition duration-500 ease-in-out">
          <x-icons.chat-dots class="w-5" />

          @if (auth()->check())
            <span class="ml-2">新增留言</span>
          @else
            <span class="ml-2">訪客留言</span>
          @endif
        </div>
      </button>
    </div>
  </div>

  {{-- root comment list --}}
  <livewire:comments.list
    :post-id="$postId"
    :post-user-id="$postUserId"
    :order="$order"
    :key="'comments-order-by-' . $order->value"
  />

  {{-- create comment modal --}}
  <livewire:comments.create-modal :post-id="$postId" />

  @auth
    {{-- edit comment modal --}}
    <livewire:comments.edit-modal />
  @endauth
</div>
