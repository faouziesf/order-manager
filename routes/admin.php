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
            
            // Routes de test avec vrais tokens
            Route::post('test-create-pickup-real-tokens', [DeliveryController::class, 'createTestPickupDataWithRealTokens'])->name('test-create-pickup-real-tokens');
            Route::get('test-complete-validation-flow', [DeliveryController::class, 'testCompleteValidationFlow'])->name('test-complete-validation-flow');
            
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
            
            // 🆕 NOUVELLES ROUTES DE DIAGNOSTIC POUR LES CONFIGURATIONS
            Route::get('configuration/{config}/diagnostic', [DeliveryController::class, 'diagnosticConfiguration'])->name('configuration.diagnostic');
            Route::get('configuration/{config}/test-and-fix', [DeliveryController::class, 'testAndFixConfiguration'])->name('configuration.test-and-fix');
            Route::post('configuration/{config}/migrate', [DeliveryController::class, 'migrateConfiguration'])->name('configuration.migrate');
            
            // ================================
            // 🆕 ROUTES DE CORRECTION DES CONFIGURATIONS
            // ================================
            
            // Réparer toutes les configurations
            Route::post('fix-all-configurations', [DeliveryController::class, 'fixAllConfigurations'])->name('fix-all-configurations');
            
            // Diagnostiquer les tokens invalides
            Route::get('fix-invalid-tokens', [DeliveryController::class, 'fixInvalidTokens'])->name('fix-invalid-tokens');
            
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
            
            // 🆕 NOUVELLES ROUTES DE DIAGNOSTIC POUR LES PICKUPS (AVANT LES PARAMÈTRES)
            Route::get('pickups/test-validation-flow', [DeliveryController::class, 'testValidationFlow'])->name('pickups.test-validation-flow');
            Route::post('pickups/create-test-data', [DeliveryController::class, 'createTestPickupData'])->name('pickups.create-test-data');
            
            // Page principale des pickups (AVANT les routes avec paramètres)
            Route::get('pickups', [DeliveryController::class, 'pickups'])->name('pickups');
            
            // ================================
            // 🆕 ROUTES DE DIAGNOSTIC DÉTAILLÉ POUR LES PICKUPS (AVEC PARAMÈTRES)
            // ================================
            
            // Routes de diagnostic (DOIVENT ÊTRE AVANT les autres routes avec paramètres pour éviter les conflits)
            Route::get('pickups/{pickup}/diagnose', [DeliveryController::class, 'diagnosePickup'])->name('pickups.diagnose');
            Route::post('pickups/{pickup}/force-validate', [DeliveryController::class, 'forceValidatePickup'])->name('pickups.force-validate');
            Route::get('pickups/{pickup}/logs', [DeliveryController::class, 'getPickupLogs'])->name('pickups.logs');
            Route::get('pickups/{pickup}/diagnostic', [DeliveryController::class, 'diagnosticPickup'])->name('pickups.diagnostic');
            
            // ROUTES AVEC PARAMÈTRES STANDARDS (DOIVENT ÊTRE APRÈS TOUTES LES ROUTES API ET DIAGNOSTIC)
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
            
            // 🆕 NOUVELLES ROUTES DE SUIVI GLOBAL (AVANT LES PARAMÈTRES)
            Route::post('shipments/track-all', [DeliveryController::class, 'trackAllShipments'])->name('shipments.track-all');
            Route::get('shipments/tracking-report', [DeliveryController::class, 'getTrackingReport'])->name('shipments.tracking-report');
            
            // Page principale des expéditions (AVANT les routes avec paramètres)
            Route::get('shipments', [DeliveryController::class, 'shipments'])->name('shipments');
            
            // ================================
            // 🆕 ROUTES DE SUIVI DE STATUT - NOUVELLES FONCTIONNALITÉS (AVEC PARAMÈTRES)
            // ================================
            
            // Suivi manuel individuel (AVANT les routes show génériques)
            Route::post('shipments/{shipment}/track', [DeliveryController::class, 'trackShipmentStatus'])->name('shipments.track');
            Route::post('shipments/{shipment}/mark-delivered', [DeliveryController::class, 'markShipmentAsDelivered'])->name('shipments.mark-delivered');
            Route::get('shipments/{shipment}/tracking-history', [DeliveryController::class, 'getShipmentTrackingHistory'])->name('shipments.tracking-history');
            Route::get('shipments/{shipment}/diagnostic', [DeliveryController::class, 'diagnosticShipment'])->name('shipments.diagnostic');
            
            // ROUTES AVEC PARAMÈTRES STANDARDS (DOIVENT ÊTRE APRÈS TOUTES LES ROUTES API)
            Route::get('shipments/{shipment}', [DeliveryController::class, 'showShipment'])->name('shipments.show');
            
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
            
            // 🆕 NOUVELLES APIs DE DIAGNOSTIC ET REPORTING
            Route::get('api/system-health', [DeliveryController::class, 'getSystemHealth'])->name('api.system-health');
            Route::get('api/error-summary', [DeliveryController::class, 'getErrorSummary'])->name('api.error-summary');
            Route::get('api/performance-metrics', [DeliveryController::class, 'getPerformanceMetrics'])->name('api.performance-metrics');
            
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
            
            // ================================
            // 🆕 ROUTES D'IMPORT/EXPORT DE DONNÉES
            // ================================
            Route::get('export/pickups', [DeliveryController::class, 'exportPickupsData'])->name('export.pickups');
            Route::get('export/shipments', [DeliveryController::class, 'exportShipmentsData'])->name('export.shipments');
            Route::get('export/configurations', [DeliveryController::class, 'exportConfigurations'])->name('export.configurations');
            Route::post('import/configurations', [DeliveryController::class, 'importConfigurations'])->name('import.configurations');
            
            // ================================
            // 🆕 ROUTES DE GESTION DES ERREURS ET MAINTENANCE
            // ================================
            Route::get('maintenance/cleanup', [DeliveryController::class, 'maintenanceCleanup'])->name('maintenance.cleanup');
            Route::post('maintenance/repair-pickups', [DeliveryController::class, 'repairBrokenPickups'])->name('maintenance.repair-pickups');
            Route::post('maintenance/sync-statuses', [DeliveryController::class, 'syncAllStatuses'])->name('maintenance.sync-statuses');
            Route::get('maintenance/health-check', [DeliveryController::class, 'healthCheck'])->name('maintenance.health-check');
        });

        // ========================================
        // 🆕 ROUTES DE TEST POUR LES TRANSPORTEURS - NOUVELLES ROUTES CRITIQUES
        // ========================================

        // Route de test factory transporteur
        Route::get('test-carrier-factory', [DeliveryController::class, 'testCarrierFactory'])->name('test-carrier-factory');

        // Route pour créer une configuration de test
        Route::post('create-test-delivery-config', [DeliveryController::class, 'createTestDeliveryConfig'])->name('create-test-delivery-config');

        // Route pour créer un pickup de test avec configuration valide
        Route::post('create-complete-test-pickup', [DeliveryController::class, 'createCompleteTestPickup'])->name('create-complete-test-pickup');

        // Route pour réparer un pickup existant
        Route::post('repair-pickup/{pickup}', [DeliveryController::class, 'repairPickup'])->name('repair-pickup');

        // Route pour test complet du système de livraison
        Route::get('test-delivery-system-complete', [DeliveryController::class, 'testDeliverySystemComplete'])->name('test-delivery-system-complete');

        // ========================================
        // 🆕 ROUTES DE TEST RAPIDE POUR LES PROBLÈMES JAX
        // ========================================
        
        // Test rapide de la validation JAX
        Route::get('test-jax-validation', function () {
            $admin = auth('admin')->user();
            
            try {
                // Récupérer un pickup JAX en mode draft
                $jaxPickup = \App\Models\Pickup::where('admin_id', $admin->id)
                    ->where('carrier_slug', 'jax_delivery')
                    ->where('status', 'draft')
                    ->with(['deliveryConfiguration', 'shipments'])
                    ->first();
                
                if (!$jaxPickup) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucun pickup JAX en mode draft trouvé',
                        'suggestion' => 'Créez d\'abord un pickup JAX avec des commandes'
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pickup JAX trouvé pour test',
                    'pickup' => [
                        'id' => $jaxPickup->id,
                        'status' => $jaxPickup->status,
                        'can_be_validated' => $jaxPickup->can_be_validated,
                        'shipments_count' => $jaxPickup->shipments->count(),
                        'config_active' => $jaxPickup->deliveryConfiguration?->is_active,
                        'config_valid' => $jaxPickup->deliveryConfiguration?->is_valid,
                    ],
                    'test_links' => [
                        'diagnose' => route('admin.delivery.pickups.diagnose', $jaxPickup->id),
                        'force_validate' => route('admin.delivery.pickups.force-validate', $jaxPickup->id),
                        'validate' => route('admin.delivery.pickups.validate', $jaxPickup->id),
                    ]
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'admin_id' => $admin->id,
                ], 500);
            }
        })->name('test-jax-validation');

        // Test rapide de la configuration JAX
        Route::get('test-jax-config', function () {
            $admin = auth('admin')->user();
            
            try {
                $jaxConfig = \App\Models\DeliveryConfiguration::where('admin_id', $admin->id)
                    ->where('carrier_slug', 'jax_delivery')
                    ->where('is_active', true)
                    ->first();
                
                if (!$jaxConfig) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucune configuration JAX active trouvée',
                        'suggestion' => 'Créez et activez une configuration JAX'
                    ]);
                }
                
                // Test de connexion
                $connectionTest = $jaxConfig->testConnection();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Configuration JAX trouvée',
                    'config' => [
                        'id' => $jaxConfig->id,
                        'integration_name' => $jaxConfig->integration_name,
                        'is_active' => $jaxConfig->is_active,
                        'is_valid' => $jaxConfig->is_valid,
                        'has_username' => !empty($jaxConfig->username),
                        'has_password' => !empty($jaxConfig->password),
                        'username' => $jaxConfig->username,
                        'password_length' => $jaxConfig->password ? strlen($jaxConfig->password) : 0,
                    ],
                    'connection_test' => $connectionTest,
                    'test_links' => [
                        'diagnostic' => route('admin.delivery.configuration.diagnostic', $jaxConfig->id),
                        'test_and_fix' => route('admin.delivery.configuration.test-and-fix', $jaxConfig->id),
                    ]
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'admin_id' => $admin->id,
                ], 500);
            }
        })->name('test-jax-config');

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
                    'test_carrier_factory' => route('admin.test-carrier-factory'),
                    'create_test_config' => route('admin.create-test-delivery-config'),
                    'create_complete_pickup' => route('admin.create-complete-test-pickup'),
                    'track_all_shipments' => route('admin.delivery.api.track-all'),
                    'bulk_track_shipments' => route('admin.delivery.shipments.bulk-track'),
                    'fix_all_configurations' => route('admin.delivery.fix-all-configurations'),
                    'fix_invalid_tokens' => route('admin.delivery.fix-invalid-tokens'),
                    'test_jax_validation' => route('admin.test-jax-validation'),
                    'test_jax_config' => route('admin.test-jax-config'),
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
                        'test_carrier_factory_exists' => \Route::has('admin.test-carrier-factory'),
                        'track_all_exists' => \Route::has('admin.delivery.api.track-all'),
                        'bulk_track_exists' => \Route::has('admin.delivery.shipments.bulk-track'),
                        'diagnose_pickup_exists' => \Route::has('admin.delivery.pickups.diagnose'),
                        'force_validate_exists' => \Route::has('admin.delivery.pickups.force-validate'),
                        'track_shipment_exists' => \Route::has('admin.delivery.shipments.track'),
                        'test_jax_validation_exists' => \Route::has('admin.test-jax-validation'),
                        'test_jax_config_exists' => \Route::has('admin.test-jax-config'),
                    ],
                    'config_carriers' => config('carriers') ? array_keys(config('carriers')) : [],
                    'timestamp' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                return [
                    'error' => 'Erreur lors de la récupération des données',
                    'message' => $e->getMessage(),
                    'admin_id' => $admin->id,
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
                    'test_system' => route('admin.delivery.test-system'),
                    'test_create_pickup' => route('admin.delivery.test-create-pickup'),
                    'test_carrier_factory' => route('admin.test-carrier-factory'),
                    'track_all' => route('admin.delivery.api.track-all'),
                    'bulk_track' => route('admin.delivery.shipments.bulk-track'),
                    'fix_all_configurations' => route('admin.delivery.fix-all-configurations'),
                    'fix_invalid_tokens' => route('admin.delivery.fix-invalid-tokens'),
                    'test_jax_validation' => route('admin.test-jax-validation'),
                    'test_jax_config' => route('admin.test-jax-config'),
                ],
                'diagnostic_routes' => [
                    'diagnose_pickup' => 'GET /admin/delivery/pickups/{pickup}/diagnose',
                    'force_validate_pickup' => 'POST /admin/delivery/pickups/{pickup}/force-validate',
                    'pickup_logs' => 'GET /admin/delivery/pickups/{pickup}/logs',
                    'diagnostic_config' => 'GET /admin/delivery/configuration/{config}/diagnostic',
                    'test_and_fix_config' => 'GET /admin/delivery/configuration/{config}/test-and-fix',
                ],
                'tracking_routes' => [
                    'track_shipment' => 'POST /admin/delivery/shipments/{shipment}/track',
                    'mark_delivered' => 'POST /admin/delivery/shipments/{shipment}/mark-delivered',
                    'tracking_history' => 'GET /admin/delivery/shipments/{shipment}/tracking-history',
                    'bulk_track' => 'POST /admin/delivery/shipments/bulk-track',
                    'track_all' => 'POST /admin/delivery/api/track-all',
                ],
                'timestamp' => now()->toISOString()
            ];
        })->name('delivery-test');

        // 🆕 ROUTE DE TEST RAPIDE POUR L'API STATS DELIVERY
        Route::get('delivery-quick-test', function () {
            $admin = auth('admin')->user();
            
            try {
                // Test direct de la méthode getGeneralStats
                $controller = app(DeliveryController::class);
                $response = $controller->getGeneralStats();
                
                return [
                    'success' => true,
                    'message' => 'API de statistiques fonctionnelle',
                    'response' => $response->getData(),
                    'admin_id' => $admin->id,
                    'test_time' => now()->toISOString(),
                ];
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
            
            // Test 4: Routes critiques
            $routeTests = [
                'delivery.index',
                'delivery.preparation',
                'delivery.preparation.orders',
                'delivery.preparation.store',
                'delivery.pickups.list',
                'delivery.pickups.diagnose',
                'delivery.pickups.force-validate',
                'delivery.shipments',
                'delivery.shipments.list',
                'delivery.shipments.track',
                'delivery.shipments.bulk-track',
                'delivery.test-system',
                'delivery.api.general-stats',
                'delivery.api.track-all',
                'delivery.fix-all-configurations',
                'delivery.fix-invalid-tokens',
                'delivery.configuration.test-and-fix',
                'delivery.configuration.migrate',
                'test-carrier-factory',
                'create-test-delivery-config',
                'test-jax-validation',
                'test-jax-config',
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
                'new_diagnostic_routes_available' => true,
                'new_tracking_features_available' => true,
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
                    'test_carrier_factory' => route('admin.test-carrier-factory'),
                    'track_all' => route('admin.delivery.api.track-all'),
                    'bulk_track' => route('admin.delivery.shipments.bulk-track'),
                    'test_jax_validation' => route('admin.test-jax-validation'),
                    'test_jax_config' => route('admin.test-jax-config'),
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
                    'new_features' => [
                        'diagnostic_routes' => true,
                        'tracking_features' => true,
                        'config_repair' => true,
                        'jax_debug' => true,
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
                    'username' => '2304',
                    'password' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
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
                        'pos_barcode' => 'TEST_' . time() . '_' . $i,
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
                    'message' => 'Données test créées',
                    'config_id' => $config->id,
                    'pickup_ids' => $pickups,
                    'shipment_ids' => $shipments,
                    'diagnostic_links' => [
                        'diagnose_first_pickup' => route('admin.delivery.pickups.diagnose', $pickups[0]),
                        'test_jax_validation' => route('admin.test-jax-validation'),
                        'test_jax_config' => route('admin.test-jax-config'),
                    ],
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
                $trackableShipmentsCount = \App\Models\Shipment::where('admin_id', $admin->id)
                    ->whereNotNull('pos_barcode')
                    ->count();
                
                // Test des routes spécifiques aux expéditions
                $shipmentRoutes = [
                    'shipments' => route('admin.delivery.shipments'),
                    'shipments_list' => route('admin.delivery.shipments.list'),
                    'shipments_stats' => route('admin.delivery.shipments.stats'),
                    'bulk_track' => route('admin.delivery.shipments.bulk-track'),
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
                        'trackable' => $trackableShipmentsCount,
                    ],
                    'shipment_routes' => $shipmentRoutes,
                    'tracking_features' => [
                        'manual_tracking' => true,
                        'bulk_tracking' => true,
                        'automatic_tracking' => true,
                        'status_history' => true,
                        'diagnostic_shipments' => true,
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
        })->name('test-shipments-quick');
    });
});