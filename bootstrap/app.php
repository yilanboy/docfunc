<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Webauthn\Exception\InvalidDataException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Symfony serializer error
        $exceptions->report(function (ExceptionInterface $e) {
            Log::error('A Symfony serializer happened: '.$e->getMessage());
        });

        // Passkey InvalidDataException
        $exceptions->report(function (InvalidDataException $e) {
            Log::error('Invalid data for generating passkey options: '.$e->getMessage());
        });
    })->create();
