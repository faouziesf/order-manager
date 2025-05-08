<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route d'accueil
Route::get('/', function () {
    return redirect()->route('login');
});

// Routes d'authentification unifiées pour Admin/Manager/Employee
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.submit');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('expired', [LoginController::class, 'showExpiredPage'])->name('expired');

// Routes pour l'inscription
Route::get('register', function () {
    return view('auth.register');
})->name('register');

Route::post('register', function (\Illuminate\Http\Request $request) {
    // Code d'inscription...
})->name('register.submit');

// Routes pour SuperAdmin (accès distinct)
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Routes d'authentification
    Route::get('login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    
    // Routes protégées par middleware
    Route::middleware('auth:super-admin')->group(function () {
        Route::post('logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Routes pour les admins
        Route::resource('admins', AdminController::class);
        Route::patch('admins/{admin}/toggle-active', [AdminController::class, 'toggleActive'])->name('admins.toggle-active');
        
        // Routes pour les paramètres
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Routes pour Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Routes d'authentification
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('expired', [AdminAuthController::class, 'showExpiredPage'])->name('expired');
    
    // Routes protégées par middleware - MODIFICATION ICI
    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', function() {
            // Vérification d'expiration directement ici plutôt que via middleware
            $admin = auth('admin')->user();
            if (!$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
                return redirect()->route('admin.expired');
            }
            
            return view('admin.dashboard');
        })->name('dashboard');
        
        // Routes pour les produits
        Route::resource('products', ProductController::class);
        
        // Gestion des commandes
        Route::resource('orders', OrderController::class);
        Route::get('/orders/{order}/history', [OrderController::class, 'showHistory'])->name('orders.history');
        Route::post('/orders/{order}/record-attempt', [OrderController::class, 'recordAttempt'])->name('orders.recordAttempt');
        Route::get('/get-cities', [OrderController::class, 'getCities'])->name('orders.getCities');
        Route::get('/search-products', [OrderController::class, 'searchProducts'])->name('orders.searchProducts');
    });
});

// Routes pour Manager
Route::prefix('manager')->name('manager.')->group(function () {
    // Routes protégées par middleware
    Route::middleware('auth:manager')->group(function () {
        Route::get('dashboard', function() {
            return "Manager Dashboard"; // À remplacer par une vraie vue
        })->name('dashboard');
    });
});

// Routes pour Employee
Route::prefix('employee')->name('employee.')->group(function () {
    // Routes protégées par middleware
    Route::middleware('auth:employee')->group(function () {
        Route::get('dashboard', function() {
            return "Employee Dashboard"; // À remplacer par une vraie vue
        })->name('dashboard');
    });
});