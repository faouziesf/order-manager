<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            // Créer une table temporaire avec les nouvelles valeurs enum
            Schema::create('order_history_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('user_type')->nullable();
                $table->enum('action', [
                    'création', 
                    'modification', 
                    'confirmation', 
                    'annulation', 
                    'datation', 
                    'tentative', 
                    'livraison',
                    'assignation',
                    'désassignation',
                    'en_route',
                    'suspension',
                    'réactivation',
                    'changement_statut'  // NOUVEAU
                ]);
                $table->string('status_before')->nullable();
                $table->string('status_after')->nullable();
                $table->text('notes')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
            });

            // Copier les données existantes
            DB::statement('INSERT INTO order_history_temp (id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at) SELECT id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at FROM order_history');

            // Supprimer l'ancienne table
            Schema::dropIfExists('order_history');

            // Renommer la nouvelle table
            Schema::rename('order_history_temp', 'order_history');
        } else {
            // Pour MySQL/PostgreSQL
            DB::statement("ALTER TABLE order_history MODIFY COLUMN action ENUM('création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison', 'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation', 'changement_statut')");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('order_history_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('user_type')->nullable();
                $table->enum('action', [
                    'création', 
                    'modification', 
                    'confirmation', 
                    'annulation', 
                    'datation', 
                    'tentative', 
                    'livraison',
                    'assignation',
                    'désassignation',
                    'en_route',
                    'suspension',
                    'réactivation'
                ]);
                $table->string('status_before')->nullable();
                $table->string('status_after')->nullable();
                $table->text('notes')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
            });

            DB::statement('INSERT INTO order_history_temp (id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at) SELECT id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at FROM order_history WHERE action != "changement_statut"');

            Schema::dropIfExists('order_history');
            Schema::rename('order_history_temp', 'order_history');
        } else {
            DB::statement("ALTER TABLE order_history MODIFY COLUMN action ENUM('création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison', 'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation')");
        }
    }
};