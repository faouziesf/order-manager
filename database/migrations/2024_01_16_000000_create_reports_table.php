<?php
// database/migrations/2024_01_16_000000_create_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            // Index
            $table->index(['type', 'created_at']);
            $table->index('is_scheduled');
            $table->index('next_execution');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

