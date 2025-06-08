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

    // Relation avec les paramètres WooCommerce
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
    
    // Nouvelle relation pour les commandes
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function loginHistory()
    {
        return $this->morphMany(LoginHistory::class, 'user');
    }

    // Accessors
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

    // Scopes
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
}