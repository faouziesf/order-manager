<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class DeliveryController extends Controller
{
    /**
     * Page principale de configuration Jax Delivery
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations existantes
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->latest()
            ->get()
            ->map(function($config) {
                $config->status_info = $this->getConfigurationStatus($config);
                return $config;
            });

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
        ];

        return view('admin.delivery.configuration', compact(
            'configurations', 
            'stats'
        ));
    }

    /**
     * Page de préparation d'enlèvement
     */
    public function preparation()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations actives Jax Delivery
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function($config) {
                $config->display_name = $config->integration_name ?: "Jax Delivery - {$config->username}";
                return $config;
            });

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
                'carrier_slug' => 'jax_delivery',
                'delivery_configuration_id' => $request->delivery_configuration_id,
                'pickup_address_id' => null, // Jax n'utilise pas d'adresses personnalisées
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
                            'customer_governorate' => $order->customer_governorate,
                            'order_number' => $order->id,
                            'total_price' => $order->total_price
                        ])
                    ];
                    
                    // Ajouter les colonnes optionnelles si elles existent
                    $columns = \Schema::getColumnListing('shipments');
                    
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
            ->with(['deliveryConfiguration', 'shipments.order'])
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
            'shipments.order',
            'shipments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);
        
        return view('admin.delivery.pickups.show', compact('pickup'));
    }

    /**
     * Valider un enlèvement - Appelle l'API Jax Delivery
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
            
            // TODO: Ici vous intégrerez votre service JaxDelivery pour appeler create_colis
            // Pour chaque shipment du pickup
            $validatedShipments = 0;
            
            foreach ($pickup->shipments as $shipment) {
                // Appel à votre JaxDeliveryService::createShipment()
                // $result = app(JaxDeliveryService::class)->createShipment($shipment->order);
                
                // Pour l'instant, on simule la validation
                $shipment->update([
                    'status' => 'validated',
                    'pos_barcode' => 'JAX_' . uniqid(), // Sera remplacé par le vrai EAN de Jax
                    'return_barcode' => null // Jax ne supporte pas les codes retour
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
    // MÉTHODES DE CONFIGURATION
    // =========================================

    /**
     * Créer une nouvelle configuration Jax Delivery
     */
    public function storeConfiguration(Request $request)
    {
        try {
            $admin = auth('admin')->user();

            $validator = Validator::make($request->all(), [
                'integration_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('delivery_configurations')
                        ->where('admin_id', $admin->id),
                ],
                'token' => 'required|string|min:10',
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
                'carrier_slug' => 'jax_delivery',
                'integration_name' => $request->integration_name,
                'token' => $request->token,
                'environment' => $request->environment,
                'expires_at' => now()->addDays(30), // Les tokens Jax expirent après 30 jours
                'is_active' => true,
            ]);

            Log::info('Configuration créée avec succès', [
                'admin_id' => $admin->id,
                'config_id' => $configuration->id,
                'integration_name' => $request->integration_name
            ]);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration Jax Delivery créée avec succès. Testez maintenant la connexion.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la création de configuration', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['token'])
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
                        ->ignore($config->id),
                ],
                'token' => 'nullable|string|min:10',
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
                'environment' => $request->environment,
            ];

            // Si un nouveau token est fourni
            if ($request->filled('token')) {
                $updateData['token'] = $request->token;
                $updateData['expires_at'] = now()->addDays(30);
            }

            $config->update($updateData);

            Log::info('Configuration mise à jour avec succès', [
                'admin_id' => $admin->id,
                'config_id' => $config->id,
                'changes' => array_keys($updateData)
            ]);

            return redirect()->route('admin.delivery.configuration')
                ->with('success', 'Configuration mise à jour avec succès.');

        } catch (Exception $e) {
            Log::error('Erreur lors de la mise à jour de configuration', [
                'error' => $e->getMessage(),
                'config_id' => $config->id,
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['token'])
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

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

            $result = $config->testConnection();

            if ($result['success']) {
                Log::info('Test de connexion réussi', [
                    'config_id' => $config->id,
                    'admin_id' => auth('admin')->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
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

    // =========================================
    // MÉTHODES UTILITAIRES
    // =========================================

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

        if ($config->expires_at->isBefore(now()->addDays(7))) {
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
    // MÉTHODES DE SUPPRESSION ET AUTRES ACTIONS
    // =========================================

    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        try {
            $config->delete();
            return response()->json(['success' => true, 'message' => 'Configuration supprimée']);
        } catch (Exception $e) {
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
        // TODO: Implémenter le suivi automatique Jax
        return response()->json(['success' => true, 'message' => 'Suivi lancé']);
    }
}