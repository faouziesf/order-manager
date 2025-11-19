<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

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
        'total_price', // Prix total final de la commande
        'status',
        'priority',
        'scheduled_date',
        'attempts_count',
        'daily_attempts_count',
        'last_attempt_at',
        'shipped_at',
        'delivered_at',
        'tracking_number',
        'carrier_name',
        'delivery_notes',
        'is_assigned',
        'is_suspended',
        'suspension_reason',
        'is_duplicate',
        'reviewed_for_duplicates',
        'duplicate_group_id',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:3',
        'scheduled_date' => 'date',
        'last_attempt_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
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
        return $this->belongsTo(Admin::class, 'manager_id')->where('role', Admin::ROLE_MANAGER);
    }

    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id')->where('role', Admin::ROLE_EMPLOYEE);
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
     * SCOPES DE BASE - CORRIGÉS POUR FILTRAGE PAR STOCK
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

    public function scopeOld($query)
    {
        return $query->where('status', 'ancienne');
    }

    public function scopeInTransit($query)
    {
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
     * NOUVEAUX SCOPES POUR LES FILES DE TRAITEMENT
     * ========================================
     */
    
    /**
     * Scope pour la file standard: nouvelles commandes non suspendues avec stock suffisant
     */
    public function scopeStandardQueue($query, $maxTotalAttempts = 9, $maxDailyAttempts = 3, $delayHours = 2.5)
    {
        return $query->where('status', 'nouvelle')
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });
    }

    /**
     * Scope pour la file datée: commandes datées après leur date, non suspendues, avec stock suffisant
     */
    public function scopeDatedQueue($query, $maxTotalAttempts = 5, $maxDailyAttempts = 2, $delayHours = 3.5)
    {
        return $query->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });
    }

    /**
     * Scope pour la file ancienne: commandes anciennes avec stock suffisant (même si suspendues)
     */
    public function scopeOldQueue($query, $maxDailyAttempts = 2, $delayHours = 6, $maxTotalAttempts = 0)
    {
        $query = $query->where('status', 'ancienne')
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });

        if ($maxTotalAttempts > 0) {
            $query->where('attempts_count', '<', $maxTotalAttempts);
        }

        return $query;
    }

    /**
     * Scope pour la file retour en stock: commandes suspendues nouvelles/datées avec stock maintenant suffisant
     */
    public function scopeRestockQueue($query, $maxTotalAttempts = 5, $maxDailyAttempts = 2, $delayHours = 1)
    {
        return $query->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });
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

    /**
     * Recalculer le prix total depuis les items - SUPPRESSION DES FRAIS DE LIVRAISON
     */
    public function recalculateTotal()
    {
        $total = $this->items->sum('total_price');
        $this->total_price = $total;
        $this->save();
        return $total;
    }

    /**
     * Enregistrer une action dans l'historique - VERSION SIMPLIFIÉE
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
     * Vérifier si la commande peut être traitée selon les règles de files
     */
    public function canBeProcessed($queueType = null, $maxDailyAttempts = 3, $maxTotalAttempts = 9, $delayHours = 2.5)
    {
        if ($queueType !== 'old' && $this->is_suspended) {
            return false;
        }
        
        if ($this->daily_attempts_count >= $maxDailyAttempts) {
            return false;
        }
        
        if ($maxTotalAttempts > 0 && $this->attempts_count >= $maxTotalAttempts) {
            return false;
        }
        
        if ($this->last_attempt_at) {
            $timeSinceLastAttempt = now()->diffInHours($this->last_attempt_at);
            if ($timeSinceLastAttempt < $delayHours) {
                return false;
            }
        }
        
        if ($queueType === 'dated' && $this->scheduled_date && $this->scheduled_date->isFuture()) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtenir le type de file selon le statut
     */
    public function getQueueType()
    {
        return match($this->status) {
            'datée' => 'dated',
            'ancienne' => 'old', 
            'nouvelle' => 'standard',
            default => 'standard'
        };
    }

    /**
     * Transition automatique vers file ancienne si nécessaire
     */
    public function transitionToOldIfNeeded($standardMaxAttempts = 9)
    {
        if ($this->status === 'nouvelle' && $this->attempts_count >= $standardMaxAttempts) {
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
        
        return false;
    }

    /**
     * Planifier la commande pour une date donnée
     */
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

    /**
     * Suspendre la commande
     */
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

    /**
     * Réactiver la commande
     */
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

    /**
     * Vérifier le stock et mettre à jour le statut de suspension automatiquement - CORRIGÉ
     */
    public function checkStockAndUpdateStatus()
    {
        $allInStock = true;
        $missingProducts = [];
        
        // Charger les relations si pas déjà fait
        if (!$this->relationLoaded('items')) {
            $this->load(['items.product']);
        }
        
        foreach ($this->items as $item) {
            if (!$item->product) {
                $allInStock = false;
                $missingProducts[] = 'Produit supprimé (ID: ' . $item->product_id . ')';
                continue;
            }
            
            if (!$item->product->is_active) {
                $allInStock = false;
                $missingProducts[] = $item->product->name . ' (inactif)';
                continue;
            }
            
            if ((int)$item->product->stock < (int)$item->quantity) {
                $allInStock = false;
                $missingProducts[] = $item->product->name . " (stock: {$item->product->stock}, besoin: {$item->quantity})";
            }
        }
        
        if (!$allInStock && !$this->is_suspended) {
            $this->suspend('Rupture de stock: ' . implode(', ', $missingProducts));
            Log::info("Commande {$this->id} suspendue automatiquement pour rupture de stock");
            return false;
        } 
        elseif ($allInStock && $this->is_suspended && str_contains($this->suspension_reason ?? '', 'stock')) {
            $this->reactivate('Stock disponible pour tous les produits');
            Log::info("Commande {$this->id} réactivée automatiquement car stock disponible");
            return true;
        }
        
        return $allInStock;
    }

    /**
     * Vérifier si la commande a un stock suffisant pour tous ses produits - NOUVELLE MÉTHODE OPTIMISÉE
     */
    public function hasSufficientStock()
    {
        // Charger les relations si pas déjà fait
        if (!$this->relationLoaded('items')) {
            $this->load(['items.product' => function($query) {
                $query->where('is_active', true);
            }]);
        }
        
        foreach ($this->items as $item) {
            if (!$item->product || !$item->product->is_active) {
                Log::warning("Commande {$this->id}: produit manquant ou inactif (item {$item->id})");
                return false;
            }
            
            if ((int)$item->product->stock < (int)$item->quantity) {
                Log::info("Commande {$this->id}: stock insuffisant pour produit {$item->product->id} ({$item->product->name}): besoin {$item->quantity}, stock {$item->product->stock}");
                return false;
            }
        }
        
        return true;
    }

    /**
     * Obtenir les détails des problèmes de stock - NOUVELLE MÉTHODE
     */
    public function getStockIssues()
    {
        $issues = [];
        
        if (!$this->relationLoaded('items')) {
            $this->load(['items.product']);
        }
        
        foreach ($this->items as $item) {
            if (!$item->product) {
                $issues[] = [
                    'type' => 'missing_product',
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'message' => 'Produit supprimé ou non trouvé'
                ];
                continue;
            }
            
            if (!$item->product->is_active) {
                $issues[] = [
                    'type' => 'inactive_product',
                    'item_id' => $item->id,
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'message' => 'Produit désactivé'
                ];
                continue;
            }
            
            if ((int)$item->product->stock < (int)$item->quantity) {
                $issues[] = [
                    'type' => 'insufficient_stock',
                    'item_id' => $item->id,
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity_needed' => $item->quantity,
                    'stock_available' => $item->product->stock,
                    'shortage' => (int)$item->quantity - (int)$item->product->stock,
                    'message' => "Stock insuffisant: besoin {$item->quantity}, disponible {$item->product->stock}"
                ];
            }
        }
        
        return $issues;
    }

    /**
     * ========================================
     * MÉTHODES POUR LA LIVRAISON (SIMPLIFIÉES)
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

    /**
     * ========================================
     * MÉTHODES STATIQUES POUR DÉTECTION DE DOUBLONS
     * ========================================
     */
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