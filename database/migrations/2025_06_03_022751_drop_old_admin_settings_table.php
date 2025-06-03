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
        // Sauvegarder les anciennes données avant suppression (optionnel)
        if (Schema::hasTable('admin_settings')) {
            // Optionnel: créer une sauvegarde des paramètres existants
            Schema::create('admin_settings_backup', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->text('value')->nullable();
                $table->timestamps();
            });
            
            // Copier les données existantes
            \DB::statement('INSERT INTO admin_settings_backup (key, value, created_at, updated_at) SELECT key, value, created_at, updated_at FROM admin_settings');
            
            // Supprimer l'ancienne table
            Schema::dropIfExists('admin_settings');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer l'ancienne structure si nécessaire
        if (!Schema::hasTable('admin_settings') && Schema::hasTable('admin_settings_backup')) {
            Schema::create('admin_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
            
            \DB::statement('INSERT INTO admin_settings (key, value, created_at, updated_at) SELECT key, value, created_at, updated_at FROM admin_settings_backup');
        }
    }
};