<?php

use App\Http\Controllers\Employee\Auth\AuthController;
use App\Http\Controllers\Employee\Dashboard\DashboardController;
use App\Http\Controllers\Employee\Order\OrderController;
use App\Http\Controllers\Employee\Process\ProcessController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee')->name('employee.')->group(function () {
    // ========================================
    // AUTHENTIFICATION EMPLOYEE
    // ========================================
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // ========================================
    // ROUTES PROTÉGÉES EMPLOYEE
    // ========================================
    Route::middleware('auth:employee')->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        
        // ========================================
        // TRAITEMENT DES COMMANDES ASSIGNÉES
        // ========================================
        Route::prefix('process')->name('process.')->group(function () {
            Route::get('/', [ProcessController::class, 'interface'])->name('interface');
            Route::get('my-orders', [ProcessController::class, 'myOrders'])->name('my-orders');
            Route::get('next-order', [ProcessController::class, 'nextOrder'])->name('next-order');
            Route::post('action/{order}', [ProcessController::class, 'processAction'])->name('action');
            Route::post('{order}/attempt', [ProcessController::class, 'recordAttempt'])->name('attempt');
        });
        
        // ========================================
        // GESTION DES COMMANDES
        // ========================================
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('assigned', [OrderController::class, 'assigned'])->name('assigned');
            Route::get('completed', [OrderController::class, 'completed'])->name('completed');
            Route::get('{order}', [OrderController::class, 'show'])->name('show');
            Route::get('{order}/history', [OrderController::class, 'history'])->name('history');
            Route::post('{order}/note', [OrderController::class, 'addNote'])->name('note');
        });
        
        // ========================================
        // PERFORMANCE ET STATISTIQUES
        // ========================================
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/', [DashboardController::class, 'performance'])->name('index');
            Route::get('daily', [DashboardController::class, 'dailyStats'])->name('daily');
            Route::get('weekly', [DashboardController::class, 'weeklyStats'])->name('weekly');
            Route::get('goals', [DashboardController::class, 'goals'])->name('goals');
        });
    });
});