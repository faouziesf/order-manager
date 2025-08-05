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
        // GESTION DES LIVRAISONS MULTI-TRANSPORTEURS - SECTION CORRIGÉE ET ÉTENDUE
        // ========================================
        Route::prefix('delivery')->name('delivery.')->group(function () {
            
            // ================================
            // 🆕 ROUTES DE TEST ET DIAGNOSTIC - AJOUT PRIORITAIRE
            // ================================
            
            // Route de diagnostic système complet
            Route::get('test-system', function() {
                $admin = auth('admin')->user();
                
                try {
                    $diagnostics = [
                        'admin_info' => [
                            'id' => $admin->id,
                            'name' => $admin->name,
                            'email' => $admin->email,
                            'is_active' => $admin->is_active,
                        ],
                        
                        'database_counts' => [
                            'total_orders' => $admin->orders()->count(),
                            'confirmed_orders' => $admin->orders()->where('status', 'confirmée')->count(),
                            'available_orders' => $admin->orders()
                                ->where('status', 'confirmée')
                                ->where(function($q) {
                                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                                })
                                ->whereDoesntHave('shipments')
                                ->count(),
                            'delivery_configurations' => $admin->deliveryConfigurations()->count(),
                            'active_configurations' => $admin->deliveryConfigurations()->where('is_active', true)->count(),
                            'pickups' => \App\Models\Pickup::where('admin_id', $admin->id)->count(),
                            'shipments' => \App\Models\Shipment::where('admin_id', $admin->id)->count(),
                        ],
                        
                        'configurations_detail' => $admin->deliveryConfigurations()->get()->map(function($config) {
                            return [
                                'id' => $config->id,
                                'carrier_slug' => $config->carrier_slug,
                                'integration_name' => $config->integration_name,
                                'is_active' => $config->is_active,
                                'created_at' => $config->created_at,
                            ];
                        }),
                        
                        'sample_orders' => $admin->orders()
                            ->where('status', 'confirmée')
                            ->where(function($q) {
                                $q->where('is_suspended', false)->orWhereNull('is_suspended');
                            })
                            ->whereDoesntHave('shipments')
                            ->take(5)
                            ->get(['id', 'customer_name', 'customer_phone', 'total_price', 'created_at']),
                        
                        'routes_check' => [
                            'preparation_route' => route('admin.delivery.preparation'),
                            'preparation_orders' => route('admin.delivery.preparation.orders'),
                            'preparation_store' => route('admin.delivery.preparation.store'),
                            'pickups_index' => route('admin.delivery.pickups'),
                        ],
                        
                        'config_check' => [
                            'carriers_config_exists' => config('carriers') !== null,
                            'carriers_available' => config('carriers') ? array_keys(config('carriers')) : [],
                            'jax_delivery_config' => config('carriers.jax_delivery') !== null,
                            'mes_colis_config' => config('carriers.mes_colis') !== null,
                        ],
                        
                        'system_status' => [
                            'php_version' => PHP_VERSION,
                            'laravel_version' => app()->version(),
                            'environment' => app()->environment(),
                            'debug_mode' => config('app.debug'),
                            'timezone' => config('app.timezone'),
                        ],
                        
                        'timestamp' => now()->toISOString(),
                    ];
                    
                    return response()->json($diagnostics, 200, [], JSON_PRETTY_PRINT);
                    
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                        'admin_id' => $admin->id,
                    ], 500);
                }
            })->name('test-system');
            
            // Route de test pour créer un pickup simple
            Route::post('test-create-pickup', function(\Illuminate\Http\Request $request) {
                $admin = auth('admin')->user();
                
                try {
                    \Log::info('Test création pickup', [
                        'admin_id' => $admin->id,
                        'request_data' => $request->all()
                    ]);
                    
                    // Test de base
                    $config = $admin->deliveryConfigurations()->where('is_active', true)->first();
                    if (!$config) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Aucune configuration active trouvée',
                            'available_configs' => $admin->deliveryConfigurations()->get(['id', 'carrier_slug', 'integration_name', 'is_active'])
                        ]);
                    }
                    
                    $orders = $admin->orders()
                        ->where('status', 'confirmée')
                        ->where(function($q) {
                            $q->where('is_suspended', false)->orWhereNull('is_suspended');
                        })
                        ->whereDoesntHave('shipments')
                        ->take(2)
                        ->get();
                        
                    if ($orders->isEmpty()) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Aucune commande disponible pour test',
                            'total_orders' => $admin->orders()->count(),
                            'confirmed_orders' => $admin->orders()->where('status', 'confirmée')->count(),
                        ]);
                    }
                    
                    // Créer un pickup de test
                    $pickup = \App\Models\Pickup::create([
                        'admin_id' => $admin->id,
                        'carrier_slug' => $config->carrier_slug,
                        'delivery_configuration_id' => $config->id,
                        'status' => 'draft',
                        'pickup_date' => now()->addDay()->format('Y-m-d'),
                    ]);
                    
                    // Créer les shipments
                    $shipmentsCreated = 0;
                    foreach ($orders as $order) {
                        $shipment = \App\Models\Shipment::create([
                            'admin_id' => $admin->id,
                            'order_id' => $order->id,
                            'pickup_id' => $pickup->id,
                            'carrier_slug' => $config->carrier_slug,
                            'status' => 'created',
                            'weight' => 1.0,
                            'value' => $order->total_price,
                            'cod_amount' => $order->total_price,
                            'nb_pieces' => 1,
                            'pickup_date' => $pickup->pickup_date,
                            'content_description' => "Test commande #{$order->id}",
                            'recipient_info' => [
                                'name' => $order->customer_name,
                                'phone' => $order->customer_phone,
                                'address' => $order->customer_address,
                            ],
                        ]);
                        
                        $order->update(['status' => 'expédiée']);
                        $shipmentsCreated++;
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => "Test pickup #{$pickup->id} créé avec {$shipmentsCreated} expéditions",
                        'data' => [
                            'pickup_id' => $pickup->id,
                            'shipments_created' => $shipmentsCreated,
                            'orders_processed' => $orders->pluck('id'),
                            'config_used' => [
                                'id' => $config->id,
                                'carrier_slug' => $config->carrier_slug,
                                'integration_name' => $config->integration_name,
                            ]
                        ]
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Erreur test pickup', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                    ], 500);
                }
            })->name('test-create-pickup');
            
            // Route de validation des données
            Route::get('validate-data', function() {
                $admin = auth('admin')->user();
                
                $validation = [
                    'database_tables' => [
                        'delivery_configurations' => \Schema::hasTable('delivery_configurations'),
                        'pickups' => \Schema::hasTable('pickups'),
                        'shipments' => \Schema::hasTable('shipments'),
                        'orders' => \Schema::hasTable('orders'),
                    ],
                    'models_exist' => [
                        'DeliveryConfiguration' => class_exists('\App\Models\DeliveryConfiguration'),
                        'Pickup' => class_exists('\App\Models\Pickup'),
                        'Shipment' => class_exists('\App\Models\Shipment'),
                        'Order' => class_exists('\App\Models\Order'),
                    ],
                    'config_files' => [
                        'carriers_config' => file_exists(config_path('carriers.php')),
                        'carriers_loaded' => config('carriers') !== null,
                    ],
                    'sample_data' => [
                        'admin_has_orders' => $admin->orders()->exists(),
                        'admin_has_configs' => $admin->deliveryConfigurations()->exists(),
                        'confirmed_orders_count' => $admin->orders()->where('status', 'confirmée')->count(),
                    ]
                ];
                
                return response()->json($validation, 200, [], JSON_PRETTY_PRINT);
            })->name('validate-data');
            
            // ================================
            // PAGE PRINCIPALE MULTI-TRANSPORTEURS
            // ================================
            Route::get('/', [DeliveryController::class, 'index'])
                ->name('index');
            
            // ================================
            // CONFIGURATION DES TRANSPORTEURS
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
            // PRÉPARATION D'ENLÈVEMENT - SECTION CORRIGÉE
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
            // STATISTIQUES ET APIs - SECTION CORRIGÉE
            // ================================
            Route::get('stats', [DeliveryController::class, 'stats'])
                ->name('stats');
                
            // 🔧 ROUTE CORRIGÉE pour les statistiques générales
            Route::get('api/general-stats', [DeliveryController::class, 'getGeneralStats'])
                ->name('api.general-stats');
                
            // Route ancienne maintenue pour compatibilité
            Route::get('api/stats', [DeliveryController::class, 'getApiStats'])
                ->name('api.stats');
                
            Route::post('api/track-all', [DeliveryController::class, 'trackAllShipments'])
                ->name('api.track-all');
            
            // ================================
            // ROUTES ADDITIONNELLES POUR LES NOUVELLES VUES
            // ================================
            
            // API pour l'activité récente
            Route::get('api/recent-activity', [DeliveryController::class, 'getRecentActivity'])
                ->name('api.recent-activity');
            
            // Export des données
            Route::get('export/pickups', [DeliveryController::class, 'exportPickups'])
                ->name('export.pickups');
            Route::get('export/shipments', [DeliveryController::class, 'exportShipments'])
                ->name('export.shipments');
            
            // Duplication de configuration
            Route::post('configuration/{config}/duplicate', [DeliveryController::class, 'duplicateConfiguration'])
                ->name('configuration.duplicate');
            
            // Validation en masse
            Route::post('pickups/bulk-validate', [DeliveryController::class, 'bulkValidatePickups'])
                ->name('pickups.bulk-validate');
            
            // Tracking en masse
            Route::post('shipments/bulk-track', [DeliveryController::class, 'bulkTrackShipments'])
                ->name('shipments.bulk-track');
            
            // Marquer comme récupéré
            Route::post('pickups/{pickup}/mark-picked-up', [DeliveryController::class, 'markPickupAsPickedUp'])
                ->name('pickups.mark-picked-up');
            
            // Rapport de livraison
            Route::get('shipments/{shipment}/delivery-proof', [DeliveryController::class, 'generateDeliveryProof'])
                ->name('shipments.delivery-proof');
            
            // Webhooks pour les transporteurs (si supportés)
            Route::post('webhook/jax-delivery', [DeliveryController::class, 'webhookJaxDelivery'])
                ->name('webhook.jax-delivery');
            Route::post('webhook/mes-colis', [DeliveryController::class, 'webhookMesColis'])
                ->name('webhook.mes-colis');
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
                'total_pickups' => $admin->pickups()->count(),
                'draft_pickups' => $admin->pickups()->where('status', 'draft')->count(),
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
                    'routes_check' => [
                        'general_stats_route_exists' => \Route::has('admin.delivery.api.general-stats'),
                        'delivery_index_exists' => \Route::has('admin.delivery.index'),
                        'delivery_config_exists' => \Route::has('admin.delivery.configuration'),
                        'test_system_exists' => \Route::has('admin.delivery.test-system'),
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
                    'shipments' => route('admin.delivery.shipments'),
                    'stats' => route('admin.delivery.stats'),
                    'api_general_stats' => route('admin.delivery.api.general-stats'),
                    'test_system' => route('admin.delivery.test-system'), // 🆕
                    'test_create_pickup' => route('admin.delivery.test-create-pickup'), // 🆕
                    'validate_data' => route('admin.delivery.validate-data'), // 🆕
                ],
                'available_views' => [
                    'admin.delivery.index',
                    'admin.delivery.configuration',
                    'admin.delivery.configuration-create',
                    'admin.delivery.configuration-edit',
                    'admin.delivery.preparation',
                    'admin.delivery.pickups',
                    'admin.delivery.shipments',
                    'admin.delivery.components.carrier-card',
                    'admin.delivery.components.pickup-status-badge',
                    'admin.delivery.components.shipment-status-badge',
                    'admin.delivery.components.tracking-history',
                    'admin.delivery.modals.test-connection',
                    'admin.delivery.modals.pickup-details',
                    'admin.delivery.modals.shipment-details',
                    'admin.delivery.partials.recent-activity',
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

        // 🆕 NOUVELLE ROUTE DE TEST COMPLET DU SYSTÈME
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
                'delivery.test-system',
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
    });
});