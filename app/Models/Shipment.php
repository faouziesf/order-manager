<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    // Constants
    const STATUS_CREATED = 'created';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PICKED_UP_BY_CARRIER = 'picked_up_by_carrier';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_IN_RETURN = 'in_return';
    const STATUS_ANOMALY = 'anomaly';

    // Relations
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

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ShipmentStatusHistory::class);
    }

    // Accessors
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

        // URL de tracking pour Fparcel (exemple)
        return "https://tracking.fparcel.com/{$this->pos_barcode}";
    }

    // Methods
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

                    // Mettre à jour la commande si livré
                    if ($newStatus === self::STATUS_DELIVERED && $this->order) {
                        $this->order->update([
                            'status' => 'livrée',
                            'delivered_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Shipment tracking failed: ' . $e->getMessage());
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
        $this->statusHistory()->create([
            'carrier_status_code' => $carrierCode,
            'carrier_status_label' => $carrierLabel,
            'internal_status' => $newStatus,
        ]);
    }

    private function generateReturnBarcode(string $posBarcode): string
    {
        return 'RET_' . $posBarcode . '_' . Str::random(6);
    }

    // Scopes
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
}