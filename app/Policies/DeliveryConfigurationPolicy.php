<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\DeliveryConfiguration;
use Illuminate\Auth\Access\Response;

class DeliveryConfigurationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, DeliveryConfiguration $deliveryConfiguration): bool
    {
        return $admin->id === $deliveryConfiguration->admin_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        // Tous les admins authentifiés peuvent créer des configurations.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, DeliveryConfiguration $deliveryConfiguration): bool
    {
        // L'admin ne peut mettre à jour que ses propres configurations.
        return $admin->id === $deliveryConfiguration->admin_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, DeliveryConfiguration $deliveryConfiguration): bool
    {
        // L'admin ne peut supprimer que ses propres configurations.
        return $admin->id === $deliveryConfiguration->admin_id;
    }
}