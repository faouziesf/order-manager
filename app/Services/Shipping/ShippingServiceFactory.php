<?php

namespace App\Services\Shipping;

use App\Models\DeliveryConfiguration;
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
                    'return_labels' => true,
                    'mass_labels' => true,
                    'pickup_address_selection' => true,
                ],
                'api_endpoints' => [
                    'test' => 'http://fparcel.net:59/WebServiceExterne',
                    'prod' => 'https://admin.fparcel.net/WebServiceExterne',
                ],
                'supported_countries' => ['TN'], // Tunisie
                'max_weight' => 30, // kg
                'max_dimensions' => [
                    'length' => 100, // cm
                    'width' => 100,
                    'height' => 100,
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
                    'token_expires_at' => $tokenData['expires_at'] ?? null,
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
                    'display_name' => $config->display_name,
                    'is_active' => $config->is_active,
                    'has_valid_token' => $config->hasValidToken(),
                    'token_expires_at' => $config->expires_at,
                    'environment' => $config->environment,
                    'pickups_count' => $config->pickups()->count(),
                    'recent_pickups' => $config->pickups()
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count(),
                    'shipments_count' => 0,
                    'delivery_rate' => 0,
                    'last_used_at' => null,
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

                // Dernière utilisation
                $lastPickup = $config->pickups()->latest()->first();
                $carrierStats['last_used_at'] = $lastPickup?->created_at;

                $stats[] = $carrierStats;
            }

            return $stats;
        });
    }

    /**
     * Valider les données d'un transporteur
     */
    public function validateCarrierData(string $carrier, array $data): array
    {
        if (!$this->isSupported($carrier)) {
            return [
                'valid' => false,
                'errors' => ['Transporteur non supporté']
            ];
        }

        $carrierInfo = $this->getCarrierInfo($carrier);
        $errors = [];

        // Vérifier les champs obligatoires
        foreach ($carrierInfo['required_fields'] as $field) {
            if (empty($data[$field])) {
                $errors[] = "Le champ '{$field}' est obligatoire";
            }
        }

        // Validation spécifique selon le transporteur
        if ($carrier === 'fparcel') {
            if (!empty($data['username']) && strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
            }
            
            if (!empty($data['password']) && strlen($data['password']) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
            }
            
            if (!empty($data['environment']) && !in_array($data['environment'], ['test', 'prod'])) {
                $errors[] = 'L\'environnement doit être "test" ou "prod"';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtenir les capacités d'un transporteur
     */
    public function getCarrierCapabilities(string $carrier): array
    {
        $info = $this->getCarrierInfo($carrier);
        
        if (!$info) {
            return [];
        }

        return [
            'features' => $info['features'] ?? [],
            'max_weight' => $info['max_weight'] ?? null,
            'max_dimensions' => $info['max_dimensions'] ?? null,
            'supported_countries' => $info['supported_countries'] ?? [],
            'environments' => $info['environments'] ?? [],
        ];
    }

    /**
     * Enregistrer un nouveau transporteur
     */
    public function registerCarrier(string $slug, string $serviceClass): void
    {
        if (!class_exists($serviceClass)) {
            throw new InvalidArgumentException("La classe de service {$serviceClass} n'existe pas");
        }

        if (!in_array(ShippingServiceInterface::class, class_implements($serviceClass))) {
            throw new InvalidArgumentException("La classe {$serviceClass} doit implémenter ShippingServiceInterface");
        }

        $this->availableCarriers[strtolower($slug)] = $serviceClass;
    }

    /**
     * Obtenir les transporteurs recommandés pour une région
     */
    public function getRecommendedCarriersForRegion(string $country, ?string $region = null): array
    {
        $recommended = [];
        
        foreach ($this->getSupportedCarriers() as $slug => $info) {
            $supportedCountries = $info['supported_countries'] ?? [];
            
            if (in_array(strtoupper($country), $supportedCountries)) {
                $recommended[] = [
                    'slug' => $slug,
                    'name' => $info['display_name'],
                    'features' => $info['features'],
                    'recommendation_score' => $this->calculateRecommendationScore($info, $country, $region)
                ];
            }
        }

        // Trier par score de recommandation
        usort($recommended, function($a, $b) {
            return $b['recommendation_score'] <=> $a['recommendation_score'];
        });

        return $recommended;
    }

    /**
     * Calculer le score de recommandation
     */
    private function calculateRecommendationScore(array $carrierInfo, string $country, ?string $region): int
    {
        $score = 0;
        
        // Points pour les fonctionnalités
        $features = $carrierInfo['features'] ?? [];
        $score += count(array_filter($features)) * 10;
        
        // Points pour le support COD (important en Tunisie)
        if ($country === 'TN' && ($features['cod'] ?? false)) {
            $score += 50;
        }
        
        // Points pour le suivi
        if ($features['tracking'] ?? false) {
            $score += 30;
        }
        
        // Points pour les étiquettes en masse
        if ($features['mass_labels'] ?? false) {
            $score += 20;
        }
        
        return $score;
    }
}