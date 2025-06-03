<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_encrypted'
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Relation avec le model Admin
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Accessor pour décrypter automatiquement les valeurs chiffrées
     */
    public function getSettingValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            return Crypt::decryptString($value);
        }
        
        return $this->castValue($value, $this->setting_type);
    }

    /**
     * Mutator pour chiffrer automatiquement les valeurs sensibles
     */
    public function setSettingValueAttribute($value)
    {
        if ($this->is_encrypted) {
            $this->attributes['setting_value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['setting_value'] = $value;
        }
    }

    /**
     * Cast la valeur selon le type spécifié
     */
    private function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }

    /**
     * Scope pour filtrer par admin
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope pour filtrer par clé de paramètre
     */
    public function scopeBySetting($query, $settingKey)
    {
        return $query->where('setting_key', $settingKey);
    }

    /**
     * MÉTHODES COMPATIBLES AVEC L'ANCIEN CODE
     * Récupère une valeur de paramètre pour l'admin connecté
     */
    public static function get($key, $default = null)
    {
        $adminId = auth('admin')->id();
        if (!$adminId) {
            return $default;
        }

        // Utiliser le cache pour éviter des requêtes fréquentes
        return Cache::remember("admin_setting_{$adminId}_{$key}", 60, function () use ($key, $default, $adminId) {
            $setting = static::forAdmin($adminId)->bySetting($key)->first();
            return $setting ? $setting->setting_value : $default;
        });
    }

    /**
     * MÉTHODES COMPATIBLES AVEC L'ANCIEN CODE
     * Définir une valeur de paramètre pour l'admin connecté
     */
    public static function set($key, $value)
    {
        $adminId = auth('admin')->id();
        if (!$adminId) {
            return null;
        }

        $setting = static::updateOrCreate(
            ['admin_id' => $adminId, 'setting_key' => $key],
            ['setting_value' => $value]
        );
        
        // Effacer le cache pour cette clé
        Cache::forget("admin_setting_{$adminId}_{$key}");
        
        return $setting;
    }

    /**
     * Récupère une valeur de paramètre pour un admin spécifique
     */
    public static function getForAdmin($adminId, $key, $default = null)
    {
        return Cache::remember("admin_setting_{$adminId}_{$key}", 60, function () use ($key, $default, $adminId) {
            $setting = static::forAdmin($adminId)->bySetting($key)->first();
            return $setting ? $setting->setting_value : $default;
        });
    }

    /**
     * Définir une valeur de paramètre pour un admin spécifique
     */
    public static function setForAdmin($adminId, $key, $value, $type = 'string', $isEncrypted = false, $description = null)
    {
        $setting = static::updateOrCreate(
            ['admin_id' => $adminId, 'setting_key' => $key],
            [
                'setting_value' => $value,
                'setting_type' => $type,
                'is_encrypted' => $isEncrypted,
                'description' => $description
            ]
        );
        
        // Effacer le cache pour cette clé
        Cache::forget("admin_setting_{$adminId}_{$key}");
        
        return $setting;
    }
}
