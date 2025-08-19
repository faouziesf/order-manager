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
        ]);

        try {
            $token = $this->getApiToken();
            
            // 🔧 CORRECTION CRITIQUE : Préparer les données selon l'API JAX exacte
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

            Log::info('📤 [JAX] Données préparées pour API', [
                'nomContact' => $jaxData['nomContact'],
                'tel' => $jaxData['tel'],
                'governorat' => $jaxData['governorat'],
                'cod' => $jaxData['cod'],
                'url' => $this->baseUrl . '/user/colis/add',
                'token_exists' => !empty($token),
            ]);

            // 🔥 APPEL API JAX AVEC GESTION D'ERREUR AMÉLIORÉE
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/user/colis/add', $jaxData);

            Log::info('📥 [JAX] Réponse API complète', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'headers' => $response->headers(),
                'full_body' => $response->body(),
                'json_body' => $response->json(),
            ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                $errorJson = $response->json();
                
                Log::error('❌ [JAX] Échec API détaillé', [
                    'status' => $response->status(),
                    'error_body' => $errorBody,
                    'error_json' => $errorJson,
                    'request_data' => $jaxData,
                    'headers_sent' => [
                        'Authorization' => 'Bearer ' . substr($token, 0, 20) . '...',
                        'Content-Type' => 'application/json',
                    ],
                ]);
                
                // Analyser l'erreur spécifique
                $errorMessage = $this->parseJaxError($errorJson, $response->status());
                
                throw new CarrierServiceException(
                    "Erreur JAX API: {$errorMessage}",
                    $response->status(),
                    $errorJson
                );
            }

            $responseData = $response->json();
            
            // 🔧 CORRECTION CRITIQUE : Extraction du numéro de suivi améliorée
            $trackingNumber = $this->extractTrackingNumberImproved($responseData);

            if (!$trackingNumber) {
                Log::error('⚠️ [JAX] Pas de numéro de suivi dans la réponse', [
                    'response_data' => $responseData,
                    'response_keys' => is_array($responseData) ? array_keys($responseData) : 'not_array',
                    'response_type' => gettype($responseData),
                ]);
                
                throw new CarrierServiceException(
                    'JAX API: Pas de numéro de suivi retourné. Réponse: ' . json_encode($responseData)
                );
            }

            Log::info('✅ [JAX] Colis créé avec succès', [
                'tracking_number' => $trackingNumber,
                'response_keys' => is_array($responseData) ? array_keys($responseData) : 'not_array',
                'cod_amount' => $jaxData['cod'],
                'recipient' => $jaxData['nomContact'],
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
        try {
            $token = $this->getApiToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(15)->get($this->baseUrl . "/user/colis/getstatubyean/{$trackingNumber}");

            if ($response->failed()) {
                throw new CarrierServiceException("Erreur JAX statut: " . $response->body());
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'status' => $this->mapJaxStatusToInternal($responseData['status'] ?? 'unknown'),
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur statut', ['tracking' => $trackingNumber, 'error' => $e->getMessage()]);
            throw new CarrierServiceException('Erreur JAX statut: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function testConnection(): array
    {
        Log::info('🧪 [JAX] Test de connexion');
        
        try {
            $token = $this->getApiToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(10)->get($this->baseUrl . '/gouvernorats');

            Log::info('🧪 [JAX] Réponse test', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 200),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion JAX réussie - Token valide',
                ];
            }

            return [
                'success' => false,
                'message' => "Échec connexion JAX (HTTP {$response->status()}) - Vérifiez le token",
            ];

        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur test connexion', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur connexion JAX: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Analyser les erreurs JAX spécifiques
     */
    protected function parseJaxError($errorData, $statusCode): string
    {
        if (!is_array($errorData)) {
            return "Erreur HTTP {$statusCode} - Réponse invalide";
        }

        // Erreurs communes JAX
        if (isset($errorData['error'])) {
            return $errorData['error'];
        }

        if (isset($errorData['message'])) {
            return $errorData['message'];
        }

        if (isset($errorData['errors']) && is_array($errorData['errors'])) {
            return implode(', ', $errorData['errors']);
        }

        // Erreurs d'authentification
        if ($statusCode === 401) {
            return 'Token d\'authentification invalide ou expiré';
        }

        if ($statusCode === 403) {
            return 'Accès refusé - Vérifiez vos droits d\'accès';
        }

        if ($statusCode === 422) {
            return 'Données invalides - Vérifiez les champs obligatoires';
        }

        return "Erreur HTTP {$statusCode}";
    }

    /**
     * 🔧 CORRECTION CRITIQUE : Extraction améliorée du numéro de suivi
     */
    protected function extractTrackingNumberImproved($responseData): ?string
    {
        if (!is_array($responseData)) {
            Log::warning('[JAX] Réponse non-array', ['type' => gettype($responseData), 'data' => $responseData]);
            return null;
        }

        // 1. Champs directs prioritaires
        $directFields = ['ean', 'id', 'barcode', 'tracking_number', 'reference', 'numero_suivi'];
        foreach ($directFields as $field) {
            if (isset($responseData[$field]) && !empty($responseData[$field])) {
                Log::info("[JAX] Tracking trouvé dans '{$field}'", ['value' => $responseData[$field]]);
                return (string) $responseData[$field];
            }
        }

        // 2. Dans sous-objet 'data'
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            foreach ($directFields as $field) {
                if (isset($responseData['data'][$field]) && !empty($responseData['data'][$field])) {
                    Log::info("[JAX] Tracking trouvé dans 'data.{$field}'", ['value' => $responseData['data'][$field]]);
                    return (string) $responseData['data'][$field];
                }
            }
        }

        // 3. Dans sous-objet 'colis' ou 'shipment'
        $subObjects = ['colis', 'shipment', 'package', 'result'];
        foreach ($subObjects as $subObj) {
            if (isset($responseData[$subObj]) && is_array($responseData[$subObj])) {
                foreach ($directFields as $field) {
                    if (isset($responseData[$subObj][$field]) && !empty($responseData[$subObj][$field])) {
                        Log::info("[JAX] Tracking trouvé dans '{$subObj}.{$field}'", ['value' => $responseData[$subObj][$field]]);
                        return (string) $responseData[$subObj][$field];
                    }
                }
            }
        }

        // 4. Recherche récursive pour les IDs numériques
        $numericId = $this->findNumericId($responseData);
        if ($numericId) {
            Log::info("[JAX] ID numérique trouvé", ['value' => $numericId]);
            return $numericId;
        }

        Log::error('[JAX] Aucun tracking number trouvé', [
            'response_structure' => $this->getArrayStructure($responseData),
            'response_data' => $responseData,
        ]);

        return null;
    }

    /**
     * 🆕 MÉTHODE HELPER : Chercher un ID numérique dans la réponse
     */
    protected function findNumericId($data, $depth = 0): ?string
    {
        if ($depth > 3 || !is_array($data)) {
            return null;
        }

        foreach ($data as $key => $value) {
            // Chercher les clés qui pourraient contenir un ID
            if (is_numeric($value) && strlen((string)$value) >= 10) {
                return (string) $value;
            }

            if (is_array($value)) {
                $result = $this->findNumericId($value, $depth + 1);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * 🆕 MÉTHODE HELPER : Obtenir la structure d'un array pour debug
     */
    protected function getArrayStructure($data): array
    {
        if (!is_array($data)) {
            return ['type' => gettype($data)];
        }

        $structure = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $structure[$key] = 'array(' . count($value) . ')';
            } else {
                $structure[$key] = gettype($value);
            }
        }

        return $structure;
    }

    /**
     * Obtenir le token d'authentification
     */
    protected function getApiToken(): string
    {
        $token = $this->config['api_token'] ?? null;
        
        if (!$token) {
            throw new CarrierServiceException('Token JAX manquant dans la configuration');
        }

        return $token;
    }

    /**
     * 🔧 CORRECTION : Nettoyage des numéros moins strict
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        
        // Supprimer tous les caractères non numériques
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Si le numéro commence par +216, enlever le préfixe
        if (str_starts_with($cleaned, '216')) {
            $cleaned = substr($cleaned, 3);
        }
        
        // Si le numéro a plus de 8 chiffres, prendre les 8 derniers
        if (strlen($cleaned) > 8) {
            $cleaned = substr($cleaned, -8);
        }
        
        Log::debug('🧹 [JAX] Numéro nettoyé', [
            'original' => $phone,
            'cleaned' => $cleaned,
            'length' => strlen($cleaned),
        ]);
        
        return $cleaned;
    }

    /**
     * 🔧 CORRECTION : Mapping gouvernorats plus complet
     */
    protected function mapGovernorateToJaxCode($governorate): string
    {
        $mapping = [
            // Normalisation des noms
            'Tunis' => '11', 'tunis' => '11', 'TUNIS' => '11',
            'Ariana' => '12', 'ariana' => '12', 'ARIANA' => '12',
            'Ben Arous' => '13', 'ben arous' => '13', 'BEN AROUS' => '13',
            'Manouba' => '14', 'manouba' => '14', 'La Manouba' => '14',
            
            'Nabeul' => '21', 'nabeul' => '21', 'NABEUL' => '21',
            'Zaghouan' => '22', 'zaghouan' => '22',
            'Bizerte' => '23', 'bizerte' => '23',
            
            'Béja' => '31', 'beja' => '31', 'Beja' => '31',
            'Jendouba' => '32', 'jendouba' => '32',
            'Le Kef' => '33', 'Kef' => '33', 'kef' => '33',
            'Siliana' => '34', 'siliana' => '34',
            
            'Kairouan' => '41', 'kairouan' => '41',
            'Kasserine' => '42', 'kasserine' => '42',
            'Sidi Bouzid' => '43', 'sidi bouzid' => '43',
            
            'Sousse' => '51', 'sousse' => '51', 'SOUSSE' => '51',
            'Monastir' => '52', 'monastir' => '52',
            'Mahdia' => '53', 'mahdia' => '53',
            
            'Sfax' => '61', 'sfax' => '61', 'SFAX' => '61',
            
            'Gafsa' => '71', 'gafsa' => '71',
            'Tozeur' => '72', 'tozeur' => '72',
            'Kebili' => '73', 'kebili' => '73', 'Kébili' => '73',
            
            'Gabès' => '81', 'gabes' => '81', 'Gabes' => '81',
            'Medenine' => '82', 'medenine' => '82', 'Médenine' => '82',
            'Tataouine' => '83', 'tataouine' => '83',
        ];

        $code = $mapping[$governorate] ?? $mapping[strtolower($governorate)] ?? '11';
        
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
        ];

        $internalStatus = $mapping[(string)$jaxStatus] ?? 'unknown';
        
        Log::debug('🔄 [JAX] Mapping statut', [
            'jax_status' => $jaxStatus,
            'internal_status' => $internalStatus,
            'found_in_mapping' => isset($mapping[(string)$jaxStatus]),
        ]);

        return $internalStatus;
    }
}