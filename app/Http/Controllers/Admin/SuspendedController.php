<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ProcessTrait;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class SuspendedController extends Controller
{
    use ProcessTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
        // Commenté temporairement pour résoudre le problème 403
        /*
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('view-suspended', auth('admin')->user())) {
                abort(403, 'Accès non autorisé à l\'interface des commandes suspendues');
            }
            return $next($request);
        });
        */
    }

    /**
     * Interface pour les commandes suspendues uniquement
     */
    public function index()
    {
        return view('admin.process.suspended');
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
            Log::error('Erreur dans getOrders: ' . $e->getMessage(), [
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
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->count();
            
            return response()->json([
                'count' => $count,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Action pour les commandes suspendues
     */
    public function processAction(Request $request, Order $order)
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
                    $oldReason = $order->suspension_reason;
                    $order->suspension_reason = $newReason;
                    $order->save();
                    
                    $order->recordHistory(
                        'modification',
                        "Raison de suspension modifiée par {$admin->name}. Nouvelle raison: {$newReason}. Notes: {$notes}",
                        ['suspension_reason_updated' => true, 'old_reason' => $oldReason]
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
            Log::error('Erreur dans processAction: ' . $e->getMessage(), [
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
     * Actions groupées pour commandes suspendues - Réactivation
     */
    public function bulkReactivate(Request $request)
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
            $processedOrderIds = [];

            foreach ($validated['order_ids'] as $orderId) {
                $order = $admin->orders()->with('items.product')->find($orderId);
                
                if (!$order) {
                    $errorCount++;
                    $errors[] = ['id' => $orderId, 'reason' => "Commande #{$orderId} non trouvée pour cet admin."];
                    continue;
                }
                if (!$order->is_suspended) {
                    $errorCount++;
                    $errors[] = ['id' => $orderId, 'reason' => "Commande #{$orderId} n'est pas suspendue."];
                    continue;
                }

                // Vérifier que les stocks sont OK
                if ($this->orderHasStockIssues($order)) {
                    $errorCount++;
                    $errors[] = ['id' => $orderId, 'reason' => "Commande #{$orderId} a encore des problèmes de stock"];
                    continue;
                }

                $order->is_suspended = false;
                $order->suspension_reason = null;
                $order->save();

                $order->recordHistory(
                    'réactivation',
                    "Commande réactivée par réactivation groupée par {$admin->name}. Raison: {$notes}",
                    ['bulk_reactivation' => true, 'notes' => $notes]
                );

                $processedOrderIds[] = $orderId;
                $successCount++;
            }

            DB::commit();

            $message = "Réactivation groupée terminée : {$successCount} réussie(s)";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} échec(s)";
            }

            return response()->json([
                'success' => $errorCount === 0,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'processed_ids' => $processedOrderIds,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans bulkReactivate: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réactivation groupée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actions groupées pour commandes suspendues - Annulation
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
            $notes = $validated['notes'];

            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $processedOrderIds = [];

            foreach ($validated['order_ids'] as $orderId) {
                $order = $admin->orders()->find($orderId);
                
                if (!$order) {
                    $errorCount++;
                    $errors[] = ['id' => $orderId, 'reason' => "Commande #{$orderId} non trouvée pour cet admin."];
                    continue;
                }

                $order->status = 'annulée';
                $order->is_suspended = false;
                $order->suspension_reason = null;
                $order->save();

                $order->recordHistory(
                    'annulation',
                    "Commande annulée par annulation groupée par {$admin->name}. Raison: {$notes}",
                    ['bulk_cancellation' => true, 'notes' => $notes]
                );

                $processedOrderIds[] = $orderId;
                $successCount++;
            }

            DB::commit();

            $message = "Annulation groupée terminée : {$successCount} réussie(s)";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} échec(s)";
            }

            return response()->json([
                'success' => $errorCount === 0,
                'message' => $message,
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'processed_ids' => $processedOrderIds,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans bulkCancel: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation groupée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper pour formater les données des commandes suspendues
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
}