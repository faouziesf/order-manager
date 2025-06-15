<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateDeliveryConfiguration
{
    /**
     * Valider que l'admin a au moins une configuration de livraison active
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Vérifier si l'admin a au moins une configuration active
        $hasActiveConfig = $admin->deliveryConfigurations()
            ->where('is_active', true)
            ->exists();

        if (!$hasActiveConfig) {
            // Exclure certaines routes de cette vérification
            $excludedRoutes = [
                'admin.delivery.configuration.*',
                'admin.dashboard',
                'admin.profile.*',
            ];

            $currentRoute = $request->route()->getName();
            
            foreach ($excludedRoutes as $pattern) {
                if (fnmatch($pattern, $currentRoute)) {
                    return $next($request);
                }
            }

            return redirect()
                ->route('admin.delivery.configuration.index')
                ->with('warning', 'Vous devez configurer au moins un transporteur pour accéder aux fonctionnalités de livraison.');
        }

        return $next($request);
    }
}