<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JaxDeliveryService implements CarrierServiceInterface
{
    protected $config;
    protected $baseUrl = 'https://core.jax-delivery.com/api';

    public function __construct(array $config)
    {
        $this->config = $config;
        
        Log::info('🔧 [JAX] Service initialisé', [
            'has_api_token' => !empty($config['api_token']),
            'has_username' => !empty($config['username']),
            'token_preview' => !empty($config['api_token']) ? substr($config['api_token'], 0, 20) . '...' : 'vide',
            'username' => $config['username'] ?? 'vide',
            'environment' => $config['environment'] ?? 'test',
        ]);
    }

    public function createShipment(array $data): array
    {
        Log::info('🚀 [JAX] Création colis dans le compte JAX', [
            'recipient' => $data['recipient_name'] ?? 'Non défini',
            'phone' => $data['recipient_phone'] ?? 'Non défini',
            'cod_amount' => $data['cod_amount'] ?? 0,
            'governorate' => $data['recipient_governorate'] ?? 'Non défini',
            'external_reference' => $data['external_reference'] ?? 'Non défini',
        ]);

        try {
            $token = $this->getApiToken();
            
            // 🔧 CORRECTION : Préparer les données selon la structure exacte de l'API JAX
            $jaxData = [
                'referenceExterne' => $data['external_reference'] ?? '',
                'nomContact' => $data['recipient_name'] ?? '',
                'tel' => $this->cleanPhoneNumber($data['recipient_phone'] ?? ''),
                'tel2' => $this->cleanPhoneNumber($data['recipient_phone_2'] ?? ''),
                'adresseLivraison' => $data['recipient_address'] ?? '',
                'governorat' => $this->mapGovernorateToJaxCode($data['recipient_governorate'] ?? 'Tunis'),
                'delegation' => $data['recipient_city'] ?? '',
                'description' => substr($data['content_description'] ?? 'Colis e-commerce', 0, 100),
                'cod' => (string)($data['cod_amount'] ?? 0),
                'echange' => 0,
            ];

            // 🆕 VALIDATION DES DONNÉES AVANT ENVOI
            $validationErrors = [];
            if (empty($jaxData['nomContact'])) {
                $validationErrors[] = 'Nom du contact manquant';
            }
            if (empty($jaxData['tel']) || strlen($jaxData['tel']) < 8) {
                $validationErrors[] = 'Numéro de téléphone invalide: ' . $jaxData['tel'];
            }
            if (empty($jaxData['adresseLivraison'])) {
                $validationErrors[] = 'Adresse de livraison manquante';
            }

            if (!empty($validationErrors)) {
                Log::error('❌ [JAX] Données invalides', [
                    'errors' => $validationErrors,
                    'data' => $jaxData,
                ]);
                throw new CarrierServiceException('Données invalides: ' . implode(', ', $validationErrors));
            }

            Log::info('📤 [JAX] Envoi vers API JAX', [
                'url' => $this->baseUrl . '/user/colis/add',
                'data_preview' => [
                    'nomContact' => $jaxData['nomContact'],
                    'tel' => $jaxData['tel'],
                    'governorat' => $jaxData['governorat'],
                    'cod' => $jaxData['cod'],
                    'referenceExterne' => $jaxData['referenceExterne'],
                ],
                'token_preview' => substr($token, 0, 20) . '...',
            ]);

            // 🔥 APPEL CRITIQUE API JAX AVEC RETRY
            $maxRetries = 3;
            $response = null;
            $lastError = null;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout(30)->post($this->baseUrl . '/user/colis/add', $jaxData);

                    if ($response->successful()) {
                        break; // Succès, sortir de la boucle
                    }

                    $lastError = "HTTP {$response->status()}: " . $response->body();
                    Log::warning("⚠️ [JAX] Tentative {$attempt}/{$maxRetries} échouée", [
                        'status' => $response->status(),
                        'error' => $lastError,
                    ]);

                    if ($attempt < $maxRetries) {
                        sleep(1); // Attendre 1 seconde avant de réessayer
                    }

                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning("⚠️ [JAX] Exception tentative {$attempt}/{$maxRetries}", [
                        'error' => $lastError,
                    ]);

                    if ($attempt < $maxRetries) {
                        sleep(1);
                    }
                }
            }

            if (!$response || $response->failed()) {
                Log::error('❌ [JAX] Échec API après tous les essais', [
                    'final_error' => $lastError,
                    'request_data' => $jaxData,
                    'attempts' => $maxRetries,
                ]);
                
                throw new CarrierServiceException(
                    "Erreur JAX API après {$maxRetries} tentatives: " . $lastError,
                    $response ? $response->status() : 500,
                    $response ? $response->json() : null
                );
            }

            $responseData = $response->json();
            
            Log::info('📥 [JAX] Réponse API reçue avec succès', [
                'status' => $response->status(),
                'response_keys' => array_keys($responseData ?? []),
                'response_preview' => is_array($responseData) ? array_slice($responseData, 0, 5, true) : $responseData,
            ]);
            
            // 🔧 AMÉLIORATION : Extraction du numéro de suivi avec multiple fallbacks
            $trackingNumber = $this->extractTrackingNumber($responseData);

            if (!$trackingNumber) {
                Log::error('⚠️ [JAX] Pas de numéro de suivi dans la réponse', [
                    'full_response' => $responseData,
                    'response_structure' => $this->analyzeResponseStructure($responseData),
                ]);
                throw new CarrierServiceException('JAX API: Réponse valide mais pas de numéro de suivi retourné');
            }

            Log::info('✅ [JAX] Colis créé avec succès dans le compte JAX', [
                'tracking_number' => $trackingNumber,
                'response_structure' => array_keys($responseData),
                'cod_amount' => $jaxData['cod'],
                'recipient' => $jaxData['nomContact'],
                'reference_externe' => $jaxData['referenceExterne'],
            ]);

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'response' => $responseData,
                'carrier_name' => 'JAX Delivery',
            ];

        } catch (CarrierServiceException $e) {
            Log::error('❌ [JAX] Erreur transporteur', [
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse(),
                'config_check' => [
                    'has_token' => !empty($this->config['api_token']),
                    'has_username' => !empty($this->config['username']),
                    'token_length' => !empty($this->config['api_token']) ? strlen($this->config['api_token']) : 0,
                ],
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur générale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $jaxData ?? null,
            ]);
            throw new CarrierServiceException('Erreur JAX: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function createPickup(array $data): array
    {
        Log::info('🚛 [JAX] Création pickup', [
            'tracking_numbers_count' => count($data['tracking_numbers'] ?? []),
        ]);

        try {
            $token = $this->getApiToken();
            
            $jaxData = [
                'adresse' => $data['address'] ?? 'Adresse pickup',
                'nbrColis' => (string)count($data['tracking_numbers'] ?? []),
                'colis_statut' => '10', // Statut pour pickup
                'colis_list' => $data['tracking_numbers'] ?? [],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/client/createByean', $jaxData);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX pickup (HTTP {$response->status()}): " . $response->body(),
                    $response->status()
                );
            }

            $responseData = $response->json();
            Log::info('✅ [JAX] Pickup créé', ['response' => $responseData]);

            return [
                'success' => true,
                'pickup_id' => $responseData['pickup_id'] ?? $responseData['id'] ?? null,
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur pickup', ['error' => $e->getMessage()]);
            throw new CarrierServiceException('Erreur JAX pickup: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function getShipmentStatus(string $trackingNumber): array
    {
        Log::info('🔍 [JAX] Récupération statut colis', [
            'tracking_number' => $trackingNumber,
        ]);

        try {
            $token = $this->getApiToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(15)->get($this->baseUrl . "/user/colis/getstatubyean/{$trackingNumber}");

            Log::info('📥 [JAX] Réponse statut reçue', [
                'tracking_number' => $trackingNumber,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX statut (HTTP {$response->status()}): " . $response->body(),
                    $response->status()
                );
            }

            $responseData = $response->json();

            Log::info('✅ [JAX] Statut récupéré avec succès', [
                'tracking_number' => $trackingNumber,
                'raw_status' => $responseData['status'] ?? 'unknown',
                'response_keys' => array_keys($responseData ?? []),
            ]);

            return [
                'success' => true,
                'status' => $this->mapJaxStatusToInternal($responseData['status'] ?? 'unknown'),
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur récupération statut', [
                'tracking_number' => $trackingNumber, 
                'error' => $e->getMessage()
            ]);
            throw new CarrierServiceException('Erreur JAX statut: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function testConnection(): array
    {
        Log::info('🧪 [JAX] Test de connexion');
        
        try {
            $token = $this->getApiToken();
            
            // Test avec l'endpoint des gouvernorats
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(10)->get($this->baseUrl . '/gouvernorats');

            Log::info('🧪 [JAX] Réponse test connexion', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'response_size' => strlen($response->body()),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connexion JAX réussie - Token valide',
                    'data' => [
                        'gouvernorats_count' => is_array($responseData) ? count($responseData) : 0,
                        'response_keys' => is_array($responseData) ? array_keys($responseData) : [],
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => "Échec connexion JAX (HTTP {$response->status()}) - Vérifiez le token",
                'details' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur test connexion', [
                'error' => $e->getMessage(),
                'config_check' => [
                    'has_token' => !empty($this->config['api_token']),
                    'token_length' => !empty($this->config['api_token']) ? strlen($this->config['api_token']) : 0,
                ],
            ]);
            return [
                'success' => false,
                'message' => 'Erreur connexion JAX: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtenir le token d'authentification
     */
    protected function getApiToken(): string
    {
        $token = $this->config['api_token'] ?? null;
        
        if (!$token) {
            Log::error('❌ [JAX] Token manquant dans config', [
                'config_keys' => array_keys($this->config),
            ]);
            throw new CarrierServiceException('Token JAX manquant dans la configuration');
        }

        Log::debug('🔑 [JAX] Token récupéré', [
            'token_length' => strlen($token),
            'token_preview' => substr($token, 0, 20) . '...',
            'username' => $this->config['username'] ?? 'N/A',
        ]);

        return $token;
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Analyser la structure de la réponse pour debug
     */
    protected function analyzeResponseStructure($data): array
    {
        if (!is_array($data)) {
            return ['type' => gettype($data), 'value' => $data];
        }

        $analysis = [];
        foreach ($data as $key => $value) {
            $analysis[$key] = [
                'type' => gettype($value),
                'empty' => empty($value),
            ];
            
            if (is_array($value)) {
                $analysis[$key]['keys'] = array_keys($value);
            } elseif (is_string($value)) {
                $analysis[$key]['length'] = strlen($value);
                $analysis[$key]['preview'] = substr($value, 0, 20);
            }
        }

        return $analysis;
    }

    /**
     * 🔧 AMÉLIORATION : Extraction du numéro de suivi avec plus de fallbacks
     */
    protected function extractTrackingNumber($responseData): ?string
    {
        if (!is_array($responseData)) {
            Log::warning('[JAX] Réponse n\'est pas un array', ['response' => $responseData]);
            return null;
        }

        // Tenter différents champs possibles dans la réponse JAX
        $possibleFields = [
            'ean', 'id', 'barcode', 'tracking_number', 'reference', 
            'colis_id', 'numero_suivi', 'tracking', 'code_barre',
            'numero', 'num_suivi', 'order_id'
        ];
        
        foreach ($possibleFields as $field) {
            // Niveau principal
            if (isset($responseData[$field]) && !empty($responseData[$field])) {
                $value = (string) $responseData[$field];
                Log::info('[JAX] Tracking trouvé dans champ principal', [
                    'field' => $field,
                    'value' => $value,
                ]);
                return $value;
            }
            
            // Dans 'data' si présent
            if (isset($responseData['data'][$field]) && !empty($responseData['data'][$field])) {
                $value = (string) $responseData['data'][$field];
                Log::info('[JAX] Tracking trouvé dans data', [
                    'field' => $field,
                    'value' => $value,
                ]);
                return $value;
            }
            
            // Dans 'result' si présent
            if (isset($responseData['result'][$field]) && !empty($responseData['result'][$field])) {
                $value = (string) $responseData['result'][$field];
                Log::info('[JAX] Tracking trouvé dans result', [
                    'field' => $field,
                    'value' => $value,
                ]);
                return $value;
            }
        }
        
        // Si aucun champ standard trouvé, chercher des valeurs numériques
        foreach ($responseData as $key => $value) {
            if (is_numeric($value) && strlen((string)$value) >= 5) {
                Log::info('[JAX] Tracking possible trouvé (valeur numérique)', [
                    'field' => $key,
                    'value' => $value,
                ]);
                return (string) $value;
            }
        }
        
        Log::warning('[JAX] Aucun numéro de suivi trouvé', [
            'available_fields' => array_keys($responseData),
            'response_sample' => array_slice($responseData, 0, 3, true),
        ]);
        
        return null;
    }

    /**
     * 🔧 CORRECTION : Nettoyer les numéros de téléphone pour format tunisien 8 chiffres
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        
        // Supprimer tous les caractères non numériques
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        Log::debug('🧹 [JAX] Nettoyage numéro téléphone', [
            'original' => $phone,
            'cleaned' => $cleaned,
            'length' => strlen($cleaned),
        ]);
        
        // Si le numéro commence par +216, enlever le préfixe
        if (str_starts_with($cleaned, '216') && strlen($cleaned) > 8) {
            $cleaned = substr($cleaned, 3);
        }
        
        // Si le numéro a exactement 8 chiffres et commence par 2, 3, 4, 5, 7, 9 (numéros valides en Tunisie)
        if (strlen($cleaned) === 8 && in_array($cleaned[0], ['2', '3', '4', '5', '7', '9'])) {
            Log::debug('✅ [JAX] Numéro tunisien valide à 8 chiffres', [
                'phone' => $cleaned,
                'first_digit' => $cleaned[0],
            ]);
            return $cleaned;
        }
        
        // Si le numéro est trop long, prendre les 8 derniers chiffres
        if (strlen($cleaned) > 8) {
            $cleaned = substr($cleaned, -8);
            Log::debug('✂️ [JAX] Numéro tronqué aux 8 derniers chiffres', [
                'phone' => $cleaned,
            ]);
        }
        
        // Si le numéro est trop court, le laisser tel quel (l'API pourrait le refuser)
        if (strlen($cleaned) < 8) {
            Log::warning('⚠️ [JAX] Numéro trop court', [
                'phone' => $cleaned,
                'length' => strlen($cleaned),
            ]);
        }
        
        return $cleaned;
    }

    /**
     * 🔧 CORRECTION : Mapper les gouvernorats vers codes JAX (mapping complet et corrigé)
     */
    protected function mapGovernorateToJaxCode($governorate): string
    {
        $mapping = [
            // Grand Tunis
            'Tunis' => '11', 'Ariana' => '12', 'Ben Arous' => '13', 
            'Manouba' => '14', 'La Mannouba' => '14',
            
            // Nord-Est
            'Nabeul' => '21', 'Zaghouan' => '22', 'Bizerte' => '23',
            
            // Nord-Ouest
            'Béja' => '31', 'Jendouba' => '32', 'Le Kef' => '33', 'Siliana' => '34',
            
            // Centre-Ouest
            'Kairouan' => '41', 'Kasserine' => '42', 'Sidi Bouzid' => '43',
            
            // Centre-Est
            'Sousse' => '51', 'Monastir' => '52', 'Mahdia' => '53',
            
            // Sud-Est
            'Sfax' => '61',
            
            // Sud-Ouest
            'Gafsa' => '71', 'Tozeur' => '72', 'Kebili' => '73', 'Kébili' => '73',
            
            // Sud
            'Gabès' => '81', 'Medenine' => '82', 'Médenine' => '82', 'Tataouine' => '83',
        ];

        $code = $mapping[$governorate] ?? '11'; // Par défaut Tunis
        
        Log::debug('🗺️ [JAX] Mapping gouvernorat', [
            'input' => $governorate,
            'output' => $code,
            'found_in_mapping' => isset($mapping[$governorate]),
        ]);

        return $code;
    }

    /**
     * 🔧 CORRECTION : Mapper les statuts JAX vers statuts internes
     */
    protected function mapJaxStatusToInternal($jaxStatus): string
    {
        $mapping = [
            // Statuts numériques
            '1' => 'created', '2' => 'validated', '3' => 'picked_up_by_carrier',
            '4' => 'in_transit', '5' => 'delivered', '6' => 'delivery_failed',
            '7' => 'in_return', '8' => 'returned', '9' => 'anomaly', '10' => 'created',
            
            // Statuts textuels
            'En attente' => 'created',
            'En cours' => 'validated',
            'En transit' => 'in_transit',
            'Livré' => 'delivered',
            'Échec' => 'delivery_failed',
            'Retour' => 'in_return',
            'Problème' => 'anomaly',
            'Validé' => 'validated',
            'Récupéré' => 'picked_up_by_carrier',
            'En livraison' => 'in_transit',
            'Livraison échouée' => 'delivery_failed',
            'En retour' => 'in_return',
            'Retourné' => 'returned',
            'Anomalie' => 'anomaly',
        ];

        $internalStatus = $mapping[(string)$jaxStatus] ?? 'unknown';
        
        Log::debug('🔄 [JAX] Mapping statut', [
            'jax_status' => $jaxStatus,
            'internal_status' => $internalStatus,
            'found_in_mapping' => isset($mapping[(string)$jaxStatus]),
        ]);

        return $internalStatus;
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Valider les données avant envoi
     */
    protected function validateShipmentData(array $data): array
    {
        $errors = [];
        
        // Validation des champs obligatoires
        $requiredFields = [
            'recipient_name' => 'Nom du destinataire',
            'recipient_phone' => 'Téléphone du destinataire',
            'recipient_address' => 'Adresse de livraison',
            'recipient_governorate' => 'Gouvernorat',
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "{$label} manquant";
            }
        }
        
        // Validation du téléphone
        if (!empty($data['recipient_phone'])) {
            $cleanPhone = $this->cleanPhoneNumber($data['recipient_phone']);
            if (strlen($cleanPhone) < 8) {
                $errors[] = "Numéro de téléphone invalide: {$data['recipient_phone']}";
            }
        }
        
        // Validation du COD
        if (isset($data['cod_amount']) && $data['cod_amount'] < 0) {
            $errors[] = "Montant COD ne peut pas être négatif";
        }
        
        return $errors;
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Obtenir les informations de configuration
     */
    public function getConfigInfo(): array
    {
        return [
            'carrier' => 'JAX Delivery',
            'base_url' => $this->baseUrl,
            'has_token' => !empty($this->config['api_token']),
            'has_username' => !empty($this->config['username']),
            'token_length' => !empty($this->config['api_token']) ? strlen($this->config['api_token']) : 0,
            'environment' => $this->config['environment'] ?? 'test',
        ];
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Test spécifique pour créer un colis de test
     */
    public function createTestShipment(): array
    {
        $testData = [
            'external_reference' => 'TEST_JAX_' . time(),
            'recipient_name' => 'Client Test JAX',
            'recipient_phone' => '12345678',
            'recipient_phone_2' => '87654321',
            'recipient_address' => 'Adresse Test JAX, Rue de Test',
            'recipient_governorate' => 'Tunis',
            'recipient_city' => 'Tunis',
            'cod_amount' => 50,
            'content_description' => 'Colis de test JAX - NE PAS LIVRER',
            'weight' => 1.0,
            'notes' => 'COLIS DE TEST - IGNORER',
        ];

        Log::info('🧪 [JAX] Création colis de test', [
            'test_data' => $testData,
        ]);

        return $this->createShipment($testData);
    }
}