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

new class extends Component
{
    use MarkdownConverter;

    public CommentForm $form;

    #[Locked]
    public int $postId;

    public string $captchaToken = '';

    public bool $previewIsEnable = false;

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

        if (! $post) {
            $this->dispatch(event: 'toast', status: 'danger', message: '無法回覆！文章已被刪除！');

            $this->redirect(url: route('posts.index'), navigate: true);

            return;
        }

        // If the parent comment has already been deleted.
        if ($this->form->parent_id) {
            $parentIsExists = Comment::query()->whereId($this->form->parent_id)->wherePostId($post->id)->exists();

            if (! $parentIsExists) {
                $this->dispatch(event: 'toast', status: 'danger', message: '無法回覆！留言已被刪除！');

                return;
            }
        }

        $comment = $this->form->store();

        // Notify the article author of new comments.
        $post->user->notifyNewComment(new NewComment($comment));

        $listName = $comment->parent_id === null ? 'root-list' : 'comment-'.$comment->parent_id.'-children-list';

        $this->dispatch(
            event: 'create-comment-in-'.$listName,
            comment: [
                'id'                => $comment->id,
                'user_id'           => $comment->user_id,
                'body'              => $comment->body,
                'created_at'        => $comment->created_at->toDateTimeString(),
                'updated_at'        => $comment->updated_at->toDateTimeString(),
                'user_name'         => auth()->check() ? auth()->user()->name : null,
                'user_gravatar_url' => auth()->check() ? get_gravatar(auth()->user()->email) : null,
                'children_count'    => 0,
            ],
        );

        $this->reset('previewIsEnable');

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
        modal: {
            isOpen: false,
            isSubmitEnabled: false,
            replyTo: ''
        },
        captcha: {
            siteKey: @js(config('services.captcha.site_key')),
        },
        openModal(event) {
            this.$wire.$set('form.parent_id', event.detail.parentId);

            this.modal.replyTo = event.detail.replyTo;
            this.modal.isOpen = true;

            this.$nextTick(() => this.$refs.createCommentTextarea?.focus());
        },
        tabToFourSpaces,
        replyToLabel() {
            return `回覆 ${this.modal.replyTo} 的留言`;
        },
        submit() {
            this.$wire.save().then(() => {
                this.modal.isOpen = false;
            });
        },
        init() {
            turnstile.ready(() => {
                turnstile.render(this.$refs.turnstileBlock, {
                    sitekey: this.captcha.siteKey,
                    callback: (token) => {
                        this.$wire.captchaToken = token;
                        this.modal.isSubmitEnabled = true;
                    }
                });
            });
        }
    }));
</script>
@endscript

<div
    class="flex fixed inset-0 z-30 justify-center items-end min-h-screen"
    x-data="commentsCreateModalPart"
    x-cloak
    x-show="modal.isOpen"
    x-on:open-create-comment-modal.window="openModal"
    x-on:keydown.escape.window="modal.isOpen = false"
>
    {{-- gray background --}}
    <div
        class="fixed inset-0 transition-opacity bg-zinc-500/75"
        x-show="modal.isOpen"
        x-transition.opacity
    ></div>

    {{--  modal  --}}
    <div
        class="overflow-auto relative p-5 mx-2 w-full rounded-tl-xl rounded-tr-xl transition-all transform md:max-w-2xl bg-zinc-50 dark:bg-zinc-800"
        x-show="modal.isOpen"
        x-transition.origin.bottom.duration.300ms
    >
        {{-- close modal button --}}
        <div class="absolute top-5 right-5">
            <button
                class="cursor-pointer text-zinc-400 dark:hover:text-zinc-300 hover:text-zinc-500"
                type="button"
                x-on:click="modal.isOpen = false"
            >
                <x-icons.x class="size-8" />
            </button>
        </div>

        <div class="flex flex-col gap-5">
            <div class="flex justify-center items-center space-x-2 text-2xl text-zinc-900 dark:text-zinc-50">
                <x-icons.chat-dots class="w-8" />
                <span>新增留言</span>
            </div>

            <div
                class="py-2 px-4 w-full rounded-lg bg-zinc-200/60 dark:bg-zinc-700/60 dark:text-zinc-50"
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
                    wire:show="previewIsEnable"
                >
                    <div class="relative space-x-4">
                        <span class="font-semibold dark:text-zinc-50">
                            {{ auth()->check() ? auth()->user()->name : '訪客' }}
                        </span>
                        <span class="text-zinc-400">{{ now()->format('Y 年 m 月 d 日') }}</span>
                    </div>

                    <div class="overflow-auto h-80 rich-text">
                        {!! $this->convertToHtml($this->form->body) !!}
                    </div>

                    <x-icons.animate-spin
                        class="absolute top-1/2 left-1/2 w-10 -translate-x-1/2 -translate-y-1/2 dark:text-zinc-50"
                        wire:loading.delay
                        wire:target="form.body"
                    />
                </div>

                <div wire:show="!previewIsEnable">
                    <x-floating-label-textarea
                        class="font-jetbrains-mono"
                        id="create-comment-body"
                        x-ref="createCommentTextarea"
                        {{-- change tab into 4 spaces --}}
                        x-on:keydown.tab.prevent="tabToFourSpaces"
                        wire:model="form.body"
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

                <div class="flex justify-between items-center space-x-3">
                    <x-toggle-switch
                        id="create-comment-modal-preview"
                        wire:model.live="previewIsEnable"
                        x-bind:disabled="$wire.form.body === ''"
                    >
                        預覽
                    </x-toggle-switch>

                    <x-button
                        id="create-comment-submit-button"
                        x-bind:disabled="modal.isSubmitEnabled === false"
                    >
                        <x-icons.reply-fill
                            class="mr-2 w-5"
                            x-cloak
                            x-show="modal.isSubmitEnabled"
                        />
                        <x-icons.animate-spin
                            class="mr-2 w-5 h-5 text-zinc-50"
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
