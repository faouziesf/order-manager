<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\PickupAddress;
use Illuminate\Auth\Access\HandlesAuthorization;

class PickupAddressPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'admin peut voir la liste des adresses
     */
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    /**
     * Déterminer si l'admin peut voir une adresse spécifique
     */
    public function view(Admin $admin, PickupAddress $pickupAddress): bool
    {
        return $admin->id === $pickupAddress->admin_id;
    }

    /**
     * Déterminer si l'admin peut créer une nouvelle adresse
     */
    public function create(Admin $admin): bool
    {
        // Limiter le nombre d'adresses par admin (par exemple 10 max)
        $maxAddresses = 10;
        $currentCount = $admin->pickupAddresses()->count();
        
        return $currentCount < $maxAddresses;
    }

    /**
     * Déterminer si l'admin peut modifier une adresse
     */
    public function update(Admin $admin, PickupAddress $pickupAddress): bool
    {
        if ($admin->id !== $pickupAddress->admin_id) {
            return false;
        }

        // Ne pas permettre la modification si des enlèvements sont en cours avec cette adresse
        $hasActivePickups = $pickupAddress->pickups()
            ->whereIn('status', ['validated', 'picked_up'])
            ->exists();

        return !$hasActivePickups;
    }

    /**
     * Déterminer si l'admin peut supprimer une adresse
     */
    public function delete(Admin $admin, PickupAddress $pickupAddress): bool
    {
        if ($admin->id !== $pickupAddress->admin_id) {
            return false;
        }

        // Ne pas permettre la suppression si des enlèvements existent avec cette adresse
        $hasPickups = $pickupAddress->pickups()->exists();
        
        return !$hasPickups;
    }

    /**
     * Déterminer si l'admin peut définir une adresse comme par défaut
     */
    public function setAsDefault(Admin $admin, PickupAddress $pickupAddress): bool
    {
        return $admin->id === $pickupAddress->admin_id && $pickupAddress->is_active;
    }

    /**
     * Déterminer si l'admin peut activer/désactiver une adresse
     */
    public function toggleStatus(Admin $admin, PickupAddress $pickupAddress): bool
    {
        if ($admin->id !== $pickupAddress->admin_id) {
            return false;
        }

        // Si on désactive, s'assurer que ce n'est pas la seule adresse active
        if ($pickupAddress->is_active) {
            $otherActiveAddresses = $admin->pickupAddresses()
                ->where('id', '!=', $pickupAddress->id)
                ->where('is_active', true)
                ->count();
                
            return $otherActiveAddresses > 0;
        }

        return true;
    }
}