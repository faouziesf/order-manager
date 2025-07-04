<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Pickup;
use App\Models\Shipment;
use App\Models\DeliveryConfiguration;
use App\Models\PickupAddress;
use App\Services\Shipping\ShippingServiceFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PickupService
{
    private ShippingServiceFactory $shippingFactory;

    public function __construct(ShippingServiceFactory $shippingFactory)
    {
        $this->shippingFactory = $shippingFactory;
    }

    /**
     * Créer un nouvel enlèvement
     */
    public function createPickup(Admin $admin, array $data): Pickup
    {
        $deliveryConfig = DeliveryConfiguration::where('id', $data['delivery_configuration_id'])
            ->where('admin_id', $admin->id)
            ->firstOrFail();

        // Valider l'adresse d'enlèvement si nécessaire
        $pickupAddress = null;
        if ($deliveryConfig->supportsPickupAddressSelection()) {
            if (empty($data['pickup_address_id'])) {
                throw new \Exception('Une adresse d\'enlèvement est requise pour ce transporteur.');
            }
            
            $pickupAddress = PickupAddress::where('id', $data['pickup_address_id'])
                ->where('admin_id', $admin->id)
                ->firstOrFail();
        }

        return DB::transaction(function () use ($admin, $data, $deliveryConfig, $pickupAddress) {
            // Créer l'enlèvement
            $pickup = Pickup::create([
                'admin_id' => $admin->id,
                'carrier_slug' => $deliveryConfig->carrier_slug,
                'delivery_configuration_id' => $deliveryConfig->id,
                'pickup_address_id' => $pickupAddress?->id,
                'pickup_date' => $data['pickup_date'] ?? null,
                'status' => Pickup::STATUS_DRAFT,
            ]);

            // Créer les expéditions pour les commandes sélectionnées
            foreach ($data['order_ids'] as $orderId) {
                $order = Order::where('id', $orderId)
                    ->where('admin_id', $admin->id)
                    ->where('status', 'confirmée')
                    ->firstOrFail();

                $this->createShipmentForOrder($pickup, $order);
            }

            Log::info('Pickup created', [
                'pickup_id' => $pickup->id,
                'admin_id' => $admin->id,
                'carrier' => $deliveryConfig->carrier_slug,
                'orders_count' => count($data['order_ids']),
            ]);

            return $pickup;
        });
    }

    /**
     * Valider un enlèvement
     */
    public function validatePickup(Pickup $pickup): array
    {
        if ($pickup->status !== Pickup::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être validés.');
        }

        if ($pickup->shipments()->count() === 0) {
            throw new \Exception('Aucune expédition trouvée pour cet enlèvement.');
        }

        return DB::transaction(function () use ($pickup) {
            $results = [
                'success_count' => 0,
                'error_count' => 0,
                'errors' => [],
                'shipments' => [],
            ];

            foreach ($pickup->shipments as $shipment) {
                try {
                    $shipment->createWithCarrier();
                    $results['success_count']++;
                    $results['shipments'][] = [
                        'id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'pos_barcode' => $shipment->pos_barcode,
                        'status' => 'success',
                    ];
                } catch (\Exception $e) {
                    $results['error_count']++;
                    $error = "Commande #{$shipment->order_id}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    $results['shipments'][] = [
                        'id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'pos_barcode' => null,
                        'status' => 'error',
                        'error' => $e->getMessage(),
                    ];
                    
                    Log::error('Shipment creation failed', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'pickup_id' => $pickup->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($results['success_count'] > 0) {
                $pickup->update([
                    'status' => Pickup::STATUS_VALIDATED,
                    'validated_at' => now(),
                ]);

                Log::info('Pickup validated', [
                    'pickup_id' => $pickup->id,
                    'success_count' => $results['success_count'],
                    'error_count' => $results['error_count'],
                ]);
            } else {
                throw new \Exception('Aucune expédition n\'a pu être créée: ' . implode(', ', $results['errors']));
            }

            return $results;
        });
    }

    /**
     * Ajouter des commandes à un enlèvement existant
     */
    public function addOrdersToPickup(Pickup $pickup, array $orderIds): array
    {
        if ($pickup->status !== Pickup::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être modifiés.');
        }

        $results = [
            'added_count' => 0,
            'skipped_count' => 0,
            'errors' => [],
        ];

        foreach ($orderIds as $orderId) {
            try {
                $order = Order::where('id', $orderId)
                    ->where('admin_id', $pickup->admin_id)
                    ->where('status', 'confirmée')
                    ->first();

                if (!$order) {
                    $results['skipped_count']++;
                    $results['errors'][] = "Commande #{$orderId} introuvable ou non confirmée";
                    continue;
                }

                // Vérifier si la commande n'a pas déjà une expédition
                if ($order->shipments()->whereNotNull('pickup_id')->exists()) {
                    $results['skipped_count']++;
                    $results['errors'][] = "Commande #{$orderId} déjà assignée à un enlèvement";
                    continue;
                }

                $this->createShipmentForOrder($pickup, $order);
                $results['added_count']++;
                
            } catch (\Exception $e) {
                $results['skipped_count']++;
                $results['errors'][] = "Commande #{$orderId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Supprimer une expédition d'un enlèvement
     */
    public function removeShipmentFromPickup(Pickup $pickup, Shipment $shipment): void
    {
        if ($pickup->status !== Pickup::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être modifiés.');
        }

        if ($shipment->pickup_id !== $pickup->id) {
            throw new \Exception('Cette expédition n\'appartient pas à cet enlèvement.');
        }

        // Enregistrer dans l'historique de la commande
        $shipment->order->recordHistory(
            'shipment_removed',
            "Expédition supprimée de l'enlèvement #{$pickup->id}",
            ['pickup_id' => $pickup->id, 'shipment_id' => $shipment->id]
        );

        $shipment->delete();
    }

    /**
     * Générer les étiquettes pour un enlèvement
     */
    public function generateLabels(Pickup $pickup): array
    {
        if ($pickup->status !== Pickup::STATUS_VALIDATED) {
            throw new \Exception('L\'enlèvement doit être validé pour générer les étiquettes.');
        }

        $posBarcodes = $pickup->shipments()
            ->whereNotNull('pos_barcode')
            ->pluck('pos_barcode')
            ->toArray();

        if (empty($posBarcodes)) {
            throw new \Exception('Aucune étiquette à générer.');
        }

        $service = $this->shippingFactory->make(
            $pickup->carrier_slug,
            $pickup->deliveryConfiguration
        );

        try {
            $result = $service->getMassLabels($posBarcodes);
            
            // Enregistrer dans l'historique
            foreach ($pickup->shipments()->whereNotNull('pos_barcode')->get() as $shipment) {
                $shipment->order->recordHistory(
                    'tracking_updated',
                    "Étiquettes générées pour l'enlèvement #{$pickup->id}",
                    [
                        'pickup_id' => $pickup->id,
                        'labels_count' => count($posBarcodes),
                        'generated_at' => now(),
                    ]
                );
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Label generation failed', [
                'pickup_id' => $pickup->id,
                'pos_barcodes' => $posBarcodes,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Générer le manifeste pour un enlèvement
     */
    public function generateManifest(Pickup $pickup): array
    {
        if ($pickup->status !== Pickup::STATUS_VALIDATED) {
            throw new \Exception('L\'enlèvement doit être validé pour générer le manifeste.');
        }

        $shipments = $pickup->shipments()->whereNotNull('pos_barcode')->get();
        
        if ($shipments->isEmpty()) {
            throw new \Exception('Aucune expédition à inclure dans le manifeste.');
        }

        $manifestData = [
            'pickup' => $pickup,
            'shipments' => $shipments,
            'total_weight' => $shipments->sum('weight'),
            'total_value' => $shipments->sum('value'),
            'total_cod' => $shipments->sum('cod_amount'),
            'pickup_address' => $pickup->pickupAddress,
            'carrier_config' => $pickup->deliveryConfiguration,
            'generated_at' => now(),
        ];

        // Ici on pourrait utiliser une vue PDF ou un générateur de manifeste
        // Pour l'instant, retourner les données
        return $manifestData;
    }

    /**
     * Mettre à jour le statut des expéditions d'un enlèvement
     */
    public function refreshPickupStatus(Pickup $pickup): array
    {
        $results = [
            'updated_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'status_changes' => [],
        ];

        foreach ($pickup->shipments as $shipment) {
            try {
                $oldStatus = $shipment->status;
                $shipment->trackStatus();
                
                if ($shipment->fresh()->status !== $oldStatus) {
                    $results['updated_count']++;
                    $results['status_changes'][] = [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'old_status' => $oldStatus,
                        'new_status' => $shipment->fresh()->status,
                        'pos_barcode' => $shipment->pos_barcode,
                    ];
                }
            } catch (\Exception $e) {
                $results['error_count']++;
                $results['errors'][] = "Expédition #{$shipment->order_id}: " . $e->getMessage();
            }
        }

        // Mettre à jour le statut de l'enlèvement
        $oldPickupStatus = $pickup->status;
        $pickup->updateStatus();
        $pickup->checkForProblems();
        
        if ($pickup->fresh()->status !== $oldPickupStatus) {
            $results['pickup_status_changed'] = [
                'old_status' => $oldPickupStatus,
                'new_status' => $pickup->fresh()->status,
            ];
        }

        return $results;
    }

    /**
     * Obtenir les commandes disponibles pour enlèvement
     */
    public function getAvailableOrdersForPickup(Admin $admin, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = Order::where('admin_id', $admin->id)
            ->where('status', 'confirmée')
            ->where('is_suspended', false)
            ->whereDoesntHave('shipments', function($q) {
                $q->whereNotNull('pickup_id');
            });

        // Appliquer les filtres
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['governorate'])) {
            $query->where('customer_governorate', $filters['governorate']);
        }

        if (!empty($filters['city'])) {
            $query->where('customer_city', $filters['city']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('total_price', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('total_price', '<=', $filters['max_amount']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir les statistiques des enlèvements
     */
    public function getPickupStats(Admin $admin, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $baseQuery = Pickup::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate);

        $stats = [
            'total_pickups' => (clone $baseQuery)->count(),
            'draft_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_DRAFT)->count(),
            'validated_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_VALIDATED)->count(),
            'picked_up_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_PICKED_UP)->count(),
            'problem_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_PROBLEM)->count(),
            'total_shipments' => 0,
            'delivered_shipments' => 0,
            'by_carrier' => [],
            'average_validation_time' => null,
            'delivery_rate' => 0,
        ];

        // Statistiques des expéditions
        $shipmentsQuery = Shipment::whereHas('pickup', function($q) use ($admin, $startDate) {
            $q->where('admin_id', $admin->id)->where('created_at', '>=', $startDate);
        });

        $stats['total_shipments'] = $shipmentsQuery->count();
        $stats['delivered_shipments'] = (clone $shipmentsQuery)->where('status', Shipment::STATUS_DELIVERED)->count();

        // Statistiques par transporteur
        $stats['by_carrier'] = Pickup::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate)
            ->groupBy('carrier_slug')
            ->selectRaw('carrier_slug, count(*) as count')
            ->pluck('count', 'carrier_slug')
            ->toArray();

        // Temps moyen de validation
        $stats['average_validation_time'] = $this->calculateAverageValidationTime($admin, $startDate);

        // Taux de livraison
        if ($stats['total_shipments'] > 0) {
            $stats['delivery_rate'] = round(($stats['delivered_shipments'] / $stats['total_shipments']) * 100, 2);
        }

        return $stats;
    }

    /**
     * Créer une expédition pour une commande
     */
    private function createShipmentForOrder(Pickup $pickup, Order $order): Shipment
    {
        return Shipment::create([
            'admin_id' => $pickup->admin_id,
            'order_id' => $order->id,
            'pickup_id' => $pickup->id,
            'order_number' => $order->id,
            'status' => Shipment::STATUS_CREATED,
            'weight' => $this->calculateOrderWeight($order),
            'value' => $order->total_price,
            'cod_amount' => $order->total_price, // COD par défaut
            'nb_pieces' => 1,
            'content_description' => 'Commande #' . $order->id,
            'recipient_info' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'address' => $order->customer_address,
                'city' => $order->customer_city,
                'governorate' => $order->customer_governorate,
                'email' => $order->customer_email,
            ],
        ]);
    }

    /**
     * Calculer le poids d'une commande
     */
    private function calculateOrderWeight(Order $order): float
    {
        $totalWeight = 0;
        
        foreach ($order->items as $item) {
            $itemWeight = $item->product->weight ?? 0.5; // 500g par défaut
            $totalWeight += $itemWeight * $item->quantity;
        }
        
        return max($totalWeight, 0.1); // Minimum 100g
    }

    /**
     * Calculer le temps moyen de validation
     */
    private function calculateAverageValidationTime(Admin $admin, Carbon $startDate): ?float
    {
        $validatedPickups = Pickup::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('validated_at')
            ->get();

        if ($validatedPickups->isEmpty()) {
            return null;
        }

        $totalMinutes = 0;
        foreach ($validatedPickups as $pickup) {
            $totalMinutes += $pickup->created_at->diffInMinutes($pickup->validated_at);
        }

        return round($totalMinutes / $validatedPickups->count(), 2);
    }

    /**
     * Supprimer un enlèvement (seulement en brouillon)
     */
    public function deletePickup(Pickup $pickup): void
    {
        if ($pickup->status !== Pickup::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être supprimés.');
        }

        DB::transaction(function () use ($pickup) {
            // Supprimer les expéditions
            foreach ($pickup->shipments as $shipment) {
                $shipment->order->recordHistory(
                    'shipment_removed',
                    "Enlèvement #{$pickup->id} supprimé",
                    ['pickup_id' => $pickup->id]
                );
            }
            
            $pickup->shipments()->delete();
            $pickup->delete();
        });
    }

    /**
     * Dupliquer un enlèvement
     */
    public function duplicatePickup(Pickup $pickup): Pickup
    {
        return DB::transaction(function () use ($pickup) {
            $newPickup = Pickup::create([
                'admin_id' => $pickup->admin_id,
                'carrier_slug' => $pickup->carrier_slug,
                'delivery_configuration_id' => $pickup->delivery_configuration_id,
                'pickup_address_id' => $pickup->pickup_address_id,
                'pickup_date' => $pickup->pickup_date,
                'status' => Pickup::STATUS_DRAFT,
            ]);

            // Dupliquer les expéditions (seulement si les commandes sont encore disponibles)
            foreach ($pickup->shipments as $shipment) {
                if ($shipment->order->status === 'confirmée' && 
                    !$shipment->order->shipments()->whereNotNull('pickup_id')->exists()) {
                    
                    $this->createShipmentForOrder($newPickup, $shipment->order);
                }
            }

            return $newPickup;
        });
    }
}