<?php

use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\AnalyticsController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\SystemController;
use App\Http\Controllers\SuperAdmin\NotificationController;
use Illuminate\Support\Facades\Route;

// ========================================
// ROUTES SUPER ADMIN
// ========================================
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // ========================================
    // AUTHENTIFICATION (non protégées)
    // ========================================
    Route::get('login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    
    // ========================================
    // ROUTES PROTÉGÉES PAR AUTHENTIFICATION
    // ========================================
    Route::middleware(['auth:super-admin', \App\Http\Middleware\SuperAdminActive::class])->group(function () {
        
        // Déconnexion
        Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
        
        // ========================================
        // DASHBOARD PRINCIPAL
        // ========================================
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);
        
        // APIs temps réel pour le dashboard
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('dashboard/stats', [DashboardController::class, 'getRealtimeStats'])->name('dashboard.stats');
            Route::get('dashboard/charts', [DashboardController::class, 'getChartData'])->name('dashboard.charts');
            Route::get('dashboard/activity', [DashboardController::class, 'getRecentActivity'])->name('dashboard.activity');
            Route::get('dashboard/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
        });
        
        // ========================================
        // GESTION DES ADMINISTRATEURS
        // ========================================
        Route::prefix('admins')->name('admins.')->group(function () {
            // CRUD de base
            Route::get('/', [AdminController::class, 'index'])->name('index');
            Route::get('create', [AdminController::class, 'create'])->name('create');
            Route::post('/', [AdminController::class, 'store'])->name('store');
            Route::get('{admin}', [AdminController::class, 'show'])->name('show');
            Route::get('{admin}/edit', [AdminController::class, 'edit'])->name('edit');
            Route::put('{admin}', [AdminController::class, 'update'])->name('update');
            Route::delete('{admin}', [AdminController::class, 'destroy'])->name('destroy');
            
            // Actions spécifiques
            Route::patch('{admin}/toggle-active', [AdminController::class, 'toggleActive'])->name('toggle-active');
            Route::patch('{admin}/extend-subscription', [AdminController::class, 'extendSubscription'])->name('extend-subscription');
            Route::post('{admin}/reset-password', [AdminController::class, 'resetPassword'])->name('reset-password');
            Route::get('{admin}/activity-log', [AdminController::class, 'activityLog'])->name('activity-log');
            Route::get('{admin}/statistics', [AdminController::class, 'statistics'])->name('statistics');
            
            // Actions en lot
            Route::post('bulk-actions', [AdminController::class, 'bulkActions'])->name('bulk-actions');
            
            // Export des données
            Route::get('export/csv', [AdminController::class, 'exportCsv'])->name('export.csv');
            Route::get('export/excel', [AdminController::class, 'exportExcel'])->name('export.excel');
            Route::get('export/pdf', [AdminController::class, 'exportPdf'])->name('export.pdf');
            
            // API pour recherche et filtrage
            Route::get('api/search', [AdminController::class, 'search'])->name('api.search');
            Route::get('api/statistics', [AdminController::class, 'getStatistics'])->name('api.statistics');
        });
        
        // ========================================
        // ANALYTICS ET RAPPORTS
        // ========================================
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('performance', [AnalyticsController::class, 'performance'])->name('performance');
            Route::get('usage', [AnalyticsController::class, 'usage'])->name('usage');
            Route::get('trends', [AnalyticsController::class, 'trends'])->name('trends');
            Route::get('geographic', [AnalyticsController::class, 'geographic'])->name('geographic');
            
            // APIs pour les données analytiques
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('performance-data', [AnalyticsController::class, 'getPerformanceData'])->name('performance');
                Route::get('usage-data', [AnalyticsController::class, 'getUsageData'])->name('usage');
                Route::get('trends-data', [AnalyticsController::class, 'getTrendsData'])->name('trends');
                Route::get('geographic-data', [AnalyticsController::class, 'getGeographicData'])->name('geographic');
            });
        });
        
        // ========================================
        // RAPPORTS
        // ========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('admin-activity', [ReportController::class, 'adminActivity'])->name('admin-activity');
            Route::get('system-usage', [ReportController::class, 'systemUsage'])->name('system-usage');
            Route::get('performance', [ReportController::class, 'performance'])->name('performance');
            Route::get('custom', [ReportController::class, 'custom'])->name('custom');
            
            // Génération et téléchargement
            Route::post('generate', [ReportController::class, 'generate'])->name('generate');
            Route::get('download/{report}', [ReportController::class, 'download'])->name('download');
            Route::get('scheduled', [ReportController::class, 'scheduled'])->name('scheduled');
            Route::post('schedule', [ReportController::class, 'schedule'])->name('schedule');
        });
        
        // ========================================
        // NOTIFICATIONS
        // ========================================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('mark-read/{notification}', [NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::get('api/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.unread-count');
        });
        
        // ========================================
        // SYSTÈME ET MAINTENANCE
        // ========================================
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemController::class, 'index'])->name('index');
            Route::get('health', [SystemController::class, 'health'])->name('health');
            Route::get('logs', [SystemController::class, 'logs'])->name('logs');
            Route::get('backups', [SystemController::class, 'backups'])->name('backups');
            Route::get('maintenance', [SystemController::class, 'maintenance'])->name('maintenance');
            
            // Actions système
            Route::post('backup/create', [SystemController::class, 'createBackup'])->name('backup.create');
            Route::post('maintenance/toggle', [SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');
            Route::post('cache/clear', [SystemController::class, 'clearCache'])->name('cache.clear');
            Route::post('logs/clear', [SystemController::class, 'clearLogs'])->name('logs.clear');
            
            // API système
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('health-status', [SystemController::class, 'getHealthStatus'])->name('health-status');
                Route::get('performance-metrics', [SystemController::class, 'getPerformanceMetrics'])->name('performance-metrics');
                Route::get('disk-usage', [SystemController::class, 'getDiskUsage'])->name('disk-usage');
            });
        });
        
        // ========================================
        // PARAMÈTRES
        // ========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('update', [SettingController::class, 'update'])->name('update');
            Route::get('security', [SettingController::class, 'security'])->name('security');
            Route::post('security/update', [SettingController::class, 'updateSecurity'])->name('security.update');
            Route::get('notifications', [SettingController::class, 'notifications'])->name('notifications');
            Route::post('notifications/update', [SettingController::class, 'updateNotifications'])->name('notifications.update');
        });
        
        // ========================================
        // PROFILE SUPER ADMIN
        // ========================================
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [SuperAdminAuthController::class, 'profile'])->name('index');
            Route::put('update', [SuperAdminAuthController::class, 'updateProfile'])->name('update');
            Route::put('password', [SuperAdminAuthController::class, 'updatePassword'])->name('password');
            Route::post('avatar', [SuperAdminAuthController::class, 'uploadAvatar'])->name('avatar');
        });
    });
});