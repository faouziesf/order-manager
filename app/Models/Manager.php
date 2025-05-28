<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\LoginHistory;

class Manager extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'admin_id',
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

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function loginHistory()
    {
        return $this->morphMany(LoginHistory::class, 'user');
    }
}