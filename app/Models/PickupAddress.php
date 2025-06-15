<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'contact_name',
        'address',
        'postal_code',
        'city',
        'phone',
        'email',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
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

    // Methods
    public function setAsDefault(): void
    {
        // Désactiver toutes les autres adresses par défaut pour cet admin
        static::where('admin_id', $this->admin_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Activer celle-ci comme défaut
        $this->update(['is_default' => true]);
    }

    public function getFormattedAddressAttribute(): array
    {
        return [
            'ENL_CONTACT_NOM' => $this->contact_name,
            'ENL_ADRESSE' => $this->address,
            'ENL_CODE_POSTAL' => $this->postal_code,
            'ENL_VILLE' => $this->city,
            'ENL_TELEPHONE' => $this->phone,
        ];
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code,
            $this->city,
        ]);
        return implode(', ', $parts);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }
}