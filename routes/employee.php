<?php

use App\Http\Controllers\Employee\AuthController as EmployeeAuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\OrderController as EmployeeOrderController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use Illuminate\Support\Facades\Route;

// ========================================
// ROUTES EMPLOYEE
// ========================================
Route::prefix('employee')->name('employee.')->group(function () {
    // ========================================
    // AUTHENTIFICATION EMPLOYEE
    // ========================================
    Route::get('login', [EmployeeAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [EmployeeAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [EmployeeAuthController::class, 'logout'])->name('logout');
    
    // ========================================
    // ROUTES PROTÉGÉES EMPLOYEE
    // ========================================
    Route::middleware('auth:employee')->group(function () {
        // Dashboard
        Route::get('dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::get('api/dashboard/stats', [EmployeeDashboardController::class, 'getStats'])->name('api.dashboard.stats');
        
        // ========================================
        // GESTION DES COMMANDES ASSIGNÉES
        // ========================================
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [EmployeeOrderController::class, 'index'])->name('index');
            Route::get('/assigned', [EmployeeOrderController::class, 'assigned'])->name('assigned');
            Route::get('/completed', [EmployeeOrderController::class, 'completed'])->name('completed');
            Route::get('/{order}', [EmployeeOrderController::class, 'show'])->name('show');
            
            // Actions sur les commandes
            Route::post('/{order}/start-processing', [EmployeeOrderController::class, 'startProcessing'])->name('start-processing');
            Route::post('/{order}/complete', [EmployeeOrderController::class, 'complete'])->name('complete');
            Route::post('/{order}/pause', [EmployeeOrderController::class, 'pause'])->name('pause');
            Route::post('/{order}/resume', [EmployeeOrderController::class, 'resume'])->name('resume');
            Route::post('/{order}/request-help', [EmployeeOrderController::class, 'requestHelp'])->name('request-help');
            Route::post('/{order}/add-note', [EmployeeOrderController::class, 'addNote'])->name('add-note');
            Route::post('/{order}/update-status', [EmployeeOrderController::class, 'updateStatus'])->name('update-status');
            
            // API pour les données en temps réel
            Route::get('/api/queue', [EmployeeOrderController::class, 'getQueue'])->name('api.queue');
            Route::get('/api/stats', [EmployeeOrderController::class, 'getStats'])->name('api.stats');
        });
        
        // ========================================
        // GESTION DES TÂCHES
        // ========================================
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [EmployeeTaskController::class, 'index'])->name('index');
            Route::get('/pending', [EmployeeTaskController::class, 'pending'])->name('pending');
            Route::get('/in-progress', [EmployeeTaskController::class, 'inProgress'])->name('in-progress');
            Route::get('/completed', [EmployeeTaskController::class, 'completed'])->name('completed');
            Route::get('/{task}', [EmployeeTaskController::class, 'show'])->name('show');
            
            // Actions sur les tâches
            Route::post('/{task}/start', [EmployeeTaskController::class, 'start'])->name('start');
            Route::post('/{task}/complete', [EmployeeTaskController::class, 'complete'])->name('complete');
            Route::post('/{task}/pause', [EmployeeTaskController::class, 'pause'])->name('pause');
            Route::post('/{task}/resume', [EmployeeTaskController::class, 'resume'])->name('resume');
            Route::post('/{task}/add-comment', [EmployeeTaskController::class, 'addComment'])->name('add-comment');
            
            // API pour les données des tâches
            Route::get('/api/queue', [EmployeeTaskController::class, 'getTaskQueue'])->name('api.queue');
            Route::get('/api/stats', [EmployeeTaskController::class, 'getTaskStats'])->name('api.stats');
        });
        
        // ========================================
        // PERFORMANCE ET OBJECTIFS
        // ========================================
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/', [EmployeeDashboardController::class, 'performance'])->name('index');
            Route::get('/daily', [EmployeeDashboardController::class, 'dailyPerformance'])->name('daily');
            Route::get('/weekly', [EmployeeDashboardController::class, 'weeklyPerformance'])->name('weekly');
            Route::get('/monthly', [EmployeeDashboardController::class, 'monthlyPerformance'])->name('monthly');
            
            // API pour les données de performance
            Route::get('/api/chart-data', [EmployeeDashboardController::class, 'getPerformanceChartData'])->name('api.chart-data');
            Route::get('/api/targets', [EmployeeDashboardController::class, 'getTargets'])->name('api.targets');
        });
        
        // ========================================
        // PROFIL ET PARAMÈTRES
        // ========================================
        Route::get('profile', [EmployeeDashboardController::class, 'profile'])->name('profile');
        Route::post('profile', [EmployeeDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('settings', [EmployeeDashboardController::class, 'settings'])->name('settings');
        Route::post('settings', [EmployeeDashboardController::class, 'updateSettings'])->name('settings.update');
        
        // ========================================
        // NOTIFICATIONS
        // ========================================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [EmployeeDashboardController::class, 'notifications'])->name('index');
            Route::post('/mark-read/{notification}', [EmployeeDashboardController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [EmployeeDashboardController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::get('/api/unread-count', [EmployeeDashboardController::class, 'getUnreadCount'])->name('api.unread-count');
        });
    });
});