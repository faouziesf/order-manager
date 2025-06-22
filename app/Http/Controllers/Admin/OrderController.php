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
            $regions = Region::all();
            return view('admin.orders.create', compact('regions'));
        } catch (\Exception $e) {
            Log::error('Erreur dans OrderController@create: ' . $e->getMessage());
            return redirect()->route('admin.orders.index')->with('error', 'Erreur lors du chargement du formulaire');
        }
    }

    /**
     * Créer une nouvelle commande
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_phone' => 'required|string|max:20',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone_2' => 'nullable|string|max:20',
                'customer_governorate' => 'nullable|exists:regions,id',
                'customer_city' => 'nullable|exists:cities,id',
                'customer_address' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'status' => 'required|in:nouvelle,confirmée',
                'priority' => 'required|in:normale,urgente,vip',
                'employee_id' => 'nullable|exists:employees,id',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ], [
                'customer_phone.required' => 'Le numéro de téléphone est obligatoire',
                'products.required' => 'Veuillez ajouter au moins un produit',
                'products.min' => 'Veuillez ajouter au moins un produit',
            ]);

            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();

            // Créer la commande
            $order = new Order();
            $order->admin_id = $admin->id;
            $order->customer_phone = $validated['customer_phone'];
            $order->customer_name = $validated['customer_name'];
            $order->customer_phone_2 = $validated['customer_phone_2'];
            $order->customer_governorate = $validated['customer_governorate'];
            $order->customer_city = $validated['customer_city'];
            $order->customer_address = $validated['customer_address'];
            $order->notes = $validated['notes'];
            $order->status = $validated['status'];
            $order->priority = $validated['priority'];
            $order->attempts_count = 0;
            $order->daily_attempts_count = 0;
            
            // Assigner à un employé si spécifié
            if ($validated['employee_id']) {
                $order->employee_id = $validated['employee_id'];
                $order->is_assigned = true;
            }

            $order->save();

            // Ajouter les produits
            $totalPrice = 0;
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                
                if ($product && $product->admin_id === $admin->id) {
                    $quantity = $productData['quantity'];
                    $unitPrice = $product->price;
                    $totalPrice += $quantity * $unitPrice;
                    
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $quantity * $unitPrice,
                    ]);
                }
            }

            // Mettre à jour le prix total
            $order->total_price = $totalPrice;
            $order->save();

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
            return redirect()->back()->with('error', 'Erreur lors de la création de la commande')->withInput();
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
            $regions = Region::all();
            $cities = [];
            
            if ($order->customer_governorate) {
                $cities = City::where('region_id', $order->customer_governorate)->get();
            }
            
            return view('admin.orders.edit', compact('order', 'regions', 'cities'));
        } catch (\Exception $e) {
            Log::error('Erreur dans OrderController@edit: ' . $e->getMessage());
            return redirect()->route('admin.orders.index')->with('error', 'Erreur lors du chargement de la commande');
        }
    }

    /**
     * Mettre à jour une commande
     */
    public function update(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'customer_phone' => 'required|string|max:20',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone_2' => 'nullable|string|max:20',
                'customer_governorate' => 'nullable|exists:regions,id',
                'customer_city' => 'nullable|exists:cities,id',
                'customer_address' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'status' => 'required|in:nouvelle,confirmée,annulée,datée,en_route,livrée',
                'priority' => 'required|in:normale,urgente,vip',
                'employee_id' => 'nullable|exists:employees,id',
                'scheduled_date' => 'nullable|date|after:today',
                'confirmed_price' => 'nullable|numeric|min:0',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $oldStatus = $order->status;

            // Mettre à jour les informations de base
            $order->fill($validated);
            
            // Gestion de l'assignation
            if ($validated['employee_id']) {
                $order->employee_id = $validated['employee_id'];
                $order->is_assigned = true;
            } else {
                $order->employee_id = null;
                $order->is_assigned = false;
            }

            $order->save();

            // Mettre à jour les produits
            if ($request->has('products')) {
                $order->items()->delete();
                
                $totalPrice = 0;
                foreach ($validated['products'] as $productData) {
                    $product = Product::find($productData['id']);
                    
                    if ($product && $product->admin_id === $order->admin_id) {
                        $quantity = $productData['quantity'];
                        $unitPrice = $product->price;
                        $totalPrice += $quantity * $unitPrice;
                        
                        $order->items()->create([
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total_price' => $quantity * $unitPrice,
                        ]);
                    }
                }
                
                $order->total_price = $totalPrice;
                $order->save();
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
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour')->withInput();
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
     * ========================================
     * MÉTHODES POUR L'INTERFACE DE TRAITEMENT
     * ========================================
     */

    /**
     * Rechercher des produits pour l'interface de traitement
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
                ->where('name', 'like', "%{$search}%")
                ->limit(10)
                ->get(['id', 'name', 'price', 'stock']);

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
                ->get(['id', 'name', 'shipping_cost']);

            return response()->json($cities);

        } catch (\Exception $e) {
            Log::error('Erreur dans getCities: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Enregistrer une tentative d'appel
     */
    public function recordAttempt(Request $request, Order $order)
    {
        try {
            $this->authorize('update', $order);

            $validated = $request->validate([
                'notes' => 'required|string|min:3|max:1000'
            ]);

            DB::beginTransaction();

            // Incrémenter les compteurs
            $order->increment('attempts_count');
            $order->increment('daily_attempts_count');
            $order->last_attempt_at = now();
            $order->save();

            // Enregistrer dans l'historique
            $order->recordHistory('tentative', $validated['notes']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tentative d\'appel enregistrée avec succès',
                'attempts_count' => $order->attempts_count,
                'daily_attempts_count' => $order->daily_attempts_count
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
     * Obtenir l'historique pour modal
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
            
            // Générer le HTML de l'historique
            $html = '';
            
            if ($history->count() > 0) {
                $html .= '<div class="timeline">';
                
                foreach ($history as $entry) {
                    $actionIcon = $this->getActionIcon($entry->action);
                    $actionClass = $this->getActionClass($entry->action);
                    
                    $html .= '<div class="timeline-item mb-3">';
                    $html .= '<div class="timeline-marker">';
                    $html .= '<div class="timeline-marker-icon bg-' . $actionClass . '">';
                    $html .= '<i class="' . $actionIcon . '"></i>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="timeline-content">';
                    $html .= '<div class="card">';
                    $html .= '<div class="card-header py-2">';
                    $html .= '<div class="d-flex justify-content-between align-items-center">';
                    $html .= '<h6 class="mb-0">';
                    $html .= '<span class="badge bg-' . $actionClass . '">' . ucfirst($entry->action) . '</span>';
                    
                    if ($entry->status_before && $entry->status_after && $entry->status_before !== $entry->status_after) {
                        $html .= '<small class="text-muted ms-2">';
                        $html .= ucfirst($entry->status_before) . ' → ' . ucfirst($entry->status_after);
                        $html .= '</small>';
                    }
                    
                    $html .= '</h6>';
                    $html .= '<small class="text-muted">';
                    $html .= $entry->created_at->format('d/m/Y H:i');
                    
                    if ($entry->created_at->diffInHours(now()) < 24) {
                        $html .= ' <span class="text-primary">(' . $entry->created_at->diffForHumans() . ')</span>';
                    }
                    
                    $html .= '</small>';
                    $html .= '</div>';
                    $html .= '</div>';
                    
                    if ($entry->notes) {
                        $html .= '<div class="card-body py-2">';
                        $html .= '<p class="mb-0">' . e($entry->notes) . '</p>';
                        
                        if ($entry->user_type && $entry->user_id) {
                            $html .= '<small class="text-muted d-block mt-1">';
                            $html .= '<i class="fas fa-user me-1"></i>';
                            $html .= 'Par: ' . $entry->user_type;
                            
                            if ($entry->user_type === 'Admin' && $entry->user_id === $admin->id) {
                                $html .= ' <span class="text-primary">(Vous)</span>';
                            }
                            
                            $html .= '</small>';
                        }
                        
                        if ($entry->changes) {
                            $changes = json_decode($entry->changes, true);
                            if ($changes && is_array($changes)) {
                                $html .= '<div class="mt-2">';
                                $html .= '<small class="text-muted">Détails:</small>';
                                $html .= '<ul class="list-unstyled mb-0 ms-3">';
                                
                                foreach ($changes as $key => $value) {
                                    $displayValue = is_array($value) ? json_encode($value) : $value;
                                    $html .= '<li><small class="text-muted">' . ucfirst(str_replace('_', ' ', $key)) . ': ' . $displayValue . '</small></li>';
                                }
                                
                                $html .= '</ul>';
                                $html .= '</div>';
                            }
                        }
                        
                        $html .= '</div>';
                    }
                    
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                
                $html .= '</div>';
            } else {
                $html .= '<div class="text-center py-4">';
                $html .= '<i class="fas fa-history fa-3x text-muted mb-3"></i>';
                $html .= '<h5 class="text-muted">Aucun historique</h5>';
                $html .= '<p class="text-muted">Cette commande n\'a pas encore d\'historique d\'actions.</p>';
                $html .= '</div>';
            }
            
            return response($html);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans getHistory: ' . $e->getMessage());
            return response('<div class="alert alert-danger">Erreur lors du chargement de l\'historique</div>', 500);
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

    /**
     * ========================================
     * MÉTHODES UTILITAIRES
     * ========================================
     */

    private function getActionIcon($action)
    {
        $icons = [
            'création' => 'fas fa-plus-circle',
            'modification' => 'fas fa-edit',
            'tentative' => 'fas fa-phone',
            'confirmation' => 'fas fa-check-circle',
            'annulation' => 'fas fa-times-circle',
            'datation' => 'fas fa-calendar-alt',
            'assignation' => 'fas fa-user-plus',
            'désassignation' => 'fas fa-user-minus',
            'suspension' => 'fas fa-pause-circle',
            'réactivation' => 'fas fa-play-circle',
        ];

        return $icons[$action] ?? 'fas fa-circle';
    }

    private function getActionClass($action)
    {
        $classes = [
            'création' => 'success',
            'modification' => 'primary',
            'tentative' => 'warning',
            'confirmation' => 'success',
            'annulation' => 'danger',
            'datation' => 'info',
            'assignation' => 'success',
            'désassignation' => 'danger',
            'suspension' => 'warning',
            'réactivation' => 'success',
        ];

        return $classes[$action] ?? 'secondary';
    }
}