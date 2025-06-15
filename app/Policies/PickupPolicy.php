<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Pickup;
use Illuminate\Auth\Access\HandlesAuthorization;

class PickupPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'admin peut voir la liste des enlèvements
     */
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    /**
     * Déterminer si l'admin peut voir un enlèvement spécifique
     */
    public function view(Admin $admin, Pickup $pickup): bool
    {
        return $admin->id === $pickup->admin_id;
    }

    /**
     * Déterminer si l'admin peut créer un nouvel enlèvement
     */
    public function create(Admin $admin): bool
    {
        // Vérifier qu'il a au moins une configuration active
        $hasActiveConfig = $admin->deliveryConfigurations()
            ->where('is_active', true)
            ->exists();

        if (!$hasActiveConfig) {
            return false;
        }

        // Limiter le nombre d'enlèvements en brouillon (par exemple 5 max)
        $maxDraftPickups = 5;
        $draftCount = $admin->pickups()
            ->where('status', Pickup::STATUS_DRAFT)
            ->count();
        
        return $draftCount < $maxDraftPickups;
    }

    /**
     * Déterminer si l'admin peut modifier un enlèvement
     */
    public function update(Admin $admin, Pickup $pickup): bool
    {
        if ($admin->id !== $pickup->admin_id) {
            return false;
        }

        // Seuls les enlèvements en brouillon peuvent être modifiés
        return $pickup->status === Pickup::STATUS_DRAFT;
    }

    /**
     * Déterminer si l'admin peut supprimer un enlèvement
     */
    public function delete(Admin $admin, Pickup $pickup): bool
    {
        if ($admin->id !== $pickup->admin_id) {
            return false;
        }

        // Seuls les enlèvements en brouillon peuvent être supprimés
        return $pickup->status === Pickup::STATUS_DRAFT;
    }

    /**
     * Déterminer si l'admin peut valider un enlèvement
     */
    public function validate(Admin $admin, Pickup $pickup): bool
    {
        if ($admin->id !== $pickup->admin_id) {
            return false;
        }

        // L'enlèvement doit être en brouillon et avoir des expéditions
        return $pickup->status === Pickup::STATUS_DRAFT && 
               $pickup->shipments()->count() > 0;
    }

    /**
     * Déterminer si l'admin peut générer les étiquettes
     */
    public function generateLabels(Admin $admin, Pickup $pickup): bool
    {
        if ($admin->id !== $pickup->admin_id) {
            return false;
        }

        // L'enlèvement doit être validé et avoir des codes de suivi
        return $pickup->status === Pickup::STATUS_VALIDATED &&
               $pickup->shipments()->whereNotNull('pos_barcode')->exists();
    }

    /**
     * Déterminer si l'admin peut générer le manifeste
     */
    public function generateManifest(Admin $admin, Pickup $pickup): bool
    {
        return $this->generateLabels($admin, $pickup);
    }

    /**
     * Déterminer si l'admin peut rafraîchir le statut
     */
    public function refreshStatus(Admin $admin, Pickup $pickup): bool
    {
        if ($admin->id !== $pickup->admin_id) {
            return false;
        }

        // Peut rafraîchir si validé ou récupéré
        return in_array($pickup->status, [
            Pickup::STATUS_VALIDATED,
            Pickup::STATUS_PICKED_UP
        ]);
    }

    /**
     * Déterminer si l'admin peut ajouter des commandes
     */
    public function addOrders(Admin $admin, Pickup $pickup): bool
    {
        return $this->update($admin, $pickup);
    }

    /**
     * Déterminer si l'admin peut supprimer une expédition
     */
    public function removeShipment(Admin $admin, Pickup $pickup): bool
    {
        return $this->update($admin, $pickup);
    }

    /**
     * Déterminer si l'admin peut accéder à la préparation d'enlèvement
     */
    public function preparation(Admin $admin): bool
    {
        // Vérifier qu'il a des commandes confirmées disponibles
        $hasConfirmedOrders = $admin->orders()
            ->where('status', 'confirmée')
            ->whereDoesntHave('shipments', function($query) {
                $query->whereNotNull('pickup_id');
            })
            ->exists();

        return $hasConfirmedOrders;
    }

    /**
     * Déterminer si l'admin peut voir les statistiques détaillées
     */
    public function viewStats(Admin $admin): bool
    {
        // Peut voir les stats s'il a au moins un enlèvement
        return $admin->pickups()->exists();
    }
}