<?php


// =====================================

// database/migrations/2024_01_21_000000_add_fields_to_super_admins_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone')->nullable()->after('avatar');
            $table->json('permissions')->nullable()->after('phone');
            $table->string('timezone')->default('UTC')->after('permissions');
            $table->string('language')->default('fr')->after('timezone');
            
            // Index
            $table->index('is_active');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'last_login_at',
                'avatar',
                'phone',
                'permissions',
                'timezone',
                'language'
            ]);
        });
    }
};