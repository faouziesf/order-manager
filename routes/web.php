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

// Route pour l'interface unifiée de traitement (avec onglets)
Route::get('admin/process', [App\Http\Controllers\Admin\ProcessController::class, 'interface'])->name('admin.process.interface');

// Routes pour charger la prochaine commande d'une file
Route::get('admin/process/{queue}', [App\Http\Controllers\Admin\ProcessController::class, 'getNextOrder'])->name('admin.process.getNext');

// Route pour charger le formulaire d'une commande spécifique
Route::get('admin/process/{queue}/{order}/form', [App\Http\Controllers\Admin\ProcessController::class, 'getOrderForm'])->name('admin.process.getForm');

// Route pour l'interface unifiée de traitement
Route::get('admin/process', [App\Http\Controllers\Admin\ProcessController::class, 'interface'])->name('admin.process.interface');

// Routes AJAX pour charger les commandes
Route::get('admin/process/{queue}/next', [App\Http\Controllers\Admin\ProcessController::class, 'getNextOrderJson'])
    ->where('queue', 'standard|dated|old')
    ->name('admin.process.queue.next');

// Routes pour les paramètres
Route::get('admin/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
Route::post('admin/settings', [App\Http\Controllers\Admin\SettingController::class, 'store'])->name('admin.settings.store');

// Routes pour le traitement des commandes
Route::get('admin/process/standard', [App\Http\Controllers\Admin\ProcessController::class, 'standardQueue'])->name('admin.process.standard');
Route::get('admin/process/dated', [App\Http\Controllers\Admin\ProcessController::class, 'datedQueue'])->name('admin.process.dated');
Route::get('admin/process/old', [App\Http\Controllers\Admin\ProcessController::class, 'oldQueue'])->name('admin.process.old');
Route::post('admin/process/{order}/action', [App\Http\Controllers\Admin\ProcessController::class, 'processAction'])->name('admin.process.action');



// Routes pour WooCommerce
Route::get('admin/woocommerce', [App\Http\Controllers\Admin\WooCommerceController::class, 'index'])->name('admin.woocommerce.index');
Route::post('admin/woocommerce', [App\Http\Controllers\Admin\WooCommerceController::class, 'store'])->name('admin.woocommerce.store');
Route::get('admin/woocommerce/sync', [App\Http\Controllers\Admin\WooCommerceController::class, 'sync'])->name('admin.woocommerce.sync');



// Routes pour marquer les produits comme examinés
Route::post('admin/products/{product}/mark-reviewed', [App\Http\Controllers\Admin\ProductController::class, 'markAsReviewed'])
    ->name('admin.products.mark-reviewed')
    ->middleware('auth:admin');

 
Route::post('admin/products/mark-all-reviewed', [App\Http\Controllers\Admin\ProductController::class, 'markAllAsReviewed'])
    ->name('admin.products.mark-all-reviewed')
    ->middleware('auth:admin');

// Route pour examiner les nouveaux produits
Route::get('admin/products/review', [App\Http\Controllers\Admin\ProductController::class, 'reviewNewProducts'])
    ->name('admin.products.review')
    ->middleware('auth:admin');


Route::get('admin/products/review', [App\Http\Controllers\Admin\ProductController::class, 'reviewNewProducts'])->name('admin.products.review');



// Routes d'authentification unifiées pour Admin/Manager/Employee
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.submit');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('expired', [LoginController::class, 'showExpiredPage'])->name('expired');

// Routes pour l'inscription
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

// Routes pour l'importation
Route::get('admin/import', [App\Http\Controllers\Admin\ImportController::class, 'index'])->name('admin.import.index');
Route::post('admin/import/csv', [App\Http\Controllers\Admin\ImportController::class, 'importCsv'])->name('admin.import.csv');
Route::post('admin/import/xml', [App\Http\Controllers\Admin\ImportController::class, 'importXml'])->name('admin.import.xml');

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