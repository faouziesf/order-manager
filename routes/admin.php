<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProcessController;
use App\Http\Controllers\Admin\Process\ExaminationController;
use App\Http\Controllers\Admin\Process\SuspendedOrderController;
use App\Http\Controllers\Admin\Process\StockReturnController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\WooCommerceController;
use App\Http\Controllers\Admin\SettingController;
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
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        
        // ========================================
        // GESTION DES PRODUITS
        // ========================================
        Route::resource('products', ProductController::class);
        Route::prefix('products')->name('products.')->group(function () {
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
        Route::resource('orders', OrderController::class);
        Route::prefix('orders')->name('orders.')->group(function () {
            // Vues spécialisées
            Route::get('unassigned/list', [OrderController::class, 'unassigned'])->name('unassigned');
            
            // Actions sur commandes
            Route::post('{order}/unassign', [OrderController::class, 'unassign'])->name('unassign');
            Route::post('bulk/assign', [OrderController::class, 'bulkAssign'])->name('bulk.assign');
            Route::post('{order}/record-attempt', [OrderController::class, 'recordAttempt'])->name('record-attempt');
            
            // Historique
            Route::get('{order}/history', [OrderController::class, 'showHistory'])->name('history');
            Route::get('{order}/history/modal', [OrderController::class, 'getHistory'])->name('history.modal');
            
            // APIs pour recherche
            Route::get('api/search-products', [OrderController::class, 'searchProducts'])->name('search-products');
            Route::get('api/regions', [OrderController::class, 'getRegions'])->name('get-regions');
            Route::get('api/cities', [OrderController::class, 'getCities'])->name('get-cities');
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
            
            // Vues spécialisées des files
            Route::get('standard', [ProcessController::class, 'standardQueue'])->name('standard');
            Route::get('dated', [ProcessController::class, 'datedQueue'])->name('dated');
            Route::get('old', [ProcessController::class, 'oldQueue'])->name('old');
            
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
            
            // Interface retour en stock
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
        Route::resource('managers', ManagerController::class);
        Route::prefix('managers')->name('managers.')->group(function () {
            Route::patch('{manager}/toggle-active', [ManagerController::class, 'toggleActive'])->name('toggle-active');
            Route::get('api/list', [ManagerController::class, 'getManagersForAdmin'])->name('api.list');
        });
        
        Route::resource('employees', EmployeeController::class);
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::patch('{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('toggle-active');
        });
        
        // ========================================
        // HISTORIQUE ET LOGS
        // ========================================
        Route::prefix('history')->name('history.')->group(function () {
            Route::get('login', [LoginHistoryController::class, 'index'])->name('login.index');
            Route::get('login/{user_type}/{user_id}', [LoginHistoryController::class, 'show'])->name('login.show');
        });
        
        // ========================================
        // IMPORTATION
        // ========================================
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/', [ImportController::class, 'index'])->name('index');
            Route::post('csv', [ImportController::class, 'importCsv'])->name('csv');
            Route::post('xml', [ImportController::class, 'importXml'])->name('xml');
        });
        
        // ========================================
        // INTÉGRATIONS
        // ========================================
        Route::prefix('woocommerce')->name('woocommerce.')->group(function () {
            Route::get('/', [WooCommerceController::class, 'index'])->name('index');
            Route::post('/', [WooCommerceController::class, 'store'])->name('store');
            Route::get('sync', [WooCommerceController::class, 'sync'])->name('sync');
            Route::post('test-connection', [WooCommerceController::class, 'testConnection'])->name('test-connection');
            Route::get('stats', [WooCommerceController::class, 'syncStats'])->name('stats');
            Route::post('toggle/{id}', [WooCommerceController::class, 'toggleIntegration'])->name('toggle');
            Route::delete('{id}', [WooCommerceController::class, 'deleteIntegration'])->name('delete');
        });
        
        // ========================================
        // PARAMÈTRES
        // ========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'store'])->name('store');
        });
    });
});