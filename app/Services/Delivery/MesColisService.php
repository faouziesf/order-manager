<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierApiException;
use App\Services\Delivery\Contracts\CarrierValidationException;
use App\Models\DeliveryConfiguration;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Service d'intégration avec Mes Colis Express
 * 
 * API: https://api.mescolis.tn/api
 * Authentification: x-access-token header
 * Documentation: Utilise noms complets des gouvernorats
 */
class MesColisService extends BaseCarrierService
{
    protected function getCarrierSlug(): string
    {
        return 'mes_colis';
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
    // IMPLÉMENTATION SPÉCIFIQUE MES COLIS EXPRESS
    // ========================================

    public function testConnection(DeliveryConfiguration $config): array
    {
        try {
            // Valider les credentials de base
            $credentials = $config->getApiCredentials();
            if (!$credentials || empty($credentials['api_token'])) {
                return [
                    'success' => false,
                    'error' => 'Token API manquant',
                    'details' => [
                        'test_time' => now()->toISOString(),
                        'carrier' => 'Mes Colis Express',
                    ]
                ];
            }

            // Tester avec une commande fictive
            $testData = $this->prepareTestShipmentData($config);
            $headers = $this->prepareAuthHeaders($config);

            Log::info("Test connexion Mes Colis Express", [
                'config_id' => $config->id,
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
                
                // Mes Colis renvoie généralement un barcode
                if (isset($data['barcode']) || isset($data['success'])) {
                    return [
                        'success' => true,
                        'message' => 'Connexion Mes Colis Express réussie',
                        'details' => [
                            'carrier' => 'Mes Colis Express',
                            'api_url' => $config->getApiBaseUrl(),
                            'test_time' => now()->toISOString(),
                            'test_response' => [
                                'status' => $response->status(),
                                'barcode' => $data['barcode'] ?? 'TEST_BARCODE',
                            ]
                        ]
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Réponse API Mes Colis inattendue: ' . json_encode($data),
                        'details' => [
                            'status_code' => $response->status(),
                            'response_data' => $data,
                        ]
                    ];
                }
            } else {
                $errorData = $response->json();
                $errorMessage = $this->extractMesColisErrorMessage($errorData);
                
                return [
                    'success' => false,
                    'error' => "Erreur Mes Colis Express: {$errorMessage}",
                    'details' => [
                        'status_code' => $response->status(),
                        'error_data' => $errorData,
                        'test_time' => now()->toISOString(),
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error("Erreur test connexion Mes Colis Express", [
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
            $mesColisData = $this->formatDataForMesColis($shipmentData, $config);
            $headers = $this->prepareAuthHeaders($config);

            Log::info("Création expédition Mes Colis Express", [
                'config_id' => $config->id,
                'mes_colis_data' => $mesColisData,
            ]);

            $response = $this->makeHttpRequest(
                'POST',
                $config->getApiEndpoint('create_shipment'),
                $mesColisData,
                $headers
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Expédition Mes Colis créée avec succès", [
                    'config_id' => $config->id,
                    'response_data' => $data,
                ]);

                return [
                    'success' => true,
                    'tracking_number' => $data['barcode'] ?? $data['tracking_number'] ?? null,
                    'carrier_reference' => $data['reference'] ?? $data['order_id'] ?? null,
                    'estimated_delivery' => $data['estimated_delivery'] ?? null,
                    'carrier_response' => $data,
                    'details' => [
                        'carrier' => 'Mes Colis Express',
                        'api_response' => $data,
                        'created_at' => now()->toISOString(),
                    ]
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $this->extractMesColisErrorMessage($errorData);
                
                Log::error("Erreur création expédition Mes Colis", [
                    'config_id' => $config->id,
                    'status_code' => $response->status(),
                    'error_data' => $errorData,
                ]);

                throw new CarrierApiException(
                    "Erreur Mes Colis Express: {$errorMessage}",
                    $response->status(),
                    $errorData,
                    $errorData['error_code'] ?? null
                );
            }

        } catch (CarrierApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Exception création expédition Mes Colis", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierServiceException(
                "Erreur lors de la création de l'expédition Mes Colis: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    protected function doTrackShipment(string $trackingNumber, DeliveryConfiguration $config): array
    {
        try {
            $headers = $this->prepareAuthHeaders($config);
            
            // Mes Colis utilise une méthode POST pour le tracking
            $trackingData = ['barcode' => $trackingNumber];

            Log::info("Suivi expédition Mes Colis Express", [
                'config_id' => $config->id,
                'tracking_number' => $trackingNumber,
            ]);

            $response = $this->makeHttpRequest(
                'POST',
                $config->getApiEndpoint('track_shipment'),
                $trackingData,
                $headers
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Suivi Mes Colis récupéré", [
                    'tracking_number' => $trackingNumber,
                    'response_data' => $data,
                ]);

                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'carrier_status' => $data['status'] ?? 'UNKNOWN',
                    'carrier_status_label' => $data['status_label'] ?? $data['status'] ?? 'Statut inconnu',
                    'location' => $data['location'] ?? null,
                    'last_update' => $data['last_update'] ?? $data['updated_at'] ?? now()->toISOString(),
                    'estimated_delivery' => $data['estimated_delivery'] ?? null,
                    'delivery_notes' => $data['notes'] ?? $data['comment'] ?? null,
                    'history' => $this->formatMesColisHistory($data['history'] ?? []),
                    'carrier_response' => $data,
                    'details' => [
                        'carrier' => 'Mes Colis Express',
                        'api_response' => $data,
                        'tracked_at' => now()->toISOString(),
                    ]
                ];
            } else {
                $errorData = $response->json();
                $errorMessage = $this->extractMesColisErrorMessage($errorData);
                
                Log::warning("Erreur suivi Mes Colis", [
                    'tracking_number' => $trackingNumber,
                    'status_code' => $response->status(),
                    'error_data' => $errorData,
                ]);

                // Si le colis n'est pas trouvé
                if ($response->status() === 404) {
                    return [
                        'success' => false,
                        'error' => 'Colis non trouvé',
                        'tracking_number' => $trackingNumber,
                        'carrier_status' => 'NOT_FOUND',
                        'details' => [
                            'carrier' => 'Mes Colis Express',
                            'error_code' => 'NOT_FOUND',
                        ]
                    ];
                }

                throw new CarrierApiException(
                    "Erreur suivi Mes Colis: {$errorMessage}",
                    $response->status(),
                    $errorData,
                    $errorData['error_code'] ?? null
                );
            }

        } catch (CarrierApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Exception suivi Mes Colis", [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);

            throw new CarrierServiceException(
                "Erreur lors du suivi Mes Colis: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    // ========================================
    // MÉTHODES UTILITAIRES MES COLIS EXPRESS
    // ========================================

    /**
     * Formater les données pour l'API Mes Colis
     */
    protected function formatDataForMesColis(array $shipmentData, DeliveryConfiguration $config): array
    {
        $credentials = $config->getApiCredentials();
        $governorateMapping = $this->getSupportedGovernorates();
        
        // Mapper le gouvernorat (ID → nom complet)
        $governorateName = null;
        if (isset($shipmentData['governorate_id'])) {
            $governorateName = $governorateMapping[$shipmentData['governorate_id']] ?? null;
        } elseif (isset($shipmentData['recipient_governorate'])) {
            $governorateName = $governorateMapping[$shipmentData['recipient_governorate']] ?? null;
        }

        if (!$governorateName) {
            throw new CarrierValidationException(
                "Gouvernorat non supporté ou manquant pour Mes Colis Express"
            );
        }

        // Format attendu par Mes Colis Express
        return [
            'product_name' => $shipmentData['content_description'],
            'client_name' => $shipmentData['recipient_name'],
            'address' => $shipmentData['recipient_address'],
            'gouvernerate' => $governorateName, // Nom complet du gouvernorat
            'city' => $shipmentData['delegation'] ?? $shipmentData['recipient_city'],
            'location' => $shipmentData['location'] ?? $shipmentData['recipient_city'],
            'Tel1' => $this->formatPhoneForMesColis($shipmentData['recipient_phone']),
            'Tel2' => $this->formatPhoneForMesColis($shipmentData['recipient_phone_2'] ?? ''),
            'price' => (string) $shipmentData['cod_amount'],
            'exchange' => '0', // Pas d'échange par défaut
            'open_ordre' => '0', // Pas d'ouverture par défaut
            'note' => $shipmentData['delivery_notes'] ?? 'Commande e-commerce',
        ];
    }

    /**
     * Formater un numéro de téléphone pour Mes Colis
     */
    protected function formatPhoneForMesColis(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Nettoyer le numéro
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Formats acceptés par Mes Colis: 12345678, 21612345678
        if (strlen($phone) === 8) {
            return $phone; // Format local
        } elseif (strlen($phone) === 11 && str_starts_with($phone, '216')) {
            return $phone; // Format international
        } elseif (strlen($phone) === 12 && str_starts_with($phone, '216')) {
            return substr($phone, 1); // Retirer le + initial
        }
        
        return $phone; // Retourner tel quel si format non reconnu
    }

    /**
     * Extraire le message d'erreur de la réponse Mes Colis
     */
    protected function extractMesColisErrorMessage(array $errorData): string
    {
        // Mes Colis peut retourner différents formats d'erreur
        if (isset($errorData['message'])) {
            return $errorData['message'];
        } elseif (isset($errorData['error'])) {
            return $errorData['error'];
        } elseif (isset($errorData['errors']) && is_array($errorData['errors'])) {
            return implode(', ', $errorData['errors']);
        }
        
        return 'Erreur inconnue de Mes Colis Express';
    }

    /**
     * Formater l'historique Mes Colis pour le format standardisé
     */
    protected function formatMesColisHistory(array $history): array
    {
        $formattedHistory = [];
        
        foreach ($history as $entry) {
            $formattedHistory[] = [
                'status' => $entry['status'] ?? 'UNKNOWN',
                'label' => $entry['status_label'] ?? $entry['description'] ?? 'Mise à jour',
                'location' => $entry['location'] ?? $entry['lieu'] ?? '',
                'timestamp' => $entry['date'] ?? $entry['created_at'] ?? now()->toISOString(),
                'notes' => $entry['comment'] ?? $entry['note'] ?? '',
            ];
        }
        
        return $formattedHistory;
    }

    /**
     * Préparer des données de test pour la connexion
     */
    protected function prepareTestShipmentData(DeliveryConfiguration $config): array
    {
        return $this->formatDataForMesColis([
            'recipient_name' => 'Client Test',
            'recipient_phone' => '12345678',
            'recipient_address' => 'Adresse de test',
            'recipient_governorate' => 1, // Tunis
            'recipient_city' => 'Tunis',
            'cod_amount' => 10.0,
            'content_description' => 'Test de connexion',
            'delivery_notes' => 'Colis de test',
        ], $config);
    }

    // ========================================
    // SURCHARGES POUR MES COLIS EXPRESS
    // ========================================

    public function validateOrderData(Order $order, DeliveryConfiguration $config): array
    {
        $errors = parent::validateOrderData($order, $config);
        
        // Validations spécifiques à Mes Colis
        if (empty($order->customer_city)) {
            $errors[] = 'Ville manquante (requis pour Mes Colis Express)';
        }
        
        // Vérifier le format du téléphone
        $phone = preg_replace('/[^0-9]/', '', $order->customer_phone);
        if (strlen($phone) < 8) {
            $errors[] = 'Numéro de téléphone invalide pour Mes Colis Express';
        }
        
        // Vérifier le gouvernorat
        $governorateMapping = $this->getSupportedGovernorates();
        if (!isset($governorateMapping[$order->customer_governorate])) {
            $errors[] = "Gouvernorat {$order->customer_governorate} non supporté par Mes Colis Express";
        }
        
        return $errors;
    }

    protected function prepareShipmentData(Order $order, DeliveryConfiguration $config, array $additionalData = []): array
    {
        $baseData = parent::prepareShipmentData($order, $config, $additionalData);
        
        // Ajouter les champs spécifiques à Mes Colis
        return array_merge($baseData, [
            'recipient_governorate' => $order->customer_governorate,
            'location' => $order->customer_city,
            'governorate_id' => $order->customer_governorate,
        ]);
    }

    protected function prepareAuthHeaders(DeliveryConfiguration $config): array
    {
        $credentials = $config->getApiCredentials();
        
        if (!$credentials || !isset($credentials['api_token'])) {
            throw new CarrierServiceException("Token API manquant pour l'authentification Mes Colis");
        }

        return [
            'x-access-token' => $credentials['api_token'],
            'Content-Type' => 'application/json',
        ];
    }
}
