<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Contracts\CarrierServiceInterface;
use App\Models\DeliveryConfiguration;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Service de simulation pour tests et développement
 * 
 * Permet de tester l'interface de livraison sans vraies APIs
 * Simule les réponses des transporteurs
 */
class MockCarrierService implements CarrierServiceInterface
{
    protected string $carrierSlug;
    
    public function __construct(string $carrierSlug = 'mock_carrier')
    {
        $this->carrierSlug = $carrierSlug;
    }

    public function testConnection(DeliveryConfiguration $config): array
    {
        // Simuler différents cas selon la configuration
        if (empty($config->username)) {
            return [
                'success' => false,
                'error' => 'Nom d\'utilisateur manquant',
                'details' => [
                    'test_time' => now()->toISOString(),
                    'simulated' => true,
                ]
            ];
        }

        if ($config->username === 'test_error') {
            return [
                'success' => false,
                'error' => 'Erreur de connexion simulée',
                'details' => [
                    'test_time' => now()->toISOString(),
                    'simulated' => true,
                ]
            ];
        }

        // Simuler une connexion réussie
        return [
            'success' => true,
            'message' => 'Connexion réussie (simulée)',
            'details' => [
                'carrier' => $this->getCarrierInfo()['name'],
                'api_url' => 'https://api.mock-carrier.com',
                'test_time' => now()->toISOString(),
                'simulated' => true,
                'account_info' => [
                    'account_id' => $config->username,
                    'account_status' => 'active',
                    'balance' => 1500.50,
                ]
            ]
        ];
    }

