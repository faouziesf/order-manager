<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Route d'accueil - Redirection intelligente basée sur l'authentification
Route::get('/', function () {
    // Si l'utilisateur est connecté avec le guard admin (tous les rôles)
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    
    // Si l'utilisateur est connecté avec le guard confirmi
    if (Auth::guard('confirmi')->check()) {
        $user = Auth::guard('confirmi')->user();
        if ($user->role === 'commercial') {
            return redirect()->route('confirmi.commercial.dashboard');
        } else {
            return redirect()->route('confirmi.employee.dashboard');
        }
    }
    
    // Si personne n'est connecté, rediriger vers la page d'accueil Confirmi
    return redirect()->route('confirmi.home');
});

// Route Offline - Affichée quand pas de connexion internet
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// ========================================
// ROUTES D'AUTHENTIFICATION COMMUNES
// ========================================
require __DIR__.'/auth.php';

// ========================================
// ROUTES SUPER ADMIN
// ========================================
require __DIR__.'/superadmin.php';

// ========================================
// ROUTES ADMIN (Utilisées par tous: admin, manager, employee)
// ========================================
require __DIR__.'/admin.php';

// ========================================
// ROUTES CONFIRMI (Commerciaux & Employés de la plateforme)
// ========================================
require __DIR__.'/confirmi.php';