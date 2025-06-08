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
        Schema::create('super_admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // admin_registered, admin_expired, system, etc.
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->unsignedBigInteger('related_admin_id')->nullable();
            $table->json('data')->nullable(); // Données supplémentaires
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index
            $table->index(['type', 'priority']);
            $table->index('read_at');
            $table->index('created_at');
            $table->index('related_admin_id');

            // Clé étrangère
            $table->foreign('related_admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_admin_notifications');
    }
};