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
        Schema::table('admins', function (Blueprint $table) {
            // Ajouter la colonne role seulement si elle n'existe pas
            if (!Schema::hasColumn('admins', 'role')) {
                $table->string('role')->default('admin')->after('email'); // admin, manager, employee
            }

            // Ajouter la colonne created_by seulement si elle n'existe pas
            if (!Schema::hasColumn('admins', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('role'); // ID de l'admin créateur
                $table->foreign('created_by')->references('id')->on('admins')->onDelete('cascade');
            }
        });

        // Migrate existing data from managers table (only if not already migrated)
        if (Schema::hasTable('managers') && DB::table('admins')->where('role', 'manager')->count() === 0) {
            DB::statement("
                INSERT INTO admins (name, email, password, role, is_active, shop_name, identifier, created_by, phone, created_at, updated_at)
                SELECT m.name, m.email, m.password, 'manager', m.is_active,
                       'manager_' || m.id, 'MGR' || substr('000000' || m.id, -6, 6),
                       m.admin_id, m.phone, m.created_at, m.updated_at
                FROM managers m
            ");
        }

        // Migrate existing data from employees table (only if not already migrated)
        if (Schema::hasTable('employees') && DB::table('admins')->where('role', 'employee')->count() === 0) {
            DB::statement("
                INSERT INTO admins (name, email, password, role, is_active, shop_name, identifier, created_by, phone, created_at, updated_at)
                SELECT e.name, e.email, e.password, 'employee', e.is_active,
                       'employee_' || e.id, 'EMP' || substr('000000' || e.id, -6, 6),
                       e.admin_id, e.phone, e.created_at, e.updated_at
                FROM employees e
            ");
        }

        // Drop old tables si elles existent
        Schema::dropIfExists('managers');
        Schema::dropIfExists('employees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['role', 'created_by']);
        });
    }
};
