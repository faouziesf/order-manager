<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_history';

    protected $fillable = [
        'order_id',
        'user_id',
        'user_type',
        'action',
        'status_before',
        'status_after',
        'notes',
        'changes',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    /**
     * Relations
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->user_type === 'Admin' 
            ? $this->belongsTo(Admin::class, 'user_id') 
            : null;
    }

    public function manager()
    {
        return $this->user_type === 'Manager' 
            ? $this->belongsTo(Manager::class, 'user_id') 
            : null;
    }

    public function employee()
    {
        return $this->user_type === 'Employee' 
            ? $this->belongsTo(Employee::class, 'user_id') 
            : null;
    }

    /**
     * Get the name of the user who performed the action
     */
    public function getUserName()
    {
        if ($this->user_type === 'Admin' && $this->admin) {
            return $this->admin->name;
        } elseif ($this->user_type === 'Manager' && $this->manager) {
            return $this->manager->name;
        } elseif ($this->user_type === 'Employee' && $this->employee) {
            return $this->employee->name;
        }

        return 'Système';
    }
}