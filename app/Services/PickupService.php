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
    public function validatePickup(Pickup $pickup): bool
    {
        if ($pickup->status !== Pickup::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être validés.');
        }

        if ($pickup->shipments()->count() === 0) {
            throw new \Exception('Aucune expédition trouvée pour cet enlèvement.');
        }

        return DB::transaction(function () use ($pickup) {
            $errors = [];
            $successCount = 0;

            foreach ($pickup->shipments as $shipment) {
                try {
                    $shipment->createWithCarrier();
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Expédition #{$shipment->order_id}: " . $e->getMessage();
                    Log::error('Shipment creation failed', [
                        'shipment_id' => $shipment->id,
                        'order_id' => $shipment->order_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($successCount > 0) {
                $pickup->update([
                    'status' => Pickup::STATUS_VALIDATED,
                    'validated_at' => now(),
                ]);

                Log::info('Pickup validated', [
                    'pickup_id' => $pickup->id,
                    'success_count' => $successCount,
                    'error_count' => count($errors),
                ]);

                return true;
            }

            throw new \Exception('Aucune expédition n\'a pu être créée: ' . implode(', ', $errors));
        });
    }

    /**
     * Ajouter des commandes à un enlèvement existant
     */
    public function addOrdersToPickup(Pickup $pickup, array $orderIds): int
    {
        if ($pickup->status !== Pickup::STATUS_DRAFT) {
            throw new \Exception('Seuls les enlèvements en brouillon peuvent être modifiés.');
        }

        $addedCount = 0;

        foreach ($orderIds as $orderId) {
            $order = Order::where('id', $orderId)
                ->where('admin_id', $pickup->admin_id)
                ->where('status', 'confirmée')
                ->first();

            if (!$order) {
                continue;
            }

            // Vérifier si la commande n'a pas déjà une expédition
            if ($order->shipments()->whereNotNull('pickup_id')->exists()) {
                continue;
            }

            $this->createShipmentForOrder($pickup, $order);
            $addedCount++;
        }

        return $addedCount;
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

        return $service->getMassLabels($posBarcodes);
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
        ];

        foreach ($pickup->shipments as $shipment) {
            try {
                $oldStatus = $shipment->status;
                $shipment->trackStatus();
                
                if ($shipment->status !== $oldStatus) {
                    $results['updated_count']++;
                }
            } catch (\Exception $e) {
                $results['error_count']++;
                $results['errors'][] = "Expédition #{$shipment->order_id}: " . $e->getMessage();
            }
        }

        // Mettre à jour le statut de l'enlèvement
        $pickup->updateStatus();
        $pickup->checkForProblems();

        return $results;
    }

    /**
     * Obtenir les commandes disponibles pour enlèvement
     */
    public function getAvailableOrdersForPickup(Admin $admin, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = Order::where('admin_id', $admin->id)
            ->where('status', 'confirmée')
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

        return [
            'total_pickups' => (clone $baseQuery)->count(),
            'validated_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_VALIDATED)->count(),
            'picked_up_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_PICKED_UP)->count(),
            'problem_pickups' => (clone $baseQuery)->where('status', Pickup::STATUS_PROBLEM)->count(),
            'total_shipments' => Shipment::whereHas('pickup', function($q) use ($admin, $startDate) {
                $q->where('admin_id', $admin->id)->where('created_at', '>=', $startDate);
            })->count(),
            'delivered_shipments' => Shipment::whereHas('pickup', function($q) use ($admin, $startDate) {
                $q->where('admin_id', $admin->id)->where('created_at', '>=', $startDate);
            })->where('status', Shipment::STATUS_DELIVERED)->count(),
            'average_validation_time' => $this->calculateAverageValidationTime($admin, $startDate),
            'delivery_rate' => $this->calculateDeliveryRate($admin, $startDate),
        ];
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
        
        foreach ($order->orderItems as $item) {
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
     * Calculer le taux de livraison
     */
    private function calculateDeliveryRate(Admin $admin, Carbon $startDate): float
    {
        $totalShipments = Shipment::whereHas('pickup', function($q) use ($admin, $startDate) {
            $q->where('admin_id', $admin->id)->where('created_at', '>=', $startDate);
        })->count();

        if ($totalShipments === 0) {
            return 0;
        }

        $deliveredShipments = Shipment::whereHas('pickup', function($q) use ($admin, $startDate) {
            $q->where('admin_id', $admin->id)->where('created_at', '>=', $startDate);
        })->where('status', Shipment::STATUS_DELIVERED)->count();

        return round(($deliveredShipments / $totalShipments) * 100, 2);
    }
}