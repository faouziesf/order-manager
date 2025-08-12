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
                    $diagnostic['api_config_preview'] = [
                        'carrier' => $pickup->carrier_slug,
                        'has_username' => !empty($pickup->deliveryConfiguration->username),
                        'has_password' => !empty($pickup->deliveryConfiguration->password),
                        'username_preview' => $pickup->deliveryConfiguration->username ? 
                            substr($pickup->deliveryConfiguration->username, 0, 4) . '***' : null,
                        'password_preview' => $pickup->deliveryConfiguration->password ? 
                            substr($pickup->deliveryConfiguration->password, 0, 10) . '...' : null,
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
     * Valider un pickup (envoi vers l'API transporteur)
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

            $result = $pickup->validate();
            
            if ($result['success']) {
                $successMessage = "Pickup #{$pickup->id} validé avec succès ! ";
                $successMessage .= "{$result['successful_shipments']}/{$result['total_shipments']} expédition(s) envoyée(s).";
                
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
                'carrier_slug' => 'required|string|max:255',
                'integration_name' => 'required|string|max:255',
                'username' => 'nullable|string|max:255',
                'password' => 'nullable|string|max:255',
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
            $config = [
                'api_key' => 'test_token_123',
                'username' => 'test_user',
                'environment' => 'test',
            ];
            
            // Test JAX Delivery
            $jaxService = SimpleCarrierFactory::create('jax_delivery', $config);
            $jaxTest = $jaxService->testConnection();
            
            // Test Mes Colis
            $mesColisService = SimpleCarrierFactory::create('mes_colis', $config);
            $mesColisTest = $mesColisService->testConnection();
            
            return [
                'success' => true,
                'service_class' => get_class($jaxService),
                'supported_carriers' => SimpleCarrierFactory::getSupportedCarriers(),
                'test_connection' => [
                    'jax_delivery' => $jaxTest,
                    'mes_colis' => $mesColisTest,
                ],
                'factory_working' => true,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'factory_working' => false,
            ];
        }
    }

    /**
     * 🆕 Créer des données de test complètes
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
}