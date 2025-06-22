<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ProcessTrait;
use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessController extends Controller
{
    use ProcessTrait;

    // Cache statique pour les compteurs
    private static $countsCache = null;
    private static $cacheTime = null;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Affiche l'interface de traitement unifiée
     */
    public function interface()
    {
        return view('admin.process.interface');
    }

    /**
     * API unifiée pour obtenir une commande selon la file
     */
    public function getQueueApi($queue)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json([
                    'error' => 'Non authentifié',
                    'hasOrder' => false
                ], 401);
            }

            // Valider le type de file
            if (!in_array($queue, ['standard', 'dated', 'old', 'restock'])) {
                return response()->json([
                    'error' => 'File d\'attente invalide',
                    'hasOrder' => false
                ], 400);
            }

            $this->resetDailyCountersIfNeeded($admin);

            // Obtenir la commande selon le type de file
            $order = $this->findOrderForQueue($admin, $queue);
            
            if ($order) {
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
            Log::error('Erreur dans getQueueApi: ' . $e->getMessage(), [
                'queue' => $queue,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur interne du serveur',
                'hasOrder' => false
            ], 500);
        }
    }

    /**
     * Trouver une commande selon le type de file
     */
    private function findOrderForQueue($admin, $queue)
    {
        switch ($queue) {
            case 'standard':
                return $this->findStandardOrder($admin);
            case 'dated':
                return $this->findDatedOrder($admin);
            case 'old':
                return $this->findOldOrder($admin);
            case 'restock':
                return $this->findRestockOrder($admin);
            default:
                return null;
        }
    }

    /**
     * File standard : commandes nouvelles, non suspendues, avec stock suffisant
     */
    private function findStandardOrder($admin)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('status', 'nouvelle')
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Filtrer les commandes avec stock suffisant
        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->first();
    }

    /**
     * File datée : commandes datées après leur date, non suspendues, avec stock suffisant
     */
    private function findDatedOrder($admin)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->get();

        // Filtrer les commandes avec stock suffisant
        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->first();
    }

    /**
     * File ancienne : commandes anciennes avec stock suffisant (même si suspendues)
     */
    private function findOldOrder($admin)
    {
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0);

        $query = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('status', 'ancienne')
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });

        if ($maxTotalAttempts > 0) {
            $query->where('attempts_count', '<', $maxTotalAttempts);
        }

        $orders = $query->orderBy('priority', 'desc')
            ->orderBy('attempts_count', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Filtrer uniquement par stock (même si suspendues)
        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->first();
    }

    /**
     * File retour en stock : commandes suspendues nouvelles/datées avec stock suffisant
     */
    private function findRestockOrder($admin)
    {
        $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2);
        $delayHours = $this->getSetting('restock_delay_hours', 1);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Filtrer les commandes avec stock maintenant disponible
        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->first();
    }

    /**
     * Obtenir les compteurs de toutes les files
     */
    public function getCounts()
    {
        try {
            // Cache de 10 secondes
            if (self::$countsCache && self::$cacheTime && now()->diffInSeconds(self::$cacheTime) < 10) {
                $cachedResponse = self::$countsCache;
                $cachedResponse['timestamp'] = now()->toISOString();
                return response()->json($cachedResponse);
            }

            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $this->resetDailyCountersIfNeeded($admin);
            
            $counts = [
                'standard' => $this->getStandardCount($admin),
                'dated' => $this->getDatedCount($admin),
                'old' => $this->getOldCount($admin),
                'examination' => $this->getExaminationCount($admin),
                'suspended' => $this->getSuspendedCount($admin),
                'restock' => $this->getRestockCount($admin),
                'timestamp' => now()->toISOString()
            ];

            // Mettre en cache
            self::$countsCache = $counts;
            self::$cacheTime = now();
            
            return response()->json($counts);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCounts: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement des compteurs',
                'standard' => 0,
                'dated' => 0,
                'old' => 0,
                'examination' => 0,
                'suspended' => 0,
                'restock' => 0
            ], 500);
        }
    }

    /**
     * Compter les commandes standard disponibles
     */
    private function getStandardCount($admin)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('status', 'nouvelle')
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->get();

        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->count();
    }

    /**
     * Compter les commandes datées disponibles
     */
    private function getDatedCount($admin)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->get();

        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->count();
    }

    /**
     * Compter les commandes anciennes disponibles
     */
    private function getOldCount($admin)
    {
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0);

        $query = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('status', 'ancienne')
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });

        if ($maxTotalAttempts > 0) {
            $query->where('attempts_count', '<', $maxTotalAttempts);
        }

        $orders = $query->get();

        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->count();
    }

    /**
     * Compter les commandes en examen
     */
    private function getExaminationCount($admin)
    {
        return $admin->orders()
            ->with(['items.product'])
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->get()
            ->filter(function($order) {
                return $this->orderHasStockIssues($order);
            })->count();
    }

    /**
     * Compter les commandes suspendues
     */
    private function getSuspendedCount($admin)
    {
        return $admin->orders()
            ->where('is_suspended', true)
            ->whereNotIn('status', ['annulée', 'livrée'])
            ->count();
    }

    /**
     * Compter les commandes de retour en stock
     */
    private function getRestockCount($admin)
    {
        $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2);
        $delayHours = $this->getSetting('restock_delay_hours', 1);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product'])
            ->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->get();

        return $orders->filter(function($order) {
            return !$this->orderHasStockIssues($order);
        })->count();
    }

    /**
     * Traiter une action sur une commande
     */
    public function processAction(Request $request, Order $order)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if ($order->admin_id !== $admin->id) {
                return response()->json([
                    'error' => 'Accès refusé à cette commande'
                ], 403);
            }
            
            DB::beginTransaction();
            
            $action = $request->action;
            $notes = $request->notes;
            $queue = $request->queue;
            
            // Validation selon l'action
            $this->validateAction($request, $action);
            
            // Traitement selon l'action
            switch ($action) {
                case 'call':
                    $this->handleCallAction($order, $notes, $admin);
                    break;
                    
                case 'confirm':
                    $this->handleConfirmAction($order, $request, $admin);
                    break;
    
                case 'cancel':
                    $this->handleCancelAction($order, $notes, $admin);
                    break;
                    
                case 'schedule':
                    $this->handleScheduleAction($order, $request, $admin);
                    break;
                
                case 'reactivate':
                    $this->handleReactivateAction($order, $request, $admin, $queue);
                    break;

                default:
                    return response()->json([
                        'error' => 'Action non reconnue'
                    ], 400);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Action traitée avec succès',
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
                'order_id' => $order->id ?? 'unknown',
                'action' => $request->action ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validation selon l'action
     */
    private function validateAction($request, $action)
    {
        $rules = [];
        
        switch ($action) {
            case 'call':
                $rules = [
                    'notes' => 'required|string|min:3|max:1000',
                ];
                break;
                
            case 'confirm':
                $rules = [
                    'confirmed_price' => 'required|numeric|min:0',
                    'customer_name' => 'required|string|min:2|max:255',
                    'customer_governorate' => 'required|string',
                    'customer_city' => 'required|string',
                    'customer_address' => 'required|string|min:5',
                    'cart_items' => 'required|array|min:1',
                ];
                break;
                
            case 'cancel':
                $rules = [
                    'notes' => 'required|string|min:3|max:1000',
                ];
                break;
                
            case 'schedule':
                $rules = [
                    'scheduled_date' => 'required|date|after_or_equal:today',
                    'notes' => 'nullable|string|max:1000',
                ];
                break;
                
            case 'reactivate':
                $rules = [
                    'notes' => 'nullable|string|max:1000',
                    'queue' => 'required|in:restock',
                ];
                break;
        }
        
        $request->validate($rules);
    }

    /**
     * Gérer l'action "Ne répond pas"
     */
    private function handleCallAction($order, $notes, $admin)
    {
        $this->recordCallAttempt($order, $notes);
        
        $historyNote = $admin->name . " a tenté d'appeler : {$notes}";
        $order->recordHistory('tentative', $historyNote);
    }

    /**
     * Gérer l'action "Confirmer"
     */
    private function handleConfirmAction($order, $request, $admin)
    {
        // Mettre à jour les informations de la commande
        $this->confirmOrder($order, $request, $admin->name . " a confirmé la commande");
        
        // Mettre à jour les items et décrémenter le stock
        if ($request->has('cart_items')) {
            $this->updateOrderItems($order, $request->cart_items);
        }
        
        $historyNote = $admin->name . " a confirmé la commande pour " . $order->total_price . " TND";
        if ($request->notes) {
            $historyNote .= " : " . $request->notes;
        }
        
        $order->recordHistory('confirmation', $historyNote);
    }

    /**
     * Gérer l'action "Annuler"
     */
    private function handleCancelAction($order, $notes, $admin)
    {
        $order->status = 'annulée';
        $order->is_suspended = false;
        $order->suspension_reason = null;
        $order->save();
        
        $historyNote = $admin->name . " a annulé la commande : {$notes}";
        $order->recordHistory('annulation', $historyNote);
    }

    /**
     * Gérer l'action "Dater"
     */
    private function handleScheduleAction($order, $request, $admin)
    {
        $order->status = 'datée';
        $order->scheduled_date = $request->scheduled_date;
        $order->attempts_count = 0;
        $order->daily_attempts_count = 0;
        $order->last_attempt_at = null;
        $order->save();
        
        $historyNote = $admin->name . " a daté la commande pour le " . $request->scheduled_date;
        if ($request->notes) {
            $historyNote .= " : " . $request->notes;
        }
        
        $order->recordHistory('datation', $historyNote);
    }

    /**
     * Gérer l'action "Réactiver"
     */
    private function handleReactivateAction($order, $request, $admin, $queue)
    {
        if ($queue !== 'restock') {
            throw new \Exception('Action de réactivation uniquement disponible dans la file retour en stock');
        }
        
        if ($this->orderHasStockIssues($order)) {
            throw new \Exception('Impossible de réactiver: certains produits sont toujours en rupture de stock');
        }
        
        $order->is_suspended = false;
        $order->suspension_reason = null;
        $order->status = 'nouvelle';
        $order->attempts_count = 0;
        $order->daily_attempts_count = 0;
        $order->last_attempt_at = null;
        $order->save();
        
        $historyNote = $admin->name . " a réactivé la commande depuis la file restock";
        if ($request->notes) {
            $historyNote .= " : " . $request->notes;
        }
        
        $order->recordHistory('réactivation', $historyNote);
    }

    /**
     * Route de test pour vérifier la connectivité
     */
    public function test()
    {
        try {
            $admin = Auth::guard('admin')->user();
            $dbConnected = false;
            
            try {
                DB::connection()->getPdo();
                $dbConnected = true;
            } catch (\Exception $e) {
                $dbConnected = false;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'API fonctionnelle',
                'admin' => $admin ? $admin->name . ' (ID: ' . $admin->id . ')' : 'Non authentifié',
                'timestamp' => now()->toISOString(),
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
                'debug' => [
                    'database_connected' => $dbConnected,
                    'admin_settings_count' => $dbConnected ? AdminSetting::count() : 'N/A (DB Error)',
                    'orders_count_for_admin' => ($admin && $dbConnected) ? $admin->orders()->count() : 'N/A'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans test(): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur dans la fonction de test: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
}