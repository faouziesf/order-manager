<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureConfirmiCommercial
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('confirmi')->check()) {
            return redirect()->route('confirmi.home', ['login' => 1]);
        }

        $user = Auth::guard('confirmi')->user();
        if (!$user->isCommercial()) {
            return redirect()->route('confirmi.dashboard')
                ->with('error', 'Accès réservé aux commerciaux.');
        }

        return $next($request);
    }
}
