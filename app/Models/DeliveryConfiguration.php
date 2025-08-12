<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Services\Delivery\SimpleCarrierFactory;

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
     * 🆕 CORRECTION : Vérifier si la configuration est valide - LOGIQUE CORRIGÉE
     */
    public function getIsValidAttribute()
    {
        Log::debug('🔍 [CONFIG] Vérification validité', [
            'config_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'has_username' => !empty($this->username),
            'has_password' => !empty($this->password),
        ]);

        // Pour JAX Delivery : username (numéro compte) + password (token JWT) requis
        if ($this->carrier_slug === 'jax_delivery') {
            $valid = !empty($this->username) && !empty($this->password);
            Log::debug('🔍 [CONFIG] Validation JAX', [
                'valid' => $valid,
                'username_length' => strlen($this->username ?? ''),
                'password_length' => strlen($this->password ?? ''),
            ]);
            return $valid;
        }

        // Pour Mes Colis : username (token) requis  
        if ($this->carrier_slug === 'mes_colis') {
            $valid = !empty($this->username);
            Log::debug('🔍 [CONFIG] Validation Mes Colis', [
                'valid' => $valid,
                'username_length' => strlen($this->username ?? ''),
            ]);
            return $valid;
        }

        // Pour autres transporteurs futurs
        $valid = !empty($this->password) || !empty($this->username);
        Log::debug('🔍 [CONFIG] Validation générique', ['valid' => $valid]);
        return $valid;
    }

    /**
     * 🆕 MÉTHODE CORRIGÉE : Vérifier si valide pour appels API
     */
    public function isValidForApiCalls(): bool
    {
        $basic_valid = $this->is_active && $this->is_valid;
        
        Log::debug('🔍 [CONFIG] Vérification API calls', [
            'config_id' => $this->id,
            'is_active' => $this->is_active,
            'is_valid' => $this->is_valid,
            'basic_valid' => $basic_valid,
            'carrier' => $this->carrier_slug,
        ]);
        
        return $basic_valid;
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
    // 🆕 MÉTHODES CORRIGÉES POUR L'INTÉGRATION API
    // ========================================

    /**
     * 🆕 CORRECTION : Obtenir la configuration pour les services transporteurs
     */
    public function getApiConfig(): array
    {
        Log::info('🔧 [CONFIG] Préparation config API', [
            'config_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'integration_name' => $this->integration_name,
        ]);

        if ($this->carrier_slug === 'jax_delivery') {
            $config = [
                'api_token' => $this->password,      // Token JWT
                'username' => $this->username,       // Numéro de compte
                'account_number' => $this->username, // Alias pour clarté
                'environment' => $this->environment ?? 'test',
            ];
        } elseif ($this->carrier_slug === 'mes_colis') {
            $config = [
                'api_token' => $this->username,      // Token API
                'environment' => $this->environment ?? 'test',
            ];
        } else {
            // Configuration générique pour futurs transporteurs
            $config = [
                'api_token' => $this->password ?? $this->username,
                'username' => $this->username,
                'environment' => $this->environment ?? 'test',
            ];
        }

        Log::debug('✅ [CONFIG] Config API préparée', [
            'carrier' => $this->carrier_slug,
            'has_api_token' => !empty($config['api_token']),
            'token_preview' => !empty($config['api_token']) ? substr($config['api_token'], 0, 10) . '...' : 'vide',
            'environment' => $config['environment'],
        ]);

        return $config;
    }

    /**
     * 🆕 CORRECTION : Test de connexion avec le transporteur
     */
    public function testConnection(): array
    {
        Log::info('🧪 [CONFIG] Test de connexion', [
            'config_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'integration_name' => $this->integration_name,
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
            // Créer le service transporteur avec la configuration
            $apiConfig = $this->getApiConfig();
            $carrierService = SimpleCarrierFactory::create($this->carrier_slug, $apiConfig);
            
            // Tester la connexion
            $result = $carrierService->testConnection();
            
            if ($result['success']) {
                Log::info('✅ [CONFIG] Test connexion réussi', [
                    'config_id' => $this->id,
                    'carrier' => $this->carrier_slug,
                    'message' => $result['message'],
                ]);
                return [
                    'success' => true,
                    'message' => $result['message'] ?? 'Connexion réussie avec ' . $this->carrier_name,
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Erreur test connexion: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Valider les credentials selon le transporteur
     */
    public function validateCredentials(): array
    {
        $errors = [];
        
        if ($this->carrier_slug === 'jax_delivery') {
            if (empty($this->username)) {
                $errors[] = 'Numéro de compte JAX manquant';
            }
            if (empty($this->password)) {
                $errors[] = 'Token JWT JAX manquant';
            } elseif (substr_count($this->password, '.') !== 2) {
                $errors[] = 'Token JWT JAX invalide (format incorrect)';
            }
        } elseif ($this->carrier_slug === 'mes_colis') {
            if (empty($this->username)) {
                $errors[] = 'Token Mes Colis manquant';
            } elseif (strlen($this->username) < 10) {
                $errors[] = 'Token Mes Colis trop court';
            }
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
     * 🆕 CORRECTION : Scope pour les configurations valides
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
            })->orWhere(function($subQ) {
                // Autres transporteurs : au moins un des deux
                $subQ->whereNotIn('carrier_slug', ['jax_delivery', 'mes_colis'])
                     ->where(function($subSubQ) {
                         $subSubQ->where(function($q1) {
                             $q1->whereNotNull('username')->where('username', '!=', '');
                         })->orWhere(function($q2) {
                             $q2->whereNotNull('password')->where('password', '!=', '');
                         });
                     });
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

    /**
     * 🆕 NOUVELLE MÉTHODE : Créer une configuration de test
     */
    public static function createTestConfig($adminId, $carrierSlug, $testCredentials = []): self
    {
        $defaultCredentials = [
            'jax_delivery' => [
                'username' => '2304',
                'password' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
            ],
            'mes_colis' => [
                'username' => 'TEST_TOKEN_MESCOLIS',
                'password' => null,
            ],
        ];

        $credentials = array_merge(
            $defaultCredentials[$carrierSlug] ?? [],
            $testCredentials
        );

        return static::create([
            'admin_id' => $adminId,
            'carrier_slug' => $carrierSlug,
            'integration_name' => "Test {$carrierSlug} " . now()->format('Y-m-d H:i'),
            'username' => $credentials['username'] ?? null,
            'password' => $credentials['password'] ?? null,
            'environment' => 'test',
            'is_active' => true,
        ]);
    }
}