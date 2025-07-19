<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Ajouter le champ référence après l'admin_id
            $table->integer('reference')->nullable()->after('admin_id')->index();
            
            // Ajouter l'index de contrainte d'unicité par admin
            $table->unique(['admin_id', 'reference'], 'unique_admin_product_reference');
            
            // Ajouter des index pour les performances
            $table->index(['admin_id', 'is_active']);
            $table->index(['admin_id', 'stock']);
            $table->index(['admin_id', 'needs_review']);
        });
        
        // Générer automatiquement des références pour les produits existants
        $this->generateReferencesForExistingProducts();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Supprimer les index avant de supprimer la colonne
            $table->dropIndex('unique_admin_product_reference');
            $table->dropIndex(['admin_id', 'is_active']);
            $table->dropIndex(['admin_id', 'stock']);
            $table->dropIndex(['admin_id', 'needs_review']);
            
            // Supprimer la colonne référence
            $table->dropColumn('reference');
        });
    }
    
    /**
     * Générer des références automatiques pour les produits existants
     */
    private function generateReferencesForExistingProducts(): void
    {
        // Récupérer tous les admins qui ont des produits
        $admins = \DB::table('admins')->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                  ->from('products')
                  ->whereColumn('products.admin_id', 'admins.id');
        })->get();
        
        foreach ($admins as $admin) {
            // Récupérer tous les produits de cet admin sans référence
            $products = \DB::table('products')
                ->where('admin_id', $admin->id)
                ->whereNull('reference')
                ->orderBy('created_at', 'asc')
                ->get();
            
            $currentReference = 1001; // Commencer à 1001
            
            foreach ($products as $product) {
                // Vérifier que cette référence n'existe pas déjà pour cet admin
                while (\DB::table('products')
                    ->where('admin_id', $admin->id)
                    ->where('reference', $currentReference)
                    ->exists()) {
                    $currentReference++;
                }
                
                // Mettre à jour le produit avec la nouvelle référence
                \DB::table('products')
                    ->where('id', $product->id)
                    ->update(['reference' => $currentReference]);
                
                $currentReference++;
            }
        }
    }
};