<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ValidateCarrierToken
{
    /**
     * Vérifier et rafraîchir les tokens des transporteurs si nécessaire
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return $next($request);
        }

        // Vérifier les tokens expirés ou sur le point d'expirer
        $expiredConfigs = $admin->deliveryConfigurations()
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '<=', now()->addMinutes(10)); // Rafraîchir 10 min avant expiration
            })
            ->get();

        foreach ($expiredConfigs as $config) {
            try {
                $config->refreshToken();
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas bloquer la requête
                \Log::warning('Token refresh failed for delivery configuration', [
                    'config_id' => $config->id,
                    'carrier' => $config->carrier_slug,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);

                // Mettre en cache l'échec pour éviter les tentatives répétées
                Cache::put(
                    "token_refresh_failed_{$config->id}",
                    true,
                    now()->addMinutes(30)
                );
            }
        }

        return $next($request);
    }
}   