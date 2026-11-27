<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\Comment;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CommentForm extends Form
{
    #[Validate(rule: ['required', 'numeric'], onUpdate: false)]
    public int $post_id;

    #[Validate(rule: ['nullable', 'numeric'], onUpdate: false)]
    public ?int $user_id = null;

    #[Validate(rule: ['nullable', 'numeric'], onUpdate: false)]
    public ?int $parent_id = null;

    #[Validate(
        rule: ['required', 'min:5', 'max:2000'],
        message: [
            'required' => '請填寫留言內容',
            'min' => '留言內容至少 5 個字元',
            'max' => '留言內容最多 2000 個字元',
        ],
        onUpdate: false,
    )]
    public string $body = '';

    /**
     * @return array{user_id: ?int, parent_id: ?int, body: string}
     */
    public function toCreateData(): array
    {
        return [
            'user_id'   => $this->user_id,
            'parent_id' => $this->parent_id,
            'body'      => $this->body,
        ];
    }

    public function update(Comment $comment): void
    {
        $this->post_id = $comment->post_id;
        $this->user_id = $comment->user_id;
        $this->parent_id = $comment->parent_id;

        $this->validate();

        $comment->update($this->all());
    }
}
