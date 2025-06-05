<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait ProcessTrait
{
    /**
     * Helper pour obtenir les paramètres avec gestion d'erreur
     */
    protected function getSetting($key, $default)
    {
        try {
            if (!is_string($key)) {
                Log::warning("getSetting: clé invalide", ['key' => $key, 'type' => gettype($key)]);
                return $default;
            }
            
            // Vérifier si la classe AdminSetting existe
            if (!class_exists(AdminSetting::class)) {
                Log::warning("AdminSetting class not found, using default value for {$key}");
                return $default;
            }
            
            $value = AdminSetting::get($key, $default);
            return is_numeric($value) ? (float)$value : $value;
        } catch (\Exception $e) {
            Log::warning("Impossible de récupérer le setting {$key}, utilisation de la valeur par défaut: {$default}. Erreur: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Réinitialise les compteurs journaliers si nécessaire
     */
    protected function resetDailyCountersIfNeeded($admin)
    {
        try {
            if (!$admin || !$admin->id) {
                Log::warning('resetDailyCountersIfNeeded: admin invalide');
                return;
            }

            $lastResetSettingKey = 'last_daily_reset_' . $admin->id;
            $lastReset = $this->getSetting($lastResetSettingKey, null);
            $today = now()->format('Y-m-d');
            
            if ($lastReset !== $today) {
                Log::info("Réinitialisation des compteurs journaliers pour l'admin {$admin->id}");
                
                // Vérifier si la table orders existe
                if (!\Schema::hasTable('orders')) {
                    Log::error('Table orders not found');
                    return;
                }
                
                $updatedCount = \App\Models\Order::where('admin_id', $admin->id)
                    ->whereDate('last_attempt_at', '<', $today)
                    ->update(['daily_attempts_count' => 0]);
                
                AdminSetting::set($lastResetSettingKey, $today);
                
                Log::info("Compteurs journaliers réinitialisés pour {$updatedCount} commandes pour l'admin {$admin->id}");
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réinitialisation des compteurs journaliers: " . $e->getMessage());
        }
    }

    /**
     * Helper: Vérifier si une commande a des problèmes de stock
     */
    protected function orderHasStockIssues($order)
    {
        try {
            if (!$order || !$order->items || $order->items->count() === 0) {
                Log::debug("orderHasStockIssues: commande sans items", ['order_id' => $order->id ?? 'unknown']);
                return false;
            }

            // Recharger les relations si nécessaire
            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }

            foreach ($order->items as $item) {
                if (!$item->product) {
                    Log::debug("orderHasStockIssues: produit supprimé détecté", [
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'product_id' => $item->product_id
                    ]);
                    return true; // Produit supprimé
                }
                
                if (!$item->product->is_active) {
                    Log::debug("orderHasStockIssues: produit inactif détecté", [
                        'order_id' => $order->id,
                        'product_id' => $item->product->id,
                        'product_name' => $item->product->name
                    ]);
                    return true; // Produit inactif
                }
                
                if ($item->product->stock < $item->quantity) {
                    Log::debug("orderHasStockIssues: stock insuffisant détecté", [
                        'order_id' => $order->id,
                        'product_id' => $item->product->id,
                        'stock_available' => $item->product->stock,
                        'quantity_needed' => $item->quantity
                    ]);
                    return true; // Stock insuffisant
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("orderHasStockIssues: Erreur pour commande {$order->id}: " . $e->getMessage());
            return true; // En cas d'erreur, considérer qu'il y a un problème
        }
    }

    /**
     * Helper: Analyser les problèmes de stock d'une commande
     */
    protected function analyzeOrderStockIssues($order)
    {
        try {
            $availableItems = collect();
            $unavailableItems = collect();
            $issues = [];

            if (!$order || !$order->items) {
                Log::warning('analyzeOrderStockIssues: commande ou items invalides', ['order_id' => $order->id ?? 'unknown']);
                return [
                    'hasIssues' => false,
                    'availableItems' => $availableItems,
                    'unavailableItems' => $unavailableItems,
                    'issues' => $issues
                ];
            }

            // Recharger les relations si nécessaire
            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }

            foreach ($order->items as $item) {
                $hasIssue = false;
                $issueReasons = [];

                if (!$item->product) {
                    $hasIssue = true;
                    $issueReasons[] = 'Produit supprimé';
                } else {
                    if (!$item->product->is_active) {
                        $hasIssue = true;
                        $issueReasons[] = 'Produit inactif';
                    }
                    
                    if ($item->product->stock < $item->quantity) {
                        $hasIssue = true;
                        $issueReasons[] = "Stock insuffisant ({$item->product->stock} disponible, {$item->quantity} demandé)";
                    }
                }

                if ($hasIssue) {
                    $unavailableItems->push($item);
                    $issues[] = [
                        'item_id' => $item->id,
                        'product_name' => $item->product ? $item->product->name : 'Produit supprimé',
                        'reasons' => $issueReasons
                    ];
                } else {
                    $availableItems->push($item);
                }
            }

            return [
                'hasIssues' => $unavailableItems->count() > 0,
                'availableItems' => $availableItems,
                'unavailableItems' => $unavailableItems,
                'issues' => $issues
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans analyzeOrderStockIssues: ' . $e->getMessage(), [
                'order_id' => $order->id ?? 'unknown'
            ]);
            return [
                'hasIssues' => false,
                'availableItems' => collect(),
                'unavailableItems' => collect(),
                'issues' => []
            ];
        }
    }

    /**
     * Formate les données d'une commande pour l'API
     */
    protected function formatOrderData($order)
    {
        try {
            if (!$order || !$order->id) {
                Log::warning('formatOrderData: commande invalide');
                return null;
            }

            // Recharger les relations si nécessaire
            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }
            
            return [
                'id' => $order->id,
                'status' => $order->status ?? 'nouvelle',
                'priority' => $order->priority ?? 'normale',
                'customer_name' => $order->customer_name ?? '',
                'customer_phone' => $order->customer_phone ?? '',
                'customer_phone_2' => $order->customer_phone_2 ?? '',
                'customer_governorate' => $order->customer_governorate ?? '',
                'customer_city' => $order->customer_city ?? '',
                'customer_address' => $order->customer_address ?? '',
                'shipping_cost' => floatval($order->shipping_cost ?? 0),
                'total_price' => floatval($order->total_price ?? 0),
                'confirmed_price' => $order->confirmed_price ? floatval($order->confirmed_price) : null,
                'scheduled_date' => $order->scheduled_date ? ($order->scheduled_date instanceof Carbon ? $order->scheduled_date->format('Y-m-d') : Carbon::parse($order->scheduled_date)->format('Y-m-d')) : null,
                'attempts_count' => intval($order->attempts_count ?? 0),
                'daily_attempts_count' => intval($order->daily_attempts_count ?? 0),
                'created_at' => $order->created_at ? ($order->created_at instanceof Carbon ? $order->created_at->toISOString() : Carbon::parse($order->created_at)->toISOString()) : null,
                'updated_at' => $order->updated_at ? ($order->updated_at instanceof Carbon ? $order->updated_at->toISOString() : Carbon::parse($order->updated_at)->toISOString()) : null,
                'last_attempt_at' => $order->last_attempt_at ? ($order->last_attempt_at instanceof Carbon ? $order->last_attempt_at->toISOString() : Carbon::parse($order->last_attempt_at)->toISOString()) : null,
                'is_suspended' => $order->is_suspended ?? false,
                'suspension_reason' => $order->suspension_reason ?? '',
                'items' => $order->items ? $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => intval($item->quantity ?? 0),
                        'unit_price' => floatval($item->unit_price ?? 0),
                        'total_price' => floatval($item->total_price ?? 0),
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name ?? 'Produit sans nom',
                            'price' => floatval($item->product->price ?? 0),
                            'stock' => intval($item->product->stock ?? 0),
                            'is_active' => $item->product->is_active ?? false
                        ] : [
                            'id' => null,
                            'name' => 'Produit supprimé',
                            'price' => 0,
                            'stock' => 0,
                            'is_active' => false
                        ]
                    ];
                })->toArray() : []
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans formatOrderData: ' . $e->getMessage(), ['order_id' => $order->id ?? 'unknown']);
            return null;
        }
    }

    /**
     * Valide les données pour une confirmation
     */
    protected function validateConfirmation($request)
    {
        return $request->validate([
            'confirmed_price' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone_2' => 'nullable|string|max:20',
            'customer_governorate' => 'nullable|string|max:255',
            'customer_city' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string|max:1000',
        ]);
    }

    /**
     * Confirme une commande
     */
    protected function confirmOrder($order, $request, $notes)
    {
        $updateData = [
            'status' => 'confirmée',
            'confirmed_price' => $request->confirmed_price,
            'shipping_cost' => $request->filled('shipping_cost') ? $request->shipping_cost : ($order->shipping_cost ?? 0),
        ];

        if ($request->filled('customer_name')) $updateData['customer_name'] = $request->customer_name;
        if ($request->filled('customer_phone')) $updateData['customer_phone'] = $request->customer_phone;
        if ($request->filled('customer_phone_2')) $updateData['customer_phone_2'] = $request->customer_phone_2;
        if ($request->filled('customer_governorate')) $updateData['customer_governorate'] = $request->customer_governorate;
        if ($request->filled('customer_city')) $updateData['customer_city'] = $request->customer_city;
        if ($request->filled('customer_address')) $updateData['customer_address'] = $request->customer_address;
        
        $order->is_suspended = false;
        $order->suspension_reason = null;

        $order->update($updateData);
        
        // Enregistrer l'historique si la méthode existe
        if (method_exists($order, 'recordHistory')) {
            $order->recordHistory('confirmation', $notes);
        }
    }

    /**
     * Met à jour les informations de base d'une commande
     */
    protected function updateOrderInfo($order, $request)
    {
        $updateData = [];
        
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_phone_2' => 'nullable|string|max:20',
            'customer_governorate' => 'nullable|string|max:255',
            'customer_city' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        if ($request->filled('customer_name')) $updateData['customer_name'] = $validated['customer_name'];
        if ($request->filled('customer_phone')) $updateData['customer_phone'] = $validated['customer_phone'];
        if ($request->filled('customer_phone_2')) $updateData['customer_phone_2'] = $validated['customer_phone_2'];
        if ($request->filled('customer_governorate')) $updateData['customer_governorate'] = $validated['customer_governorate'];
        if ($request->filled('customer_city')) $updateData['customer_city'] = $validated['customer_city'];
        if ($request->filled('customer_address')) $updateData['customer_address'] = $validated['customer_address'];
        if ($request->filled('shipping_cost')) $updateData['shipping_cost'] = $validated['shipping_cost'];
        
        if (!empty($updateData)) {
            $order->update($updateData);
        }
    }

    /**
     * Enregistre une tentative d'appel
     */
    protected function recordCallAttempt($order, $notes)
    {
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        
        $order->last_attempt_at = now();
        $order->save();
        
        // Enregistrer l'historique si la méthode existe
        if (method_exists($order, 'recordHistory')) {
            $order->recordHistory('tentative', $notes);
        }
        
        $standardMaxAttempts = (int)$this->getSetting('standard_max_total_attempts', 9);
        if ($order->status === 'nouvelle' && $order->attempts_count >= $standardMaxAttempts) {
            
            $previousStatus = $order->status;
            $order->status = 'ancienne';
            $order->attempts_count = 0;
            $order->daily_attempts_count = 0;
            $order->save();
            
            if (method_exists($order, 'recordHistory')) {
                $order->recordHistory(
                    'changement_statut_auto', 
                    "Commande #{$order->id} automatiquement passée en file 'ancienne' après {$standardMaxAttempts} tentatives en file 'nouvelle'. Notes: {$notes}",
                    [
                        'previous_status' => $previousStatus, 
                        'new_status' => 'ancienne',
                        'attempts_reached' => $standardMaxAttempts,
                        'auto_transition' => true
                    ]
                );
            }
            
            Log::info("Commande {$order->id} automatiquement changée au statut 'ancienne' après {$order->attempts_count} tentatives (seuil: {$standardMaxAttempts})");
        }
    }

    /**
     * Met à jour les items de la commande
     */
    protected function updateOrderItems($order, $cartItems)
    {
        try {
            if (!is_array($cartItems)) {
                Log::warning('updateOrderItems: cartItems n\'est pas un tableau', ['order_id' => $order->id]);
                return;
            }

            $validItems = [];
            foreach ($cartItems as $item) {
                if (isset($item['product_id'], $item['quantity'], $item['unit_price']) &&
                    is_numeric($item['product_id']) &&
                    is_numeric($item['quantity']) && $item['quantity'] > 0 &&
                    is_numeric($item['unit_price']) && $item['unit_price'] >= 0) {
                    $validItems[] = $item;
                } else {
                    Log::warning('updateOrderItems: item invalide ignoré', ['item' => $item, 'order_id' => $order->id]);
                }
            }

            if (empty($validItems) && !empty($cartItems)) {
                Log::error('updateOrderItems: Aucun item valide fourni pour la mise à jour.', ['order_id' => $order->id]);
                return;
            }

            $order->items()->delete();
            
            $newCalculatedTotalPrice = 0;
            foreach ($validItems as $item) {
                $totalItemPrice = $item['quantity'] * $item['unit_price'];
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalItemPrice,
                ]);
                $newCalculatedTotalPrice += $totalItemPrice;
            }
            
            $orderUpdateData = ['total_price' => $newCalculatedTotalPrice];
            
            $order->update($orderUpdateData);
            Log::info("Items mis à jour pour la commande {$order->id}. Nouveau total: {$newCalculatedTotalPrice}");
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour des items pour la commande {$order->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}