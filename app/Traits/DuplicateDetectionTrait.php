<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Log;

trait DuplicateDetectionTrait
{
    /**
     * Détecter automatiquement les doublons lors de la création d'une commande
     * MODIFICATION: Détecte maintenant TOUTES les commandes avec le même téléphone, peu importe le statut
     */
    public function detectDuplicatesOnCreate(Order $newOrder)
    {
        $duplicateOrders = $this->findDuplicateOrders($newOrder);
        
        if ($duplicateOrders->count() > 0) {
            // Ajouter la nouvelle commande au groupe
            $duplicateOrders->push($newOrder);
            
            // Générer un ID de groupe unique
            $groupId = 'DUP_' . time() . '_' . $newOrder->id;
            
            // Marquer toutes les commandes comme doublons (peu importe leur statut)
            foreach ($duplicateOrders as $order) {
                $order->update([
                    'is_duplicate' => true,
                    'duplicate_group_id' => $groupId
                ]);
                
                $order->recordHistory(
                    'duplicate_detected',
                    'Doublon détecté automatiquement lors de la création de la commande #' . $newOrder->id
                );
            }
            
            Log::info("Doublons détectés pour la commande #{$newOrder->id}", [
                'phone' => $newOrder->customer_phone,
                'duplicate_count' => $duplicateOrders->count(),
                'group_id' => $groupId,
                'statuses' => $duplicateOrders->pluck('status')->unique()->toArray()
            ]);
            
            return $duplicateOrders->count();
        }
        
        return false;
    }

    /**
     * Trouver les commandes en double pour une commande donnée
     * MODIFICATION: Recherche maintenant dans TOUTES les commandes, peu importe le statut
     */
    public function findDuplicateOrders(Order $order)
    {
        return Order::where('admin_id', $order->admin_id)
            ->where('id', '!=', $order->id)
            // SUPPRESSION du filtre sur les statuts - maintenant on cherche dans TOUS les statuts
            ->where(function($query) use ($order) {
                $query->where(function($q) use ($order) {
                    // Correspondance exacte du téléphone principal
                    $q->where('customer_phone', $order->customer_phone);
                    
                    // Correspondance avec téléphone secondaire
                    if ($order->customer_phone_2) {
                        $q->orWhere('customer_phone', $order->customer_phone_2)
                          ->orWhere('customer_phone_2', $order->customer_phone)
                          ->orWhere('customer_phone_2', $order->customer_phone_2);
                    }
                })
                ->orWhere(function($q) use ($order) {
                    // Vérification des 8 chiffres successifs
                    $q->whereRaw('1=1'); // Base query, sera affinée ci-dessous
                });
            })
            ->get()
            ->filter(function($otherOrder) use ($order) {
                return $this->phoneMatches($order->customer_phone, $otherOrder->customer_phone) ||
                       $this->has8SuccessiveDigits($order->customer_phone, $otherOrder->customer_phone) ||
                       ($order->customer_phone_2 && (
                           $this->phoneMatches($order->customer_phone_2, $otherOrder->customer_phone) ||
                           $this->phoneMatches($order->customer_phone_2, $otherOrder->customer_phone_2) ||
                           $this->has8SuccessiveDigits($order->customer_phone_2, $otherOrder->customer_phone)
                       ));
            });
    }

    /**
     * Vérifier si deux numéros de téléphone correspondent exactement
     */
    private function phoneMatches($phone1, $phone2)
    {
        if (!$phone1 || !$phone2) {
            return false;
        }
        
        return trim($phone1) === trim($phone2);
    }

