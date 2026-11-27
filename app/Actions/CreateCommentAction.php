<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewComment;
use Illuminate\Support\Facades\DB;

final readonly class CreateCommentAction
{
    /**
     * @param  array{user_id: ?int, parent_id: ?int, body: string}  $data
     */
    public function handle(Post $post, array $data): Comment
    {
        $comment = DB::transaction(
            fn (): Comment => $post->comments()->create($data)
        );

        if ($post->user_id !== $comment->user_id) {
            $post->user->notify(new NewComment($comment));
        }

        return $comment;
    }
}
