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
        
        // Vérifier si le fichier de configuration existe
        $carriers = config('carriers', []);
        if (empty($carriers)) {
            return redirect()->back()->with('error', 'Configuration des transporteurs manquante');
        }
        
        try {
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
            
            // Statistiques générales avec gestion d'erreurs et valeurs par défaut
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

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la page delivery', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            // Valeurs par défaut en cas d'erreur
            $carriersData = [];
            $generalStats = [
                'total_configurations' => 0,
                'active_configurations' => 0,
                'total_pickups' => 0,
                'pending_pickups' => 0,
                'total_shipments' => 0,
                'active_shipments' => 0,
            ];
            
            return view('admin.delivery.index', compact('carriersData', 'generalStats'))
                ->with('error', 'Erreur lors du chargement des données de livraison');
        }

        return view('admin.delivery.index', compact('carriersData', 'generalStats'));
    }

    /**
     * API pour les statistiques générales (pour le rafraîchissement temps réel)
     */
    public function getGeneralStats()
    {
        $admin = auth('admin')->user();
        
        try {
            $generalStats = [
                'total_configurations' => DeliveryConfiguration::where('admin_id', $admin->id)->count(),
                'active_configurations' => DeliveryConfiguration::where('admin_id', $admin->id)
                    ->where('is_active', true)->count(),
                'total_pickups' => Pickup::where('admin_id', $admin->id)->count(),
                'pending_pickups' => Pickup::where('admin_id', $admin->id)
                    ->where('status', 'draft')->count(),
                'total_shipments' => Shipment::where('admin_id', $admin->id)->count(),
                'active_shipments' => Shipment::where('admin_id', $admin->id)
                    ->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'general_stats' => $generalStats
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération statistiques delivery', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'general_stats' => [
                    'total_configurations' => 0,
                    'active_configurations' => 0,
                    'total_pickups' => 0,
                    'pending_pickups' => 0,
                    'total_shipments' => 0,
                    'active_shipments' => 0,
                ]
            ]);
        }
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
     * Sauvegarder une nouvelle configuration avec support des longs tokens
     */
    public function storeConfiguration(Request $request)
    {
        $admin = auth('admin')->user();
        $carrierSlug = $request->input('carrier_slug');
        
        // Validation selon le transporteur avec support des longs tokens
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
            
            // Préparer les données avec validation des tokens
            $configData = [
                'admin_id' => $admin->id,
                'carrier_slug' => $carrierSlug,
                'integration_name' => $request->input('integration_name'),
                'environment' => 'prod', // Production uniquement selon contraintes
                'is_active' => false, // Inactive par défaut, activée après test
                'settings' => $request->input('settings', []),
            ];

            // Gestion spécifique des credentials selon le transporteur
            if ($carrierSlug === 'jax_delivery') {
                // JAX Delivery : username = numéro de compte, password = token JWT
                $configData['username'] = $request->input('username'); // Numéro de compte (ex: 2304)
                $configData['password'] = $request->input('password'); // Token JWT long
                
                // Validation du format JWT pour JAX
                if (!$this->isValidJwtToken($request->input('password'))) {
                    Log::warning('Token JAX non-JWT détecté', [
                        'admin_id' => $admin->id,
                        'token_preview' => substr($request->input('password'), 0, 20) . '...'
                    ]);
                }
                
            } elseif ($carrierSlug === 'mes_colis') {
                // Mes Colis : username = token API, password = null ou vide
                $configData['username'] = $request->input('username'); // Token API (ex: OL6B3FUA526SMLMBN7U3QZ1UMW5YW91D)
                $configData['password'] = null; // Non utilisé pour Mes Colis
            }
            
            // Créer la configuration
            $config = DeliveryConfiguration::create($configData);
            
            // Enregistrer dans l'historique
            $this->recordConfigurationHistory($config, 'created', 'Configuration créée', [
                'carrier_slug' => $carrierSlug,
                'integration_name' => $config->integration_name,
                'username_length' => strlen($config->username),
                'password_length' => $config->password ? strlen($config->password) : 0,
            ]);
            
            DB::commit();
            
            Log::info('Configuration delivery créée', [
                'config_id' => $config->id,
                'admin_id' => $admin->id,
                'carrier_slug' => $carrierSlug,
                'integration_name' => $config->integration_name,
                'token_lengths' => [
                    'username' => strlen($config->username),
                    'password' => $config->password ? strlen($config->password) : 0,
                ]
            ]);
            
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
        // Vérification d'autorisation simplifiée
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        $carrier = $this->carriers[$config->carrier_slug];
        $carrierSlug = $config->carrier_slug;
        
        return view('admin.delivery.configuration-edit', compact('config', 'carrier', 'carrierSlug'));
    }

    /**
     * Mettre à jour une configuration avec support des longs tokens
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        // Vérification d'autorisation simplifiée
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
                'settings' => $request->input('settings', []),
            ];
            
            // Gestion spécifique des credentials selon le transporteur
            if ($config->carrier_slug === 'jax_delivery') {
                // JAX Delivery : username = numéro de compte, password = token JWT
                $updateData['username'] = $request->input('username');
                if ($request->filled('password')) {
                    $updateData['password'] = $request->input('password');
                    
                    // Validation du format JWT pour JAX
                    if (!$this->isValidJwtToken($request->input('password'))) {
                        Log::warning('Token JAX non-JWT détecté lors de la mise à jour', [
                            'config_id' => $config->id,
                            'token_preview' => substr($request->input('password'), 0, 20) . '...'
                        ]);
                    }
                }
                
            } elseif ($config->carrier_slug === 'mes_colis') {
                // Mes Colis : username = token API
                $updateData['username'] = $request->input('username');
                // password reste null pour Mes Colis
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
                'changes' => array_diff_assoc($config->fresh()->toArray(), $oldData),
                'credentials_changed' => $credentialsChanged
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
        // Vérification d'autorisation simplifiée
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
     * Tester la connexion d'une configuration avec support des longs tokens
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        // Vérification d'autorisation simplifiée
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            Log::info("Test de connexion demandé", [
                'config_id' => $config->id,
                'carrier_slug' => $config->carrier_slug,
                'integration_name' => $config->integration_name,
                'username_length' => strlen($config->username),
                'password_length' => $config->password ? strlen($config->password) : 0,
                'is_jwt_token' => $config->password ? $this->isValidJwtToken($config->password) : false,
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
        // Vérification d'autorisation simplifiée
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
    // PRÉPARATION D'ENLÈVEMENT - VERSION CORRIGÉE
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
     * API pour obtenir les commandes disponibles - VERSION SIMPLIFIÉE
     */
    public function getAvailableOrders(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $query = Order::where('admin_id', $admin->id)
                ->where('status', 'confirmée')
                ->where(function($q) {
                    $q->where('is_suspended', false)
                      ->orWhereNull('is_suspended');
                })
                ->whereDoesntHave('shipments') // Commandes pas encore expédiées
                ->with(['items.product', 'region']);
            
            // Filtres optionnels
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('id', $search);
                });
            }
            
            if ($request->filled('governorate')) {
                $query->where('customer_governorate', $request->governorate);
            }
            
            // Pagination
            $perPage = min($request->get('per_page', 20), 50); // Max 50 par page
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            // Enrichir avec les informations nécessaires
            $orders->getCollection()->transform(function ($order) {
                $order->can_be_shipped = true; // Simplification - toutes les commandes récupérées peuvent être expédiées
                $order->region_name = $order->region ? $order->region->name : ($order->customer_governorate ?: 'Région inconnue');
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
            
        } catch (\Exception $e) {
            Log::error('Erreur récupération commandes', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un pickup avec les commandes sélectionnées - VERSION SIMPLIFIÉE
     */
    public function createPickup(Request $request)
    {
        $admin = auth('admin')->user();
        
        Log::info('Création pickup demandée', [
            'admin_id' => $admin->id,
            'request_data' => $request->all()
        ]);
        
        // Validation simplifiée
        try {
            $validator = Validator::make($request->all(), [
                'delivery_configuration_id' => 'required|integer|exists:delivery_configurations,id',
                'order_ids' => 'required|array|min:1|max:50', // Limite de 50 commandes
                'order_ids.*' => 'integer|exists:orders,id',
                'pickup_date' => 'nullable|date|after_or_equal:today',
            ]);
            
            if ($validator->fails()) {
                Log::warning('Validation échouée', [
                    'errors' => $validator->errors()->toArray(),
                    'admin_id' => $admin->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides : ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            // 1. Vérifier la configuration de transporteur
            $config = DeliveryConfiguration::where('id', $request->delivery_configuration_id)
                ->where('admin_id', $admin->id)
                ->where('is_active', true)
                ->first();
            
            if (!$config) {
                throw new \Exception('Configuration de transporteur non trouvée ou inactive');
            }
            
            Log::info('Configuration trouvée', [
                'config_id' => $config->id,
                'carrier_slug' => $config->carrier_slug,
                'integration_name' => $config->integration_name
            ]);
            
            // 2. Récupérer et vérifier les commandes
            $orders = Order::where('admin_id', $admin->id)
                ->whereIn('id', $request->order_ids)
                ->where('status', 'confirmée')
                ->where(function($query) {
                    $query->where('is_suspended', false)
                          ->orWhereNull('is_suspended');
                })
                ->get();
            
            Log::info('Commandes récupérées', [
                'orders_requested' => count($request->order_ids),
                'orders_found' => $orders->count(),
                'order_ids_found' => $orders->pluck('id')->toArray()
            ]);
            
            if ($orders->isEmpty()) {
                throw new \Exception('Aucune commande valide trouvée');
            }
            
            if ($orders->count() !== count($request->order_ids)) {
                $missing = array_diff($request->order_ids, $orders->pluck('id')->toArray());
                Log::warning('Commandes manquantes', ['missing_ids' => $missing]);
                throw new \Exception('Certaines commandes ne sont pas disponibles (IDs: ' . implode(', ', $missing) . ')');
            }
            
            // 3. Vérifier qu'aucune commande n'a déjà d'expédition
            $ordersWithShipments = $orders->filter(function($order) {
                return $order->shipments()->exists();
            });
            
            if ($ordersWithShipments->count() > 0) {
                $conflictIds = $ordersWithShipments->pluck('id')->toArray();
                throw new \Exception('Les commandes suivantes ont déjà des expéditions : ' . implode(', ', $conflictIds));
            }
            
            // 4. Créer le pickup
            $pickupDate = $request->pickup_date ?: now()->addDay()->format('Y-m-d');
            
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $config->carrier_slug,
                'delivery_configuration_id' => $config->id,
                'status' => 'draft',
                'pickup_date' => $pickupDate,
            ]);
            
            Log::info('Pickup créé', [
                'pickup_id' => $pickup->id,
                'carrier_slug' => $config->carrier_slug,
                'pickup_date' => $pickupDate
            ]);
            
            // 5. Créer les expéditions pour chaque commande
            $shipmentsCreated = 0;
            
            foreach ($orders as $order) {
                try {
                    $shipment = Shipment::create([
                        'admin_id' => $admin->id,
                        'order_id' => $order->id,
                        'pickup_id' => $pickup->id,
                        'carrier_slug' => $config->carrier_slug,
                        'status' => 'created',
                        'weight' => $this->calculateOrderWeight($order),
                        'value' => $order->total_price,
                        'cod_amount' => $order->total_price,
                        'nb_pieces' => $order->items ? $order->items->sum('quantity') : 1,
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
                    
                    // Mettre à jour le statut de la commande
                    $order->update(['status' => 'expédiée']);
                    
                    // Enregistrer dans l'historique
                    $order->recordHistory(
                        'shipment_created',
                        "Expédition créée dans pickup #{$pickup->id} via {$config->integration_name}",
                        [
                            'pickup_id' => $pickup->id,
                            'shipment_id' => $shipment->id,
                            'carrier_slug' => $config->carrier_slug,
                            'integration_name' => $config->integration_name,
                        ],
                        'confirmée',
                        'expédiée',
                        null,
                        null,
                        null,
                        $config->carrier_name ?? $config->carrier_slug
                    );
                    
                    $shipmentsCreated++;
                    
                    Log::info('Shipment créé', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $order->id,
                        'pickup_id' => $pickup->id
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Erreur création shipment', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue avec les autres commandes
                    continue;
                }
            }
            
            DB::commit();
            
            Log::info('Pickup créé avec succès', [
                'pickup_id' => $pickup->id,
                'orders_processed' => $orders->count(),
                'shipments_created' => $shipmentsCreated
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Enlèvement #{$pickup->id} créé avec succès ! {$shipmentsCreated} expédition(s) créée(s).",
                'data' => [
                    'pickup_id' => $pickup->id,
                    'orders_count' => $orders->count(),
                    'shipments_created' => $shipmentsCreated,
                    'carrier_name' => $config->carrier_name ?? $config->carrier_slug,
                    'pickup_date' => $pickup->pickup_date,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur création pickup', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // Messages d'erreur plus clairs
            $errorMessage = $e->getMessage();
            
            if (strpos($errorMessage, 'SQLSTATE') !== false) {
                if (strpos($errorMessage, '23000') !== false || strpos($errorMessage, 'Duplicate') !== false) {
                    $errorMessage = 'Une ou plusieurs commandes ont déjà été expédiées. Veuillez recharger la page.';
                } else {
                    $errorMessage = 'Erreur de base de données. Veuillez réessayer.';
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage(),
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'request_data' => $request->all(),
                    'timestamp' => now()->toISOString()
                ]
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
        // Vérification d'autorisation simplifiée
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
        // Vérification d'autorisation simplifiée
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
        // Vérification d'autorisation simplifiée
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
        // Vérification d'autorisation simplifiée
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
        // Vérification d'autorisation simplifiée
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
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
     * Calculer le poids d'une commande - VERSION SIMPLIFIÉE
     */
    protected function calculateOrderWeight($order): float
    {
        try {
            $itemsCount = $order->items ? $order->items->sum('quantity') : 1;
            return max(1.0, $itemsCount * 0.5); // 0.5kg par article minimum
        } catch (\Exception $e) {
            Log::warning('Erreur calcul poids', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return 1.0; // Poids par défaut
        }
    }

    /**
     * Générer la description du contenu - VERSION SIMPLIFIÉE
     */
    protected function generateContentDescription($order): string
    {
        try {
            if (!$order->items || $order->items->isEmpty()) {
                return 'Commande e-commerce #' . $order->id;
            }
            
            $items = $order->items->take(3)->map(function($item) {
                return $item->product ? $item->product->name : 'Produit';
            })->filter()->toArray();
            
            $description = implode(', ', $items);
            
            if ($order->items->count() > 3) {
                $description .= ' et ' . ($order->items->count() - 3) . ' autres articles';
            }
            
            return substr($description ?: 'Commande e-commerce #' . $order->id, 0, 200);
            
        } catch (\Exception $e) {
            Log::warning('Erreur génération description', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return 'Commande e-commerce #' . $order->id;
        }
    }

    /**
     * Obtenir les règles de validation selon le transporteur avec support des longs tokens
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
        
        // Règles spécifiques selon le transporteur avec support des longs tokens
        if ($carrierSlug === 'jax_delivery') {
            $rules['username'] = 'required|string|max:255'; // Numéro de compte (ex: 2304)
            $rules['password'] = 'required|string|min:10'; // Token JWT (très long, minimum 10 caractères)
        } elseif ($carrierSlug === 'mes_colis') {
            $rules['username'] = 'required|string|min:10|max:255'; // Token API (ex: OL6B3FUA526SMLMBN7U3QZ1UMW5YW91D)
            $rules['password'] = 'nullable|string'; // Non utilisé pour Mes Colis
        }
        
        return $rules;
    }

    /**
     * Vérifier si une chaîne est un token JWT valide
     */
    protected function isValidJwtToken($token)
    {
        if (!is_string($token) || empty($token)) {
            return false;
        }
        
        $parts = explode('.', $token);
        return count($parts) === 3 && 
               strlen($parts[0]) > 10 && 
               strlen($parts[1]) > 10 && 
               strlen($parts[2]) > 10;
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

    // ========================================
    // MÉTHODES DE TEST ET DIAGNOSTIC
    // ========================================

    /**
     * Diagnostic complet du système
     */
    public function testSystem()
    {
        $admin = auth('admin')->user();
        
        try {
            $diagnostics = [
                'admin_info' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'is_active' => $admin->is_active,
                ],
                
                'database_counts' => [
                    'total_orders' => $admin->orders()->count(),
                    'confirmed_orders' => $admin->orders()->where('status', 'confirmée')->count(),
                    'available_orders' => $admin->orders()
                        ->where('status', 'confirmée')
                        ->where(function($q) {
                            $q->where('is_suspended', false)->orWhereNull('is_suspended');
                        })
                        ->whereDoesntHave('shipments')
                        ->count(),
                    'delivery_configurations' => $admin->deliveryConfigurations()->count(),
                    'active_configurations' => $admin->deliveryConfigurations()->where('is_active', true)->count(),
                    'pickups' => Pickup::where('admin_id', $admin->id)->count(),
                    'shipments' => Shipment::where('admin_id', $admin->id)->count(),
                ],
                
                'configurations_detail' => $admin->deliveryConfigurations()->get()->map(function($config) {
                    return [
                        'id' => $config->id,
                        'carrier_slug' => $config->carrier_slug,
                        'integration_name' => $config->integration_name,
                        'is_active' => $config->is_active,
                        'created_at' => $config->created_at,
                    ];
                }),
                
                'sample_orders' => $admin->orders()
                    ->where('status', 'confirmée')
                    ->where(function($q) {
                        $q->where('is_suspended', false)->orWhereNull('is_suspended');
                    })
                    ->whereDoesntHave('shipments')
                    ->take(5)
                    ->get(['id', 'customer_name', 'customer_phone', 'total_price', 'created_at']),
                
                'routes_check' => [
                    'preparation_route' => route('admin.delivery.preparation'),
                    'preparation_orders' => route('admin.delivery.preparation.orders'),
                    'preparation_store' => route('admin.delivery.preparation.store'),
                    'pickups_index' => route('admin.delivery.pickups'),
                ],
                
                'config_check' => [
                    'carriers_config_exists' => config('carriers') !== null,
                    'carriers_available' => config('carriers') ? array_keys(config('carriers')) : [],
                ],
                
                'timestamp' => now()->toISOString(),
            ];
            
            return response()->json($diagnostics, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => $admin->id,
            ], 500);
        }
    }

    /**
     * Test de création de pickup simple
     */
    public function testCreatePickup(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            Log::info('Test création pickup', [
                'admin_id' => $admin->id,
                'request_data' => $request->all()
            ]);
            
            // Test de base
            $config = $admin->deliveryConfigurations()->where('is_active', true)->first();
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune configuration active trouvée'
                ]);
            }
            
            $orders = $admin->orders()
                ->where('status', 'confirmée')
                ->where(function($q) {
                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                })
                ->whereDoesntHave('shipments')
                ->take(2)
                ->get();
                
            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune commande disponible pour test'
                ]);
            }
            
            // Créer un pickup de test
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $config->carrier_slug,
                'delivery_configuration_id' => $config->id,
                'status' => 'draft',
                'pickup_date' => now()->addDay()->format('Y-m-d'),
            ]);
            
            // Créer les shipments
            $shipmentsCreated = 0;
            foreach ($orders as $order) {
                $shipment = Shipment::create([
                    'admin_id' => $admin->id,
                    'order_id' => $order->id,
                    'pickup_id' => $pickup->id,
                    'carrier_slug' => $config->carrier_slug,
                    'status' => 'created',
                    'weight' => 1.0,
                    'value' => $order->total_price,
                    'cod_amount' => $order->total_price,
                    'nb_pieces' => 1,
                    'pickup_date' => $pickup->pickup_date,
                    'content_description' => "Test commande #{$order->id}",
                    'recipient_info' => [
                        'name' => $order->customer_name,
                        'phone' => $order->customer_phone,
                        'address' => $order->customer_address,
                    ],
                ]);
                
                $order->update(['status' => 'expédiée']);
                $shipmentsCreated++;
            }
            
            return response()->json([
                'success' => true,
                'message' => "Test pickup #{$pickup->id} créé avec {$shipmentsCreated} expéditions",
                'pickup_id' => $pickup->id,
                'shipments_created' => $shipmentsCreated,
                'orders_processed' => $orders->pluck('id'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur test pickup', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }
}