<?php
// final_fix.php - Correction définitive

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\AdminSetting;

echo "🔧 Correction définitive de la table orders...\n";

try {
    // 1. Vérifier le problème actuel
    echo "1. Diagnostic du problème...\n";
    
    // Récupérer la structure actuelle de la table
    $tableInfo = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='orders'");
    
    if (empty($tableInfo)) {
        throw new Exception("Table orders introuvable!");
    }
    
    $currentSchema = $tableInfo[0]->sql;
    echo "📋 Structure actuelle détectée\n";
    
    // Vérifier si "ancienne" est dans la contrainte CHECK
    if (strpos($currentSchema, '"ancienne"') === false && strpos($currentSchema, "'ancienne'") === false) {
        echo "❌ Le statut 'ancienne' manque dans la contrainte CHECK\n";
        
        // 2. Sauvegarder toutes les données
        echo "2. Sauvegarde des données...\n";
        $orders = DB::select("SELECT * FROM orders");
        echo "📦 " . count($orders) . " commandes sauvegardées\n";
        
        // 3. Supprimer et recréer la table avec la bonne contrainte
        echo "3. Recréation de la table orders...\n";
        
        DB::statement('DROP TABLE orders');
        
        DB::statement('CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_id INTEGER NOT NULL,
            manager_id INTEGER,
            employee_id INTEGER,
            customer_name VARCHAR,
            customer_phone VARCHAR NOT NULL,
            customer_phone_2 VARCHAR,
            customer_governorate VARCHAR,
            customer_city VARCHAR,
            customer_address TEXT,
            total_price DECIMAL(10,3) DEFAULT 0,
            shipping_cost DECIMAL(10,3) DEFAULT 0,
            confirmed_price DECIMAL(10,3),
            status VARCHAR CHECK (status IN ("nouvelle", "confirmée", "annulée", "datée", "ancienne", "en_route", "livrée")) DEFAULT "nouvelle",
            priority VARCHAR CHECK (priority IN ("normale", "urgente", "vip")) DEFAULT "normale",
            scheduled_date DATE,
            attempts_count INTEGER DEFAULT 0,
            daily_attempts_count INTEGER DEFAULT 0,
            last_attempt_at DATETIME,
            is_assigned BOOLEAN DEFAULT 0,
            is_suspended BOOLEAN DEFAULT 0,
            suspension_reason VARCHAR,
            notes TEXT,
            created_at DATETIME,
            updated_at DATETIME,
            deleted_at DATETIME,
            external_id VARCHAR,
            external_source VARCHAR,
            customer_email VARCHAR
        )');
        
        echo "✅ Table orders recréée avec le statut 'ancienne'\n";
        
        // 4. Restaurer les données
        echo "4. Restauration des données...\n";
        
        foreach ($orders as $order) {
            $orderArray = (array) $order;
            
            // Construire la requête d'insertion
            $columns = array_keys($orderArray);
            $values = array_values($orderArray);
            
            // Échapper les valeurs NULL et les chaînes
            $escapedValues = array_map(function($value) {
                if ($value === null) {
                    return 'NULL';
                } elseif (is_string($value)) {
                    return "'" . str_replace("'", "''", $value) . "'";
                } else {
                    return $value;
                }
            }, $values);
            
            $columnsStr = '"' . implode('", "', $columns) . '"';
            $valuesStr = implode(', ', $escapedValues);
            
            try {
                DB::statement("INSERT INTO orders ({$columnsStr}) VALUES ({$valuesStr})");
            } catch (Exception $e) {
                echo "  ⚠️ Erreur restauration commande ID {$order->id}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Données restaurées\n";
        
    } else {
        echo "✅ Le statut 'ancienne' est déjà présent dans la contrainte CHECK\n";
    }
    
    // 5. Test du statut "ancienne"
    echo "5. Test du statut 'ancienne'...\n";
    
    try {
        $testId = DB::table('orders')->insertGetId([
            'admin_id' => 1,
            'customer_phone' => 'TEST_STATUS',
            'status' => 'ancienne',
            'attempts_count' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        DB::table('orders')->where('id', $testId)->delete();
        echo "✅ Test statut 'ancienne' réussi\n";
    } catch (Exception $e) {
        throw new Exception("Test statut 'ancienne' échoué: " . $e->getMessage());
    }
    
    // 6. Migrer les commandes existantes
    echo "6. Migration des commandes vers 'ancienne'...\n";
    
    $standardMaxAttempts = (int)AdminSetting::get('standard_max_total_attempts', 9);
    echo "📊 Seuil configuré: {$standardMaxAttempts} tentatives\n";
    
    $ordersToMigrate = DB::table('orders')
        ->where('status', 'nouvelle')
        ->where('attempts_count', '>=', $standardMaxAttempts)
        ->get();
    
    echo "📋 " . count($ordersToMigrate) . " commande(s) à migrer\n";
    
    $migrated = 0;
    foreach ($ordersToMigrate as $order) {
        try {
            // Mettre à jour le statut
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'status' => 'ancienne',
                    'updated_at' => now()
                ]);
            
            // Ajouter à l'historique
            DB::table('order_history')->insert([
                'order_id' => $order->id,
                'user_id' => $order->admin_id,
                'user_type' => 'Admin',
                'action' => 'changement_statut',
                'status_before' => 'nouvelle',
                'status_after' => 'ancienne',
                'notes' => "Migration automatique vers file ancienne - {$order->attempts_count} tentatives (seuil: {$standardMaxAttempts})",
                'changes' => json_encode(['auto_migration' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $migrated++;
            echo "  ✅ Commande #{$order->id} migrée (tentatives: {$order->attempts_count})\n";
            
        } catch (Exception $e) {
            echo "  ❌ Erreur commande #{$order->id}: " . $e->getMessage() . "\n";
        }
    }
    
    // 7. Statistiques finales
    echo "\n📊 Statistiques finales:\n";
    $stats = [
        'nouvelle' => DB::table('orders')->where('status', 'nouvelle')->count(),
        'ancienne' => DB::table('orders')->where('status', 'ancienne')->count(),
        'datée' => DB::table('orders')->where('status', 'datée')->count(),
        'confirmée' => DB::table('orders')->where('status', 'confirmée')->count(),
        'annulée' => DB::table('orders')->where('status', 'annulée')->count(),
    ];
    
    foreach ($stats as $status => $count) {
        echo "  - {$status}: {$count}\n";
    }
    
    echo "\n🎉 Correction terminée avec succès !\n";
    echo "✅ {$migrated} commande(s) migrée(s) vers le statut 'ancienne'\n";
    echo "👉 Vous pouvez maintenant aller sur /admin/process pour tester\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📍 Ligne: " . $e->getLine() . "\n";
}