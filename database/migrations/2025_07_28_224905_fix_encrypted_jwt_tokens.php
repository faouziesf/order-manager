<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    public function up(): void
    {
        // Corriger les tokens JAX et Mes Colis qui ont été chiffrés
        $configs = DB::table('delivery_configurations')
            ->whereIn('carrier_slug', ['jax_delivery', 'mes_colis'])
            ->get();
        
        foreach ($configs as $config) {
            if ($config->password) {
                try {
                    // Tenter de déchiffrer
                    $decrypted = Crypt::decryptString($config->password);
                    
                    // Si ça réussit et que c'est un JWT, sauvegarder déchiffré
                    if ($this->isValidJwtFormat($decrypted)) {
                        DB::table('delivery_configurations')
                            ->where('id', $config->id)
                            ->update(['password' => $decrypted]);
                        
                        echo "✅ Token {$config->carrier_slug} Config #{$config->id} corrigé\n";
                    }
                } catch (\Exception $e) {
                    // Si ça échoue, le token n'était pas chiffré
                    echo "ℹ️ Token {$config->carrier_slug} Config #{$config->id} déjà OK\n";
                }
            }
        }
    }
    
    private function isValidJwtFormat(string $token): bool
    {
        $parts = explode('.', $token);
        return count($parts) === 3;
    }
    
    public function down(): void
    {
        // Pas de rollback nécessaire
    }
};