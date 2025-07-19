<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'reference',
        'name',
        'image',
        'price',
        'stock',
        'is_active',
        'needs_review',
        'description'
    ];
    
    protected $casts = [
        'reference' => 'integer',
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

    /**
     * Vérifier si le produit est utilisé dans des commandes
     * NOUVELLE MÉTHODE POUR LA PROTECTION
     */
    public function isUsedInOrders()
    {
        return $this->orderItems()->exists();
    }

    /**
     * Obtenir le nombre de commandes utilisant ce produit
     */
    public function getOrdersCount()
    {
        return $this->orders()->distinct()->count();
    }

    /**
     * Obtenir la quantité totale vendue
     */
    public function getTotalSoldQuantity()
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Obtenir le chiffre d'affaires généré par ce produit
     */
    public function getTotalRevenue()
    {
        return $this->orderItems()->sum('total_price');
    }

    /**
     * Scope pour les produits actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les produits en stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope pour les produits en rupture de stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope pour les produits avec stock faible
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock', '>', 0)->where('stock', '<=', $threshold);
    }

    /**
     * Accessor pour afficher la référence formatée
     */
    public function getFormattedReferenceAttribute()
    {
        return $this->reference ? 'REF-' . str_pad($this->reference, 4, '0', STR_PAD_LEFT) : null;
    }

    /**
     * Accessor pour le statut du stock
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock <= 10) {
            return 'low_stock';
        } elseif ($this->stock <= 50) {
            return 'normal_stock';
        } else {
            return 'high_stock';
        }
    }

    /**
     * Accessor pour le badge de statut de stock
     */
    public function getStockBadgeAttribute()
    {
        switch ($this->stock_status) {
            case 'out_of_stock':
                return [
                    'class' => 'badge-danger',
                    'icon' => 'fas fa-times',
                    'text' => 'Rupture'
                ];
            case 'low_stock':
                return [
                    'class' => 'badge-warning',
                    'icon' => 'fas fa-exclamation-triangle',
                    'text' => $this->stock . ' unités'
                ];
            case 'normal_stock':
                return [
                    'class' => 'badge-info',
                    'icon' => 'fas fa-info',
                    'text' => $this->stock . ' unités'
                ];
            case 'high_stock':
                return [
                    'class' => 'badge-success',
                    'icon' => 'fas fa-check',
                    'text' => $this->stock . ' unités'
                ];
            default:
                return [
                    'class' => 'badge-secondary',
                    'icon' => 'fas fa-question',
                    'text' => 'Inconnu'
                ];
        }
    }
}