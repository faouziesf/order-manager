<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Manager;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManagerPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'admin peut voir n'importe quel manager
     */
    public function viewAny(Admin $admin)
    {
        return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
    }

    /**
     * Déterminer si l'admin peut voir le manager
     */
    public function view(Admin $admin, Manager $manager)
    {
        return $admin->is_active && 
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $manager->admin_id === $admin->id;
    }

    /**
     * Déterminer si l'admin peut créer des managers
     */
    public function create(Admin $admin)
    {
        return $admin->is_active && 
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $admin->managers()->count() < $admin->max_managers;
    }

    /**
     * Déterminer si l'admin peut mettre à jour le manager
     */
    public function update(Admin $admin, Manager $manager)
    {
        return $admin->is_active && 
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $manager->admin_id === $admin->id;
    }

    /**
     * Déterminer si l'admin peut supprimer le manager
     */
    public function delete(Admin $admin, Manager $manager)
    {
        return $admin->is_active && 
               (!$admin->expiry_date || !$admin->expiry_date->isPast()) &&
               $manager->admin_id === $admin->id;
    }
}