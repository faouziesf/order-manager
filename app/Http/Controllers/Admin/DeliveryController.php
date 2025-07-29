<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Region;
use App\Services\Delivery\ShippingServiceFactory;
use App\Services\Delivery\Contracts\CarrierServiceException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * Configuration des transporteurs depuis le fichier config
     */
    protected $carriers;
    
    /**
     * Factory pour les services de transporteurs
     */
    protected $shippingFactory;
    
    public function __construct(ShippingServiceFactory $shippingFactory)
    {
        $this->middleware('auth:admin');
        $this->carriers = config('carriers');
        $this->shippingFactory = $shippingFactory;
    }

    // ========================================
    // PAGE PRINCIPALE MULTI-TRANSPORTEURS
    // ========================================

    /**
     * Interface principale de sélection des transporteurs
     */
    public function index()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations existantes par transporteur
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->get()
            ->groupBy('carrier_slug');
        
        // Préparer les données des transporteurs avec leurs statuts
        $carriersData = [];
        
        foreach ($this->carriers as $slug => $carrierConfig) {
            if ($slug === 'system' || $slug === 'history_actions') continue;
            
            $carrierConfigurations = $configurations->get($slug, collect());
            $activeConfigs = $carrierConfigurations->where('is_active', true);
            
            $carriersData[$slug] = [
                'config' => $carrierConfig,
                'slug' => $slug,
                'configurations' => $carrierConfigurations,
                'active_configurations' => $activeConfigs,
                'is_configured' => $carrierConfigurations->isNotEmpty(),
                'is_active' => $activeConfigs->isNotEmpty(),
                'status' => $this->getCarrierStatus($carrierConfigurations),
                'stats' => $this->getCarrierStats($admin->id, $slug),
            ];
        }
        
        // Statistiques générales
        $generalStats = [
            'total_configurations' => $configurations->flatten()->count(),
            'active_configurations' => $configurations->flatten()->where('is_active', true)->count(),
            'total_pickups' => Pickup::where('admin_id', $admin->id)->count(),
            'pending_pickups' => Pickup::where('admin_id', $admin->id)->where('status', 'draft')->count(),
            'total_shipments' => Shipment::where('admin_id', $admin->id)->count(),
            'active_shipments' => Shipment::where('admin_id', $admin->id)
                ->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])
                ->count(),
        ];

        return view('admin.delivery.index', compact('carriersData', 'generalStats'));
    }

    // ========================================
    // GESTION DES CONFIGURATIONS
    // ========================================

    /**
     * Page de gestion des configurations
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->orderBy('carrier_slug')
            ->orderBy('integration_name')
            ->get();
        
        // Grouper par transporteur
        $configsByCarrier = $configurations->groupBy('carrier_slug');
        
        return view('admin.delivery.configuration', compact('configsByCarrier', 'configurations'));
    }

    /**
     * Formulaire de création de configuration
     */
    public function createConfiguration(Request $request)
    {
        $carrierSlug = $request->get('carrier', 'jax_delivery');
        
        // Vérifier que le transporteur existe
        if (!isset($this->carriers[$carrierSlug])) {
            return redirect()->route('admin.delivery.configuration')
                ->with('error', 'Transporteur non supporté');
        }
        
        $carrier = $this->carriers[$carrierSlug];
        
        return view('admin.delivery.configuration-create', compact('carrier', 'carrierSlug'));
    }

    /**
     * Sauvegarder une nouvelle configuration
     */
    public function storeConfiguration(Request $request)
    {
        $admin = auth('admin')->user();
        $carrierSlug = $request->input('carrier_slug');
        
        // Validation selon le transporteur
        $rules = $this->getValidationRules($carrierSlug);
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Données de validation invalides'
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Créer la configuration
            $config = DeliveryConfiguration::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $carrierSlug,
                'integration_name' => $request->input('integration_name'),
                'username' => $request->input('username'),
                'password' => $request->input('password', ''),
                'environment' => 'prod', // Production uniquement selon contraintes
                'is_active' => false, // Inactive par défaut, activée après test
                'settings' => $request->input('settings', []),
            ]);
            
            // Enregistrer dans l'historique
            $this->recordConfigurationHistory($config, 'created', 'Configuration créée');
            
            DB::commit();
            
            // Si c'est une requête AJAX (pour test), retourner JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuration créée avec succès',
                    'config_id' => $config->id,
                    'config' => $config
                ]);
            }
            
            return redirect()->route('admin.delivery.configuration')
                ->with('success', "Configuration {$config->integration_name} créée avec succès. Testez la connexion pour l'activer.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création configuration delivery', [
                'admin_id' => $admin->id,
                'carrier_slug' => $carrierSlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la création: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'Erreur lors de la création de la configuration: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Formulaire d'édition de configuration
     */
    public function editConfiguration(DeliveryConfiguration $config)
    {
        $this->authorize('update', $config);
        
        $carrier = $this->carriers[$config->carrier_slug];
        $carrierSlug = $config->carrier_slug;
        
        return view('admin.delivery.configuration-edit', compact('config', 'carrier', 'carrierSlug'));
    }

    /**
     * Mettre à jour une configuration
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        $this->authorize('update', $config);
        
        // Validation selon le transporteur
        $rules = $this->getValidationRules($config->carrier_slug, $config->id);
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Données de validation invalides'
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $oldData = $config->toArray();
            
            $updateData = [
                'integration_name' => $request->input('integration_name'),
                'username' => $request->input('username'),
                'settings' => $request->input('settings', []),
            ];
            
            // Mettre à jour le mot de passe seulement si fourni
            if ($request->filled('password')) {
                $updateData['password'] = $request->input('password');
            }
            
            $config->update($updateData);
            
            // Si les credentials ont changé, désactiver la config
            $credentialsChanged = ($oldData['username'] !== $config->username) || 
                                  ($request->filled('password') && $request->input('password') !== $oldData['password']);
            
            if ($credentialsChanged) {
                $config->update(['is_active' => false]);
                $message = 'Configuration mise à jour. Testez la connexion pour la réactiver.';
            } else {
                $message = 'Configuration mise à jour avec succès.';
            }
            
            // Enregistrer dans l'historique
            $this->recordConfigurationHistory($config, 'updated', 'Configuration mise à jour', [
                'changes' => array_diff_assoc($config->fresh()->toArray(), $oldData)
            ]);
            
            DB::commit();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'config' => $config->fresh()
                ]);
            }
            
            return redirect()->route('admin.delivery.configuration')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour configuration delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        $this->authorize('delete', $config);
        
        try {
            DB::beginTransaction();
            
            // Vérifier qu'aucun pickup/shipment n'utilise cette config
            $pickupsCount = Pickup::where('delivery_configuration_id', $config->id)->count();
            $shipmentsCount = Shipment::where('admin_id', $config->admin_id)
                ->where('carrier_slug', $config->carrier_slug)
                ->count();
            
            if ($pickupsCount > 0 || $shipmentsCount > 0) {
                $message = "Impossible de supprimer : {$pickupsCount} pickup(s) et {$shipmentsCount} expédition(s) utilisent cette configuration";
                
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 400);
                }
                
                return back()->with('error', $message);
            }
            
            // Enregistrer dans l'historique avant suppression
            $this->recordConfigurationHistory($config, 'deleted', 'Configuration supprimée');
            
            $integrationName = $config->integration_name;
            $config->delete();
            
            DB::commit();
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Configuration {$integrationName} supprimée avec succès"
                ]);
            }
            
            return redirect()->route('admin.delivery.configuration')
                ->with('success', "Configuration {$integrationName} supprimée avec succès");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression configuration delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la suppression: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Tester la connexion d'une configuration
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        $this->authorize('update', $config);
        
        try {
            Log::info("Test de connexion demandé", [
                'config_id' => $config->id,
                'carrier_slug' => $config->carrier_slug,
                'integration_name' => $config->integration_name
            ]);
            
            // Créer le service approprié
            $service = $this->shippingFactory->createFromConfig($config);
            
            // Effectuer le test
            $result = $service->testConnection($config);
            
            Log::info("Résultat test de connexion", [
                'config_id' => $config->id,
                'success' => $result['success'],
                'message' => $result['message'] ?? $result['error'] ?? 'No message'
            ]);
            
            if ($result['success']) {
                // Activer la configuration si le test réussit
                $config->markAsTestedSuccessfully();
                
                $this->recordConfigurationHistory($config, 'connection_test_success', 
                    'Test de connexion réussi - Configuration activée', $result);
                
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'Connexion réussie ! Configuration activée.',
                    'details' => $result['details'] ?? null
                ]);
            } else {
                // Désactiver la configuration si le test échoue
                $config->markAsTestFailed($result['error'] ?? 'Test échoué');
                
                $this->recordConfigurationHistory($config, 'connection_test_failed', 
                    'Test de connexion échoué - Configuration désactivée', $result);
                
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Test de connexion échoué',
                    'details' => $result['details'] ?? null
                ], 400);
            }
            
        } catch (CarrierServiceException $e) {
            Log::error("Erreur service transporteur lors du test", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse()
            ]);
            
            $config->markAsTestFailed($e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse()
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Erreur test connexion delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $config->markAsTestFailed($e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du test de connexion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/désactiver une configuration
     */
    public function toggleConfiguration(DeliveryConfiguration $config)
    {
        $this->authorize('update', $config);
        
        try {
            $newStatus = !$config->is_active;
            $config->update(['is_active' => $newStatus]);
            
            $action = $newStatus ? 'activated' : 'deactivated';
            $message = $newStatus ? 'Configuration activée' : 'Configuration désactivée';
            
            $this->recordConfigurationHistory($config, $action, $message);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur toggle configuration delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du changement de statut: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // PRÉPARATION D'ENLÈVEMENT
    // ========================================

    /**
     * Page de préparation des enlèvements
     */
    public function preparation()
    {
        $admin = auth('admin')->user();
        
        // Récupérer les configurations actives
        $activeConfigurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->get();
        
        if ($activeConfigurations->isEmpty()) {
            return redirect()->route('admin.delivery.configuration')
                ->with('warning', 'Aucune configuration de transporteur active. Configurez un transporteur d\'abord.');
        }
        
        return view('admin.delivery.preparation', compact('activeConfigurations'));
    }

    /**
     * Obtenir les commandes disponibles pour expédition (API)
     */
    public function getAvailableOrders(Request $request)
    {
        $admin = auth('admin')->user();
        
        $query = Order::where('admin_id', $admin->id)
            ->where('status', 'confirmée')
            ->where('is_suspended', false)
            ->whereDoesntHave('shipments') // Commandes pas encore expédiées
            ->with(['items.product', 'region']);
        
        // Filtres optionnels
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }
        
        if ($request->has('governorate') && $request->governorate) {
            $query->where('customer_governorate', $request->governorate);
        }
        
        // Pagination
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));
        
        // Enrichir avec les informations nécessaires
        $orders->getCollection()->transform(function ($order) {
            $order->can_be_shipped = $order->canBeShipped();
            $order->stock_issues = $order->can_be_shipped ? [] : $order->getStockIssues();
            $order->region_name = $order->region ? $order->region->name : 'Région inconnue';
            return $order;
        });
        
        return response()->json([
            'success' => true,
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Créer un pickup avec les commandes sélectionnées
     */
    public function createPickup(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'delivery_configuration_id' => 'required|exists:delivery_configurations,id',
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'pickup_date' => 'nullable|date|after_or_equal:today',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Vérifier la configuration
            $config = DeliveryConfiguration::where('id', $request->delivery_configuration_id)
                ->where('admin_id', $admin->id)
                ->where('is_active', true)
                ->firstOrFail();
            
            // Vérifier les commandes
            $orders = Order::where('admin_id', $admin->id)
                ->whereIn('id', $request->order_ids)
                ->where('status', 'confirmée')
                ->where('is_suspended', false)
                ->whereDoesntHave('shipments')
                ->get();
            
            if ($orders->count() !== count($request->order_ids)) {
                throw new \Exception('Certaines commandes ne sont pas disponibles pour expédition');
            }
            
            // Vérifier le stock pour toutes les commandes
            $stockIssues = [];
            foreach ($orders as $order) {
                if (!$order->canBeShipped()) {
                    $stockIssues[] = "Commande #{$order->id}: " . implode(', ', 
                        array_column($order->getStockIssues(), 'message'));
                }
            }
            
            if (!empty($stockIssues)) {
                throw new \Exception('Problèmes de stock détectés: ' . implode('; ', $stockIssues));
            }
            
            // Créer le pickup
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $config->carrier_slug,
                'delivery_configuration_id' => $config->id,
                'status' => 'draft',
                'pickup_date' => $request->pickup_date ?: now()->addDay()->format('Y-m-d'),
            ]);
            
            // Créer les shipments pour chaque commande
            foreach ($orders as $order) {
                $shipment = Shipment::create([
                    'admin_id' => $admin->id,
                    'order_id' => $order->id,
                    'pickup_id' => $pickup->id,
                    'carrier_slug' => $config->carrier_slug,
                    'status' => 'created',
                    'weight' => $this->calculateOrderWeight($order),
                    'value' => $order->total_price,
                    'cod_amount' => $order->total_price,
                    'nb_pieces' => $order->items->sum('quantity'),
                    'pickup_date' => $pickup->pickup_date,
                    'content_description' => $this->generateContentDescription($order),
                    'recipient_info' => [
                        'name' => $order->customer_name,
                        'phone' => $order->customer_phone,
                        'phone_2' => $order->customer_phone_2,
                        'address' => $order->customer_address,
                        'governorate' => $order->customer_governorate,
                        'city' => $order->customer_city,
                    ],
                ]);
                
                // Enregistrer dans l'historique de la commande
                $order->recordHistory(
                    'shipment_created',
                    "Expédition créée dans pickup #{$pickup->id} via {$config->integration_name}",
                    [
                        'pickup_id' => $pickup->id,
                        'shipment_id' => $shipment->id,
                        'carrier_slug' => $config->carrier_slug,
                        'integration_name' => $config->integration_name,
                    ],
                    $order->status,
                    'expédiée',
                    null,
                    null,
                    null,
                    $this->carriers[$config->carrier_slug]['name']
                );
                
                // Mettre à jour le statut de la commande
                $order->update(['status' => 'expédiée']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickup->id} créé avec {$orders->count()} commande(s)",
                'pickup_id' => $pickup->id,
                'orders_count' => $orders->count(),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création pickup', [
                'admin_id' => $admin->id,
                'config_id' => $request->delivery_configuration_id,
                'order_ids' => $request->order_ids,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du pickup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // GESTION DES PICKUPS ET SHIPMENTS
    // ========================================

    /**
     * Page de gestion des pickups
     */
    public function pickups()
    {
        $admin = auth('admin')->user();
        
        $pickups = Pickup::where('admin_id', $admin->id)
            ->with(['deliveryConfiguration', 'shipments.order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.delivery.pickups', compact('pickups'));
    }

    /**
     * Afficher les détails d'un pickup
     */
    public function showPickup(Pickup $pickup)
    {
        $this->authorize('view', $pickup);
        
        $pickup->load(['shipments.order', 'deliveryConfiguration']);
        
        return response()->json([
            'success' => true,
            'pickup' => $pickup,
        ]);
    }

    /**
     * Valider un pickup (envoi vers l'API transporteur)
     */
    public function validatePickup(Pickup $pickup)
    {
        $this->authorize('update', $pickup);
        
        try {
            // Valider le pickup
            $result = $pickup->validate();
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Pickup #{$pickup->id} validé avec succès"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Impossible de valider le pickup'
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur validation pickup', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la validation: ' . $e->getMessage()
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
            ->with(['order', 'pickup'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.delivery.shipments', compact('shipments'));
    }

    /**
     * Afficher les détails d'une expédition
     */
    public function showShipment(Shipment $shipment)
    {
        $this->authorize('view', $shipment);
        
        $shipment->load(['order', 'pickup']);
        
        return response()->json([
            'success' => true,
            'shipment' => $shipment,
        ]);
    }

    /**
     * Suivre le statut d'une expédition
     */
    public function trackShipmentStatus(Shipment $shipment)
    {
        $this->authorize('view', $shipment);
        
        try {
            if (!$shipment->can_be_tracked) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cette expédition ne peut pas être suivie'
                ], 400);
            }
            
            // Créer le service et suivre l'expédition
            $config = $shipment->pickup->deliveryConfiguration;
            $service = $this->shippingFactory->createFromConfig($config);
            
            $result = $service->trackShipment($shipment->tracking_number, $config);
            
            if ($result['success']) {
                // Mettre à jour le statut si nécessaire
                if (isset($result['internal_status']) && $result['internal_status'] !== $shipment->status) {
                    $shipment->updateStatus(
                        $result['internal_status'],
                        $result['carrier_status'] ?? null,
                        $result['carrier_status_label'] ?? null,
                        'Mise à jour automatique du suivi'
                    );
                }
                
                return response()->json([
                    'success' => true,
                    'tracking_data' => $result,
                    'shipment' => $shipment->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erreur de suivi'
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur suivi expédition', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du suivi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une expédition comme livrée
     */
    public function markShipmentAsDelivered(Shipment $shipment)
    {
        $this->authorize('update', $shipment);
        
        try {
            $shipment->markAsDelivered('Marqué comme livré manuellement');
            
            return response()->json([
                'success' => true,
                'message' => 'Expédition marquée comme livrée',
                'shipment' => $shipment->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur marquage livraison', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du marquage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Page des statistiques
     */
    public function stats()
    {
        $admin = auth('admin')->user();
        
        $stats = [
            'configurations' => DeliveryConfiguration::getStatusCountsForAdmin($admin->id),
            'pickups' => Pickup::getStatsForAdmin($admin->id),
            'shipments' => Shipment::getStatsForAdmin($admin->id),
            'delivery_stats' => $admin->getDeliveryStats(30),
        ];
        
        return view('admin.delivery.stats', compact('stats'));
    }

    /**
     * API des statistiques
     */
    public function getApiStats()
    {
        $admin = auth('admin')->user();
        
        return response()->json([
            'success' => true,
            'stats' => [
                'configurations' => DeliveryConfiguration::getStatusCountsForAdmin($admin->id),
                'pickups' => Pickup::getStatsForAdmin($admin->id),
                'shipments' => Shipment::getStatsForAdmin($admin->id),
                'delivery_stats' => $admin->getDeliveryStats(30),
            ]
        ]);
    }

    /**
     * Suivre toutes les expéditions actives
     */
    public function trackAllShipments()
    {
        $admin = auth('admin')->user();
        
        try {
            $shipments = Shipment::getTrackableShipments($admin->id);
            $results = [];
            
            foreach ($shipments as $shipment) {
                try {
                    $config = $shipment->pickup->deliveryConfiguration;
                    $service = $this->shippingFactory->createFromConfig($config);
                    
                    $result = $service->trackShipment($shipment->tracking_number, $config);
                    
                    if ($result['success'] && isset($result['internal_status'])) {
                        if ($result['internal_status'] !== $shipment->status) {
                            $shipment->updateStatus(
                                $result['internal_status'],
                                $result['carrier_status'] ?? null,
                                $result['carrier_status_label'] ?? null,
                                'Mise à jour automatique'
                            );
                            $results[$shipment->id] = 'updated';
                        } else {
                            $results[$shipment->id] = 'no_change';
                        }
                    } else {
                        $results[$shipment->id] = 'error';
                    }
                    
                } catch (\Exception $e) {
                    $results[$shipment->id] = 'error: ' . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Suivi de ' . count($shipments) . ' expéditions terminé',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suivi global', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du suivi global: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Obtenir les règles de validation selon le transporteur
     */
    protected function getValidationRules($carrierSlug, $excludeId = null)
    {
        $carrier = $this->carriers[$carrierSlug];
        $adminId = auth('admin')->id();
        
        $rules = [
            'carrier_slug' => 'required|in:' . implode(',', array_keys($this->carriers)),
            'integration_name' => [
                'required',
                'string',
                'max:255',
                "unique:delivery_configurations,integration_name,{$excludeId},id,admin_id,{$adminId},carrier_slug,{$carrierSlug}"
            ],
        ];
        
        // Règles spécifiques selon le transporteur
        if ($carrierSlug === 'jax_delivery') {
            $rules['username'] = 'required|string|max:255'; // Numéro de compte
            $rules['password'] = 'required|string|max:65535'; // Token API (TEXT field)
        } elseif ($carrierSlug === 'mes_colis') {
            $rules['username'] = 'required|string|max:65535'; // Token API
            $rules['password'] = 'nullable|string|max:65535'; // Non utilisé
        }
        
        return $rules;
    }

    /**
     * Obtenir le statut d'un transporteur
     */
    protected function getCarrierStatus($configurations)
    {
        if ($configurations->isEmpty()) {
            return 'non_configuré';
        }
        
        if ($configurations->where('is_active', true)->isNotEmpty()) {
            return 'connecté';
        }
        
        return 'configuré_inactif';
    }

    /**
     * Obtenir les statistiques d'un transporteur
     */
    protected function getCarrierStats($adminId, $carrierSlug)
    {
        return [
            'configurations' => DeliveryConfiguration::where('admin_id', $adminId)
                ->where('carrier_slug', $carrierSlug)->count(),
            'active_configurations' => DeliveryConfiguration::where('admin_id', $adminId)
                ->where('carrier_slug', $carrierSlug)->where('is_active', true)->count(),
            'pickups' => Pickup::where('admin_id', $adminId)
                ->where('carrier_slug', $carrierSlug)->count(),
            'shipments' => Shipment::where('admin_id', $adminId)
                ->where('carrier_slug', $carrierSlug)->count(),
        ];
    }

    /**
     * Calculer le poids d'une commande
     */
    protected function calculateOrderWeight($order)
    {
        // Logique simple pour Phase 1
        $itemsCount = $order->items->sum('quantity');
        return max(1.0, $itemsCount * 0.5); // 0.5kg par article minimum
    }

    /**
     * Générer la description du contenu
     */
    protected function generateContentDescription($order)
    {
        $items = $order->items->take(3)->pluck('product.name')->toArray();
        $description = implode(', ', $items);
        
        if ($order->items->count() > 3) {
            $description .= ' et ' . ($order->items->count() - 3) . ' autres articles';
        }
        
        return substr($description, 0, 200);
    }

    /**
     * Enregistrer dans l'historique des configurations
     */
    protected function recordConfigurationHistory($config, $action, $notes, $data = [])
    {
        Log::info("Configuration {$action}", [
            'config_id' => $config->id,
            'admin_id' => $config->admin_id,
            'carrier_slug' => $config->carrier_slug,
            'integration_name' => $config->integration_name,
            'action' => $action,
            'notes' => $notes,
            'data' => $data,
        ]);
    }
}