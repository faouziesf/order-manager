<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\PickupAddress;

interface ShippingServiceInterface
{
    /**
     * Créer une expédition avec le transporteur
     */
    public function createShipment(Order $order, ?PickupAddress $pickupAddress): array;

    /**
     * Obtenir les étiquettes en masse pour plusieurs expéditions
     */
    public function getMassLabels(array $posBarcodes): array;

    /**
     * Suivre une expédition
     */
    public function trackShipment(string $trackingCode): ?array;

    /**
     * Obtenir un token d'authentification
     */
    public function getToken(): array;

    /**
     * Vérifier si ce transporteur supporte la sélection d'adresse d'enlèvement
     */
    public function supportsPickupAddressSelection(): bool;

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getPaymentMethods(): array;

    /**
     * Obtenir les points de dépôt disponibles
     */
    public function getDropPoints(?string $city = null): array;

    /**
     * Obtenir les motifs d'anomalie
     */
    public function getAnomalyReasons(): array;

    /**
     * Valider les données d'expédition avant création
     */
    public function validateShipmentData(array $data): array;

    /**
     * Calculer le coût d'expédition (si supporté)
     */
    public function calculateShippingCost(array $shipmentData): ?float;

    /**
     * Obtenir les informations de statut du transporteur
     */
    public function getCarrierInfo(): array;
}