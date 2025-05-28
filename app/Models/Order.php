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
        
        // Commandes nouvelles qui ont dépassé le seuil standard
        if ($this->status === 'nouvelle') {
            $standardMaxAttempts = (int)AdminSetting::get("standard_max_total_attempts", 9);
            
            if ($this->attempts_count >= $standardMaxAttempts) {
                return 'old';
            }
            
            return 'standard';
        }
        
        // Par défaut, retourner standard
        return 'standard';
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
     * CORRECTION: Scope pour la file ancienne
     */
    public function scopeOldQueue($query)
    {
        $standardMaxAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
        
        return $query->where('status', 'nouvelle')
            ->where('attempts_count', '>=', $standardMaxAttempts)
            ->availableForQueue('old');
    }
}