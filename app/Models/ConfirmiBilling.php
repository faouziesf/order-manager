<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfirmiBilling extends Model
{
    protected $table = 'confirmi_billing';

    protected $fillable = [
        'admin_id',
        'order_id',
        'billing_type',
        'amount',
        'is_paid',
        'billed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'is_paid' => 'boolean',
        'billed_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
