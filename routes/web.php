<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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
// INCLUSION DES ROUTES MODULAIRES
// ========================================

// Routes Super Admin
require_once __DIR__ . '/super-admin.php';

// Routes Admin
require_once __DIR__ . '/admin.php';

// Routes Manager
require_once __DIR__ . '/manager.php';

// Routes Employee
require_once __DIR__ . '/employee.php';