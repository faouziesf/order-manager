<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Appelé après la mise à jour d'un produit
     */
    public function updated(Product $product)
    {
        // Vérifier si le stock a changé
        if ($product->isDirty('stock')) {
            $this->updateRelatedOrders($product);
        }
    }
    
    /**
     * Met à jour le statut des commandes liées à ce produit
     */
    private function updateRelatedOrders(Product $product)
    {
        try {
            // Récupérer toutes les commandes non confirmées contenant ce produit
            $orders = Order::whereIn('status', ['nouvelle', 'datée'])
                ->whereHas('items', function($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->get();
            
            if ($orders->isEmpty()) {
                return;
            }
            
            $reactivated = 0;
            
            foreach ($orders as $order) {
                // Vérifier et mettre à jour le statut de la commande
                $stockAvailable = $order->checkStockAndUpdateStatus();
                
                if ($stockAvailable && $order->is_suspended === false) {
                    $reactivated++;
                }
            }
            
            if ($reactivated > 0) {
                Log::info("Réactivation de {$reactivated} commandes suite à la mise à jour du stock de {$product->name}");
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour des commandes liées au produit: " . $e->getMessage());
        }
    }
}