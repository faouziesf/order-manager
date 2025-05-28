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
        Schema::table('orders', function (Blueprint $table) {
            // Index composé pour la file standard
            $table->index(['admin_id', 'status', 'attempts_count', 'daily_attempts_count', 'is_suspended'], 'idx_orders_standard_queue');
            
            // Index composé pour la file datée
            $table->index(['admin_id', 'status', 'scheduled_date', 'attempts_count', 'daily_attempts_count', 'is_suspended'], 'idx_orders_dated_queue');
            
            // Index sur updated_at pour les vérifications de délai
            $table->index(['updated_at'], 'idx_orders_updated_at');
            
            // Index sur last_attempt_at
            $table->index(['last_attempt_at'], 'idx_orders_last_attempt');
            
            // Index composé pour les priorités et tri
            $table->index(['priority', 'attempts_count', 'created_at'], 'idx_orders_priority_sort');
            
            // Index pour les commandes suspendues
            $table->index(['is_suspended', 'suspension_reason'], 'idx_orders_suspension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_standard_queue');
            $table->dropIndex('idx_orders_dated_queue');
            $table->dropIndex('idx_orders_updated_at');
            $table->dropIndex('idx_orders_last_attempt');
            $table->dropIndex('idx_orders_priority_sort');
            $table->dropIndex('idx_orders_suspension');
        });
    }
};