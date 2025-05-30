<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\LoginHistory;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'admin_id',
        'manager_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    // AJOUT IMPORTANT : Relation avec les commandes
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Relation avec les commandes assignées
    public function assignedOrders()
    {
        return $this->hasMany(Order::class)->where('is_assigned', true);
    }

    public function loginHistory()
    {
        return $this->morphMany(LoginHistory::class, 'user');
    }
}