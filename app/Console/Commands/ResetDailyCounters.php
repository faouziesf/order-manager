<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Log;

class ResetDailyCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:reset-daily-counters {--admin-id= : Reset for specific admin only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily attempt counters for all orders at midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Début de la réinitialisation des compteurs journaliers...');
        
        try {
            $query = Order::where('daily_attempts_count', '>', 0);
            
            // Si un admin spécifique est demandé
            if ($this->option('admin-id')) {
                $adminId = $this->option('admin-id');
                $query->where('admin_id', $adminId);
                $this->info("Réinitialisation pour l'admin ID: {$adminId}");
            }
            
            // Compter les commandes affectées
            $affectedCount = $query->count();
            
            if ($affectedCount === 0) {
                $this->info('✅ Aucun compteur journalier à réinitialiser.');
                return Command::SUCCESS;
            }
            
            // Réinitialiser les compteurs
            $updated = $query->update(['daily_attempts_count' => 0]);
            
            // Mettre à jour la date de dernière réinitialisation globale
            if (!$this->option('admin-id')) {
                AdminSetting::set('last_global_daily_reset', now()->format('Y-m-d H:i:s'));
            }
            
            $this->info("✅ {$updated} compteurs journaliers réinitialisés avec succès.");
            
            // Log pour traçabilité
            Log::info('Réinitialisation des compteurs journaliers', [
                'affected_orders' => $affectedCount,
                'updated_orders' => $updated,
                'admin_id' => $this->option('admin-id') ?: 'all',
                'timestamp' => now()
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la réinitialisation: ' . $e->getMessage());
            
            Log::error('Erreur lors de la réinitialisation des compteurs journaliers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => $this->option('admin-id') ?: 'all'
            ]);
            
            return Command::FAILURE;
        }
    }
}