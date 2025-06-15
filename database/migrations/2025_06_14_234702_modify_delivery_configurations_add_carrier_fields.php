<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_configurations', function (Blueprint $table) {
            // Supprimer l'index unique existant sur admin_id
            $table->dropUnique(['admin_id']);
            
            // Ajouter les nouveaux champs
            $table->string('carrier_slug')->after('admin_id')->default('fparcel');
            $table->string('integration_name')->after('carrier_slug');
            
            // Ajouter l'index unique composé
            $table->unique(['admin_id', 'carrier_slug', 'integration_name'], 'delivery_config_unique');
        });
    }

    public function down()
    {
        Schema::table('delivery_configurations', function (Blueprint $table) {
            // Supprimer l'index unique composé
            $table->dropUnique('delivery_config_unique');
            
            // Supprimer les nouveaux champs
            $table->dropColumn(['carrier_slug', 'integration_name']);
            
            // Remettre l'index unique sur admin_id
            $table->unique('admin_id');
        });
    }
};