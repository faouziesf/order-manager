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
                'cod' => (string)($data['cod_amount'] ?? 0), // 🔧 CORRECTION : COD doit être string
                'echange' => 0, // Pas d'échange
            ];

            Log::info('📤 [JAX] Envoi vers API JAX', [
                'url' => $this->baseUrl . '/user/colis/add',
                'data' => [
                    'nomContact' => $jaxData['nomContact'],
                    'tel' => $jaxData['tel'],
                    'governorat' => $jaxData['governorat'],
                    'cod' => $jaxData['cod'],
                ],
                'token_preview' => substr($token, 0, 20) . '...',
            ]);

            // 🔥 APPEL CRITIQUE API JAX
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/user/colis/add', $jaxData);

            Log::info('📥 [JAX] Réponse API reçue', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 300),
            ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('❌ [JAX] Échec API', [
                    'status' => $response->status(),
                    'error_body' => $errorBody,
                    'request_data' => $jaxData,
                ]);
                
                throw new CarrierServiceException(
                    "Erreur JAX API (HTTP {$response->status()}): " . $errorBody,
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();
            
            // 🔧 CORRECTION : Gérer différents formats de réponse JAX
            $trackingNumber = $this->extractTrackingNumber($responseData);

            if (!$trackingNumber) {
                Log::warning('⚠️ [JAX] Pas de numéro de suivi dans la réponse', [
                    'response_data' => $responseData,
                    'response_keys' => array_keys($responseData),
                ]);
                throw new CarrierServiceException('JAX API: Pas de numéro de suivi retourné');
            }

            Log::info('✅ [JAX] Colis créé avec succès dans le compte JAX', [
                'tracking_number' => $trackingNumber,
                'response_structure' => array_keys($responseData),
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
                ],
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur générale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config_check' => [
                    'has_token' => !empty($this->config['api_token']),
                    'has_username' => !empty($this->config['username']),
                ],
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
            
            // Test avec l'endpoint des gouvernorats
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(10)->get($this->baseUrl . '/gouvernorats');

            Log::info('🧪 [JAX] Réponse test', [
                'status' => $response->status(),
                'successful' => $response->successful(),
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
     * Extraire le numéro de suivi de la réponse JAX
     */
    protected function extractTrackingNumber($responseData): ?string
    {
        // Tenter différents champs possibles dans la réponse JAX
        $possibleFields = ['ean', 'id', 'barcode', 'tracking_number', 'reference'];
        
        foreach ($possibleFields as $field) {
            if (isset($responseData[$field]) && !empty($responseData[$field])) {
                return (string) $responseData[$field];
            }
            
            // Vérifier aussi dans data si présent
            if (isset($responseData['data'][$field]) && !empty($responseData['data'][$field])) {
                return (string) $responseData['data'][$field];
            }
        }
        
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