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
use Illuminate\Support\Facades\Route;

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
        // GESTION DES COMMANDES
        // ========================================

        // Routes spéciales AVANT le resource
        Route::post('orders/bulk-assign', [OrderController::class, 'bulkAssign'])
            ->name('orders.bulk-assign');
        Route::post('orders/{order}/unassign', [OrderController::class, 'unassign'])
            ->name('orders.unassign');
        Route::get('orders/unassigned', [OrderController::class, 'unassigned'])
            ->name('orders.unassigned');
        Route::get('orders/{order}/history', [OrderController::class, 'showHistory'])
            ->name('orders.history');
        Route::get('orders/{order}/history-modal', [OrderController::class, 'getHistory'])
            ->name('orders.history-modal');
        Route::post('orders/{order}/record-attempt', [OrderController::class, 'recordAttempt'])
            ->name('orders.recordAttempt');
        Route::post('orders/{order}/quick-attempt', [OrderController::class, 'quickAttempt'])
            ->name('orders.quick-attempt');

        // Routes pour l'interface de traitement
        Route::get('orders/get-regions', [OrderController::class, 'getRegions'])
            ->name('orders.getRegions');
        Route::get('orders/get-cities', [OrderController::class, 'getCities'])
            ->name('orders.getCities');
        Route::get('orders/search-products', [OrderController::class, 'searchProducts'])
            ->name('orders.searchProducts');

        // CRUD de base
        Route::resource('orders', OrderController::class);

        // ========================================
        // TRAITEMENT DES COMMANDES
        // ========================================

        // Interface de traitement principal
        Route::get('process', [ProcessController::class, 'interface'])
            ->name('process.interface');
        Route::get('process/test', [ProcessController::class, 'test'])
            ->name('process.test');
        Route::get('process/counts', [ProcessController::class, 'getCounts'])
            ->name('process.getCounts');
        Route::post('process/action/{order}', [ProcessController::class, 'processAction'])
            ->name('process.action');

        // Route API spécifique pour restock
        Route::get('process/api/restock', [ProcessController::class, 'getQueue'])
            ->defaults('queue', 'restock')
            ->name('process.api.restock');

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

        // Route générique pour les autres onglets
        Route::get('process/{queue}', [ProcessController::class, 'getQueue'])
            ->where('queue', 'standard|dated|old')
            ->name('process.getQueue');

        // ========================================
        // GESTION DES UTILISATEURS
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
        // IMPORTATION ET INTÉGRATIONS
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
        // GESTION DES COMMANDES DOUBLES
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
        // PARAMÈTRES
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
        // DEBUG
        // ========================================
        Route::get('debug-auth', function () {
            $admin = auth('admin')->user();
            return [
                'is_authenticated' => auth('admin')->check(),
                'admin_id' => $admin ? $admin->id : null,
                'admin_name' => $admin ? $admin->name : null,
                'admin_class' => $admin ? get_class($admin) : null,
                'admin_instance_check' => $admin instanceof \App\Models\Admin,
                'gates' => [
                    'view-process-interface' => \Gate::allows('view-process-interface', $admin),
                    'view-examination' => \Gate::allows('view-examination', $admin),
                    'view-suspended' => \Gate::allows('view-suspended', $admin),
                    'view-restock' => \Gate::allows('view-restock', $admin),
                ]
            ];
        })->name('debug-auth');
        // ========================================
        // GESTION DES LIVRAISONS
        // ========================================
        Route::prefix('delivery')->name('delivery.')->group(function () {
            // Configuration page
            Route::get('configuration', [App\Http\Controllers\Admin\DeliveryController::class, 'configuration'])
                ->name('configuration');
            Route::post('configuration', [App\Http\Controllers\Admin\DeliveryController::class, 'updateConfiguration'])
                ->name('configuration.update');

            // Management page
            Route::get('management', [App\Http\Controllers\Admin\DeliveryController::class, 'management'])
                ->name('management');
            Route::post('management', [App\Http\Controllers\Admin\DeliveryController::class, 'updateManagement'])
                ->name('management.update');

            // Additional routes you might need later
            Route::get('zones', [App\Http\Controllers\Admin\DeliveryController::class, 'zones'])
                ->name('zones');
            Route::get('tarifs', [App\Http\Controllers\Admin\DeliveryController::class, 'tarifs'])
                ->name('tarifs');
        });
        // Add this section in your routes/admin.php file, within the authenticated admin middleware group
        // Place it after the existing route groups (around line 150-160)

        // ========================================
        // GESTION DES LIVRAISONS
        // ========================================
        Route::prefix('delivery')->name('delivery.')->group(function () {
            // Configuration page
            Route::get('configuration', [App\Http\Controllers\Admin\DeliveryController::class, 'configuration'])
                ->name('configuration');
            Route::post('configuration', [App\Http\Controllers\Admin\DeliveryController::class, 'updateConfiguration'])
                ->name('configuration.update');

            // Management page
            Route::get('management', [App\Http\Controllers\Admin\DeliveryController::class, 'management'])
                ->name('management');
            Route::post('management', [App\Http\Controllers\Admin\DeliveryController::class, 'updateManagement'])
                ->name('management.update');

            // FParcel API connection routes
            Route::get('status', [App\Http\Controllers\Admin\DeliveryController::class, 'getConnectionStatus'])
                ->name('status');
            Route::post('connect', [App\Http\Controllers\Admin\DeliveryController::class, 'connectToFParcel'])
                ->name('connect');
            Route::post('test', [App\Http\Controllers\Admin\DeliveryController::class, 'testConnection'])
                ->name('test');
            Route::post('disconnect', [App\Http\Controllers\Admin\DeliveryController::class, 'disconnect'])
                ->name('disconnect');
            Route::post('refresh-token', [App\Http\Controllers\Admin\DeliveryController::class, 'refreshToken'])
                ->name('refresh-token');

            // Synchronization routes
            Route::post('sync-payment-methods', [App\Http\Controllers\Admin\DeliveryController::class, 'syncPaymentMethods'])
                ->name('sync-payment-methods');
            Route::post('sync-drop-points', [App\Http\Controllers\Admin\DeliveryController::class, 'syncDropPoints'])
                ->name('sync-drop-points');
            Route::post('sync-anomaly-reasons', [App\Http\Controllers\Admin\DeliveryController::class, 'syncAnomalyReasons'])
                ->name('sync-anomaly-reasons');

            // Additional utility routes
            Route::get('zones', [App\Http\Controllers\Admin\DeliveryController::class, 'zones'])
                ->name('zones');
            Route::get('tarifs', [App\Http\Controllers\Admin\DeliveryController::class, 'tarifs'])
                ->name('tarifs');
        });
    });
});
