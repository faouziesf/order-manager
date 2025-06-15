<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'shipment_status_history';
    
    protected $fillable = [
        'shipment_id',
        'carrier_status_code',
        'carrier_status_label',
        'internal_status',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    // Relations
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    // Accessors
    public function getHumanStatusAttribute(): string
    {
        $statuses = [
            'created' => 'Créé',
            'validated' => 'Validé',
            'picked_up_by_carrier' => 'Récupéré par le transporteur',
            'in_transit' => 'En transit',
            'delivered' => 'Livré',
            'cancelled' => 'Annulé',
            'in_return' => 'En retour',
            'anomaly' => 'Anomalie',
        ];

        return $statuses[$this->internal_status] ?? $this->internal_status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->internal_status) {
            'created' => 'badge-secondary',
            'validated' => 'badge-primary',
            'picked_up_by_carrier' => 'badge-info',
            'in_transit' => 'badge-warning',
            'delivered' => 'badge-success',
            'cancelled' => 'badge-dark',
            'in_return' => 'badge-warning',
            'anomaly' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    // Scopes
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('internal_status', $status);
    }

    public function scopeOrderByLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}