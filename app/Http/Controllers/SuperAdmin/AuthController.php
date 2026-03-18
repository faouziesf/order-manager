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
        return redirect()->route('confirmi.home');
    }

    public function login(Request $request)
    {
        // Rediriger vers le contrôleur de connexion unifié
        return app(\App\Http\Controllers\Confirmi\AuthController::class)->login($request);
    }

    public function logout(Request $request)
    {
        Auth::guard('super-admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('confirmi.home');
    }
}