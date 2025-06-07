<?php

use App\Http\Controllers\Manager\AuthController as ManagerAuthController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\OrderController as ManagerOrderController;
use App\Http\Controllers\Manager\EmployeeController as ManagerEmployeeController;
use App\Http\Controllers\Manager\ReportController as ManagerReportController;
use Illuminate\Support\Facades\Route;

// ========================================
// ROUTES MANAGER
// ========================================
Route::prefix('manager')->name('manager.')->group(function () {
    // ========================================
    // AUTHENTIFICATION MANAGER
    // ========================================
    Route::get('login', [ManagerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ManagerAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [ManagerAuthController::class, 'logout'])->name('logout');
    
    // ========================================
    // ROUTES PROTÉGÉES MANAGER
    // ========================================
    Route::middleware('auth:manager')->group(function () {
        // Dashboard
        Route::get('dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::get('api/dashboard/stats', [ManagerDashboardController::class, 'getStats'])->name('api.dashboard.stats');
        
        // ========================================
        // GESTION DES COMMANDES (VUE MANAGER)
        // ========================================
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [ManagerOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [ManagerOrderController::class, 'show'])->name('show');
            Route::get('/assigned/to-me', [ManagerOrderController::class, 'assignedToMe'])->name('assigned-to-me');
            Route::get('/team/assigned', [ManagerOrderController::class, 'teamAssigned'])->name('team-assigned');
            
            // Actions sur les commandes
            Route::post('/{order}/assign-employee', [ManagerOrderController::class, 'assignToEmployee'])->name('assign-employee');
            Route::post('/{order}/take-ownership', [ManagerOrderController::class, 'takeOwnership'])->name('take-ownership');
            Route::post('/{order}/update-status', [ManagerOrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/add-note', [ManagerOrderController::class, 'addNote'])->name('add-note');
            
            // API pour la recherche et filtres
            Route::get('/api/search', [ManagerOrderController::class, 'search'])->name('api.search');
            Route::get('/api/stats', [ManagerOrderController::class, 'getOrderStats'])->name('api.stats');
        });
        
        // ========================================
        // GESTION DES EMPLOYÉS
        // ========================================
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [ManagerEmployeeController::class, 'index'])->name('index');
            Route::get('/{employee}', [ManagerEmployeeController::class, 'show'])->name('show');
            Route::get('/{employee}/performance', [ManagerEmployeeController::class, 'performance'])->name('performance');
            
            // Actions sur les employés
            Route::post('/{employee}/assign-orders', [ManagerEmployeeController::class, 'assignOrders'])->name('assign-orders');
            Route::post('/{employee}/set-target', [ManagerEmployeeController::class, 'setTarget'])->name('set-target');
            Route::post('/{employee}/add-feedback', [ManagerEmployeeController::class, 'addFeedback'])->name('add-feedback');
            
            // API pour les statistiques employés
            Route::get('/api/performance-data', [ManagerEmployeeController::class, 'getPerformanceData'])->name('api.performance');
            Route::get('/api/workload', [ManagerEmployeeController::class, 'getWorkloadData'])->name('api.workload');
        });
        
        // ========================================
        // RAPPORTS MANAGER
        // ========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ManagerReportController::class, 'index'])->name('index');
            Route::get('/team-performance', [ManagerReportController::class, 'teamPerformance'])->name('team-performance');
            Route::get('/orders-summary', [ManagerReportController::class, 'ordersSummary'])->name('orders-summary');
            Route::get('/productivity', [ManagerReportController::class, 'productivity'])->name('productivity');
            
            // Export de rapports
            Route::get('/export/team-performance', [ManagerReportController::class, 'exportTeamPerformance'])->name('export.team-performance');
            Route::get('/export/orders', [ManagerReportController::class, 'exportOrders'])->name('export.orders');
        });
        
        // ========================================
        // PARAMÈTRES MANAGER
        // ========================================
        Route::get('settings', [ManagerDashboardController::class, 'settings'])->name('settings');
        Route::post('settings', [ManagerDashboardController::class, 'updateSettings'])->name('settings.update');
    });
});