<?php

declare(strict_types=1);

use App\Livewire\Forms\CommentForm;
use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

new class extends Component
{
    use MarkdownConverter;

    public CommentForm $form;

    public array $comment = [
        'id'        => null,
        'list_name' => '',
    ];

    public bool $previewIsEnable = false;

    public function mount(): void
    {
        if (! auth()->check()) {
            throw new Exception(message: 'Edit modal part component requires authentication.');
        }
    }

    public function save(): void
    {
        $comment = Comment::findOrFail($this->comment['id']);

        $this->authorize('update', $comment);

        $this->form->update($comment);

        $this->reset('previewIsEnable');

        $this->dispatch(event: 'update-comment-in-'.$this->comment['list_name'], id: $comment->id, body: $comment->body,
            updatedAt: $comment->updated_at);
    }
};
?>

@assets
@vite('resources/ts/markdown-helper.ts')
@endassets

@script
<script>
    Alpine.data('commentsEditModalPart', () => ({
        modal: {
            isOpen: false
        },
        openModal(event) {
            this.$wire.$set('comment.list_name', event.detail.listName);
            this.$wire.$set('comment.id', event.detail.id);
            this.$wire.$set('form.body', event.detail.body);

            this.modal.isOpen = true;

            this.$nextTick(() => this.$refs.editCommentTextarea?.focus());
        },
        tabToFourSpaces,
        submit() {
            this.$wire.save().then(() => {
                this.modal.isOpen = false;
            });
        }
    }));
</script>
@endscript

<div
    class="flex fixed inset-0 z-30 justify-center items-end min-h-screen"
    x-data="commentsEditModalPart"
    x-cloak
    x-show="modal.isOpen"
    x-on:open-edit-comment-modal.window="openModal"
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
                <span>編輯留言</span>
            </div>

            <form
                class="space-y-6"
                x-on:submit.prevent="submit"
            >
                <x-auth-validation-errors :errors="$errors" />

                <div
                    class="relative space-y-2"
                    wire:show="previewIsEnable"
                >
                    <div class="space-x-4">
                        <span class="font-semibold dark:text-zinc-50"> {{ auth()->user()->name }}</span>
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
                        id="edit-comment-body"
                        x-ref="editCommentTextarea"
                        x-on:keydown.tab.prevent="tabToFourSpaces"
                        wire:model="form.body"
                        rows="12"
                        placeholder="寫下你的留言吧！**支援 Markdown**"
                        required
                    />
                </div>

                <div class="flex justify-between items-center space-x-3">
                    <x-toggle-switch
                        id="edit-comment-modal-preview"
                        wire:model.live="previewIsEnable"
                        x-bind:disabled="$wire.form.body === ''"
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
