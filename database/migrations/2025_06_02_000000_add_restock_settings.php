<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les nouveaux paramètres pour l'interface de retour en stock
        DB::table('admin_settings')->insert([
            // Paramètres pour la file de retour en stock
            ['key' => 'restock_max_daily_attempts', 'value' => '3'],
            ['key' => 'restock_delay_hours', 'value' => '4'],
            ['key' => 'restock_max_total_attempts', 'value' => '10'],
            
            // Paramètres pour l'interface d'examen
            ['key' => 'examination_auto_refresh_interval', 'value' => '120'], // secondes
            ['key' => 'examination_max_orders_per_page', 'value' => '50'],
            
            // Paramètres pour les commandes suspendues
            ['key' => 'suspended_auto_check_interval', 'value' => '300'], // secondes
            ['key' => 'suspended_max_orders_per_page', 'value' => '30'],
            
            // Paramètres généraux pour la suspension automatique
            ['key' => 'auto_suspend_on_stock_issue', 'value' => '1'], // 1 = oui, 0 = non
            ['key' => 'auto_suspend_threshold_days', 'value' => '7'], // suspendre après X jours sans stock
            ['key' => 'restock_notification_enabled', 'value' => '1'], // notifications de retour en stock
            
            // Paramètres de performance
            ['key' => 'stock_check_cache_duration', 'value' => '300'], // cache en secondes
            ['key' => 'bulk_action_max_orders', 'value' => '100'], // max d'orders par action groupée
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les paramètres ajoutés
        DB::table('admin_settings')->whereIn('key', [
            'restock_max_daily_attempts',
            'restock_delay_hours', 
            'restock_max_total_attempts',
            'examination_auto_refresh_interval',
            'examination_max_orders_per_page',
            'suspended_auto_check_interval',
            'suspended_max_orders_per_page',
            'auto_suspend_on_stock_issue',
            'auto_suspend_threshold_days',
            'restock_notification_enabled',
            'stock_check_cache_duration',
            'bulk_action_max_orders'
        ])->delete();
    }
};