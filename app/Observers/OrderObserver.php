<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\ConfirmiOrderAssignment;
use App\Models\Order;
use App\Traits\DuplicateDetectionTrait;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    use DuplicateDetectionTrait;

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order)
    {
        // Détecter automatiquement les doublons après création
        $this->handleDuplicateDetection($order);

        // Auto-push vers Confirmi si l'admin a le service actif
        $this->pushToConfirmiIfActive($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order)
    {
        // Si le numéro de téléphone a changé, re-vérifier les doublons
        if ($order->wasChanged('customer_phone') || $order->wasChanged('customer_phone_2')) {
            $this->handleDuplicateDetection($order);
        }
    }

    /**
     * Handle the Order "saving" event.
     */
    public function saving(Order $order)
    {
        // Nettoyer les données avant sauvegarde
        if ($order->customer_phone) {
            $order->customer_phone = trim($order->customer_phone);
        }
        
        if ($order->customer_phone_2) {
            $order->customer_phone_2 = trim($order->customer_phone_2);
        }
    }

    /**
     * Gérer la détection des doublons
     */
    private function handleDuplicateDetection(Order $order)
    {
        try {
            // Ne vérifier que si la commande est dans un statut éligible
            if (!in_array($order->status, ['nouvelle', 'datée'])) {
                return;
            }

            // Ne pas re-détecter si déjà marqué comme doublon
            if ($order->is_duplicate) {
                return;
            }

            // Détecter les doublons
            $duplicatesCount = $this->detectDuplicatesOnCreate($order);
            
            if ($duplicatesCount) {
                Log::info("Doublons détectés automatiquement pour la commande #{$order->id}", [
                    'order_id' => $order->id,
                    'customer_phone' => $order->customer_phone,
                    'duplicates_count' => $duplicatesCount,
                    'admin_id' => $order->admin_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la détection automatique des doublons pour la commande #{$order->id}", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Auto-push une commande vers Confirmi si l'admin a le service actif
     */
    private function pushToConfirmiIfActive(Order $order)
    {
        try {
            $admin = Admin::find($order->admin_id);
            if (!$admin || $admin->confirmi_status !== 'active') {
                return;
            }

            // Vérifier qu'il n'y a pas déjà une assignation
            if (ConfirmiOrderAssignment::where('order_id', $order->id)->exists()) {
                return;
            }

            ConfirmiOrderAssignment::create([
                'order_id' => $order->id,
                'admin_id' => $admin->id,
                'status' => 'pending',
            ]);

            Log::info("[Confirmi] Commande #{$order->id} auto-pushée vers Confirmi pour admin #{$admin->id}");
        } catch (\Exception $e) {
            Log::error("[Confirmi] Erreur auto-push commande #{$order->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}