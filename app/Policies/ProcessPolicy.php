<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcessPolicy
{
    use HandlesAuthorization;

    /**
     * Vérifier si l'admin peut voir l'interface de traitement
     */
    public function viewProcessInterface(Admin $admin)
    {
        return $admin->is_active && (!$admin->expiry_date || !$admin->expiry_date->isPast());
    }

    /**
     * Vérifier si l'admin peut voir l'interface d'examen
     */
    public function viewExamination(Admin $admin)
    {
        return $this->viewProcessInterface($admin);
    }

    /**
     * Vérifier si l'admin peut voir l'interface des commandes suspendues
     */
    public function viewSuspended(Admin $admin)
    {
        return $this->viewProcessInterface($admin);
    }

    /**
     * Vérifier si l'admin peut voir l'interface de retour en stock
     */
    public function viewRestock(Admin $admin)
    {
        return $this->viewProcessInterface($admin);
    }

    /**
     * Vérifier si l'admin peut traiter une commande
     */
    public function processOrder(Admin $admin, Order $order)
    {
        // L'admin peut traiter ses propres commandes ou être super admin
        return $this->viewProcessInterface($admin) && 
               ($order->admin_id === $admin->id || $admin->hasRole('super_admin'));
    }

    /**
     * Vérifier si l'admin peut diviser une commande
     */
    public function splitOrder(Admin $admin, Order $order)
    {
        return $this->processOrder($admin, $order);
    }

    /**
     * Vérifier si l'admin peut suspendre une commande
     */
    public function suspendOrder(Admin $admin, Order $order)
    {
        return $this->processOrder($admin, $order);
    }

    /**
     * Vérifier si l'admin peut réactiver une commande
     */
    public function reactivateOrder(Admin $admin, Order $order)
    {
        return $this->processOrder($admin, $order);
    }

    /**
     * Vérifier si l'admin peut annuler une commande
     */
    public function cancelOrder(Admin $admin, Order $order)
    {
        return $this->processOrder($admin, $order);
    }

    /**
     * Vérifier si l'admin peut effectuer des actions groupées
     */
    public function bulkActions(Admin $admin)
    {
        return $this->viewProcessInterface($admin);
    }

    /**
     * Vérifier si l'admin peut accéder aux statistiques
     */
    public function viewStats(Admin $admin)
    {
        return $this->viewProcessInterface($admin);
    }
}