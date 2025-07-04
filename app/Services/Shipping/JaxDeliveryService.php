<?php

namespace App\Services\Shipping;

use App\Models\DeliveryConfiguration;
use App\Models\Order;
use App\Models\PickupAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JaxDeliveryService implements CarrierServiceInterface
{
    private DeliveryConfiguration $config;
    private string $baseUrl;

    public function __construct(DeliveryConfiguration $config)
    {
        $this->config = $config;
        $this->baseUrl = config('carriers.jax_delivery.api_endpoints.' . $config->environment, 
            'https://core.jax-delivery.com/api');
    }

    // ========================================
    // MÉTHODES PRINCIPALES
    // ========================================

    public function createShipment(Order $order, ?PickupAddress $pickupAddress = null): array
    {
        try {
            $payload = [
                'referenceExterne' => "ORDER_{$order->id}",
                'nomContact' => $order->customer_name,
                'tel' => $order->customer_phone,
                'adresseLivraison' => $order->customer_address,
                'governorat' => $this->mapGovernorate($order->customer_governorate),
                'cod' => $order->total_price,
                'remarque' => "Commande #{$order->id}",
            ];

            Log::info('JaxDelivery createShipment', [
                'order_id' => $order->id,
                'payload' => $payload
            ]);

            $response = Http::withToken($this->config->token)
                ->timeout(30)
                ->post($this->baseUrl . '/user/colis/add', $payload);

            if (!$response->successful()) {
                throw new \Exception('Erreur API Jax: ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['ean'])) {
                throw new \Exception('Réponse invalide de Jax: EAN manquant');
            }

            return [
                'success' => true,
                'tracking_number' => $data['ean'],
                'ean' => $data['ean'],
                'reference' => $payload['referenceExterne'],
                'carrier_response' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('JaxDelivery createShipment failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function trackShipment(string $trackingNumber): ?array
    {
        try {
            $response = Http::withToken($this->config->token)
                ->timeout(30)
                ->get($this->baseUrl . '/user/colis/statut', [
                    'ean' => $trackingNumber
                ]);

            if (!$response->successful()) {
                Log::warning('JaxDelivery trackShipment failed', [
                    'tracking_number' => $trackingNumber,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            return [
                'status' => $data['statut'] ?? null,
                'status_label' => $data['statut_libelle'] ?? null,
                'location' => $data['localisation'] ?? null,
                'updated_at' => $data['date_maj'] ?? null,
                'carrier_response' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('JaxDelivery trackShipment failed', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function testConnection(): array
    {
        try {
            $response = Http::withToken($this->config->token)
                ->timeout(10)
                ->get($this->baseUrl . '/user/profile');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion réussie avec Jax Delivery',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Échec de connexion: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage(),
            ];
        }
    }

    public function getToken(): array
    {
        // Jax utilise un token statique, pas de renouvellement automatique
        return [
            'token' => $this->config->token,
            'expires_at' => now()->addDays(30),
        ];
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        try {
            $response = Http::withToken($this->config->token)
                ->timeout(30)
                ->post($this->baseUrl . '/user/colis/annuler', [
                    'ean' => $trackingNumber
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('JaxDelivery cancelShipment failed', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ========================================
    // MÉTHODES DE CAPACITÉS
    // ========================================

    public function supportsPickupAddressSelection(): bool
    {
        return false; // Jax utilise l'adresse du compte
    }

    public function supportsBLGeneration(): bool
    {
        return false; // Jax génère ses propres BL
    }

    public function supportsLabelGeneration(): bool
    {
        return false; // Jax génère ses propres étiquettes
    }

    public function supportsDropPoints(): bool
    {
        return false;
    }

    public function supportsPickupScheduling(): bool
    {
        return true; // Jax supporte la programmation d'enlèvement
    }

    public function getCarrierInfo(): array
    {
        return config('carriers.jax_delivery', []);
    }

    // ========================================
    // MÉTHODES CONDITIONNELLES (NON SUPPORTÉES PAR JAX)
    // ========================================

    public function getMassLabels(array $trackingCodes): array
    {
        throw new \Exception('Jax Delivery ne supporte pas la génération d\'étiquettes en masse');
    }

    public function getPaymentMethods(): array
    {
        return []; // Jax utilise des méthodes fixes
    }

    public function getDropPoints(): array
    {
        return []; // Jax ne supporte pas les points de dépôt personnalisés
    }

    public function getAnomalyReasons(): array
    {
        return []; // Jax utilise ses propres motifs
    }

    public function schedulePickup(array $shipments, \DateTime $pickupDate): array
    {
        try {
            $eans = array_map(fn($shipment) => $shipment['tracking_number'], $shipments);

            $response = Http::withToken($this->config->token)
                ->timeout(30)
                ->post($this->baseUrl . '/user/pickup/programmer', [
                    'eans' => $eans,
                    'date_pickup' => $pickupDate->format('Y-m-d'),
                    'heure_debut' => '09:00',
                    'heure_fin' => '17:00',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'pickup_id' => $response->json()['pickup_id'] ?? null,
                    'scheduled_date' => $pickupDate->format('Y-m-d'),
                ];
            }

            throw new \Exception('Erreur lors de la programmation: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('JaxDelivery schedulePickup failed', [
                'error' => $e->getMessage(),
                'shipments_count' => count($shipments),
            ]);
            throw $e;
        }
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    private function mapGovernorate(string $governorate): string
    {
        $mapping = config('carriers.jax_delivery.default_settings.default_governorate_mapping', []);
        return $mapping[$governorate] ?? 'TUN';
    }
}