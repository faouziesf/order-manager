<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'manager_id',
        'employee_id',
        'external_id',
        'external_source',
        'customer_name',
        'customer_phone',
        'customer_phone_2',
        'customer_email',
        'customer_governorate',
        'customer_city',
        'customer_address',
        'total_price',
        'shipping_cost',
        'confirmed_price',
        'status',
        'priority',
        'scheduled_date',
        'attempts_count',
        'daily_attempts_count',
        'last_attempt_at',
        'shipped_at',             // Ajouté pour la livraison
        'delivered_at',           // Ajouté pour la livraison
        'tracking_number',        // Ajouté pour la livraison
        'carrier_name',           // Ajouté pour la livraison
        'delivery_notes',         // Ajouté pour la livraison
        'is_assigned',
        'is_suspended',
        'suspension_reason',
        'is_duplicate',           // Marquage doublon
        'reviewed_for_duplicates', // Examiné pour doublons
        'duplicate_group_id',     // ID du groupe de doublons
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:3',
        'shipping_cost' => 'decimal:3',
        'confirmed_price' => 'decimal:3',
        'scheduled_date' => 'date',
        'last_attempt_at' => 'datetime',
        'shipped_at' => 'datetime',       // Ajouté pour la livraison
        'delivered_at' => 'datetime',     // Ajouté pour la livraison
        'is_assigned' => 'boolean',
        'is_suspended' => 'boolean',
        'is_duplicate' => 'boolean',
        'reviewed_for_duplicates' => 'boolean',
    ];

    /**
     * ========================================
     * RELATIONS
     * ========================================
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'customer_governorate');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'customer_city');
    }

    /**
     * ========================================
     * NOUVELLES RELATIONS POUR LA LIVRAISON
     * ========================================
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function activeShipment()
    {
        return $this->hasOne(Shipment::class)->whereIn('status', [
            'created', 'validated', 'picked_up_by_carrier', 'in_transit'
        ])->latest();
    }

    public function deliveredShipment()
    {
        return $this->hasOne(Shipment::class)->where('status', 'delivered')->latest();
    }
    
    /**
     * ========================================
     * ACCESSORS POUR LA LIVRAISON
     * ========================================
     */
    public function getHasShipmentAttribute(): bool
    {
        return $this->shipments()->exists();
    }

    public function getIsShippedAttribute(): bool
    {
        return in_array($this->status, ['expédiée', 'en_transit', 'tentative_livraison', 'livrée']);
    }

    public function getIsDeliveredAttribute(): bool
    {
        return $this->status === 'livrée';
    }

    public function getShippingStatusLabelAttribute(): string
    {
        return match($this->status) {
            'expédiée' => 'Expédiée',
            'en_transit' => 'En transit',
            'tentative_livraison' => 'Tentative de livraison',
            'échec_livraison' => 'Échec de livraison',
            'en_retour' => 'En retour',
            'anomalie_livraison' => 'Anomalie de livraison',
            'livrée' => 'Livrée',
            default => 'Non expédiée',
        };
    }

    /**
     * ========================================
     * SCOPES DE BASE
     * ========================================
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'nouvelle');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmée');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'annulée');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'datée');
    }

    public function scopeInTransit($query)
    {
        // Statut modifié pour refléter les nouveaux statuts de livraison
        return $query->whereIn('status', ['en_route', 'en_transit']);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'livrée');
    }

    public function scopeAssigned($query)
    {
        return $query->where('is_assigned', true);
    }

    public function scopeUnassigned($query)
    {
        return $query->where('is_assigned', false);
    }

    public function scopeNotSuspended($query)
    {
        return $query->where(function($q) {
            $q->where('is_suspended', false)
            ->orWhereNull('is_suspended');
        });
    }

    public function scopeSuspended($query)
    {
        return $query->where('is_suspended', true);
    }

    /**
     * ========================================
     * NOUVEAUX SCOPES POUR LA LIVRAISON
     * ========================================
     */
    public function scopeReadyForShipping($query)
    {
        return $query->where('status', 'confirmée')
            ->where('is_suspended', false)
            ->whereDoesntHave('shipments');
    }

    public function scopeShipped($query)
    {
        return $query->whereIn('status', ['expédiée', 'en_transit', 'tentative_livraison']);
    }

    public function scopeWithShipments($query)
    {
        return $query->whereHas('shipments');
    }

    public function scopeWithActiveShipment($query)
    {
        return $query->whereHas('shipments', function($q) {
            $q->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit']);
        });
    }

    public function scopeDeliveryFailed($query)
    {
        return $query->whereIn('status', ['échec_livraison', 'en_retour', 'anomalie_livraison']);
    }

    /**
     * ========================================
     * SCOPES POUR LES DOUBLONS
     * ========================================
     */
    public function scopeDuplicate($query)
    {
        return $query->where('is_duplicate', true);
    }

    public function scopeUnreviewedDuplicates($query)
    {
        return $query->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false);
    }

    public function scopeReviewedDuplicates($query)
    {
        return $query->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', true);
    }

    public function scopeMergeable($query)
    {
        return $query->whereIn('status', ['nouvelle', 'datée'])
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false);
    }

    public function scopeNonMergeable($query)
    {
        return $query->whereNotIn('status', ['nouvelle', 'datée'])
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false);
    }

    public function scopeDuplicatesOf($query, $phone)
    {
        return $query->where(function($q) use ($phone) {
            $q->where('customer_phone', $phone)
              ->orWhere('customer_phone_2', $phone);
        })->where('is_duplicate', true);
    }

    /**
     * ========================================
     * MÉTHODES PRINCIPALES
     * ========================================
     */
    public function assignTo($employeeId)
    {
        $this->update([
            'employee_id' => $employeeId,
            'is_assigned' => true
        ]);
    }

    public function unassign()
    {
        $this->update([
            'employee_id' => null,
            'is_assigned' => false
        ]);
    }

    public function recalculateTotal()
    {
        $total = $this->items->sum('total_price');
        $this->total_price = $total;
        $this->save();
        return $total;
    }

    /**
     * Enregistrer une action dans l'historique (VERSION MISE À JOUR)
     */
    public function recordHistory(
        $action,
        $notes = null,
        $changes = null,
        $statusBefore = null,
        $statusAfter = null,
        $carrierStatusCode = null,
        $carrierStatusLabel = null,
        $trackingNumber = null,
        $carrierName = null
    ) {
        $userId = null;
        $userType = null;

        if (auth()->guard('admin')->check()) {
            $userId = auth()->guard('admin')->id();
            $userType = 'Admin';
        } elseif (auth()->guard('manager')->check()) {
            $userId = auth()->guard('manager')->id();
            $userType = 'Manager';
        } elseif (auth()->guard('employee')->check()) {
            $userId = auth()->guard('employee')->id();
            $userType = 'Employee';
        }

        return $this->history()->create([
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'notes' => $notes,
            'changes' => $changes ? json_encode($changes) : null,
            'status_before' => $statusBefore ?? $this->getOriginal('status'),
            'status_after' => $statusAfter ?? $this->status,
            'carrier_status_code' => $carrierStatusCode,
            'carrier_status_label' => $carrierStatusLabel,
            'tracking_number' => $trackingNumber,
            'carrier_name' => $carrierName,
        ]);
    }

    /**
     * Enregistrer une tentative d'appel
     */
    public function recordCallAttempt($notes = null)
    {
        $this->increment('attempts_count');
        $this->increment('daily_attempts_count');
        $this->last_attempt_at = now();
        $this->save();
        
        if ($notes) {
            $this->recordHistory('tentative', $notes);
        }
        
        return $this;
    }

    public function resetDailyAttempts()
    {
        $this->daily_attempts_count = 0;
        $this->save();
        return $this;
    }

    /**
     * Vérifier si la commande peut être traitée
     */
    public function canBeProcessed($queueType = null)
    {
        if (!$queueType) {
            $queueType = $this->getQueueType();
        }
        
        if ($this->is_suspended) {
            return false;
        }
        
        $maxDailyAttempts = (int)AdminSetting::get("{$queueType}_max_daily_attempts", 3);
        $maxTotalAttempts = (int)AdminSetting::get("{$queueType}_max_total_attempts", 9);
        $delayHours = (float)AdminSetting::get("{$queueType}_delay_hours", 2.5);
        
        if ($this->daily_attempts_count >= $maxDailyAttempts) {
            return false;
        }
        
        if ($maxTotalAttempts > 0 && $this->attempts_count >= $maxTotalAttempts) {
            return false;
        }
        
        if ($this->last_attempt_at && $this->updated_at) {
            $timeSinceLastModification = now()->diffInHours($this->updated_at);
            if ($timeSinceLastModification < $delayHours) {
                return false;
            }
        }
        
        if ($queueType === 'dated' && $this->scheduled_date && $this->scheduled_date->isFuture()) {
            return false;
        }
        
        return true;
    }

    public function getQueueType()
    {
        if ($this->status === 'datée') {
            return 'dated';
        }
        
        if ($this->status === 'ancienne') {
            return 'old';
        }
        
        if ($this->status === 'nouvelle') {
            return 'standard';
        }
        
        return 'standard';
    }

    public function transitionToOldIfNeeded()
    {
        if ($this->status === 'nouvelle') {
            $standardMaxAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
            
            if ($this->attempts_count >= $standardMaxAttempts) {
                $previousStatus = $this->status;
                $this->status = 'ancienne';
                $this->save();
                
                $this->recordHistory(
                    'changement_statut',
                    "Commande automatiquement passée en file ancienne après {$standardMaxAttempts} tentatives",
                    ['auto_transition' => true, 'attempts_reached' => $this->attempts_count],
                    $previousStatus,
                    'ancienne'
                );
                
                return true;
            }
        }
        
        return false;
    }

    public function scheduleFor($date, $notes = null)
    {
        $this->status = 'datée';
        $this->scheduled_date = $date;
        $this->attempts_count = 0;
        $this->daily_attempts_count = 0;
        $this->last_attempt_at = null;
        $this->save();
        
        if ($notes) {
            $this->recordHistory('datation', $notes);
        }
        
        return $this;
    }

    public function suspend($reason = null)
    {
        $this->is_suspended = true;
        $this->suspension_reason = $reason;
        $this->save();
        
        $this->recordHistory(
            'suspension', 
            'Commande suspendue: ' . ($reason ?: 'Raison non spécifiée')
        );
        
        return $this;
    }

    public function reactivate($note = null)
    {
        $this->is_suspended = false;
        $this->suspension_reason = null;
        $this->save();
        
        $this->recordHistory(
            'réactivation', 
            $note ?: 'Commande réactivée'
        );
        
        return $this;
    }

    public function checkStockAndUpdateStatus()
    {
        $allInStock = true;
        $missingProducts = [];
        
        foreach ($this->items as $item) {
            if ($item->product && $item->product->stock < $item->quantity) {
                $allInStock = false;
                $missingProducts[] = $item->product->name;
            }
        }
        
        if (!$allInStock && !$this->is_suspended) {
            $this->suspend('Rupture de stock: ' . implode(', ', $missingProducts));
            return false;
        } 
        elseif ($allInStock && $this->is_suspended) {
            $this->reactivate('Stock disponible pour tous les produits');
            return true;
        }
        
        return $allInStock;
    }

    public function hasSufficientStock()
    {
        foreach ($this->items as $item) {
            if ($item->product && $item->product->stock < $item->quantity) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * ========================================
     * MÉTHODES POUR LA LIVRAISON
     * ========================================
     */

    /**
     * Vérifier si la commande peut être expédiée
     */
    public function canBeShipped(): bool
    {
        return $this->status === 'confirmée' && 
               !$this->is_suspended && 
               !$this->shipments()->exists() &&
               $this->hasSufficientStock();
    }
    
    /**
     * Marquer comme expédiée
     */
    public function markAsShipped($trackingNumber = null, $carrierName = null, $notes = null)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => 'expédiée',
            'shipped_at' => now(),
            'tracking_number' => $trackingNumber,
            'carrier_name' => $carrierName,
        ]);

        $this->recordHistory(
            'shipment_created',
            $notes ?: "Commande expédiée via {$carrierName}",
            [
                'tracking_number' => $trackingNumber,
                'carrier_name' => $carrierName,
                'shipped_at' => $this->shipped_at,
            ],
            $oldStatus,
            'expédiée',
            null,
            null,
            $trackingNumber,
            $carrierName
        );

        return $this;
    }

    /**
     * Mettre à jour le statut de livraison
     */
    public function updateDeliveryStatus(
        $newStatus, 
        $carrierStatusCode = null, 
        $carrierStatusLabel = null, 
        $notes = null,
        $trackingNumber = null
    ) {
        $oldStatus = $this->status;
        
        $updateData = ['status' => $newStatus];
        
        if ($newStatus === 'livrée') {
            $updateData['delivered_at'] = now();
        }
        
        $this->update($updateData);

        $action = match($newStatus) {
            'en_transit' => 'in_transit',
            'tentative_livraison' => 'delivery_attempted',
            'échec_livraison' => 'delivery_failed',
            'livrée' => 'livraison',
            'en_retour' => 'in_return',
            'anomalie_livraison' => 'delivery_anomaly',
            default => 'tracking_updated',
        };

        $this->recordHistory(
            $action,
            $notes ?: "Statut mis à jour: {$newStatus}",
            [
                'carrier_status_code' => $carrierStatusCode,
                'carrier_status_label' => $carrierStatusLabel,
                'updated_at' => now(),
            ],
            $oldStatus,
            $newStatus,
            $carrierStatusCode,
            $carrierStatusLabel,
            $trackingNumber ?: $this->tracking_number,
            $this->carrier_name
        );

        return $this;
    }

    /**
     * Obtenir l'historique de livraison
     */
    public function getDeliveryHistory()
    {
        return $this->history()
            ->whereIn('action', [
                'shipment_created', 'shipment_validated', 'picked_up_by_carrier',
                'in_transit', 'delivery_attempted', 'delivery_failed', 'livraison',
                'in_return', 'delivery_anomaly', 'tracking_updated'
            ])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir le dernier statut de livraison
     */
    public function getLastDeliveryStatus()
    {
        return $this->getDeliveryHistory()->first();
    }

    /**
     * ========================================
     * SCOPES POUR LES FILES
     * ========================================
     */
    public function scopeAvailableForQueue($query, $queueType)
    {
        $maxDailyAttempts = (int)AdminSetting::get("{$queueType}_max_daily_attempts", 3);
        $maxTotalAttempts = (int)AdminSetting::get("{$queueType}_max_total_attempts", 9);
        $delayHours = (float)AdminSetting::get("{$queueType}_delay_hours", 2.5);
        
        return $query->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($maxTotalAttempts) {
                if ($maxTotalAttempts > 0) {
                    $q->where('attempts_count', '<', $maxTotalAttempts);
                }
            })
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->notSuspended();
    }

    public function scopeStandardQueue($query)
    {
        $maxTotalAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
        
        return $query->where('status', 'nouvelle')
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->availableForQueue('standard');
    }

    public function scopeDatedQueue($query)
    {
        return $query->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->availableForQueue('dated');
    }

    public function scopeOld($query)
    {
        return $query->where('status', 'ancienne');
    }

    public function scopeOldQueue($query)
    {
        return $query->where('status', 'ancienne')
            ->availableForQueue('old');
    }

    /**
     * ========================================
     * MÉTHODES POUR LA GESTION DES DOUBLONS
     * ========================================
     */
    public function markAsDuplicate($groupId = null)
    {
        $this->update([
            'is_duplicate' => true,
            'duplicate_group_id' => $groupId ?: 'DUP_' . time() . '_' . $this->id
        ]);
        
        $this->recordHistory(
            'duplicate_detected',
            'Commande marquée comme doublon (Statut: ' . $this->status . ')'
        );
        
        return $this;
    }

    public function markDuplicateAsReviewed($note = null)
    {
        $this->update(['reviewed_for_duplicates' => true]);
        
        $this->recordHistory(
            'duplicate_review',
            $note ?: 'Commande marquée comme examinée pour doublons (Statut: ' . $this->status . ')'
        );
        
        return $this;
    }

    public function unmarkAsDuplicate()
    {
        $this->update([
            'is_duplicate' => false,
            'reviewed_for_duplicates' => false,
            'duplicate_group_id' => null
        ]);
        
        return $this;
    }

    public function getDuplicateOrders()
    {
        return static::where('admin_id', $this->admin_id)
            ->where(function($q) {
                $q->where('customer_phone', $this->customer_phone);
                if ($this->customer_phone_2) {
                    $q->orWhere('customer_phone', $this->customer_phone_2)
                      ->orWhere('customer_phone_2', $this->customer_phone)
                      ->orWhere('customer_phone_2', $this->customer_phone_2);
                }
            })
            ->where('id', '!=', $this->id)
            ->where('is_duplicate', true)
            ->get();
    }

    public function canMergeWith(Order $otherOrder)
    {
        if ($this->admin_id !== $otherOrder->admin_id) {
            return false;
        }
        
        $mergeableStatuses = ['nouvelle', 'datée'];
        if (!in_array($this->status, $mergeableStatuses) || !in_array($otherOrder->status, $mergeableStatuses)) {
            return false;
        }
        
        $compatibleStatuses = [
            ['nouvelle', 'nouvelle'],
            ['datée', 'datée'],
            ['nouvelle', 'datée'],
            ['datée', 'nouvelle']
        ];
        
        $statusPair = [$this->status, $otherOrder->status];
        
        foreach ($compatibleStatuses as $compatible) {
            if (($statusPair[0] === $compatible[0] && $statusPair[1] === $compatible[1]) ||
                ($statusPair[0] === $compatible[1] && $statusPair[1] === $compatible[0])) {
                return true;
            }
        }
        
        return false;
    }

    public function mergeWith(Order $otherOrder, $note = null)
    {
        if (!$this->canMergeWith($otherOrder)) {
            throw new \Exception('Ces commandes ne peuvent pas être fusionnées (seules les commandes nouvelle/datée sont fusionnables)');
        }
        
        $mergedNames = collect([$this->customer_name, $otherOrder->customer_name])
            ->filter()->unique()->implode(' / ');
            
        $mergedAddresses = collect([$this->customer_address, $otherOrder->customer_address])
            ->filter()->unique()->implode(' / ');
        
        foreach ($otherOrder->items as $item) {
            $existingItem = $this->items->where('product_id', $item->product_id)->first();
            
            if ($existingItem) {
                $existingItem->quantity += $item->quantity;
                $existingItem->total_price += $item->total_price;
                $existingItem->save();
            } else {
                $this->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price
                ]);
            }
        }
        
        $this->update([
            'customer_name' => $mergedNames,
            'customer_address' => $mergedAddresses,
            'total_price' => $this->items->sum('total_price') + $this->shipping_cost,
            'reviewed_for_duplicates' => true,
            'notes' => ($this->notes ? $this->notes . "\n" : "") . 
                      "[FUSION " . now()->format('d/m/Y H:i') . "] " . 
                      ($note ?: "Fusion avec commande #{$otherOrder->id} (Statut: {$otherOrder->status})")
        ]);
        
        $this->recordHistory(
            'duplicate_merge',
            "Fusion avec commande #{$otherOrder->id} (Statut: {$otherOrder->status})",
            [
                'merged_order_id' => $otherOrder->id,
                'merged_order_status' => $otherOrder->status,
                'total_price_before' => $this->getOriginal('total_price'),
                'total_price_after' => $this->total_price,
                'admin_note' => $note
            ]
        );
        
        $otherOrder->delete();
        
        return $this;
    }

    public function getDuplicateCount()
    {
        return static::where('admin_id', $this->admin_id)
            ->where(function($q) {
                $q->where('customer_phone', $this->customer_phone);
                if ($this->customer_phone_2) {
                    $q->orWhere('customer_phone', $this->customer_phone_2)
                      ->orWhere('customer_phone_2', $this->customer_phone)
                      ->orWhere('customer_phone_2', $this->customer_phone_2);
                }
            })
            ->where('is_duplicate', true)
            ->count();
    }

    public function getMergeableDuplicateCount()
    {
        return static::where('admin_id', $this->admin_id)
            ->where(function($q) {
                $q->where('customer_phone', $this->customer_phone);
                if ($this->customer_phone_2) {
                    $q->orWhere('customer_phone', $this->customer_phone_2)
                      ->orWhere('customer_phone_2', $this->customer_phone)
                      ->orWhere('customer_phone_2', $this->customer_phone_2);
                }
            })
            ->where('is_duplicate', true)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->count();
    }

    public function hasRecentDuplicates()
    {
        return static::where('admin_id', $this->admin_id)
            ->where(function($q) {
                $q->where('customer_phone', $this->customer_phone);
                if ($this->customer_phone_2) {
                    $q->orWhere('customer_phone', $this->customer_phone_2)
                      ->orWhere('customer_phone_2', $this->customer_phone)
                      ->orWhere('customer_phone_2', $this->customer_phone_2);
                }
            })
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->where('id', '!=', $this->id)
            ->exists();
    }

    public function hasMergeableDuplicates()
    {
        return static::where('admin_id', $this->admin_id)
            ->where(function($q) {
                $q->where('customer_phone', $this->customer_phone);
                if ($this->customer_phone_2) {
                    $q->orWhere('customer_phone', $this->customer_phone_2)
                      ->orWhere('customer_phone_2', $this->customer_phone)
                      ->orWhere('customer_phone_2', $this->customer_phone_2);
                }
            })
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where('id', '!=', $this->id)
            ->exists();
    }

    public static function detectDuplicatesForAdmin($adminId)
    {
        $orders = static::where('admin_id', $adminId)->get();
        $duplicatesFound = 0;
        $processedPhones = [];
        
        foreach ($orders as $order1) {
            if (in_array($order1->customer_phone, $processedPhones)) {
                continue;
            }
            
            $duplicateOrders = collect();
            
            foreach ($orders as $order2) {
                if ($order1->id !== $order2->id && 
                    (static::phoneMatches($order1->customer_phone, $order2->customer_phone) ||
                     static::has8SuccessiveDigits($order1->customer_phone, $order2->customer_phone))) {
                    
                    $duplicateOrders->push($order2);
                }
            }
            
            if ($duplicateOrders->count() > 0) {
                $duplicateOrders->push($order1);
                $groupId = 'DUP_' . time() . '_' . $order1->id;
                
                foreach ($duplicateOrders as $dupOrder) {
                    $dupOrder->markAsDuplicate($groupId);
                }
                
                $duplicatesFound += $duplicateOrders->count();
                $processedPhones[] = $order1->customer_phone;
            }
        }
        
        return $duplicatesFound;
    }

    public static function phoneMatches($phone1, $phone2)
    {
        return $phone1 === $phone2;
    }

    public static function has8SuccessiveDigits($phone1, $phone2)
    {
        $digits1 = preg_replace('/\D/', '', $phone1);
        $digits2 = preg_replace('/\D/', '', $phone2);
        
        if (strlen($digits1) < 8 || strlen($digits2) < 8) {
            return false;
        }
        
        for ($i = 0; $i <= strlen($digits1) - 8; $i++) {
            $substring = substr($digits1, $i, 8);
            if (strpos($digits2, $substring) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public static function countUnreviewedDuplicatesForAdmin($adminId)
    {
        return static::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->distinct('customer_phone')
            ->count('customer_phone');
    }

    public static function countMergeableUnreviewedDuplicatesForAdmin($adminId)
    {
        return static::where('admin_id', $adminId)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->distinct('customer_phone')
            ->count('customer_phone');
    }
}