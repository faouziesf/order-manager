<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Récupérer l'historique pour l'admin et ses sous-comptes
        $query = LoginHistory::query()
            ->where(function($q) use ($admin) {
                // Historique de l'admin lui-même
                $q->where('user_type', 'App\Models\Admin')
                  ->where('user_id', $admin->id);
                
                // Historique des managers
                $managerIds = $admin->managers()->pluck('id');
                if ($managerIds->isNotEmpty()) {
                    $q->orWhere(function($subQ) use ($managerIds) {
                        $subQ->where('user_type', 'App\Models\Manager')
                             ->whereIn('user_id', $managerIds);
                    });
                }
                
                // Historique des employés
                $employeeIds = $admin->employees()->pluck('id');
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhere(function($subQ) use ($employeeIds) {
                        $subQ->where('user_type', 'App\Models\Employee')
                             ->whereIn('user_id', $employeeIds);
                    });
                }
            })
            ->with('user')
            ->latest('login_at');

        // Filtres
        if ($request->filled('user_type')) {
            $query->where('user_type', 'like', '%' . $request->user_type . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        if ($request->filled('is_successful')) {
            $query->where('is_successful', $request->boolean('is_successful'));
        }

        $loginHistories = $query->paginate(20);

        // Statistiques
        $baseQuery = LoginHistory::query()
            ->where(function($q) use ($admin) {
                $q->where('user_type', 'App\Models\Admin')
                  ->where('user_id', $admin->id);
                
                $managerIds = $admin->managers()->pluck('id');
                if ($managerIds->isNotEmpty()) {
                    $q->orWhere(function($subQ) use ($managerIds) {
                        $subQ->where('user_type', 'App\Models\Manager')
                             ->whereIn('user_id', $managerIds);
                    });
                }
                
                $employeeIds = $admin->employees()->pluck('id');
                if ($employeeIds->isNotEmpty()) {
                    $q->orWhere(function($subQ) use ($employeeIds) {
                        $subQ->where('user_type', 'App\Models\Employee')
                             ->whereIn('user_id', $employeeIds);
                    });
                }
            });

        $stats = [
            'total_logins' => $baseQuery->count(),
            'successful_logins' => $baseQuery->clone()->where('is_successful', true)->count(),
            'failed_logins' => $baseQuery->clone()->where('is_successful', false)->count(),
            'unique_ips' => $baseQuery->clone()->distinct('ip_address')->count(),
        ];

        return view('admin.login-history.index', compact('loginHistories', 'stats'));
    }

    public function show($userType, $userId, Request $request)
    {
        $admin = auth('admin')->user();
        
        // Convertir le type d'utilisateur en nom de classe complet
        $fullUserType = 'App\\Models\\' . $userType;
        
        // Vérifier que l'utilisateur appartient à cet admin
        $hasAccess = false;
        switch($fullUserType) {
            case 'App\Models\Admin':
                $hasAccess = $admin->id == $userId;
                break;
            case 'App\Models\Manager':
                $hasAccess = $admin->managers()->where('id', $userId)->exists();
                break;
            case 'App\Models\Employee':
                $hasAccess = $admin->employees()->where('id', $userId)->exists();
                break;
        }

        if (!$hasAccess) {
            abort(403, 'Accès non autorisé à cet historique.');
        }

        // Récupérer l'utilisateur
        $user = null;
        switch($fullUserType) {
            case 'App\Models\Admin':
                $user = \App\Models\Admin::find($userId);
                break;
            case 'App\Models\Manager':
                $user = \App\Models\Manager::find($userId);
                break;
            case 'App\Models\Employee':
                $user = \App\Models\Employee::find($userId);
                break;
        }

        // Query pour l'historique
        $query = LoginHistory::where('user_type', $fullUserType)
            ->where('user_id', $userId)
            ->latest('login_at');

        // Filtres
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        if ($request->filled('is_successful')) {
            $query->where('is_successful', $request->boolean('is_successful'));
        }

        $loginHistories = $query->paginate(15);

        // Statistiques pour cet utilisateur
        $baseQuery = LoginHistory::where('user_type', $fullUserType)
            ->where('user_id', $userId);

        $stats = [
            'total_logins' => $baseQuery->count(),
            'successful_logins' => $baseQuery->clone()->where('is_successful', true)->count(),
            'failed_logins' => $baseQuery->clone()->where('is_successful', false)->count(),
            'last_login' => $baseQuery->clone()
                ->where('is_successful', true)
                ->latest('login_at')
                ->first(),
        ];

        return view('admin.login-history.show', compact('loginHistories', 'stats', 'user', 'userType'));
    }

    /**
     * Méthode pour enregistrer une connexion (appelée depuis les contrôleurs d'auth)
     */
    public static function recordLogin($user, $request, $successful = true)
    {
        try {
            $userType = get_class($user);
            
            LoginHistory::create([
                'user_id' => $user->id,
                'user_type' => $userType,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'is_successful' => $successful,
                'country' => self::getCountryFromIp($request->ip()),
                'city' => self::getCityFromIp($request->ip()),
                'device_type' => self::getDeviceType($request->userAgent()),
                'browser' => self::getBrowserName($request->userAgent()),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'enregistrement de l\'historique de connexion: ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer une déconnexion
     */
    public static function recordLogout($user)
    {
        try {
            $userType = get_class($user);
            
            // Trouver la dernière connexion réussie sans logout
            $lastLogin = LoginHistory::where('user_type', $userType)
                ->where('user_id', $user->id)
                ->where('is_successful', true)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            if ($lastLogin) {
                $lastLogin->update(['logout_at' => now()]);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'enregistrement de la déconnexion: ' . $e->getMessage());
        }
    }

    /**
     * Méthodes utilitaires privées
     */
    private static function getDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'Tablette';
        }
        
        return 'Ordinateur';
    }

    private static function getBrowserName($userAgent)
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

    private static function getCountryFromIp($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
            return 'Local';
        }
        
        return null; // À implémenter avec un service de géolocalisation
    }

    private static function getCityFromIp($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
            return 'Local';
        }
        
        return null; // À implémenter avec un service de géolocalisation
    }
}