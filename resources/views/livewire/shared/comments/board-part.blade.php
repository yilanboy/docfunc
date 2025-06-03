@script
  <script>
    Alpine.data('commentsBoardPart', () => ({
      orderDropdownIsOpen: false,
      openOrderDropdown() {
        this.orderDropdownIsOpen = true;
      },
      closeOrderDropdown() {
        this.orderDropdownIsOpen = false;
      },
      changeOrder() {
        this.$wire.changeOrder(this.$el.dataset.order);
        this.orderDropdownIsOpen = false;
      },
      openCreateCommentModal() {
        this.$dispatch('open-create-comment-modal', {
          parentId: null,
          replyTo: ''
        });
      }
    }));
  </script>
@endscript

@php
  use App\Enums\CommentOrderOptions;
@endphp

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
          <span>{{ $commentCounts }} 則留言</span>
        </div>

        <div class="relative inline-block text-left">
          <div>
            <button
              class="inline-flex w-full cursor-pointer items-center justify-center gap-2 text-zinc-900 dark:text-zinc-50"
              type="button"
              x-on:click="openOrderDropdown"
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
          </div>

          <div
            class="focus:outline-hidden absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-zinc-50 shadow-lg ring-1 ring-black/5 dark:bg-zinc-800 dark:ring-white/20"
            x-show="orderDropdownIsOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            x-on:click.outside="closeOrderDropdown"
          >
            <div class="w-full py-1">
              @foreach (CommentOrderOptions::cases() as $commentOrder)
                <button
                  data-order="{{ $commentOrder->value }}"
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
        {{-- the comment group name should be full name --}}
        x-on:click="openCreateCommentModal"
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

  {{-- new root comment will show here --}}
  <livewire:shared.comments.group-part
    :post-id="$postId"
    :post-user-id="$postUserId"
    :comment-group-name="'root-new-comment-group'"
    :key="'root-new-comment-group-order-by-' . $order->value"
  />

  {{-- root comment list --}}
  <livewire:shared.comments.list-part
    :post-id="$postId"
    :post-user-id="$postUserId"
    :order="$order"
    :key="'root-comment-list-order-by-' . $order->value"
  />

  {{-- create comment modal --}}
  <livewire:shared.comments.create-modal-part :post-id="$postId" />

  @auth
    {{-- edit comment modal --}}
    <livewire:shared.comments.edit-modal-part />
  @endauth
</div>
