<?php

// =====================================

// database/migrations/2024_01_17_000000_create_backups_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['database', 'files', 'full']);
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed']);
            $table->string('file_path')->nullable();
            $table->bigInteger('size')->nullable(); // en bytes
            $table->string('compression')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retention_days')->default(30);
            $table->boolean('is_automated')->default(false);
            $table->timestamps();

            // Index
            $table->index(['status', 'created_at']);
            $table->index('type');
            $table->index('is_automated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};

