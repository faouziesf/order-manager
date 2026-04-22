<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheets_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            
            // Configuration Google Sheets
            $table->string('spreadsheet_id')->nullable();
            $table->string('sheet_name')->default('Orders');
            $table->string('sheet_range')->default('A:Z');
            
            // Paramètres d'import
            $table->enum('import_type', ['published_csv', 'oauth2'])->default('published_csv');
            $table->string('csv_url')->nullable();
            $table->text('oauth_token')->nullable();
            
            // Configuration synchronisation
            $table->timestamp('first_sync_date')->nullable();
            $table->integer('resync_day_of_week')->nullable();
            $table->time('resync_time')->default('03:00:00');
            $table->boolean('auto_sync')->default(false);
            
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
            $table->unique(['admin_id']);
            $table->index(['is_active', 'auto_sync']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheets_integrations');
    }
};
