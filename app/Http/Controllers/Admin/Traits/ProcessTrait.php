<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\AdminSetting;
use App\Models\Region;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

trait ProcessTrait
{
    /**
     * Obtenir un paramètre de configuration
     */
    protected function getSetting($key, $default = null)
    {
        return AdminSetting::getForAdmin(Auth::guard('admin')->id(), $key, $default);
    }

    /**
     * Réinitialiser les compteurs quotidiens si nécessaire
     */
    protected function resetDailyCountersIfNeeded($admin)
    {
        $lastReset = $this->getSetting('last_daily_reset');
        $today = now()->format('Y-m-d');
        
        if (!$lastReset || $lastReset !== $today) {
            Order::where('admin_id', $admin->id)->update(['daily_attempts_count' => 0]);
            AdminSetting::setForAdmin($admin->id, 'last_daily_reset', $today);
            Log::info("Compteurs quotidiens réinitialisés pour admin {$admin->id}");
        }
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
        
        $order->recordHistory('tentative', $notes);
        
        // Vérifier si la commande doit passer en file ancienne
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
                
                // Utiliser une action existante dans la DB au lieu de 'changement_statut_auto'
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
                
                Log::info("Commande #{$order->id} automatiquement passée en file ancienne après {$order->attempts_count} tentatives");
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Confirmer une commande
     */
    protected function confirmOrder($order, $request, $notes)
    {
        $order->status = 'confirmée';
        $order->confirmed_price = $request->confirmed_price;
        $order->save();
        
        $order->recordHistory('confirmation', $notes);
    }

    /**
     * Valider les données de confirmation
     */
    protected function validateConfirmation($request)
    {
        $request->validate([
            'confirmed_price' => 'required|numeric|min:0',
        ]);
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
     * Mettre à jour les items de la commande
     */
    protected function updateOrderItems($order, $cartItems)
    {
        if (!$cartItems || !is_array($cartItems)) {
            return;
        }
        
        // Supprimer les anciens items
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
            
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalItemPrice,
            ]);
        }
        
        // Mettre à jour le prix total de la commande
        $order->total_price = $totalPrice + ($order->shipping_cost ?? 0);
        $order->save();
    }

    /**
     * Vérifier si une commande a des problèmes de stock
     */
    protected function orderHasStockIssues($order)
    {
        if (!$order->items || $order->items->isEmpty()) {
            return false;
        }
        
        foreach ($order->items as $item) {
            if (!$item->product) {
                continue; // Produit supprimé, on ignore
            }
            
            if ($item->product->stock < $item->quantity) {
                return true;
            }
        }
        
        return false;
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
            'shipping_cost' => $order->shipping_cost,
            'confirmed_price' => $order->confirmed_price,
            'attempts_count' => $order->attempts_count,
            'daily_attempts_count' => $order->daily_attempts_count,
            'last_attempt_at' => $order->last_attempt_at,
            'scheduled_date' => $order->scheduled_date,
            'is_assigned' => $order->is_assigned,
            'is_suspended' => $order->is_suspended,
            'suspension_reason' => $order->suspension_reason,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
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
            }) : [],
            'employee' => $order->employee ? [
                'id' => $order->employee->id,
                'name' => $order->employee->name
            ] : null,
            'region' => $order->region ? [
                'id' => $order->region->id,
                'name' => $order->region->name
            ] : null,
            'city' => $order->city ? [
                'id' => $order->city->id,
                'name' => $order->city->name,
                'shipping_cost' => $order->city->shipping_cost
            ] : null
        ];
    }

    /**
     * Enregistrer une entrée d'historique avec gestion des erreurs
     */
    protected function safeRecordHistory($order, $action, $notes = null, $changes = null, $statusBefore = null, $statusAfter = null)
    {
        try {
            $order->recordHistory($action, $notes, $changes, $statusBefore, $statusAfter);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement de l'historique pour la commande {$order->id}", [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback: essayer avec une action générique
            try {
                $order->recordHistory('modification', $notes, $changes, $statusBefore, $statusAfter);
            } catch (\Exception $fallbackError) {
                Log::error("Erreur lors du fallback historique pour la commande {$order->id}", [
                    'fallback_error' => $fallbackError->getMessage()
                ]);
            }
        }
    }
}