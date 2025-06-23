<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait DuplicateDetectionTrait
{
    /**
     * Détecter automatiquement les doublons lors de la création d'une commande
     * LOGIQUE SIMPLIFIÉE: Si plusieurs commandes ont le même téléphone → toutes marquées comme double
     */
    public function detectDuplicatesOnCreate(Order $newOrder)
    {
        // Trouver toutes les commandes avec le même numéro (y compris la nouvelle)
        $allOrdersWithSamePhone = Order::where('admin_id', $newOrder->admin_id)
            ->where(function($query) use ($newOrder) {
                $query->where('customer_phone', $newOrder->customer_phone);
                if ($newOrder->customer_phone_2) {
                    $query->orWhere('customer_phone', $newOrder->customer_phone_2)
                          ->orWhere('customer_phone_2', $newOrder->customer_phone)
                          ->orWhere('customer_phone_2', $newOrder->customer_phone_2);
                }
            })
            ->get();

        // Si plus d'une commande avec ce numéro → toutes marquées comme double
        if ($allOrdersWithSamePhone->count() > 1) {
            $groupId = 'DUP_' . time() . '_' . $newOrder->customer_phone;
            
            foreach ($allOrdersWithSamePhone as $order) {
                $order->update([
                    'is_duplicate' => true,
                    'duplicate_group_id' => $groupId,
                    'reviewed_for_duplicates' => false
                ]);
                
                $order->recordHistory(
                    'duplicate_detected',
                    'Doublon détecté automatiquement (groupe: ' . $groupId . ')'
                );
            }
            
            Log::info("Doublons détectés pour le téléphone {$newOrder->customer_phone}", [
                'orders_count' => $allOrdersWithSamePhone->count(),
                'group_id' => $groupId
            ]);
            
            return $allOrdersWithSamePhone->count();
        }
        
        return false;
    }

    /**
     * Scanner toutes les commandes d'un admin pour détecter les doublons
     * LOGIQUE SIMPLIFIÉE: Grouper par téléphone, si plus d'une → toutes marquées comme double
     */
    public function scanAllOrdersForDuplicates($adminId)
    {
        try {
            DB::beginTransaction();
            
            // Reset tous les marquages existants
            Order::where('admin_id', $adminId)
                  ->update([
                      'is_duplicate' => false, 
                      'duplicate_group_id' => null,
                      'reviewed_for_duplicates' => false
                  ]);
            
            // Grouper les commandes par numéro de téléphone
            $phoneGroups = Order::where('admin_id', $adminId)
                ->select('customer_phone')
                ->groupBy('customer_phone')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('customer_phone');
            
            $duplicatesFound = 0;
            $groupsCreated = 0;
            
            foreach ($phoneGroups as $phone) {
                $ordersWithThisPhone = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $phone)
                    ->get();
                
                if ($ordersWithThisPhone->count() > 1) {
                    $groupId = 'DUP_' . time() . '_' . $phone . '_' . $ordersWithThisPhone->count();
                    
                    foreach ($ordersWithThisPhone as $order) {
                        $order->update([
                            'is_duplicate' => true,
                            'duplicate_group_id' => $groupId,
                            'reviewed_for_duplicates' => false
                        ]);
                        
                        $order->recordHistory(
                            'duplicate_detected',
                            'Doublon détecté lors du scan global (groupe: ' . $groupId . ')'
                        );
                    }
                    
                    $duplicatesFound += $ordersWithThisPhone->count();
                    $groupsCreated++;
                }
            }
            
            DB::commit();
            
            Log::info("Scan des doublons terminé", [
                'admin_id' => $adminId,
                'duplicates_found' => $duplicatesFound,
                'groups_created' => $groupsCreated
            ]);
            
            return [
                'success' => true,
                'duplicates_found' => $duplicatesFound,
                'groups_created' => $groupsCreated
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du scan des doublons', [
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fusion automatique basée sur le délai configuré
     * Fusion limitée aux commandes "nouvelle" et "datée" seulement
     */
    public function autoMergeDuplicates($adminId)
    {
        try {
            $delayHours = AdminSetting::getForAdmin($adminId, 'duplicate_auto_merge_delay_hours', 2);
            $cutoffTime = now()->subHours($delayHours);
            
            // Trouver les groupes de doublons fusionnables
            $duplicateGroups = Order::where('admin_id', $adminId)
                ->whereIn('status', ['nouvelle', 'datée'])
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->where('created_at', '<=', $cutoffTime)
                ->select('customer_phone')
                ->groupBy('customer_phone')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $mergedCount = 0;
            $groupsProcessed = 0;
            
            foreach ($duplicateGroups as $group) {
                $orders = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $group->customer_phone)
                    ->whereIn('status', ['nouvelle', 'datée'])
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                if ($orders->count() > 1) {
                    $primaryOrder = $orders->first();
                    $secondaryOrders = $orders->skip(1);
                    
                    $this->performMerge($primaryOrder, $secondaryOrders, 
                        "Fusion automatique après {$delayHours}h de délai");
                    
                    $mergedCount += $orders->count();
                    $groupsProcessed++;
                }
            }
            
            return [
                'success' => true,
                'merged_count' => $mergedCount,
                'groups_processed' => $groupsProcessed
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la fusion automatique', [
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Effectuer la fusion de commandes
     * NOUVEAU: Vérification automatique après fusion
     */
    private function performMerge(Order $primaryOrder, $secondaryOrders, $note = null)
    {
        DB::beginTransaction();
        
        try {
            $mergedNames = collect([$primaryOrder->customer_name]);
            $mergedAddresses = collect([$primaryOrder->customer_address]);

            foreach ($secondaryOrders as $secondaryOrder) {
                // Collecter noms et adresses uniques
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
            $originalPrice = $primaryOrder->total_price;
            $primaryOrder->update([
                'customer_name' => $mergedNames->filter()->implode(' / '),
                'customer_address' => $mergedAddresses->filter()->implode(' / '),
                'total_price' => $primaryOrder->items->sum('total_price'),
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
                    'total_price_before' => $originalPrice,
                    'total_price_after' => $primaryOrder->total_price,
                    'admin_note' => $note
                ]
            );

            // Supprimer les commandes secondaires
            foreach ($secondaryOrders as $secondaryOrder) {
                $secondaryOrder->delete();
            }

            DB::commit();

            // NOUVEAU: Vérifier automatiquement si la commande restante doit encore être marquée comme double
            $this->autoCleanSingleOrders($primaryOrder->admin_id, $primaryOrder->customer_phone);

            return $primaryOrder;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * NOUVELLE MÉTHODE: Nettoyage automatique des commandes uniques
     * Si un numéro de téléphone n'a plus qu'une seule commande, elle ne doit plus être marquée comme double
     */
    public function autoCleanSingleOrders($adminId, $customerPhone = null)
    {
        try {
            $query = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true);
            
            if ($customerPhone) {
                $query->where('customer_phone', $customerPhone);
            }
            
            // Grouper par téléphone et compter
            $phoneGroups = $query->select('customer_phone')
                ->groupBy('customer_phone')
                ->selectRaw('customer_phone, COUNT(*) as count')
                ->get();
            
            $cleanedCount = 0;
            
            foreach ($phoneGroups as $group) {
                if ($group->count == 1) {
                    // Si il ne reste qu'une commande avec ce numéro → la démarquer
                    $order = Order::where('admin_id', $adminId)
                        ->where('customer_phone', $group->customer_phone)
                        ->where('is_duplicate', true)
                        ->first();
                    
                    if ($order) {
                        $order->update([
                            'is_duplicate' => false,
                            'duplicate_group_id' => null,
                            'reviewed_for_duplicates' => false
                        ]);
                        
                        $order->recordHistory(
                            'duplicate_auto_clean',
                            'Marquage doublon supprimé automatiquement (commande unique pour ce numéro)'
                        );
                        
                        $cleanedCount++;
                    }
                }
            }
            
            if ($cleanedCount > 0) {
                Log::info("Nettoyage automatique effectué", [
                    'admin_id' => $adminId,
                    'cleaned_count' => $cleanedCount,
                    'customer_phone' => $customerPhone
                ]);
            }
            
            return $cleanedCount;
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage automatique', [
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Compter les doublons non examinés pour un admin
     */
    public function countUnreviewedDuplicates($adminId)
    {
        return Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->distinct('customer_phone')
            ->count('customer_phone');
    }

    /**
     * Obtenir les statistiques des doublons pour un admin
     */
    public function getDuplicateStats($adminId)
    {
        $totalDuplicates = Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->count();
            
        $mergeableDuplicates = Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->count();
        
        return [
            'total_duplicates' => $totalDuplicates,
            'mergeable_duplicates' => $mergeableDuplicates,
            'non_mergeable_duplicates' => $totalDuplicates - $mergeableDuplicates,
            
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
            
            'auto_merge_delay' => AdminSetting::getForAdmin($adminId, 'duplicate_auto_merge_delay_hours', 2)
        ];
    }

    /**
     * Obtenir les statistiques détaillées par statut
     */
    public function getDuplicateStatsByStatus($adminId)
    {
        return Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Vérifier s'il y a des doublons non examinés pour un admin
     */
    public function hasUnreviewedDuplicates($adminId)
    {
        return Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->exists();
    }
}