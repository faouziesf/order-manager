<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'order_id',
        'pickup_id',
        'pos_barcode',
        'pos_reference',
        'order_number',
        'status',
        'carrier_slug',
        'carrier_last_status_update',
        'carrier_data',
        'weight',
        'value',
        'cod_amount',
        'nb_pieces',
        'pickup_date',
        'content_description',
        'sender_info',
        'recipient_info',
        'delivered_at',
        'delivery_notes',
    ];

    protected $casts = [
        'carrier_data' => 'array',
        'sender_info' => 'array',
        'recipient_info' => 'array',
        'weight' => 'decimal:2',
        'value' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'pickup_date' => 'date',
        'delivered_at' => 'datetime',
        'carrier_last_status_update' => 'datetime',
    ];

    // ========================================
    // CONSTANTES
    // ========================================
    
    const STATUS_CREATED = 'created';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PICKED_UP_BY_CARRIER = 'picked_up_by_carrier';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_IN_RETURN = 'in_return';
    const STATUS_ANOMALY = 'anomaly';

    // ========================================
    // RELATIONS
    // ========================================
    
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class);
    }

    // ========================================
    // ACCESSORS
    // ========================================
    
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_CREATED => 'Créé',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP_BY_CARRIER => 'Récupéré par transporteur',
            self::STATUS_IN_TRANSIT => 'En transit',
            self::STATUS_DELIVERED => 'Livré',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_IN_RETURN => 'En retour',
            self::STATUS_ANOMALY => 'Anomalie',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            self::STATUS_CREATED => 'badge-secondary',
            self::STATUS_VALIDATED => 'badge-primary',
            self::STATUS_PICKED_UP_BY_CARRIER => 'badge-info',
            self::STATUS_IN_TRANSIT => 'badge-warning',
            self::STATUS_DELIVERED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-dark',
            self::STATUS_IN_RETURN => 'badge-warning',
            self::STATUS_ANOMALY => 'badge-danger',
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    public function getTrackingUrlAttribute(): ?string
    {
        if (!$this->pos_barcode || !$this->carrier_slug) {
            return null;
        }

        // URLs de tracking par transporteur
        $trackingUrls = [
            'jax_delivery' => "https://tracking.jax-delivery.com/{$this->pos_barcode}",
            'fparcel' => "https://tracking.fparcel.com/{$this->pos_barcode}",
            'aramex' => "https://www.aramex.com/track/results?ShipmentNumber={$this->pos_barcode}",
            // Ajouter d'autres transporteurs ici
        ];

        return $trackingUrls[$this->carrier_slug] ?? null;
    }

    public function getCarrierDisplayNameAttribute(): string
    {
        if (!$this->carrier_slug) {
            return 'Transporteur non défini';
        }

        $carriers = config('carriers', []);
        return $carriers[$this->carrier_slug]['display_name'] ?? ucfirst($this->carrier_slug);
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->recipient_info['name'] ?? $this->order->customer_name ?? 'N/A';
    }

    public function getCustomerPhoneAttribute(): string
    {
        return $this->recipient_info['phone'] ?? $this->order->customer_phone ?? 'N/A';
    }

    public function getCustomerAddressAttribute(): string
    {
        return $this->recipient_info['address'] ?? $this->order->customer_address ?? 'N/A';
    }

    public function getDaysInTransitAttribute(): ?int
    {
        if ($this->status === self::STATUS_DELIVERED && $this->delivered_at) {
            return $this->created_at->diffInDays($this->delivered_at);
        }
        
        if (in_array($this->status, [self::STATUS_IN_TRANSIT, self::STATUS_PICKED_UP_BY_CARRIER])) {
            return $this->created_at->diffInDays(now());
        }
        
        return null;
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_VALIDATED,
            self::STATUS_PICKED_UP_BY_CARRIER,
            self::STATUS_IN_TRANSIT
        ]);
    }

    public function getIsCompletedAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_IN_RETURN
        ]);
    }

    // ========================================
    // MÉTHODES EXTENSIBLES
    // ========================================
    
    /**
     * Créer l'expédition avec le transporteur approprié
     */
    public function createWithCarrier(): bool
    {
        if (!$this->pickup || !$this->pickup->deliveryConfiguration) {
            throw new \Exception('Configuration de livraison manquante');
        }

        // S'assurer que carrier_slug est défini
        if (!$this->carrier_slug) {
            $this->carrier_slug = $this->pickup->carrier_slug;
        }

        // Utiliser le service factory pour obtenir le bon service
        $shippingService = app(\App\Services\Shipping\ShippingServiceFactory::class)
            ->make($this->carrier_slug, $this->pickup->deliveryConfiguration);

        try {
            // Récupérer l'adresse de pickup si elle existe (pour les transporteurs qui la supportent)
            $pickupAddress = null;
            if (method_exists($this->pickup, 'pickupAddress') && $this->pickup->pickupAddress) {
                $pickupAddress = $this->pickup->pickupAddress;
            }

            $result = $shippingService->createShipment($this->order, $pickupAddress);
            
            $this->update([
                'pos_barcode' => $result['tracking_number'] ?? $result['pos_barcode'] ?? $result['ean'],
                'pos_reference' => $result['reference'] ?? null,
                'status' => self::STATUS_VALIDATED,
                'carrier_slug' => $this->carrier_slug,
                'carrier_data' => $result,
            ]);

            // Mettre à jour la commande
            $this->order->markAsShipped(
                $this->pos_barcode,
                $this->carrier_slug,
                "Expédition créée via {$this->carrier_display_name}"
            );

            $this->logStatusChange(self::STATUS_CREATED, self::STATUS_VALIDATED);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Shipment creation failed', [
                'carrier' => $this->carrier_slug,
                'error' => $e->getMessage(),
                'shipment_id' => $this->id,
            ]);
            throw $e;
        }
    }

    /**
     * Suivre le statut avec le transporteur approprié
     */
    public function trackStatus(): void
    {
        if (!$this->pos_barcode || !$this->carrier_slug) {
            return;
        }

        if (!$this->pickup || !$this->pickup->deliveryConfiguration) {
            \Log::warning('Shipment tracking skipped: missing pickup or config', [
                'shipment_id' => $this->id,
                'pos_barcode' => $this->pos_barcode,
            ]);
            return;
        }

        // Utiliser le service factory pour obtenir le bon service
        $shippingService = app(\App\Services\Shipping\ShippingServiceFactory::class)
            ->make($this->carrier_slug, $this->pickup->deliveryConfiguration);

        try {
            $trackingData = $shippingService->trackShipment($this->pos_barcode);
            
            if ($trackingData && isset($trackingData['status'])) {
                $newStatus = $this->mapCarrierStatusToInternal($trackingData['status']);
                
                if ($newStatus !== $this->status) {
                    $oldStatus = $this->status;
                    $this->update([
                        'status' => $newStatus,
                        'carrier_last_status_update' => now(),
                    ]);
                    
                    $this->logStatusChange(
                        $oldStatus,
                        $newStatus,
                        $trackingData['status'],
                        $trackingData['status_label'] ?? null
                    );

                    // Mettre à jour la commande selon le nouveau statut
                    $this->updateOrderStatus($newStatus, $trackingData);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Shipment tracking failed', [
                'carrier' => $this->carrier_slug,
                'tracking_number' => $this->pos_barcode,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mapper le statut du transporteur vers le statut interne
     */
    private function mapCarrierStatusToInternal(string $carrierStatus): string
    {
        // Vérifier que carrier_slug existe
        if (!$this->carrier_slug) {
            \Log::warning('Cannot map carrier status: carrier_slug is null', [
                'shipment_id' => $this->id,
                'carrier_status' => $carrierStatus,
            ]);
            return $this->status;
        }

        // Récupérer le mapping depuis la config du transporteur
        $carriers = config('carriers', []);
        $mapping = $carriers[$this->carrier_slug]['status_mapping'] ?? [];

        $mappedStatus = $mapping[$carrierStatus] ?? null;
        
        if (!$mappedStatus) {
            \Log::info('Unknown carrier status, keeping current status', [
                'carrier' => $this->carrier_slug,
                'carrier_status' => $carrierStatus,
                'current_status' => $this->status,
            ]);
            return $this->status;
        }

        return $mappedStatus;
    }

    /**
     * Mettre à jour le statut de la commande
     */
    private function updateOrderStatus(string $shipmentStatus, array $trackingData): void
    {
        $statusMapping = [
            self::STATUS_PICKED_UP_BY_CARRIER => 'expédiée',
            self::STATUS_IN_TRANSIT => 'en_transit',
            self::STATUS_DELIVERED => 'livrée',
            self::STATUS_IN_RETURN => 'en_retour',
            self::STATUS_ANOMALY => 'anomalie_livraison',
        ];

        $orderStatus = $statusMapping[$shipmentStatus] ?? null;

        if ($orderStatus) {
            $this->order->updateDeliveryStatus(
                $orderStatus,
                $trackingData['status'] ?? null,
                $trackingData['status_label'] ?? null,
                "Mise à jour automatique du suivi {$this->carrier_display_name}",
                $this->pos_barcode
            );
        }
    }

    /**
     * Enregistrer un changement de statut dans l'historique
     */
    private function logStatusChange(
        ?string $oldStatus,
        string $newStatus,
        ?string $carrierCode = null,
        ?string $carrierLabel = null
    ): void {
        $actionMapping = [
            self::STATUS_VALIDATED => 'shipment_validated',
            self::STATUS_PICKED_UP_BY_CARRIER => 'picked_up_by_carrier',
            self::STATUS_IN_TRANSIT => 'in_transit',
            self::STATUS_DELIVERED => 'livraison',
            self::STATUS_IN_RETURN => 'in_return',
            self::STATUS_ANOMALY => 'delivery_anomaly',
        ];

        $action = $actionMapping[$newStatus] ?? 'tracking_updated';

        $this->order->recordHistory(
            $action,
            "Statut expédition mis à jour: {$this->status_label}",
            [
                'shipment_id' => $this->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'carrier' => $this->carrier_slug,
                'carrier_status_code' => $carrierCode,
                'carrier_status_label' => $carrierLabel,
            ],
            $oldStatus,
            $newStatus,
            $carrierCode,
            $carrierLabel,
            $this->pos_barcode,
            $this->carrier_slug
        );
    }

    public function cancel(string $reason = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'delivery_notes' => $reason,
        ]);

        $this->order->recordHistory(
            'changement_statut',
            $reason ?: 'Expédition annulée',
            [
                'shipment_id' => $this->id,
                'cancelled_reason' => $reason,
                'carrier' => $this->carrier_slug,
            ],
            $oldStatus,
            'annulée'
        );
    }

    public function markAsDelivered(string $notes = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
            'delivery_notes' => $notes,
        ]);

        $this->order->updateDeliveryStatus(
            'livrée',
            'delivered',
            'Colis livré',
            $notes ?: 'Livraison confirmée manuellement',
            $this->pos_barcode
        );
    }

    public function getTrackingHistory(): array
    {
        return $this->order->getDeliveryHistory()
            ->where('tracking_number', $this->pos_barcode)
            ->get()
            ->toArray();
    }

    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCarrier($query, string $carrier)
    {
        return $query->where('carrier_slug', $carrier);
    }

    public function scopeNeedsTracking($query)
    {
        return $query->whereIn('status', [
            self::STATUS_VALIDATED,
            self::STATUS_PICKED_UP_BY_CARRIER,
            self::STATUS_IN_TRANSIT,
        ])->whereNotNull('pos_barcode');
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeInReturn($query)
    {
        return $query->where('status', self::STATUS_IN_RETURN);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_VALIDATED,
            self::STATUS_PICKED_UP_BY_CARRIER,
            self::STATUS_IN_TRANSIT
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_IN_RETURN
        ]);
    }

    public function scopeWithTracking($query)
    {
        return $query->whereNotNull('pos_barcode');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================
    
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_CREATED => 'Créé',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP_BY_CARRIER => 'Récupéré par transporteur',
            self::STATUS_IN_TRANSIT => 'En transit',
            self::STATUS_DELIVERED => 'Livré',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_IN_RETURN => 'En retour',
            self::STATUS_ANOMALY => 'Anomalie',
        ];
    }

    public static function getActiveStatuses(): array
    {
        return [
            self::STATUS_VALIDATED,
            self::STATUS_PICKED_UP_BY_CARRIER,
            self::STATUS_IN_TRANSIT,
        ];
    }

    public static function getCompletedStatuses(): array
    {
        return [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_IN_RETURN,
        ];
    }
}