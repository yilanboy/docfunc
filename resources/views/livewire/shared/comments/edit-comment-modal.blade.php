@script
  <script>
    Alpine.data('editCommentModal', () => ({
      observers: [],
      modalIsOpen: false,
      id: null,
      body: @entangle('form.body'),
      groupName: null,
      openModal(event) {
        this.id = event.detail.id;
        this.body = event.detail.body;
        this.groupName = event.detail.groupName;

        this.modalIsOpen = true;
        this.$nextTick(() => this.$refs.editCommentTextarea?.focus());
      },
      closeModal() {
        this.modalIsOpen = false;
      },
      submitForm() {
        this.$wire.save(this.id, this.groupName);
      },
      tabToFourSpaces() {
        this.$el.setRangeText('    ', this.$el.selectionStart, this.$el.selectionStart, 'end');
      },
      bodyIsEmpty() {
        return this.body === '';
      },
      init() {
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
  x-data="editCommentModal"
  x-ref="editCommentModal"
  x-show="modalIsOpen"
  x-on:open-edit-comment-modal.window="openModal"
  x-on:close-edit-comment-modal.window="closeModal"
  x-on:keydown.escape.window="closeModal"
>
  {{-- gray background --}}
  <div
    class="fixed inset-0 bg-gray-500/75 transition-opacity"
    x-show="modalIsOpen"
    x-transition.opacity
  ></div>

  {{--  modal  --}}
  <div
    class="relative mx-2 w-full transform overflow-auto rounded-tl-xl rounded-tr-xl bg-gray-50 p-5 transition-all md:max-w-2xl dark:bg-gray-800"
    x-show="modalIsOpen"
    x-transition.origin.bottom.duration.300ms
  >
    {{-- close modal button --}}
    <div class="absolute right-5 top-5">
      <button
        class="cursor-pointer text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
        type="button"
        x-on:click="closeModal"
      >
        <x-icons.x class="size-8" />
      </button>
    </div>

    <div class="flex flex-col gap-5">
      <div class="flex items-center justify-center space-x-2 text-2xl text-gray-900 dark:text-gray-50">
        <x-icons.chat-dots class="w-8" />
        <span>編輯留言</span>
      </div>

      <form
        class="space-y-6"
        x-on:submit.prevent="submitForm"
      >
        <x-auth-validation-errors :errors="$errors" />

        @if ($previewIsEnabled)
          <div class="space-y-2">
            <div class="space-x-4">
              <span class="font-semibold dark:text-gray-50">{{ auth()->user()->name }}</span>
              <span class="text-gray-400">{{ now()->format('Y 年 m 月 d 日') }}</span>
            </div>
            <div class="rich-text h-80 overflow-auto">
              {!! $this->removeHeadingInHtml($this->convertToHtml($this->form->body)) !!}
            </div>
          </div>
        @else
          <x-floating-label-textarea
            id="edit-comment-body"
            x-ref="editCommentTextarea"
            x-on:keydown.tab.prevent="tabToFourSpaces"
            x-model="body"
            rows="12"
            placeholder="寫下你的留言吧！**支援 Markdown**"
            required
          />
        @endif

        <div class="flex items-center justify-between space-x-3">
          <x-toggle-switch
            id="edit-comment-modal-preview"
            wire:model.live="previewIsEnabled"
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
