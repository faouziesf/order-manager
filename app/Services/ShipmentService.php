<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Shipment;
use App\Models\BLTemplate;
use App\Services\Shipping\ShippingServiceFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShipmentService
{
    private ShippingServiceFactory $shippingFactory;

    public function __construct(ShippingServiceFactory $shippingFactory)
    {
        $this->shippingFactory = $shippingFactory;
    }

    /**
     * Suivre le statut d'une expédition spécifique
     */
    public function trackShipment(Shipment $shipment): ?array
    {
        if (!$shipment->pos_barcode || !$shipment->pickup) {
            return null;
        }

        try {
            $service = $this->shippingFactory->make(
                $shipment->pickup->carrier_slug,
                $shipment->pickup->deliveryConfiguration
            );

            $trackingData = $service->trackShipment($shipment->pos_barcode);
            
            if ($trackingData) {
                $this->updateShipmentStatus($shipment, $trackingData);
            }

            return $trackingData;

        } catch (\Exception $e) {
            Log::error('Shipment tracking failed', [
                'shipment_id' => $shipment->id,
                'pos_barcode' => $shipment->pos_barcode,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Suivre toutes les expéditions nécessitant un suivi
     */
    public function trackAllShipments(array $filters = []): array
    {
        $query = Shipment::needsTracking()->with(['pickup.deliveryConfiguration', 'order']);

        // Appliquer les filtres
        if (!empty($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }

        if (!empty($filters['carrier'])) {
            $query->whereHas('pickup', function($q) use ($filters) {
                $q->where('carrier_slug', $filters['carrier']);
            });
        }

        if (!empty($filters['pickup_id'])) {
            $query->where('pickup_id', $filters['pickup_id']);
        }

        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        $shipments = $query->orderBy('carrier_last_status_update', 'asc')->get();
        
        $results = [
            'total' => $shipments->count(),
            'updated' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($shipments as $shipment) {
            try {
                $oldStatus = $shipment->status;
                $trackingData = $this->trackShipment($shipment);
                
                if ($trackingData && $shipment->fresh()->status !== $oldStatus) {
                    $results['updated']++;
                    $results['details'][] = [
                        'shipment_id' => $shipment->id,
                        'pos_barcode' => $shipment->pos_barcode,
                        'order_id' => $shipment->order_id,
                        'old_status' => $oldStatus,
                        'new_status' => $shipment->fresh()->status,
                    ];
                }

            } catch (\Exception $e) {
                $results['errors']++;
                Log::error('Bulk shipment tracking error', [
                    'shipment_id' => $shipment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Générer un bordereau de livraison personnalisé
     */
    public function generateCustomBL(Shipment $shipment, ?BLTemplate $template = null): string
    {
        if (!$template) {
            $template = BLTemplate::where('admin_id', $shipment->admin_id)
                ->where('carrier_slug', $shipment->pickup->carrier_slug)
                ->where('is_default', true)
                ->first();
        }

        if (!$template) {
            // Utiliser le template par défaut
            $template = $this->createDefaultBLTemplate($shipment->admin_id, $shipment->pickup->carrier_slug);
        }

        return $this->renderBLTemplate($shipment, $template);
    }

    /**
     * Obtenir les statistiques d'expédition pour un admin
     */
    public function getShipmentStats(Admin $admin, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $baseQuery = Shipment::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate);

        $stats = [
            'total_shipments' => (clone $baseQuery)->count(),
            'by_status' => [],
            'by_carrier' => [],
            'delivery_performance' => [],
            'recent_activity' => [],
            'trends' => [],
        ];

        // Statistiques par statut
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach (Shipment::getStatusOptions() as $status => $label) {
            $stats['by_status'][$status] = [
                'label' => $label,
                'count' => $statusCounts[$status] ?? 0,
                'percentage' => $stats['total_shipments'] > 0 ? 
                    round((($statusCounts[$status] ?? 0) / $stats['total_shipments']) * 100, 1) : 0,
            ];
        }

        // Statistiques par transporteur
        $carrierStats = (clone $baseQuery)
            ->join('pickups', 'shipments.pickup_id', '=', 'pickups.id')
            ->selectRaw('pickups.carrier_slug, COUNT(*) as count')
            ->groupBy('pickups.carrier_slug')
            ->pluck('count', 'carrier_slug')
            ->toArray();

        $stats['by_carrier'] = $carrierStats;

        // Performance de livraison
        $stats['delivery_performance'] = $this->calculateDeliveryPerformance($admin, $startDate);

        // Activité récente
        $stats['recent_activity'] = $this->getRecentShipmentActivity($admin, 10);

        // Tendances (évolution sur les 7 derniers jours)
        $stats['trends'] = $this->calculateTrends($admin, $days);

        return $stats;
    }

    /**
     * Rechercher des expéditions avec filtres avancés
     */
    public function searchShipments(Admin $admin, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Shipment::where('admin_id', $admin->id)
            ->with(['order', 'pickup.deliveryConfiguration']);

        // Filtre par statut
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Filtre par transporteur
        if (!empty($filters['carrier'])) {
            $query->whereHas('pickup', function($q) use ($filters) {
                $q->where('carrier_slug', $filters['carrier']);
            });
        }

        // Filtre par code de suivi
        if (!empty($filters['tracking_code'])) {
            $query->where('pos_barcode', 'LIKE', '%' . $filters['tracking_code'] . '%');
        }

        // Filtre par numéro de commande
        if (!empty($filters['order_number'])) {
            $query->where('order_number', 'LIKE', '%' . $filters['order_number'] . '%');
        }

        // Filtre par client
        if (!empty($filters['customer_name'])) {
            $query->whereHas('order', function($q) use ($filters) {
                $q->where('customer_name', 'LIKE', '%' . $filters['customer_name'] . '%');
            });
        }

        // Filtre par téléphone
        if (!empty($filters['customer_phone'])) {
            $query->whereHas('order', function($q) use ($filters) {
                $q->where('customer_phone', 'LIKE', '%' . $filters['customer_phone'] . '%');
            });
        }

        // Filtre par date de création
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filtre par gouvernorat
        if (!empty($filters['governorate'])) {
            $query->whereJsonContains('recipient_info->governorate', $filters['governorate']);
        }

        // Filtre par enlèvement
        if (!empty($filters['pickup_id'])) {
            $query->where('pickup_id', $filters['pickup_id']);
        }

        // Filtre par valeur
        if (!empty($filters['min_value'])) {
            $query->where('value', '>=', $filters['min_value']);
        }

        if (!empty($filters['max_value'])) {
            $query->where('value', '<=', $filters['max_value']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Analyser les retours d'expéditions
     */
    public function analyzeReturns(Admin $admin, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $returns = Shipment::where('admin_id', $admin->id)
            ->where('status', Shipment::STATUS_IN_RETURN)
            ->where('created_at', '>=', $startDate)
            ->with(['order', 'pickup'])
            ->get();

        $analysis = [
            'total_returns' => $returns->count(),
            'return_reasons' => [],
            'by_carrier' => [],
            'by_governorate' => [],
            'by_time_period' => [],
            'return_rate' => 0,
            'average_return_time' => null,
        ];

        // Grouper par transporteur
        $analysis['by_carrier'] = $returns->groupBy('pickup.carrier_slug')
            ->map(function($group) {
                return $group->count();
            })->toArray();

        // Grouper par gouvernorat
        $analysis['by_governorate'] = $returns->groupBy('order.customer_governorate')
            ->map(function($group) {
                return $group->count();
            })->toArray();

        // Analyser par période (par semaine)
        $analysis['by_time_period'] = $returns->groupBy(function($shipment) {
            return $shipment->created_at->startOfWeek()->format('Y-m-d');
        })->map(function($group) {
            return $group->count();
        })->toArray();

        // Calculer le taux de retour
        $totalShipments = Shipment::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($totalShipments > 0) {
            $analysis['return_rate'] = round(($returns->count() / $totalShipments) * 100, 2);
        }

        // Temps moyen de retour (depuis la création jusqu'au retour)
        $returnTimes = $returns->filter(function($shipment) {
            return $shipment->carrier_last_status_update;
        })->map(function($shipment) {
            return $shipment->created_at->diffInDays($shipment->carrier_last_status_update);
        });

        if ($returnTimes->count() > 0) {
            $analysis['average_return_time'] = round($returnTimes->average(), 1);
        }

        return $analysis;
    }

    /**
     * Obtenir le tableau de bord des expéditions en temps réel
     */
    public function getRealtimeDashboard(Admin $admin): array
    {
        return Cache::remember("shipment_dashboard_{$admin->id}", 60, function () use ($admin) {
            return [
                'active_shipments' => Shipment::where('admin_id', $admin->id)
                    ->active()
                    ->count(),
                'delivered_today' => Shipment::where('admin_id', $admin->id)
                    ->where('status', Shipment::STATUS_DELIVERED)
                    ->whereDate('delivered_at', today())
                    ->count(),
                'in_transit' => Shipment::where('admin_id', $admin->id)
                    ->where('status', Shipment::STATUS_IN_TRANSIT)
                    ->count(),
                'pending_pickup' => Shipment::where('admin_id', $admin->id)
                    ->where('status', Shipment::STATUS_VALIDATED)
                    ->count(),
                'problems' => Shipment::where('admin_id', $admin->id)
                    ->whereIn('status', [Shipment::STATUS_ANOMALY, Shipment::STATUS_IN_RETURN])
                    ->count(),
                'total_value_in_transit' => Shipment::where('admin_id', $admin->id)
                    ->active()
                    ->sum('value'),
            ];
        });
    }

    /**
     * Marquer manuellement une expédition comme livrée
     */
    public function markAsDelivered(Shipment $shipment, string $notes = null): void
    {
        if ($shipment->status === Shipment::STATUS_DELIVERED) {
            throw new \Exception('Cette expédition est déjà marquée comme livrée.');
        }

        $shipment->markAsDelivered($notes);

        Log::info('Shipment manually marked as delivered', [
            'shipment_id' => $shipment->id,
            'order_id' => $shipment->order_id,
            'pos_barcode' => $shipment->pos_barcode,
            'notes' => $notes,
        ]);
    }

    /**
     * Mettre à jour le statut d'une expédition
     */
    private function updateShipmentStatus(Shipment $shipment, array $trackingData): void
    {
        $newStatus = $this->mapCarrierStatus($trackingData['status']);
        
        if ($newStatus !== $shipment->status) {
            $oldStatus = $shipment->status;
            
            $shipment->update([
                'status' => $newStatus,
                'carrier_last_status_update' => now(),
            ]);

            // Le logging est déjà géré dans le modèle Shipment
            Log::info('Shipment status updated', [
                'shipment_id' => $shipment->id,
                'pos_barcode' => $shipment->pos_barcode,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'carrier_status' => $trackingData['status'],
            ]);
        }
    }

    /**
     * Mapper le statut du transporteur vers le statut interne
     */
    private function mapCarrierStatus(string $carrierStatus): string
    {
        // Mapping Fparcel EVENT_ID vers statuts internes
        $mapping = [
            '1' => Shipment::STATUS_CREATED,
            '3' => Shipment::STATUS_PICKED_UP_BY_CARRIER,
            '6' => Shipment::STATUS_IN_TRANSIT,
            '7' => Shipment::STATUS_DELIVERED,
            '9' => Shipment::STATUS_IN_RETURN,
            '11' => Shipment::STATUS_ANOMALY,
        ];

        return $mapping[$carrierStatus] ?? Shipment::STATUS_CREATED;
    }

    /**
     * Calculer la performance de livraison
     */
    private function calculateDeliveryPerformance(Admin $admin, Carbon $startDate): array
    {
        $shipments = Shipment::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', [
                Shipment::STATUS_DELIVERED, 
                Shipment::STATUS_IN_RETURN, 
                Shipment::STATUS_ANOMALY
            ])
            ->get();

        $performance = [
            'delivery_rate' => 0,
            'return_rate' => 0,
            'anomaly_rate' => 0,
            'average_delivery_time' => null,
            'on_time_delivery' => 0,
        ];

        if ($shipments->count() > 0) {
            $delivered = $shipments->where('status', Shipment::STATUS_DELIVERED)->count();
            $returns = $shipments->where('status', Shipment::STATUS_IN_RETURN)->count();
            $anomalies = $shipments->where('status', Shipment::STATUS_ANOMALY)->count();
            $total = $shipments->count();

            $performance['delivery_rate'] = round(($delivered / $total) * 100, 2);
            $performance['return_rate'] = round(($returns / $total) * 100, 2);
            $performance['anomaly_rate'] = round(($anomalies / $total) * 100, 2);

            // Calculer le temps moyen de livraison
            $deliveredShipments = $shipments->where('status', Shipment::STATUS_DELIVERED)
                ->filter(function($shipment) {
                    return $shipment->delivered_at && $shipment->created_at;
                });

            if ($deliveredShipments->count() > 0) {
                $totalHours = 0;
                foreach ($deliveredShipments as $shipment) {
                    $totalHours += $shipment->created_at->diffInHours($shipment->delivered_at);
                }
                $performance['average_delivery_time'] = round($totalHours / $deliveredShipments->count(), 1);
            }
        }

        return $performance;
    }

    /**
     * Obtenir l'activité récente des expéditions
     */
    private function getRecentShipmentActivity(Admin $admin, int $limit): array
    {
        return Shipment::where('admin_id', $admin->id)
            ->with(['order'])
            ->whereNotNull('carrier_last_status_update')
            ->orderBy('carrier_last_status_update', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($shipment) {
                return [
                    'date' => $shipment->carrier_last_status_update,
                    'shipment_id' => $shipment->id,
                    'order_number' => $shipment->order_number,
                    'customer_name' => $shipment->customer_name,
                    'status' => $shipment->status,
                    'status_label' => $shipment->status_label,
                    'pos_barcode' => $shipment->pos_barcode,
                    'carrier' => $shipment->carrier_name,
                ];
            })
            ->toArray();
    }

    /**
     * Calculer les tendances
     */
    private function calculateTrends(Admin $admin, int $days): array
    {
        $periods = [];
        $periodDays = max(1, intval($days / 7)); // Diviser en 7 périodes

        for ($i = 0; $i < 7; $i++) {
            $endDate = now()->subDays($i * $periodDays);
            $startDate = $endDate->copy()->subDays($periodDays);
            
            $count = Shipment::where('admin_id', $admin->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
                
            $periods[] = [
                'period' => $startDate->format('M d'),
                'count' => $count,
            ];
        }

        return array_reverse($periods);
    }

    /**
     * Créer un template BL par défaut
     */
    private function createDefaultBLTemplate(int $adminId, string $carrierSlug): BLTemplate
    {
        return BLTemplate::create([
            'admin_id' => $adminId,
            'carrier_slug' => $carrierSlug,
            'template_name' => 'Template par défaut',
            'layout_config' => BLTemplate::getDefaultLayoutConfig(),
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Rendre un template BL
     */
    private function renderBLTemplate(Shipment $shipment, BLTemplate $template): string
    {
        // Cette méthode utiliserait une librairie de génération PDF
        // comme TCPDF ou DomPDF pour créer le bordereau
        
        // Pour l'instant, retourner une version JSON des données
        $data = [
            'template' => $template->template_name,
            'shipment' => [
                'pos_barcode' => $shipment->pos_barcode,
                'return_barcode' => $shipment->return_barcode,
                'order_number' => $shipment->order_number,
                'weight' => $shipment->weight,
                'value' => $shipment->value,
                'pieces' => $shipment->nb_pieces,
            ],
            'customer' => $shipment->recipient_info,
            'sender' => $shipment->sender_info,
            'config' => $template->layout_config,
            'generated_at' => now(),
        ];
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}