<?php

namespace App\Http\Middleware;

use App\Models\LoginHistory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordLoginHistory
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Enregistrer uniquement les connexions réussies
        if ($request->isMethod('post') && $request->is('*/login') && $response->getStatusCode() == 302) {
            $this->recordLogin($request);
        }

        return $response;
    }

    /**
     * Enregistrer la connexion dans l'historique
     */
    private function recordLogin(Request $request)
    {
        // Déterminer quel guard est utilisé
        $guards = ['admin', 'manager', 'employee', 'super-admin'];
        $user = null;
        $userType = null;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $userType = $this->getUserType($guard);
                break;
            }
        }

        if ($user && $userType) {
            try {
                LoginHistory::create([
                    'user_id' => $user->id,
                    'user_type' => $userType,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(),
                    'is_successful' => true,
                    'country' => $this->getCountryFromIp($request->ip()),
                    'city' => $this->getCityFromIp($request->ip()),
                    'device_type' => $this->getDeviceType($request->userAgent()),
                    'browser' => $this->getBrowserName($request->userAgent()),
                ]);
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas interrompre le processus de connexion
                \Log::error('Erreur lors de l\'enregistrement de l\'historique de connexion: ' . $e->getMessage());
            }
        }
    }

    /**
     * Convertir le nom du guard en type de modèle
     */
    private function getUserType($guard)
    {
        $mapping = [
            'admin' => 'App\Models\Admin',
            'manager' => 'App\Models\Manager',
            'employee' => 'App\Models\Employee',
            'super-admin' => 'App\Models\SuperAdmin',
        ];

        return $mapping[$guard] ?? null;
    }

    /**
     * Déterminer le type d'appareil à partir du User-Agent
     */
    private function getDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'Tablette';
        }
        
        return 'Ordinateur';
    }

    /**
     * Déterminer le navigateur à partir du User-Agent
     */
    private function getBrowserName($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'edge')) {
            return 'Edge';
        }
        
        return 'Autre';
    }

    /**
     * Obtenir le pays à partir de l'IP (version simplifiée)
     */
    private function getCountryFromIp($ip)
    {
        // Pour une version de production, vous pourriez utiliser un service comme GeoIP
        // Pour l'instant, on retourne null pour les IPs locales
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
            return 'Local';
        }
        
        return null; // À implémenter avec un service de géolocalisation
    }

    /**
     * Obtenir la ville à partir de l'IP (version simplifiée)
     */
    private function getCityFromIp($ip)
    {
        // Même logique que pour le pays
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
            return 'Local';
        }
        
        return null; // À implémenter avec un service de géolocalisation
    }
}