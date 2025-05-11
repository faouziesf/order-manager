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
            strpos($request->route()->getName(), 'admin.orders.update') === 0) {
                
            try {
                // Récupérer l'ID de la commande depuis la route
                $orderId = $request->route('order');
                
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
                
                // Se connecter à l'API WooCommerce
                $client = $settings->getClient();
                
                if (!$client) {
                    return;
                }
                
                // Mapper le statut de la commande dans Order Manager vers WooCommerce
                $wooStatus = $this->mapOrderStatusToWooCommerce($order->status);
                
                if (!$wooStatus) {
                    return;
                }
                
                // Mettre à jour la commande dans WooCommerce
                $client->put('orders/' . $order->external_id, [
                    'status' => $wooStatus,
                    'customer_note' => "Statut mis à jour depuis Order Manager: {$order->status}"
                ]);
                
                Log::info("WooCommerce order #{$order->external_id} status updated to {$wooStatus}");
            } catch (\Exception $e) {
                Log::error('Error updating WooCommerce order status: ' . $e->getMessage());
            }
        }
    }
    
    private function mapOrderStatusToWooCommerce($status)
    {
        // Mapper les statuts Order Manager vers WooCommerce
        $statusMap = [
            'nouvelle' => 'pending',
            'confirmée' => 'processing',
            'annulée' => 'cancelled',
            'datée' => 'on-hold',
            'en_route' => 'processing',
            'livrée' => 'completed'
        ];
        
        return $statusMap[$status] ?? null;
    }
}