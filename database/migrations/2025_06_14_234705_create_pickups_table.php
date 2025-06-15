<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('carrier_slug');
            $table->unsignedBigInteger('delivery_configuration_id'); // Links to carrier account
            $table->unsignedBigInteger('pickup_address_id')->nullable(); // Null if carrier doesn't support address selection
            $table->enum('status', ['draft', 'validated', 'picked_up', 'problem'])->default('draft');
            $table->date('pickup_date')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            // Index et contraintes
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('delivery_configuration_id')->references('id')->on('delivery_configurations')->onDelete('cascade');
            $table->foreign('pickup_address_id')->references('id')->on('pickup_addresses')->onDelete('set null');
            $table->index(['admin_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pickups');
    }
};