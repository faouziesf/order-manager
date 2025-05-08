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
        Schema::create('order_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable(); // L'utilisateur qui a effectué l'action
            $table->string('user_type')->nullable(); // Admin, Manager ou Employee
            $table->enum('action', ['création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison']);
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->text('notes')->nullable();
            $table->json('changes')->nullable(); // Pour stocker les changements en JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_history');
    }
};