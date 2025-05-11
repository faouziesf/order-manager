<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('woocommerce_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->string('store_url');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->boolean('is_active')->default(false);
            $table->enum('sync_status', ['idle', 'syncing', 'error'])->default('idle');
            $table->string('sync_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('default_status')->default('nouvelle');
            $table->string('default_priority')->default('normale');
            $table->foreignId('default_governorate_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('default_city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('woocommerce_settings');
    }
};