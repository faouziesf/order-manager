<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    // CONSTANTES DE STATUTS
    // ========================================

    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_PROBLEM = 'problem';

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * L'admin propriétaire de ce pickup
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * La configuration de livraison utilisée
     */
    public function deliveryConfiguration()
    {
        return $this->belongsTo(DeliveryConfiguration::class);
    }

    /**
     * Les expéditions incluses dans ce pickup
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Les commandes incluses via les expéditions
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, Shipment::class, 'pickup_id', 'id', 'id', 'order_id');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Obtenir le nom du transporteur
     */
    public function getCarrierNameAttribute()
    {
        $carriers = config('carriers');
        return $carriers[$this->carrier_slug]['name'] ?? ucfirst(str_replace('_', ' ', $this->carrier_slug));
    }

    /**
     * Obtenir la couleur du badge de statut
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_VALIDATED => 'success',
            self::STATUS_PICKED_UP => 'info',
            self::STATUS_PROBLEM => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_PROBLEM => 'Problème',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'fa-edit',
            self::STATUS_VALIDATED => 'fa-check',
            self::STATUS_PICKED_UP => 'fa-truck',
            self::STATUS_PROBLEM => 'fa-exclamation-triangle',
            default => 'fa-question',
        };
    }

    /**
     * Vérifier si le pickup peut être modifié
     */
    public function getCanBeEditedAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Vérifier si le pickup peut être validé
     */
    public function getCanBeValidatedAttribute()
    {
        return $this->status === self::STATUS_DRAFT && 
               $this->shipments()->exists() &&
               $this->deliveryConfiguration &&
               $this->deliveryConfiguration->is_active;
    }

    /**
     * Vérifier si le pickup peut être supprimé
     */
    public function getCanBeDeletedAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Obtenir le nombre total d'expéditions
     */
    public function getShipmentsCountAttribute()
    {
        return $this->shipments()->count();
    }

    /**
     * Obtenir le nombre total de commandes
     */
    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    /**
     * Obtenir la valeur totale COD
     */
    public function getTotalCodAmountAttribute()
    {
        return $this->shipments()->sum('cod_amount');
    }

    /**
     * Obtenir le poids total
     */
    public function getTotalWeightAttribute()
    {
        return $this->shipments()->sum('weight');
    }

    /**
     * Obtenir le nombre total de pièces
     */
    public function getTotalPiecesAttribute()
    {
        return $this->shipments()->sum('nb_pieces');
    }

    /**
     * Vérifier si le pickup est en retard
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->pickup_date) {
            return false;
        }
        
        return $this->pickup_date->isPast() && 
               in_array($this->status, [self::STATUS_DRAFT, self::STATUS_VALIDATED]);
    }

    /**
     * Obtenir les jours de retard
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        
        return $this->pickup_date->diffInDays(now());
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope pour les pickups en brouillon
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope pour les pickups validés
     */
    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    /**
     * Scope pour les pickups récupérés
     */
    public function scopePickedUp($query)
    {
        return $query->where('status', self::STATUS_PICKED_UP);
    }

    /**
     * Scope pour les pickups avec problème
     */
    public function scopeProblem($query)
    {
        return $query->where('status', self::STATUS_PROBLEM);
    }

    /**
     * Scope pour un transporteur spécifique
     */
    public function scopeForCarrier($query, $carrierSlug)
    {
        return $query->where('carrier_slug', $carrierSlug);
    }

    /**
     * Scope pour un admin spécifique
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope pour les pickups d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('pickup_date', today());
    }

    /**
     * Scope pour les pickups en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('pickup_date', '<', today())
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_VALIDATED]);
    }

    /**
     * Scope pour les pickups prêts à être validés
     */
    public function scopeReadyToValidate($query)
    {
        return $query->where('status', self::STATUS_DRAFT)
            ->whereHas('shipments')
            ->whereHas('deliveryConfiguration', function($q) {
                $q->where('is_active', true);
            });
    }

    // ========================================
    // MÉTHODES PRINCIPALES
    // ========================================

    /**
     * Valider le pickup (envoi vers l'API transporteur)
     */
    public function validate()
    {
        if (!$this->can_be_validated) {
            throw new \Exception('Ce pickup ne peut pas être validé');
        }

        try {
            // TODO: Implémenter l'envoi vers l'API dans Phase 4
            // Pour l'instant, juste changer le statut
            
            $this->update([
                'status' => self::STATUS_VALIDATED,
                'validated_at' => now(),
            ]);

            // Mettre à jour le statut des expéditions
            $this->shipments()->update(['status' => 'validated']);

            // Enregistrer dans l'historique des commandes
            foreach ($this->orders as $order) {
                $order->recordHistory(
                    'pickup_validated',
                    "Pickup #{$this->id} validé et envoyé au transporteur {$this->carrier_name}",
                    [
                        'pickup_id' => $this->id,
                        'carrier_slug' => $this->carrier_slug,
                        'pickup_date' => $this->pickup_date->toDateString(),
                        'validated_at' => $this->validated_at->toISOString(),
                    ],
                    $order->status,
                    $order->status, // Pas de changement de statut pour l'instant
                    null,
                    'Pickup validé',
                    null,
                    $this->carrier_name
                );
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('Erreur validation pickup', [
                'pickup_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Marquer comme récupéré par le transporteur
     */
    public function markAsPickedUp()
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            throw new \Exception('Seuls les pickups validés peuvent être marqués comme récupérés');
        }

        $this->update(['status' => self::STATUS_PICKED_UP]);

        // Mettre à jour le statut des expéditions
        $this->shipments()->update(['status' => 'picked_up_by_carrier']);

        // Enregistrer dans l'historique des commandes
        foreach ($this->orders as $order) {
            $order->recordHistory(
                'picked_up_by_carrier',
                "Pickup #{$this->id} récupéré par le transporteur {$this->carrier_name}",
                [
                    'pickup_id' => $this->id,
                    'carrier_slug' => $this->carrier_slug,
                ],
                $order->status,
                'en_transit',
                null,
                'Récupéré par transporteur',
                null,
                $this->carrier_name
            );

            // Mettre à jour le statut de la commande
            $order->update(['status' => 'en_transit']);
        }
    }

    /**
     * Marquer comme ayant un problème
     */
    public function markAsProblem($reason = null)
    {
        $this->update(['status' => self::STATUS_PROBLEM]);

        // Enregistrer dans l'historique des commandes
        foreach ($this->orders as $order) {
            $order->recordHistory(
                'pickup_problem',
                "Problème avec pickup #{$this->id}: " . ($reason ?: 'Raison non spécifiée'),
                [
                    'pickup_id' => $this->id,
                    'carrier_slug' => $this->carrier_slug,
                    'problem_reason' => $reason,
                ],
                $order->status,
                $order->status,
                null,
                'Problème pickup',
                null,
                $this->carrier_name
            );
        }
    }

    /**
     * Ajouter une expédition au pickup
     */
    public function addShipment(Shipment $shipment)
    {
        if (!$this->can_be_edited) {
            throw new \Exception('Ce pickup ne peut plus être modifié');
        }

        if ($shipment->pickup_id && $shipment->pickup_id !== $this->id) {
            throw new \Exception('Cette expédition est déjà assignée à un autre pickup');
        }

        $shipment->update(['pickup_id' => $this->id]);
        
        return $this;
    }

    /**
     * Retirer une expédition du pickup
     */
    public function removeShipment(Shipment $shipment)
    {
        if (!$this->can_be_edited) {
            throw new \Exception('Ce pickup ne peut plus être modifié');
        }

        if ($shipment->pickup_id === $this->id) {
            $shipment->update(['pickup_id' => null, 'status' => 'created']);
        }
        
        return $this;
    }

    /**
     * Obtenir le résumé du pickup
     */
    public function getSummary()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'carrier_name' => $this->carrier_name,
            'pickup_date' => $this->pickup_date?->format('d/m/Y'),
            'shipments_count' => $this->shipments_count,
            'orders_count' => $this->orders_count,
            'total_cod_amount' => $this->total_cod_amount,
            'total_weight' => $this->total_weight,
            'total_pieces' => $this->total_pieces,
            'is_overdue' => $this->is_overdue,
            'days_overdue' => $this->days_overdue,
            'can_be_validated' => $this->can_be_validated,
            'can_be_edited' => $this->can_be_edited,
            'can_be_deleted' => $this->can_be_deleted,
        ];
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Créer un nouveau pickup pour un admin et transporteur
     */
    public static function createForCarrier($adminId, $carrierSlug, $configurationId, $pickupDate = null)
    {
        return static::create([
            'admin_id' => $adminId,
            'carrier_slug' => $carrierSlug,
            'delivery_configuration_id' => $configurationId,
            'status' => self::STATUS_DRAFT,
            'pickup_date' => $pickupDate ?: now()->addDay()->format('Y-m-d'),
        ]);
    }

    /**
     * Obtenir les statistiques des pickups pour un admin
     */
    public static function getStatsForAdmin($adminId)
    {
        $pickups = static::where('admin_id', $adminId);
        
        return [
            'total' => $pickups->count(),
            'draft' => $pickups->where('status', self::STATUS_DRAFT)->count(),
            'validated' => $pickups->where('status', self::STATUS_VALIDATED)->count(),
            'picked_up' => $pickups->where('status', self::STATUS_PICKED_UP)->count(),
            'problem' => $pickups->where('status', self::STATUS_PROBLEM)->count(),
            'overdue' => static::where('admin_id', $adminId)->overdue()->count(),
            'today' => static::where('admin_id', $adminId)->today()->count(),
        ];
    }

    /**
     * Obtenir les pickups récents pour un admin
     */
    public static function getRecentForAdmin($adminId, $limit = 10)
    {
        return static::where('admin_id', $adminId)
            ->with(['deliveryConfiguration', 'shipments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir tous les statuts disponibles
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PICKED_UP => 'Récupéré',
            self::STATUS_PROBLEM => 'Problème',
        ];
    }
}