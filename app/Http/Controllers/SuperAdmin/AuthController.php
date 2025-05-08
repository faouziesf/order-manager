<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('super-admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Ajout de logs pour le débogage
        Log::info('SuperAdmin login attempt', ['email' => $request->email]);

        $superAdmin = SuperAdmin::where('email', $request->email)->first();

        if (!$superAdmin) {
            Log::info('SuperAdmin not found');
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        if (!Hash::check($request->password, $superAdmin->password)) {
            Log::info('Invalid password');
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        Log::info('SuperAdmin authenticated successfully', ['id' => $superAdmin->id]);
        Auth::guard('super-admin')->login($superAdmin, $request->filled('remember'));
        
        Log::info('Auth::guard(super-admin)->check() after login = ' . (Auth::guard('super-admin')->check() ? 'true' : 'false'));

        return redirect()->route('super-admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('super-admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }
}