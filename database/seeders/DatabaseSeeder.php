<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuperAdmin;
use App\Models\Admin;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer un super admin par défaut
        SuperAdmin::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Créer un admin de test
        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'shop_name' => 'Boutique Test',
            'identifier' => '1234/a',
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
            'max_managers' => 2,
            'max_employees' => 5,
        ]);

        // Ajouter quelques produits pour cet admin
        $products = [
            [
                'name' => 'Produit Test 1',
                'price' => 19.990,
                'stock' => 100,
                'is_active' => true,
                'description' => 'Description du produit test 1',
            ],
            [
                'name' => 'Produit Test 2',
                'price' => 29.990,
                'stock' => 50,
                'is_active' => true,
                'description' => 'Description du produit test 2',
            ],
            [
                'name' => 'Produit Test 3',
                'price' => 39.990,
                'stock' => 75,
                'is_active' => true,
                'description' => 'Description du produit test 3',
            ],
            [
                'name' => 'Produit Test 4',
                'price' => 49.990,
                'stock' => 25,
                'is_active' => true,
                'description' => 'Description du produit test 4',
            ],
            [
                'name' => 'Produit Test 5',
                'price' => 59.990,
                'stock' => 10,
                'is_active' => true,
                'description' => 'Description du produit test 5',
            ],
        ];

        foreach ($products as $productData) {
            $productData['admin_id'] = $admin->id;
            Product::create($productData);
        }

        // Ajouter les paramètres par défaut
        Setting::create([
            'key' => 'trial_period',
            'value' => 3,
            'description' => 'Période d\'essai en jours pour les nouveaux admins',
        ]);

        Setting::create([
            'key' => 'allow_registration',
            'value' => 1,
            'description' => 'Autoriser l\'inscription publique',
        ]);

        // Exécuter les autres seeders
        $this->call([
            RegionsSeeder::class,
        ]);
    }
}