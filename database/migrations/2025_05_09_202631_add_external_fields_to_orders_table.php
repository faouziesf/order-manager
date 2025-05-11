<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('id');
            $table->string('external_source')->nullable()->after('external_id');
            $table->string('customer_email')->nullable()->after('customer_phone_2');
            
            // Index pour accélérer les recherches
            $table->index(['admin_id', 'external_id', 'external_source']);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'external_source', 'customer_email']);
            $table->dropIndex(['admin_id', 'external_id', 'external_source']);
        });
    }
};