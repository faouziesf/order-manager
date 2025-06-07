<?php

use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\AnalyticsController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\SystemController;
use Illuminate\Support\Facades\Route;

// ========================================
// ROUTES SUPER ADMIN
// ========================================
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // ========================================
    // AUTHENTIFICATION
    // ========================================
    Route::get('login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    
    // ========================================
    // ROUTES PROTÉGÉES
    // ========================================
    Route::middleware('auth:super-admin')->group(function () {
        Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
        
        // ========================================
        // DASHBOARD ET ANALYTICS
        // ========================================
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // API pour le dashboard en temps réel
        Route::get('api/dashboard/stats', [DashboardController::class, 'getRealtimeStats'])->name('api.dashboard.stats');
        Route::get('api/dashboard/charts', [DashboardController::class, 'getChartData'])->name('api.dashboard.charts');
        Route::get('api/dashboard/activity', [DashboardController::class, 'getRecentActivity'])->name('api.dashboard.activity');
        
        // Analytics avancées
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
            Route::get('/trends', [AnalyticsController::class, 'trends'])->name('trends');
            Route::get('/usage', [AnalyticsController::class, 'usage'])->name('usage');
            Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
            
            // API pour les analytics
            Route::get('/api/performance-data', [AnalyticsController::class, 'getPerformanceData'])->name('api.performance');
            Route::get('/api/trends-data', [AnalyticsController::class, 'getTrendsData'])->name('api.trends');
            Route::get('/api/usage-data', [AnalyticsController::class, 'getUsageData'])->name('api.usage');
            Route::get('/api/revenue-data', [AnalyticsController::class, 'getRevenueData'])->name('api.revenue');
        });
        
        // ========================================
        // GESTION DES ADMINS
        // ========================================
        Route::resource('admins', AdminController::class);
        Route::patch('admins/{admin}/toggle-active', [AdminController::class, 'toggleActive'])
            ->name('admins.toggle-active');
        
        // Actions en lot pour les admins
        Route::post('admins/bulk-activate', [AdminController::class, 'bulkActivate'])
            ->name('admins.bulk-activate');
        Route::post('admins/bulk-deactivate', [AdminController::class, 'bulkDeactivate'])
            ->name('admins.bulk-deactivate');
        Route::post('admins/bulk-extend', [AdminController::class, 'bulkExtend'])
            ->name('admins.bulk-extend');
        Route::delete('admins/bulk-delete', [AdminController::class, 'bulkDelete'])
            ->name('admins.bulk-delete');
        
        // Export des données admins
        Route::get('admins/export/csv', [AdminController::class, 'exportCsv'])
            ->name('admins.export.csv');
        Route::get('admins/export/excel', [AdminController::class, 'exportExcel'])
            ->name('admins.export.excel');
        Route::get('admins/export/pdf', [AdminController::class, 'exportPdf'])
            ->name('admins.export.pdf');
        
        // API pour la recherche et filtrage
        Route::get('api/admins/search', [AdminController::class, 'search'])
            ->name('api.admins.search');
        Route::get('api/admins/stats', [AdminController::class, 'getStats'])
            ->name('api.admins.stats');
        
        // ========================================
        // RAPPORTS
        // ========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
            Route::get('/activity', [ReportController::class, 'activity'])->name('activity');
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/custom', [ReportController::class, 'custom'])->name('custom');
            
            // Génération de rapports
            Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
            Route::get('/download/{report}', [ReportController::class, 'download'])->name('download');
            Route::get('/scheduled', [ReportController::class, 'scheduled'])->name('scheduled');
            Route::post('/schedule', [ReportController::class, 'schedule'])->name('schedule');
        });
        
        // ========================================
        // SYSTÈME ET MAINTENANCE
        // ========================================
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemController::class, 'index'])->name('index');
            Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
            Route::get('/backup', [SystemController::class, 'backup'])->name('backup');
            Route::get('/maintenance', [SystemController::class, 'maintenance'])->name('maintenance');
            Route::get('/security', [SystemController::class, 'security'])->name('security');
            
            // Actions système
            Route::post('/backup/create', [SystemController::class, 'createBackup'])->name('backup.create');
            Route::post('/maintenance/toggle', [SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');
            Route::post('/cache/clear', [SystemController::class, 'clearCache'])->name('cache.clear');
            Route::post('/logs/clear', [SystemController::class, 'clearLogs'])->name('logs.clear');
            
            // Monitoring système
            Route::get('/api/health', [SystemController::class, 'getHealthStatus'])->name('api.health');
            Route::get('/api/performance', [SystemController::class, 'getPerformanceMetrics'])->name('api.performance');
        });
        
        // ========================================
        // PARAMÈTRES GLOBAUX
        // ========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            
            // Catégories de paramètres
            Route::get('/general', [SettingController::class, 'general'])->name('general');
            Route::get('/security', [SettingController::class, 'security'])->name('security');
            Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
            Route::get('/integrations', [SettingController::class, 'integrations'])->name('integrations');
            Route::get('/appearance', [SettingController::class, 'appearance'])->name('appearance');
            
            // Sauvegarde et restauration des paramètres
            Route::get('/export', [SettingController::class, 'export'])->name('export');
            Route::post('/import', [SettingController::class, 'import'])->name('import');
            Route::post('/reset', [SettingController::class, 'reset'])->name('reset');
        });
        
        // ========================================
        // NOTIFICATIONS ET ALERTES
        // ========================================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [SystemController::class, 'notifications'])->name('index');
            Route::post('/mark-read/{notification}', [SystemController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [SystemController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/delete/{notification}', [SystemController::class, 'deleteNotification'])->name('delete');
            Route::get('/api/unread-count', [SystemController::class, 'getUnreadCount'])->name('api.unread-count');
        });
    });
});