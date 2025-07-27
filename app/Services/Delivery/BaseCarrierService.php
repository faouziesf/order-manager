<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierApiException;
use App\Services\Delivery\Contracts\CarrierValidationException;
use App\Models\DeliveryConfiguration;
use App\Models\Order;
use App\Models\Region;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

/**
 * Service de base abstrait pour tous les transporteurs
 * 
 * Contient la logique commune à tous les services de transporteurs
 * pour éviter la duplication de code
 */
abstract class BaseCarrierService implements CarrierServiceInterface
{
    /**
     * Configuration du transporteur depuis le fichier config
     */
    protected array $carrierConfig;

    /**
     * Slug du transporteur
     */
    protected string $carrierSlug;

    /**
     * Fonctionnalités supportées par ce transporteur
     */
    protected array $supportedFeatures = [
        'create_shipment' => true,
        'track_shipment' => true,
        'test_connection' => true,
        'cancel_shipment' => false,
        'multiple_tracking' => true,
        'pickup_support' => false,
        'webhook_support' => false,
        'label_generation' => false,
        'cost_calculation' => false,
    ];

    public function __construct()
    {
        $this->carrierConfig = config('carriers.' . $this->getCarrierSlug());
        $this->carrierSlug = $this->getCarrierSlug();

        if (!$this->carrierConfig) {
            throw new CarrierServiceException(
                "Configuration manquante pour le transporteur: {$this->carrierSlug}"
            );
        }
    }

    /**
     * Obtenir le slug du transporteur (à implémenter par les classes filles)
     */
    abstract protected function getCarrierSlug(): string;

    /**
     * Implémenter la création d'expédition spécifique au transporteur
     */
    abstract protected function doCreateShipment(array $shipmentData, DeliveryConfiguration $config): array;

    /**
     * Implémenter le suivi spécifique au transporteur
     */
    abstract protected function doTrackShipment(string $trackingNumber, DeliveryConfiguration $config): array;

    // ========================================
    // IMPLÉMENTATION DE L'INTERFACE
    // ========================================

    public function getCarrierInfo(): array
    {
        return [
            'slug' => $this->carrierSlug,
            'name' => $this->carrierConfig['name'],
            'description' => $this->carrierConfig['description'] ?? '',
            'website' => $this->carrierConfig['website'] ?? '',
            'support_phone' => $this->carrierConfig['support_phone'] ?? '',
            'support_email' => $this->carrierConfig['support_email'] ?? '',
        ];
    }

    public function getCarrierLimits(): array
    {
        return $this->carrierConfig['limits'] ?? [];
    }

    public function supportsFeature(string $feature): bool
    {
        return $this->supportedFeatures[$feature] ?? false;
    }

    public function getSupportedGovernorates(): array
    {
        return $this->carrierConfig['governorate_mapping'] ?? [];
    }

    public function mapCarrierStatusToInternal(string $carrierStatus, DeliveryConfiguration $config): string
    {
        $mapping = $this->carrierConfig['status_mapping'] ?? [];
        return $mapping[$carrierStatus] ?? 'unknown';
    }

    // ========================================
    // MÉTHODES COMMUNES
    // ========================================

    public function validateOrderData(Order $order, DeliveryConfiguration $config): array
    {
        $errors = [];

        // Validation des champs requis
        $requiredFields = $this->carrierConfig['shipment_structure']['required_fields'] ?? [];
        
        foreach ($requiredFields as $field) {
            $value = $this->getOrderFieldValue($order, $field);
            if (empty($value)) {
                $errors[] = "Champ requis manquant: {$field}";
            }
        }

        // Validation des limites
        $limits = $this->getCarrierLimits();
        
        if (isset($limits['max_weight'])) {
            $weight = $this->calculateOrderWeight($order);
            if ($weight > $limits['max_weight']) {
                $errors[] = "Poids dépassé: {$weight}kg (max: {$limits['max_weight']}kg)";
            }
        }

        if (isset($limits['max_cod_amount'])) {
            if ($order->total_price > $limits['max_cod_amount']) {
                $errors[] = "Montant COD dépassé: {$order->total_price} TND (max: {$limits['max_cod_amount']} TND)";
            }
        }

        // Validation du gouvernorat
        $governorateMapping = $this->getSupportedGovernorates();
        if (!isset($governorateMapping[$order->customer_governorate])) {
            $errors[] = "Gouvernorat non supporté: {$order->customer_governorate}";
        }

        // Validation de l'adresse
        if (isset($limits['max_address_length'])) {
            if (strlen($order->customer_address) > $limits['max_address_length']) {
                $errors[] = "Adresse trop longue (max: {$limits['max_address_length']} caractères)";
            }
        }

        return $errors;
    }

