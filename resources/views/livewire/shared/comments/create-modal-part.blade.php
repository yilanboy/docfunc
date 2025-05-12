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
        parentId: @entangle('form.parent_id'),
        body: @entangle('form.body')
      },
      captcha: {
        siteKey: @js(config('services.captcha.site_key')),
        token: @entangle('captchaToken')
      },
      openModal(event) {
        this.comment.parentId = event.detail.parentId;

        this.modal.replyTo = event.detail.replyTo;
        this.modal.isOpen = true;

        this.$nextTick(() => this.$refs.createCommentTextarea?.focus());
      },
      closeModal() {
        this.modal.isOpen = false;
      },
      submitModal() {
        this.$wire.save();
      },
      tabToFourSpaces() {
        this.$el.setRangeText('    ', this.$el.selectionStart, this.$el.selectionStart, 'end');
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
      init() {
        turnstile.ready(() => {
          turnstile.render(this.$refs.turnstileBlock, {
            sitekey: this.captcha.siteKey,
            callback: (token) => {
              this.captcha.token = token;
              this.modal.isSubmitEnabled = true;
            }
          });
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

        @if ($previewIsEnabled)
          <div class="space-y-2">
            <div class="space-x-4">
              <span class="font-semibold dark:text-zinc-50">
                {{ auth()->check() ? auth()->user()->name : '訪客' }}
              </span>
              <span class="text-zinc-400">{{ now()->format('Y 年 m 月 d 日') }}</span>
            </div>
            <div class="rich-text h-80 overflow-auto">
              {!! $this->removeHeadingInHtml($this->convertToHtml($this->form->body)) !!}
            </div>
          </div>
        @else
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
        @endif

        <div
          class="hidden"
          x-ref="turnstileBlock"
          wire:ignore
        ></div>

        <div class="flex items-center justify-between space-x-3">
          <x-toggle-switch
            id="create-comment-modal-preview"
            wire:model.live="previewIsEnabled"
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
