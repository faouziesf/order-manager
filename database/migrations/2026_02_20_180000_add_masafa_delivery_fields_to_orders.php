<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add delivery tracking columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'carrier_name')) {
                $table->string('carrier_name')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('carrier_name');
            }
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
            if (!Schema::hasColumn('orders', 'carrier_status_code')) {
                $table->string('carrier_status_code')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('orders', 'carrier_status_label')) {
                $table->string('carrier_status_label')->nullable()->after('carrier_status_code');
            }
        });

        // 2. Extend the status enum to include Masafa Express delivery statuses
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM(
            'nouvelle','confirmée','annulée','datée','ancienne','en_route','livrée',
            'pending','processing','on-hold','completed','cancelled','refunded','failed',
            'expédiée','en_transit','tentative_livraison','en_retour','échec_livraison'
        ) NOT NULL DEFAULT 'nouvelle'");
    }

    public function down(): void
    {
        // Revert status enum
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM(
            'nouvelle','confirmée','annulée','datée','ancienne','en_route','livrée',
            'pending','processing','on-hold','completed','cancelled','refunded','failed'
        ) NOT NULL DEFAULT 'nouvelle'");

        Schema::table('orders', function (Blueprint $table) {
            $cols = ['tracking_number','carrier_name','shipped_at','delivered_at','carrier_status_code','carrier_status_label'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
