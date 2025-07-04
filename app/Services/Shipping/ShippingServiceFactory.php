<?php

namespace App\Services\Shipping;

use App\Models\DeliveryConfiguration;
use InvalidArgumentException;

class ShippingServiceFactory
{
    /**
     * Mapping des transporteurs vers leurs services
     */
    private array $services = [
        'jax_delivery' => JaxDeliveryService::class,
        'fparcel' => FparcelService::class,
        'aramex' => AramexService::class,
        'dhl' => DHLService::class,
        // Ajouter d'autres transporteurs ici
    ];

    /**
     * Créer une instance du service de transporteur approprié
     */
    public function make(string $carrier, DeliveryConfiguration $config): CarrierServiceInterface
    {
        $serviceClass = $this->services[$carrier] ?? null;
        
        if (!$serviceClass) {
            throw new InvalidArgumentException("Transporteur non supporté: {$carrier}");
        }

        if (!class_exists($serviceClass)) {
            throw new InvalidArgumentException("Classe de service non trouvée: {$serviceClass}");
        }

        $service = new $serviceClass($config);

        if (!$service instanceof CarrierServiceInterface) {
            throw new InvalidArgumentException(
                "Le service {$serviceClass} doit implémenter CarrierServiceInterface"
            );
        }

        return $service;
    }

    /**
     * Obtenir la liste des transporteurs supportés
     */
    public function getSupportedCarriers(): array
    {
        $carriers = [];
        
        foreach ($this->services as $slug => $serviceClass) {
            if (class_exists($serviceClass)) {
                $carriers[$slug] = config("carriers.{$slug}", [
                    'display_name' => ucfirst($slug),
                    'available' => true
                ]);
            }
        }

        return $carriers;
    }

    /**
     * Vérifier si un transporteur est supporté
     */
    public function isSupported(string $carrier): bool
    {
        return isset($this->services[$carrier]) && class_exists($this->services[$carrier]);
    }

    /**
     * Enregistrer un nouveau service de transporteur
     */
    public function register(string $carrier, string $serviceClass): void
    {
        if (!class_exists($serviceClass)) {
            throw new InvalidArgumentException("Classe de service non trouvée: {$serviceClass}");
        }

        $this->services[$carrier] = $serviceClass;
    }

    /**
     * Obtenir les capacités d'un transporteur sans instancier le service
     */
    public function getCarrierCapabilities(string $carrier): array
    {
        $carrierConfig = config("carriers.{$carrier}");
        
        if (!$carrierConfig) {
            return [];
        }

        return [
            'supports_pickup_address' => $carrierConfig['supports_pickup_address'] ?? false,
            'supports_bl_templates' => $carrierConfig['supports_bl_templates'] ?? false,
            'supports_mass_labels' => $carrierConfig['supports_mass_labels'] ?? false,
            'supports_drop_points' => $carrierConfig['supports_drop_points'] ?? false,
            'supports_payment_methods' => $carrierConfig['supports_payment_methods'] ?? false,
            'supports_pickup_scheduling' => $carrierConfig['supports_pickup_scheduling'] ?? false,
        ];
    }

    /**
     * Valider la configuration d'un transporteur
     */
    public function validateCarrierConfig(string $carrier, array $config): array
    {
        $carrierConfig = config("carriers.{$carrier}");
        
        if (!$carrierConfig) {
            return [
                'valid' => false,
                'errors' => ["Transporteur non configuré: {$carrier}"]
            ];
        }

        $errors = [];
        $requiredFields = $carrierConfig['required_fields'] ?? [];

        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                $errors[] = "Champ requis manquant: {$field}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'carrier_info' => $carrierConfig
        ];
    }
}