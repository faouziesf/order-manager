<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\WooCommerceController;
use App\Models\WooCommerceSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWooCommerceOrders extends Command
{
    protected $signature = 'woocommerce:sync';
    protected $description = 'Synchronize orders from WooCommerce';

    public function handle()
    {
        $this->info('Starting WooCommerce synchronization...');
        
        // Vérifier s'il y a des paramètres WooCommerce actifs
        $activeSettings = WooCommerceSetting::where('is_active', true)->count();
        
        if ($activeSettings === 0) {
            $this->warn('No active WooCommerce integrations found.');
            return 0;
        }
        
        try {
            $wooController = new WooCommerceController();
            $result = $wooController->syncOrders();
            
            $this->info('Synchronization completed: ' . $result['total_imported'] . ' orders imported.');
            
            if (count($result['errors']) > 0) {
                $this->warn('There were some errors:');
                foreach ($result['errors'] as $error) {
                    $this->error('- ' . $error);
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            Log::error('WooCommerce sync command error: ' . $e->getMessage());
            return 1;
        }
    }
}