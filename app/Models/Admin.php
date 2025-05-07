<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'shop_name',
        'identifier',
        'expiry_date',
        'phone',
        'is_active',
        'max_managers',
        'max_employees',
        'total_orders',
        'total_active_hours',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function managers()
    {
        return $this->hasMany(Manager::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}