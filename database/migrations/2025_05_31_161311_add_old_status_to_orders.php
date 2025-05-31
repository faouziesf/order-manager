<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Pour SQLite, on doit recréer la table car ALTER COLUMN n'est pas supporté
        if (DB::getDriverName() === 'sqlite') {
            // Créer une table temporaire avec le nouveau statut
            Schema::create('orders_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained()->onDelete('cascade');
                $table->foreignId('manager_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
                
                // Informations du client
                $table->string('customer_name')->nullable();
                $table->string('customer_phone')->required();
                $table->string('customer_phone_2')->nullable();
                $table->string('customer_governorate')->nullable();
                $table->string('customer_city')->nullable();
                $table->text('customer_address')->nullable();
                
                // Informations de la commande
                $table->decimal('total_price', 10, 3)->default(0);
                $table->decimal('shipping_cost', 10, 3)->default(0);
                $table->decimal('confirmed_price', 10, 3)->nullable();
                
                // NOUVEAU: Ajouter "ancienne" aux statuts possibles
                $table->enum('status', ['nouvelle', 'confirmée', 'annulée', 'datée', 'ancienne', 'en_route', 'livrée'])->default('nouvelle');
                $table->enum('priority', ['normale', 'urgente', 'vip'])->default('normale');
                $table->date('scheduled_date')->nullable();
                
                // Compteurs de tentatives
                $table->integer('attempts_count')->default(0);
                $table->integer('daily_attempts_count')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                
                // Champs assignation et suspension
                $table->boolean('is_assigned')->default(false);
                $table->boolean('is_suspended')->default(false);
                $table->string('suspension_reason')->nullable();
                
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Copier les données existantes
            DB::statement('INSERT INTO orders_temp SELECT * FROM orders');

            // Supprimer l'ancienne table
            Schema::dropIfExists('orders');

            // Renommer la nouvelle table
            Schema::rename('orders_temp', 'orders');
            
        } else {
            // Pour MySQL/PostgreSQL
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('nouvelle', 'confirmée', 'annulée', 'datée', 'ancienne', 'en_route', 'livrée') DEFAULT 'nouvelle'");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            // Recréer l'ancienne structure
            Schema::create('orders_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained()->onDelete('cascade');
                $table->foreignId('manager_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
                
                $table->string('customer_name')->nullable();
                $table->string('customer_phone')->required();
                $table->string('customer_phone_2')->nullable();
                $table->string('customer_governorate')->nullable();
                $table->string('customer_city')->nullable();
                $table->text('customer_address')->nullable();
                
                $table->decimal('total_price', 10, 3)->default(0);
                $table->decimal('shipping_cost', 10, 3)->default(0);
                $table->decimal('confirmed_price', 10, 3)->nullable();
                
                // Statuts originaux sans "ancienne"
                $table->enum('status', ['nouvelle', 'confirmée', 'annulée', 'datée', 'en_route', 'livrée'])->default('nouvelle');
                $table->enum('priority', ['normale', 'urgente', 'vip'])->default('normale');
                $table->date('scheduled_date')->nullable();
                
                $table->integer('attempts_count')->default(0);
                $table->integer('daily_attempts_count')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                
                $table->boolean('is_assigned')->default(false);
                $table->boolean('is_suspended')->default(false);
                $table->string('suspension_reason')->nullable();
                
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Copier les données (les commandes "anciennes" redeviennent "nouvelles")
            DB::statement('INSERT INTO orders_temp SELECT id, admin_id, manager_id, employee_id, customer_name, customer_phone, customer_phone_2, customer_governorate, customer_city, customer_address, total_price, shipping_cost, confirmed_price, CASE WHEN status = "ancienne" THEN "nouvelle" ELSE status END, priority, scheduled_date, attempts_count, daily_attempts_count, last_attempt_at, is_assigned, is_suspended, suspension_reason, notes, created_at, updated_at, deleted_at FROM orders');

            Schema::dropIfExists('orders');
            Schema::rename('orders_temp', 'orders');
            
        } else {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('nouvelle', 'confirmée', 'annulée', 'datée', 'en_route', 'livrée') DEFAULT 'nouvelle'");
        }
    }
};