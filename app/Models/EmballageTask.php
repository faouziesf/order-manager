<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmballageTask extends Model
{
    protected $fillable = [
        'order_id',
        'admin_id',
        'assignment_id',
        'assigned_to',
        'assigned_by',
        'status',
        'tracking_number',
        'notes',
        'assigned_at',
        'received_at',
        'packed_at',
        'shipped_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'received_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RECEIVED = 'received';
    const STATUS_PACKED = 'packed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';

    // ========== Relations ==========

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function assignment()
    {
        return $this->belongsTo(ConfirmiOrderAssignment::class, 'assignment_id');
    }

    public function agent()
    {
        return $this->belongsTo(ConfirmiUser::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(ConfirmiUser::class, 'assigned_by');
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForAgent($query, $agentId)
    {
        return $query->where('assigned_to', $agentId);
    }

    // ========== Helpers ==========

    public function canBeProcessed(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_RECEIVED, self::STATUS_PACKED]);
    }
}
