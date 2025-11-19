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
        // Trouver le premier admin principal (role='admin')
        $mainAdmin = DB::table('admins')->where('role', 'admin')->first();

        if ($mainAdmin) {
            // Mettre à jour tous les employés sans created_by
            DB::table('admins')
                ->where('role', 'employee')
                ->whereNull('created_by')
                ->update(['created_by' => $mainAdmin->id]);

            // Mettre à jour tous les managers sans created_by
            DB::table('admins')
                ->where('role', 'manager')
                ->whereNull('created_by')
                ->update(['created_by' => $mainAdmin->id]);

            echo "Employés et managers mis à jour avec created_by = {$mainAdmin->id}\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne remet pas à NULL car cela casserait le système
        // Cette migration est non-réversible
    }
};
