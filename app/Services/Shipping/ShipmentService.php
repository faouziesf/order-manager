<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Shipment;
use App\Models\ShipmentStatusHistory;
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
    public function trackAllShipments(): array
    {
        $shipments = Shipment::needsTracking()->with(['pickup.deliveryConfiguration'])->get();
        
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
                
                if ($trackingData && $shipment->status !== $oldStatus) {
                    $results['updated']++;
                    $results['details'][] = [
                        'shipment_id' => $shipment->id,
                        'pos_barcode' => $shipment->pos_barcode,
                        'old_status' => $oldStatus,
                        'new_status' => $shipment->status,
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
        ];

        // Statistiques par statut
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach (Shipment::class::STATUS_LABELS as $status => $label) {
            $stats['by_status'][$status] = [
                'label' => $label,
                'count' => $statusCounts[$status] ?? 0,
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

        return $stats;
    }

    /**
     * Rechercher des expéditions avec filtres avancés
     */
    public function searchShipments(Admin $admin, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Shipment::where('admin_id', $admin->id)
            ->with(['order', 'pickup.deliveryConfiguration', 'statusHistory']);

        // Filtre par statut
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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
            'return_rate' => 0,
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

        // Calculer le taux de retour
        $totalShipments = Shipment::where('admin_id', $admin->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        if ($totalShipments > 0) {
            $analysis['return_rate'] = round(($returns->count() / $totalShipments) * 100, 2);
        }

        return $analysis;
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

            // Enregistrer dans l'historique
            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'carrier_status_code' => $trackingData['status'],
                'carrier_status_label' => $trackingData['carrier_status_label'] ?? null,
                'internal_status' => $newStatus,
            ]);

            // Mettre à jour la commande si livré
            if ($newStatus === Shipment::STATUS_DELIVERED && $shipment->order) {
                $shipment->order->update([
                    'status' => 'livrée',
                    'delivered_at' => now(),
                ]);
            }

            Log::info('Shipment status updated', [
                'shipment_id' => $shipment->id,
                'pos_barcode' => $shipment->pos_barcode,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
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
            ->whereIn('status', [Shipment::STATUS_DELIVERED, Shipment::STATUS_IN_RETURN, Shipment::STATUS_ANOMALY])
            ->get();

        $performance = [
            'delivery_rate' => 0,
            'return_rate' => 0,
            'anomaly_rate' => 0,
            'average_delivery_time' => null,
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
        return ShipmentStatusHistory::whereHas('shipment', function($q) use ($admin) {
                $q->where('admin_id', $admin->id);
            })
            ->with(['shipment.order'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($history) {
                return [
                    'date' => $history->created_at,
                    'shipment_id' => $history->shipment_id,
                    'order_number' => $history->shipment->order_number,
                    'customer_name' => $history->shipment->order->customer_name ?? 'N/A',
                    'status' => $history->internal_status,
                    'status_label' => $history->human_status,
                ];
            })
            ->toArray();
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
        
        $pdf = app('dompdf.wrapper');
        
        $data = [
            'shipment' => $shipment,
            'template' => $template,
            'config' => $template->layout_config,
        ];
        
        $html = view('admin.delivery.bl-template', $data)->render();
        $pdf->loadHTML($html);
        
        return $pdf->output();
    }
}