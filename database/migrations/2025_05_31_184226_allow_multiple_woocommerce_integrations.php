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
        // Vérifier s'il y a une contrainte unique sur admin_id et la supprimer
        Schema::table('woocommerce_settings', function (Blueprint $table) {
            // Ajouter une contrainte unique sur admin_id + store_url pour éviter les doublons
            $table->unique(['admin_id', 'store_url'], 'unique_admin_store');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('woocommerce_settings', function (Blueprint $table) {
            $table->dropUnique('unique_admin_store');
        });
    }
};