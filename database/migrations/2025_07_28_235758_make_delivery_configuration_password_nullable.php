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
            // Rendre le champ password nullable et l'étendre à TEXT
            $table->text('password')->nullable()->change();
            
            // Aussi étendre le champ username à TEXT pour les longs tokens
            $table->text('username')->change();
            
            // Étendre le champ token aussi
            $table->text('token')->nullable()->change();
        });
        
        echo "✅ Champs password (nullable), username et token étendus à TEXT\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_configurations', function (Blueprint $table) {
            $table->string('password', 255)->change();
            $table->string('username', 255)->change();
            $table->string('token', 255)->nullable()->change();
        });
    }
};