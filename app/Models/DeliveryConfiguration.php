<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'settings' => 'json',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * L'admin propriétaire de cette configuration
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Les pickups utilisant cette configuration
     */
    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    /**
     * Les expéditions liées via les pickups  
     */
    public function shipments()
    {
        return $this->hasManyThrough(Shipment::class, Pickup::class);
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Chiffrer automatiquement le mot de passe
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Déchiffrer automatiquement le mot de passe
     */
    public function getPasswordAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                // Si le déchiffrement échoue, retourner la valeur brute
                return $value;
            }
        }
        return $value;
    }

    /**
     * Chiffrer automatiquement le token
     */
    public function setTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['token'] = Crypt::encryptString($value);
        }
    }

    /**
     * Déchiffrer automatiquement le token
     */
    public function getTokenAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                // Si le déchiffrement échoue, retourner la valeur brute
                return $value;
            }
        }
        return $value;
    }

    /**
     * Obtenir le nom du transporteur
     */
    public function getCarrierNameAttribute()
    {
        $carriers = config('carriers');
        return $carriers[$this->carrier_slug]['name'] ?? ucfirst(str_replace('_', ' ', $this->carrier_slug));
    }

    /**
     * Obtenir la configuration du transporteur depuis le fichier config
     */
    public function getCarrierConfigAttribute()
    {
        $carriers = config('carriers');
        return $carriers[$this->carrier_slug] ?? null;
    }

    /**
     * Vérifier si la configuration est valide
     */
    public function getIsValidAttribute()
    {
        $carrierConfig = $this->carrier_config;
        
        if (!$carrierConfig) {
            return false;
        }

        // Vérifier les champs requis selon le transporteur
        if ($this->carrier_slug === 'jax_delivery') {
            return !empty($this->username) && !empty($this->password);
        } elseif ($this->carrier_slug === 'mes_colis') {
            return !empty($this->username);
        }

        return false;
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
    // SCOPES
    // ========================================

    /**
     * Scope pour les configurations actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les configurations inactives
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
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
     * Scope pour les configurations valides
     */
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->where(function($subQ) {
                // JAX Delivery : username et password requis
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
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Obtenir les credentials pour l'API
     */
    public function getApiCredentials()
    {
        $carrierConfig = $this->carrier_config;
        
        if (!$carrierConfig) {
            return null;
        }

        if ($this->carrier_slug === 'jax_delivery') {
            return [
                'account_number' => $this->username,
                'api_token' => $this->password,
                'auth_type' => 'bearer_token',
                'header_name' => 'Authorization',
                'header_value' => 'Bearer ' . $this->password,
            ];
        } elseif ($this->carrier_slug === 'mes_colis') {
            return [
                'api_token' => $this->username,
                'auth_type' => 'header_token',
                'header_name' => 'x-access-token',
                'header_value' => $this->username,
            ];
        }

        return null;
    }

    /**
     * Obtenir l'URL de base de l'API
     */
    public function getApiBaseUrl()
    {
        $carrierConfig = $this->carrier_config;
        return $carrierConfig['api']['base_url'] ?? null;
    }

    /**
     * Obtenir l'endpoint pour une action
     */
    public function getApiEndpoint($action)
    {
        $carrierConfig = $this->carrier_config;
        $endpoint = $carrierConfig['endpoints'][$action] ?? null;
        
        if ($endpoint) {
            return $this->getApiBaseUrl() . $endpoint;
        }
        
        return null;
    }

    /**
     * Mapper les gouvernorats selon le transporteur
     */
    public function mapGovernorate($regionId)
    {
        $carrierConfig = $this->carrier_config;
        
        if (!$carrierConfig || !isset($carrierConfig['governorate_mapping'])) {
            return null;
        }
        
        return $carrierConfig['governorate_mapping'][$regionId] ?? null;
    }

    /**
     * Mapper un statut transporteur vers statut interne
     */
    public function mapCarrierStatus($carrierStatus)
    {
        $carrierConfig = $this->carrier_config;
        
        if (!$carrierConfig || !isset($carrierConfig['status_mapping'])) {
            return 'unknown';
        }
        
        return $carrierConfig['status_mapping'][$carrierStatus] ?? 'unknown';
    }

    /**
     * Obtenir la configuration par défaut pour une expédition
     */
    public function getShipmentDefaults()
    {
        $carrierConfig = $this->carrier_config;
        return $carrierConfig['defaults'] ?? [];
    }

    /**
     * Valider les données d'une expédition selon les limites du transporteur
     */
    public function validateShipmentData($data)
    {
        $carrierConfig = $this->carrier_config;
        $limits = $carrierConfig['limits'] ?? [];
        $errors = [];

        if (isset($limits['max_weight']) && ($data['weight'] ?? 0) > $limits['max_weight']) {
            $errors[] = "Poids maximum dépassé ({$limits['max_weight']} kg)";
        }

        if (isset($limits['max_cod_amount']) && ($data['cod_amount'] ?? 0) > $limits['max_cod_amount']) {
            $errors[] = "Montant COD maximum dépassé ({$limits['max_cod_amount']} TND)";
        }

        if (isset($limits['max_content_length']) && strlen($data['content_description'] ?? '') > $limits['max_content_length']) {
            $errors[] = "Description trop longue (max {$limits['max_content_length']} caractères)";
        }

        if (isset($limits['max_address_length']) && strlen($data['address'] ?? '') > $limits['max_address_length']) {
            $errors[] = "Adresse trop longue (max {$limits['max_address_length']} caractères)";
        }

        return $errors;
    }

    /**
     * Préparer les données pour l'API du transporteur
     */
    public function prepareApiData($order, $shipment = null)
    {
        $carrierConfig = $this->carrier_config;
        $credentials = $this->getApiCredentials();
        $defaults = $this->getShipmentDefaults();
        
        if (!$carrierConfig || !$credentials) {
            throw new \Exception('Configuration transporteur invalide');
        }

        // Mapper le gouvernorat
        $governorateMapped = $this->mapGovernorate($order->customer_governorate);
        if (!$governorateMapped) {
            throw new \Exception("Gouvernorat non supporté: {$order->customer_governorate}");
        }

        // Données communes
        $data = [
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => $order->customer_address,
            'cod_amount' => $order->total_price,
            'content_description' => $shipment ? $shipment->content_description : ($defaults['content_description'] ?? 'Commande e-commerce'),
            'weight' => $shipment ? $shipment->weight : ($defaults['weight'] ?? 1.0),
            'nb_pieces' => $shipment ? $shipment->nb_pieces : 1,
        ];

        // Données spécifiques par transporteur
        if ($this->carrier_slug === 'jax_delivery') {
            $data['account_number'] = $credentials['account_number'];
            $data['governorate_code'] = $governorateMapped;
            $data['delegation'] = $order->customer_city;
        } elseif ($this->carrier_slug === 'mes_colis') {
            $data['governorate_name'] = $governorateMapped;
            $data['location'] = $order->customer_city;
        }

        // Champs optionnels
        if ($order->customer_phone_2) {
            $data['recipient_phone_2'] = $order->customer_phone_2;
        }

        if ($shipment && $shipment->pickup_date) {
            $data['pickup_date'] = $shipment->pickup_date;
        }

        return $data;
    }

    /**
     * Tester la configuration
     */
    public function testConnection()
    {
        // TODO: Implémenter le test réel dans les phases suivantes
        // Pour l'instant, juste valider la configuration
        return $this->is_valid;
    }

    /**
     * Marquer comme testée avec succès
     */
    public function markAsTestedSuccessfully()
    {
        $this->update([
            'is_active' => true,
            'settings' => array_merge($this->settings ?? [], [
                'last_test_at' => now()->toISOString(),
                'last_test_success' => true,
            ])
        ]);
    }

    /**
     * Marquer comme test échoué
     */
    public function markAsTestFailed($error = null)
    {
        $this->update([
            'is_active' => false,
            'settings' => array_merge($this->settings ?? [], [
                'last_test_at' => now()->toISOString(),
                'last_test_success' => false,
                'last_test_error' => $error,
            ])
        ]);
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

    /**
     * Obtenir les transporteurs configurés pour un admin
     */
    public static function getConfiguredCarriersForAdmin($adminId)
    {
        return static::where('admin_id', $adminId)
            ->valid()
            ->active()
            ->pluck('carrier_slug')
            ->unique()
            ->values();
    }

    /**
     * Compter les configurations par statut pour un admin
     */
    public static function getStatusCountsForAdmin($adminId)
    {
        $configs = static::where('admin_id', $adminId)->get();
        
        return [
            'total' => $configs->count(),
            'active' => $configs->where('is_active', true)->count(),
            'inactive' => $configs->where('is_active', false)->count(),
            'valid' => $configs->filter->is_valid->count(),
            'invalid' => $configs->reject->is_valid->count(),
        ];
    }
}