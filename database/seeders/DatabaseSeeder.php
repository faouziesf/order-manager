<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
        ]);
        
        // Création des paramètres par défaut
        \App\Models\Setting::create([
            'key' => 'trial_period',
            'value' => '3',
            'description' => 'Période d\'essai en jours pour les nouveaux admins',
        ]);
        
        \App\Models\Setting::create([
            'key' => 'allow_registration',
            'value' => '0',
            'description' => 'Autoriser l\'inscription publique',
        ]);
    }
}