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
});