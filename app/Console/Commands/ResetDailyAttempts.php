<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetDailyAttempts extends Command
{
    protected $signature = 'orders:reset-daily-attempts';
    protected $description = 'Reset daily attempts counter for all orders';

    public function handle()
    {
        $this->info('Resetting daily attempts counters...');
        
        try {
            // Réinitialiser tous les compteurs quotidiens
            $affectedRows = Order::whereIn('status', ['nouvelle', 'datée'])
                ->where('daily_attempts_count', '>', 0)
                ->update(['daily_attempts_count' => 0]);
            
            $this->info("Successfully reset daily attempts for {$affectedRows} orders.");
            Log::info("Daily attempts reset for {$affectedRows} orders.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error resetting daily attempts: ' . $e->getMessage());
            Log::error('Error in ResetDailyAttempts command: ' . $e->getMessage());
            
            return 1;
        }
    }
}