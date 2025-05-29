<?php

use App\Http\Controllers\Admin\ProcessController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\WooCommerceController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingController;
use Illuminate\Support\Facades\Route;

// Route d'accueil
Route::get('/', function () {
    return redirect()->route('login');
});

// ========================================
// ROUTES D'AUTHENTIFICATION UNIFIÉES
// ========================================
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.submit');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('expired', [LoginController::class, 'showExpiredPage'])->name('expired');

// Routes pour l'inscription
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

// ========================================
// ROUTES SUPER ADMIN
// ========================================
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Authentification
    Route::get('login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    
    // Routes protégées
    Route::middleware('auth:super-admin')->group(function () {
        Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Gestion des admins
        Route::resource('admins', AdminController::class);
        Route::patch('admins/{admin}/toggle-active', [AdminController::class, 'toggleActive'])
            ->name('admins.toggle-active');
        
        // Paramètres globaux
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// ========================================
// ROUTES ADMIN
// ========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentification Admin
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('expired', [AdminAuthController::class, 'showExpiredPage'])->name('expired');
    
    // Routes protégées Admin
    Route::middleware('auth:admin')->group(function () {
        // Dashboard
        Route::get('dashboard', function() {
            $admin = auth('admin')->user();
            if (!$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
                return redirect()->route('admin.expired');
            }
            return view('admin.dashboard');
        })->name('dashboard');
        
        // ========================================
        // GESTION DES PRODUITS
        // ========================================
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/mark-reviewed', [ProductController::class, 'markAsReviewed'])
            ->name('products.mark-reviewed');
        Route::post('products/mark-all-reviewed', [ProductController::class, 'markAllAsReviewed'])
            ->name('products.mark-all-reviewed');
        Route::get('products/review', [ProductController::class, 'reviewNewProducts'])
            ->name('products.review');
        Route::post('products/bulk-activate', [ProductController::class, 'bulkActivate'])
            ->name('products.bulk-activate');
        Route::post('products/bulk-deactivate', [ProductController::class, 'bulkDeactivate'])
            ->name('products.bulk-deactivate');
        Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])
            ->name('products.bulk-delete');
        
        // ========================================
        // GESTION DES COMMANDES
        // ========================================
        
        // Routes spéciales AVANT le resource
        Route::post('orders/bulk-assign', [OrderController::class, 'bulkAssign'])
            ->name('orders.bulk-assign');
        Route::get('orders/{order}/history', [OrderController::class, 'showHistory'])
            ->name('orders.history');
        Route::get('orders/{order}/history-modal', [OrderController::class, 'getHistory'])
            ->name('orders.history-modal');
        Route::post('orders/{order}/record-attempt', [OrderController::class, 'recordAttempt'])
            ->name('orders.recordAttempt');
        Route::post('orders/{order}/quick-attempt', [OrderController::class, 'quickAttempt'])
            ->name('orders.quick-attempt');
        Route::get('orders/get-regions', [OrderController::class, 'getRegions'])
            ->name('orders.getRegions');
        Route::get('orders/get-cities', [OrderController::class, 'getCities'])
            ->name('orders.getCities');
        Route::get('orders/search-products', [OrderController::class, 'searchProducts'])
            ->name('orders.searchProducts');
        
        // CRUD de base
        Route::resource('orders', OrderController::class);
        
        // ========================================
        // GESTION DES UTILISATEURS
        // ========================================
        Route::resource('managers', ManagerController::class);
        Route::patch('managers/{manager}/toggle-active', [ManagerController::class, 'toggleActive'])
            ->name('managers.toggle-active');
        Route::get('api/managers', [ManagerController::class, 'getManagersForAdmin'])
            ->name('api.managers');

        Route::resource('employees', EmployeeController::class);
        Route::patch('employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])
            ->name('employees.toggle-active');

        Route::get('login-history', [LoginHistoryController::class, 'index'])
            ->name('login-history.index');
        Route::get('login-history/{user_type}/{user_id}', [LoginHistoryController::class, 'show'])
            ->name('login-history.show');
        
        // ========================================
        // IMPORTATION ET INTÉGRATIONS
        // ========================================
        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import/csv', [ImportController::class, 'importCsv'])->name('import.csv');
        Route::post('import/xml', [ImportController::class, 'importXml'])->name('import.xml');

        Route::get('woocommerce', [WooCommerceController::class, 'index'])
            ->name('woocommerce.index');
        Route::post('woocommerce', [WooCommerceController::class, 'store'])
            ->name('woocommerce.store');
        Route::get('woocommerce/sync', [WooCommerceController::class, 'sync'])
            ->name('woocommerce.sync');
        
        // ========================================
        // TRAITEMENT DES COMMANDES
        // ========================================
        Route::get('process', [ProcessController::class, 'interface'])
            ->name('process.interface');
        Route::get('process/test', [ProcessController::class, 'test'])
            ->name('process.test');
        Route::get('process/counts', [ProcessController::class, 'getCounts'])
            ->name('process.getCounts');
        Route::get('process/{queue}', [ProcessController::class, 'getQueue'])
            ->where('queue', 'standard|dated|old')
            ->name('process.getQueue');
        Route::post('process/action/{order}', [ProcessController::class, 'processAction'])
            ->name('process.action');
        Route::get('process/standard', [ProcessController::class, 'standardQueue'])
            ->name('process.standard');
        Route::get('process/dated', [ProcessController::class, 'datedQueue'])
            ->name('process.dated');
        Route::get('process/old', [ProcessController::class, 'oldQueue'])
            ->name('process.old');
        
        // ========================================
        // PARAMÈTRES
        // ========================================
        Route::get('settings', [AdminSettingController::class, 'index'])
            ->name('settings.index');
        Route::post('settings', [AdminSettingController::class, 'store'])
            ->name('settings.store');
    });
});

// ========================================
// ROUTES MANAGER
// ========================================
Route::prefix('manager')->name('manager.')->group(function () {
    Route::middleware('auth:manager')->group(function () {
        Route::get('dashboard', function() {
            return "Manager Dashboard";
        })->name('dashboard');
    });
});

// ========================================
// ROUTES EMPLOYEE
// ========================================
Route::prefix('employee')->name('employee.')->group(function () {
    Route::middleware('auth:employee')->group(function () {
        Route::get('dashboard', function() {
            return "Employee Dashboard";
        })->name('dashboard');
    });
});