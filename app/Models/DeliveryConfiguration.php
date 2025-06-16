<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class DeliveryConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'carrier_slug',
        'integration_name',
        'username',
        'password',
        'environment',
        'token',
        'expires_at',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    // ========================================
    // RELATIONS
    // ========================================
    
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function pickups(): HasMany
    {
        return $this->hasMany(Pickup::class);
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================
    
    public function getDisplayNameAttribute(): string
    {
        return ucfirst($this->carrier_slug) . ' - ' . $this->integration_name;
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        if ($this->password) {
            try {
                return Crypt::decryptString($this->password);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getCarrierDisplayNameAttribute(): string
    {
        return match($this->carrier_slug) {
            'fparcel' => 'Fparcel Tunisia',
            'dhl' => 'DHL Express',
            'aramex' => 'Aramex',
            'tunisia_post' => 'Poste Tunisienne',
            default => ucfirst($this->carrier_slug),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge-secondary';
        }
        
        if ($this->hasValidToken()) {
            return 'badge-success';
        }
        
        return 'badge-warning';
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactif';
        }
        
        if ($this->hasValidToken()) {
            return 'Connecté';
        }
        
        return 'Token expiré';
    }

    // ========================================
    // MÉTHODES
    // ========================================
    
    public function hasValidToken(): bool
    {
        return $this->token && $this->expires_at && $this->expires_at->isFuture();
    }

    public function getCarrierService()
    {
        return app(\App\Services\Shipping\ShippingServiceFactory::class)
            ->make($this->carrier_slug, $this);
    }

    public function refreshToken(): bool
    {
        if ($this->hasValidToken()) {
            return true;
        }

        try {
            $service = $this->getCarrierService();
            $tokenData = $service->getToken();
            
            $this->update([
                'token' => $tokenData['token'],
                'expires_at' => $tokenData['expires_at'],
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    public function supportsPickupAddressSelection(): bool
    {
        $service = $this->getCarrierService();
        return $service->supportsPickupAddressSelection();
    }

    public function testConnection(): array
    {
        try {
            $service = $this->getCarrierService();
            $tokenData = $service->getToken();
            
            $this->update([
                'token' => $tokenData['token'],
                'expires_at' => $tokenData['expires_at'],
            ]);
            
            return [
                'success' => true,
                'message' => 'Connexion réussie avec ' . $this->display_name,
                'token_expires_at' => $tokenData['expires_at'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage(),
            ];
        }
    }

    public function getPickupCount(): int
    {
        return $this->pickups()->count();
    }

    public function getActivePickupCount(): int
    {
        return $this->pickups()->whereIn('status', ['draft', 'validated'])->count();
    }

    public function getShipmentCount(): int
    {
        return Shipment::whereHas('pickup', function($query) {
            $query->where('delivery_configuration_id', $this->id);
        })->count();
    }

    public function getDeliveryRate(): float
    {
        $totalShipments = $this->getShipmentCount();
        
        if ($totalShipments === 0) {
            return 0;
        }
        
        $deliveredShipments = Shipment::whereHas('pickup', function($query) {
            $query->where('delivery_configuration_id', $this->id);
        })->where('status', 'delivered')->count();
        
        return round(($deliveredShipments / $totalShipments) * 100, 2);
    }

    public function updateSettings(array $settings): void
    {
        $currentSettings = $this->settings ?? [];
        $this->update([
            'settings' => array_merge($currentSettings, $settings)
        ]);
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCarrier($query, string $carrier)
    {
        return $query->where('carrier_slug', $carrier);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeWithValidToken($query)
    {
        return $query->whereNotNull('token')
            ->where('expires_at', '>', now());
    }

    public function scopeWithExpiredToken($query)
    {
        return $query->where(function($q) {
            $q->whereNull('token')
              ->orWhere('expires_at', '<=', now());
        });
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================
    
    public static function getAvailableCarriers(): array
    {
        return [
            'fparcel' => [
                'name' => 'Fparcel Tunisia',
                'supports_pickup_address' => true,
                'supports_tracking' => true,
                'supports_mass_labels' => true,
                'features' => ['cod', 'tracking', 'mass_labels', 'pickup_scheduling']
            ],
            // Futurs transporteurs...
        ];
    }

    public static function createForAdmin(Admin $admin, array $data): self
    {
        // Valider que l'admin n'a pas déjà cette configuration
        $existing = self::where('admin_id', $admin->id)
            ->where('carrier_slug', $data['carrier_slug'])
            ->where('integration_name', $data['integration_name'])
            ->first();
            
        if ($existing) {
            throw new \Exception('Une configuration avec ce nom existe déjà pour ce transporteur.');
        }
        
        return self::create(array_merge($data, [
            'admin_id' => $admin->id,
        ]));
    }
}