<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\Contracts\CarrierValidationException;
use App\Models\Order;
use App\Models\DeliveryConfiguration;
use Illuminate\Support\Facades\Log;

/**
 * Service de simulation/mock pour les tests et développement
 */
class MockCarrierService implements CarrierServiceInterface
{
    protected $config;
    protected $carrierName;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->carrierName = $config['carrier_name'] ?? 'Mock Carrier';
    }

    /**
     * 🆕 NOUVELLE SIGNATURE - Compatible avec l'interface
     */
    public function createShipment(array $shipmentData): array
    {
        Log::info('🧪 [MOCK CARRIER] Création shipment simulée', [
            'shipment_data' => $shipmentData,
            'carrier' => $this->carrierName
        ]);

        try {
            // Valider les données requises
            $this->validateShipmentData($shipmentData);

            // Simuler un délai d'API
            if ($this->config['simulate_delay'] ?? false) {
                sleep(1);
            }

            // Simuler des erreurs aléatoirement pour les tests
            if ($this->config['simulate_errors'] ?? false) {
                if (rand(1, 10) <= 2) { // 20% de chance d'erreur
                    throw new CarrierServiceException(
                        'Erreur simulée pour test - ' . $this->carrierName,
                        500,
                        ['simulated' => true]
                    );
                }
            }

            // Générer un numéro de suivi fictif
            $trackingNumber = $this->generateMockTrackingNumber();

            Log::info('✅ [MOCK CARRIER] Shipment simulé créé', [
                'tracking_number' => $trackingNumber,
                'carrier' => $this->carrierName
            ]);

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'carrier_response' => [
                    'mock' => true,
                    'carrier' => $this->carrierName,
                    'created_at' => now()->toISOString(),
                    'simulated_response' => 'Colis créé avec succès (simulation)',
                ],
                'carrier_id' => $trackingNumber,
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MOCK CARRIER] Erreur création shipment simulée', [
                'error' => $e->getMessage(),
                'carrier' => $this->carrierName
            ]);
            throw $e;
        }
    }

    /**
     * 🆕 NOUVELLE SIGNATURE - Compatible avec l'interface
     */
    public function createPickup(array $pickupData): array
    {
        Log::info('🧪 [MOCK CARRIER] Création pickup simulée', [
            'pickup_data' => $pickupData,
            'carrier' => $this->carrierName
        ]);

        try {
            // Valider les données requises
            $this->validatePickupData($pickupData);

            // Simuler un délai d'API
            if ($this->config['simulate_delay'] ?? false) {
                sleep(1);
            }

            $pickupId = 'PICKUP_MOCK_' . time() . '_' . rand(1000, 9999);

            Log::info('✅ [MOCK CARRIER] Pickup simulé créé', [
                'pickup_id' => $pickupId,
                'carrier' => $this->carrierName
            ]);

            return [
                'success' => true,
                'pickup_id' => $pickupId,
                'carrier_response' => [
                    'mock' => true,
                    'carrier' => $this->carrierName,
                    'pickup_scheduled' => $pickupData['pickup_date'] ?? now()->addDay()->toDateString(),
                    'shipments_count' => count($pickupData['tracking_numbers'] ?? []),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MOCK CARRIER] Erreur création pickup simulée', [
                'error' => $e->getMessage(),
                'carrier' => $this->carrierName
            ]);
            throw $e;
        }
    }

    /**
     * 🆕 NOUVELLE SIGNATURE - Compatible avec l'interface
     */
    public function getShipmentStatus(string $trackingNumber): array
    {
        Log::info('🧪 [MOCK CARRIER] Récupération statut simulée', [
            'tracking_number' => $trackingNumber,
            'carrier' => $this->carrierName
        ]);

        try {
            // Simuler différents statuts selon le numéro de suivi
            $status = $this->simulateStatusFromTrackingNumber($trackingNumber);

            return [
                'success' => true,
                'status' => $status,
                'carrier_status' => strtoupper($status),
                'carrier_response' => [
                    'mock' => true,
                    'carrier' => $this->carrierName,
                    'tracking_number' => $trackingNumber,
                    'status' => $status,
                    'simulated_events' => $this->generateMockEvents($status),
                ],
                'last_update' => now(),
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MOCK CARRIER] Erreur récupération statut simulée', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
                'carrier' => $this->carrierName
            ]);
            throw $e;
        }
    }

    /**
     * 🆕 NOUVELLE SIGNATURE - Compatible avec l'interface
     */
    public function testConnection(): array
    {
        Log::info('🧪 [MOCK CARRIER] Test connexion simulée', [
            'carrier' => $this->carrierName
        ]);

        try {
            // Simuler un test de connexion
            if ($this->config['simulate_connection_failure'] ?? false) {
                throw new CarrierServiceException(
                    'Test de connexion échoué (simulation)',
                    500
                );
            }

            return [
                'success' => true,
                'message' => 'Connexion ' . $this->carrierName . ' réussie (simulation)',
                'response_time' => rand(100, 500), // ms
                'environment' => $this->config['environment'] ?? 'test',
                'mock' => true,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec connexion ' . $this->carrierName . ' (simulation): ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'mock' => true,
            ];
        }
    }

    /**
     * 🔄 MÉTHODE DE COMPATIBILITÉ - Ancienne signature pour rétrocompatibilité
     */
    public function createShipmentLegacy(Order $order, DeliveryConfiguration $config, array $additionalData = []): array
    {
        Log::info('🔄 [MOCK CARRIER] Utilisation méthode legacy', [
            'order_id' => $order->id,
            'config_id' => $config->id
        ]);

        // Convertir vers la nouvelle signature
        $shipmentData = [
            'external_reference' => "ORDER_{$order->id}",
            'recipient_info' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'phone_2' => $order->customer_phone_2,
                'address' => $order->customer_address,
                'governorate' => $order->customer_governorate,
                'city' => $order->customer_city,
            ],
            'cod_amount' => $order->total_price,
            'content_description' => "Commande #{$order->id}",
            'weight' => $additionalData['weight'] ?? 1.0,
            'nb_pieces' => $additionalData['nb_pieces'] ?? 1,
        ];

        // Appeler la nouvelle méthode
        return $this->createShipment($shipmentData);
    }

    /**
     * Valider les données de shipment
     */
    protected function validateShipmentData(array $data): void
    {
        $required = ['recipient_info', 'cod_amount'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new CarrierValidationException(
                'Données manquantes pour Mock Carrier: ' . implode(', ', $missing),
                422,
                $missing
            );
        }

        // Valider les informations du destinataire
        $recipientInfo = $data['recipient_info'];
        $requiredRecipient = ['name', 'phone', 'address'];
        $missingRecipient = [];

        foreach ($requiredRecipient as $field) {
            if (empty($recipientInfo[$field])) {
                $missingRecipient[] = $field;
            }
        }

        if (!empty($missingRecipient)) {
            throw new CarrierValidationException(
                'Informations destinataire manquantes pour Mock Carrier: ' . implode(', ', $missingRecipient),
                422,
                $missingRecipient
            );
        }
    }

    /**
     * Valider les données de pickup
     */
    protected function validatePickupData(array $data): void
    {
        $required = ['tracking_numbers'];
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new CarrierValidationException(
                'Données de pickup manquantes pour Mock Carrier: ' . implode(', ', $missing),
                422,
                $missing
            );
        }

        if (empty($data['tracking_numbers']) || !is_array($data['tracking_numbers'])) {
            throw new CarrierValidationException(
                'Liste des numéros de suivi requise pour Mock Carrier',
                422,
                ['tracking_numbers']
            );
        }
    }

    /**
     * Générer un numéro de suivi fictif
     */
    protected function generateMockTrackingNumber(): string
    {
        $prefix = strtoupper(substr($this->carrierName, 0, 3));
        $timestamp = time();
        $random = rand(1000, 9999);
        
        return "{$prefix}{$timestamp}{$random}";
    }

    /**
     * Simuler un statut basé sur le numéro de suivi
     */
    protected function simulateStatusFromTrackingNumber(string $trackingNumber): string
    {
        // Utiliser le hash du tracking number pour déterminer le statut
        $hash = crc32($trackingNumber);
        $statuses = ['created', 'validated', 'picked_up_by_carrier', 'in_transit', 'delivered'];
        
        return $statuses[abs($hash) % count($statuses)];
    }

    /**
     * Générer des événements de suivi fictifs
     */
    protected function generateMockEvents(string $status): array
    {
        $events = [
            [
                'date' => now()->subDays(2)->toISOString(),
                'status' => 'created',
                'description' => 'Colis créé (simulation)',
                'location' => 'Centre de tri principal',
            ],
            [
                'date' => now()->subDays(1)->toISOString(),
                'status' => 'validated',
                'description' => 'Colis validé et en cours de traitement (simulation)',
                'location' => 'Centre de tri principal',
            ],
        ];

        if (in_array($status, ['picked_up_by_carrier', 'in_transit', 'delivered'])) {
            $events[] = [
                'date' => now()->subHours(12)->toISOString(),
                'status' => 'picked_up_by_carrier',
                'description' => 'Colis récupéré par le transporteur (simulation)',
                'location' => 'Dépôt transporteur',
            ];
        }

        if (in_array($status, ['in_transit', 'delivered'])) {
            $events[] = [
                'date' => now()->subHours(6)->toISOString(),
                'status' => 'in_transit',
                'description' => 'Colis en transit (simulation)',
                'location' => 'En route vers destination',
            ];
        }

        if ($status === 'delivered') {
            $events[] = [
                'date' => now()->subHours(2)->toISOString(),
                'status' => 'delivered',
                'description' => 'Colis livré avec succès (simulation)',
                'location' => 'Adresse de livraison',
            ];
        }

        return $events;
    }

    /**
     * 🆕 MÉTHODES STATIQUES POUR FACILITER LES TESTS
     */
    public static function createForTesting(array $config = []): self
    {
        $defaultConfig = [
            'carrier_name' => 'Mock Test Carrier',
            'environment' => 'test',
            'simulate_delay' => false,
            'simulate_errors' => false,
            'simulate_connection_failure' => false,
        ];

        return new self(array_merge($defaultConfig, $config));
    }

    /**
     * Configuration pour simuler des erreurs (utile pour les tests)
     */
    public function enableErrorSimulation(bool $enable = true): self
    {
        $this->config['simulate_errors'] = $enable;
        return $this;
    }

    /**
     * Configuration pour simuler des délais (utile pour les tests)
     */
    public function enableDelaySimulation(bool $enable = true): self
    {
        $this->config['simulate_delay'] = $enable;
        return $this;
    }

    /**
     * Configuration pour simuler des échecs de connexion (utile pour les tests)
     */
    public function enableConnectionFailureSimulation(bool $enable = true): self
    {
        $this->config['simulate_connection_failure'] = $enable;
        return $this;
    }
}