    public function createShipment(Order $order, DeliveryConfiguration $config, array $additionalData = []): array
    {
        try {
            // Valider les données
            $validationErrors = $this->validateOrderData($order, $config);
            if (!empty($validationErrors)) {
                throw new CarrierValidationException(
                    'Données de commande invalides',
                    $validationErrors
                );
            }

            // Préparer les données d'expédition
            $shipmentData = $this->prepareShipmentData($order, $config, $additionalData);
            
            // Appeler l'implémentation spécifique
            $result = $this->doCreateShipment($shipmentData, $config);

            // Logger le succès
            Log::info("Expédition créée avec succès", [
                'carrier' => $this->carrierSlug,
                'order_id' => $order->id,
                'tracking_number' => $result['tracking_number'] ?? null,
            ]);

            return $result;

        } catch (CarrierValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erreur création expédition", [
                'carrier' => $this->carrierSlug,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierServiceException(
                "Erreur lors de la création de l'expédition: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function trackShipment(string $trackingNumber, DeliveryConfiguration $config): array
    {
        try {
            $result = $this->doTrackShipment($trackingNumber, $config);

            // Mapper le statut
            if (isset($result['carrier_status'])) {
                $result['internal_status'] = $this->mapCarrierStatusToInternal(
                    $result['carrier_status'], 
                    $config
                );
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Erreur suivi expédition", [
                'carrier' => $this->carrierSlug,
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierServiceException(
                "Erreur lors du suivi: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function trackMultipleShipments(array $trackingNumbers, DeliveryConfiguration $config): array
    {
        $results = [];

        foreach ($trackingNumbers as $trackingNumber) {
            try {
                $results[$trackingNumber] = $this->trackShipment($trackingNumber, $config);
            } catch (\Exception $e) {
                $results[$trackingNumber] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    // ========================================
    // MÉTHODES UTILITAIRES PROTÉGÉES
    // ========================================

    /**
     * Effectuer une requête HTTP avec gestion d'erreurs
     */
    protected function makeHttpRequest(
        string $method, 
        string $url, 
        array $data = [], 
        array $headers = [],
        int $timeout = null
    ): Response {
        $timeout = $timeout ?? $this->carrierConfig['api']['timeout'] ?? 30;
        
        $http = Http::timeout($timeout);
        
        // Ajouter les headers
        if (!empty($headers)) {
            $http = $http->withHeaders($headers);
        }

        try {
            $response = match(strtoupper($method)) {
                'GET' => $http->get($url, $data),
                'POST' => $http->post($url, $data),
                'PUT' => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
                default => throw new \InvalidArgumentException("Méthode HTTP non supportée: {$method}")
            };

            Log::debug("Requête HTTP effectuée", [
                'carrier' => $this->carrierSlug,
                'method' => $method,
                'url' => $url,
                'status' => $response->status(),
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error("Erreur requête HTTP", [
                'carrier' => $this->carrierSlug,
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierApiException(
                "Erreur de communication avec l'API: " . $e->getMessage(),
                0,
                null,
                null,
                0,
                $e
            );
        }
    }

    /**
     * Préparer les headers d'authentification
     */
    protected function prepareAuthHeaders(DeliveryConfiguration $config): array
    {
        $credentials = $config->getApiCredentials();
        
        if (!$credentials) {
            throw new CarrierServiceException("Credentials manquants pour l'authentification");
        }

        return [
            $credentials['header_name'] => $credentials['header_value'],
        ];
    }

    /**
     * Préparer les données d'expédition
     */
    protected function prepareShipmentData(Order $order, DeliveryConfiguration $config, array $additionalData = []): array
    {
        $governorateMapping = $this->getSupportedGovernorates();
        $mappedGovernorate = $governorateMapping[$order->customer_governorate] ?? null;
        
        if (!$mappedGovernorate) {
            throw new CarrierValidationException(
                "Gouvernorat non supporté: {$order->customer_governorate}"
            );
        }

        $defaults = $this->carrierConfig['defaults'] ?? [];
        
        return array_merge($defaults, [
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_phone_2' => $order->customer_phone_2,
            'recipient_address' => $order->customer_address,
            'cod_amount' => $order->total_price,
            'weight' => $additionalData['weight'] ?? $this->calculateOrderWeight($order),
            'nb_pieces' => $additionalData['nb_pieces'] ?? $order->items->sum('quantity'),
            'content_description' => $additionalData['content_description'] ?? $this->generateContentDescription($order),
        ], $additionalData);
    }

    /**
     * Calculer le poids d'une commande
     */
    protected function calculateOrderWeight(Order $order): float
    {
        $itemsCount = $order->items->sum('quantity');
        return max(1.0, $itemsCount * 0.5); // 0.5kg par article minimum
    }

    /**
     * Générer la description du contenu
     */
    protected function generateContentDescription(Order $order): string
    {
        $items = $order->items->take(3)->pluck('product.name')->filter()->toArray();
        $description = implode(', ', $items);
        
        if ($order->items->count() > 3) {
            $description .= ' et ' . ($order->items->count() - 3) . ' autres articles';
        }
        
        $maxLength = $this->carrierConfig['limits']['max_content_length'] ?? 255;
        return substr($description ?: 'Commande e-commerce', 0, $maxLength);
    }

    /**
     * Obtenir la valeur d'un champ de commande
     */
    protected function getOrderFieldValue(Order $order, string $field): mixed
    {
        return match($field) {
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => $order->customer_address,
            'governorate_code', 'governorate_name' => $order->customer_governorate,
            'delegation', 'location' => $order->customer_city,
            'cod_amount' => $order->total_price,
            'content_description' => $this->generateContentDescription($order),
            default => null,
        };
    }

    // ========================================
    // MÉTHODES STUBS (À IMPLÉMENTER SI NÉCESSAIRE)
    // ========================================

    public function cancelShipment(string $trackingNumber, DeliveryConfiguration $config, string $reason = ''): array
    {
        if (!$this->supportsFeature('cancel_shipment')) {
            throw new CarrierServiceException("L'annulation n'est pas supportée par ce transporteur");
        }

        // À implémenter dans les classes filles si supporté
        throw new CarrierServiceException("Fonctionnalité non implémentée");
    }

    public function getShipmentDetails(string $trackingNumber, DeliveryConfiguration $config): array
    {
        // Par défaut, utiliser le tracking standard
        return $this->trackShipment($trackingNumber, $config);
    }

    public function generateShipmentLabel(string $trackingNumber, DeliveryConfiguration $config): array
    {
        if (!$this->supportsFeature('label_generation')) {
            throw new CarrierServiceException("La génération d'étiquettes n'est pas supportée par ce transporteur");
        }

        throw new CarrierServiceException("Fonctionnalité non implémentée");
    }

    public function calculateShippingCost(Order $order, DeliveryConfiguration $config): array
    {
        if (!$this->supportsFeature('cost_calculation')) {
            throw new CarrierServiceException("Le calcul des frais n'est pas supporté par ce transporteur");
        }

        throw new CarrierServiceException("Fonctionnalité non implémentée");
    }
}