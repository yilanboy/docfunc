<?php

declare(strict_types=1);

use App\Livewire\Forms\CommentForm;
use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

new class extends Component {
    use MarkdownConverter;

    public CommentForm $form;

    public function mount(): void
    {
        if (!auth()->check()) {
            throw new Exception(message: 'Edit modal part component requires authentication.');
        }
    }

    public function save(Comment $comment, string $listName): void
    {
        $this->authorize('update', $comment);

        $this->form->update($comment);

        $this->dispatch(event: 'update-comment-in-' . $listName, id: $comment->id, body: $comment->body, updatedAt: $comment->updated_at);
    }
};
?>

@assets
  @vite('resources/ts/markdown-helper.ts')
@endassets

@script
  <script>
    Alpine.data('commentsEditModalPart', () => ({
      observers: [],
      modal: {
        isOpen: false
      },
      comment: {
        id: null,
        body: ''
      },
      listName: null,
      previewIsEnable: false,
      openModal(event) {
        this.listName = event.detail.listName;
        this.comment.id = event.detail.id;
        this.comment.body = event.detail.body;
        this.modal.isOpen = true;

        this.$nextTick(() => this.$refs.editCommentTextarea?.focus());
      },
      closeModal() {
        this.modal.isOpen = false;
        this.previewIsEnable = false;
      },
      submit() {
        $wire.form.body = this.comment.body;
        $wire.save(this.comment.id, this.listName);
      },
      tabToFourSpaces,
      previewChanged(event) {
        if (event.target.checked) {
          $wire.$set('form.body', this.comment.body, true);
        } else {
          this.$refs.convertedBody.innerHTML = '';
        }
      },
      init() {
        $wire.intercept('save', ({
          onSuccess,
        }) => {
          onSuccess(() => {
            this.closeModal();
          })
        })

        let previewObserver = highlightObserver(this.$refs.editCommentModal)
        this.observers.push(previewObserver);
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
  class="fixed inset-0 z-30 flex min-h-screen items-end justify-center"
  x-cloak
  x-data="commentsEditModalPart"
  x-ref="editCommentModal"
  x-show="modal.isOpen"
  x-on:open-edit-comment-modal.window="openModal"
  x-on:keydown.escape.window="closeModal"
>
  {{-- gray background --}}
  <div
    class="fixed inset-0 bg-zinc-500/75 transition-opacity"
    x-show="modal.isOpen"
    x-transition.opacity
  ></div>

  {{--  modal  --}}
  <div
    class="relative mx-2 w-full transform overflow-auto rounded-tl-xl rounded-tr-xl bg-zinc-50 p-5 transition-all md:max-w-2xl dark:bg-zinc-800"
    x-show="modal.isOpen"
    x-transition.origin.bottom.duration.300ms
  >
    {{-- close modal button --}}
    <div class="absolute right-5 top-5">
      <button
        class="cursor-pointer text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300"
        type="button"
        x-on:click="closeModal"
      >
        <x-icons.x class="size-8" />
      </button>
    </div>

    <div class="flex flex-col gap-5">
      <div class="flex items-center justify-center space-x-2 text-2xl text-zinc-900 dark:text-zinc-50">
        <x-icons.chat-dots class="w-8" />
        <span>編輯留言</span>
      </div>

      <form
        class="space-y-6"
        x-on:submit.prevent="submit"
      >
        <x-auth-validation-errors :errors="$errors" />

        <div
          class="relative space-y-2"
          x-cloak
          x-show="previewIsEnable"
        >
          <div class="space-x-4">
            <span class="font-semibold dark:text-zinc-50"> {{ auth()->user()->name }}</span>
            <span class="text-zinc-400">{{ now()->format('Y 年 m 月 d 日') }}</span>
          </div>

          <div
            class="rich-text h-80 overflow-auto"
            x-ref="convertedBody"
          >
            {!! $this->convertToHtml($this->form->body) !!}
          </div>

          <x-icons.animate-spin
            class="absolute left-1/2 top-1/2 hidden w-10 -translate-x-1/2 -translate-y-1/2 dark:text-zinc-50"
            wire:loading.class.remove="hidden"
            wire:target="form.body"
          />
        </div>

        <div
          x-cloak
          x-show="!previewIsEnable"
        >
          <x-floating-label-textarea
            class="font-jetbrains-mono"
            id="edit-comment-body"
            x-ref="editCommentTextarea"
            x-on:keydown.tab.prevent="tabToFourSpaces"
            x-model="comment.body"
            rows="12"
            placeholder="寫下你的留言吧！**支援 Markdown**"
            required
          />
        </div>

        <div class="flex items-center justify-between space-x-3">
          <x-toggle-switch
            id="edit-comment-modal-preview"
            x-model="previewIsEnable"
            x-on:change="previewChanged"
            x-bind:disabled="comment.body === ''"
          >
            預覽
          </x-toggle-switch>

          <x-button>
            <x-icons.save class="w-5" />
            <span class="ml-2">更新</span>
          </x-button>
        </div>
      </form>
    </div>
  </div>
</div>
