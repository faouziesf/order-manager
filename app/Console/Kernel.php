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
        // Réinitialiser les compteurs journaliers chaque jour à minuit
        $schedule->command('orders:reset-daily-counters')
                 ->dailyAt('00:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/daily-reset.log'));

        // Optionnel: Nettoyer les anciens logs de réinitialisation chaque semaine
        $schedule->call(function () {
            $logFile = storage_path('logs/daily-reset.log');
            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) { // 10MB
                // Garder seulement les 1000 dernières lignes
                $lines = file($logFile);
                $lastLines = array_slice($lines, -1000);
                file_put_contents($logFile, implode('', $lastLines));
            }
        })->weekly()->sundays()->at('02:00');

        // Optionnel: Vérifier et réactiver les commandes suspendues dont le stock est redevenu disponible
        $schedule->call(function () {
            \App\Models\Order::suspended()
                ->whereNotNull('suspension_reason')
                ->where('suspension_reason', 'like', 'Rupture de stock%')
                ->chunk(100, function ($orders) {
                    foreach ($orders as $order) {
                        $order->checkStockAndUpdateStatus();
                    }
                });
        })->hourly();
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