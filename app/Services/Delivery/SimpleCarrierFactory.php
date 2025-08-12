<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;

/**
 * Factory simple pour créer les services de transporteurs
 */
class SimpleCarrierFactory
{
    /**
     * Créer un service transporteur
     */
    public static function create(string $carrierSlug, array $config): CarrierServiceInterface
    {
        switch ($carrierSlug) {
            case 'jax_delivery':
                return new JaxDeliveryService($config);
                
            case 'mes_colis':
                return new MesColisService($config);
                
            default:
                throw new CarrierServiceException("Transporteur non supporté: {$carrierSlug}");
        }
    }

    /**
     * Obtenir les transporteurs supportés
     */
    public static function getSupportedCarriers(): array
    {
        return [
            'jax_delivery' => 'JAX Delivery',
            'mes_colis' => 'Mes Colis Express',
        ];
    }

    /**
     * Vérifier si un transporteur est supporté
     */
    public static function isSupported(string $carrierSlug): bool
    {
        return array_key_exists($carrierSlug, self::getSupportedCarriers());
    }
}