    /**
     * Vérifier si deux numéros ont 8 chiffres successifs identiques
     */
    private function has8SuccessiveDigits($phone1, $phone2)
    {
        if (!$phone1 || !$phone2) {
            return false;
        }
        
        // Extraire seulement les chiffres
        $digits1 = preg_replace('/\D/', '', $phone1);
        $digits2 = preg_replace('/\D/', '', $phone2);
        
        // Vérifier que les deux numéros ont au moins 8 chiffres
        if (strlen($digits1) < 8 || strlen($digits2) < 8) {
            return false;
        }
        
        // Chercher 8 chiffres successifs identiques
        for ($i = 0; $i <= strlen($digits1) - 8; $i++) {
            $substring = substr($digits1, $i, 8);
            if (strpos($digits2, $substring) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Scanner toutes les commandes d'un admin pour détecter les doublons
     * MODIFICATION: Scan maintenant TOUTES les commandes, peu importe le statut
     */
    public function scanAllOrdersForDuplicates($adminId)
    {
        try {
            // Reset les tags existants
            Order::where('admin_id', $adminId)
                  ->update(['is_duplicate' => false, 'duplicate_group_id' => null]);
            
            // MODIFICATION: Récupérer TOUTES les commandes, pas seulement nouvelle/datée
            $orders = Order::where('admin_id', $adminId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $duplicatesFound = 0;
            $processedPhones = [];
            $processedOrders = [];
            
            foreach ($orders as $order) {
                // Éviter de traiter deux fois le même numéro ou la même commande
                if (in_array($order->customer_phone, $processedPhones) || 
                    in_array($order->id, $processedOrders)) {
                    continue;
                }
                
                $duplicateOrders = $this->findDuplicateOrders($order);
                
                if ($duplicateOrders->count() > 0) {
                    // Ajouter la commande principale
                    $duplicateOrders->push($order);
                    
                    // Générer un ID de groupe unique
                    $groupId = 'DUP_' . time() . '_' . $order->id . '_' . $duplicateOrders->count();
                    
                    // Marquer toutes les commandes du groupe comme doublons
                    foreach ($duplicateOrders as $dupOrder) {
                        $dupOrder->update([
                            'is_duplicate' => true,
                            'duplicate_group_id' => $groupId
                        ]);
                        
                        $dupOrder->recordHistory(
                            'duplicate_detected',
                            'Doublon détecté lors du scan global - Groupe: ' . $groupId . ' (Statut: ' . $dupOrder->status . ')'
                        );
                        
                        $processedOrders[] = $dupOrder->id;
                    }
                    
                    $duplicatesFound += $duplicateOrders->count();
                    $processedPhones[] = $order->customer_phone;
                    
                    Log::info("Groupe de doublons détecté", [
                        'group_id' => $groupId,
                        'phone' => $order->customer_phone,
                        'orders_count' => $duplicateOrders->count(),
                        'order_ids' => $duplicateOrders->pluck('id')->toArray(),
                        'statuses' => $duplicateOrders->pluck('status')->unique()->toArray()
                    ]);
                }
            }
            
            Log::info("Scan des doublons terminé", [
                'admin_id' => $adminId,
                'total_duplicates_found' => $duplicatesFound,
                'unique_groups' => count($processedPhones)
            ]);
            
            return [
                'success' => true,
                'duplicates_found' => $duplicatesFound,
                'groups_created' => count($processedPhones)
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du scan des doublons', [
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fusion automatique basée sur le délai configuré
     * REMARQUE: La fusion reste limitée aux commandes "nouvelle" et "datée" seulement
     */
    public function autoMergeDuplicates($adminId)
    {
        try {
            $delayHours = AdminSetting::getForAdmin($adminId, 'duplicate_auto_merge_delay_hours', 2);
            $cutoffTime = now()->subHours($delayHours);
            
            // IMPORTANT: Pour la fusion, on ne traite que les commandes nouvelle/datée
            $duplicateGroups = Order::where('admin_id', $adminId)
                ->whereIn('status', ['nouvelle', 'datée']) // Fusion limitée à ces statuts
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
                // Ne traiter que les commandes fusionnables (nouvelle/datée)
                $orders = Order::where('admin_id', $adminId)
                    ->where('customer_phone', $group->customer_phone)
                    ->whereIn('status', ['nouvelle', 'datée']) // Fusion limitée
                    ->where('is_duplicate', true)
                    ->where('reviewed_for_duplicates', false)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                if ($orders->count() > 1) {
                    $primaryOrder = $orders->first();
                    $secondaryOrders = $orders->skip(1);
                    
                    // Vérifier la compatibilité avant fusion
                    $canMerge = true;
                    foreach ($secondaryOrders as $secondaryOrder) {
                        if (!$primaryOrder->canMergeWith($secondaryOrder)) {
                            $canMerge = false;
                            break;
                        }
                    }
                    
                    if ($canMerge) {
                        $this->performMerge($primaryOrder, $secondaryOrders, 
                            "Fusion automatique après {$delayHours}h de délai");
                        
                        $mergedCount += $orders->count();
                        $groupsProcessed++;
                        
                        Log::info("Fusion automatique effectuée", [
                            'primary_order_id' => $primaryOrder->id,
                            'merged_orders' => $secondaryOrders->pluck('id')->toArray(),
                            'phone' => $group->customer_phone
                        ]);
                    }
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
     * REMARQUE: Ne fonctionne que pour les commandes nouvelle/datée
     */
    private function performMerge(Order $primaryOrder, $secondaryOrders, $note = null)
    {
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
                'total_price_before' => $originalPrice,
                'total_price_after' => $primaryOrder->total_price,
                'admin_note' => $note
            ]
        );

        // Supprimer les commandes secondaires
        foreach ($secondaryOrders as $secondaryOrder) {
            $secondaryOrder->delete();
        }

        // NOUVELLE LOGIQUE: Après fusion, auto-marquer toutes les autres commandes du client
        $this->autoMarkAllClientDuplicatesAsReviewed($primaryOrder->admin_id, $primaryOrder->customer_phone);

        return $primaryOrder;
    }

    /**
     * Vérifier s'il y a des doublons non examinés pour un admin
     * MODIFICATION: Compte maintenant TOUS les doublons, pas seulement nouvelle/datée
     */
    public function hasUnreviewedDuplicates($adminId)
    {
        return Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->exists();
    }

    /**
     * Compter les doublons non examinés pour un admin
     * MODIFICATION: Compte maintenant TOUS les doublons, pas seulement nouvelle/datée
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
     * MODIFICATION: Statistiques pour TOUS les doublons, avec séparation fusionnables/non-fusionnables
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
            
        $nonMergeableDuplicates = $totalDuplicates - $mergeableDuplicates;
        
        return [
            'total_duplicates' => $totalDuplicates,
            'mergeable_duplicates' => $mergeableDuplicates,
            'non_mergeable_duplicates' => $nonMergeableDuplicates,
            
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
     * NOUVELLE MÉTHODE: Obtenir les statistiques détaillées par statut
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
     * NOUVELLE MÉTHODE: Auto-marquer toutes les commandes doubles d'un client comme examinées
     * Si un client a au moins une commande double examinée/fusionnée, 
     * marquer TOUTES ses commandes doubles comme examinées
     */
    private function autoMarkAllClientDuplicatesAsReviewed($adminId, $customerPhone)
    {
        try {
            // Marquer toutes les commandes doubles de ce client comme examinées
            $updated = Order::where('admin_id', $adminId)
                ->where(function($query) use ($customerPhone) {
                    $query->where('customer_phone', $customerPhone)
                          ->orWhere('customer_phone_2', $customerPhone);
                })
                ->where('is_duplicate', true)
                ->where('reviewed_for_duplicates', false)
                ->update(['reviewed_for_duplicates' => true]);
            
            if ($updated > 0) {
                Log::info("Auto-marquage après fusion pour client {$customerPhone}: {$updated} commandes mises à jour");
                
                // Enregistrer dans l'historique pour traçabilité
                $orders = Order::where('admin_id', $adminId)
                    ->where(function($query) use ($customerPhone) {
                        $query->where('customer_phone', $customerPhone)
                              ->orWhere('customer_phone_2', $customerPhone);
                    })
                    ->where('is_duplicate', true)
                    ->get();
                    
                foreach ($orders as $order) {
                    $order->recordHistory(
                        'auto_duplicate_review',
                        'Commande automatiquement marquée comme examinée (client a eu une fusion)'
                    );
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'auto-marquage des commandes du client: ' . $e->getMessage());
        }
    }
    
    /**
     * NOUVELLE MÉTHODE: Auto-marquer tous les clients avec des commandes examinées après un scan
     * Cette méthode est appelée après un scan pour maintenir la cohérence
     */
    private function autoMarkAllReviewedClientsAfterScan($adminId)
    {
        try {
            // Trouver les clients qui ont au moins une commande double examinée ou fusionnée
            $clientsWithReviewedOrders = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->where(function($query) {
                    $query->where('reviewed_for_duplicates', true) // Déjà examinée
                          ->orWhereHas('history', function($subQuery) { // Ou fusionnée
                              $subQuery->where('action', 'duplicate_merge');
                          });
                })
                ->distinct()
                ->pluck('customer_phone');
            
            if ($clientsWithReviewedOrders->count() > 0) {
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
                    
                    if ($updated > 0) {
                        Log::info("Auto-marquage après scan pour client {$phone}: {$updated} commandes mises à jour");
                        
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
                                'Commande automatiquement marquée comme examinée après scan (client a des commandes déjà traitées)'
                            );
                        }
                    }
                }
                
                if ($totalUpdated > 0) {
                    Log::info("Auto-marquage après scan terminé pour l'admin {$adminId}", [
                        'clients_processed' => $clientsWithReviewedOrders->count(),
                        'total_orders_updated' => $totalUpdated
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'auto-marquage après scan: ' . $e->getMessage());
        }
    }
}