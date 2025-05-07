<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'image',
        'price',
        'stock',
        'is_active',
        'description'
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}