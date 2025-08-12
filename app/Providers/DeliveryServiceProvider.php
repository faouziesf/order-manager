<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Delivery\ShippingServiceFactory;
use App\Services\Delivery\JaxDeliveryService;
use App\Services\Delivery\MesColisService;
use App\Services\Delivery\Contracts\CarrierServiceInterface;

/**
 * Provider pour le système de livraison multi-transporteurs
 * 
 * Enregistre tous les services et bindings nécessaires
 * pour le fonctionnement du système de livraison avec intégration API réelle
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

        // 🆕 Enregistrer les services de transporteurs réels
        $this->app->bind('delivery.jax', function ($app) {
            // Le service sera créé avec la configuration lors de l'utilisation
            return null; // La factory s'occupera de la création avec config
        });

        $this->app->bind('delivery.mes_colis', function ($app) {
            // Le service sera créé avec la configuration lors de l'utilisation
            return null; // La factory s'occupera de la création avec config
        });

        // Binding principal pour l'interface CarrierServiceInterface
        $this->app->bind(CarrierServiceInterface::class, function ($app) {
            // Retourner la factory qui gérera la création des services
            return $app->make(ShippingServiceFactory::class);
        });

        // 🆕 Enregistrer les services spécifiques par classe
        $this->app->bind(JaxDeliveryService::class, function ($app) {
            // Configuration par défaut - sera remplacée par la vraie config
            return new JaxDeliveryService([
                'api_key' => config('carriers.jax_delivery.api_key', ''),
                'environment' => config('carriers.jax_delivery.environment', 'test'),
            ]);
        });

        $this->app->bind(MesColisService::class, function ($app) {
            // Configuration par défaut - sera remplacée par la vraie config
            return new MesColisService([
                'api_key' => config('carriers.mes_colis.api_key', ''),
                'environment' => config('carriers.mes_colis.environment', 'test'),
            ]);
        });

        // Enregistrer les commandes artisan personnalisées
        $this->registerCommands();

        // 🆕 Enregistrer les helpers pour debug
        $this->registerDebugHelpers();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier les fichiers de configuration
        $this->publishes([
            __DIR__.'/../../config/carriers.php' => config_path('carriers.php'),
        ], 'delivery-config');

        // 🆕 S'assurer que le fichier de configuration existe
        $this->ensureConfigFileExists();

        // Enregistrer les policies
        $this->registerPolicies();

        // Enregistrer les event listeners
        $this->registerEventListeners();

        // 🆕 Enregistrer les routes de test si en mode debug
        if (config('app.debug')) {
            $this->registerDebugRoutes();
        }
    }

    /**
     * 🆕 S'assurer que le fichier de configuration carriers.php existe
     */
    protected function ensureConfigFileExists(): void
    {
        $configPath = config_path('carriers.php');
        
        if (!file_exists($configPath)) {
            // Créer le fichier de configuration avec la structure de base
            $defaultConfig = $this->getDefaultCarriersConfig();
            file_put_contents($configPath, "<?php\n\nreturn " . var_export($defaultConfig, true) . ";\n");
            
            \Log::info('📁 [DELIVERY PROVIDER] Fichier carriers.php créé automatiquement', [
                'path' => $configPath
            ]);
        }
    }

    /**
     * 🆕 Configuration par défaut pour carriers.php
     */
    protected function getDefaultCarriersConfig(): array
    {
        return [
            'jax_delivery' => [
                'name' => 'JAX Delivery',
                'slug' => 'jax_delivery',
                'description' => 'Service de livraison JAX Delivery en Tunisie',
                'supported_services' => [
                    'create_shipment' => true,
                    'create_pickup' => true,
                    'track_shipment' => true,
                    'webhooks' => true,
                ],
                'api' => [
                    'base_url' => 'https://core.jax-delivery.com/api',
                    'timeout' => 30,
                ],
                'features' => [
                    'cod_support' => true,
                    'weight_based_pricing' => true,
                    'pickup_scheduling' => true,
                    'real_time_tracking' => true,
                ],
            ],
            'mes_colis' => [
                'name' => 'Mes Colis Express',
                'slug' => 'mes_colis',
                'description' => 'Service de livraison Mes Colis Express en Tunisie',
                'supported_services' => [
                    'create_shipment' => true,
                    'create_pickup' => false,
                    'track_shipment' => true,
                    'webhooks' => false,
                ],
                'api' => [
                    'base_url' => 'https://api.mescolis.tn/api',
                    'timeout' => 30,
                ],
                'features' => [
                    'cod_support' => true,
                    'weight_based_pricing' => true,
                    'pickup_scheduling' => false,
                    'real_time_tracking' => true,
                ],
            ],
            'system' => [
                'default_timeout' => 30,
                'max_retries' => 3,
                'retry_delay' => 2,
                'enable_webhooks' => env('CARRIERS_ENABLE_WEBHOOKS', true),
                'debug_mode' => env('CARRIERS_DEBUG_MODE', false),
            ],
        ];
    }

    /**
     * Enregistrer les commandes artisan personnalisées
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // 🔮 Commandes futures à créer
                // \App\Console\Commands\DeliveryTrackingCommand::class,
                // \App\Console\Commands\DeliveryTestCommand::class,
                // \App\Console\Commands\DeliveryStatusCommand::class,
                // \App\Console\Commands\DeliveryWebhookTestCommand::class,
            ]);
        }
    }

    /**
     * 🆕 Enregistrer les helpers pour debug
     */
    protected function registerDebugHelpers(): void
    {
        // Helper global pour tester la factory
        $this->app->bind('delivery.test', function ($app) {
            return function ($carrierSlug = 'jax_delivery') {
                try {
                    $factory = $app->make(ShippingServiceFactory::class);
                    $testConfig = [
                        'api_key' => 'test_token_' . time(),
                        'environment' => 'test',
                    ];
                    
                    $service = $factory->create($carrierSlug, $testConfig);
                    
                    return [
                        'success' => true,
                        'carrier' => $carrierSlug,
                        'service_class' => get_class($service),
                        'test_connection' => $service->testConnection(),
                    ];
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'carrier' => $carrierSlug,
                        'error' => $e->getMessage(),
                    ];
                }
            };
        });

        // Helper pour vérifier les carriers supportés
        $this->app->bind('delivery.carriers', function ($app) {
            return function () {
                $factory = $app->make(ShippingServiceFactory::class);
                return $factory->getSupportedCarriers();
            };
        });
    }

    /**
     * Enregistrer les policies pour l'autorisation
     */
    protected function registerPolicies(): void
    {
        // 🔮 Policies futures à créer si nécessaire
        // Gate::policy(\App\Models\DeliveryConfiguration::class, \App\Policies\DeliveryConfigurationPolicy::class);
        // Gate::policy(\App\Models\Pickup::class, \App\Policies\PickupPolicy::class);
        // Gate::policy(\App\Models\Shipment::class, \App\Policies\ShipmentPolicy::class);
    }

    /**
     * Enregistrer les event listeners
     */
    protected function registerEventListeners(): void
    {
        // 🔮 Event listeners futures pour la livraison
        // Event::listen(\App\Events\OrderStatusChanged::class, \App\Listeners\UpdateShipmentStatus::class);
        // Event::listen(\App\Events\ShipmentCreated::class, \App\Listeners\NotifyCarrier::class);
        // Event::listen(\App\Events\ShipmentDelivered::class, \App\Listeners\UpdateOrderStatus::class);
        // Event::listen(\App\Events\PickupValidated::class, \App\Listeners\NotifyCarrierPickup::class);
    }

    /**
     * 🆕 Enregistrer les routes de test en mode debug
     */
    protected function registerDebugRoutes(): void
    {
        // Ces routes seront disponibles uniquement en mode debug
        $router = $this->app['router'];
        
        // Test rapide de la factory
        $router->get('/_debug/delivery/factory-test', function () {
            try {
                $testFunction = app('delivery.test');
                $jaxTest = $testFunction('jax_delivery');
                $mesColisTest = $testFunction('mes_colis');
                
                return response()->json([
                    'factory_test' => 'OK',
                    'jax_delivery' => $jaxTest,
                    'mes_colis' => $mesColisTest,
                    'timestamp' => now()->toISOString(),
                ], 200, [], JSON_PRETTY_PRINT);
            } catch (\Exception $e) {
                return response()->json([
                    'factory_test' => 'ERROR',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }
        });

        // Liste des carriers supportés
        $router->get('/_debug/delivery/carriers', function () {
            $carriersFunction = app('delivery.carriers');
            return response()->json([
                'supported_carriers' => $carriersFunction(),
                'config_carriers' => config('carriers'),
                'timestamp' => now()->toISOString(),
            ], 200, [], JSON_PRETTY_PRINT);
        });
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
            JaxDeliveryService::class,
            MesColisService::class,
            'delivery.jax',
            'delivery.mes_colis',
            'delivery.test',
            'delivery.carriers',
        ];
    }
}

