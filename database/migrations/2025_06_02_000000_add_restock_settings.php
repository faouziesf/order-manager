<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['setting_key' => 'restock_max_daily_attempts', 'setting_value' => '3'],
            ['setting_key' => 'restock_delay_hours', 'setting_value' => '4'],
            ['setting_key' => 'restock_max_total_attempts', 'setting_value' => '10'],
            ['setting_key' => 'examination_auto_refresh_interval', 'setting_value' => '120'],
            ['setting_key' => 'examination_max_orders_per_page', 'setting_value' => '50'],
            ['setting_key' => 'suspended_auto_check_interval', 'setting_value' => '300'],
            ['setting_key' => 'suspended_max_orders_per_page', 'setting_value' => '30'],
            ['setting_key' => 'auto_suspend_on_stock_issue', 'setting_value' => '1'],
            ['setting_key' => 'auto_suspend_threshold_days', 'setting_value' => '7'],
            ['setting_key' => 'restock_notification_enabled', 'setting_value' => '1'],
            ['setting_key' => 'stock_check_cache_duration', 'setting_value' => '300'],
            ['setting_key' => 'bulk_action_max_orders', 'setting_value' => '100'],
        ];

        $admins = DB::table('admins')->select('id')->get();

        foreach ($admins as $admin) {
            foreach ($settings as $setting) {
                $exists = DB::table('admin_settings')
                    ->where('admin_id', $admin->id)
                    ->where('setting_key', $setting['setting_key'])
                    ->exists();

                if (!$exists) {
                    DB::table('admin_settings')->insert([
                        'admin_id' => $admin->id,
                        'setting_key' => $setting['setting_key'],
                        'setting_value' => $setting['setting_value'],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $keys = [
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
            'bulk_action_max_orders',
        ];

        DB::table('admin_settings')->whereIn('setting_key', $keys)->delete();
    }
};
