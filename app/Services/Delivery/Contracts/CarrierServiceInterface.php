<?php

namespace App\Services\Delivery\Contracts;

/**
 * Interface simple pour les transporteurs
 */
interface CarrierServiceInterface
{
    /**
     * Créer un colis
     * @param array $data Données du colis
     * @return array ['success' => bool, 'tracking_number' => string, 'response' => array]
     */
    public function createShipment(array $data): array;

    /**
     * Créer un pickup (si supporté)
     * @param array $data Données du pickup
     * @return array ['success' => bool, 'pickup_id' => string, 'response' => array]
     */
    public function createPickup(array $data): array;

    /**
     * Obtenir le statut d'un colis
     * @param string $trackingNumber
     * @return array ['success' => bool, 'status' => string, 'response' => array]
     */
    public function getShipmentStatus(string $trackingNumber): array;

    /**
     * Test de connexion
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;
}

/**
 * Exceptions simples
 */
class CarrierServiceException extends \Exception
{
    protected $carrierResponse;

    public function __construct(string $message = "", int $code = 0, $carrierResponse = null, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->carrierResponse = $carrierResponse;
    }

    public function getCarrierResponse()
    {
        return $this->carrierResponse;
    }
}