<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'is_successful',
        'country',
        'city',
        'device_type',
        'browser',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_successful' => 'boolean',
    ];

    /**
     * Relation polymorphique pour l'utilisateur
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Scope pour filtrer par type d'utilisateur
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope pour les connexions réussies
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope pour les tentatives échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * Accessor pour le nom du navigateur formaté
     */
    public function getBrowserNameAttribute()
    {
        // Si le champ browser est déjà rempli, l'utiliser
        if ($this->attributes['browser']) {
            return $this->attributes['browser'];
        }

        // Sinon, détecter à partir du user_agent
        if (!$this->user_agent) {
            return 'Inconnu';
        }

        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'safari') && !str_contains($userAgent, 'chrome')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'edge')) {
            return 'Edge';
        } elseif (str_contains($userAgent, 'opera')) {
            return 'Opera';
        }
        
        return 'Autre';
    }

    /**
     * Accessor pour le type d'appareil
     */
    public function getDeviceTypeAttribute($value)
    {
        // Si le champ device_type est déjà rempli, l'utiliser
        if ($value) {
            return $value;
        }

        // Sinon, détecter à partir du user_agent
        if (!$this->user_agent) {
            return 'Inconnu';
        }

        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'Tablette';
        }
        
        return 'Ordinateur';
    }

    /**
     * Accessor pour la localisation formatée
     */
    public function getLocationAttribute()
    {
        if ($this->city && $this->country) {
            return $this->city . ', ' . $this->country;
        } elseif ($this->city) {
            return $this->city;
        } elseif ($this->country) {
            return $this->country;
        }
        
        return 'Non déterminé';
    }

    /**
     * Accessor pour savoir si c'est une IP locale
     */
    public function getIsLocalIpAttribute()
    {
        return $this->ip_address === '127.0.0.1' || 
               $this->ip_address === '::1' || 
               str_starts_with($this->ip_address, '192.168.') ||
               str_starts_with($this->ip_address, '10.') ||
               str_starts_with($this->ip_address, '172.');
    }

    /**
     * Accessor pour la durée de session formatée
     */
    public function getSessionDurationAttribute()
    {
        if (!$this->logout_at || !$this->is_successful) {
            return null;
        }

        $minutes = $this->login_at->diffInMinutes($this->logout_at);
        
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        
        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . 'h ' . $remainingMinutes . 'min';
    }
}