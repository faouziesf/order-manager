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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('user'); // user_id, user_type
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
            
            // Index pour améliorer les performances
            $table->index(['user_id', 'user_type']);
            $table->index('login_at');
            $table->index('is_successful');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};