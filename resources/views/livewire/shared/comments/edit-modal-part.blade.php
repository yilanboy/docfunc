@script
  <script>
    Alpine.data('commentsEditModalPart', () => ({
      observers: [],
      modal: {
        isOpen: false
      },
      comment: {
        id: null,
        groupName: null,
        body: ''
      },
      previewIsEnable: false,
      openModal(event) {
        this.comment.id = event.detail.id;
        this.comment.groupName = event.detail.groupName;
        this.comment.body = event.detail.body;

        this.modal.isOpen = true;

        this.$nextTick(() => this.$refs.editCommentTextarea?.focus());
      },
      closeModal() {
        this.modal.isOpen = false;
        this.previewIsEnable = false;
      },
      submitModal() {
        this.$wire.form.body = this.comment.body;
        this.$wire.save(this.comment.id, this.comment.groupName);
      },
      tabToFourSpaces() {
        const TAB_SPACE = '    ';
        const start = this.$el.selectionStart;
        const end = this.$el.selectionEnd;
        const value = this.$el.value;

        let lineStart = value.lastIndexOf('\n', start - 1) + 1;
        let lineEnd = value.indexOf('\n', end);
        if (lineEnd === -1) {
          lineEnd = value.length;
        }

        const lines = value.substring(lineStart, lineEnd).split('\n');
        const indentedLines = lines.map(line => TAB_SPACE + line);

        this.$el.value = value.substring(0, lineStart) + indentedLines.join('\n') + value.substring(lineEnd);
        this.$el.selectionStart = start + TAB_SPACE.length;
        this.$el.selectionEnd = end + (TAB_SPACE.length * lines.length);
      },
      bodyIsEmpty() {
        return this.comment.body === '';
      },
      previewIsDisable() {
        return this.previewIsEnable === false;
      },
      init() {
        this.$watch('previewIsEnable', (value) => {
          if (value) {
            this.$wire.$set('form.body', this.comment.body, true);
          } else {
            this.$refs.convertedBody.innerHTML = '';
          }
        });

        let previewObserver = new MutationObserver(() => {
          this.$refs.editCommentModal
            .querySelectorAll('pre code:not(.hljs)')
            .forEach((element) => {
              hljs.highlightElement(element);
            });
        });

        previewObserver.observe(this.$refs.editCommentModal, {
          childList: true,
          subtree: true,
          attributes: true,
          characterData: false
        });

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
  x-on:close-edit-comment-modal.window="closeModal"
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
        x-on:submit.prevent="submitModal"
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
          x-show="previewIsDisable"
        >
          <x-floating-label-textarea
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
            x-bind:disabled="bodyIsEmpty"
          >
            預覽
          </x-toggle-switch>

          <x-button>
            <x-icons.reply-fill class="w-5" />
            <span class="ml-2">更新</span>
          </x-button>
        </div>
      </form>
    </div>
  </div>
</div>
