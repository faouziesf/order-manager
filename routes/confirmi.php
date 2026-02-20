<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Confirmi\AuthController;
use App\Http\Controllers\Confirmi\DashboardController;
use App\Http\Controllers\Confirmi\OrderManagementController;
use App\Http\Controllers\Confirmi\EmployeeOrderController;
use App\Http\Controllers\Confirmi\RequestController;
use App\Http\Controllers\Confirmi\EmployeeManagementController;

// ========================================
// CONFIRMI AUTH ROUTES
// ========================================
Route::prefix('confirmi')->name('confirmi.')->group(function () {

    Route::get('/', [AuthController::class, 'home'])->name('home');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // ========================================
    // ROUTES PROTÉGÉES CONFIRMI
    // ========================================
    Route::middleware(['confirmi'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ========================================
        // ROUTES COMMERCIAL (gestion des commandes, assignation, demandes)
        // ========================================
        Route::middleware(['confirmi.commercial'])->prefix('commercial')->name('commercial.')->group(function () {

            // Gestion des commandes
            Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
            Route::get('/orders/pending', [OrderManagementController::class, 'pending'])->name('orders.pending');
            Route::get('/orders/{assignment}', [OrderManagementController::class, 'show'])->name('orders.show');
            Route::post('/orders/{assignment}/assign', [OrderManagementController::class, 'assign'])->name('orders.assign');
            Route::post('/orders/{assignment}/mark-delivered', [OrderManagementController::class, 'markDelivered'])->name('orders.mark-delivered');
            Route::post('/orders/bulk-assign', [OrderManagementController::class, 'bulkAssign'])->name('orders.bulk-assign');

            // Liste des admins Confirmi
            Route::get('/admins', [OrderManagementController::class, 'adminsList'])->name('admins');

            // Gestion des employés Confirmi
            Route::get('/employees', [EmployeeManagementController::class, 'index'])->name('employees.index');
            Route::get('/employees/create', [EmployeeManagementController::class, 'create'])->name('employees.create');
            Route::post('/employees', [EmployeeManagementController::class, 'store'])->name('employees.store');
            Route::get('/employees/{employee}/edit', [EmployeeManagementController::class, 'edit'])->name('employees.edit');
            Route::put('/employees/{employee}', [EmployeeManagementController::class, 'update'])->name('employees.update');
            Route::post('/employees/{employee}/toggle', [EmployeeManagementController::class, 'toggleActive'])->name('employees.toggle');
            Route::delete('/employees/{employee}', [EmployeeManagementController::class, 'destroy'])->name('employees.destroy');

            // Gestion des demandes d'activation
            Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
            Route::get('/requests/{confirmiRequest}', [RequestController::class, 'show'])->name('requests.show');
            Route::post('/requests/{confirmiRequest}/approve', [RequestController::class, 'approve'])->name('requests.approve');
            Route::post('/requests/{confirmiRequest}/reject', [RequestController::class, 'reject'])->name('requests.reject');
        });

        // ========================================
        // ROUTES EMPLOYÉ CONFIRMI (traitement des commandes assignées)
        // ========================================
        Route::prefix('employee')->name('employee.')->group(function () {

            Route::get('/orders', [EmployeeOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/history', [EmployeeOrderController::class, 'history'])->name('orders.history');
            Route::get('/orders/queue', [EmployeeOrderController::class, 'processQueue'])->name('orders.queue');
            Route::get('/orders/{assignment}/process', [EmployeeOrderController::class, 'process'])->name('orders.process');
            Route::get('/orders/{assignment}', [EmployeeOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{assignment}/start', [EmployeeOrderController::class, 'startProcessing'])->name('orders.start');
            Route::post('/orders/{assignment}/attempt', [EmployeeOrderController::class, 'recordAttempt'])->name('orders.attempt');
        });
    });
});
