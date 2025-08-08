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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * Configuration des transporteurs depuis le fichier config
     * @var array
     */
    protected $carriers;
    
    /**
     * Factory pour les services de transporteurs
     * @var ShippingServiceFactory
     */
    protected $shippingFactory;
    
    public function __construct(ShippingServiceFactory $shippingFactory)
    {
        $this->middleware('auth:admin');
        $this->carriers = config('carriers', []);
        $this->shippingFactory = $shippingFactory;
    }

    // ========================================
    // PAGE PRINCIPALE MULTI-TRANSPORTEURS
    // ========================================

    /**
     * Interface principale de sélection des transporteurs - VERSION CORRIGÉE ET ROBUSTIFIÉE
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $admin = auth('admin')->user();
        
        Log::info('🏠 [DELIVERY INDEX] Accès page principale', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name
        ]);
        
        // Vérifier si le fichier de configuration des transporteurs existe et n'est pas vide
        if (empty($this->carriers)) {
            Log::error('❌ [DELIVERY INDEX] Fichier de configuration des transporteurs (config/carriers.php) manquant ou vide.');
            return redirect()->back()->with('error', 'Fichier de configuration des transporteurs manquant ou vide.');
        }
        
        try {
            // Récupérer toutes les configurations de l'admin en une seule fois
            $configurations = DeliveryConfiguration::where('admin_id', $admin->id)
                ->get()
                ->groupBy('carrier_slug');
            
            $carriersData = [];
            
            // Itérer sur la configuration pour garantir que tous les transporteurs sont affichés
            foreach ($this->carriers as $slug => $carrierConfig) {
                // Ignorer les clés de configuration système
                if ($slug === 'system' || $slug === 'history_actions') {
                    continue;
                }
                
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
                    'stats' => $this->getCarrierStats($admin->id, $slug), // Méthode corrigée avec try/catch
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

            Log::info('✅ [DELIVERY INDEX] Données chargées avec succès', [
                'admin_id' => $admin->id,
                'carriers_count' => count($carriersData),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [DELIVERY INDEX] Erreur critique lors du chargement de la page', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // En cas d'erreur (ex: connexion DB), on prépare des données par défaut
            // pour éviter que la vue ne plante. C'est la correction la plus importante.
            $carriersData = [];
            foreach ($this->carriers as $slug => $carrierConfig) {
                if ($slug === 'system' || $slug === 'history_actions') continue;
                
                $carriersData[$slug] = [
                    'config' => $carrierConfig,
                    'slug' => $slug,
                    'configurations' => collect(),
                    'active_configurations' => collect(),
                    'is_configured' => false,
                    'is_active' => false,
                    'status' => 'non_configuré',
                    'stats' => [ // Assurer que la structure 'stats' est toujours présente
                        'configurations' => 0,
                        'pickups' => 0,
                        'shipments' => 0,
                    ],
                ];
            }
            
            $generalStats = [
                'total_configurations' => 0,
                'active_configurations' => 0,
                'total_pickups' => 0,
                'pending_pickups' => 0,
                'total_shipments' => 0,
                'active_shipments' => 0,
            ];
            
            // On retourne la vue avec les données par défaut et un message d'erreur
            return view('admin.delivery.index', compact('carriersData', 'generalStats'))
                ->with('error', 'Une erreur est survenue lors du chargement des données de livraison.');
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
            Log::error('❌ [DELIVERY STATS] Erreur récupération statistiques', [
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
            ], 500);
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
        
        Log::info('⚙️ [DELIVERY CONFIG] Accès page configuration', ['admin_id' => $admin->id]);
        
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
        
        Log::info('📝 [DELIVERY CONFIG] Formulaire création', [
            'carrier_slug' => $carrierSlug
        ]);
        
        // Vérifier que le transporteur existe
        if (!isset($this->carriers[$carrierSlug])) {
            Log::warning('⚠️ [DELIVERY CONFIG] Transporteur non supporté', ['carrier' => $carrierSlug]);
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
        
        Log::info('💾 [DELIVERY CONFIG] Sauvegarde configuration', [
            'admin_id' => $admin->id,
            'carrier_slug' => $carrierSlug,
            'integration_name' => $request->input('integration_name')
        ]);
        
        // Validation selon le transporteur avec support des longs tokens
        $rules = $this->getValidationRules($carrierSlug);
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            Log::warning('⚠️ [DELIVERY CONFIG] Validation échouée', [
                'errors' => $validator->errors()->toArray()
            ]);
            
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
                    Log::warning('⚠️ [DELIVERY CONFIG] Token JAX non-JWT détecté', [
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
            
            Log::info('✅ [DELIVERY CONFIG] Configuration créée avec succès', [
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
            Log::error('❌ [DELIVERY CONFIG] Erreur création configuration', [
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

    // ========================================
    // PRÉPARATION D'ENLÈVEMENT
    // ========================================

    /**
     * Page de préparation des enlèvements
     */
    public function preparation()
    {
        $admin = auth('admin')->user();
        
        Log::info('📦 [DELIVERY PREP] Accès page préparation', ['admin_id' => $admin->id]);
        
        // Récupérer les configurations actives
        $activeConfigurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->get();
        
        if ($activeConfigurations->isEmpty()) {
            Log::warning('⚠️ [DELIVERY PREP] Aucune configuration active', ['admin_id' => $admin->id]);
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
        
        Log::info('📋 [DELIVERY ORDERS] Récupération commandes disponibles', [
            'admin_id' => $admin->id,
            'filters' => $request->only(['search', 'governorate'])
        ]);
        
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
            
            Log::info('✅ [DELIVERY ORDERS] Commandes récupérées', [
                'count' => $orders->count(),
                'total' => $orders->total()
            ]);
            
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
            Log::error('❌ [DELIVERY ORDERS] Erreur récupération commandes', [
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
        
        Log::info('🆕 [PICKUP CREATE] Début création pickup', [
            'admin_id' => $admin->id,
            'request_data' => $request->only(['delivery_configuration_id', 'order_ids', 'pickup_date'])
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
                Log::warning('⚠️ [PICKUP CREATE] Validation échouée', [
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
            
            Log::info('✅ [PICKUP CREATE] Configuration trouvée', [
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
            
            Log::info('📋 [PICKUP CREATE] Commandes récupérées', [
                'orders_requested' => count($request->order_ids),
                'orders_found' => $orders->count(),
                'order_ids_found' => $orders->pluck('id')->toArray()
            ]);
            
            if ($orders->isEmpty()) {
                throw new \Exception('Aucune commande valide trouvée');
            }
            
            if ($orders->count() !== count($request->order_ids)) {
                $missing = array_diff($request->order_ids, $orders->pluck('id')->toArray());
                Log::warning('⚠️ [PICKUP CREATE] Commandes manquantes', ['missing_ids' => $missing]);
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
            
            Log::info('📦 [PICKUP CREATE] Pickup créé', [
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
                    
                    Log::info('📨 [PICKUP CREATE] Shipment créé', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $order->id,
                        'pickup_id' => $pickup->id
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('❌ [PICKUP CREATE] Erreur création shipment', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue avec les autres commandes
                    continue;
                }
            }
            
            DB::commit();
            
            Log::info('🎉 [PICKUP CREATE] Pickup créé avec succès', [
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
            
            Log::error('❌ [PICKUP CREATE] Erreur création pickup', [
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
    // GESTION DES PICKUPS - SECTION PRINCIPALE
    // ========================================

    /**
     * Page de gestion des pickups
     */
    public function pickups()
    {
        $admin = auth('admin')->user();
        
        Log::info('📦 [PICKUPS PAGE] Accès page pickups', ['admin_id' => $admin->id]);
        
        return view('admin.delivery.pickups');
    }

    /**
     * API CRITIQUE - Liste des pickups avec pagination et filtres
     */
    public function getPickupsList(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Logs détaillés pour debug
        Log::info('🚀 [PICKUPS API] Début de getPickupsList', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'request_params' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);
        
        try {
            // Vérification que l'admin est bien authentifié
            if (!$admin) {
                Log::error('❌ [PICKUPS API] Admin non authentifié');
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }
            
            // Vérification de l'existence de la table pickups
            if (!Schema::hasTable('pickups')) {
                Log::error('❌ [PICKUPS API] Table pickups inexistante');
                return response()->json([
                    'success' => false,
                    'error' => 'Table pickups non trouvée dans la base de données'
                ], 500);
            }
            
            // Query de base avec relations
            $query = Pickup::where('admin_id', $admin->id);
            
            // Tentative de chargement des relations avec gestion d'erreur
            try {
                $query->with(['deliveryConfiguration', 'shipments.order']);
                Log::info('✅ [PICKUPS API] Relations ajoutées à la requête');
            } catch (\Exception $relationError) {
                Log::warning('⚠️ [PICKUPS API] Erreur chargement relations', [
                    'error' => $relationError->getMessage()
                ]);
                // Continuer sans les relations si problème
            }
            
            // Application des filtres
            if ($request->filled('search')) {
                $search = $request->search;
                Log::info('🔍 [PICKUPS API] Filtre recherche appliqué', ['search' => $search]);
                
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%");
                    
                    // Recherche dans la configuration seulement si la relation existe
                    try {
                        $q->orWhereHas('deliveryConfiguration', function($subQ) use ($search) {
                            $subQ->where('integration_name', 'like', "%{$search}%");
                        });
                    } catch (\Exception $e) {
                        Log::warning('⚠️ [PICKUPS API] Recherche dans deliveryConfiguration ignorée', ['error' => $e->getMessage()]);
                    }
                });
            }
            
            if ($request->filled('status')) {
                $status = $request->status;
                Log::info('📊 [PICKUPS API] Filtre statut appliqué', ['status' => $status]);
                $query->where('status', $status);
            }
            
            if ($request->filled('carrier')) {
                $carrier = $request->carrier;
                Log::info('🚛 [PICKUPS API] Filtre transporteur appliqué', ['carrier' => $carrier]);
                $query->where('carrier_slug', $carrier);
            }
            
            // Test si c'est une requête de test
            if ($request->filled('test')) {
                Log::info('🧪 [PICKUPS API] Mode test détecté');
                $count = $query->count();
                return response()->json([
                    'success' => true,
                    'message' => 'Test API réussi',
                    'count' => $count,
                    'admin_id' => $admin->id,
                    'timestamp' => now()->toISOString()
                ]);
            }
            
            // Récupération avec limite de sécurité
            $perPage = min($request->get('per_page', 20), 100); // Max 100 pour éviter les surcharges
            
            Log::info('📄 [PICKUPS API] Récupération avec pagination', [
                'per_page' => $perPage,
                'order_by' => 'created_at DESC'
            ]);
            
            $pickups = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage);
            
            Log::info('📊 [PICKUPS API] Pickups récupérés', [
                'total_pickups' => $pickups->total(),
                'current_page_count' => $pickups->count(),
                'current_page' => $pickups->currentPage(),
                'last_page' => $pickups->lastPage()
            ]);
            
            // Transformation des données avec gestion d'erreur pour chaque pickup
            $pickupsData = $pickups->getCollection()->map(function ($pickup) {
                try {
                    // Données de base du pickup (toujours disponibles)
                    $pickupData = [
                        'id' => $pickup->id,
                        'status' => $pickup->status,
                        'carrier_slug' => $pickup->carrier_slug,
                        'pickup_date' => $pickup->pickup_date,
                        'created_at' => $pickup->created_at->toISOString(),
                        'can_be_validated' => $pickup->can_be_validated ?? false,
                        'can_be_edited' => $pickup->can_be_edited ?? false,
                        'can_be_deleted' => $pickup->can_be_deleted ?? false,
                    ];
                    
                    // Configuration (avec fallback si relation échoue)
                    try {
                        $pickupData['configuration_name'] = $pickup->deliveryConfiguration->integration_name ?? 'Configuration inconnue';
                    } catch (\Exception $e) {
                        $pickupData['configuration_name'] = 'Configuration non disponible';
                        Log::warning('⚠️ [PICKUPS API] Relation deliveryConfiguration échouée pour pickup ' . $pickup->id);
                    }
                    
                    // Shipments et totaux (avec fallback si relation échoue)
                    try {
                        $shipments = $pickup->shipments ?? collect();
                        $pickupData['orders_count'] = $shipments->count();
                        $pickupData['total_weight'] = round($shipments->sum('weight') ?: 0, 2);
                        $pickupData['total_pieces'] = $shipments->sum('nb_pieces') ?: 0;
                        $pickupData['total_cod_amount'] = round($shipments->sum('cod_amount') ?: 0, 3);
                        
                        // Orders (avec gestion d'erreur pour chaque order)
                        $pickupData['orders'] = $shipments->map(function($shipment) {
                            try {
                                $order = $shipment->order ?? null;
                                return $order ? [
                                    'id' => $order->id,
                                    'customer_name' => $order->customer_name ?? 'Client inconnu',
                                    'customer_phone' => $order->customer_phone ?? '',
                                    'customer_address' => $order->customer_address ?? '',
                                    'customer_city' => $order->customer_city ?? '',
                                    'total_price' => $order->total_price ?? 0,
                                    'status' => $order->status ?? 'inconnu',
                                    'region_name' => $order->region->name ?? $order->customer_governorate ?? 'Région inconnue'
                                ] : null;
                            } catch (\Exception $e) {
                                Log::warning('⚠️ [PICKUPS API] Erreur traitement order dans shipment', ['error' => $e->getMessage()]);
                                return null;
                            }
                        })->filter()->values();
                        
                    } catch (\Exception $e) {
                        Log::warning('⚠️ [PICKUPS API] Relation shipments échouée pour pickup ' . $pickup->id, ['error' => $e->getMessage()]);
                        // Valeurs par défaut si les relations échouent
                        $pickupData['orders_count'] = 0;
                        $pickupData['total_weight'] = 0;
                        $pickupData['total_pieces'] = 0;
                        $pickupData['total_cod_amount'] = 0;
                        $pickupData['orders'] = [];
                    }
                    
                    return $pickupData;
                    
                } catch (\Exception $e) {
                    Log::error('❌ [PICKUPS API] Erreur transformation pickup ' . $pickup->id, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Retourner les données minimales en cas d'erreur
                    return [
                        'id' => $pickup->id,
                        'status' => $pickup->status ?? 'unknown',
                        'carrier_slug' => $pickup->carrier_slug ?? 'unknown',
                        'configuration_name' => 'Erreur chargement',
                        'pickup_date' => $pickup->pickup_date,
                        'created_at' => $pickup->created_at->toISOString(),
                        'orders_count' => 0,
                        'total_weight' => 0,
                        'total_pieces' => 0,
                        'total_cod_amount' => 0,
                        'orders' => [],
                        'can_be_validated' => false,
                        'can_be_edited' => false,
                        'can_be_deleted' => false,
                        'error' => 'Erreur lors du chargement des données'
                    ];
                }
            });
            
            // Construction de la réponse
            $response = [
                'success' => true,
                'pickups' => $pickupsData,
                'pagination' => [
                    'current_page' => $pickups->currentPage(),
                    'last_page' => $pickups->lastPage(),
                    'per_page' => $pickups->perPage(),
                    'total' => $pickups->total(),
                    'from' => $pickups->firstItem(),
                    'to' => $pickups->lastItem(),
                ],
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'query_time' => now()->toISOString(),
                    'filters_applied' => [
                        'search' => $request->search ?? null,
                        'status' => $request->status ?? null,
                        'carrier' => $request->carrier ?? null,
                    ],
                    'relations_loaded' => ['deliveryConfiguration', 'shipments.order'],
                ]
            ];
            
            Log::info('✅ [PICKUPS API] Réponse construite avec succès', [
                'pickups_returned' => $pickupsData->count(),
                'response_size_kb' => round(strlen(json_encode($response)) / 1024, 2)
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUPS API] Erreur fatale dans getPickupsList', [
                'admin_id' => $admin->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_params' => $request->all()
            ]);
            
            // Réponse d'erreur détaillée pour le debug
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des pickups',
                'message' => $e->getMessage(),
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'timestamp' => now()->toISOString(),
                    'request_params' => $request->all(),
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ] : 'Debug mode désactivé'
                ]
            ], 500);
        }
    }

    /**
     * Afficher les détails d'un pickup
     */
    public function showPickup(Pickup $pickup)
    {
        // Vérification d'autorisation simplifiée
        if ($pickup->admin_id !== auth('admin')->id()) {
            Log::warning('⚠️ [PICKUP SHOW] Accès non autorisé', [
                'pickup_id' => $pickup->id,
                'admin_id' => auth('admin')->id(),
                'pickup_admin_id' => $pickup->admin_id
            ]);
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('👁️ [PICKUP SHOW] Affichage détails pickup', ['pickup_id' => $pickup->id]);
        
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
        
        Log::info('✅ [PICKUP VALIDATE] Validation pickup', ['pickup_id' => $pickup->id]);
        
        try {
            // Valider le pickup
            $result = $pickup->validate();
            
            if ($result) {
                Log::info('🎉 [PICKUP VALIDATE] Pickup validé avec succès', ['pickup_id' => $pickup->id]);
                return response()->json([
                    'success' => true,
                    'message' => "Pickup #{$pickup->id} validé avec succès"
                ]);
            } else {
                Log::error('❌ [PICKUP VALIDATE] Impossible de valider', ['pickup_id' => $pickup->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Impossible de valider le pickup'
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP VALIDATE] Erreur validation pickup', [
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
     * Marquer un pickup comme récupéré par le transporteur
     */
    public function markPickupAsPickedUp(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🚛 [PICKUP PICKED UP] Marquage récupération', ['pickup_id' => $pickup->id]);
        
        try {
            $pickup->markAsPickedUp();
            
            Log::info('🎉 [PICKUP PICKED UP] Pickup marqué récupéré', ['pickup_id' => $pickup->id]);
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickup->id} marqué comme récupéré",
                'pickup' => $pickup->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP PICKED UP] Erreur marquage pickup récupéré', [
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
        
        Log::info('🗑️ [PICKUP DELETE] Suppression pickup', ['pickup_id' => $pickup->id]);
        
        if (!$pickup->can_be_deleted) {
            Log::warning('⚠️ [PICKUP DELETE] Pickup ne peut pas être supprimé', [
                'pickup_id' => $pickup->id,
                'status' => $pickup->status
            ]);
            
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
            
            $pickupId = $pickup->id;
            $pickup->delete();
            
            DB::commit();
            
            Log::info('🎉 [PICKUP DELETE] Pickup supprimé avec succès', ['pickup_id' => $pickupId]);
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickupId} supprimé avec succès"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ [PICKUP DELETE] Erreur suppression pickup', [
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
     * Validation en masse des pickups
     */
    public function bulkValidatePickups(Request $request)
    {
        $admin = auth('admin')->user();
        
        Log::info('✅ [PICKUP BULK VALIDATE] Validation en masse', [
            'admin_id' => $admin->id,
            'pickup_ids' => $request->pickup_ids
        ]);
        
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
            
            Log::info('🎉 [PICKUP BULK VALIDATE] Validation terminée', [
                'validated' => $validated,
                'errors_count' => count($errors)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "{$validated} pickup(s) validé(s)",
                'data' => [
                    'validated' => $validated,
                    'errors' => $errors
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP BULK VALIDATE] Erreur validation en masse', [
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
     * Export des pickups en CSV
     */
    public function exportPickups(Request $request)
    {
        $admin = auth('admin')->user();
        
        Log::info('📤 [PICKUP EXPORT] Export des pickups', ['admin_id' => $admin->id]);
        
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
            
            Log::info('✅ [PICKUP EXPORT] Export généré', ['filename' => $filename]);
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP EXPORT] Erreur export pickups', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export'
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
        
        Log::info('🔧 [TEST SYSTEM] Début diagnostic système', ['admin_id' => $admin->id]);
        
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
                    'pickups_list' => route('admin.delivery.pickups.list'),
                ],
                
                'config_check' => [
                    'carriers_config_exists' => config('carriers') !== null,
                    'carriers_available' => config('carriers') ? array_keys(config('carriers')) : [],
                    'app_debug' => config('app.debug'),
                    'app_env' => config('app.env'),
                ],
                
                'tables_check' => [
                    'pickups' => Schema::hasTable('pickups'),
                    'delivery_configurations' => Schema::hasTable('delivery_configurations'),
                    'shipments' => Schema::hasTable('shipments'),
                    'orders' => Schema::hasTable('orders'),
                ],
                
                'timestamp' => now()->toISOString(),
            ];
            
            Log::info('✅ [TEST SYSTEM] Diagnostic terminé avec succès');
            
            return response()->json($diagnostics, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            Log::error('❌ [TEST SYSTEM] Erreur diagnostic', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
        
        Log::info('🧪 [TEST CREATE PICKUP] Test création pickup', ['admin_id' => $admin->id]);
        
        try {
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
            
            Log::info('✅ [TEST CREATE PICKUP] Pickup test créé', [
                'pickup_id' => $pickup->id,
                'shipments_created' => $shipmentsCreated
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Test pickup #{$pickup->id} créé avec {$shipmentsCreated} expéditions",
                'pickup_id' => $pickup->id,
                'shipments_created' => $shipmentsCreated,
                'orders_processed' => $orders->pluck('id'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [TEST CREATE PICKUP] Erreur test pickup', [
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
            Log::warning('⚠️ [WEIGHT CALC] Erreur calcul poids', ['order_id' => $order->id, 'error' => $e->getMessage()]);
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
            Log::warning('⚠️ [CONTENT DESC] Erreur génération description', ['order_id' => $order->id, 'error' => $e->getMessage()]);
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
     * Obtenir le statut d'une configuration de transporteur.
     *
     * @param \Illuminate\Support\Collection $configurations
     * @return string
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
     * Obtenir les statistiques d'un transporteur - VERSION CORRIGÉE
     *
     * @param int $adminId
     * @param string $carrierSlug
     * @return array
     */
    protected function getCarrierStats($adminId, $carrierSlug)
    {
        try {
            // On inclut les 3 types de statistiques pour être cohérent.
            return [
                'configurations' => DeliveryConfiguration::where('admin_id', $adminId)
                    ->where('carrier_slug', $carrierSlug)
                    ->count(),
                'pickups' => Pickup::where('admin_id', $adminId)
                    ->where('carrier_slug', $carrierSlug)
                    ->count(),
                'shipments' => Shipment::where('admin_id', $adminId)
                    ->where('carrier_slug', $carrierSlug)
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::warning('⚠️ [CARRIER STATS] Erreur lors de la récupération des stats pour le transporteur', [
                'carrier' => $carrierSlug,
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
            
            // Retourner des valeurs par défaut avec toutes les clés nécessaires pour la vue
            return [
                'configurations' => 0,
                'pickups' => 0,
                'shipments' => 0,
            ];
        }
    }

    /**
     * Enregistrer dans l'historique des configurations
     */
    protected function recordConfigurationHistory($config, $action, $notes, $data = [])
    {
        Log::info("🔄 [CONFIG HISTORY] {$action}", [
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
}
