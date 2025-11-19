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
        Schema::create('prestashop_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('shop_url'); // URL de la boutique PrestaShop
            $table->string('api_key'); // Clé Web Service
            $table->boolean('is_active')->default(false);
            $table->enum('sync_status', ['idle', 'syncing', 'error'])->default('idle');
            $table->string('sync_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestashop_settings');
    }
};
