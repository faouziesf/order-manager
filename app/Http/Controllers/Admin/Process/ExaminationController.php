<?php

namespace App\Http\Controllers\Admin\Process;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ExaminationController extends Controller
{
    /**
     * Interface d'examen des commandes avec problèmes de stock
     */
    public function index()
    {
        return view('admin.process.examination.index');
    }

    /**
     * Obtenir les commandes avec problèmes de stock
     */
    public function getOrders(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrders' => false
                ], 401);
            }

            // Obtenir les commandes avec problèmes de stock
            $orders = $this->findOrdersWithStockIssues($admin);
            
            // Appliquer les filtres si fournis
            if ($request->has('filters')) {
                $orders = $this->applyFilters($orders, $request->input('filters'));
            }
            
            if ($orders->count() > 0) {
                $ordersData = $orders->map(function($order) {
                    return $this->formatOrderDataForExamination($order);
                })->filter()->values()->toArray();
                
                return response()->json([
                    'hasOrders' => true,
                    'orders' => $ordersData,
                    'total' => count($ordersData)
                ]);
            }
            
            return response()->json([
                'hasOrders' => false,
                'message' => 'Aucune commande avec problème de stock trouvée'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans ExaminationController@getOrders: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(),
                'hasOrders' => false
            ], 500);
        }
    }

    /**
     * Compter les commandes avec problèmes de stock
     */
    public function getCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $this->countOrdersWithStockIssues($admin);
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans ExaminationController@getCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Diviser une commande en retirant les produits problématiques
     */
    public function splitOrder(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'notes' => 'required|string|min:3|max:1000'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            // Vérifier que la commande a des problèmes de stock
            $stockIssues = $this->analyzeOrderStockIssues($order);
            
            if (!$stockIssues['hasIssues'] || $stockIssues['availableItems']->count() === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande ne peut pas être divisée (aucun produit disponible ou pas de problème de stock)'
                ], 400);
            }

            // Créer la nouvelle commande avec les produits disponibles
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
            foreach ($stockIssues['availableItems'] as $item) {
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
            foreach ($stockIssues['availableItems'] as $item) {
                $item->delete();
            }

            // Recalculer le prix de la commande originale et la suspendre
            $originalTotalPrice = $stockIssues['unavailableItems']->sum('total_price');
            $order->total_price = $originalTotalPrice;
            $order->is_suspended = true;
            $order->suspension_reason = 'Produits en rupture de stock ou inactifs - Division effectuée';
            $order->save();

            // Enregistrer dans l'historique
            $order->recordHistory(
                'division',
                "Commande divisée par {$admin->name}. Nouvelle commande #{$newOrder->id} créée avec les produits disponibles. Raison: {$notes}",
                [
                    'new_order_id' => $newOrder->id,
                    'available_items_count' => $stockIssues['availableItems']->count(),
                    'unavailable_items_count' => $stockIssues['unavailableItems']->count(),
                    'division_reason' => $notes
                ]
            );

            $newOrder->recordHistory(
                'création',
                "Commande créée suite à la division de la commande #{$order->id} par {$admin->name}. Contient uniquement les produits disponibles.",
                [
                    'original_order_id' => $order->id,
                    'items_count' => $stockIssues['availableItems']->count(),
                    'created_from_division' => true
                ]
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
            Log::error('Erreur dans ExaminationController@splitOrder: ' . $e->getMessage(), [
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
     * Traiter une action d'examen (annuler, suspendre, réactiver)
     */
    public function processAction(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'action' => 'required|in:cancel,suspend,reactivate',
                'notes' => 'required|string|min:3|max:1000'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $action = $validated['action'];
            $notes = $validated['notes'];

            switch ($action) {
                case 'cancel':
                    $order->status = 'annulée';
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    
                    $order->recordHistory(
                        'annulation',
                        "Commande annulée depuis l'interface d'examen par {$admin->name}. Raison: {$notes}",
                        ['cancelled_from_examination' => true]
                    );
                    break;

                case 'suspend':
                    $order->is_suspended = true;
                    $order->suspension_reason = $notes;
                    $order->save();
                    
                    $order->recordHistory(
                        'suspension',
                        "Commande suspendue depuis l'interface d'examen par {$admin->name}. Raison: {$notes}",
                        ['suspended_from_examination' => true]
                    );
                    break;

                case 'reactivate':
                    // Vérifier d'abord que les stocks sont OK
                    $stockIssues = $this->analyzeOrderStockIssues($order);
                    
                    if ($stockIssues['hasIssues']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Impossible de réactiver: certains produits sont toujours en rupture de stock ou inactifs'
                        ], 400);
                    }
                    
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    
                    $order->recordHistory(
                        'réactivation',
                        "Commande réactivée depuis l'interface d'examen par {$admin->name}. Raison: {$notes}",
                        ['reactivated_from_examination' => true]
                    );
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Action effectuée avec succès',
                'order_id' => $order->id,
                'action' => $action
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans ExaminationController@processAction: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'action' => $request->action,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actions en lot
     */
    public function bulkSplit(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'notes' => 'required|string|min:3|max:1000'
            ]);

            $admin = Auth::guard('admin')->user();
            $orderIds = $validated['order_ids'];
            $notes = $validated['notes'];

            DB::beginTransaction();

            $successCount = 0;
            $failedOrders = [];

            foreach ($orderIds as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if (!$order) {
                    $failedOrders[] = "Commande #{$orderId} non trouvée";
                    continue;
                }

                $stockIssues = $this->analyzeOrderStockIssues($order);
                
                if (!$stockIssues['hasIssues'] || $stockIssues['availableItems']->count() === 0) {
                    $failedOrders[] = "Commande #{$orderId} ne peut pas être divisée";
                    continue;
                }

                // Effectuer la division (logique similaire à splitOrder)
                $result = $this->performOrderSplit($order, $notes, $admin);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedOrders[] = "Commande #{$orderId}: " . $result['message'];
                }
            }

            DB::commit();

            $message = "{$successCount} commande(s) divisée(s) avec succès";
            if (count($failedOrders) > 0) {
                $message .= ". Échecs: " . implode(', ', $failedOrders);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => count($failedOrders)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans ExaminationController@bulkSplit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la division en lot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkCancel(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'notes' => 'required|string|min:3|max:1000'
            ]);

            $admin = Auth::guard('admin')->user();
            $orderIds = $validated['order_ids'];
            $notes = $validated['notes'];

            DB::beginTransaction();

            $successCount = 0;

            foreach ($orderIds as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if ($order) {
                    $order->status = 'annulée';
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    
                    $order->recordHistory(
                        'annulation',
                        "Commande annulée en lot depuis l'interface d'examen par {$admin->name}. Raison: {$notes}",
                        ['bulk_cancelled_from_examination' => true]
                    );
                    
                    $successCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$successCount} commande(s) annulée(s) avec succès",
                'success_count' => $successCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans ExaminationController@bulkCancel: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation en lot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkSuspend(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'notes' => 'required|string|min:3|max:1000'
            ]);

            $admin = Auth::guard('admin')->user();
            $orderIds = $validated['order_ids'];
            $notes = $validated['notes'];

            DB::beginTransaction();

            $successCount = 0;

            foreach ($orderIds as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if ($order) {
                    $order->is_suspended = true;
                    $order->suspension_reason = $notes;
                    $order->save();
                    
                    $order->recordHistory(
                        'suspension',
                        "Commande suspendue en lot depuis l'interface d'examen par {$admin->name}. Raison: {$notes}",
                        ['bulk_suspended_from_examination' => true]
                    );
                    
                    $successCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$successCount} commande(s) suspendue(s) avec succès",
                'success_count' => $successCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans ExaminationController@bulkSuspend: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suspension en lot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HELPERS
     */

    /**
     * Trouver les commandes avec problèmes de stock
     */
    private function findOrdersWithStockIssues($admin)
    {
        return $admin->orders()
            ->with(['items.product'])
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->get()
            ->filter(function($order) {
                return $this->orderHasStockIssues($order);
            });
    }

    /**
     * Compter les commandes avec problèmes de stock
     */
    private function countOrdersWithStockIssues($admin)
    {
        $orders = $admin->orders()
            ->with(['items.product'])
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->get();

        return $orders->filter(function($order) {
            return $this->orderHasStockIssues($order);
        })->count();
    }

    /**
     * Vérifier si une commande a des problèmes de stock
     */
    private function orderHasStockIssues($order)
    {
        foreach ($order->items as $item) {
            if (!$item->product) {
                return true; // Produit supprimé
            }
            
            if (!$item->product->is_active) {
                return true; // Produit inactif
            }
            
            if ($item->product->stock < $item->quantity) {
                return true; // Stock insuffisant
            }
        }
        
        return false;
    }

    /**
     * Analyser les problèmes de stock d'une commande
     */
    private function analyzeOrderStockIssues($order)
    {
        $availableItems = collect();
        $unavailableItems = collect();
        $issues = [];

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
    }

    /**
     * Formater les données d'une commande pour l'examen
     */
    private function formatOrderDataForExamination($order)
    {
        try {
            if (!$order || !$order->id) {
                return null;
            }

            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }
            
            $stockAnalysis = $this->analyzeOrderStockIssues($order);
            
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
                'total_price' => floatval($order->total_price ?? 0),
                'created_at' => $order->created_at ? $order->created_at->toISOString() : now()->toISOString(),
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
                })->toArray() : [],
                'stock_analysis' => $stockAnalysis
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans formatOrderDataForExamination: ' . $e->getMessage(), [
                'order_id' => $order->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Appliquer des filtres aux commandes
     */
    private function applyFilters($orders, $filters)
    {
        if (isset($filters['status']) && $filters['status']) {
            $orders = $orders->where('status', $filters['status']);
        }

        if (isset($filters['priority']) && $filters['priority']) {
            $orders = $orders->where('priority', $filters['priority']);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $orders = $orders->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $orders = $orders->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $orders;
    }

    /**
     * Effectuer la division d'une commande (helper pour actions en lot)
     */
    private function performOrderSplit($order, $notes, $admin)
    {
        try {
            $stockIssues = $this->analyzeOrderStockIssues($order);
            
            if (!$stockIssues['hasIssues'] || $stockIssues['availableItems']->count() === 0) {
                return [
                    'success' => false,
                    'message' => 'Aucun produit disponible'
                ];
            }

            // Créer la nouvelle commande
            $newOrder = $order->replicate();
            $newOrder->status = 'nouvelle';
            $newOrder->attempts_count = 0;
            $newOrder->daily_attempts_count = 0;
            $newOrder->last_attempt_at = null;
            $newOrder->is_suspended = false;
            $newOrder->suspension_reason = null;
            $newOrder->save();

            // Ajouter les produits disponibles
            $newTotalPrice = 0;
            foreach ($stockIssues['availableItems'] as $item) {
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

            // Modifier la commande originale
            foreach ($stockIssues['availableItems'] as $item) {
                $item->delete();
            }

            $originalTotalPrice = $stockIssues['unavailableItems']->sum('total_price');
            $order->total_price = $originalTotalPrice;
            $order->is_suspended = true;
            $order->suspension_reason = 'Produits en rupture - Division en lot';
            $order->save();

            // Historique
            $order->recordHistory(
                'division',
                "Commande divisée en lot par {$admin->name}. Nouvelle commande #{$newOrder->id}. Raison: {$notes}",
                ['bulk_division' => true, 'new_order_id' => $newOrder->id]
            );

            return [
                'success' => true,
                'new_order_id' => $newOrder->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}