<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminExpiry
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();
        
        // Si l'administrateur n'est pas actif ou sa période d'essai a expiré
        if (!$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
            // Rediriger vers la page d'expiration
            return redirect()->route('admin.expired');
        }
        
        return $next($request);
    }
}