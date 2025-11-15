<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\MiddlewarePriority;
use App\Http\Middleware\EnsureEmailIsVerifiedCustom;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\TeacherMiddleware;
use App\Http\Middleware\DeanMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias your route middleware here
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'teacher' => TeacherMiddleware::class,
            'dean' => DeanMiddleware::class,
            'verified.custom' => EnsureEmailIsVerifiedCustom::class,
        ]);

        // Optional: global middleware can be added here if needed
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling if needed
    })
    ->create();

// Register middleware for the web group (Laravel 11 style)
MiddlewarePriority::forWeb([
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,

    // Your custom route middleware
    EnsureEmailIsVerifiedCustom::class,
    AdminMiddleware::class,
    TeacherMiddleware::class,
    DeanMiddleware::class,
]);
