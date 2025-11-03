<?php

declare(strict_types=1);

use App\Livewire\Forms\CommentForm;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewComment;
use App\Rules\Captcha;
use App\Traits\MarkdownConverter;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    use MarkdownConverter;

    public CommentForm $form;

    #[Locked]
    public int $postId;

    public string $captchaToken = '';

    public function mount(): void
    {
        $this->form->post_id = $this->postId;
        $this->form->user_id = auth()->id();
    }

    public function save(): void
    {
        $this->validate(
            rules: [
                'captchaToken' => ['required', new Captcha()],
            ],
            messages: [
                'captchaToken.required' => '未完成驗證',
            ],
        );

        // If the post has already been deleted.
        $post = Post::find(id: $this->postId, columns: ['id', 'user_id']);

        if (!$post) {
            $this->dispatch(event: 'toast', status: 'danger', message: '無法回覆！文章已被刪除！');

            $this->redirect(url: route('posts.index'), navigate: true);

            return;
        }

        // If the parent comment has already been deleted.
        if ($this->form->parent_id) {
            $parentIsExists = Comment::query()->whereId($this->form->parent_id)->wherePostId($post->id)->exists();

            if (!$parentIsExists) {
                $this->dispatch(event: 'toast', status: 'danger', message: '無法回覆！留言已被刪除！');

                return;
            }
        }

        $comment = $this->form->store();

        // Notify the article author of new comments.
        $post->user->notifyNewComment(new NewComment($comment));

        $this->dispatch(
            event: 'create-new-comment-to-' . ($comment->parent_id ?? 'root') . '-new-comment-group',
            comment: [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'body' => $comment->body,
                'created_at' => $comment->created_at->toDateTimeString(),
                'updated_at' => $comment->updated_at->toDateTimeString(),
                'user_name' => auth()->check() ? auth()->user()->name : null,
                'user_gravatar_url' => auth()->check() ? get_gravatar(auth()->user()->email) : null,
                'children_count' => 0,
            ],
        );

        $this->dispatch(event: 'append-new-id-to-' . ($comment->parent_id ?? 'root') . '-comment-list', id: $comment->id);

        $this->dispatch(event: 'close-create-comment-modal');

        $this->dispatch('reset-form-in-create-comment-modal');

        $this->dispatch(event: 'update-comments-count');

        $this->dispatch(event: 'toast', status: 'success', message: '成功新增留言！');
    }
};
?>

@assets
  @vite('resources/ts/markdown-helper.ts')
@endassets

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
        parentId: null,
        body: ''
      },
      captcha: {
        siteKey: @js(config('services.captcha.site_key')),
      },
      previewIsEnable: false,
      openModal(event) {
        this.comment.parentId = event.detail.parentId;

        this.modal.replyTo = event.detail.replyTo;
        this.modal.isOpen = true;

        this.$nextTick(() => this.$refs.createCommentTextarea?.focus());
      },
      closeModal() {
        this.modal.isOpen = false;
        this.previewIsEnable = false;
      },
      submit() {
        $wire.form.parent_id = this.comment.parentId;
        $wire.form.body = this.comment.body;
        $wire.save();
      },
      tabToFourSpaces,
      replyToLabel() {
        return `回覆 ${this.modal.replyTo} 的留言`;
      },
      previewChanged(event) {
        if (event.target.checked) {
          $wire.$set('form.body', this.comment.body, true);
        } else {
          this.$refs.convertedBody.innerHTML = '';
        }
      },
      init() {
        turnstile.ready(() => {
          turnstile.render(this.$refs.turnstileBlock, {
            sitekey: this.captcha.siteKey,
            callback: (token) => {
              $wire.captchaToken = token;
              this.modal.isSubmitEnabled = true;
            }
          });
        });

        $wire.on('reset-form-in-create-comment-modal', () => {
          this.comment.parentId = null;
          this.comment.body = '';
        });

        let previewObserver = highlightObserver(this.$refs.createCommentModal)
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
        x-show="modal.replyTo !== ''"
        x-text="replyToLabel"
      ></div>

      <form
        class="space-y-6"
        x-on:submit.prevent="submit"
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
          x-show="!previewIsEnable"
        >
          <x-floating-label-textarea
            class="font-jetbrains-mono"
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
            x-on:change="previewChanged"
            x-bind:disabled="comment.body === ''"
          >
            預覽
          </x-toggle-switch>

          <x-button x-bind:disabled="modal.isSubmitEnabled === false">
            <x-icons.reply-fill
              class="mr-2 w-5"
              x-cloak
              x-show="modal.isSubmitEnabled"
            />
            <x-icons.animate-spin
              class="mr-2 h-5 w-5 text-zinc-50"
              x-cloak
              x-show="modal.isSubmitEnabled === false"
            />
            <span x-text="modal.isSubmitEnabled ? '回覆' : '驗證中'"></span>
          </x-button>
        </div>
      </form>
    </div>
  </div>
</div>
