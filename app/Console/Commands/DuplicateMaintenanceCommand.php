<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Admin;
use App\Traits\DuplicateDetectionTrait;
use Illuminate\Support\Facades\Log;

class DuplicateMaintenanceCommand extends Command
{
    use DuplicateDetectionTrait;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'duplicates:maintenance
                            {--admin= : ID de l\'admin spécifique à traiter}
                            {--scan : Scanner et détecter tous les doublons}
                            {--auto-merge : Effectuer la fusion automatique}
                            {--clean : Nettoyer les anciens marquages de doublons}
                            {--stats : Afficher les statistiques}';

    /**
     * The console command description.
     */
    protected $description = 'Maintenance et gestion automatique des commandes en double';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminId = $this->option('admin');
        
        if ($adminId) {
            $admin = Admin::find($adminId);
            if (!$admin) {
                $this->error("Admin avec l'ID {$adminId} introuvable");
                return 1;
            }
            $this->info("Traitement pour l'admin: {$admin->name} (ID: {$adminId})");
            $admins = collect([$admin]);
        } else {
            $admins = Admin::where('is_active', true)->get();
            $this->info("Traitement pour tous les admins actifs (" . $admins->count() . " admins)");
        }

        foreach ($admins as $admin) {
            $this->processAdmin($admin);
        }

        $this->info("Maintenance des doublons terminée");
        return 0;
    }

    /**
     * Traiter un admin spécifique
     */
    private function processAdmin(Admin $admin)
    {
        $this->line("--- Traitement de l'admin: {$admin->name} (ID: {$admin->id}) ---");

        if ($this->option('stats')) {
            $this->showStats($admin->id);
        }

        if ($this->option('clean')) {
            $this->cleanOldDuplicates($admin->id);
        }

        if ($this->option('scan')) {
            $this->scanForDuplicates($admin->id);
        }

        if ($this->option('auto-merge')) {
            $this->performAutoMerge($admin->id);
        }

        // Si aucune option spécifique, faire un traitement complet
        if (!$this->option('stats') && !$this->option('clean') && 
            !$this->option('scan') && !$this->option('auto-merge')) {
            $this->showStats($admin->id);
            $this->scanForDuplicates($admin->id);
            $this->performAutoMerge($admin->id);
        }
    }

    /**
     * Afficher les statistiques des doublons
     */
    private function showStats($adminId)
    {
        $this->info("📊 Statistiques des doublons:");
        
        $stats = $this->getDuplicateStats($adminId);
        
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Commandes doubles non examinées', $stats['total_duplicates']],
                ['Clients uniques avec doublons', $stats['unique_clients']],
                ['Commandes fusionnées aujourd\'hui', $stats['merged_today']],
                ['Délai auto-fusion (heures)', $stats['auto_merge_delay']]
            ]
        );

        // Statistiques détaillées
        $duplicateGroups = Order::where('admin_id', $adminId)
            ->where('is_duplicate', true)
            ->where('reviewed_for_duplicates', false)
            ->select('customer_phone')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('customer_phone')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        if ($duplicateGroups->count() > 0) {
            $this->info("🔍 Top 10 des clients avec le plus de doublons:");
            $tableData = [];
            foreach ($duplicateGroups as $group) {
                $tableData[] = [$group->customer_phone, $group->count];
            }
            $this->table(['Téléphone', 'Nombre de doublons'], $tableData);
        }
    }

    /**
     * Scanner et détecter les doublons
     */
    private function scanForDuplicates($adminId)
    {
        $this->info("🔍 Scan des doublons en cours...");
        
        $bar = $this->output->createProgressBar(100);
        $bar->start();

        try {
            $result = $this->scanAllOrdersForDuplicates($adminId);
            
            $bar->finish();
            $this->newLine();

            if ($result['success']) {
                $this->info("✅ Scan terminé: {$result['duplicates_found']} doublons détectés dans {$result['groups_created']} groupes");
            } else {
                $this->error("❌ Erreur lors du scan: " . $result['error']);
            }
            
        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine();
            $this->error("❌ Erreur lors du scan: " . $e->getMessage());
            Log::error("Erreur dans la commande de maintenance des doublons", [
                'admin_id' => $adminId,
                'action' => 'scan',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Effectuer la fusion automatique
     */
    private function performAutoMerge($adminId)
    {
        $this->info("🔗 Fusion automatique en cours...");

        try {
            $result = $this->autoMergeDuplicates($adminId);

            if ($result['success']) {
                $this->info("✅ Fusion terminée: {$result['merged_count']} commandes fusionnées dans {$result['groups_processed']} groupes");
            } else {
                $this->error("❌ Erreur lors de la fusion: " . $result['error']);
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la fusion: " . $e->getMessage());
            Log::error("Erreur dans la commande de maintenance des doublons", [
                'admin_id' => $adminId,
                'action' => 'auto-merge',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Nettoyer les anciens marquages de doublons
     */
    private function cleanOldDuplicates($adminId)
    {
        $this->info("🧹 Nettoyage des anciens doublons...");

        try {
            // Nettoyer les doublons qui ne sont plus valides
            $cleanedCount = 0;

            // Trouver les commandes marquées comme doublons mais qui n'ont plus de "partenaires"
            $duplicateOrders = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->get();

            foreach ($duplicateOrders as $order) {
                $duplicates = $this->findDuplicateOrders($order);
                
                // Si pas de doublons trouvés, nettoyer le marquage
                if ($duplicates->count() === 0) {
                    $order->unmarkAsDuplicate();
                    $cleanedCount++;
                }
            }

            // Nettoyer les marquages de doublons pour les commandes annulées ou livrées
            $cleanedStatusCount = Order::where('admin_id', $adminId)
                ->where('is_duplicate', true)
                ->whereNotIn('status', ['nouvelle', 'datée'])
                ->update([
                    'is_duplicate' => false,
                    'reviewed_for_duplicates' => false,
                    'duplicate_group_id' => null
                ]);

            $totalCleaned = $cleanedCount + $cleanedStatusCount;
            $this->info("✅ Nettoyage terminé: {$totalCleaned} marquages de doublons supprimés");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors du nettoyage: " . $e->getMessage());
        }
    }
}