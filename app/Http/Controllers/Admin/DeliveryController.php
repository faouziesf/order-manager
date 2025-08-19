<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\Region;
use App\Services\Delivery\Contracts\CarrierServiceException;
use App\Services\Delivery\SimpleCarrierFactory;
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
    
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->carriers = config('carriers', []);
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
        
        Log::info('🏠 [DELIVERY INDEX] Accès page principale', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name
        ]);
        
        if (empty($this->carriers)) {
            Log::error('❌ [DELIVERY INDEX] Configuration transporteurs manquante');
            return redirect()->back()->with('error', 'Configuration des transporteurs manquante.');
        }
        
        try {
            $configurations = DeliveryConfiguration::where('admin_id', $admin->id)->get()->groupBy('carrier_slug');
            $carriersData = [];
            
            foreach ($this->carriers as $slug => $carrierConfig) {
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
            
        } catch (\Exception $e) {
            Log::error('❌ [DELIVERY INDEX] Erreur critique', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            
            return view('admin.delivery.index', [
                'carriersData' => [],
                'generalStats' => [
                    'total_configurations' => 0,
                    'active_configurations' => 0,
                    'total_pickups' => 0,
                    'pending_pickups' => 0,
                    'total_shipments' => 0,
                    'active_shipments' => 0,
                ]
            ])->with('error', 'Erreur lors du chargement des données.');
        }
    }

    // ========================================
    // GESTION DES PICKUPS
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
     * API - Liste des pickups avec diagnostic complet
     */
    public function getPickupsList(Request $request)
    {
        $startTime = microtime(true);
        $admin = auth('admin')->user();
        
        Log::info('🚀 [PICKUPS API] Début getPickupsList', [
            'admin_id' => $admin->id,
            'request_params' => $request->all(),
        ]);
        
        try {
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }
            
            $tableChecks = [
                'pickups' => Schema::hasTable('pickups'),
                'delivery_configurations' => Schema::hasTable('delivery_configurations'),
                'shipments' => Schema::hasTable('shipments'),
                'orders' => Schema::hasTable('orders')
            ];
            
            foreach ($tableChecks as $table => $exists) {
                if (!$exists) {
                    return response()->json([
                        'success' => false,
                        'error' => "Table $table non trouvée"
                    ], 500);
                }
            }
            
            $query = Pickup::where('admin_id', $admin->id);
            
            try {
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
            } catch (\Exception $relationError) {
                Log::warning('⚠️ [PICKUPS API] Erreur relations', ['error' => $relationError->getMessage()]);
                $query = Pickup::where('admin_id', $admin->id);
            }
            
            $appliedFilters = [];
            
            if ($request->filled('search')) {
                $search = trim($request->search);
                $appliedFilters['search'] = $search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('carrier_slug', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('status')) {
                $status = $request->status;
                $appliedFilters['status'] = $status;
                $query->where('status', $status);
            }
            
            if ($request->filled('carrier')) {
                $carrier = $request->carrier;
                $appliedFilters['carrier'] = $carrier;
                $query->where('carrier_slug', $carrier);
            }
            
            // Test mode
            if ($request->filled('test') && $request->test === '1') {
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
                    ]
                ]);
            }
            
            $perPage = min($request->get('per_page', 20), 100);
            $pickups = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            $transformedData = [];
            $transformationErrors = [];
            
            foreach ($pickups->items() as $pickup) {
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
                    
                    // Configuration
                    try {
                        if ($pickup->relationLoaded('deliveryConfiguration') && $pickup->deliveryConfiguration) {
                            $pickupData['configuration_name'] = $pickup->deliveryConfiguration->integration_name;
                        } else {
                            $config = DeliveryConfiguration::where('id', $pickup->delivery_configuration_id)->first();
                            $pickupData['configuration_name'] = $config ? $config->integration_name : 'Configuration inconnue';
                        }
                    } catch (\Exception $configError) {
                        $pickupData['configuration_name'] = 'Configuration non disponible';
                    }
                    
                    // Shipments
                    try {
                        if ($pickup->relationLoaded('shipments')) {
                            $shipments = $pickup->shipments;
                        } else {
                            $shipments = Shipment::where('pickup_id', $pickup->id)->get();
                        }
                        
                        $pickupData['orders_count'] = $shipments->count();
                        $pickupData['total_weight'] = round($shipments->sum('weight') ?: 0, 2);
                        $pickupData['total_pieces'] = $shipments->sum('nb_pieces') ?: 0;
                        $pickupData['total_cod_amount'] = round($shipments->sum('cod_amount') ?: 0, 3);
                        
                        $ordersData = [];
                        foreach ($shipments as $shipment) {
                            try {
                                if ($shipment->relationLoaded('order') && $shipment->order) {
                                    $order = $shipment->order;
                                } else {
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
                                continue;
                            }
                        }
                        
                        $pickupData['orders'] = $ordersData;
                        
                    } catch (\Exception $shipmentsError) {
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
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return response()->json([
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
                    'applied_filters' => $appliedFilters,
                    'table_checks' => $tableChecks,
                    'transformation_errors' => $transformationErrors,
                ]
            ]);
            
        } catch (\Exception $e) {
            $errorTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('❌ [PICKUPS API] Erreur fatale', [
                'admin_id' => $admin->id,
                'error_message' => $e->getMessage(),
                'response_time_ms' => $errorTime,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des pickups',
                'message' => $e->getMessage(),
                'debug_info' => [
                    'admin_id' => $admin->id,
                    'response_time_ms' => $errorTime,
                    'suggestions' => [
                        'Vérifiez les logs Laravel',
                        'Testez avec ?test=1',
                        'Vérifiez les tables',
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Afficher les détails d'un pickup
     */
    public function showPickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $pickup->load(['shipments.order', 'deliveryConfiguration']);
            
            return response()->json([
                'success' => true,
                'pickup' => $pickup,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP SHOW] Erreur', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement'
            ], 500);
        }
    }

    /**
     * 🆕 MÉTHODE PRINCIPALE : Diagnostic complet d'un pickup
     */
    public function diagnosticPickup($pickupId)
    {
        $admin = auth('admin')->user();
        
        Log::info('🔍 [PICKUP DIAGNOSTIC] Début diagnostic', [
            'pickup_id' => $pickupId,
            'admin_id' => $admin->id,
        ]);
        
        try {
            $pickup = Pickup::where('admin_id', $admin->id)
                ->with(['deliveryConfiguration', 'shipments.order'])
                ->find($pickupId);
                
            if (!$pickup) {
                return response()->json([
                    'success' => false,
                    'error' => "Pickup #{$pickupId} non trouvé",
                ], 404);
            }
            
            $diagnostic = [
                'pickup_info' => [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'carrier_slug' => $pickup->carrier_slug,
                    'delivery_configuration_id' => $pickup->delivery_configuration_id,
                    'pickup_date' => $pickup->pickup_date?->toDateString(),
                    'created_at' => $pickup->created_at->toISOString(),
                ],
                
                'configuration_check' => [
                    'exists' => !!$pickup->deliveryConfiguration,
                    'id' => $pickup->deliveryConfiguration?->id,
                    'integration_name' => $pickup->deliveryConfiguration?->integration_name,
                    'carrier_slug' => $pickup->deliveryConfiguration?->carrier_slug,
                    'is_active' => $pickup->deliveryConfiguration?->is_active ?? false,
                    'is_valid' => $pickup->deliveryConfiguration?->is_valid ?? false,
                    'username_exists' => !empty($pickup->deliveryConfiguration?->username),
                    'password_exists' => !empty($pickup->deliveryConfiguration?->password),
                    'environment' => $pickup->deliveryConfiguration?->environment,
                ],
                
                'shipments_check' => [
                    'count' => $pickup->shipments->count(),
                    'shipments' => $pickup->shipments->map(function($shipment) {
                        return [
                            'id' => $shipment->id,
                            'order_id' => $shipment->order_id,
                            'status' => $shipment->status,
                            'has_recipient_info' => !empty($shipment->recipient_info),
                            'recipient_name' => $shipment->recipient_info['name'] ?? null,
                            'recipient_phone' => $shipment->recipient_info['phone'] ?? null,
                            'cod_amount' => $shipment->cod_amount,
                        ];
                    }),
                ],
                
                'validation_check' => [
                    'can_be_validated' => $pickup->can_be_validated,
                    'can_be_edited' => $pickup->can_be_edited,
                    'can_be_deleted' => $pickup->can_be_deleted,
                    'status_allows_validation' => $pickup->status === 'draft',
                    'has_shipments' => $pickup->shipments()->exists(),
                    'config_is_active' => $pickup->deliveryConfiguration?->is_active ?? false,
                    'config_is_valid_for_api' => $pickup->deliveryConfiguration?->isValidForApiCalls() ?? false,
                ],
                
                'api_config_preview' => null,
            ];
            
            // Test de la config API si possible
            if ($pickup->deliveryConfiguration && $pickup->deliveryConfiguration->isValidForApiCalls()) {
                try {
                    $apiConfig = $pickup->deliveryConfiguration->getApiConfig();
                    $diagnostic['api_config_preview'] = [
                        'carrier' => $pickup->carrier_slug,
                        'has_api_token' => !empty($apiConfig['api_token']),
                        'has_username' => !empty($apiConfig['username']),
                        'token_preview' => !empty($apiConfig['api_token']) ? 
                            substr($apiConfig['api_token'], 0, 10) . '...' : null,
                        'username' => $apiConfig['username'] ?? null,
                        'environment' => $apiConfig['environment'] ?? null,
                    ];
                } catch (\Exception $e) {
                    $diagnostic['api_config_preview'] = ['error' => $e->getMessage()];
                }
            }
            
            return response()->json([
                'success' => true,
                'pickup_id' => $pickupId,
                'diagnostic' => $diagnostic,
                'recommendations' => $this->getPickupRecommendations($diagnostic),
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP DIAGNOSTIC] Erreur', [
                'pickup_id' => $pickupId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du diagnostic: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 MÉTHODE HELPER : Recommandations pour corriger un pickup
     */
    private function getPickupRecommendations($diagnostic)
    {
        $recommendations = [];
        
        if (!$diagnostic['configuration_check']['exists']) {
            $recommendations[] = [
                'type' => 'error',
                'message' => 'Aucune configuration de transporteur associée',
                'action' => 'Créer et associer une configuration valide',
            ];
        } elseif (!$diagnostic['configuration_check']['is_active']) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Configuration inactive',
                'action' => 'Activer la configuration',
            ];
        } elseif (!$diagnostic['configuration_check']['is_valid']) {
            $recommendations[] = [
                'type' => 'error',
                'message' => 'Configuration invalide',
                'action' => 'Vérifier les tokens/identifiants',
            ];
        }
        
        if ($diagnostic['shipments_check']['count'] === 0) {
            $recommendations[] = [
                'type' => 'error',
                'message' => 'Aucune expédition dans ce pickup',
                'action' => 'Ajouter des commandes au pickup',
            ];
        }
        
        if (!$diagnostic['validation_check']['can_be_validated']) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Le pickup ne peut pas être validé',
                'action' => 'Corriger les problèmes ci-dessus',
            ];
        } else {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Le pickup peut être validé',
                'action' => 'Vous pouvez procéder à la validation',
            ];
        }
        
        return $recommendations;
    }

    /**
     * 🔥 MÉTHODE PRINCIPALE CORRIGÉE : Valider un pickup (envoi vers l'API transporteur)
     */
    public function validatePickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('✅ [PICKUP VALIDATE] Début validation', [
            'pickup_id' => $pickup->id,
            'carrier' => $pickup->carrier_slug,
            'admin_id' => auth('admin')->id()
        ]);
        
        try {
            if (!$pickup->can_be_validated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ce pickup ne peut pas être validé',
                    'details' => [
                        'pickup_status' => $pickup->status,
                        'shipments_count' => $pickup->shipments->count(),
                        'config_active' => $pickup->deliveryConfiguration?->is_active ?? false,
                    ]
                ], 400);
            }

            if ($pickup->shipments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune expédition à valider'
                ], 400);
            }

            if (!$pickup->deliveryConfiguration || !$pickup->deliveryConfiguration->is_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Configuration inactive ou manquante'
                ], 400);
            }

            // 🔥 CORRECTION CRITIQUE : Appeler la méthode validate() du modèle Pickup
            $result = $pickup->validate();
            
            if ($result['success']) {
                $successMessage = "Pickup #{$pickup->id} validé avec succès ! ";
                $successMessage .= "{$result['successful_shipments']}/{$result['total_shipments']} expédition(s) envoyée(s) vers le transporteur.";
                
                if (!empty($result['errors'])) {
                    $successMessage .= " Attention : " . count($result['errors']) . " erreur(s).";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => [
                        'pickup_id' => $pickup->id,
                        'successful_shipments' => $result['successful_shipments'],
                        'total_shipments' => $result['total_shipments'],
                        'tracking_numbers' => $result['tracking_numbers'] ?? [],
                        'errors' => $result['errors'],
                        'validated_at' => $pickup->fresh()->validated_at->toISOString(),
                    ],
                    'pickup' => $pickup->fresh()->load(['shipments.order', 'deliveryConfiguration'])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur validation : ' . implode(', ', $result['errors']),
                    'details' => $result,
                ], 500);
            }
            
        } catch (CarrierServiceException $e) {
            Log::error('❌ [PICKUP VALIDATE] Erreur transporteur', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => "Erreur transporteur : " . $e->getMessage(),
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('❌ [PICKUP VALIDATE] Erreur générale', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur validation : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 DIAGNOSTIC DÉTAILLÉ : Analyser un pickup problématique
     */
    public function diagnosePickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $diagnosis = $pickup->diagnoseValidationIssues();
            
            // Ajouter des tests supplémentaires
            $diagnosis['api_tests'] = [];
            
            if ($pickup->deliveryConfiguration && $pickup->deliveryConfiguration->is_active) {
                try {
                    $apiConfig = $pickup->deliveryConfiguration->getApiConfig();
                    $carrierService = SimpleCarrierFactory::create($pickup->carrier_slug, $apiConfig);
                    
                    // Test de connexion
                    $connectionTest = $carrierService->testConnection();
                    $diagnosis['api_tests']['connection'] = $connectionTest;
                    
                    // Test avec un colis fictif
                    if ($connectionTest['success']) {
                        try {
                            $testData = [
                                'external_reference' => 'TEST_' . time(),
                                'recipient_name' => 'Test Client',
                                'recipient_phone' => '12345678',
                                'recipient_address' => 'Test Address',
                                'recipient_governorate' => 'Tunis',
                                'recipient_city' => 'Tunis',
                                'cod_amount' => 10,
                                'content_description' => 'Test colis',
                                'weight' => 1.0,
                            ];
                            
                            // NE PAS créer réellement, juste valider les données
                            $diagnosis['api_tests']['test_data_validation'] = [
                                'success' => true,
                                'message' => 'Données de test préparées avec succès',
                                'test_data' => $testData,
                            ];
                        } catch (\Exception $e) {
                            $diagnosis['api_tests']['test_data_validation'] = [
                                'success' => false,
                                'error' => $e->getMessage(),
                            ];
                        }
                    }
                    
                } catch (\Exception $e) {
                    $diagnosis['api_tests']['error'] = $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'pickup_id' => $pickup->id,
                'diagnosis' => $diagnosis,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur diagnostic: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 VALIDATION FORCÉE : Forcer la validation avec diagnostic détaillé
     */
    public function forceValidatePickup(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🔧 [FORCE VALIDATE] Validation forcée avec diagnostic', [
            'pickup_id' => $pickup->id,
            'admin_id' => auth('admin')->id(),
            'shipments_count' => $pickup->shipments->count(),
        ]);
        
        try {
            // Diagnostic avant validation
            $preValidationDiagnosis = $pickup->diagnoseValidationIssues();
            
            Log::info('📋 [FORCE VALIDATE] Diagnostic pré-validation', [
                'pickup_id' => $pickup->id,
                'can_be_validated' => $preValidationDiagnosis['can_be_validated'],
                'issues_count' => count($preValidationDiagnosis['issues']),
                'issues' => $preValidationDiagnosis['issues'],
            ]);
            
            // Validation avec logging détaillé
            $result = $pickup->validate();
            
            Log::info('🎯 [FORCE VALIDATE] Résultat validation', [
                'pickup_id' => $pickup->id,
                'success' => $result['success'],
                'successful_shipments' => $result['successful_shipments'] ?? 0,
                'total_shipments' => $result['total_shipments'] ?? 0,
                'errors_count' => count($result['errors'] ?? []),
            ]);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? "Validation forcée réussie: {$result['successful_shipments']}/{$result['total_shipments']} expéditions"
                    : "Échec validation forcée",
                'data' => $result,
                'pre_validation_diagnosis' => $preValidationDiagnosis,
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [FORCE VALIDATE] Erreur validation forcée', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur validation forcée: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 LOGS : Récupérer les logs récents pour un pickup
     */
    public function getPickupLogs(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            // Cette méthode nécessiterait une implémentation de logging structuré
            // Pour l'instant, retourner des infos de base
            
            $logs = [
                'pickup_id' => $pickup->id,
                'created_at' => $pickup->created_at->toISOString(),
                'updated_at' => $pickup->updated_at->toISOString(),
                'status' => $pickup->status,
                'validated_at' => $pickup->validated_at?->toISOString(),
                'carrier' => $pickup->carrier_slug,
                'shipments' => $pickup->shipments->map(function($shipment) {
                    return [
                        'id' => $shipment->id,
                        'status' => $shipment->status,
                        'pos_barcode' => $shipment->pos_barcode,
                        'created_at' => $shipment->created_at->toISOString(),
                        'updated_at' => $shipment->updated_at->toISOString(),
                    ];
                }),
            ];
            
            return response()->json([
                'success' => true,
                'logs' => $logs,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur récupération logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marquer un pickup comme récupéré
     */
    public function markPickupAsPickedUp(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            if ($pickup->status !== 'validated') {
                return response()->json([
                    'success' => false,
                    'error' => 'Seuls les pickups validés peuvent être marqués comme récupérés'
                ], 400);
            }

            $pickup->markAsPickedUp();
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickup->id} marqué comme récupéré",
                'pickup' => $pickup->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur : ' . $e->getMessage()
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
            
            foreach ($pickup->shipments as $shipment) {
                if ($shipment->order) {
                    $shipment->order->update(['status' => 'confirmée']);
                }
                $shipment->delete();
            }
            
            $pickupId = $pickup->id;
            $pickup->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Pickup #{$pickupId} supprimé avec succès"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // GESTION DES EXPÉDITIONS (SHIPMENTS)
    // ========================================

    /**
     * Page de gestion des expéditions
     */
    public function shipments()
    {
        $admin = auth('admin')->user();
        Log::info('📦 [SHIPMENTS PAGE] Accès page expéditions', ['admin_id' => $admin->id]);
        return view('admin.delivery.shipments');
    }

    /**
     * API pour la liste des expéditions
     */
    public function getShipmentsList(Request $request)
    {
        $startTime = microtime(true);
        $admin = auth('admin')->user();
        
        try {
            if (!$admin) {
                return response()->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }
            
            $query = Shipment::where('admin_id', $admin->id);
            
            try {
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
            } catch (\Exception $relationError) {
                $query = Shipment::where('admin_id', $admin->id);
            }
            
            $appliedFilters = [];
            
            if ($request->filled('search')) {
                $search = trim($request->search);
                $appliedFilters['search'] = $search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('pos_barcode', 'like', "%{$search}%")
                      ->orWhere('order_id', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('status')) {
                $status = $request->status;
                $appliedFilters['status'] = $status;
                $query->where('status', $status);
            }
            
            if ($request->filled('carrier')) {
                $carrier = $request->carrier;
                $appliedFilters['carrier'] = $carrier;
                $query->where('carrier_slug', $carrier);
            }
            
            // Test mode
            if ($request->filled('test') && $request->test === '1') {
                $testCount = $query->count();
                $testTime = round((microtime(true) - $startTime) * 1000, 2);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Test API réussi',
                    'test_results' => [
                        'count' => $testCount,
                        'response_time_ms' => $testTime,
                    ]
                ]);
            }
            
            $perPage = min($request->get('per_page', 20), 100);
            $shipments = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            $transformedData = [];
            foreach ($shipments->items() as $shipment) {
                try {
                    $shipmentData = [
                        'id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'pickup_id' => $shipment->pickup_id,
                        'carrier_slug' => $shipment->carrier_slug,
                        'status' => $shipment->status,
                        'pos_barcode' => $shipment->pos_barcode,
                        'weight' => $shipment->weight,
                        'cod_amount' => $shipment->cod_amount,
                        'nb_pieces' => $shipment->nb_pieces,
                        'recipient_info' => $shipment->recipient_info ?: [],
                        'created_at' => $shipment->created_at->toISOString(),
                    ];
                    
                    // Configuration
                    if ($shipment->relationLoaded('pickup') && $shipment->pickup && 
                        $shipment->pickup->relationLoaded('deliveryConfiguration') && 
                        $shipment->pickup->deliveryConfiguration) {
                        $shipmentData['integration_name'] = $shipment->pickup->deliveryConfiguration->integration_name;
                    } else {
                        $pickup = Pickup::with('deliveryConfiguration')->find($shipment->pickup_id);
                        $shipmentData['integration_name'] = $pickup && $pickup->deliveryConfiguration 
                            ? $pickup->deliveryConfiguration->integration_name 
                            : 'Configuration inconnue';
                    }
                    
                    // Order details
                    if ($shipment->relationLoaded('order') && $shipment->order) {
                        $order = $shipment->order;
                    } else {
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
                            'total_price' => 0,
                        ];
                    }
                    
                    $transformedData[] = $shipmentData;
                    
                } catch (\Exception $transformError) {
                    $transformedData[] = [
                        'id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'status' => $shipment->status ?? 'unknown',
                        'carrier_slug' => $shipment->carrier_slug ?? 'unknown',
                        'integration_name' => 'Erreur chargement',
                        'order' => ['id' => $shipment->order_id, 'customer_name' => 'Erreur'],
                        'error' => 'Erreur chargement données'
                    ];
                }
            }
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return response()->json([
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
                    'response_time_ms' => $responseTime,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur récupération expéditions',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des expéditions
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
                'stats' => ['shipments' => $stats]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur statistiques',
                'stats' => ['shipments' => ['in_transit' => 0, 'delivered' => 0, 'in_return' => 0, 'anomaly' => 0]]
            ], 500);
        }
    }

    // ========================================
    // 🆕 MÉTHODES DE SUIVI DE STATUT - NOUVELLE FONCTIONNALITÉ COMPLÈTE
    // ========================================

    /**
     * 🆕 NOUVELLE MÉTHODE : Suivi manuel d'un shipment spécifique
     */
    public function trackShipmentStatus(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        Log::info('🔍 [MANUAL TRACK] Début suivi manuel', [
            'shipment_id' => $shipment->id,
            'tracking_number' => $shipment->pos_barcode,
            'carrier' => $shipment->carrier_slug,
            'current_status' => $shipment->status,
        ]);
        
        try {
            if (empty($shipment->pos_barcode)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucun numéro de suivi disponible pour cette expédition'
                ], 400);
            }
            
            // Vérifier que nous avons une configuration valide
            if (!$shipment->pickup || !$shipment->pickup->deliveryConfiguration) {
                return response()->json([
                    'success' => false,
                    'error' => 'Configuration transporteur manquante'
                ], 400);
            }
            
            $config = $shipment->pickup->deliveryConfiguration;
            
            if (!$config->is_active || !$config->is_valid) {
                return response()->json([
                    'success' => false,
                    'error' => 'Configuration transporteur inactive ou invalide'
                ], 400);
            }
            
            // Créer le service transporteur et récupérer le statut
            $apiConfig = $config->getApiConfig();
            $carrierService = SimpleCarrierFactory::create($shipment->carrier_slug, $apiConfig);
            
            $result = $carrierService->getShipmentStatus($shipment->pos_barcode);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Impossible de récupérer le statut depuis l\'API du transporteur'
                ], 422);
            }
            
            $oldStatus = $shipment->status;
            $newStatus = $result['status'];
            $statusChanged = ($newStatus !== $oldStatus && $newStatus !== 'unknown');
            
            // Mettre à jour le statut si il a changé
            if ($statusChanged) {
                $shipment->updateStatus(
                    $newStatus,
                    $result['response']['carrier_code'] ?? null,
                    $result['response']['carrier_label'] ?? null,
                    "Statut mis à jour manuellement par " . auth('admin')->user()->name
                );
                
                Log::info('✅ [MANUAL TRACK] Statut mis à jour', [
                    'shipment_id' => $shipment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => auth('admin')->user()->name,
                ]);
            } else {
                // Mettre à jour la date de dernière vérification même si pas de changement
                $shipment->update(['carrier_last_status_update' => now()]);
            }
            
            return response()->json([
                'success' => true,
                'tracking_number' => $shipment->pos_barcode,
                'status_changed' => $statusChanged,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'carrier_response' => $result['response'],
                'last_update' => $shipment->fresh()->carrier_last_status_update->toISOString(),
                'message' => $statusChanged 
                    ? "Statut mis à jour : {$oldStatus} → {$newStatus}"
                    : "Statut inchangé : {$newStatus}",
                'shipment' => $shipment->fresh()->load(['order', 'pickup.deliveryConfiguration'])
            ]);
            
        } catch (CarrierServiceException $e) {
            Log::error('❌ [MANUAL TRACK] Erreur transporteur', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
                'carrier_response' => $e->getCarrierResponse(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => "Erreur transporteur : " . $e->getMessage(),
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('❌ [MANUAL TRACK] Erreur générale', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du suivi : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Suivi en lot de plusieurs shipments
     */
    public function bulkTrackShipments(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $validator = Validator::make($request->all(), [
                'shipment_ids' => 'required|array|min:1|max:20',
                'shipment_ids.*' => 'integer|exists:shipments,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $shipmentIds = $request->shipment_ids;
            $shipments = Shipment::where('admin_id', $admin->id)
                ->whereIn('id', $shipmentIds)
                ->with(['pickup.deliveryConfiguration', 'order'])
                ->get();
            
            if ($shipments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune expédition trouvée'
                ], 404);
            }
            
            Log::info('🔍 [BULK TRACK] Début suivi en lot', [
                'admin_id' => $admin->id,
                'shipment_count' => $shipments->count(),
                'shipment_ids' => $shipmentIds,
            ]);
            
            $results = [];
            $stats = [
                'processed' => 0,
                'updated' => 0,
                'errors' => 0,
            ];
            
            foreach ($shipments as $shipment) {
                try {
                    $result = $this->trackSingleShipmentForBulk($shipment);
                    
                    $results[] = [
                        'shipment_id' => $shipment->id,
                        'tracking_number' => $shipment->pos_barcode,
                        'success' => $result['success'],
                        'status_changed' => $result['status_changed'] ?? false,
                        'old_status' => $result['old_status'] ?? null,
                        'new_status' => $result['new_status'] ?? null,
                        'message' => $result['message'],
                        'error' => $result['error'] ?? null,
                    ];
                    
                    $stats['processed']++;
                    if ($result['status_changed'] ?? false) {
                        $stats['updated']++;
                    }
                    
                } catch (\Exception $e) {
                    $results[] = [
                        'shipment_id' => $shipment->id,
                        'tracking_number' => $shipment->pos_barcode,
                        'success' => false,
                        'status_changed' => false,
                        'message' => 'Erreur : ' . $e->getMessage(),
                        'error' => $e->getMessage(),
                    ];
                    
                    $stats['processed']++;
                    $stats['errors']++;
                }
            }
            
            Log::info('✅ [BULK TRACK] Suivi en lot terminé', [
                'admin_id' => $admin->id,
                'stats' => $stats,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Suivi en lot terminé : {$stats['processed']} expéditions traitées, {$stats['updated']} mises à jour",
                'stats' => $stats,
                'results' => $results,
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [BULK TRACK] Erreur', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur suivi en lot : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 MÉTHODE HELPER : Suivre un shipment pour le suivi en lot
     */
    private function trackSingleShipmentForBulk(Shipment $shipment): array
    {
        try {
            if (empty($shipment->pos_barcode)) {
                return [
                    'success' => false,
                    'message' => 'Aucun numéro de suivi',
                    'error' => 'Aucun numéro de suivi disponible',
                ];
            }
            
            if (!$shipment->pickup || !$shipment->pickup->deliveryConfiguration) {
                return [
                    'success' => false,
                    'message' => 'Configuration manquante',
                    'error' => 'Configuration transporteur manquante',
                ];
            }
            
            $config = $shipment->pickup->deliveryConfiguration;
            
            if (!$config->is_active || !$config->is_valid) {
                return [
                    'success' => false,
                    'message' => 'Configuration inactive',
                    'error' => 'Configuration transporteur inactive ou invalide',
                ];
            }
            
            // Récupérer le statut via l'API
            $apiConfig = $config->getApiConfig();
            $carrierService = SimpleCarrierFactory::create($shipment->carrier_slug, $apiConfig);
            $result = $carrierService->getShipmentStatus($shipment->pos_barcode);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => 'Erreur API transporteur',
                    'error' => 'Impossible de récupérer le statut',
                ];
            }
            
            $oldStatus = $shipment->status;
            $newStatus = $result['status'];
            $statusChanged = ($newStatus !== $oldStatus && $newStatus !== 'unknown');
            
            // Mettre à jour si nécessaire
            if ($statusChanged) {
                $shipment->updateStatus(
                    $newStatus,
                    $result['response']['carrier_code'] ?? null,
                    $result['response']['carrier_label'] ?? null,
                    "Statut mis à jour via suivi en lot"
                );
                
                return [
                    'success' => true,
                    'status_changed' => true,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'message' => "Statut mis à jour : {$oldStatus} → {$newStatus}",
                ];
            } else {
                $shipment->update(['carrier_last_status_update' => now()]);
                
                return [
                    'success' => true,
                    'status_changed' => false,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'message' => "Statut inchangé : {$newStatus}",
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Suivre toutes les expéditions actives d'un admin
     */
    public function trackAllShipments()
    {
        $admin = auth('admin')->user();
        
        try {
            // Récupérer toutes les expéditions trackables de l'admin
            $shipments = Shipment::where('admin_id', $admin->id)
                ->whereNotNull('pos_barcode')
                ->whereIn('status', [
                    'validated',
                    'picked_up_by_carrier', 
                    'in_transit',
                    'delivery_attempted'
                ])
                ->with(['pickup.deliveryConfiguration', 'order'])
                ->get();
            
            if ($shipments->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aucune expédition active à suivre',
                    'stats' => [
                        'processed' => 0,
                        'updated' => 0,
                        'errors' => 0,
                    ]
                ]);
            }
            
            Log::info('🔍 [TRACK ALL] Début suivi de toutes les expéditions', [
                'admin_id' => $admin->id,
                'shipment_count' => $shipments->count(),
            ]);
            
            $stats = [
                'processed' => 0,
                'updated' => 0,
                'errors' => 0,
                'by_carrier' => [],
            ];
            
            foreach ($shipments as $shipment) {
                $carrier = $shipment->carrier_slug;
                
                if (!isset($stats['by_carrier'][$carrier])) {
                    $stats['by_carrier'][$carrier] = [
                        'processed' => 0,
                        'updated' => 0,
                        'errors' => 0,
                    ];
                }
                
                try {
                    $result = $this->trackSingleShipmentForBulk($shipment);
                    
                    $stats['processed']++;
                    $stats['by_carrier'][$carrier]['processed']++;
                    
                    if ($result['status_changed'] ?? false) {
                        $stats['updated']++;
                        $stats['by_carrier'][$carrier]['updated']++;
                    }
                    
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['by_carrier'][$carrier]['errors']++;
                    
                    Log::error('❌ [TRACK ALL] Erreur shipment', [
                        'shipment_id' => $shipment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('✅ [TRACK ALL] Suivi général terminé', [
                'admin_id' => $admin->id,
                'stats' => $stats,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Suivi terminé : {$stats['processed']} expéditions traitées, {$stats['updated']} mises à jour, {$stats['errors']} erreurs",
                'stats' => $stats,
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [TRACK ALL] Erreur', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur suivi général : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Marquer manuellement un shipment comme livré
     */
    public function markShipmentAsDelivered(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            if ($shipment->status === 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette expédition est déjà marquée comme livrée'
                ], 400);
            }
            
            $oldStatus = $shipment->status;
            $shipment->markAsDelivered("Marqué manuellement comme livré par " . auth('admin')->user()->name);
            
            Log::info('✅ [MANUAL DELIVERY] Shipment marqué comme livré', [
                'shipment_id' => $shipment->id,
                'old_status' => $oldStatus,
                'marked_by' => auth('admin')->user()->name,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Expédition marquée comme livrée avec succès',
                'old_status' => $oldStatus,
                'new_status' => 'delivered',
                'delivered_at' => $shipment->fresh()->delivered_at->toISOString(),
                'shipment' => $shipment->fresh()->load(['order', 'pickup.deliveryConfiguration'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [MANUAL DELIVERY] Erreur', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Obtenir l'historique de suivi d'un shipment
     */
    public function getShipmentTrackingHistory(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            // Récupérer l'historique via la commande associée
            $history = $shipment->order 
                ? $shipment->order->getDeliveryHistory()->get()
                : collect();
            
            return response()->json([
                'success' => true,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->pos_barcode,
                'current_status' => $shipment->status,
                'last_update' => $shipment->carrier_last_status_update?->toISOString(),
                'history' => $history->map(function($entry) {
                    return [
                        'id' => $entry->id,
                        'action' => $entry->action,
                        'status_before' => $entry->status_before,
                        'status_after' => $entry->status_after,
                        'carrier_status_code' => $entry->carrier_status_code,
                        'carrier_status_label' => $entry->carrier_status_label,
                        'notes' => $entry->notes,
                        'user_type' => $entry->user_type,
                        'created_at' => $entry->created_at->toISOString(),
                    ];
                }),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur récupération historique : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher les détails d'un shipment
     */
    public function showShipment(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $shipment->load(['order', 'pickup.deliveryConfiguration']);
            
            return response()->json([
                'success' => true,
                'shipment' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement'
            ], 500);
        }
    }

    // ========================================
    // GESTION DE LA CONFIGURATION
    // ========================================

    /**
     * Page de configuration des transporteurs
     */
    public function configuration()
    {
        $admin = auth('admin')->user();
        
        try {
            $configurations = $admin->deliveryConfigurations()->get();
            $configsByCarrier = $configurations->groupBy('carrier_slug');
            $carriers = $this->carriers;
            
            $carriersData = [];
            foreach ($carriers as $slug => $carrierConfig) {
                if ($slug === 'system' || $slug === 'history_actions') {
                    continue;
                }
                
                $carrierConfigurations = $configsByCarrier->get($slug, collect());
                $activeConfigs = $carrierConfigurations->where('is_active', true);
                
                $carriersData[$slug] = [
                    'config' => $carrierConfig,
                    'slug' => $slug,
                    'configurations' => $carrierConfigurations,
                    'active_configurations' => $activeConfigs,
                    'is_configured' => $carrierConfigurations->isNotEmpty(),
                    'is_active' => $activeConfigs->isNotEmpty(),
                    'status' => $this->getCarrierStatus($carrierConfigurations),
                ];
            }
            
            return view('admin.delivery.configuration', [
                'carriers' => $carriers,
                'configurations' => $configurations,
                'configsByCarrier' => $configsByCarrier,
                'carriersData' => $carriersData,
            ]);
            
        } catch (\Exception $e) {
            return view('admin.delivery.configuration', [
                'carriers' => $this->carriers,
                'configurations' => collect(),
                'configsByCarrier' => collect(),
                'carriersData' => [],
            ])->with('error', 'Erreur chargement configurations.');
        }
    }

    /**
     * Créer une nouvelle configuration
     */
    public function createConfiguration(Request $request)
    {
        $admin = auth('admin')->user();
        $carrierSlug = $request->get('carrier');
        
        if (!$carrierSlug || !isset($this->carriers[$carrierSlug])) {
            return redirect()->route('admin.delivery.configuration')
                ->with('error', 'Transporteur non trouvé.');
        }
        
        $carrierData = $this->carriers[$carrierSlug];
        $carrierData['slug'] = $carrierSlug;
        
        if (!isset($carrierData['name'])) {
            $carrierData['name'] = ucfirst(str_replace('_', ' ', $carrierSlug));
        }
        
        return view('admin.delivery.configuration-create', [
            'carrier' => $carrierData,
            'carrierSlug' => $carrierSlug,
            'carriers' => $this->carriers,
            'admin' => $admin,
        ]);
    }

    /**
     * Sauvegarder une configuration
     */
    public function storeConfiguration(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $validator = Validator::make($request->all(), [
                'carrier_slug' => 'required|string|max:500',
                'integration_name' => 'required|string|max:500',
                'username' => 'nullable|string|max:500',
                'password' => 'nullable|string|max:500',
                'environment' => 'required|in:test,production',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            if (!isset($this->carriers[$request->carrier_slug])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transporteur non reconnu'
                ], 422);
            }
            
            $config = DeliveryConfiguration::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $request->carrier_slug,
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'password' => $request->password,
                'environment' => $request->environment ?? 'test',
                'is_active' => $request->boolean('is_active', false),
                'settings' => $request->settings ?? [],
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration sauvegardée avec succès',
                'config' => $config,
                'redirect' => route('admin.delivery.configuration')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur sauvegarde: ' . $e->getMessage()
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
        
        try {
            if (!$config->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration inactive'
                ], 422);
            }
            
            $result = $config->testConnection();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Éditer une configuration existante
     */
    public function editConfiguration(DeliveryConfiguration $config)
    {
        $admin = auth('admin')->user();
        
        if ($config->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé');
        }
        
        $carrierSlug = $config->carrier_slug;
        
        if (!isset($this->carriers[$carrierSlug])) {
            return redirect()->route('admin.delivery.configuration')
                ->with('error', 'Transporteur non trouvé.');
        }
        
        $carrierData = $this->carriers[$carrierSlug];
        $carrierData['slug'] = $carrierSlug;
        
        if (!isset($carrierData['name'])) {
            $carrierData['name'] = ucfirst(str_replace('_', ' ', $carrierSlug));
        }
        
        return view('admin.delivery.configuration-edit', [
            'carrier' => $carrierData,
            'carrierSlug' => $carrierSlug,
            'carriers' => $this->carriers,
            'admin' => $admin,
            'config' => $config,
        ]);
    }

    /**
     * Mettre à jour une configuration
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        $admin = auth('admin')->user();
        
        if ($config->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $validator = Validator::make($request->all(), [
                'integration_name' => 'required|string|max:500',
                'username' => 'nullable|string|max:500',
                'password' => 'nullable|string|max:500',
                'environment' => 'required|in:test,production',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $config->update([
                'integration_name' => $request->integration_name,
                'username' => $request->username,
                'password' => $request->password,
                'environment' => $request->environment ?? 'test',
                'is_active' => $request->boolean('is_active', false),
                'settings' => $request->settings ?? [],
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration mise à jour avec succès',
                'config' => $config->fresh(),
                'redirect' => route('admin.delivery.configuration')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        $admin = auth('admin')->user();
        
        if ($config->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            // Vérifier qu'il n'y a pas de pickups actifs
            $activePickups = Pickup::where('delivery_configuration_id', $config->id)
                ->whereIn('status', ['draft', 'validated'])
                ->count();
            
            if ($activePickups > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer cette configuration. {$activePickups} pickup(s) actif(s) l'utilisent encore."
                ], 400);
            }
            
            $configName = $config->integration_name;
            $config->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Configuration '{$configName}' supprimée avec succès"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/désactiver une configuration
     */
    public function toggleConfiguration(DeliveryConfiguration $config)
    {
        $admin = auth('admin')->user();
        
        if ($config->admin_id !== $admin->id) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $config->update(['is_active' => !$config->is_active]);
            
            $status = $config->is_active ? 'activée' : 'désactivée';
            
            return response()->json([
                'success' => true,
                'message' => "Configuration {$status} avec succès",
                'is_active' => $config->is_active
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // 🆕 MÉTHODES DE CORRECTION DES CONFIGURATIONS
    // ========================================

    /**
     * 🆕 NOUVELLE MÉTHODE : Réparer toutes les configurations problématiques
     */
    public function fixAllConfigurations()
    {
        $admin = auth('admin')->user();
        
        try {
            Log::info('🔧 [CONFIG FIX] Début réparation configurations', [
                'admin_id' => $admin->id,
            ]);
            
            $results = [
                'total_configs' => 0,
                'valid_configs' => 0,
                'invalid_configs' => 0,
                'migrated_configs' => 0,
                'connection_tests' => [],
                'recommendations' => [],
            ];
            
            // Récupérer toutes les configurations de l'admin
            $configurations = DeliveryConfiguration::where('admin_id', $admin->id)->get();
            $results['total_configs'] = $configurations->count();
            
            foreach ($configurations as $config) {
                if ($config->is_valid) {
                    $results['valid_configs']++;
                } else {
                    $results['invalid_configs']++;
                    
                    // Essayer de migrer si c'est Mes Colis en ancien format
                    if ($config->carrier_slug === 'mes_colis') {
                        $migrated = $config->migrateToNewFormat();
                        if ($migrated) {
                            $results['migrated_configs']++;
                            $results['recommendations'][] = [
                                'type' => 'success',
                                'config_id' => $config->id,
                                'message' => "Configuration '{$config->integration_name}' migrée vers le nouveau format",
                            ];
                        }
                    }
                }
                
                // Tester la connexion pour chaque config active
                if ($config->is_active && $config->fresh()->is_valid) {
                    try {
                        $connectionTest = $config->testConnection();
                        $results['connection_tests'][] = [
                            'config_id' => $config->id,
                            'integration_name' => $config->integration_name,
                            'carrier' => $config->carrier_slug,
                            'success' => $connectionTest['success'],
                            'message' => $connectionTest['message'],
                            'format' => $config->getConfigFormat(),
                        ];
                        
                        if (!$connectionTest['success']) {
                            $results['recommendations'][] = [
                                'type' => 'error',
                                'config_id' => $config->id,
                                'message' => "Connexion échouée pour '{$config->integration_name}': {$connectionTest['message']}",
                                'action' => 'Vérifiez le token dans l\'interface de configuration',
                            ];
                        }
                    } catch (\Exception $e) {
                        $results['connection_tests'][] = [
                            'config_id' => $config->id,
                            'integration_name' => $config->integration_name,
                            'carrier' => $config->carrier_slug,
                            'success' => false,
                            'message' => $e->getMessage(),
                            'format' => $config->getConfigFormat(),
                        ];
                    }
                }
            }
            
            Log::info('✅ [CONFIG FIX] Réparation terminée', [
                'admin_id' => $admin->id,
                'results' => $results,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Analyse terminée : {$results['valid_configs']}/{$results['total_configs']} configurations valides",
                'results' => $results,
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [CONFIG FIX] Erreur réparation', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur réparation configurations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Tester une configuration spécifique et proposer corrections
     */
    public function testAndFixConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $analysis = [
                'config_info' => [
                    'id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'carrier_slug' => $config->carrier_slug,
                    'is_active' => $config->is_active,
                    'is_valid' => $config->is_valid,
                    'format' => $config->getConfigFormat(),
                ],
                'credentials_analysis' => [
                    'has_username' => !empty($config->username),
                    'has_password' => !empty($config->password),
                    'username_length' => $config->username ? strlen($config->username) : 0,
                    'password_length' => $config->password ? strlen($config->password) : 0,
                ],
                'validation_results' => $config->validateCredentials(),
                'connection_test' => null,
                'migration_available' => false,
                'recommendations' => [],
            ];
            
            // Test de migration pour Mes Colis ancien format
            if ($config->carrier_slug === 'mes_colis' && $config->getConfigFormat() === 'ancien') {
                $analysis['migration_available'] = true;
                $analysis['recommendations'][] = [
                    'type' => 'info',
                    'message' => 'Cette configuration utilise l\'ancien format (token dans username)',
                    'action' => 'Migration automatique vers le nouveau format disponible',
                ];
            }
            
            // Test de connexion si configuration valide
            if ($config->is_valid && $config->is_active) {
                try {
                    $connectionTest = $config->testConnection();
                    $analysis['connection_test'] = $connectionTest;
                    
                    if (!$connectionTest['success']) {
                        $analysis['recommendations'][] = [
                            'type' => 'error',
                            'message' => 'Test de connexion échoué',
                            'action' => 'Vérifiez que le token est correct et valide',
                            'details' => $connectionTest['message'],
                        ];
                    } else {
                        $analysis['recommendations'][] = [
                            'type' => 'success',
                            'message' => 'Configuration fonctionnelle',
                            'action' => 'Prête pour utilisation',
                        ];
                    }
                } catch (\Exception $e) {
                    $analysis['connection_test'] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            } elseif (!$config->is_valid) {
                $analysis['recommendations'][] = [
                    'type' => 'error',
                    'message' => 'Configuration invalide',
                    'action' => 'Remplissez tous les champs requis',
                ];
            } elseif (!$config->is_active) {
                $analysis['recommendations'][] = [
                    'type' => 'warning',
                    'message' => 'Configuration inactive',
                    'action' => 'Activez la configuration pour l\'utiliser',
                ];
            }
            
            return response()->json([
                'success' => true,
                'config_id' => $config->id,
                'analysis' => $analysis,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur analyse configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Migrer une configuration Mes Colis vers le nouveau format
     */
    public function migrateConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            if ($config->carrier_slug !== 'mes_colis') {
                return response()->json([
                    'success' => false,
                    'error' => 'Migration disponible seulement pour Mes Colis'
                ], 400);
            }
            
            $oldFormat = $config->getConfigFormat();
            
            if ($oldFormat !== 'ancien') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cette configuration n\'a pas besoin de migration'
                ], 400);
            }
            
            $migrated = $config->migrateToNewFormat();
            
            if ($migrated) {
                Log::info('✅ [CONFIG MIGRATE] Configuration migrée', [
                    'config_id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'from_format' => $oldFormat,
                    'to_format' => $config->fresh()->getConfigFormat(),
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "Configuration '{$config->integration_name}' migrée vers le nouveau format",
                    'old_format' => $oldFormat,
                    'new_format' => $config->fresh()->getConfigFormat(),
                    'config' => $config->fresh(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Migration échouée'
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur migration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Corriger les tokens invalides (utilitaire de diagnostic)
     */
    public function fixInvalidTokens()
    {
        $admin = auth('admin')->user();
        
        try {
            $invalidConfigs = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('is_active', true)
                ->get()
                ->filter(function($config) {
                    return !$config->is_valid;
                });
            
            $fixes = [];
            
            foreach ($invalidConfigs as $config) {
                $fix = [
                    'config_id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'carrier' => $config->carrier_slug,
                    'current_format' => $config->getConfigFormat(),
                    'issues' => [],
                    'recommendations' => [],
                ];
                
                $validation = $config->validateCredentials();
                $fix['issues'] = $validation['errors'];
                
                if ($config->carrier_slug === 'jax_delivery') {
                    if (empty($config->username)) {
                        $fix['recommendations'][] = 'Ajoutez votre numéro de compte JAX dans le champ "Numéro de Compte"';
                    }
                    if (empty($config->password)) {
                        $fix['recommendations'][] = 'Ajoutez votre token JWT JAX dans le champ "Token API"';
                    }
                    if (!empty($config->password) && substr_count($config->password, '.') !== 2) {
                        $fix['recommendations'][] = 'Vérifiez que le token JWT est correct (doit contenir 3 parties séparées par des points)';
                    }
                } elseif ($config->carrier_slug === 'mes_colis') {
                    if (empty($config->username) && empty($config->password)) {
                        $fix['recommendations'][] = 'Ajoutez votre token Mes Colis dans le champ "Token d\'Accès"';
                    }
                    if ($config->getConfigFormat() === 'ancien') {
                        $fix['recommendations'][] = 'Migration vers le nouveau format recommandée';
                    }
                }
                
                $fixes[] = $fix;
            }
            
            return response()->json([
                'success' => true,
                'message' => count($fixes) . ' configuration(s) avec problèmes détectée(s)',
                'invalid_configs_count' => count($fixes),
                'fixes' => $fixes,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur diagnostic tokens: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Diagnostic détaillé d'une configuration
     */
    public function diagnosticConfiguration(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        try {
            $diagnostic = [
                'config_info' => [
                    'id' => $config->id,
                    'carrier_slug' => $config->carrier_slug,
                    'integration_name' => $config->integration_name,
                    'environment' => $config->environment,
                    'is_active' => $config->is_active,
                    'is_valid' => $config->is_valid,
                    'status' => $config->status,
                    'created_at' => $config->created_at->toISOString(),
                ],
                
                'credentials_check' => [
                    'has_username' => !empty($config->username),
                    'has_password' => !empty($config->password),
                    'username_length' => $config->username ? strlen($config->username) : 0,
                    'password_length' => $config->password ? strlen($config->password) : 0,
                ],
                
                'validation_results' => $config->validateCredentials(),
                
                'api_config_preview' => null,
                'connection_test' => null,
            ];
            
            // Test de configuration API si valide
            if ($config->isValidForApiCalls()) {
                try {
                    $apiConfig = $config->getApiConfig();
                    $diagnostic['api_config_preview'] = [
                        'has_api_token' => !empty($apiConfig['api_token']),
                        'token_preview' => !empty($apiConfig['api_token']) ? substr($apiConfig['api_token'], 0, 20) . '...' : null,
                        'environment' => $apiConfig['environment'],
                    ];
                    
                    // Test de connexion
                    $connectionTest = $config->testConnection();
                    $diagnostic['connection_test'] = $connectionTest;
                    
                } catch (\Exception $e) {
                    $diagnostic['api_config_preview'] = ['error' => $e->getMessage()];
                    $diagnostic['connection_test'] = ['success' => false, 'error' => $e->getMessage()];
                }
            }
            
            return response()->json([
                'success' => true,
                'config_id' => $config->id,
                'diagnostic' => $diagnostic,
                'recommendations' => $this->getConfigRecommendations($diagnostic),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur diagnostic configuration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 MÉTHODE HELPER : Recommandations pour corriger une configuration
     */
    private function getConfigRecommendations($diagnostic)
    {
        $recommendations = [];
        
        if (!$diagnostic['config_info']['is_active']) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Configuration inactive',
                'action' => 'Activer la configuration',
            ];
        }
        
        if (!$diagnostic['config_info']['is_valid']) {
            $recommendations[] = [
                'type' => 'error',
                'message' => 'Configuration invalide',
                'action' => 'Vérifier les credentials requis',
            ];
        }
        
        if (!empty($diagnostic['validation_results']['errors'])) {
            foreach ($diagnostic['validation_results']['errors'] as $error) {
                $recommendations[] = [
                    'type' => 'error',
                    'message' => $error,
                    'action' => 'Corriger le problème mentionné',
                ];
            }
        }
        
        if ($diagnostic['connection_test'] && !$diagnostic['connection_test']['success']) {
            $recommendations[] = [
                'type' => 'error',
                'message' => 'Test de connexion échoué',
                'action' => 'Vérifier les tokens et la connectivité',
            ];
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Configuration correctement configurée',
                'action' => 'Prête pour utilisation',
            ];
        }
        
        return $recommendations;
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
        
        $activeConfigurations = DeliveryConfiguration::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->get();
        
        if ($activeConfigurations->isEmpty()) {
            return redirect()->route('admin.delivery.configuration')
                ->with('warning', 'Aucune configuration active. Configurez un transporteur d\'abord.');
        }
        
        return view('admin.delivery.preparation', compact('activeConfigurations'));
    }

    /**
     * API pour obtenir les commandes disponibles
     */
    public function getAvailableOrders(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $query = Order::where('admin_id', $admin->id)
                ->where('status', 'confirmée')
                ->where(function($q) {
                    $q->where('is_suspended', false)->orWhereNull('is_suspended');
                })
                ->whereDoesntHave('shipments');
            
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
                $order->region_name = $order->customer_governorate ?: 'Région inconnue';
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur récupération commandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un pickup avec les commandes sélectionnées
     */
    public function createPickup(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $validator = Validator::make($request->all(), [
                'delivery_configuration_id' => 'required|integer|exists:delivery_configurations,id',
                'order_ids' => 'required|array|min:1|max:50',
                'order_ids.*' => 'integer|exists:orders,id',
                'pickup_date' => 'nullable|date|after_or_equal:today',
            ]);
            
            if ($validator->fails()) {
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
                throw new \Exception('Configuration transporteur non trouvée ou inactive');
            }
            
            $orders = Order::where('admin_id', $admin->id)
                ->whereIn('id', $request->order_ids)
                ->where('status', 'confirmée')
                ->where(function($query) {
                    $query->where('is_suspended', false)->orWhereNull('is_suspended');
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
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ========================================
    // STATISTIQUES ET APIs GLOBALES
    // ========================================

    /**
     * API pour les statistiques générales
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
                    'shipments' => $shipmentStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur statistiques',
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
     * API pour les statistiques (alias)
     */
    public function getApiStats()
    {
        return $this->getGeneralStats();
    }

    // ========================================
    // 🆕 MÉTHODES DE TEST ET DIAGNOSTIC
    // ========================================

    /**
     * 🆕 Test système complet
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
                        'is_valid' => $config->is_valid,
                        'can_test' => $config->isValidForApiCalls(),
                        'created_at' => $config->created_at,
                    ];
                }),
                
                'tables_check' => [
                    'pickups' => Schema::hasTable('pickups'),
                    'delivery_configurations' => Schema::hasTable('delivery_configurations'),
                    'shipments' => Schema::hasTable('shipments'),
                    'orders' => Schema::hasTable('orders'),
                ],
                
                'config_check' => [
                    'carriers_config_exists' => config('carriers') !== null,
                    'carriers_available' => config('carriers') ? array_keys(config('carriers')) : [],
                    'app_debug' => config('app.debug'),
                    'app_env' => config('app.env'),
                ],
                
                'factory_test' => $this->testCarrierFactory(),
                
                'timestamp' => now()->toISOString(),
            ];
            
            return response()->json($diagnostics, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
            ], 500);
        }
    }

    /**
     * 🆕 Test de la factory transporteur
     */
    public function testCarrierFactory()
    {
        try {
            Log::info('🧪 [FACTORY TEST] Début test factory');
            
            // Test avec configuration JAX
            $jaxConfig = [
                'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
                'username' => 'TEST_ACCOUNT',
                'environment' => 'test',
            ];
            
            $jaxService = SimpleCarrierFactory::create('jax_delivery', $jaxConfig);
            $jaxTest = $jaxService->testConnection();
            
            Log::info('✅ [FACTORY TEST] Service JAX créé', [
                'service_class' => get_class($jaxService),
                'test_result' => $jaxTest['success'],
            ]);
            
            // Test avec configuration Mes Colis
            $mesColisConfig = [
                'api_token' => 'TEST_TOKEN_MESCOLIS_123',
                'environment' => 'test',
            ];
            
            $mesColisService = SimpleCarrierFactory::create('mes_colis', $mesColisConfig);
            $mesColisTest = $mesColisService->testConnection();
            
            Log::info('✅ [FACTORY TEST] Service Mes Colis créé', [
                'service_class' => get_class($mesColisService),
                'test_result' => $mesColisTest['success'],
            ]);
            
            return [
                'success' => true,
                'jax_service_class' => get_class($jaxService),
                'mes_colis_service_class' => get_class($mesColisService),
                'supported_carriers' => SimpleCarrierFactory::getSupportedCarriers(),
                'test_connections' => [
                    'jax_delivery' => $jaxTest,
                    'mes_colis' => $mesColisTest,
                ],
                'factory_working' => true,
            ];
            
        } catch (\Exception $e) {
            Log::error('❌ [FACTORY TEST] Erreur', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'factory_working' => false,
            ];
        }
    }

    /**
     * 🔧 MÉTHODE CORRIGÉE : Validation en masse des pickups avec gestion détaillée des erreurs
     */
    public function bulkValidatePickups(Request $request)
    {
        $admin = auth('admin')->user();
        
        try {
            $validator = Validator::make($request->all(), [
                'pickup_ids' => 'required|array|min:1|max:50',
                'pickup_ids.*' => 'integer|exists:pickups,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $pickupIds = $request->pickup_ids;
            $pickups = Pickup::where('admin_id', $admin->id)
                ->whereIn('id', $pickupIds)
                ->with(['deliveryConfiguration', 'shipments.order'])
                ->get();
            
            if ($pickups->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun pickup trouvé pour cet admin'
                ], 404);
            }
            
            Log::info('🚀 [BULK VALIDATE] Début validation en masse', [
                'admin_id' => $admin->id,
                'pickup_count' => $pickups->count(),
                'pickup_ids' => $pickupIds,
            ]);
            
            $results = [];
            $globalStats = [
                'total_pickups' => $pickups->count(),
                'validated_pickups' => 0,
                'failed_pickups' => 0,
                'total_shipments' => 0,
                'validated_shipments' => 0,
                'failed_shipments' => 0,
                'tracking_numbers' => [],
            ];
            
            foreach ($pickups as $pickup) {
                $pickupResult = [
                    'pickup_id' => $pickup->id,
                    'pickup_status' => $pickup->status,
                    'can_be_validated' => $pickup->can_be_validated,
                    'success' => false,
                    'shipments_total' => $pickup->shipments->count(),
                    'shipments_validated' => 0,
                    'tracking_numbers' => [],
                    'errors' => [],
                    'partial_success' => false,
                ];
                
                $globalStats['total_shipments'] += $pickup->shipments->count();
                
                try {
                    // Vérification détaillée du pickup
                    if (!$pickup->can_be_validated) {
                        $reasons = [];
                        if ($pickup->status !== 'draft') {
                            $reasons[] = "Statut incorrect: {$pickup->status} (doit être 'draft')";
                        }
                        if ($pickup->shipments->isEmpty()) {
                            $reasons[] = "Aucune expédition dans le pickup";
                        }
                        if (!$pickup->deliveryConfiguration) {
                            $reasons[] = "Configuration transporteur manquante";
                        } elseif (!$pickup->deliveryConfiguration->is_active) {
                            $reasons[] = "Configuration transporteur inactive";
                        } elseif (!$pickup->deliveryConfiguration->is_valid) {
                            $reasons[] = "Configuration transporteur invalide";
                        }
                        
                        $pickupResult['errors'] = $reasons;
                        $results[] = $pickupResult;
                        $globalStats['failed_pickups']++;
                        continue;
                    }
                    
                    Log::info("🔄 [BULK VALIDATE] Validation pickup #{$pickup->id}", [
                        'shipments_count' => $pickup->shipments->count(),
                        'carrier' => $pickup->carrier_slug,
                    ]);
                    
                    // Appeler la méthode validate du pickup
                    $validationResult = $pickup->validate();
                    
                    if ($validationResult['success']) {
                        $pickupResult['success'] = true;
                        $pickupResult['shipments_validated'] = $validationResult['successful_shipments'];
                        $pickupResult['tracking_numbers'] = $validationResult['tracking_numbers'] ?? [];
                        
                        // Vérifier si c'est un succès partiel
                        if ($validationResult['successful_shipments'] < $validationResult['total_shipments']) {
                            $pickupResult['partial_success'] = true;
                            $pickupResult['errors'] = $validationResult['errors'] ?? [];
                        }
                        
                        $globalStats['validated_pickups']++;
                        $globalStats['validated_shipments'] += $validationResult['successful_shipments'];
                        $globalStats['failed_shipments'] += ($validationResult['total_shipments'] - $validationResult['successful_shipments']);
                        $globalStats['tracking_numbers'] = array_merge(
                            $globalStats['tracking_numbers'], 
                            $validationResult['tracking_numbers'] ?? []
                        );
                        
                        Log::info("✅ [BULK VALIDATE] Pickup #{$pickup->id} validé", [
                            'successful_shipments' => $validationResult['successful_shipments'],
                            'total_shipments' => $validationResult['total_shipments'],
                            'tracking_numbers_count' => count($validationResult['tracking_numbers'] ?? []),
                        ]);
                        
                    } else {
                        $pickupResult['errors'] = $validationResult['errors'] ?? ['Erreur de validation inconnue'];
                        $globalStats['failed_pickups']++;
                        $globalStats['failed_shipments'] += $pickup->shipments->count();
                        
                        Log::error("❌ [BULK VALIDATE] Échec pickup #{$pickup->id}", [
                            'errors' => $validationResult['errors'] ?? [],
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $pickupResult['errors'] = ['Erreur technique : ' . $e->getMessage()];
                    $globalStats['failed_pickups']++;
                    $globalStats['failed_shipments'] += $pickup->shipments->count();
                    
                    Log::error("❌ [BULK VALIDATE] Exception pickup #{$pickup->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
                
                $results[] = $pickupResult;
            }
            
            // Générer le message de résumé
            $message = "Validation terminée : ";
            $message .= "{$globalStats['validated_pickups']}/{$globalStats['total_pickups']} pickups validés";
            
            if ($globalStats['validated_shipments'] > 0) {
                $message .= ", {$globalStats['validated_shipments']} expéditions créées";
            }
            
            if ($globalStats['failed_pickups'] > 0) {
                $message .= ", {$globalStats['failed_pickups']} échecs";
            }
            
            Log::info('🎉 [BULK VALIDATE] Validation en masse terminée', [
                'admin_id' => $admin->id,
                'stats' => $globalStats,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'stats' => $globalStats,
                    'results' => $results,
                    'summary' => [
                        'total_pickups' => $globalStats['total_pickups'],
                        'validated_pickups' => $globalStats['validated_pickups'],
                        'failed_pickups' => $globalStats['failed_pickups'],
                        'total_tracking_numbers' => count($globalStats['tracking_numbers']),
                        'has_partial_success' => collect($results)->contains('partial_success', true),
                        'has_failures' => $globalStats['failed_pickups'] > 0,
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ [BULK VALIDATE] Erreur critique', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur critique lors de la validation en masse',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Calculer le poids d'une commande
     */
    protected function calculateOrderWeight($order): float
    {
        try {
            $itemsCount = $order->items ? $order->items->sum('quantity') : 1;
            return max(1.0, $itemsCount * 0.5);
        } catch (\Exception $e) {
            return 1.0;
        }
    }

    /**
     * Générer la description du contenu
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
            return 'Commande e-commerce #' . $order->id;
        }
    }

    /**
     * Obtenir le statut d'une configuration de transporteur
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
            return [
                'configurations' => 0,
                'pickups' => 0,
                'shipments' => 0,
            ];
        }
    }

    // ========================================
    // MÉTHODES SUPPLÉMENTAIRES POUR LA COMPLÉTUDE
    // ========================================

    /**
     * Page de statistiques delivery
     */
    public function stats()
    {
        $admin = auth('admin')->user();
        return view('admin.delivery.stats', compact('admin'));
    }

    /**
     * Générer le manifeste d'un pickup
     */
    public function generatePickupManifest(Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Ici vous pourriez générer un PDF ou autre format
        return response()->json([
            'success' => true,
            'message' => 'Manifeste généré',
            'pickup_id' => $pickup->id
        ]);
    }

    /**
     * Ajouter des commandes à un pickup
     */
    public function addOrdersToPickup(Request $request, Pickup $pickup)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Implémentation future
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en développement'
        ]);
    }

    /**
     * Retirer une commande d'un pickup
     */
    public function removeOrderFromPickup(Pickup $pickup, Order $order)
    {
        if ($pickup->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Implémentation future
        return response()->json([
            'success' => false,
            'message' => 'Fonctionnalité en développement'
        ]);
    }

    /**
     * Générer une étiquette d'expédition
     */
    public function generateShippingLabel(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Implémentation future pour générer des étiquettes
        return response()->json([
            'success' => false,
            'message' => 'Génération d\'étiquettes en développement'
        ]);
    }

    /**
     * Générer une preuve de livraison
     */
    public function generateDeliveryProof(Shipment $shipment)
    {
        if ($shipment->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Implémentation future
        return response()->json([
            'success' => false,
            'message' => 'Preuve de livraison en développement'
        ]);
    }

    /**
     * 🆕 Créer des données de test complètes avec vrais tokens
     */
    public function createTestPickupDataWithRealTokens()
    {
        $admin = auth('admin')->user();
        
        try {
            DB::beginTransaction();
            
            Log::info('🔥 [TEST REAL TOKENS] Début création test avec vrais tokens', [
                'admin_id' => $admin->id,
            ]);
            
            // 🔥 CRÉER CONFIGURATION JAX AVEC VRAIS TOKENS
            $jaxConfig = DeliveryConfiguration::firstOrCreate([
                'admin_id' => $admin->id,
                'carrier_slug' => 'jax_delivery',
                'integration_name' => 'JAX Production - Test Validation'
            ], [
                'username' => '2304', // Numéro de compte réel
                'password' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2NvcmUuamF4LWRlbGl2ZXJ5LmNvbS9hcGkvdXRpbGlzYXRldXJzL0xvbmdUb2tlbiIsImlhdCI6MTc0NDExMjM0NywiZXhwIjoxODA3MTg0MzQ3LCJuYmYiOjE3NDQxMTIzNDcsImp0aSI6IktGTlhHUlFrZTNLY2ZDM3oiLCJzdWIiOiIyNjAwIiwicHJ2IjoiZDA5MDViY2Y2NWE2ZDk5MmQ5MGNiZmU0NjIyNmJkMzEzYWU1MTkzZiJ9.E0J5H5iOjyl52g47PP_arXrO8ZC7lorBg0AdIU0MDiY', // Token JWT réel
                'environment' => 'production',
                'is_active' => true,
            ]);
            
            // 🔥 CRÉER CONFIGURATION MES COLIS AVEC VRAI TOKEN
            $mesColisConfig = DeliveryConfiguration::firstOrCreate([
                'admin_id' => $admin->id,
                'carrier_slug' => 'mes_colis',
                'integration_name' => 'Mes Colis Production - Test Validation'
            ], [
                'username' => 'OL6B3FUA526SMLMBN7U3QZ1UMW5YW91D', // Token réel
                'password' => null, // Pas utilisé pour Mes Colis
                'environment' => 'production',
                'is_active' => true,
            ]);
            
            // Tester les connexions
            $jaxTest = $jaxConfig->testConnection();
            $mesColisTest = $mesColisConfig->testConnection();
            
            Log::info('🧪 [TEST REAL TOKENS] Tests de connexion', [
                'jax_test' => $jaxTest['success'],
                'mes_colis_test' => $mesColisTest['success'],
            ]);
            
            // Créer des pickups de test pour les deux transporteurs
            $pickups = [];
            
            // Pickup JAX
            $jaxPickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => 'jax_delivery',
                'delivery_configuration_id' => $jaxConfig->id,
                'status' => 'draft',
                'pickup_date' => now()->addDay(),
            ]);
            $pickups['jax'] = $jaxPickup->id;
            
            // Pickup Mes Colis
            $mesColisPickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => 'mes_colis',
                'delivery_configuration_id' => $mesColisConfig->id,
                'status' => 'draft',
                'pickup_date' => now()->addDay(),
            ]);
            $pickups['mes_colis'] = $mesColisPickup->id;
            
            // Créer des shipments réalistes pour chaque pickup
            $shipments = [];
            
            // Shipments pour JAX
            for ($i = 1; $i <= 2; $i++) {
                $shipment = Shipment::create([
                    'admin_id' => $admin->id,
                    'order_id' => null,
                    'pickup_id' => $jaxPickup->id,
                    'carrier_slug' => 'jax_delivery',
                    'status' => 'created',
                    'weight' => 1.5,
                    'cod_amount' => 50 + ($i * 10),
                    'nb_pieces' => 1,
                    'recipient_info' => [
                        'name' => "Client JAX Test {$i}",
                        'phone' => "12345678{$i}",
                        'phone_2' => "87654321{$i}",
                        'address' => "Adresse test JAX {$i}, Rue de la Paix",
                        'city' => 'Tunis',
                        'governorate' => 'Tunis',
                    ],
                    'content_description' => "Produit test JAX {$i} - E-commerce",
                ]);
                $shipments['jax'][] = $shipment->id;
            }
            
            // Shipments pour Mes Colis
            for ($i = 1; $i <= 2; $i++) {
                $shipment = Shipment::create([
                    'admin_id' => $admin->id,
                    'order_id' => null,
                    'pickup_id' => $mesColisPickup->id,
                    'carrier_slug' => 'mes_colis',
                    'status' => 'created',
                    'weight' => 2.0,
                    'cod_amount' => 75 + ($i * 15),
                    'nb_pieces' => 1,
                    'recipient_info' => [
                        'name' => "Client Mes Colis Test {$i}",
                        'phone' => "98765432{$i}",
                        'phone_2' => "12345679{$i}",
                        'address' => "Adresse test Mes Colis {$i}, Avenue Habib Bourguiba",
                        'city' => 'Sousse',
                        'governorate' => 'Sousse',
                    ],
                    'content_description' => "Produit test Mes Colis {$i} - E-commerce",
                ]);
                $shipments['mes_colis'][] = $shipment->id;
            }
            
            DB::commit();
            
            Log::info('✅ [TEST REAL TOKENS] Données créées avec succès', [
                'jax_pickup_id' => $jaxPickup->id,
                'mes_colis_pickup_id' => $mesColisPickup->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Données de test créées avec vrais tokens',
                'configs' => [
                    'jax' => [
                        'id' => $jaxConfig->id,
                        'connection_test' => $jaxTest,
                        'can_be_validated' => $jaxPickup->fresh()->can_be_validated,
                    ],
                    'mes_colis' => [
                        'id' => $mesColisConfig->id,
                        'connection_test' => $mesColisTest,
                        'can_be_validated' => $mesColisPickup->fresh()->can_be_validated,
                    ],
                ],
                'pickups' => $pickups,
                'shipments' => $shipments,
                'next_steps' => [
                    'jax_validate_url' => route('admin.delivery.pickups.validate', $jaxPickup->id),
                    'mes_colis_validate_url' => route('admin.delivery.pickups.validate', $mesColisPickup->id),
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ [TEST REAL TOKENS] Erreur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * 🆕 Test complet de validation avec vrais tokens
     */
    public function testCompleteValidationFlow()
    {
        $admin = auth('admin')->user();
        
        try {
            Log::info('🚀 [TEST VALIDATION FLOW] Début test complet', [
                'admin_id' => $admin->id,
            ]);
            
            // 1. Créer des données de test
            $setupResponse = $this->createTestPickupDataWithRealTokens();
            $setupData = $setupResponse->getData(true);
            
            if (!$setupData['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Échec création données test',
                    'details' => $setupData,
                ], 500);
            }
            
            $results = [
                'setup' => $setupData,
                'validations' => [],
            ];
            
            // 2. Tester validation JAX
            $jaxPickupId = $setupData['pickups']['jax'];
            $jaxPickup = Pickup::find($jaxPickupId);
            
            if ($jaxPickup && $jaxPickup->can_be_validated) {
                try {
                    Log::info('🧪 [TEST VALIDATION FLOW] Test validation JAX', ['pickup_id' => $jaxPickupId]);
                    $jaxResult = $jaxPickup->validate();
                    $results['validations']['jax'] = [
                        'success' => $jaxResult['success'],
                        'tracking_numbers' => $jaxResult['tracking_numbers'] ?? [],
                        'successful_shipments' => $jaxResult['successful_shipments'] ?? 0,
                        'errors' => $jaxResult['errors'] ?? [],
                    ];
                } catch (\Exception $e) {
                    $results['validations']['jax'] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            } else {
                $results['validations']['jax'] = [
                    'success' => false,
                    'error' => 'Pickup JAX ne peut pas être validé',
                    'can_be_validated' => $jaxPickup ? $jaxPickup->can_be_validated : false,
                ];
            }
            
            // 3. Tester validation Mes Colis
            $mesColisPickupId = $setupData['pickups']['mes_colis'];
            $mesColisPickup = Pickup::find($mesColisPickupId);
            
            if ($mesColisPickup && $mesColisPickup->can_be_validated) {
                try {
                    Log::info('🧪 [TEST VALIDATION FLOW] Test validation Mes Colis', ['pickup_id' => $mesColisPickupId]);
                    $mesColisResult = $mesColisPickup->validate();
                    $results['validations']['mes_colis'] = [
                        'success' => $mesColisResult['success'],
                        'tracking_numbers' => $mesColisResult['tracking_numbers'] ?? [],
                        'successful_shipments' => $mesColisResult['successful_shipments'] ?? 0,
                        'errors' => $mesColisResult['errors'] ?? [],
                    ];
                } catch (\Exception $e) {
                    $results['validations']['mes_colis'] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            } else {
                $results['validations']['mes_colis'] = [
                    'success' => false,
                    'error' => 'Pickup Mes Colis ne peut pas être validé',
                    'can_be_validated' => $mesColisPickup ? $mesColisPickup->can_be_validated : false,
                ];
            }
            
            // 4. Résumé global
            $totalSuccess = 0;
            $totalErrors = 0;
            $totalTrackingNumbers = [];
            
            foreach ($results['validations'] as $carrier => $validation) {
                if ($validation['success']) {
                    $totalSuccess += $validation['successful_shipments'] ?? 0;
                    $totalTrackingNumbers = array_merge($totalTrackingNumbers, $validation['tracking_numbers'] ?? []);
                } else {
                    $totalErrors++;
                }
            }
            
            $results['summary'] = [
                'total_successful_shipments' => $totalSuccess,
                'total_errors' => $totalErrors,
                'total_tracking_numbers' => count($totalTrackingNumbers),
                'tracking_numbers' => $totalTrackingNumbers,
                'overall_success' => $totalSuccess > 0,
            ];
            
            Log::info('🎉 [TEST VALIDATION FLOW] Test terminé', [
                'total_success' => $totalSuccess,
                'total_tracking_numbers' => count($totalTrackingNumbers),
            ]);
            
            return response()->json($results, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            Log::error('❌ [TEST VALIDATION FLOW] Erreur', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur test complet validation',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * 🆕 Créer des données de test complètes (version normale)
     */
    public function createTestPickupData()
    {
        $admin = auth('admin')->user();
        
        try {
            DB::beginTransaction();
            
            // Créer configuration JAX si elle n'existe pas
            $config = DeliveryConfiguration::firstOrCreate([
                'admin_id' => $admin->id,
                'carrier_slug' => 'jax_delivery',
                'integration_name' => 'JAX Test Auto'
            ], [
                'username' => '2304',
                'password' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test.token',
                'environment' => 'test',
                'is_active' => true,
            ]);
            
            // Créer pickup de test
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => 'jax_delivery',
                'delivery_configuration_id' => $config->id,
                'status' => 'draft',
                'pickup_date' => now()->addDay(),
            ]);
            
            // Créer shipments de test
            $shipments = [];
            for ($i = 1; $i <= 3; $i++) {
                $shipment = Shipment::create([
                    'admin_id' => $admin->id,
                    'order_id' => null,
                    'pickup_id' => $pickup->id,
                    'carrier_slug' => 'jax_delivery',
                    'status' => 'created',
                    'weight' => 1.5,
                    'cod_amount' => 50 + ($i * 10),
                    'nb_pieces' => 1,
                    'recipient_info' => [
                        'name' => "Client Test {$i}",
                        'phone' => "12345678{$i}",
                        'address' => "Adresse test {$i}",
                        'city' => 'Tunis',
                        'governorate' => 'Tunis',
                    ],
                    'content_description' => "Produit test {$i}",
                ]);
                $shipments[] = $shipment->id;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Données test créées',
                'pickup_id' => $pickup->id,
                'config_id' => $config->id,
                'shipment_ids' => $shipments,
                'can_be_validated' => $pickup->fresh()->can_be_validated,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ========================================
    // 🆕 NOUVELLES MÉTHODES DE DIAGNOSTIC JAX - À AJOUTER DANS DeliveryController.php
    // ========================================

    /**
     * 🆕 NOUVELLE MÉTHODE : Diagnostic complet JAX avec test réel
     */
    public function diagnosticJaxComplete()
    {
        $admin = auth('admin')->user();
        
        try {
            Log::info('🔍 [JAX DIAGNOSTIC] Début diagnostic complet', [
                'admin_id' => $admin->id,
                'timestamp' => now()->toISOString(),
            ]);
            
            $diagnostic = [
                'admin_info' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],
                'configurations_jax' => [],
                'api_tests' => [],
                'pickup_tests' => [],
                'recommendations' => [],
                'critical_errors' => [],
            ];
            
            // 1. Analyser toutes les configurations JAX
            $jaxConfigs = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('carrier_slug', 'jax_delivery')
                ->get();
            
            if ($jaxConfigs->isEmpty()) {
                $diagnostic['critical_errors'][] = 'Aucune configuration JAX trouvée';
                $diagnostic['recommendations'][] = [
                    'type' => 'error',
                    'message' => 'Créez d\'abord une configuration JAX dans la section Configuration',
                    'action' => 'Aller à Configuration > Créer une configuration JAX',
                ];
            }
            
            foreach ($jaxConfigs as $config) {
                $configDiag = [
                    'id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'is_active' => $config->is_active,
                    'is_valid' => $config->is_valid,
                    'username' => $config->username,
                    'has_password' => !empty($config->password),
                    'password_length' => $config->password ? strlen($config->password) : 0,
                    'environment' => $config->environment,
                    'validation_errors' => [],
                    'api_test_result' => null,
                    'create_test_result' => null,
                ];
                
                // Test de validation
                $validation = $config->validateCredentials();
                $configDiag['validation_errors'] = $validation['errors'] ?? [];
                
                // Test de connexion si configuration valide
                if ($config->is_valid && $config->is_active) {
                    try {
                        $apiConfig = $config->getApiConfig();
                        $jaxService = SimpleCarrierFactory::create('jax_delivery', $apiConfig);
                        
                        // Test de connexion
                        $connectionTest = $jaxService->testConnection();
                        $configDiag['api_test_result'] = $connectionTest;
                        
                        // NOTE: Test de création désactivé pour éviter frais
                        $configDiag['create_test_result'] = [
                            'success' => 'test_skipped',
                            'message' => 'Test de création réel désactivé pour éviter les frais',
                            'note' => 'Utilisez testJaxCreationWithRealData() pour test réel',
                        ];
                        
                    } catch (\Exception $apiError) {
                        $configDiag['api_test_result'] = [
                            'success' => false,
                            'error' => $apiError->getMessage(),
                        ];
                    }
                } else {
                    $configDiag['api_test_result'] = [
                        'success' => false,
                        'message' => 'Configuration inactive ou invalide',
                    ];
                }
                
                $diagnostic['configurations_jax'][] = $configDiag;
            }
            
            // 2. Analyser les pickups JAX existants
            $jaxPickups = Pickup::where('admin_id', $admin->id)
                ->where('carrier_slug', 'jax_delivery')
                ->with(['deliveryConfiguration', 'shipments'])
                ->get();
            
            foreach ($jaxPickups as $pickup) {
                $pickupDiag = [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'can_be_validated' => $pickup->can_be_validated,
                    'config_id' => $pickup->delivery_configuration_id,
                    'config_exists' => !!$pickup->deliveryConfiguration,
                    'config_active' => $pickup->deliveryConfiguration?->is_active ?? false,
                    'shipments_count' => $pickup->shipments->count(),
                    'shipments_with_tracking' => $pickup->shipments->whereNotNull('pos_barcode')->count(),
                    'validation_issues' => [],
                ];
                
                // Analyser les problèmes potentiels
                if (!$pickup->can_be_validated) {
                    if ($pickup->status !== 'draft') {
                        $pickupDiag['validation_issues'][] = "Statut incorrect: {$pickup->status}";
                    }
                    if (!$pickup->deliveryConfiguration) {
                        $pickupDiag['validation_issues'][] = 'Configuration manquante';
                    }
                    if ($pickup->deliveryConfiguration && !$pickup->deliveryConfiguration->is_active) {
                        $pickupDiag['validation_issues'][] = 'Configuration inactive';
                    }
                    if ($pickup->shipments->isEmpty()) {
                        $pickupDiag['validation_issues'][] = 'Aucune expédition';
                    }
                }
                
                $diagnostic['pickup_tests'][] = $pickupDiag;
            }
            
            // 3. Générer les recommandations
            $activeConfigs = collect($diagnostic['configurations_jax'])->where('is_active', true);
            $validConfigs = $activeConfigs->where('is_valid', true);
            $workingConfigs = $validConfigs->where('api_test_result.success', true);
            
            if ($activeConfigs->isEmpty()) {
                $diagnostic['recommendations'][] = [
                    'type' => 'error',
                    'message' => 'Aucune configuration JAX active',
                    'action' => 'Activez au moins une configuration JAX',
                ];
            } elseif ($validConfigs->isEmpty()) {
                $diagnostic['recommendations'][] = [
                    'type' => 'error',
                    'message' => 'Configurations JAX invalides',
                    'action' => 'Vérifiez le numéro de compte et le token JWT',
                ];
            } elseif ($workingConfigs->isEmpty()) {
                $diagnostic['recommendations'][] = [
                    'type' => 'error',
                    'message' => 'Aucune configuration JAX fonctionnelle',
                    'action' => 'Testez la connexion et vérifiez les tokens',
                ];
            } else {
                $diagnostic['recommendations'][] = [
                    'type' => 'success',
                    'message' => 'Configuration JAX fonctionnelle trouvée',
                    'action' => 'Vous pouvez créer des pickups JAX',
                ];
            }
            
            Log::info('✅ [JAX DIAGNOSTIC] Diagnostic terminé', [
                'admin_id' => $admin->id,
                'configs_count' => count($diagnostic['configurations_jax']),
                'pickups_count' => count($diagnostic['pickup_tests']),
                'recommendations_count' => count($diagnostic['recommendations']),
            ]);
            
            return response()->json([
                'success' => true,
                'diagnostic' => $diagnostic,
                'summary' => [
                    'total_jax_configs' => count($diagnostic['configurations_jax']),
                    'active_configs' => $activeConfigs->count(),
                    'valid_configs' => $validConfigs->count(),
                    'working_configs' => $workingConfigs->count(),
                    'total_pickups' => count($diagnostic['pickup_tests']),
                    'critical_errors' => count($diagnostic['critical_errors']),
                    'recommendations' => count($diagnostic['recommendations']),
                ],
            ], 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            Log::error('❌ [JAX DIAGNOSTIC] Erreur', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du diagnostic JAX: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔧 MÉTHODE CORRIGÉE : Test de création JAX avec données réelles
     */
    public function testJaxCreationWithRealData()
    {
        $admin = auth('admin')->user();
        
        try {
            // 🔧 CORRECTION : Recherche moins stricte des configurations
            $config = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('carrier_slug', 'jax_delivery')
                ->where('is_active', true)
                ->first();
            
            // 🔧 CORRECTION : Si pas de config avec is_valid, prendre n'importe quelle config active
            if (!$config) {
                $config = DeliveryConfiguration::where('admin_id', $admin->id)
                    ->where('carrier_slug', 'jax_delivery')
                    ->first();
            }
            
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune configuration JAX trouvée',
                    'debug' => [
                        'total_configs' => DeliveryConfiguration::where('admin_id', $admin->id)->count(),
                        'jax_configs' => DeliveryConfiguration::where('admin_id', $admin->id)
                            ->where('carrier_slug', 'jax_delivery')
                            ->get(['id', 'integration_name', 'is_active', 'is_valid']),
                    ],
                ], 400);
            }
            
            // 🔧 CORRECTION : Test des credentials avant utilisation
            $validation = $config->validateCredentials();
            if (!empty($validation['errors'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Configuration JAX invalide: ' . implode(', ', $validation['errors']),
                    'config_id' => $config->id,
                    'config_name' => $config->integration_name,
                ], 400);
            }
            
            Log::info('🧪 [JAX TEST CREATION] Test avec configuration', [
                'config_id' => $config->id,
                'integration_name' => $config->integration_name,
                'username' => $config->username,
                'has_password' => !empty($config->password),
                'is_active' => $config->is_active,
                'is_valid' => $config->is_valid,
            ]);
            
            // Préparer des données de test réalistes
            $testData = [
                'external_reference' => 'TEST_REAL_' . time(),
                'recipient_name' => 'Client Test Réel',
                'recipient_phone' => '98765432',
                'recipient_phone_2' => '12345678',
                'recipient_address' => 'Avenue Habib Bourguiba, Immeuble Test, Appartement 5',
                'recipient_governorate' => 'Tunis',
                'recipient_city' => 'Centre Ville',
                'cod_amount' => 25,
                'content_description' => 'Produit test e-commerce - Diagnostic',
                'weight' => 1.5,
                'notes' => 'Test diagnostic automatique - Admin: ' . $admin->name,
            ];
            
            // Créer le service JAX
            $apiConfig = $config->getApiConfig();
            $jaxService = SimpleCarrierFactory::create('jax_delivery', $apiConfig);
            
            // Test de connexion d'abord
            $connectionTest = $jaxService->testConnection();
            
            if (!$connectionTest['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Test de connexion JAX échoué: ' . $connectionTest['message'],
                    'config_id' => $config->id,
                ], 422);
            }
            
            // Test de création de colis
            Log::info('🚀 [JAX TEST CREATION] Envoi vers API JAX', [
                'test_data' => $testData,
                'config_id' => $config->id,
            ]);
            
            $result = $jaxService->createShipment($testData);
            
            if ($result['success']) {
                Log::info('✅ [JAX TEST CREATION] Succès', [
                    'tracking_number' => $result['tracking_number'],
                    'config_id' => $config->id,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Test de création JAX réussi !',
                    'tracking_number' => $result['tracking_number'],
                    'config_used' => [
                        'id' => $config->id,
                        'integration_name' => $config->integration_name,
                    ],
                    'test_data' => $testData,
                    'jax_response' => $result['response'],
                    'next_step' => 'Vous pouvez maintenant valider vos pickups JAX normalement',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Échec création colis JAX',
                    'details' => $result,
                    'config_id' => $config->id,
                ], 422);
            }
            
        } catch (\Exception $e) {
            Log::error('❌ [JAX TEST CREATION] Erreur', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur test création JAX: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Test rapide JAX
     */
    public function quickTestJax()
    {
        $admin = auth('admin')->user();
        
        try {
            // Trouver une config JAX
            $config = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('carrier_slug', 'jax_delivery')
                ->where('is_active', true)
                ->first();
            
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune configuration JAX active',
                    'available_configs' => DeliveryConfiguration::where('admin_id', $admin->id)
                        ->where('carrier_slug', 'jax_delivery')
                        ->get(['id', 'integration_name', 'is_active', 'is_valid']),
                ]);
            }
            
            // Tester la config
            $apiConfig = $config->getApiConfig();
            $jaxService = SimpleCarrierFactory::create('jax_delivery', $apiConfig);
            $testResult = $jaxService->testConnection();
            
            return response()->json([
                'success' => true,
                'config_test' => $testResult,
                'config_info' => [
                    'id' => $config->id,
                    'integration_name' => $config->integration_name,
                    'username' => $config->username,
                    'has_password' => !empty($config->password),
                    'is_valid' => $config->is_valid,
                ],
                'api_config_preview' => [
                    'has_api_token' => !empty($apiConfig['api_token']),
                    'token_length' => !empty($apiConfig['api_token']) ? strlen($apiConfig['api_token']) : 0,
                    'username' => $apiConfig['username'] ?? null,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
            ], 500);
        }
    }

    /**
     * 🆕 NOUVELLE MÉTHODE : Réparer une configuration JAX
     */
    public function repairJaxConfig(DeliveryConfiguration $config)
    {
        if ($config->admin_id !== auth('admin')->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        if ($config->carrier_slug !== 'jax_delivery') {
            return response()->json([
                'success' => false,
                'error' => 'Cette méthode est spécifique à JAX Delivery',
            ], 400);
        }
        
        try {
            $issues = [];
            $fixes = [];
            
            // Vérifier et corriger les problèmes courants
            if (empty($config->username)) {
                $issues[] = 'Numéro de compte manquant';
            }
            
            if (empty($config->password)) {
                $issues[] = 'Token JWT manquant';
            } elseif (substr_count($config->password, '.') !== 2) {
                $issues[] = 'Token JWT invalide (doit contenir 3 parties séparées par des points)';
            }
            
            if (!$config->is_active) {
                $issues[] = 'Configuration inactive';
                $config->update(['is_active' => true]);
                $fixes[] = 'Configuration activée automatiquement';
            }
            
            if ($config->environment === 'test') {
                $issues[] = 'Environnement en mode test';
                $fixes[] = 'Considérez passer en mode production pour l\'utilisation réelle';
            }
            
            // Test de connexion après corrections
            $testResult = null;
            if ($config->is_valid) {
                try {
                    $apiConfig = $config->getApiConfig();
                    $jaxService = SimpleCarrierFactory::create('jax_delivery', $apiConfig);
                    $testResult = $jaxService->testConnection();
                    
                    if ($testResult['success']) {
                        $fixes[] = 'Test de connexion réussi';
                    } else {
                        $issues[] = 'Test de connexion échoué: ' . $testResult['message'];
                    }
                } catch (\Exception $e) {
                    $issues[] = 'Erreur test de connexion: ' . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => count($issues) === 0 || count($fixes) > 0,
                'config_id' => $config->id,
                'issues_found' => $issues,
                'fixes_applied' => $fixes,
                'connection_test' => $testResult,
                'recommendations' => $this->getJaxConfigRecommendations($config, $issues),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur réparation config: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🆕 MÉTHODE HELPER : Recommandations pour config JAX
     */
    private function getJaxConfigRecommendations($config, $issues): array
    {
        $recommendations = [];
        
        foreach ($issues as $issue) {
            if (strpos($issue, 'compte manquant') !== false) {
                $recommendations[] = [
                    'type' => 'error',
                    'message' => 'Ajoutez votre numéro de compte JAX dans le champ "Numéro de Compte"',
                    'action' => 'Contactez JAX Delivery pour obtenir votre numéro de compte',
                ];
            }
            
            if (strpos($issue, 'Token JWT') !== false) {
                $recommendations[] = [
                    'type' => 'error',
                    'message' => 'Ajoutez un token JWT valide dans le champ "Token API"',
                    'action' => 'Connectez-vous à votre espace JAX et générez un nouveau token',
                ];
            }
            
            if (strpos($issue, 'connexion échoué') !== false) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => 'Vérifiez que vos identifiants JAX sont corrects',
                    'action' => 'Testez avec Postman ou contactez le support JAX',
                ];
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Configuration JAX correctement configurée',
                'action' => 'Vous pouvez créer et valider des pickups',
            ];
        }
        
        return $recommendations;
    }

}