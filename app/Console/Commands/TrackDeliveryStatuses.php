<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shipment;
use App\Services\Delivery\SimpleCarrierFactory;
use App\Services\Delivery\Contracts\CarrierServiceException;
use Illuminate\Support\Facades\Log;

class TrackDeliveryStatuses extends Command
{
    /**
     * 🆕 NOUVELLE COMMANDE : Suivi automatique des statuts de livraison
     */
    protected $signature = 'delivery:track-statuses 
                            {--carrier= : Transporteur spécifique à traiter}
                            {--limit=50 : Nombre maximum d\'expéditions à traiter}
                            {--dry-run : Mode test sans mise à jour}';

    protected $description = 'Met à jour automatiquement les statuts des expéditions en cours via les API des transporteurs';

    public function handle()
    {
        $startTime = microtime(true);
        
        $this->info('🚀 [DELIVERY TRACK] Début du suivi automatique des statuts');
        
        try {
            $carrier = $this->option('carrier');
            $limit = (int) $this->option('limit');
            $dryRun = $this->option('dry-run');
            
            if ($dryRun) {
                $this->warn('⚠️ Mode DRY-RUN activé - Aucune mise à jour ne sera effectuée');
            }
            
            // Récupérer les expéditions à suivre
            $shipments = $this->getShipmentsToTrack($carrier, $limit);
            
            if ($shipments->isEmpty()) {
                $this->info('ℹ️ Aucune expédition à suivre');
                return Command::SUCCESS;
            }
            
            $this->info("📦 {$shipments->count()} expédition(s) à traiter");
            
            $stats = [
                'processed' => 0,
                'updated' => 0,
                'errors' => 0,
                'by_carrier' => [],
            ];
            
            // Traiter chaque expédition
            foreach ($shipments as $shipment) {
                $carrierId = $shipment->carrier_slug;
                
                if (!isset($stats['by_carrier'][$carrierId])) {
                    $stats['by_carrier'][$carrierId] = [
                        'processed' => 0,
                        'updated' => 0,
                        'errors' => 0,
                    ];
                }
                
                try {
                    $this->line("🔍 Suivi expédition #{$shipment->id} ({$carrierId}) - {$shipment->pos_barcode}");
                    
                    $result = $this->trackSingleShipment($shipment, $dryRun);
                    
                    $stats['processed']++;
                    $stats['by_carrier'][$carrierId]['processed']++;
                    
                    if ($result['updated']) {
                        $stats['updated']++;
                        $stats['by_carrier'][$carrierId]['updated']++;
                        
                        $this->info("  ✅ Statut mis à jour: {$result['old_status']} → {$result['new_status']}");
                    } else {
                        $this->line("  ℹ️ Statut inchangé: {$result['current_status']}");
                    }
                    
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['by_carrier'][$carrierId]['errors']++;
                    
                    $this->error("  ❌ Erreur: {$e->getMessage()}");
                    
                    Log::error('❌ [DELIVERY TRACK] Erreur suivi shipment', [
                        'shipment_id' => $shipment->id,
                        'carrier' => $carrierId,
                        'tracking_number' => $shipment->pos_barcode,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Afficher les statistiques finales
            $executionTime = round((microtime(true) - $startTime), 2);
            
            $this->newLine();
            $this->info('📊 Statistiques du suivi:');
            $this->table(
                ['Transporteur', 'Traitées', 'Mises à jour', 'Erreurs'],
                collect($stats['by_carrier'])->map(function ($data, $carrier) {
                    return [$carrier, $data['processed'], $data['updated'], $data['errors']];
                })->toArray()
            );
            
            $this->info("✅ Suivi terminé en {$executionTime}s");
            $this->info("📈 Total: {$stats['processed']} traitées, {$stats['updated']} mises à jour, {$stats['errors']} erreurs");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur fatale du suivi: {$e->getMessage()}");
            
            Log::error('❌ [DELIVERY TRACK] Erreur fatale commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * 🆕 MÉTHODE : Récupérer les expéditions à suivre
     */
    protected function getShipmentsToTrack(?string $carrier = null, int $limit = 50)
    {
        $query = Shipment::with(['pickup.deliveryConfiguration', 'order'])
            ->whereNotNull('pos_barcode')
            ->whereIn('status', [
                'validated',
                'picked_up_by_carrier', 
                'in_transit',
                'delivery_attempted'
            ])
            ->where('carrier_last_status_update', '<', now()->subHours(1)) // Pas mis à jour depuis 1h
            ->orderBy('carrier_last_status_update', 'asc');
        
        if ($carrier) {
            $query->where('carrier_slug', $carrier);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * 🆕 MÉTHODE : Suivre une expédition spécifique
     */
    protected function trackSingleShipment(Shipment $shipment, bool $dryRun = false): array
    {
        $oldStatus = $shipment->status;
        
        // Vérifier que nous avons une configuration valide
        if (!$shipment->pickup || !$shipment->pickup->deliveryConfiguration) {
            throw new \Exception('Configuration transporteur manquante');
        }
        
        $config = $shipment->pickup->deliveryConfiguration;
        
        if (!$config->is_active || !$config->is_valid) {
            throw new \Exception('Configuration transporteur inactive ou invalide');
        }
        
        // Créer le service transporteur
        $apiConfig = $config->getApiConfig();
        $carrierService = SimpleCarrierFactory::create($shipment->carrier_slug, $apiConfig);
        
        // Récupérer le statut depuis l'API
        $result = $carrierService->getShipmentStatus($shipment->pos_barcode);
        
        if (!$result['success']) {
            throw new \Exception('Impossible de récupérer le statut depuis l\'API');
        }
        
        $newStatus = $result['status'];
        $updated = false;
        
        // Mettre à jour si le statut a changé
        if ($newStatus !== $oldStatus && $newStatus !== 'unknown') {
            if (!$dryRun) {
                $shipment->updateStatus(
                    $newStatus,
                    $result['response']['carrier_code'] ?? null,
                    $result['response']['carrier_label'] ?? null,
                    "Statut mis à jour automatiquement via commande de suivi"
                );
                
                Log::info('✅ [DELIVERY TRACK] Statut mis à jour', [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->pos_barcode,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'carrier' => $shipment->carrier_slug,
                ]);
            }
            
            $updated = true;
        } else {
            // Mettre à jour la date de dernière vérification même si pas de changement
            if (!$dryRun) {
                $shipment->update(['carrier_last_status_update' => now()]);
            }
        }
        
        return [
            'updated' => $updated,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'current_status' => $newStatus,
        ];
    }
}