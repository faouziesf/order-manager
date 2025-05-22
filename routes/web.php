<?php
use App\Http\Controllers\Admin\ProcessController;
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
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

// Routes pour l'importation
Route::get('admin/import', [App\Http\Controllers\Admin\ImportController::class, 'index'])->name('admin.import.index');
Route::post('admin/import/csv', [App\Http\Controllers\Admin\ImportController::class, 'importCsv'])->name('admin.import.csv');
Route::post('admin/import/xml', [App\Http\Controllers\Admin\ImportController::class, 'importXml'])->name('admin.import.xml');

// Routes pour WooCommerce
Route::get('admin/woocommerce', [App\Http\Controllers\Admin\WooCommerceController::class, 'index'])->name('admin.woocommerce.index');
Route::post('admin/woocommerce', [App\Http\Controllers\Admin\WooCommerceController::class, 'store'])->name('admin.woocommerce.store');
Route::get('admin/woocommerce/sync', [App\Http\Controllers\Admin\WooCommerceController::class, 'sync'])->name('admin.woocommerce.sync');

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
    
    // Routes protégées par middleware
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
        
        // Routes pour marquer les produits comme examinés
        Route::post('products/{product}/mark-reviewed', [ProductController::class, 'markAsReviewed'])
            ->name('products.mark-reviewed');
        Route::post('products/mark-all-reviewed', [ProductController::class, 'markAllAsReviewed'])
            ->name('products.mark-all-reviewed');
        Route::get('products/review', [ProductController::class, 'reviewNewProducts'])
            ->name('products.review');
        
        // Gestion des commandes
        Route::resource('orders', OrderController::class);
        Route::get('orders/{order}/history', [OrderController::class, 'showHistory'])->name('orders.history');
        Route::post('orders/{order}/record-attempt', [OrderController::class, 'recordAttempt'])->name('orders.recordAttempt');
        Route::get('orders/{order}/attempts', [OrderController::class, 'getAttempts'])->name('orders.attempts');
        
        // Routes AJAX pour les données géographiques et produits
        Route::get('get-regions', [OrderController::class, 'getRegions'])->name('orders.getRegions');
        Route::get('get-cities', [OrderController::class, 'getCities'])->name('orders.getCities');
        Route::get('search-products', [OrderController::class, 'searchProducts'])->name('orders.searchProducts');
        
        // Routes pour les paramètres
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'store'])->name('settings.store');
        
        // ===========================================
        // ROUTES POUR LE TRAITEMENT DES COMMANDES
        // ===========================================
        
        // Interface principale de traitement
        Route::get('process', [ProcessController::class, 'interface'])->name('process.interface');
        
        // Routes API pour le traitement des commandes
        Route::get('process/test', [ProcessController::class, 'test'])->name('process.test');
        Route::get('process/counts', [ProcessController::class, 'getCounts'])->name('process.getCounts');
        Route::get('process/{queue}', [ProcessController::class, 'getQueue'])
            ->where('queue', 'standard|dated|old')
            ->name('process.getQueue');
        Route::post('process/action/{order}', [ProcessController::class, 'processAction'])->name('process.action');
        
        // Routes pour les files d'attente individuelles (si nécessaire pour des vues séparées)
        Route::get('process/standard', [ProcessController::class, 'standardQueue'])->name('process.standard');
        Route::get('process/dated', [ProcessController::class, 'datedQueue'])->name('process.dated');
        Route::get('process/old', [ProcessController::class, 'oldQueue'])->name('process.old');
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