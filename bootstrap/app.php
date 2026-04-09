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
        $middleware->replace(
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\Authenticate::class
        );

        // Ajouter no-cache headers à toutes les réponses web
        $middleware->web(append: [
            \App\Http\Middleware\NoCacheHeaders::class,
        ]);

        // Enregistrement des alias de middleware personnalisés
        $middleware->alias([
            'super-admin' => \App\Http\Middleware\EnsureSuperAdminAccess::class,
            'admin' => \App\Http\Middleware\EnsureAdminAccess::class,
            'manager' => \App\Http\Middleware\EnsureManagerAccess::class,
            'employee' => \App\Http\Middleware\EnsureEmployeeAccess::class,
            'check-admin-expiry' => \App\Http\Middleware\CheckAdminExpiry::class,
            'check_user_active' => \App\Http\Middleware\CheckUserActive::class,
            'super-admin.active' => \App\Http\Middleware\SuperAdminActive::class,
            'confirmi' => \App\Http\Middleware\EnsureConfirmiAccess::class,
            'confirmi.commercial' => \App\Http\Middleware\EnsureConfirmiCommercial::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gérer TokenMismatchException (419 CSRF) - rediriger au lieu d'afficher une erreur
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if (\Illuminate\Support\Facades\Auth::guard('confirmi')->check()) {
                return redirect()->route('confirmi.dashboard')
                    ->with('info', 'Votre session a été rafraîchie.');
            }
            if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard')
                    ->with('info', 'Votre session a été rafraîchie.');
            }
            if (\Illuminate\Support\Facades\Auth::guard('super-admin')->check()) {
                return redirect()->route('super-admin.dashboard')
                    ->with('info', 'Votre session a été rafraîchie.');
            }
            return redirect()->route('confirmi.home')
                ->with('info', 'Votre session a expiré. Veuillez vous reconnecter.');
        });
    })->create();
