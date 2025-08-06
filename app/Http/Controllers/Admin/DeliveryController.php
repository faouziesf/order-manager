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
    // NOUVELLES MÉTHODES AJOUTÉES
    // ========================================

    /**
     * Marquer un pickup comme récupéré par le transporteur
     */
    public function markPickupAsPickedUp(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $pickup->markAsPickedUp();
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickup->id} marqué comme récupéré",
                'pickup' => $pickup->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur marquage pickup récupéré', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du marquage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un pickup
     */
    public function destroyPickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        if (!$pickup->can_be_deleted) {
            return response()->json([
                'success' => false,
                'error' => 'Ce pickup ne peut pas être supprimé'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Supprimer les shipments associés et remettre les commandes en statut confirmée
            foreach ($pickup->shipments as $shipment) {
                if ($shipment->order) {
                    $shipment->order->update(['status' => 'confirmée']);
                    $shipment->order->recordHistory(
                        'shipment_cancelled',
                        "Expédition annulée suite à la suppression du pickup #{$pickup->id}",
                        ['pickup_id' => $pickup->id],
                        'expédiée',
                        'confirmée'
                    );
                }
                $shipment->delete();
            }
            
            $pickup->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickup->id} supprimé avec succès"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur suppression pickup', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter des commandes à un pickup existant
     */
    public function addOrdersToPickup(Request $request, Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        if (!$pickup->can_be_edited) {
            return response()->json([
                'success' => false,
                'error' => 'Ce pickup ne peut plus être modifié'
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1|max:20',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $admin = auth('admin')->user();
            
            // Récupérer les commandes disponibles
            $orders = Order::where('admin_id', $admin->id)
                ->whereIn('id', $request->order_ids)
                ->where('status', 'confirmée')
                ->where(function($q) {
                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                })
                ->whereDoesntHave('shipments')
                ->get();
            
            if ($orders->isEmpty()) {
                throw new \Exception('Aucune commande valide trouvée');
            }
            
            $shipmentsCreated = 0;
            
            foreach ($orders as $order) {
                // Créer l'expédition
                $shipment = Shipment::create([
                    'admin_id' => $admin->id,
                    'order_id' => $order->id,
                    'pickup_id' => $pickup->id,
                    'carrier_slug' => $pickup->carrier_slug,
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
                    "Expédition ajoutée au pickup #{$pickup->id}",
                    [
                        'pickup_id' => $pickup->id,
                        'shipment_id' => $shipment->id,
                    ],
                    'confirmée',
                    'expédiée'
                );
                
                $shipmentsCreated++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$shipmentsCreated} commande(s) ajoutée(s) au pickup",
                'data' => [
                    'shipments_created' => $shipmentsCreated,
                    'pickup_id' => $pickup->id
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur ajout commandes pickup', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirer une commande d'un pickup
     */
    public function removeOrderFromPickup(Pickup $pickup, Order $order)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        if (!$pickup->can_be_edited) {
            return response()->json([
                'success' => false,
                'error' => 'Ce pickup ne peut plus être modifié'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Trouver et supprimer l'expédition
            $shipment = Shipment::where('pickup_id', $pickup->id)
                ->where('order_id', $order->id)
                ->first();
            
            if (!$shipment) {
                throw new \Exception('Expédition non trouvée dans ce pickup');
            }
            
            // Remettre la commande en statut confirmée
            $order->update(['status' => 'confirmée']);
            
            // Enregistrer dans l'historique
            $order->recordHistory(
                'shipment_cancelled',
                "Commande retirée du pickup #{$pickup->id}",
                ['pickup_id' => $pickup->id],
                'expédiée',
                'confirmée'
            );
            
            // Supprimer l'expédition
            $shipment->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Commande retirée du pickup'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur suppression commande pickup', [
                'pickup_id' => $pickup->id,
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validation en masse des pickups
     */
    public function bulkValidatePickups(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'pickup_ids' => 'required|array|min:1|max:10',
            'pickup_ids.*' => 'integer|exists:pickups,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $pickups = Pickup::where('admin_id', $admin->id)
                ->whereIn('id', $request->pickup_ids)
                ->where('status', 'draft')
                ->get();
            
            $validated = 0;
            $errors = [];
            
            foreach ($pickups as $pickup) {
                try {
                    if ($pickup->can_be_validated) {
                        $pickup->validate();
                        $validated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Pickup #{$pickup->id}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$validated} pickup(s) validé(s)",
                'data' => [
                    'validated' => $validated,
                    'errors' => $errors
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur validation en masse', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la liste des pickups avec pagination et filtres
     */
    public function getPickupsList(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $query = Pickup::where('admin_id', $admin->id)
                ->with(['deliveryConfiguration', 'shipments.order']);
            
            // Filtres
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('carrier')) {
                $query->where('carrier_slug', $request->carrier);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', $search)
                      ->orWhereHas('deliveryConfiguration', function($subQ) use ($search) {
                          $subQ->where('integration_name', 'like', "%{$search}%");
                      });
                });
            }
            
            // Pagination
            $perPage = min($request->get('per_page', 20), 50);
            $pickups = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            // Enrichir les données
            $pickups->getCollection()->transform(function ($pickup) {
                return [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'carrier_slug' => $pickup->carrier_slug,
                    'configuration_name' => $pickup->deliveryConfiguration->integration_name ?? 'Configuration supprimée',
                    'pickup_date' => $pickup->pickup_date,
                    'created_at' => $pickup->created_at,
                    'orders_count' => $pickup->shipments->count(),
                    'total_weight' => $pickup->shipments->sum('weight'),
                    'total_pieces' => $pickup->shipments->sum('nb_pieces'),
                    'total_cod_amount' => $pickup->shipments->sum('cod_amount'),
                    'can_be_validated' => $pickup->can_be_validated,
                    'can_be_edited' => $pickup->can_be_edited,
                    'can_be_deleted' => $pickup->can_be_deleted,
                    'orders' => $pickup->shipments->map(function($shipment) {
                        $order = $shipment->order;
                        return $order ? [
                            'id' => $order->id,
                            'customer_name' => $order->customer_name,
                            'customer_phone' => $order->customer_phone,
                            'customer_address' => $order->customer_address,
                            'customer_city' => $order->customer_city,
                            'total_price' => $order->total_price,
                            'status' => $order->status,
                            'region_name' => $order->region ? $order->region->name : $order->customer_governorate
                        ] : null;
                    })->filter()
                ];
            });
            
            return response()->json([
                'success' => true,
                'pickups' => $pickups->items(),
                'pagination' => [
                    'current_page' => $pickups->currentPage(),
                    'last_page' => $pickups->lastPage(),
                    'per_page' => $pickups->perPage(),
                    'total' => $pickups->total(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur récupération pickups', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des pickups'
            ], 500);
        }
    }

    /**
     * API pour obtenir les commandes disponibles avec filtres avancés
     */
    public function getAvailableOrdersApi(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $query = Order::where('admin_id', $admin->id)
                ->where('status', 'confirmée')
                ->where(function($q) {
                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                })
                ->whereDoesntHave('shipments')
                ->with(['items.product', 'region']);
            
            // Filtres
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
            
            if ($request->filled('min_amount')) {
                $query->where('total_price', '>=', $request->min_amount);
            }
            
            if ($request->filled('max_amount')) {
                $query->where('total_price', '<=', $request->max_amount);
            }
            
            // Pagination
            $perPage = min($request->get('per_page', 20), 50);
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
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
            Log::error('Erreur API commandes disponibles', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des commandes'
            ], 500);
        }
    }

    /**
     * Exporter les pickups en CSV
     */
    public function exportPickups(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $query = Pickup::where('admin_id', $admin->id)
                ->with(['deliveryConfiguration', 'shipments.order']);
            
            // Appliquer les filtres si nécessaires
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            $pickups = $query->orderBy('created_at', 'desc')->get();
            
            $filename = 'pickups_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            
            $callback = function() use ($pickups) {
                $file = fopen('php://output', 'w');
                
                // Headers CSV
                fputcsv($file, [
                    'ID',
                    'Statut',
                    'Transporteur',
                    'Configuration',
                    'Date enlèvement',
                    'Nb commandes',
                    'Poids total (kg)',
                    'COD total (TND)',
                    'Créé le'
                ]);
                
                // Données
                foreach ($pickups as $pickup) {
                    fputcsv($file, [
                        $pickup->id,
                        $pickup->status_label,
                        $pickup->carrier_name,
                        $pickup->deliveryConfiguration->integration_name ?? 'N/A',
                        $pickup->pickup_date->format('d/m/Y'),
                        $pickup->shipments->count(),
                        $pickup->shipments->sum('weight'),
                        $pickup->shipments->sum('cod_amount'),
                        $pickup->created_at->format('d/m/Y H:i')
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Erreur export pickups', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export'
            ], 500);
        }
    }

    /**
     * Exporter les expéditions en CSV
     */
    public function exportShipments(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $query = Shipment::where('admin_id', $admin->id)->with(['order', 'pickup']);
            
            // Filtres
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('carrier')) {
                $query->where('carrier_slug', $request->carrier);
            }
            
            $shipments = $query->orderBy('created_at', 'desc')->get();
            
            $filename = 'shipments_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            
            $callback = function() use ($shipments) {
                $file = fopen('php://output', 'w');
                
                // Headers CSV
                fputcsv($file, [
                    'ID Expédition',
                    'ID Commande',
                    'Client',
                    'Téléphone',
                    'Ville',
                    'Transporteur',
                    'Statut',
                    'Tracking',
                    'Poids (kg)',
                    'COD (TND)',
                    'Date création',
                    'Date livraison'
                ]);
                
                // Données
                foreach ($shipments as $shipment) {
                    $order = $shipment->order;
                    fputcsv($file, [
                        $shipment->id,
                        $order ? $order->id : '',
                        $order ? $order->customer_name : '',
                        $order ? $order->customer_phone : '',
                        $order ? $order->customer_city : '',
                        $shipment->carrier_name,
                        $shipment->status_label,
                        $shipment->tracking_number ?: '',
                        $shipment->weight,
                        $shipment->cod_amount,
                        $shipment->created_at->format('d/m/Y H:i'),
                        $shipment->delivered_at ? $shipment->delivered_at->format('d/m/Y H:i') : ''
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Erreur export expéditions', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export'
            ], 500);
        }
    }

    /**
     * Suivi en masse des expéditions
     */
    public function bulkTrackShipments(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'shipment_ids' => 'required|array|min:1|max:20',
            'shipment_ids.*' => 'integer|exists:shipments,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $shipments = Shipment::where('admin_id', $admin->id)
                ->whereIn('id', $request->shipment_ids)
                ->with('pickup.deliveryConfiguration')
                ->get();
            
            $results = [];
            $updated = 0;
            
            foreach ($shipments as $shipment) {
                try {
                    if ($shipment->can_be_tracked && $shipment->pickup && $shipment->pickup->deliveryConfiguration) {
                        $config = $shipment->pickup->deliveryConfiguration;
                        $service = $this->shippingFactory->createFromConfig($config);
                        
                        $result = $service->trackShipment($shipment->tracking_number, $config);
                        
                        if ($result['success'] && isset($result['internal_status'])) {
                            if ($result['internal_status'] !== $shipment->status) {
                                $shipment->updateStatus(
                                    $result['internal_status'],
                                    $result['carrier_status'] ?? null,
                                    $result['carrier_status_label'] ?? null,
                                    'Mise à jour automatique en masse'
                                );
                                $updated++;
                            }
                        }
                        
                        $results[$shipment->id] = [
                            'success' => $result['success'],
                            'status' => $result['internal_status'] ?? $shipment->status,
                            'message' => $result['carrier_status_label'] ?? 'Mis à jour'
                        ];
                    } else {
                        $results[$shipment->id] = [
                            'success' => false,
                            'message' => 'Suivi non disponible pour cette expédition'
                        ];
                    }
                } catch (\Exception $e) {
                    $results[$shipment->id] = [
                        'success' => false,
                        'message' => 'Erreur: ' . $e->getMessage()
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$updated} expédition(s) mise(s) à jour",
                'data' => [
                    'updated_count' => $updated,
                    'total_processed' => count($shipments),
                    'results' => $results
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suivi en masse', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du suivi en masse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'activité récente des pickups et shipments
     */
    public function getRecentActivity()
    {
        $admin = auth('admin')->user();
        
        try {
            // Activité récente des pickups
            $recentPickups = Pickup::where('admin_id', $admin->id)
                ->with('deliveryConfiguration')
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($pickup) {
                    return [
                        'type' => 'pickup',
                        'id' => $pickup->id,
                        'status' => $pickup->status,
                        'carrier_name' => $pickup->carrier_name,
                        'updated_at' => $pickup->updated_at,
                        'description' => "Pickup #{$pickup->id} - " . $pickup->status_label
                    ];
                });
            
            // Activité récente des shipments
            $recentShipments = Shipment::where('admin_id', $admin->id)
                ->with('order')
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($shipment) {
                    return [
                        'type' => 'shipment',
                        'id' => $shipment->id,
                        'status' => $shipment->status,
                        'carrier_name' => $shipment->carrier_name,
                        'updated_at' => $shipment->updated_at,
                        'description' => "Expédition #{$shipment->id} - " . $shipment->status_label,
                        'order_id' => $shipment->order_id
                    ];
                });
            
            // Fusionner et trier par date
            $activity = $recentPickups->concat($recentShipments)
                ->sortByDesc('updated_at')
                ->take(20)
                ->values();
            
            return response()->json([
                'success' => true,
                'activity' => $activity
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur activité récente', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de l\'activité'
            ], 500);
        }
    }

    /**
     * Dupliquer une configuration de transporteur
     */
    public function duplicateConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $duplicatedConfig = $config->replicate();
            $duplicatedConfig->integration_name = $config->integration_name . ' (Copie)';
            $duplicatedConfig->is_active = false; // Désactiver la copie par défaut
            $duplicatedConfig->save();
            
            $this->recordConfigurationHistory($duplicatedConfig, 'duplicated', 
                "Configuration dupliquée depuis #{$config->id}");
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration dupliquée avec succès',
                'config' => $duplicatedConfig
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur duplication configuration', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la duplication: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un manifeste pour un pickup
     */
    public function generatePickupManifest(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $pickup->load(['shipments.order', 'deliveryConfiguration']);
            
            $html = view('admin.delivery.manifests.pickup', compact('pickup'))->render();
            
            return response($html)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            Log::error('Erreur génération manifeste', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération du manifeste'
            ], 500);
        }
    }

    /**
     * Sauvegarder les préférences utilisateur
     */
    public function saveUserPreferences(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'preferences.default_carrier' => 'nullable|string',
            'preferences.items_per_page' => 'nullable|integer|min:10|max:100',
            'preferences.auto_refresh' => 'nullable|boolean',
            'preferences.notifications' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Sauvegarder dans les settings ou une table dédiée
            $currentSettings = $admin->settings ?? [];
            $currentSettings['delivery_preferences'] = $request->preferences;
            
            $admin->update(['settings' => $currentSettings]);
            
            return response()->json([
                'success' => true,
                'message' => 'Préférences sauvegardées'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde préférences', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la sauvegarde'
            ], 500);
        }
    }

    /**
     * Récupérer les préférences utilisateur
     */
    public function getUserPreferences()
    {
        $admin = auth('admin')->user();
        
        $defaultPreferences = [
            'default_carrier' => 'jax_delivery',
            'items_per_page' => 20,
            'auto_refresh' => true,
            'notifications' => [
                'pickup_validated' => true,
                'shipment_delivered' => true,
                'delivery_failed' => true,
            ]
        ];
        
        $preferences = $admin->settings['delivery_preferences'] ?? $defaultPreferences;
        
        return response()->json([
            'success' => true,
            'preferences' => $preferences
        ]);
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

    /**
     * Obtenir le nom d'un transporteur
     */
    protected function getCarrierName($carrierSlug)
    {
        $carriers = config('carriers');
        return $carriers[$carrierSlug]['name'] ?? ucfirst(str_replace('_', ' ', $carrierSlug));
    }

    /**
     * Rafraîchir le statut d'un pickup
     */
    public function refreshPickupStatus(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            // Si le pickup est validé, on peut essayer de récupérer le statut depuis l'API
            if ($pickup->status === 'validated') {
                // TODO: Implémenter la récupération du statut depuis l'API transporteur
                // Pour l'instant, on retourne juste les données actuelles
            }
            
            return response()->json([
                'success' => true,
                'pickup' => [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'last_updated' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur rafraîchissement pickup', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du rafraîchissement: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Générer une preuve de livraison
     */
    public function generateDeliveryProof(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        if ($shipment->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'error' => 'Cette expédition n\'est pas encore livrée'
            ], 400);
        }
        
        try {
            $shipment->load(['order', 'pickup.deliveryConfiguration']);
            
            $proofData = [
                'shipment' => $shipment,
                'order' => $shipment->order,
                'carrier_name' => $shipment->carrier_name,
                'delivered_at' => $shipment->delivered_at,
                'tracking_number' => $shipment->tracking_number,
                'generated_at' => now(),
            ];
            
            // Générer le HTML de la preuve
            $html = view('admin.delivery.proofs.delivery', $proofData)->render();
            
            return response($html)->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            Log::error('Erreur génération preuve livraison', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération de la preuve'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'un transporteur spécifique
     */
    public function getCarrierStats($carrierSlug)
    {
        $admin = auth('admin')->user();
        
        try {
            // Configurations
            $configs = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('carrier_slug', $carrierSlug)
                ->get();
            
            // Pickups
            $pickups = Pickup::where('admin_id', $admin->id)
                ->where('carrier_slug', $carrierSlug);
            
            // Expéditions
            $shipments = Shipment::where('admin_id', $admin->id)
                ->where('carrier_slug', $carrierSlug);
            
            // Statistiques par période
            $last30Days = now()->subDays(30);
            $last7Days = now()->subDays(7);
            
            $stats = [
                'carrier_info' => [
                    'slug' => $carrierSlug,
                    'name' => $this->getCarrierName($carrierSlug),
                ],
                'configurations' => [
                    'total' => $configs->count(),
                    'active' => $configs->where('is_active', true)->count(),
                    'inactive' => $configs->where('is_active', false)->count(),
                ],
                'pickups' => [
                    'total' => $pickups->count(),
                    'draft' => $pickups->where('status', 'draft')->count(),
                    'validated' => $pickups->where('status', 'validated')->count(),
                    'picked_up' => $pickups->where('status', 'picked_up')->count(),
                    'last_30_days' => $pickups->where('created_at', '>=', $last30Days)->count(),
                    'last_7_days' => $pickups->where('created_at', '>=', $last7Days)->count(),
                ],
                'shipments' => [
                    'total' => $shipments->count(),
                    'active' => $shipments->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])->count(),
                    'delivered' => $shipments->where('status', 'delivered')->count(),
                    'with_problems' => $shipments->whereIn('status', ['in_return', 'anomaly'])->count(),
                    'last_30_days' => $shipments->where('created_at', '>=', $last30Days)->count(),
                    'last_7_days' => $shipments->where('created_at', '>=', $last7Days)->count(),
                ],
                'financial' => [
                    'total_cod_amount' => $shipments->sum('cod_amount'),
                    'total_weight' => $shipments->sum('weight'),
                    'average_cod' => $shipments->avg('cod_amount'),
                    'average_weight' => $shipments->avg('weight'),
                ],
                'performance' => [
                    'delivery_rate' => $this->calculateDeliveryRate($carrierSlug, $admin->id),
                    'average_delivery_time' => $this->calculateAverageDeliveryTime($carrierSlug, $admin->id),
                    'problem_rate' => $this->calculateProblemRate($carrierSlug, $admin->id),
                ]
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur statistiques transporteur', [
                'carrier_slug' => $carrierSlug,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Calculer le taux de livraison pour un transporteur
     */
    protected function calculateDeliveryRate($carrierSlug, $adminId)
    {
        $totalShipments = Shipment::where('admin_id', $adminId)
            ->where('carrier_slug', $carrierSlug)
            ->whereIn('status', ['delivered', 'in_return', 'anomaly'])
            ->count();
        
        if ($totalShipments === 0) return 0;
        
        $deliveredShipments = Shipment::where('admin_id', $adminId)
            ->where('carrier_slug', $carrierSlug)
            ->where('status', 'delivered')
            ->count();
        
        return round(($deliveredShipments / $totalShipments) * 100, 2);
    }

    /**
     * Calculer le temps moyen de livraison
     */
    protected function calculateAverageDeliveryTime($carrierSlug, $adminId)
    {
        $deliveredShipments = Shipment::where('admin_id', $adminId)
            ->where('carrier_slug', $carrierSlug)
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->get();
        
        if ($deliveredShipments->isEmpty()) return null;
        
        $totalHours = 0;
        $count = 0;
        
        foreach ($deliveredShipments as $shipment) {
            if ($shipment->delivered_at && $shipment->created_at) {
                $hours = $shipment->created_at->diffInHours($shipment->delivered_at);
                $totalHours += $hours;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalHours / $count, 1) : null;
    }

    /**
     * Calculer le taux de problèmes pour un transporteur
     */
    protected function calculateProblemRate($carrierSlug, $adminId)
    {
        $totalShipments = Shipment::where('admin_id', $adminId)
            ->where('carrier_slug', $carrierSlug)
            ->count();
        
        if ($totalShipments === 0) return 0;
        
        $problemShipments = Shipment::where('admin_id', $adminId)
            ->where('carrier_slug', $carrierSlug)
            ->whereIn('status', ['in_return', 'anomaly'])
            ->count();
        
        return round(($problemShipments / $totalShipments) * 100, 2);
    }

    /**
     * Générer des étiquettes d'expédition (si supporté)
     */
    public function generateShippingLabel(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $config = $shipment->pickup->deliveryConfiguration;
            $service = $this->shippingFactory->createFromConfig($config);
            
            if (!$service->supportsFeature('label_generation')) {
                return response()->json([
                    'success' => false,
                    'error' => 'La génération d\'étiquettes n\'est pas supportée par ce transporteur'
                ], 400);
            }
            
            $result = $service->generateShipmentLabel($shipment->tracking_number, $config);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération étiquette', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération de l\'étiquette'
            ], 500);
        }
    }

    /**
     * Générer des étiquettes en masse
     */
    public function generateBulkLabels(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'shipment_ids' => 'required|array|min:1|max:20',
            'shipment_ids.*' => 'integer|exists:shipments,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $shipments = Shipment::where('admin_id', $admin->id)
                ->whereIn('id', $request->shipment_ids)
                ->with('pickup.deliveryConfiguration')
                ->get();
            
            $labels = [];
            $errors = [];
            
            foreach ($shipments as $shipment) {
                try {
                    $config = $shipment->pickup->deliveryConfiguration;
                    $service = $this->shippingFactory->createFromConfig($config);
                    
                    if ($service->supportsFeature('label_generation')) {
                        $result = $service->generateShipmentLabel($shipment->tracking_number, $config);
                        if ($result['success']) {
                            $labels[$shipment->id] = $result;
                        } else {
                            $errors[$shipment->id] = $result['error'];
                        }
                    } else {
                        $errors[$shipment->id] = 'Génération d\'étiquettes non supportée';
                    }
                } catch (\Exception $e) {
                    $errors[$shipment->id] = $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'errors' => $errors,
                'generated_count' => count($labels)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération étiquettes en masse', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération des étiquettes'
            ], 500);
        }
    }

    /**
     * Calculer les frais de livraison
     */
    public function calculateShippingCost(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'carrier_slug' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'governorate' => 'required|string',
            'cod_amount' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $config = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('carrier_slug', $request->carrier_slug)
                ->where('is_active', true)
                ->first();
            
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'error' => 'Configuration de transporteur non trouvée'
                ], 404);
            }
            
            $service = $this->shippingFactory->createFromConfig($config);
            
            if (!$service->supportsFeature('cost_calculation')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Le calcul des frais n\'est pas supporté par ce transporteur'
                ], 400);
            }
            
            // Créer un ordre fictif pour le calcul
            $mockOrder = new Order([
                'customer_governorate' => $request->governorate,
                'total_price' => $request->cod_amount,
            ]);
            
            $result = $service->calculateShippingCost($mockOrder, $config);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Erreur calcul frais livraison', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du calcul des frais'
            ], 500);
        }
    }

    /**
     * Comparer les coûts entre transporteurs
     */
    public function compareCarrierCosts(Request $request)
    {
        $admin = auth('admin')->user();
        
        $validator = Validator::make($request->all(), [
            'carriers' => 'required|array|min:2',
            'carriers.*' => 'string',
            'weight' => 'required|numeric|min:0.1',
            'governorate' => 'required|string',
            'cod_amount' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $comparisons = [];
            
            foreach ($request->carriers as $carrierSlug) {
                $config = DeliveryConfiguration::where('admin_id', $admin->id)
                    ->where('carrier_slug', $carrierSlug)
                    ->where('is_active', true)
                    ->first();
                
                if ($config) {
                    try {
                        $service = $this->shippingFactory->createFromConfig($config);
                        
                        if ($service->supportsFeature('cost_calculation')) {
                            $mockOrder = new Order([
                                'customer_governorate' => $request->governorate,
                                'total_price' => $request->cod_amount,
                            ]);
                            
                            $result = $service->calculateShippingCost($mockOrder, $config);
                            $comparisons[$carrierSlug] = $result;
                        } else {
                            $comparisons[$carrierSlug] = [
                                'success' => false,
                                'error' => 'Calcul non supporté'
                            ];
                        }
                    } catch (\Exception $e) {
                        $comparisons[$carrierSlug] = [
                            'success' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                } else {
                    $comparisons[$carrierSlug] = [
                        'success' => false,
                        'error' => 'Configuration non trouvée'
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'comparisons' => $comparisons
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur comparaison transporteurs', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la comparaison'
            ], 500);
        }
    }

    /**
     * Nettoyer les données orphelines
     */
    public function cleanupOrphanedData()
    {
        $admin = auth('admin')->user();
        
        try {
            DB::beginTransaction();
            
            $cleaned = [
                'shipments' => 0,
                'pickups' => 0,
                'orders_restored' => 0
            ];
            
            // Nettoyer les shipments sans pickup
            $orphanedShipments = Shipment::where('admin_id', $admin->id)
                ->whereNotNull('pickup_id')
                ->whereDoesntHave('pickup')
                ->get();
            
            foreach ($orphanedShipments as $shipment) {
                if ($shipment->order) {
                    $shipment->order->update(['status' => 'confirmée']);
                    $cleaned['orders_restored']++;
                }
                $shipment->delete();
                $cleaned['shipments']++;
            }
            
            // Nettoyer les pickups sans configuration
            $orphanedPickups = Pickup::where('admin_id', $admin->id)
                ->whereDoesntHave('deliveryConfiguration')
                ->get();
            
            foreach ($orphanedPickups as $pickup) {
                foreach ($pickup->shipments as $shipment) {
                    if ($shipment->order) {
                        $shipment->order->update(['status' => 'confirmée']);
                        $cleaned['orders_restored']++;
                    }
                    $shipment->delete();
                    $cleaned['shipments']++;
                }
                $pickup->delete();
                $cleaned['pickups']++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Nettoyage terminé',
                'cleaned' => $cleaned
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur nettoyage données', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du nettoyage'
            ], 500);
        }
    }

    /**
     * Synchronisation des statuts avec les transporteurs
     */
    public function syncAllStatusesWithCarriers()
    {
        $admin = auth('admin')->user();
        
        try {
            $activeShipments = Shipment::where('admin_id', $admin->id)
                ->whereIn('status', ['created', 'validated', 'picked_up_by_carrier', 'in_transit'])
                ->with('pickup.deliveryConfiguration')
                ->get();
            
            $synced = 0;
            $errors = 0;
            
            foreach ($activeShipments as $shipment) {
                try {
                    if ($shipment->can_be_tracked && $shipment->pickup && $shipment->pickup->deliveryConfiguration) {
                        $config = $shipment->pickup->deliveryConfiguration;
                        $service = $this->shippingFactory->createFromConfig($config);
                        
                        $result = $service->trackShipment($shipment->tracking_number, $config);
                        
                        if ($result['success'] && isset($result['internal_status'])) {
                            if ($result['internal_status'] !== $shipment->status) {
                                $shipment->updateStatus(
                                    $result['internal_status'],
                                    $result['carrier_status'] ?? null,
                                    $result['carrier_status_label'] ?? null,
                                    'Synchronisation automatique'
                                );
                                $synced++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Erreur sync shipment', [
                        'shipment_id' => $shipment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Synchronisation terminée: {$synced} mis à jour, {$errors} erreurs",
                'data' => [
                    'total_processed' => $activeShipments->count(),
                    'synced' => $synced,
                    'errors' => $errors
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur synchronisation globale', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la synchronisation'
            ], 500);
        }
    }

    /**
     * Webhooks pour JAX Delivery
     */
    public function webhookJaxDelivery(Request $request)
    {
        try {
            // Valider les données du webhook
            $data = $request->all();
            
            Log::info('Webhook JAX Delivery reçu', ['data' => $data]);
            
            // TODO: Implémenter le traitement du webhook JAX
            // Pour l'instant, juste logger et retourner OK
            
            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            Log::error('Erreur webhook JAX Delivery', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Webhooks pour Mes Colis Express
     */
    public function webhookMesColis(Request $request)
    {
        try {
            // Valider les données du webhook
            $data = $request->all();
            
            Log::info('Webhook Mes Colis reçu', ['data' => $data]);
            
            // TODO: Implémenter le traitement du webhook Mes Colis
            // Pour l'instant, juste logger et retourner OK
            
            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            Log::error('Erreur webhook Mes Colis', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }
}