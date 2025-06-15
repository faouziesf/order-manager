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

    // Relations
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function pickups(): HasMany
    {
        return $this->hasMany(Pickup::class);
    }

    // Accessors & Mutators
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

    // Methods
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
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage(),
            ];
        }
    }

    // Scopes
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
}