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
        Schema::table('admins', function (Blueprint $table) {
            // Vérifier et ajouter les colonnes manquantes
            if (!Schema::hasColumn('admins', 'subscription_type')) {
                $table->string('subscription_type')->default('trial')->after('total_active_hours');
            }
            
            if (!Schema::hasColumn('admins', 'total_revenue')) {
                $table->decimal('total_revenue', 10, 2)->default(0)->after('subscription_type');
            }
            
            if (!Schema::hasColumn('admins', 'created_by_super_admin')) {
                $table->boolean('created_by_super_admin')->default(false)->after('total_revenue');
            }
            
            if (!Schema::hasColumn('admins', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('created_by_super_admin');
            }
            
            if (!Schema::hasColumn('admins', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('last_login_at');
            }
        });
        
        // Mettre à jour les valeurs par défaut pour les colonnes existantes
        Schema::table('admins', function (Blueprint $table) {
            // S'assurer que les colonnes ont les bons types et valeurs par défaut
            $table->integer('total_orders')->default(0)->change();
            $table->integer('total_active_hours')->default(0)->change();
            $table->integer('max_managers')->default(1)->change();
            $table->integer('max_employees')->default(2)->change();
            $table->boolean('is_active')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $columns = [
                'subscription_type',
                'total_revenue', 
                'created_by_super_admin',
                'last_login_at',
                'ip_address'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('admins', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};