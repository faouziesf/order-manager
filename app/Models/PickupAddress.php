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
    // ACCESSORS
    // ========================================
    
    public function getFormattedAddressAttribute(): array
    {
        return [
            'ENL_CONTACT_NOM' => $this->contact_name,
            'ENL_ADRESSE' => $this->address,
            'ENL_CODE_POSTAL' => $this->postal_code,
            'ENL_VILLE' => $this->city,
            'ENL_PORTABLE' => $this->phone, // Fparcel utilise ENL_PORTABLE
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

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->contact_name . ')';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge-secondary';
        }
        
        return $this->is_default ? 'badge-primary' : 'badge-success';
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        
        return $this->is_default ? 'Par défaut' : 'Active';
    }

    // ========================================
    // MÉTHODES
    // ========================================
    
    public function setAsDefault(): void
    {
        // Désactiver toutes les autres adresses par défaut pour cet admin
        static::where('admin_id', $this->admin_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Activer celle-ci comme défaut
        $this->update(['is_default' => true, 'is_active' => true]);
    }

    public function canBeDeleted(): bool
    {
        // Ne peut pas être supprimée si elle a des enlèvements
        return !$this->pickups()->exists();
    }

    public function canBeDeactivated(): bool
    {
        // Ne peut pas être désactivée si c'est la seule adresse active
        if (!$this->is_active) {
            return false;
        }
        
        $otherActiveAddresses = static::where('admin_id', $this->admin_id)
            ->where('id', '!=', $this->id)
            ->where('is_active', true)
            ->count();
            
        return $otherActiveAddresses > 0;
    }

    public function getPickupCount(): int
    {
        return $this->pickups()->count();
    }

    public function getActivePickupCount(): int
    {
        return $this->pickups()->whereIn('status', ['draft', 'validated'])->count();
    }

    public function validate(): array
    {
        $errors = [];
        
        // Validation des champs obligatoires
        if (empty($this->name)) {
            $errors[] = 'Le nom de l\'adresse est obligatoire';
        }
        
        if (empty($this->contact_name)) {
            $errors[] = 'Le nom du contact est obligatoire';
        }
        
        if (empty($this->address)) {
            $errors[] = 'L\'adresse est obligatoire';
        }
        
        if (empty($this->phone)) {
            $errors[] = 'Le numéro de téléphone est obligatoire';
        }
        
        // Validation du téléphone
        if ($this->phone && !preg_match('/^[\d\s\+\-\(\)]{8,}$/', $this->phone)) {
            $errors[] = 'Le format du numéro de téléphone est invalide';
        }
        
        // Validation de l'email si fourni
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Le format de l\'email est invalide';
        }
        
        // Vérifier l'unicité du nom pour cet admin
        $existing = static::where('admin_id', $this->admin_id)
            ->where('name', $this->name)
            ->where('id', '!=', $this->id)
            ->exists();
            
        if ($existing) {
            $errors[] = 'Une adresse avec ce nom existe déjà';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    // ========================================
    // SCOPES
    // ========================================
    
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

    public function scopeWithPickups($query)
    {
        return $query->whereHas('pickups');
    }

    public function scopeWithoutPickups($query)
    {
        return $query->whereDoesntHave('pickups');
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================
    
    public static function createForAdmin(Admin $admin, array $data): self
    {
        // Si c'est la première adresse, la marquer comme par défaut
        $isFirstAddress = !$admin->pickupAddresses()->exists();
        
        $address = self::create(array_merge($data, [
            'admin_id' => $admin->id,
            'is_default' => $isFirstAddress || ($data['is_default'] ?? false),
            'is_active' => true,
        ]));
        
        // Si marquée comme par défaut, désactiver les autres
        if ($address->is_default) {
            $address->setAsDefault();
        }
        
        return $address;
    }

    public static function getDefaultForAdmin(Admin $admin): ?self
    {
        return self::where('admin_id', $admin->id)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}