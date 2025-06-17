<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pickup;
use App\Models\Order;
use App\Models\DeliveryConfiguration;
use App\Models\PickupAddress;
use App\Services\PickupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PickupController extends Controller
{
    private PickupService $pickupService;

    public function __construct(PickupService $pickupService)
    {
        $this->pickupService = $pickupService;
    }

    /**
     * Afficher la liste des enlèvements
     */
    public function index(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $query = $admin->pickups()->with(['deliveryConfiguration', 'pickupAddress', 'shipments']);

            // Appliquer les filtres
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('carrier')) {
                $query->where('carrier_slug', $request->carrier);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $pickups = $query->orderBy('created_at', 'desc')->paginate(20);

            // Statistiques
            $stats = [
                'total' => $admin->pickups()->count(),
                'draft' => $admin->pickups()->where('status', Pickup::STATUS_DRAFT)->count(),
                'validated' => $admin->pickups()->where('status', Pickup::STATUS_VALIDATED)->count(),
                'picked_up' => $admin->pickups()->where('status', Pickup::STATUS_PICKED_UP)->count(),
                'problem' => $admin->pickups()->where('status', Pickup::STATUS_PROBLEM)->count(),
            ];

            if ($request->ajax()) {
                return response()->json([
                    'pickups' => $pickups,
                    'stats' => $stats,
                    'html' => view('admin.delivery.pickups.table', compact('pickups'))->render()
                ]);
            }

            return view('admin.delivery.pickups.index', compact('pickups', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@index: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Erreur lors du chargement des enlèvements'], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des enlèvements');
        }
    }

    /**
     * Afficher un enlèvement
     */
    public function show(Pickup $pickup)
    {
        try {
            $this->authorize('view', $pickup);
            
            $pickup->load([
                'deliveryConfiguration', 
                'pickupAddress', 
                'shipments.order',
                'shipments' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ]);

            return view('admin.delivery.pickups.show', compact('pickup'));

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@show: ' . $e->getMessage());
            return redirect()->route('admin.delivery.pickups.index')
                ->with('error', 'Erreur lors du chargement de l\'enlèvement');
        }
    }

    /**
     * Valider un enlèvement
     */
    public function validate(Request $request, Pickup $pickup)
    {
        try {
            $this->authorize('validate', $pickup);

            $results = $this->pickupService->validatePickup($pickup);

            // Enregistrer dans l'historique des commandes
            foreach ($pickup->shipments()->whereNotNull('pos_barcode')->get() as $shipment) {
                $shipment->order->recordHistory(
                    'pickup_validated',
                    "Enlèvement #{$pickup->id} validé - {$results['success_count']} expédition(s) créée(s)",
                    [
                        'pickup_id' => $pickup->id,
                        'carrier' => $pickup->carrier_slug,
                        'success_count' => $results['success_count'],
                        'error_count' => $results['error_count'],
                    ]
                );
            }

            $message = "Enlèvement validé avec succès. {$results['success_count']} expédition(s) créée(s)";
            
            if ($results['error_count'] > 0) {
                $message .= " - {$results['error_count']} erreur(s)";
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'results' => $results
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@validate: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Générer les étiquettes
     */
    public function generateLabels(Request $request, Pickup $pickup)
    {
        try {
            $this->authorize('generateLabels', $pickup);

            $result = $this->pickupService->generateLabels($pickup);

            // Enregistrer dans l'historique
            foreach ($pickup->shipments()->whereNotNull('pos_barcode')->get() as $shipment) {
                $shipment->order->recordHistory(
                    'tracking_updated',
                    "Étiquettes générées pour l'enlèvement #{$pickup->id}",
                    [
                        'pickup_id' => $pickup->id,
                        'labels_count' => count($pickup->shipments()->whereNotNull('pos_barcode')->get()),
                        'generated_at' => now(),
                    ]
                );
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Étiquettes générées avec succès',
                    'download_url' => route('admin.delivery.pickups.show', $pickup) . '?download=labels'
                ]);
            }

            // Retourner le PDF pour téléchargement
            return response($result['labels'])
                ->header('Content-Type', $result['content_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@generateLabels: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Générer le manifeste
     */
    public function generateManifest(Request $request, Pickup $pickup)
    {
        try {
            $this->authorize('generateManifest', $pickup);

            $manifestData = $this->pickupService->generateManifest($pickup);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manifeste généré avec succès',
                    'data' => $manifestData
                ]);
            }

            // Générer et retourner le PDF du manifeste
            $pdf = app('dompdf.wrapper');
            $html = view('admin.delivery.pickups.manifest', compact('manifestData'))->render();
            $pdf->loadHTML($html);

            return $pdf->download("manifeste_enlevement_{$pickup->id}.pdf");

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@generateManifest: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rafraîchir le statut
     */
    public function refreshStatus(Request $request, Pickup $pickup)
    {
        try {
            $this->authorize('refreshStatus', $pickup);

            $results = $this->pickupService->refreshPickupStatus($pickup);

            // Enregistrer les changements dans l'historique
            foreach ($results['status_changes'] ?? [] as $change) {
                $shipment = $pickup->shipments()->find($change['shipment_id']);
                if ($shipment && $shipment->order) {
                    $shipment->order->recordHistory(
                        'tracking_updated',
                        "Statut mis à jour automatiquement: {$change['new_status']}",
                        [
                            'pickup_id' => $pickup->id,
                            'old_status' => $change['old_status'],
                            'new_status' => $change['new_status'],
                            'pos_barcode' => $change['pos_barcode'],
                        ],
                        $change['old_status'],
                        $change['new_status'],
                        null,
                        null,
                        $change['pos_barcode'],
                        $pickup->carrier_slug
                    );
                }
            }

            $message = "Statut rafraîchi. {$results['updated_count']} expédition(s) mise(s) à jour";
            
            if ($results['error_count'] > 0) {
                $message .= " - {$results['error_count']} erreur(s)";
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'results' => $results
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@refreshStatus: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Supprimer un enlèvement
     */
    public function destroy(Pickup $pickup)
    {
        try {
            $this->authorize('delete', $pickup);

            if (!$pickup->canBeDeleted()) {
                throw new \Exception('Seuls les enlèvements en brouillon peuvent être supprimés.');
            }

            $pickupId = $pickup->id;
            
            // Enregistrer dans l'historique avant suppression
            foreach ($pickup->shipments as $shipment) {
                $shipment->order->recordHistory(
                    'shipment_removed',
                    "Enlèvement #{$pickupId} supprimé",
                    ['pickup_id' => $pickupId]
                );
            }

            $this->pickupService->deletePickup($pickup);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Enlèvement #{$pickupId} supprimé avec succès"
                ]);
            }

            return redirect()->route('admin.delivery.pickups.index')
                ->with('success', "Enlèvement #{$pickupId} supprimé avec succès");

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@destroy: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ajouter des commandes à un enlèvement
     */
    public function addOrders(Request $request, Pickup $pickup)
    {
        try {
            $this->authorize('addOrders', $pickup);

            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id'
            ]);

            $results = $this->pickupService->addOrdersToPickup($pickup, $validated['order_ids']);

            $message = "{$results['added_count']} commande(s) ajoutée(s) à l'enlèvement";
            
            if ($results['skipped_count'] > 0) {
                $message .= " - {$results['skipped_count']} commande(s) ignorée(s)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'results' => $results
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@addOrders: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une expédition d'un enlèvement
     */
    public function removeShipment(Request $request, Pickup $pickup, $shipmentId)
    {
        try {
            $this->authorize('removeShipment', $pickup);

            $shipment = $pickup->shipments()->findOrFail($shipmentId);
            
            // Enregistrer dans l'historique
            $shipment->order->recordHistory(
                'shipment_removed',
                "Expédition supprimée de l'enlèvement #{$pickup->id}",
                ['pickup_id' => $pickup->id, 'shipment_id' => $shipment->id]
            );

            $this->pickupService->removeShipmentFromPickup($pickup, $shipment);

            return response()->json([
                'success' => true,
                'message' => 'Expédition supprimée de l\'enlèvement'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@removeShipment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dupliquer un enlèvement
     */
    public function duplicate(Request $request, Pickup $pickup)
    {
        try {
            $this->authorize('view', $pickup);

            $newPickup = $this->pickupService->duplicatePickup($pickup);

            return response()->json([
                'success' => true,
                'message' => 'Enlèvement dupliqué avec succès',
                'pickup_id' => $newPickup->id,
                'redirect_url' => route('admin.delivery.pickups.show', $newPickup)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans PickupController@duplicate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}