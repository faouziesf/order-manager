<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\AdminSetting;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait ProcessTrait
{
    /**
     * Obtenir un paramètre de configuration
     */
    protected function getSetting($key, $default = null)
    {
        $admin = Auth::guard('admin')->user();
        return AdminSetting::getForAdmin($admin->id, $key, $default);
    }

    /**
     * Réinitialiser les compteurs quotidiens si nécessaire
     */
    protected function resetDailyCountersIfNeeded($admin)
    {
        $lastReset = AdminSetting::getForAdmin($admin->id, 'last_daily_reset');
        $today = now()->format('Y-m-d');
        
        if (!$lastReset || $lastReset !== $today) {
            Order::where('admin_id', $admin->id)->update(['daily_attempts_count' => 0]);
            AdminSetting::setForAdmin($admin->id, 'last_daily_reset', $today);
            Log::info("Compteurs quotidiens réinitialisés pour admin {$admin->id}");
        }
    }

    /**
     * Vérifier si une commande a des problèmes de stock
     */
    protected function orderHasStockIssues($order)
    {
        if (!$order || !$order->items || $order->items->isEmpty()) {
            return false;
        }
        
        foreach ($order->items as $item) {
            // Si le produit n'existe plus, on considère qu'il n'y a pas de problème de stock
            if (!$item->product) {
                continue;
            }
            
            // Vérifier si le stock est insuffisant
            if ($item->product->stock < $item->quantity) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Enregistrer une tentative d'appel
     */
    protected function recordCallAttempt($order, $notes)
    {
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        $order->last_attempt_at = now();
        $order->save();
        
        // Enregistrer dans l'historique
        $order->recordHistory('tentative', $notes);
        
        // Vérifier la transition automatique vers file ancienne
        $this->checkForAutoTransition($order);
    }

    /**
     * Vérifier et effectuer la transition automatique vers file ancienne
     */
    protected function checkForAutoTransition($order)
    {
        if ($order->status === 'nouvelle') {
            $standardMaxAttempts = (int)$this->getSetting('standard_max_total_attempts', 9);
            
            if ($order->attempts_count >= $standardMaxAttempts) {
                $previousStatus = $order->status;
                $order->status = 'ancienne';
                $order->save();
                
                $order->recordHistory(
                    'modification',
                    "Commande automatiquement passée en file ancienne après {$order->attempts_count} tentatives",
                    [
                        'previous_status' => $previousStatus,
                        'new_status' => 'ancienne',
                        'attempts_reached' => $order->attempts_count,
                        'auto_transition' => true
                    ],
                    $previousStatus,
                    'ancienne'
                );
                
                Log::info("Commande #{$order->id} automatiquement passée en file ancienne");
                return true;
            }
        }
        
        return false;
    }

    /**
     * Confirmer une commande avec mise à jour du stock
     */
    protected function confirmOrder($order, $request, $notes)
    {
        $order->status = 'confirmée';
        $order->total_price = $request->confirmed_price; // Prix total confirmé
        
        // Mettre à jour les informations client
        $order->customer_name = $request->customer_name;
        $order->customer_phone_2 = $request->customer_phone_2;
        $order->customer_governorate = $request->customer_governorate;
        $order->customer_city = $request->customer_city;
        $order->customer_address = $request->customer_address;
        
        $order->save();
        
        $order->recordHistory('confirmation', $notes);
    }

    /**
     * Validation pour la confirmation
     */
    protected function validateConfirmation($request)
    {
        $request->validate([
            'confirmed_price' => 'required|numeric|min:0',
            'customer_name' => 'required|string|min:2|max:255',
            'customer_governorate' => 'required|string',
            'customer_city' => 'required|string', 
            'customer_address' => 'required|string|min:5',
            'cart_items' => 'required|array|min:1',
        ]);
    }

    /**
     * Mettre à jour les items de la commande et décrémenter le stock
     */
    protected function updateOrderItems($order, $cartItems)
    {
        if (!$cartItems || !is_array($cartItems)) {
            return;
        }
        
        // Supprimer les anciens items (sans remettre le stock)
        $order->items()->delete();
        
        $totalPrice = 0;
        
        foreach ($cartItems as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                continue;
            }
            
            $quantity = (int)$item['quantity'];
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $totalItemPrice = $quantity * $unitPrice;
            $totalPrice += $totalItemPrice;
            
            // Créer le nouvel item
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalItemPrice,
            ]);
            
            // DÉCRÉMENTER LE STOCK DU PRODUIT
            $product = Product::find($item['product_id']);
            if ($product && $product->admin_id === $order->admin_id) {
                $product->stock = max(0, $product->stock - $quantity);
                $product->save();
                
                Log::info("Stock décrémenté pour produit {$product->id}: -{$quantity} (nouveau stock: {$product->stock})");
            }
        }
        
        // Mettre à jour le prix total de la commande (SANS frais de livraison)
        $order->total_price = $totalPrice;
        $order->save();
    }

    /**
     * Mettre à jour les informations de la commande
     */
    protected function updateOrderInfo($order, $request)
    {
        $fieldsToUpdate = [
            'customer_name',
            'customer_phone_2', 
            'customer_governorate',
            'customer_city',
            'customer_address'
        ];
        
        $changes = [];
        foreach ($fieldsToUpdate as $field) {
            if ($request->has($field)) {
                $oldValue = $order->$field;
                $newValue = $request->$field;
                
                if ($oldValue !== $newValue) {
                    $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                    $order->$field = $newValue;
                }
            }
        }
        
        if (!empty($changes)) {
            $order->save();
        }
    }

    /**
     * Obtenir les informations de doublons pour une commande
     */
    protected function getDuplicateInfo($order)
    {
        if (!$order) {
            return [
                'has_duplicates' => false,
                'duplicates_count' => 0,
                'duplicates' => []
            ];
        }

        // Rechercher les doublons par téléphone
        $duplicates = Order::where('admin_id', $order->admin_id)
            ->where('id', '!=', $order->id)
            ->where(function($query) use ($order) {
                $query->where('customer_phone', $order->customer_phone);
                if ($order->customer_phone_2) {
                    $query->orWhere('customer_phone', $order->customer_phone_2)
                          ->orWhere('customer_phone_2', $order->customer_phone)
                          ->orWhere('customer_phone_2', $order->customer_phone_2);
                }
            })
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $duplicatesData = $duplicates->map(function($duplicate) {
            return [
                'id' => $duplicate->id,
                'status' => $duplicate->status,
                'customer_name' => $duplicate->customer_name,
                'customer_phone' => $duplicate->customer_phone,
                'customer_phone_2' => $duplicate->customer_phone_2,
                'total_price' => $duplicate->total_price,
                'created_at' => $duplicate->created_at->format('d/m/Y H:i'),
                'items_count' => $duplicate->items->count(),
                'items' => $duplicate->items->map(function($item) {
                    return [
                        'product_name' => $item->product ? $item->product->name : 'Produit supprimé',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price
                    ];
                })
            ];
        });

        return [
            'has_duplicates' => $duplicates->count() > 0,
            'duplicates_count' => $duplicates->count(),
            'duplicates' => $duplicatesData
        ];
    }

    /**
     * Formater les données d'une commande pour l'API
     */
    protected function formatOrderData($order)
    {
        if (!$order) {
            return null;
        }
        
        // Obtenir les informations de doublons
        $duplicateInfo = $this->getDuplicateInfo($order);
        
        return [
            'id' => $order->id,
            'status' => $order->status,
            'priority' => $order->priority,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_phone_2' => $order->customer_phone_2,
            'customer_governorate' => $order->customer_governorate,
            'customer_city' => $order->customer_city,
            'customer_address' => $order->customer_address,
            'total_price' => $order->total_price,
            'attempts_count' => $order->attempts_count,
            'daily_attempts_count' => $order->daily_attempts_count,
            'last_attempt_at' => $order->last_attempt_at ? $order->last_attempt_at->toISOString() : null,
            'scheduled_date' => $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : null,
            'is_assigned' => $order->is_assigned,
            'is_suspended' => $order->is_suspended,
            'suspension_reason' => $order->suspension_reason,
            'notes' => $order->notes,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'duplicate_info' => $duplicateInfo,
            'items' => $order->items ? $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'stock' => $item->product->stock,
                        'is_active' => $item->product->is_active
                    ] : null
                ];
            }) : []
        ];
    }
}