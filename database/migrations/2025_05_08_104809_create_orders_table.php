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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations du client
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->required(); // Seul champ obligatoire
            $table->string('customer_phone_2')->nullable();
            $table->string('customer_governorate')->nullable();
            $table->string('customer_city')->nullable();
            $table->text('customer_address')->nullable();
            
            // Informations de la commande
            $table->decimal('total_price', 10, 3)->default(0);
            $table->decimal('shipping_cost', 10, 3)->default(0);
            $table->decimal('confirmed_price', 10, 3)->nullable();
            
            // Statuts et attributs
            $table->enum('status', ['nouvelle', 'confirmée', 'annulée', 'datée', 'en_route', 'livrée'])->default('nouvelle');
            $table->enum('priority', ['normale', 'urgente', 'vip'])->default('normale');
            $table->date('scheduled_date')->nullable(); // Pour les commandes datées
            
            // Compteurs de tentatives
            $table->integer('attempts_count')->default(0);
            $table->integer('daily_attempts_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            
            // Champ pour déterminer si la commande est assignée ou non
            $table->boolean('is_assigned')->default(false);
            
            // Historique et timestamps
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Permet de "supprimer" sans réellement supprimer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};