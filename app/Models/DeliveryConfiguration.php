<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DeliveryConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'carrier_slug',
        'integration_name',
        'username',
        'password',
        'api_key',
        'environment',
        'token',
        'expires_at',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'token',
        'api_key',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    public function shipments()
    {
        return $this->hasManyThrough(Shipment::class, Pickup::class);
    }

    // ========================================
    // 🔧 ACCESSORS SIMPLIFIÉS - PLUS DE CHIFFREMENT COMPLEXE
    // ========================================

    /**
     * 🆕 CORRECTION : Gestion simplifiée - pas de chiffrement automatique
     * Les tokens JWT et API sont stockés en clair pour éviter les problèmes
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value;
    }

    public function getPasswordAttribute($value)
    {
        return $value;
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = $value;
    }

    public function getUsernameAttribute($value)
    {
        return $value;
    }

    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = $value;
    }

    public function getApiKeyAttribute($value)
    {
        return $value;
    }

    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = $value;
    }

    public function getTokenAttribute($value)
    {
        return $value;
    }

    // ========================================
    // ACCESSORS MÉTIER
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
     * 🆕 CORRECTION : Vérifier si la configuration est valide - LOGIQUE SIMPLIFIÉE
     */
    public function getIsValidAttribute()
    {
        // Pour JAX Delivery : username (numéro compte) + password (token JWT) requis
        if ($this->carrier_slug === 'jax_delivery') {
            return !empty($this->username) && !empty($this->password);
        }

        // Pour Mes Colis : seulement username (token) requis  
        if ($this->carrier_slug === 'mes_colis') {
            return !empty($this->username);
        }

        // Pour autres transporteurs futurs
        return !empty($this->password) || !empty($this->username);
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Vérifier si valide pour appels API
     */
    public function isValidForApiCalls(): bool
    {
        return $this->is_active && $this->is_valid;
    }

    /**
     * Obtenir le statut de la configuration
     */
    public function getStatusAttribute()
    {
        if (!$this->is_valid) {
            return 'invalid';
        }
        
        if (!$this->is_active) {
            return 'inactive';
        }
        
        return 'active';
    }

    /**
     * Obtenir la couleur du badge de statut
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'invalid' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'invalid' => 'Configuration invalide',
            default => 'Inconnu',
        };
    }

    // ========================================
    // 🆕 NOUVELLES MÉTHODES POUR L'INTÉGRATION API SIMPLIFIÉES
    // ========================================

    /**
     * 🆕 CORRECTION : Obtenir la configuration déchiffrée pour les services - VERSION SIMPLIFIÉE
     */
    public function getDecryptedConfig(): array
    {
        // Plus de déchiffrement complexe, retourner les valeurs directement
        return [
            'api_key' => $this->password ?: $this->username, // Token principal
            'username' => $this->username, // Numéro de compte pour JAX ou token pour Mes Colis
            'environment' => $this->environment ?? 'test',
        ];
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Obtenir les credentials selon le transporteur
     */
    public function getApiCredentials(): array
    {
        if ($this->carrier_slug === 'jax_delivery') {
            return [
                'account_number' => $this->username, // Numéro de compte (ex: 2304)
                'api_token' => $this->password,      // Token JWT
                'auth_type' => 'bearer_token',
                'header_name' => 'Authorization',
                'header_value' => 'Bearer ' . $this->password,
            ];
        }

        if ($this->carrier_slug === 'mes_colis') {
            return [
                'api_token' => $this->username,      // Token API
                'auth_type' => 'header_token',
                'header_name' => 'x-access-token',
                'header_value' => $this->username,
            ];
        }

        return [];
    }

    /**
     * 🆕 CORRECTION : Test de connexion simplifié
     */
    public function testConnection(): array
    {
        Log::info('🧪 [CONFIG] Test de connexion', [
            'config_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'is_valid' => $this->is_valid,
            'is_active' => $this->is_active
        ]);

        if (!$this->isValidForApiCalls()) {
            Log::warning('🧪 [CONFIG] Configuration invalide pour test', [
                'config_id' => $this->id,
                'carrier' => $this->carrier_slug,
                'is_active' => $this->is_active,
                'is_valid' => $this->is_valid
            ]);
            return [
                'success' => false,
                'message' => 'Configuration invalide ou inactive',
            ];
        }

        try {
            // Utiliser SimpleCarrierFactory pour tester
            $carrierService = \App\Services\Delivery\SimpleCarrierFactory::create(
                $this->carrier_slug, 
                $this->getDecryptedConfig()
            );
            
            $result = $carrierService->testConnection();
            
            if ($result['success']) {
                Log::info('✅ [CONFIG] Test connexion réussi', [
                    'config_id' => $this->id,
                    'carrier' => $this->carrier_slug
                ]);
                return [
                    'success' => true,
                    'message' => 'Connexion réussie avec ' . $this->carrier_name,
                ];
            } else {
                Log::error('❌ [CONFIG] Test connexion échoué', [
                    'config_id' => $this->id,
                    'carrier' => $this->carrier_slug,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                return [
                    'success' => false,
                    'message' => 'Échec connexion: ' . ($result['message'] ?? 'Erreur inconnue'),
                ];
            }
        } catch (\Exception $e) {
            Log::error('❌ [CONFIG] Erreur test connexion', [
                'config_id' => $this->id,
                'carrier' => $this->carrier_slug,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Erreur test connexion: ' . $e->getMessage(),
            ];
        }
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForCarrier($query, $carrierSlug)
    {
        return $query->where('carrier_slug', $carrierSlug);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * 🆕 CORRECTION : Scope pour les configurations valides - LOGIQUE SIMPLIFIÉE
     */
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->where(function($subQ) {
                // JAX Delivery : username ET password requis
                $subQ->where('carrier_slug', 'jax_delivery')
                     ->whereNotNull('username')
                     ->where('username', '!=', '')
                     ->whereNotNull('password')
                     ->where('password', '!=', '');
            })->orWhere(function($subQ) {
                // Mes Colis : seulement username requis
                $subQ->where('carrier_slug', 'mes_colis')
                     ->whereNotNull('username')
                     ->where('username', '!=', '');
            });
        });
    }

    // ========================================
    // MÉTHODES STATIQUES
    // ========================================

    /**
     * Obtenir les configurations actives pour un admin
     */
    public static function getActiveForAdmin($adminId)
    {
        return static::where('admin_id', $adminId)
            ->active()
            ->valid()
            ->get();
    }

    /**
     * Obtenir la configuration par défaut pour un admin et un transporteur
     */
    public static function getDefaultForCarrier($adminId, $carrierSlug)
    {
        return static::where('admin_id', $adminId)
            ->where('carrier_slug', $carrierSlug)
            ->active()
            ->valid()
            ->first();
    }
}