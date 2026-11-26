<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Live fingerprint terminals / LAN bridges POST punches here. They cannot
        // carry a CSRF token, so the route is authenticated by device token instead.
        $middleware->validateCsrfTokens(except: [
            'attendance/device-punch',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
