<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use MassPrunable;

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->whereNotNull('read_at')
            ->where('read_at', '<=', now()->subWeek());
    }
}
