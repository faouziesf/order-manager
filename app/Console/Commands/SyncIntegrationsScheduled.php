<?php

namespace App\Console\Commands;

use App\Models\WooCommerceSetting;
use App\Models\ShopifySetting;
use App\Models\PrestashopSetting;
use App\Models\WixIntegration;
use App\Models\GoogleSheetsIntegration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncIntegrationsScheduled extends Command
{
    protected $signature = 'sync:integrations-scheduled';
    protected $description = 'Synchroniser les intégrations en fonction du jour et de l\'heure configurés';

    public function handle()
    {
        $today = now()->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
        $currentHour = now()->hour;

        Log::info('Starting scheduled integrations sync', [
            'day_of_week' => $today,
            'current_hour' => $currentHour,
        ]);

        $synced = 0;

        // Synchroniser WooCommerce
        $wooIntegrations = WooCommerceSetting::where('is_active', true)
            ->where('resync_day_of_week', $today)
            ->get();

        foreach ($wooIntegrations as $integration) {
            if ($this->shouldSyncNow($integration->resync_time, $currentHour)) {
                try {
                    // Appeler le sync pour cette intégration
                    \Artisan::call('sync:woocommerce', ['id' => $integration->id]);
                    $synced++;
                    Log::info('WooCommerce sync completed', ['integration_id' => $integration->id]);
                } catch (\Exception $e) {
                    Log::error('WooCommerce sync failed', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Synchroniser Shopify
        $shopifyIntegrations = ShopifySetting::where('is_active', true)
            ->where('resync_day_of_week', $today)
            ->get();

        foreach ($shopifyIntegrations as $integration) {
            if ($this->shouldSyncNow($integration->resync_time, $currentHour)) {
                try {
                    \Artisan::call('sync:shopify', ['id' => $integration->id]);
                    $synced++;
                    Log::info('Shopify sync completed', ['integration_id' => $integration->id]);
                } catch (\Exception $e) {
                    Log::error('Shopify sync failed', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Synchroniser PrestaShop
        $prestashopIntegrations = PrestashopSetting::where('is_active', true)
            ->where('resync_day_of_week', $today)
            ->get();

        foreach ($prestashopIntegrations as $integration) {
            if ($this->shouldSyncNow($integration->resync_time, $currentHour)) {
                try {
                    \Artisan::call('sync:prestashop', ['id' => $integration->id]);
                    $synced++;
                    Log::info('PrestaShop sync completed', ['integration_id' => $integration->id]);
                } catch (\Exception $e) {
                    Log::error('PrestaShop sync failed', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Synchroniser Wix
        $wixIntegrations = WixIntegration::where('is_active', true)
            ->where('resync_day_of_week', $today)
            ->get();

        foreach ($wixIntegrations as $integration) {
            if ($this->shouldSyncNow($integration->resync_time, $currentHour)) {
                try {
                    \Artisan::call('sync:wix', ['id' => $integration->id]);
                    $synced++;
                    Log::info('Wix sync completed', ['integration_id' => $integration->id]);
                } catch (\Exception $e) {
                    Log::error('Wix sync failed', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Synchroniser Google Sheets
        $googleSheetIntegrations = GoogleSheetsIntegration::where('is_active', true)
            ->where('auto_sync', true)
            ->where('resync_day_of_week', $today)
            ->get();

        foreach ($googleSheetIntegrations as $integration) {
            if ($this->shouldSyncNow($integration->resync_time, $currentHour)) {
                try {
                    \Artisan::call('sync:google-sheets', ['id' => $integration->id]);
                    $synced++;
                    Log::info('Google Sheets sync completed', ['integration_id' => $integration->id]);
                } catch (\Exception $e) {
                    Log::error('Google Sheets sync failed', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("✓ Synchronized $synced integrations");
        Log::info('Scheduled integrations sync completed', ['synced' => $synced]);
    }

    /**
     * Vérifie si une synchronisation doit avoir lieu maintenant
     * Accepte une fenêtre de ±30 minutes autour de l'heure configurée
     */
    private function shouldSyncNow($resyncTime, $currentHour): bool
    {
        if (!$resyncTime) {
            return false;
        }

        $configuredHour = (int) explode(':', $resyncTime)[0];
        $configuredMinute = (int) explode(':', $resyncTime)[1] ?? 0;

        $now = now();
        $configuredTime = now()
            ->setHour($configuredHour)
            ->setMinute($configuredMinute)
            ->setSecond(0);

        // Vérifier si on est dans une fenêtre de ±30 minutes
        $difference = abs($now->diffInMinutes($configuredTime));
        return $difference <= 30;
    }
}
