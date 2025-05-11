<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Récupère une valeur de paramètre
     */
    public static function get($key, $default = null)
    {
        // Utiliser le cache pour éviter des requêtes fréquentes
        return Cache::remember('admin_setting_' . $key, 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set($key, $value)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        // Effacer le cache pour cette clé
        Cache::forget('admin_setting_' . $key);
        
        return $setting;
    }
}