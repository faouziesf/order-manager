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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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
        // 🚀 GESTION DES LIVRAISONS MULTI-TRANSPORTEURS - SECTION COMPLÈTE ET CORRIGÉE
        // ========================================
        Route::prefix('delivery')->name('delivery.')->group(function () {
            
            // ================================
            // 🆕 ROUTES DE TEST ET DIAGNOSTIC - PRIORITÉ 1 (DOIVENT ÊTRE EN PREMIER)
            // ================================
            
            // Routes de diagnostic système complet
            Route::get('test-system', [DeliveryController::class, 'testSystem'])->name('test-system');
            Route::post('test-create-pickup', [DeliveryController::class, 'testCreatePickup'])->name('test-create-pickup');
            
            // ================================
            // PAGE PRINCIPALE MULTI-TRANSPORTEURS
            // ================================
            Route::get('/', [DeliveryController::class, 'index'])->name('index');
            
            // ================================
            // CONFIGURATION DES TRANSPORTEURS
            // ================================
            Route::get('configuration', [DeliveryController::class, 'configuration'])->name('configuration');
            Route::get('configuration/create', [DeliveryController::class, 'createConfiguration'])->name('configuration.create');
            Route::post('configuration', [DeliveryController::class, 'storeConfiguration'])->name('configuration.store');
            Route::get('configuration/{config}/edit', [DeliveryController::class, 'editConfiguration'])->name('configuration.edit');
            Route::patch('configuration/{config}', [DeliveryController::class, 'updateConfiguration'])->name('configuration.update');
            Route::delete('configuration/{config}', [DeliveryController::class, 'deleteConfiguration'])->name('configuration.delete');
            Route::post('configuration/{config}/test', [DeliveryController::class, 'testConnection'])->name('configuration.test');
            Route::post('configuration/{config}/toggle', [DeliveryController::class, 'toggleConfiguration'])->name('configuration.toggle');
            
            // ================================
            // PRÉPARATION D'ENLÈVEMENT
            // ================================
            Route::get('preparation', [DeliveryController::class, 'preparation'])->name('preparation');
            Route::get('preparation/orders', [DeliveryController::class, 'getAvailableOrders'])->name('preparation.orders');
            Route::post('preparation', [DeliveryController::class, 'createPickup'])->name('preparation.store');
            
            // ================================
            // 🚨 GESTION DES ENLÈVEMENTS (PICKUPS) - ORDRE CRITIQUE DES ROUTES
            // ================================
            
            // ⚠️ ROUTES API - DOIVENT ABSOLUMENT ÊTRE AVANT LES ROUTES AVEC PARAMÈTRES ⚠️
            Route::get('pickups/list', [DeliveryController::class, 'getPickupsList'])->name('pickups.list');
            Route::get('pickups/export', [DeliveryController::class, 'exportPickups'])->name('pickups.export');
            Route::post('pickups/bulk-validate', [DeliveryController::class, 'bulkValidatePickups'])->name('pickups.bulk-validate');
            
            // Page principale des pickups (AVANT les routes avec paramètres)
            Route::get('pickups', [DeliveryController::class, 'pickups'])->name('pickups');
            
            // ROUTES AVEC PARAMÈTRES (DOIVENT ÊTRE APRÈS TOUTES LES ROUTES API)
            Route::get('pickups/{pickup}/details', [DeliveryController::class, 'showPickup'])->name('pickups.show');
            Route::post('pickups/{pickup}/validate', [DeliveryController::class, 'validatePickup'])->name('pickups.validate');
            Route::post('pickups/{pickup}/mark-picked-up', [DeliveryController::class, 'markPickupAsPickedUp'])->name('pickups.mark-picked-up');
            Route::post('pickups/{pickup}/refresh', [DeliveryController::class, 'refreshPickupStatus'])->name('pickups.refresh');
            Route::delete('pickups/{pickup}', [DeliveryController::class, 'destroyPickup'])->name('pickups.destroy');
            
            // Gestion des commandes dans les pickups
            Route::post('pickups/{pickup}/add-orders', [DeliveryController::class, 'addOrdersToPickup'])->name('pickups.add-orders');
            Route::delete('pickups/{pickup}/orders/{order}', [DeliveryController::class, 'removeOrderFromPickup'])->name('pickups.remove-order');
            
            // Manifeste et impression
            Route::get('pickups/{pickup}/manifest', [DeliveryController::class, 'generatePickupManifest'])->name('pickups.manifest');
            
            // ================================
            // 🆕 GESTION DES EXPÉDITIONS (SHIPMENTS) - SECTION ÉTENDUE ET COMPLÈTE
            // ================================
            
            // ⚠️ ROUTES API CRITIQUES - DOIVENT ÊTRE EN PREMIER (AVANT LES PARAMÈTRES) ⚠️
            Route::get('shipments/list', [DeliveryController::class, 'getShipmentsList'])->name('shipments.list');
            Route::get('shipments/stats', [DeliveryController::class, 'getShipmentsStats'])->name('shipments.stats');
            Route::get('shipments/export', [DeliveryController::class, 'exportShipments'])->name('shipments.export');
            Route::post('shipments/bulk-track', [DeliveryController::class, 'bulkTrackShipments'])->name('shipments.bulk-track');
            Route::post('shipments/bulk-labels', [DeliveryController::class, 'generateBulkLabels'])->name('shipments.bulk-labels');
            
            // Page principale des expéditions (AVANT les routes avec paramètres)
            Route::get('shipments', [DeliveryController::class, 'shipments'])->name('shipments');
            
            // ROUTES AVEC PARAMÈTRES (DOIVENT ÊTRE APRÈS TOUTES LES ROUTES API)
            Route::get('shipments/{shipment}', [DeliveryController::class, 'showShipment'])->name('shipments.show');
            Route::post('shipments/{shipment}/track', [DeliveryController::class, 'trackShipmentStatus'])->name('shipments.track');
            Route::post('shipments/{shipment}/mark-delivered', [DeliveryController::class, 'markShipmentAsDelivered'])->name('shipments.mark-delivered');
            
            // Génération de documents pour les expéditions
            Route::get('shipments/{shipment}/label', [DeliveryController::class, 'generateShippingLabel'])->name('shipments.label');
            Route::get('shipments/{shipment}/delivery-proof', [DeliveryController::class, 'generateDeliveryProof'])->name('shipments.delivery-proof');
            
            // ================================
            // STATISTIQUES ET APIs GLOBALES
            // ================================
            Route::get('stats', [DeliveryController::class, 'stats'])->name('stats');
            Route::get('api/general-stats', [DeliveryController::class, 'getGeneralStats'])->name('api.general-stats');
            Route::get('api/stats', [DeliveryController::class, 'getApiStats'])->name('api.stats');
            Route::get('api/recent-activity', [DeliveryController::class, 'getRecentActivity'])->name('api.recent-activity');
            Route::get('api/available-orders', [DeliveryController::class, 'getAvailableOrdersApi'])->name('api.available-orders');
            Route::get('api/carrier-stats/{carrier}', [DeliveryController::class, 'getCarrierStats'])->name('api.carrier-stats');
            Route::post('api/track-all', [DeliveryController::class, 'trackAllShipments'])->name('api.track-all');
            
            // ================================
            // WEBHOOKS POUR LES TRANSPORTEURS
            // ================================
            Route::post('webhook/jax-delivery', [DeliveryController::class, 'webhookJaxDelivery'])->name('webhook.jax-delivery');
            Route::post('webhook/mes-colis', [DeliveryController::class, 'webhookMesColis'])->name('webhook.mes-colis');
            Route::get('webhook/validate/{carrier}', [DeliveryController::class, 'validateWebhookSetup'])->name('webhook.validate');
            
            // ================================
            // GESTION DES COÛTS ET ZONES
            // ================================
            Route::post('calculate-shipping-cost', [DeliveryController::class, 'calculateShippingCost'])->name('calculate-shipping-cost');
            Route::post('compare-carriers', [DeliveryController::class, 'compareCarrierCosts'])->name('compare-carriers');
            Route::get('cost-history', [DeliveryController::class, 'getCostHistory'])->name('cost-history');
            Route::get('delivery-zones/{carrier}', [DeliveryController::class, 'getDeliveryZones'])->name('delivery-zones.get');
            Route::post('delivery-zones/{carrier}', [DeliveryController::class, 'updateDeliveryZones'])->name('delivery-zones.update');
            Route::post('check-coverage', [DeliveryController::class, 'checkDeliveryCoverage'])->name('check-coverage');
        });

        // ========================================
        // DEBUG ET DIAGNOSTICS - SECTION ÉTENDUE ET AMÉLIORÉE
        // ========================================
        Route::get('debug-auth', function () {
            $admin = auth('admin')->user();
            return [
                'is_authenticated' => auth('admin')->check(),
                'admin_id' => $admin ? $admin->id : null,
                'admin_name' => $admin ? $admin->name : null,
                'admin_email' => $admin ? $admin->email : null,
                'admin_class' => $admin ? get_class($admin) : null,
                'admin_instance_check' => $admin instanceof \App\Models\Admin,
                'csrf_token' => csrf_token(),
                'session_id' => session()->getId(),
                'current_time' => now()->toISOString(),
                'guard' => 'admin',
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
                    'delivery_pickups' => route('admin.delivery.pickups'),
                    'delivery_pickups_list' => route('admin.delivery.pickups.list'),
                    'delivery_shipments' => route('admin.delivery.shipments'),
                    'delivery_shipments_list' => route('admin.delivery.shipments.list'),
                    'delivery_api_general_stats' => route('admin.delivery.api.general-stats'),
                    'delivery_test_system' => route('admin.delivery.test-system'),
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
                'total_pickups' => \App\Models\Pickup::where('admin_id', $admin->id)->count(),
                'draft_pickups' => \App\Models\Pickup::where('admin_id', $admin->id)->where('status', 'draft')->count(),
                'total_shipments' => \App\Models\Shipment::where('admin_id', $admin->id)->count(),
                'active_shipments' => \App\Models\Shipment::where('admin_id', $admin->id)
                    ->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])
                    ->count(),
            ];
        })->name('debug-process');

        // 🆕 NOUVELLE ROUTE DE DEBUG POUR LES STATISTIQUES DELIVERY
        Route::get('delivery-debug-stats', function () {
            $admin = auth('admin')->user();
            
            try {
                return [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'delivery_configs' => \App\Models\DeliveryConfiguration::where('admin_id', $admin->id)->count(),
                    'active_configs' => \App\Models\DeliveryConfiguration::where('admin_id', $admin->id)->where('is_active', true)->count(),
                    'pickups' => \App\Models\Pickup::where('admin_id', $admin->id)->count(),
                    'draft_pickups' => \App\Models\Pickup::where('admin_id', $admin->id)->where('status', 'draft')->count(),
                    'shipments' => \App\Models\Shipment::where('admin_id', $admin->id)->count(),
                    'active_shipments' => \App\Models\Shipment::where('admin_id', $admin->id)
                        ->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])
                        ->count(),
                    'shipment_stats' => [
                        'in_transit' => \App\Models\Shipment::where('admin_id', $admin->id)->where('status', 'in_transit')->count(),
                        'delivered' => \App\Models\Shipment::where('admin_id', $admin->id)->where('status', 'delivered')->count(),
                        'in_return' => \App\Models\Shipment::where('admin_id', $admin->id)->where('status', 'in_return')->count(),
                        'anomaly' => \App\Models\Shipment::where('admin_id', $admin->id)->where('status', 'anomaly')->count(),
                    ],
                    'routes_check' => [
                        'general_stats_route_exists' => \Route::has('admin.delivery.api.general-stats'),
                        'delivery_index_exists' => \Route::has('admin.delivery.index'),
                        'delivery_config_exists' => \Route::has('admin.delivery.configuration'),
                        'test_system_exists' => \Route::has('admin.delivery.test-system'),
                        'pickups_list_exists' => \Route::has('admin.delivery.pickups.list'),
                        'shipments_exists' => \Route::has('admin.delivery.shipments'),
                        'shipments_list_exists' => \Route::has('admin.delivery.shipments.list'),
                    ],
                    'config_carriers' => config('carriers') ? array_keys(config('carriers')) : [],
                    'timestamp' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                return [
                    'error' => 'Erreur lors de la récupération des données',
                    'message' => $e->getMessage(),
                    'admin_id' => $admin->id,
                    'routes_check' => [
                        'general_stats_route_exists' => \Route::has('admin.delivery.api.general-stats'),
                        'delivery_index_exists' => \Route::has('admin.delivery.index'),
                        'test_system_exists' => \Route::has('admin.delivery.test-system'),
                        'pickups_list_exists' => \Route::has('admin.delivery.pickups.list'),
                        'shipments_exists' => \Route::has('admin.delivery.shipments'),
                        'shipments_list_exists' => \Route::has('admin.delivery.shipments.list'),
                    ]
                ];
            }
        })->name('delivery-debug-stats');

        // ========================================
        // ROUTES DE TEST POUR LES NOUVELLES VUES DE LIVRAISON - SECTION ÉTENDUE
        // ========================================
        Route::get('delivery-test', function () {
            $admin = auth('admin')->user();
            
            return [
                'message' => 'Routes de livraison fonctionnelles',
                'admin' => $admin->name,
                'delivery_routes' => [
                    'index' => route('admin.delivery.index'),
                    'configuration' => route('admin.delivery.configuration'),
                    'configuration_create' => route('admin.delivery.configuration.create'),
                    'preparation' => route('admin.delivery.preparation'),
                    'pickups' => route('admin.delivery.pickups'),
                    'pickups_list' => route('admin.delivery.pickups.list'),
                    'shipments' => route('admin.delivery.shipments'),
                    'shipments_list' => route('admin.delivery.shipments.list'),
                    'stats' => route('admin.delivery.stats'),
                    'api_general_stats' => route('admin.delivery.api.general-stats'),
                    'api_stats' => route('admin.delivery.api.stats'),
                    'test_system' => route('admin.delivery.test-system'),
                    'test_create_pickup' => route('admin.delivery.test-create-pickup'),
                ],
                'available_views' => [
                    'admin.delivery.index',
                    'admin.delivery.configuration',
                    'admin.delivery.configuration-create',
                    'admin.delivery.configuration-edit',
                    'admin.delivery.preparation',
                    'admin.delivery.pickups',
                    'admin.delivery.shipments',
                    'admin.delivery.stats',
                ],
                'timestamp' => now()->toISOString()
            ];
        })->name('delivery-test');

        // 🆕 ROUTE DE TEST RAPIDE POUR L'API STATS DELIVERY
        Route::get('delivery-quick-test', function () {
            $admin = auth('admin')->user();
            
            try {
                // Test direct de la méthode si elle existe
                if (method_exists(DeliveryController::class, 'getGeneralStats')) {
                    $controller = new DeliveryController(app(\App\Services\Delivery\ShippingServiceFactory::class));
                    $response = $controller->getGeneralStats();
                    
                    return [
                        'success' => true,
                        'message' => 'API de statistiques fonctionnelle',
                        'response' => $response->getData(),
                        'admin_id' => $admin->id,
                        'test_time' => now()->toISOString(),
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Méthode getGeneralStats non trouvée dans DeliveryController',
                        'admin_id' => $admin->id,
                        'controller_methods' => get_class_methods(DeliveryController::class),
                        'test_time' => now()->toISOString(),
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'admin_id' => $admin->id,
                    'test_time' => now()->toISOString(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                ];
            }
        })->name('delivery-quick-test');

        // 🆕 ROUTE DE TEST COMPLET DU SYSTÈME
        Route::get('full-system-test', function () {
            $admin = auth('admin')->user();
            $results = [];
            
            // Test 1: Authentification
            $results['auth_test'] = [
                'success' => auth('admin')->check(),
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
            ];
            
            // Test 2: Base de données
            try {
                $results['database_test'] = [
                    'success' => true,
                    'orders_count' => $admin->orders()->count(),
                    'configs_count' => $admin->deliveryConfigurations()->count(),
                    'pickups_count' => \App\Models\Pickup::where('admin_id', $admin->id)->count(),
                    'shipments_count' => \App\Models\Shipment::where('admin_id', $admin->id)->count(),
                ];
            } catch (\Exception $e) {
                $results['database_test'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
            
            // Test 3: Configuration
            $results['config_test'] = [
                'success' => config('carriers') !== null,
                'carriers_available' => config('carriers') ? array_keys(config('carriers')) : [],
                'app_debug' => config('app.debug'),
                'app_env' => config('app.env'),
            ];
            
            // Test 4: Routes
            $routeTests = [
                'delivery.index',
                'delivery.preparation',
                'delivery.preparation.orders',
                'delivery.preparation.store',
                'delivery.pickups.list',
                'delivery.shipments',
                'delivery.shipments.list',
                'delivery.test-system',
                'delivery.api.general-stats',
            ];
            
            $results['routes_test'] = [];
            foreach ($routeTests as $routeName) {
                $results['routes_test'][$routeName] = \Route::has('admin.' . $routeName);
            }
            
            // Test 5: Modèles
            $modelTests = [
                'DeliveryConfiguration' => '\App\Models\DeliveryConfiguration',
                'Pickup' => '\App\Models\Pickup',
                'Shipment' => '\App\Models\Shipment',
                'Order' => '\App\Models\Order',
            ];
            
            $results['models_test'] = [];
            foreach ($modelTests as $name => $class) {
                $results['models_test'][$name] = class_exists($class);
            }
            
            // Résumé global
            $results['overall_status'] = [
                'all_tests_passed' => collect($results)->every(function($test) {
                    return is_array($test) ? ($test['success'] ?? true) : true;
                }),
                'timestamp' => now()->toISOString(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ];
            
            return response()->json($results, 200, [], JSON_PRETTY_PRINT);
        })->name('full-system-test');

        // ========================================
        // ROUTES DE TEST ET CRÉATION DE DONNÉES
        // ========================================

        // Route de test rapide pour les pickups avec diagnostic complet
        Route::get('test-pickups-quick', function () {
            $admin = auth('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            try {
                // Test basique de la base de données
                $pickupsCount = \DB::table('pickups')->where('admin_id', $admin->id)->count();
                $configsCount = \DB::table('delivery_configurations')->where('admin_id', $admin->id)->count();
                $ordersCount = \DB::table('orders')->where('admin_id', $admin->id)->count();
                $shipmentsCount = \DB::table('shipments')->where('admin_id', $admin->id)->count();
                
                // Test des routes
                $routes = [
                    'pickups' => route('admin.delivery.pickups'),
                    'pickups_list' => route('admin.delivery.pickups.list'),
                    'shipments' => route('admin.delivery.shipments'),
                    'shipments_list' => route('admin.delivery.shipments.list'),
                    'preparation' => route('admin.delivery.preparation'),
                ];
                
                return response()->json([
                    'success' => true,
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                    ],
                    'database' => [
                        'pickups' => $pickupsCount,
                        'configs' => $configsCount,
                        'orders' => $ordersCount,
                        'shipments' => $shipmentsCount,
                    ],
                    'routes' => $routes,
                    'tables_exist' => [
                        'pickups' => Schema::hasTable('pickups'),
                        'delivery_configurations' => Schema::hasTable('delivery_configurations'),
                        'shipments' => Schema::hasTable('shipments'),
                        'orders' => Schema::hasTable('orders'),
                    ],
                    'timestamp' => now()->toISOString(),
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'admin_id' => $admin->id,
                ], 500);
            }
        })->name('test-pickups-quick');

        // Route pour créer des données de test
        Route::post('create-test-pickup-data', function () {
            $admin = auth('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            try {
                \DB::beginTransaction();
                
                // Créer une configuration de test si elle n'existe pas
                $config = \App\Models\DeliveryConfiguration::firstOrCreate([
                    'admin_id' => $admin->id,
                    'carrier_slug' => 'jax_delivery',
                    'integration_name' => 'Configuration Test Automatique'
                ], [
                    'username' => 'test_user_' . time(),
                    'password' => 'test_token_' . time(),
                    'environment' => 'test',
                    'is_active' => true,
                ]);
                
                // Créer quelques pickups de test
                $pickups = [];
                for ($i = 1; $i <= 3; $i++) {
                    $pickup = \App\Models\Pickup::create([
                        'admin_id' => $admin->id,
                        'carrier_slug' => 'jax_delivery',
                        'delivery_configuration_id' => $config->id,
                        'status' => ['draft', 'validated', 'picked_up'][($i - 1) % 3],
                        'pickup_date' => now()->addDays($i),
                    ]);
                    
                    $pickups[] = $pickup->id;
                }
                
                // Créer quelques shipments de test
                $shipments = [];
                for ($i = 1; $i <= 5; $i++) {
                    $shipment = \App\Models\Shipment::create([
                        'admin_id' => $admin->id,
                        'order_id' => null, // Pas de commande associée pour le test
                        'pickup_id' => $pickups[($i - 1) % count($pickups)],
                        'carrier_slug' => 'jax_delivery',
                        'status' => ['created', 'validated', 'in_transit', 'delivered'][($i - 1) % 4],
                        'weight' => rand(5, 50) / 10,
                        'cod_amount' => rand(20, 200),
                        'nb_pieces' => rand(1, 5),
                        'recipient_info' => [
                            'name' => "Client Test {$i}",
                            'phone' => '12345678' . $i,
                            'city' => 'Tunis',
                        ],
                    ]);
                    
                    $shipments[] = $shipment->id;
                }
                
                \DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Données de test créées',
                    'config_id' => $config->id,
                    'pickup_ids' => $pickups,
                    'shipment_ids' => $shipments,
                ]);
                
            } catch (\Exception $e) {
                \DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
            
        })->name('create-test-pickup-data');

        // 🆕 ROUTE DE TEST POUR LES EXPÉDITIONS
        Route::get('test-shipments-quick', function () {
            $admin = auth('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            try {
                // Test de l'API des expéditions
                $shipmentsCount = \App\Models\Shipment::where('admin_id', $admin->id)->count();
                $activeShipmentsCount = \App\Models\Shipment::where('admin_id', $admin->id)
                    ->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])
                    ->count();
                
                // Test des routes spécifiques aux expéditions
                $shipmentRoutes = [
                    'shipments' => route('admin.delivery.shipments'),
                    'shipments_list' => route('admin.delivery.shipments.list'),
                    'shipments_stats' => route('admin.delivery.shipments.stats'),
                    'api_track_all' => route('admin.delivery.api.track-all'),
                ];
                
                return response()->json([
                    'success' => true,
                    'message' => 'Test des expéditions réussi',
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                    ],
                    'shipments_data' => [
                        'total' => $shipmentsCount,
                        'active' => $activeShipmentsCount,
                    ],
                    'shipment_routes' => $shipmentRoutes,
                    'timestamp' => now()->toISOString(),
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'admin_id' => $admin->id,
                ], 500);
            }
        })->name('test-shipments-quick');
    });
});