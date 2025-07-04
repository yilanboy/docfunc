@script
  <script>
    Alpine.data('commentsCreateModalPart', () => ({
      observers: [],
      modal: {
        isOpen: false,
        isSubmitEnabled: false,
        replyTo: '',
      },
      comment: {
        body: ''
      },
      captcha: {
        siteKey: @js(config('services.captcha.site_key')),
      },
      previewIsEnable: false,
      openModal(event) {
        this.$wire.form.parent_id = event.detail.parentId;

        this.modal.replyTo = event.detail.replyTo;
        this.modal.isOpen = true;

        this.$nextTick(() => this.$refs.createCommentTextarea?.focus());
      },
      closeModal() {
        this.modal.isOpen = false;
        this.previewIsEnable = false;
      },
      submitModal() {
        this.$wire.form.body = this.comment.body;
        this.$wire.save();
      },
      tabToFourSpaces() {
        const TAB_SPACE = '    ';
        const start = this.$el.selectionStart;
        const end = this.$el.selectionEnd;
        const value = this.$el.value;

        // Find line boundaries for the selection
        // If lastIndexOf finds a newline: returns the index of \n, then +1 gives us the character right after it (start of next line)
        // If lastIndexOf doesn't find a newline: returns -1, then -1 + 1 = 0 (start of the string, which is the first line)
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
      submitIsDisabled() {
        return this.modal.isSubmitEnabled === false;
      },
      informationOnSubmitButton() {
        return this.modal.isSubmitEnabled ? '回覆' : '驗證中';
      },
      showReplyToLabel() {
        return this.modal.replyTo !== '';
      },
      replyToLabel() {
        return '回覆 ' + this.modal.replyTo + ' 的留言';
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

        turnstile.ready(() => {
          turnstile.render(this.$refs.turnstileBlock, {
            sitekey: this.captcha.siteKey,
            callback: (token) => {
              this.$wire.captchaToken = token;
              this.modal.isSubmitEnabled = true;
            }
          });
        });

        this.$wire.on('reset-body-in-create-comment-modal', () => {
          this.comment.body = '';
        });

        let previewObserver = new MutationObserver(() => {
          this.$refs.createCommentModal
            .querySelectorAll('pre code:not(.hljs)')
            .forEach((element) => {
              hljs.highlightElement(element);
            });
        });

        previewObserver.observe(this.$refs.createCommentModal, {
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
  x-data="commentsCreateModalPart"
  x-ref="createCommentModal"
  x-show="modal.isOpen"
  x-on:open-create-comment-modal.window="openModal"
  x-on:close-create-comment-modal.window="closeModal"
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
        <span>新增留言</span>
      </div>

      <div
        class="w-full rounded-lg bg-zinc-200/60 px-4 py-2 dark:bg-zinc-700/60 dark:text-zinc-50"
        x-cloak
        x-show="showReplyToLabel"
        x-text="replyToLabel"
      ></div>

      <form
        class="space-y-6"
        x-on:submit.prevent="submitModal"
      >
        <x-auth-validation-errors :errors="$errors" />

        <div
          class="space-y-2"
          x-cloak
          x-show="previewIsEnable"
        >
          <div class="relative space-x-4">
            <span class="font-semibold dark:text-zinc-50">
              {{ auth()->check() ? auth()->user()->name : '訪客' }}
            </span>
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
            id="create-comment-body"
            x-ref="createCommentTextarea"
            {{-- change tab into 4 spaces --}}
            x-on:keydown.tab.prevent="tabToFourSpaces"
            x-model="comment.body"
            rows="12"
            placeholder="寫下你的留言吧！**支援 Markdown**"
            required
          />
        </div>

        <div
          class="hidden"
          x-ref="turnstileBlock"
          wire:ignore
        ></div>

        <div class="flex items-center justify-between space-x-3">
          <x-toggle-switch
            id="create-comment-modal-preview"
            x-model="previewIsEnable"
            x-bind:disabled="bodyIsEmpty"
          >
            預覽
          </x-toggle-switch>

          <x-button x-bind:disabled="submitIsDisabled">
            <x-icons.reply-fill
              class="mr-2 w-5"
              x-cloak
              x-show="modal.isSubmitEnabled"
            />
            <x-icons.animate-spin
              class="mr-2 h-5 w-5 text-zinc-50"
              x-cloak
              x-show="submitIsDisabled"
            />
            <span x-text="informationOnSubmitButton"></span>
          </x-button>
        </div>
      </form>
    </div>
  </div>
</div>
