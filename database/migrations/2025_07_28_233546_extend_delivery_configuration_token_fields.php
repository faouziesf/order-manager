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
        Schema::table('delivery_configurations', function (Blueprint $table) {
            // Changer password de VARCHAR(255) vers TEXT pour supporter les longs tokens JWT
            $table->text('password')->change();
            
            // Aussi agrandir le champ token au cas où
            $table->text('token')->nullable()->change();
        });
        
        echo "✅ Colonnes password et token étendues pour supporter les longs tokens JWT\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_configurations', function (Blueprint $table) {
            // Revenir à VARCHAR(255) - ATTENTION: cela peut tronquer les données existantes
            $table->string('password', 255)->change();
            $table->string('token', 255)->nullable()->change();
        });
    }
};


