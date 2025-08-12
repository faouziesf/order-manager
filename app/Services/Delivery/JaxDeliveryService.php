<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JaxDeliveryService implements CarrierServiceInterface
{
    protected $baseUrl = 'https://core.jax-delivery.com/api';
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Créer un colis (shipment) dans JAX Delivery
     */
    public function createShipment(array $shipmentData): array
    {
        Log::info('🚀 [JAX DELIVERY] Création colis', [
            'shipment_data' => $shipmentData
        ]);

        try {
            // Valider les données requises
            $this->validateShipmentData($shipmentData);

            // Préparer les données pour JAX API
            $jaxData = $this->prepareJaxShipmentData($shipmentData);

            Log::info('📤 [JAX DELIVERY] Envoi vers API', [
                'url' => $this->baseUrl . '/user/colis/add',
                'data' => $jaxData
            ]);

            // Appel à l'API JAX Delivery
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/user/colis/add', $jaxData);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX Delivery (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();

            Log::info('✅ [JAX DELIVERY] Colis créé', [
                'response' => $responseData
            ]);

            // JAX Delivery retourne probablement un ID/EAN du colis
            return [
                'success' => true,
                'tracking_number' => $responseData['ean'] ?? $responseData['id'] ?? null,
                'carrier_response' => $responseData,
                'carrier_id' => $responseData['ean'] ?? $responseData['id'] ?? null,
            ];

        } catch (CarrierServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [JAX DELIVERY] Erreur création colis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new CarrierServiceException(
                'Erreur lors de la création du colis JAX Delivery: ' . $e->getMessage(),
                500,
                ['original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * Créer un pickup dans JAX Delivery
     */
    public function createPickup(array $pickupData): array
    {
        Log::info('🚀 [JAX DELIVERY] Création pickup', [
            'pickup_data' => $pickupData
        ]);

        try {
            // Valider les données requises
            $this->validatePickupData($pickupData);

            // Préparer les données pour JAX API
            $jaxData = $this->prepareJaxPickupData($pickupData);

            Log::info('📤 [JAX DELIVERY] Envoi pickup vers API', [
                'url' => $this->baseUrl . '/client/createByean',
                'data' => $jaxData
            ]);

            // Appel à l'API JAX Delivery pour créer le pickup
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/client/createByean', $jaxData);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX Delivery pickup (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();

            Log::info('✅ [JAX DELIVERY] Pickup créé', [
                'response' => $responseData
            ]);

            return [
                'success' => true,
                'pickup_id' => $responseData['pickup_id'] ?? $responseData['id'] ?? null,
                'carrier_response' => $responseData,
            ];

        } catch (CarrierServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [JAX DELIVERY] Erreur création pickup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new CarrierServiceException(
                'Erreur lors de la création du pickup JAX Delivery: ' . $e->getMessage(),
                500,
                ['original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtenir le statut d'un colis
     */
    public function getShipmentStatus(string $trackingNumber): array
    {
        Log::info('🔍 [JAX DELIVERY] Récupération statut', [
            'tracking_number' => $trackingNumber
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => 'application/json',
            ])->timeout(15)->get($this->baseUrl . "/user/colis/getstatubyean/{$trackingNumber}");

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX Delivery statut (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();

            Log::info('✅ [JAX DELIVERY] Statut récupéré', [
                'tracking_number' => $trackingNumber,
                'response' => $responseData
            ]);

            return [
                'success' => true,
                'status' => $this->mapJaxStatusToInternal($responseData['status'] ?? 'unknown'),
                'carrier_status' => $responseData['status'] ?? 'unknown',
                'carrier_response' => $responseData,
                'last_update' => now(),
            ];

        } catch (CarrierServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [JAX DELIVERY] Erreur récupération statut', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            throw new CarrierServiceException(
                'Erreur lors de la récupération du statut JAX Delivery: ' . $e->getMessage(),
                500,
                ['original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * Préparer les données pour l'API JAX (shipment)
     */
    protected function prepareJaxShipmentData(array $shipmentData): array
    {
        $recipientInfo = $shipmentData['recipient_info'] ?? [];

        return [
            'referenceExterne' => $shipmentData['external_reference'] ?? '',
            'nomContact' => $recipientInfo['name'] ?? 'Client',
            'tel' => $recipientInfo['phone'] ?? '',
            'tel2' => $recipientInfo['phone_2'] ?? '',
            'adresseLivraison' => $recipientInfo['address'] ?? '',
            'governorat' => $this->mapGovernorateToJaxCode($recipientInfo['governorate'] ?? ''),
            'delegation' => $recipientInfo['city'] ?? '',
            'description' => $shipmentData['content_description'] ?? 'Produits e-commerce',
            'cod' => (string)($shipmentData['cod_amount'] ?? 0),
            'echange' => $shipmentData['exchange'] ?? 0,
        ];
    }

    /**
     * Préparer les données pour l'API JAX (pickup)
     */
    protected function prepareJaxPickupData(array $pickupData): array
    {
        return [
            'adresse' => $pickupData['address'] ?? 'Adresse pickup',
            'nbrColis' => (string)($pickupData['shipments_count'] ?? 0),
            'colis_statut' => '10', // Statut par défaut pour les nouveaux colis
            'colis_list' => $pickupData['tracking_numbers'] ?? [],
        ];
    }

    /**
     * Valider les données de shipment
     */
    protected function validateShipmentData(array $data): void
    {
        $required = ['recipient_info', 'cod_amount'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new CarrierValidationException(
                'Données manquantes pour JAX Delivery: ' . implode(', ', $missing),
                422,
                $missing
            );
        }

        // Valider les informations du destinataire
        $recipientInfo = $data['recipient_info'];
        $requiredRecipient = ['name', 'phone', 'address'];
        $missingRecipient = [];

        foreach ($requiredRecipient as $field) {
            if (empty($recipientInfo[$field])) {
                $missingRecipient[] = $field;
            }
        }

        if (!empty($missingRecipient)) {
            throw new CarrierValidationException(
                'Informations destinataire manquantes pour JAX Delivery: ' . implode(', ', $missingRecipient),
                422,
                $missingRecipient
            );
        }
    }

    /**
     * Valider les données de pickup
     */
    protected function validatePickupData(array $data): void
    {
        $required = ['tracking_numbers', 'shipments_count'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new CarrierValidationException(
                'Données de pickup manquantes pour JAX Delivery: ' . implode(', ', $missing),
                422,
                $missing
            );
        }

        if (empty($data['tracking_numbers']) || !is_array($data['tracking_numbers'])) {
            throw new CarrierValidationException(
                'Liste des numéros de suivi requise pour JAX Delivery',
                422,
                ['tracking_numbers']
            );
        }
    }

    /**
     * Obtenir le token d'authentification en utilisant la config
     */
    protected function getToken(): string
    {
        // Essayer d'abord api_key, puis password selon la config
        $token = $this->config['api_key'] ?? $this->config['password'] ?? null;
        
        if (!$token) {
            $carrierConfig = config('carriers.jax_delivery');
            $mapping = $carrierConfig['config_mapping'] ?? [];
            
            // Utiliser le mapping pour trouver le bon champ
            if (isset($mapping['api_token'])) {
                $token = $this->config[$mapping['api_token']] ?? null;
            }
        }
        
        if (!$token) {
            throw new CarrierServiceException(
                'Token d\'authentification JAX Delivery manquant',
                401,
                ['config_keys' => array_keys($this->config)]
            );
        }

        // Déchiffrer si nécessaire
        try {
            return decrypt($token);
        } catch (\Exception $e) {
            // Si le déchiffrement échoue, utiliser tel quel
            return $token;
        }
    }

    /**
     * Mapper les gouvernorats vers les codes JAX en utilisant la config
     */
    protected function mapGovernorateToJaxCode($governorate): string
    {
        $carrierConfig = config('carriers.jax_delivery');
        $mapping = $carrierConfig['governorate_mapping'] ?? [];
        
        // Si c'est un ID numérique, le convertir directement
        if (is_numeric($governorate)) {
            $governorate = (int)$governorate;
        }
        
        return $mapping[$governorate] ?? '11'; // Par défaut Tunis
    }

    /**
     * Mapper les statuts JAX vers les statuts internes en utilisant la config
     */
    protected function mapJaxStatusToInternal($jaxStatus): string
    {
        $carrierConfig = config('carriers.jax_delivery');
        $mapping = $carrierConfig['status_mapping'] ?? [];
        
        // Convertir en string pour la comparaison
        $jaxStatus = (string)$jaxStatus;
        
        return $mapping[$jaxStatus] ?? 'unknown';
    }

    /**
     * Test de connexion en utilisant l'endpoint configuré
     */
    public function testConnection(): array
    {
        try {
            // Utiliser l'endpoint de test configuré
            $carrierConfig = config('carriers.jax_delivery');
            $testEndpoint = $carrierConfig['endpoints']['test_connection'] ?? '/gouvernorats';
            
            // Appel de test vers l'API JAX
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Accept' => 'application/json',
            ])->timeout(10)->get($this->baseUrl . $testEndpoint);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Test de connexion JAX échoué (HTTP {$response->status()}): " . $response->body(),
                    $response->status()
                );
            }

            return [
                'success' => true,
                'message' => 'Connexion JAX Delivery réussie',
                'response_time' => $response->transferStats?->getTransferTime() ?? 0,
                'environment' => $this->config['environment'] ?? 'unknown',
                'endpoint_tested' => $testEndpoint,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec connexion JAX Delivery: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}