<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Delivery\ShippingServiceFactory;
use App\Services\Delivery\MockCarrierService;
use App\Services\Delivery\Contracts\CarrierServiceInterface;

/**
 * Provider pour le système de livraison multi-transporteurs
 * 
 * Enregistre tous les services et bindings nécessaires
 * pour le fonctionnement du système de livraison
 */
class DeliveryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer la factory comme singleton
        $this->app->singleton(ShippingServiceFactory::class, function ($app) {
            return new ShippingServiceFactory();
        });

        // Enregistrer l'alias pour la factory
        $this->app->alias(ShippingServiceFactory::class, 'shipping.factory');

        // Enregistrer le service mock pour les tests
        $this->app->bind(MockCarrierService::class, function ($app) {
            return new MockCarrierService();
        });

        // Binding conditionnel selon l'environnement
        $this->app->bind(CarrierServiceInterface::class, function ($app) {
            // En mode test, utiliser le service mock
            if ($app->environment('testing')) {
                return $app->make(MockCarrierService::class);
            }
            
            // En mode production, utiliser la factory (sera résolu plus tard)
            return $app->make(ShippingServiceFactory::class);
        });

        // Enregistrer les services spécifiques (à implémenter dans Phase 2 et 3)
        $this->registerCarrierServices();

        // Enregistrer les commandes artisan personnalisées
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier les fichiers de configuration si nécessaire
        $this->publishes([
            __DIR__.'/../../config/carriers.php' => config_path('carriers.php'),
        ], 'delivery-config');

        // Enregistrer les policies
        $this->registerPolicies();

        // Enregistrer les event listeners
        $this->registerEventListeners();

        // Enregistrer les routes de webhook si nécessaire
        $this->registerWebhookRoutes();
    }

    /**
     * Enregistrer les services de transporteurs spécifiques
     */
    protected function registerCarrierServices(): void
    {
        // Service JAX Delivery (RÉEL)
        $this->app->bind('delivery.jax', function ($app) {
            return new \App\Services\Delivery\JaxDeliveryService();
        });

        // Service Mes Colis Express (Mock pour l'instant, Phase 3)
        $this->app->bind('delivery.mes_colis', function ($app) {
            return new MockCarrierService('mes_colis');
        });

        // Enregistrer automatiquement les services dans la factory
        if ($this->app->resolved(ShippingServiceFactory::class)) {
            $factory = $this->app->make(ShippingServiceFactory::class);
            
            // Utiliser le vrai service JAX en production
            $factory::register('jax_delivery', \App\Services\Delivery\JaxDeliveryService::class);
            
            // Mock pour Mes Colis (Phase 3)
            $factory::register('mes_colis', MockCarrierService::class);
        }
    }

    /**
     * Enregistrer les commandes artisan personnalisées
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // \App\Console\Commands\DeliveryTrackingCommand::class, // À créer en Phase 5
                // \App\Console\Commands\DeliveryTestCommand::class,      // À créer pour tests
                // \App\Console\Commands\DeliveryStatusCommand::class,    // À créer pour debug
            ]);
        }
    }

    /**
     * Enregistrer les policies pour l'autorisation
     */
    protected function registerPolicies(): void
    {
        // Policies à créer si nécessaire
        // Gate::policy(DeliveryConfiguration::class, DeliveryConfigurationPolicy::class);
        // Gate::policy(Pickup::class, PickupPolicy::class);
        // Gate::policy(Shipment::class, ShipmentPolicy::class);
    }

    /**
     * Enregistrer les event listeners
     */
    protected function registerEventListeners(): void
    {
        // Event listeners pour la livraison
        // Event::listen(OrderStatusChanged::class, UpdateShipmentStatus::class);
        // Event::listen(ShipmentCreated::class, NotifyCarrier::class);
        // Event::listen(ShipmentDelivered::class, UpdateOrderStatus::class);
    }

    /**
     * Enregistrer les routes de webhook
     */
    protected function registerWebhookRoutes(): void
    {
        // Les routes de webhook sont définies dans le fichier de routes
        // Ici on peut ajouter des middlewares spécifiques si nécessaire
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ShippingServiceFactory::class,
            'shipping.factory',
            CarrierServiceInterface::class,
            MockCarrierService::class,
            'delivery.jax',
            'delivery.mes_colis',
        ];
    }
}

/**
 * INSTRUCTIONS D'INSTALLATION :
 * 
 * 1. Ajouter ce provider dans config/app.php :
 * 
 * 'providers' => [
 *     // ...
 *     App\Providers\DeliveryServiceProvider::class,
 * ],
 * 
 * 2. Optionnel - Ajouter des alias dans config/app.php :
 * 
 * 'aliases' => [
 *     // ...
 *     'ShippingFactory' => App\Services\Delivery\ShippingServiceFactory::class,
 * ],
 * 
 * 3. Publier la configuration :
 * 
 * php artisan vendor:publish --tag=delivery-config
 * 
 * 4. Utilisation dans les contrôleurs :
 * 
 * public function __construct(ShippingServiceFactory $factory)
 * {
 *     $this->factory = $factory;
 * }
 * 
 * // ou
 * 
 * $service = app('shipping.factory')->create('jax_delivery');
 * $result = $service->testConnection($config);
 */