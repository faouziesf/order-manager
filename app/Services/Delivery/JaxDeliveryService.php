<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierApiException;
use App\Services\Delivery\Contracts\CarrierValidationException;
use App\Models\DeliveryConfiguration;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Service d'intégration avec JAX Delivery
 * 
 * API: https://core.jax-delivery.com/api
 * Authentification: Bearer Token JWT (permanent)
 * Documentation: Utilise codes numériques gouvernorats (1-24)
 */
class JaxDeliveryService extends BaseCarrierService
{
    protected function getCarrierSlug(): string
    {
        return 'jax_delivery';
    }

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

    // ========================================
    // IMPLÉMENTATION SPÉCIFIQUE JAX DELIVERY
    // ========================================

    public function testConnection(DeliveryConfiguration $config): array
    {
        try {
            // Valider les credentials de base
            $credentials = $config->getApiCredentials();
            if (!$credentials || empty($credentials['account_number']) || empty($credentials['api_token'])) {
                return [
                    'success' => false,
                    'error' => 'Numéro de compte ou token API manquant',
                    'details' => [
                        'test_time' => now()->toISOString(),
                        'carrier' => 'JAX Delivery',
                    ]
                ];
            }

            // Tester avec un colis fictif
            $testData = $this->prepareTestShipmentData($config);
            $headers = $this->prepareAuthHeaders($config);

            Log::info("Test connexion JAX Delivery", [
                'config_id' => $config->id,
                'account_number' => $credentials['account_number'],
                'api_url' => $config->getApiEndpoint('create_shipment'),
            ]);

            $response = $this->makeHttpRequest(
                'POST',
                $config->getApiEndpoint('create_shipment'),
                $testData,
                $headers,
                10 // timeout court pour test
            );

            if ($response->successful()) {
                $data = $response->json();
                
                // JAX renvoie généralement un objet avec un ID de colis
                if (isset($data['id']) || isset($data['colis_id']) || isset($data['tracking_number'])) {
                    return [
                        'success' => true,
                        'message' => 'Connexion JAX Delivery réussie',
                        'details' => [
                            'carrier' => 'JAX Delivery',
                            'account_number' => $credentials['account_number'],
                            'api_url' => $config->getApiBaseUrl(),
                            'test_time' => now()->toISOString(),
                            'test_response' => [
                                'status' => $response->status(),
                                'tracking_id' => $data['id'] ?? $data['colis_id'] ?? $data['tracking_number'] ?? 'TEST_ID',
                            ]
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Réponse API JAX inattendue: ' . json_encode($data),
                        'details' => [
                            'status_code' => $response->status(),
                            'response_data' => $data,
                        ]
                    ];
                }
            } else {
                $errorData = $response->json();
                $errorMessage = $this->extractJaxErrorMessage($errorData);
                
                return [
                    'success' => false,
                    'error' => "Erreur JAX Delivery: {$errorMessage}",
                    'details' => [
                        'status_code' => $response->status(),
                        'error_data' => $errorData,
                        'test_time' => now()->toISOString(),
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error("Erreur test connexion JAX Delivery", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur de communication: ' . $e->getMessage(),
                'details' => [
                    'exception' => get_class($e),
                    'test_time' => now()->toISOString(),
                ]
            ];
        }
    }

    protected function doCreateShipment(array $shipmentData, DeliveryConfiguration $config): array
    {
        try {
            $jaxData = $this->formatDataForJax($shipmentData, $config);
            $headers = $this->prepareAuthHeaders($config);

            Log::info("Création expédition JAX Delivery", [
                'config_id' => $config->id,
                'jax_data' => $jaxData,
            ]);

            $response = $this->makeHttpRequest(
                'POST',
                $config->getApiEndpoint('create_shipment'),
                $jaxData,
                $headers
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Expédition JAX créée avec succès", [
                    'config_id' => $config->id,
                    'response_data' => $data,
                ]);

                return [
                    'success' => true,
                    'tracking_number' => $data['id'] ?? $data['colis_id'] ?? $data['ean'] ?? $data['tracking_number'] ?? null,
                    'carrier_reference' => $data['reference'] ?? $data['barcode'] ?? null,
                    'estimated_delivery' => $data['estimated_delivery'] ?? null,
                    'carrier_response' => $data,
                    'details' => [
                        'carrier' => 'JAX Delivery',
                        'api_response' => $data,
                        'created_at' => now()->toISOString(),
                    ]
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $this->extractJaxErrorMessage($errorData);
                
                Log::error("Erreur création expédition JAX", [
                    'config_id' => $config->id,
                    'status_code' => $response->status(),
                    'error_data' => $errorData,
                ]);

                throw new CarrierApiException(
                    "Erreur JAX Delivery: {$errorMessage}",
                    $response->status(),
                    $errorData,
                    $errorData['error_code'] ?? null
                );
            }

        } catch (CarrierApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Exception création expédition JAX", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierServiceException(
                "Erreur lors de la création de l'expédition JAX: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    protected function doTrackShipment(string $trackingNumber, DeliveryConfiguration $config): array
    {
        try {
            $headers = $this->prepareAuthHeaders($config);
            $trackingUrl = str_replace('{ean}', $trackingNumber, $config->getApiEndpoint('track_shipment'));

            Log::info("Suivi expédition JAX Delivery", [
                'config_id' => $config->id,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
            ]);

            $response = $this->makeHttpRequest(
                'GET',
                $trackingUrl,
                [],
                $headers
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Suivi JAX récupéré", [
                    'tracking_number' => $trackingNumber,
                    'response_data' => $data,
                ]);

                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'carrier_status' => $data['status'] ?? 'UNKNOWN',
                    'carrier_status_label' => $data['status_label'] ?? $data['status_description'] ?? 'Statut inconnu',
                    'location' => $data['current_location'] ?? $data['location'] ?? null,
                    'last_update' => $data['last_update'] ?? $data['updated_at'] ?? now()->toISOString(),
                    'estimated_delivery' => $data['estimated_delivery'] ?? null,
                    'delivery_notes' => $data['notes'] ?? $data['comment'] ?? null,
                    'history' => $this->formatJaxHistory($data['history'] ?? []),
                    'carrier_response' => $data,
                    'details' => [
                        'carrier' => 'JAX Delivery',
                        'api_response' => $data,
                        'tracked_at' => now()->toISOString(),
                    ]
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $this->extractJaxErrorMessage($errorData);
                
                Log::warning("Erreur suivi JAX", [
                    'tracking_number' => $trackingNumber,
                    'status_code' => $response->status(),
                    'error_data' => $errorData,
                ]);

                // Si le colis n'est pas trouvé, ce n'est pas forcément une erreur
                if ($response->status() === 404) {
                    return [
                        'success' => false,
                        'error' => 'Colis non trouvé',
                        'tracking_number' => $trackingNumber,
                        'carrier_status' => 'NOT_FOUND',
                        'details' => [
                            'carrier' => 'JAX Delivery',
                            'error_code' => 'NOT_FOUND',
                        ]
                    ];
                }

                throw new CarrierApiException(
                    "Erreur suivi JAX: {$errorMessage}",
                    $response->status(),
                    $errorData,
                    $errorData['error_code'] ?? null
                );
            }

        } catch (CarrierApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Exception suivi JAX", [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierServiceException(
                "Erreur lors du suivi JAX: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    // ========================================
    // MÉTHODES UTILITAIRES JAX DELIVERY
    // ========================================

    /**
     * Formater les données pour l'API JAX
     */
    protected function formatDataForJax(array $shipmentData, DeliveryConfiguration $config): array
    {
        $credentials = $config->getApiCredentials();
        $governorateMapping = $this->getSupportedGovernorates();
        
        // Mapper le gouvernorat (ID → code numérique)
        $governorateCode = null;
        if (isset($shipmentData['governorate_id'])) {
            $governorateCode = $governorateMapping[$shipmentData['governorate_id']] ?? null;
        } elseif (isset($shipmentData['recipient_governorate'])) {
            $governorateCode = $governorateMapping[$shipmentData['recipient_governorate']] ?? null;
        }

        if (!$governorateCode) {
            throw new CarrierValidationException(
                "Gouvernorat non supporté ou manquant pour JAX Delivery"
            );
        }

        // Format attendu par JAX Delivery
        return [
            'compte' => $credentials['account_number'],
            'nom' => $shipmentData['recipient_name'],
            'telephone' => $this->formatPhoneForJax($shipmentData['recipient_phone']),
            'telephone2' => $this->formatPhoneForJax($shipmentData['recipient_phone_2'] ?? ''),
            'adresse' => $shipmentData['recipient_address'],
            'gouvernorat' => $governorateCode, // Code numérique (1-24)
            'delegation' => $shipmentData['delegation'] ?? $shipmentData['recipient_city'],
            'montant' => (float) $shipmentData['cod_amount'],
            'description' => $shipmentData['content_description'],
            'poids' => (float) ($shipmentData['weight'] ?? 1.0),
            'pieces' => (int) ($shipmentData['nb_pieces'] ?? 1),
            'date_enlevement' => $shipmentData['pickup_date'] ?? now()->format('Y-m-d'),
            'commentaire' => $shipmentData['delivery_notes'] ?? '',
        ];
    }

    /**
     * Formater un numéro de téléphone pour JAX
     */
    protected function formatPhoneForJax(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Nettoyer le numéro
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Formats acceptés par JAX: 12345678, 21612345678, +21612345678
        if (strlen($phone) === 8) {
            return $phone; // Format local
        } elseif (strlen($phone) === 11 && str_starts_with($phone, '216')) {
            return $phone; // Format international sans +
        } elseif (strlen($phone) === 12 && str_starts_with($phone, '216')) {
            return substr($phone, 1); // Retirer le + initial
        }
        
        return $phone; // Retourner tel quel si format non reconnu
    }

    /**
     * Extraire le message d'erreur de la réponse JAX
     */
    protected function extractJaxErrorMessage(array $errorData): string
    {
        // JAX peut retourner différents formats d'erreur
        if (isset($errorData['message'])) {
            return $errorData['message'];
        } elseif (isset($errorData['error'])) {
            return $errorData['error'];
        } elseif (isset($errorData['erreur'])) {
            return $errorData['erreur'];
        } elseif (isset($errorData['errors']) && is_array($errorData['errors'])) {
            return implode(', ', $errorData['errors']);
        }
        
        return 'Erreur inconnue de JAX Delivery';
    }

    /**
     * Formater l'historique JAX pour le format standardisé
     */
    protected function formatJaxHistory(array $history): array
    {
        $formattedHistory = [];
        
        foreach ($history as $entry) {
            $formattedHistory[] = [
                'status' => $entry['status'] ?? 'UNKNOWN',
                'label' => $entry['status_label'] ?? $entry['description'] ?? 'Mise à jour',
                'location' => $entry['location'] ?? $entry['lieu'] ?? '',
                'timestamp' => $entry['date'] ?? $entry['created_at'] ?? now()->toISOString(),
                'notes' => $entry['comment'] ?? $entry['commentaire'] ?? '',
            ];
        }
        
        return $formattedHistory;
    }

    /**
     * Préparer des données de test pour la connexion
     */
    protected function prepareTestShipmentData(DeliveryConfiguration $config): array
    {
        return $this->formatDataForJax([
            'recipient_name' => 'Test Client',
            'recipient_phone' => '12345678',
            'recipient_address' => 'Adresse de test',
            'recipient_governorate' => 1, // Tunis
            'recipient_city' => 'Tunis',
            'cod_amount' => 10.0,
            'content_description' => 'Test de connexion',
            'weight' => 1.0,
            'nb_pieces' => 1,
        ], $config);
    }

    // ========================================
    // SURCHARGES POUR JAX DELIVERY
    // ========================================

    public function validateOrderData(Order $order, DeliveryConfiguration $config): array
    {
        $errors = parent::validateOrderData($order, $config);
        
        // Validations spécifiques à JAX
        if (empty($order->customer_city)) {
            $errors[] = 'Délégation/ville manquante (requis pour JAX Delivery)';
        }
        
        // Vérifier le format du téléphone
        $phone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (strlen($phone) < 8) {
            $errors[] = 'Numéro de téléphone invalide pour JAX Delivery';
        }
        
        // Vérifier le gouvernorat
        $governorateMapping = $this->getSupportedGovernorates();
        if (!isset($governorateMapping[$order->customer_governorate])) {
            $errors[] = "Gouvernorat {$order->customer_governorate} non supporté par JAX Delivery";
        }
        
        return $errors;
    }

    protected function prepareShipmentData(Order $order, DeliveryConfiguration $config, array $additionalData = []): array
    {
        $baseData = parent::prepareShipmentData($order, $config, $additionalData);
        
        // Ajouter les champs spécifiques à JAX
        return array_merge($baseData, [
            'recipient_governorate' => $order->customer_governorate,
            'delegation' => $order->customer_city,
            'governorate_id' => $order->customer_governorate,
        ]);
    }
}