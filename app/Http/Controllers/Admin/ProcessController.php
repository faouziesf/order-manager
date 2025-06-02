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
     * NOUVELLE MÉTHODE: Interface d'examen des commandes avec problèmes de stock
     */
    public function examination()
    {
        return view('admin.process.examination');
    }

    /**
     * NOUVELLE MÉTHODE: Obtenir les commandes pour l'interface d'examen
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

            // Obtenir les commandes avec problèmes de stock
            $orders = $this->findOrdersWithStockIssues($admin);
            
            if ($orders->count() > 0) {
                // CORRECTION: Filtrer les nulls et convertir en array
                $ordersData = $orders->map(function($order) {
                    return $this->formatOrderDataForExamination($order);
                })->filter(function($orderData) {
                    return $orderData !== null; // Filtrer les données nulles
                })->values()->toArray(); // Convertir en array avec réindexation
                
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
     * NOUVELLE MÉTHODE: Compter les commandes avec problèmes de stock
     */
    public function getExaminationCount()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $count = $this->countOrdersWithStockIssues($admin);
            
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
     * NOUVELLE MÉTHODE: Diviser une commande en retirant les produits problématiques
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
     * NOUVELLE MÉTHODE: Action d'examen (annuler, modifier, etc.)
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
     * Helper: Trouver les commandes avec problèmes de stock
     */
    private function findOrdersWithStockIssues($admin)
    {
        return $admin->orders()
            ->with(['items.product'])
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée']) // Exclure les annulées et livrées
            ->get()
            ->filter(function($order) {
                return $this->orderHasStockIssues($order);
            });
    }

    /**
     * Helper: Compter les commandes avec problèmes de stock
     */
    private function countOrdersWithStockIssues($admin)
    {
        $orders = $admin->orders()
            ->with(['items.product'])
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->get();

        return $orders->filter(function($order) {
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
            // Vérifier que l'ordre est valide
            if (!$order || !$order->id) {
                Log::warning('formatOrderDataForExamination: order invalide');
                return null;
            }

            // Charger les relations si nécessaire
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

    // ... (garder toutes les autres méthodes existantes de ProcessController)

    /**
     * Route unifiée pour obtenir les données d'une file d'attente
     */
    public function getQueue($queue)
    {
        try {
            // Validation du paramètre queue
            if (!is_string($queue)) {
                Log::error('getQueue: paramètre queue n\'est pas une chaîne', ['queue' => $queue, 'type' => gettype($queue)]);
                return response()->json([
                    'error' => 'Paramètre de file d\'attente invalide (type incorrect)',
                    'hasOrder' => false
                ], 400);
            }

            $queue = trim(strtolower($queue));
            
            // Valider le nom de la file
            if (!in_array($queue, ['standard', 'dated', 'old'])) {
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

            // Réinitialiser les compteurs journaliers si nécessaire
            $this->resetDailyCountersIfNeeded($admin);

            // MODIFICATION: Exclure les commandes avec problèmes de stock de l'interface normale
            $order = $this->findNextOrderExcludingStockIssues($admin, $queue);
            
            if ($order) {
                // Charger les relations nécessaires avec vérification
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
     * MODIFICATION: Trouver la prochaine commande en excluant celles avec problèmes de stock
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
                ->with(['items.product']);
            
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

            // Filtrer les commandes pour exclure celles avec problèmes de stock
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
     * Helper modifié: Commandes standard sans problèmes de stock
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
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Helper modifié: Commandes datées sans problèmes de stock
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
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc');
    }

    /**
     * Helper modifié: Commandes anciennes sans problèmes de stock
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
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            });
            
        if ($maxTotalAttempts > 0) {
            $baseQuery->where('attempts_count', '<', $maxTotalAttempts);
        }
        
        return $baseQuery->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->orderBy('created_at', 'asc');
    }

    // ... (garder toutes les autres méthodes existantes)

    /**
     * Obtient les compteurs des files d'attente
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
            
            // Réinitialiser les compteurs journaliers si nécessaire
            $this->resetDailyCountersIfNeeded($admin);
            
            // Compteurs avec gestion d'erreur - MODIFICATION: exclure les commandes avec problèmes de stock
            $standard = $this->getQueueCountExcludingStockIssues($admin, 'standard');
            $dated = $this->getQueueCountExcludingStockIssues($admin, 'dated');
            $old = $this->getQueueCountExcludingStockIssues($admin, 'old');
            $examination = $this->countOrdersWithStockIssues($admin);
            
            return response()->json([
                'standard' => $standard,
                'dated' => $dated,
                'old' => $old,
                'examination' => $examination,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCounts: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement des compteurs',
                'standard' => 0,
                'dated' => 0,
                'old' => 0,
                'examination' => 0
            ], 500);
        }
    }

    /**
     * Helper modifié: Compter les commandes sans problèmes de stock
     */
    private function getQueueCountExcludingStockIssues($admin, $queue)
    {
        try {
            if (!is_string($queue) || !in_array($queue, ['standard', 'dated', 'old'])) {
                Log::warning("getQueueCountExcludingStockIssues: paramètre queue invalide", ['queue' => $queue]);
                return 0;
            }

            $query = Order::where('admin_id', $admin->id)->with(['items.product']);
            
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

            // Filtrer pour exclure les commandes avec problèmes de stock
            $filteredOrders = $orders->get()->filter(function($order) {
                return !$this->orderHasStockIssues($order);
            });

            return $filteredOrders->count();

        } catch (\Exception $e) {
            Log::error("Erreur dans getQueueCountExcludingStockIssues pour {$queue}: " . $e->getMessage());
            return 0;
        }
    }

    // ... (garder toutes les autres méthodes existantes)

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
            // Charger les relations nécessaires de manière sécurisée
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
            // Vérifier s'il faut réinitialiser les compteurs journaliers
            $lastReset = AdminSetting::get('last_daily_reset_' . $admin->id);
            $today = now()->format('Y-m-d');
            
            if ($lastReset !== $today) {
                Log::info("Réinitialisation des compteurs journaliers pour l'admin {$admin->id}");
                
                // Réinitialiser tous les compteurs journaliers pour cet admin
                Order::where('admin_id', $admin->id)
                    ->where('daily_attempts_count', '>', 0)
                    ->update(['daily_attempts_count' => 0]);
                
                // Marquer la date de dernière réinitialisation
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
            // Validation de base
            $request->validate([
                'action' => 'nullable|string',
                'notes' => 'required|string|min:3',
                'queue' => 'required|in:standard,dated,old',
            ]);
            
            // Vérifier que l'admin possède cette commande
            $admin = Auth::guard('admin')->user();
            if ($order->admin_id !== $admin->id) {
                return response()->json([
                    'error' => 'Accès refusé à cette commande'
                ], 403);
            }
            
            DB::beginTransaction();
            
            $action = $request->action;
            $notes = $request->notes;
            
            // Enregistrer dans l'historique avec le nom de l'utilisateur
            $historyNote = $admin->name . " a effectué l'action [{$action}] : {$notes}";
            
            // Traiter selon l'action
            switch ($action) {
                case 'call':
                    $this->recordCallAttempt($order, $historyNote);
                    break;
                    
                case 'confirm':
                    $this->validateConfirmation($request);
                    $this->confirmOrder($order, $request, $historyNote);
                    
                    // Mettre à jour les items du panier si fournis
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
                    // Réinitialiser les compteurs quand on date une commande
                    $order->status = 'datée';
                    $order->scheduled_date = $request->scheduled_date;
                    $order->attempts_count = 0; 
                    $order->daily_attempts_count = 0; 
                    $order->last_attempt_at = null; 
                    $order->save();
                    $order->recordHistory('datation', $historyNote);
                    break;
                    
                default:
                    // Action générique - mise à jour des informations
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

        // Ajouter les champs optionnels s'ils sont fournis
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
        
        // Champs optionnels
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
        // Incrémenter les compteurs
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        
        // Mettre à jour la date de dernière tentative
        $order->last_attempt_at = now();
        $order->save(); // Le updated_at sera automatiquement mis à jour
        
        // Enregistrer dans l'historique
        $order->recordHistory('tentative', $notes);
        
        // Vérifier si la commande doit passer en "ancienne"
        $standardMaxAttempts = $this->getSetting('standard_max_total_attempts', 9);
        if ($order->status === 'nouvelle' && $order->attempts_count >= $standardMaxAttempts) {
            
            // Sauvegarder le statut précédent
            $previousStatus = $order->status;
            
            // Changer le statut vers "ancienne"
            $order->status = 'ancienne';
            $order->save();
            
            // Enregistrer le changement de statut dans l'historique
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

            // Supprimer les anciens items
            $order->items()->delete();
            
            // Ajouter les nouveaux items
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
            
            // Recalculer le total de la commande
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

    // Méthodes pour les vues individuelles (si nécessaire)
    public function standardQueue()
    {
        return view('admin.process.standard');
    }

    public function datedQueue()
    {
        return view('admin.process.dated');
    }

    public function oldQueue()
    {
        return view('admin.process.old');
    }
}