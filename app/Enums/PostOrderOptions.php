<?php

declare(strict_types=1);

namespace App\Enums;

use App\Interfaces\OptionsInterface;

enum PostOrderOptions: string implements OptionsInterface
{
    case LATEST = 'latest';
    case RECENT = 'recent';
    case COMMENT = 'comment';

    public function label(): string
    {
        return match ($this) {
            self::LATEST => '最新文章',
            self::RECENT => '最近更新',
            self::COMMENT => '最多留言',
        };
    }

    public function iconComponentName(): string
    {
        return match ($this) {
            self::LATEST => 'icons.stars',
            self::RECENT => 'icons.wrench',
            self::COMMENT => 'icons.chat-square-text',
        };
    }
}
