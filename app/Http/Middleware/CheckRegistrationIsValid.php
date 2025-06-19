<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(! SettingService::isRegisterAllowed(), 503);

        return $next($request);
    }
}
