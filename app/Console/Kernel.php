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
        // Synchroniser les commandes WooCommerce toutes les 15 minutes
        $schedule->command('woocommerce:sync')->everyFifteenMinutes();
        
        // Réinitialiser les compteurs de tentatives quotidiennes à minuit
        $schedule->command('orders:reset-daily-attempts')->dailyAt('00:00');
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