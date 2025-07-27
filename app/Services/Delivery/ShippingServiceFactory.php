<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierConfigurationException;
use App\Models\DeliveryConfiguration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Factory pour créer les services de transporteurs
 * 
 * Cette factory utilise le pattern Factory pour instancier automatiquement
 * le bon service selon le transporteur configuré
 */
class ShippingServiceFactory
{
    /**
     * Mapping des transporteurs vers leurs services
     */
    protected static array $carrierServices = [
        'jax_delivery' => JaxDeliveryService::class,
        'mes_colis' => MockCarrierService::class, // Sera remplacé par MesColisService en Phase 3
    ];

    /**
     * Cache des instances de services
     */
    protected static array $serviceInstances = [];

    /**
     * Créer un service pour une configuration donnée
     * 
     * @param DeliveryConfiguration $config
     * @return CarrierServiceInterface
     * @throws CarrierServiceException
     */
    public static function createFromConfig(DeliveryConfiguration $config): CarrierServiceInterface
    {
        return static::create($config->carrier_slug);
    }

    /**
     * Créer un service pour un transporteur donné
     * 
     * @param string $carrierSlug
     * @return CarrierServiceInterface
     * @throws CarrierServiceException
     */
    public static function create(string $carrierSlug): CarrierServiceInterface
    {
        // Vérifier que le transporteur est supporté
        if (!isset(static::$carrierServices[$carrierSlug])) {
            throw new CarrierConfigurationException(
                "Transporteur non supporté: {$carrierSlug}. " .
                "Transporteurs disponibles: " . implode(', ', array_keys(static::$carrierServices))
            );
        }

        // Utiliser le cache si disponible
        if (isset(static::$serviceInstances[$carrierSlug])) {
            return static::$serviceInstances[$carrierSlug];
        }

        $serviceClass = static::$carrierServices[$carrierSlug];

        try {
            // Vérifier que la classe existe
            if (!class_exists($serviceClass)) {
                throw new CarrierConfigurationException(
                    "Classe de service non trouvée: {$serviceClass}"
                );
            }

            // Instancier le service
            $service = App::make($serviceClass);

            // Vérifier que le service implémente l'interface
            if (!$service instanceof CarrierServiceInterface) {
                throw new CarrierConfigurationException(
                    "Le service {$serviceClass} doit implémenter CarrierServiceInterface"
                );
            }

            // Mettre en cache
            static::$serviceInstances[$carrierSlug] = $service;

            Log::info("Service de transporteur créé", [
                'carrier_slug' => $carrierSlug,
                'service_class' => $serviceClass
            ]);

            return $service;

        } catch (\Exception $e) {
            Log::error("Erreur création service transporteur", [
                'carrier_slug' => $carrierSlug,
                'service_class' => $serviceClass,
                'error' => $e->getMessage()
            ]);

            throw new CarrierServiceException(
                "Impossible de créer le service pour {$carrierSlug}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Obtenir tous les transporteurs supportés
     * 
     * @return array
     */
    public static function getSupportedCarriers(): array
    {
        return array_keys(static::$carrierServices);
    }

    /**
     * Vérifier si un transporteur est supporté
     * 
     * @param string $carrierSlug
     * @return bool
     */
    public static function isSupported(string $carrierSlug): bool
    {
        return isset(static::$carrierServices[$carrierSlug]);
    }

    /**
     * Enregistrer un nouveau service de transporteur
     * 
     * @param string $carrierSlug
     * @param string $serviceClass
     * @return void
     * @throws CarrierConfigurationException
     */
    public static function register(string $carrierSlug, string $serviceClass): void
    {
        if (!class_exists($serviceClass)) {
            throw new CarrierConfigurationException(
                "Classe de service non trouvée: {$serviceClass}"
            );
        }

        if (!is_subclass_of($serviceClass, CarrierServiceInterface::class)) {
            throw new CarrierConfigurationException(
                "Le service {$serviceClass} doit implémenter CarrierServiceInterface"
            );
        }

        static::$carrierServices[$carrierSlug] = $serviceClass;

        // Invalider le cache pour ce transporteur
        unset(static::$serviceInstances[$carrierSlug]);

        Log::info("Nouveau service de transporteur enregistré", [
            'carrier_slug' => $carrierSlug,
            'service_class' => $serviceClass
        ]);
    }

    /**
     * Retirer un service de transporteur
     * 
     * @param string $carrierSlug
     * @return void
     */
    public static function unregister(string $carrierSlug): void
    {
        unset(static::$carrierServices[$carrierSlug]);
        unset(static::$serviceInstances[$carrierSlug]);

        Log::info("Service de transporteur retiré", [
            'carrier_slug' => $carrierSlug
        ]);
    }

    /**
     * Vider le cache des services
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        static::$serviceInstances = [];
        Log::info("Cache des services de transporteurs vidé");
    }

    /**
     * Obtenir les informations de tous les transporteurs
     * 
     * @return array
     */
    public static function getAllCarriersInfo(): array
    {
        $carriers = [];
        $configs = config('carriers');

        foreach (static::$carrierServices as $slug => $serviceClass) {
            try {
                $service = static::create($slug);
                $info = $service->getCarrierInfo();
                $limits = $service->getCarrierLimits();
                
                $carriers[$slug] = array_merge($info, [
                    'limits' => $limits,
                    'config' => $configs[$slug] ?? [],
                    'service_class' => $serviceClass,
                    'is_available' => class_exists($serviceClass),
                ]);
            } catch (\Exception $e) {
                Log::warning("Impossible d'obtenir les infos du transporteur {$slug}", [
                    'error' => $e->getMessage()
                ]);
                
                $carriers[$slug] = [
                    'slug' => $slug,
                    'name' => $configs[$slug]['name'] ?? ucfirst(str_replace('_', ' ', $slug)),
                    'is_available' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $carriers;
    }

    /**
     * Tester tous les services configurés
     * 
     * @param int $adminId
     * @return array
     */
    public static function testAllServices(int $adminId): array
    {
        $results = [];
        
        $configurations = DeliveryConfiguration::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get();

        foreach ($configurations as $config) {
            try {
                $service = static::createFromConfig($config);
                $testResult = $service->testConnection($config);
                
                $results[$config->carrier_slug][] = [
                    'config_id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'test_result' => $testResult,
                ];
            } catch (\Exception $e) {
                $results[$config->carrier_slug][] = [
                    'config_id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'test_result' => [
                        'success' => false,
                        'error' => $e->getMessage(),
                    ],
                ];
            }
        }

        return $results;
    }

    /**
     * Obtenir les statistiques d'utilisation des services
     * 
     * @return array
     */
    public static function getUsageStats(): array
    {
        $stats = [];

        foreach (static::$carrierServices as $slug => $serviceClass) {
            $stats[$slug] = [
                'configurations_count' => DeliveryConfiguration::where('carrier_slug', $slug)->count(),
                'active_configurations_count' => DeliveryConfiguration::where('carrier_slug', $slug)
                    ->where('is_active', true)->count(),
                'pickups_count' => \App\Models\Pickup::where('carrier_slug', $slug)->count(),
                'shipments_count' => \App\Models\Shipment::where('carrier_slug', $slug)->count(),
                'is_cached' => isset(static::$serviceInstances[$slug]),
                'service_class' => $serviceClass,
            ];
        }

        return $stats;
    }

    /**
     * Valider la configuration d'un transporteur
     * 
     * @param string $carrierSlug
     * @param array $configData
     * @return array Erreurs de validation
     */
    public static function validateCarrierConfig(string $carrierSlug, array $configData): array
    {
        try {
            $service = static::create($carrierSlug);
            
            // Créer une configuration temporaire pour validation
            $tempConfig = new DeliveryConfiguration([
                'carrier_slug' => $carrierSlug,
                'username' => $configData['username'] ?? '',
                'password' => $configData['password'] ?? '',
            ]);

            // Valider via le service (si méthode disponible)
            if (method_exists($service, 'validateConfiguration')) {
                return $service->validateConfiguration($tempConfig);
            }

            return []; // Pas d'erreurs si validation pas supportée

        } catch (\Exception $e) {
            return ["Erreur de validation: " . $e->getMessage()];
        }
    }

    /**
     * Créer un service de test/mock
     * 
     * @param string $carrierSlug
     * @return CarrierServiceInterface
     */
    public static function createMockService(string $carrierSlug): CarrierServiceInterface
    {
        if (app()->environment('testing') || config('app.debug')) {
            return new MockCarrierService($carrierSlug);
        }

        return static::create($carrierSlug);
    }

    /**
     * Obtenir les fonctionnalités supportées par transporteur
     * 
     * @return array
     */
    public static function getSupportedFeatures(): array
    {
        $features = [];
        $commonFeatures = [
            'create_shipment',
            'track_shipment', 
            'test_connection',
            'cancel_shipment',
            'multiple_tracking',
            'pickup_support',
            'webhook_support',
            'label_generation',
            'cost_calculation',
        ];

        foreach (static::$carrierServices as $slug => $serviceClass) {
            try {
                $service = static::create($slug);
                $features[$slug] = [];
                
                foreach ($commonFeatures as $feature) {
                    $features[$slug][$feature] = $service->supportsFeature($feature);
                }
            } catch (\Exception $e) {
                $features[$slug] = array_fill_keys($commonFeatures, false);
                $features[$slug]['error'] = $e->getMessage();
            }
        }

        return $features;
    }
}