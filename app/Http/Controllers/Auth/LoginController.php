<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return redirect()->route('confirmi.home');
    }

    public function login(Request $request)
    {
        return app(\App\Http\Controllers\Confirmi\AuthController::class)->login($request);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('confirmi.home');
    }

    public function showExpiredPage()
    {
        return view('auth.expired');
    }
}
