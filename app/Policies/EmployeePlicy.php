<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'admin peut voir n'importe quel employé
     */
    public function viewAny(Admin $admin)
    {
        return $admin->is_active &&
               $admin->role === Admin::ROLE_ADMIN &&
               (!$admin->expiry_date || !$admin->expiry_date->isPast());
    }

    /**
     * Déterminer si l'admin peut voir l'employé
     */
    public function view(Admin $admin, Admin $employee)
    {
        return $admin->is_active &&
               $admin->role === Admin::ROLE_ADMIN &&
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $employee->role === Admin::ROLE_EMPLOYEE &&
               $employee->created_by === $admin->id;
    }

    /**
     * Déterminer si l'admin peut créer des employés
     */
    public function create(Admin $admin)
    {
        return $admin->is_active &&
               $admin->role === Admin::ROLE_ADMIN &&
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $admin->employees()->count() < $admin->max_employees;
    }

    /**
     * Déterminer si l'admin peut mettre à jour l'employé
     */
    public function update(Admin $admin, Admin $employee)
    {
        return $admin->is_active &&
               $admin->role === Admin::ROLE_ADMIN &&
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $employee->role === Admin::ROLE_EMPLOYEE &&
               $employee->created_by === $admin->id;
    }

    /**
     * Déterminer si l'admin peut supprimer l'employé
     */
    public function delete(Admin $admin, Admin $employee)
    {
        return $admin->is_active &&
               $admin->role === Admin::ROLE_ADMIN &&
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $employee->role === Admin::ROLE_EMPLOYEE &&
               $employee->created_by === $admin->id;
    }
}