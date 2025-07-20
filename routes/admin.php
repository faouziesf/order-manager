<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DuplicateOrdersController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExaminationController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProcessController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RestockController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SuspendedController;
use App\Http\Controllers\Admin\WooCommerceController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\PickupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PickupAddressController;  
use App\Http\Controllers\Admin\BLTemplateController;

// ========================================
// ROUTES ADMIN
// ========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // ========================================
    // AUTHENTIFICATION ADMIN
    // ========================================
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('expired', [AdminAuthController::class, 'showExpiredPage'])->name('expired');

    // ========================================
    // ROUTES PROTÉGÉES ADMIN
    // ========================================
    Route::middleware('auth:admin')->group(function () {
        // Dashboard
        Route::get('dashboard', function () {
            $admin = auth('admin')->user();
            if (!$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
                return redirect()->route('admin.expired');
            }
            return view('admin.dashboard');
        })->name('dashboard');

        // ========================================
        // GESTION DES PRODUITS
        // ========================================

        // Routes spéciales AVANT la route resource (ORDRE CRITIQUE)
        Route::get('products/review', [ProductController::class, 'reviewNewProducts'])
            ->name('products.review');
        Route::get('products/kanban', [ProductController::class, 'kanban'])
            ->name('products.kanban');
        Route::get('products/live-search', [ProductController::class, 'liveSearch'])
            ->name('products.live-search');
        Route::get('products/stats', [ProductController::class, 'getStats'])
            ->name('products.stats');
        Route::get('products/realtime-stats', [ProductController::class, 'getRealtimeStats'])
            ->name('products.realtime-stats');
        Route::get('products/search', [ProductController::class, 'searchProducts'])
            ->name('products.search');

        // Actions groupées AVANT resource
        Route::post('products/mark-all-reviewed', [ProductController::class, 'markAllAsReviewed'])
            ->name('products.mark-all-reviewed');
        Route::post('products/bulk-activate', [ProductController::class, 'bulkActivate'])
            ->name('products.bulk-activate');
        Route::post('products/bulk-deactivate', [ProductController::class, 'bulkDeactivate'])
            ->name('products.bulk-deactivate');
        Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])
            ->name('products.bulk-delete');

        // Actions sur des produits spécifiques AVANT resource
        Route::post('products/{product}/mark-reviewed', [ProductController::class, 'markAsReviewed'])
            ->name('products.mark-reviewed');

        // Route resource APRÈS toutes les routes spéciales
        Route::resource('products', ProductController::class);

        // ========================================
        // GESTION DES COMMANDES - SECTION CORRIGÉE
        // ========================================

        // ⚠️ ROUTES SPÉCIALES AVANT RESOURCE (ORDRE CRITIQUE POUR ÉVITER 404) ⚠️
        Route::get('orders/check-phone-duplicates', [OrderController::class, 'checkPhoneForDuplicates'])
            ->name('orders.check-phone-duplicates');
        Route::get('orders/client-history', [OrderController::class, 'getClientHistory'])
            ->name('orders.client-history');
        Route::get('orders/search-products', [OrderController::class, 'searchProducts'])
            ->name('orders.search-products');
        Route::get('orders/get-regions', [OrderController::class, 'getRegions'])
            ->name('orders.get-regions');
        Route::get('orders/get-cities', [OrderController::class, 'getCities'])
            ->name('orders.get-cities');
        Route::get('orders/unassigned', [OrderController::class, 'unassigned'])
            ->name('orders.unassigned');

        // Actions groupées AVANT resource
        Route::post('orders/bulk-assign', [OrderController::class, 'bulkAssign'])
            ->name('orders.bulk-assign');

        // Actions sur commandes spécifiques AVANT resource
        Route::post('orders/{order}/unassign', [OrderController::class, 'unassign'])
            ->name('orders.unassign');
        Route::get('orders/{order}/history', [OrderController::class, 'showHistory'])
            ->name('orders.history');
        Route::get('orders/{order}/history-modal', [OrderController::class, 'getHistory'])
            ->name('orders.history-modal');
        Route::post('orders/{order}/record-attempt', [OrderController::class, 'recordAttempt'])
            ->name('orders.record-attempt');

        // ⚠️ ROUTE RESOURCE EN DERNIER (CRITICAL) ⚠️
        Route::resource('orders', OrderController::class);

        // ========================================
        // TRAITEMENT DES COMMANDES - API UNIFIÉE (INCHANGÉ)
        // ========================================

        // Interface de traitement principal
        Route::get('process', [ProcessController::class, 'interface'])
            ->name('process.interface');
        
        // API de test pour vérifier la connectivité
        Route::get('process/test', [ProcessController::class, 'test'])
            ->name('process.test');
        
        // API pour obtenir les compteurs de toutes les files
        Route::get('process/counts', [ProcessController::class, 'getCounts'])
            ->name('process.getCounts');
        
        // API pour traiter une action sur une commande
        Route::post('process/action/{order}', [ProcessController::class, 'processAction'])
            ->name('process.action');

        // ========================================
        // API UNIFIÉE POUR TOUTES LES FILES - ROUTES PRINCIPALES
        // ========================================
        Route::get('process/api/{queue}', [ProcessController::class, 'getQueueApi'])
            ->where('queue', 'standard|dated|old|restock')
            ->name('process.api.queue');

        // ========================================
        // INTERFACES SPÉCIALISÉES (INCHANGÉ)
        // ========================================
        
        // INTERFACE D'EXAMEN DES COMMANDES
        Route::prefix('process/examination')->name('process.examination.')->group(function () {
            Route::get('/', [ExaminationController::class, 'index'])->name('index');
            Route::get('/orders', [ExaminationController::class, 'getOrders'])->name('orders');
            Route::get('/count', [ExaminationController::class, 'getCount'])->name('count');
            Route::post('/split/{order}', [ExaminationController::class, 'splitOrder'])->name('split');
            Route::post('/action/{order}', [ExaminationController::class, 'processAction'])->name('action');
            Route::post('/bulk-split', [ExaminationController::class, 'bulkSplit'])->name('bulk-split');
            Route::post('/bulk-cancel', [ExaminationController::class, 'bulkCancel'])->name('bulk-cancel');
            Route::post('/bulk-suspend', [ExaminationController::class, 'bulkSuspend'])->name('bulk-suspend');
        });

        // INTERFACE DES COMMANDES SUSPENDUES
        Route::prefix('process/suspended')->name('process.suspended.')->group(function () {
            Route::get('/', [SuspendedController::class, 'index'])->name('index');
            Route::get('/orders', [SuspendedController::class, 'getOrders'])->name('orders');
            Route::get('/count', [SuspendedController::class, 'getCount'])->name('count');
            Route::post('/action/{order}', [SuspendedController::class, 'processAction'])->name('action');
            Route::post('/bulk-reactivate', [SuspendedController::class, 'bulkReactivate'])->name('bulk-reactivate');
            Route::post('/bulk-cancel', [SuspendedController::class, 'bulkCancel'])->name('bulk-cancel');
        });

        // INTERFACE DE RETOUR EN STOCK
        Route::prefix('process/restock')->name('process.restock.')->group(function () {
            Route::get('/', [RestockController::class, 'index'])->name('index');
            Route::get('/orders', [RestockController::class, 'getOrders'])->name('orders');
            Route::get('/count', [RestockController::class, 'getCount'])->name('count');
            Route::get('/stats', [RestockController::class, 'getStats'])->name('stats');
            Route::post('/reactivate/{order}', [RestockController::class, 'reactivateOrder'])->name('reactivate');
            Route::post('/bulk-reactivate', [RestockController::class, 'bulkReactivate'])->name('bulk-reactivate');
        });

        // ========================================
        // GESTION DES UTILISATEURS (INCHANGÉ)
        // ========================================
        Route::resource('managers', ManagerController::class);
        Route::patch('managers/{manager}/toggle-active', [ManagerController::class, 'toggleActive'])
            ->name('managers.toggle-active');
        Route::get('api/managers', [ManagerController::class, 'getManagersForAdmin'])
            ->name('api.managers');

        Route::resource('employees', EmployeeController::class);
        Route::patch('employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])
            ->name('employees.toggle-active');

        Route::get('login-history', [LoginHistoryController::class, 'index'])
            ->name('login-history.index');
        Route::get('login-history/{user_type}/{user_id}', [LoginHistoryController::class, 'show'])
            ->name('login-history.show');

        // ========================================
        // IMPORTATION ET INTÉGRATIONS (INCHANGÉ)
        // ========================================
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import/csv', [ImportController::class, 'importCsv'])->name('import.csv');
        Route::post('import/xml', [ImportController::class, 'importXml'])->name('import.xml');

        // Routes WooCommerce
        Route::prefix('woocommerce')->name('woocommerce.')->group(function () {
            Route::get('/', [WooCommerceController::class, 'index'])->name('index');
            Route::post('/', [WooCommerceController::class, 'store'])->name('store');
            Route::get('/sync', [WooCommerceController::class, 'sync'])->name('sync');
            Route::post('/test-connection', [WooCommerceController::class, 'testConnection'])->name('test-connection');
            Route::get('/stats', [WooCommerceController::class, 'syncStats'])->name('stats');
            Route::post('/toggle/{id}', [WooCommerceController::class, 'toggleIntegration'])->name('toggle');
            Route::get('/delete/{id}', [WooCommerceController::class, 'deleteIntegration'])->name('delete');
        });

        Route::get('get-cities', [WooCommerceController::class, 'getCities'])->name('get-cities');

        // ========================================
        // GESTION DES COMMANDES DOUBLES (INCHANGÉ)
        // ========================================
        Route::prefix('duplicates')->name('duplicates.')->group(function () {
            Route::get('/', [DuplicateOrdersController::class, 'index'])->name('index');
            Route::get('/get', [DuplicateOrdersController::class, 'getDuplicates'])->name('get');
            Route::get('/stats', [DuplicateOrdersController::class, 'getDashboardStats'])->name('stats');
            Route::get('/history', [DuplicateOrdersController::class, 'getClientHistory'])->name('history');
            Route::get('/detail/{phone}', [DuplicateOrdersController::class, 'clientDetail'])->name('detail');
            Route::post('/check', [DuplicateOrdersController::class, 'checkAllDuplicates'])->name('check');
            Route::post('/merge', [DuplicateOrdersController::class, 'mergeOrders'])->name('merge');
            Route::post('/selective-merge', [DuplicateOrdersController::class, 'selectiveMerge'])->name('selective-merge');
            Route::post('/mark-reviewed', [DuplicateOrdersController::class, 'markAsReviewed'])->name('mark-reviewed');
            Route::post('/cancel', [DuplicateOrdersController::class, 'cancelOrder'])->name('cancel');
            Route::post('/auto-merge', [DuplicateOrdersController::class, 'autoMergeDuplicates'])->name('auto-merge');
            Route::post('/settings', [DuplicateOrdersController::class, 'updateSettings'])->name('settings');
            Route::post('/clean-data', [DuplicateOrdersController::class, 'cleanData'])->name('clean-data');
        });

        // ========================================
        // PARAMÈTRES (INCHANGÉ)
        // ========================================
        Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [AdminSettingController::class, 'store'])->name('settings.store');
        Route::get('settings/reset', [AdminSettingController::class, 'reset'])->name('settings.reset');
        Route::get('settings/export', [AdminSettingController::class, 'export'])->name('settings.export');
        Route::post('settings/import', [AdminSettingController::class, 'import'])->name('settings.import');
        Route::get('settings/api/{key}', [AdminSettingController::class, 'getSetting'])->name('settings.get');
        Route::post('settings/api/{key}', [AdminSettingController::class, 'setSetting'])->name('settings.set');
        Route::get('settings/stats', [AdminSettingController::class, 'getUsageStats'])->name('settings.stats');

        // ========================================
        // GESTION DES LIVRAISONS MULTI-TRANSPORTEURS (INCHANGÉ)
        // ========================================
        Route::prefix('delivery')->name('delivery.')->group(function () {
            
            // ================================
            // PAGE PRINCIPALE MULTI-TRANSPORTEURS
            // ================================
            Route::get('/', [DeliveryController::class, 'index'])
                ->name('index');
            
            // ================================
            // CONFIGURATION JAX DELIVERY - PAGES SÉPARÉES
            // ================================
            Route::get('configuration', [DeliveryController::class, 'configuration'])
                ->name('configuration');
            Route::get('configuration/create', [DeliveryController::class, 'createConfiguration'])
                ->name('configuration.create');
            Route::post('configuration', [DeliveryController::class, 'storeConfiguration'])
                ->name('configuration.store');
            Route::get('configuration/{config}/edit', [DeliveryController::class, 'editConfiguration'])
                ->name('configuration.edit');
            Route::patch('configuration/{config}', [DeliveryController::class, 'updateConfiguration'])
                ->name('configuration.update');
            Route::delete('configuration/{config}', [DeliveryController::class, 'deleteConfiguration'])
                ->name('configuration.delete');
            Route::post('configuration/{config}/test', [DeliveryController::class, 'testConnection'])
                ->name('configuration.test');
            Route::post('configuration/{config}/toggle', [DeliveryController::class, 'toggleConfiguration'])
                ->name('configuration.toggle');
            
            // ================================
            // PRÉPARATION D'ENLÈVEMENT
            // ================================
            Route::get('preparation', [DeliveryController::class, 'preparation'])
                ->name('preparation');
            Route::get('preparation/orders', [DeliveryController::class, 'getAvailableOrders'])
                ->name('preparation.orders');
            Route::post('preparation', [DeliveryController::class, 'createPickup'])
                ->name('preparation.store');
            
            // ================================
            // GESTION DES ENLÈVEMENTS (PICKUPS)
            // ================================
            Route::get('pickups', [DeliveryController::class, 'pickups'])
                ->name('pickups');
            Route::get('pickups/list', [DeliveryController::class, 'pickups'])
                ->name('pickups.index');
            Route::get('pickups/{pickup}/details', [DeliveryController::class, 'showPickup'])
                ->name('pickups.show');
            Route::post('pickups/{pickup}/validate', [DeliveryController::class, 'validatePickup'])
                ->name('pickups.validate');
            Route::post('pickups/{pickup}/refresh', [DeliveryController::class, 'refreshPickupStatus'])
                ->name('pickups.refresh');
            Route::delete('pickups/{pickup}', [DeliveryController::class, 'destroyPickup'])
                ->name('pickups.destroy');
            
            // ================================
            // GESTION DES EXPÉDITIONS (SHIPMENTS)
            // ================================
            Route::get('shipments', [DeliveryController::class, 'shipments'])
                ->name('shipments');
            Route::get('shipments/list', [DeliveryController::class, 'shipments'])
                ->name('shipments.index');
            Route::get('shipments/{shipment}', [DeliveryController::class, 'showShipment'])
                ->name('shipments.show');
            Route::post('shipments/{shipment}/track', [DeliveryController::class, 'trackShipmentStatus'])
                ->name('shipments.track');
            Route::post('shipments/{shipment}/mark-delivered', [DeliveryController::class, 'markShipmentAsDelivered'])
                ->name('shipments.mark-delivered');
            
            // ================================
            // STATISTIQUES ET APIs
            // ================================
            Route::get('stats', [DeliveryController::class, 'stats'])
                ->name('stats');
            Route::get('api/stats', [DeliveryController::class, 'getApiStats'])
                ->name('api.stats');
            Route::post('api/track-all', [DeliveryController::class, 'trackAllShipments'])
                ->name('api.track-all');
        });

        // ========================================
        // DEBUG ET DIAGNOSTICS (INCHANGÉ)
        // ========================================
        Route::get('debug-auth', function () {
            $admin = auth('admin')->user();
            return [
                'is_authenticated' => auth('admin')->check(),
                'admin_id' => $admin ? $admin->id : null,
                'admin_name' => $admin ? $admin->name : null,
                'admin_class' => $admin ? get_class($admin) : null,
                'admin_instance_check' => $admin instanceof \App\Models\Admin,
                'csrf_token' => csrf_token(),
                'current_time' => now()->toISOString(),
                'routes' => [
                    'process_interface' => route('admin.process.interface'),
                    'process_api_standard' => route('admin.process.api.queue', 'standard'),
                    'process_api_dated' => route('admin.process.api.queue', 'dated'),
                    'process_api_old' => route('admin.process.api.queue', 'old'),
                    'process_api_restock' => route('admin.process.api.queue', 'restock'),
                    'process_counts' => route('admin.process.getCounts'),
                    'delivery_index' => route('admin.delivery.index'),
                    'delivery_configuration' => route('admin.delivery.configuration'),
                    'delivery_preparation' => route('admin.delivery.preparation'),
                ]
            ];
        })->name('debug-auth');

        // Route de test spécifique pour l'API de traitement
        Route::get('debug-process', function () {
            $admin = auth('admin')->user();
            if (!$admin) {
                return ['error' => 'Non authentifié'];
            }

            return [
                'admin_orders_count' => $admin->orders()->count(),
                'nouvelle_orders' => $admin->orders()->where('status', 'nouvelle')->count(),
                'datee_orders' => $admin->orders()->where('status', 'datée')->count(),
                'ancienne_orders' => $admin->orders()->where('status', 'ancienne')->count(),
                'suspended_orders' => $admin->orders()->where('is_suspended', true)->count(),
                'products_count' => $admin->products()->count(),
                'active_products' => $admin->products()->where('is_active', true)->count(),
                'delivery_configs' => $admin->deliveryConfigurations()->count(),
                'active_delivery_configs' => $admin->deliveryConfigurations()->where('is_active', true)->count(),
                'total_pickups' => $admin->pickups()->count(),
                'draft_pickups' => $admin->pickups()->where('status', 'draft')->count(),
            ];
        })->name('debug-process');
    });
});