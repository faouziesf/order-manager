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
            'has_username' => !empty($config['username']),
            'has_api_key' => !empty($config['api_key']),
            'username_preview' => !empty($config['username']) ? substr($config['username'], 0, 4) . '***' : 'vide',
            'token_preview' => !empty($config['api_key']) ? substr($config['api_key'], 0, 10) . '...' : 'vide',
        ]);
    }

    public function createShipment(array $data): array
    {
        Log::info('🚀 [JAX] Création colis', [
            'recipient' => $data['recipient_name'] ?? 'Non défini',
            'cod_amount' => $data['cod_amount'] ?? 0,
        ]);

        try {
            $token = $this->getToken();
            
            // 🆕 CORRECTION : Préparer les données selon la structure JAX réelle
            $jaxData = [
                'referenceExterne' => $data['external_reference'] ?? '',
                'nomContact' => $data['recipient_name'] ?? '',
                'tel' => $data['recipient_phone'] ?? '',
                'tel2' => $data['recipient_phone_2'] ?? '',
                'adresseLivraison' => $data['recipient_address'] ?? '',
                'governorat' => $this->mapGovernorate($data['recipient_governorate'] ?? 'Tunis'),
                'delegation' => $data['recipient_city'] ?? '',
                'description' => substr($data['content_description'] ?? 'Colis e-commerce', 0, 100),
                'cod' => (string)($data['cod_amount'] ?? 0),
                'echange' => 0,
            ];

            Log::info('📤 [JAX] Envoi vers API', [
                'url' => $this->baseUrl . '/user/colis/add',
                'governorat_code' => $jaxData['governorat'],
                'cod' => $jaxData['cod'],
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/user/colis/add', $jaxData);

            Log::info('📥 [JAX] Réponse reçue', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 200),
            ]);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();
            
            // 🆕 CORRECTION : Gérer différents formats de réponse JAX
            $trackingNumber = null;
            if (isset($responseData['ean'])) {
                $trackingNumber = $responseData['ean'];
            } elseif (isset($responseData['id'])) {
                $trackingNumber = $responseData['id'];
            } elseif (isset($responseData['data']['ean'])) {
                $trackingNumber = $responseData['data']['ean'];
            }

            if (!$trackingNumber) {
                Log::warning('⚠️ [JAX] Pas de numéro de suivi dans la réponse', ['response' => $responseData]);
                throw new CarrierServiceException('Pas de numéro de suivi dans la réponse JAX');
            }

            Log::info('✅ [JAX] Colis créé avec succès', [
                'tracking_number' => $trackingNumber,
                'response_keys' => array_keys($responseData),
            ]);

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'response' => $responseData,
            ];

        } catch (CarrierServiceException $e) {
            Log::error('❌ [JAX] Erreur transporteur', [
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse(),
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
            $token = $this->getToken();
            
            $jaxData = [
                'adresse' => $data['address'] ?? 'Adresse pickup',
                'nbrColis' => (string)count($data['tracking_numbers'] ?? []),
                'colis_statut' => '10',
                'colis_list' => $data['tracking_numbers'] ?? [],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
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
            $token = $this->getToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->timeout(15)->get($this->baseUrl . "/user/colis/getstatubyean/{$trackingNumber}");

            if ($response->failed()) {
                throw new CarrierServiceException("Erreur JAX statut: " . $response->body());
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'status' => $this->mapStatus($responseData['status'] ?? 'unknown'),
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
            $token = $this->getToken();
            
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
                    'message' => 'Connexion JAX réussie',
                ];
            }

            return [
                'success' => false,
                'message' => "Échec connexion JAX (HTTP {$response->status()})",
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
     * 🆕 CORRECTION : Obtenir le token d'authentification
     */
    protected function getToken(): string
    {
        $token = $this->config['api_key'] ?? null;
        
        if (!$token) {
            Log::error('❌ [JAX] Token manquant dans config', [
                'config_keys' => array_keys($this->config),
            ]);
            throw new CarrierServiceException('Token JAX manquant dans la configuration');
        }

        // Le token est maintenant stocké en clair, plus besoin de déchiffrement
        Log::debug('🔑 [JAX] Token récupéré', [
            'token_length' => strlen($token),
            'token_preview' => substr($token, 0, 10) . '...',
        ]);

        return $token;
    }

    /**
     * 🆕 CORRECTION : Mapper les gouvernorats vers codes JAX
     */
    protected function mapGovernorate($governorate): string
    {
        $mapping = [
            'Tunis' => '11', 'Ariana' => '12', 'Ben Arous' => '13', 'Manouba' => '14',
            'Nabeul' => '21', 'Zaghouan' => '22', 'Bizerte' => '23',
            'Béja' => '31', 'Jendouba' => '32', 'Le Kef' => '33', 'Siliana' => '34',
            'Kairouan' => '41', 'Kasserine' => '42', 'Sidi Bouzid' => '43',
            'Sousse' => '51', 'Monastir' => '52', 'Mahdia' => '53',
            'Sfax' => '61', 'Gafsa' => '71', 'Tozeur' => '72', 'Kebili' => '73',
            'Gabès' => '81', 'Medenine' => '82', 'Tataouine' => '83',
        ];

        $code = $mapping[$governorate] ?? '11'; // Par défaut Tunis
        
        Log::debug('🗺️ [JAX] Mapping gouvernorat', [
            'input' => $governorate,
            'output' => $code,
        ]);

        return $code;
    }

    /**
     * Mapper les statuts JAX vers statuts internes
     */
    protected function mapStatus($jaxStatus): string
    {
        $mapping = [
            '1' => 'created', '2' => 'validated', '3' => 'picked_up_by_carrier',
            '4' => 'in_transit', '5' => 'delivered', '6' => 'delivery_failed',
            '7' => 'in_return', '8' => 'returned', '9' => 'anomaly', '10' => 'created',
        ];

        return $mapping[(string)$jaxStatus] ?? 'unknown';
    }
}

// ========================================
// SERVICE MES COLIS CORRIGÉ
// ========================================

class MesColisService implements CarrierServiceInterface
{
    protected $config;
    protected $baseUrl = 'https://api.mescolis.tn/api';

    public function __construct(array $config)
    {
        $this->config = $config;
        
        Log::info('🔧 [MES COLIS] Service initialisé', [
            'has_api_key' => !empty($config['api_key']),
            'token_preview' => !empty($config['api_key']) ? substr($config['api_key'], 0, 8) . '...' : 'vide',
        ]);
    }

    public function createShipment(array $data): array
    {
        Log::info('🚀 [MES COLIS] Création colis', [
            'recipient' => $data['recipient_name'] ?? 'Non défini',
            'cod_amount' => $data['cod_amount'] ?? 0,
        ]);

        try {
            $token = $this->getToken();
            
            // 🆕 CORRECTION : Préparer les données selon la structure Mes Colis
            $mesColisData = [
                'product_name' => substr($data['content_description'] ?? 'Produits e-commerce', 0, 100),
                'client_name' => $data['recipient_name'] ?? '',
                'address' => $data['recipient_address'] ?? '',
                'gouvernerate' => $this->mapGovernorate($data['recipient_governorate'] ?? 'Tunis'),
                'city' => $data['recipient_city'] ?? '',
                'location' => $data['recipient_address'] ?? '',
                'Tel1' => $data['recipient_phone'] ?? '',
                'Tel2' => $data['recipient_phone_2'] ?? '',
                'price' => (string)($data['cod_amount'] ?? 0),
                'exchange' => '0',
                'open_ordre' => '0',
                'note' => substr($data['notes'] ?? '', 0, 200),
            ];

            Log::info('📤 [MES COLIS] Envoi vers API', [
                'url' => $this->baseUrl . '/orders/Create',
                'gouvernerate' => $mesColisData['gouvernerate'],
                'price' => $mesColisData['price'],
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/orders/Create', $mesColisData);

            Log::info('📥 [MES COLIS] Réponse reçue', [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200),
            ]);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur Mes Colis (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();
            
            // 🆕 CORRECTION : Gérer différents formats de réponse Mes Colis
            $trackingNumber = null;
            if (isset($responseData['barcode'])) {
                $trackingNumber = $responseData['barcode'];
            } elseif (isset($responseData['id'])) {
                $trackingNumber = $responseData['id'];
            } elseif (isset($responseData['data']['barcode'])) {
                $trackingNumber = $responseData['data']['barcode'];
            }

            if (!$trackingNumber) {
                Log::warning('⚠️ [MES COLIS] Pas de numéro de suivi dans la réponse', ['response' => $responseData]);
                throw new CarrierServiceException('Pas de numéro de suivi dans la réponse Mes Colis');
            }

            Log::info('✅ [MES COLIS] Colis créé avec succès', [
                'tracking_number' => $trackingNumber,
                'response_keys' => array_keys($responseData),
            ]);

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'response' => $responseData,
            ];

        } catch (CarrierServiceException $e) {
            Log::error('❌ [MES COLIS] Erreur transporteur', [
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur générale', [
                'error' => $e->getMessage(),
            ]);
            throw new CarrierServiceException('Erreur Mes Colis: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function createPickup(array $data): array
    {
        // Mes Colis n'a pas d'API pickup dédiée
        Log::info('🚛 [MES COLIS] Pickup simulé (pas d\'API dédiée)');

        return [
            'success' => true,
            'pickup_id' => 'PICKUP_MESCOLIS_' . time(),
            'response' => [
                'message' => 'Pickup simulé - Mes Colis n\'a pas d\'API pickup dédiée',
                'tracking_numbers' => $data['tracking_numbers'] ?? [],
            ],
        ];
    }

    public function getShipmentStatus(string $trackingNumber): array
    {
        try {
            $token = $this->getToken();
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
            ])->timeout(15)->post($this->baseUrl . '/orders/GetOrder', [
                'barcode' => $trackingNumber,
            ]);

            if ($response->failed()) {
                throw new CarrierServiceException("Erreur Mes Colis statut: " . $response->body());
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'status' => $this->mapStatus($responseData['status'] ?? 'unknown'),
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur statut', ['tracking' => $trackingNumber, 'error' => $e->getMessage()]);
            throw new CarrierServiceException('Erreur Mes Colis statut: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function testConnection(): array
    {
        Log::info('🧪 [MES COLIS] Test de connexion');
        
        try {
            $token = $this->getToken();
            
            // Test avec barcode fictif pour vérifier l'auth
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
            ])->timeout(10)->post($this->baseUrl . '/orders/GetOrder', [
                'barcode' => 'TEST_' . time(),
            ]);

            Log::info('🧪 [MES COLIS] Réponse test', [
                'status' => $response->status(),
            ]);

            // Status 200 ou 404 indique que l'auth fonctionne
            if ($response->status() === 200 || $response->status() === 404) {
                return [
                    'success' => true,
                    'message' => 'Connexion Mes Colis réussie',
                ];
            }

            return [
                'success' => false,
                'message' => "Échec connexion Mes Colis (HTTP {$response->status()})",
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur test connexion', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erreur connexion Mes Colis: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 🆕 CORRECTION : Obtenir le token d'authentification
     */
    protected function getToken(): string
    {
        $token = $this->config['api_key'] ?? null;
        
        if (!$token) {
            Log::error('❌ [MES COLIS] Token manquant dans config', [
                'config_keys' => array_keys($this->config),
            ]);
            throw new CarrierServiceException('Token Mes Colis manquant dans la configuration');
        }

        Log::debug('🔑 [MES COLIS] Token récupéré', [
            'token_length' => strlen($token),
            'token_preview' => substr($token, 0, 8) . '...',
        ]);

        return $token;
    }

    /**
     * 🆕 CORRECTION : Mapper les gouvernorats vers noms Mes Colis
     */
    protected function mapGovernorate($governorate): string
    {
        $mapping = [
            'Tunis' => 'Tunis', 'Ariana' => 'Ariana', 'Ben Arous' => 'Ben Arous', 
            'Manouba' => 'La Mannouba', 'Nabeul' => 'Nabeul', 'Zaghouan' => 'Zaghouan',
            'Bizerte' => 'Bizerte', 'Béja' => 'Béja', 'Jendouba' => 'Jendouba',
            'Le Kef' => 'Le Kef', 'Siliana' => 'Siliana', 'Kairouan' => 'Kairouan',
            'Kasserine' => 'Kasserine', 'Sidi Bouzid' => 'Sidi Bouzid',
            'Sousse' => 'Sousse', 'Monastir' => 'Monastir', 'Mahdia' => 'Mahdia',
            'Sfax' => 'Sfax', 'Gafsa' => 'Gafsa', 'Tozeur' => 'Tozeur',
            'Kebili' => 'Kébili', 'Gabès' => 'Gabès', 'Medenine' => 'Médenine',
            'Tataouine' => 'Tataouine',
        ];

        $mapped = $mapping[$governorate] ?? 'Tunis'; // Par défaut Tunis
        
        Log::debug('🗺️ [MES COLIS] Mapping gouvernorat', [
            'input' => $governorate,
            'output' => $mapped,
        ]);

        return $mapped;
    }

    /**
     * Mapper les statuts Mes Colis vers statuts internes
     */
    protected function mapStatus($mesColisStatus): string
    {
        $mapping = [
            'En attente' => 'created',
            'En cours' => 'validated',
            'Au magasin' => 'picked_up_by_carrier',
            'Retour au dépôt' => 'in_return',
            'Livré' => 'delivered',
            'Retour client/agence' => 'in_return',
            'Retour définitif' => 'returned',
        ];

        return $mapping[$mesColisStatus] ?? 'unknown';
    }
}