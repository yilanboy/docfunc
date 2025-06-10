<?php

namespace App\Livewire\Shared\Comments;

use App\Livewire\Forms\CommentForm;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewComment;
use App\Rules\Captcha;
use App\Traits\MarkdownConverter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class CreateModalPart extends Component
{
    use MarkdownConverter;

    public CommentForm $form;

    #[Locked]
    public int $postId;

    public string $captchaToken = '';

    protected function rules(): array
    {
        return [
            'captchaToken' => ['required', new Captcha],
        ];
    }

    protected function messages(): array
    {
        return [
            'captchaToken.required' => '未完成驗證',
        ];
    }

    public function mount(): void
    {
        $this->form->post_id = $this->postId;
        $this->form->user_id = auth()->id();
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        // validate captcha
        $this->validate();

        // If post has already been deleted.
        $post = Post::find(id: $this->postId, columns: ['id', 'user_id']);

        if (is_null($post)) {
            $this->dispatch(event: 'toast', status: 'danger', message: '無法回覆！文章已被刪除！');

            $this->redirect(url: route('posts.index'), navigate: true);

            return;
        }

        // If parent comment has already been deleted.
        if (! is_null($this->form->parent_id)) {
            $parentComment = Comment::find(id: $this->form->parent_id, columns: ['id']);

            if (is_null($parentComment)) {
                $this->dispatch(event: 'toast', status: 'danger', message: '無法回覆！留言已被刪除！');

                return;
            }
        }

        $comment = $this->form->store();

        // Notify the article author of new comments.
        $post->user->notifyNewComment(new NewComment($comment));

        $this->dispatch(
            event: 'create-new-comment-to-'.($this->form->parent_id ?? 'root').'-new-comment-group',
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

        $this->dispatch(
            event: 'append-new-id-to-'.($this->form->parent_id ?? 'root').'-comment-list',
            id: $comment->id
        );

        $this->dispatch(event: 'close-create-comment-modal');

        $this->dispatch(event: 'update-comments-count');

        $this->dispatch(event: 'toast', status: 'success', message: '成功新增留言！');

        $this->reset('form.body', 'form.parent_id');
    }

    public function render(): View
    {
        return view('livewire.shared.comments.create-modal-part');
    }
}
