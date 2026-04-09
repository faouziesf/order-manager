<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Routes de test CSRF/Session (à supprimer après debug)
Route::get('/test-session', function () {
    return view('test-session');
});
Route::post('/test-csrf', function () {
    return redirect('/test-session')->with('test_result', 'success');
});

// Route d'accueil - Redirection intelligente basée sur l'authentification
Route::get('/', function () {
    // Si l'utilisateur est connecté avec le guard admin (tous les rôles)
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    
    // Si l'utilisateur est connecté avec le guard confirmi
    if (Auth::guard('confirmi')->check()) {
        return redirect()->route('confirmi.dashboard');
    }
    
    // Si l'utilisateur est connecté avec le guard super-admin
    if (Auth::guard('super-admin')->check()) {
        return redirect()->route('super-admin.dashboard');
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