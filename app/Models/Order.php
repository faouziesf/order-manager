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
        'is_assigned',
        'is_suspended',
        'suspension_reason',
        'is_duplicate',           // NOUVEAU
        'reviewed_for_duplicates', // NOUVEAU
        'duplicate_group_id',     // NOUVEAU
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:3',
        'shipping_cost' => 'decimal:3',
        'confirmed_price' => 'decimal:3',
        'scheduled_date' => 'date',
        'last_attempt_at' => 'datetime',
        'is_assigned' => 'boolean',
        'is_suspended' => 'boolean',
        'is_duplicate' => 'boolean',           // NOUVEAU
        'reviewed_for_duplicates' => 'boolean', // NOUVEAU
    ];

    /**
     * Relations
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
     * Scopes
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
        return $query->where('status', 'en_route');
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

    // ======== NOUVEAUX SCOPES POUR LES DOUBLONS ========

    /**
     * Scope pour les commandes marquées comme doublons
     */
    public function scopeDuplicate($query)
    {
        return $query->where('is_duplicate', true);
    }

    /**
     * Scope pour les commandes doubles non examinées
     */
    public function scopeUnreviewedDuplicates($query)
    {
        return $query->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false);
    }

    /**
     * Scope pour les commandes doubles examinées
     */
    public function scopeReviewedDuplicates($query)
    {
        return $query->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', true);
    }

    /**
     * Scope pour les commandes fusionnables (nouvelles et datées)
     */
    public function scopeMergeable($query)
    {
        return $query->whereIn('status', ['nouvelle', 'datée'])
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false);
    }

    /**
     * Scope pour trouver les doublons d'un numéro de téléphone
     */
    public function scopeDuplicatesOf($query, $phone)
    {
        return $query->where(function($q) use ($phone) {
            $q->where('customer_phone', $phone)
              ->orWhere('customer_phone_2', $phone);
        })->where('is_duplicate', true);
    }

    /**
     * Méthodes principales
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

    public function recordHistory($action, $notes = null, $changes = null, $statusBefore = null, $statusAfter = null)
    {
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
            'status_after' => $statusAfter ?? $this->status
        ]);
    }

    /**
     * CORRECTION: Méthode pour enregistrer une tentative d'appel
     * Cette méthode incrémente les compteurs et met à jour les timestamps
     */
    public function recordCallAttempt($notes = null)
    {
        // Incrémenter les compteurs
        $this->increment('attempts_count');
        $this->increment('daily_attempts_count');
        
        // Mettre à jour la date de dernière tentative
        $this->last_attempt_at = now();
        $this->save(); // updated_at sera automatiquement mis à jour
        
        // Enregistrer dans l'historique
        if ($notes) {
            $this->recordHistory('tentative', $notes);
        }
        
        return $this;
    }

    /**
     * Réinitialise le compteur journalier (appelé automatiquement à minuit)
     */
    public function resetDailyAttempts()
    {
        $this->daily_attempts_count = 0;
        $this->save();
        return $this;
    }

    /**
     * CORRECTION: Vérifie si la commande peut être traitée selon la nouvelle logique
     */
    public function canBeProcessed($queueType = null)
    {
        // Si pas de type de file spécifié, le détecter automatiquement
        if (!$queueType) {
            $queueType = $this->getQueueType();
        }
        
        // Vérifier si la commande est suspendue
        if ($this->is_suspended) {
            return false;
        }
        
        // Récupérer les paramètres pour ce type de file
        $maxDailyAttempts = (int)AdminSetting::get("{$queueType}_max_daily_attempts", 3);
        $maxTotalAttempts = (int)AdminSetting::get("{$queueType}_max_total_attempts", 9);
        $delayHours = (float)AdminSetting::get("{$queueType}_delay_hours", 2.5);
        
        // Vérifier le nombre maximum de tentatives journalières
        if ($this->daily_attempts_count >= $maxDailyAttempts) {
            return false;
        }
        
        // Vérifier le nombre maximum de tentatives totales (sauf pour 'old' si illimité)
        if ($maxTotalAttempts > 0 && $this->attempts_count >= $maxTotalAttempts) {
            return false;
        }
        
        // Vérifier le délai depuis la dernière modification
        if ($this->last_attempt_at && $this->updated_at) {
            $timeSinceLastModification = now()->diffInHours($this->updated_at);
            if ($timeSinceLastModification < $delayHours) {
                return false;
            }
        }
        
        // Pour les commandes datées, vérifier que la date est atteinte
        if ($queueType === 'dated' && $this->scheduled_date && $this->scheduled_date->isFuture()) {
            return false;
        }
        
        return true;
    }

    /**
     * CORRECTION: Détermine le type de file pour cette commande
     */
    public function getQueueType()
    {
        // Commandes datées
        if ($this->status === 'datée') {
            return 'dated';
        }
        
        // Commandes anciennes (nouveau statut)
        if ($this->status === 'ancienne') {
            return 'old';
        }
        
        // Commandes nouvelles standard
        if ($this->status === 'nouvelle') {
            return 'standard';
        }
        
        // Par défaut, retourner standard
        return 'standard';
    }

    /**
     * NOUVELLE MÉTHODE: Transition automatique vers le statut "ancienne"
     */
    public function transitionToOldIfNeeded()
    {
        if ($this->status === 'nouvelle') {
            $standardMaxAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
            
            if ($this->attempts_count >= $standardMaxAttempts) {
                $previousStatus = $this->status;
                $this->status = 'ancienne';
                $this->save();
                
                // Enregistrer dans l'historique
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

    /**
     * CORRECTION: Transition vers une commande datée avec réinitialisation des compteurs
     */
    public function scheduleFor($date, $notes = null)
    {
        $this->status = 'datée';
        $this->scheduled_date = $date;
        
        // IMPORTANT: Réinitialiser les compteurs selon les spécifications
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
     * Suspend la commande (généralement pour rupture de stock)
     */
    public function suspend($reason = null)
    {
        $this->is_suspended = true;
        $this->suspension_reason = $reason;
        $this->save();
        
        // Enregistrer dans l'historique
        $this->recordHistory(
            'suspension', 
            'Commande suspendue: ' . ($reason ?: 'Raison non spécifiée')
        );
        
        return $this;
    }

    /**
     * Réactive une commande suspendue
     */
    public function reactivate($note = null)
    {
        $this->is_suspended = false;
        $this->suspension_reason = null;
        $this->save();
        
        // Enregistrer dans l'historique
        $this->recordHistory(
            'réactivation', 
            $note ?: 'Commande réactivée'
        );
        
        return $this;
    }

    /**
     * Vérifie la disponibilité des stocks et suspend/réactive la commande si nécessaire
     */
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

    /**
     * Vérifie le stock pour tous les produits de la commande
     */
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
     * CORRECTION: Scope pour les commandes disponibles dans une file spécifique
     */
    public function scopeAvailableForQueue($query, $queueType)
    {
        $maxDailyAttempts = (int)AdminSetting::get("{$queueType}_max_daily_attempts", 3);
        $maxTotalAttempts = (int)AdminSetting::get("{$queueType}_max_total_attempts", 9);
        $delayHours = (float)AdminSetting::get("{$queueType}_delay_hours", 2.5);
        
        return $query->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($maxTotalAttempts) {
                // Appliquer la limite totale seulement si elle est définie (> 0)
                if ($maxTotalAttempts > 0) {
                    $q->where('attempts_count', '<', $maxTotalAttempts);
                }
            })
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->notSuspended();
    }

    /**
     * CORRECTION: Scope pour la file standard
     */
    public function scopeStandardQueue($query)
    {
        $maxTotalAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
        
        return $query->where('status', 'nouvelle')
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->availableForQueue('standard');
    }

    /**
     * CORRECTION: Scope pour la file datée
     */
    public function scopeDatedQueue($query)
    {
        return $query->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->availableForQueue('dated');
    }

    /**
     * NOUVEAU: Scope pour les commandes anciennes
     */
    public function scopeOld($query)
    {
        return $query->where('status', 'ancienne');
    }

    /**
     * CORRECTION: Scope pour la file ancienne - Utilise maintenant le statut "ancienne"
     */
    public function scopeOldQueue($query)
    {
        return $query->where('status', 'ancienne')
            ->availableForQueue('old');
    }

    // ======== NOUVELLES MÉTHODES POUR LA GESTION DES DOUBLONS ========

    /**
     * Marquer la commande comme doublon
     */
    public function markAsDuplicate($groupId = null)
    {
        $this->update([
            'is_duplicate' => true,
            'duplicate_group_id' => $groupId ?: 'DUP_' . time() . '_' . $this->id
        ]);
        
        $this->recordHistory(
            'duplicate_detected',
            'Commande marquée comme doublon'
        );
        
        return $this;
    }

    /**
     * Marquer la commande comme examinée pour doublons
     */
    public function markDuplicateAsReviewed($note = null)
    {
        $this->update(['reviewed_for_duplicates' => true]);
        
        $this->recordHistory(
            'duplicate_review',
            $note ?: 'Commande marquée comme examinée pour doublons'
        );
        
        return $this;
    }

    /**
     * Retirer le marquage de doublon
     */
    public function unmarkAsDuplicate()
    {
        $this->update([
            'is_duplicate' => false,
            'reviewed_for_duplicates' => false,
            'duplicate_group_id' => null
        ]);
        
        return $this;
    }

    /**
     * Trouver toutes les commandes doubles de ce client
     */
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
     * Vérifier si cette commande peut être fusionnée avec une autre
     */
    public function canMergeWith(Order $otherOrder)
    {
        // Même admin
        if ($this->admin_id !== $otherOrder->admin_id) {
            return false;
        }
        
        // Statuts compatibles
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

    /**
     * Fusionner cette commande avec une autre
     */
    public function mergeWith(Order $otherOrder, $note = null)
    {
        if (!$this->canMergeWith($otherOrder)) {
            throw new \Exception('Ces commandes ne peuvent pas être fusionnées');
        }
        
        // Fusionner les informations client
        $mergedNames = collect([$this->customer_name, $otherOrder->customer_name])
            ->filter()
            ->unique()
            ->implode(' / ');
            
        $mergedAddresses = collect([$this->customer_address, $otherOrder->customer_address])
            ->filter()
            ->unique()
            ->implode(' / ');
        
        // Fusionner les produits
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
        
        // Mettre à jour les informations de cette commande
        $this->update([
            'customer_name' => $mergedNames,
            'customer_address' => $mergedAddresses,
            'total_price' => $this->items->sum('total_price') + $this->shipping_cost,
            'reviewed_for_duplicates' => true,
            'notes' => ($this->notes ? $this->notes . "\n" : "") . 
                      "[FUSION " . now()->format('d/m/Y H:i') . "] " . 
                      ($note ?: "Fusion avec commande #{$otherOrder->id}")
        ]);
        
        // Enregistrer l'historique
        $this->recordHistory(
            'duplicate_merge',
            "Fusion avec commande #{$otherOrder->id}",
            [
                'merged_order_id' => $otherOrder->id,
                'total_price_before' => $this->getOriginal('total_price'),
                'total_price_after' => $this->total_price,
                'admin_note' => $note
            ]
        );
        
        // Supprimer l'autre commande
        $otherOrder->delete();
        
        return $this;
    }

    /**
     * Obtenir le nombre de commandes doubles pour ce numéro de téléphone
     */
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

    /**
     * Vérifier si ce numéro de téléphone a des doublons récents (non examinés)
     */
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

    /**
     * Méthodes statiques pour la détection des doublons
     */
    public static function detectDuplicatesForAdmin($adminId)
    {
        $orders = static::where('admin_id', $adminId)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->get();
        
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

    /**
     * Vérifier si deux numéros de téléphone correspondent exactement
     */
    public static function phoneMatches($phone1, $phone2)
    {
        return $phone1 === $phone2;
    }

    /**
     * Vérifier si deux numéros ont 8 chiffres successifs identiques
     */
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

    /**
     * Scope pour compter les doublons non examinés d'un admin
     */
    public static function countUnreviewedDuplicatesForAdmin($adminId)
    {
        return static::where('admin_id', $adminId)
            ->where('status', 'nouvelle')
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->distinct('customer_phone')
            ->count('customer_phone');
    }
}