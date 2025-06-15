<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bl_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('carrier_slug')->nullable(); // Null for default template
            $table->string('template_name');
            $table->json('layout_config'); // JSON storing field positions, fonts, logo, etc.
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index et contraintes
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'carrier_slug', 'is_active']);
            $table->unique(['admin_id', 'carrier_slug', 'template_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bl_templates');
    }
};