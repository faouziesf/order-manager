<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureConfirmiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('confirmi')->check()) {
            return redirect()->route('confirmi.home', ['login' => 1]);
        }

        $user = Auth::guard('confirmi')->user();
        if (!$user->is_active) {
            Auth::guard('confirmi')->logout();
            return redirect()->route('confirmi.home')
                ->with('error', 'Votre compte a été désactivé.');
        }

        return $next($request);
    }
}
