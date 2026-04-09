<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating test data...');

        // Get or find admin (owner)
        $admin = Admin::find(2) ?? Admin::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->error('No admin found. Create an admin first.');
            return;
        }

        $adminId = $admin->id;
        $this->command->info("Using admin: {$admin->name} (ID: {$adminId})");

        // ===== CREATE MANAGER =====
        $manager = Admin::firstOrCreate(
            ['email' => 'manager@test.com'],
            [
                'name' => 'Omar Manager',
                'password' => Hash::make('password'),
                'role' => Admin::ROLE_MANAGER,
                'created_by' => $adminId,
                'is_active' => true,
                'shop_name' => $admin->shop_name ?? 'Test Shop',
                'identifier' => 'MGR' . rand(1000, 9999),
            ]
        );
        $this->command->info("Manager: {$manager->name} (ID: {$manager->id})");

        // ===== CREATE EMPLOYEES =====
        $employees = [];
        $employeeData = [
            ['name' => 'Sami Employé', 'email' => 'sami@test.com'],
            ['name' => 'Nour Employée', 'email' => 'nour@test.com'],
            ['name' => 'Khaled Employé', 'email' => 'khaled@test.com'],
        ];

        foreach ($employeeData as $data) {
            $emp = Admin::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => Admin::ROLE_EMPLOYEE,
                    'created_by' => $adminId,
                    'is_active' => true,
                    'shop_name' => $admin->shop_name ?? 'Test Shop',
                    'identifier' => 'EMP' . rand(1000, 9999),
                ]
            );
            $employees[] = $emp;
            $this->command->info("Employee: {$emp->name} (ID: {$emp->id})");
        }

        // ===== CREATE TEST ORDERS =====
        $regions = \App\Models\Region::whereIn('id', range(1, 24))->pluck('id', 'name')->toArray();
        $regionIds = array_values($regions);

        $statuses = ['nouvelle', 'confirmée', 'confirmée', 'confirmée', 'annulée', 'expédiée', 'livrée', 'en_retour'];
        $names = [
            'Ahmed Ben Ali', 'Fatma Trabelsi', 'Mohamed Jebali', 'Leila Bousnina',
            'Youssef Karchouni', 'Ines Chaabane', 'Karim Hamdi', 'Salma Mejri',
            'Hamza Sassi', 'Mariem Fakhfakh', 'Ali Boussetta', 'Hana Dridi',
            'Bilel Slimani', 'Amira Zaidi', 'Rami Gharbi', 'Sarra Hamrouni',
            'Aymen Khemiri', 'Rim Ben Youssef', 'Tarek Zouari', 'Nadia Jlassi',
        ];

        $phones = [];
        for ($i = 0; $i < 20; $i++) {
            $phones[] = str_pad(rand(20000000, 99999999), 8, '0', STR_PAD_LEFT);
        }

        $existingCount = Order::where('admin_id', $adminId)->count();
        if ($existingCount >= 15) {
            $this->command->info("Already {$existingCount} orders. Skipping order creation.");
        } else {
            $ordersToCreate = 20 - $existingCount;
            $this->command->info("Creating {$ordersToCreate} test orders...");

            for ($i = 0; $i < $ordersToCreate; $i++) {
                $regionId = $regionIds[array_rand($regionIds)];
                $cities = \App\Models\City::where('region_id', $regionId)->pluck('id')->toArray();
                $cityId = !empty($cities) ? $cities[array_rand($cities)] : null;

                $status = $statuses[array_rand($statuses)];
                $employeeId = null;
                $isAssigned = false;

                // Assign some confirmed orders to employees
                if (in_array($status, ['confirmée', 'expédiée', 'livrée']) && rand(0, 1)) {
                    $emp = $employees[array_rand($employees)];
                    $employeeId = $emp->id;
                    $isAssigned = true;
                }

                $trackingNumber = null;
                $carrierName = null;
                $shippedAt = null;
                $deliveredAt = null;

                if ($status === 'expédiée') {
                    $trackingNumber = 'PKG_TEST' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                    $carrierName = 'Kolixy';
                    $shippedAt = now()->subDays(rand(1, 5));
                }
                if ($status === 'livrée') {
                    $trackingNumber = 'PKG_DONE' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                    $carrierName = 'Kolixy';
                    $shippedAt = now()->subDays(rand(5, 10));
                    $deliveredAt = now()->subDays(rand(1, 4));
                }
                if ($status === 'en_retour') {
                    $trackingNumber = 'PKG_RET' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                    $carrierName = 'Kolixy';
                    $shippedAt = now()->subDays(rand(3, 7));
                }

                Order::create([
                    'admin_id' => $adminId,
                    'employee_id' => $employeeId,
                    'customer_name' => $names[$i % count($names)],
                    'customer_phone' => $phones[$i % count($phones)],
                    'customer_governorate' => (string) $regionId,
                    'customer_city' => (string) $cityId,
                    'customer_address' => 'Adresse test ' . ($i + 1),
                    'total_price' => rand(1500, 25000) / 100,
                    'status' => $status,
                    'is_assigned' => $isAssigned,
                    'tracking_number' => $trackingNumber,
                    'carrier_name' => $carrierName,
                    'shipped_at' => $shippedAt,
                    'delivered_at' => $deliveredAt,
                    'notes' => $i % 3 === 0 ? 'Note de test pour cette commande' : null,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }

            $this->command->info("Orders created successfully!");
        }

        // Summary
        $this->command->newLine();
        $this->command->info('=== TEST DATA SUMMARY ===');
        $this->command->info("Admin:     {$admin->name} ({$admin->email})");
        $this->command->info("Manager:   {$manager->name} (manager@test.com / password)");
        foreach ($employees as $emp) {
            $this->command->info("Employee:  {$emp->name} ({$emp->email} / password)");
        }
        $this->command->info("Orders:    " . Order::where('admin_id', $adminId)->count() . " total");
        $this->command->info("  Confirmées: " . Order::where('admin_id', $adminId)->where('status', 'confirmée')->count());
        $this->command->info("  Ready to ship: " . Order::where('admin_id', $adminId)->where('status', 'confirmée')->whereNull('tracking_number')->count());
    }
}
