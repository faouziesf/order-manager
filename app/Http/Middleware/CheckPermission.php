<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Accepts one or more permission keys (comma-separated).
     * Admins always pass. Managers/Employees must have at least one of the listed permissions.
     *
     * Usage in routes:  ->middleware('permission:can_manage_products')
     *                   ->middleware('permission:can_manage_orders,can_process_orders')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Full admins bypass all permission checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if the user has at least one of the required permissions
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Accès refusé — permission insuffisante.'], 403);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'Accès refusé — vous n\'avez pas la permission requise.');
    }
}
