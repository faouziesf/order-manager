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

        // Récupérer l'utilisateur selon le type
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

        // Vérification des identifiants
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        // Vérifications spécifiques selon le type d'utilisateur
        $validationResult = $this->validateUserAccount($user, $guard);
        
        if (!$validationResult['valid']) {
            return redirect()->route('expired')->with([
                'expired_reason' => $validationResult['reason'],
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_type' => $guard
            ]);
        }

        // Connecter l'utilisateur avec le garde approprié
        Auth::guard($guard)->login($user, $request->filled('remember'));

        // Rediriger vers le tableau de bord approprié
        return $this->redirectToDashboard($guard);
    }

    public function logout(Request $request)
    {
        // Détecter et déconnecter le bon garde
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

    /**
     * Valider le compte utilisateur selon son type
     */
    private function validateUserAccount($user, string $userType): array
    {
        switch ($userType) {
            case 'admin':
                return $this->validateAdminAccount($user);
            case 'manager':
                return $this->validateManagerAccount($user);
            case 'employee':
                return $this->validateEmployeeAccount($user);
            default:
                return ['valid' => false, 'reason' => 'invalid_type'];
        }
    }

    /**
     * Valider un compte administrateur
     */
    private function validateAdminAccount(Admin $admin): array
    {
        if (!$admin->is_active) {
            return ['valid' => false, 'reason' => 'inactive'];
        }

        if ($admin->expiry_date && $admin->expiry_date->isPast()) {
            return ['valid' => false, 'reason' => 'expired'];
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Valider un compte manager
     */
    private function validateManagerAccount(Manager $manager): array
    {
        // Vérifier si le manager est actif
        if (!$manager->is_active) {
            return ['valid' => false, 'reason' => 'inactive'];
        }

        // Vérifier si l'admin parent est toujours valide
        $admin = $manager->admin;
        if (!$admin || !$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
            return ['valid' => false, 'reason' => 'admin_expired'];
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Valider un compte employé
     */
    private function validateEmployeeAccount(Employee $employee): array
    {
        // Vérifier si l'employé est actif
        if (!$employee->is_active) {
            return ['valid' => false, 'reason' => 'inactive'];
        }

        // Vérifier si l'admin parent est toujours valide
        $admin = $employee->admin;
        if (!$admin || !$admin->is_active || ($admin->expiry_date && $admin->expiry_date->isPast())) {
            return ['valid' => false, 'reason' => 'admin_expired'];
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Rediriger vers le bon tableau de bord
     */
    private function redirectToDashboard(string $guard)
    {
        switch ($guard) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'manager':
                return redirect()->route('manager.dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
            default:
                return redirect()->route('login');
        }
    }
}