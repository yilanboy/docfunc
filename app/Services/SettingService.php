<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public static function isRegisterAllowed(): bool
    {
        $isRegisterAllow = Cache::remember('setting:allow_register', 3600, function () {
            return Setting::query()
                ->where('key', 'allow_register')
                ->first()
                ->value;
        });

        return filter_var($isRegisterAllow, FILTER_VALIDATE_BOOLEAN);
    }
}
