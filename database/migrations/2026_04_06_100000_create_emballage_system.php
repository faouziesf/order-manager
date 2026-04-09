<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Emballage tasks table
        Schema::create('emballage_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('confirmi_order_assignments')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('confirmi_users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('confirmi_users')->nullOnDelete();
            $table->enum('status', ['pending', 'received', 'packed', 'shipped', 'completed'])->default('pending');
            $table->string('tracking_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        // Add emballage columns to admins
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('emballage_enabled')->default(false)->after('confirmi_default_employee_id');
            $table->foreignId('confirmi_default_agent_id')->nullable()->after('emballage_enabled')
                ->constrained('confirmi_users')->nullOnDelete();
        });

        // Global company kolixy configuration (super-admin level)
        Schema::create('company_kolixy_config', function (Blueprint $table) {
            $table->id();
            $table->string('api_token', 500);
            $table->string('pickup_address_id', 50)->nullable();
            $table->string('company_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_kolixy_config');

        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['confirmi_default_agent_id']);
            $table->dropColumn(['emballage_enabled', 'confirmi_default_agent_id']);
        });

        Schema::dropIfExists('emballage_tasks');
    }
};
