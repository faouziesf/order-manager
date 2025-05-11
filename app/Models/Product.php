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
        'needs_review',
        'description'
    ];
    
    protected $casts = [
        'price' => 'decimal:3',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'needs_review' => 'boolean',
    ];

    public function markAsReviewed()
    {
        $this->needs_review = false;
        $this->save();
        return $this;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Nouvelles relations pour les commandes
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }

    // Vérifier si le produit est en stock
    public function isInStock()
    {
        return $this->stock > 0;
    }

    // Décrémenter le stock
    public function decrementStock($quantity = 1)
    {
        if ($this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
            return true;
        }
        return false;
    }

    // Incrémenter le stock
    public function incrementStock($quantity = 1)
    {
        $this->increment('stock', $quantity);
        return true;
    }
}