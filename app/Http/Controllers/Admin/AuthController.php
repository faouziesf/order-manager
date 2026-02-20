<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return redirect()->route('confirmi.home', ['login' => 1]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        // Vérification des identifiants
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        // VERIFICATION CRITIQUE : Bloquer AVANT la connexion si le compte est inactif ou expiré
        if (!$this->isAccountValid($admin)) {
            // Ne pas connecter l'utilisateur, rediriger directement
            return redirect()->route('admin.expired')->with([
                'expired_reason' => $this->getExpiredReason($admin),
                'admin_name' => $admin->name,
                'admin_email' => $admin->email
            ]);
        }

        // Seulement si le compte est valide, procéder à la connexion
        Auth::guard('admin')->login($admin, $request->filled('remember'));

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('confirmi.home');
    }

    public function showExpiredPage()
    {
        return view('admin.auth.expired');
    }

    /**
     * Vérifier si le compte administrateur est valide
     */
    private function isAccountValid(Admin $admin): bool
    {
        // Vérifier si le compte est actif
        if (!$admin->is_active) {
            return false;
        }

        // Vérifier si le compte n'est pas expiré
        if ($admin->expiry_date && $admin->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir la raison de l'expiration
     */
    private function getExpiredReason(Admin $admin): string
    {
        if (!$admin->is_active) {
            return 'inactive';
        }

        if ($admin->expiry_date && $admin->expiry_date->isPast()) {
            return 'expired';
        }

        return 'unknown';
    }
}