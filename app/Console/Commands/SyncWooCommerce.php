<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\WooCommerceController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWooCommerce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'woocommerce:sync {--force : Force une synchronisation complète}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise automatiquement les commandes WooCommerce toutes les 3 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Début de la synchronisation WooCommerce automatique...');
        
        $startTime = microtime(true);
        
        try {
            $controller = new WooCommerceController();
            $results = $controller->autoSync();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->info("✅ Synchronisation terminée en {$duration}s:");
            $this->info("   📥 {$results['total_imported']} commandes importées");
            $this->info("   🔄 {$results['total_updated']} commandes mises à jour");
            
            if (!empty($results['errors'])) {
                $this->warn("⚠️  Erreurs rencontrées:");
                foreach ($results['errors'] as $error) {
                    $this->error("   - {$error}");
                }
            }
            
            // Log pour monitoring
            Log::info('WooCommerce sync completed', [
                'imported' => $results['total_imported'],
                'updated' => $results['total_updated'],
                'errors' => count($results['errors']),
                'duration' => $duration
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la synchronisation: {$e->getMessage()}");
            
            Log::error('WooCommerce sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}