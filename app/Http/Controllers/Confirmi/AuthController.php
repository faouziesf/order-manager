<?php

namespace App\Http\Controllers\Confirmi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function home()
    {
        if (Auth::guard('confirmi')->check()) {
            return redirect()->route('confirmi.dashboard');
        }
        if (Auth::guard('super-admin')->check()) {
            return redirect()->route('super-admin.dashboard');
        }
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            return $admin->confirmi_status === 'active'
                ? redirect()->route('admin.confirmi.index')
                : redirect()->route('admin.dashboard');
        }

        return view('confirmi.home');
    }

    public function showLoginForm()
    {
        return redirect()->route('confirmi.home', ['login' => 1]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        // ── 1. Confirmi users (commercial / employee) ──────────────────────
        if (Auth::guard('confirmi')->attempt($credentials, $remember)) {
            $user = Auth::guard('confirmi')->user();

            if (!$user->is_active) {
                Auth::guard('confirmi')->logout();
                return back()->with('error', 'Votre compte Confirmi a été désactivé.')->withInput();
            }

            $user->update([
                'last_login_at' => now(),
                'ip_address'    => $request->ip(),
            ]);

            $request->session()->regenerate();
            return redirect()->route('confirmi.dashboard');
        }

        // ── 2. Super Admin ─────────────────────────────────────────────────
        if (Auth::guard('super-admin')->attempt($credentials, $remember)) {
            $superAdmin = Auth::guard('super-admin')->user();

            if (isset($superAdmin->is_active) && !$superAdmin->is_active) {
                Auth::guard('super-admin')->logout();
                return back()->with('error', 'Compte super-admin désactivé.')->withInput();
            }

            $request->session()->regenerate();
            return redirect()->route('super-admin.dashboard');
        }

        // ── 3. Admin (all admins allowed; redirect based on Confirmi status) ────
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();

            if (!$admin->is_active) {
                Auth::guard('admin')->logout();
                return back()->with('error', 'Votre compte est désactivé.')->withInput();
            }

            $request->session()->regenerate();

            return $admin->confirmi_status === 'active'
                ? redirect()->route('admin.confirmi.index')
                : redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Identifiants incorrects.')->withInput();
    }

    public function logout(Request $request)
    {
        // Log out from whichever guard is active
        foreach (['confirmi', 'super-admin', 'admin'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
                break;
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('confirmi.home');
    }
}
