<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public static function isRegisterAllowed(): bool
    {
        $isRegisterAllow = Setting::query()
            ->where('key', 'allow_register')
            ->first()
            ->value;

        return filter_var($isRegisterAllow, FILTER_VALIDATE_BOOLEAN);
    }
}
