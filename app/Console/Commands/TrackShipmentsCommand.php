<?php

namespace App\Console\Commands;

use App\Models\Pickup;
use App\Models\Shipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TrackShipmentsCommand extends Command
{
    protected $signature = 'delivery:track-shipments 
                            {--pickup-id= : Suivre uniquement les expéditions d\'un enlèvement spécifique}
                            {--admin-id= : Suivre uniquement les expéditions d\'un admin spécifique}
                            {--limit=100 : Limiter le nombre d\'expéditions à traiter}
                            {--dry-run : Simuler l\'exécution sans mettre à jour}';

    protected $description = 'Suivre les statuts des expéditions Jax Delivery et mettre à jour les statuts des enlèvements';

    public function handle(): int
    {
        $this->info('🚚 Démarrage du suivi des expéditions Jax Delivery...');

        $startTime = microtime(true);
        
        try {
            // Construire la requête des expéditions à suivre
            $shipmentsQuery = $this->buildShipmentsQuery();
            $totalShipments = $shipmentsQuery->count();

            if ($totalShipments === 0) {
                $this->info('Aucune expédition à suivre.');
                return 0;
            }

            $this->info("📦 {$totalShipments} expédition(s) à traiter...");

            // Traiter les expéditions par lots
            $results = $this->processShipments($shipmentsQuery);

            // Mettre à jour les statuts des enlèvements
            $this->updatePickupStatuses();

            // Afficher les résultats
            $this->displayResults($results, microtime(true) - $startTime);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du suivi des expéditions: ' . $e->getMessage());
            Log::error('TrackShipmentsCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Construire la requête des expéditions à suivre
     */
    private function buildShipmentsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Shipment::needsTracking()
            ->with(['pickup.deliveryConfiguration', 'order']);

        // Filtre par enlèvement spécifique
        if ($this->option('pickup-id')) {
            $query->where('pickup_id', $this->option('pickup-id'));
        }

        // Filtre par admin spécifique
        if ($this->option('admin-id')) {
            $query->where('admin_id', $this->option('admin-id'));
        }

        // Limiter le nombre de résultats
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->orderBy('carrier_last_status_update', 'asc');
    }

    /**
     * Traiter les expéditions
     */
    private function processShipments(\Illuminate\Database\Eloquent\Builder $query): array
    {
        $results = [
            'total' => 0,
            'updated' => 0,
            'errors' => 0,
            'details' => [],
        ];

        $shipments = $query->get();
        $results['total'] = $shipments->count();

        $progressBar = $this->output->createProgressBar($results['total']);
        $progressBar->start();

        foreach ($shipments as $shipment) {
            try {
                $oldStatus = $shipment->status;
                
                if (!$this->option('dry-run')) {
                    // TODO: Intégrer avec JaxDeliveryService
                    // $shipment->trackStatus();
                    
                    // Pour l'instant, simulation
                    if (rand(1, 10) > 8) { // 20% de chance de mise à jour
                        $newStatuses = ['in_transit', 'delivered'];
                        $newStatus = $newStatuses[array_rand($newStatuses)];
                        
                        $shipment->update([
                            'status' => $newStatus,
                            'carrier_last_status_update' => now(),
                        ]);
                        
                        $results['updated']++;
                        $results['details'][] = [
                            'shipment_id' => $shipment->id,
                            'pos_barcode' => $shipment->pos_barcode,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                        ];
                        
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("\n📄 Expédition {$shipment->pos_barcode}: {$oldStatus} → {$newStatus}");
                        }
                    }
                } else {
                    // Mode dry-run
                    $this->line("\n[DRY-RUN] Traitement de l'expédition {$shipment->pos_barcode}");
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $this->error("\n❌ Erreur expédition {$shipment->pos_barcode}: " . $e->getMessage());
                
                Log::error('Shipment tracking error', [
                    'shipment_id' => $shipment->id,
                    'pos_barcode' => $shipment->pos_barcode,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');

        return $results;
    }

    /**
     * Mettre à jour les statuts des enlèvements
     */
    private function updatePickupStatuses(): void
    {
        if ($this->option('dry-run')) {
            $this->info('🏷️  [DRY-RUN] Mise à jour des statuts d\'enlèvement...');
            return;
        }

        $this->info('🏷️  Mise à jour des statuts d\'enlèvement...');

        $pickupsQuery = Pickup::byStatus(Pickup::STATUS_VALIDATED)
            ->with('shipments');

        // Filtrer par admin si spécifié
        if ($this->option('admin-id')) {
            $pickupsQuery->where('admin_id', $this->option('admin-id'));
        }

        $pickups = $pickupsQuery->get();
        $updatedPickups = 0;
        $problemPickups = 0;

        foreach ($pickups as $pickup) {
            try {
                $oldStatus = $pickup->status;
                
                // Mettre à jour le statut basé sur les expéditions
                $pickup->updateStatus();
                
                // Vérifier les problèmes
                $pickup->checkForProblems();
                
                if ($pickup->status !== $oldStatus) {
                    $updatedPickups++;
                    $this->line("📋 Enlèvement {$pickup->id}: {$oldStatus} → {$pickup->status}");
                    
                    if ($pickup->status === Pickup::STATUS_PROBLEM) {
                        $problemPickups++;
                    }
                }

            } catch (\Exception $e) {
                $this->error("❌ Erreur enlèvement {$pickup->id}: " . $e->getMessage());
                Log::error('Pickup status update error', [
                    'pickup_id' => $pickup->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("✅ Enlèvements mis à jour: {$updatedPickups}");
        
        if ($problemPickups > 0) {
            $this->warn("⚠️  {$problemPickups} enlèvement(s) marqué(s) comme problématique(s)");
        }
    }

    /**
     * Afficher les résultats
     */
    private function displayResults(array $results, float $executionTime): void
    {
        $this->info('');
        $this->info('📊 Résultats du suivi Jax Delivery:');
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Expéditions traitées', $results['total']],
                ['Expéditions mises à jour', $results['updated']],
                ['Erreurs rencontrées', $results['errors']],
                ['Temps d\'exécution', round($executionTime, 2) . 's'],
                ['Taux de réussite', $results['total'] > 0 ? round((($results['total'] - $results['errors']) / $results['total']) * 100, 1) . '%' : '0%'],
            ]
        );

        if ($this->getOutput()->isVeryVerbose() && !empty($results['details'])) {
            $this->info('');
            $this->info('📋 Détails des mises à jour:');
            $this->table(
                ['ID Expédition', 'Code Suivi', 'Ancien Statut', 'Nouveau Statut'],
                array_map(function($detail) {
                    return [
                        $detail['shipment_id'],
                        $detail['pos_barcode'],
                        $detail['old_status'],
                        $detail['new_status'],
                    ];
                }, $results['details'])
            );
        }

        if ($results['errors'] > 0) {
            $this->warn("⚠️  {$results['errors']} erreur(s) rencontrée(s). Consultez les logs pour plus de détails.");
        }

        if ($this->option('dry-run')) {
            $this->comment('ℹ️  Mode dry-run activé - aucune modification n\'a été effectuée');
        }
    }
}