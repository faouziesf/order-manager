<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà avant de les ajouter
            if (!Schema::hasColumn('orders', 'is_duplicate')) {
                $table->boolean('is_duplicate')->default(false)->after('is_suspended');
            }
            
            if (!Schema::hasColumn('orders', 'reviewed_for_duplicates')) {
                $table->boolean('reviewed_for_duplicates')->default(false)->after('is_duplicate');
            }
            
            if (!Schema::hasColumn('orders', 'duplicate_group_id')) {
                $table->string('duplicate_group_id')->nullable()->after('reviewed_for_duplicates');
            }
        });

        // Ajouter les nouvelles actions dans order_history seulement si nécessaire
        $existingActions = DB::select("PRAGMA table_info(order_history)");
        $actionColumn = collect($existingActions)->firstWhere('name', 'action');
        
        // Vérifier si les nouvelles actions existent déjà
        $hasNewActions = false;
        if ($actionColumn) {
            $sampleOrderHistory = DB::table('order_history')->first();
            if ($sampleOrderHistory) {
                try {
                    // Tenter d'insérer une action de test pour voir si elle est acceptée
                    DB::table('order_history')->insert([
                        'order_id' => 999999, // ID qui n'existe pas
                        'action' => 'duplicate_detected',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    // Si ça marche, supprimer l'enregistrement de test
                    DB::table('order_history')->where('order_id', 999999)->delete();
                    $hasNewActions = true;
                } catch (\Exception $e) {
                    // Les nouvelles actions n'existent pas encore
                    $hasNewActions = false;
                }
            }
        }

        if (!$hasNewActions && DB::getDriverName() === 'sqlite') {
            // Pour SQLite, recréer la table avec les nouvelles actions
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
                    'changement_statut',
                    'duplicate_detected',   // NOUVEAU
                    'duplicate_review',     // NOUVEAU
                    'duplicate_merge',      // NOUVEAU
                    'duplicate_ignore',     // NOUVEAU
                    'duplicate_cancel'      // NOUVEAU
                ]);
                $table->string('status_before')->nullable();
                $table->string('status_after')->nullable();
                $table->text('notes')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
            });

            // Copier les données existantes
            DB::statement('INSERT INTO order_history_temp (id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at) SELECT id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at FROM order_history');

            // Supprimer l'ancienne table et renommer
            Schema::dropIfExists('order_history');
            Schema::rename('order_history_temp', 'order_history');
        }

        // Ajouter le paramètre de délai auto-fusion par défaut si il n'existe pas
        if (Schema::hasTable('admin_settings')) {
            $existingSetting = DB::table('admin_settings')
                ->where('setting_key', 'duplicate_auto_merge_delay_hours')
                ->first();
                
            if (!$existingSetting) {
                DB::table('admin_settings')->insert([
                    'admin_id' => 1, // Sera mis à jour pour chaque admin
                    'setting_key' => 'duplicate_auto_merge_delay_hours',
                    'setting_value' => '2',
                    'setting_type' => 'integer',
                    'description' => 'Délai en heures pour la fusion automatique des commandes doubles',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Vérifier avant de supprimer
            if (Schema::hasColumn('orders', 'duplicate_group_id')) {
                $table->dropColumn('duplicate_group_id');
            }
            if (Schema::hasColumn('orders', 'reviewed_for_duplicates')) {
                $table->dropColumn('reviewed_for_duplicates');
            }
            if (Schema::hasColumn('orders', 'is_duplicate')) {
                $table->dropColumn('is_duplicate');
            }
        });

        // Restaurer les actions dans order_history
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
                    'réactivation',
                    'changement_statut'
                ]);
                $table->string('status_before')->nullable();
                $table->string('status_after')->nullable();
                $table->text('notes')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
            });

            DB::statement('INSERT INTO order_history_temp (id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at) SELECT id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at FROM order_history WHERE action NOT IN ("duplicate_detected", "duplicate_review", "duplicate_merge", "duplicate_ignore", "duplicate_cancel")');

            Schema::dropIfExists('order_history');
            Schema::rename('order_history_temp', 'order_history');
        }

        // Supprimer le paramètre
        if (Schema::hasTable('admin_settings')) {
            DB::table('admin_settings')->where('setting_key', 'duplicate_auto_merge_delay_hours')->delete();
        }
    }
};