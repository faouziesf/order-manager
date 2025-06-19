<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Rendre pos_barcode nullable car il sera rempli lors de la validation
            $table->string('pos_barcode')->nullable()->change();
            
            // Ajouter carrier_slug s'il n'existe pas
            if (!Schema::hasColumn('shipments', 'carrier_slug')) {
                $table->string('carrier_slug')->after('order_id');
            }
            
            // Rendre return_barcode nullable aussi
            if (Schema::hasColumn('shipments', 'return_barcode')) {
                $table->string('return_barcode')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Remettre pos_barcode comme non nullable si besoin
            // $table->string('pos_barcode')->nullable(false)->change();
        });
    }
};