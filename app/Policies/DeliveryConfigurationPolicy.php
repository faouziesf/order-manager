<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\DeliveryConfiguration;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryConfigurationPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'admin peut voir la liste des configurations
     */
    public function viewAny(Admin $admin): bool
    {
        return true; // Tous les admins peuvent voir leurs propres configurations
    }

    /**
     * Déterminer si l'admin peut voir une configuration spécifique
     */
    public function view(Admin $admin, DeliveryConfiguration $configuration): bool
    {
        return $admin->id === $configuration->admin_id;
    }

    /**
     * Déterminer si l'admin peut créer une nouvelle configuration
     */
    public function create(Admin $admin): bool
    {
        // Limiter le nombre de configurations par admin (par exemple 5 max)
        $maxConfigurations = 5;
        $currentCount = $admin->deliveryConfigurations()->count();
        
        return $currentCount < $maxConfigurations;
    }

    /**
     * Déterminer si l'admin peut modifier une configuration
     */
    public function update(Admin $admin, DeliveryConfiguration $configuration): bool
    {
        if ($admin->id !== $configuration->admin_id) {
            return false;
        }

        // Ne pas permettre la modification si des enlèvements sont en cours
        $hasActivePickups = $configuration->pickups()
            ->whereIn('status', ['validated', 'picked_up'])
            ->exists();

        return !$hasActivePickups;
    }

    /**
     * Déterminer si l'admin peut supprimer une configuration
     */
    public function delete(Admin $admin, DeliveryConfiguration $configuration): bool
    {
        if ($admin->id !== $configuration->admin_id) {
            return false;
        }

        // Ne pas permettre la suppression si des enlèvements existent
        $hasPickups = $configuration->pickups()->exists();
        
        return !$hasPickups;
    }

    /**
     * Déterminer si l'admin peut tester la connexion
     */
    public function testConnection(Admin $admin, DeliveryConfiguration $configuration): bool
    {
        return $admin->id === $configuration->admin_id;
    }

    /**
     * Déterminer si l'admin peut synchroniser les données
     */
    public function syncData(Admin $admin, DeliveryConfiguration $configuration): bool
    {
        return $admin->id === $configuration->admin_id && $configuration->is_active;
    }

    /**
     * Déterminer si l'admin peut activer/désactiver une configuration
     */
    public function toggleStatus(Admin $admin, DeliveryConfiguration $configuration): bool
    {
        if ($admin->id !== $configuration->admin_id) {
            return false;
        }

        // Si on désactive, s'assurer qu'il n'y a pas d'enlèvements actifs
        if ($configuration->is_active) {
            $hasActivePickups = $configuration->pickups()
                ->whereIn('status', ['draft', 'validated'])
                ->exists();
                
            return !$hasActivePickups;
        }

        return true;
    }
}