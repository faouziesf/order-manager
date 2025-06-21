<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABLES D'AUTHENTIFICATION ET UTILISATEURS DE BASE
        
        // Table users (Laravel par défaut)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 2. CACHE ET JOBS

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 3. SYSTÈME DE RÔLES ET UTILISATEURS

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->json('permissions')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('language')->default('fr');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('last_login_at');
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('shop_name');
            $table->string('identifier', 6)->unique();
            $table->date('expiry_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('subscription_type')->default('trial');
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('created_by_super_admin')->default(false);
            $table->integer('max_managers')->default(1);
            $table->integer('max_employees')->default(2);
            $table->integer('total_orders')->default(0);
            $table->integer('total_active_hours')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('last_login_at');
            $table->index('subscription_type');
            $table->index('created_by_super_admin');
        });

        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('managers')->onDelete('set null');
        });

        // 4. GÉOGRAPHIE ET RÉGIONS

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('shipping_cost', 8, 3)->default(0);
            $table->timestamps();
        });

        // 5. PRODUITS

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('price', 10, 3);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('needs_review')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 6. COMMANDES

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->string('external_id')->nullable();
            $table->string('external_source')->nullable();
            
            // Informations du client
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_phone_2')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_governorate')->nullable();
            $table->string('customer_city')->nullable();
            $table->text('customer_address')->nullable();
            
            // Informations de la commande
            $table->decimal('total_price', 10, 3)->default(0);
            $table->decimal('shipping_cost', 10, 3)->default(0);
            $table->decimal('confirmed_price', 10, 3)->nullable();
            
            // Statuts incluant WooCommerce + Order Manager
            $table->enum('status', [
                'nouvelle', 'confirmée', 'annulée', 'datée', 'ancienne', 'en_route', 'livrée',
                'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'
            ])->default('nouvelle');
            
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
            
            // Champs pour la gestion des doublons
            $table->boolean('is_duplicate')->default(false);
            $table->boolean('reviewed_for_duplicates')->default(false);
            $table->string('duplicate_group_id')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour les performances
            $table->index(['admin_id', 'external_id', 'external_source']);
            $table->index(['admin_id', 'status', 'attempts_count', 'daily_attempts_count', 'is_suspended'], 'idx_orders_standard_queue');
            $table->index(['admin_id', 'status', 'scheduled_date', 'attempts_count', 'daily_attempts_count', 'is_suspended'], 'idx_orders_dated_queue');
            $table->index(['updated_at'], 'idx_orders_updated_at');
            $table->index(['last_attempt_at'], 'idx_orders_last_attempt');
            $table->index(['priority', 'attempts_count', 'created_at'], 'idx_orders_priority_sort');
            $table->index(['is_suspended', 'suspension_reason'], 'idx_orders_suspension');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 3);
            $table->decimal('total_price', 10, 3);
            $table->timestamps();
        });

        Schema::create('order_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable();
            $table->string('user_type')->nullable();
            $table->enum('action', [
                'création', 'modification', 'confirmation', 'annulation', 'datation', 'tentative', 'livraison',
                'assignation', 'désassignation', 'en_route', 'suspension', 'réactivation', 'changement_statut',
                'duplicate_detected', 'duplicate_review', 'duplicate_merge', 'duplicate_ignore', 'duplicate_cancel',
                'shipment_created', 'shipment_validated', 'pickup_created', 'pickup_validated',
                'picked_up_by_carrier', 'in_transit', 'delivery_attempted', 'delivery_failed',
                'in_return', 'delivery_anomaly', 'tracking_updated'
            ]);
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->text('notes')->nullable();
            $table->json('changes')->nullable();
            
            // Champs pour la livraison
            $table->string('carrier_status_code')->nullable();
            $table->string('carrier_status_label')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier_name')->nullable();
            
            $table->timestamps();
        });

        // 7. PARAMÈTRES

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->string('setting_type')->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
            
            $table->index(['admin_id', 'setting_key']);
            $table->unique(['admin_id', 'setting_key']);
        });

        // 8. INTÉGRATIONS

        Schema::create('woocommerce_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->string('store_url');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->boolean('is_active')->default(false);
            $table->enum('sync_status', ['idle', 'syncing', 'error'])->default('idle');
            $table->string('sync_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            
            $table->unique(['admin_id', 'store_url'], 'unique_admin_store');
        });

        // 9. LIVRAISON

        Schema::create('delivery_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('carrier_slug')->default('fparcel');
            $table->string('integration_name');
            $table->string('username');
            $table->text('password');
            $table->enum('environment', ['test', 'prod'])->default('test');
            $table->text('token')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique(['admin_id', 'carrier_slug', 'integration_name'], 'delivery_config_unique');
            $table->index(['admin_id', 'is_active']);
        });

        Schema::create('delivery_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('mr_code');
            $table->string('mr_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'is_active']);
            $table->unique(['admin_id', 'mr_code']);
        });

        Schema::create('delivery_drop_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('point_id');
            $table->string('point_name');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->text('opening_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'is_active']);
            $table->index(['admin_id', 'city']);
            $table->unique(['admin_id', 'point_id']);
        });

        Schema::create('delivery_anomaly_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('reason_code');
            $table->string('reason_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'is_active']);
            $table->unique(['admin_id', 'reason_code']);
        });

        Schema::create('fparcel_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('code')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique(['admin_id', 'code']);
        });

        Schema::create('pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('name');
            $table->string('contact_name');
            $table->text('address');
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'is_active']);
            $table->unique(['admin_id', 'name']);
        });

        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('carrier_slug');
            $table->unsignedBigInteger('delivery_configuration_id');
            $table->unsignedBigInteger('pickup_address_id')->nullable();
            $table->enum('status', ['draft', 'validated', 'picked_up', 'problem'])->default('draft');
            $table->date('pickup_date')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('delivery_configuration_id')->references('id')->on('delivery_configurations')->onDelete('cascade');
            $table->foreign('pickup_address_id')->references('id')->on('pickup_addresses')->onDelete('set null');
            $table->index(['admin_id', 'status']);
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('pickup_id')->nullable();
            $table->string('carrier_slug');
            $table->string('pos_barcode')->nullable()->unique();
            $table->string('return_barcode')->nullable()->unique();
            $table->string('pos_reference')->nullable();
            $table->string('order_number')->nullable();
            $table->enum('status', [
                'created', 'validated', 'picked_up_by_carrier', 'in_transit', 
                'delivered', 'cancelled', 'in_return', 'anomaly'
            ])->default('created');
            $table->json('fparcel_data')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->integer('nb_pieces')->default(1);
            $table->date('pickup_date')->nullable();
            $table->text('content_description')->nullable();
            $table->json('sender_info')->nullable();
            $table->json('recipient_info')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('carrier_last_status_update')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->foreign('pickup_id')->references('id')->on('pickups')->onDelete('set null');
            $table->index(['admin_id', 'status']);
            $table->index(['admin_id', 'order_id']);
            $table->index('pos_barcode');
        });

        Schema::create('bl_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('carrier_slug')->nullable();
            $table->string('template_name');
            $table->json('layout_config');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'carrier_slug', 'is_active']);
            $table->unique(['admin_id', 'carrier_slug', 'template_name']);
        });

        Schema::create('shipment_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->string('carrier_status_code')->nullable();
            $table->string('carrier_status_label')->nullable();
            $table->string('internal_status');
            $table->timestamp('created_at');

            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->index('shipment_id');
        });

        // 10. RAPPORTS ET LOGS

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['admin_activity', 'system_usage', 'performance', 'revenue', 'custom']);
            $table->enum('format', ['pdf', 'excel', 'csv']);
            $table->date('date_from');
            $table->date('date_to');
            $table->json('metrics')->nullable();
            $table->json('data')->nullable();
            $table->boolean('include_charts')->default(false);
            $table->boolean('is_scheduled')->default(false);
            $table->enum('schedule_frequency', ['daily', 'weekly', 'monthly'])->nullable();
            $table->timestamp('next_execution')->nullable();
            $table->string('generated_by');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->boolean('is_automated')->default(false);
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('is_scheduled');
            $table->index('next_execution');
        });

        Schema::create('report_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['report_id', 'status']);
            $table->index('started_at');
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['database', 'files', 'full']);
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed']);
            $table->string('file_path')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('compression')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retention_days')->default(30);
            $table->boolean('is_automated')->default(false);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('type');
            $table->index('is_automated');
        });

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            $table->string('channel')->nullable();
            $table->timestamp('datetime');
            $table->string('remote_addr')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->timestamps();

            $table->index(['level', 'datetime']);
            $table->index('channel');
            $table->index('datetime');
            $table->index('created_at');
        });

        // 11. NOTIFICATIONS ET HISTORIQUE

        Schema::create('super_admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->unsignedBigInteger('related_admin_id')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'priority']);
            $table->index('read_at');
            $table->index('created_at');
            $table->index('related_admin_id');
            $table->foreign('related_admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('user');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->boolean('is_successful')->default(true);
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'user_type']);
            $table->index('login_at');
            $table->index('is_successful');
        });

        // 12. INSÉRER LES DONNÉES DE BASE
        
        // Insérer les gouvernorats tunisiens
        $regions = [
            'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan', 'Bizerte',
            'Béja', 'Jendouba', 'Le Kef', 'Siliana', 'Kairouan', 'Kasserine', 'Sidi Bouzid',
            'Sousse', 'Monastir', 'Mahdia', 'Sfax', 'Gafsa', 'Tozeur', 'Kebili',
            'Gabès', 'Medenine', 'Tataouine'
        ];

        foreach ($regions as $region) {
            DB::table('regions')->insert([
                'name' => $region,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insérer quelques villes principales
        $cities = [
            ['region_id' => 1, 'name' => 'Tunis', 'shipping_cost' => 7.000],
            ['region_id' => 1, 'name' => 'Carthage', 'shipping_cost' => 7.000],
            ['region_id' => 2, 'name' => 'Ariana Ville', 'shipping_cost' => 7.000],
            ['region_id' => 3, 'name' => 'Ben Arous', 'shipping_cost' => 7.000],
            ['region_id' => 5, 'name' => 'Nabeul', 'shipping_cost' => 9.000],
            ['region_id' => 5, 'name' => 'Hammamet', 'shipping_cost' => 9.000],
            ['region_id' => 15, 'name' => 'Sousse', 'shipping_cost' => 9.000],
            ['region_id' => 18, 'name' => 'Sfax', 'shipping_cost' => 10.000],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'region_id' => $city['region_id'],
                'name' => $city['name'],
                'shipping_cost' => $city['shipping_cost'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insérer les rôles de base
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Manager', 'slug' => 'manager'],
            ['name' => 'Employee', 'slug' => 'employee'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'slug' => $role['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Créer un super admin par défaut
        DB::table('super_admins')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@ordercrm.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ Base de données créée avec succès!\n";
        echo "📧 Super Admin: admin@ordercrm.com\n";
        echo "🔑 Mot de passe: password123\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Désactiver les contraintes de clés étrangères temporairement
        Schema::disableForeignKeyConstraints();

        // Supprimer toutes les tables dans l'ordre inverse
        $tables = [
            'login_histories',
            'super_admin_notifications',
            'system_logs',
            'backups',
            'report_executions',
            'reports',
            'shipment_status_history',
            'bl_templates',
            'shipments',
            'pickups',
            'pickup_addresses',
            'fparcel_payment_methods',
            'delivery_anomaly_reasons',
            'delivery_drop_points',
            'delivery_payment_methods',
            'delivery_configurations',
            'woocommerce_settings',
            'admin_settings',
            'settings',
            'order_history',
            'order_items',
            'orders',
            'products',
            'cities',
            'regions',
            'employees',
            'managers',
            'admins',
            'super_admins',
            'roles',
            'failed_jobs',
            'job_batches',
            'jobs',
            'cache_locks',
            'cache',
            'personal_access_tokens',
            'sessions',
            'password_reset_tokens',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        // Réactiver les contraintes de clés étrangères
        Schema::enableForeignKeyConstraints();
    }
};