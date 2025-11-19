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
    ->withMiddleware(function (Middleware $middleware) {
        // Enregistrement des alias de middleware personnalisés
        $middleware->alias([
            'super-admin' => \App\Http\Middleware\EnsureSuperAdminAccess::class,
            'admin' => \App\Http\Middleware\EnsureAdminAccess::class,
            'manager' => \App\Http\Middleware\EnsureManagerAccess::class,
            'employee' => \App\Http\Middleware\EnsureEmployeeAccess::class,
            'check-admin-expiry' => \App\Http\Middleware\CheckAdminExpiry::class,
            'check_user_active' => \App\Http\Middleware\CheckUserActive::class,
            'super-admin.active' => \App\Http\Middleware\SuperAdminActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
