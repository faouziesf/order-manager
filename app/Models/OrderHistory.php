<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_history';

    protected $fillable = [
        'order_id',
        'user_id',
        'user_type',
        'action',
        'status_before',
        'status_after',
        'notes',
        'changes',
        // NOUVEAUX CHAMPS POUR LA LIVRAISON
        'carrier_status_code',
        'carrier_status_label',
        'tracking_number',
        'carrier_name',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    // ========================================
    // CONSTANTES POUR LES ACTIONS
    // ========================================
    
    const ACTION_CREATION = 'création';
    const ACTION_MODIFICATION = 'modification';
    const ACTION_CONFIRMATION = 'confirmation';
    const ACTION_ANNULATION = 'annulation';
    const ACTION_DATATION = 'datation';
    const ACTION_TENTATIVE = 'tentative';
    const ACTION_LIVRAISON = 'livraison';
    const ACTION_ASSIGNATION = 'assignation';
    const ACTION_DESASSIGNATION = 'désassignation';
    const ACTION_EN_ROUTE = 'en_route';
    const ACTION_SUSPENSION = 'suspension';
    const ACTION_REACTIVATION = 'réactivation';
    const ACTION_CHANGEMENT_STATUT = 'changement_statut';
    
    // Actions pour doublons
    const ACTION_DUPLICATE_DETECTED = 'duplicate_detected';
    const ACTION_DUPLICATE_REVIEW = 'duplicate_review';
    const ACTION_DUPLICATE_MERGE = 'duplicate_merge';
    const ACTION_DUPLICATE_IGNORE = 'duplicate_ignore';
    const ACTION_DUPLICATE_CANCEL = 'duplicate_cancel';
    
    // NOUVELLES ACTIONS POUR LA LIVRAISON
    const ACTION_SHIPMENT_CREATED = 'shipment_created';
    const ACTION_SHIPMENT_VALIDATED = 'shipment_validated';
    const ACTION_PICKUP_CREATED = 'pickup_created';
    const ACTION_PICKUP_VALIDATED = 'pickup_validated';
    const ACTION_PICKED_UP_BY_CARRIER = 'picked_up_by_carrier';
    const ACTION_IN_TRANSIT = 'in_transit';
    const ACTION_DELIVERY_ATTEMPTED = 'delivery_attempted';
    const ACTION_DELIVERY_FAILED = 'delivery_failed';
    const ACTION_IN_RETURN = 'in_return';
    const ACTION_DELIVERY_ANOMALY = 'delivery_anomaly';
    const ACTION_TRACKING_UPDATED = 'tracking_updated';

    // ========================================
    // RELATIONS
    // ========================================
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->user_type === 'Admin' 
            ? $this->belongsTo(Admin::class, 'user_id') 
            : null;
    }

    public function manager()
    {
        return $this->user_type === 'Manager' 
            ? $this->belongsTo(Manager::class, 'user_id') 
            : null;
    }

    public function employee()
    {
        return $this->user_type === 'Employee' 
            ? $this->belongsTo(Employee::class, 'user_id') 
            : null;
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Obtenir le nom de l'utilisateur qui a effectué l'action
     */
    public function getUserName()
    {
        if ($this->user_type === 'Admin' && $this->admin) {
            return $this->admin->name;
        } elseif ($this->user_type === 'Manager' && $this->manager) {
            return $this->manager->name;
        } elseif ($this->user_type === 'Employee' && $this->employee) {
            return $this->employee->name;
        }

        return 'Système';
    }

    /**
     * Obtenir le libellé français de l'action
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATION => 'Création',
            self::ACTION_MODIFICATION => 'Modification',
            self::ACTION_CONFIRMATION => 'Confirmation',
            self::ACTION_ANNULATION => 'Annulation',
            self::ACTION_DATATION => 'Mise en date',
            self::ACTION_TENTATIVE => 'Tentative d\'appel',
            self::ACTION_LIVRAISON => 'Livraison',
            self::ACTION_ASSIGNATION => 'Assignation',
            self::ACTION_DESASSIGNATION => 'Désassignation',
            self::ACTION_EN_ROUTE => 'Mise en route',
            self::ACTION_SUSPENSION => 'Suspension',
            self::ACTION_REACTIVATION => 'Réactivation',
            self::ACTION_CHANGEMENT_STATUT => 'Changement de statut',
            
            // Doublons
            self::ACTION_DUPLICATE_DETECTED => 'Doublon détecté',
            self::ACTION_DUPLICATE_REVIEW => 'Doublon examiné',
            self::ACTION_DUPLICATE_MERGE => 'Fusion de doublons',
            self::ACTION_DUPLICATE_IGNORE => 'Doublon ignoré',
            self::ACTION_DUPLICATE_CANCEL => 'Doublon annulé',
            
            // Livraison
            self::ACTION_SHIPMENT_CREATED => 'Expédition créée',
            self::ACTION_SHIPMENT_VALIDATED => 'Expédition validée',
            self::ACTION_PICKUP_CREATED => 'Enlèvement créé',
            self::ACTION_PICKUP_VALIDATED => 'Enlèvement validé',
            self::ACTION_PICKED_UP_BY_CARRIER => 'Récupéré par transporteur',
            self::ACTION_IN_TRANSIT => 'En transit',
            self::ACTION_DELIVERY_ATTEMPTED => 'Tentative de livraison',
            self::ACTION_DELIVERY_FAILED => 'Échec de livraison',
            self::ACTION_IN_RETURN => 'En retour',
            self::ACTION_DELIVERY_ANOMALY => 'Anomalie de livraison',
            self::ACTION_TRACKING_UPDATED => 'Suivi mis à jour',
            
            default => ucfirst($this->action),
        };
    }

    /**
     * Vérifier si c'est une action de livraison
     */
    public function getIsDeliveryActionAttribute(): bool
    {
        return in_array($this->action, [
            self::ACTION_SHIPMENT_CREATED,
            self::ACTION_SHIPMENT_VALIDATED,
            self::ACTION_PICKUP_CREATED,
            self::ACTION_PICKUP_VALIDATED,
            self::ACTION_PICKED_UP_BY_CARRIER,
            self::ACTION_IN_TRANSIT,
            self::ACTION_DELIVERY_ATTEMPTED,
            self::ACTION_DELIVERY_FAILED,
            self::ACTION_LIVRAISON,
            self::ACTION_IN_RETURN,
            self::ACTION_DELIVERY_ANOMALY,
            self::ACTION_TRACKING_UPDATED,
        ]);
    }

    /**
     * Obtenir la classe CSS pour l'icône
     */
    public function getIconClassAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATION => 'fa-plus text-success',
            self::ACTION_MODIFICATION => 'fa-edit text-info',
            self::ACTION_CONFIRMATION => 'fa-check text-success',
            self::ACTION_ANNULATION => 'fa-times text-danger',
            self::ACTION_DATATION => 'fa-calendar text-warning',
            self::ACTION_TENTATIVE => 'fa-phone text-primary',
            self::ACTION_LIVRAISON => 'fa-truck text-success',
            self::ACTION_ASSIGNATION => 'fa-user-plus text-info',
            self::ACTION_DESASSIGNATION => 'fa-user-minus text-warning',
            self::ACTION_EN_ROUTE => 'fa-route text-info',
            self::ACTION_SUSPENSION => 'fa-pause text-danger',
            self::ACTION_REACTIVATION => 'fa-play text-success',
            
            // Livraison
            self::ACTION_SHIPMENT_CREATED => 'fa-box text-primary',
            self::ACTION_SHIPMENT_VALIDATED => 'fa-box-check text-success',
            self::ACTION_PICKUP_CREATED => 'fa-warehouse text-info',
            self::ACTION_PICKUP_VALIDATED => 'fa-warehouse text-success',
            self::ACTION_PICKED_UP_BY_CARRIER => 'fa-truck-pickup text-warning',
            self::ACTION_IN_TRANSIT => 'fa-truck-moving text-primary',
            self::ACTION_DELIVERY_ATTEMPTED => 'fa-door-open text-warning',
            self::ACTION_DELIVERY_FAILED => 'fa-exclamation-triangle text-danger',
            self::ACTION_IN_RETURN => 'fa-undo text-warning',
            self::ACTION_DELIVERY_ANOMALY => 'fa-exclamation-circle text-danger',
            self::ACTION_TRACKING_UPDATED => 'fa-sync text-info',
            
            default => 'fa-circle text-secondary',
        };
    }

    /**
     * Obtenir une description enrichie de l'action
     */
    public function getDescriptionAttribute(): string
    {
        $description = $this->action_label;
        
        if ($this->is_delivery_action) {
            if ($this->carrier_name) {
                $description .= " via {$this->carrier_name}";
            }
            
            if ($this->tracking_number) {
                $description .= " (Suivi: {$this->tracking_number})";
            }
            
            if ($this->carrier_status_label) {
                $description .= " - {$this->carrier_status_label}";
            }
        }
        
        if ($this->status_before && $this->status_after && $this->status_before !== $this->status_after) {
            $description .= " : {$this->status_before} → {$this->status_after}";
        }
        
        return $description;
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeDeliveryActions($query)
    {
        return $query->whereIn('action', [
            self::ACTION_SHIPMENT_CREATED,
            self::ACTION_SHIPMENT_VALIDATED,
            self::ACTION_PICKUP_CREATED,
            self::ACTION_PICKUP_VALIDATED,
            self::ACTION_PICKED_UP_BY_CARRIER,
            self::ACTION_IN_TRANSIT,
            self::ACTION_DELIVERY_ATTEMPTED,
            self::ACTION_DELIVERY_FAILED,
            self::ACTION_LIVRAISON,
            self::ACTION_IN_RETURN,
            self::ACTION_DELIVERY_ANOMALY,
            self::ACTION_TRACKING_UPDATED,
        ]);
    }

    public function scopeByCarrier($query, $carrier)
    {
        return $query->where('carrier_name', $carrier);
    }

    public function scopeWithTracking($query)
    {
        return $query->whereNotNull('tracking_number');
    }

    public function scopeSystemActions($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeUserActions($query)
    {
        return $query->whereNotNull('user_id');
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Créer une entrée d'historique pour livraison
     */
    public static function createDeliveryEntry(
        $orderId,
        $action,
        $notes = null,
        $statusBefore = null,
        $statusAfter = null,
        $carrierStatusCode = null,
        $carrierStatusLabel = null,
        $trackingNumber = null,
        $carrierName = null,
        $changes = null
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

        return self::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
            'notes' => $notes,
            'changes' => $changes ? json_encode($changes) : null,
            'carrier_status_code' => $carrierStatusCode,
            'carrier_status_label' => $carrierStatusLabel,
            'tracking_number' => $trackingNumber,
            'carrier_name' => $carrierName,
        ]);
    }

    /**
     * Obtenir les actions disponibles
     */
    public static function getAvailableActions(): array
    {
        return [
            'general' => [
                self::ACTION_CREATION => 'Création',
                self::ACTION_MODIFICATION => 'Modification',
                self::ACTION_CONFIRMATION => 'Confirmation',
                self::ACTION_ANNULATION => 'Annulation',
                self::ACTION_DATATION => 'Mise en date',
                self::ACTION_TENTATIVE => 'Tentative d\'appel',
                self::ACTION_LIVRAISON => 'Livraison',
                self::ACTION_ASSIGNATION => 'Assignation',
                self::ACTION_DESASSIGNATION => 'Désassignation',
                self::ACTION_EN_ROUTE => 'Mise en route',
                self::ACTION_SUSPENSION => 'Suspension',
                self::ACTION_REACTIVATION => 'Réactivation',
                self::ACTION_CHANGEMENT_STATUT => 'Changement de statut',
            ],
            'duplicates' => [
                self::ACTION_DUPLICATE_DETECTED => 'Doublon détecté',
                self::ACTION_DUPLICATE_REVIEW => 'Doublon examiné',
                self::ACTION_DUPLICATE_MERGE => 'Fusion de doublons',
                self::ACTION_DUPLICATE_IGNORE => 'Doublon ignoré',
                self::ACTION_DUPLICATE_CANCEL => 'Doublon annulé',
            ],
            'delivery' => [
                self::ACTION_SHIPMENT_CREATED => 'Expédition créée',
                self::ACTION_SHIPMENT_VALIDATED => 'Expédition validée',
                self::ACTION_PICKUP_CREATED => 'Enlèvement créé',
                self::ACTION_PICKUP_VALIDATED => 'Enlèvement validé',
                self::ACTION_PICKED_UP_BY_CARRIER => 'Récupéré par transporteur',
                self::ACTION_IN_TRANSIT => 'En transit',
                self::ACTION_DELIVERY_ATTEMPTED => 'Tentative de livraison',
                self::ACTION_DELIVERY_FAILED => 'Échec de livraison',
                self::ACTION_IN_RETURN => 'En retour',
                self::ACTION_DELIVERY_ANOMALY => 'Anomalie de livraison',
                self::ACTION_TRACKING_UPDATED => 'Suivi mis à jour',
            ],
        ];
    }
}