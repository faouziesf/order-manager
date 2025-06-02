<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessController extends Controller
{
    /**
     * Affiche l'interface de traitement unifiée
     */
    public function interface()
    {
        return view('admin.process.interface');
    }

    /**
     * Interface d'examen des commandes avec problèmes de stock
     */
    public function examination()
    {
        return view('admin.process.examination');
    }

    /**
     * NOUVELLE: Interface pour les commandes suspendues uniquement
     */
    public function suspended()
    {
        return view('admin.process.suspended');
    }

    /**
     * NOUVELLE: Interface pour le retour en stock
     */
    public function restockInterface()
    {
        return view('admin.process.restock');
    }

    /**
     * Obtenir les commandes pour l'interface d'examen
     */
    public function getExaminationOrders(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrders' => false
                ], 401);
            }

            // Obtenir les commandes avec problèmes de stock (mais pas suspendues)
            $orders = $this->findOrdersWithStockIssues($admin, false); // false = ne pas inclure les suspendues
            
            if ($orders->count() > 0) {
                $ordersData = $orders->map(function($order) {
                    return $this->formatOrderDataForExamination($order);
                })->filter(function($orderData) {
                    return $orderData !== null;
                })->values()->toArray();
                
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
            Log::error('Erreur dans getExaminationOrders: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(),
                'hasOrders' => false
            ], 500);
        }
    }

    /**
     * NOUVELLE: Obtenir les commandes suspendues
     */
    public function getSuspendedOrders(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrders' => false
                ], 401);
            }

            $query = $admin->orders()
                ->with(['items.product', 'employee'])
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée']);

            // Appliquer les filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('suspension_reason', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('has_stock_issues')) {
                if ($request->has_stock_issues === 'yes') {
                    // Filtrer uniquement celles avec problèmes de stock
                    $orders = $query->get()->filter(function($order) {
                        return $this->orderHasStockIssues($order);
                    });
                } else {
                    // Filtrer celles sans problèmes de stock
                    $orders = $query->get()->filter(function($order) {
                        return !$this->orderHasStockIssues($order);
                    });
                }
            } else {
                $orders = $query->get();
            }

            // Tri
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            
            if ($sortField === 'created_at') {
                $orders = $sortOrder === 'desc' 
                    ? $orders->sortByDesc('created_at')
                    : $orders->sortBy('created_at');
            } elseif ($sortField === 'customer_name') {
                $orders = $sortOrder === 'desc' 
                    ? $orders->sortByDesc('customer_name')
                    : $orders->sortBy('customer_name');
            }

            if ($orders->count() > 0) {
                $ordersData = $orders->map(function($order) {
                    return $this->formatSuspendedOrderData($order);
                })->filter(function($orderData) {
                    return $orderData !== null;
                })->values()->toArray();
                
                return response()->json([
                    'hasOrders' => true,
                    'orders' => $ordersData,
                    'total' => count($ordersData)
                ]);
            }
            
            return response()->json([
                'hasOrders' => false,
                'message' => 'Aucune commande suspendue trouvée'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getSuspendedOrders: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(),
                'hasOrders' => false
            ], 500);
        }
    }

    /**
     * NOUVELLE: Obtenir les commandes pour le retour en stock
     */
    public function getRestockOrders(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrders' => false
                ], 401);
            }

            // Trouver les commandes suspendues où les produits sont maintenant disponibles
            $suspendedOrders = $admin->orders()
                ->with(['items.product'])
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->get();

            $restockOrders = $suspendedOrders->filter(function($order) {
                return $this->orderCanBeReactivated($order);
            });

            if ($restockOrders->count() > 0) {
                $ordersData = $restockOrders->map(function($order) {
                    return $this->formatRestockOrderData($order);
                })->filter(function($orderData) {
                    return $orderData !== null;
                })->values()->toArray();
                
                return response()->json([
                    'hasOrders' => true,
                    'orders' => $ordersData,
                    'total' => count($ordersData)
                ]);
            }
            
            return response()->json([
                'hasOrders' => false,
                'message' => 'Aucune commande prête pour réactivation'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getRestockOrders: ' . $e->getMessage(), [
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
    public function getExaminationCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $this->countOrdersWithStockIssues($admin, false); // false = ne pas inclure les suspendues
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getExaminationCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * NOUVELLE: Compter les commandes suspendues
     */
    public function getSuspendedCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $admin->orders()
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->count();
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getSuspendedCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * NOUVELLE: Compter les commandes prêtes pour réactivation
     */
    public function getRestockCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $suspendedOrders = $admin->orders()
                ->with(['items.product'])
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->get();

            $count = $suspendedOrders->filter(function($order) {
                return $this->orderCanBeReactivated($order);
            })->count();
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getRestockCount: ' . $e->getMessage());
            
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

            // Recalculer le prix de la commande originale
            $originalTotalPrice = $stockIssues['unavailableItems']->sum('total_price');
            $order->total_price = $originalTotalPrice;
            $order->is_suspended = true;
            $order->suspension_reason = 'Produits en rupture de stock ou inactifs';
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
            Log::error('Erreur dans splitOrder: ' . $e->getMessage(), [
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
     * Action d'examen (annuler, modifier, etc.)
     */
    public function examinationAction(Request $request, Order $order)
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
            Log::error('Erreur dans examinationAction: ' . $e->getMessage(), [
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
     * NOUVELLE: Action pour les commandes suspendues
     */
    public function suspendedAction(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'action' => 'required|in:reactivate,cancel,edit_suspension',
                'notes' => 'required|string|min:3|max:1000',
                'new_suspension_reason' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $action = $validated['action'];
            $notes = $validated['notes'];

            switch ($action) {
                case 'reactivate':
                    // Vérifier que les stocks sont OK
                    if ($this->orderHasStockIssues($order)) {
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
                        "Commande réactivée depuis l'interface suspendues par {$admin->name}. Raison: {$notes}",
                        ['reactivated_from_suspended_interface' => true]
                    );
                    break;

                case 'cancel':
                    $order->status = 'annulée';
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    
                    $order->recordHistory(
                        'annulation',
                        "Commande annulée depuis l'interface suspendues par {$admin->name}. Raison: {$notes}",
                        ['cancelled_from_suspended_interface' => true]
                    );
                    break;

                case 'edit_suspension':
                    $newReason = $validated['new_suspension_reason'] ?? $order->suspension_reason;
                    $order->suspension_reason = $newReason;
                    $order->save();
                    
                    $order->recordHistory(
                        'modification',
                        "Raison de suspension modifiée par {$admin->name}. Nouvelle raison: {$newReason}. Notes: {$notes}",
                        ['suspension_reason_updated' => true, 'old_reason' => $order->suspension_reason]
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
            Log::error('Erreur dans suspendedAction: ' . $e->getMessage(), [
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
     * Helper: Trouver les commandes avec problèmes de stock
     */
    private function findOrdersWithStockIssues($admin, $includeSuspended = true)
    {
        $query = $admin->orders()->with(['items.product']);
        
        if (!$includeSuspended) {
            $query->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            });
        }
        
        return $query->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->get()
            ->filter(function($order) {
                return $this->orderHasStockIssues($order);
            });
    }

    /**
     * Helper: Compter les commandes avec problèmes de stock
     */
    private function countOrdersWithStockIssues($admin, $includeSuspended = true)
    {
        $orders = $admin->orders()->with(['items.product']);
        
        if (!$includeSuspended) {
            $orders->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            });
        }
        
        return $orders->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->get()
            ->filter(function($order) {
                return $this->orderHasStockIssues($order);
            })->count();
    }

    /**
     * Helper: Vérifier si une commande a des problèmes de stock
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
     * Helper: Vérifier si une commande peut être réactivée
     */
    private function orderCanBeReactivated($order)
    {
        return !$this->orderHasStockIssues($order);
    }

    /**
     * Helper: Analyser les problèmes de stock d'une commande
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
     * Helper: Formater les données d'une commande pour l'examen
     */
    private function formatOrderDataForExamination($order)
    {
        try {
            if (!$order || !$order->id) {
                Log::warning('formatOrderDataForExamination: order invalide');
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
     * NOUVELLE: Helper pour formater les données des commandes suspendues
     */
    private function formatSuspendedOrderData($order)
    {
        try {
            if (!$order || !$order->id) {
                return null;
            }

            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }
            
            $hasStockIssues = $this->orderHasStockIssues($order);
            $stockAnalysis = $hasStockIssues ? $this->analyzeOrderStockIssues($order) : null;
            
            return [
                'id' => $order->id,
                'status' => $order->status ?? 'nouvelle',
                'priority' => $order->priority ?? 'normale',
                'customer_name' => $order->customer_name ?? '',
                'customer_phone' => $order->customer_phone ?? '',
                'customer_phone_2' => $order->customer_phone_2 ?? '',
                'customer_address' => $order->customer_address ?? '',
                'total_price' => floatval($order->total_price ?? 0),
                'created_at' => $order->created_at ? $order->created_at->toISOString() : now()->toISOString(),
                'suspension_reason' => $order->suspension_reason ?? '',
                'has_stock_issues' => $hasStockIssues,
                'can_reactivate' => !$hasStockIssues,
                'items_count' => $order->items ? $order->items->count() : 0,
                'employee_name' => $order->employee ? $order->employee->name : null,
                'stock_analysis' => $stockAnalysis,
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
            Log::error('Erreur dans formatSuspendedOrderData: ' . $e->getMessage(), [
                'order_id' => $order->id ?? 'unknown'
            ]);
            return null;
        }
    }

    /**
     * NOUVELLE: Helper pour formater les données des commandes pour le retour en stock
     */
    private function formatRestockOrderData($order)
    {
        try {
            if (!$order || !$order->id) {
                return null;
            }

            if (!$order->relationLoaded('items')) {
                $order->load(['items.product']);
            }
            
            return [
                'id' => $order->id,
                'status' => $order->status ?? 'nouvelle',
                'priority' => $order->priority ?? 'normale',
                'customer_name' => $order->customer_name ?? '',
                'customer_phone' => $order->customer_phone ?? '',
                'total_price' => floatval($order->total_price ?? 0),
                'created_at' => $order->created_at ? $order->created_at->toISOString() : now()->toISOString(),
                'suspension_reason' => $order->suspension_reason ?? '',
                'items_count' => $order->items ? $order->items->count() : 0,
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
                            'stock' => intval($item->product->stock ?? 0),
                            'is_active' => $item->product->is_active ?? false
                        ] : null
                    ];
                })->toArray() : []
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans formatRestockOrderData: ' . $e->getMessage(), [
                'order_id' => $order->id ?? 'unknown'
            ]);
            return null;
        }
    }

    // ... (continuer avec les autres méthodes existantes)

    public function getQueue($queue)
    {
        try {
            if (!is_string($queue)) {
                Log::error('getQueue: paramètre queue n\'est pas une chaîne', ['queue' => $queue, 'type' => gettype($queue)]);
                return response()->json([
                    'error' => 'Paramètre de file d\'attente invalide (type incorrect)',
                    'hasOrder' => false
                ], 400);
            }

            $queue = trim(strtolower($queue));
            
            if (!in_array($queue, ['standard', 'dated', 'old', 'restock'])) {
                Log::error('getQueue: nom de file invalide', ['queue' => $queue]);
                return response()->json([
                    'error' => 'File d\'attente invalide',
                    'hasOrder' => false
                ], 400);
            }

            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrder' => false
                ], 401);
            }

            $this->resetDailyCountersIfNeeded($admin);

            // CORRECTION: Gestion spéciale pour restock
            if ($queue === 'restock') {
                $order = $this->findNextRestockOrder($admin);
            } else {
                $order = $this->findNextOrderExcludingStockIssues($admin, $queue);
            }
            
            if ($order) {
                $orderData = $this->formatOrderData($order);
                
                return response()->json([
                    'hasOrder' => true,
                    'order' => $orderData
                ]);
            }
            
            return response()->json([
                'hasOrder' => false,
                'message' => 'Aucune commande disponible dans cette file'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getQueue: ' . $e->getMessage(), [
                'queue' => $queue ?? 'undefined',
                'queue_type' => isset($queue) ? gettype($queue) : 'undefined',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(),
                'hasOrder' => false
            ], 500);
        }
    }

    /**
     * CORRECTION: Trouver la prochaine commande pour le retour en stock avec logging amélioré
     */
    private function findNextRestockOrder($admin)
    {
        try {
            Log::info("findNextRestockOrder: Début de la recherche pour admin {$admin->id}");
            
            // Paramètres spécifiques pour les commandes retour en stock
            $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 10); // Augmenté
            $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 5);  // Augmenté
            $delayHours = $this->getSetting('restock_delay_hours', 0.5); // Réduit

            Log::info("findNextRestockOrder: Paramètres - max_total: {$maxTotalAttempts}, max_daily: {$maxDailyAttempts}, delay: {$delayHours}h");

            // Chercher les commandes suspendues avec statut nouvelle ou datée
            $query = Order::where('admin_id', $admin->id)
                ->with(['items.product'])
                ->where('is_suspended', true)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->whereNotIn('status', ['annulée', 'livrée']) // Exclure explicitement
                ->where('attempts_count', '<', $maxTotalAttempts)
                ->where('daily_attempts_count', '<', $maxDailyAttempts)
                ->where(function($q) use ($delayHours) {
                    $q->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
                })
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'asc');

            $orders = $query->get();
            Log::info("findNextRestockOrder: {$orders->count()} commandes suspendues trouvées");

            // Filtrer les commandes qui peuvent maintenant être traitées
            $eligibleOrders = collect();
            
            foreach ($orders as $order) {
                $canReactivate = $this->orderCanBeReactivatedDebug($order);
                Log::info("findNextRestockOrder: Commande {$order->id} - peut être réactivée: " . ($canReactivate ? 'OUI' : 'NON'));
                
                if ($canReactivate) {
                    $eligibleOrders->push($order);
                }
            }

            $selectedOrder = $eligibleOrders->first();
            
            if ($selectedOrder) {
                Log::info("findNextRestockOrder: Commande sélectionnée: {$selectedOrder->id}");
            } else {
                Log::info("findNextRestockOrder: Aucune commande éligible trouvée");
            }

            return $selectedOrder;

        } catch (\Exception $e) {
            Log::error('Erreur dans findNextRestockOrder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Trouver la prochaine commande en excluant celles avec problèmes de stock ET les suspendues
     */
    private function findNextOrderExcludingStockIssues($admin, $queue)
    {
        try {
            if (!$admin || !is_string($queue)) {
                Log::error('findNextOrderExcludingStockIssues: paramètres invalides', [
                    'admin' => $admin ? $admin->id : null,
                    'queue' => $queue,
                    'queue_type' => gettype($queue)
                ]);
                return null;
            }

            $queue = trim(strtolower($queue));
            
            if (!in_array($queue, ['standard', 'dated', 'old'])) {
                Log::error('findNextOrderExcludingStockIssues: queue invalide', ['queue' => $queue]);
                return null;
            }

            $query = Order::where('admin_id', $admin->id)
                ->with(['items.product'])
                // NOUVEAU: Exclure les commandes suspendues
                ->where(function($q) {
                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                });
            
            switch ($queue) {
                case 'standard':
                    $orders = $this->findStandardOrdersExcludingStockIssues($query);
                    break;
                case 'dated':
                    $orders = $this->findDatedOrdersExcludingStockIssues($query);
                    break;
                case 'old':
                    $orders = $this->findOldOrdersExcludingStockIssues($query);
                    break;
                default:
                    return null;
            }

            $filteredOrders = $orders->get()->filter(function($order) {
                return !$this->orderHasStockIssues($order);
            });

            return $filteredOrders->first();

        } catch (\Exception $e) {
            Log::error('Erreur dans findNextOrderExcludingStockIssues: ' . $e->getMessage(), [
                'queue' => $queue ?? 'undefined',
                'admin_id' => $admin ? $admin->id : null
            ]);
            return null;
        }
    }

    /**
     * Helper modifié: Commandes standard sans problèmes de stock et non suspendues
     */
    private function findStandardOrdersExcludingStockIssues($query)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);
        
        return $query->where('status', 'nouvelle')
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Helper modifié: Commandes datées sans problèmes de stock et non suspendues
     */
    private function findDatedOrdersExcludingStockIssues($query)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);
        
        return $query->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc');
    }

    /**
     * Helper modifié: Commandes anciennes sans problèmes de stock et non suspendues
     */
    private function findOldOrdersExcludingStockIssues($query)
    {
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0);
        
        $baseQuery = $query->where('status', 'ancienne')
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            });
            
        if ($maxTotalAttempts > 0) {
            $baseQuery->where('attempts_count', '<', $maxTotalAttempts);
        }
        
        return $baseQuery->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * MISE À JOUR: Corriger getCounts pour inclure restock
     */
    public function getCounts()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié'
                ], 401);
            }
            
            $this->resetDailyCountersIfNeeded($admin);
            
            $standard = $this->getQueueCountExcludingStockIssues($admin, 'standard');
            $dated = $this->getQueueCountExcludingStockIssues($admin, 'dated');
            $old = $this->getQueueCountExcludingStockIssues($admin, 'old');
            $examination = $this->countOrdersWithStockIssues($admin, false);
            $suspended = $admin->orders()->where('is_suspended', true)->whereNotIn('status', ['annulée', 'livrée'])->count();
            
            // NOUVEAU: Compter les commandes restock
            $restock = $this->getRestockCountForInterface($admin);
            
            return response()->json([
                'standard' => $standard,
                'dated' => $dated,
                'old' => $old,
                'examination' => $examination,
                'suspended' => $suspended,
                'restock' => $restock,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCounts: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement des compteurs',
                'standard' => 0,
                'dated' => 0,
                'old' => 0,
                'examination' => 0,
                'suspended' => 0,
                'restock' => 0
            ], 500);
        }
    }

    /**
     * Helper modifié: Compter les commandes sans problèmes de stock et non suspendues
     */
    private function getQueueCountExcludingStockIssues($admin, $queue)
    {
        try {
            if (!is_string($queue) || !in_array($queue, ['standard', 'dated', 'old'])) {
                Log::warning("getQueueCountExcludingStockIssues: paramètre queue invalide", ['queue' => $queue]);
                return 0;
            }

            $query = Order::where('admin_id', $admin->id)
                ->with(['items.product'])
                // NOUVEAU: Exclure les commandes suspendues
                ->where(function($q) {
                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                });
            
            switch ($queue) {
                case 'standard':
                    $orders = $this->findStandardOrdersExcludingStockIssues($query);
                    break;
                case 'dated':
                    $orders = $this->findDatedOrdersExcludingStockIssues($query);
                    break;
                case 'old':
                    $orders = $this->findOldOrdersExcludingStockIssues($query);
                    break;
                default:
                    return 0;
            }

            $filteredOrders = $orders->get()->filter(function($order) {
                return !$this->orderHasStockIssues($order);
            });

            return $filteredOrders->count();

        } catch (\Exception $e) {
            Log::error("Erreur dans getQueueCountExcludingStockIssues pour {$queue}: " . $e->getMessage());
            return 0;
        }
    }

    // ... (garder toutes les autres méthodes existantes avec leurs implémentations complètes)

    /**
     * Helper pour obtenir les paramètres avec gestion d'erreur
     */
    private function getSetting($key, $default)
    {
        try {
            if (!is_string($key)) {
                Log::warning("getSetting: clé invalide", ['key' => $key, 'type' => gettype($key)]);
                return $default;
            }
            
            return (float)AdminSetting::get($key, $default);
        } catch (\Exception $e) {
            Log::warning("Impossible de récupérer le setting {$key}, utilisation de la valeur par défaut: {$default}");
            return $default;
        }
    }

    /**
     * Formate les données d'une commande pour l'API
     */
    private function formatOrderData($order)
    {
        try {
            $order->load(['items.product']);
            
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
                'shipping_cost' => floatval($order->shipping_cost ?? 0),
                'total_price' => floatval($order->total_price ?? 0),
                'confirmed_price' => $order->confirmed_price ? floatval($order->confirmed_price) : null,
                'scheduled_date' => $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : null,
                'attempts_count' => intval($order->attempts_count ?? 0),
                'daily_attempts_count' => intval($order->daily_attempts_count ?? 0),
                'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
                'updated_at' => $order->updated_at ? $order->updated_at->toISOString() : null,
                'last_attempt_at' => $order->last_attempt_at ? $order->last_attempt_at->toISOString() : null,
                'is_suspended' => $order->is_suspended ?? false,
                'suspension_reason' => $order->suspension_reason ?? '',
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
                            'stock' => intval($item->product->stock)
                        ] : null
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans formatOrderData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Réinitialise les compteurs journaliers si nécessaire
     */
    private function resetDailyCountersIfNeeded($admin)
    {
        try {
            $lastReset = AdminSetting::get('last_daily_reset_' . $admin->id);
            $today = now()->format('Y-m-d');
            
            if ($lastReset !== $today) {
                Log::info("Réinitialisation des compteurs journaliers pour l'admin {$admin->id}");
                
                Order::where('admin_id', $admin->id)
                    ->where('daily_attempts_count', '>', 0)
                    ->update(['daily_attempts_count' => 0]);
                
                AdminSetting::set('last_daily_reset_' . $admin->id, $today);
                
                Log::info("Compteurs journaliers réinitialisés avec succès pour l'admin {$admin->id}");
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réinitialisation des compteurs journaliers: " . $e->getMessage());
        }
    }

    /**
     * Traite une action sur une commande
     */
    public function processAction(Request $request, Order $order)
    {
        try {
            $request->validate([
                'action' => 'nullable|string',
                'notes' => 'required|string|min:3',
                'queue' => 'required|in:standard,dated,old,restock',
            ]);
            
            $admin = Auth::guard('admin')->user();
            if ($order->admin_id !== $admin->id) {
                return response()->json([
                    'error' => 'Accès refusé à cette commande'
                ], 403);
            }
            
            DB::beginTransaction();
            
            $action = $request->action;
            $notes = $request->notes;
            
            $historyNote = $admin->name . " a effectué l'action [{$action}] : {$notes}";
            
            switch ($action) {
                case 'call':
                    $this->recordCallAttempt($order, $historyNote);
                    break;
                    
                case 'confirm':
                    $this->validateConfirmation($request);
                    $this->confirmOrder($order, $request, $historyNote);
                    
                    if ($request->has('cart_items')) {
                        $this->updateOrderItems($order, $request->cart_items);
                    }
                    break;
    
                case 'cancel':
                    $order->status = 'annulée';
                    $order->save();
                    $order->recordHistory('annulation', $historyNote);
                    break;
                    
                case 'schedule':
                    $request->validate([
                        'scheduled_date' => 'required|date|after:today',
                    ]);
                    $order->status = 'datée';
                    $order->scheduled_date = $request->scheduled_date;
                    $order->attempts_count = 0; 
                    $order->daily_attempts_count = 0; 
                    $order->last_attempt_at = null; 
                    $order->save();
                    $order->recordHistory('datation', $historyNote);
                    break;
                
                case 'reactivate':
                    // NOUVELLE: Action pour réactiver une commande depuis l'onglet retour en stock
                    if ($request->queue !== 'restock') {
                        return response()->json([
                            'error' => 'Action de réactivation uniquement disponible dans la file retour en stock'
                        ], 400);
                    }
                    
                    if ($this->orderHasStockIssues($order)) {
                        return response()->json([
                            'error' => 'Impossible de réactiver: certains produits sont toujours en rupture de stock'
                        ], 400);
                    }
                    
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    $order->recordHistory('réactivation', $historyNote);
                    break;    

                default:
                    $this->updateOrderInfo($order, $request);
                    $order->recordHistory('modification', $historyNote);
                    break;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Commande traitée avec succès',
                'order_id' => $order->id,
                'action' => $action
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans processAction: ' . $e->getMessage(), [
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
     * NOUVELLE: Compter les commandes pour l'interface restock
     */
    private function getRestockCountForInterface($admin)
    {
        try {
            $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5);
            $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2);
            $delayHours = $this->getSetting('restock_delay_hours', 1);

            $query = Order::where('admin_id', $admin->id)
                ->with(['items.product'])
                ->where('is_suspended', true)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->where('attempts_count', '<', $maxTotalAttempts)
                ->where('daily_attempts_count', '<', $maxDailyAttempts)
                ->where(function($q) use ($delayHours) {
                    $q->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
                });

            $orders = $query->get();

            $count = $orders->filter(function($order) {
                return $this->orderCanBeReactivated($order);
            })->count();

            return $count;

        } catch (\Exception $e) {
            Log::error("Erreur dans getRestockCountForInterface: " . $e->getMessage());
            return 0;
        }
    }    


    /**
     * NOUVELLE: Actions groupées pour commandes suspendues - Réactivation
     */
    public function bulkReactivateSuspended(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'notes' => 'required|string|min:3|max:1000'
            ]);

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($validated['order_ids'] as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if (!$order || !$order->is_suspended) {
                    $errorCount++;
                    $errors[] = "Commande #{$orderId} non trouvée ou non suspendue";
                    continue;
                }

                // Vérifier que les stocks sont OK
                if ($this->orderHasStockIssues($order)) {
                    $errorCount++;
                    $errors[] = "Commande #{$orderId} a encore des problèmes de stock";
                    continue;
                }

                $order->is_suspended = false;
                $order->suspension_reason = null;
                $order->save();

                $order->recordHistory(
                    'réactivation',
                    "Commande réactivée par réactivation groupée par {$admin->name}. Raison: {$notes}",
                    ['bulk_reactivation' => true]
                );

                $successCount++;
            }

            DB::commit();

            $message = "Réactivation groupée terminée : {$successCount} réussie(s)";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} échec(s)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans bulkReactivateSuspended: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réactivation groupée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOUVELLE: Actions groupées pour commandes suspendues - Annulation
     */
    public function bulkCancelSuspended(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'notes' => 'required|string|min:3|max:1000'
            ]);

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($validated['order_ids'] as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if (!$order || !$order->is_suspended) {
                    $errorCount++;
                    $errors[] = "Commande #{$orderId} non trouvée ou non suspendue";
                    continue;
                }

                $order->status = 'annulée';
                $order->is_suspended = false;
                $order->suspension_reason = null;
                $order->save();

                $order->recordHistory(
                    'annulation',
                    "Commande annulée par annulation groupée par {$admin->name}. Raison: {$notes}",
                    ['bulk_cancellation' => true]
                );

                $successCount++;
            }

            DB::commit();

            $message = "Annulation groupée terminée : {$successCount} réussie(s)";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} échec(s)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans bulkCancelSuspended: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation groupée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valide les données pour une confirmation
     */
    private function validateConfirmation(Request $request)
    {
        $request->validate([
            'confirmed_price' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Confirme une commande
     */
    private function confirmOrder(Order $order, Request $request, $notes)
    {
        $updateData = [
            'status' => 'confirmée',
            'confirmed_price' => $request->confirmed_price,
            'shipping_cost' => $request->shipping_cost ?? 0,
        ];

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
        $order->recordHistory('confirmation', $notes);
    }

    /**
     * Met à jour les informations de base d'une commande
     */
    private function updateOrderInfo(Order $order, Request $request)
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
     * Enregistre une tentative d'appel
     */
    private function recordCallAttempt(Order $order, $notes)
    {
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        
        $order->last_attempt_at = now();
        $order->save();
        
        $order->recordHistory('tentative', $notes);
        
        $standardMaxAttempts = $this->getSetting('standard_max_total_attempts', 9);
        if ($order->status === 'nouvelle' && $order->attempts_count >= $standardMaxAttempts) {
            
            $previousStatus = $order->status;
            
            $order->status = 'ancienne';
            $order->save();
            
            $order->recordHistory(
                'changement_statut', 
                "Commande automatiquement passée en file ancienne après avoir atteint {$standardMaxAttempts} tentatives standard",
                [
                    'status_change' => [
                        'from' => $previousStatus, 
                        'to' => 'ancienne'
                    ],
                    'attempts_count' => $order->attempts_count,
                    'threshold_reached' => $standardMaxAttempts,
                    'auto_transition' => true
                ],
                $previousStatus,
                'ancienne'
            );
            
            Log::info("Commande {$order->id} automatiquement changée au statut 'ancienne' après {$order->attempts_count} tentatives (seuil: {$standardMaxAttempts})");
        }
    }

    /**
     * Met à jour les items de la commande
     */
    private function updateOrderItems(Order $order, $cartItems)
    {
        try {
            if (!is_array($cartItems)) {
                Log::warning('updateOrderItems: cartItems n\'est pas un tableau');
                return;
            }

            $order->items()->delete();
            
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
            
            $newTotal = $order->items()->sum('total_price');
            $order->update(['total_price' => $newTotal]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des items: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Route de test pour vérifier la connectivité
     */
    public function test()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            return response()->json([
                'success' => true,
                'message' => 'API fonctionnelle',
                'admin' => $admin ? $admin->name : 'Non authentifié',
                'timestamp' => now()->toISOString(),
                'debug' => [
                    'database_connected' => DB::connection()->getPdo() ? true : false,
                    'admin_settings_count' => AdminSetting::count(),
                    'orders_count' => $admin ? $admin->orders()->count() : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans test(): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
}