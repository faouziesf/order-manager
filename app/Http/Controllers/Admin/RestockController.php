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

class RestockController extends Controller
{
    use ProcessTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('view-restock', auth('admin')->user())) {
                abort(403, 'Accès non autorisé à l\'interface de retour en stock');
            }
            return $next($request);
        });
    }

    /**
     * Interface pour le retour en stock
     */
    public function index()
    {
        return view('admin.process.restock');
    }

    /**
     * Obtenir les commandes pour le retour en stock
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
     * Compter les commandes prêtes pour réactivation
     */
    public function getCount()
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
            Log::error('Erreur dans getCount: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement du compteur',
                'count' => 0
            ], 500);
        }
    }

    /**
     * Réactiver une commande depuis l'interface restock
     */
    public function reactivateOrder(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'notes' => 'required|string|min:3|max:1000'
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $notes = $validated['notes'];

            // Vérifier que la commande est suspendue
            if (!$order->is_suspended) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande n\'est pas suspendue'
                ], 400);
            }

            // Vérifier que les stocks sont maintenant OK
            if ($this->orderHasStockIssues($order)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de réactiver: certains produits sont toujours en rupture de stock ou inactifs'
                ], 400);
            }

            $order->is_suspended = false;
            $order->suspension_reason = null;
            $order->status = 'nouvelle';
            $order->attempts_count = 0;
            $order->daily_attempts_count = 0;
            $order->last_attempt_at = null;
            $order->save();

            $order->recordHistory(
                'réactivation',
                "Commande réactivée depuis l'interface retour en stock par {$admin->name}. Raison: {$notes}",
                ['reactivated_from_restock_interface' => true]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande réactivée avec succès',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans reactivateOrder: ' . $e->getMessage(), [
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
     * Actions groupées - Réactivation multiple
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
                $order->status = 'nouvelle';
                $order->attempts_count = 0;
                $order->daily_attempts_count = 0;
                $order->last_attempt_at = null;
                $order->save();

                $order->recordHistory(
                    'réactivation',
                    "Commande réactivée par réactivation groupée depuis retour en stock par {$admin->name}. Raison: {$notes}",
                    ['bulk_reactivation_from_restock' => true, 'notes' => $notes]
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
     * Obtenir les statistiques de retour en stock
     */
    public function getStats()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Commandes suspendues totales
            $totalSuspended = $admin->orders()
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->count();

            // Commandes prêtes pour réactivation
            $suspendedOrders = $admin->orders()
                ->with(['items.product'])
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->get();

            $readyForReactivation = $suspendedOrders->filter(function($order) {
                return $this->orderCanBeReactivated($order);
            })->count();

            // Commandes avec problèmes de stock
            $withStockIssues = $suspendedOrders->filter(function($order) {
                return $this->orderHasStockIssues($order);
            })->count();

            // Statistiques par raison de suspension
            $suspensionReasons = $admin->orders()
                ->where('is_suspended', true)
                ->whereNotIn('status', ['annulée', 'livrée'])
                ->selectRaw('suspension_reason, COUNT(*) as count')
                ->groupBy('suspension_reason')
                ->pluck('count', 'suspension_reason')
                ->toArray();

            return response()->json([
                'total_suspended' => $totalSuspended,
                'ready_for_reactivation' => $readyForReactivation,
                'with_stock_issues' => $withStockIssues,
                'suspension_reasons' => $suspensionReasons,
                'percentage_ready' => $totalSuspended > 0 ? round(($readyForReactivation / $totalSuspended) * 100, 1) : 0,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getStats: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }

    /**
     * Helper: Vérifier si une commande peut être réactivée
     */
    private function orderCanBeReactivated($order)
    {
        return !$this->orderHasStockIssues($order);
    }

    /**
     * Helper pour formater les données des commandes pour le retour en stock
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
}