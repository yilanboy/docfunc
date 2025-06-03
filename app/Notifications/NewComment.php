<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewComment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Comment $comment) {}

    public function via(object $notifiable): array
    {
        // 開啟通知的頻道
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $post = $this->comment->post;
        $link = route('comments.show', ['id' => $this->comment->id]);

        // 存入資料庫裡的數據
        return [
            'comment_id' => $this->comment->id,
            'post_link' => $link,
            'post_id' => $post->id,
            'post_title' => $post->title,
        ];
    }
}
