<?php

use App\Http\Controllers\Manager\Auth\AuthController;
use App\Http\Controllers\Manager\Dashboard\DashboardController;
use App\Http\Controllers\Manager\Order\OrderController;
use App\Http\Controllers\Manager\Employee\EmployeeController;
use App\Http\Controllers\Manager\Report\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')->name('manager.')->group(function () {
    // ========================================
    // AUTHENTIFICATION MANAGER
    // ========================================
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // ========================================
    // ROUTES PROTÉGÉES MANAGER
    // ========================================
    Route::middleware('auth:manager')->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        
        // ========================================
        // GESTION DES COMMANDES
        // ========================================
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('{order}', [OrderController::class, 'show'])->name('show');
            Route::get('{order}/edit', [OrderController::class, 'edit'])->name('edit');
            Route::put('{order}', [OrderController::class, 'update'])->name('update');
            Route::get('{order}/history', [OrderController::class, 'history'])->name('history');
            
            // Assignation
            Route::post('{order}/assign', [OrderController::class, 'assign'])->name('assign');
            Route::post('{order}/unassign', [OrderController::class, 'unassign'])->name('unassign');
            Route::post('bulk/assign', [OrderController::class, 'bulkAssign'])->name('bulk.assign');
            
            // Supervision
            Route::get('assigned/list', [OrderController::class, 'assigned'])->name('assigned');
            Route::get('problematic/list', [OrderController::class, 'problematic'])->name('problematic');
            Route::post('{order}/priority', [OrderController::class, 'updatePriority'])->name('priority');
        });
        
        // ========================================
        // GESTION DES EMPLOYÉS
        // ========================================
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('{employee}', [EmployeeController::class, 'show'])->name('show');
            Route::get('{employee}/performance', [EmployeeController::class, 'performance'])->name('performance');
            Route::get('{employee}/orders', [EmployeeController::class, 'orders'])->name('orders');
            Route::post('{employee}/note', [EmployeeController::class, 'addNote'])->name('note');
        });
        
        // ========================================
        // RAPPORTS ET ANALYTICS
        // ========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('daily', [ReportController::class, 'daily'])->name('daily');
            Route::get('weekly', [ReportController::class, 'weekly'])->name('weekly');
            Route::get('monthly', [ReportController::class, 'monthly'])->name('monthly');
            Route::get('employee-performance', [ReportController::class, 'employeePerformance'])->name('employee-performance');
            Route::post('generate', [ReportController::class, 'generate'])->name('generate');
        });
    });
});