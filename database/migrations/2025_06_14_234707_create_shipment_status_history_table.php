<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipment_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id'); // Links to shipments
            $table->string('carrier_status_code')->nullable(); // e.g., "3"
            $table->string('carrier_status_label')->nullable(); // e.g., "Colis récupéré"
            $table->string('internal_status'); // e.g., "picked_up_by_carrier"
            $table->timestamp('created_at');

            // Index et contraintes
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->index('shipment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_status_history');
    }
};