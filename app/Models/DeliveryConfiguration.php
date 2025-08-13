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
    // 🔧 ACCESSORS COMPATIBLES ANCIEN/NOUVEAU FORMAT
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
     * 🔧 CORRECTION COMPATIBLE : Vérifier si la configuration est valide - GÈRE ANCIEN ET NOUVEAU FORMAT
     */
    public function getIsValidAttribute()
    {
        Log::debug('🔍 [CONFIG] Vérification validité format compatible', [
            'config_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'has_username' => !empty($this->username),
            'has_password' => !empty($this->password),
        ]);

        if ($this->carrier_slug === 'jax_delivery') {
            // JAX : toujours username (numéro compte) + password (token) requis
            $valid = !empty($this->username) && !empty($this->password);
            Log::debug('🔍 [CONFIG] Validation JAX', [
                'valid' => $valid,
                'has_account_number' => !empty($this->username),
                'has_jwt_token' => !empty($this->password),
            ]);
            return $valid;
        }

        if ($this->carrier_slug === 'mes_colis') {
            // 🆕 COMPATIBILITÉ : Mes Colis accepte ANCIEN format (username) OU NOUVEAU format (password)
            $hasTokenInUsername = !empty($this->username); // Ancien format
            $hasTokenInPassword = !empty($this->password); // Nouveau format
            $valid = $hasTokenInUsername || $hasTokenInPassword;
            
            Log::debug('🔍 [CONFIG] Validation Mes Colis compatible', [
                'valid' => $valid,
                'has_token_in_username' => $hasTokenInUsername,
                'has_token_in_password' => $hasTokenInPassword,
                'format_detected' => $hasTokenInPassword ? 'nouveau' : ($hasTokenInUsername ? 'ancien' : 'aucun'),
            ]);
            return $valid;
        }

        // Pour autres transporteurs futurs : au moins password requis
        $valid = !empty($this->password);
        Log::debug('🔍 [CONFIG] Validation générique', ['valid' => $valid]);
        return $valid;
    }

    /**
     * 🔧 CORRECTION : Vérifier si valide pour appels API
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
    // 🔧 MÉTHODES COMPATIBLES ANCIEN/NOUVEAU FORMAT
    // ========================================

    /**
     * 🔧 CORRECTION COMPATIBLE : Obtenir la configuration pour les services transporteurs
     */
    public function getApiConfig(): array
    {
        Log::info('🔧 [CONFIG] Préparation config API format compatible', [
            'config_id' => $this->id,
            'carrier' => $this->carrier_slug,
            'integration_name' => $this->integration_name,
        ]);

        if ($this->carrier_slug === 'jax_delivery') {
            // JAX : toujours username + password
            $config = [
                'api_token' => $this->password,      // Token JWT
                'username' => $this->username,       // Numéro de compte
                'account_number' => $this->username, // Alias pour clarté
                'environment' => $this->environment ?? 'test',
            ];
        } elseif ($this->carrier_slug === 'mes_colis') {
            // 🆕 COMPATIBILITÉ : Mes Colis détecte automatiquement le format
            $token = null;
            $format = 'inconnu';
            
            if (!empty($this->password)) {
                // Nouveau format : token dans password
                $token = $this->password;
                $format = 'nouveau';
            } elseif (!empty($this->username)) {
                // Ancien format : token dans username
                $token = $this->username;
                $format = 'ancien';
            }
            
            $config = [
                'api_token' => $token,
                'environment' => $this->environment ?? 'test',
            ];
            
            Log::info('🔄 [CONFIG] Format Mes Colis détecté', [
                'format' => $format,
                'has_token' => !empty($token),
                'token_preview' => $token ? substr($token, 0, 8) . '...' : 'vide',
            ]);
        } else {
            // Configuration générique pour futurs transporteurs
            $config = [
                'api_token' => $this->password ?? $this->username,
                'username' => $this->username,
                'environment' => $this->environment ?? 'test',
            ];
        }

        Log::debug('✅ [CONFIG] Config API préparée format compatible', [
            'carrier' => $this->carrier_slug,
            'has_api_token' => !empty($config['api_token']),
            'token_preview' => !empty($config['api_token']) ? substr($config['api_token'], 0, 10) . '...' : 'vide',
            'has_username' => !empty($config['username'] ?? null),
            'environment' => $config['environment'],
        ]);

        return $config;
    }

    /**
     * 🔧 CORRECTION : Test de connexion avec le transporteur
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
     * 🆕 MÉTHODE : Valider les credentials selon le transporteur - VERSION COMPATIBLE
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
            // 🆕 COMPATIBILITÉ : Mes Colis accepte ancien OU nouveau format
            $hasTokenInUsername = !empty($this->username);
            $hasTokenInPassword = !empty($this->password);
            
            if (!$hasTokenInUsername && !$hasTokenInPassword) {
                $errors[] = 'Token Mes Colis manquant (requis dans username ou password)';
            } else {
                $token = $hasTokenInPassword ? $this->password : $this->username;
                if (strlen($token) < 10) {
                    $errors[] = 'Token Mes Colis trop court (minimum 10 caractères)';
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * 🆕 MÉTHODE : Migrer vers le nouveau format
     */
    public function migrateToNewFormat(): bool
    {
        if ($this->carrier_slug === 'mes_colis') {
            // Si token dans username et password vide, migrer vers nouveau format
            if (!empty($this->username) && empty($this->password)) {
                $oldToken = $this->username;
                
                $this->update([
                    'password' => $oldToken,  // Déplacer token vers password
                    'username' => null,       // Vider username
                ]);
                
                Log::info('🔄 [CONFIG] Migration vers nouveau format effectuée', [
                    'config_id' => $this->id,
                    'carrier' => $this->carrier_slug,
                    'token_moved' => 'username → password',
                ]);
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * 🆕 MÉTHODE : Détecter le format utilisé
     */
    public function getConfigFormat(): string
    {
        if ($this->carrier_slug === 'jax_delivery') {
            return 'standard'; // Toujours username + password
        }
        
        if ($this->carrier_slug === 'mes_colis') {
            if (!empty($this->password)) {
                return 'nouveau'; // Token dans password
            } elseif (!empty($this->username)) {
                return 'ancien';  // Token dans username
            } else {
                return 'invalide'; // Aucun token
            }
        }
        
        return 'inconnu';
    }

    // ========================================
    // SCOPES CORRIGÉS POUR COMPATIBILITÉ
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
     * 🔧 CORRECTION : Scope pour les configurations valides - VERSION COMPATIBLE
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
                // 🆕 COMPATIBILITÉ : Mes Colis accepte token dans username OU password
                $subQ->where('carrier_slug', 'mes_colis')
                     ->where(function($mesColisQ) {
                         $mesColisQ->where(function($oldFormat) {
                             // Ancien format : token dans username
                             $oldFormat->whereNotNull('username')
                                      ->where('username', '!=', '');
                         })->orWhere(function($newFormat) {
                             // Nouveau format : token dans password
                             $newFormat->whereNotNull('password')
                                      ->where('password', '!=', '');
                         });
                     });
            })->orWhere(function($subQ) {
                // Autres transporteurs : au moins password requis
                $subQ->whereNotIn('carrier_slug', ['jax_delivery', 'mes_colis'])
                     ->whereNotNull('password')
                     ->where('password', '!=', '');
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
     * 🆕 MÉTHODE : Migrer toutes les configurations Mes Colis vers le nouveau format
     */
    public static function migrateAllMesColisToNewFormat(): int
    {
        $migratedCount = 0;
        
        $oldFormatConfigs = static::where('carrier_slug', 'mes_colis')
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->whereNull('password')
            ->get();
        
        foreach ($oldFormatConfigs as $config) {
            if ($config->migrateToNewFormat()) {
                $migratedCount++;
            }
        }
        
        Log::info('🔄 [CONFIG] Migration globale terminée', [
            'migrated_count' => $migratedCount,
            'total_found' => $oldFormatConfigs->count(),
        ]);
        
        return $migratedCount;
    }
}