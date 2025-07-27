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

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Manager::class => ManagerPolicy::class,
        Employee::class => EmployeePolicy::class,
        Admin::class => AdminPolicy::class,
        // Policies pour la livraison
        \App\Models\DeliveryConfiguration::class => \App\Policies\DeliveryConfigurationPolicy::class,
        Pickup::class => PickupPolicy::class,
        Shipment::class => ShipmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ========================================
        // GATES SIMPLIFIÉS POUR LE SYSTÈME DE TRAITEMENT
        // ========================================
        
        // Gates pour les interfaces de traitement - VERSION SIMPLIFIÉE
        Gate::define('view-process-interface', function ($admin = null) {
            // Autoriser l'accès si l'utilisateur est connecté en tant qu'admin
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('view-examination', function ($admin = null) {
            // Autoriser l'accès si l'utilisateur est connecté en tant qu'admin
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('view-suspended', function ($admin = null) {
            // Autoriser l'accès si l'utilisateur est connecté en tant qu'admin
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('view-restock', function ($admin = null) {
            // Autoriser l'accès si l'utilisateur est connecté en tant qu'admin
            return $admin && $admin instanceof Admin;
        });
        
        // Gates pour les actions de traitement - VERSION SIMPLIFIÉE
        Gate::define('process-order', function ($admin = null, $order = null) {
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('split-order', function ($admin = null, $order = null) {
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('suspend-order', function ($admin = null, $order = null) {
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('reactivate-order', function ($admin = null, $order = null) {
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('cancel-order', function ($admin = null, $order = null) {
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('bulk-actions', function ($admin = null) {
            return $admin && $admin instanceof Admin;
        });
        
        Gate::define('view-process-stats', function ($admin = null) {
            return $admin && $admin instanceof Admin;
        });

        // ========================================
        // AUTRES GATES SIMPLIFIÉS
        // ========================================
        
        Gate::define('viewLoginHistory', function ($admin, $userType, $userId) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canCreateManager', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canCreateEmployee', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('adminActive', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('super-admin', function ($user) {
            return $user && $user instanceof Admin;
        });

        Gate::define('active-admin', function ($user) {
            return $user && $user instanceof Admin;
        });

        // Gates pour les produits
        Gate::define('bulkManageProducts', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canActivateProducts', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canDeactivateProducts', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canDeleteProducts', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canReviewProducts', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        // Gates pour les commandes
        Gate::define('canAssignOrders', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('viewUnassignedOrders', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        // Gates pour les imports
        Gate::define('canImportData', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canManageWooCommerce', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        // Gates pour les paramètres
        Gate::define('canManageSettings', function ($admin) {
            return $admin && $admin instanceof Admin;
        });

        Gate::define('canManageAdvancedSettings', function ($admin) {
            return $admin && $admin instanceof Admin;
        });
    }
}