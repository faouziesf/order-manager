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
            // CORRECTION: Vérifier que $queue est une chaîne et la nettoyer
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
                                    'price' => $item->product->price
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
            // CORRECTION: Validation du paramètre queue
            if (!is_string($queue) || !in_array($queue, ['standard', 'dated', 'old'])) {
                Log::warning("getQueueCount: paramètre queue invalide", ['queue' => $queue]);
                return 0;
            }

            $query = Order::where('admin_id', $admin->id);
            
            switch ($queue) {
                case 'standard':
                    $maxAttempts = $this->getSetting('standard_max_total_attempts', 9);
                    return $query->where('status', 'nouvelle')
                        ->where('attempts_count', '<', $maxAttempts)
                        ->where(function($q) {
                            $q->where('is_suspended', false)->orWhereNull('is_suspended');
                        })
                        ->count();
                        
                case 'dated':
                    return $query->where('status', 'datée')
                        ->whereDate('scheduled_date', '<=', now())
                        ->where(function($q) {
                            $q->where('is_suspended', false)->orWhereNull('is_suspended');
                        })
                        ->count();
                        
                case 'old':
                    $standardMaxAttempts = $this->getSetting('standard_max_total_attempts', 9);
                    return $query->where('status', 'nouvelle')
                        ->where('attempts_count', '>=', $standardMaxAttempts)
                        ->where(function($q) {
                            $q->where('is_suspended', false)->orWhereNull('is_suspended');
                        })
                        ->count();
                        
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            Log::error("Erreur dans getQueueCount pour {$queue}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper pour obtenir les paramètres avec gestion d'erreur
     */
    private function getSetting($key, $default)
    {
        try {
            // CORRECTION: Validation du paramètre key
            if (!is_string($key)) {
                Log::warning("getSetting: clé invalide", ['key' => $key, 'type' => gettype($key)]);
                return $default;
            }
            
            return (int)AdminSetting::get($key, $default);
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
            // CORRECTION: Validation des paramètres
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
            
            // Charger les paramètres avec valeurs par défaut
            $maxDailyAttempts = $this->getSetting("{$queue}_max_daily_attempts", ($queue === 'standard' ? 3 : 2));
            $maxTotalAttempts = $this->getSetting("{$queue}_max_total_attempts", ($queue === 'standard' ? 9 : ($queue === 'dated' ? 5 : 0)));
            
            // Conditions selon la file
            if ($queue === 'standard') {
                $query->where('status', 'nouvelle');
                
                if ($maxTotalAttempts > 0) {
                    $query->where('attempts_count', '<', $maxTotalAttempts);
                }
            } 
            elseif ($queue === 'dated') {
                $query->where('status', 'datée')
                    ->whereDate('scheduled_date', '<=', now());
                    
                if ($maxTotalAttempts > 0) {
                    $query->where('attempts_count', '<', $maxTotalAttempts);
                }
            }
            elseif ($queue === 'old') {
                $standardMaxAttempts = $this->getSetting("standard_max_total_attempts", 9);
                
                $query->where('status', 'nouvelle')
                    ->where('attempts_count', '>=', $standardMaxAttempts);
                    
                if ($maxTotalAttempts > 0) {
                    $query->where('attempts_count', '<', $maxTotalAttempts);
                }
            }
            
            // Conditions communes
            $query->where('daily_attempts_count', '<', $maxDailyAttempts);
            
            // Exclure les commandes suspendues
            $query->where(function($q) {
                $q->where('is_suspended', false)
                ->orWhereNull('is_suspended');
            });
            
            // Tri simple pour éviter les erreurs SQL
            $query->orderBy('priority', 'desc')  // VIP d'abord
                ->orderBy('attempts_count', 'asc')  // Moins de tentatives d'abord
                ->orderBy('created_at', 'asc');     // Plus anciennes commandes d'abord
            
            return $query->first();

        } catch (\Exception $e) {
            Log::error('Erreur dans findNextOrder: ' . $e->getMessage(), [
                'queue' => $queue ?? 'undefined',
                'admin_id' => $admin ? $admin->id : null
            ]);
            return null;
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
            
            // Traiter selon l'action
            switch ($action) {
                case 'call':
                    $this->recordCallAttempt($order, $notes);
                    break;
                    
                case 'confirm':
                    $this->validateConfirmation($request);
                    $this->confirmOrder($order, $request);
                    break;
    
                case 'cancel':
                    $order->status = 'annulée';
                    $order->save();
                    $order->recordHistory('annulation', $notes);
                    break;
                    
                case 'schedule':
                    $request->validate([
                        'scheduled_date' => 'required|date|after:today',
                    ]);
                    $order->status = 'datée';
                    $order->scheduled_date = $request->scheduled_date;
                    $order->save();
                    $order->recordHistory('datation', $notes);
                    break;
                    
                default:
                    // Action générique - mise à jour des informations
                    $this->updateOrderInfo($order, $request);
                    $order->recordHistory('modification', $notes);
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
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'confirmed_price' => 'required|numeric|min:0',
        ]);
    }

    /**
     * Confirme une commande
     */
    private function confirmOrder(Order $order, Request $request)
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
        
        $order->recordHistory('confirmation', $request->notes);
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
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        $order->last_attempt_at = now();
        $order->save();
        
        $order->recordHistory('tentative', $notes);
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