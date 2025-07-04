<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\PickupAddress;

interface CarrierServiceInterface
{
    /**
     * Créer une expédition
     */
    public function createShipment(Order $order, ?PickupAddress $pickupAddress = null): array;

    /**
     * Suivre une expédition
     */
    public function trackShipment(string $trackingNumber): ?array;

    /**
     * Tester la connexion
     */
    public function testConnection(): array;

    /**
     * Obtenir le token d'authentification
     */
    public function getToken(): array;

    /**
     * Annuler une expédition
     */
    public function cancelShipment(string $trackingNumber): bool;

    // ========================================
    // MÉTHODES DE CAPACITÉS
    // ========================================

    /**
     * Le transporteur supporte-t-il les adresses d'enlèvement personnalisées ?
     */
    public function supportsPickupAddressSelection(): bool;

    /**
     * Le transporteur supporte-t-il la génération de BL ?
     */
    public function supportsBLGeneration(): bool;

    /**
     * Le transporteur supporte-t-il la génération d'étiquettes en masse ?
     */
    public function supportsLabelGeneration(): bool;

    /**
     * Le transporteur supporte-t-il les points de dépôt ?
     */
    public function supportsDropPoints(): bool;

    /**
     * Le transporteur supporte-t-il la planification d'enlèvement ?
     */
    public function supportsPickupScheduling(): bool;

    /**
     * Obtenir les informations du transporteur
     */
    public function getCarrierInfo(): array;

    // ========================================
    // MÉTHODES CONDITIONNELLES
    // ========================================

    /**
     * Générer des étiquettes en masse (si supporté)
     */
    public function getMassLabels(array $trackingCodes): array;

    /**
     * Obtenir les méthodes de paiement (si supporté)
     */
    public function getPaymentMethods(): array;

    /**
     * Obtenir les points de dépôt (si supporté)
     */
    public function getDropPoints(): array;

    /**
     * Obtenir les motifs d'anomalie (si supporté)
     */
    public function getAnomalyReasons(): array;

    /**
     * Programmer un enlèvement (si supporté)
     */
    public function schedulePickup(array $shipments, \DateTime $pickupDate): array;
}