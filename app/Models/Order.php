<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'manager_id',
        'employee_id',
        'customer_name',
        'customer_phone',
        'customer_phone_2',
        'customer_governorate',
        'customer_city',
        'customer_address',
        'total_price',
        'shipping_cost',
        'confirmed_price',
        'status',
        'priority',
        'scheduled_date',
        'attempts_count',
        'daily_attempts_count',
        'last_attempt_at',
        'is_assigned',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:3',
        'shipping_cost' => 'decimal:3',
        'confirmed_price' => 'decimal:3',
        'scheduled_date' => 'date',
        'last_attempt_at' => 'datetime',
        'is_assigned' => 'boolean',
    ];

    /**
     * Relations
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'customer_governorate');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'customer_city');
    }

    /**
     * Scopes
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'nouvelle');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmée');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'annulée');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'datée');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'en_route');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'livrée');
    }

    public function scopeAssigned($query)
    {
        return $query->where('is_assigned', true);
    }

    public function scopeUnassigned($query)
    {
        return $query->where('is_assigned', false);
    }

    /**
     * Méthodes
     */
    public function assignTo($employeeId)
    {
        $this->update([
            'employee_id' => $employeeId,
            'is_assigned' => true
        ]);
    }

    public function unassign()
    {
        $this->update([
            'employee_id' => null,
            'is_assigned' => false
        ]);
    }

    public function recalculateTotal()
    {
        $total = $this->items->sum('total_price');
        $this->total_price = $total;
        $this->save();
        return $total;
    }

    public function recordHistory($action, $notes = null, $changes = null, $statusBefore = null, $statusAfter = null)
    {
        $userId = null;
        $userType = null;

        if (auth()->guard('admin')->check()) {
            $userId = auth()->guard('admin')->id();
            $userType = 'Admin';
        } elseif (auth()->guard('manager')->check()) {
            $userId = auth()->guard('manager')->id();
            $userType = 'Manager';
        } elseif (auth()->guard('employee')->check()) {
            $userId = auth()->guard('employee')->id();
            $userType = 'Employee';
        }

        return $this->history()->create([
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'notes' => $notes,
            'changes' => $changes ? json_encode($changes) : null,
            'status_before' => $statusBefore ?? $this->getOriginal('status'),
            'status_after' => $statusAfter ?? $this->status
        ]);
    }

    public function incrementAttempts()
    {
        $this->increment('attempts_count');
        $this->increment('daily_attempts_count');
        $this->last_attempt_at = now();
        $this->save();
    }

    public function resetDailyAttempts()
    {
        $this->daily_attempts_count = 0;
        $this->save();
    }
}