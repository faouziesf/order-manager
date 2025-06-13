<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DuplicateOrdersController extends Controller
{
    /**
     * Interface principale des commandes doubles
     */
    public function index()
    {
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
                ->where('status', 'nouvelle')
                ->count(),
            
            'merged_today' => Order::where('admin_id', $adminId)
                ->whereHas('history', function($q) {
                    $q->where('action', 'duplicate_merge')
                      ->whereDate('created_at', today());
                })
                ->count(),
            
            'pending_review' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->where('status', 'nouvelle')
                ->distinct('customer_phone')
                ->count('customer_phone'),
            
            'auto_merge_delay' => AdminSetting::get('duplicate_auto_merge_delay_hours', 2)
        ];

        return $stats;
    }

    /**
     * Récupérer la liste des commandes doubles pour DataTable
     */
    public function getDuplicates(Request $request)
    {
        $adminId = auth('admin')->id();
        
        $query = Order::select([
                'customer_phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(created_at) as latest_order_date'),
                DB::raw('MAX(id) as latest_order_id'),
                DB::raw('SUM(total_price) as total_amount')
            ])
            ->where('admin_id', $adminId)
            ->where('status', 'nouvelle')
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

        // Tri
        $sortField = $request->get('sort', 'latest_order_date');
        $sortDirection = $request->get('direction', 'desc');
        
        $validSortFields = ['latest_order_date', 'total_orders', 'total_amount', 'customer_phone'];
        if (in_array($sortField, $validSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $duplicates = $query->paginate($request->get('per_page', 15));

        // Enrichir les données
        $duplicates->getCollection()->transform(function ($duplicate) use ($adminId) {
            $orders = Order::where('admin_id', $adminId)
                ->where('customer_phone', $duplicate->customer_phone)
                ->where('status', 'nouvelle')
                ->where('is_duplicate', true)
                ->with(['items.product'])
                ->get();

            $duplicate->orders = $orders;
            $duplicate->latest_order = $orders->where('id', $duplicate->latest_order_id)->first();
            $duplicate->can_auto_merge = $this->canAutoMerge($duplicate->customer_phone);
            
            return $duplicate;
        });

        return response()->json($duplicates);
    }

    /**
     * Vérifier tous les doublons
     */
    public function checkAllDuplicates()
    {
        try {
            $adminId = auth('admin')->id();
            
            // Reset les tags existants
            Order::where('admin_id', $adminId)
                  ->update(['is_duplicate' => false, 'duplicate_group_id' => null]);
            
            // Scanner toutes les commandes
            $orders = Order::where('admin_id', $adminId)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->get();
            
            $duplicatesFound = 0;
            $processedPhones = [];
            
            foreach ($orders as $order1) {
                if (in_array($order1->customer_phone, $processedPhones)) {
                    continue;
                }
                
                $duplicateOrders = collect();
                
                foreach ($orders as $order2) {
                    if ($order1->id !== $order2->id && 
                        ($this->phoneMatches($order1->customer_phone, $order2->customer_phone) ||
                         $this->has8SuccessiveDigits($order1->customer_phone, $order2->customer_phone))) {
                        
                        $duplicateOrders->push($order2);
                    }
                }
                
                if ($duplicateOrders->count() > 0) {
                    $duplicateOrders->push($order1);
                    $groupId = 'DUP_' . time() . '_' . $order1->id;
                    
                    foreach ($duplicateOrders as $dupOrder) {
                        $dupOrder->update([
                            'is_duplicate' => true, 
                            'duplicate_group_id' => $groupId
                        ]);
                        
                        // Enregistrer dans l'historique
                        $dupOrder->recordHistory(
                            'duplicate_detected',
                            'Doublon détecté automatiquement lors de la vérification globale'
                        );
                    }
                    
                    $duplicatesFound += $duplicateOrders->count();
                    $processedPhones[] = $order1->customer_phone;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$duplicatesFound} commandes doubles détectées et marquées",
                'duplicates_found' => $duplicatesFound
            ]);
            
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
                    'message' => 'Moins de 2 commandes trouvées pour la fusion'
                ], 400);
            }

            $primaryOrder = $orders->first();
            $secondaryOrders = $orders->skip(1);

            // Fusionner les données
            $mergedNames = collect([$primaryOrder->customer_name]);
            $mergedAddresses = collect([$primaryOrder->customer_address]);
            $totalMergedPrice = $primaryOrder->total_price;

            foreach ($secondaryOrders as $secondaryOrder) {
                // Vérifier compatibilité
                if (!$this->canMergeOrders($primaryOrder, $secondaryOrder)) {
                    continue;
                }

                // Collecter noms et adresses
                if ($secondaryOrder->customer_name && 
                    !$mergedNames->contains($secondaryOrder->customer_name)) {
                    $mergedNames->push($secondaryOrder->customer_name);
                }
                
                if ($secondaryOrder->customer_address && 
                    !$mergedAddresses->contains($secondaryOrder->customer_address)) {
                    $mergedAddresses->push($secondaryOrder->customer_address);
                }

                // Fusionner les produits
                foreach ($secondaryOrder->items as $item) {
                    $existingItem = $primaryOrder->items->where('product_id', $item->product_id)->first();
                    
                    if ($existingItem) {
                        $existingItem->quantity += $item->quantity;
                        $existingItem->total_price += $item->total_price;
                        $existingItem->save();
                    } else {
                        $primaryOrder->items()->create([
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price
                        ]);
                    }
                }

                $totalMergedPrice += $secondaryOrder->total_price;
            }

            // Mettre à jour la commande principale
            $primaryOrder->update([
                'customer_name' => $mergedNames->filter()->implode(' / '),
                'customer_address' => $mergedAddresses->filter()->implode(' / '),
                'total_price' => $primaryOrder->items->sum('total_price') + $primaryOrder->shipping_cost,
                'reviewed_for_duplicates' => true,
                'notes' => ($primaryOrder->notes ? $primaryOrder->notes . "\n" : "") . 
                          "[FUSION " . now()->format('d/m/Y H:i') . "] " . 
                          ($request->note ?: "Fusion automatique de " . $secondaryOrders->count() . " commandes")
            ]);

            // Enregistrer l'historique de fusion
            $primaryOrder->recordHistory(
                'duplicate_merge',
                "Fusion avec commandes: " . $secondaryOrders->pluck('id')->implode(', '),
                [
                    'merged_orders_ids' => $secondaryOrders->pluck('id')->toArray(),
                    'total_price_before' => $primaryOrder->getOriginal('total_price'),
                    'total_price_after' => $primaryOrder->total_price,
                    'admin_note' => $request->note
                ]
            );

            // Supprimer les commandes secondaires
            foreach ($secondaryOrders as $secondaryOrder) {
                $secondaryOrder->delete();
            }

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
     */
    public function markAsReviewed(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string'
        ]);

        try {
            $adminId = auth('admin')->id();
            
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

            return response()->json([
                'success' => true,
                'message' => "{$updated} commandes marquées comme examinées",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
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
        
        // Toutes les commandes du client
        $orders = Order::where('admin_id', $adminId)
            ->where(function($q) use ($request) {
                $q->where('customer_phone', $request->customer_phone)
                  ->orWhere('customer_phone_2', $request->customer_phone);
            })
            ->with(['items.product', 'history'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Historique des notes de fusion
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

        return response()->json([
            'orders' => $orders,
            'merge_history' => $mergeHistory,
            'total_orders' => $orders->count(),
            'total_spent' => $orders->where('status', '!=', 'annulée')->sum('total_price')
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
                ->where(function($q) use ($phone) {
                    $q->where('customer_phone', $phone)
                    ->orWhere('customer_phone_2', $phone);
                })
                ->with(['items.product', 'history'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                abort(404, 'Aucune commande trouvée pour ce numéro');
            }

            $duplicateOrders = $orders->where('is_duplicate', true);
            
            // Calculer toutes les statistiques nécessaires
            $stats = [
                'total_orders' => $orders->count(),
                'duplicate_orders' => $duplicateOrders->count(),
                'total_spent' => $orders->where('status', '!=', 'annulée')->sum('total_price'),
                'cancelled_orders' => $orders->whereIn('status', ['annulée', 'cancelled', 'annulee'])->count(), // ← AJOUT DE CETTE LIGNE
                'confirmed_orders' => $orders->whereIn('status', ['confirmée', 'confirmed', 'confirmee'])->count(),
                'new_orders' => $orders->whereIn('status', ['nouvelle', 'pending', 'new'])->count(),
                'delivered_orders' => $orders->whereIn('status', ['livrée', 'completed', 'delivered', 'livree'])->count(),
                'first_order' => $orders->min('created_at'),
                'last_order' => $orders->max('created_at'),
                'avg_order_value' => $orders->count() > 0 ? $orders->sum('total_price') / $orders->count() : 0
            ];

            // Calculer les produits les plus commandés
            $topProducts = $this->calculateTopProducts($orders);

            return view('admin.duplicates.client-detail', compact('orders', 'phone', 'stats', 'topProducts'));

        } catch (\Exception $e) {
            \Log::error('Erreur dans clientDetail: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des données du client');
        }
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

        // Trier par quantité décroissante et prendre les 5 premiers
        uasort($topProducts, function($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        return array_slice($topProducts, 0, 5, true);
    }

    /**
     * Fusion sélective (page détaillée)
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
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins 2 commandes sont nécessaires pour la fusion'
                ], 400);
            }

            // Vérifier la compatibilité
            $primaryOrder = $orders->first();
            foreach ($orders->skip(1) as $order) {
                if (!$this->canMergeOrders($primaryOrder, $order)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Impossible de fusionner la commande #{$order->id} avec #{$primaryOrder->id}"
                    ], 400);
                }
            }

            // Effectuer la fusion (même logique que mergeOrders)
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
            $delayHours = AdminSetting::get('duplicate_auto_merge_delay_hours', 2);
            $cutoffTime = now()->subHours($delayHours);
            
            $adminId = auth('admin')->id();
            
            $duplicateGroups = Order::where('admin_id', $adminId)
                ->where('status', 'nouvelle')
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->where('created_at', '<=', $cutoffTime)
                ->select('customer_phone')
                ->groupBy('customer_phone')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $mergedCount = 0;
            
            foreach ($duplicateGroups as $group) {
                $orders = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $group->customer_phone)
                    ->where('status', 'nouvelle')
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                if ($orders->count() > 1) {
                    $primary = $orders->first();
                    $secondary = $orders->skip(1);
                    
                    $this->performMerge($primary, $secondary, "Fusion automatique après {$delayHours}h");
                    $mergedCount += $orders->count();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$mergedCount} commandes fusionnées automatiquement",
                'merged_count' => $mergedCount
            ]);
            
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
            'auto_merge_delay' => 'required|integer|min:1|max:168' // Max 1 semaine
        ]);

        AdminSetting::set('duplicate_auto_merge_delay_hours', $request->auto_merge_delay);

        return response()->json([
            'success' => true,
            'message' => 'Paramètres mis à jour avec succès'
        ]);
    }

    // ======== MÉTHODES UTILITAIRES ========

    /**
     * Vérifier si deux numéros de téléphone correspondent
     */
    private function phoneMatches($phone1, $phone2)
    {
        return $phone1 === $phone2;
    }

    /**
     * Vérifier si deux numéros ont 8 chiffres successifs identiques
     */
    private function has8SuccessiveDigits($phone1, $phone2)
    {
        $digits1 = preg_replace('/\D/', '', $phone1);
        $digits2 = preg_replace('/\D/', '', $phone2);
        
        if (strlen($digits1) < 8 || strlen($digits2) < 8) {
            return false;
        }
        
        for ($i = 0; $i <= strlen($digits1) - 8; $i++) {
            $substring = substr($digits1, $i, 8);
            if (strpos($digits2, $substring) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Vérifier si deux commandes peuvent être fusionnées
     */
    private function canMergeOrders($order1, $order2)
    {
        // Même admin
        if ($order1->admin_id !== $order2->admin_id) {
            return false;
        }
        
        // Statuts compatibles
        $compatibleStatuses = [
            ['nouvelle', 'nouvelle'],
            ['datée', 'datée'],
            ['nouvelle', 'datée'],
            ['datée', 'nouvelle']
        ];
        
        $statusPair = [$order1->status, $order2->status];
        
        foreach ($compatibleStatuses as $compatible) {
            if (($statusPair[0] === $compatible[0] && $statusPair[1] === $compatible[1]) ||
                ($statusPair[0] === $compatible[1] && $statusPair[1] === $compatible[0])) {
                return true;
            }
        }
        
        return false;
    }

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
            ->where('status', 'nouvelle')
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->orderBy('created_at', 'asc')
            ->first();
            
        return $oldestOrder && $oldestOrder->created_at <= $cutoffTime;
    }

    /**
     * Effectuer la fusion (méthode commune)
     */
    private function performMerge($primaryOrder, $secondaryOrders, $note = null)
    {
        $mergedNames = collect([$primaryOrder->customer_name]);
        $mergedAddresses = collect([$primaryOrder->customer_address]);

        foreach ($secondaryOrders as $secondaryOrder) {
            // Collecter noms et adresses
            if ($secondaryOrder->customer_name && 
                !$mergedNames->contains($secondaryOrder->customer_name)) {
                $mergedNames->push($secondaryOrder->customer_name);
            }
            
            if ($secondaryOrder->customer_address && 
                !$mergedAddresses->contains($secondaryOrder->customer_address)) {
                $mergedAddresses->push($secondaryOrder->customer_address);
            }

            // Fusionner les produits
            foreach ($secondaryOrder->items as $item) {
                $existingItem = $primaryOrder->items->where('product_id', $item->product_id)->first();
                
                if ($existingItem) {
                    $existingItem->quantity += $item->quantity;
                    $existingItem->total_price += $item->total_price;
                    $existingItem->save();
                } else {
                    $primaryOrder->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price
                    ]);
                }
            }
        }

        // Mettre à jour la commande principale
        $primaryOrder->update([
            'customer_name' => $mergedNames->filter()->implode(' / '),
            'customer_address' => $mergedAddresses->filter()->implode(' / '),
            'total_price' => $primaryOrder->items->sum('total_price') + $primaryOrder->shipping_cost,
            'reviewed_for_duplicates' => true,
            'notes' => ($primaryOrder->notes ? $primaryOrder->notes . "\n" : "") . 
                      "[FUSION " . now()->format('d/m/Y H:i') . "] " . 
                      ($note ?: "Fusion de " . $secondaryOrders->count() . " commandes")
        ]);

        // Enregistrer l'historique
        $primaryOrder->recordHistory(
            'duplicate_merge',
            "Fusion avec commandes: " . $secondaryOrders->pluck('id')->implode(', '),
            [
                'merged_orders_ids' => $secondaryOrders->pluck('id')->toArray(),
                'total_price_before' => $primaryOrder->getOriginal('total_price'),
                'total_price_after' => $primaryOrder->total_price,
                'admin_note' => $note
            ]
        );

        // Supprimer les commandes secondaires
        foreach ($secondaryOrders as $secondaryOrder) {
            $secondaryOrder->delete();
        }
    }
}