<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckAdminExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est connecté en tant qu'admin
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            // Vérifier si l'admin est désactivé
            if (!$admin->is_active) {
                Log::info("Admin désactivé tenté de se connecter", [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                Auth::guard('admin')->logout();
                
                return redirect()->route('admin.login')
                    ->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur système.');
            }
            
            // Vérifier la date d'expiration
            if ($admin->expiry_date && Carbon::parse($admin->expiry_date)->isPast()) {
                Log::info("Admin expiré tenté d'accéder au système", [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'expiry_date' => $admin->expiry_date,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                Auth::guard('admin')->logout();
                
                return redirect()->route('admin.expired')
                    ->with('error', 'Votre abonnement a expiré le ' . 
                        Carbon::parse($admin->expiry_date)->format('d/m/Y') . 
                        '. Veuillez renouveler votre abonnement.');
            }
            
            // Vérifier si l'expiration approche (7 jours)
            if ($admin->expiry_date) {
                $expiryDate = Carbon::parse($admin->expiry_date);
                $daysUntilExpiry = Carbon::now()->diffInDays($expiryDate, false);
                
                if ($daysUntilExpiry <= 7 && $daysUntilExpiry > 0) {
                    // Ajouter une notification d'expiration imminente à la session
                    $request->session()->flash('expiry_warning', [
                        'days' => $daysUntilExpiry,
                        'date' => $expiryDate->format('d/m/Y')
                    ]);
                }
            }
            
            // Mettre à jour la dernière activité (optionnel)
            $admin->touch();
        }
        
        return $next($request);
    }
}

// ============================================================================
// À ajouter dans app/Http/Kernel.php dans le groupe 'web' middleware
// ============================================================================

/*
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\CheckAdminExpiry::class, // <-- AJOUTER ICI
    ],
    // ...
];
*/