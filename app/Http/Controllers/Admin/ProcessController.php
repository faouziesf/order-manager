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
use Illuminate\Support\Facades\Gate;

class ProcessController extends Controller
{
    use ProcessTrait;

    // Static properties for getCounts cache
    private static $countsCache = null;
    private static $cacheTime = null;

    public function __construct()
    {
        $this->middleware('auth:admin'); //
        // Commentez temporairement cette partie : //
        /* //
        $this->middleware(function ($request, $next) { //
            if (!Gate::allows('view-process-interface', auth('admin')->user())) { //
                abort(403, 'Accès non autorisé à l\'interface de traitement'); //
            } //
            return $next($request); //
        }); //
        */ //
    }

    /**
     * Affiche l'interface de traitement unifiée
     */
    public function interface()
    {
        return view('admin.process.interface'); //
    }

    /**
     * Obtenir une commande de la file spécifiée
     */
    public function getQueue($queue)
    {
        try {
            if (!is_string($queue)) { //
                Log::error('getQueue: paramètre queue n\'est pas une chaîne', ['queue' => $queue, 'type' => gettype($queue)]); //
                return response()->json([ //
                    'error' => 'Paramètre de file d\'attente invalide (type incorrect)', //
                    'hasOrder' => false //
                ], 400); //
            }

            $queue = trim(strtolower($queue)); //
            
            if (!in_array($queue, ['standard', 'dated', 'old', 'restock'])) { //
                Log::error('getQueue: nom de file invalide', ['queue' => $queue]); //
                return response()->json([ //
                    'error' => 'File d\'attente invalide', //
                    'hasOrder' => false //
                ], 400); //
            }

            $admin = Auth::guard('admin')->user(); //
            
            if (!$admin) { //
                return response()->json([ //
                    'error' => 'Non authentifié', //
                    'hasOrder' => false //
                ], 401); //
            }

            $this->resetDailyCountersIfNeeded($admin); //

            // Gestion spéciale pour restock //
            if ($queue === 'restock') { //
                $order = $this->findNextRestockOrder($admin); //
            } else {
                $order = $this->findNextOrderExcludingStockIssues($admin, $queue); //
            }
            
            if ($order) { //
                $orderData = $this->formatOrderData($order); //
                
                return response()->json([ //
                    'hasOrder' => true, //
                    'order' => $orderData //
                ]); //
            }
            
            return response()->json([ //
                'hasOrder' => false, //
                'message' => 'Aucune commande disponible dans cette file' //
            ]); //

        } catch (\Exception $e) {
            Log::error('Erreur dans getQueue: ' . $e->getMessage(), [ //
                'queue' => $queue ?? 'undefined', //
                'queue_type' => isset($queue) ? gettype($queue) : 'undefined', //
                'trace' => $e->getTraceAsString() //
            ]);
            
            return response()->json([ //
                'error' => 'Erreur interne du serveur: ' . $e->getMessage(), //
                'hasOrder' => false //
            ], 500); //
        }
    }

    /**
     * Obtenir les compteurs de toutes les files
     */
    public function getCounts()
    {
        try {
            // Cache de 10 secondes pour éviter les calculs répétés //
            if (self::$countsCache && self::$cacheTime && now()->diffInSeconds(self::$cacheTime) < 10) { //
                $cachedResponse = self::$countsCache; //
                $cachedResponse['timestamp'] = now()->toISOString(); //
                return response()->json($cachedResponse); //
            }

            $admin = Auth::guard('admin')->user(); //
            
            if (!$admin) { //
                return response()->json([ //
                    'error' => 'Non authentifié' //
                ], 401); //
            }
            
            $this->resetDailyCountersIfNeeded($admin); //
            
            $standard = $this->getQueueCountExcludingStockIssues($admin, 'standard'); //
            $dated = $this->getQueueCountExcludingStockIssues($admin, 'dated'); //
            $old = $this->getQueueCountExcludingStockIssues($admin, 'old'); //
            
            // Compter les commandes d'examen (sans suspendues) //
            $examination = $this->countOrdersWithStockIssues($admin, false); //
            
            // Compter les commandes suspendues //
            $suspended = $admin->orders()->where('is_suspended', true)->whereNotIn('status', ['annulée', 'livrée'])->count(); //
            
            // Compter les commandes restock //
            $restock = $this->getRestockCountForInterface($admin); //
            
            $counts = [ //
                'standard' => $standard, //
                'dated' => $dated, //
                'old' => $old, //
                'examination' => $examination, //
                'suspended' => $suspended, //
                'restock' => $restock, //
                'timestamp' => now()->toISOString() //
            ];

            // Mettre en cache //
            self::$countsCache = $counts; //
            self::$cacheTime = now(); //
            
            return response()->json($counts); //

        } catch (\Exception $e) {
            Log::error('Erreur dans getCounts: ' . $e->getMessage()); //
            
            return response()->json([ //
                'error' => 'Erreur lors du chargement des compteurs', //
                'standard' => 0, //
                'dated' => 0, //
                'old' => 0, //
                'examination' => 0, //
                'suspended' => 0, //
                'restock' => 0 //
            ], 500); //
        }
    }

