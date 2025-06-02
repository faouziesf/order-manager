<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\Order\OrderProcessController;
use App\Http\Controllers\Admin\Process\ProcessController;
use App\Http\Controllers\Admin\Process\ExaminationController;
use App\Http\Controllers\Admin\Process\SuspendedOrderController;
use App\Http\Controllers\Admin\Process\StockReturnController;
use App\Http\Controllers\Admin\User\ManagerController;
use App\Http\Controllers\Admin\User\EmployeeController;
use App\Http\Controllers\Admin\History\LoginHistoryController;
use App\Http\Controllers\Admin\Import\ImportController;
use App\Http\Controllers\Admin\Integration\WooCommerceController;
use App\Http\Controllers\Admin\Setting\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // ========================================
    // AUTHENTIFICATION ADMIN
    // ========================================
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('expired', [AuthController::class, 'showExpiredPage'])->name('expired');
    
    // ========================================
    // ROUTES PROTÉGÉES ADMIN
    // ========================================
    Route::middleware('auth:admin')->group(function () {
        
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        
        // ========================================
        // GESTION DES PRODUITS
        // ========================================
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('{product}', [ProductController::class, 'show'])->name('show');
            Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
            
            // Actions spéciales
            Route::get('review/list', [ProductController::class, 'reviewNewProducts'])->name('review');
            Route::get('kanban/view', [ProductController::class, 'kanban'])->name('kanban');
            Route::get('stats/realtime', [ProductController::class, 'getRealtimeStats'])->name('stats.realtime');
            Route::get('search/live', [ProductController::class, 'liveSearch'])->name('search.live');
            Route::get('search/api', [ProductController::class, 'searchProducts'])->name('search.api');
            
            // Actions en lot
            Route::post('bulk/activate', [ProductController::class, 'bulkActivate'])->name('bulk.activate');
            Route::post('bulk/deactivate', [ProductController::class, 'bulkDeactivate'])->name('bulk.deactivate');
            Route::delete('bulk/delete', [ProductController::class, 'bulkDelete'])->name('bulk.delete');
            Route::post('mark-all-reviewed', [ProductController::class, 'markAllAsReviewed'])->name('mark-all-reviewed');
            Route::post('{product}/mark-reviewed', [ProductController::class, 'markAsReviewed'])->name('mark-reviewed');
        });
        
        // ========================================
        // GESTION DES COMMANDES
        // ========================================
        Route::prefix('orders')->name('orders.')->group(function () {
            // CRUD de base
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('{order}', [OrderController::class, 'show'])->name('show');
            Route::get('{order}/edit', [OrderController::class, 'edit'])->name('edit');
            Route::put('{order}', [OrderController::class, 'update'])->name('update');
            Route::delete('{order}', [OrderController::class, 'destroy'])->name('destroy');
            
            // Vues spécialisées
            Route::get('unassigned/list', [OrderController::class, 'unassigned'])->name('unassigned');
            Route::get('suspended/list', [OrderController::class, 'suspended'])->name('suspended');
            
            // Actions sur commandes
            Route::post('{order}/assign', [OrderController::class, 'assign'])->name('assign');
            Route::post('{order}/unassign', [OrderController::class, 'unassign'])->name('unassign');
            Route::post('bulk/assign', [OrderController::class, 'bulkAssign'])->name('bulk.assign');
            Route::post('{order}/record-attempt', [OrderController::class, 'recordAttempt'])->name('record-attempt');
            
            // Historique
            Route::get('{order}/history', [OrderController::class, 'showHistory'])->name('history');
            Route::get('{order}/history/modal', [OrderController::class, 'getHistory'])->name('history.modal');
        });
        
        // ========================================
        // APIS POUR INTERFACE DE TRAITEMENT
        // ========================================
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('regions', [OrderProcessController::class, 'getRegions'])->name('regions');
            Route::get('cities', [OrderProcessController::class, 'getCities'])->name('cities');
            Route::get('products/search', [OrderProcessController::class, 'searchProducts'])->name('products.search');
        });
        
        // ========================================
        // TRAITEMENT DES COMMANDES
        // ========================================
        Route::prefix('process')->name('process.')->group(function () {
            // Interface principale de traitement
            Route::get('/', [ProcessController::class, 'interface'])->name('interface');
            Route::get('test', [ProcessController::class, 'test'])->name('test');
            Route::get('counts', [ProcessController::class, 'getCounts'])->name('counts');
            Route::get('{queue}', [ProcessController::class, 'getQueue'])
                ->where('queue', 'standard|dated|old')
                ->name('queue');
            Route::post('action/{order}', [ProcessController::class, 'processAction'])->name('action');
            
            // Interface d'examen des commandes avec problèmes de stock
            Route::prefix('examination')->name('examination.')->group(function () {
                Route::get('/', [ExaminationController::class, 'index'])->name('index');
                Route::get('orders', [ExaminationController::class, 'getOrders'])->name('orders');
                Route::get('count', [ExaminationController::class, 'getCount'])->name('count');
                Route::post('split/{order}', [ExaminationController::class, 'splitOrder'])->name('split');
                Route::post('action/{order}', [ExaminationController::class, 'processAction'])->name('action');
                Route::post('bulk/split', [ExaminationController::class, 'bulkSplit'])->name('bulk.split');
                Route::post('bulk/cancel', [ExaminationController::class, 'bulkCancel'])->name('bulk.cancel');
                Route::post('bulk/suspend', [ExaminationController::class, 'bulkSuspend'])->name('bulk.suspend');
            });
            
            // Interface des commandes suspendues
            Route::prefix('suspended')->name('suspended.')->group(function () {
                Route::get('/', [SuspendedOrderController::class, 'index'])->name('index');
                Route::get('orders', [SuspendedOrderController::class, 'getOrders'])->name('orders');
                Route::get('count', [SuspendedOrderController::class, 'getCount'])->name('count');
                Route::post('reactivate/{order}', [SuspendedOrderController::class, 'reactivate'])->name('reactivate');
                Route::post('cancel/{order}', [SuspendedOrderController::class, 'cancel'])->name('cancel');
                Route::post('bulk/reactivate', [SuspendedOrderController::class, 'bulkReactivate'])->name('bulk.reactivate');
                Route::post('bulk/cancel', [SuspendedOrderController::class, 'bulkCancel'])->name('bulk.cancel');
            });
            
            // NOUVELLE: Interface retour en stock
            Route::prefix('stock-return')->name('stock-return.')->group(function () {
                Route::get('/', [StockReturnController::class, 'interface'])->name('interface');
                Route::get('orders', [StockReturnController::class, 'getOrders'])->name('orders');
                Route::get('count', [StockReturnController::class, 'getCount'])->name('count');
                Route::post('action/{order}', [StockReturnController::class, 'processAction'])->name('action');
                Route::post('reactivate/{order}', [StockReturnController::class, 'reactivateOrder'])->name('reactivate');
                Route::post('split/{order}', [StockReturnController::class, 'splitOrder'])->name('split');
            });
        });
        
        // ========================================
        // GESTION DES UTILISATEURS
        // ========================================
        Route::prefix('users')->name('users.')->group(function () {
            // Managers
            Route::prefix('managers')->name('managers.')->group(function () {
                Route::get('/', [ManagerController::class, 'index'])->name('index');
                Route::get('create', [ManagerController::class, 'create'])->name('create');
                Route::post('/', [ManagerController::class, 'store'])->name('store');
                Route::get('{manager}', [ManagerController::class, 'show'])->name('show');
                Route::get('{manager}/edit', [ManagerController::class, 'edit'])->name('edit');
                Route::put('{manager}', [ManagerController::class, 'update'])->name('update');
                Route::delete('{manager}', [ManagerController::class, 'destroy'])->name('destroy');
                Route::patch('{manager}/toggle-active', [ManagerController::class, 'toggleActive'])->name('toggle-active');
                Route::get('api/list', [ManagerController::class, 'getManagersForAdmin'])->name('api.list');
            });
            
            // Employés
            Route::prefix('employees')->name('employees.')->group(function () {
                Route::get('/', [EmployeeController::class, 'index'])->name('index');
                Route::get('create', [EmployeeController::class, 'create'])->name('create');
                Route::post('/', [EmployeeController::class, 'store'])->name('store');
                Route::get('{employee}', [EmployeeController::class, 'show'])->name('show');
                Route::get('{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
                Route::put('{employee}', [EmployeeController::class, 'update'])->name('update');
                Route::delete('{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
                Route::patch('{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('toggle-active');
            });
        });
        
        // ========================================
        // HISTORIQUE ET LOGS
        // ========================================
        Route::prefix('history')->name('history.')->group(function () {
            Route::get('login', [LoginHistoryController::class, 'index'])->name('login.index');
            Route::get('login/{user_type}/{user_id}', [LoginHistoryController::class, 'show'])->name('login.show');
        });
        
        // ========================================
        // IMPORTATION ET INTÉGRATIONS
        // ========================================
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/', [ImportController::class, 'index'])->name('index');
            Route::post('csv', [ImportController::class, 'importCsv'])->name('csv');
            Route::post('xml', [ImportController::class, 'importXml'])->name('xml');
            Route::post('excel', [ImportController::class, 'importExcel'])->name('excel');
        });
        
        Route::prefix('integrations')->name('integrations.')->group(function () {
            // WooCommerce
            Route::prefix('woocommerce')->name('woocommerce.')->group(function () {
                Route::get('/', [WooCommerceController::class, 'index'])->name('index');
                Route::post('/', [WooCommerceController::class, 'store'])->name('store');
                Route::get('sync', [WooCommerceController::class, 'sync'])->name('sync');
                Route::post('test-connection', [WooCommerceController::class, 'testConnection'])->name('test-connection');
                Route::get('stats', [WooCommerceController::class, 'syncStats'])->name('stats');
                Route::post('toggle/{id}', [WooCommerceController::class, 'toggleIntegration'])->name('toggle');
                Route::delete('{id}', [WooCommerceController::class, 'deleteIntegration'])->name('delete');
            });
        });
        
        // ========================================
        // PARAMÈTRES
        // ========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'store'])->name('store');
            Route::get('export', [SettingController::class, 'export'])->name('export');
            Route::post('import', [SettingController::class, 'import'])->name('import');
        });
    });
});