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
        // Nettoyer les données incohérentes au chargement de la page
        $this->cleanInconsistentData();
        
        $stats = $this->getDashboardStats();
        return view('admin.duplicates.index', compact('stats'));
    }
    
    /**
     * NOUVELLE MÉTHODE: Nettoyer les données incohérentes
     * Marque automatiquement comme examinés les clients qui ont au moins une commande examinée/fusionnée
     */
    private function cleanInconsistentData()
    {
        try {
            $adminId = auth('admin')->id();
            
            // NOUVELLE LOGIQUE PRINCIPALE: Auto-marquer les clients avec des commandes examinées
            $this->autoMarkReviewedClients($adminId);
            
            // Trouver les clients où toutes les commandes sont marquées comme examinées
            // mais qui apparaissent encore dans les requêtes (problème de cache ou d'incohérence)
            $inconsistentPhones = DB::table('orders')
                ->select('customer_phone')
                ->where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->groupBy('customer_phone')
                ->havingRaw('SUM(CASE WHEN reviewed_for_duplicates = 0 THEN 1 ELSE 0 END) = 0')
                ->havingRaw('COUNT(*) > 0')
                ->pluck('customer_phone');
            
            if ($inconsistentPhones->count() > 0) {
                \Log::info("Nettoyage des données incohérentes pour l'admin {$adminId}", [
                    'phones' => $inconsistentPhones->toArray()
                ]);
                
                // Ces clients ne devraient plus apparaître dans les listes de doublons
                // Marquer explicitement toutes leurs commandes comme examinées
                foreach ($inconsistentPhones as $phone) {
                    Order::where('admin_id', $adminId)
                        ->where('customer_phone', $phone)
                        ->where('is_duplicate', true)
                        ->update(['reviewed_for_duplicates' => true]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors du nettoyage des données incohérentes: ' . $e->getMessage());
        }
    }
    
    /**
     * NOUVELLE MÉTHODE PRINCIPALE: Auto-marquer les clients avec des commandes examinées
     * Si un client a au moins une commande double examinée/fusionnée, 
     * marquer TOUTES ses commandes doubles comme examinées
     */
    private function autoMarkReviewedClients($adminId)
    {
        try {
            // ÉTAPE 1: Trouver les clients qui ont au moins une commande double examinée ou fusionnée
            $clientsWithReviewedOrders = DB::table('orders as o1')
                ->select('o1.customer_phone')
                ->where('o1.admin_id', $adminId)
                ->where('o1.is_duplicate', true)
                ->where(function($query) {
                    $query->where('o1.reviewed_for_duplicates', true) // Déjà examinée
                          ->orWhereExists(function($subQuery) { // Ou fusionnée (a un historique de fusion)
                              $subQuery->select(DB::raw(1))
                                       ->from('order_histories')
                                       ->whereRaw('order_histories.order_id = o1.id')
                                       ->where('action', 'duplicate_merge');
                          });
                })
                ->distinct()
                ->pluck('customer_phone');
            
            if ($clientsWithReviewedOrders->count() > 0) {
                // ÉTAPE 2: Pour chaque client trouvé, marquer TOUTES ses commandes doubles comme examinées
                $totalUpdated = 0;
                
                foreach ($clientsWithReviewedOrders as $phone) {
                    $updated = Order::where('admin_id', $adminId)
                        ->where(function($query) use ($phone) {
                            $query->where('customer_phone', $phone)
                                  ->orWhere('customer_phone_2', $phone);
                        })
                        ->where('is_duplicate', true)
                        ->where('reviewed_for_duplicates', false)
                        ->update(['reviewed_for_duplicates' => true]);
                    
                    $totalUpdated += $updated;
                    
                    // Log pour chaque client traité
                    if ($updated > 0) {
                        \Log::info("Auto-marquage pour client {$phone}: {$updated} commandes mises à jour");
                        
                        // Enregistrer dans l'historique pour traçabilité
                        $orders = Order::where('admin_id', $adminId)
                            ->where(function($query) use ($phone) {
                                $query->where('customer_phone', $phone)
                                      ->orWhere('customer_phone_2', $phone);
                            })
                            ->where('is_duplicate', true)
                            ->get();
                            
                        foreach ($orders as $order) {
                            $order->recordHistory(
                                'auto_duplicate_review',
                                'Commande automatiquement marquée comme examinée (client a des commandes déjà traitées)'
                            );
                        }
                    }
                }
                
                if ($totalUpdated > 0) {
                    \Log::info("Auto-marquage terminé pour l'admin {$adminId}", [
                        'clients_processed' => $clientsWithReviewedOrders->count(),
                        'total_orders_updated' => $totalUpdated
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'auto-marquage des clients: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer les statistiques du dashboard
     * MODIFICATION: Statistiques mises à jour pour inclure tous les statuts
     */
    public function getDashboardStats()
    {
        $adminId = auth('admin')->id();
        
        $stats = [
            // Total des doublons non examinés (tous statuts)
            'total_duplicates' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->count(),
            
            // Doublons fusionnables (nouvelle/datée seulement)
            'mergeable_duplicates' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->count(),
            
            // Clients uniques avec doublons
            'unique_clients' => Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->distinct('customer_phone')
                ->count('customer_phone'),
            
            // Commandes fusionnées aujourd'hui
            'merged_today' => Order::where('admin_id', $adminId)
                ->whereHas('history', function($q) {
                    $q->where('action', 'duplicate_merge')
                      ->whereDate('created_at', today());
                })
                ->count(),
            
            // Délai de fusion automatique
            'auto_merge_delay' => AdminSetting::get('duplicate_auto_merge_delay_hours', 2),
            
            // NOUVELLES STATS: Répartition par statut
            'duplicates_by_status' => $this->getDuplicatesByStatusSafe($adminId)
        ];
        
        // Calculer les doublons non fusionnables
        $stats['non_mergeable_duplicates'] = $stats['total_duplicates'] - $stats['mergeable_duplicates'];

        return $stats;
    }
    
    /**
     * Méthode sécurisée pour obtenir les doublons par statut
     */
    private function getDuplicatesByStatusSafe($adminId)
    {
        try {
            return Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->pluck('count', 'status')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des statistiques par statut: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer la liste des commandes doubles pour DataTable
     * MODIFICATION: Inclut maintenant tous les statuts avec indication de fusionnabilité
     */
    public function getDuplicates(Request $request)
    {
        $adminId = auth('admin')->id();
        
        // Adapter la requête selon le type de base de données
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite ne supporte pas ORDER BY dans GROUP_CONCAT
            $statusesRaw = 'GROUP_CONCAT(DISTINCT status) as statuses';
        } else {
            // MySQL et autres
            $statusesRaw = 'GROUP_CONCAT(DISTINCT status ORDER BY status) as statuses';
        }
        
        $query = Order::select([
                'customer_phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(created_at) as latest_order_date'),
                DB::raw('MAX(id) as latest_order_id'),
                DB::raw('SUM(total_price) as total_amount'),
                DB::raw($statusesRaw)
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
        
        // NOUVEAU FILTRE: Par type de doublons
        if ($request->filled('duplicate_type')) {
            $type = $request->get('duplicate_type');
            if ($type === 'mergeable') {
                // Seulement les groupes qui ont au moins une commande fusionnable
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
                // Seulement les groupes qui n'ont aucune commande fusionnable
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
            
            // VÉRIFICATION SUPPLÉMENTAIRE : Filtrer les groupes qui n'ont plus de doublons non examinés
            $duplicates->getCollection()->transform(function ($duplicate) use ($adminId) {
                // Vérifier qu'il reste bien des doublons non examinés pour ce client
                $hasUnreviewedDuplicates = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $duplicate->customer_phone)
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false)
                    ->exists();
                
                if (!$hasUnreviewedDuplicates) {
                    // Si plus de doublons non examinés, retourner null pour filtrer
                    return null;
                }
                
                $orders = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $duplicate->customer_phone)
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false)
                    ->with(['items.product'])
                    ->get();

                $duplicate->orders = $orders;
                $duplicate->latest_order = $orders->where('id', $duplicate->latest_order_id)->first();
                $duplicate->can_auto_merge = $this->canAutoMerge($duplicate->customer_phone);
                
                // NOUVELLES PROPRIÉTÉS - Correction pour éviter les erreurs
                $mergeableOrders = $orders->whereIn('status', ['nouvelle', 'datée']);
                $nonMergeableOrders = $orders->whereNotIn('status', ['nouvelle', 'datée']);
                
                $duplicate->mergeable_orders = $mergeableOrders->values()->all(); // Convertir en array
                $duplicate->non_mergeable_orders = $nonMergeableOrders->values()->all(); // Convertir en array
                $duplicate->can_merge = $mergeableOrders->count() > 1;
                $duplicate->has_old_orders = $nonMergeableOrders->count() > 0;
                
                return $duplicate;
            });
            
            // Filtrer les éléments null
            $duplicates->setCollection($duplicates->getCollection()->filter());
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la requête des doublons: ' . $e->getMessage());
            
            // En cas d'erreur, retourner une réponse vide
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
     * MODIFICATION: Détecte maintenant TOUS les doublons, peu importe le statut
     * NOUVELLE LOGIQUE: Préserve les commandes déjà examinées/fusionnées
     */
    public function checkAllDuplicates()
    {
        try {
            $adminId = auth('admin')->id();
            
            // ÉTAPE 1: Sauvegarder les commandes déjà examinées/fusionnées
            $alreadyReviewedOrders = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', true)
                ->get(['id', 'customer_phone', 'reviewed_for_duplicates'])
                ->keyBy('id');
            
            // ÉTAPE 2: Sauvegarder les commandes qui ont un historique de fusion
            $mergedOrderIds = Order::where('admin_id', $adminId)
                ->whereHas('history', function($q) {
                    $q->where('action', 'duplicate_merge');
                })
                ->pluck('id')
                ->toArray();
            
            // ÉTAPE 3: Reset seulement les marquages de doublons, pas les examens
            Order::where('admin_id', $adminId)
                ->update([
                    'is_duplicate' => false, 
                    'duplicate_group_id' => null
                    // NOTE: On ne remet PAS à zéro 'reviewed_for_duplicates'
                ]);
            
            // ÉTAPE 4: Scanner TOUTES les commandes, peu importe le statut
            $orders = Order::where('admin_id', $adminId)->get();
            
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
                        // ÉTAPE 5: Marquer comme doublon mais préserver l'état d'examen
                        $wasAlreadyReviewed = $alreadyReviewedOrders->has($dupOrder->id) || 
                                            in_array($dupOrder->id, $mergedOrderIds);
                        
                        $dupOrder->update([
                            'is_duplicate' => true, 
                            'duplicate_group_id' => $groupId,
                            // IMPORTANT: Préserver l'état d'examen si déjà traité
                            'reviewed_for_duplicates' => $wasAlreadyReviewed ? true : $dupOrder->reviewed_for_duplicates
                        ]);
                        
                        // Enregistrer dans l'historique
                        $historyNote = $wasAlreadyReviewed 
                            ? 'Doublon re-détecté lors de la vérification globale (statut examiné préservé)'
                            : 'Doublon détecté automatiquement lors de la vérification globale (Statut: ' . $dupOrder->status . ')';
                            
                        $dupOrder->recordHistory('duplicate_detected', $historyNote);
                    }
                    
                    $duplicatesFound += $duplicateOrders->count();
                    $processedPhones[] = $order1->customer_phone;
                }
            }
            
            // ÉTAPE 6: Appliquer la logique d'auto-marquage pour maintenir la cohérence
            $this->autoMarkReviewedClients($adminId);
            
            \Log::info("Vérification des doublons terminée", [
                'admin_id' => $adminId,
                'duplicates_found' => $duplicatesFound,
                'already_reviewed_preserved' => $alreadyReviewedOrders->count(),
                'merged_orders_preserved' => count($mergedOrderIds)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "{$duplicatesFound} commandes doubles détectées et marquées (tous statuts) - " . 
                           $alreadyReviewedOrders->count() . " commandes déjà examinées préservées",
                'duplicates_found' => $duplicatesFound,
                'preserved_count' => $alreadyReviewedOrders->count()
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
     * REMARQUE: La fusion reste limitée aux commandes nouvelle/datée seulement
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
            
            // IMPORTANT: Pour la fusion, on ne traite que les commandes nouvelle/datée
            $orders = Order::where('admin_id', $adminId)
                ->where('customer_phone', $request->customer_phone)
                ->whereIn('status', ['nouvelle', 'datée']) // Fusion limitée
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Moins de 2 commandes fusionnables trouvées (seules les commandes nouvelle/datée peuvent être fusionnées)'
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
            
            // NOUVELLE LOGIQUE: Après une fusion, auto-marquer toutes les autres commandes du client
            $this->autoMarkReviewedClients($adminId);

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
     * MODIFICATION: Marque maintenant TOUS les doublons du client, peu importe le statut
     * ET s'assure que le groupe disparaît complètement de la liste
     */
    public function markAsReviewed(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            
            $adminId = auth('admin')->id();
            
            // MODIFICATION: Marquer TOUTES les commandes du client comme examinées
            // Utiliser une requête plus stricte pour s'assurer qu'on prend toutes les commandes
            $updated = Order::where('admin_id', $adminId)
                ->where(function($query) use ($request) {
                    $query->where('customer_phone', $request->customer_phone)
                          ->orWhere('customer_phone_2', $request->customer_phone);
                })
                ->where('is_duplicate', true)
                ->update(['reviewed_for_duplicates' => true]);

            // Vérification : s'assurer qu'il n'y a plus de doublons non examinés pour ce client
            $remainingDuplicates = Order::where('admin_id', $adminId)
                ->where(function($query) use ($request) {
                    $query->where('customer_phone', $request->customer_phone)
                          ->orWhere('customer_phone_2', $request->customer_phone);
                })
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->count();

            if ($remainingDuplicates > 0) {
                // Si il reste des doublons non examinés, les marquer aussi
                Order::where('admin_id', $adminId)
                    ->where(function($query) use ($request) {
                        $query->where('customer_phone', $request->customer_phone)
                              ->orWhere('customer_phone_2', $request->customer_phone);
                    })
                    ->where('is_duplicate', true)
                    ->update(['reviewed_for_duplicates' => true]);
                
                $updated += $remainingDuplicates;
            }

            // Enregistrer dans l'historique pour toutes les commandes concernées
            $orders = Order::where('admin_id', $adminId)
                ->where(function($query) use ($request) {
                    $query->where('customer_phone', $request->customer_phone)
                          ->orWhere('customer_phone_2', $request->customer_phone);
                })
                ->where('is_duplicate', true)
                ->get();

            foreach ($orders as $order) {
                $order->recordHistory(
                    'duplicate_review',
                    'Commandes marquées comme examinées pour doublons (Statut: ' . $order->status . ')'
                );
            }

            DB::commit();

            // NOUVELLE LOGIQUE: Après marquage manuel, appliquer l'auto-marquage pour cohérence
            $this->autoMarkReviewedClients($adminId);

            // Log pour debug
            \Log::info("Doublons marqués comme examinés", [
                'admin_id' => $adminId,
                'customer_phone' => $request->customer_phone,
                'updated_count' => $updated,
                'orders_processed' => $orders->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} commandes marquées comme examinées (tous statuts)",
                'updated_count' => $updated,
                'should_refresh' => true // Indicateur pour forcer le rafraîchissement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du marquage: ' . $e->getMessage(), [
                'admin_id' => $adminId ?? null,
                'customer_phone' => $request->customer_phone,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'historique d'un client
     * MODIFICATION: Inclut maintenant TOUTES les commandes du client, peu importe le statut
     */
    public function getClientHistory(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string'
        ]);

        $adminId = auth('admin')->id();
        
        // MODIFICATION: Toutes les commandes du client, pas de filtre de statut
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

        // NOUVELLES STATISTIQUES: Répartition par statut
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
     * MODIFICATION: Statistiques étendues pour tous les statuts
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
            $mergeableOrders = $duplicateOrders->whereIn('status', ['nouvelle', 'datée']);
            
            // NOUVELLES STATISTIQUES: Plus détaillées
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
                
                // NOUVELLES STATS: Répartition par statut
                'status_breakdown' => $orders->groupBy('status')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total_value' => $group->sum('total_price'),
                        'duplicate_count' => $group->where('is_duplicate', true)->count()
                    ];
                })->toArray()
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
     * REMARQUE: Reste limitée aux commandes nouvelle/datée
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
                ->whereIn('status', ['nouvelle', 'datée']) // IMPORTANT: Vérifier que seules les commandes fusionnables sont sélectionnées
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins 2 commandes fusionnables (nouvelle/datée) sont nécessaires pour la fusion'
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
            
            // NOUVELLE LOGIQUE: Après fusion sélective, auto-marquer toutes les autres commandes du client
            $this->autoMarkReviewedClients($adminId);

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
     * REMARQUE: Ne traite que les commandes nouvelle/datée
     */
    public function autoMergeDuplicates()
    {
        try {
            $delayHours = AdminSetting::get('duplicate_auto_merge_delay_hours', 2);
            $cutoffTime = now()->subHours($delayHours);
            
            $adminId = auth('admin')->id();
            
            // IMPORTANT: Pour la fusion automatique, on ne traite que nouvelle/datée
            $duplicateGroups = Order::where('admin_id', $adminId)
                ->whereIn('status', ['nouvelle', 'datée']) // Fusion limitée
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
                    ->whereIn('status', ['nouvelle', 'datée']) // Fusion limitée
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
            
            // NOUVELLE LOGIQUE: Après fusion automatique, auto-marquer tous les clients concernés
            if ($mergedCount > 0) {
                $this->autoMarkReviewedClients($adminId);
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
    
    /**
     * NOUVELLE MÉTHODE: Nettoyer manuellement les données incohérentes
     */
    public function cleanData()
    {
        try {
            $adminId = auth('admin')->id();
            $cleanedCount = 0;
            
            // 1. Trouver les clients où toutes les commandes sont marquées comme examinées
            $fullyReviewedPhones = DB::table('orders')
                ->select('customer_phone')
                ->where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->groupBy('customer_phone')
                ->havingRaw('SUM(CASE WHEN reviewed_for_duplicates = 0 THEN 1 ELSE 0 END) = 0')
                ->havingRaw('COUNT(*) > 0')
                ->pluck('customer_phone');
                
            $cleanedCount += $fullyReviewedPhones->count();
            
            // 2. Nettoyer les commandes orphelines (marquées comme doublons mais plus de partenaires)
            $allDuplicates = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->get();
                
            foreach ($allDuplicates->groupBy('customer_phone') as $phone => $phoneOrders) {
                if ($phoneOrders->count() === 1) {
                    // Si plus qu'une seule commande "doublon" pour ce téléphone, ce n'est plus un doublon
                    $phoneOrders->first()->update([
                        'is_duplicate' => false,
                        'reviewed_for_duplicates' => false,
                        'duplicate_group_id' => null
                    ]);
                    $cleanedCount++;
                }
            }
            
            // 3. Vérifier les groupes de doublons incohérents
            $groupsFixed = 0;
            $duplicateGroups = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->whereNotNull('duplicate_group_id')
                ->get()
                ->groupBy('duplicate_group_id');
                
            foreach ($duplicateGroups as $groupId => $groupOrders) {
                $phones = $groupOrders->pluck('customer_phone')->unique();
                if ($phones->count() > 1) {
                    // Groupe incohérent avec plusieurs téléphones différents
                    foreach ($groupOrders as $order) {
                        $order->update(['duplicate_group_id' => 'DUP_' . time() . '_' . $order->id]);
                    }
                    $groupsFixed++;
                }
            }
            
            \Log::info("Nettoyage manuel des données terminé", [
                'admin_id' => $adminId,
                'cleaned_phones' => $cleanedCount,
                'groups_fixed' => $groupsFixed
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Nettoyage terminé : {$cleanedCount} enregistrements nettoyés, {$groupsFixed} groupes corrigés",
                'cleaned_count' => $cleanedCount,
                'groups_fixed' => $groupsFixed
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors du nettoyage manuel: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage: ' . $e->getMessage()
            ], 500);
        }
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
     * REMARQUE: Seules les commandes nouvelle/datée peuvent être fusionnées
     */
    private function canMergeOrders($order1, $order2)
    {
        // Même admin
        if ($order1->admin_id !== $order2->admin_id) {
            return false;
        }
        
        // IMPORTANT: Vérifier que les deux commandes sont fusionnables
        $mergeableStatuses = ['nouvelle', 'datée'];
        if (!in_array($order1->status, $mergeableStatuses) || !in_array($order2->status, $mergeableStatuses)) {
            return false;
        }
        
        // Statuts compatibles entre eux
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
        
        // Vérifier s'il y a des commandes fusionnables assez anciennes
        $oldestOrder = Order::where('admin_id', $adminId)
            ->where('customer_phone', $customerPhone)
            ->whereIn('status', ['nouvelle', 'datée']) // Seulement les fusionnables
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