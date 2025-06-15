<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Renommer la table
        Schema::rename('delivery_positions', 'shipments');
        
        // Modifier la structure de la table
        Schema::table('shipments', function (Blueprint $table) {
            // Ajouter de nouveaux champs
            $table->unsignedBigInteger('pickup_id')->nullable()->after('order_id');
            $table->string('return_barcode')->unique()->nullable()->after('pos_barcode');
            $table->timestamp('carrier_last_status_update')->nullable()->after('delivered_at');
            
            // Ajouter la clé étrangère pour pickup_id
            $table->foreign('pickup_id')->references('id')->on('pickups')->onDelete('set null');
        });
        
        // Modifier l'enum status pour inclure les nouvelles valeurs
        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('status', [
                'created', 
                'validated', 
                'picked_up_by_carrier', 
                'in_transit', 
                'delivered', 
                'cancelled', 
                'in_return', 
                'anomaly'
            ])->default('created')->change();
        });
    }

    public function down()
    {
        // Supprimer les nouvelles contraintes et colonnes
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['pickup_id']);
            $table->dropColumn(['pickup_id', 'return_barcode', 'carrier_last_status_update']);
            
            // Remettre l'ancien enum status
            $table->enum('status', ['created', 'validated', 'in_transit', 'delivered', 'cancelled'])->default('created')->change();
        });
        
        // Renommer la table en arrière
        Schema::rename('shipments', 'delivery_positions');
    }
};