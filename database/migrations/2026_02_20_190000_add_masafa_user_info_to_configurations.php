<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masafa_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('masafa_configurations', 'masafa_user_name')) {
                $table->string('masafa_user_name')->nullable()->after('api_token');
            }
            if (!Schema::hasColumn('masafa_configurations', 'masafa_user_email')) {
                $table->string('masafa_user_email')->nullable()->after('masafa_user_name');
            }
            if (!Schema::hasColumn('masafa_configurations', 'masafa_user_id')) {
                $table->unsignedBigInteger('masafa_user_id')->nullable()->after('masafa_user_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('masafa_configurations', function (Blueprint $table) {
            foreach (['masafa_user_name', 'masafa_user_email', 'masafa_user_id'] as $col) {
                if (Schema::hasColumn('masafa_configurations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
