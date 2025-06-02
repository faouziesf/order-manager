<?php

namespace App\Http\Controllers\Admin\Process;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockReturnController extends Controller
{
    /**
     * Interface de traitement des retours en stock
     */
    public function interface()
    {
        return view('admin.process.stock-return.interface');
    }

    /**
     * Obtenir les commandes suspendues avec produits revenus en stock
     */
    public function getOrders(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrder' => false
                ], 401);
            }

            // Réinitialiser les compteurs journaliers si nécessaire
            $this->resetDailyCountersIfNeeded($admin);

            // Trouver la prochaine commande à traiter
            $order = $this->findNextStockReturnOrder($admin);
            
            if ($order) {
                $orderData = $this->formatStockReturnOrderData($order);
                
                return response()->json([
                    'hasOrder' => true,
                    'order' => $orderData
                ]);
            }
            
            return response()->json([
                'hasOrder' => false,
                'message' => 'Aucune commande suspendue avec produits revenus en stock'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans StockReturnController@getOrders: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(),
                'hasOrder' => false
            ], 500);
        }
    }

    /**
     * Compter les commandes suspendues avec produits revenus en stock
     */
    public function getCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $this->countStockReturnOrders($admin);
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans StockReturnController@getCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Traiter une action sur une commande de retour en stock
     */
    public function processAction(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'action' => 'required|string',
                'notes' => 'required|string|min:3|max:1000',
                'confirmed_price' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'scheduled_date' => 'nullable|date|after:today',
                'cart_items' => 'nullable|array'
            ]);

            if (!$order->is_suspended) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande n\'est pas suspendue'
                ], 400);
            }

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $action = $validated['action'];
            $notes = $validated['notes'];

            // Enregistrer dans l'historique avec le nom de l'utilisateur
            $historyNote = $admin->name . " a effectué l'action [{$action}] : {$notes}";

            switch ($action) {
                case 'call':
                    $this->recordStockReturnAttempt($order, $historyNote);
                    break;
                    
                case 'confirm':
                    $this->validateConfirmation($request);
                    $this->confirmStockReturnOrder($order, $request, $historyNote);
                    
                    // Mettre à jour les items du panier si fournis
                    if ($request->has('cart_items')) {
                        $this->updateOrderItems($order, $request->cart_items);
                    }
                    break;

                case 'cancel':
                    $order->status = 'annulée';
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    $order->recordHistory('annulation', $historyNote);
                    break;
                    
                case 'schedule':
                    $request->validate([
                        'scheduled_date' => 'required|date|after:today',
                    ]);
                    // Réinitialiser les compteurs quand on date une commande
                    $order->status = 'datée';
                    $order->scheduled_date = $request->scheduled_date;
                    $order->attempts_count = 0; 
                    $order->daily_attempts_count = 0; 
                    $order->last_attempt_at = null;
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    $order->recordHistory('datation', $historyNote);
                    break;

                case 'partial_reactivate':
                    // Réactiver partiellement - garder suspendu mais permettre le traitement
                    $this->updateOrderInfo($order, $request);
                    $order->recordHistory('modification', $historyNote);
                    break;
                    
                default:
                    // Action générique - mise à jour des informations
                    $this->updateOrderInfo($order, $request);
                    $order->recordHistory('modification', $historyNote);
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Action traitée avec succès',
                'order_id' => $order->id,
                'action' => $action
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans StockReturnController@processAction: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'action' => $request->action,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réactiver complètement une commande (retour en stock complet)
     */
    public function reactivateOrder(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            if (!$order->is_suspended) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande n\'est pas suspendue'
                ], 400);
            }

            $validated = $request->validate([
                'notes' => 'required|string|min:3|max:1000'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            // Vérifier que tous les produits sont maintenant disponibles
            $stockStatus = $this->checkCompleteStockAvailability($order);
            
            if ($stockStatus['hasIssues']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de réactiver complètement: certains produits ont encore des problèmes',
                    'issues' => $stockStatus['issues']
                ], 400);
            }

            // Réactiver complètement
            $order->is_suspended = false;
            $order->suspension_reason = null;
            $order->attempts_count = 0; // Reset des tentatives
            $order->daily_attempts_count = 0;
            $order->last_attempt_at = null;
            $order->save();
            
            $order->recordHistory(
                'réactivation',
                "Commande complètement réactivée depuis l'interface retour en stock par {$admin->name}. Raison: {$notes}",
                ['full_reactivation_from_stock_return' => true]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande réactivée avec succès et remise dans le circuit normal',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans StockReturnController@reactivateOrder: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réactivation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Diviser une commande suspendue (séparer produits disponibles des non-disponibles)
     */
    public function splitOrder(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            if (!$order->is_suspended) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande n\'est pas suspendue'
                ], 400);
            }

            $validated = $request->validate([
                'notes' => 'required|string|min:3|max:1000'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            // Analyser les stocks
            $stockAnalysis = $this->analyzeStockReturn($order);
            
            if (!$stockAnalysis['hasAvailableItems']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun produit disponible pour division'
                ], 400);
            }

            // Créer nouvelle commande avec produits disponibles
            $newOrder = $order->replicate();
            $newOrder->status = 'nouvelle';
            $newOrder->attempts_count = 0;
            $newOrder->daily_attempts_count = 0;
            $newOrder->last_attempt_at = null;
            $newOrder->is_suspended = false;
            $newOrder->suspension_reason = null;
            $newOrder->save();

            // Ajouter les produits disponibles à la nouvelle commande
            $newTotalPrice = 0;
            foreach ($stockAnalysis['availableItems'] as $item) {
                $newOrder->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
                $newTotalPrice += $item->total_price;
            }
            
            $newOrder->total_price = $newTotalPrice;
            $newOrder->save();

            // Supprimer les produits disponibles de la commande originale
            foreach ($stockAnalysis['availableItems'] as $item) {
                $item->delete();
            }

            // Recalculer le prix de la commande originale
            $originalTotalPrice = $stockAnalysis['unavailableItems']->sum('total_price');
            $order->total_price = $originalTotalPrice;
            $order->save();

            // Historique
            $order->recordHistory(
                'division',
                "Commande divisée depuis l'interface retour en stock par {$admin->name}. Nouvelle commande #{$newOrder->id}. Raison: {$notes}",
                ['division_from_stock_return' => true, 'new_order_id' => $newOrder->id]
            );

            $newOrder->recordHistory(
                'création',
                "Commande créée par division depuis l'interface retour en stock. Commande originale #{$order->id}.",
                ['created_from_stock_return_division' => true, 'original_order_id' => $order->id]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Commande divisée avec succès. Nouvelle commande #{$newOrder->id} créée avec les produits disponibles.",
                'newOrderId' => $newOrder->id,
                'originalOrderId' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans StockReturnController@splitOrder: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la division: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HELPERS
     */

    /**
     * Trouver la prochaine commande suspendue avec produits revenus en stock
     */
    private function findNextStockReturnOrder($admin)
    {
        $maxDailyAttempts = $this->getSetting('stock_return_max_daily_attempts', 3);
        $delayHours = $this->getSetting('stock_return_delay_hours', 4);
        $maxTotalAttempts = $this->getSetting('stock_return_max_total_attempts', 15);

        $suspendedOrders = $admin->orders()
            ->with(['items.product'])
            ->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('priority', 'desc')
            ->orderBy('updated_at', 'asc') // Plus anciennes en premier
            ->get();

        // Filtrer pour ne garder que celles avec des produits revenus en stock
        foreach ($suspendedOrders as $order) {
            if ($maxTotalAttempts > 0 && $order->attempts_count >= $maxTotalAttempts) {
                continue;
            }

            $analysis = $this->analyzeStockReturn($order);
            if ($analysis['hasAvailableItems'] || $analysis['hasImprovedStock']) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Compter les commandes suspendues avec produits revenus en stock
     */
    private function countStockReturnOrders($admin)
    {
        $maxDailyAttempts = $this->getSetting('stock_return_max_daily_attempts', 3);
        $delayHours = $this->getSetting('stock_return_delay_hours', 4);
        $maxTotalAttempts = $this->getSetting('stock_return_max_total_attempts', 15);

        $suspendedOrders = $admin->orders()
            ->with(['items.product'])
            ->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->get();

        $count = 0;
        foreach ($suspendedOrders as $order) {
            if ($maxTotalAttempts > 0 && $order->attempts_count >= $maxTotalAttempts) {
                continue;
            }

            $analysis = $this->analyzeStockReturn($order);
            if ($analysis['hasAvailableItems'] || $analysis['hasImprovedStock']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Analyser le retour en stock d'une commande
     */
    private function analyzeStockReturn($order)
    {
        $availableItems = collect();
        $unavailableItems = collect();
        $improvedItems = collect();
        
        foreach ($order->items as $item) {
            $isAvailable = true;
            $hasImproved = false;

            if (!$item->product) {
                $isAvailable = false;
            } else {
                if (!$item->product->is_active) {
                    $isAvailable = false;
                } else {
                    $hasImproved = true; // Produit réactivé
                }
                
                if ($item->product->stock < $item->quantity) {
                    $isAvailable = false;
                    if ($item->product->stock > 0) {
                        $hasImproved = true; // Stock partiel mais amélioration
                    }
                }
            }

            if ($isAvailable) {
                $availableItems->push($item);
            } else {
                $unavailableItems->push($item);
            }

            if ($hasImproved) {
                $improvedItems->push($item);
            }
        }

        return [
            'availableItems' => $availableItems,
            'unavailableItems' => $unavailableItems,
            'improvedItems' => $improvedItems,
            'hasAvailableItems' => $availableItems->count() > 0,
            'hasImprovedStock' => $improvedItems->count() > 0,
            'canFullyReactivate' => $unavailableItems->count() === 0
        ];
    }

    /**
     * Formater les données d'une commande pour l'interface retour en stock
     */
    private function formatStockReturnOrderData($order)
    {
        try {
            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }
            
            $stockAnalysis = $this->analyzeStockReturn($order);
            
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
                'total_price' => floatval($order->total_price),
                'confirmed_price' => $order->confirmed_price ? floatval($order->confirmed_price) : null,
                'shipping_cost' => floatval($order->shipping_cost ?? 0),
                'attempts_count' => intval($order->attempts_count),
                'daily_attempts_count' => intval($order->daily_attempts_count),
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'last_attempt_at' => $order->last_attempt_at ? $order->last_attempt_at->toISOString() : null,
                'suspension_reason' => $order->suspension_reason,
                'stock_analysis' => $stockAnalysis,
                'items' => $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => intval($item->quantity),
                        'unit_price' => floatval($item->unit_price),
                        'total_price' => floatval($item->total_price),
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => floatval($item->product->price),
                            'stock' => intval($item->product->stock),
                            'is_active' => $item->product->is_active
                        ] : null
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans formatStockReturnOrderData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifier la disponibilité complète des stocks
     */
    private function checkCompleteStockAvailability($order)
    {
        $issues = [];
        $hasIssues = false;

        foreach ($order->items as $item) {
            if (!$item->product) {
                $hasIssues = true;
                $issues[] = "Produit {$item->product_id} supprimé";
            } else {
                if (!$item->product->is_active) {
                    $hasIssues = true;
                    $issues[] = "{$item->product->name} est inactif";
                }
                
                if ($item->product->stock < $item->quantity) {
                    $hasIssues = true;
                    $issues[] = "{$item->product->name}: stock insuffisant ({$item->product->stock}/{$item->quantity})";
                }
            }
        }

        return [
            'hasIssues' => $hasIssues,
            'issues' => $issues
        ];
    }

    /**
     * Enregistrer une tentative d'appel pour retour en stock
     */
    private function recordStockReturnAttempt($order, $notes)
    {
        // Incrémenter les compteurs
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        
        // Mettre à jour la date de dernière tentative
        $order->last_attempt_at = now();
        $order->save();
        
        // Enregistrer dans l'historique
        $order->recordHistory('tentative_retour_stock', $notes);
    }

    /**
     * Valider les données pour une confirmation
     */
    private function validateConfirmation(Request $request)
    {
        $request->validate([
            'confirmed_price' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Confirmer une commande de retour en stock
     */
    private function confirmStockReturnOrder($order, $request, $notes)
    {
        $updateData = [
            'status' => 'confirmée',
            'confirmed_price' => $request->confirmed_price,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'is_suspended' => false, // Réactiver lors de la confirmation
            'suspension_reason' => null,
        ];

        // Ajouter les champs optionnels s'ils sont fournis
        if ($request->filled('customer_name')) {
            $updateData['customer_name'] = $request->customer_name;
        }
        if ($request->filled('customer_phone_2')) {
            $updateData['customer_phone_2'] = $request->customer_phone_2;
        }
        if ($request->filled('customer_governorate')) {
            $updateData['customer_governorate'] = $request->customer_governorate;
        }
        if ($request->filled('customer_city')) {
            $updateData['customer_city'] = $request->customer_city;
        }
        if ($request->filled('customer_address')) {
            $updateData['customer_address'] = $request->customer_address;
        }

        $order->update($updateData);
        $order->recordHistory('confirmation_retour_stock', $notes);
    }

    /**
     * Mettre à jour les informations de base d'une commande
     */
    private function updateOrderInfo($order, $request)
    {
        $updateData = [];
        
        if ($request->filled('customer_name')) {
            $updateData['customer_name'] = $request->customer_name;
        }
        
        if ($request->filled('customer_phone_2')) {
            $updateData['customer_phone_2'] = $request->customer_phone_2;
        }
        
        if ($request->filled('customer_address')) {
            $updateData['customer_address'] = $request->customer_address;
        }
        
        if ($request->filled('shipping_cost')) {
            $updateData['shipping_cost'] = $request->shipping_cost;
        }
        
        if ($request->filled('customer_governorate')) {
            $updateData['customer_governorate'] = $request->customer_governorate;
        }
        
        if ($request->filled('customer_city')) {
            $updateData['customer_city'] = $request->customer_city;
        }
        
        if (!empty($updateData)) {
            $order->update($updateData);
        }
    }

    /**
     * Mettre à jour les items de la commande
     */
    private function updateOrderItems($order, $cartItems)
    {
        try {
            if (!is_array($cartItems)) {
                Log::warning('updateOrderItems: cartItems n\'est pas un tableau');
                return;
            }

            // Supprimer les anciens items
            $order->items()->delete();
            
            // Ajouter les nouveaux items
            foreach ($cartItems as $item) {
                if (isset($item['product_id'], $item['quantity'], $item['unit_price'])) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                    ]);
                }
            }
            
            // Recalculer le total de la commande
            $newTotal = $order->items()->sum('total_price');
            $order->update(['total_price' => $newTotal]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des items: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtenir une configuration avec gestion d'erreur
     */
    private function getSetting($key, $default)
    {
        try {
            return (float)AdminSetting::get($key, $default);
        } catch (\Exception $e) {
            Log::warning("Impossible de récupérer le setting {$key}, utilisation de la valeur par défaut: {$default}");
            return $default;
        }
    }

    /**
     * Réinitialiser les compteurs journaliers si nécessaire
     */
    private function resetDailyCountersIfNeeded($admin)
    {
        try {
            $lastReset = AdminSetting::get('last_daily_reset_' . $admin->id);
            $today = now()->format('Y-m-d');
            
            if ($lastReset !== $today) {
                Log::info("Réinitialisation des compteurs journaliers pour l'admin {$admin->id}");
                
                $admin->orders()
                    ->where('daily_attempts_count', '>', 0)
                    ->update(['daily_attempts_count' => 0]);
                
                AdminSetting::set('last_daily_reset_' . $admin->id, $today);
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réinitialisation des compteurs journaliers: " . $e->getMessage());
        }
    }
}