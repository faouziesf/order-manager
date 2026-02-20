<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté en tant que super-admin
        if (!Auth::guard('super-admin')->check()) {
            return redirect()->route('confirmi.home', ['login' => 1])
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $superAdmin = Auth::guard('super-admin')->user();
        
        // Vérifier si le super admin est actif
        if (!$superAdmin || !$superAdmin->is_active) {
            Auth::guard('super-admin')->logout();
            return redirect()->route('confirmi.home')
                ->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur système.');
        }

        return $next($request);
    }
}