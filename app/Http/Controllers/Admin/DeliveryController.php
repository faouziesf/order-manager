<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfiguration;
use App\Models\PickupAddress;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\Order;
use App\Services\PickupService;
use App\Services\ShipmentService;
use App\Services\Shipping\ShippingServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    private PickupService $pickupService;
    private ShipmentService $shipmentService;
    private ShippingServiceFactory $shippingFactory;

    public function __construct(
        PickupService $pickupService,
        ShipmentService $shipmentService,
        ShippingServiceFactory $shippingFactory
    ) {
        $this->pickupService = $pickupService;
        $this->shipmentService = $shipmentService;
        $this->shippingFactory = $shippingFactory;
    }

    /**
     * Page de configuration principale
     */
    public function configuration(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $configurations = $admin->deliveryConfigurations()
                ->orderBy('carrier_slug')
                ->orderBy('integration_name')
                ->get();

            $pickupAddresses = $admin->pickupAddresses()
                ->where('is_active', true)
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();

            $availableCarriers = $this->shippingFactory->getSupportedCarriers();

            return view('admin.delivery.configuration', compact(
                'configurations', 
                'pickupAddresses', 
                'availableCarriers'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@configuration: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la configuration');
        }
    }

    /**
     * Créer une configuration de transporteur
     */
    public function storeConfiguration(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();

            $validated = $request->validate([
                'carrier_slug' => 'required|string|max:50',
                'integration_name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'password' => 'required|string|max:255',
                'environment' => 'required|in:test,prod',
            ], [
                'carrier_slug.required' => 'Le transporteur est obligatoire',
                'integration_name.required' => 'Le nom d\'intégration est obligatoire',
                'username.required' => 'Le nom d\'utilisateur est obligatoire',
                'password.required' => 'Le mot de passe est obligatoire',
                'environment.required' => 'L\'environnement est obligatoire',
                'environment.in' => 'L\'environnement doit être "test" ou "prod"',
            ]);

            // Vérifier l'unicité
            $existing = DeliveryConfiguration::where('admin_id', $admin->id)
                ->where('carrier_slug', $validated['carrier_slug'])
                ->where('integration_name', $validated['integration_name'])
                ->exists();

            if ($existing) {
                throw new ValidationException(['integration_name' => ['Une configuration avec ce nom existe déjà pour ce transporteur.']]);
            }

            DB::beginTransaction();

            $configuration = DeliveryConfiguration::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $validated['carrier_slug'],
                'integration_name' => $validated['integration_name'],
                'username' => $validated['username'],
                'password' => $validated['password'],
                'environment' => $validated['environment'],
                'is_active' => true,
            ]);

            // Tester la connexion et obtenir le token
            $testResult = $configuration->testConnection();
            
            if (!$testResult['success']) {
                throw new \Exception($testResult['message']);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuration créée et testée avec succès',
                    'configuration' => $configuration
                ]);
            }

            return redirect()->back()->with('success', 'Configuration créée et testée avec succès');

        } catch (ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans DeliveryController@storeConfiguration: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Mettre à jour une configuration
     */
    public function updateConfiguration(Request $request, DeliveryConfiguration $config)
    {
        try {
            $this->authorize('update', $config);

            $validated = $request->validate([
                'integration_name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'password' => 'nullable|string|max:255',
                'environment' => 'required|in:test,prod',
            ]);

            // Si pas de nouveau mot de passe, garder l'ancien
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            $config->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Configuration mise à jour avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@updateConfiguration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfiguration(DeliveryConfiguration $config)
    {
        try {
            $this->authorize('delete', $config);

            $configName = $config->display_name;
            $config->delete();

            return response()->json([
                'success' => true,
                'message' => "Configuration \"{$configName}\" supprimée avec succès"
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@deleteConfiguration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester la connexion
     */
    public function testConnection(DeliveryConfiguration $config)
    {
        try {
            $this->authorize('testConnection', $config);

            $result = $config->testConnection();

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@testConnection: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test de connexion'
            ], 500);
        }
    }

    /**
     * Rafraîchir le token
     */
    public function refreshToken(DeliveryConfiguration $config)
    {
        try {
            $this->authorize('testConnection', $config);

            $success = $config->refreshToken();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Token rafraîchi avec succès',
                    'expires_at' => $config->expires_at
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du token'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@refreshToken: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du token'
            ], 500);
        }
    }

    /**
     * Activer/désactiver une configuration
     */
    public function toggleConfiguration(DeliveryConfiguration $config)
    {
        try {
            $this->authorize('toggleStatus', $config);

            $config->update(['is_active' => !$config->is_active]);
            
            $status = $config->is_active ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "Configuration {$status} avec succès",
                'is_active' => $config->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@toggleConfiguration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une adresse d'enlèvement
     */
    public function storePickupAddress(Request $request)
    {
        // Déléguer au PickupAddressController
        return app(PickupAddressController::class)->store($request);
    }

    /**
     * Supprimer une adresse d'enlèvement
     */
    public function deletePickupAddress(PickupAddress $address)
    {
        // Déléguer au PickupAddressController
        return app(PickupAddressController::class)->destroy($address);
    }

    /**
     * Page de préparation d'enlèvement
     */
    public function preparation(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            // Configurations actives
            $configurations = $admin->deliveryConfigurations()
                ->where('is_active', true)
                ->get();

            if ($configurations->isEmpty()) {
                return redirect()->route('admin.delivery.configuration')
                    ->with('warning', 'Veuillez d\'abord configurer au moins un transporteur.');
            }

            // Adresses d'enlèvement
            $pickupAddresses = $admin->pickupAddresses()
                ->where('is_active', true)
                ->get();

            // Statistiques
            $stats = [
                'available_orders' => $admin->orders()
                    ->where('status', 'confirmée')
                    ->whereDoesntHave('shipments', function($q) {
                        $q->whereNotNull('pickup_id');
                    })
                    ->count(),
                'draft_pickups' => $admin->pickups()->where('status', Pickup::STATUS_DRAFT)->count(),
            ];

            return view('admin.delivery.preparation', compact(
                'configurations', 
                'pickupAddresses', 
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@preparation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la préparation');
        }
    }

    /**
     * Obtenir les commandes disponibles pour enlèvement
     */
    public function getAvailableOrders(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $filters = $request->only([
                'date_from', 'date_to', 'governorate', 'city', 
                'min_amount', 'max_amount', 'search'
            ]);

            $orders = $this->pickupService
                ->getAvailableOrdersForPickup($admin, $filters)
                ->with(['items.product'])
                ->paginate(20);

            if ($request->ajax()) {
                return response()->json([
                    'orders' => $orders,
                    'html' => view('admin.delivery.preparation.orders-table', compact('orders'))->render()
                ]);
            }

            return response()->json(['orders' => $orders]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@getAvailableOrders: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement des commandes'
            ], 500);
        }
    }

    /**
     * Créer un enlèvement
     */
    public function createPickup(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();

            $validated = $request->validate([
                'delivery_configuration_id' => 'required|exists:delivery_configurations,id',
                'pickup_address_id' => 'nullable|exists:pickup_addresses,id',
                'pickup_date' => 'nullable|date|after_or_equal:today',
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'exists:orders,id'
            ], [
                'delivery_configuration_id.required' => 'Veuillez sélectionner une configuration de transporteur',
                'order_ids.required' => 'Veuillez sélectionner au moins une commande',
                'order_ids.min' => 'Veuillez sélectionner au moins une commande',
            ]);

            $pickup = $this->pickupService->createPickup($admin, $validated);

            // Enregistrer dans l'historique des commandes
            foreach ($pickup->shipments as $shipment) {
                $shipment->order->recordHistory(
                    'pickup_created',
                    "Enlèvement #{$pickup->id} créé avec {$pickup->carrier_slug}",
                    [
                        'pickup_id' => $pickup->id,
                        'carrier' => $pickup->carrier_slug,
                        'pickup_address_id' => $pickup->pickup_address_id,
                    ]
                );
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enlèvement créé avec succès',
                    'pickup_id' => $pickup->id,
                    'redirect_url' => route('admin.delivery.pickups.show', $pickup)
                ]);
            }

            return redirect()->route('admin.delivery.pickups.show', $pickup)
                ->with('success', 'Enlèvement créé avec succès');

        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@createPickup: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Page des enlèvements
     */
    public function pickups(Request $request)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->index($request);
    }

    /**
     * Afficher un enlèvement
     */
    public function showPickup(Pickup $pickup)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->show($pickup);
    }

    /**
     * Valider un enlèvement
     */
    public function validatePickup(Request $request, Pickup $pickup)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->validate($request, $pickup);
    }

    /**
     * Générer les étiquettes
     */
    public function generateLabels(Request $request, Pickup $pickup)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->generateLabels($request, $pickup);
    }

    /**
     * Générer le manifeste
     */
    public function generateManifest(Request $request, Pickup $pickup)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->generateManifest($request, $pickup);
    }

    /**
     * Rafraîchir le statut
     */
    public function refreshStatus(Request $request, Pickup $pickup)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->refreshStatus($request, $pickup);
    }

    /**
     * Supprimer un enlèvement
     */
    public function destroyPickup(Pickup $pickup)
    {
        // Déléguer au PickupController
        return app(PickupController::class)->destroy($pickup);
    }

    /**
     * Page des expéditions
     */
    public function shipments(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $filters = $request->only([
                'status', 'carrier', 'tracking_code', 'order_number',
                'customer_name', 'customer_phone', 'date_from', 'date_to',
                'governorate', 'pickup_id', 'min_value', 'max_value'
            ]);

            $shipments = $this->shipmentService
                ->searchShipments($admin, $filters)
                ->paginate(20);

            // Statistiques
            $stats = $this->shipmentService->getShipmentStats($admin);

            if ($request->ajax()) {
                return response()->json([
                    'shipments' => $shipments,
                    'stats' => $stats,
                    'html' => view('admin.delivery.shipments.table', compact('shipments'))->render()
                ]);
            }

            return view('admin.delivery.shipments.index', compact('shipments', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@shipments: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Erreur lors du chargement des expéditions'], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des expéditions');
        }
    }

    /**
     * Afficher une expédition
     */
    public function showShipment(Shipment $shipment)
    {
        try {
            $this->authorize('view', $shipment->order);
            
            $shipment->load(['order', 'pickup.deliveryConfiguration', 'pickup.pickupAddress']);
            
            $trackingHistory = $shipment->getTrackingHistory();

            return view('admin.delivery.shipments.show', compact('shipment', 'trackingHistory'));

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@showShipment: ' . $e->getMessage());
            return redirect()->route('admin.delivery.shipments.index')
                ->with('error', 'Erreur lors du chargement de l\'expédition');
        }
    }

    /**
     * Suivre une expédition
     */
    public function trackShipment(Request $request, Shipment $shipment)
    {
        try {
            $this->authorize('view', $shipment->order);

            $trackingData = $this->shipmentService->trackShipment($shipment);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour',
                'tracking_data' => $trackingData,
                'shipment' => $shipment->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@trackShipment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du suivi'
            ], 500);
        }
    }

    /**
     * Marquer comme livré
     */
    public function markDelivered(Request $request, Shipment $shipment)
    {
        try {
            $this->authorize('update', $shipment->order);

            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000'
            ]);

            $this->shipmentService->markAsDelivered($shipment, $validated['notes'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Expédition marquée comme livrée'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@markDelivered: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Page des statistiques
     */
    public function stats(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $days = $request->get('days', 30);
            
            $pickupStats = $this->pickupService->getPickupStats($admin, $days);
            $shipmentStats = $this->shipmentService->getShipmentStats($admin, $days);
            $returnAnalysis = $this->shipmentService->analyzeReturns($admin, $days);
            $dashboard = $this->shipmentService->getRealtimeDashboard($admin);

            return view('admin.delivery.stats', compact(
                'pickupStats', 
                'shipmentStats', 
                'returnAnalysis', 
                'dashboard',
                'days'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@stats: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des statistiques');
        }
    }

    /**
     * API: Obtenir les transporteurs disponibles
     */
    public function getCarriers()
    {
        try {
            $carriers = $this->shippingFactory->getSupportedCarriers();
            
            return response()->json([
                'success' => true,
                'carriers' => $carriers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des transporteurs'
            ], 500);
        }
    }

    /**
     * API: Obtenir les statistiques
     */
    public function getApiStats(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $dashboard = $this->shipmentService->getRealtimeDashboard($admin);
            
            return response()->json([
                'success' => true,
                'stats' => $dashboard
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }

    /**
     * API: Suivre toutes les expéditions
     */
    public function trackAllShipments(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $filters = array_merge($request->only(['admin_id', 'carrier', 'pickup_id', 'limit']), [
                'admin_id' => $admin->id
            ]);
            
            $results = $this->shipmentService->trackAllShipments($filters);

            return response()->json([
                'success' => true,
                'message' => "Suivi terminé. {$results['updated']} expédition(s) mise(s) à jour",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans DeliveryController@trackAllShipments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du suivi des expéditions'
            ], 500);
        }
    }
}