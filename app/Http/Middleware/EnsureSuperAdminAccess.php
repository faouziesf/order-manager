<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ajout de logs pour le débogage
        Log::info('EnsureSuperAdminAccess middleware triggered');
        Log::info('Auth::guard(super-admin)->check() = ' . (Auth::guard('super-admin')->check() ? 'true' : 'false'));
        
        if (!Auth::guard('super-admin')->check()) {
            Log::info('Redirecting to super-admin.login');
            return redirect()->route('super-admin.login');
        }
        
        Log::info('Proceeding with request');
        return $next($request);
    }
}