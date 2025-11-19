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
        // Admin/Manager : peut voir toutes ses commandes
        if ($admin->role === Admin::ROLE_ADMIN) {
            return $admin->id === $order->admin_id;
        }

        // Manager : peut voir les commandes de son admin parent
        if ($admin->role === Admin::ROLE_MANAGER && $admin->created_by) {
            return $admin->created_by === $order->admin_id;
        }

        // Employé : peut voir toutes les commandes de son admin
        if ($admin->role === Admin::ROLE_EMPLOYEE && $admin->created_by) {
            return $admin->created_by === $order->admin_id;
        }

        return false;
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
        // Admin : peut éditer toutes ses commandes
        if ($admin->role === Admin::ROLE_ADMIN) {
            return $admin->id === $order->admin_id;
        }

        // Manager : peut éditer les commandes de son admin parent
        if ($admin->role === Admin::ROLE_MANAGER && $admin->created_by) {
            return $admin->created_by === $order->admin_id;
        }

        // Employé : peut éditer UNIQUEMENT les commandes qui lui sont assignées
        if ($admin->role === Admin::ROLE_EMPLOYEE) {
            return $order->employee_id === $admin->id && $order->is_assigned;
        }

        return false;
    }

    /**
     * Determine whether the admin can delete the order.
     */
    public function delete(Admin $admin, Order $order)
    {
        // Seuls les admins et managers peuvent supprimer
        if ($admin->role === Admin::ROLE_ADMIN) {
            return $admin->id === $order->admin_id;
        }

        if ($admin->role === Admin::ROLE_MANAGER && $admin->created_by) {
            return $admin->created_by === $order->admin_id;
        }

        // Les employés ne peuvent PAS supprimer
        return false;
    }
}