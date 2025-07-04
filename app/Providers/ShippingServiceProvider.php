<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Shipping\ShippingServiceFactory;
use App\Services\Shipping\JaxDeliveryService;
use App\Services\Shipping\FparcelService;
use App\Services\Shipping\AramexService;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer le factory comme singleton
        $this->app->singleton(ShippingServiceFactory::class, function ($app) {
            $factory = new ShippingServiceFactory();
            
            // Auto-registration des services basés sur la config
            $this->autoRegisterServices($factory);
            
            return $factory;
        });

        // Alias pour plus de facilité
        $this->app->alias(ShippingServiceFactory::class, 'shipping.factory');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier la configuration si nécessaire
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/carriers.php' => config_path('carriers.php'),
            ], 'carrier-config');
        }
    }

    /**
     * Auto-enregistrement des services basés sur la configuration
     */
    private function autoRegisterServices(ShippingServiceFactory $factory): void
    {
        $carriers = config('carriers', []);
        
        foreach ($carriers as $slug => $config) {
            $serviceClass = $this->getServiceClassForCarrier($slug);
            
            if ($serviceClass && class_exists($serviceClass)) {
                $factory->register($slug, $serviceClass);
            }
        }
    }

    /**
     * Obtenir la classe de service pour un transporteur
     */
    private function getServiceClassForCarrier(string $slug): ?string
    {
        // Convention de nommage : SnakeCaseService
        $className = str_replace('_', '', ucwords($slug, '_')) . 'Service';
        $fullClassName = "App\\Services\\Shipping\\{$className}";
        
        // Mapping manuel pour les cas spéciaux
        $manualMapping = [
            'jax_delivery' => JaxDeliveryService::class,
            'fparcel' => FparcelService::class,
            'aramex' => AramexService::class,
            // Ajouter d'autres mappings si nécessaire
        ];

        return $manualMapping[$slug] ?? (class_exists($fullClassName) ? $fullClassName : null);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ShippingServiceFactory::class,
            'shipping.factory',
        ];
    }
}