<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\PickupAddress;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class DeliveryController extends Controller
{
    /**
     * Page principale de configuration des transporteurs
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations existantes avec leur statut
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->latest()
            ->get()
            ->map(function($config) {
                $config->status_info = $this->getConfigurationStatus($config);
                return $config;
            });

        // Récupérer les adresses d'enlèvement
        $pickupAddresses = PickupAddress::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        // Transporteurs supportés
        $supportedCarriers = $this->getSupportedCarriers();
        $availableCarriers = $this->getAvailableCarriers();

        // Statistiques rapides
        $stats = [
            'total_configs' => $configurations->count(),
            'active_configs' => $configurations->where('is_active', true)->count(),
            'tested_configs' => $configurations->filter(function($config) {
                return $config->status_info['status'] === 'valid';
            })->count(),
            'expired_tokens' => $configurations->filter(function($config) {
                return in_array($config->status_info['status'], ['expired', 'expiring_soon']);
            })->count(),
            'total_addresses' => $pickupAddresses->count(),
        ];

        return view('admin.delivery.configuration', compact(
            'configurations', 
            'pickupAddresses', 
            'supportedCarriers',
            'availableCarriers',
            'stats'
        ));
    }

    /**
     * Page de préparation d'enlèvement - Point d'entrée principal du workflow
     */
    public function preparation()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations actives avec leurs noms d'intégration
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function($config) {
                $config->display_name = $config->integration_name ?: "{$config->carrier_slug} - {$config->username}";
                $config->supports_pickup_address = $this->carrierSupportsPickupAddress($config->carrier_slug);
                return $config;
            });

        // Récupérer les adresses d'enlèvement actives
        $pickupAddresses = PickupAddress::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        // Statistiques pour la page
        $stats = [
            'available_orders' => Order::where('admin_id', $admin->id)
                ->where('status', 'confirmée')
                ->whereDoesntHave('shipments')
                ->count(),
            'draft_pickups' => Pickup::where('admin_id', $admin->id)
                ->where('status', 'draft')
                ->count(),
        ];

        return view('admin.delivery.preparation', compact(
            'configurations',
            'pickupAddresses', 
            'stats'
        ));
    }

    /**
     * Récupérer les commandes disponibles pour l'enlèvement (AJAX)
     */
    public function getAvailableOrders(Request $request)
    {
        try {
            $admin = auth('admin')->user();
            
            // Récupérer les commandes confirmées qui ne sont pas encore dans un enlèvement
            $query = Order::where('admin_id', $admin->id)
                ->where('status', 'confirmée')
                ->whereDoesntHave('shipments');
            
            // Appliquer les filtres si fournis
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            if ($request->has('min_amount') && $request->min_amount) {
                $query->where('total_price', '>=', $request->min_amount);
            }
            
            if ($request->has('max_amount') && $request->max_amount) {
                $query->where('total_price', '<=', $request->max_amount);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('customer_address', 'like', "%{$search}%");
                });
            }
            
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate(20);
            
            Log::info('Chargement commandes preparation', [
                'admin_id' => $admin->id,
                'total_orders' => $orders->total(),
                'filters' => $request->all()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                try {
                    $html = view('admin.delivery.partials.orders-table', compact('orders'))->render();
                    
                    return response()->json([
                        'success' => true,
                        'html' => $html,
                        'total' => $orders->total()
                    ]);
                    
                } catch (Exception $viewError) {
                    Log::warning('Erreur rendu vue partielle', [
                        'error' => $viewError->getMessage(),
                        'admin_id' => $admin->id
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'orders' => $orders,
                        'fallback' => true
                    ]);
                }
            }
            
            return view('admin.delivery.preparation', compact('orders'));
            
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des commandes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->all()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du chargement des commandes: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des commandes');
        }
    }

    /**
     * Créer un nouvel enlèvement (pickup) en statut draft
     */
    public function createPickup(Request $request)
    {
        try {
            $admin = auth('admin')->user();

            $validator = Validator::make($request->all(), [
                'delivery_configuration_id' => 'required|exists:delivery_configurations,id',
                'pickup_address_id' => 'nullable|exists:pickup_addresses,id',
                'pickup_date' => 'nullable|date|after_or_equal:today',
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'exists:orders,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que la configuration appartient à l'admin
            $config = DeliveryConfiguration::where('id', $request->delivery_configuration_id)
                ->where('admin_id', $admin->id)
                ->firstOrFail();

            DB::beginTransaction();

            // Créer l'enlèvement en statut draft
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $config->carrier_slug,
                'delivery_configuration_id' => $request->delivery_configuration_id,
                'pickup_address_id' => $request->pickup_address_id,
                'pickup_date' => $request->pickup_date,
                'status' => 'draft'
            ]);

            // Créer les expéditions (shipments) pour chaque commande
            $shipmentCount = 0;
            foreach ($request->order_ids as $orderId) {
                $order = Order::where('id', $orderId)
                    ->where('admin_id', $admin->id)
                    ->where('status', 'confirmée')
                    ->whereDoesntHave('shipments')
                    ->first();
                
                if ($order) {
                    // Préparer les données de base
                    $shipmentData = [
                        'admin_id' => $admin->id,
                        'pickup_id' => $pickup->id,
                        'order_id' => $order->id,
                        'status' => 'created',
                        'recipient_info' => json_encode([
                            'customer_name' => $order->customer_name,
                            'customer_phone' => $order->customer_phone,
                            'customer_address' => $order->customer_address,
                            'order_number' => $order->id,
                            'total_price' => $order->total_price
                        ])
                    ];
                    
                    // Ajouter les colonnes optionnelles si elles existent
                    $columns = \Schema::getColumnListing('shipments');
                    
                    if (in_array('carrier_slug', $columns)) {
                        $shipmentData['carrier_slug'] = $config->carrier_slug;
                    }
                    
                    if (in_array('pos_barcode', $columns)) {
                        $shipmentData['pos_barcode'] = 'PENDING_' . uniqid();
                    }
                    
                    if (in_array('return_barcode', $columns)) {
                        $shipmentData['return_barcode'] = 'RET_PENDING_' . uniqid();
                    }
                    
                    // Insérer directement dans la base
                    DB::table('shipments')->insert(array_merge($shipmentData, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]));
                    
                    $shipmentCount++;
                }
            }

            DB::commit();

            Log::info('Enlèvement créé avec succès', [
                'pickup_id' => $pickup->id,
                'admin_id' => $admin->id,
                'shipments_count' => $shipmentCount,
                'config_name' => $config->integration_name
            ]);

            return response()->json([
                'success' => true,
                'message' => "Enlèvement créé avec succès ({$shipmentCount} expéditions)",
                'pickup_id' => $pickup->id,
                'redirect_url' => route('admin.delivery.pickups.show', $pickup)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création d\'enlèvement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Page de gestion des enlèvements
     */
    public function pickups()
    {
        $admin = auth('admin')->user();
        
        $pickups = Pickup::where('admin_id', $admin->id)
            ->with(['deliveryConfiguration', 'pickupAddress', 'shipments.order'])
            ->withCount('shipments')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Statistiques pour la vue
        $stats = [
            'total' => Pickup::where('admin_id', $admin->id)->count(),
            'draft' => Pickup::where('admin_id', $admin->id)->where('status', 'draft')->count(),
            'validated' => Pickup::where('admin_id', $admin->id)->where('status', 'validated')->count(),
            'picked_up' => Pickup::where('admin_id', $admin->id)->where('status', 'picked_up')->count(),
            'problem' => Pickup::where('admin_id', $admin->id)->where('status', 'problem')->count(),
        ];
        
        return view('admin.delivery.pickups.index', compact('pickups', 'stats'));
    }

    /**
     * Afficher un enlèvement spécifique avec ses expéditions
     */
    public function showPickup(Pickup $pickup)
    {
        // Vérifier que l'enlèvement appartient à l'admin connecté
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403);
        }
        
        $pickup->load([
            'deliveryConfiguration', 
            'pickupAddress', 
            'shipments.order',
            'shipments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);
        
        return view('admin.delivery.pickups.show', compact('pickup'));
    }

    /**
     * Valider un enlèvement - Appelle l'API du transporteur pour créer les expéditions
     */
    public function validatePickup(Pickup $pickup)
    {
        try {
            if ($pickup->admin_id !== auth('admin')->id()) {
                abort(403);
            }
            
            if ($pickup->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les enlèvements en brouillon peuvent être validés'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // TODO: Ici vous intégrerez votre service Fparcel pour appeler pos_create
            // Pour chaque shipment du pickup
            $validatedShipments = 0;
            
            foreach ($pickup->shipments as $shipment) {
                // Appel à votre FparcelService::createShipment()
                // $result = app(FparcelService::class)->createShipment($shipment->order, $pickup->pickupAddress);
                
                // Pour l'instant, on simule la validation
                $shipment->update([
                    'status' => 'validated',
                    'pos_barcode' => 'SIM_' . uniqid(), // Sera remplacé par le vrai POSBARCODE de Fparcel
                    'return_barcode' => 'RET_' . uniqid()
                ]);
                
                $validatedShipments++;
            }
            
            // Mettre à jour le pickup
            $pickup->update([
                'status' => 'validated',
                'validated_at' => now()
            ]);
            
            DB::commit();
            
            Log::info('Enlèvement validé avec succès', [
                'pickup_id' => $pickup->id,
                'validated_shipments' => $validatedShipments
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Enlèvement validé avec succès ({$validatedShipments} expéditions)"
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la validation d\'enlèvement', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickup->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter des commandes à un enlèvement existant
     */
    public function addOrdersToPickup(Request $request, Pickup $pickup)
    {
        try {
            if ($pickup->admin_id !== auth('admin')->id()) {
                abort(403);
            }
            
            if ($pickup->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'ajouter des commandes à un enlèvement validé'
                ], 400);
            }
            
            $validator = Validator::make($request->all(), [
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'exists:orders,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $addedCount = 0;
            
            foreach ($request->order_ids as $orderId) {
                $order = Order::where('id', $orderId)
                    ->where('admin_id', auth('admin')->id())
                    ->where('status', 'confirmée')
                    ->whereDoesntHave('shipments')
                    ->first();
                
                if ($order) {
                    Shipment::create([
                        'admin_id' => auth('admin')->id(),
                        'pickup_id' => $pickup->id,
                        'order_id' => $order->id,
                        'carrier_slug' => $pickup->carrier_slug,
                        'status' => 'created',
                        'recipient_info' => [
                            'customer_name' => $order->customer_name,
                            'customer_phone' => $order->customer_phone,
                            'customer_address' => $order->customer_address,
                            'order_number' => $order->id,
                            'total_price' => $order->total_price
                        ]
                    ]);
                    $addedCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$addedCount} commandes ajoutées à l'enlèvement"
            ]);
            
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de commandes', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickup->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirer des commandes d'un enlèvement
     */
    public function removeOrdersFromPickup(Request $request, Pickup $pickup)
    {
        try {
            if ($pickup->admin_id !== auth('admin')->id()) {
                abort(403);
            }
            
            if ($pickup->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de retirer des commandes d\'un enlèvement validé'
                ], 400);
            }
            
            $validator = Validator::make($request->all(), [
                'shipment_ids' => 'required|array|min:1',
                'shipment_ids.*' => 'exists:shipments,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $removedCount = Shipment::whereIn('id', $request->shipment_ids)
                ->where('pickup_id', $pickup->id)
                ->where('admin_id', auth('admin')->id())
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => "{$removedCount} expéditions retirées de l'enlèvement"
            ]);
            
        } catch (Exception $e) {
            Log::error('Erreur lors du retrait de commandes', [
                'error' => $e->getMessage(),
                'pickup_id' => $pickup->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Page de gestion des expéditions
     */
    public function shipments()
    {
        $admin = auth('admin')->user();
        
        $shipments = Shipment::where('admin_id', $admin->id)
            ->with(['pickup.deliveryConfiguration', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.delivery.shipments.index', compact('shipments'));
    }

    /**
     * Afficher une expédition spécifique
     */
    public function showShipment(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403);
        }
        
        $shipment->load(['pickup.deliveryConfiguration', 'order', 'statusHistory']);
        
        return view('admin.delivery.shipments.show', compact('shipment'));
    }

    /**
     * Page des statistiques
     */
    public function stats()
    {
        $admin = auth('admin')->user();
        
        $stats = [
            'total_pickups' => Pickup::where('admin_id', $admin->id)->count(),
            'draft_pickups' => Pickup::where('admin_id', $admin->id)->where('status', 'draft')->count(),
            'validated_pickups' => Pickup::where('admin_id', $admin->id)->where('status', 'validated')->count(),
            'total_shipments' => Shipment::where('admin_id', $admin->id)->count(),
            'active_shipments' => Shipment::where('admin_id', $admin->id)
                ->whereIn('status', ['validated', 'picked_up_by_carrier', 'in_transit'])->count(),
            'delivered_this_month' => Shipment::where('admin_id', $admin->id)
                ->where('status', 'delivered')
                ->whereMonth('created_at', now()->month)->count(),
        ];
        
        return view('admin.delivery.stats', compact('stats'));
    }

    // =========================================
    // MÉTHODES DE CONFIGURATION (inchangées)
    // =========================================

    /**
     * Créer une nouvelle configuration de transporteur
     */
    public function storeConfiguration(Request $request)
    {
        try {
            $admin = auth('admin')->user();

            $validator = Validator::make($request->all(), [
                'carrier_slug' => 'required|string|in:fparcel',
                'integration_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('delivery_configurations')
                        ->where('admin_id', $admin->id)
                        ->where('carrier_slug', $request->carrier_slug),
                ],
                'username' => 'required|string|min:3|max:255',
                'password' => 'required|string|min:6',
                'environment' => 'required|in:test,prod',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Erreur de validation des données.');
            }

            $configuration = DeliveryConfiguration::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $request->carrier_slug,
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'password' => $request->password,
                'environment' => $request->environment,
                'token' => null,
                'expires_at' => null,
                'is_active' => true,
            ]);

            Log::info('Configuration créée avec succès', [
                'admin_id' => $admin->id,
                'config_id' => $configuration->id,
                'carrier' => $request->carrier_slug,
                'integration_name' => $request->integration_name
            ]);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration du transporteur créée avec succès. Testez maintenant la connexion.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la création de configuration', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['password'])
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour une configuration existante
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        try {
            // Vérifier que la configuration appartient à l'admin connecté
            if ($config->admin_id !== auth('admin')->id()) {
                abort(403);
            }

            $admin = auth('admin')->user();

            $validator = Validator::make($request->all(), [
                'integration_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('delivery_configurations')
                        ->where('admin_id', $admin->id)
                        ->where('carrier_slug', $config->carrier_slug)
                        ->ignore($config->id),
                ],
                'username' => 'required|string|min:3|max:255',
                'password' => 'nullable|string|min:6', // Nullable pour ne pas obliger à changer
                'environment' => 'required|in:test,prod',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Erreur de validation des données.');
            }

            $updateData = [
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'environment' => $request->environment,
            ];

            // Si un nouveau mot de passe est fourni
            if ($request->filled('password')) {
                $updateData['password'] = $request->password;
                // Réinitialiser le token si on change le mot de passe
                $updateData['token'] = null;
                $updateData['expires_at'] = null;
            }

            // Si on change l'environnement, réinitialiser le token
            if ($config->environment !== $request->environment) {
                $updateData['token'] = null;
                $updateData['expires_at'] = null;
            }

            $config->update($updateData);

            Log::info('Configuration mise à jour avec succès', [
                'admin_id' => $admin->id,
                'config_id' => $config->id,
                'changes' => array_keys($updateData)
            ]);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration mise à jour avec succès. Re-testez la connexion si nécessaire.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la mise à jour de configuration', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['password'])
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Créer une adresse d'enlèvement
     */
    public function storePickupAddress(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $admin = auth('admin')->user();

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('pickup_addresses')
                        ->where('admin_id', $admin->id),
                ],
                'contact_name' => 'required|string|max:255',
                'address' => 'required|string|max:1000',
                'postal_code' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_default' => 'nullable',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Erreur de validation des données.');
            }

            $isDefault = $request->has('is_default') && $request->is_default;
            
            if ($isDefault) {
                PickupAddress::where('admin_id', $admin->id)
                    ->update(['is_default' => false]);
            }

            $address = PickupAddress::create([
                'admin_id' => $admin->id,
                'name' => trim($request->name),
                'contact_name' => trim($request->contact_name),
                'address' => trim($request->address),
                'postal_code' => $request->postal_code ? trim($request->postal_code) : null,
                'city' => $request->city ? trim($request->city) : null,
                'phone' => trim($request->phone),
                'email' => $request->email ? trim($request->email) : null,
                'is_default' => $isDefault,
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Adresse d\'enlèvement créée avec succès.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création d\'adresse', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    // =========================================
    // MÉTHODES UTILITAIRES
    // =========================================

    /**
     * Vérifier si un transporteur supporte la sélection d'adresse d'enlèvement
     */
    private function carrierSupportsPickupAddress(string $carrierSlug): bool
    {
        $supportedCarriers = ['fparcel']; // Liste des transporteurs supportant les adresses
        return in_array($carrierSlug, $supportedCarriers);
    }

    /**
     * Obtenir les transporteurs supportés
     */
    private function getSupportedCarriers(): array
    {
        return [
            'fparcel' => [
                'name' => 'Fparcel',
                'display_name' => 'Fparcel Tunisia',
                'supports_pickup_address' => true,
                'supports_tracking' => true,
                'supports_mass_labels' => true,
                'features' => ['cod', 'tracking', 'mass_labels', 'pickup_scheduling']
            ]
        ];
    }

    /**
     * Obtenir les transporteurs disponibles
     */
    private function getAvailableCarriers(): array
    {
        return [
            'fparcel' => [
                'name' => 'Fparcel',
                'display_name' => 'Fparcel Tunisia',
                'description' => 'Service de livraison tunisien',
                'website' => 'https://fparcel.com',
                'environments' => ['test', 'prod']
            ]
        ];
    }

    /**
     * Vérifier si le token est valide (avec expiration 48h)
     */
    private function hasValidToken(DeliveryConfiguration $config): bool
    {
        return $config->token && 
               $config->expires_at && 
               $config->expires_at->isFuture();
    }

    /**
     * Obtenir le statut d'une configuration pour l'affichage
     */
    public function getConfigurationStatus(DeliveryConfiguration $config): array
    {
        if (!$config->token) {
            return [
                'status' => 'not_tested',
                'badge_class' => 'bg-secondary',
                'badge_text' => 'Non testé',
                'message' => 'Configuration non testée'
            ];
        }

        if (!$config->expires_at) {
            return [
                'status' => 'invalid',
                'badge_class' => 'bg-danger',
                'badge_text' => 'Invalide',
                'message' => 'Token sans date d\'expiration'
            ];
        }

        if ($config->expires_at->isPast()) {
            return [
                'status' => 'expired',
                'badge_class' => 'bg-danger',
                'badge_text' => 'Expiré',
                'message' => 'Token expiré le ' . $config->expires_at->format('d/m/Y H:i')
            ];
        }

        if ($config->expires_at->isBefore(now()->addHours(6))) {
            return [
                'status' => 'expiring_soon',
                'badge_class' => 'bg-warning',
                'badge_text' => 'Expire bientôt',
                'message' => 'Token expire le ' . $config->expires_at->format('d/m/Y H:i')
            ];
        }

        return [
            'status' => 'valid',
            'badge_class' => 'bg-success',
            'badge_text' => 'Connecté',
            'message' => 'Token valide jusqu\'au ' . $config->expires_at->format('d/m/Y H:i')
        ];
    }

    // =========================================
    // MÉTHODES API FPARCEL (restaurées et corrigées)
    // =========================================

    /**
     * Tester la connexion d'une configuration
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        try {
            // Vérifier que la configuration appartient à l'admin connecté
            if ($config->admin_id !== auth('admin')->id()) {
                abort(403);
            }

            $result = $this->testFparcelConnection([
                'username' => $config->username,
                'password' => $this->getDecryptedPassword($config),
                'environment' => $config->environment,
            ]);

            if ($result['success']) {
                // Mettre à jour le token avec expiration de 48h
                $config->update([
                    'token' => $result['data']['token'],
                    'expires_at' => $result['data']['expires_at'],
                ]);

                Log::info('Test de connexion réussi', [
                    'config_id' => $config->id,
                    'admin_id' => auth('admin')->id(),
                    'expires_at' => $result['data']['expires_at']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token_expires_at' => $result['data']['expires_at']->format('d/m/Y H:i'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Erreur lors du test de connexion', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rafraîchir le token d'une configuration
     */
    public function refreshToken(DeliveryConfiguration $config)
    {
        try {
            // Vérifier que la configuration appartient à l'admin connecté
            if ($config->admin_id !== auth('admin')->id()) {
                abort(403);
            }

            $result = $this->testFparcelConnection([
                'username' => $config->username,
                'password' => $this->getDecryptedPassword($config),
                'environment' => $config->environment,
            ]);

            if ($result['success']) {
                $config->update([
                    'token' => $result['data']['token'],
                    'expires_at' => $result['data']['expires_at'],
                ]);

                Log::info('Token rafraîchi avec succès', [
                    'config_id' => $config->id,
                    'admin_id' => auth('admin')->id(),
                    'expires_at' => $result['data']['expires_at']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Token rafraîchi avec succès.',
                    'expires_at' => $result['data']['expires_at']->format('d/m/Y H:i'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de rafraîchir le token : ' . $result['message'],
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Erreur lors du rafraîchissement de token', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tester la connexion avec l'API Fparcel - Expiration 48h
     */
    private function testFparcelConnection(array $credentials): array
    {
        try {
            $baseUrl = $credentials['environment'] === 'prod' 
                ? 'https://admin.fparcel.net/WebServiceExterne' 
                : 'http://fparcel.net:59/WebServiceExterne';

            Log::info('Test connexion Fparcel', [
                'url' => $baseUrl . '/get_token',
                'username' => $credentials['username'],
                'environment' => $credentials['environment']
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post($baseUrl . '/get_token', [
                    'USERNAME' => $credentials['username'],
                    'PASSWORD' => $credentials['password'],
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erreur HTTP ' . $response->status()
                ];
            }

            $responseBody = trim($response->body());
            
            // Log pour debug
            Log::info('Réponse Fparcel brute', ['body' => $responseBody]);
            
            // Extraire le token de toutes les façons possibles
            $token = null;
            
            // Cas 1: JSON avec structure {"TOKEN": "..."}
            $data = $response->json();
            if (is_array($data) && isset($data['TOKEN'])) {
                $token = $data['TOKEN'];
            }
            // Cas 2: JSON string directe "token_value"
            elseif (is_string($data) && strlen($data) > 10) {
                $token = $data;
            }
            // Cas 3: String JSON quoted dans le body brut
            elseif (preg_match('/^"([^"]+)"$/', $responseBody, $matches)) {
                $token = $matches[1];
            }
            // Cas 4: Texte brut qui ressemble à un token
            elseif (strlen($responseBody) > 10 && !str_contains(strtolower($responseBody), 'error')) {
                $token = $responseBody;
            }
            
            // Si on a trouvé un token
            if ($token) {
                // Nettoyer le token (enlever les échappements)
                $token = str_replace(['\\/', '\\"'], ['/', '"'], $token);
                
                // EXPIRATION 48H au lieu de 1H
                $expiresAt = now()->addHours(48);
                
                Log::info('Token Fparcel extrait avec succès', [
                    'token' => substr($token, 0, 20) . '...', // Masquer le token complet
                    'expires_at' => $expiresAt
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Connexion réussie avec Fparcel. Token valide 48h.',
                    'data' => [
                        'token' => $token,
                        'expires_at' => $expiresAt,
                    ]
                ];
            }
            
            // Échec d'extraction
            return [
                'success' => false,
                'message' => 'Impossible d\'extraire le token de la réponse Fparcel: ' . $responseBody
            ];

        } catch (Exception $e) {
            Log::error('Erreur test connexion Fparcel', [
                'error' => $e->getMessage(),
                'username' => $credentials['username'],
                'environment' => $credentials['environment']
            ]);

            return [
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir le mot de passe déchiffré
     */
    private function getDecryptedPassword(DeliveryConfiguration $config): string
    {
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($config->password);
        } catch (Exception $e) {
            // Si le déchiffrement échoue, retourner le mot de passe tel quel
            Log::warning('Impossible de déchiffrer le mot de passe', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            return $config->password;
        }
    }

    /**
     * Importer les adresses depuis Fparcel
     */
    public function importFparcelAddresses(DeliveryConfiguration $config)
    {
        try {
            // Vérifier que la configuration appartient à l'admin connecté
            if ($config->admin_id !== auth('admin')->id()) {
                abort(403);
            }

            Log::info('Début import adresses Fparcel', ['config_id' => $config->id]);

            // Vérifier que la configuration a un token valide
            if (!$this->hasValidToken($config)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalide. Veuillez d\'abord tester la connexion.'
                ], 400);
            }

            // Récupérer les drop points depuis Fparcel
            $dropPoints = $this->getFparcelDropPoints($config);
            
            if (!$dropPoints['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération des adresses : ' . $dropPoints['message']
                ]);
            }

            $imported = 0;
            $skipped = 0;
            $admin = auth('admin')->user();

            DB::beginTransaction();

            foreach ($dropPoints['data'] as $dropPoint) {
                // Vérifier si l'adresse existe déjà
                $exists = PickupAddress::where('admin_id', $admin->id)
                    ->where('name', $dropPoint['name'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Créer l'adresse
                PickupAddress::create([
                    'admin_id' => $admin->id,
                    'name' => $dropPoint['name'],
                    'contact_name' => $dropPoint['contact_name'] ?? 'Contact Fparcel',
                    'address' => $dropPoint['address'],
                    'postal_code' => $dropPoint['postal_code'] ?? null,
                    'city' => $dropPoint['city'] ?? null,
                    'phone' => $dropPoint['phone'] ?? '00000000',
                    'email' => $dropPoint['email'] ?? null,
                    'is_default' => false,
                    'is_active' => true,
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('Import adresses Fparcel terminé', [
                'config_id' => $config->id,
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import terminé : {$imported} adresses importées, {$skipped} ignorées (déjà existantes).",
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de l\'import adresses Fparcel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config_id' => $config->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les drop points depuis Fparcel
     */
    private function getFparcelDropPoints(DeliveryConfiguration $config): array
    {
        try {
            $baseUrl = $config->environment === 'prod' 
                ? 'https://admin.fparcel.net/WebServiceExterne' 
                : 'http://fparcel.net:59/WebServiceExterne';

            Log::info('Récupération drop points Fparcel', [
                'url' => $baseUrl . '/droppoint_list'
            ]);

            $response = Http::timeout(30)
                ->get($baseUrl . '/droppoint_list');

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erreur HTTP ' . $response->status()
                ];
            }

            $data = $response->json();
            
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'message' => 'Format de réponse invalide'
                ];
            }

            // Transformer les données Fparcel en format standard
            $addresses = [];
            foreach ($data as $item) {
                $addresses[] = [
                    'name' => $item['NOM'] ?? ($item['DESIGNATION'] ?? 'Agence Fparcel'),
                    'contact_name' => $item['CONTACT'] ?? ($item['NOM'] ?? 'Contact'),
                    'address' => $item['ADRESSE'] ?? ($item['ADDRESS'] ?? ''),
                    'postal_code' => $item['CODE_POSTAL'] ?? ($item['POSTAL_CODE'] ?? null),
                    'city' => $item['VILLE'] ?? ($item['CITY'] ?? null),
                    'phone' => $item['TELEPHONE'] ?? ($item['PHONE'] ?? null),
                    'email' => $item['EMAIL'] ?? ($item['MAIL'] ?? null),
                ];
            }

            Log::info('Drop points récupérés avec succès', [
                'count' => count($addresses)
            ]);

            return [
                'success' => true,
                'data' => $addresses
            ];

        } catch (Exception $e) {
            Log::error('Erreur récupération drop points', [
                'error' => $e->getMessage(),
                'config_id' => $config->id
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function generateLabels(Pickup $pickup)
    {
        // TODO: Utiliser votre FparcelService pour get_bl_en_masse
        return response()->json(['success' => true, 'message' => 'Étiquettes générées']);
    }

    public function generateManifest(Pickup $pickup)
    {
        // TODO: Générer le manifeste PDF
        return response()->json(['success' => true, 'message' => 'Manifeste généré']);
    }

    public function refreshStatus(Pickup $pickup)
    {
        // TODO: Utiliser votre FparcelService pour tracking_position
        return response()->json(['success' => true, 'message' => 'Statuts mis à jour']);
    }

    public function trackShipment(Shipment $shipment)
    {
        // TODO: Utiliser votre FparcelService
        return response()->json(['success' => true, 'message' => 'Suivi mis à jour']);
    }

    public function markDelivered(Shipment $shipment)
    {
        try {
            $shipment->update(['status' => 'delivered']);
            return response()->json(['success' => true, 'message' => 'Marqué comme livré']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Méthodes de suppression et autres actions CRUD standard...
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        try {
            $config->delete();
            return response()->json(['success' => true, 'message' => 'Configuration supprimée']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePickupAddress(PickupAddress $address)
    {
        try {
            $address->delete();
            return response()->json(['success' => true, 'message' => 'Adresse supprimée']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function setDefaultAddress(PickupAddress $address)
    {
        try {
            DB::beginTransaction();
            
            PickupAddress::where('admin_id', auth('admin')->id())
                ->update(['is_default' => false]);
            
            $address->update(['is_default' => true]);
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Adresse par défaut mise à jour']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyPickup(Pickup $pickup)
    {
        try {
            if ($pickup->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les enlèvements en brouillon peuvent être supprimés'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Supprimer les expéditions associées
            $pickup->shipments()->delete();
            
            // Supprimer l'enlèvement
            $pickup->delete();
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Enlèvement supprimé']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleConfiguration(DeliveryConfiguration $config)
    {
        try {
            $config->update(['is_active' => !$config->is_active]);
            $status = $config->is_active ? 'activée' : 'désactivée';
            return response()->json(['success' => true, 'message' => "Configuration {$status}"]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCarriers()
    {
        return response()->json(['success' => true, 'carriers' => $this->getSupportedCarriers()]);
    }

    public function getApiStats()
    {
        $admin = auth('admin')->user();
        
        $stats = [
            'configurations' => DeliveryConfiguration::where('admin_id', $admin->id)->count(),
            'pickups' => Pickup::where('admin_id', $admin->id)->count(),
            'shipments' => Shipment::where('admin_id', $admin->id)->count(),
        ];
        
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function trackAllShipments()
    {
        // TODO: Implémenter le suivi automatique
        return response()->json(['success' => true, 'message' => 'Suivi lancé']);
    }
}