/**
 * 📋 INSTRUCTIONS D'UTILISATION MISE À JOUR :
 * 
 * 1. ✅ Ce provider est déjà enregistré dans votre config/app.php
 * 
 * 2. 🆕 Test de l'installation :
 * 
 * Visitez: /admin/test-delivery-integration
 * 
 * 3. 🆕 Tests de debug (en mode debug uniquement) :
 * 
 * - /_debug/delivery/factory-test
 * - /_debug/delivery/carriers
 * 
 * 4. 🆕 Utilisation dans les contrôleurs :
 * 
 * public function __construct(ShippingServiceFactory $factory)
 * {
 *     $this->factory = $factory;
 * }
 * 
 * // Créer un service avec configuration
 * $config = $deliveryConfiguration->getDecryptedConfig();
 * $service = $this->factory->create('jax_delivery', $config);
 * $result = $service->createShipment($shipmentData);
 * 
 * 5. 🆕 Test rapide en artisan tinker :
 * 
 * php artisan tinker
 * >>> $test = app('delivery.test');
 * >>> $result = $test('jax_delivery');
 * >>> dump($result);
 * 
 * 6. 🆕 Lister les carriers supportés :
 * 
 * >>> $carriers = app('delivery.carriers');
 * >>> dump($carriers());
 * 
 * 7. 🆕 Configuration automatique :
 * 
 * Le fichier config/carriers.php sera créé automatiquement s'il n'existe pas.
 * 
 * 8. 🔧 Debugging :
 * 
 * - Logs détaillés avec tags [JAX DELIVERY] et [MES COLIS]
 * - Routes de test intégrées en mode debug
 * - Helpers pour tester les services rapidement
 */