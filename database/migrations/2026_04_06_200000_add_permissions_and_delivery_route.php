<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────
        // 1. Granular permissions for Admin sub-accounts (manager / employee)
        // ──────────────────────────────────────
        Schema::table('admins', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role')
                ->comment('JSON: granular permissions for manager/employee');
        });

        // ──────────────────────────────────────
        // 2. Add emballage_enabled to orders table for per-order routing decision
        //    (may already exist on admins — we add an order-level override)
        // ──────────────────────────────────────
        if (!Schema::hasColumn('orders', 'delivery_route')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('delivery_route', 30)->nullable()->after('carrier_name')
                    ->comment('kolixy_company | kolixy_personal | manual');
            });
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });

        if (Schema::hasColumn('orders', 'delivery_route')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('delivery_route');
            });
        }
    }
};
