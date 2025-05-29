<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class SystemController extends Controller
{
    /**
     * Page d'expiration pour les admins
     */
    public function expired()
    {
        return view('system.expired');
    }
    
    /**
     * Page de maintenance
     */
    public function maintenance()
    {
        return view('system.maintenance');
    }
    
    /**
     * Vérification du statut du système
     */
    public function healthCheck()
    {
        try {
            $checks = [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'admin_expirations' => $this->checkAdminExpirations(),
                'system_stats' => $this->getSystemStats()
            ];
            
            $allHealthy = collect($checks)->every(function ($check) {
                return isset($check['status']) ? $check['status'] === 'ok' : true;
            });
            
            return response()->json([
                'status' => $allHealthy ? 'healthy' : 'warning',
                'timestamp' => now()->toISOString(),
                'checks' => $checks
            ]);
            
        } catch (\Exception $e) {
            Log::error('Health check failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'System check failed',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
    
    /**
     * API pour les notifications temps réel
     */
    public function getNotifications(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return response()->json(['notifications' => []]);
        }
        
        $admin = Auth::guard('admin')->user();
        $notifications = [];
        
        // Vérifier les produits à examiner
        $productsToReview = $admin->products()->where('needs_review', true)->count();
        if ($productsToReview > 0) {
            $notifications[] = [
                'id' => 'products_review_' . $productsToReview,
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Produits à examiner',
                'message' => "{$productsToReview} produit(s) créé(s) automatiquement nécessite(nt) votre attention",
                'action_url' => route('admin.products.review'),
                'action_text' => 'Examiner',
                'created_at' => now()
            ];
        }
        
        // Vérifier les commandes non assignées
        $unassignedOrders = $admin->orders()->where('is_assigned', false)->count();
        if ($unassignedOrders > 0) {
            $notifications[] = [
                'id' => 'unassigned_orders_' . $unassignedOrders,
                'type' => 'info',
                'icon' => 'fas fa-user-times',
                'title' => 'Commandes non assignées',
                'message' => "{$unassignedOrders} commande(s) en attente d'assignation",
                'action_url' => route('admin.orders.unassigned'),
                'action_text' => 'Voir',
                'created_at' => now()
            ];
        }
        
        // Vérifier les stocks faibles
        $lowStockProducts = $admin->products()->where('stock', '<=', 10)->where('stock', '>', 0)->count();
        if ($lowStockProducts > 0) {
            $notifications[] = [
                'id' => 'low_stock_' . $lowStockProducts,
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-circle',
                'title' => 'Stock faible',
                'message' => "{$lowStockProducts} produit(s) avec un stock faible",
                'action_url' => route('admin.products.index') . '?stock=low_stock',
                'action_text' => 'Voir',
                'created_at' => now()
            ];
        }
        
        // Vérifier les produits en rupture
        $outOfStockProducts = $admin->products()->where('stock', '<=', 0)->count();
        if ($outOfStockProducts > 0) {
            $notifications[] = [
                'id' => 'out_of_stock_' . $outOfStockProducts,
                'type' => 'danger',
                'icon' => 'fas fa-times-circle',
                'title' => 'Rupture de stock',
                'message' => "{$outOfStockProducts} produit(s) en rupture de stock",
                'action_url' => route('admin.products.index') . '?stock=out_of_stock',
                'action_text' => 'Voir',
                'created_at' => now()
            ];
        }
        
        // Vérifier l'expiration proche
        if ($admin->expiry_date) {
            $daysUntilExpiry = Carbon::now()->diffInDays(Carbon::parse($admin->expiry_date), false);
            if ($daysUntilExpiry <= 7 && $daysUntilExpiry > 0) {
                $notifications[] = [
                    'id' => 'expiry_warning_' . $daysUntilExpiry,
                    'type' => 'danger',
                    'icon' => 'fas fa-calendar-times',
                    'title' => 'Abonnement expire bientôt',
                    'message' => "Votre abonnement expire dans {$daysUntilExpiry} jour(s)",
                    'action_url' => route('admin.settings.index'),
                    'action_text' => 'Renouveler',
                    'created_at' => now()
                ];
            }
        }
        
        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications),
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Nettoyage automatique du système
     */
    public function cleanup(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        try {
            $results = [];
            
            // Nettoyer les sessions expirées
            DB::table('sessions')->where('last_activity', '<', now()->subHours(24)->timestamp)->delete();
            $results['sessions_cleaned'] = true;
            
            // Nettoyer le cache des vues
            if ($request->has('clear_cache')) {
                Cache::flush();
                $results['cache_cleared'] = true;
            }
            
            // Réinitialiser les compteurs journaliers des tentatives
            if (Carbon::now()->hour === 0) { // Minuit
                Order::query()->update(['daily_attempts_count' => 0]);
                $results['daily_attempts_reset'] = true;
            }
            
            // Log de l'opération
            Log::info('System cleanup performed', [
                'admin_id' => Auth::guard('admin')->id(),
                'results' => $results,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Nettoyage effectué avec succès',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('System cleanup failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du nettoyage'
            ], 500);
        }
    }
    
    /**
     * Vérification de la base de données
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            $adminCount = Admin::count();
            $orderCount = Order::count();
            
            return [
                'status' => 'ok',
                'message' => 'Base de données accessible',
                'stats' => [
                    'admins' => $adminCount,
                    'orders' => $orderCount
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Vérification du cache
     */
    private function checkCache()
    {
        try {
            $key = 'health_check_' . now()->timestamp;
            Cache::put($key, 'test', 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            
            return [
                'status' => $retrieved === 'test' ? 'ok' : 'error',
                'message' => $retrieved === 'test' ? 'Cache fonctionnel' : 'Problème de cache'
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de cache: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Vérification du stockage
     */
    private function checkStorage()
    {
        try {
            $storagePath = storage_path();
            $isWritable = is_writable($storagePath);
            $freeSpace = disk_free_space($storagePath);
            
            return [
                'status' => $isWritable ? 'ok' : 'warning',
                'message' => $isWritable ? 'Stockage accessible' : 'Problème d\'écriture',
                'free_space' => $freeSpace ? $this->formatBytes($freeSpace) : 'Inconnu'
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de stockage: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Vérification des expirations d'admins
     */
    private function checkAdminExpirations()
    {
        try {
            $expiringAdmins = Admin::where('expiry_date', '<=', now()->addDays(7))
                                  ->where('expiry_date', '>', now())
                                  ->where('is_active', true)
                                  ->count();
                                  
            $expiredAdmins = Admin::where('expiry_date', '<=', now())
                                 ->where('is_active', true)
                                 ->count();
            
            return [
                'status' => $expiredAdmins > 0 ? 'warning' : 'ok',
                'expiring_soon' => $expiringAdmins,
                'expired' => $expiredAdmins,
                'message' => $expiredAdmins > 0 ? 
                    "{$expiredAdmins} admin(s) expiré(s)" : 
                    'Aucune expiration critique'
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la vérification des expirations: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Statistiques système
     */
    private function getSystemStats()
    {
        try {
            return [
                'total_admins' => Admin::count(),
                'active_admins' => Admin::where('is_active', true)->count(),
                'total_orders' => Order::count(),
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'total_products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version()
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Formatage des octets
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// ============================================================================
// Routes à ajouter dans web.php
// ============================================================================

/*
// Routes système
Route::get('/expired', [SystemController::class, 'expired'])->name('expired');
Route::get('/maintenance', [SystemController::class, 'maintenance'])->name('maintenance');

// API système (protégé par auth)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/api/health', [SystemController::class, 'healthCheck'])->name('api.health');
    Route::get('/api/notifications', [SystemController::class, 'getNotifications'])->name('api.notifications');
    Route::post('/api/cleanup', [SystemController::class, 'cleanup'])->name('api.cleanup');
});
*/