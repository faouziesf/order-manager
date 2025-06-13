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
        Schema::create('delivery_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('username');
            $table->text('password'); // encrypted
            $table->enum('environment', ['test', 'prod'])->default('test');
            $table->text('token')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // For additional configuration
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique('admin_id');
            $table->index(['admin_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_configurations');
    }
};
