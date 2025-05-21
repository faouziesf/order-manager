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
 * Obtient la prochaine commande d'une file d'attente au format JSON
 * 
 * @param string $queue Le nom de la file (standard, dated, old)
 * @return \Illuminate\Http\JsonResponse
 */
public function getNextOrderJson($queue)
{
    $admin = Auth::guard('admin')->user();
    $order = $this->findNextOrder($admin, $queue);
    
    if ($order) {
        // Charger les relations nécessaires
        $order->load(['region', 'city', 'items.product']);
        
        return response()->json([
            'hasOrder' => true,
            'order' => $order,
            'count' => 1 // On renvoie 1 car on a trouvé une commande
        ]);
    }
    
    return response()->json([
        'hasOrder' => false,
        'count' => 0
    ]);
}

    /**
     * Trouve la prochaine commande à traiter
     * 
     * @param \App\Models\Admin $admin L'administrateur actuel
     * @param string $queue Le nom de la file (standard, dated, old)
     * @return \App\Models\Order|null
     */
    private function findNextOrder($admin, $queue)
    {
        $query = Order::where('admin_id', $admin->id);
        
        // Charger les paramètres
        $maxDailyAttempts = (int)AdminSetting::get("{$queue}_max_daily_attempts", ($queue === 'standard' ? 3 : 2));
        $delayHours = (float)AdminSetting::get("{$queue}_delay_hours", ($queue === 'standard' ? 2.5 : ($queue === 'dated' ? 3.5 : 6)));
        $maxTotalAttempts = (int)AdminSetting::get("{$queue}_max_total_attempts", ($queue === 'standard' ? 9 : ($queue === 'dated' ? 5 : 0)));
        
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
            $standardMaxAttempts = (int)AdminSetting::get("standard_max_total_attempts", 9);
            
            $query->where('status', 'nouvelle')
                ->where('attempts_count', '>=', $standardMaxAttempts);
                
            if ($maxTotalAttempts > 0) {
                $query->where('attempts_count', '<', $maxTotalAttempts);
            }
        }
        
        // Conditions communes
        $query->where(function($q) use ($maxDailyAttempts, $delayHours) {
            $q->where('daily_attempts_count', '<', $maxDailyAttempts)
            ->orWhere(function($q2) use ($delayHours) {
                $q2->whereNull('last_attempt_at')
                    ->orWhere('last_attempt_at', '<=', now()->subHours($delayHours));
            });
        });
        
        // Exclure les commandes suspendues
        $query->notSuspended();
        
        // Tri par priorité et ancienneté (compatible avec SQLite)
        $query->orderByRaw("CASE 
                WHEN priority = 'vip' THEN 1 
                WHEN priority = 'urgente' THEN 2 
                WHEN priority = 'normale' THEN 3 
                ELSE 4 
            END")
            ->orderBy('attempts_count', 'asc')  // Moins de tentatives d'abord
            ->orderBy('created_at', 'asc');     // Plus anciennes commandes d'abord
        
        return $query->first();
    }
    

    /**
     * Obtient les compteurs des files d'attente
     */
    public function getQueueCounts()
    {
        $admin = Auth::guard('admin')->user();
        
        // Paramètres pour chaque file
        $standardMaxAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9); 
        
        // Compteurs
        $standard = Order::where('admin_id', $admin->id)
            ->where('status', 'nouvelle')
            ->where('attempts_count', '<', $standardMaxAttempts)
            ->where(function($q) {
                $q->where('is_suspended', false)
                ->orWhereNull('is_suspended');
            })
            ->count();
        
        $dated = Order::where('admin_id', $admin->id)
            ->where('status', 'datée')
            ->whereDate('scheduled_date', '<=', now())
            ->where(function($q) {
                $q->where('is_suspended', false)
                ->orWhereNull('is_suspended');
            })
            ->count();
        
        $old = Order::where('admin_id', $admin->id)
            ->where('status', 'nouvelle')
            ->where('attempts_count', '>=', $standardMaxAttempts)
            ->where(function($q) {
                $q->where('is_suspended', false)
                ->orWhereNull('is_suspended');
            })
            ->count();
        
        return response()->json([
            'standard' => $standard,
            'dated' => $dated,
            'old' => $old
        ]);
    }

    
    /**
     * Obtient la prochaine commande d'une file d'attente
     */
    public function getNextOrder($queue)
    {
        $admin = Auth::guard('admin')->user();
        $order = $this->findNextOrder($admin, $queue);
        
        // Si c'est une requête AJAX, retourner en JSON
        if (request()->ajax()) {
            if ($order) {
                // Charger les relations nécessaires
                $order->load(['region', 'city', 'items.product']);
                
                return response()->json([
                    'hasOrder' => true,
                    'order' => $order
                ]);
            }
            
            return response()->json([
                'hasOrder' => false
            ]);
        }
        
        // Pour les requêtes non-AJAX, retourner la vue
        if ($order) {
            return $this->getOrderForm($queue, $order);
        }
        
        return view('admin.process.no_orders');
    }
    
    /**
     * Obtient le formulaire pour une commande spécifique
     */
    public function getOrderForm($queue, Order $order)
    {
        $admin = Auth::guard('admin')->user();
        $regions = Region::with('cities')->orderBy('name')->get();
        $products = $admin->products()->where('is_active', true)->orderBy('name')->get();
        
        // Charger la commande avec ses relations
        $order->load(['region', 'city', 'items.product', 'history' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);
        
        // Déterminer le statut de la file actuelle
        $queueType = $queue;
        
        return view('admin.process.order-form', compact('order', 'regions', 'products', 'queueType'));
    }
    
    /**
     * Traite une action sur une commande
     */
    public function processAction(Request $request, Order $order)
    {
        $request->validate([
            'action' => 'nullable|string',
            'notes' => 'required|string|min:3',
            'status' => 'required|in:nouvelle,confirmée,annulée,datée,en_route,livrée',
            'priority' => 'required|in:normale,urgente,vip',
            'queue' => 'required|in:standard,dated,old',
        ]);
        
        // Validation supplémentaire selon l'action
        if ($request->action === 'confirm' || $request->status === 'confirmée') {
            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_governorate' => 'required|exists:regions,id',
                'customer_city' => 'required|exists:cities,id',
                'customer_address' => 'required|string',
                'confirmed_price' => 'required|numeric|min:0',
            ]);
        } elseif ($request->action === 'schedule' || $request->status === 'datée') {
            $request->validate([
                'scheduled_date' => 'required|date|after:today',
            ]);
        }
        
        DB::beginTransaction();
        
        try {
            // Traiter selon l'action
            if ($request->action) {
                switch ($request->action) {
                    case 'confirm':
                        $this->confirmOrder($order, $request);
                        break;
                        
                    case 'cancel':
                        $order->status = 'annulée';
                        $order->save();
                        $order->recordHistory('annulation', $request->notes);
                        break;
                        
                    case 'schedule':
                        $order->status = 'datée';
                        $order->scheduled_date = $request->scheduled_date;
                        $order->save();
                        $order->recordHistory('datation', $request->notes);
                        break;
                        
                    case 'call':
                        $this->recordCallAttempt($order, $request->notes);
                        break;
                }
            } else {
                // Mise à jour normale sans action spécifique
                $this->updateOrder($order, $request);
            }
            
            DB::commit();
            
            // Rediriger vers la file appropriée
            $queue = $request->queue;
            
            return redirect()->route('admin.process.interface') . '#' . $queue;
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors du traitement: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirme une commande
     */
    private function confirmOrder(Order $order, Request $request)
    {
        // Mettre à jour les infos client
        $order->customer_name = $request->customer_name;
        $order->customer_phone_2 = $request->customer_phone_2;
        $order->customer_governorate = $request->customer_governorate;
        $order->customer_city = $request->customer_city;
        $order->customer_address = $request->customer_address;
        $order->status = 'confirmée';
        $order->confirmed_price = $request->confirmed_price;
        
        // Traiter les produits
        $this->processOrderProducts($order, $request);
        
        // Décrémenter les stocks pour les commandes confirmées
        foreach ($order->items as $item) {
            $item->product->decrementStock($item->quantity);
        }
        
        $order->save();
        $order->recordHistory('confirmation', $request->notes);
    }
    
    /**
     * Met à jour une commande sans action spécifique
     */
    private function updateOrder(Order $order, Request $request)
    {
        $oldStatus = $order->status;
        
        // Mettre à jour les informations de base
        $order->status = $request->status;
        $order->priority = $request->priority;
        $order->customer_name = $request->customer_name;
        $order->customer_phone_2 = $request->customer_phone_2;
        $order->customer_governorate = $request->customer_governorate;
        $order->customer_city = $request->customer_city;
        $order->customer_address = $request->customer_address;
        $order->shipping_cost = $request->shipping_cost ?? 0;
        
        // Champs conditionnels
        if ($request->status === 'confirmée') {
            $order->confirmed_price = $request->confirmed_price;
        }
        
        if ($request->status === 'datée') {
            $order->scheduled_date = $request->scheduled_date;
        }
        
        // Traiter les produits
        $this->processOrderProducts($order, $request);
        
        $order->save();
        
        // Enregistrer l'historique avec le changement de statut
        $action = 'modification';
        if ($oldStatus !== $request->status) {
            switch ($request->status) {
                case 'confirmée':
                    $action = 'confirmation';
                    // Décrémenter les stocks
                    foreach ($order->items as $item) {
                        $item->product->decrementStock($item->quantity);
                    }
                    break;
                case 'annulée':
                    $action = 'annulation';
                    break;
                case 'datée':
                    $action = 'datation';
                    break;
                case 'en_route':
                    $action = 'en_route';
                    break;
                case 'livrée':
                    $action = 'livraison';
                    break;
            }
        }
        
        $order->recordHistory($action, $request->notes);
        
        // Incrémenter les tentatives si spécifié
        if ($request->increment_attempts) {
            $this->recordCallAttempt($order, $request->notes);
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
        
        return response()->json([
            'success' => true,
            'message' => 'Tentative enregistrée'
        ]);
    }
    
    /**
     * Traite les produits d'une commande
     */
    private function processOrderProducts(Order $order, Request $request)
    {
        if ($order->status === 'confirmée') {
            // Restaurer les stocks pour les commandes précédemment confirmées
            foreach ($order->items as $item) {
                $item->product->incrementStock($item->quantity);
            }
        }
        
        // Supprimer tous les produits existants
        $order->items()->delete();
        
        // Ajouter les nouveaux produits
        $totalPrice = 0;
        
        if ($request->has('products') && is_array($request->products)) {
            foreach ($request->products as $productData) {
                if (empty($productData['id']) && empty($productData['is_new'])) {
                    continue;
                }
                
                $product = null;
                
                if (!empty($productData['is_new']) && !empty($productData['name']) && isset($productData['price'])) {
                    // Créer un nouveau produit
                    $product = Product::create([
                        'admin_id' => $order->admin_id,
                        'name' => $productData['name'],
                        'price' => $productData['price'],
                        'stock' => 1000000,
                        'is_active' => true,
                        'needs_review' => true,
                    ]);
                } elseif (is_string($productData['id']) && strpos($productData['id'], 'new:') === 0) {
                    // Format : new:nom:prix
                    $parts = explode(':', $productData['id']);
                    if (count($parts) >= 3) {
                        $product = Product::create([
                            'admin_id' => $order->admin_id,
                            'name' => $parts[1],
                            'price' => $parts[2],
                            'stock' => 1000000,
                            'is_active' => true,
                            'needs_review' => true,
                        ]);
                    }
                } else {
                    // Produit existant
                    $product = Product::find($productData['id']);
                }
                
                if ($product) {
                    $quantity = isset($productData['quantity']) ? (int)$productData['quantity'] : 1;
                    $unitPrice = $product->price;
                    
                    $orderItem = $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * $quantity,
                    ]);
                    
                    $totalPrice += $orderItem->total_price;
                }
            }
        }
        
        // Mettre à jour le total
        $order->total_price = $totalPrice;
        $order->save();
    }
    

}