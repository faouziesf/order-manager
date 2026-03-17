<?php

namespace Database\Seeders;

use App\Models\ConfirmiUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConfirmiSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un utilisateur commercial Confirmi
        ConfirmiUser::create([
            'name' => 'Commercial Confirmi',
            'email' => 'commercial@confirmi.com',
            'password' => Hash::make('password'),
            'role' => 'commercial',
            'is_active' => true,
        ]);

        // Créer un utilisateur employé Confirmi
        ConfirmiUser::create([
            'name' => 'Employé Confirmi',
            'email' => 'employe@confirmi.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        // Créer un admin de test avec Confirmi activé
        $admin = \App\Models\Admin::where('email', 'admin@test.com')->first();
        if ($admin) {
            $admin->update([
                'confirmi_status' => 'active',
                'confirmi_rate_confirmed' => 1.500,
                'confirmi_rate_delivered' => 0.800,
                'confirmi_activated_at' => now(),
            ]);
        }

        echo "\n✅ Utilisateurs Confirmi créés:\n";
        echo "📧 Commercial: commercial@confirmi.com (password: password)\n";
        echo "📧 Employé: employe@confirmi.com (password: password)\n";
        echo "✅ Admin test configuré avec Confirmi actif\n\n";
    }
}
