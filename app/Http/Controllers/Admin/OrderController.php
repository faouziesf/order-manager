<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Affiche la liste des commandes
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = $admin->orders();
        
        // Si la requête est AJAX (pour la recherche en temps réel)
        if ($request->ajax() && $request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_address', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
            
            $orders = $query->take(10)->get();
            return response()->json(['orders' => $orders]);
        }
        
        // Filtres de recherche standard
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_address', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filtre par assignation
        if ($request->filled('assigned')) {
            $query->where('is_assigned', $request->assigned == 'yes');
        }
        
        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortField, $sortOrder);
        
        $orders = $query->with(['region', 'city'])->paginate(10)->withQueryString();
        
        // Statistiques
        $totalOrders = $admin->orders()->count();
        $newOrders = $admin->orders()->where('status', 'nouvelle')->count();
        $confirmedOrders = $admin->orders()->where('status', 'confirmée')->count();
        $scheduledOrders = $admin->orders()->where('status', 'datée')->count();
        
        return view('admin.orders.index', compact(
            'orders', 
            'totalOrders', 
            'newOrders', 
            'confirmedOrders', 
            'scheduledOrders'
        ));
    }

    /**
     * Affiche le formulaire de création de commande
     */
    public function create()
    {
        $admin = Auth::guard('admin')->user();
        $regions = Region::with('cities')->orderBy('name')->get();
        $products = $admin->products()->where('is_active', true)->orderBy('name')->get();
        
        return view('admin.orders.create', compact('regions', 'products'));
    }

    /**
     * Enregistre une nouvelle commande
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required|string|max:20',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone_2' => 'nullable|string|max:20',
            'customer_governorate' => 'nullable|exists:regions,id',
            'customer_city' => 'nullable|exists:cities,id',
            'customer_address' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:nouvelle,confirmée',
            'priority' => 'required|in:normale,urgente,vip',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        // Validation supplémentaire si le statut est "confirmée"
        if ($request->status === 'confirmée') {
            if (empty($request->customer_name) || empty($request->customer_governorate) || 
                empty($request->customer_city) || empty($request->customer_address)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Tous les champs client sont obligatoires pour une commande confirmée.');
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Créer la commande
            $order = new Order();
            $order->admin_id = $admin->id;
            $order->customer_phone = $request->customer_phone;
            $order->customer_name = $request->customer_name;
            $order->customer_phone_2 = $request->customer_phone_2;
            $order->customer_governorate = $request->customer_governorate;
            $order->customer_city = $request->customer_city;
            $order->customer_address = $request->customer_address;
            $order->shipping_cost = $request->shipping_cost ?? 0;
            $order->status = $request->status;
            $order->priority = $request->priority;
            $order->notes = $request->notes;
            $order->save();
            
            // Ajouter les produits à la commande
            $totalPrice = 0;
            foreach ($request->products as $productData) {
                $product = null;
                
                // Vérifier si c'est un produit existant ou un nouveau produit
                if (isset($productData['id']) && is_numeric($productData['id']) && $productData['id'] > 0) {
                    $product = Product::find($productData['id']);
                } else if (isset($productData['name']) && isset($productData['price'])) {
                    // Création d'un nouveau produit à la volée
                    $product = new Product([
                        'admin_id' => $admin->id,
                        'name' => $productData['name'],
                        'price' => $productData['price'],
                        'stock' => 1000000, // Stock énorme par défaut
                        'is_active' => true,
                    ]);
                    $product->save();
                }
                
                if ($product) {
                    $orderItem = new OrderItem([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $product->price,
                        'total_price' => $product->price * $productData['quantity'],
                    ]);
                    $orderItem->save();
                    
                    $totalPrice += $orderItem->total_price;
                    
                    // Décrémenter le stock si la commande est confirmée
                    if ($request->status === 'confirmée') {
                        $product->decrementStock($productData['quantity']);
                    }
                }
            }
            
            // Mettre à jour le prix total de la commande
            $order->total_price = $totalPrice;
            $order->save();
            
            // Enregistrer l'historique
            $order->recordHistory('création', $request->notes);
            
            DB::commit();
            
            return redirect()->route('admin.orders.index')
                ->with('success', 'Commande créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la commande: ' . $e->getMessage());
        }
    }

    /**
     * Affiche le formulaire de modification d'une commande
     */
    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        
        $admin = Auth::guard('admin')->user();
        $regions = Region::with('cities')->orderBy('name')->get();
        $products = $admin->products()->where('is_active', true)->orderBy('name')->get();
        
        // Charger les produits de la commande
        $order->load('items.product');
        
        // Charger l'historique manuellement pour éviter les erreurs de relations
        try {
            $history = $order->history()->orderBy('created_at', 'desc')->get();
            $order->setRelation('history', $history);
        } catch (\Exception $e) {
            // En cas d'erreur, définir un historique vide
            $order->setRelation('history', collect([]));
        }
        
        return view('admin.orders.edit', compact('order', 'regions', 'products'));
    }

    /**
     * Met à jour une commande existante
     */
    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'customer_phone' => 'required|string|max:20',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone_2' => 'nullable|string|max:20',
            'customer_governorate' => 'nullable|exists:regions,id',
            'customer_city' => 'nullable|exists:cities,id',
            'customer_address' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:nouvelle,confirmée,annulée,datée,en_route,livrée',
            'priority' => 'required|in:normale,urgente,vip',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required_without:products.*.is_new',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.name' => 'required_if:products.*.is_new,1',
            'products.*.price' => 'required_if:products.*.is_new,1|numeric|min:0',
            'confirmed_price' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date|required_if:status,datée',
        ]);
        
        // Validation supplémentaire si le statut est "confirmée"
        if ($request->status === 'confirmée') {
            if (empty($request->customer_name) || empty($request->customer_governorate) || 
                empty($request->customer_city) || empty($request->customer_address)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Tous les champs client sont obligatoires pour une commande confirmée.');
            }
            
            // Pour les commandes confirmées, le prix confirmé est obligatoire
            if (empty($request->confirmed_price)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Le prix confirmé est obligatoire pour une commande confirmée.');
            }
        }
        
        // Validation supplémentaire si le statut est "datée"
        if ($request->status === 'datée' && empty($request->scheduled_date)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'La date de livraison est obligatoire pour une commande datée.');
        }
        
        try {
            DB::beginTransaction();
            
            // Enregistrer l'état précédent pour l'historique
            $statusBefore = $order->status;
            $changes = [];
            
            // Mettre à jour les informations de la commande
            if ($order->customer_name !== $request->customer_name) $changes['customer_name'] = ['old' => $order->customer_name, 'new' => $request->customer_name];
            if ($order->customer_phone !== $request->customer_phone) $changes['customer_phone'] = ['old' => $order->customer_phone, 'new' => $request->customer_phone];
            if ($order->customer_phone_2 !== $request->customer_phone_2) $changes['customer_phone_2'] = ['old' => $order->customer_phone_2, 'new' => $request->customer_phone_2];
            if ($order->customer_governorate != $request->customer_governorate) $changes['customer_governorate'] = ['old' => $order->customer_governorate, 'new' => $request->customer_governorate];
            if ($order->customer_city != $request->customer_city) $changes['customer_city'] = ['old' => $order->customer_city, 'new' => $request->customer_city];
            if ($order->customer_address !== $request->customer_address) $changes['customer_address'] = ['old' => $order->customer_address, 'new' => $request->customer_address];
            if ($order->shipping_cost != $request->shipping_cost) $changes['shipping_cost'] = ['old' => $order->shipping_cost, 'new' => $request->shipping_cost];
            if ($order->priority !== $request->priority) $changes['priority'] = ['old' => $order->priority, 'new' => $request->priority];
            if ($order->confirmed_price != $request->confirmed_price) $changes['confirmed_price'] = ['old' => $order->confirmed_price, 'new' => $request->confirmed_price];
            if ($order->scheduled_date != $request->scheduled_date) $changes['scheduled_date'] = ['old' => $order->scheduled_date, 'new' => $request->scheduled_date];
            
            $order->customer_name = $request->customer_name;
            $order->customer_phone = $request->customer_phone;
            $order->customer_phone_2 = $request->customer_phone_2;
            $order->customer_governorate = $request->customer_governorate;
            $order->customer_city = $request->customer_city;
            $order->customer_address = $request->customer_address;
            $order->shipping_cost = $request->shipping_cost ?? 0;
            $order->priority = $request->priority;
            $order->confirmed_price = $request->confirmed_price;
            $order->scheduled_date = $request->scheduled_date;
            
            // Si le statut change, enregistrer l'action spécifique
            $statusAction = 'modification';
            if ($statusBefore !== $request->status) {
                $changes['status'] = ['old' => $statusBefore, 'new' => $request->status];
                
                switch ($request->status) {
                    case 'confirmée':
                        $statusAction = 'confirmation';
                        break;
                    case 'annulée':
                        $statusAction = 'annulation';
                        break;
                    case 'datée':
                        $statusAction = 'datation';
                        break;
                    case 'en_route':
                        $statusAction = 'en_route';
                        break;
                    case 'livrée':
                        $statusAction = 'livraison';
                        break;
                }
            }
            
            $order->status = $request->status;
            $order->save();
            
            // Gérer les produits
            
            // 1. Supprimer tous les produits actuels et réaffecter les stocks
            if ($statusBefore === 'confirmée') {
                foreach ($order->items as $item) {
                    $item->product->incrementStock($item->quantity);
                }
            }
            
            // 2. Supprimer les anciens produits
            $order->items()->delete();
            
            // 3. Ajouter les nouveaux produits
            $totalPrice = 0;
            foreach ($request->products as $productData) {
                // Ignorer les lignes vides
                if (empty($productData['id']) && empty($productData['is_new'])) {
                    continue;
                }
                
                $product = null;
                
                // Vérifier si c'est un nouveau produit ou un produit existant
                if (!empty($productData['is_new']) && !empty($productData['name']) && isset($productData['price'])) {
                    // Création d'un nouveau produit
                    $product = new Product([
                        'admin_id' => $order->admin_id,
                        'name' => $productData['name'],
                        'price' => $productData['price'],
                        'stock' => 1000000, // Stock énorme par défaut
                        'is_active' => true,
                    ]);
                    $product->save();
                } 
                elseif (!empty($productData['id'])) {
                    // Si l'ID commence par "new:", c'est un nouveau produit déjà traité par le formulaire
                    if (is_string($productData['id']) && strpos($productData['id'], 'new:') === 0) {
                        $parts = explode(':', $productData['id']);
                        if (count($parts) >= 3) {
                            $productName = $parts[1];
                            $productPrice = $parts[2];
                            
                            $product = new Product([
                                'admin_id' => $order->admin_id,
                                'name' => $productName,
                                'price' => $productPrice,
                                'stock' => 1000000,
                                'is_active' => true,
                            ]);
                            $product->save();
                        }
                    } else {
                        // Produit existant
                        $product = Product::find($productData['id']);
                    }
                }
                
                if ($product) {
                    $orderItem = new OrderItem([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $product->price,
                        'total_price' => $product->price * $productData['quantity'],
                    ]);
                    $orderItem->save();
                    
                    $totalPrice += $orderItem->total_price;
                    
                    // Décrémenter le stock si la commande est confirmée
                    if ($request->status === 'confirmée') {
                        $product->decrementStock($productData['quantity']);
                    }
                }
            }
            
            // Mettre à jour le prix total de la commande
            $order->total_price = $totalPrice;
            $order->save();
            
            // Gestion des compteurs de tentatives pour l'action "appel effectué"
            if ($request->has('increment_attempts') && $request->increment_attempts) {
                $order->incrementAttempts();
            }
            
            // Enregistrer l'historique
            $order->recordHistory($statusAction, $request->notes, $changes, $statusBefore, $request->status);
            
            DB::commit();
            
            return redirect()->route('admin.orders.index')
                ->with('success', 'Commande mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de la commande: ' . $e->getMessage());
        }
    }

    /**
     * Supprime une commande
     */
    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        
        try {
            DB::beginTransaction();
            
            // Si la commande était confirmée, restaurer les stocks
            if ($order->status === 'confirmée') {
                foreach ($order->items as $item) {
                    $item->product->incrementStock($item->quantity);
                }
            }
            
            // Supprimer la commande
            $order->delete();
            
            DB::commit();
            
            return redirect()->route('admin.orders.index')
                ->with('success', 'Commande supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la commande: ' . $e->getMessage());
        }
    }

    /**
     * Affiche l'historique d'une commande
     */
    public function showHistory(Order $order)
    {
        $this->authorize('view', $order);
        
        $history = $order->history()->with(['admin', 'manager', 'employee'])->orderBy('created_at', 'desc')->get();
        
        return view('admin.orders.history', compact('order', 'history'));
    }

    /**
     * Ajoute une tentative d'appel à l'historique
     */
    public function recordAttempt(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'notes' => 'required|string|min:3',
        ]);
        
        $order->incrementAttempts();
        $order->recordHistory('tentative', $request->notes);
        
        return redirect()->back()->with('success', 'Tentative enregistrée avec succès.');
    }

    /**
     * Obtient les villes pour un gouvernorat spécifique (pour AJAX)
     */
    public function getCities(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
        ]);
        
        $cities = City::where('region_id', $request->region_id)
            ->orderBy('name')
            ->get(['id', 'name', 'shipping_cost']);
        
        return response()->json($cities);
    }

    /**
     * Recherche de produits (pour AJAX)
     */
    public function searchProducts(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $query = $admin->products();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        $query->where('is_active', true)->orderBy('name');
        
        $products = $query->take(10)->get();
        
        return response()->json($products);
    }
}