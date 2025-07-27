<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Region;
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
    
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->carriers = config('carriers');
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
            
            return redirect()->route('admin.delivery.configuration')
                ->with('success', "Configuration {$config->integration_name} créée avec succès. Testez la connexion pour l'activer.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création configuration delivery', [
                'admin_id' => $admin->id,
                'carrier_slug' => $carrierSlug,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur lors de la création de la configuration')
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
        
        return view('admin.delivery.configuration-edit', compact('config', 'carrier'));
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
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $oldData = $config->toArray();
            
            $config->update([
                'integration_name' => $request->input('integration_name'),
                'username' => $request->input('username'),
                'password' => $request->input('password') ?: $config->password,
                'settings' => $request->input('settings', []),
            ]);
            
            // Si les credentials ont changé, désactiver la config
            if ($oldData['username'] !== $config->username || 
                ($request->input('password') && $oldData['password'] !== $config->password)) {
                $config->update(['is_active' => false]);
                $message = 'Configuration mise à jour. Testez la connexion pour la réactiver.';
            } else {
                $message = 'Configuration mise à jour avec succès.';
            }
            
            // Enregistrer dans l'historique
            $this->recordConfigurationHistory($config, 'updated', 'Configuration mise à jour', [
                'changes' => array_diff_assoc($config->toArray(), $oldData)
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.delivery.configuration')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour configuration delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur lors de la mise à jour')
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
                return back()->with('error', 
                    "Impossible de supprimer : {$pickupsCount} pickup(s) et {$shipmentsCount} expédition(s) utilisent cette configuration");
            }
            
            // Enregistrer dans l'historique avant suppression
            $this->recordConfigurationHistory($config, 'deleted', 'Configuration supprimée');
            
            $integrationName = $config->integration_name;
            $config->delete();
            
            DB::commit();
            
            return redirect()->route('admin.delivery.configuration')
                ->with('success', "Configuration {$integrationName} supprimée avec succès");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression configuration delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Tester la connexion d'une configuration
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        $this->authorize('update', $config);
        
        try {
            // TODO: Implémenter le test réel avec les services dans Phase 2/3
            // Pour l'instant, simulation du test
            $carrier = $this->carriers[$config->carrier_slug];
            
            // Simulation d'un test de connexion
            $testResult = $this->simulateConnectionTest($config, $carrier);
            
            if ($testResult['success']) {
                // Activer la configuration si le test réussit
                $config->update(['is_active' => true]);
                
                $this->recordConfigurationHistory($config, 'connection_test_success', 
                    'Test de connexion réussi - Configuration activée');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connexion réussie ! Configuration activée.',
                    'details' => $testResult['details']
                ]);
            } else {
                // Désactiver la configuration si le test échoue
                $config->update(['is_active' => false]);
                
                $this->recordConfigurationHistory($config, 'connection_test_failed', 
                    'Test de connexion échoué - Configuration désactivée', [
                        'error' => $testResult['error']
                    ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Test de connexion échoué',
                    'error' => $testResult['error']
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur test connexion delivery', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test de connexion',
                'error' => $e->getMessage()
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
                'message' => 'Erreur lors du changement de statut'
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
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du pickup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // GESTION DES PICKUPS ET SHIPMENTS (STUBS POUR PHASES SUIVANTES)
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
        // TODO: Implémenter dans Phase 4
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité à implémenter dans Phase 4'
        ]);
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
     * Page des statistiques
     */
    public function stats()
    {
        $admin = auth('admin')->user();
        
        // TODO: Implémenter les vraies statistiques
        $stats = [
            'total_shipments' => Shipment::where('admin_id', $admin->id)->count(),
            'delivered_shipments' => Shipment::where('admin_id', $admin->id)->where('status', 'delivered')->count(),
        ];
        
        return view('admin.delivery.stats', compact('stats'));
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
            $rules['password'] = 'required|string|max:255'; // Token API
        } elseif ($carrierSlug === 'mes_colis') {
            $rules['username'] = 'required|string|max:255'; // Token API
            $rules['password'] = 'nullable|string|max:255'; // Non utilisé
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
     * Simuler un test de connexion (pour Phase 1)
     */
    protected function simulateConnectionTest($config, $carrier)
    {
        // Validation basique des credentials
        if (empty($config->username)) {
            return [
                'success' => false,
                'error' => 'Nom d\'utilisateur manquant'
            ];
        }
        
        if ($config->carrier_slug === 'jax_delivery' && empty($config->password)) {
            return [
                'success' => false,
                'error' => 'Token API manquant'
            ];
        }
        
        // Simulation réussie
        return [
            'success' => true,
            'details' => [
                'carrier' => $carrier['name'],
                'api_url' => $carrier['api']['base_url'],
                'test_time' => now()->toISOString(),
                'simulated' => true
            ]
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
        // TODO: Créer une table d'historique pour les configurations
        // ou utiliser order_history avec un order_id null
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

    // Méthodes stubs pour les phases suivantes
    public function refreshPickupStatus(Pickup $pickup) { /* Phase 4 */ }
    public function destroyPickup(Pickup $pickup) { /* Phase 4 */ }
    public function showShipment(Shipment $shipment) { /* Phase 5 */ }
    public function trackShipmentStatus(Shipment $shipment) { /* Phase 5 */ }
    public function markShipmentAsDelivered(Shipment $shipment) { /* Phase 5 */ }
    public function getApiStats() { /* Phase 5 */ }
    public function trackAllShipments() { /* Phase 5 */ }
}