<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wix_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            
            // Informations API Wix
            $table->string('account_id')->nullable();
            $table->string('api_key')->nullable();
            $table->string('site_display_name')->nullable();
            
            // Configuration synchronisation
            $table->timestamp('first_sync_date')->nullable();
            $table->integer('resync_day_of_week')->nullable(); // 0=Sunday, 1=Monday, etc.
            $table->time('resync_time')->default('02:00:00');
            
            // Statuts
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_error')->nullable();
            
            // Metadata
            $table->integer('total_imported')->default(0);
            $table->integer('total_updated')->default(0);
            $table->timestamps();
            
            // Index
            $table->unique(['admin_id', 'account_id']);
            $table->index(['admin_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wix_integrations');
    }
};
