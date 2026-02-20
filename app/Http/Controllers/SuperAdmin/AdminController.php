<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Exports\AdminsExport;
use App\Http\Controllers\SuperAdmin\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::where('role', Admin::ROLE_ADMIN)
            ->withCount([
                'subAccounts as managers_count' => function($q) {
                    $q->where('role', Admin::ROLE_MANAGER);
                },
                'subAccounts as employees_count' => function($q) {
                    $q->where('role', Admin::ROLE_EMPLOYEE);
                }
            ]);

        // Filtres
        $this->applyFilters($query, $request);

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $admins = $query->paginate(15)->withQueryString();

        // Statistiques pour les filtres
        $stats = $this->getAdminStats();

        return view('super-admin.admins.index', compact('admins', 'stats'));
    }

    public function create()
    {
        return view('super-admin.admins.create');
    }

    public function store(Request $request)
    {
        \Log::info('Tentative de création d\'admin:', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'shop_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'max_managers' => 'nullable|integer|min:0|max:100',
            'max_employees' => 'nullable|integer|min:0|max:1000',
            'expiry_date' => 'nullable|date|after:today',
            'is_active' => 'nullable',
            'subscription_type' => 'nullable|in:trial,basic,premium,enterprise',
        ]);

        $identifier = $this->generateUniqueIdentifier();

        try {
            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'shop_name' => $request->shop_name,
                'identifier' => $identifier,
                'phone' => $request->phone,
                'expiry_date' => $request->expiry_date,
                'is_active' => $request->has('is_active'),
                'max_managers' => (int) ($request->max_managers ?? 1),
                'max_employees' => (int) ($request->max_employees ?? 2),
                'subscription_type' => $request->subscription_type ?? 'trial',
                'created_by_super_admin' => true,
                'total_orders' => 0,
                'total_active_hours' => 0,
                'total_revenue' => 0,
            ]);

            \Log::info('Admin créé avec succès:', ['admin_id' => $admin->id]);

            // Créer une notification
            NotificationController::notifyAdminRegistered($admin);

            // Envoyer un email de bienvenue si demandé
            if ($request->has('send_welcome_email')) {
                $this->sendWelcomeEmail($admin, $request->password);
            }

            return redirect()->route('super-admin.admins.index')
                ->with('success', 'Administrateur créé avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de l\'admin:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'administrateur: ' . $e->getMessage());
        }
    }

    public function show(Admin $admin)
    {
        $totalManagers = $admin->subAccounts()->where('role', Admin::ROLE_MANAGER)->count();
        $totalEmployees = $admin->subAccounts()->where('role', Admin::ROLE_EMPLOYEE)->count();
        
        // Statistiques avancées
        $stats = [
            'total_orders' => $admin->total_orders,
            'total_revenue' => $admin->total_revenue ?? 0,
            'active_hours' => $admin->total_active_hours,
            'avg_orders_per_day' => $this->getAverageOrdersPerDay($admin),
            'last_login' => $admin->last_login_at,
            'registration_date' => $admin->created_at,
            'subscription_status' => $this->getSubscriptionStatus($admin),
            'usage_percentage' => $this->getUsagePercentage($admin)
        ];

        // Activité récente
        $recentActivity = $this->getAdminRecentActivity($admin);

        // Graphiques
        $chartData = $this->getAdminChartData($admin);

        return view('super-admin.admins.show', compact(
            'admin', 
            'totalManagers', 
            'totalEmployees', 
            'stats', 
            'recentActivity',
            'chartData'
        ));
    }

    public function edit(Admin $admin)
    {
        return view('super-admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        // Debug pour voir ce qui est envoyé
        \Log::info('Données reçues pour mise à jour:', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'shop_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'max_managers' => 'nullable|integer|min:0|max:100',
            'max_employees' => 'nullable|integer|min:0|max:1000',
            'expiry_date' => 'nullable|date',
            'subscription_type' => 'nullable|in:trial,basic,premium,enterprise',
            'password' => 'nullable|string|min:8',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'shop_name' => $request->shop_name,
                'phone' => $request->phone,
                'max_managers' => (int) ($request->max_managers ?? 1),
                'max_employees' => (int) ($request->max_employees ?? 2),
                'subscription_type' => $request->subscription_type ?? 'trial',
                'expiry_date' => $request->expiry_date,
                'is_active' => $request->has('is_active') && $request->is_active == '1',
            ];

            // Ajouter le mot de passe seulement s'il est fourni
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            \Log::info('Données à mettre à jour:', $data);
            
            $admin->update($data);
            
            \Log::info('Admin mis à jour avec succès:', ['admin_id' => $admin->id]);

            return redirect()->route('super-admin.admins.index')
                ->with('success', 'Administrateur mis à jour avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de l\'admin:', [
                'admin_id' => $admin->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function destroy(Admin $admin)
    {
        try {
            // Vérifier s'il y a des données liées
            $hasManagers = $admin->subAccounts()->where('role', Admin::ROLE_MANAGER)->count() > 0;
            $hasEmployees = $admin->subAccounts()->where('role', Admin::ROLE_EMPLOYEE)->count() > 0;

            if ($hasManagers || $hasEmployees) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cet administrateur car il a des managers ou employés associés.');
            }

            $admin->delete();

            return redirect()->route('super-admin.admins.index')
                ->with('success', 'Administrateur supprimé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function toggleActive(Admin $admin)
    {
        $admin->update([
            'is_active' => !$admin->is_active
        ]);

        $status = $admin->is_active ? 'activé' : 'désactivé';
        
        // Créer une notification
        if (!$admin->is_active) {
            NotificationController::createAdminNotification(
                'admin_deactivated',
                'Administrateur désactivé',
                "L'administrateur {$admin->name} a été désactivé",
                $admin->id,
                'medium'
            );
        }

        return redirect()->back()
            ->with('success', "Administrateur {$status} avec succès.");
    }

    public function extendSubscription(Request $request, Admin $admin)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:24'
        ]);

        $currentExpiry = $admin->expiry_date ?? now();
        $newExpiry = Carbon::parse($currentExpiry)->addMonths((int) $request->months);

        $admin->update(['expiry_date' => $newExpiry]);

        NotificationController::createAdminNotification(
            'subscription_extended',
            'Abonnement prolongé',
            "L'abonnement de {$admin->name} a été prolongé jusqu'au {$newExpiry->format('d/m/Y')}",
            $admin->id,
            'low'
        );

        return redirect()->back()
            ->with('success', "Abonnement prolongé de {$request->months} mois.");
    }

    public function resetPassword(Admin $admin)
    {
        $newPassword = Str::random(12);
        
        $admin->update([
            'password' => Hash::make($newPassword)
        ]);

        // Envoyer le nouveau mot de passe par email
        $this->sendPasswordResetEmail($admin, $newPassword);

        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé et envoyé par email.');
    }

    public function activityLog(Admin $admin)
    {
        // Simuler un log d'activité
        $activities = collect([
            [
                'action' => 'Connexion',
                'description' => 'Connexion au système',
                'ip' => '192.168.1.1',
                'date' => now()->subHours(2)
            ],
            [
                'action' => 'Création commande',
                'description' => 'Nouvelle commande #12345',
                'ip' => '192.168.1.1',
                'date' => now()->subHours(4)
            ]
        ]);

        return view('super-admin.admins.activity-log', compact('admin', 'activities'));
    }

    public function statistics(Admin $admin)
    {
        $stats = [
            'daily_orders' => $this->getDailyOrdersStats($admin),
            'monthly_revenue' => $this->getMonthlyRevenueStats($admin),
            'employee_performance' => $this->getEmployeePerformanceStats($admin),
            'usage_analytics' => $this->getUsageAnalytics($admin)
        ];

        return view('super-admin.admins.statistics', compact('admin', 'stats'));
    }

    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,extend,delete',
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admins,id',
            'months' => 'required_if:action,extend|integer|min:1|max:24'
        ]);

        $adminIds = $request->admin_ids;
        $action = $request->action;

        try {
            switch ($action) {
                case 'activate':
                    Admin::whereIn('id', $adminIds)->update(['is_active' => true]);
                    $message = count($adminIds) . ' administrateur(s) activé(s)';
                    break;

                case 'deactivate':
                    Admin::whereIn('id', $adminIds)->update(['is_active' => false]);
                    $message = count($adminIds) . ' administrateur(s) désactivé(s)';
                    break;

                case 'extend':
                    $admins = Admin::whereIn('id', $adminIds)->get();
                    foreach ($admins as $admin) {
                        $currentExpiry = $admin->expiry_date ?? now();
                        $newExpiry = Carbon::parse($currentExpiry)->addMonths((int) $request->months);
                        $admin->update(['expiry_date' => $newExpiry]);
                    }
                    $message = count($adminIds) . ' abonnement(s) prolongé(s)';
                    break;

                case 'delete':
                    Admin::whereIn('id', $adminIds)->delete();
                    $message = count($adminIds) . ' administrateur(s) supprimé(s)';
                    break;
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'action groupée: ' . $e->getMessage());
        }
    }

    // Export methods
    public function exportCsv(Request $request)
    {
        $query = Admin::query();
        $this->applyFilters($query, $request);
        
        $admins = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admins_' . date('Y-m-d') . '.csv"'
        ];

        $callback = function() use ($admins) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Identifiant', 'Nom', 'Email', 'Boutique', 
                'Téléphone', 'Actif', 'Date d\'expiration', 'Date de création'
            ]);
            
            // Data
            foreach ($admins as $admin) {
                fputcsv($file, [
                    $admin->id,
                    $admin->identifier,
                    $admin->name,
                    $admin->email,
                    $admin->shop_name,
                    $admin->phone,
                    $admin->is_active ? 'Oui' : 'Non',
                    $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : '',
                    $admin->created_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AdminsExport($request), 'admins_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Admin::query();
        $this->applyFilters($query, $request);
        $admins = $query->get();

        $pdf = Pdf::loadView('super-admin.admins.export-pdf', compact('admins'));
        
        return $pdf->download('admins_' . date('Y-m-d') . '.pdf');
    }

    // API methods
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $admins = Admin::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('shop_name', 'LIKE', "%{$query}%")
            ->orWhere('identifier', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'shop_name', 'identifier']);

        return response()->json($admins);
    }

    public function getStatistics()
    {
        return response()->json($this->getAdminStats());
    }

    // Private helper methods
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('shop_name', 'LIKE', "%{$search}%")
                  ->orWhere('identifier', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('subscription')) {
            $query->where('subscription_type', $request->subscription);
        }

        if ($request->filled('expiry_filter')) {
            switch ($request->expiry_filter) {
                case 'expired':
                    $query->where('expiry_date', '<', now());
                    break;
                case 'expiring':
                    $query->where('expiry_date', '<=', now()->addDays(7))
                          ->where('expiry_date', '>=', now());
                    break;
                case 'valid':
                    $query->where('expiry_date', '>', now());
                    break;
            }
        }
    }

    private function getAdminStats()
    {
        return [
            'total' => Admin::count(),
            'active' => Admin::where('is_active', true)->count(),
            'inactive' => Admin::where('is_active', false)->count(),
            'expired' => Admin::where('expiry_date', '<', now())->count(),
            'expiring_soon' => Admin::where('expiry_date', '<=', now()->addDays(7))
                                   ->where('expiry_date', '>=', now())->count(),
            'new_this_month' => Admin::where('created_at', '>=', now()->startOfMonth())->count()
        ];
    }

    private function generateUniqueIdentifier()
    {
        do {
            $numbers = mt_rand(1000, 9999);
            $letter = strtolower(chr(rand(97, 122)));
            $identifier = $numbers . '/' . $letter;
        } while (Admin::where('identifier', $identifier)->exists());

        return $identifier;
    }

    private function sendWelcomeEmail($admin, $password)
    {
        // Implémenter l'envoi d'email de bienvenue
        // Mail::to($admin->email)->send(new WelcomeAdminMail($admin, $password));
    }

    private function sendPasswordResetEmail($admin, $password)
    {
        // Implémenter l'envoi d'email de réinitialisation
        // Mail::to($admin->email)->send(new PasswordResetMail($admin, $password));
    }

    // Méthodes d'analyse (à implémenter selon les besoins)
    private function getAverageOrdersPerDay($admin) { return round($admin->total_orders / 30, 1); }
    private function getSubscriptionStatus($admin) { 
        if (!$admin->expiry_date) return 'Illimité';
        return $admin->expiry_date > now() ? 'Actif' : 'Expiré';
    }
    private function getUsagePercentage($admin) { return rand(60, 95); }
    private function getAdminRecentActivity($admin) { return collect(); }
    private function getAdminChartData($admin) { return []; }
    private function getDailyOrdersStats($admin) { return []; }
    private function getMonthlyRevenueStats($admin) { return []; }
    private function getEmployeePerformanceStats($admin) { return []; }
    private function getUsageAnalytics($admin) { return []; }
}