<?php

// =====================================

// database/migrations/2024_01_18_000000_create_system_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // emergency, alert, critical, error, warning, notice, info, debug
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            $table->string('channel')->nullable();
            $table->timestamp('datetime');
            $table->string('remote_addr')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->timestamps();

            // Index
            $table->index(['level', 'datetime']);
            $table->index('channel');
            $table->index('datetime');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};

