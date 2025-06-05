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

class ExaminationController extends Controller
{
    use ProcessTrait;

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('view-examination', auth('admin')->user())) {
                abort(403, 'Accès non autorisé à l\'interface d\'examen');
            }
            return $next($request);
        });
    }

    /**
     * Interface d'examen des commandes avec problèmes de stock
     */
    public function index()
    {
        return view('admin.process.examination');
    }

    /**
     * Obtenir les commandes pour l'interface d'examen
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

            // Obtenir les commandes avec problèmes de stock (mais pas suspendues)
            $orders = $this->findOrdersWithStockIssues($admin, false);
            
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
     * Compter les commandes avec problèmes de stock
     */
    public function getCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $this->countOrdersWithStockIssues($admin, false);
            
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
     * Actions groupées - Division
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

                // Vérifier que la commande peut être divisée
                $stockIssues = $this->analyzeOrderStockIssues($order);
                
                if (!$stockIssues['hasIssues'] || $stockIssues['availableItems']->count() === 0) {
                    $errorCount++;
                    $errors[] = ['id' => $orderId, 'reason' => "Commande #{$orderId} ne peut pas être divisée"];
                    continue;
                }

                try {
                    // Processus de division identique à splitOrder
                    $newOrder = $order->replicate();
                    $newOrder->status = 'nouvelle';
                    $newOrder->attempts_count = 0;
                    $newOrder->daily_attempts_count = 0;
                    $newOrder->last_attempt_at = null;
                    $newOrder->is_suspended = false;
                    $newOrder->suspension_reason = null;
                    $newOrder->save();

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

                    foreach ($stockIssues['availableItems'] as $item) {
                        $item->delete();
                    }

                    $originalTotalPrice = $stockIssues['unavailableItems']->sum('total_price');
                    $order->total_price = $originalTotalPrice;
                    $order->is_suspended = true;
                    $order->suspension_reason = 'Produits en rupture de stock ou inactifs';
                    $order->save();

                    $order->recordHistory(
                        'division',
                        "Commande divisée par division groupée par {$admin->name}. Nouvelle commande #{$newOrder->id} créée. Raison: {$notes}",
                        ['bulk_division' => true, 'new_order_id' => $newOrder->id]
                    );

                    $processedOrderIds[] = $orderId;
                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = ['id' => $orderId, 'reason' => "Erreur lors de la division: " . $e->getMessage()];
                }
            }

            DB::commit();

            $message = "Division groupée terminée : {$successCount} réussie(s)";
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
            Log::error('Erreur dans bulkSplit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la division groupée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actions groupées - Annulation
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
     * Actions groupées - Suspension
     */
    public function bulkSuspend(Request $request)
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

                $order->is_suspended = true;
                $order->suspension_reason = $notes;
                $order->save();

                $order->recordHistory(
                    'suspension',
                    "Commande suspendue par suspension groupée par {$admin->name}. Raison: {$notes}",
                    ['bulk_suspension' => true, 'notes' => $notes]
                );

                $processedOrderIds[] = $orderId;
                $successCount++;
            }

            DB::commit();

            $message = "Suspension groupée terminée : {$successCount} réussie(s)";
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
            Log::error('Erreur dans bulkSuspend: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suspension groupée: ' . $e->getMessage()
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
}