<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\LoginController;
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
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:admins',
        'password' => 'required|string|min:8|confirmed',
        'shop_name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
    ]);

    // Générer un identifiant unique au format 4 chiffres/lettre
    do {
        $numbers = mt_rand(1000, 9999);
        $letter = strtolower(chr(rand(97, 122))); // lettre aléatoire a-z
        $identifier = $numbers . '/' . $letter;
    } while (\App\Models\Admin::where('identifier', $identifier)->exists());

    // Récupérer la période d'essai dans les paramètres
    $trialPeriod = \App\Models\Setting::where('key', 'trial_period')->first();
    $trialDays = (int)($trialPeriod ? $trialPeriod->value : 3); // Converti en entier
    
    // Récupérer le paramètre pour autoriser l'inscription
    $allowRegistration = \App\Models\Setting::where('key', 'allow_registration')->first();
    $isActive = $allowRegistration && (int)$allowRegistration->value ? true : false;
    
    try {
        // Créer l'administrateur
        \App\Models\Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'shop_name' => $request->shop_name,
            'identifier' => $identifier,
            'expiry_date' => now()->addDays($trialDays),
            'phone' => $request->phone,
            'is_active' => $isActive,
            'max_managers' => 1, // Valeur par défaut
            'max_employees' => 2, // Valeur par défaut
        ]);

        return redirect()->route('login')->with('success', 'Votre compte a été créé avec succès. ' . 
            ($isActive ? 'Vous pouvez maintenant vous connecter.' : 'Il sera activé après vérification.'));
    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Erreur lors de la création du compte: ' . $e->getMessage());
    }
})->name('register.submit');

// Routes pour SuperAdmin (accès distinct)
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Routes d'authentification
    Route::get('login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    
    // Routes protégées par middleware
    Route::middleware('super-admin')->group(function () {
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
    
    // Routes protégées par middleware - corrected to use the alias properly
    Route::middleware(['auth:admin', \App\Http\Middleware\CheckAdminExpiry::class])->group(function () {
        Route::get('dashboard', function() {
            return view('admin.dashboard');
        })->name('dashboard');
        
        // Routes pour les produits
        Route::resource('products', ProductController::class);
    });
});

// Routes pour Manager
Route::prefix('manager')->name('manager.')->group(function () {
    // Routes protégées par middleware
    Route::middleware('manager')->group(function () {
        Route::get('dashboard', function() {
            return "Manager Dashboard"; // À remplacer par une vraie vue
        })->name('dashboard');
    });
});

// Routes pour Employee
Route::prefix('employee')->name('employee.')->group(function () {
    // Routes protégées par middleware
    Route::middleware('employee')->group(function () {
        Route::get('dashboard', function() {
            return "Employee Dashboard"; // À remplacer par une vraie vue
        })->name('dashboard');
    });
});