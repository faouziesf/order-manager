<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfirmiOrderAssignment extends Model
{
    protected $table = 'confirmi_order_assignments';

    protected $fillable = [
        'order_id',
        'admin_id',
        'assigned_to',
        'assigned_by',
        'status',
        'attempts',
        'notes',
        'assigned_at',
        'first_attempt_at',
        'last_attempt_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'first_attempt_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ========== Relations ==========

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function assignee()
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
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['confirmed', 'delivered', 'cancelled']);
    }

    // ========== Helpers ==========

    public function hasAttempts(): bool
    {
        return $this->attempts > 0;
    }

    public function canBeManaged(): bool
    {
        return !in_array($this->status, ['confirmed', 'delivered', 'cancelled']);
    }
}
