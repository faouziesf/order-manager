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

            $order = $this->findNextOrder($admin, $queue);
            
            if ($order) {
                // Charger les relations nécessaires
                $order->load(['region', 'city', 'items.product']);
                
                return response()->json([
                    'hasOrder' => true,
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'priority' => $order->priority,
                        'customer_name' => $order->customer_name,
                        'customer_phone' => $order->customer_phone,
                        'customer_phone_2' => $order->customer_phone_2,
                        'customer_governorate' => $order->customer_governorate,
                        'customer_city' => $order->customer_city,
                        'customer_address' => $order->customer_address,
                        'shipping_cost' => $order->shipping_cost ?? 0,
                        'total_price' => $order->total_price ?? 0,
                        'confirmed_price' => $order->confirmed_price,
                        'scheduled_date' => $order->scheduled_date,
                        'attempts_count' => $order->attempts_count ?? 0,
                        'daily_attempts_count' => $order->daily_attempts_count ?? 0,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                        'last_attempt_at' => $order->last_attempt_at,
                        'items' => $order->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price,
                                'product' => $item->product ? [
                                    'id' => $item->product->id,
                                    'name' => $item->product->name,
                                    'price' => $item->product->price,
                                    'stock' => $item->product->stock
                                ] : null
                            ];
                        })
                    ]
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
            
            // Compteurs avec gestion d'erreur
            $standard = $this->getQueueCount($admin, 'standard');
            $dated = $this->getQueueCount($admin, 'dated');
            $old = $this->getQueueCount($admin, 'old');
            
            return response()->json([
                'standard' => $standard,
                'dated' => $dated,
                'old' => $old,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCounts: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement des compteurs',
                'standard' => 0,
                'dated' => 0,
                'old' => 0
            ], 500);
        }
    }

    /**
     * Helper pour obtenir le nombre de commandes dans une file
     */
    private function getQueueCount($admin, $queue)
    {
        try {
            if (!is_string($queue) || !in_array($queue, ['standard', 'dated', 'old'])) {
                Log::warning("getQueueCount: paramètre queue invalide", ['queue' => $queue]);
                return 0;
            }

            $query = Order::where('admin_id', $admin->id);
            
            switch ($queue) {
                case 'standard':
                    return $this->getStandardQueueCount($query);
                        
                case 'dated':
                    return $this->getDatedQueueCount($query);
                        
                case 'old':
                    return $this->getOldQueueCount($query);
                        
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            Log::error("Erreur dans getQueueCount pour {$queue}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compteur pour la file standard
     */
    private function getStandardQueueCount($query)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);
        
        return $query->where('status', 'nouvelle')
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->count();
    }

    /**
     * Compteur pour la file datée
     */
    private function getDatedQueueCount($query)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);
        
        return $query->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->count();
    }

    /**
     * Compteur pour la file ancienne
     */
    private function getOldQueueCount($query)
    {
        $standardMaxAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0); // 0 = illimité
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        
        $baseQuery = $query->where('status', 'nouvelle')
            ->where('attempts_count', '>=', $standardMaxAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            });
            
        // Appliquer la limite totale seulement si elle est définie (> 0)
        if ($maxTotalAttempts > 0) {
            $baseQuery->where('attempts_count', '<', $maxTotalAttempts);
        }
        
        return $baseQuery->count();
    }

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
     * Trouve la prochaine commande à traiter
     */
    private function findNextOrder($admin, $queue)
    {
        try {
            if (!$admin || !is_string($queue)) {
                Log::error('findNextOrder: paramètres invalides', [
                    'admin' => $admin ? $admin->id : null,
                    'queue' => $queue,
                    'queue_type' => gettype($queue)
                ]);
                return null;
            }

            $queue = trim(strtolower($queue));
            
            if (!in_array($queue, ['standard', 'dated', 'old'])) {
                Log::error('findNextOrder: queue invalide', ['queue' => $queue]);
                return null;
            }

            $query = Order::where('admin_id', $admin->id);
            
            switch ($queue) {
                case 'standard':
                    return $this->findStandardOrder($query);
                case 'dated':
                    return $this->findDatedOrder($query);
                case 'old':
                    return $this->findOldOrder($query);
                default:
                    return null;
            }

        } catch (\Exception $e) {
            Log::error('Erreur dans findNextOrder: ' . $e->getMessage(), [
                'queue' => $queue ?? 'undefined',
                'admin_id' => $admin ? $admin->id : null
            ]);
            return null;
        }
    }

    /**
     * Trouve la prochaine commande standard
     */
    private function findStandardOrder($query)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);
        
        return $query->where('status', 'nouvelle')
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->orderBy('priority', 'desc')  // VIP d'abord
            ->orderBy('attempts_count', 'asc')  // Moins de tentatives d'abord
            ->orderBy('created_at', 'asc')  // Plus anciennes d'abord
            ->first();
    }

    /**
     * Trouve la prochaine commande datée
     */
    private function findDatedOrder($query)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);
        
        return $query->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->orderBy('scheduled_date', 'asc')  // Plus anciennes dates d'abord
            ->orderBy('priority', 'desc')  // VIP d'abord
            ->orderBy('attempts_count', 'asc')  // Moins de tentatives d'abord
            ->first();
    }

    /**
     * Trouve la prochaine commande ancienne
     */
    private function findOldOrder($query)
    {
        $standardMaxAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0); // 0 = illimité
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        
        $baseQuery = $query->where('status', 'nouvelle')
            ->where('attempts_count', '>=', $standardMaxAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                // Première tentative OU délai écoulé depuis la dernière modification
                $q->whereNull('last_attempt_at')
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours));
            })
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            });
            
        // Appliquer la limite totale seulement si elle est définie (> 0)
        if ($maxTotalAttempts > 0) {
            $baseQuery->where('attempts_count', '<', $maxTotalAttempts);
        }
        
        return $baseQuery->orderBy('priority', 'desc')  // VIP d'abord
            ->orderBy('attempts_count', 'asc')  // Moins de tentatives d'abord
            ->orderBy('created_at', 'asc')  // Plus anciennes d'abord
            ->first();
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
            $historyNote = Auth::guard('admin')->user()->name . " a effectué l'action [{$action}] et a laissé une note de [{$notes}]";
            
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
        $order->update([
            'customer_name' => $request->customer_name,
            'customer_phone_2' => $request->customer_phone_2,
            'customer_governorate' => $request->customer_governorate,
            'customer_city' => $request->customer_city,
            'customer_address' => $request->customer_address,
            'status' => 'confirmée',
            'confirmed_price' => $request->confirmed_price,
            'shipping_cost' => $request->shipping_cost ?? 0,
        ]);
        
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
            // La commande va maintenant apparaître dans la file "ancienne"
            Log::info("Commande {$order->id} a atteint le maximum de tentatives standard ({$standardMaxAttempts}) et va passer en file ancienne");
        }
    }

    /**
     * NOUVELLE MÉTHODE: Met à jour les items de la commande
     */
    private function updateOrderItems(Order $order, $cartItems)
    {
        try {
            // Supprimer les anciens items
            $order->items()->delete();
            
            // Ajouter les nouveaux items
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }
            
            // Recalculer le total de la commande
            $order->recalculateTotal();
            
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
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
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