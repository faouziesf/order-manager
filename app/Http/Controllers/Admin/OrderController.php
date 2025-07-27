<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\City;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Afficher la liste des commandes
     */
    public function index(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            // Construire la query de base
            $query = $admin->orders()->with(['items.product', 'employee']);
            
            // Appliquer les filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('customer_phone_2', 'like', "%{$search}%")
                      ->orWhere('customer_address', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            if ($request->filled('assigned')) {
                if ($request->assigned === 'yes') {
                    $query->where('is_assigned', true);
                } elseif ($request->assigned === 'no') {
                    $query->where('is_assigned', false);
                }
            }
            
            // Tri
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            
            $allowedSortFields = ['id', 'created_at', 'customer_name', 'status', 'priority'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            // Pagination
            $orders = $query->paginate(20);
            
            // Statistiques rapides
            $totalOrders = $admin->orders()->count();
            $newOrders = $admin->orders()->where('status', 'nouvelle')->count();
            $confirmedOrders = $admin->orders()->where('status', 'confirmée')->count();
            $scheduledOrders = $admin->orders()->where('status', 'datée')->count();
            
            // Si c'est une requête AJAX, retourner JSON
            if ($request->ajax()) {
                return response()->json([
                    'orders' => $orders,
                    'stats' => [
                        'total' => $totalOrders,
                        'new' => $newOrders,
                        'confirmed' => $confirmedOrders,
                        'scheduled' => $scheduledOrders
                    ]
                ]);
            }
            
            return view('admin.orders.index', compact(
                'orders', 
                'totalOrders', 
                'newOrders', 
                'confirmedOrders', 
                'scheduledOrders'
            ));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans OrderController@index: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Erreur lors du chargement des commandes'], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des commandes');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        try {
            $regions = Region::orderBy('name')->get();
            $employees = Auth::guard('admin')->user()->employees()->where('is_active', true)->get();
            return view('admin.orders.create', compact('regions', 'employees'));
        } catch (\Exception $e) {
            Log::error('Erreur dans OrderController@create: ' . $e->getMessage());
            return redirect()->route('admin.orders.index')->with('error', 'Erreur lors du chargement du formulaire');
        }
    }

    /**
     * Créer une nouvelle commande - VERSION CORRIGÉE COMPLÈTE
     */
    public function store(Request $request)
    {
        try {
            // Validation adaptée selon le statut
            $baseRules = [
                'customer_phone' => 'required|string|max:20',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'status' => 'required|in:nouvelle,confirmée',
                'priority' => 'required|in:normale,urgente,vip',
                'employee_id' => 'nullable|exists:employees,id',
            ];

            // Si statut confirmée, tous les champs sont obligatoires (Y COMPRIS PRIX TOTAL)
            if ($request->status === 'confirmée') {
                $additionalRules = [
                    'customer_name' => 'required|string|max:255',
                    'customer_governorate' => 'required|exists:regions,id',
                    'customer_city' => 'required|exists:cities,id',
                    'customer_address' => 'required|string|max:500',
                    'total_price' => 'required|numeric|min:0', // AJOUT OBLIGATOIRE
                ];
                $baseRules = array_merge($baseRules, $additionalRules);
            } else {
                // Pour statut nouvelle, seuls téléphone et produits obligatoires
                $baseRules['customer_name'] = 'nullable|string|max:255';
                $baseRules['customer_governorate'] = 'nullable|exists:regions,id';
                $baseRules['customer_city'] = 'nullable|exists:cities,id';
                $baseRules['customer_address'] = 'nullable|string|max:500';
                $baseRules['total_price'] = 'nullable|numeric|min:0';
            }

            // Validation des champs optionnels
            $baseRules['customer_phone_2'] = 'nullable|string|max:20';
            $baseRules['notes'] = 'nullable|string|max:1000';

            $validated = $request->validate($baseRules, [
                'customer_phone.required' => 'Le numéro de téléphone est obligatoire',
                'customer_name.required' => 'Le nom du client est obligatoire pour une commande confirmée',
                'customer_governorate.required' => 'Le gouvernorat est obligatoire pour une commande confirmée',
                'customer_city.required' => 'La ville est obligatoire pour une commande confirmée',
                'customer_address.required' => 'L\'adresse est obligatoire pour une commande confirmée',
                'total_price.required' => 'Le prix total est obligatoire pour une commande confirmée',
                'products.required' => 'Veuillez ajouter au moins un produit',
                'products.min' => 'Veuillez ajouter au moins un produit',
                'products.*.id.exists' => 'Un ou plusieurs produits sélectionnés n\'existent pas',
                'products.*.quantity.required' => 'La quantité est obligatoire pour tous les produits',
                'products.*.quantity.min' => 'La quantité doit être au moins de 1',
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();

            // Vérifier et préparer les produits
            $productsData = [];
            $totalPrice = 0;

            foreach ($validated['products'] as $productData) {
                $product = Product::where('id', $productData['id'])
                    ->where('admin_id', $admin->id)
                    ->where('is_active', true)
                    ->first();
                
                if (!$product) {
                    throw new \Exception("Le produit avec l'ID {$productData['id']} n'existe pas ou n'est pas actif.");
                }

                $quantity = (int) $productData['quantity'];
                
                // Si le statut sera confirmé, vérifier le stock
                if ($validated['status'] === 'confirmée' && $product->stock < $quantity) {
                    throw new \Exception("Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock}, quantité demandée: {$quantity}");
                }

                $unitPrice = (float) $product->price;
                $itemTotal = $quantity * $unitPrice;
                $totalPrice += $itemTotal;

                $productsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                ];
            }

            // Créer la commande
            $order = new Order();
            $order->admin_id = $admin->id;
            $order->customer_phone = $validated['customer_phone'];
            $order->customer_name = $validated['customer_name'] ?? null;
            $order->customer_phone_2 = $request->customer_phone_2 ?? null;
            $order->customer_governorate = $validated['customer_governorate'] ?? null;
            $order->customer_city = $validated['customer_city'] ?? null;
            $order->customer_address = $validated['customer_address'] ?? null;
            $order->notes = $request->notes ?? null;
            $order->status = $validated['status'];
            $order->priority = $validated['priority'];
            
            // Utiliser le prix personnalisé si fourni pour commande confirmée, sinon calculé
            $order->total_price = ($validated['status'] === 'confirmée' && $request->filled('total_price')) 
                ? (float) $request->total_price 
                : $totalPrice;
            
            $order->attempts_count = 0;
            $order->daily_attempts_count = 0;
            
            // Assigner à un employé si spécifié
            if (!empty($validated['employee_id'])) {
                $employee = Employee::where('id', $validated['employee_id'])
                    ->where('admin_id', $admin->id)
                    ->where('is_active', true)
                    ->first();
                
                if ($employee) {
                    $order->employee_id = $employee->id;
                    $order->is_assigned = true;
                } else {
                    $order->employee_id = null;
                    $order->is_assigned = false;
                }
            } else {
                $order->employee_id = null;
                $order->is_assigned = false;
            }

            $order->save();

            // Ajouter les produits et décrémenter le stock si confirmé
            foreach ($productsData as $productData) {
                $order->items()->create([
                    'product_id' => $productData['product']->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'total_price' => $productData['total_price'],
                ]);

                // Si commande confirmée, décrémenter le stock
                if ($validated['status'] === 'confirmée') {
                    $productData['product']->decrement('stock', $productData['quantity']);
                    Log::info("Stock décrémenté pour produit {$productData['product']->id}: -{$productData['quantity']}");
                }
            }

            // Vérifier le stock et suspendre si nécessaire (pour les commandes non confirmées)
            if ($validated['status'] !== 'confirmée') {
                $order->checkStockAndUpdateStatus();
            }

            // Enregistrer dans l'historique
            $order->recordHistory('création', 'Commande créée par ' . $admin->name);

            DB::commit();

            return redirect()->route('admin.orders.index')->with('success', 'Commande créée avec succès');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans OrderController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Afficher une commande
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        $order->load(['items.product', 'history', 'employee']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Order $order)
    {
        try {
            $this->authorize('update', $order);
            
            $order->load(['items.product']);
            $regions = Region::orderBy('name')->get();
            $cities = [];
            $employees = Auth::guard('admin')->user()->employees()->where('is_active', true)->get();
            
            if ($order->customer_governorate) {
                $cities = City::where('region_id', $order->customer_governorate)->orderBy('name')->get();
            }
            
            return view('admin.orders.edit', compact('order', 'regions', 'cities', 'employees'));
        } catch (\Exception $e) {
            Log::error('Erreur dans OrderController@edit: ' . $e->getMessage());
            return redirect()->route('admin.orders.index')->with('error', 'Erreur lors du chargement de la commande');
        }
    }

    /**
     * Mettre à jour une commande - VERSION CORRIGÉE
     */
    public function update(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            // VALIDATION CONDITIONNELLE SELON STATUT
            $validationRules = [
                'customer_phone' => 'required|string|max:20',
                'customer_phone_2' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:1000',
                'status' => 'required|in:nouvelle,confirmée,annulée,datée,ancienne,en_route,livrée',
                'priority' => 'required|in:normale,urgente,vip',
                'employee_id' => 'nullable|exists:employees,id',
                'scheduled_date' => 'nullable|date|after:today',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ];

            // Champs conditionnels selon le statut
            if ($request->status === 'confirmée') {
                $validationRules['customer_name'] = 'required|string|max:255';
                $validationRules['customer_governorate'] = 'required|exists:regions,id';
                $validationRules['customer_city'] = 'required|exists:cities,id';
                $validationRules['customer_address'] = 'required|string|max:500';
                $validationRules['total_price'] = 'required|numeric|min:0'; // PRIX OBLIGATOIRE
            } else {
                $validationRules['customer_name'] = 'nullable|string|max:255';
                $validationRules['customer_governorate'] = 'nullable|exists:regions,id';
                $validationRules['customer_city'] = 'nullable|exists:cities,id';
                $validationRules['customer_address'] = 'nullable|string|max:500';
                $validationRules['total_price'] = 'nullable|numeric|min:0';
            }

            $validated = $request->validate($validationRules, [
                'customer_phone.required' => 'Le numéro de téléphone est obligatoire',
                'customer_name.required' => 'Le nom du client est obligatoire pour une commande confirmée',
                'customer_governorate.required' => 'Le gouvernorat est obligatoire pour une commande confirmée',
                'customer_city.required' => 'La ville est obligatoire pour une commande confirmée',
                'customer_address.required' => 'L\'adresse est obligatoire pour une commande confirmée',
                'total_price.required' => 'Le prix total est obligatoire pour une commande confirmée',
                'products.required' => 'Veuillez ajouter au moins un produit',
                'products.min' => 'Veuillez ajouter au moins un produit',
                'scheduled_date.after' => 'La date programmée doit être dans le futur',
                'products.*.quantity.min' => 'La quantité doit être au moins de 1',
                'products.*.id.exists' => 'Un produit sélectionné n\'existe pas',
            ]);

            DB::beginTransaction();

            $oldStatus = $order->status;
            $wasConfirmed = $oldStatus === 'confirmée';
            $willBeConfirmed = $validated['status'] === 'confirmée';

            // RESTAURER LE STOCK si passage de confirmée vers autre statut
            if ($wasConfirmed && !$willBeConfirmed) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                        Log::info("Stock restauré pour produit {$item->product->id}: +{$item->quantity}");
                    }
                }
            }

            // Préparer les nouveaux produits
            $newProductsData = [];
            $totalPrice = 0;

            foreach ($validated['products'] as $productData) {
                $product = Product::where('id', $productData['id'])
                    ->where('admin_id', $order->admin_id)
                    ->where('is_active', true)
                    ->first();
                
                if (!$product) {
                    throw new \Exception("Le produit avec l'ID {$productData['id']} n'existe pas ou n'est pas actif.");
                }

                $quantity = (int) $productData['quantity'];
                
                // Si la commande devient confirmée, vérifier le stock
                if ($willBeConfirmed && $product->stock < $quantity) {
                    throw new \Exception("Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock}, quantité demandée: {$quantity}");
                }

                $unitPrice = (float) $product->price;
                $itemTotal = $quantity * $unitPrice;
                $totalPrice += $itemTotal;

                $newProductsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                ];
            }

            // Mettre à jour les informations de base
            $order->customer_phone = $validated['customer_phone'];
            $order->customer_name = $validated['customer_name'] ?? null;
            $order->customer_phone_2 = $validated['customer_phone_2'] ?? null;
            $order->customer_governorate = $validated['customer_governorate'] ?? null;
            $order->customer_city = $validated['customer_city'] ?? null;
            $order->customer_address = $validated['customer_address'] ?? null;
            $order->notes = $validated['notes'] ?? null;
            $order->status = $validated['status'];
            $order->priority = $validated['priority'];
            $order->total_price = $validated['total_price'] ?? $totalPrice;
            
            if (!empty($validated['scheduled_date'])) {
                $order->scheduled_date = $validated['scheduled_date'];
            } elseif ($validated['status'] !== 'datée') {
                $order->scheduled_date = null;
            }
            
            // Gestion de l'assignation
            if (!empty($validated['employee_id'])) {
                $employee = Employee::where('id', $validated['employee_id'])
                    ->where('admin_id', $order->admin_id)
                    ->where('is_active', true)
                    ->first();
                
                if ($employee) {
                    $order->employee_id = $employee->id;
                    $order->is_assigned = true;
                } else {
                    $order->employee_id = null;
                    $order->is_assigned = false;
                }
            } else {
                $order->employee_id = null;
                $order->is_assigned = false;
            }

            $order->save();

            // Supprimer les anciens items
            $order->items()->delete();
            
            // Ajouter les nouveaux produits
            foreach ($newProductsData as $productData) {
                $order->items()->create([
                    'product_id' => $productData['product']->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'total_price' => $productData['total_price'],
                ]);

                // Si la commande devient confirmée, décrémenter le stock
                if ($willBeConfirmed) {
                    $productData['product']->decrement('stock', $productData['quantity']);
                    Log::info("Stock décrémenté pour produit {$productData['product']->id}: -{$productData['quantity']}");
                }
            }

            // Vérifier le stock et mettre à jour le statut de suspension
            if ($validated['status'] !== 'confirmée') {
                $order->checkStockAndUpdateStatus();
            }

            // Enregistrer dans l'historique si le statut a changé
            if ($oldStatus !== $order->status) {
                $order->recordHistory(
                    'modification', 
                    'Statut modifié de "' . $oldStatus . '" vers "' . $order->status . '" par ' . Auth::guard('admin')->user()->name,
                    null,
                    $oldStatus,
                    $order->status
                );
            }

            DB::commit();

            return redirect()->route('admin.orders.index')->with('success', 'Commande mise à jour avec succès');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans OrderController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Supprimer une commande
     */
    public function destroy(Order $order)
    {
        try {
            $this->authorize('delete', $order);

            DB::beginTransaction();

            $orderId = $order->id;
            
            // Restaurer le stock si la commande était confirmée
            if ($order->status === 'confirmée') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                        Log::info("Stock restauré pour produit {$item->product->id}: +{$item->quantity}");
                    }
                }
            }
            
            // Supprimer les items et l'historique
            $order->items()->delete();
            $order->history()->delete();
            
            // Supprimer la commande
            $order->delete();

            DB::commit();

            return redirect()->route('admin.orders.index')->with('success', "Commande #{$orderId} supprimée avec succès");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans OrderController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Rechercher des produits pour l'interface - CORRIGÉE AVEC RÉFÉRENCE
     */
    public function searchProducts(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $search = $request->get('search', '');

            if (strlen($search) < 2) {
                return response()->json([]);
            }

            $products = $admin->products()
                ->where('is_active', true)
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%");
                })
                ->where('stock', '>', 0)
                ->orderBy('reference')
                ->limit(10)
                ->get(['id', 'name', 'reference', 'price', 'stock']);

            return response()->json($products);

        } catch (\Exception $e) {
            Log::error('Erreur dans searchProducts: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Obtenir les régions
     */
    public function getRegions(Request $request)
    {
        try {
            $regions = Region::orderBy('name')->get(['id', 'name']);
            return response()->json($regions);
        } catch (\Exception $e) {
            Log::error('Erreur dans getRegions: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Obtenir les villes d'une région
     */
    public function getCities(Request $request)
    {
        try {
            $regionId = $request->get('region_id');
            
            if (!$regionId) {
                return response()->json([]);
            }

            $cities = City::where('region_id', $regionId)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json($cities);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCities: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Vérifier les doublons avec algorithme 8 chiffres consécutifs - CORRIGÉE
     */
    public function checkPhoneForDuplicates(Request $request)
    {
        try {
            $phone = $request->get('phone');
            $admin = Auth::guard('admin')->user();
            
            if (!$phone || strlen($phone) < 8) {
                return response()->json(['has_duplicates' => false]);
            }

            // Extraire tous les chiffres du numéro entré
            $inputDigits = preg_replace('/\D/', '', $phone);
            if (strlen($inputDigits) < 8) {
                return response()->json(['has_duplicates' => false]);
            }

            // Récupérer toutes les commandes de l'admin
            $allOrders = $admin->orders()->get(['id', 'customer_phone', 'customer_phone_2', 'status', 'created_at', 'total_price', 'customer_name']);

            // Filtrer les commandes qui partagent 8 chiffres consécutifs
            $duplicates = [];
            foreach ($allOrders as $order) {
                $phone1Digits = preg_replace('/\D/', '', $order->customer_phone ?? '');
                $phone2Digits = preg_replace('/\D/', '', $order->customer_phone_2 ?? '');
                
                if ($this->hasEightConsecutiveDigits($inputDigits, $phone1Digits) || 
                    ($phone2Digits && $this->hasEightConsecutiveDigits($inputDigits, $phone2Digits))) {
                    $duplicates[] = [
                        'id' => $order->id,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('d/m/Y'),
                        'total_price' => $order->total_price,
                        'customer_name' => $order->customer_name,
                        'customer_phone' => $order->customer_phone
                    ];
                }
            }

            return response()->json([
                'has_duplicates' => count($duplicates) > 0,
                'total_orders' => count($duplicates),
                'orders' => $duplicates
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans checkPhoneForDuplicates: ' . $e->getMessage());
            return response()->json(['has_duplicates' => false], 500);
        }
    }

    /**
     * Récupérer l'historique complet d'un client - CORRIGÉE
     */
    public function getClientHistory(Request $request)
    {
        try {
            $phone = $request->get('phone');
            $admin = Auth::guard('admin')->user();
            
            if (!$phone) {
                return response()->json(['orders' => [], 'latest_order' => null]);
            }

            // Extraire tous les chiffres du numéro entré
            $inputDigits = preg_replace('/\D/', '', $phone);
            if (strlen($inputDigits) < 8) {
                return response()->json(['orders' => [], 'latest_order' => null]);
            }

            // Récupérer toutes les commandes de l'admin avec leurs items
            $allOrders = $admin->orders()
                ->with(['items.product'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Filtrer les commandes qui partagent 8 chiffres consécutifs
            $validOrders = [];
            foreach ($allOrders as $order) {
                $phone1Digits = preg_replace('/\D/', '', $order->customer_phone ?? '');
                $phone2Digits = preg_replace('/\D/', '', $order->customer_phone_2 ?? '');
                
                if ($this->hasEightConsecutiveDigits($inputDigits, $phone1Digits) || 
                    ($phone2Digits && $this->hasEightConsecutiveDigits($inputDigits, $phone2Digits))) {
                    $validOrders[] = $order;
                }
            }

            return response()->json([
                'orders' => $validOrders,
                'latest_order' => !empty($validOrders) ? $validOrders[0] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getClientHistory: ' . $e->getMessage());
            return response()->json(['orders' => [], 'latest_order' => null], 500);
        }
    }

    /**
     * Vérifier si deux numéros partagent 8 chiffres consécutifs - MÉTHODE UTILITAIRE
     */
    private function hasEightConsecutiveDigits($digits1, $digits2)
    {
        if (strlen($digits1) < 8 || strlen($digits2) < 8) {
            return false;
        }
        
        // Vérifier toutes les séquences possibles de 8 chiffres dans digits1
        for ($i = 0; $i <= strlen($digits1) - 8; $i++) {
            $sequence = substr($digits1, $i, 8);
            if (strpos($digits2, $sequence) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Enregistrer une tentative d'appel - CORRIGÉE
     */
    public function recordAttempt(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'notes' => 'required|string|min:3|max:1000'
            ], [
                'notes.required' => 'Une note est obligatoire pour enregistrer une tentative',
                'notes.min' => 'La note doit contenir au moins 3 caractères'
            ]);

            DB::beginTransaction();

            // Incrémenter les compteurs et mettre à jour la date
            $order->increment('attempts_count');
            $order->increment('daily_attempts_count');
            $order->last_attempt_at = now();
            $order->save();

            // Enregistrer dans l'historique
            $admin = Auth::guard('admin')->user();
            $order->recordHistory('tentative', $admin->name . ' a tenté d\'appeler : ' . $validated['notes']);

            // Vérifier la transition automatique vers file ancienne si nécessaire
            $order->transitionToOldIfNeeded();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tentative d\'appel enregistrée avec succès',
                'attempts_count' => $order->attempts_count,
                'daily_attempts_count' => $order->daily_attempts_count,
                'last_attempt_at' => $order->last_attempt_at->toISOString()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans recordAttempt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement'
            ], 500);
        }
    }

    /**
     * Afficher l'historique d'une commande
     */
    public function showHistory(Order $order)
    {
        $this->authorize('view', $order);
        
        $history = $order->history()
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.orders.history', compact('order', 'history'));
    }

    /**
     * Obtenir l'historique pour modal - SIMPLIFIÉ
     */
    public function getHistory(Order $order)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            if ($order->admin_id !== $admin->id) {
                return response()->json(['error' => 'Accès refusé'], 403);
            }
            
            $history = $order->history()
                ->orderBy('created_at', 'desc')
                ->get();
            
            return view('admin.orders.partials.history', compact('history'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans getHistory: ' . $e->getMessage());
            return view('admin.orders.partials.history-error');
        }
    }

    /**
     * Assignation en lot
     */
    public function bulkAssign(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'employee_id' => 'required|exists:employees,id'
            ]);

            $admin = Auth::guard('admin')->user();
            $employee = Employee::where('id', $validated['employee_id'])
                ->where('admin_id', $admin->id)
                ->where('is_active', true)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé ou inactif'
                ], 404);
            }

            DB::beginTransaction();

            $updatedCount = 0;
            foreach ($validated['order_ids'] as $orderId) {
                $order = $admin->orders()->find($orderId);
                if ($order && !$order->is_assigned) {
                    $order->employee_id = $employee->id;
                    $order->is_assigned = true;
                    $order->save();
                    
                    $order->recordHistory('assignation', "Commande assignée à {$employee->name} par {$admin->name}");
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} commande(s) assignée(s) à {$employee->name}"
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans bulkAssign: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation'
            ], 500);
        }
    }

    /**
     * Désassigner une commande
     */
    public function unassign(Order $order)
    {
        try {
            $this->authorize('update', $order);

            $employeeName = $order->employee ? $order->employee->name : 'Employé inconnu';
            
            $order->employee_id = null;
            $order->is_assigned = false;
            $order->save();

            $order->recordHistory('désassignation', "Commande désassignée de {$employeeName} par " . Auth::guard('admin')->user()->name);

            return response()->json([
                'success' => true,
                'message' => 'Commande désassignée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans unassign: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désassignation'
            ], 500);
        }
    }

    /**
     * Lister les commandes non assignées
     */
    public function unassigned(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $orders = $admin->orders()
                ->where('is_assigned', false)
                ->where('status', '!=', 'annulée')
                ->where('status', '!=', 'livrée')
                ->with(['items.product'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'asc')
                ->paginate(20);

            return view('admin.orders.unassigned', compact('orders'));

        } catch (\Exception $e) {
            Log::error('Erreur dans unassigned: ' . $e->getMessage());
            return redirect()->route('admin.orders.index')->with('error', 'Erreur lors du chargement');
        }
    }
}