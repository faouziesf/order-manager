<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Manager;
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
            'user_type' => 'required|in:admin,manager,employee',
        ]);

        $user = null;
        $guard = $request->user_type;

        // Vérifier les identifiants en fonction du type d'utilisateur
        switch ($guard) {
            case 'admin':
                $user = Admin::where('email', $request->email)->first();
                break;
            case 'manager':
                $user = Manager::where('email', $request->email)->first();
                break;
            case 'employee':
                $user = Employee::where('email', $request->email)->first();
                break;
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        // Connecter l'utilisateur avec le garde approprié
        Auth::guard($guard)->login($user, $request->filled('remember'));

        // Vérifications supplémentaires pour les admins
        if ($guard === 'admin') {
            // Si l'administrateur n'est pas actif ou a expiré, rediriger vers la page d'expiration
            if (!$user->is_active || ($user->expiry_date && $user->expiry_date->isPast())) {
                return redirect()->route('expired');
            }
        }

        // Rediriger vers le tableau de bord approprié
        switch ($guard) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'manager':
                return redirect()->route('manager.dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
        }
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('manager')->check()) {
            Auth::guard('manager')->logout();
        } elseif (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showExpiredPage()
    {
        return view('auth.expired');
    }
}