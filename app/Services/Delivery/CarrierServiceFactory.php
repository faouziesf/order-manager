<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;

class CarrierServiceFactory
{
    /**
     * Créer un service transporteur
     */
    public static function make(string $carrierSlug, array $config): CarrierServiceInterface
    {
        switch (strtolower($carrierSlug)) {
            case 'jax_delivery':
                return new JaxDeliveryService($config);
                
            case 'mes_colis':
                return new MesColisService($config);
                
            default:
                throw new \InvalidArgumentException("Transporteur non supporté: {$carrierSlug}");
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
     * Tester si un transporteur est supporté
     */
    public static function isSupported(string $carrierSlug): bool
    {
        return array_key_exists(strtolower($carrierSlug), self::getSupportedCarriers());
    }
}