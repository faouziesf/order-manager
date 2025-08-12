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
    }

    public function createShipment(array $data): array
    {
        Log::info('🚀 [MES COLIS] Création colis', ['data' => $data]);

        try {
            $token = $this->getToken();
            
            // Préparer les données pour Mes Colis
            $mesColisData = [
                'product_name' => $data['content_description'] ?? 'Produits e-commerce',
                'client_name' => $data['recipient_name'] ?? '',
                'address' => $data['recipient_address'] ?? '',
                'gouvernerate' => $this->mapGovernorate($data['recipient_governorate'] ?? ''),
                'city' => $data['recipient_city'] ?? '',
                'location' => $data['recipient_address'] ?? '',
                'Tel1' => $data['recipient_phone'] ?? '',
                'Tel2' => $data['recipient_phone_2'] ?? '',
                'price' => (string)($data['cod_amount'] ?? 0),
                'exchange' => '0',
                'open_ordre' => '0',
                'note' => $data['notes'] ?? '',
            ];

            Log::info('📤 [MES COLIS] Envoi vers API', ['mes_colis_data' => $mesColisData]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
            ])->timeout(30)->post($this->baseUrl . '/orders/Create', $mesColisData);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur Mes Colis (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();
            Log::info('✅ [MES COLIS] Colis créé', ['response' => $responseData]);

            return [
                'success' => true,
                'tracking_number' => $responseData['barcode'] ?? $responseData['id'] ?? null,
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MES COLIS] Erreur création colis', ['error' => $e->getMessage()]);
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
        try {
            $token = $this->getToken();
            
            // Test avec barcode fictif
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-access-token' => $token,
            ])->timeout(10)->post($this->baseUrl . '/orders/GetOrder', [
                'barcode' => 'TEST_' . time(),
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
                'message' => 'Échec connexion Mes Colis',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur connexion Mes Colis: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtenir le token d'authentification
     */
    protected function getToken(): string
    {
        $token = $this->config['api_key'] ?? $this->config['username'] ?? null;
        
        if (!$token) {
            throw new CarrierServiceException('Token Mes Colis manquant');
        }

        // Essayer de déchiffrer
        try {
            return decrypt($token);
        } catch (\Exception $e) {
            return $token; // Utiliser tel quel si déchiffrement échoue
        }
    }

    /**
     * Mapper les gouvernorats vers noms Mes Colis
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

        return $mapping[$governorate] ?? 'Tunis'; // Par défaut Tunis
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