<?php

declare(strict_types=1);

namespace App\Enums;

use App\Interfaces\OptionsInterface;

enum UserInfoOptions: string implements OptionsInterface
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
            self::INFORMATION => 'icons.info-circle',
            self::POSTS => 'icons.file-earmark-richtext',
            self::COMMENTS => 'icons.chat-square-text',
        };
    }

    public function livewireComponentName(): string
    {
        return match ($this) {
            self::INFORMATION => 'shared.users.info-cards-part',
            self::POSTS => 'shared.users.posts-part',
            self::COMMENTS => 'shared.users.comments-part',
        };
    }
}
