<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ConfirmiUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'confirmi_users';

    const ROLE_COMMERCIAL = 'commercial';
    const ROLE_EMPLOYEE = 'employee';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'created_by',
        'last_login_at',
        'ip_address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ========== Role helpers ==========

    public function isCommercial(): bool
    {
        return $this->role === self::ROLE_COMMERCIAL;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    // ========== Relations ==========

    public function creator()
    {
        return $this->belongsTo(SuperAdmin::class, 'created_by');
    }

    public function assignedOrders()
    {
        return $this->hasMany(ConfirmiOrderAssignment::class, 'assigned_to');
    }

    public function distributedOrders()
    {
        return $this->hasMany(ConfirmiOrderAssignment::class, 'assigned_by');
    }

    // ========== Scopes ==========

    public function scopeCommercials($query)
    {
        return $query->where('role', self::ROLE_COMMERCIAL);
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', self::ROLE_EMPLOYEE);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
