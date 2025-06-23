<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\AdminSetting;
use App\Traits\DuplicateDetectionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DuplicateOrdersController extends Controller
{
    use DuplicateDetectionTrait;

    /**
     * Interface principale des commandes doubles
     */
    public function index()
    {
        // Nettoyage automatique au chargement
        $this->autoCleanSingleOrders(auth('admin')->id());
        
        $stats = $this->getDashboardStats();
        return view('admin.duplicates.index', compact('stats'));
    }

    /**
     * Récupérer les statistiques du dashboard
     */
    public function getDashboardStats()
    {
        $adminId = auth('admin')->id();
        
        $stats = [
            'total_duplicates' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->count(),
            
            'mergeable_duplicates' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->count(),
            
            'unique_clients' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->distinct('customer_phone')
                ->count('customer_phone'),
            
            'merged_today' => Order::where('admin_id', $adminId)
                ->whereHas('history', function($q) {
                    $q->where('action', 'duplicate_merge')
                      ->whereDate('created_at', today());
                })
                ->count(),
            
            'auto_merge_delay' => AdminSetting::get('duplicate_auto_merge_delay_hours', 2),
        ];
        
        $stats['non_mergeable_duplicates'] = $stats['total_duplicates'] - $stats['mergeable_duplicates'];

        return $stats;
    }

    /**
     * Récupérer la liste des commandes doubles pour DataTable
     */
    public function getDuplicates(Request $request)
    {
        $adminId = auth('admin')->id();
        
        // Nettoyage automatique avant affichage
        $this->autoCleanSingleOrders($adminId);
        
        $query = Order::select([
                'customer_phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(created_at) as latest_order_date'),
                DB::raw('MAX(id) as latest_order_id'),
                DB::raw('SUM(total_price) as total_amount'),
                DB::raw('GROUP_CONCAT(DISTINCT status ORDER BY status) as statuses')
            ])
            ->where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->groupBy('customer_phone')
            ->having('total_orders', '>', 1);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('customer_phone', 'like', "%{$search}%");
        }

        if ($request->filled('min_orders')) {
            $query->having('total_orders', '>=', $request->get('min_orders'));
        }
        
        if ($request->filled('duplicate_type')) {
            $type = $request->get('duplicate_type');
            if ($type === 'mergeable') {
                $query->whereExists(function($subQuery) use ($adminId) {
                    $subQuery->select(DB::raw(1))
                        ->from('orders as o2')
                        ->whereRaw('o2.customer_phone = orders.customer_phone')
                        ->where('o2.admin_id', $adminId)
                        ->whereIn('o2.status', ['nouvelle', 'datée'])
                        ->where('o2.is_duplicate', true)
                        ->where('o2.reviewed_for_duplicates', false);
                });
            } elseif ($type === 'non_mergeable') {
                $query->whereNotExists(function($subQuery) use ($adminId) {
                    $subQuery->select(DB::raw(1))
                        ->from('orders as o2')
                        ->whereRaw('o2.customer_phone = orders.customer_phone')
                        ->where('o2.admin_id', $adminId)
                        ->whereIn('o2.status', ['nouvelle', 'datée'])
                        ->where('o2.is_duplicate', true)
                        ->where('o2.reviewed_for_duplicates', false);
                });
            }
        }

        // Tri
        $sortField = $request->get('sort', 'latest_order_date');
        $sortDirection = $request->get('direction', 'desc');
        
        $validSortFields = ['latest_order_date', 'total_orders', 'total_amount', 'customer_phone'];
        if (in_array($sortField, $validSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        try {
            $duplicates = $query->paginate($request->get('per_page', 15));
            
            $duplicates->getCollection()->transform(function ($duplicate) use ($adminId) {
                $orders = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $duplicate->customer_phone)
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false)
                    ->with(['items.product'])
                    ->get();

                $duplicate->orders = $orders;
                $duplicate->latest_order = $orders->where('id', $duplicate->latest_order_id)->first();
                $duplicate->can_auto_merge = $this->canAutoMerge($duplicate->customer_phone);
                
                $mergeableOrders = $orders->whereIn('status', ['nouvelle', 'datée']);
                $nonMergeableOrders = $orders->whereNotIn('status', ['nouvelle', 'datée']);
                
                $duplicate->mergeable_orders = $mergeableOrders->values()->all();
                $duplicate->non_mergeable_orders = $nonMergeableOrders->values()->all();
                $duplicate->can_merge = $mergeableOrders->count() > 1;
                $duplicate->has_old_orders = $nonMergeableOrders->count() > 0;
                
                return $duplicate;
            });
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la requête des doublons: ' . $e->getMessage());
            
            return response()->json([
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'from' => 0,
                'to' => 0
            ]);
        }

        return response()->json($duplicates);
    }

    /**
     * Vérifier tous les doublons
     * LOGIQUE SIMPLIFIÉE: Regrouper par téléphone, si plus d'une → toutes marquées comme double
     */
    public function checkAllDuplicates()
    {
        try {
            $adminId = auth('admin')->id();
            
            $result = $this->scanAllOrdersForDuplicates($adminId);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "{$result['duplicates_found']} commandes doubles détectées dans {$result['groups_created']} groupes",
                    'duplicates_found' => $result['duplicates_found'],
                    'groups_created' => $result['groups_created']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la vérification: ' . $result['error']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des doublons: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fusionner les commandes
     */
    public function mergeOrders(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();
            
            $adminId = auth('admin')->id();
            
            // Seulement les commandes fusionnables
            $orders = Order::where('admin_id', $adminId)
                ->where('customer_phone', $request->customer_phone)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Moins de 2 commandes fusionnables trouvées'
                ], 400);
            }

            $primaryOrder = $orders->first();
            $secondaryOrders = $orders->skip(1);

            // Fusionner
            $this->performMerge($primaryOrder, $secondaryOrders, $request->note);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commandes fusionnées avec succès',
                'merged_order_id' => $primaryOrder->id,
                'merged_count' => $secondaryOrders->count() + 1
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la fusion: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fusion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer comme examiné
     * LOGIQUE SIMPLIFIÉE: Marquer toutes les commandes du client comme examinées
     */
    public function markAsReviewed(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            
            $adminId = auth('admin')->id();
            
            // Marquer toutes les commandes du client comme examinées
            $updated = Order::where('admin_id', $adminId)
                ->where('customer_phone', $request->customer_phone)
                ->where('is_duplicate', true)
                ->update(['reviewed_for_duplicates' => true]);

            // Enregistrer dans l'historique
            $orders = Order::where('admin_id', $adminId)
                ->where('customer_phone', $request->customer_phone)
                ->where('is_duplicate', true)
                ->get();

            foreach ($orders as $order) {
                $order->recordHistory(
                    'duplicate_review',
                    'Commandes marquées comme examinées pour doublons'
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updated} commandes marquées comme examinées",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du marquage: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'historique d'un client
     */
    public function getClientHistory(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string'
        ]);

        $adminId = auth('admin')->id();
        
        $orders = Order::where('admin_id', $adminId)
            ->where('customer_phone', $request->customer_phone)
            ->with(['items.product', 'history'])
            ->orderBy('created_at', 'desc')
            ->get();

        $mergeHistory = collect();
        foreach ($orders as $order) {
            if ($order->notes && str_contains($order->notes, '[FUSION')) {
                $mergeHistory->push([
                    'order_id' => $order->id,
                    'date' => $order->updated_at,
                    'note' => $order->notes
                ]);
            }
        }

        $statusBreakdown = $orders->groupBy('status')->map(function($group) {
            return [
                'count' => $group->count(),
                'total_value' => $group->sum('total_price')
            ];
        })->toArray();

        return response()->json([
            'orders' => $orders,
            'merge_history' => $mergeHistory,
            'total_orders' => $orders->count(),
            'total_spent' => $orders->whereNotIn('status', ['annulée', 'cancelled'])->sum('total_price'),
            'status_breakdown' => $statusBreakdown,
            'duplicate_orders' => $orders->where('is_duplicate', true)->count(),
            'mergeable_orders' => $orders->whereIn('status', ['nouvelle', 'datée'])->where('is_duplicate', true)->count()
        ]);
    }

    /**
     * Page détaillée pour un client
     */
    public function clientDetail($phone)
    {
        try {
            $adminId = auth('admin')->id();
            
            $orders = Order::where('admin_id', $adminId)
                ->where('customer_phone', $phone)
                ->with(['items.product', 'history'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                abort(404, 'Aucune commande trouvée pour ce numéro');
            }

            $duplicateOrders = $orders->where('is_duplicate', true);
            $mergeableOrders = $duplicateOrders->whereIn('status', ['nouvelle', 'datée']);
            
            $stats = [
                'total_orders' => $orders->count(),
                'duplicate_orders' => $duplicateOrders->count(),
                'mergeable_duplicates' => $mergeableOrders->count(),
                'non_mergeable_duplicates' => $duplicateOrders->count() - $mergeableOrders->count(),
                'total_spent' => $orders->whereNotIn('status', ['annulée', 'cancelled', 'annulee'])->sum('total_price'),
                'cancelled_orders' => $orders->whereIn('status', ['annulée', 'cancelled', 'annulee'])->count(),
                'confirmed_orders' => $orders->whereIn('status', ['confirmée', 'confirmed', 'confirmee'])->count(),
                'new_orders' => $orders->whereIn('status', ['nouvelle', 'pending', 'new'])->count(),
                'delivered_orders' => $orders->whereIn('status', ['livrée', 'completed', 'delivered', 'livree'])->count(),
                'dated_orders' => $orders->whereIn('status', ['datée', 'scheduled', 'datee'])->count(),
                'first_order' => $orders->min('created_at'),
                'last_order' => $orders->max('created_at'),
                'avg_order_value' => $orders->count() > 0 ? $orders->sum('total_price') / $orders->count() : 0,
                
                'status_breakdown' => $orders->groupBy('status')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total_value' => $group->sum('total_price'),
                        'duplicate_count' => $group->where('is_duplicate', true)->count()
                    ];
                })->toArray()
            ];

            $topProducts = $this->calculateTopProducts($orders);

            return view('admin.duplicates.client-detail', compact('orders', 'phone', 'stats', 'topProducts'));

        } catch (\Exception $e) {
            Log::error('Erreur dans clientDetail: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des données du client');
        }
    }

    /**
     * Fusion sélective
     */
    public function selectiveMerge(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:2',
            'order_ids.*' => 'required|integer|exists:orders,id',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();
            
            $adminId = auth('admin')->id();
            $orders = Order::where('admin_id', $adminId)
                ->whereIn('id', $request->order_ids)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins 2 commandes fusionnables sont nécessaires'
                ], 400);
            }

            $primaryOrder = $orders->first();
            $secondaryOrders = $orders->skip(1);
            
            $this->performMerge($primaryOrder, $secondaryOrders, $request->note);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fusion sélective réalisée avec succès',
                'merged_order_id' => $primaryOrder->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la fusion sélective: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fusion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler une commande double
     */
    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $adminId = auth('admin')->id();
            $order = Order::where('admin_id', $adminId)
                ->where('id', $request->order_id)
                ->firstOrFail();

            $order->update([
                'status' => 'annulée',
                'reviewed_for_duplicates' => true,
                'notes' => ($order->notes ? $order->notes . "\n" : "") . 
                          "[ANNULATION " . now()->format('d/m/Y H:i') . "] " . 
                          ($request->reason ?: "Annulée depuis l'interface des doublons")
            ]);

            $order->recordHistory(
                'duplicate_cancel',
                'Commande annulée depuis l\'interface des doublons',
                ['reason' => $request->reason]
            );

            // Vérifier si il faut nettoyer le marquage doublon
            $this->autoCleanSingleOrders($adminId, $order->customer_phone);

            return response()->json([
                'success' => true,
                'message' => 'Commande annulée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fusion automatique basée sur délai
     */
    public function autoMergeDuplicates()
    {
        try {
            $adminId = auth('admin')->id();
            $result = $this->autoMergeDuplicates($adminId);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "{$result['merged_count']} commandes fusionnées automatiquement",
                    'merged_count' => $result['merged_count']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la fusion automatique: ' . $result['error']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la fusion automatique: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fusion automatique: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'auto_merge_delay' => 'required|integer|min:1|max:168'
        ]);

        AdminSetting::set('duplicate_auto_merge_delay_hours', $request->auto_merge_delay);

        return response()->json([
            'success' => true,
            'message' => 'Paramètres mis à jour avec succès'
        ]);
    }

    // ======== MÉTHODES UTILITAIRES ========

    /**
     * Vérifier si un groupe peut être fusionné automatiquement
     */
    private function canAutoMerge($customerPhone)
    {
        $delayHours = AdminSetting::get('duplicate_auto_merge_delay_hours', 2);
        $cutoffTime = now()->subHours($delayHours);
        
        $adminId = auth('admin')->id();
        
        $oldestOrder = Order::where('admin_id', $adminId)
            ->where('customer_phone', $customerPhone)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->orderBy('created_at', 'asc')
            ->first();
            
        return $oldestOrder && $oldestOrder->created_at <= $cutoffTime;
    }

    /**
     * Calculer les produits les plus commandés
     */
    private function calculateTopProducts($orders)
    {
        $topProducts = [];
        
        foreach ($orders as $order) {
            if (!$order->items) continue;
            
            foreach ($order->items as $item) {
                if (!$item->product) continue;
                
                $productName = $item->product->name ?? 'Produit supprimé';
                
                if (!isset($topProducts[$productName])) {
                    $topProducts[$productName] = [
                        'quantity' => 0,
                        'orders_count' => 0,
                        'total_value' => 0,
                    ];
                }
                
                $topProducts[$productName]['quantity'] += (int) $item->quantity;
                $topProducts[$productName]['orders_count']++;
                $topProducts[$productName]['total_value'] += (float) $item->total_price;
            }
        }

        uasort($topProducts, function($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        return array_slice($topProducts, 0, 5, true);
    }
}