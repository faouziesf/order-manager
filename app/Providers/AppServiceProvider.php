<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer le service de notifications comme singleton
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the observer for orders
        Order::observe(OrderObserver::class);
        
        // Utiliser Bootstrap pour la pagination
        Paginator::useBootstrapFive();
        
        // Enregistrer des directives Blade personnalisées si nécessaire
        Blade::directive('money', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', ' ') . ' €'; ?>";
        });
        
        // Configuration pour les environnements de développement
        if ($this->app->environment('local')) {
            // Activer les logs détaillés pour les notifications
            \Illuminate\Support\Facades\Log::info('NotificationService enregistré en mode développement');
        }
        
        // Vues partagées pour les composants de notifications
        view()->composer('*', function ($view) {
            // Partager le count des notifications non lues pour tous les vues
            if (auth('super-admin')->check()) {
                $unreadNotifications = \App\Models\SuperAdminNotification::whereNull('read_at')->count();
                $view->with('globalUnreadNotifications', $unreadNotifications);
            }
        });
    }
}