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
    
   
});