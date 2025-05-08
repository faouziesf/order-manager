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
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        Auth::guard('admin')->login($admin, $request->filled('remember'));

        // Si l'administrateur n'est pas actif ou a expiré, rediriger vers la page d'expiration
        if (!$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
            return redirect()->route('admin.expired');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Correction ici : rediriger vers 'login' au lieu de 'admin.login'
        return redirect()->route('login');
    }

    public function showExpiredPage()
    {
        return view('admin.auth.expired');
    }
}