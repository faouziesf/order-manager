<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Confirmi\AuthController;
use App\Http\Controllers\Confirmi\DashboardController;
use App\Http\Controllers\Confirmi\OrderManagementController;
use App\Http\Controllers\Confirmi\EmployeeOrderController;
use App\Http\Controllers\Confirmi\RequestController;
use App\Http\Controllers\Confirmi\EmployeeManagementController;
use App\Http\Controllers\Confirmi\ConfirmiProcessController;
use App\Http\Controllers\Confirmi\ConfirmiEmballageController;
use App\Models\Admin;

// ========================================
// CONFIRMI AUTH ROUTES
// ========================================
Route::prefix('confirmi')->name('confirmi.')->group(function () {

    Route::get('/', [AuthController::class, 'home'])->name('home');
    Route::get('/services',  fn() => view('confirmi.services'))->name('services');
    Route::get('/contact',   fn() => view('confirmi.contact'))->name('contact');
    Route::get('/register',  fn() => redirect()->route('register'))->name('register');
    Route::post('/contact', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'name'    => 'required|min:2|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required',
            'message' => 'required|min:10|max:2000',
        ]);
        return back()->with('contact_sent', true);
    })->name('contact.send');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

    // ========================================
    // ROUTES PROTÉGÉES CONFIRMI
    // ========================================
    Route::middleware(['confirmi'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/live-stats', [DashboardController::class, 'liveStats'])->name('dashboard.live-stats');

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
            Route::post('/orders/bulk-reject', [OrderManagementController::class, 'bulkReject'])->name('orders.bulk-reject');
            Route::post('/orders/bulk-reassign', [OrderManagementController::class, 'bulkReassign'])->name('orders.bulk-reassign');

            // Liste des admins Confirmi + auto-assignation
            Route::get('/admins', [OrderManagementController::class, 'adminsList'])->name('admins');
            Route::post('/admins/{admin}/auto-assign', [OrderManagementController::class, 'setAutoAssign'])->name('admins.auto-assign');
            Route::post('/admins/{admin}/toggle-emballage', [OrderManagementController::class, 'toggleEmballage'])->name('admins.toggle-emballage');
            Route::post('/admins/{admin}/default-agent', [OrderManagementController::class, 'setDefaultAgent'])->name('admins.default-agent');

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

            Route::get('/dashboard', [EmployeeOrderController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [EmployeeOrderController::class, 'profile'])->name('profile');
            Route::get('/orders', [EmployeeOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/history', [EmployeeOrderController::class, 'history'])->name('orders.history');
            Route::get('/orders/search', [EmployeeOrderController::class, 'search'])->name('orders.search');
            Route::get('/orders/{assignment}', [EmployeeOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{assignment}/start', [EmployeeOrderController::class, 'startProcessing'])->name('orders.start');
            Route::post('/orders/{assignment}/attempt', [EmployeeOrderController::class, 'recordAttempt'])->name('orders.attempt');

            // Produits admin (pour ajouter aux commandes assignées)
            Route::get('/products', [EmployeeOrderController::class, 'products'])->name('products.index');
            Route::get('/products/api', [EmployeeOrderController::class, 'productsApi'])->name('products.api');
            Route::post('/orders/{assignment}/add-item', [EmployeeOrderController::class, 'addItem'])->name('orders.add-item');
            Route::delete('/orders/{assignment}/remove-item/{item}', [EmployeeOrderController::class, 'removeItem'])->name('orders.remove-item');

            // Interface de traitement (identique à l'admin)
            Route::get('/process', [ConfirmiProcessController::class, 'interface'])->name('process.interface');
            Route::get('/process/counts', [ConfirmiProcessController::class, 'getCounts'])->name('process.counts');
            Route::get('/process/api/{queue}', [ConfirmiProcessController::class, 'getQueueApi'])
                ->where('queue', 'standard|dated|old|restock')
                ->name('process.api.queue');
            Route::post('/process/action/{order}', [ConfirmiProcessController::class, 'processAction'])->name('process.action');
            Route::get('/process/products/search', [ConfirmiProcessController::class, 'searchProducts'])->name('process.products.search');
            Route::get('/process/regions', [ConfirmiProcessController::class, 'getRegions'])->name('process.regions');
            Route::get('/process/cities', [ConfirmiProcessController::class, 'getCities'])->name('process.cities');
        });

        // ========================================
        // ROUTES AGENT EMBALLAGE (réception, emballage, expédition)
        // ========================================
        Route::prefix('agent')->name('agent.')->group(function () {
            Route::get('/emballage', [ConfirmiEmballageController::class, 'interface'])->name('emballage.interface');
            Route::get('/emballage/tasks/{tab}', [ConfirmiEmballageController::class, 'getTasks'])
                ->where('tab', 'pending|received|packed|shipped')
                ->name('emballage.tasks');
            Route::get('/emballage/counts', [ConfirmiEmballageController::class, 'getCounts'])->name('emballage.counts');
            Route::post('/emballage/{task}/receive', [ConfirmiEmballageController::class, 'markReceived'])->name('emballage.receive');
            Route::post('/emballage/{task}/pack', [ConfirmiEmballageController::class, 'markPacked'])->name('emballage.pack');
            Route::post('/emballage/{task}/create-bl', [ConfirmiEmballageController::class, 'createBL'])->name('emballage.create-bl');
            Route::get('/emballage/{task}/print-bl', [ConfirmiEmballageController::class, 'printBL'])->name('emballage.print-bl');
            Route::post('/emballage/bulk-receive', [ConfirmiEmballageController::class, 'bulkReceive'])->name('emballage.bulk-receive');
        });
    });
});
