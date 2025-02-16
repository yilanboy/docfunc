<?php

namespace App\Enums;

enum UserInfoTab: string
{
    case INFORMATION = 'information';
    case POSTS = 'posts';
    case COMMENTS = 'comments';

    public function label(): string
    {
        return match ($this) {
            self::INFORMATION => '個人資訊',
            self::POSTS => '發布文章',
            self::COMMENTS => '留言紀錄',
        };
    }

    public function iconComponentName(): string
    {
        return match ($this) {
            self::INFORMATION => 'icon.info-circle',
            self::POSTS => 'icon.file-earmark-richtext',
            self::COMMENTS => 'icon.chat-square-text',
        };
    }

    public function livewireComponentName(): string
    {
        return match ($this) {
            self::INFORMATION => 'shared.users.info-cards',
            self::POSTS => 'shared.users.posts',
            self::COMMENTS => 'shared.users.comments',
        };
    }
}
