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
        // Supprimer la colonne pickup_address_id de la table pickups
        if (Schema::hasColumn('pickups', 'pickup_address_id')) {
            Schema::table('pickups', function (Blueprint $table) {
                $table->dropForeign(['pickup_address_id']);
                $table->dropColumn('pickup_address_id');
            });
        }

        // Supprimer la table shipment_status_history
        Schema::dropIfExists('shipment_status_history');

        // Supprimer la table pickup_addresses
        Schema::dropIfExists('pickup_addresses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table pickup_addresses
        Schema::create('pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('name');
            $table->string('contact_name');
            $table->text('address');
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'is_active']);
            $table->unique(['admin_id', 'name']);
        });

        // Recréer la table shipment_status_history
        Schema::create('shipment_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->string('carrier_status_code')->nullable();
            $table->string('carrier_status_label')->nullable();
            $table->string('internal_status');
            $table->timestamp('created_at');

            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->index('shipment_id');
        });

        // Ajouter la colonne pickup_address_id à la table pickups
        Schema::table('pickups', function (Blueprint $table) {
            $table->unsignedBigInteger('pickup_address_id')->nullable()->after('delivery_configuration_id');
            $table->foreign('pickup_address_id')->references('id')->on('pickup_addresses')->onDelete('set null');
        });
    }
};