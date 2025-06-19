<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return $user->isAuthorOf($comment);
    }

    public function destroy(User $user, Comment $comment): bool
    {
        return $user->isAuthorOf($comment) || $user->isAuthorOf($comment->post);
    }
}
