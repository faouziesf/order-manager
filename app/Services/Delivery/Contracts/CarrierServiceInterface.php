<?php

namespace App\Services\Delivery\Contracts;

/**
 * Interface pour tous les services de transporteurs
 */
interface CarrierServiceInterface
{
    /**
     * Créer un colis/shipment chez le transporteur
     * 
     * @param array $shipmentData Données du colis
     * @return array Résultat avec tracking_number, etc.
     */
    public function createShipment(array $shipmentData): array;

    /**
     * Créer un pickup/enlèvement chez le transporteur
     * 
     * @param array $pickupData Données de l'enlèvement
     * @return array Résultat avec pickup_id, etc.
     */
    public function createPickup(array $pickupData): array;

    /**
     * Obtenir le statut d'un colis
     * 
     * @param string $trackingNumber Numéro de suivi
     * @return array Statut et informations
     */
    public function getShipmentStatus(string $trackingNumber): array;

    /**
     * Tester la connexion avec le transporteur
     * 
     * @return array Résultat du test
     */
    public function testConnection(): array;
}

/**
 * Exception pour les erreurs de service transporteur
 */
class CarrierServiceException extends \Exception
{
    protected $carrierResponse;
    protected $carrierCode;

    public function __construct(
        string $message = "",
        int $code = 0,
        $carrierResponse = null,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->carrierResponse = $carrierResponse;
    }

    public function getCarrierResponse()
    {
        return $this->carrierResponse;
    }

    public function getCarrierCode(): ?string
    {
        return $this->carrierCode;
    }

    public function setCarrierCode(?string $code): self
    {
        $this->carrierCode = $code;
        return $this;
    }
}

/**
 * Exception pour les erreurs de validation transporteur
 */
class CarrierValidationException extends CarrierServiceException
{
    protected $validationErrors;

    public function __construct(
        string $message = "",
        int $code = 422,
        array $validationErrors = [],
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $validationErrors, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}

/**
 * Factory pour créer les services de transporteurs
 */
class ShippingServiceFactory
{
    /**
     * Créer un service transporteur
     * 
     * @param string $carrierSlug Identifiant du transporteur
     * @param array $config Configuration du transporteur
     * @return CarrierServiceInterface
     * @throws \InvalidArgumentException
     */
    public function create(string $carrierSlug, array $config): CarrierServiceInterface
    {
        switch ($carrierSlug) {
            case 'jax_delivery':
                if (!class_exists(\App\Services\Delivery\JaxDeliveryService::class)) {
                    throw new \InvalidArgumentException("Classe JaxDeliveryService non trouvée");
                }
                return new \App\Services\Delivery\JaxDeliveryService($config);
                
            case 'mes_colis':
                if (!class_exists(\App\Services\Delivery\MesColisService::class)) {
                    throw new \InvalidArgumentException("Classe MesColisService non trouvée");
                }
                return new \App\Services\Delivery\MesColisService($config);
                
            default:
                throw new \InvalidArgumentException("Transporteur non supporté: {$carrierSlug}. Transporteurs supportés: " . implode(', ', array_keys($this->getSupportedCarriers())));
        }
    }

    /**
     * Obtenir la liste des transporteurs supportés
     * 
     * @return array
     */
    public function getSupportedCarriers(): array
    {
        return [
            'jax_delivery' => [
                'name' => 'JAX Delivery',
                'service_class' => \App\Services\Delivery\JaxDeliveryService::class,
                'available' => class_exists(\App\Services\Delivery\JaxDeliveryService::class),
            ],
            'mes_colis' => [
                'name' => 'Mes Colis Express',
                'service_class' => \App\Services\Delivery\MesColisService::class,
                'available' => class_exists(\App\Services\Delivery\MesColisService::class),
            ],
        ];
    }

    /**
     * Vérifier si un transporteur est supporté
     * 
     * @param string $carrierSlug
     * @return bool
     */
    public function isSupported(string $carrierSlug): bool
    {
        $supported = $this->getSupportedCarriers();
        return array_key_exists($carrierSlug, $supported) && ($supported[$carrierSlug]['available'] ?? false);
    }

    /**
     * Obtenir les informations d'un transporteur
     * 
     * @param string $carrierSlug
     * @return array|null
     */
    public function getCarrierInfo(string $carrierSlug): ?array
    {
        $supported = $this->getSupportedCarriers();
        return $supported[$carrierSlug] ?? null;
    }

    /**
     * Tester la création de tous les services
     * 
     * @return array
     */
    public function testAllServices(): array
    {
        $results = [];
        $testConfig = ['api_key' => 'test_token', 'environment' => 'test'];
        
        foreach ($this->getSupportedCarriers() as $slug => $info) {
            try {
                if ($info['available']) {
                    $service = $this->create($slug, $testConfig);
                    $results[$slug] = [
                        'success' => true,
                        'service_class' => get_class($service),
                        'implements_interface' => $service instanceof CarrierServiceInterface,
                    ];
                } else {
                    $results[$slug] = [
                        'success' => false,
                        'error' => 'Classe de service non disponible',
                        'expected_class' => $info['service_class'],
                    ];
                }
            } catch (\Exception $e) {
                $results[$slug] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'expected_class' => $info['service_class'],
                ];
            }
        }
        
        return $results;
    }
}