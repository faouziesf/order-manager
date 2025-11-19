<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Chercher l'utilisateur dans la table Admin (tous les rôles)
        $user = null;
        $guard = 'admin';
        $userType = null;

        // Chercher dans Admin (tous les rôles: admin, manager, employee)
        $user = Admin::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            // Déterminer le type d'utilisateur basé sur le rôle
            $userType = match($user->role) {
                Admin::ROLE_ADMIN => 'admin',
                Admin::ROLE_MANAGER => 'manager',
                Admin::ROLE_EMPLOYEE => 'employee',
                default => 'admin'
            };
        } else {
            $user = null;
        }

        // Vérification des identifiants
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        // Vérifications du compte
        if (!$user->is_active) {
            return redirect()->route('login')->with([
                'error' => 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.',
            ]);
        }

        // Vérification de la date d'expiration (uniquement pour les admins principaux)
        if ($user->role === Admin::ROLE_ADMIN && isset($user->expiry_date) && $user->expiry_date && $user->expiry_date->isPast()) {
            return redirect()->route('login')->with([
                'error' => 'Votre compte a expiré. Veuillez contacter l\'administrateur.',
            ]);
        }

        // Connecter l'utilisateur avec le bon garde
        // Toujours activer "remember me" pour rester connecté indéfiniment
        Auth::guard($guard)->login($user, true);

        // Mettre à jour last_login_at si le champ existe
        try {
            $user->update(['last_login_at' => now()]);
        } catch (\Exception $e) {
            // Si le champ n'existe pas, on ignore
        }

        // Rediriger vers le tableau de bord approprié
        return $this->redirectToDashboard($userType);
    }

    public function logout(Request $request)
    {
        // Déconnecter de l'admin guard (qui gère tous les rôles)
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showExpiredPage()
    {
        return view('auth.expired');
    }

    /**
     * Rediriger vers le bon tableau de bord selon le type d'utilisateur
     * Tous les rôles (admin, manager, employee) utilisent le même dashboard admin
     */
    private function redirectToDashboard(string $userType)
    {
        // Dans le nouveau système multicompte, tous les rôles utilisent le dashboard admin
        return redirect()->route('admin.dashboard');
    }
}