    /**
     * Traite une action sur une commande
     */
    public function processAction(Request $request, Order $order)
    {
        try {
            $request->validate([ //
                'action' => 'nullable|string', //
                'notes' => 'required|string|min:3', //
                'queue' => 'required|in:standard,dated,old,restock', //
            ]);
            
            $admin = Auth::guard('admin')->user(); //
            if ($order->admin_id !== $admin->id && !$admin->hasRole('super_admin')) { //
                return response()->json([ //
                    'error' => 'Accès refusé à cette commande' //
                ], 403); //
            }
            
            DB::beginTransaction(); //
            
            $action = $request->action; //
            $notes = $request->notes; //
            
            $actionDisplay = $action ?: 'modification_info'; //
            $historyNote = $admin->name . " a effectué l'action [{$actionDisplay}] : {$notes}"; //
            
            switch ($action) { //
                case 'call': //
                    $this->recordCallAttempt($order, $historyNote); //
                    break; //
                    
                case 'confirm': //
                    $this->validateConfirmation($request); //
                    $this->confirmOrder($order, $request, $historyNote); //
                    
                    if ($request->has('cart_items')) { //
                        $this->updateOrderItems($order, $request->cart_items); //
                    }
                    break; //
    
                case 'cancel': //
                    $order->status = 'annulée'; //
                    $order->is_suspended = false; //
                    $order->suspension_reason = null; //
                    $order->save(); //
                    $order->recordHistory('annulation', $historyNote); //
                    break; //
                    
                case 'schedule': //
                    $request->validate([ //
                        'scheduled_date' => 'required|date|after_or_equal:today', //
                    ]);
                    $order->status = 'datée'; //
                    $order->scheduled_date = $request->scheduled_date; //
                    $order->attempts_count = 0;  //
                    $order->daily_attempts_count = 0;  //
                    $order->last_attempt_at = null;  //
                    $order->save(); //
                    $order->recordHistory('datation', $historyNote); //
                    break; //
                
                case 'reactivate': //
                    // Action pour réactiver une commande depuis l'onglet retour en stock //
                    if ($request->queue !== 'restock') { //
                        DB::rollBack(); //
                        return response()->json([ //
                            'error' => 'Action de réactivation uniquement disponible dans la file retour en stock' //
                        ], 400); //
                    }
                    
                    if ($this->orderHasStockIssues($order)) { //
                        DB::rollBack(); //
                        return response()->json([ //
                            'error' => 'Impossible de réactiver: certains produits sont toujours en rupture de stock' //
                        ], 400); //
                    }
                    
                    $order->is_suspended = false; //
                    $order->suspension_reason = null; //
                    $order->status = 'nouvelle'; //
                    $order->attempts_count = 0; //
                    $order->daily_attempts_count = 0; //
                    $order->last_attempt_at = null; //
                    $order->save(); //
                    $order->recordHistory('réactivation', $historyNote . " (depuis file restock)"); //
                    break;     //

                default: //
                    $this->updateOrderInfo($order, $request); //
                    $order->recordHistory('modification_info', $historyNote); //
                    break; //
            }
            
            DB::commit(); //
            
            return response()->json([ //
                'success' => true, //
                'message' => 'Commande traitée avec succès', //
                'order_id' => $order->id, //
                'action' => $actionDisplay //
            ]); //
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); //
            return response()->json([ //
                'error' => 'Erreur de validation', //
                'errors' => $e->errors() //
            ], 422); //
            
        } catch (\Exception $e) {
            DB::rollBack(); //
            Log::error('Erreur dans processAction: ' . $e->getMessage(), [ //
                'order_id' => $order->id ?? 'unknown', //
                'action' => $request->action ?? 'unknown', //
                'trace' => $e->getTraceAsString() //
            ]);
            
            return response()->json([ //
                'error' => 'Erreur lors du traitement: ' . $e->getMessage() //
            ], 500); //
        }
    }

    /**
     * Route de test pour vérifier la connectivité
     */
    public function test()
    {
        try {
            $admin = Auth::guard('admin')->user(); //
            $dbConnected = false; //
            try {
                DB::connection()->getPdo(); //
                $dbConnected = true; //
            } catch (\Exception $e) {
                $dbConnected = false; //
            }
            
            return response()->json([ //
                'success' => true, //
                'message' => 'API fonctionnelle', //
                'admin' => $admin ? $admin->name . ' (ID: ' . $admin->id . ')' : 'Non authentifié', //
                'timestamp' => now()->toISOString(), //
                'php_version' => phpversion(), //
                'laravel_version' => app()->version(), //
                'debug' => [ //
                    'database_connected' => $dbConnected, //
                    'admin_settings_count' => $dbConnected ? AdminSetting::count() : 'N/A (DB Error)', //
                    'orders_count_for_admin' => ($admin && $dbConnected) ? $admin->orders()->count() : 'N/A' //
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans test(): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]); //
            return response()->json([ //
                'success' => false, //
                'error' => 'Erreur serveur dans la fonction de test: ' . $e->getMessage(), //
                'timestamp' => now()->toISOString() //
            ], 500); //
        }
    }

    /**
     * Trouver la prochaine commande pour le retour en stock
     */
    private function findNextRestockOrder($admin)
    {
        try {
            $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5); //
            $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2); //
            $delayHours = $this->getSetting('restock_delay_hours', 1); //

            $query = Order::where('admin_id', $admin->id) //
                ->with(['items.product']) //
                ->where('is_suspended', true) //
                ->whereIn('status', ['nouvelle', 'datée']) //
                ->where('attempts_count', '<', $maxTotalAttempts) //
                ->where('daily_attempts_count', '<', $maxDailyAttempts) //
                ->where(function($q) use ($delayHours) { //
                    $q->whereNull('last_attempt_at') //
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours)); //
                })
                ->orderBy('priority', 'desc') //
                ->orderBy('created_at', 'asc'); //

            $orders = $query->get(); //

            $eligibleOrder = $orders->filter(function($order) { //
                return !$this->orderHasStockIssues($order); //
            })->first(); //

            return $eligibleOrder; //

        } catch (\Exception $e) {
            Log::error('Erreur dans findNextRestockOrder: ' . $e->getMessage()); //
            return null; //
        }
    }

    /**
     * Trouver la prochaine commande en excluant celles avec problèmes de stock ET les suspendues
     */
    private function findNextOrderExcludingStockIssues($admin, $queue)
    {
        try {
            if (!$admin || !is_string($queue)) { //
                Log::error('findNextOrderExcludingStockIssues: paramètres invalides', [ //
                    'admin' => $admin ? $admin->id : null, //
                    'queue' => $queue, //
                    'queue_type' => gettype($queue) //
                ]);
                return null; //
            }

            $queue = trim(strtolower($queue)); //
            
            if (!in_array($queue, ['standard', 'dated', 'old'])) { //
                Log::error('findNextOrderExcludingStockIssues: queue invalide', ['queue' => $queue]); //
                return null; //
            }

            $query = Order::where('admin_id', $admin->id) //
                ->with(['items.product']) //
                ->where(function($q) { //
                    $q->where('is_suspended', false)->orWhereNull('is_suspended'); //
                });
            
            switch ($queue) { //
                case 'standard': //
                    $orders = $this->findStandardOrdersExcludingStockIssues($query); //
                    break; //
                case 'dated': //
                    $orders = $this->findDatedOrdersExcludingStockIssues($query); //
                    break; //
                case 'old': //
                    $orders = $this->findOldOrdersExcludingStockIssues($query); //
                    break; //
                default: //
                    return null; //
            }

            $filteredOrders = $orders->get()->filter(function($order) { //
                return !$this->orderHasStockIssues($order); //
            });

            return $filteredOrders->first(); //

        } catch (\Exception $e) {
            Log::error('Erreur dans findNextOrderExcludingStockIssues: ' . $e->getMessage(), [ //
                'queue' => $queue ?? 'undefined', //
                'admin_id' => $admin ? $admin->id : null //
            ]);
            return null; //
        }
    }

    /**
     * Helper: Commandes standard sans problèmes de stock et non suspendues
     */
    private function findStandardOrdersExcludingStockIssues($query)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9); //
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3); //
        $delayHours = $this->getSetting('standard_delay_hours', 2.5); //
        
        return $query->where('status', 'nouvelle') //
            ->where('attempts_count', '<', $maxTotalAttempts) //
            ->where('daily_attempts_count', '<', $maxDailyAttempts) //
            ->where(function($q) use ($delayHours) { //
                $q->whereNull('last_attempt_at') //
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours)); //
            })
            ->orderBy('priority', 'desc') //
            ->orderBy('attempts_count', 'asc') //
            ->orderBy('created_at', 'asc'); //
    }

    /**
     * Helper: Commandes datées sans problèmes de stock et non suspendues
     */
    private function findDatedOrdersExcludingStockIssues($query)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5); //
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2); //
        $delayHours = $this->getSetting('dated_delay_hours', 3.5); //
        
        return $query->where('status', 'datée') //
            ->whereDate('scheduled_date', '<=', now()) //
            ->where('attempts_count', '<', $maxTotalAttempts) //
            ->where('daily_attempts_count', '<', $maxDailyAttempts) //
            ->where(function($q) use ($delayHours) { //
                $q->whereNull('last_attempt_at') //
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours)); //
            })
            ->orderBy('scheduled_date', 'asc') //
            ->orderBy('priority', 'desc') //
            ->orderBy('attempts_count', 'asc'); //
    }

    /**
     * Helper: Commandes anciennes sans problèmes de stock et non suspendues
     */
    private function findOldOrdersExcludingStockIssues($query)
    {
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2); //
        $delayHours = $this->getSetting('old_delay_hours', 6); //
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0); //
        
        $baseQuery = $query->where('status', 'ancienne') //
            ->where('daily_attempts_count', '<', $maxDailyAttempts) //
            ->where(function($q) use ($delayHours) { //
                $q->whereNull('last_attempt_at') //
                  ->orWhere('updated_at', '<=', now()->subHours($delayHours)); //
            });
            
        if ($maxTotalAttempts > 0) { //
            $baseQuery->where('attempts_count', '<', $maxTotalAttempts); //
        }
        
        return $baseQuery->orderBy('priority', 'desc') //
            ->orderBy('attempts_count', 'asc') //
            ->orderBy('created_at', 'asc'); //
    }

    /**
     * Helper: Compter les commandes sans problèmes de stock et non suspendues
     */
    private function getQueueCountExcludingStockIssues($admin, $queue)
    {
        try {
            if (!is_string($queue) || !in_array($queue, ['standard', 'dated', 'old'])) { //
                Log::warning("getQueueCountExcludingStockIssues: paramètre queue invalide", ['queue' => $queue]); //
                return 0; //
            }

            $query = Order::where('admin_id', $admin->id) //
                ->with(['items.product']) //
                ->where(function($q) { //
                    $q->where('is_suspended', false)->orWhereNull('is_suspended'); //
                });
            
            switch ($queue) { //
                case 'standard': //
                    $orders = $this->findStandardOrdersExcludingStockIssues($query); //
                    break; //
                case 'dated': //
                    $orders = $this->findDatedOrdersExcludingStockIssues($query); //
                    break; //
                case 'old': //
                    $orders = $this->findOldOrdersExcludingStockIssues($query); //
                    break; //
                default: //
                    return 0; //
            }

            $filteredOrders = $orders->get()->filter(function($order) { //
                return !$this->orderHasStockIssues($order); //
            });

            return $filteredOrders->count(); //

        } catch (\Exception $e) {
            Log::error("Erreur dans getQueueCountExcludingStockIssues pour {$queue}: " . $e->getMessage()); //
            return 0; //
        }
    }

    /**
     * Helper: Compter les commandes avec problèmes de stock
     */
    private function countOrdersWithStockIssues($admin, $includeSuspended = true)
    {
        $orders = $admin->orders()->with(['items.product']); //
        
        if (!$includeSuspended) { //
            $orders->where(function($q) { //
                $q->where('is_suspended', false)->orWhereNull('is_suspended'); //
            });
        }
        
        return $orders->whereIn('status', ['nouvelle', 'confirmée', 'datée']) //
            ->get() //
            ->filter(function($order) { //
                return $this->orderHasStockIssues($order); //
            })->count(); //
    }

    /**
     * Compter les commandes pour l'interface restock
     */
    private function getRestockCountForInterface($admin)
    {
        try {
            $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5); //
            $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2); //
            $delayHours = $this->getSetting('restock_delay_hours', 1); //

            $query = Order::where('admin_id', $admin->id) //
                ->with(['items.product']) //
                ->where('is_suspended', true) //
                ->whereIn('status', ['nouvelle', 'datée']) //
                ->where('attempts_count', '<', $maxTotalAttempts) //
                ->where('daily_attempts_count', '<', $maxDailyAttempts) //
                ->where(function($q) use ($delayHours) { //
                    $q->whereNull('last_attempt_at') //
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours)); //
                });

            $orders = $query->get(); //

            $count = $orders->filter(function($order) { //
                return !$this->orderHasStockIssues($order); //
            })->count(); //

            return $count; //

        } catch (\Exception $e) {
            Log::error("Erreur dans getRestockCountForInterface: " . $e->getMessage()); //
            return 0; //
        }
    }
}