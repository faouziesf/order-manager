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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

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

        // Gate pour vérifier si l'admin est actif et non expiré
        Gate::define('adminActive', function ($admin) {
            return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
        });

        // Gates supplémentaires pour les produits (actions groupées)
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
    }
}