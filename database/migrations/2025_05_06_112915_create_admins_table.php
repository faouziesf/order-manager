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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('shop_name');
            $table->string('identifier', 6)->unique(); // Format: 4 chiffres/lettre (ex: 4526/l)
            $table->date('expiry_date')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_managers')->default(1);
            $table->integer('max_employees')->default(2);
            $table->integer('total_orders')->default(0);
            $table->integer('total_active_hours')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};