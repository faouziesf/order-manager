<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\LoginHistory;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'shop_name',
        'identifier',
        'expiry_date',
        'phone',
        'is_active',
        'max_managers',
        'max_employees',
        'total_orders',
        'total_active_hours',
        'total_revenue',
        'subscription_type',
        'created_by_super_admin',
        'last_login_at',
        'ip_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'created_by_super_admin' => 'boolean',
        'last_login_at' => 'datetime',
        'total_orders' => 'integer',
        'total_active_hours' => 'integer',
        'max_managers' => 'integer',
        'max_employees' => 'integer',
        'total_revenue' => 'decimal:2',
    ];

    // ========================================
    // RELATIONS EXISTANTES
    // ========================================
    
    public function woocommerceSettings()
    {
        return $this->hasOne(WooCommerceSetting::class);
    }

    public function managers()
    {
        return $this->hasMany(Manager::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function loginHistory()
    {
        return $this->morphMany(LoginHistory::class, 'user');
    }

    // ========================================
    // NOUVELLES RELATIONS POUR LA LIVRAISON
    // ========================================
    
    public function deliveryConfigurations()
    {
        return $this->hasMany(DeliveryConfiguration::class);
    }

    public function pickupAddresses()
    {
        return $this->hasMany(PickupAddress::class);
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function blTemplates()
    {
        return $this->hasMany(BLTemplate::class);
    }

    // Relations avec les tables existantes de votre structure
    public function deliveryPaymentMethods()
    {
        return $this->hasMany(DeliveryPaymentMethod::class);
    }

    public function deliveryDropPoints()
    {
        return $this->hasMany(DeliveryDropPoint::class);
    }

    public function deliveryAnomalyReasons()
    {
        return $this->hasMany(DeliveryAnomalyReason::class);
    }

    public function fparcelPaymentMethods()
    {
        return $this->hasMany(FparcelPaymentMethod::class);
    }

    // ========================================
    // ACCESSORS EXISTANTS
    // ========================================

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays() <= 7;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) return null;
        
        return $this->expiry_date->isFuture() 
            ? $this->expiry_date->diffInDays() 
            : -$this->expiry_date->diffInDays();
    }

    // ========================================
    // NOUVEAUX ACCESSORS POUR LA LIVRAISON
    // ========================================

    public function getHasActiveDeliveryConfigAttribute()
    {
        return $this->deliveryConfigurations()->where('is_active', true)->exists();
    }

    public function getActiveDeliveryConfigsCountAttribute()
    {
        return $this->deliveryConfigurations()->where('is_active', true)->count();
    }

    public function getDefaultPickupAddressAttribute()
    {
        return $this->pickupAddresses()->where('is_default', true)->where('is_active', true)->first();
    }

    public function getPendingPickupsCountAttribute()
    {
        return $this->pickups()->where('status', 'draft')->count();
    }

    public function getActiveShipmentsCountAttribute()
    {
        return $this->shipments()->whereIn('status', ['validated', 'picked_up_by_carrier', 'in_transit'])->count();
    }

    // ========================================
    // SCOPES EXISTANTS
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now());
    }

    public function scopeBySubscription($query, $type)
    {
        return $query->where('subscription_type', $type);
    }

    // ========================================
    // NOUVEAUX SCOPES POUR LA LIVRAISON
    // ========================================

    public function scopeWithDeliveryConfig($query)
    {
        return $query->whereHas('deliveryConfigurations', function($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeWithActiveShipments($query)
    {
        return $query->whereHas('shipments', function($q) {
            $q->whereIn('status', ['validated', 'picked_up_by_carrier', 'in_transit']);
        });
    }

    // ========================================
    // MÉTHODES POUR LA LIVRAISON
    // ========================================

    /**
     * Obtenir la configuration de livraison par défaut
     */
    public function getDefaultDeliveryConfiguration(?string $carrier = null)
    {
        $query = $this->deliveryConfigurations()->where('is_active', true);
        
        if ($carrier) {
            $query->where('carrier_slug', $carrier);
        }
        
        return $query->first();
    }

    /**
     * Vérifier si l'admin peut créer des enlèvements
     */
    public function canCreatePickups(): bool
    {
        return $this->has_active_delivery_config && 
               $this->orders()->where('status', 'confirmée')->exists();
    }

    /**
     * Obtenir les statistiques de livraison
     */
    public function getDeliveryStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'total_shipments' => $this->shipments()->where('created_at', '>=', $startDate)->count(),
            'delivered_shipments' => $this->shipments()->where('status', 'delivered')->where('created_at', '>=', $startDate)->count(),
            'in_transit_shipments' => $this->shipments()->whereIn('status', ['validated', 'picked_up_by_carrier', 'in_transit'])->count(),
            'total_pickups' => $this->pickups()->where('created_at', '>=', $startDate)->count(),
            'pending_pickups' => $this->pickups()->where('status', 'draft')->count(),
            'active_carriers' => $this->deliveryConfigurations()->where('is_active', true)->count(),
        ];
    }

    /**
     * Obtenir les commandes prêtes pour l'expédition
     */
    public function getOrdersReadyForShipping()
    {
        return $this->orders()
            ->where('status', 'confirmée')
            ->whereDoesntHave('shipments')
            ->where('is_suspended', false)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Vérifier si l'admin a des tokens expirés
     */
    public function hasExpiredTokens(): bool
    {
        return $this->deliveryConfigurations()
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '<=', now());
            })->exists();
    }
}