<?php

namespace App\Http\Controllers\Admin\Process;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuspendedOrderController extends Controller
{
    /**
     * Interface des commandes suspendues
     */
    public function index()
    {
        return view('admin.process.suspended.index');
    }

    /**
     * Obtenir les commandes suspendues
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

            $query = $admin->orders()
                ->with(['items.product'])
                ->where('is_suspended', true)
                ->whereIn('status', ['nouvelle', 'confirmée', 'datée']);

            // Appliquer les filtres
            $this->applyFilters($query, $request);

            // Tri
            $sortField = $request->get('sort', 'updated_at');
            $sortOrder = $request->get('order', 'desc');
            
            $allowedSortFields = ['id', 'created_at', 'updated_at', 'customer_name', 'status', 'priority', 'total_price'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortOrder);
            } else {
                $query->orderBy('updated_at', 'desc');
            }

            // Pagination
            $perPage = min($request->get('per_page', 20), 100);
            $orders = $query->paginate($perPage);

            if ($orders->count() > 0) {
                $ordersData = $orders->getCollection()->map(function($order) {
                    return $this->formatSuspendedOrderData($order);
                })->filter()->values()->toArray();
                
                return response()->json([
                    'hasOrders' => true,
                    'orders' => $ordersData,
                    'pagination' => [
                        'total' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'from' => $orders->firstItem(),
                        'to' => $orders->lastItem()
                    ]
                ]);
            }
            
            return response()->json([
                'hasOrders' => false,
                'message' => 'Aucune commande suspendue trouvée'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans SuspendedOrderController@getOrders: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(),
                'hasOrders' => false
            ], 500);
        }
    }

    /**
     * Compter les commandes suspendues
     */
    public function getCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $admin->orders()
                ->where('is_suspended', true)
                ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
                ->count();
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans SuspendedOrderController@getCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Réactiver une commande suspendue
     */
    public function reactivate(Request $request, Order $order)
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
                'notes' => 'required|string|min:3|max:1000',
                'check_stock' => 'boolean'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            // Vérifier le stock si demandé
            if ($validated['check_stock'] ?? true) {
                $stockIssues = $this->checkOrderStockStatus($order);
                
                if ($stockIssues['hasIssues']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de réactiver: certains produits sont toujours problématiques',
                        'stock_issues' => $stockIssues['issues']
                    ], 400);
                }
            }

            // Réactiver la commande
            $order->is_suspended = false;
            $order->suspension_reason = null;
            $order->save();
            
            $order->recordHistory(
                'réactivation',
                "Commande réactivée depuis l'interface des suspendues par {$admin->name}. Raison: {$notes}",
                ['reactivated_from_suspended_interface' => true]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande réactivée avec succès',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans SuspendedOrderController@reactivate: ' . $e->getMessage(), [
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
     * Annuler une commande suspendue
     */
    public function cancel(Request $request, Order $order)
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

            // Annuler la commande
            $order->status = 'annulée';
            $order->is_suspended = false;
            $order->suspension_reason = null;
            $order->save();
            
            $order->recordHistory(
                'annulation',
                "Commande annulée depuis l'interface des suspendues par {$admin->name}. Raison: {$notes}",
                ['cancelled_from_suspended_interface' => true]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande annulée avec succès',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans SuspendedOrderController@cancel: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réactivation en lot
     */
    public function bulkReactivate(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'notes' => 'required|string|min:3|max:1000',
                'check_stock' => 'boolean'
            ]);

            $admin = Auth::guard('admin')->user();
            $orderIds = $validated['order_ids'];
            $notes = $validated['notes'];
            $checkStock = $validated['check_stock'] ?? true;

            DB::beginTransaction();

            $successCount = 0;
            $failedOrders = [];

            foreach ($orderIds as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if (!$order || !$order->is_suspended) {
                    $failedOrders[] = "Commande #{$orderId} non suspendue";
                    continue;
                }

                // Vérifier le stock si demandé
                if ($checkStock) {
                    $stockIssues = $this->checkOrderStockStatus($order);
                    if ($stockIssues['hasIssues']) {
                        $failedOrders[] = "Commande #{$orderId} a encore des problèmes de stock";
                        continue;
                    }
                }

                // Réactiver
                $order->is_suspended = false;
                $order->suspension_reason = null;
                $order->save();
                
                $order->recordHistory(
                    'réactivation',
                    "Commande réactivée en lot depuis l'interface des suspendues par {$admin->name}. Raison: {$notes}",
                    ['bulk_reactivated_from_suspended_interface' => true]
                );
                
                $successCount++;
            }

            DB::commit();

            $message = "{$successCount} commande(s) réactivée(s) avec succès";
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
            Log::error('Erreur dans SuspendedOrderController@bulkReactivate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réactivation en lot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annulation en lot
     */
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
                
                if ($order && $order->is_suspended) {
                    $order->status = 'annulée';
                    $order->is_suspended = false;
                    $order->suspension_reason = null;
                    $order->save();
                    
                    $order->recordHistory(
                        'annulation',
                        "Commande annulée en lot depuis l'interface des suspendues par {$admin->name}. Raison: {$notes}",
                        ['bulk_cancelled_from_suspended_interface' => true]
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
            Log::error('Erreur dans SuspendedOrderController@bulkCancel: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation en lot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HELPERS
     */

    /**
     * Appliquer les filtres à la requête
     */
    private function applyFilters($query, $request)
    {
        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_phone_2', 'like', "%{$search}%")
                  ->orWhere('suspension_reason', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par priorité
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filtre par date de création
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtre par date de suspension
        if ($request->filled('suspended_from')) {
            $query->whereDate('updated_at', '>=', $request->suspended_from);
        }

        if ($request->filled('suspended_to')) {
            $query->whereDate('updated_at', '<=', $request->suspended_to);
        }

        // Filtre par raison de suspension
        if ($request->filled('suspension_reason')) {
            $query->where('suspension_reason', 'like', '%' . $request->suspension_reason . '%');
        }

        // Filtre par montant
        if ($request->filled('amount_min')) {
            $query->where('total_price', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total_price', '<=', $request->amount_max);
        }
    }

    /**
     * Formater les données d'une commande suspendue
     */
    private function formatSuspendedOrderData($order)
    {
        try {
            if (!$order || !$order->id) {
                return null;
            }

            // Analyser le statut des stocks actuel
            $stockStatus = $this->checkOrderStockStatus($order);

            return [
                'id' => $order->id,
                'status' => $order->status ?? 'nouvelle',
                'priority' => $order->priority ?? 'normale',
                'customer_name' => $order->customer_name ?? '',
                'customer_phone' => $order->customer_phone ?? '',
                'customer_phone_2' => $order->customer_phone_2 ?? '',
                'customer_address' => $order->customer_address ?? '',
                'total_price' => floatval($order->total_price ?? 0),
                'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
                'updated_at' => $order->updated_at ? $order->updated_at->toISOString() : null,
                'suspension_reason' => $order->suspension_reason ?? '',
                'suspension_date' => $order->updated_at ? $order->updated_at->toISOString() : null,
                'items_count' => $order->items ? $order->items->count() : 0,
                'stock_status' => $stockStatus,
                'can_reactivate' => !$stockStatus['hasIssues'],
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
     * Vérifier le statut des stocks d'une commande
     */
    private function checkOrderStockStatus($order)
    {
        $issues = [];
        $hasIssues = false;

        foreach ($order->items as $item) {
            $itemIssues = [];

            if (!$item->product) {
                $hasIssues = true;
                $itemIssues[] = 'Produit supprimé';
            } else {
                if (!$item->product->is_active) {
                    $hasIssues = true;
                    $itemIssues[] = 'Produit inactif';
                }
                
                if ($item->product->stock < $item->quantity) {
                    $hasIssues = true;
                    $itemIssues[] = "Stock insuffisant ({$item->product->stock} disponible, {$item->quantity} demandé)";
                }
            }

            if (!empty($itemIssues)) {
                $issues[] = [
                    'item_id' => $item->id,
                    'product_name' => $item->product ? $item->product->name : 'Produit supprimé',
                    'issues' => $itemIssues
                ];
            }
        }

        return [
            'hasIssues' => $hasIssues,
            'issues' => $issues
        ];
    }
}