<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Models\WooCommerceSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyncWooCommerceStatusMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Continuer la requête normalement d'abord
        $response = $next($request);
        
        // Après avoir traité la requête, vérifier si nous devons mettre à jour WooCommerce
        $this->updateWooCommerceOrderStatus($request);
        
        return $response;
    }
    
    private function updateWooCommerceOrderStatus(Request $request)
    {
        // Vérifier si nous sommes sur une page de mise à jour d'une commande
        if ($request->isMethod('post') && $request->route() && 
            (strpos($request->route()->getName(), 'admin.orders.update') === 0 ||
             strpos($request->route()->getName(), 'admin.process.action') === 0)) {
                
            try {
                // Récupérer l'ID de la commande depuis la route
                $orderId = $request->route('order') ?? $request->route('id');
                
                if (!$orderId) return;
                
                // Récupérer la commande
                $order = Order::find($orderId);
                
                // Vérifier si c'est une commande WooCommerce
                if (!$order || $order->external_source !== 'woocommerce' || !$order->external_id) {
                    return;
                }
                
                // Récupérer les paramètres WooCommerce
                $admin = Auth::guard('admin')->user();
                $settings = $admin->woocommerceSettings;
                
                if (!$settings || !$settings->is_active) {
                    return;
                }
                
                // Effectuer la mise à jour
                $this->performStatusUpdate($order, $settings);
                
            } catch (\Exception $e) {
                Log::error('Erreur dans SyncWooCommerceStatusMiddleware: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Effectue la mise à jour du statut
     */
    private function performStatusUpdate($order, $settings)
    {
        try {
            $client = $settings->getClient();
            
            if (!$client) {
                Log::warning('Client WooCommerce non disponible pour la commande #' . $order->id);
                return;
            }
            
            // Mapper le statut de la commande vers WooCommerce
            $wooStatus = $this->mapOrderManagerStatusToWooCommerce($order->status);
            
            if (!$wooStatus) {
                Log::info("Aucun mappage WooCommerce nécessaire pour le statut: {$order->status}");
                return;
            }
            
            // Préparer les données de mise à jour
            $updateData = [
                'status' => $wooStatus
            ];
            
            // Ajouter une note client si nécessaire
            $customerNote = $this->generateCustomerNote($order);
            if ($customerNote) {
                $updateData['customer_note'] = $customerNote;
            }
            
            // Mettre à jour la commande dans WooCommerce
            $response = $client->put("orders/{$order->external_id}", $updateData);
            
            Log::info("Statut WooCommerce mis à jour pour la commande #{$order->external_id}: {$order->status} → {$wooStatus}");
            
            // Enregistrer dans l'historique de la commande
            $order->recordHistory(
                'modification',
                "Statut synchronisé vers WooCommerce: {$wooStatus}",
                [
                    'woocommerce_status' => $wooStatus,
                    'order_manager_status' => $order->status,
                    'sync_timestamp' => now()->toISOString()
                ]
            );
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour du statut WooCommerce pour la commande #{$order->id}: " . $e->getMessage());
        }
    }
    
    /**
     * Mappe les statuts Order Manager vers WooCommerce (seulement si modifiés manuellement)
     */
    private function mapOrderManagerStatusToWooCommerce($status)
    {
        // Si c'est déjà un statut WooCommerce, ne pas le mapper
        if (in_array($status, ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'])) {
            return null; // Pas de mapping nécessaire
        }
        
        // Mapper seulement les statuts Order Manager vers WooCommerce
        $statusMap = [
            'nouvelle' => 'pending',
            'confirmée' => 'processing',
            'annulée' => 'cancelled',
            'datée' => 'on-hold',
            'ancienne' => 'on-hold',
            'en_route' => 'processing',
            'livrée' => 'completed'
        ];
        
        return $statusMap[$status] ?? null;
    }
    
    /**
     * Génère une note client appropriée selon le statut
     */
    private function generateCustomerNote($order)
    {
        $notes = [];
        
        switch ($order->status) {
            case 'confirmée':
            case 'processing':
                $notes[] = "Votre commande a été confirmée et est en cours de préparation.";
                if ($order->confirmed_price && $order->confirmed_price != $order->total_price) {
                    $notes[] = "Prix confirmé: {$order->confirmed_price} DT";
                }
                break;
                
            case 'en_route':
                $notes[] = "Votre commande est en route vers vous !";
                break;
                
            case 'livrée':
            case 'completed':
                $notes[] = "Votre commande a été livrée avec succès. Merci pour votre confiance !";
                break;
                
            case 'annulée':
            case 'cancelled':
                $notes[] = "Votre commande a été annulée.";
                if ($order->notes) {
                    $notes[] = "Raison: " . substr($order->notes, 0, 100);
                }
                break;
                
            case 'datée':
            case 'on-hold':
                if ($order->scheduled_date) {
                    $notes[] = "Votre commande est programmée pour le " . $order->scheduled_date->format('d/m/Y');
                } else {
                    $notes[] = "Votre commande est temporairement en attente.";
                }
                break;
        }
        
        return implode(' ', $notes);
    }
}