    public function createShipment(Order $order, DeliveryConfiguration $config, array $additionalData = []): array
    {
        // Simuler la validation
        $validationErrors = $this->validateOrderData($order, $config);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'error' => 'Données invalides: ' . implode(', ', $validationErrors),
                'validation_errors' => $validationErrors,
            ];
        }

        // Générer un numéro de suivi factice
        $trackingNumber = $this->generateMockTrackingNumber($config->carrier_slug);
        
        // Simuler une réponse d'API
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'carrier_reference' => 'REF-' . strtoupper(Str::random(8)),
            'estimated_delivery' => now()->addDays(2)->format('Y-m-d'),
            'cost' => [
                'amount' => 8.5,
                'currency' => 'TND',
            ],
            'details' => [
                'carrier' => $this->getCarrierInfo()['name'],
                'service_type' => 'standard',
                'weight' => $additionalData['weight'] ?? 1.0,
                'dimensions' => $additionalData['dimensions'] ?? '20x15x10',
                'created_at' => now()->toISOString(),
                'simulated' => true,
            ]
        ];
    }

    public function trackShipment(string $trackingNumber, DeliveryConfiguration $config): array
    {
        // Simuler différents statuts selon le numéro
        $status = $this->generateMockStatus($trackingNumber);
        
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'carrier_status' => $status['carrier_status'],
            'carrier_status_label' => $status['label'],
            'internal_status' => $status['internal_status'],
            'location' => 'Centre de tri Tunis',
            'last_update' => now()->subHours(rand(1, 24))->toISOString(),
            'estimated_delivery' => now()->addDays(rand(1, 3))->format('Y-m-d'),
            'history' => $this->generateMockHistory($trackingNumber),
            'details' => [
                'carrier' => $this->getCarrierInfo()['name'],
                'simulated' => true,
            ]
        ];
    }

    public function trackMultipleShipments(array $trackingNumbers, DeliveryConfiguration $config): array
    {
        $results = [];
        
        foreach ($trackingNumbers as $trackingNumber) {
            $results[$trackingNumber] = $this->trackShipment($trackingNumber, $config);
        }
        
        return $results;
    }

    public function validateOrderData(Order $order, DeliveryConfiguration $config): array
    {
        $errors = [];
        
        if (empty($order->customer_name)) {
            $errors[] = 'Nom du client manquant';
        }
        
        if (empty($order->customer_phone)) {
            $errors[] = 'Téléphone du client manquant';
        }
        
        if (empty($order->customer_address)) {
            $errors[] = 'Adresse du client manquante';
        }
        
        if (empty($order->customer_governorate)) {
            $errors[] = 'Gouvernorat manquant';
        }
        
        if ($order->total_price <= 0) {
            $errors[] = 'Montant COD invalide';
        }
        
        // Simuler des erreurs spécifiques pour les tests
        if ($order->customer_name === 'TEST_ERROR') {
            $errors[] = 'Nom de test pour simulation d\'erreur';
        }
        
        if ($order->total_price > 5000) {
            $errors[] = 'Montant COD dépassé (max: 5000 TND)';
        }
        
        return $errors;
    }

    public function mapCarrierStatusToInternal(string $carrierStatus, DeliveryConfiguration $config): string
    {
        return match($carrierStatus) {
            'CREATED' => 'created',
            'VALIDATED' => 'validated',
            'PICKED_UP' => 'picked_up_by_carrier',
            'IN_TRANSIT' => 'in_transit',
            'OUT_FOR_DELIVERY' => 'in_transit',
            'DELIVERED' => 'delivered',
            'FAILED' => 'delivery_failed',
            'RETURNED' => 'in_return',
            'CANCELLED' => 'cancelled',
            default => 'unknown',
        };
    }

    public function getCarrierInfo(): array
    {
        return [
            'slug' => $this->carrierSlug,
            'name' => match($this->carrierSlug) {
                'jax_delivery' => 'JAX Delivery (Simulé)',
                'mes_colis' => 'Mes Colis Express (Simulé)',
                default => 'Transporteur Simulé',
            },
            'description' => 'Service de test pour développement',
            'website' => 'https://mock-carrier.test',
            'support_phone' => '+216 12 345 678',
            'support_email' => 'support@mock-carrier.test',
        ];
    }

    public function getCarrierLimits(): array
    {
        return [
            'max_weight' => 30.0,
            'max_cod_amount' => 5000.0,
            'max_content_length' => 255,
            'max_address_length' => 500,
        ];
    }

    public function supportsFeature(string $feature): bool
    {
        $supportedFeatures = [
            'create_shipment' => true,
            'track_shipment' => true,
            'test_connection' => true,
            'cancel_shipment' => true,
            'multiple_tracking' => true,
            'pickup_support' => false,
            'webhook_support' => false,
            'label_generation' => false,
            'cost_calculation' => true,
        ];
        
        return $supportedFeatures[$feature] ?? false;
    }

    public function getSupportedGovernorates(): array
    {
        // Simuler le mapping pour tous les gouvernorats tunisiens
        $governorates = [];
        for ($i = 1; $i <= 24; $i++) {
            $governorates[$i] = (string)$i;
        }
        return $governorates;
    }

    public function cancelShipment(string $trackingNumber, DeliveryConfiguration $config, string $reason = ''): array
    {
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'cancelled_at' => now()->toISOString(),
            'reason' => $reason ?: 'Annulation demandée',
            'details' => [
                'simulated' => true,
            ]
        ];
    }

    public function getShipmentDetails(string $trackingNumber, DeliveryConfiguration $config): array
    {
        return $this->trackShipment($trackingNumber, $config);
    }

    public function generateShipmentLabel(string $trackingNumber, DeliveryConfiguration $config): array
    {
        return [
            'success' => true,
            'label_url' => "https://mock-carrier.test/labels/{$trackingNumber}.pdf",
            'label_format' => 'PDF',
            'details' => [
                'tracking_number' => $trackingNumber,
                'generated_at' => now()->toISOString(),
                'simulated' => true,
            ]
        ];
    }

    public function calculateShippingCost(Order $order, DeliveryConfiguration $config): array
    {
        // Calculer un coût simulé basé sur le poids et la région
        $baseCost = 7.0;
        $weightCost = ($order->items->sum('quantity') * 0.5) * 1.5; // 1.5 TND par kg
        $totalCost = $baseCost + $weightCost;
        
        return [
            'success' => true,
            'cost' => round($totalCost, 2),
            'currency' => 'TND',
            'breakdown' => [
                'base_cost' => $baseCost,
                'weight_cost' => $weightCost,
                'total' => $totalCost,
            ],
            'details' => [
                'estimated_weight' => $order->items->sum('quantity') * 0.5,
                'governorate' => $order->customer_governorate,
                'simulated' => true,
            ]
        ];
    }

    // ========================================
    // MÉTHODES PRIVÉES POUR LA SIMULATION
    // ========================================

    private function generateMockTrackingNumber(string $carrierSlug): string
    {
        $prefix = match($carrierSlug) {
            'jax_delivery' => 'JAX',
            'mes_colis' => 'MC',
            default => 'MOCK',
        };
        
        return $prefix . date('Ymd') . rand(100000, 999999);
    }

    private function generateMockStatus(string $trackingNumber): array
    {
        // Générer un statut basé sur le hash du numéro de suivi
        $hash = crc32($trackingNumber) % 8;
        
        return match($hash) {
            0 => [
                'carrier_status' => 'CREATED',
                'label' => 'Colis créé',
                'internal_status' => 'created',
            ],
            1 => [
                'carrier_status' => 'VALIDATED',
                'label' => 'Colis validé',
                'internal_status' => 'validated',
            ],
            2 => [
                'carrier_status' => 'PICKED_UP',
                'label' => 'Colis récupéré',
                'internal_status' => 'picked_up_by_carrier',
            ],
            3, 4 => [
                'carrier_status' => 'IN_TRANSIT',
                'label' => 'En transit',
                'internal_status' => 'in_transit',
            ],
            5, 6 => [
                'carrier_status' => 'DELIVERED',
                'label' => 'Livré',
                'internal_status' => 'delivered',
            ],
            7 => [
                'carrier_status' => 'FAILED',
                'label' => 'Échec de livraison',
                'internal_status' => 'delivery_failed',
            ],
            default => [
                'carrier_status' => 'IN_TRANSIT',
                'label' => 'En transit',
                'internal_status' => 'in_transit',
            ],
        };
    }

    private function generateMockHistory(string $trackingNumber): array
    {
        $history = [];
        $statuses = [
            ['status' => 'CREATED', 'label' => 'Colis créé', 'location' => 'Entrepôt'],
            ['status' => 'VALIDATED', 'label' => 'Colis validé', 'location' => 'Entrepôt'],
            ['status' => 'PICKED_UP', 'label' => 'Récupéré par transporteur', 'location' => 'Centre de tri'],
            ['status' => 'IN_TRANSIT', 'label' => 'En transit', 'location' => 'Centre de tri Tunis'],
        ];
        
        $currentTime = now()->subDays(2);
        
        foreach ($statuses as $index => $statusInfo) {
            $history[] = [
                'status' => $statusInfo['status'],
                'label' => $statusInfo['label'],
                'location' => $statusInfo['location'],
                'timestamp' => $currentTime->addHours(rand(6, 12))->toISOString(),
                'notes' => 'Mise à jour automatique',
            ];
        }
        
        return $history;
    }
}