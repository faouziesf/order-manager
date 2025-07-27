<?php

namespace App\Services\Delivery\Contracts;

use App\Models\DeliveryConfiguration;
use App\Models\Order;
use App\Models\Shipment;

/**
 * Interface unifiée pour tous les services de transporteurs
 * 
 * Cette interface garantit que tous les transporteurs (JAX Delivery, Mes Colis Express, etc.)
 * implémentent les mêmes méthodes de base pour assurer l'interopérabilité
 */
interface CarrierServiceInterface
{
    /**
     * Tester la connexion avec l'API du transporteur
     * 
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Résultat du test avec success (bool) et détails
     */
    public function testConnection(DeliveryConfiguration $config): array;

    /**
     * Créer une expédition via l'API du transporteur
     * 
     * @param Order $order Commande à expédier
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @param array $additionalData Données supplémentaires optionnelles
     * @return array Résultat avec success (bool), tracking_number, et détails
     */
    public function createShipment(Order $order, DeliveryConfiguration $config, array $additionalData = []): array;

    /**
     * Suivre le statut d'une expédition
     * 
     * @param string $trackingNumber Numéro de suivi
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Statut actuel avec code, label, et métadonnées
     */
    public function trackShipment(string $trackingNumber, DeliveryConfiguration $config): array;

    /**
     * Suivre plusieurs expéditions en lot
     * 
     * @param array $trackingNumbers Liste des numéros de suivi
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Résultats indexés par numéro de suivi
     */
    public function trackMultipleShipments(array $trackingNumbers, DeliveryConfiguration $config): array;

    /**
     * Valider les données d'une commande avant expédition
     * 
     * @param Order $order Commande à valider
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Erreurs de validation (vide si valide)
     */
    public function validateOrderData(Order $order, DeliveryConfiguration $config): array;

    /**
     * Mapper un statut transporteur vers le statut interne
     * 
     * @param string $carrierStatus Statut du transporteur
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return string Statut interne correspondant
     */
    public function mapCarrierStatusToInternal(string $carrierStatus, DeliveryConfiguration $config): string;

    /**
     * Obtenir les informations du transporteur
     * 
     * @return array Nom, slug, et métadonnées du transporteur
     */
    public function getCarrierInfo(): array;

    /**
     * Obtenir les limites et contraintes du transporteur
     * 
     * @return array Poids max, montant COD max, etc.
     */
    public function getCarrierLimits(): array;

    /**
     * Annuler une expédition (si supporté)
     * 
     * @param string $trackingNumber Numéro de suivi
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @param string $reason Raison de l'annulation
     * @return array Résultat de l'annulation
     */
    public function cancelShipment(string $trackingNumber, DeliveryConfiguration $config, string $reason = ''): array;

    /**
     * Obtenir les détails d'expédition (si supporté)
     * 
     * @param string $trackingNumber Numéro de suivi
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Détails complets de l'expédition
     */
    public function getShipmentDetails(string $trackingNumber, DeliveryConfiguration $config): array;

    /**
     * Générer les données pour l'étiquette (si supporté)
     * 
     * @param string $trackingNumber Numéro de suivi
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Données pour l'étiquette (URL PDF, base64, etc.)
     */
    public function generateShipmentLabel(string $trackingNumber, DeliveryConfiguration $config): array;

    /**
     * Vérifier si le transporteur supporte une fonctionnalité
     * 
     * @param string $feature Nom de la fonctionnalité
     * @return bool True si supportée
     */
    public function supportsFeature(string $feature): bool;

    /**
     * Obtenir la liste des gouvernorats supportés
     * 
     * @return array Liste des gouvernorats avec mapping
     */
    public function getSupportedGovernorates(): array;

    /**
     * Calculer les frais de livraison (si supporté)
     * 
     * @param Order $order Commande
     * @param DeliveryConfiguration $config Configuration
     * @return array Frais avec détails
     */
    public function calculateShippingCost(Order $order, DeliveryConfiguration $config): array;
}

/**
 * Interface pour les services avec support de pickup groupé
 */
interface PickupSupportInterface
{
    /**
     * Créer un enlèvement groupé
     * 
     * @param array $orders Liste des commandes
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @param array $pickupData Données de l'enlèvement
     * @return array Résultat avec pickup_id et détails
     */
    public function createPickup(array $orders, DeliveryConfiguration $config, array $pickupData = []): array;

    /**
     * Valider un enlèvement
     * 
     * @param string $pickupId ID de l'enlèvement
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Résultat de la validation
     */
    public function validatePickup(string $pickupId, DeliveryConfiguration $config): array;

    /**
     * Obtenir les détails d'un enlèvement
     * 
     * @param string $pickupId ID de l'enlèvement
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Détails de l'enlèvement
     */
    public function getPickupDetails(string $pickupId, DeliveryConfiguration $config): array;
}

/**
 * Interface pour les services avec notifications webhook
 */
interface WebhookSupportInterface
{
    /**
     * Traiter une notification webhook
     * 
     * @param array $webhookData Données du webhook
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return array Résultat du traitement
     */
    public function processWebhook(array $webhookData, DeliveryConfiguration $config): array;

    /**
     * Valider la signature d'un webhook
     * 
     * @param string $payload Payload du webhook
     * @param string $signature Signature reçue
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @return bool True si valide
     */
    public function validateWebhookSignature(string $payload, string $signature, DeliveryConfiguration $config): bool;

    /**
     * Configurer les webhooks
     * 
     * @param DeliveryConfiguration $config Configuration du transporteur
     * @param string $webhookUrl URL de callback
     * @return array Résultat de la configuration
     */
    public function setupWebhooks(DeliveryConfiguration $config, string $webhookUrl): array;
}

/**
 * Exception personnalisée pour les erreurs de transporteur
 */
class CarrierServiceException extends \Exception
{
    protected $carrierResponse;
    protected $carrierCode;

    public function __construct(
        string $message, 
        int $code = 0, 
        \Throwable $previous = null,
        array $carrierResponse = null,
        string $carrierCode = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->carrierResponse = $carrierResponse;
        $this->carrierCode = $carrierCode;
    }

    public function getCarrierResponse(): ?array
    {
        return $this->carrierResponse;
    }

    public function getCarrierCode(): ?string
    {
        return $this->carrierCode;
    }
}

/**
 * Exception pour les erreurs de configuration
 */
class CarrierConfigurationException extends CarrierServiceException
{
    //
}

/**
 * Exception pour les erreurs de validation
 */
class CarrierValidationException extends CarrierServiceException
{
    protected $validationErrors;

    public function __construct(
        string $message,
        array $validationErrors = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}

/**
 * Exception pour les erreurs d'API
 */
class CarrierApiException extends CarrierServiceException
{
    protected $httpStatusCode;

    public function __construct(
        string $message,
        int $httpStatusCode = 0,
        array $carrierResponse = null,
        string $carrierCode = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous, $carrierResponse, $carrierCode);
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}