<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     * Utilise uniquement le guard admin pour tous les utilisateurs
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Utiliser uniquement le guard admin
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();

            // Vérifier si l'utilisateur est inactif
            if (isset($user->is_active) && !$user->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.');
            }

            // Vérifier la date d'expiration (uniquement pour les vrais admins)
            if ($user->isAdmin() && isset($user->expiry_date) && $user->expiry_date && $user->expiry_date->isPast()) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('expired');
            }
        }

        return $next($request);
    }
}
