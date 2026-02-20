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
        // 1. Table des utilisateurs Confirmi (Commerciaux & Employés de la plateforme)
        Schema::create('confirmi_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('role')->default('employee'); // 'commercial' ou 'employee'
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable(); // super_admin qui a créé
            $table->timestamp('last_login_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('is_active');
        });

        // 2. Ajouter les champs Confirmi sur la table admins
        Schema::table('admins', function (Blueprint $table) {
            $table->string('confirmi_status')->default('disabled')->after('subscription_type');
            // disabled = pas activé, pending = demande en cours, active = activé, suspended = suspendu
            $table->decimal('confirmi_rate_confirmed', 8, 3)->default(0)->after('confirmi_status');
            // Tarif par commande confirmée
            $table->decimal('confirmi_rate_delivered', 8, 3)->default(0)->after('confirmi_rate_confirmed');
            // Tarif par commande confirmée ET livrée
            $table->unsignedBigInteger('confirmi_approved_by')->nullable()->after('confirmi_rate_delivered');
            // ID du commercial/super_admin qui a approuvé
            $table->timestamp('confirmi_activated_at')->nullable()->after('confirmi_approved_by');

            $table->index('confirmi_status');
        });

        // 3. Table des demandes d'activation Confirmi
        Schema::create('confirmi_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->decimal('proposed_rate_confirmed', 8, 3)->default(0);
            $table->decimal('proposed_rate_delivered', 8, 3)->default(0);
            $table->text('admin_message')->nullable(); // Message de l'admin
            $table->text('response_message')->nullable(); // Réponse du commercial/super_admin
            $table->unsignedBigInteger('processed_by')->nullable(); // confirmi_user ou super_admin
            $table->string('processed_by_type')->nullable(); // 'confirmi_user' ou 'super_admin'
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index('status');
        });

        // 4. Table d'assignation des commandes Confirmi
        Schema::create('confirmi_order_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('admin_id'); // L'admin propriétaire de la commande
            $table->unsignedBigInteger('assigned_to')->nullable(); // confirmi_user (employé) assigné
            $table->unsignedBigInteger('assigned_by')->nullable(); // confirmi_user (commercial) qui a assigné
            $table->string('status')->default('pending');
            // pending = en attente d'assignation, assigned = assigné, in_progress = en cours,
            // confirmed = confirmée, cancelled = annulée, delivered = livrée
            $table->integer('attempts')->default(0); // Nombre de tentatives de confirmation
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('confirmi_users')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('confirmi_users')->onDelete('set null');
            $table->index('status');
            $table->index(['admin_id', 'status']);
        });

        // 5. Table de facturation Confirmi (suivi des frais par admin)
        Schema::create('confirmi_billing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('order_id');
            $table->string('billing_type'); // 'confirmed' ou 'delivered'
            $table->decimal('amount', 8, 3)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('billed_at');
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index(['admin_id', 'is_paid']);
        });

        // 6. Configuration Masafa Express par admin
        Schema::create('masafa_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('api_token')->nullable(); // Token API Masafa Express
            $table->string('masafa_client_id')->nullable(); // ID client Masafa
            $table->string('default_gouvernorat')->nullable();
            $table->string('default_delegation')->nullable();
            $table->string('default_address')->nullable();
            $table->string('default_phone')->nullable();
            $table->string('pickup_name')->nullable(); // Nom de l'adresse de pickup
            $table->boolean('is_active')->default(false);
            $table->boolean('auto_send')->default(false); // Envoi auto des commandes livrées
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masafa_configurations');
        Schema::dropIfExists('confirmi_billing');
        Schema::dropIfExists('confirmi_order_assignments');
        Schema::dropIfExists('confirmi_requests');

        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                $columns = ['confirmi_status', 'confirmi_rate_confirmed', 'confirmi_rate_delivered', 'confirmi_approved_by', 'confirmi_activated_at'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('admins', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('confirmi_users');
    }
};
