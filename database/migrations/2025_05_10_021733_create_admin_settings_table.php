<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        DB::table('admin_settings')->insert([
            // Paramètres pour la file standard
            ['key' => 'standard_max_daily_attempts', 'value' => '3'],
            ['key' => 'standard_delay_hours', 'value' => '2.5'],
            ['key' => 'standard_max_total_attempts', 'value' => '9'],
            
            // Paramètres pour la file datée
            ['key' => 'dated_max_daily_attempts', 'value' => '2'],
            ['key' => 'dated_delay_hours', 'value' => '3.5'],
            ['key' => 'dated_max_total_attempts', 'value' => '5'],
            
            // Paramètres pour la file ancienne
            ['key' => 'old_max_daily_attempts', 'value' => '2'],
            ['key' => 'old_delay_hours', 'value' => '6'],
            ['key' => 'old_max_total_attempts', 'value' => '0'], // 0 = pas de limite totale
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('admin_settings');
    }
};