<?php

namespace App\Services\Shipping;

use App\Models\DeliveryConfiguration;
use App\Services\Shipping\Fparcel\FparcelService;
use InvalidArgumentException;
use Illuminate\Support\Facades\Cache;

class ShippingServiceFactory
{
    /**
     * Services d'expédition disponibles
     */
    private array $availableCarriers = [
        'fparcel' => FparcelService::class,
    ];

    /**
     * Créer une instance de service d'expédition
     */
    public function make(string $carrier, DeliveryConfiguration $config): ShippingServiceInterface
    {
        $carrier = strtolower($carrier);
        
        if (!isset($this->availableCarriers[$carrier])) {
            throw new InvalidArgumentException("Transporteur non supporté: {$carrier}");
        }

        $serviceClass = $this->availableCarriers[$carrier];
        
        return new $serviceClass($config);
    }

    /**
     * Obtenir la liste des transporteurs supportés
     */
    public function getSupportedCarriers(): array
    {
        return [
            'fparcel' => [
                'name' => 'Fparcel',
                'display_name' => 'Fparcel Tunisia',
                'supports_pickup_address' => true,
                'supports_tracking' => true,
                'supports_mass_labels' => true,
                'supports_drop_points' => true,
                'environments' => ['test', 'prod'],
                'required_fields' => ['username', 'password'],
                'optional_fields' => ['environment'],
                'features' => [
                    'cod' => true, // Cash on delivery
                    'insurance' => false,
                    'signature_required' => false,
                    'scheduling' => true,
                ],
                'api_endpoints' => [
                    'test' => 'https://test-api.fparcel.com',
                    'prod' => 'https://api.fparcel.com',
                ],
            ],
        ];
    }

    /**
     * Vérifier si un transporteur est supporté
     */
    public function isSupported(string $carrier): bool
    {
        return array_key_exists(strtolower($carrier), $this->availableCarriers);
    }

    /**
     * Obtenir les informations d'un transporteur
     */
    public function getCarrierInfo(string $carrier): ?array
    {
        $carriers = $this->getSupportedCarriers();
        return $carriers[strtolower($carrier)] ?? null;
    }

    /**
     * Obtenir tous les services actifs pour un admin
     */
    public function getActiveServicesForAdmin(int $adminId): array
    {
        $configs = DeliveryConfiguration::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get();

        $services = [];
        foreach ($configs as $config) {
            try {
                $services[] = [
                    'config' => $config,
                    'service' => $this->make($config->carrier_slug, $config),
                    'carrier_info' => $this->getCarrierInfo($config->carrier_slug),
                ];
            } catch (\Exception $e) {
                \Log::warning("Impossible de créer le service pour {$config->carrier_slug}: " . $e->getMessage());
            }
        }

        return $services;
    }

    /**
     * Tester la connectivité d'un transporteur
     */
    public function testCarrierConnection(string $carrier, array $credentials): array
    {
        if (!$this->isSupported($carrier)) {
            return [
                'success' => false,
                'message' => 'Transporteur non supporté',
            ];
        }

        try {
            // Créer une configuration temporaire pour le test
            $tempConfig = new DeliveryConfiguration([
                'carrier_slug' => $carrier,
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'environment' => $credentials['environment'] ?? 'test',
            ]);

            $service = $this->make($carrier, $tempConfig);
            $tokenData = $service->getToken();

            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'token_obtained' => !empty($tokenData['token']),
                    'carrier_info' => $service->getCarrierInfo(),
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtenir les statistiques des transporteurs
     */
    public function getCarrierStats(int $adminId): array
    {
        return Cache::remember("carrier_stats_{$adminId}", 300, function () use ($adminId) {
            $stats = [];
            
            $configs = DeliveryConfiguration::where('admin_id', $adminId)->get();
            
            foreach ($configs as $config) {
                $carrierStats = [
                    'config_id' => $config->id,
                    'carrier' => $config->carrier_slug,
                    'integration_name' => $config->integration_name,
                    'is_active' => $config->is_active,
                    'has_valid_token' => $config->hasValidToken(),
                    'pickups_count' => $config->pickups()->count(),
                    'recent_pickups' => $config->pickups()
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count(),
                    'shipments_count' => 0,
                    'delivery_rate' => 0,
                ];

                // Calculer les statistiques d'expédition
                $shipments = \App\Models\Shipment::whereHas('pickup', function ($query) use ($config) {
                    $query->where('delivery_configuration_id', $config->id);
                });

                $carrierStats['shipments_count'] = $shipments->count();
                
                $totalShipments = $shipments->count();
                $deliveredShipments = $shipments->where('status', 'delivered')->count();
                
                if ($totalShipments > 0) {
                    $carrierStats['delivery_rate'] = round(($deliveredShipments / $totalShipments) * 100, 2);
                }

                $stats[] = $carrierStats;
            }

            return $stats;
        });
    }
}