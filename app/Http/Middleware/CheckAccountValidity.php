<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountValidity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        if (!$guard) {
            // Détecter automatiquement le garde actif
            if (Auth::guard('admin')->check()) {
                $guard = 'admin';
            }
        }

        if ($guard && Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();
            
            // Vérifier la validité du compte selon le type
            if (!$this->isAccountValid($user, $guard)) {
                // Déconnecter immédiatement l'utilisateur
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Rediriger vers la page d'expiration avec les informations
                return redirect()->route('expired')->with([
                    'expired_reason' => $this->getExpiredReason($user, $guard),
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_type' => $guard
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Vérifier si le compte est valide selon son type
     */
    private function isAccountValid($user, string $guard): bool
    {
        switch ($guard) {
            case 'admin':
                return $this->isAdminValid($user);
            default:
                return false;
        }
    }

    /**
     * Vérifier la validité d'un admin
     */
    private function isAdminValid($admin): bool
    {
        if (!$admin->is_active) {
            return false;
        }

        if ($admin->expiry_date && $admin->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Vérifier la validité d'un manager
     */
    private function isManagerValid($manager): bool
    {
        if (!$manager->is_active) {
            return false;
        }

        // Vérifier aussi l'admin parent
        $admin = $manager->admin;
        if (!$admin || !$this->isAdminValid($admin)) {
            return false;
        }

        return true;
    }

    /**
     * Vérifier la validité d'un employé
     */
    private function isEmployeeValid($employee): bool
    {
        if (!$employee->is_active) {
            return false;
        }

        // Vérifier aussi l'admin parent
        $admin = $employee->admin;
        if (!$admin || !$this->isAdminValid($admin)) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir la raison de l'expiration
     */
    private function getExpiredReason($user, string $guard): string
    {
        switch ($guard) {
            case 'admin':
                if (!$user->is_active) return 'inactive';
                if ($user->expiry_date && $user->expiry_date->isPast()) return 'expired';
                break;
        }

        return 'unknown';
    }
}