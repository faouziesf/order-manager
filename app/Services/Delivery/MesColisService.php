<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MesColisService implements CarrierServiceInterface
{
    protected $config;
    protected $baseUrl = 'https://api.mescolis.tn/api';

    public function __construct(array $config)
    {
        $this->config = $config;
        
        Log::info('🔧 [MES COLIS] Service initialisé', [
            'has_api_token' => !empty($config['api_token']),
            'token_preview' => !empty($config['api_token']) ? substr($config['api_token'], 0, 8) . '...' : 'vide',
            'environment' => $config['environment'] ?? 'test',
        ]);
    }

    public function createShipment(array $data): array
    {
        Log::info('🚀 [MES COLIS] Création colis dans le compte Mes Colis', [
            'recipient' => $data['recipient_name'] ?? 'Non défini',
            'phone' => $data['recipient_phone'] ?? 'Non défini',
            'cod_amount' => $data['cod_amount'] ?? 0,
            'governorate' => $data['recipient_governorate'] ?? 'Non défini',
        ]);

        try {
            $token = $this->getApiToken();
            
            // 🔧 CORRECTION : Préparer les données selon la structure exacte de l'API Mes Colis
            $mesColisData = [
                'product_name' => substr($data['content_description'] ?? 'Produits e-commerce', 0, 100),
                'client_name' => $data['recipient_name'] ?? '',
                'address' => $data['recipient_address'] ?? '',
                'gouvernerate' => $this->mapGovernorateToMesColisName($data['recipient_governorate'] ?? 'Tunis'),
                'city' => $data['recipient_city'] ?? '',
                'location' => $data['recipient_address'] ?? '', // Point de repère
                'Tel1' => $this->cleanPhoneNumber($data['recipient_phone'] ?? ''),
                'Tel2' => $this->cleanPhoneNumber($data['recipient_phone_2'] ?? ''),
                'price' => (string)($data['cod_amount'] ?? 0), // 🔧 CORRECTION : Price doit être string
                'exchange' => '0', // Pas d'échange
                'open_ordre' => '0', // Pas d'ouverture autorisée
                'note' => substr($data['notes'] ?? 'Commande e-commerce', 0, 200),
            ];

            Log::info('📤 [MES COLIS] Envoi vers API Mes Colis', [
                'url' => $this->baseUrl . '/orders/Create',
                'data' => [
                    'client_name' => $mesColisData['client_name'],
                    'Tel1' => $mesColisData['Tel1'],
                    'gouvernerate' => $mesColisData['gouvernerate'],
                    'price' => $mesColisData['price'],
                ],
                'token_preview' => substr($token, 0, 8) . '...',
            ]);

            // 🔥 APPEL CRITIQUE API MES COLIS
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/orders/Create', $mesColisData);

            Log::info('📥 [MES COLIS] Réponse API reçue', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 300),
            ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('❌ [MES COLIS] Échec API', [
                    'status' => $response->status(),
                    'error_body' => $errorBody,
                    'request_data' => $mesColisData,
                ]);
                
                throw new CarrierServiceException(
                    "Erreur Mes Colis API (HTTP {$response->status()}): " . $errorBody,
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();
            
            // 🔧 CORRECTION : Gérer différents formats de réponse Mes Colis
            $trackingNumber = $this->extractTrackingNumber($responseData);

            if (!$trackingNumber) {
                Log::warning('⚠️ [MES COLIS] Pas de numéro de suivi dans la réponse', [
                    'response_data' => $responseData,
                    'response_keys' => array_keys($responseData),
                ]);
                throw new CarrierServiceException('Mes Colis API: Pas de numéro de suivi retourné');
            }

            Log::info('✅ [MES COLIS] Colis créé avec succès dans le compte Mes Colis', [
                'tracking_number' => $trackingNumber,
                'response_structure' => array_keys($responseData),
                'cod_amount' => $mesColisData['price'],
                'recipient' => $mesColisData['client_name'],
            ]);

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'response' => $responseData,
                'carrier_name' => 'Mes Colis Express',
            ];

        } catch (CarrierServiceException $e) {
            Log::error('❌ [MES COLIS] Erreur transporteur', [
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse(),
                'config_check' => [
                    'has_token' => !empty($this->config['api_token']),
                ],
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur générale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config_check' => [
                    'has_token' => !empty($this->config['api_token']),
                ],
            ]);
            throw new CarrierServiceException('Erreur Mes Colis: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function createPickup(array $data): array
    {
        // Mes Colis n'a pas d'API pickup dédiée selon la documentation
        Log::info('🚛 [MES COLIS] Pickup simulé (API pickup non disponible)');

        return [
            'success' => true,
            'pickup_id' => 'PICKUP_MESCOLIS_' . time(),
            'response' => [
                'message' => 'Pickup simulé - Mes Colis n\'a pas d\'API pickup dédiée',
                'tracking_numbers' => $data['tracking_numbers'] ?? [],
                'note' => 'Les colis sont automatiquement prêts pour enlèvement après création',
            ],
        ];
    }

    public function getShipmentStatus(string $trackingNumber): array
    {
        try {
            $token = $this->getApiToken();
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
                'Accept' => 'application/json',
            ])->timeout(15)->post($this->baseUrl . '/orders/GetOrder', [
                'barcode' => $trackingNumber,
            ]);

            if ($response->failed()) {
                throw new CarrierServiceException("Erreur Mes Colis statut: " . $response->body());
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'status' => $this->mapMesColisStatusToInternal($responseData['status'] ?? 'unknown'),
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
            $token = $this->getApiToken();
            
            // Test avec barcode fictif pour vérifier l'auth
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
                'Accept' => 'application/json',
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
                    'message' => 'Connexion Mes Colis réussie - Token valide',
                ];
            }

            return [
                'success' => false,
                'message' => "Échec connexion Mes Colis (HTTP {$response->status()}) - Vérifiez le token",
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
     * Obtenir le token d'authentification
     */
    protected function getApiToken(): string
    {
        $token = $this->config['api_token'] ?? null;
        
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
     * Extraire le numéro de suivi de la réponse Mes Colis
     */
    protected function extractTrackingNumber($responseData): ?string
    {
        // Tenter différents champs possibles dans la réponse Mes Colis
        $possibleFields = ['barcode', 'id', 'tracking_number', 'reference', 'order_id'];
        
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
        
        Log::debug('🧹 [MES COLIS] Nettoyage numéro téléphone', [
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
            Log::debug('✅ [MES COLIS] Numéro tunisien valide à 8 chiffres', [
                'phone' => $cleaned,
                'first_digit' => $cleaned[0],
            ]);
            return $cleaned;
        }
        
        // Si le numéro est trop long, prendre les 8 derniers chiffres
        if (strlen($cleaned) > 8) {
            $cleaned = substr($cleaned, -8);
            Log::debug('✂️ [MES COLIS] Numéro tronqué aux 8 derniers chiffres', [
                'phone' => $cleaned,
            ]);
        }
        
        // Si le numéro est trop court, le laisser tel quel (l'API pourrait le refuser)
        if (strlen($cleaned) < 8) {
            Log::warning('⚠️ [MES COLIS] Numéro trop court', [
                'phone' => $cleaned,
                'length' => strlen($cleaned),
            ]);
        }
        
        return $cleaned;
    }

    /**
     * 🔧 CORRECTION : Mapper les gouvernorats vers noms Mes Colis (mapping complet et vérifié)
     */
    protected function mapGovernorateToMesColisName($governorate): string
    {
        $mapping = [
            // Grand Tunis
            'Tunis' => 'Tunis', 
            'Ariana' => 'Ariana', 
            'Ben Arous' => 'Ben Arous', 
            'Manouba' => 'La Mannouba', 
            'La Mannouba' => 'La Mannouba',
            
            // Nord-Est
            'Nabeul' => 'Nabeul', 
            'Zaghouan' => 'Zaghouan', 
            'Bizerte' => 'Bizerte',
            
            // Nord-Ouest
            'Béja' => 'Béja', 
            'Jendouba' => 'Jendouba', 
            'Le Kef' => 'Le Kef', 
            'Siliana' => 'Siliana',
            
            // Centre-Ouest 
            'Kairouan' => 'Kairouan', 
            'Kasserine' => 'Kasserine', 
            'Sidi Bouzid' => 'Sidi Bouzid',
            
            // Centre-Est
            'Sousse' => 'Sousse', 
            'Monastir' => 'Monastir', 
            'Mahdia' => 'Mahdia',
            
            // Sud-Est
            'Sfax' => 'Sfax',
            
            // Sud-Ouest
            'Gafsa' => 'Gafsa', 
            'Tozeur' => 'Tozeur',
            'Kebili' => 'Kébili', 
            'Kébili' => 'Kébili',
            
            // Sud
            'Gabès' => 'Gabès', 
            'Medenine' => 'Médenine', 
            'Médenine' => 'Médenine', 
            'Tataouine' => 'Tataouine',
        ];

        $mapped = $mapping[$governorate] ?? 'Tunis'; // Par défaut Tunis
        
        Log::debug('🗺️ [MES COLIS] Mapping gouvernorat', [
            'input' => $governorate,
            'output' => $mapped,
            'found_in_mapping' => isset($mapping[$governorate]),
        ]);

        return $mapped;
    }

    /**
     * 🔧 CORRECTION : Mapper les statuts Mes Colis vers statuts internes (mapping complet)
     */
    protected function mapMesColisStatusToInternal($mesColisStatus): string
    {
        $mapping = [
            // Statuts principaux
            'En attente' => 'created',
            'En cours' => 'validated',
            'Au magasin' => 'picked_up_by_carrier',
            'Retour au dépôt' => 'in_return',
            'Livré' => 'delivered',
            
            // Statuts de retour
            'Retour client/agence' => 'in_return',
            'Retour définitif' => 'returned',
            'Retour reçu' => 'returned',
            'Retour payé' => 'returned',
            'Retour expéditeur' => 'returned',
            
            // Statuts spéciaux
            'À vérifier' => 'anomaly',
            'Échange' => 'delivery_attempted',
            'À enlever' => 'created',
            'Enlevé' => 'picked_up_by_carrier',
            'Non reçu' => 'anomaly',
            'Supprimé' => 'cancelled',
            'Inconnu' => 'unknown',
        ];

        $internalStatus = $mapping[$mesColisStatus] ?? 'unknown';
        
        Log::debug('🔄 [MES COLIS] Mapping statut', [
            'mes_colis_status' => $mesColisStatus,
            'internal_status' => $internalStatus,
            'found_in_mapping' => isset($mapping[$mesColisStatus]),
        ]);

        return $internalStatus;
    }
}