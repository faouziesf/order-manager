<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'order_id',
        'pickup_id',
        'carrier_slug',
        'pos_barcode',
        'return_barcode',
        'pos_reference',
        'order_number',
        'status',
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
        'carrier_last_status_update',
        'delivery_notes',
    ];

    protected $casts = [
        'fparcel_data' => 'json',
        'sender_info' => 'json',
        'recipient_info' => 'json',
        'weight' => 'decimal:2',
        'value' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'pickup_date' => 'date',
        'delivered_at' => 'datetime',
        'carrier_last_status_update' => 'datetime',
    ];

    // ========================================
    // CONSTANTES DE STATUTS
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

    /**
     * L'admin propriétaire de cette expédition
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * La commande associée
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Le pickup associé
     */
    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }

    /**
     * L'historique des statuts via la commande associée
     */
    public function statusHistory()
    {
        if (!$this->order) {
            return collect();
        }
        
        return $this->order->history()
            ->whereIn('action', [
                'shipment_created', 'shipment_validated', 'pickup_created', 'pickup_validated',
                'picked_up_by_carrier', 'in_transit', 'delivery_attempted', 'delivery_failed',
                'livraison', 'in_return', 'delivery_anomaly', 'tracking_updated'
            ])
            ->orderBy('created_at', 'desc');
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
            self::STATUS_CREATED => 'secondary',
            self::STATUS_VALIDATED => 'primary',
            self::STATUS_PICKED_UP_BY_CARRIER => 'warning',
            self::STATUS_IN_TRANSIT => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_IN_RETURN => 'warning',
            self::STATUS_ANOMALY => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
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
            default => 'Statut inconnu',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_CREATED => 'fa-plus',
            self::STATUS_VALIDATED => 'fa-check',
            self::STATUS_PICKED_UP_BY_CARRIER => 'fa-truck-pickup',
            self::STATUS_IN_TRANSIT => 'fa-truck-moving',
            self::STATUS_DELIVERED => 'fa-check-circle',
            self::STATUS_CANCELLED => 'fa-times',
            self::STATUS_IN_RETURN => 'fa-undo',
            self::STATUS_ANOMALY => 'fa-exclamation-triangle',
            default => 'fa-question',
        };
    }

    /**
     * Vérifier si l'expédition est en cours
     */
    public function getIsActiveAttribute()
    {
        return in_array($this->status, [
            self::STATUS_CREATED,
            self::STATUS_VALIDATED,
            self::STATUS_PICKED_UP_BY_CARRIER,
            self::STATUS_IN_TRANSIT,
        ]);
    }

    /**
     * Vérifier si l'expédition est terminée
     */
    public function getIsCompletedAttribute()
    {
        return in_array($this->status, [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Vérifier si l'expédition a un problème
     */
    public function getHasProblemAttribute()
    {
        return in_array($this->status, [
            self::STATUS_IN_RETURN,
            self::STATUS_ANOMALY,
        ]);
    }

    /**
     * Obtenir le numéro de suivi principal
     */
    public function getTrackingNumberAttribute()
    {
        return $this->pos_barcode ?: $this->pos_reference ?: $this->order_number;
    }

    /**
     * Vérifier si l'expédition peut être suivie
     */
    public function getCanBeTrackedAttribute()
    {
        return !empty($this->tracking_number) && $this->is_active;
    }

    /**
     * Obtenir les jours depuis la création
     */
    public function getDaysOldAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Vérifier si l'expédition est en retard
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->pickup_date || $this->is_completed) {
            return false;
        }

        return $this->pickup_date->addDays(3)->isPast(); // 3 jours de délai standard
    }

    /**
     * Obtenir les informations du destinataire formatées
     */
    public function getFormattedRecipientAttribute()
    {
        $info = $this->recipient_info;
        if (!$info) return null;

        $result = $info['name'] ?? 'Nom inconnu';
        if (!empty($info['phone'])) {
            $result .= ' - ' . $info['phone'];
        }
        if (!empty($info['city'])) {
            $result .= ' (' . $info['city'] . ')';
        }

        return $result;
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope pour les expéditions actives
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_CREATED,
            self::STATUS_VALIDATED,
            self::STATUS_PICKED_UP_BY_CARRIER,
            self::STATUS_IN_TRANSIT,
        ]);
    }

    /**
     * Scope pour les expéditions terminées
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Scope pour les expéditions avec problème
     */
    public function scopeWithProblem($query)
    {
        return $query->whereIn('status', [
            self::STATUS_IN_RETURN,
            self::STATUS_ANOMALY,
        ]);
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
     * Scope pour les expéditions livrées
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope pour les expéditions pouvant être suivies
     */
    public function scopeTrackable($query)
    {
        return $query->whereNotNull('pos_barcode')
            ->orWhereNotNull('pos_reference')
            ->orWhereNotNull('order_number');
    }

    /**
     * Scope pour les expéditions en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('pickup_date', '<', now()->subDays(3))
            ->whereNotIn('status', [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope pour les expéditions d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ========================================
    // MÉTHODES PRINCIPALES
    // ========================================

    /**
     * Mettre à jour le statut avec historique
     */
    public function updateStatus($newStatus, $carrierStatusCode = null, $carrierStatusLabel = null, $notes = null)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $newStatus,
            'carrier_last_status_update' => now(),
        ]);

        // Si livré, enregistrer la date
        if ($newStatus === self::STATUS_DELIVERED && !$this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        }

        // Enregistrer dans l'historique de la commande (pas shipment_status_history)
        if ($this->order) {
            $action = match($newStatus) {
                self::STATUS_CREATED => 'shipment_created',
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
                $notes ?: "Statut expédition mis à jour: {$this->getStatusLabelAttribute()}",
                [
                    'shipment_id' => $this->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'carrier_status_code' => $carrierStatusCode,
                    'carrier_status_label' => $carrierStatusLabel,
                ],
                $oldStatus,
                $newStatus,
                $carrierStatusCode,
                $carrierStatusLabel,
                $this->tracking_number,
                $this->carrier_name
            );
        }

        // Mettre à jour le statut de la commande si associée
        if ($this->order) {
            $orderStatus = $this->mapToOrderStatus($newStatus);
            if ($orderStatus && $orderStatus !== $this->order->status) {
                $this->order->updateDeliveryStatus(
                    $orderStatus,
                    $carrierStatusCode,
                    $carrierStatusLabel,
                    $notes,
                    $this->tracking_number
                );
            }
        }

        return $this;
    }

    /**
     * Mapper le statut d'expédition vers le statut de commande
     */
    protected function mapToOrderStatus($shipmentStatus)
    {
        return match($shipmentStatus) {
            self::STATUS_CREATED, self::STATUS_VALIDATED => 'expédiée',
            self::STATUS_PICKED_UP_BY_CARRIER, self::STATUS_IN_TRANSIT => 'en_transit',
            self::STATUS_DELIVERED => 'livrée',
            self::STATUS_IN_RETURN => 'en_retour',
            self::STATUS_ANOMALY => 'anomalie_livraison',
            self::STATUS_CANCELLED => 'annulée',
            default => null,
        };
    }

    /**
     * Marquer comme livré
     */
    public function markAsDelivered($notes = null)
    {
        $this->updateStatus(self::STATUS_DELIVERED, null, 'Livré', $notes);
        
        if (!$this->delivered_at) {
            $this->update(['delivered_at' => now()]);
        }
        
        return $this;
    }

    /**
     * Marquer comme ayant une anomalie
     */
    public function markAsAnomaly($reason = null)
    {
        $this->updateStatus(self::STATUS_ANOMALY, 'ANOMALY', $reason ?: 'Anomalie de livraison');
        return $this;
    }

    /**
     * Marquer comme en retour
     */
    public function markAsReturn($reason = null)
    {
        $this->updateStatus(self::STATUS_IN_RETURN, 'RETURN', $reason ?: 'Colis en retour');
        return $this;
    }

    /**
     * Obtenir la configuration du transporteur
     */
    public function getCarrierConfig()
    {
        $carriers = config('carriers');
        return $carriers[$this->carrier_slug] ?? null;
    }

    /**
     * Préparer les données pour l'API du transporteur
     */
    public function prepareApiData()
    {
        if (!$this->order) {
            throw new \Exception('Commande non trouvée pour cette expédition');
        }

        // Utiliser la configuration du pickup si disponible
        $deliveryConfig = $this->pickup ? $this->pickup->deliveryConfiguration : null;
        
        if (!$deliveryConfig) {
            throw new \Exception('Configuration de transporteur non trouvée');
        }

        return $deliveryConfig->prepareApiData($this->order, $this);
    }

    /**
     * Obtenir le résumé de l'expédition
     */
    public function getSummary()
    {
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'carrier_name' => $this->carrier_name,
            'recipient' => $this->formatted_recipient,
            'cod_amount' => $this->cod_amount,
            'weight' => $this->weight,
            'nb_pieces' => $this->nb_pieces,
            'pickup_date' => $this->pickup_date?->format('d/m/Y'),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            'delivered_at' => $this->delivered_at?->format('d/m/Y H:i'),
            'is_active' => $this->is_active,
            'is_completed' => $this->is_completed,
            'has_problem' => $this->has_problem,
            'can_be_tracked' => $this->can_be_tracked,
            'is_overdue' => $this->is_overdue,
            'days_old' => $this->days_old,
        ];
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Créer une nouvelle expédition pour une commande
     */
    public static function createForOrder($order, $pickupId = null, $carrierSlug = 'jax_delivery')
    {
        return static::create([
            'admin_id' => $order->admin_id,
            'order_id' => $order->id,
            'pickup_id' => $pickupId,
            'carrier_slug' => $carrierSlug,
            'status' => self::STATUS_CREATED,
            'weight' => static::calculateWeight($order),
            'value' => $order->total_price,
            'cod_amount' => $order->total_price,
            'nb_pieces' => $order->items->sum('quantity'),
            'content_description' => static::generateDescription($order),
            'recipient_info' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'phone_2' => $order->customer_phone_2,
                'address' => $order->customer_address,
                'governorate' => $order->customer_governorate,
                'city' => $order->customer_city,
            ],
        ]);
    }

    /**
     * Calculer le poids d'une commande
     */
    protected static function calculateWeight($order)
    {
        $itemsCount = $order->items->sum('quantity');
        return max(1.0, $itemsCount * 0.5); // 0.5kg par article minimum
    }

    /**
     * Générer la description du contenu
     */
    protected static function generateDescription($order)
    {
        $items = $order->items->take(3)->pluck('product.name')->filter()->toArray();
        $description = implode(', ', $items);
        
        if ($order->items->count() > 3) {
            $description .= ' et ' . ($order->items->count() - 3) . ' autres articles';
        }
        
        return substr($description ?: 'Commande e-commerce', 0, 200);
    }

    /**
     * Obtenir les statistiques des expéditions pour un admin
     */
    public static function getStatsForAdmin($adminId)
    {
        $shipments = static::where('admin_id', $adminId);
        
        return [
            'total' => $shipments->count(),
            'active' => $shipments->active()->count(),
            'completed' => $shipments->completed()->count(),
            'delivered' => $shipments->delivered()->count(),
            'with_problem' => $shipments->withProblem()->count(),
            'overdue' => $shipments->overdue()->count(),
            'today' => $shipments->today()->count(),
            'trackable' => $shipments->trackable()->count(),
        ];
    }

    /**
     * Obtenir les expéditions récentes pour un admin
     */
    public static function getRecentForAdmin($adminId, $limit = 10)
    {
        return static::where('admin_id', $adminId)
            ->with(['order', 'pickup'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir toutes les expéditions à suivre
     */
    public static function getTrackableShipments($adminId = null, $carrierSlug = null)
    {
        $query = static::active()->trackable();
        
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        
        if ($carrierSlug) {
            $query->where('carrier_slug', $carrierSlug);
        }
        
        return $query->get();
    }

    /**
     * Obtenir tous les statuts disponibles
     */
    public static function getAvailableStatuses()
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
}