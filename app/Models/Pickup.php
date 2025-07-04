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
        'status',
        'pickup_date',
        'validated_at',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'validated_at' => 'datetime',
    ];

    // ========================================
    // CONSTANTES
    // ========================================
    
    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_PROBLEM = 'problem';

    // ========================================
    // RELATIONS
    // ========================================
    
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function deliveryConfiguration(): BelongsTo
    {
        return $this->belongsTo(DeliveryConfiguration::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    // ========================================
    // ACCESSORS
    // ========================================
    
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

    public function getCarrierDisplayNameAttribute(): string
    {
        return 'Jax Delivery Services';
    }

    public function getTotalValueAttribute(): float
    {
        return $this->shipments()->sum('value') ?? 0;
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->shipments()->sum('weight') ?? 0;
    }

    public function getDeliveredShipmentsCountAttribute(): int
    {
        return $this->shipments()->where('status', 'delivered')->count();
    }

    public function getValidatedShipmentsCountAttribute(): int
    {
        return $this->shipments()->whereNotNull('pos_barcode')->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->shipment_count === 0) {
            return 0;
        }
        
        return round(($this->delivered_shipments_count / $this->shipment_count) * 100, 1);
    }

    public function getDaysInCurrentStatusAttribute(): int
    {
        $referenceDate = match($this->status) {
            self::STATUS_VALIDATED => $this->validated_at,
            default => $this->updated_at,
        };
        
        return $referenceDate ? $referenceDate->diffInDays(now()) : 0;
    }

    // ========================================
    // MÉTHODES
    // ========================================
    
    public function validate(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être validés.');
        }

        if ($this->shipments()->count() === 0) {
            throw new \Exception('Aucune expédition trouvée pour cet enlèvement.');
        }

        return \DB::transaction(function () {
            $errors = [];
            $successCount = 0;

            foreach ($this->shipments as $shipment) {
                try {
                    // TODO: Intégrer avec JaxDeliveryService
                    // $shipment->createWithJaxDelivery();
                    
                    // Pour l'instant, simulation
                    $shipment->update([
                        'status' => 'validated',
                        'pos_barcode' => 'JAX_' . uniqid(),
                    ]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Expédition #{$shipment->order_id}: " . $e->getMessage();
                    \Log::error('Shipment creation failed', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($successCount > 0) {
                $this->update([
                    'status' => self::STATUS_VALIDATED,
                    'validated_at' => now(),
                ]);

                // Enregistrer dans l'historique de chaque commande
                foreach ($this->shipments()->whereNotNull('pos_barcode')->get() as $shipment) {
                    $shipment->order->recordHistory(
                        'pickup_validated',
                        "Enlèvement #{$this->id} validé - {$successCount} expédition(s) créée(s)",
                        [
                            'pickup_id' => $this->id,
                            'carrier' => 'jax_delivery',
                            'success_count' => $successCount,
                            'error_count' => count($errors),
                        ]
                    );
                }

                \Log::info('Pickup validated', [
                    'pickup_id' => $this->id,
                    'success_count' => $successCount,
                    'error_count' => count($errors),
                ]);

                return true;
            }

            throw new \Exception('Aucune expédition n\'a pu être créée: ' . implode(', ', $errors));
        });
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
                
                // Enregistrer dans l'historique
                foreach ($this->shipments as $shipment) {
                    $shipment->order->recordHistory(
                        'delivery_anomaly',
                        "Enlèvement #{$this->id} marqué comme problématique - non récupéré après 24h",
                        ['pickup_id' => $this->id, 'hours_elapsed' => $this->validated_at->diffInHours(now())]
                    );
                }
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
            
            // Enregistrer dans l'historique
            foreach ($this->shipments as $shipment) {
                $shipment->order->recordHistory(
                    'picked_up_by_carrier',
                    "Enlèvement #{$this->id} entièrement récupéré par le transporteur",
                    ['pickup_id' => $this->id, 'shipments_count' => $totalShipments]
                );
            }
        }
    }

    public function canBeValidated(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->shipments()->count() > 0;
    }

    public function canBeModified(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeDeleted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function addOrder(Order $order): Shipment
    {
        if (!$this->canBeModified()) {
            throw new \Exception('Cet enlèvement ne peut plus être modifié.');
        }

        if ($order->status !== 'confirmée') {
            throw new \Exception('Seules les commandes confirmées peuvent être ajoutées.');
        }

        if ($order->shipments()->whereNotNull('pickup_id')->exists()) {
            throw new \Exception('Cette commande est déjà assignée à un enlèvement.');
        }

        return Shipment::create([
            'admin_id' => $this->admin_id,
            'order_id' => $order->id,
            'pickup_id' => $this->id,
            'order_number' => $order->id,
            'status' => Shipment::STATUS_CREATED,
            'weight' => $this->calculateOrderWeight($order),
            'value' => $order->total_price,
            'cod_amount' => $order->total_price,
            'nb_pieces' => 1,
            'content_description' => 'Commande #' . $order->id,
            'recipient_info' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'address' => $order->customer_address,
                'city' => $order->customer_city,
                'governorate' => $order->customer_governorate,
                'email' => $order->customer_email,
            ],
        ]);
    }

    public function removeShipment(Shipment $shipment): void
    {
        if (!$this->canBeModified()) {
            throw new \Exception('Cet enlèvement ne peut plus être modifié.');
        }

        if ($shipment->pickup_id !== $this->id) {
            throw new \Exception('Cette expédition n\'appartient pas à cet enlèvement.');
        }

        $shipment->delete();
    }

    private function calculateOrderWeight(Order $order): float
    {
        $totalWeight = 0;
        
        foreach ($order->items as $item) {
            $itemWeight = $item->product->weight ?? 0.5; // 500g par défaut
            $totalWeight += $itemWeight * $item->quantity;
        }
        
        return max($totalWeight, 0.1); // Minimum 100g
    }

    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
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

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    public function scopePickedUp($query)
    {
        return $query->where('status', self::STATUS_PICKED_UP);
    }

    public function scopeProblem($query)
    {
        return $query->where('status', self::STATUS_PROBLEM);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================
    
    public static function createForAdmin(Admin $admin, array $data): self
    {
        $deliveryConfig = DeliveryConfiguration::where('id', $data['delivery_configuration_id'])
            ->where('admin_id', $admin->id)
            ->firstOrFail();

        return self::create([
            'admin_id' => $admin->id,
            'carrier_slug' => 'jax_delivery',
            'delivery_configuration_id' => $deliveryConfig->id,
            'pickup_date' => $data['pickup_date'] ?? null,
            'status' => self::STATUS_DRAFT,
        ]);
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_PROBLEM => 'Problème',
        ];
    }
}