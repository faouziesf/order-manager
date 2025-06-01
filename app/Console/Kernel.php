<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Synchronisation WooCommerce toutes les 3 minutes
        $schedule->command('woocommerce:sync')
            ->everyThreeMinutes()
            ->withoutOverlapping(10) // Timeout après 10 minutes si bloqué
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('WooCommerce sync completed successfully via scheduler');
            })
            ->onFailure(function () {
                \Log::error('WooCommerce sync failed via scheduler');
            });
        
        // Réinitialisation des tentatives journalières à minuit
        $schedule->call(function () {
            \App\Models\Order::query()->update(['daily_attempts_count' => 0]);
            \Log::info('Daily attempts count reset completed');
        })->dailyAt('00:00')->name('reset-daily-attempts');
        
        // Nettoyage des anciens logs WooCommerce (optionnel, hebdomadaire)
        $schedule->call(function () {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/laravel-*.log');
            
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-30 days')) {
                    unlink($file);
                }
            }
            
            \Log::info('Old log files cleaned up');
        })->weekly()->sundays()->at('02:00')->name('cleanup-logs');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}