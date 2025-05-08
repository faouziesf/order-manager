<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any orders.
     */
    public function viewAny(Admin $admin)
    {
        return true;
    }

    /**
     * Determine whether the admin can view the order.
     */
    public function view(Admin $admin, Order $order)
    {
        return $admin->id === $order->admin_id;
    }

    /**
     * Determine whether the admin can create orders.
     */
    public function create(Admin $admin)
    {
        return true;
    }

    /**
     * Determine whether the admin can update the order.
     */
    public function update(Admin $admin, Order $order)
    {
        return $admin->id === $order->admin_id;
    }

    /**
     * Determine whether the admin can delete the order.
     */
    public function delete(Admin $admin, Order $order)
    {
        return $admin->id === $order->admin_id;
    }
}