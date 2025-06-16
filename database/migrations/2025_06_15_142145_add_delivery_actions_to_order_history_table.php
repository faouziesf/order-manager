<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ajouter les nouvelles actions pour la livraison dans order_history
        if (DB::getDriverName() === 'sqlite') {
            // Pour SQLite, recréer la table avec les nouvelles actions
            Schema::create('order_history_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('user_type')->nullable();
                $table->enum('action', [
                    'création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison',
                    'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation', 'changement_statut',
                    'duplicate_detected', 'duplicate_review', 'duplicate_merge', 'duplicate_ignore', 'duplicate_cancel',
                    // NOUVELLES ACTIONS POUR LA LIVRAISON
                    'shipment_created', 'shipment_validated', 'pickup_created', 'pickup_validated',
                    'picked_up_by_carrier', 'in_transit', 'delivery_attempted', 'delivery_failed',
                    'in_return', 'delivery_anomaly', 'tracking_updated'
                ]);
                $table->string('status_before')->nullable();
                $table->string('status_after')->nullable();
                $table->text('notes')->nullable();
                $table->json('changes')->nullable();
                
                // NOUVEAUX CHAMPS POUR LA LIVRAISON
                $table->string('carrier_status_code')->nullable();
                $table->string('carrier_status_label')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('carrier_name')->nullable();
                
                $table->timestamps();
            });

            // Copier les données existantes
            DB::statement('INSERT INTO order_history_temp (id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at) SELECT id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at FROM order_history');

            Schema::dropIfExists('order_history');
            Schema::rename('order_history_temp', 'order_history');
        } else {
            // Pour MySQL/PostgreSQL - Ajouter les nouveaux champs
            Schema::table('order_history', function (Blueprint $table) {
                $table->string('carrier_status_code')->nullable()->after('changes');
                $table->string('carrier_status_label')->nullable()->after('carrier_status_code');
                $table->string('tracking_number')->nullable()->after('carrier_status_label');
                $table->string('carrier_name')->nullable()->after('tracking_number');
            });
            
            // Modifier l'enum pour ajouter les nouvelles actions
            DB::statement("ALTER TABLE order_history MODIFY COLUMN action ENUM('création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison', 'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation', 'changement_statut', 'duplicate_detected', 'duplicate_review', 'duplicate_merge', 'duplicate_ignore', 'duplicate_cancel', 'shipment_created', 'shipment_validated', 'pickup_created', 'pickup_validated', 'picked_up_by_carrier', 'in_transit', 'delivery_attempted', 'delivery_failed', 'in_return', 'delivery_anomaly', 'tracking_updated')");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            // Rollback pour SQLite
            Schema::create('order_history_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('user_type')->nullable();
                $table->enum('action', [
                    'création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison',
                    'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation', 'changement_statut',
                    'duplicate_detected', 'duplicate_review', 'duplicate_merge', 'duplicate_ignore', 'duplicate_cancel'
                ]);
                $table->string('status_before')->nullable();
                $table->string('status_after')->nullable();
                $table->text('notes')->nullable();
                $table->json('changes')->nullable();
                $table->timestamps();
            });

            DB::statement('INSERT INTO order_history_temp (id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at) SELECT id, order_id, user_id, user_type, action, status_before, status_after, notes, changes, created_at, updated_at FROM order_history');
            Schema::dropIfExists('order_history');
            Schema::rename('order_history_temp', 'order_history');
        } else {
            Schema::table('order_history', function (Blueprint $table) {
                $table->dropColumn(['carrier_status_code', 'carrier_status_label', 'tracking_number', 'carrier_name']);
            });
            
            DB::statement("ALTER TABLE order_history MODIFY COLUMN action ENUM('création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison', 'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation', 'changement_statut', 'duplicate_detected', 'duplicate_review', 'duplicate_merge', 'duplicate_ignore', 'duplicate_cancel')");
        }
    }
};