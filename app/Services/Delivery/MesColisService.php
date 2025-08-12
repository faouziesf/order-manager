<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MesColisService implements CarrierServiceInterface
{
    protected $baseUrl = 'https://api.mescolis.tn/api';
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Créer un colis (shipment) dans Mes Colis Express
     */
    public function createShipment(array $shipmentData): array
    {
        Log::info('🚀 [MES COLIS] Création colis', [
            'shipment_data' => $shipmentData
        ]);

        try {
            // Valider les données requises
            $this->validateShipmentData($shipmentData);

            // Préparer les données pour Mes Colis API
            $mesColisData = $this->prepareMesColisShipmentData($shipmentData);

            Log::info('📤 [MES COLIS] Envoi vers API', [
                'url' => $this->baseUrl . '/orders/Create',
                'data' => $mesColisData
            ]);

            // Appel à l'API Mes Colis Express
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $this->getToken(),
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/orders/Create', $mesColisData);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur Mes Colis Express (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();

            Log::info('✅ [MES COLIS] Colis créé', [
                'response' => $responseData
            ]);

            // Mes Colis Express retourne probablement un barcode
            return [
                'success' => true,
                'tracking_number' => $responseData['barcode'] ?? $responseData['id'] ?? null,
                'carrier_response' => $responseData,
                'carrier_id' => $responseData['barcode'] ?? $responseData['id'] ?? null,
            ];

        } catch (CarrierServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur création colis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new CarrierServiceException(
                'Erreur lors de la création du colis Mes Colis Express: ' . $e->getMessage(),
                500,
                ['original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * Créer un pickup dans Mes Colis Express
     */
    public function createPickup(array $pickupData): array
    {
        Log::info('🚀 [MES COLIS] Création pickup', [
            'pickup_data' => $pickupData
        ]);

        try {
            // Mes Colis Express n'a pas d'API spécifique pour les pickups
            // On simule la création en validant tous les colis
            $trackingNumbers = $pickupData['tracking_numbers'] ?? [];
            
            if (empty($trackingNumbers)) {
                throw new CarrierValidationException(
                    'Aucun numéro de suivi fourni pour le pickup Mes Colis Express',
                    422,
                    ['tracking_numbers']
                );
            }

            Log::info('✅ [MES COLIS] Pickup simulé (pas d\'API dédiée)', [
                'tracking_numbers' => $trackingNumbers
            ]);

            return [
                'success' => true,
                'pickup_id' => 'PICKUP_' . time(),
                'carrier_response' => [
                    'message' => 'Pickup simulé - Mes Colis Express n\'a pas d\'API pickup dédiée',
                    'tracking_numbers' => $trackingNumbers,
                ],
            ];

        } catch (CarrierServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur création pickup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new CarrierServiceException(
                'Erreur lors de la création du pickup Mes Colis Express: ' . $e->getMessage(),
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
        Log::info('🔍 [MES COLIS] Récupération statut', [
            'tracking_number' => $trackingNumber
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $this->getToken(),
                'Accept' => 'application/json',
            ])->timeout(15)->post($this->baseUrl . '/orders/GetOrder', [
                'barcode' => $trackingNumber
            ]);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur Mes Colis Express statut (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();

            Log::info('✅ [MES COLIS] Statut récupéré', [
                'tracking_number' => $trackingNumber,
                'response' => $responseData
            ]);

            return [
                'success' => true,
                'status' => $this->mapMesColisStatusToInternal($responseData['status'] ?? 'unknown'),
                'carrier_status' => $responseData['status'] ?? 'unknown',
                'carrier_response' => $responseData,
                'last_update' => now(),
            ];

        } catch (CarrierServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur récupération statut', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            throw new CarrierServiceException(
                'Erreur lors de la récupération du statut Mes Colis Express: ' . $e->getMessage(),
                500,
                ['original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * Préparer les données pour l'API Mes Colis (shipment)
     */
    protected function prepareMesColisShipmentData(array $shipmentData): array
    {
        $recipientInfo = $shipmentData['recipient_info'] ?? [];

        return [
            'product_name' => $shipmentData['content_description'] ?? 'Produits e-commerce',
            'client_name' => $recipientInfo['name'] ?? 'Client',
            'address' => $recipientInfo['address'] ?? '',
            'gouvernerate' => $this->mapGovernorateToMesColis($recipientInfo['governorate'] ?? ''),
            'city' => $recipientInfo['city'] ?? '',
            'location' => $recipientInfo['location'] ?? $recipientInfo['address'] ?? '',
            'Tel1' => $recipientInfo['phone'] ?? '',
            'Tel2' => $recipientInfo['phone_2'] ?? '',
            'price' => (string)($shipmentData['cod_amount'] ?? 0),
            'exchange' => $shipmentData['exchange'] ?? '0',
            'open_ordre' => $shipmentData['open_order'] ?? '0',
            'note' => $shipmentData['notes'] ?? '',
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
                'Données manquantes pour Mes Colis Express: ' . implode(', ', $missing),
                422,
                $missing
            );
        }

        // Valider les informations du destinataire
        $recipientInfo = $data['recipient_info'];
        $requiredRecipient = ['name', 'phone', 'address', 'governorate'];
        $missingRecipient = [];

        foreach ($requiredRecipient as $field) {
            if (empty($recipientInfo[$field])) {
                $missingRecipient[] = $field;
            }
        }

        if (!empty($missingRecipient)) {
            throw new CarrierValidationException(
                'Informations destinataire manquantes pour Mes Colis Express: ' . implode(', ', $missingRecipient),
                422,
                $missingRecipient
            );
        }

        // Valider le gouvernorat en utilisant la config
        $carrierConfig = config('carriers.mes_colis');
        $validGovernorates = $carrierConfig['valid_governorates'] ?? [];

        if (!in_array($recipientInfo['governorate'], $validGovernorates)) {
            throw new CarrierValidationException(
                'Gouvernorat invalide pour Mes Colis Express: ' . $recipientInfo['governorate'],
                422,
                ['valid_governorates' => $validGovernorates]
            );
        }
    }

    /**
     * Valider les données de pickup
     */
    protected function validatePickupData(array $data): void
    {
        $required = ['tracking_numbers'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new CarrierValidationException(
                'Données de pickup manquantes pour Mes Colis Express: ' . implode(', ', $missing),
                422,
                $missing
            );
        }

        if (empty($data['tracking_numbers']) || !is_array($data['tracking_numbers'])) {
            throw new CarrierValidationException(
                'Liste des numéros de suivi requise pour Mes Colis Express',
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
        // Essayer d'abord api_key, puis username selon la config
        $token = $this->config['api_key'] ?? $this->config['username'] ?? null;
        
        if (!$token) {
            $carrierConfig = config('carriers.mes_colis');
            $mapping = $carrierConfig['config_mapping'] ?? [];
            
            // Utiliser le mapping pour trouver le bon champ
            if (isset($mapping['api_token'])) {
                $token = $this->config[$mapping['api_token']] ?? null;
            }
        }
        
        if (!$token) {
            throw new CarrierServiceException(
                'Token d\'authentification Mes Colis Express manquant',
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
     * Mapper les gouvernorats vers les noms Mes Colis en utilisant la config
     */
    protected function mapGovernorateToMesColis($governorate): string
    {
        $carrierConfig = config('carriers.mes_colis');
        $mapping = $carrierConfig['governorate_mapping'] ?? [];
        
        // Si c'est un ID numérique, le convertir directement
        if (is_numeric($governorate)) {
            $governorate = (int)$governorate;
        }
        
        return $mapping[$governorate] ?? 'Tunis'; // Par défaut Tunis
    }

    /**
     * Mapper les statuts Mes Colis vers les statuts internes en utilisant la config
     */
    protected function mapMesColisStatusToInternal($mesColisStatus): string
    {
        $carrierConfig = config('carriers.mes_colis');
        $mapping = $carrierConfig['status_mapping'] ?? [];
        
        // Convertir en string pour la comparaison
        $mesColisStatus = (string)$mesColisStatus;
        
        return $mapping[$mesColisStatus] ?? 'unknown';
    }

    /**
     * Test de connexion en utilisant l'endpoint configuré
     */
    public function testConnection(): array
    {
        try {
            // Utiliser l'endpoint de test configuré
            $carrierConfig = config('carriers.mes_colis');
            $testEndpoint = $carrierConfig['endpoints']['test_connection'] ?? '/orders/GetOrder';
            
            // Test simple en tentant de récupérer le statut d'un colis inexistant
            // Cela nous permettra de vérifier que l'authentification fonctionne
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $this->getToken(),
                'Accept' => 'application/json',
            ])->timeout(10)->post($this->baseUrl . $testEndpoint, [
                'barcode' => 'TEST_CONNECTION_' . time()
            ]);

            // Même si l'ordre n'existe pas, une réponse 200 indique que l'auth fonctionne
            if ($response->status() === 200 || $response->status() === 404) {
                return [
                    'success' => true,
                    'message' => 'Connexion Mes Colis Express réussie',
                    'response_time' => $response->transferStats?->getTransferTime() ?? 0,
                    'environment' => $this->config['environment'] ?? 'unknown',
                    'endpoint_tested' => $testEndpoint,
                ];
            }

            throw new CarrierServiceException(
                "Test de connexion Mes Colis échoué (HTTP {$response->status()}): " . $response->body(),
                $response->status()
            );

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec connexion Mes Colis Express: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}