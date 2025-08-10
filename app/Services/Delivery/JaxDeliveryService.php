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
                        'config_id' => $config->id,
                        'has_credentials' => !empty($credentials),
                        'username_length' => $config->username ? strlen($config->username) : 0,
                        'password_length' => $config->password ? strlen($config->password) : 0,
                    ]
                ];
            }

            // Tester avec un colis fictif
            $testData = $this->prepareTestShipmentData($config);
            $headers = $this->prepareAuthHeaders($config);

            Log::info("Test connexion JAX Delivery", [
                'config_id' => $config->id,
                'account_number' => $credentials['account_number'],
                'token_length' => strlen($credentials['api_token']),
                'api_url' => $config->getApiEndpoint('create_shipment'),
                'test_data' => $testData,
                'headers' => array_keys($headers), // Ne pas logger les valeurs sensibles
            ]);

            $response = $this->makeHttpRequest(
                'POST',
                $config->getApiEndpoint('create_shipment'),
                $testData,
                $headers,
                15 // timeout de 15 secondes pour test
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Test JAX - Réponse API reçue", [
                    'status' => $response->status(),
                    'response_keys' => array_keys($data),
                    'has_id' => isset($data['id']),
                    'has_colis_id' => isset($data['colis_id']),
                    'has_tracking' => isset($data['tracking_number']),
                ]);
                
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
                        'error' => 'Réponse API JAX inattendue: Structure de réponse non reconnue',
                        'details' => [
                            'status_code' => $response->status(),
                            'response_keys' => array_keys($data),
                            'response_sample' => array_slice($data, 0, 3, true), // Première partie seulement
                        ]
                    ];
                }
            } else {
                $errorData = $response->json() ?: [];
                $errorMessage = $this->extractJaxErrorMessage($errorData);
                
                Log::warning("Test JAX - Erreur API", [
                    'status' => $response->status(),
                    'error_data' => $errorData,
                    'extracted_message' => $errorMessage,
                ]);
                
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
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur de communication: ' . $e->getMessage(),
                'details' => [
                    'exception' => get_class($e),
                    'test_time' => now()->toISOString(),
                    'config_id' => $config->id,
                ]
            ];
        }
    }

    protected function doCreateShipment(array $shipmentData, DeliveryConfiguration $config): array
    {
        try {
            // Valider les données avant envoi
            $this->validateShipmentDataForJax($shipmentData);
            
            $jaxData = $this->formatDataForJax($shipmentData, $config);
            $headers = $this->prepareAuthHeaders($config);

            Log::info("Création expédition JAX Delivery", [
                'config_id' => $config->id,
                'jax_data' => $jaxData,
                'endpoint' => $config->getApiEndpoint('create_shipment'),
            ]);

            $response = $this->makeHttpRequest(
                'POST',
                $config->getApiEndpoint('create_shipment'),
                $jaxData,
                $headers,
                30 // timeout de 30 secondes pour création
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Expédition JAX créée avec succès", [
                    'config_id' => $config->id,
                    'response_status' => $response->status(),
                    'response_keys' => array_keys($data),
                    'tracking_info' => [
                        'id' => $data['id'] ?? null,
                        'colis_id' => $data['colis_id'] ?? null,
                        'ean' => $data['ean'] ?? null,
                        'tracking_number' => $data['tracking_number'] ?? null,
                    ]
                ]);

                // Extraire le numéro de suivi selon les différents formats JAX
                $trackingNumber = $this->extractJaxTrackingNumber($data);
                $carrierReference = $this->extractJaxReference($data);

                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'carrier_reference' => $carrierReference,
                    'estimated_delivery' => $data['estimated_delivery'] ?? null,
                    'carrier_response' => $data,
                    'details' => [
                        'carrier' => 'JAX Delivery',
                        'api_response' => $data,
                        'created_at' => now()->toISOString(),
                        'account_number' => $jaxData['compte'] ?? 'N/A',
                    ]
                ];
            } else {
                $errorData = $response->json() ?: [];
                $errorMessage = $this->extractJaxErrorMessage($errorData);
                
                Log::error("Erreur création expédition JAX", [
                    'config_id' => $config->id,
                    'status_code' => $response->status(),
                    'error_data' => $errorData,
                    'jax_data_sent' => $jaxData,
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
        } catch (CarrierValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Exception création expédition JAX", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
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
                $headers,
                20 // timeout de 20 secondes pour tracking
            );

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Suivi JAX récupéré", [
                    'tracking_number' => $trackingNumber,
                    'response_keys' => array_keys($data),
                    'status' => $data['status'] ?? 'Non défini',
                ]);

                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'carrier_status' => $data['status'] ?? 'UNKNOWN',
                    'carrier_status_label' => $data['status_label'] ?? $data['status_description'] ?? $data['libelle'] ?? 'Statut inconnu',
                    'location' => $data['current_location'] ?? $data['location'] ?? $data['lieu'] ?? null,
                    'last_update' => $data['last_update'] ?? $data['updated_at'] ?? $data['date_maj'] ?? now()->toISOString(),
                    'estimated_delivery' => $data['estimated_delivery'] ?? $data['date_livraison_prevue'] ?? null,
                    'delivery_notes' => $data['notes'] ?? $data['comment'] ?? $data['commentaire'] ?? null,
                    'history' => $this->formatJaxHistory($data['history'] ?? $data['historique'] ?? []),
                    'carrier_response' => $data,
                    'details' => [
                        'carrier' => 'JAX Delivery',
                        'api_response' => $data,
                        'tracked_at' => now()->toISOString(),
                    ]
                ];
            } else {
                $errorData = $response->json() ?: [];
                $errorMessage = $this->extractJaxErrorMessage($errorData);
                
                Log::warning("Erreur suivi JAX", [
                    'tracking_number' => $trackingNumber,
                    'status_code' => $response->status(),
                    'error_data' => $errorData,
                ]);

                // Si le colis n'est pas trouvé, ce n'est pas forcément une erreur fatale
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
                'error_class' => get_class($e),
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
     * Valider les données spécifiques à JAX avant envoi
     */
    protected function validateShipmentDataForJax(array $shipmentData): void
    {
        $required = ['recipient_name', 'recipient_phone', 'recipient_address', 'cod_amount'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($shipmentData[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new CarrierValidationException(
                "Champs requis manquants pour JAX: " . implode(', ', $missing)
            );
        }
        
        // Valider le gouvernorat
        if (empty($shipmentData['recipient_governorate']) && empty($shipmentData['governorate_id'])) {
            throw new CarrierValidationException("Gouvernorat manquant pour JAX Delivery");
        }
    }

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
                "Gouvernorat non supporté ou manquant pour JAX Delivery. " .
                "Gouvernorat fourni: " . ($shipmentData['recipient_governorate'] ?? $shipmentData['governorate_id'] ?? 'Aucun')
            );
        }

        // Format attendu par JAX Delivery selon leur documentation
        $jaxData = [
            'referenceExterne' => $shipmentData['external_reference'] ?? '',
            'nomContact' => $shipmentData['recipient_name'],
            'tel' => $this->formatPhoneForJax($shipmentData['recipient_phone']),
            'tel2' => $this->formatPhoneForJax($shipmentData['recipient_phone_2'] ?? ''),
            'adresseLivraison' => $shipmentData['recipient_address'],
            'gouvernorat' => $governorateCode, // Code numérique (1-24)
            'delegation' => $shipmentData['delegation'] ?? $shipmentData['recipient_city'] ?? '',
            'description' => $shipmentData['content_description'] ?? 'Colis e-commerce',
            'cod' => (string) $shipmentData['cod_amount'],
            'echange' => $shipmentData['exchange'] ?? 0,
        ];

        // Ajouter les champs optionnels si disponibles
        if (isset($shipmentData['weight'])) {
            $jaxData['poids'] = (float) $shipmentData['weight'];
        }
        
        if (isset($shipmentData['nb_pieces'])) {
            $jaxData['pieces'] = (int) $shipmentData['nb_pieces'];
        }
        
        if (isset($shipmentData['pickup_date'])) {
            $jaxData['date_enlevement'] = $shipmentData['pickup_date'];
        }
        
        if (isset($shipmentData['delivery_notes'])) {
            $jaxData['commentaire'] = $shipmentData['delivery_notes'];
        }

        Log::debug("Données formatées pour JAX", [
            'original_governorate' => $shipmentData['recipient_governorate'] ?? $shipmentData['governorate_id'] ?? null,
            'mapped_governorate' => $governorateCode,
            'jax_data_keys' => array_keys($jaxData),
        ]);

        return $jaxData;
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
        
        // Si le format n'est pas reconnu, retourner tel quel et laisser JAX décider
        return $phone;
    }

    /**
     * Extraire le numéro de suivi de la réponse JAX
     */
    protected function extractJaxTrackingNumber(array $data): ?string
    {
        // JAX peut retourner différents champs pour le numéro de suivi
        return $data['id'] ?? 
               $data['colis_id'] ?? 
               $data['ean'] ?? 
               $data['tracking_number'] ?? 
               $data['numero_suivi'] ?? 
               null;
    }

    /**
     * Extraire la référence transporteur de la réponse JAX
     */
    protected function extractJaxReference(array $data): ?string
    {
        return $data['reference'] ?? 
               $data['barcode'] ?? 
               $data['reference_jax'] ?? 
               $data['numero_reference'] ?? 
               null;
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
            return is_array($errorData['error']) ? implode(', ', $errorData['error']) : $errorData['error'];
        } elseif (isset($errorData['erreur'])) {
            return $errorData['erreur'];
        } elseif (isset($errorData['errors']) && is_array($errorData['errors'])) {
            return implode(', ', $errorData['errors']);
        } elseif (isset($errorData['data']) && is_array($errorData['data'])) {
            return 'Erreur de données: ' . json_encode($errorData['data']);
        }
        
        // Si aucun message d'erreur standard n'est trouvé
        if (!empty($errorData)) {
            return 'Erreur JAX: ' . json_encode($errorData);
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
                'status' => $entry['status'] ?? $entry['statut'] ?? 'UNKNOWN',
                'label' => $entry['status_label'] ?? 
                          $entry['description'] ?? 
                          $entry['libelle'] ?? 
                          'Mise à jour',
                'location' => $entry['location'] ?? 
                             $entry['lieu'] ?? 
                             $entry['localisation'] ?? '',
                'timestamp' => $entry['date'] ?? 
                              $entry['created_at'] ?? 
                              $entry['date_maj'] ?? 
                              now()->toISOString(),
                'notes' => $entry['comment'] ?? 
                          $entry['commentaire'] ?? 
                          $entry['note'] ?? '',
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
            'recipient_name' => 'Client Test JAX',
            'recipient_phone' => '12345678',
            'recipient_address' => 'Adresse de test JAX Delivery',
            'recipient_governorate' => 1, // Tunis
            'recipient_city' => 'Tunis',
            'cod_amount' => 10.0,
            'content_description' => 'Test de connexion JAX',
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
            $errors[] = 'Numéro de téléphone invalide pour JAX Delivery (minimum 8 chiffres)';
        }
        
        // Vérifier le gouvernorat
        $governorateMapping = $this->getSupportedGovernorates();
        if (!isset($governorateMapping[$order->customer_governorate])) {
            $errors[] = "Gouvernorat {$order->customer_governorate} non supporté par JAX Delivery";
        }
        
        // Vérifier l'adresse
        if (strlen($order->customer_address) < 10) {
            $errors[] = 'Adresse trop courte pour JAX Delivery (minimum 10 caractères)';
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
            'external_reference' => $additionalData['external_reference'] ?? "ORDER_{$order->id}",
        ]);
    }

    protected function prepareAuthHeaders(DeliveryConfiguration $config): array
    {
        $credentials = $config->getApiCredentials();
        
        if (!$credentials || !isset($credentials['api_token'])) {
            throw new CarrierServiceException("Token API manquant pour l'authentification JAX");
        }

        return [
            'Authorization' => 'Bearer ' . $credentials['api_token'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    // ========================================
    // MÉTHODES SPÉCIFIQUES AU MAPPING GOUVERNORATS JAX
    // ========================================

    public function getSupportedGovernorates(): array
    {
        // JAX utilise des codes numériques pour les gouvernorats (1-24)
        return [
            1 => '1',   // Tunis
            2 => '2',   // Ariana  
            3 => '3',   // Ben Arous
            4 => '4',   // Manouba
            5 => '5',   // Nabeul
            6 => '6',   // Zaghouan
            7 => '7',   // Bizerte
            8 => '8',   // Béja
            9 => '9',   // Jendouba
            10 => '10', // Le Kef
            11 => '11', // Siliana
            12 => '12', // Kairouan
            13 => '13', // Kasserine
            14 => '14', // Sidi Bouzid
            15 => '15', // Sousse
            16 => '16', // Monastir
            17 => '17', // Mahdia
            18 => '18', // Sfax
            19 => '19', // Gafsa
            20 => '20', // Tozeur
            21 => '21', // Kebili
            22 => '22', // Gabès
            23 => '23', // Medenine
            24 => '24', // Tataouine
        ];
    }

    /**
     * Obtenir le nom du gouvernorat par son ID
     */
    public function getGovernorateNameById(int $id): ?string
    {
        $names = [
            1 => 'Tunis', 2 => 'Ariana', 3 => 'Ben Arous', 4 => 'Manouba',
            5 => 'Nabeul', 6 => 'Zaghouan', 7 => 'Bizerte', 8 => 'Béja',
            9 => 'Jendouba', 10 => 'Le Kef', 11 => 'Siliana', 12 => 'Kairouan',
            13 => 'Kasserine', 14 => 'Sidi Bouzid', 15 => 'Sousse', 16 => 'Monastir',
            17 => 'Mahdia', 18 => 'Sfax', 19 => 'Gafsa', 20 => 'Tozeur',
            21 => 'Kebili', 22 => 'Gabès', 23 => 'Medenine', 24 => 'Tataouine'
        ];
        
        return $names[$id] ?? null;
    }
}