<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Vérifier et ajouter last_login_at si elle n'existe pas
            if (!Schema::hasColumn('admins', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
            
            // Vérifier et ajouter subscription_type si elle n'existe pas
            if (!Schema::hasColumn('admins', 'subscription_type')) {
                $table->string('subscription_type')->default('trial')->after('is_active');
            }
            
            // Vérifier et ajouter total_revenue si elle n'existe pas
            if (!Schema::hasColumn('admins', 'total_revenue')) {
                $table->decimal('total_revenue', 10, 2)->default(0)->after('total_active_hours');
            }
            
            // Vérifier et ajouter created_by_super_admin si elle n'existe pas
            if (!Schema::hasColumn('admins', 'created_by_super_admin')) {
                $table->boolean('created_by_super_admin')->default(false)->after('is_active');
            }
            
            // Vérifier et ajouter ip_address si elle n'existe pas
            if (!Schema::hasColumn('admins', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('phone');
            }
            
            // email_verified_at existe déjà, donc on ne l'ajoute pas
            
            // Modifier total_orders pour s'assurer qu'il a une valeur par défaut
            if (Schema::hasColumn('admins', 'total_orders')) {
                // Mettre à jour les valeurs NULL existantes
                \DB::table('admins')->whereNull('total_orders')->update(['total_orders' => 0]);
            }
        });
        
        // Ajouter les index après avoir créé les colonnes
        Schema::table('admins', function (Blueprint $table) {
            // Ajouter les index seulement s'ils n'existent pas déjà
            $indexes = \DB::select("PRAGMA index_list('admins')");
            $existingIndexes = array_column($indexes, 'name');
            
            if (!in_array('admins_last_login_at_index', $existingIndexes)) {
                $table->index('last_login_at');
            }
            
            if (!in_array('admins_subscription_type_index', $existingIndexes)) {
                $table->index('subscription_type');
            }
            
            if (!in_array('admins_created_by_super_admin_index', $existingIndexes)) {
                $table->index('created_by_super_admin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Supprimer les colonnes qui ont été ajoutées
            $columnsToCheck = [
                'last_login_at',
                'subscription_type', 
                'total_revenue',
                'created_by_super_admin',
                'ip_address'
            ];
            
            $existingColumns = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('admins', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};