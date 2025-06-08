<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class SuperAdmin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'avatar',
        'phone',
        'permissions',
        'timezone',
        'language'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'permissions' => 'array'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Avatar par défaut basé sur les initiales
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=4e73df&background=f8f9fc';
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    public function getLastLoginFormattedAttribute()
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Jamais connecté';
    }

    // Methods
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return true; // Super admin a tous les droits par défaut
        }
        
        return in_array($permission, $this->permissions);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    // Relationships (si nécessaire dans le futur)
    public function logs()
    {
        return $this->hasMany(SystemLog::class, 'user_id');
    }
}