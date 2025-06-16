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
        'return_barcode',
        'pos_reference',
        'order_number',
        'status',
        'carrier_last_status_update',
        'fparcel_data',
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
        'fparcel_data' => 'array',
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
        return match($this->status) {
            self::STATUS_CREATED => 'Créé',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP_BY_CARRIER => 'Récupéré par transporteur',
            self::STATUS_IN_TRANSIT => 'En transit',
            self::STATUS_DELIVERED => 'Livré',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_IN_RETURN => 'En retour',
            self::STATUS_ANOMALY => 'Anomalie',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_CREATED => 'badge-secondary',
            self::STATUS_VALIDATED => 'badge-primary',
            self::STATUS_PICKED_UP_BY_CARRIER => 'badge-info',
            self::STATUS_IN_TRANSIT => 'badge-warning',
            self::STATUS_DELIVERED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-dark',
            self::STATUS_IN_RETURN => 'badge-warning',
            self::STATUS_ANOMALY => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getTrackingUrlAttribute(): ?string
    {
        if (!$this->pos_barcode) {
            return null;
        }

        // URL de tracking pour Fparcel
        return "https://tracking.fparcel.com/{$this->pos_barcode}";
    }

    public function getCarrierNameAttribute(): string
    {
        return $this->pickup->carrier_slug ?? 'N/A';
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
    // MÉTHODES
    // ========================================
    
    public function createWithCarrier(): bool
    {
        if (!$this->pickup || !$this->pickup->deliveryConfiguration) {
            throw new \Exception('Configuration de livraison manquante');
        }

        $shippingService = app(\App\Services\Shipping\ShippingServiceFactory::class)
            ->make($this->pickup->carrier_slug, $this->pickup->deliveryConfiguration);

        try {
            $result = $shippingService->createShipment($this->order, $this->pickup->pickupAddress);
            
            $this->update([
                'pos_barcode' => $result['pos_barcode'],
                'return_barcode' => $this->generateReturnBarcode($result['pos_barcode']),
                'pos_reference' => $result['pos_reference'] ?? null,
                'status' => self::STATUS_VALIDATED,
                'fparcel_data' => $result,
            ]);

            // Mettre à jour la commande
            $this->order->markAsShipped(
                $this->pos_barcode,
                $this->pickup->carrier_slug,
                "Expédition créée via {$this->pickup->carrier_slug}"
            );

            $this->logStatusChange(self::STATUS_CREATED, self::STATUS_VALIDATED);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Shipment creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function trackStatus(): void
    {
        if (!$this->pos_barcode || !$this->pickup) {
            return;
        }

        $shippingService = app(\App\Services\Shipping\ShippingServiceFactory::class)
            ->make($this->pickup->carrier_slug, $this->pickup->deliveryConfiguration);

        try {
            $trackingData = $shippingService->trackShipment($this->pos_barcode);
            
            if ($trackingData && isset($trackingData['status'])) {
                $newStatus = $this->mapCarrierStatus($trackingData['status']);
                
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
            \Log::error('Shipment tracking failed: ' . $e->getMessage());
        }
    }

    private function updateOrderStatus(string $shipmentStatus, array $trackingData): void
    {
        $orderStatus = match($shipmentStatus) {
            self::STATUS_PICKED_UP_BY_CARRIER => 'expédiée',
            self::STATUS_IN_TRANSIT => 'en_transit',
            self::STATUS_DELIVERED => 'livrée',
            self::STATUS_IN_RETURN => 'en_retour',
            self::STATUS_ANOMALY => 'anomalie_livraison',
            default => null,
        };

        if ($orderStatus) {
            $this->order->updateDeliveryStatus(
                $orderStatus,
                $trackingData['status'] ?? null,
                $trackingData['status_label'] ?? null,
                "Mise à jour automatique du suivi",
                $this->pos_barcode
            );
        }
    }

    private function mapCarrierStatus(string $carrierStatus): string
    {
        // Mapping Fparcel EVENT_ID vers statuts internes
        $mapping = [
            '1' => self::STATUS_CREATED,
            '3' => self::STATUS_PICKED_UP_BY_CARRIER,
            '6' => self::STATUS_IN_TRANSIT,
            '7' => self::STATUS_DELIVERED,
            '9' => self::STATUS_IN_RETURN,
            '11' => self::STATUS_ANOMALY,
        ];

        return $mapping[$carrierStatus] ?? $this->status;
    }

    private function logStatusChange(
        ?string $oldStatus,
        string $newStatus,
        ?string $carrierCode = null,
        ?string $carrierLabel = null
    ): void {
        // Utiliser OrderHistory comme demandé
        $action = match($newStatus) {
            self::STATUS_VALIDATED => 'shipment_validated',
            self::STATUS_PICKED_UP_BY_CARRIER => 'picked_up_by_carrier',
            self::STATUS_IN_TRANSIT => 'in_transit',
            self::STATUS_DELIVERED => 'livraison',
            self::STATUS_IN_RETURN => 'in_return',
            self::STATUS_ANOMALY => 'delivery_anomaly',
            default => 'tracking_updated',
        };

        $this->order->recordHistory(
            $action,
            "Statut expédition mis à jour: {$this->status_label}",
            [
                'shipment_id' => $this->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'carrier_status_code' => $carrierCode,
                'carrier_status_label' => $carrierLabel,
            ],
            $oldStatus,
            $newStatus,
            $carrierCode,
            $carrierLabel,
            $this->pos_barcode,
            $this->carrier_name
        );
    }

    private function generateReturnBarcode(string $posBarcode): string
    {
        return 'RET_' . $posBarcode . '_' . Str::random(6);
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
            '7',
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

    public function scopeByCarrier($query, string $carrier)
    {
        return $query->whereHas('pickup', function($q) use ($carrier) {
            $q->where('carrier_slug', $carrier);
        });
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