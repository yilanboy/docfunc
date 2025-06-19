<?php

declare(strict_types=1);

namespace App\Enums;

use App\Interfaces\OptionsInterface;

enum CommentOrderOptions: string implements OptionsInterface
{
    case POPULAR = 'popular';

    case LATEST = 'latest';

    case OLDEST = 'oldest';

    public function label(): string
    {
        return match ($this) {
            self::POPULAR => '熱門留言',
            self::LATEST => '由新到舊',
            self::OLDEST => '由舊到新',
        };
    }
}
