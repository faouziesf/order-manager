<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('name'); // e.g., "Main Warehouse"
            $table->string('contact_name'); // maps to Fparcel's ENL_CONTACT_NOM
            $table->text('address'); // maps to ENL_ADRESSE
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable(); // maps to Fparcel's ENL_TELEPHONE
            $table->string('email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index et contraintes
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'is_active']);
            $table->unique(['admin_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pickup_addresses');
    }
};