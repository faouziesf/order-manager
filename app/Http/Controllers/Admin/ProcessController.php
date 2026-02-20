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
use Illuminate\Validation\ValidationException;

class ProcessController extends Controller
{
    use ProcessTrait;

    // Cache statique pour les compteurs
    private static $countsCache = null;
    private static $cacheTime = null;

    public function __construct()
    {
        // Support admin guard only
        $this->middleware(function ($request, $next) {
            if (!auth('admin')->check()) {
                abort(401, 'Non authentifié');
            }
            return $next($request);
        });
    }

    /**
     * Get current authenticated user (admin only)
     */
    private function getCurrentUser()
    {
        return auth('admin')->user();
    }

    /**
     * Affiche l'interface de traitement unifiée
     */
    public function interface()
    {
        // Return admin view
        return view('admin.process.interface');
    }

    /**
     * API unifiée pour obtenir une commande selon la file - CORRIGÉE AVEC FILTRAGE STOCK
     */
    public function getQueueApi($queue)
    {
        try {
            $admin = $this->getCurrentUser();

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

            // Obtenir la commande selon le type de file avec filtrage stock amélioré
            $order = $this->findOrderForQueue($admin, $queue);
            
            if ($order) {
                // Double vérification du stock avant de retourner la commande
                if ($this->orderHasStockIssues($order)) {
                    Log::warning("Commande {$order->id} a des problèmes de stock détectés à la dernière minute");
                    
                    // Si c'est une commande non suspendue qui a des problèmes de stock, la suspendre
                    if (!$order->is_suspended && in_array($queue, ['standard', 'dated'])) {
                        $this->autoSuspendOrderForStock($order);
                    }
                    
                    // Rechercher une autre commande
                    return $this->getQueueApi($queue);
                }
                
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
     * Trouver une commande selon le type de file - LOGIQUE CORRIGÉE
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
     * File standard : commandes nouvelles, non suspendues, avec stock suffisant - CORRIGÉE
     */
    private function findStandardOrder($admin)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);

        // Pour les employés : uniquement les commandes qui leur sont assignées
        // Pour les managers et admins : toutes les commandes
        $query = Order::query();

        if ($admin->isEmployee()) {
            $query->where('employee_id', $admin->id);
        } elseif ($admin->isManager() && $admin->created_by) {
            $query->where('admin_id', $admin->created_by);
        } else {
            $query->where('admin_id', $admin->id);
        }

        $orders = $query
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
            ->whereDoesntHave('confirmiAssignment', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
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
            ->limit(50)
            ->get();

        Log::info("File standard: {$orders->count()} commandes trouvées avant filtrage stock");

        // Filtrer rigoureusement par stock disponible
        return $this->findFirstValidOrder($orders);
    }

    /**
     * File datée : commandes datées après leur date, non suspendues, avec stock suffisant - CORRIGÉE
     */
    private function findDatedOrder($admin)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);

        $query = Order::query();

        if ($admin->isEmployee()) {
            $query->where('employee_id', $admin->id);
        } elseif ($admin->isManager() && $admin->created_by) {
            $query->where('admin_id', $admin->created_by);
        } else {
            $query->where('admin_id', $admin->id);
        }

        $orders = $query
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
            ->whereDoesntHave('confirmiAssignment', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
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
            ->limit(50)
            ->get();

        Log::info("File datée: {$orders->count()} commandes trouvées avant filtrage stock");

        return $this->findFirstValidOrder($orders);
    }

    /**
     * File ancienne : commandes anciennes avec stock suffisant (même si suspendues) - CORRIGÉE
     */
    private function findOldOrder($admin)
    {
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0);

        $query = Order::query();

        if ($admin->isEmployee()) {
            $query->where('employee_id', $admin->id);
        } elseif ($admin->isManager() && $admin->created_by) {
            $query->where('admin_id', $admin->created_by);
        } else {
            $query->where('admin_id', $admin->id);
        }

        $query = $query
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
            ->whereDoesntHave('confirmiAssignment', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->where('status', 'ancienne')
            // Ne pas filtrer par suspension pour la file ancienne
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
            ->limit(50)
            ->get();

        Log::info("File ancienne: {$orders->count()} commandes trouvées avant filtrage stock");

        return $this->findFirstValidOrder($orders);
    }

    /**
     * File retour en stock : commandes suspendues nouvelles/datées avec stock maintenant suffisant - CORRIGÉE
     */
    private function findRestockOrder($admin)
    {
        $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2);
        $delayHours = $this->getSetting('restock_delay_hours', 1);

        $query = Order::query();

        if ($admin->isEmployee()) {
            $query->where('employee_id', $admin->id);
        } elseif ($admin->isManager() && $admin->created_by) {
            $query->where('admin_id', $admin->created_by);
        } else {
            $query->where('admin_id', $admin->id);
        }

        $orders = $query
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
            ->whereDoesntHave('confirmiAssignment', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where(function($q) {
                // Suspendues pour stock ou automatiquement
                $q->where('suspension_reason', 'like', '%stock%')
                  ->orWhere('suspension_reason', 'like', '%Stock%')
                  ->orWhere('suspension_reason', 'like', '%rupture%')
                  ->orWhere('suspension_reason', 'like', '%Rupture%');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        Log::info("File restock: {$orders->count()} commandes suspendues trouvées avant filtrage stock");

        // Pour restock, on cherche spécifiquement les commandes qui MAINTENANT ont le stock disponible
        return $this->findFirstValidOrder($orders, true);
    }

    /**
     * Trouver la première commande valide avec stock suffisant - NOUVELLE MÉTHODE OPTIMISÉE
     */
    private function findFirstValidOrder($orders, $forRestock = false)
    {
        $checkedCount = 0;
        $suspendedCount = 0;
        
        foreach ($orders as $order) {
            $checkedCount++;
            
            // Vérification rigoureuse du stock
            if (!$this->orderHasStockIssues($order)) {
                if ($forRestock) {
                    Log::info("Commande {$order->id} trouvée pour restock - stock maintenant disponible");
                }
                Log::info("Commande valide trouvée: {$order->id} (vérifiée parmi {$checkedCount} commandes)");
                return $order;
            } else {
                // Pour les files standard et datée, suspendre automatiquement les commandes avec problème de stock
                if (!$forRestock && !$order->is_suspended) {
                    $this->autoSuspendOrderForStock($order);
                    $suspendedCount++;
                }
            }
        }
        
        if ($suspendedCount > 0) {
            Log::info("Aucune commande valide trouvée. {$suspendedCount} commandes suspendues automatiquement pour problème de stock.");
        }
        
        Log::info("Aucune commande avec stock suffisant trouvée parmi {$checkedCount} commandes vérifiées.");
        return null;
    }

    /**
     * Obtenir les compteurs de toutes les files - CORRIGÉ AVEC FILTRAGE STOCK
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

            $admin = $this->getCurrentUser();

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
     * Compter les commandes standard disponibles - CORRIGÉ AVEC FILTRAGE STOCK
     */
    private function getStandardCount($admin)
    {
        $maxTotalAttempts = $this->getSetting('standard_max_total_attempts', 9);
        $maxDailyAttempts = $this->getSetting('standard_max_daily_attempts', 3);
        $delayHours = $this->getSetting('standard_delay_hours', 2.5);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
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

        $count = 0;
        foreach ($orders as $order) {
            if (!$this->orderHasStockIssues($order)) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Compter les commandes datées disponibles - CORRIGÉ AVEC FILTRAGE STOCK
     */
    private function getDatedCount($admin)
    {
        $maxTotalAttempts = $this->getSetting('dated_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('dated_max_daily_attempts', 2);
        $delayHours = $this->getSetting('dated_delay_hours', 3.5);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
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

        $count = 0;
        foreach ($orders as $order) {
            if (!$this->orderHasStockIssues($order)) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Compter les commandes anciennes disponibles - CORRIGÉ AVEC FILTRAGE STOCK
     */
    private function getOldCount($admin)
    {
        $maxDailyAttempts = $this->getSetting('old_max_daily_attempts', 2);
        $delayHours = $this->getSetting('old_delay_hours', 6);
        $maxTotalAttempts = $this->getSetting('old_max_total_attempts', 0);

        $query = Order::where('admin_id', $admin->id)
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
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

        $count = 0;
        foreach ($orders as $order) {
            if (!$this->orderHasStockIssues($order)) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Compter les commandes en examen - CORRIGÉ
     */
    private function getExaminationCount($admin)
    {
        $orders = $admin->orders()
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
            ->where(function($q) {
                $q->where('is_suspended', false)->orWhereNull('is_suspended');
            })
            ->whereIn('status', ['nouvelle', 'confirmée', 'datée'])
            ->get();
        
        $count = 0;
        foreach ($orders as $order) {
            if ($this->orderHasStockIssues($order)) {
                $count++;
            }
        }
        
        return $count;
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
     * Compter les commandes de retour en stock - CORRIGÉ AVEC FILTRAGE STOCK
     */
    private function getRestockCount($admin)
    {
        $maxTotalAttempts = $this->getSetting('restock_max_total_attempts', 5);
        $maxDailyAttempts = $this->getSetting('restock_max_daily_attempts', 2);
        $delayHours = $this->getSetting('restock_delay_hours', 1);

        $orders = Order::where('admin_id', $admin->id)
            ->with(['items.product' => function($query) {
                $query->where('is_active', true);
            }])
            ->where('is_suspended', true)
            ->whereIn('status', ['nouvelle', 'datée'])
            ->where(function($q) {
                $q->where('suspension_reason', 'like', '%stock%')
                  ->orWhere('suspension_reason', 'like', '%Stock%')
                  ->orWhere('suspension_reason', 'like', '%rupture%')
                  ->orWhere('suspension_reason', 'like', '%Rupture%');
            })
            ->where('attempts_count', '<', $maxTotalAttempts)
            ->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->where(function($q) use ($delayHours) {
                $q->whereNull('last_attempt_at')
                  ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            })
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            // Pour restock, on compte les commandes qui maintenant ont le stock disponible
            if (!$this->orderHasStockIssues($order)) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Traiter une action sur une commande - CORRIGÉ AVEC VÉRIFICATION STOCK
     */
    public function processAction(Request $request, Order $order)
    {
        try {
            $admin = $this->getCurrentUser();
            
            if ($order->admin_id !== $admin->id) {
                return response()->json([
                    'error' => 'Accès refusé à cette commande'
                ], 403);
            }

            // Bloquer le traitement si la commande est gérée par Confirmi
            $confirmiAssignment = \App\Models\ConfirmiOrderAssignment::where('order_id', $order->id)
                ->whereNotIn('status', ['cancelled'])
                ->first();
            if ($confirmiAssignment) {
                return response()->json([
                    'error' => 'Cette commande est en cours de traitement par l\'équipe Confirmi. Vous ne pouvez pas la modifier.',
                    'locked_by_confirmi' => true,
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
            
        } catch (ValidationException $e) {
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
     * Validation selon l'action - CORRIGÉE
     */
    private function validateAction($request, $action)
    {
        $rules = [];
        $messages = [];
        
        switch ($action) {
            case 'call':
                $rules = [
                    'notes' => 'required|string|min:3|max:1000',
                ];
                $messages = [
                    'notes.required' => 'Une note est obligatoire pour cette action',
                    'notes.min' => 'La note doit contenir au moins 3 caractères',
                ];
                break;
                
            case 'confirm':
                $rules = [
                    'confirmed_price' => 'required|numeric|min:0.001',
                    'customer_name' => 'required|string|min:2|max:255',
                    'customer_governorate' => 'required|string|max:255',
                    'customer_city' => 'required|string|max:255',
                    'customer_address' => 'required|string|min:5|max:500',
                    'cart_items' => 'required|array|min:1',
                    'cart_items.*.product_id' => 'required|exists:products,id',
                    'cart_items.*.quantity' => 'required|integer|min:1',
                ];
                $messages = [
                    'confirmed_price.required' => 'Le prix total confirmé est obligatoire',
                    'confirmed_price.min' => 'Le prix doit être supérieur à 0',
                    'customer_name.required' => 'Le nom du client est obligatoire',
                    'customer_governorate.required' => 'Le gouvernorat est obligatoire',
                    'customer_city.required' => 'La ville est obligatoire',
                    'customer_address.required' => 'L\'adresse est obligatoire',
                    'customer_address.min' => 'L\'adresse doit contenir au moins 5 caractères',
                    'cart_items.required' => 'Au moins un produit est requis',
                ];
                break;
                
            case 'cancel':
                $rules = [
                    'notes' => 'required|string|min:3|max:1000',
                ];
                $messages = [
                    'notes.required' => 'Une raison d\'annulation est obligatoire',
                    'notes.min' => 'La raison doit contenir au moins 3 caractères',
                ];
                break;
                
            case 'schedule':
                $rules = [
                    'scheduled_date' => 'required|date|after_or_equal:today',
                    'notes' => 'nullable|string|max:1000',
                ];
                $messages = [
                    'scheduled_date.required' => 'Une date est obligatoire',
                    'scheduled_date.after_or_equal' => 'La date ne peut pas être dans le passé',
                ];
                break;
                
            case 'reactivate':
                $rules = [
                    'notes' => 'nullable|string|max:1000',
                    'queue' => 'required|in:restock',
                ];
                break;
        }
        
        $request->validate($rules, $messages);
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
     * Gérer l'action "Confirmer" - CORRIGÉE AVEC VÉRIFICATION STOCK ET DÉCRÉMENTATION
     */
    private function handleConfirmAction($order, $request, $admin)
    {
        // Vérifier le stock avant de confirmer - VÉRIFICATION CRITIQUE
        if ($this->orderHasStockIssues($order)) {
            throw new \Exception('Impossible de confirmer: certains produits ne sont plus en stock suffisant. Veuillez actualiser la page.');
        }
        
        // Vérification supplémentaire du stock des items du panier
        if ($request->has('cart_items')) {
            foreach ($request->cart_items as $cartItem) {
                $product = \App\Models\Product::where('id', $cartItem['product_id'])
                    ->where('admin_id', $order->admin_id)
                    ->where('is_active', true)
                    ->first();
                
                if (!$product) {
                    throw new \Exception("Produit {$cartItem['product_id']} non trouvé ou inactif");
                }
                
                if ((int)$product->stock < (int)$cartItem['quantity']) {
                    throw new \Exception("Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock}, quantité demandée: {$cartItem['quantity']}");
                }
            }
        }
        
        // Valider les données de confirmation
        $this->validateConfirmation($request);
        
        // Mettre à jour les informations de la commande
        $this->confirmOrder($order, $request, "Commande confirmée par " . $admin->name);
        
        // Mettre à jour les items et décrémenter le stock - POINT CRITIQUE
        if ($request->has('cart_items')) {
            $this->updateOrderItems($order, $request->cart_items);
        }
        
        $historyNote = $admin->name . " a confirmé la commande pour " . $request->confirmed_price . " TND";
        if ($request->notes) {
            $historyNote .= " : " . $request->notes;
        }
        
        $order->recordHistory('confirmation', $historyNote);
        
        Log::info("Commande {$order->id} confirmée avec succès par admin {$admin->id}");
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
            $admin = $this->getCurrentUser();
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