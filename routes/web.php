<?php

use Illuminate\Support\Facades\Route;

// Route d'accueil
Route::get('/', function () {
    return redirect()->route('login');
});

// ========================================
// ROUTES D'AUTHENTIFICATION COMMUNES
// ========================================
require __DIR__.'/auth.php';

// ========================================
// ROUTES SUPER ADMIN
// ========================================
require __DIR__.'/superadmin.php';

// ========================================
// ROUTES ADMIN
// ========================================
require __DIR__.'/admin.php';

// ========================================
// ROUTES MANAGER
// ========================================
require __DIR__.'/manager.php';

// ========================================
// ROUTES EMPLOYEE
// ========================================
require __DIR__.'/employee.php';