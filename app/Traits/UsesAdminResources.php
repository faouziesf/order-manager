<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait UsesAdminResources
{
    /**
     * Obtenir l'admin associé selon le type de guard
     */
    protected function getAdmin()
    {
        $guard = $this->getGuard();

        if ($guard === 'admin') {
            return Auth::guard('admin')->user();
        }

        return null;
    }

    /**
     * Obtenir le guard actuel
     */
    protected function getGuard()
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }

        return null;
    }

    /**
     * Obtenir le chemin de vue selon le guard
     * Utilise les vues admin par défaut
     */
    protected function getViewPath(string $view)
    {
        $guard = $this->getGuard();

        return "{$guard}.{$view}";
    }

    /**
     * Obtenir le préfixe de route selon le guard
     */
    protected function getRoutePrefix()
    {
        $guard = $this->getGuard();
        return $guard ? "{$guard}." : '';
    }

    /**
     * Vérifier si l'utilisateur a accès à une fonctionnalité
     */
    protected function canAccess(string $feature)
    {
        $guard = $this->getGuard();

        // Admin a accès à tout
        if ($guard === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Obtenir le layout selon le guard
     */
    protected function getLayout()
    {
        $guard = $this->getGuard();

        // Tous utilisent le même layout admin (simplifié)
        return 'layouts.admin';
    }
}
