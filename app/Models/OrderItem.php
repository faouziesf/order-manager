<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:3',
        'total_price' => 'decimal:3',
    ];

    /**
     * Relations
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Méthodes
     */
    public function calculateTotal()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
        return $this->total_price;
    }
}