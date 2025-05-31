<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateOldOrders extends Command
{
    protected $signature = 'orders:migrate-old {--dry-run : Afficher les changements sans les appliquer}';
    
    protected $description = 'Migre les commandes existantes qui ont dépassé le seuil standard vers le statut "ancienne"';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔄 Migration des commandes vers le statut "ancienne"...');
        $this->newLine();
        
        try {
            // Récupérer le seuil standard
            $standardMaxAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
            
            $this->info("📊 Seuil standard configuré : {$standardMaxAttempts} tentatives");
            
            // Trouver les commandes éligibles
            $eligibleOrders = Order::where('status', 'nouvelle')
                ->where('attempts_count', '>=', $standardMaxAttempts)
                ->get();
                
            $count = $eligibleOrders->count();
            
            if ($count === 0) {
                $this->info('✅ Aucune commande à migrer.');
                return 0;
            }
            
            $this->info("📋 {$count} commande(s) éligible(s) pour migration :");
            
            // Afficher les détails des commandes
            $this->table(
                ['ID', 'Admin', 'Tentatives', 'Dernière tentative', 'Client'],
                $eligibleOrders->map(function($order) {
                    return [
                        $order->id,
                        $order->admin->name ?? 'N/A',
                        $order->attempts_count,
                        $order->last_attempt_at ? $order->last_attempt_at->format('d/m/Y H:i') : 'Jamais',
                        $order->customer_name ?: substr($order->customer_phone, 0, 10) . '...'
                    ];
                })
            );
            
            if ($isDryRun) {
                $this->warn('🧪 Mode dry-run : Aucune modification appliquée.');
                return 0;
            }
            
            // Demander confirmation
            if (!$this->confirm("Voulez-vous procéder à la migration de {$count} commande(s) ?")) {
                $this->info('❌ Migration annulée.');
                return 0;
            }
            
            // Procéder à la migration
            $migrated = 0;
            
            DB::beginTransaction();
            
            foreach ($eligibleOrders as $order) {
                try {
                    $previousStatus = $order->status;
                    $order->status = 'ancienne';
                    $order->save();
                    
                    // Enregistrer dans l'historique
                    $order->recordHistory(
                        'changement_statut',
                        "Migration automatique vers file ancienne - Commande avait {$order->attempts_count} tentatives (seuil: {$standardMaxAttempts})",
                        [
                            'migration_type' => 'automatic',
                            'attempts_count' => $order->attempts_count,
                            'threshold' => $standardMaxAttempts
                        ],
                        $previousStatus,
                        'ancienne'
                    );
                    
                    $migrated++;
                    $this->info("  ✓ Commande #{$order->id} migrée");
                    
                } catch (\Exception $e) {
                    $this->error("  ✗ Erreur commande #{$order->id}: " . $e->getMessage());
                    Log::error("Erreur migration commande {$order->id}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("✅ Migration terminée : {$migrated}/{$count} commandes migrées avec succès.");
            
            if ($migrated < $count) {
                $this->warn("⚠️  " . ($count - $migrated) . " commande(s) n'ont pas pu être migrées (voir les logs).");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erreur lors de la migration : ' . $e->getMessage());
            Log::error('Erreur migration commandes anciennes: ' . $e->getMessage());
            return 1;
        }
    }
}