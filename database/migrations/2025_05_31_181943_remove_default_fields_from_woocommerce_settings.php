<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('woocommerce_settings', function (Blueprint $table) {
            // Supprimer les colonnes de configuration par défaut qui ne sont plus nécessaires
            $table->dropForeign(['default_governorate_id']);
            $table->dropForeign(['default_city_id']);
            $table->dropColumn([
                'default_status',
                'default_priority', 
                'default_governorate_id',
                'default_city_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('woocommerce_settings', function (Blueprint $table) {
            // Restaurer les colonnes si besoin
            $table->string('default_status')->default('nouvelle');
            $table->string('default_priority')->default('normale');
            $table->foreignId('default_governorate_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('default_city_id')->nullable()->constrained('cities')->onDelete('set null');
        });
    }
};