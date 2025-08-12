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
    }

    public function createShipment(array $data): array
    {
        Log::info('🚀 [JAX] Création colis', ['data' => $data]);

        try {
            $token = $this->getToken();
            
            // Préparer les données pour JAX
            $jaxData = [
                'referenceExterne' => $data['external_reference'] ?? '',
                'nomContact' => $data['recipient_name'] ?? '',
                'tel' => $data['recipient_phone'] ?? '',
                'tel2' => $data['recipient_phone_2'] ?? '',
                'adresseLivraison' => $data['recipient_address'] ?? '',
                'governorat' => $this->mapGovernorate($data['recipient_governorate'] ?? ''),
                'delegation' => $data['recipient_city'] ?? '',
                'description' => $data['content_description'] ?? 'Colis e-commerce',
                'cod' => (string)($data['cod_amount'] ?? 0),
                'echange' => 0,
            ];

            Log::info('📤 [JAX] Envoi vers API', ['jax_data' => $jaxData]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/user/colis/add', $jaxData);

            if ($response->failed()) {
                throw new CarrierServiceException(
                    "Erreur JAX (HTTP {$response->status()}): " . $response->body(),
                    $response->status(),
                    $response->json()
                );
            }

            $responseData = $response->json();
            Log::info('✅ [JAX] Colis créé', ['response' => $responseData]);

            return [
                'success' => true,
                'tracking_number' => $responseData['ean'] ?? $responseData['id'] ?? null,
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [JAX] Erreur création colis', ['error' => $e->getMessage()]);
            throw new CarrierServiceException('Erreur JAX: ' . $e->getMessage(), 500, null, $e);
        }
    }

    public function createPickup(array $data): array
    {
        Log::info('🚛 [JAX] Création pickup', ['data' => $data]);

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
        try {
            $token = $this->getToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->timeout(10)->get($this->baseUrl . '/gouvernorats');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion JAX réussie',
                ];
            }

            return [
                'success' => false,
                'message' => 'Échec connexion JAX',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur connexion JAX: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtenir le token d'authentification
     */
    protected function getToken(): string
    {
        $token = $this->config['api_key'] ?? $this->config['password'] ?? null;
        
        if (!$token) {
            throw new CarrierServiceException('Token JAX manquant');
        }

        // Essayer de déchiffrer
        try {
            return decrypt($token);
        } catch (\Exception $e) {
            return $token; // Utiliser tel quel si déchiffrement échoue
        }
    }

    /**
     * Mapper les gouvernorats vers codes JAX
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

        return $mapping[$governorate] ?? '11'; // Par défaut Tunis
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