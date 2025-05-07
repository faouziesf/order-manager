<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $superAdmin = SuperAdmin::where('email', $request->email)->first();

        if (!$superAdmin || !Hash::check($request->password, $superAdmin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        Auth::guard('super-admin')->login($superAdmin, $request->filled('remember'));

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