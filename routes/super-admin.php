<?php

use App\Http\Controllers\SuperAdmin\Auth\AuthController;
use App\Http\Controllers\SuperAdmin\Dashboard\DashboardController;
use App\Http\Controllers\SuperAdmin\Admin\AdminController;
use App\Http\Controllers\SuperAdmin\Setting\SettingController;
use App\Http\Controllers\SuperAdmin\Analytics\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // ========================================
    // AUTHENTIFICATION SUPER ADMIN
    // ========================================
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    
    // ========================================
    // ROUTES PROTÉGÉES SUPER ADMIN
    // ========================================
    Route::middleware('auth:super-admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        
        // ========================================
        // GESTION DES ADMINS
        // ========================================
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('index');
            Route::get('create', [AdminController::class, 'create'])->name('create');
            Route::post('/', [AdminController::class, 'store'])->name('store');
            Route::get('{admin}', [AdminController::class, 'show'])->name('show');
            Route::get('{admin}/edit', [AdminController::class, 'edit'])->name('edit');
            Route::put('{admin}', [AdminController::class, 'update'])->name('update');
            Route::delete('{admin}', [AdminController::class, 'destroy'])->name('destroy');
            Route::patch('{admin}/toggle-active', [AdminController::class, 'toggleActive'])->name('toggle-active');
            Route::post('{admin}/extend-expiry', [AdminController::class, 'extendExpiry'])->name('extend-expiry');
            Route::get('{admin}/stats', [AdminController::class, 'getAdminStats'])->name('stats');
        });
        
        // ========================================
        // ANALYTICS ET RAPPORTS
        // ========================================
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('global-stats', [AnalyticsController::class, 'globalStats'])->name('global-stats');
            Route::get('admin-performance', [AnalyticsController::class, 'adminPerformance'])->name('admin-performance');
            Route::get('system-health', [AnalyticsController::class, 'systemHealth'])->name('system-health');
            Route::get('reports/generate', [AnalyticsController::class, 'generateReport'])->name('reports.generate');
        });
        
        // ========================================
        // PARAMÈTRES GLOBAUX
        // ========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            Route::get('system', [SettingController::class, 'systemSettings'])->name('system');
            Route::post('system', [SettingController::class, 'updateSystemSettings'])->name('system.update');
            Route::get('backup', [SettingController::class, 'backup'])->name('backup');
            Route::post('backup/create', [SettingController::class, 'createBackup'])->name('backup.create');
            Route::get('maintenance', [SettingController::class, 'maintenance'])->name('maintenance');
            Route::post('maintenance/toggle', [SettingController::class, 'toggleMaintenance'])->name('maintenance.toggle');
        });
    });
});