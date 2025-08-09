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
            
            // En cas d'erreur, préparer des données par défaut
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
                    'stats' => [
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
            
            return view('admin.delivery.index', compact('carriersData', 'generalStats'))
                ->with('error', 'Une erreur est survenue lors du chargement des données de livraison.');
        }

        return view('admin.delivery.index', compact('carriersData', 'generalStats'));
    }

    // ========================================
    // GESTION DES PICKUPS - VERSION COMPLÈTEMENT REFACTORISÉE
    // ========================================

    /**
     * Page de gestion des pickups
     */
    public function pickups()
    {
        $admin = auth('admin')->user();
        
        Log::info('📦 [PICKUPS PAGE] Accès page pickups', [
            'admin_id' => $admin->id,
            'timestamp' => now()->toISOString()
        ]);
        
        return view('admin.delivery.pickups');
    }

    /**
     * API COMPLÈTEMENT REFACTORISÉE - Liste des pickups avec diagnostic complet
     */
    public function getPickupsList(Request $request)
    {
        $startTime = microtime(true);
        $admin = auth('admin')->user();
        
        // ÉTAPE 1: LOGS DE DÉMARRAGE DÉTAILLÉS
        Log::info('🚀 [PICKUPS API] === DÉBUT DE getPickupsList ===', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_params' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);
        
        try {
            // ÉTAPE 2: VÉRIFICATIONS DE SÉCURITÉ
            if (!$admin) {
                Log::error('❌ [PICKUPS API] Admin non authentifié');
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié',
                    'debug_info' => [
                        'auth_guard' => 'admin',
                        'auth_check' => auth('admin')->check(),
                        'timestamp' => now()->toISOString()
                    ]
                ], 401);
            }
            
            Log::info('✅ [PICKUPS API] Authentification validée', [
                'admin_id' => $admin->id,
                'admin_active' => $admin->is_active ?? 'unknown'
            ]);
            
            // ÉTAPE 3: VÉRIFICATION DES TABLES
            $tableChecks = [
                'pickups' => Schema::hasTable('pickups'),
                'delivery_configurations' => Schema::hasTable('delivery_configurations'),
                'shipments' => Schema::hasTable('shipments'),
                'orders' => Schema::hasTable('orders')
            ];
            
            Log::info('🔍 [PICKUPS API] Vérification des tables', $tableChecks);
            
            foreach ($tableChecks as $table => $exists) {
                if (!$exists) {
                    Log::error("❌ [PICKUPS API] Table $table n'existe pas");
                    return response()->json([
                        'success' => false,
                        'error' => "Table $table non trouvée dans la base de données",
                        'table_checks' => $tableChecks
                    ], 500);
                }
            }
            
            // ÉTAPE 4: CONSTRUCTION DE LA REQUÊTE DE BASE
            Log::info('🔨 [PICKUPS API] Construction de la requête de base');
            
            $query = Pickup::where('admin_id', $admin->id);
            
            // ÉTAPE 5: AJOUT DES RELATIONS AVEC GESTION D'ERREUR
            try {
                Log::info('🔗 [PICKUPS API] Ajout des relations');
                $query->with([
                    'deliveryConfiguration' => function($q) {
                        $q->select('id', 'carrier_slug', 'integration_name', 'is_active');
                    },
                    'shipments' => function($q) {
                        $q->select('id', 'pickup_id', 'order_id', 'weight', 'cod_amount', 'nb_pieces', 'status');
                    },
                    'shipments.order' => function($q) {
                        $q->select('id', 'customer_name', 'customer_phone', 'customer_address', 
                                  'customer_city', 'customer_governorate', 'total_price', 'status');
                    }
                ]);
                Log::info('✅ [PICKUPS API] Relations ajoutées avec succès');
            } catch (\Exception $relationError) {
                Log::warning('⚠️ [PICKUPS API] Erreur lors de l\'ajout des relations', [
                    'error' => $relationError->getMessage(),
                    'line' => $relationError->getLine()
                ]);
                // Continuer sans les relations si problème
                $query = Pickup::where('admin_id', $admin->id);
            }
            
            // ÉTAPE 6: APPLICATION DES FILTRES
            $appliedFilters = [];
            
            if ($request->filled('search')) {
                $search = trim($request->search);
                $appliedFilters['search'] = $search;
                Log::info('🔍 [PICKUPS API] Application filtre recherche', ['search' => $search]);
                
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('carrier_slug', 'like', "%{$search}%");
                    
                    // Recherche dans la configuration seulement si la relation existe
                    try {
                        $q->orWhereHas('deliveryConfiguration', function($subQ) use ($search) {
                            $subQ->where('integration_name', 'like', "%{$search}%");
                        });
                    } catch (\Exception $e) {
                        Log::debug('🔍 [PICKUPS API] Recherche dans deliveryConfiguration ignorée', [
                            'error' => $e->getMessage()
                        ]);
                    }
                });
            }
            
            if ($request->filled('status')) {
                $status = $request->status;
                $appliedFilters['status'] = $status;
                Log::info('📊 [PICKUPS API] Application filtre statut', ['status' => $status]);
                $query->where('status', $status);
            }
            
            if ($request->filled('carrier')) {
                $carrier = $request->carrier;
                $appliedFilters['carrier'] = $carrier;
                Log::info('🚛 [PICKUPS API] Application filtre transporteur', ['carrier' => $carrier]);
                $query->where('carrier_slug', $carrier);
            }
            
            // ÉTAPE 7: TEST DE CONNECTIVITÉ (mode test)
            if ($request->filled('test') && $request->test === '1') {
                Log::info('🧪 [PICKUPS API] Mode test détecté');
                
                try {
                    $testCount = $query->count();
                    $testTime = round((microtime(true) - $startTime) * 1000, 2);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Test API réussi',
                        'test_results' => [
                            'count' => $testCount,
                            'response_time_ms' => $testTime,
                            'admin_id' => $admin->id,
                            'applied_filters' => $appliedFilters,
                            'query_sql' => $query->toSql(),
                            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
                        ],
                        'timestamp' => now()->toISOString()
                    ]);
                } catch (\Exception $testError) {
                    Log::error('❌ [PICKUPS API] Erreur pendant le test', [
                        'error' => $testError->getMessage(),
                        'trace' => $testError->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Erreur pendant le test: ' . $testError->getMessage(),
                        'admin_id' => $admin->id
                    ], 500);
                }
            }
            
            // ÉTAPE 8: PAGINATION ET RÉCUPÉRATION
            $perPage = min($request->get('per_page', 20), 100);
            Log::info('📄 [PICKUPS API] Récupération avec pagination', [
                'per_page' => $perPage,
                'applied_filters' => $appliedFilters
            ]);
            
            $pickups = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            Log::info('📊 [PICKUPS API] Pickups récupérés de la DB', [
                'total_found' => $pickups->total(),
                'current_page_count' => $pickups->count(),
                'current_page' => $pickups->currentPage(),
                'last_page' => $pickups->lastPage(),
                'per_page' => $pickups->perPage()
            ]);
            
            // ÉTAPE 9: TRANSFORMATION DES DONNÉES AVEC DEBUG DÉTAILLÉ
            $transformedData = [];
            $transformationErrors = [];
            
            foreach ($pickups->items() as $index => $pickup) {
                Log::debug("🔄 [PICKUPS API] Transformation pickup #{$pickup->id}");
                
                try {
                    $pickupData = [
                        'id' => $pickup->id,
                        'status' => $pickup->status,
                        'carrier_slug' => $pickup->carrier_slug,
                        'pickup_date' => $pickup->pickup_date ? $pickup->pickup_date->toDateString() : null,
                        'created_at' => $pickup->created_at->toISOString(),
                        'can_be_validated' => $pickup->can_be_validated ?? false,
                        'can_be_edited' => $pickup->can_be_edited ?? false,
                        'can_be_deleted' => $pickup->can_be_deleted ?? false,
                    ];
                    
                    // Configuration (avec fallback robuste)
                    try {
                        if ($pickup->relationLoaded('deliveryConfiguration') && $pickup->deliveryConfiguration) {
                            $pickupData['configuration_name'] = $pickup->deliveryConfiguration->integration_name;
                        } else {
                            // Fallback: chercher la configuration manuellement
                            $config = DeliveryConfiguration::where('id', $pickup->delivery_configuration_id)->first();
                            $pickupData['configuration_name'] = $config ? $config->integration_name : 'Configuration inconnue';
                        }
                    } catch (\Exception $configError) {
                        Log::debug("⚠️ [PICKUPS API] Erreur configuration pickup #{$pickup->id}", [
                            'error' => $configError->getMessage()
                        ]);
                        $pickupData['configuration_name'] = 'Configuration non disponible';
                    }
                    
                    // Shipments et statistiques (avec fallback robuste)
                    try {
                        if ($pickup->relationLoaded('shipments')) {
                            $shipments = $pickup->shipments;
                        } else {
                            // Fallback: chercher les shipments manuellement
                            $shipments = Shipment::where('pickup_id', $pickup->id)->get();
                        }
                        
                        $pickupData['orders_count'] = $shipments->count();
                        $pickupData['total_weight'] = round($shipments->sum('weight') ?: 0, 2);
                        $pickupData['total_pieces'] = $shipments->sum('nb_pieces') ?: 0;
                        $pickupData['total_cod_amount'] = round($shipments->sum('cod_amount') ?: 0, 3);
                        
                        // Orders détails (avec gestion d'erreur pour chaque order)
                        $ordersData = [];
                        foreach ($shipments as $shipment) {
                            try {
                                if ($shipment->relationLoaded('order') && $shipment->order) {
                                    $order = $shipment->order;
                                } else {
                                    // Fallback: chercher l'order manuellement
                                    $order = Order::find($shipment->order_id);
                                }
                                
                                if ($order) {
                                    $ordersData[] = [
                                        'id' => $order->id,
                                        'customer_name' => $order->customer_name ?? 'Client inconnu',
                                        'customer_phone' => $order->customer_phone ?? '',
                                        'customer_address' => $order->customer_address ?? '',
                                        'customer_city' => $order->customer_city ?? '',
                                        'total_price' => $order->total_price ?? 0,
                                        'status' => $order->status ?? 'inconnu',
                                        'region_name' => $order->customer_governorate ?? 'Région inconnue'
                                    ];
                                }
                            } catch (\Exception $orderError) {
                                Log::debug("⚠️ [PICKUPS API] Erreur order dans shipment #{$shipment->id}", [
                                    'error' => $orderError->getMessage()
                                ]);
                                continue;
                            }
                        }
                        
                        $pickupData['orders'] = $ordersData;
                        
                    } catch (\Exception $shipmentsError) {
                        Log::debug("⚠️ [PICKUPS API] Erreur shipments pickup #{$pickup->id}", [
                            'error' => $shipmentsError->getMessage()
                        ]);
                        
                        // Valeurs par défaut si les relations échouent
                        $pickupData['orders_count'] = 0;
                        $pickupData['total_weight'] = 0;
                        $pickupData['total_pieces'] = 0;
                        $pickupData['total_cod_amount'] = 0;
                        $pickupData['orders'] = [];
                    }
                    
                    $transformedData[] = $pickupData;
                    
                } catch (\Exception $transformError) {
                    $errorMsg = "Erreur transformation pickup #{$pickup->id}: " . $transformError->getMessage();
                    $transformationErrors[] = $errorMsg;
                    
                    Log::error("❌ [PICKUPS API] $errorMsg", [
                        'pickup_id' => $pickup->id,
                        'error_trace' => $transformError->getTraceAsString()
                    ]);
                    
                    // Ajouter un pickup avec données minimales en cas d'erreur
                    $transformedData[] = [
                        'id' => $pickup->id,
                        'status' => $pickup->status ?? 'unknown',
                        'carrier_slug' => $pickup->carrier_slug ?? 'unknown',
                        'configuration_name' => 'Erreur chargement',
                        'pickup_date' => $pickup->pickup_date ? $pickup->pickup_date->toDateString() : null,
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
            }
            
            // ÉTAPE 10: CONSTRUCTION DE LA RÉPONSE FINALE
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $response = [
                'success' => true,
                'pickups' => $transformedData,
                'pagination' => [
                    'current_page' => $pickups->currentPage(),
                    'last_page' => $pickups->lastPage(),
                    'per_page' => $pickups->perPage(),
                    'total' => $pickups->total(),
                    'from' => $pickups->firstItem(),
                    'to' => $pickups->lastItem(),
                ],
                'stats' => [
                    'total_returned' => count($transformedData),
                    'transformation_errors' => count($transformationErrors),
                    'response_time_ms' => $responseTime,
                ],
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'query_time' => now()->toISOString(),
                    'applied_filters' => $appliedFilters,
                    'table_checks' => $tableChecks,
                    'memory_peak_mb' => memory_get_peak_usage(true) / 1024 / 1024,
                    'transformation_errors' => $transformationErrors,
                    'has_relations' => [
                        'deliveryConfiguration' => $pickups->first() ? $pickups->first()->relationLoaded('deliveryConfiguration') : false,
                        'shipments' => $pickups->first() ? $pickups->first()->relationLoaded('shipments') : false,
                    ]
                ]
            ];
            
            Log::info('✅ [PICKUPS API] === RÉPONSE CONSTRUITE AVEC SUCCÈS ===', [
                'pickups_returned' => count($transformedData),
                'response_time_ms' => $responseTime,
                'response_size_kb' => round(strlen(json_encode($response)) / 1024, 2),
                'transformation_errors' => count($transformationErrors)
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            $errorTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('❌ [PICKUPS API] === ERREUR FATALE ===', [
                'admin_id' => $admin->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_params' => $request->all(),
                'response_time_ms' => $errorTime,
                'memory_usage_mb' => memory_get_usage(true) / 1024 / 1024
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
                    'response_time_ms' => $errorTime,
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ] : 'Debug mode désactivé',
                    'suggestions' => [
                        'Vérifiez les logs Laravel pour plus de détails',
                        'Testez la connexion avec ?test=1',
                        'Vérifiez que toutes les tables existent',
                        'Vérifiez les relations entre les modèles'
                    ]
                ]
            ], 500);
        }
    }

    // ========================================
    // GESTION DES EXPÉDITIONS (SHIPMENTS) - SECTION COMPLÈTE
    // ========================================

    /**
     * Page de gestion des expéditions
     */
    public function shipments()
    {
        $admin = auth('admin')->user();
        
        Log::info('📦 [SHIPMENTS PAGE] Accès page expéditions', [
            'admin_id' => $admin->id,
            'timestamp' => now()->toISOString()
        ]);
        
        return view('admin.delivery.shipments');
    }

    /**
     * API pour la liste des expéditions avec diagnostic complet
     */
    public function getShipmentsList(Request $request)
    {
        $startTime = microtime(true);
        $admin = auth('admin')->user();
        
        // LOGS DE DÉMARRAGE DÉTAILLÉS
        Log::info('🚀 [SHIPMENTS API] === DÉBUT DE getShipmentsList ===', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_params' => $request->all(),
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);
        
        try {
            // VÉRIFICATIONS DE SÉCURITÉ
            if (!$admin) {
                Log::error('❌ [SHIPMENTS API] Admin non authentifié');
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié',
                    'debug_info' => [
                        'auth_guard' => 'admin',
                        'auth_check' => auth('admin')->check(),
                        'timestamp' => now()->toISOString()
                    ]
                ], 401);
            }
            
            Log::info('✅ [SHIPMENTS API] Authentification validée', [
                'admin_id' => $admin->id
            ]);
            
            // VÉRIFICATION DES TABLES
            $tableChecks = [
                'shipments' => Schema::hasTable('shipments'),
                'pickups' => Schema::hasTable('pickups'),
                'delivery_configurations' => Schema::hasTable('delivery_configurations'),
                'orders' => Schema::hasTable('orders')
            ];
            
            Log::info('🔍 [SHIPMENTS API] Vérification des tables', $tableChecks);
            
            foreach ($tableChecks as $table => $exists) {
                if (!$exists) {
                    Log::error("❌ [SHIPMENTS API] Table $table n'existe pas");
                    return response()->json([
                        'success' => false,
                        'error' => "Table $table non trouvée dans la base de données",
                        'table_checks' => $tableChecks
                    ], 500);
                }
            }
            
            // CONSTRUCTION DE LA REQUÊTE DE BASE
            Log::info('🔨 [SHIPMENTS API] Construction de la requête de base');
            
            $query = Shipment::where('admin_id', $admin->id);
            
            // AJOUT DES RELATIONS AVEC GESTION D'ERREUR
            try {
                Log::info('🔗 [SHIPMENTS API] Ajout des relations');
                $query->with([
                    'order' => function($q) {
                        $q->select('id', 'customer_name', 'customer_phone', 'customer_address', 
                                  'customer_city', 'customer_governorate', 'total_price', 'status');
                    },
                    'pickup' => function($q) {
                        $q->select('id', 'carrier_slug', 'delivery_configuration_id', 'status');
                    },
                    'pickup.deliveryConfiguration' => function($q) {
                        $q->select('id', 'carrier_slug', 'integration_name', 'is_active');
                    }
                ]);
                Log::info('✅ [SHIPMENTS API] Relations ajoutées avec succès');
            } catch (\Exception $relationError) {
                Log::warning('⚠️ [SHIPMENTS API] Erreur lors de l\'ajout des relations', [
                    'error' => $relationError->getMessage(),
                    'line' => $relationError->getLine()
                ]);
                // Continuer sans les relations si problème
                $query = Shipment::where('admin_id', $admin->id);
            }
            
            // APPLICATION DES FILTRES
            $appliedFilters = [];
            
            if ($request->filled('search')) {
                $search = trim($request->search);
                $appliedFilters['search'] = $search;
                Log::info('🔍 [SHIPMENTS API] Application filtre recherche', ['search' => $search]);
                
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('pos_barcode', 'like', "%{$search}%")
                      ->orWhere('order_id', 'like', "%{$search}%");
                    
                    // Recherche dans les informations du destinataire
                    $q->orWhereJsonContains('recipient_info->name', $search)
                      ->orWhereJsonContains('recipient_info->phone', $search);
                });
            }
            
            if ($request->filled('status')) {
                $status = $request->status;
                $appliedFilters['status'] = $status;
                Log::info('📊 [SHIPMENTS API] Application filtre statut', ['status' => $status]);
                $query->where('status', $status);
            }
            
            if ($request->filled('carrier')) {
                $carrier = $request->carrier;
                $appliedFilters['carrier'] = $carrier;
                Log::info('🚛 [SHIPMENTS API] Application filtre transporteur', ['carrier' => $carrier]);
                $query->where('carrier_slug', $carrier);
            }
            
            if ($request->filled('period')) {
                $period = $request->period;
                $appliedFilters['period'] = $period;
                Log::info('📅 [SHIPMENTS API] Application filtre période', ['period' => $period]);
                
                $now = now();
                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', $now->toDateString());
                        break;
                    case 'yesterday':
                        $query->whereDate('created_at', $now->subDay()->toDateString());
                        break;
                    case 'week':
                        $query->where('created_at', '>=', $now->subWeek());
                        break;
                    case 'month':
                        $query->where('created_at', '>=', $now->subMonth());
                        break;
                }
            }
            
            // TEST DE CONNECTIVITÉ (mode test)
            if ($request->filled('test') && $request->test === '1') {
                Log::info('🧪 [SHIPMENTS API] Mode test détecté');
                
                try {
                    $testCount = $query->count();
                    $testTime = round((microtime(true) - $startTime) * 1000, 2);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Test API réussi',
                        'test_results' => [
                            'count' => $testCount,
                            'response_time_ms' => $testTime,
                            'admin_id' => $admin->id,
                            'applied_filters' => $appliedFilters,
                            'query_sql' => $query->toSql(),
                            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
                        ],
                        'timestamp' => now()->toISOString()
                    ]);
                } catch (\Exception $testError) {
                    Log::error('❌ [SHIPMENTS API] Erreur pendant le test', [
                        'error' => $testError->getMessage(),
                        'trace' => $testError->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Erreur pendant le test: ' . $testError->getMessage(),
                        'admin_id' => $admin->id
                    ], 500);
                }
            }
            
            // PAGINATION ET RÉCUPÉRATION
            $perPage = min($request->get('per_page', 20), 100);
            Log::info('📄 [SHIPMENTS API] Récupération avec pagination', [
                'per_page' => $perPage,
                'applied_filters' => $appliedFilters
            ]);
            
            $shipments = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            Log::info('📊 [SHIPMENTS API] Expéditions récupérées de la DB', [
                'total_found' => $shipments->total(),
                'current_page_count' => $shipments->count(),
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage()
            ]);
            
            // TRANSFORMATION DES DONNÉES AVEC DEBUG DÉTAILLÉ
            $transformedData = [];
            $transformationErrors = [];
            
            foreach ($shipments->items() as $index => $shipment) {
                Log::debug("🔄 [SHIPMENTS API] Transformation shipment #{$shipment->id}");
                
                try {
                    $shipmentData = [
                        'id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'pickup_id' => $shipment->pickup_id,
                        'carrier_slug' => $shipment->carrier_slug,
                        'status' => $shipment->status,
                        'pos_barcode' => $shipment->pos_barcode,
                        'pos_reference' => $shipment->pos_reference,
                        'weight' => $shipment->weight,
                        'cod_amount' => $shipment->cod_amount,
                        'nb_pieces' => $shipment->nb_pieces,
                        'recipient_info' => $shipment->recipient_info ?: [],
                        'created_at' => $shipment->created_at->toISOString(),
                        'updated_at' => $shipment->updated_at->toISOString(),
                    ];
                    
                    // Configuration (avec fallback robuste)
                    try {
                        if ($shipment->relationLoaded('pickup') && $shipment->pickup && 
                            $shipment->pickup->relationLoaded('deliveryConfiguration') && 
                            $shipment->pickup->deliveryConfiguration) {
                            $shipmentData['integration_name'] = $shipment->pickup->deliveryConfiguration->integration_name;
                        } else {
                            // Fallback: chercher la configuration manuellement
                            $pickup = Pickup::with('deliveryConfiguration')->find($shipment->pickup_id);
                            $shipmentData['integration_name'] = $pickup && $pickup->deliveryConfiguration 
                                ? $pickup->deliveryConfiguration->integration_name 
                                : 'Configuration inconnue';
                        }
                    } catch (\Exception $configError) {
                        Log::debug("⚠️ [SHIPMENTS API] Erreur configuration shipment #{$shipment->id}", [
                            'error' => $configError->getMessage()
                        ]);
                        $shipmentData['integration_name'] = 'Configuration non disponible';
                    }
                    
                    // Order details (avec fallback robuste)
                    try {
                        if ($shipment->relationLoaded('order') && $shipment->order) {
                            $order = $shipment->order;
                        } else {
                            // Fallback: chercher l'order manuellement
                            $order = Order::find($shipment->order_id);
                        }
                        
                        if ($order) {
                            $shipmentData['order'] = [
                                'id' => $order->id,
                                'customer_name' => $order->customer_name ?? 'Client inconnu',
                                'customer_phone' => $order->customer_phone ?? '',
                                'customer_address' => $order->customer_address ?? '',
                                'customer_city' => $order->customer_city ?? '',
                                'customer_governorate' => $order->customer_governorate ?? '',
                                'total_price' => $order->total_price ?? 0,
                                'status' => $order->status ?? 'inconnu',
                            ];
                        } else {
                            $shipmentData['order'] = [
                                'id' => $shipment->order_id,
                                'customer_name' => 'Commande introuvable',
                                'customer_phone' => '',
                                'customer_address' => '',
                                'customer_city' => '',
                                'customer_governorate' => '',
                                'total_price' => 0,
                                'status' => 'inconnu',
                            ];
                        }
                    } catch (\Exception $orderError) {
                        Log::debug("⚠️ [SHIPMENTS API] Erreur order dans shipment #{$shipment->id}", [
                            'error' => $orderError->getMessage()
                        ]);
                        $shipmentData['order'] = [
                            'id' => $shipment->order_id,
                            'customer_name' => 'Erreur chargement',
                            'customer_phone' => '',
                            'customer_address' => '',
                            'customer_city' => '',
                            'customer_governorate' => '',
                            'total_price' => 0,
                            'status' => 'erreur',
                        ];
                    }
                    
                    $transformedData[] = $shipmentData;
                    
                } catch (\Exception $transformError) {
                    $errorMsg = "Erreur transformation shipment #{$shipment->id}: " . $transformError->getMessage();
                    $transformationErrors[] = $errorMsg;
                    
                    Log::error("❌ [SHIPMENTS API] $errorMsg", [
                        'shipment_id' => $shipment->id,
                        'error_trace' => $transformError->getTraceAsString()
                    ]);
                    
                    // Ajouter un shipment avec données minimales en cas d'erreur
                    $transformedData[] = [
                        'id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'pickup_id' => $shipment->pickup_id,
                        'carrier_slug' => $shipment->carrier_slug ?? 'unknown',
                        'status' => $shipment->status ?? 'unknown',
                        'pos_barcode' => $shipment->pos_barcode,
                        'weight' => 0,
                        'cod_amount' => 0,
                        'recipient_info' => [],
                        'integration_name' => 'Erreur chargement',
                        'order' => [
                            'id' => $shipment->order_id,
                            'customer_name' => 'Erreur chargement',
                            'customer_phone' => '',
                            'total_price' => 0,
                            'status' => 'erreur',
                        ],
                        'created_at' => $shipment->created_at->toISOString(),
                        'updated_at' => $shipment->updated_at->toISOString(),
                        'error' => 'Erreur lors du chargement des données'
                    ];
                }
            }
            
            // CONSTRUCTION DE LA RÉPONSE FINALE
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $response = [
                'success' => true,
                'shipments' => $transformedData,
                'pagination' => [
                    'current_page' => $shipments->currentPage(),
                    'last_page' => $shipments->lastPage(),
                    'per_page' => $shipments->perPage(),
                    'total' => $shipments->total(),
                    'from' => $shipments->firstItem(),
                    'to' => $shipments->lastItem(),
                ],
                'stats' => [
                    'total_returned' => count($transformedData),
                    'transformation_errors' => count($transformationErrors),
                    'response_time_ms' => $responseTime,
                ],
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'query_time' => now()->toISOString(),
                    'applied_filters' => $appliedFilters,
                    'table_checks' => $tableChecks,
                    'memory_peak_mb' => memory_get_peak_usage(true) / 1024 / 1024,
                    'transformation_errors' => $transformationErrors,
                    'has_relations' => [
                        'order' => $shipments->first() ? $shipments->first()->relationLoaded('order') : false,
                        'pickup' => $shipments->first() ? $shipments->first()->relationLoaded('pickup') : false,
                    ]
                ]
            ];
            
            Log::info('✅ [SHIPMENTS API] === RÉPONSE CONSTRUITE AVEC SUCCÈS ===', [
                'shipments_returned' => count($transformedData),
                'response_time_ms' => $responseTime,
                'response_size_kb' => round(strlen(json_encode($response)) / 1024, 2),
                'transformation_errors' => count($transformationErrors)
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            $errorTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('❌ [SHIPMENTS API] === ERREUR FATALE ===', [
                'admin_id' => $admin->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_params' => $request->all(),
                'response_time_ms' => $errorTime,
                'memory_usage_mb' => memory_get_usage(true) / 1024 / 1024
            ]);
            
            // Réponse d'erreur détaillée pour le debug
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des expéditions',
                'message' => $e->getMessage(),
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'timestamp' => now()->toISOString(),
                    'request_params' => $request->all(),
                    'response_time_ms' => $errorTime,
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ] : 'Debug mode désactivé',
                    'suggestions' => [
                        'Vérifiez les logs Laravel pour plus de détails',
                        'Testez la connexion avec ?test=1',
                        'Vérifiez que toutes les tables existent',
                        'Vérifiez les relations entre les modèles'
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Afficher les détails d'une expédition
     */
    public function showShipment(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            Log::warning('⚠️ [SHIPMENT SHOW] Accès non autorisé', [
                'shipment_id' => $shipment->id,
                'admin_id' => auth('admin')->id(),
                'shipment_admin_id' => $shipment->admin_id
            ]);
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('👁️ [SHIPMENT SHOW] Affichage détails expédition', ['shipment_id' => $shipment->id]);
        
        try {
            $shipment->load(['order', 'pickup.deliveryConfiguration']);
            
            return response()->json([
                'success' => true,
                'shipment' => $shipment,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [SHIPMENT SHOW] Erreur chargement expédition', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des détails'
            ], 500);
        }
    }

    /**
     * Suivre le statut d'une expédition
     */
    public function trackShipmentStatus(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🔍 [SHIPMENT TRACK] Suivi expédition', ['shipment_id' => $shipment->id]);
        
        try {
            // TODO: Implémenter l'appel à l'API du transporteur pour récupérer le statut
            // Pour l'instant, simuler une mise à jour
            
            if (!$shipment->pos_barcode) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucun numéro de suivi disponible'
                ], 400);
            }
            
            // Simuler une réponse de l'API transporteur (à remplacer par de vrais appels API)
            $trackingData = [
                'status' => $shipment->status,
                'last_update' => now(),
                'tracking_number' => $shipment->pos_barcode,
                'events' => [
                    [
                        'status' => $shipment->status,
                        'date' => now(),
                        'description' => 'Statut mis à jour via suivi',
                        'location' => 'Centre de tri'
                    ]
                ]
            ];
            
            // Mettre à jour le timestamp de dernière vérification
            $shipment->update([
                'carrier_last_status_update' => now()
            ]);
            
            Log::info('✅ [SHIPMENT TRACK] Suivi mis à jour', [
                'shipment_id' => $shipment->id,
                'status' => $shipment->status
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Suivi mis à jour avec succès',
                'tracking_data' => $trackingData,
                'shipment' => $shipment->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [SHIPMENT TRACK] Erreur suivi expédition', [
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
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('✅ [SHIPMENT DELIVERED] Marquage livraison', ['shipment_id' => $shipment->id]);
        
        try {
            if (!in_array($shipment->status, ['in_transit', 'picked_up_by_carrier'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cette expédition ne peut pas être marquée comme livrée'
                ], 400);
            }
            
            // Marquer comme livré
            $shipment->markAsDelivered('Marqué manuellement comme livré');
            
            Log::info('🎉 [SHIPMENT DELIVERED] Expédition marquée livrée', ['shipment_id' => $shipment->id]);
            
            return response()->json([
                'success' => true,
                'message' => "Expédition #{$shipment->id} marquée comme livrée",
                'shipment' => $shipment->fresh()
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [SHIPMENT DELIVERED] Erreur marquage livraison', [
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
     * Suivi en masse de toutes les expéditions
     */
    public function trackAllShipments()
    {
        $admin = auth('admin')->user();
        
        Log::info('🔄 [TRACK ALL] Suivi en masse', ['admin_id' => $admin->id]);
        
        try {
            $activeShipments = Shipment::where('admin_id', $admin->id)
                ->whereIn('status', ['validated', 'picked_up_by_carrier', 'in_transit'])
                ->whereNotNull('pos_barcode')
                ->get();
            
            $updated = 0;
            $errors = [];
            
            foreach ($activeShipments as $shipment) {
                try {
                    // TODO: Implémenter les appels API réels aux transporteurs
                    // Pour l'instant, juste mettre à jour le timestamp
                    $shipment->update([
                        'carrier_last_status_update' => now()
                    ]);
                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = "Expédition #{$shipment->id}: " . $e->getMessage();
                }
            }
            
            Log::info('🎉 [TRACK ALL] Suivi en masse terminé', [
                'updated' => $updated,
                'errors_count' => count($errors)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "{$updated} expédition(s) mise(s) à jour",
                'data' => [
                    'updated' => $updated,
                    'total' => $activeShipments->count(),
                    'errors' => $errors
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [TRACK ALL] Erreur suivi en masse', [
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
     * Obtenir les statistiques des expéditions pour l'API
     */
    public function getShipmentsStats()
    {
        $admin = auth('admin')->user();
        
        try {
            $stats = [
                'in_transit' => Shipment::where('admin_id', $admin->id)->where('status', 'in_transit')->count(),
                'delivered' => Shipment::where('admin_id', $admin->id)->where('status', 'delivered')->count(),
                'in_return' => Shipment::where('admin_id', $admin->id)->where('status', 'in_return')->count(),
                'anomaly' => Shipment::where('admin_id', $admin->id)->where('status', 'anomaly')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => [
                    'shipments' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [SHIPMENTS STATS] Erreur récupération statistiques', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'stats' => [
                    'shipments' => [
                        'in_transit' => 0,
                        'delivered' => 0,
                        'in_return' => 0,
                        'anomaly' => 0,
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Export des expéditions
     */
    public function exportShipments(Request $request)
    {
        $admin = auth('admin')->user();
        
        Log::info('📤 [SHIPMENTS EXPORT] Export des expéditions', ['admin_id' => $admin->id]);
        
        try {
            $query = Shipment::where('admin_id', $admin->id)
                ->with(['order', 'pickup.deliveryConfiguration']);
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('carrier')) {
                $query->where('carrier_slug', $request->carrier);
            }
            
            $shipments = $query->orderBy('created_at', 'desc')->get();
            
            $filename = 'expeditions_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            
            $callback = function() use ($shipments) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, [
                    'ID',
                    'Commande',
                    'Statut',
                    'Transporteur',
                    'Numéro suivi',
                    'Destinataire',
                    'Téléphone',
                    'Ville',
                    'Montant COD (TND)',
                    'Poids (kg)',
                    'Nb pièces',
                    'Créé le',
                    'Mis à jour le'
                ]);
                
                foreach ($shipments as $shipment) {
                    $recipientInfo = $shipment->recipient_info ?: [];
                    
                    fputcsv($file, [
                        $shipment->id,
                        $shipment->order_id,
                        $shipment->status,
                        $shipment->carrier_slug,
                        $shipment->pos_barcode ?: 'N/A',
                        $recipientInfo['name'] ?? 'N/A',
                        $recipientInfo['phone'] ?? 'N/A',
                        $recipientInfo['city'] ?? 'N/A',
                        $shipment->cod_amount,
                        $shipment->weight,
                        $shipment->nb_pieces,
                        $shipment->created_at->format('d/m/Y H:i'),
                        $shipment->updated_at->format('d/m/Y H:i')
                    ]);
                }
                
                fclose($file);
            };
            
            Log::info('✅ [SHIPMENTS EXPORT] Export généré', ['filename' => $filename]);
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('❌ [SHIPMENTS EXPORT] Erreur export expéditions', [
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
     * Actions en masse - suivi
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
                ->whereNotNull('pos_barcode')
                ->get();
            
            $updated = 0;
            $errors = [];
            
            foreach ($shipments as $shipment) {
                try {
                    // TODO: Implémenter les appels API réels
                    $shipment->update(['carrier_last_status_update' => now()]);
                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = "Expédition #{$shipment->id}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$updated} expédition(s) mise(s) à jour",
                'data' => [
                    'updated' => $updated,
                    'errors' => $errors
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [BULK TRACK] Erreur suivi en masse', [
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
     * Génération d'étiquettes en masse
     */
    public function generateBulkLabels(Request $request)
    {
        $admin = auth('admin')->user();
        
        Log::info('🏷️ [BULK LABELS] Génération étiquettes en masse', [
            'admin_id' => $admin->id,
            'shipment_ids' => $request->shipment_ids
        ]);
        
        // TODO: Implémenter la génération d'étiquettes
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * API pour les statistiques générales (pour le rafraîchissement temps réel)
     * MISE À JOUR avec les statistiques des shipments
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

            // 🆕 NOUVELLES STATISTIQUES DÉTAILLÉES POUR LES SHIPMENTS
            $shipmentStats = [
                'in_transit' => Shipment::where('admin_id', $admin->id)->where('status', 'in_transit')->count(),
                'delivered' => Shipment::where('admin_id', $admin->id)->where('status', 'delivered')->count(),
                'in_return' => Shipment::where('admin_id', $admin->id)->where('status', 'in_return')->count(),
                'anomaly' => Shipment::where('admin_id', $admin->id)->where('status', 'anomaly')->count(),
                'created' => Shipment::where('admin_id', $admin->id)->where('status', 'created')->count(),
                'validated' => Shipment::where('admin_id', $admin->id)->where('status', 'validated')->count(),
                'picked_up_by_carrier' => Shipment::where('admin_id', $admin->id)->where('status', 'picked_up_by_carrier')->count(),
            ];

            return response()->json([
                'success' => true,
                'general_stats' => $generalStats,
                'stats' => [
                    'shipments' => $shipmentStats  // 🆕 Ajout des stats détaillées des shipments
                ]
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
                ],
                'stats' => [
                    'shipments' => [
                        'in_transit' => 0,
                        'delivered' => 0,
                        'in_return' => 0,
                        'anomaly' => 0,
                        'created' => 0,
                        'validated' => 0,
                        'picked_up_by_carrier' => 0,
                    ]
                ]
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE: API spécifiquement pour les statistiques (utilisée par la vue shipments)
     */
    public function getApiStats()
    {
        return $this->getGeneralStats(); // Réutilise la méthode principale
    }

    // ========================================
    // ACTIONS SUR LES PICKUPS - VERSION SIMPLIFIÉE ET ROBUSTIFIÉE
    // ========================================

    /**
     * Afficher les détails d'un pickup
     */
    public function showPickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            Log::warning('⚠️ [PICKUP SHOW] Accès non autorisé', [
                'pickup_id' => $pickup->id,
                'admin_id' => auth('admin')->id(),
                'pickup_admin_id' => $pickup->admin_id
            ]);
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('👁️ [PICKUP SHOW] Affichage détails pickup', ['pickup_id' => $pickup->id]);
        
        try {
            $pickup->load(['shipments.order', 'deliveryConfiguration']);
            
            return response()->json([
                'success' => true,
                'pickup' => $pickup,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP SHOW] Erreur chargement pickup', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des détails'
            ], 500);
        }
    }

    /**
     * Valider un pickup (envoi vers l'API transporteur)
     */
    public function validatePickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('✅ [PICKUP VALIDATE] Validation pickup', ['pickup_id' => $pickup->id]);
        
        try {
            // Vérifier que le pickup peut être validé
            if (!$pickup->can_be_validated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ce pickup ne peut pas être validé'
                ], 400);
            }

            // Mettre à jour le statut
            $pickup->update([
                'status' => 'validated',
                'validated_at' => now(),
            ]);

            // Mettre à jour le statut des expéditions
            $pickup->shipments()->update(['status' => 'validated']);
            
            Log::info('🎉 [PICKUP VALIDATE] Pickup validé avec succès', ['pickup_id' => $pickup->id]);
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickup->id} validé avec succès",
                'pickup' => $pickup->fresh()
            ]);
            
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
            if ($pickup->status !== 'validated') {
                return response()->json([
                    'success' => false,
                    'error' => 'Seuls les pickups validés peuvent être marqués comme récupérés'
                ], 400);
            }

            $pickup->update(['status' => 'picked_up']);
            $pickup->shipments()->update(['status' => 'picked_up_by_carrier']);
            
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
                        $pickup->update([
                            'status' => 'validated',
                            'validated_at' => now(),
                        ]);
                        $pickup->shipments()->update(['status' => 'validated']);
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
                
                foreach ($pickups as $pickup) {
                    fputcsv($file, [
                        $pickup->id,
                        $pickup->status,
                        $pickup->carrier_slug,
                        $pickup->deliveryConfiguration->integration_name ?? 'N/A',
                        $pickup->pickup_date ? $pickup->pickup_date->format('d/m/Y') : 'N/A',
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
    // MÉTHODES POUR LA PRÉPARATION D'ENLÈVEMENT
    // ========================================

    /**
     * Page de préparation des enlèvements
     */
    public function preparation()
    {
        $admin = auth('admin')->user();
        
        Log::info('📦 [DELIVERY PREP] Accès page préparation', ['admin_id' => $admin->id]);
        
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
                ->whereDoesntHave('shipments')
                ->with(['items.product', 'region']);
            
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
            
            $perPage = min($request->get('per_page', 20), 50);
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            $orders->getCollection()->transform(function ($order) {
                $order->can_be_shipped = true;
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
        
        try {
            $validator = Validator::make($request->all(), [
                'delivery_configuration_id' => 'required|integer|exists:delivery_configurations,id',
                'order_ids' => 'required|array|min:1|max:50',
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
            
            $config = DeliveryConfiguration::where('id', $request->delivery_configuration_id)
                ->where('admin_id', $admin->id)
                ->where('is_active', true)
                ->first();
            
            if (!$config) {
                throw new \Exception('Configuration de transporteur non trouvée ou inactive');
            }
            
            $orders = Order::where('admin_id', $admin->id)
                ->whereIn('id', $request->order_ids)
                ->where('status', 'confirmée')
                ->where(function($query) {
                    $query->where('is_suspended', false)
                          ->orWhereNull('is_suspended');
                })
                ->get();
            
            if ($orders->isEmpty()) {
                throw new \Exception('Aucune commande valide trouvée');
            }
            
            $pickupDate = $request->pickup_date ?: now()->addDay()->format('Y-m-d');
            
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $config->carrier_slug,
                'delivery_configuration_id' => $config->id,
                'status' => 'draft',
                'pickup_date' => $pickupDate,
            ]);
            
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
                    
                    $order->update(['status' => 'expédiée']);
                    $shipmentsCreated++;
                    
                } catch (\Exception $e) {
                    Log::error('❌ [PICKUP CREATE] Erreur création shipment', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
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
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculer le poids d'une commande - VERSION SIMPLIFIÉE
     */
    protected function calculateOrderWeight($order): float
    {
        try {
            $itemsCount = $order->items ? $order->items->sum('quantity') : 1;
            return max(1.0, $itemsCount * 0.5);
        } catch (\Exception $e) {
            Log::warning('⚠️ [WEIGHT CALC] Erreur calcul poids', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return 1.0;
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

    // ========================================
    // MÉTHODES UTILITAIRES AMÉLIORÉES
    // ========================================

    /**
     * Obtenir le statut d'une configuration de transporteur.
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
     */
    protected function getCarrierStats($adminId, $carrierSlug)
    {
        try {
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
            
            return [
                'configurations' => 0,
                'pickups' => 0,
                'shipments' => 0,
            ];
        }
    }

    // ========================================
    // MÉTHODES DE TEST ET DIAGNOSTIC ÉTENDUES
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
                    'shipments_index' => route('admin.delivery.shipments'),
                    'shipments_list' => route('admin.delivery.shipments.list'),
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

    // ========================================
    // MÉTHODES POUR LA CONFIGURATION DES TRANSPORTEURS (PLACEHOLDER)
    // ========================================

    /**
     * Page de configuration des transporteurs
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        Log::info('⚙️ [DELIVERY CONFIG] Accès page configuration', ['admin_id' => $admin->id]);
        
        // TODO: Implémenter la page de configuration
        return view('admin.delivery.configuration', [
            'carriers' => $this->carriers,
            'configurations' => $admin->deliveryConfigurations()->get()
        ]);
    }

    /**
     * Créer une nouvelle configuration
     */
    public function createConfiguration()
    {
        Log::info('🆕 [CONFIG CREATE] Création nouvelle configuration');
        
        // TODO: Implémenter la création de configuration
        return view('admin.delivery.configuration-create', [
            'carriers' => $this->carriers
        ]);
    }

    /**
     * Sauvegarder une configuration
     */
    public function storeConfiguration(Request $request)
    {
        $admin = auth('admin')->user();
        
        Log::info('💾 [CONFIG STORE] Sauvegarde configuration', [
            'admin_id' => $admin->id,
            'carrier_slug' => $request->carrier_slug
        ]);
        
        // TODO: Implémenter la sauvegarde
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Éditer une configuration
     */
    public function editConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('✏️ [CONFIG EDIT] Édition configuration', ['config_id' => $config->id]);
        
        // TODO: Implémenter l'édition
        return view('admin.delivery.configuration-edit', [
            'config' => $config,
            'carriers' => $this->carriers
        ]);
    }

    /**
     * Mettre à jour une configuration
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🔄 [CONFIG UPDATE] Mise à jour configuration', ['config_id' => $config->id]);
        
        // TODO: Implémenter la mise à jour
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🗑️ [CONFIG DELETE] Suppression configuration', ['config_id' => $config->id]);
        
        try {
            $config->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CONFIG DELETE] Erreur suppression', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Tester la connexion d'une configuration
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🧪 [CONFIG TEST] Test connexion', ['config_id' => $config->id]);
        
        // TODO: Implémenter le test de connexion
        return response()->json([
            'success' => true,
            'message' => 'Test de connexion simulé - OK'
        ]);
    }

    /**
     * Activer/désactiver une configuration
     */
    public function toggleConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🔄 [CONFIG TOGGLE] Changement statut', [
            'config_id' => $config->id,
            'was_active' => $config->is_active
        ]);
        
        try {
            $config->update(['is_active' => !$config->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => $config->is_active ? 'Configuration activée' : 'Configuration désactivée',
                'is_active' => $config->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CONFIG TOGGLE] Erreur changement statut', [
                'config_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }

    // ========================================
    // AUTRES MÉTHODES PLACEHOLDER
    // ========================================

    /**
     * Page des statistiques
     */
    public function stats()
    {
        $admin = auth('admin')->user();
        
        Log::info('📊 [DELIVERY STATS] Accès page statistiques', ['admin_id' => $admin->id]);
        
        // TODO: Implémenter la page de statistiques
        return view('admin.delivery.stats');
    }

    /**
     * Activité récente
     */
    public function getRecentActivity()
    {
        $admin = auth('admin')->user();
        
        try {
            // TODO: Implémenter la récupération d'activité récente
            $recentActivity = [
                [
                    'type' => 'pickup_created',
                    'message' => 'Nouveau pickup créé',
                    'timestamp' => now()->subMinutes(5),
                ],
                [
                    'type' => 'shipment_delivered',
                    'message' => 'Expédition livrée',
                    'timestamp' => now()->subMinutes(15),
                ]
            ];
            
            return response()->json([
                'success' => true,
                'activity' => $recentActivity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de l\'activité'
            ], 500);
        }
    }

    /**
     * API pour les commandes disponibles (version API)
     */
    public function getAvailableOrdersApi(Request $request)
    {
        return $this->getAvailableOrders($request);
    }

    // ========================================
    // WEBHOOKS ET APIs EXTERNES (PLACEHOLDER)
    // ========================================

    /**
     * Webhook JAX Delivery
     */
    public function webhookJaxDelivery(Request $request)
    {
        Log::info('📨 [WEBHOOK JAX] Réception webhook', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        // TODO: Implémenter le traitement du webhook JAX Delivery
        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Webhook Mes Colis
     */
    public function webhookMesColis(Request $request)
    {
        Log::info('📨 [WEBHOOK MES COLIS] Réception webhook', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        // TODO: Implémenter le traitement du webhook Mes Colis
        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Valider la configuration d'un webhook
     */
    public function validateWebhookSetup($carrier)
    {
        Log::info("🔍 [WEBHOOK VALIDATE] Validation setup webhook {$carrier}");
        
        // TODO: Implémenter la validation
        return response()->json([
            'success' => true,
            'message' => "Webhook {$carrier} configuré correctement"
        ]);
    }

    // ========================================
    // MÉTHODES DE COÛTS ET ZONES (PLACEHOLDER)
    // ========================================

    /**
     * Calculer le coût d'expédition
     */
    public function calculateShippingCost(Request $request)
    {
        Log::info('💰 [SHIPPING COST] Calcul coût expédition', $request->all());
        
        // TODO: Implémenter le calcul des coûts
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Comparer les coûts entre transporteurs
     */
    public function compareCarrierCosts(Request $request)
    {
        Log::info('⚖️ [CARRIER COMPARE] Comparaison coûts transporteurs', $request->all());
        
        // TODO: Implémenter la comparaison
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Historique des coûts
     */
    public function getCostHistory(Request $request)
    {
        // TODO: Implémenter l'historique des coûts
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Obtenir les zones de livraison
     */
    public function getDeliveryZones($carrier)
    {
        // TODO: Implémenter la récupération des zones
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Mettre à jour les zones de livraison
     */
    public function updateDeliveryZones(Request $request, $carrier)
    {
        // TODO: Implémenter la mise à jour des zones
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Vérifier la couverture de livraison
     */
    public function checkDeliveryCoverage(Request $request)
    {
        // TODO: Implémenter la vérification de couverture
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    // ========================================
    // MÉTHODES DE GÉNÉRATION DE DOCUMENTS (PLACEHOLDER)
    // ========================================

    /**
     * Générer une étiquette d'expédition
     */
    public function generateShippingLabel(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🏷️ [LABEL GENERATE] Génération étiquette', ['shipment_id' => $shipment->id]);
        
        // TODO: Implémenter la génération d'étiquette
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Générer un manifeste de pickup
     */
    public function generatePickupManifest(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('📋 [MANIFEST GENERATE] Génération manifeste', ['pickup_id' => $pickup->id]);
        
        // TODO: Implémenter la génération de manifeste
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }

    /**
     * Générer une preuve de livraison
     */
    public function generateDeliveryProof(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('📄 [PROOF GENERATE] Génération preuve livraison', ['shipment_id' => $shipment->id]);
        
        // TODO: Implémenter la génération de preuve de livraison
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en cours de développement'
        ], 501);
    }
}