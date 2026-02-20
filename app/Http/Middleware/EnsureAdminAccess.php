<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('confirmi.home', ['login' => 1]);
        }

        $user = Auth::guard('admin')->user();
        if (!$user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Accès refusé. Cette section est réservée aux administrateurs.');
        }
        
        return $next($request);
    }
}