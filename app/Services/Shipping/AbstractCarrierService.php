<?php

namespace App\Services\Shipping;

use App\Models\DeliveryConfiguration;
use App\Models\Order;
use App\Models\PickupAddress;

abstract class AbstractCarrierService implements CarrierServiceInterface
{
    protected DeliveryConfiguration $config;
    protected string $baseUrl;
    protected string $carrierSlug;

    public function __construct(DeliveryConfiguration $config)
    {
        $this->config = $config;
        $this->carrierSlug = $config->carrier_slug;
        $this->baseUrl = $this->getApiEndpoint();
    }

    // ========================================
    // MÉTHODES ABSTRAITES À IMPLÉMENTER
    // ========================================

    abstract public function createShipment(Order $order, ?PickupAddress $pickupAddress = null): array;
    abstract public function trackShipment(string $trackingNumber): ?array;
    abstract public function testConnection(): array;

    // ========================================
    // MÉTHODES AVEC IMPLÉMENTATION PAR DÉFAUT
    // ========================================

    public function getToken(): array
    {
        return [
            'token' => $this->config->token,
            'expires_at' => $this->config->expires_at ?? now()->addDays(30),
        ];
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        // Implémentation par défaut : non supporté
        return false;
    }

    public function getCarrierInfo(): array
    {
        return config("carriers.{$this->carrierSlug}", []);
    }

    // ========================================
    // CAPACITÉS BASÉES SUR LA CONFIG
    // ========================================

    public function supportsPickupAddressSelection(): bool
    {
        return $this->getCarrierConfig('supports_pickup_address', false);
    }

    public function supportsBLGeneration(): bool
    {
        return $this->getCarrierConfig('supports_bl_templates', false);
    }

    public function supportsLabelGeneration(): bool
    {
        return $this->getCarrierConfig('supports_mass_labels', false);
    }

    public function supportsDropPoints(): bool
    {
        return $this->getCarrierConfig('supports_drop_points', false);
    }

    public function supportsPickupScheduling(): bool
    {
        return $this->getCarrierConfig('supports_pickup_scheduling', false);
    }

    // ========================================
    // MÉTHODES CONDITIONNELLES AVEC EXCEPTIONS PAR DÉFAUT
    // ========================================

    public function getMassLabels(array $trackingCodes): array
    {
        if (!$this->supportsLabelGeneration()) {
            throw new \Exception("{$this->carrierSlug} ne supporte pas la génération d'étiquettes en masse");
        }
        
        return [];
    }

    public function getPaymentMethods(): array
    {
        if (!$this->getCarrierConfig('supports_payment_methods', false)) {
            return [];
        }
        
        return [];
    }

    public function getDropPoints(): array
    {
        if (!$this->supportsDropPoints()) {
            return [];
        }
        
        return [];
    }

    public function getAnomalyReasons(): array
    {
        return [];
    }

    public function schedulePickup(array $shipments, \DateTime $pickupDate): array
    {
        if (!$this->supportsPickupScheduling()) {
            throw new \Exception("{$this->carrierSlug} ne supporte pas la programmation d'enlèvement");
        }
        
        throw new \Exception("Programmation d'enlèvement non implémentée pour {$this->carrierSlug}");
    }

    // ========================================
    // MÉTHODES UTILITAIRES PROTÉGÉES
    // ========================================

    protected function getApiEndpoint(): string
    {
        $endpoints = config("carriers.{$this->carrierSlug}.api_endpoints", []);
        return $endpoints[$this->config->environment] ?? $endpoints['prod'] ?? '';
    }

    protected function getCarrierConfig(string $key, $default = null)
    {
        return config("carriers.{$this->carrierSlug}.{$key}", $default);
    }

    protected function getStatusMapping(): array
    {
        return config("carriers.{$this->carrierSlug}.status_mapping", []);
    }

    protected function mapStatusToInternal(string $carrierStatus): string
    {
        $mapping = $this->getStatusMapping();
        return $mapping[$carrierStatus] ?? 'unknown';
    }

    protected function validateConfig(): void
    {
        $requiredFields = $this->getCarrierConfig('required_fields', []);
        
        foreach ($requiredFields as $field) {
            if (empty($this->config->$field)) {
                throw new \Exception("Champ requis manquant: {$field}");
            }
        }
    }

    protected function logError(string $operation, array $context = []): void
    {
        \Log::error("{$this->carrierSlug} {$operation} failed", array_merge([
            'carrier' => $this->carrierSlug,
            'config_id' => $this->config->id,
        ], $context));
    }

    protected function logInfo(string $operation, array $context = []): void
    {
        \Log::info("{$this->carrierSlug} {$operation}", array_merge([
            'carrier' => $this->carrierSlug,
            'config_id' => $this->config->id,
        ], $context));
    }
}