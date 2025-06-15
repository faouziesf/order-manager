<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Pickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'carrier_slug',
        'delivery_configuration_id',
        'pickup_address_id',
        'status',
        'pickup_date',
        'validated_at',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'validated_at' => 'datetime',
    ];

    // Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_PROBLEM = 'problem';

    // Relations
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function deliveryConfiguration(): BelongsTo
    {
        return $this->belongsTo(DeliveryConfiguration::class);
    }

    public function pickupAddress(): BelongsTo
    {
        return $this->belongsTo(PickupAddress::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    // Accessors
    public function getShipmentCountAttribute(): int
    {
        return $this->shipments()->count();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_PROBLEM => 'Problème',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_VALIDATED => 'badge-primary',
            self::STATUS_PICKED_UP => 'badge-success',
            self::STATUS_PROBLEM => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    // Methods
    public function validate(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        try {
            \DB::transaction(function() {
                // Créer les expéditions avec le transporteur via l'API
                foreach ($this->shipments as $shipment) {
                    $shipment->createWithCarrier();
                }

                $this->update([
                    'status' => self::STATUS_VALIDATED,
                    'validated_at' => now(),
                ]);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Pickup validation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function checkForProblems(): void
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            return;
        }

        // Si validé depuis plus de 24h et qu'il y a des expéditions non récupérées
        if ($this->validated_at && $this->validated_at->diffInHours(now()) > 24) {
            $notPickedUp = $this->shipments()
                ->where('status', '!=', Shipment::STATUS_PICKED_UP_BY_CARRIER)
                ->exists();

            if ($notPickedUp) {
                $this->update(['status' => self::STATUS_PROBLEM]);
            }
        }
    }

    public function updateStatus(): void
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            return;
        }

        $totalShipments = $this->shipments()->count();
        $pickedUpShipments = $this->shipments()
            ->where('status', Shipment::STATUS_PICKED_UP_BY_CARRIER)
            ->count();

        if ($totalShipments > 0 && $pickedUpShipments === $totalShipments) {
            $this->update(['status' => self::STATUS_PICKED_UP]);
        }
    }

    public function canBeValidated(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->shipments()->count() > 0;
    }

    public function canGenerateLabels(): bool
    {
        return $this->status === self::STATUS_VALIDATED && 
               $this->shipments()->whereNotNull('pos_barcode')->count() > 0;
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCarrier($query, string $carrier)
    {
        return $query->where('carrier_slug', $carrier);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeNeedsStatusCheck($query)
    {
        return $query->where('status', self::STATUS_VALIDATED)
            ->where('validated_at', '<', now()->subHours(1));
    }
}