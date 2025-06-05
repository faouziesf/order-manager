<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Models\Manager;
use App\Policies\ManagerPolicy;
use App\Models\Employee;
use App\Policies\EmployeePolicy;
use App\Models\Admin;
use App\Policies\AdminPolicy;
use App\Policies\ProcessPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Manager::class => ManagerPolicy::class,
        Employee::class => EmployeePolicy::class,
        Admin::class => AdminPolicy::class,
        // Politique pour les contrôleurs de traitement
        'process' => ProcessPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ========================================
        // GATES POUR LE SYSTÈME DE TRAITEMENT
        // ========================================
        
        // Gates pour les interfaces de traitement
        Gate::define('view-process-interface', [ProcessPolicy::class, 'viewProcessInterface']);
        Gate::define('view-examination', [ProcessPolicy::class, 'viewExamination']);
        Gate::define('view-suspended', [ProcessPolicy::class, 'viewSuspended']);
        Gate::define('view-restock', [ProcessPolicy::class, 'viewRestock']);
        
        // Gates pour les actions de traitement
        Gate::define('process-order', [ProcessPolicy::class, 'processOrder']);
        Gate::define('split-order', [ProcessPolicy::class, 'splitOrder']);
        Gate::define('suspend-order', [ProcessPolicy::class, 'suspendOrder']);
        Gate::define('reactivate-order', [ProcessPolicy::class, 'reactivateOrder']);
        Gate::define('cancel-order', [ProcessPolicy::class, 'cancelOrder']);
        Gate::define('bulk-actions', [ProcessPolicy::class, 'bulkActions']);
        Gate::define('view-process-stats', [ProcessPolicy::class, 'viewStats']);

        // ========================================
        // GATES POUR LES UTILISATEURS
        // ========================================
        
        // Gate pour vérifier l'accès à l'historique de connexion
        Gate::define('viewLoginHistory', function ($admin, $userType, $userId) {
            // Vérifier que l'utilisateur appartient à cet admin
            switch($userType) {
                case 'App\Models\Admin':
                    return $admin->id == $userId;
                case 'App\Models\Manager':
                    return $admin->managers()->where('id', $userId)->exists();
                case 'App\Models\Employee':
                    return $admin->employees()->where('id', $userId)->exists();
                default:
                    return false;
            }
        });

        // Gate pour vérifier si l'admin peut créer plus d'utilisateurs
        Gate::define('canCreateManager', function ($admin) {
            return $admin->managers()->count() < $admin->max_managers;
        });

        Gate::define('canCreateEmployee', function ($admin) {
            return $admin->employees()->count() < $admin->max_employees;
        });

        // ========================================
        // GATES POUR LES ADMINS
        // ========================================
        
        // Gate pour vérifier si l'admin est actif et non expiré
        Gate::define('adminActive', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // Gate pour super admin
        Gate::define('super-admin', function ($user) {
            return $user instanceof Admin && $user->hasRole('super_admin');
        });

        // Gate pour admin actif
        Gate::define('active-admin', function ($user) {
            return $user instanceof Admin && $user->is_active && 
                   (!$user->expiry_date || !$user->expiry_date->isPast());
        });

        // ========================================
        // GATES POUR LES PRODUITS
        // ========================================
        
        // Gates pour les actions groupées sur les produits
        Gate::define('bulkManageProducts', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // Gate pour les actions spécifiques sur les produits
        Gate::define('canActivateProducts', function ($admin) {
            return Gate::allows('bulkManageProducts', $admin);
        });

        Gate::define('canDeactivateProducts', function ($admin) {
            return Gate::allows('bulkManageProducts', $admin);
        });

        Gate::define('canDeleteProducts', function ($admin) {
            return Gate::allows('bulkManageProducts', $admin);
        });

        // Gate pour marquer les produits comme examinés
        Gate::define('canReviewProducts', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // ========================================
        // GATES POUR LES COMMANDES
        // ========================================
        
        // Gate pour assigner des commandes
        Gate::define('canAssignOrders', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // Gate pour voir les commandes non assignées
        Gate::define('viewUnassignedOrders', function ($admin) {
            return Gate::allows('canAssignOrders', $admin);
        });

        // ========================================
        // GATES POUR LES IMPORTS ET INTÉGRATIONS
        // ========================================
        
        // Gate pour les imports
        Gate::define('canImportData', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // Gate pour WooCommerce
        Gate::define('canManageWooCommerce', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // ========================================
        // GATES POUR LES PARAMÈTRES
        // ========================================
        
        // Gate pour les paramètres
        Gate::define('canManageSettings', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // Gate pour les paramètres avancés (export/import de config)
        Gate::define('canManageAdvancedSettings', function ($admin) {
            return Gate::allows('canManageSettings', $admin) && $admin->hasRole('super_admin');
        });
    }
}