<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add soft deletes + default_employee to confirmi_users
        Schema::table('confirmi_users', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 2. Add default_employee_id to admins (auto-assignment)
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('confirmi_default_employee_id')->nullable()->after('confirmi_activated_at');
            $table->foreign('confirmi_default_employee_id')->references('id')->on('confirmi_users')->nullOnDelete();
        });

        // 3. Add last_result to track individual attempt results
        Schema::table('confirmi_order_assignments', function (Blueprint $table) {
            $table->string('last_result', 30)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('confirmi_order_assignments', function (Blueprint $table) {
            $table->dropColumn('last_result');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['confirmi_default_employee_id']);
            $table->dropColumn('confirmi_default_employee_id');
        });

        Schema::table('confirmi_users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
