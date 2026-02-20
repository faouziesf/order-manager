<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfirmiRequest extends Model
{
    protected $table = 'confirmi_requests';

    protected $fillable = [
        'admin_id',
        'status',
        'proposed_rate_confirmed',
        'proposed_rate_delivered',
        'admin_message',
        'response_message',
        'processed_by',
        'processed_by_type',
        'processed_at',
    ];

    protected $casts = [
        'proposed_rate_confirmed' => 'decimal:3',
        'proposed_rate_delivered' => 'decimal:3',
        'processed_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
