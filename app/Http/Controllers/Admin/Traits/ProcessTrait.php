<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\AdminSetting;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait ProcessTrait
{
    /**
     * Obtenir un paramètre de configuration
     */
    protected function getSetting($key, $default = null)
    {
        $admin = Auth::guard('admin')->user();
        return AdminSetting::getForAdmin($admin->id, $key, $default);
    }

    /**
     * Réinitialiser les compteurs quotidiens si nécessaire
     */
    protected function resetDailyCountersIfNeeded($admin)
    {
        $lastReset = AdminSetting::getForAdmin($admin->id, 'last_daily_reset');
        $today = now()->format('Y-m-d');
        
        if (!$lastReset || $lastReset !== $today) {
            Order::where('admin_id', $admin->id)->update(['daily_attempts_count' => 0]);
            AdminSetting::setForAdmin($admin->id, 'last_daily_reset', $today);
            Log::info("Compteurs quotidiens réinitialisés pour admin {$admin->id}");
        }
    }

    /**
     * Vérifier si une commande a des problèmes de stock - LOGIQUE CORRIGÉE
     */
    protected function orderHasStockIssues($order)
    {
        // Vérifier si la commande existe
        if (!$order || !$order->id) {
            Log::warning("Commande invalide ou sans ID");
            return true;
        }
        
        // Charger les items si pas déjà chargés
        if (!$order->relationLoaded('items')) {
            $order->load(['items.product']);
        }
        
        // Si pas d'items, considérer comme problématique
        if (!$order->items || $order->items->isEmpty()) {
            Log::warning("Commande {$order->id} sans items");
            return true;
        }
        
        // Vérifier chaque item
        foreach ($order->items as $item) {
            // Vérifier si l'item a les données requises
            if (!$item->product_id || !$item->quantity) {
                Log::warning("Item invalide pour commande {$order->id}, item {$item->id}");
                return true;
            }
            
            // Si le produit n'existe plus ou n'est pas chargé
            if (!$item->product) {
                Log::warning("Produit manquant pour commande {$order->id}, product_id {$item->product_id}");
                return true;
            }
            
            // Si le produit est inactif
            if (!$item->product->is_active) {
                Log::info("Produit inactif pour commande {$order->id}, product {$item->product->id}");
                return true;
            }
            
            // Vérifier le stock (conversion en entier pour éviter les problèmes de type)
            $requiredStock = (int)$item->quantity;
            $availableStock = (int)$item->product->stock;
            
            if ($availableStock < $requiredStock) {
                Log::info("Stock insuffisant pour commande {$order->id}, produit {$item->product->id}: besoin {$requiredStock}, disponible {$availableStock}");
                return true;
            }
        }
        
        // Aucun problème détecté
        return false;
    }

    /**
     * Analyser les problèmes de stock d'une commande - MÉTHODE CORRIGÉE
     */
    protected function analyzeOrderStockIssues($order)
    {
        $analysis = [
            'hasIssues' => false,
            'availableItems' => collect(),
            'unavailableItems' => collect(),
            'issues' => []
        ];
        
        if (!$order || !$order->items || $order->items->isEmpty()) {
            $analysis['hasIssues'] = true;
            $analysis['issues'][] = [
                'type' => 'no_items',
                'message' => 'Commande sans produits'
            ];
            return $analysis;
        }
        
        foreach ($order->items as $item) {
            $itemIssues = [];
            $hasItemIssues = false;
            
            // Vérifier l'existence du produit
            if (!$item->product) {
                $itemIssues[] = 'Produit supprimé';
                $hasItemIssues = true;
            } else {
                // Vérifier si le produit est actif
                if (!$item->product->is_active) {
                    $itemIssues[] = 'Produit inactif';
                    $hasItemIssues = true;
                }
                
                // Vérifier le stock
                $requiredStock = (int)$item->quantity;
                $availableStock = (int)($item->product->stock ?? 0);
                
                if ($availableStock < $requiredStock) {
                    $itemIssues[] = "Stock insuffisant (besoin: {$requiredStock}, disponible: {$availableStock})";
                    $hasItemIssues = true;
                }
            }
            
            // Classer l'item
            if ($hasItemIssues) {
                $analysis['unavailableItems']->push($item);
                $analysis['issues'][] = [
                    'product_name' => $item->product ? $item->product->name : "Produit #{$item->product_id}",
                    'reasons' => $itemIssues
                ];
                $analysis['hasIssues'] = true;
            } else {
                $analysis['availableItems']->push($item);
            }
        }
        
        return $analysis;
    }

    /**
     * Suspendre automatiquement une commande pour problème de stock
     */
    protected function autoSuspendOrderForStock($order)
    {
        try {
            if ($this->orderHasStockIssues($order)) {
                $analysis = $this->analyzeOrderStockIssues($order);
                
                $reasons = [];
                foreach ($analysis['issues'] as $issue) {
                    $reasons[] = $issue['product_name'] . ': ' . implode(', ', $issue['reasons']);
                }
                
                $order->is_suspended = true;
                $order->suspension_reason = 'Auto-suspension: ' . implode(' | ', array_slice($reasons, 0, 3));
                if (count($reasons) > 3) {
                    $order->suspension_reason .= ' (et ' . (count($reasons) - 3) . ' autre(s))';
                }
                $order->save();
                
                $order->recordHistory(
                    'suspension', 
                    'Commande suspendue automatiquement pour problèmes de stock: ' . implode(', ', array_slice($reasons, 0, 2))
                );
                
                Log::info("Commande {$order->id} suspendue automatiquement pour problèmes de stock");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suspension automatique de la commande {$order->id}: " . $e->getMessage());
        }
        
        return false;
    }

    /**
     * Enregistrer une tentative d'appel - SIMPLIFIÉ
     */
    protected function recordCallAttempt($order, $notes)
    {
        $order->increment('attempts_count');
        $order->increment('daily_attempts_count');
        $order->last_attempt_at = now();
        $order->save();
        
        // Enregistrer dans l'historique
        $order->recordHistory('tentative', $notes);
        
        // Vérifier la transition automatique vers file ancienne
        $this->checkForAutoTransition($order);
    }

    /**
     * Vérifier et effectuer la transition automatique vers file ancienne
     */
    protected function checkForAutoTransition($order)
    {
        if ($order->status === 'nouvelle') {
            $standardMaxAttempts = (int)$this->getSetting('standard_max_total_attempts', 9);
            
            if ($order->attempts_count >= $standardMaxAttempts) {
                $previousStatus = $order->status;
                $order->status = 'ancienne';
                $order->save();
                
                $order->recordHistory(
                    'modification',
                    "Commande automatiquement passée en file ancienne après {$order->attempts_count} tentatives",
                    [
                        'previous_status' => $previousStatus,
                        'new_status' => 'ancienne',
                        'attempts_reached' => $order->attempts_count,
                        'auto_transition' => true
                    ],
                    $previousStatus,
                    'ancienne'
                );
                
                Log::info("Commande #{$order->id} automatiquement passée en file ancienne");
                return true;
            }
        }
        
        return false;
    }

    /**
     * Confirmer une commande - SIMPLIFIÉ
     */
    protected function confirmOrder($order, $request, $notes)
    {
        $order->status = 'confirmée';
        $order->total_price = $request->confirmed_price; // Prix total confirmé
        
        // Mettre à jour les informations client
        $order->customer_name = $request->customer_name;
        $order->customer_phone_2 = $request->customer_phone_2;
        $order->customer_governorate = $request->customer_governorate;
        $order->customer_city = $request->customer_city;
        $order->customer_address = $request->customer_address;
        
        $order->save();
        
        $order->recordHistory('confirmation', $notes);
    }

    /**
     * Validation pour la confirmation - CORRIGÉE
     */
    protected function validateConfirmation($request)
    {
        $request->validate([
            'confirmed_price' => 'required|numeric|min:0.001',
            'customer_name' => 'required|string|min:2|max:255',
            'customer_governorate' => 'required|string|max:255',
            'customer_city' => 'required|string|max:255', 
            'customer_address' => 'required|string|min:5|max:500',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|exists:products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'cart_items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'confirmed_price.required' => 'Le prix total confirmé est obligatoire',
            'confirmed_price.min' => 'Le prix doit être supérieur à 0',
            'customer_name.required' => 'Le nom du client est obligatoire',
            'customer_name.min' => 'Le nom doit contenir au moins 2 caractères',
            'customer_governorate.required' => 'Le gouvernorat est obligatoire',
            'customer_city.required' => 'La ville est obligatoire',
            'customer_address.required' => 'L\'adresse est obligatoire',
            'customer_address.min' => 'L\'adresse doit contenir au moins 5 caractères',
            'cart_items.required' => 'Au moins un produit est requis',
            'cart_items.min' => 'Au moins un produit est requis',
        ]);
    }

    /**
     * Mettre à jour les items de la commande et décrémenter le stock - CORRIGÉ AVEC DÉCRÉMENTATION
     */
    protected function updateOrderItems($order, $cartItems)
    {
        if (!$cartItems || !is_array($cartItems)) {
            throw new \Exception('Aucun produit fourni pour la mise à jour');
        }
        
        DB::beginTransaction();
        
        try {
            // ÉTAPE 1: Vérifier que tous les produits ont un stock suffisant AVANT de commencer
            foreach ($cartItems as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    continue;
                }
                
                $product = Product::where('id', $item['product_id'])
                    ->where('admin_id', $order->admin_id)
                    ->where('is_active', true)
                    ->lockForUpdate() // Verrouiller pour éviter les conditions de course
                    ->first();
                
                if (!$product) {
                    throw new \Exception("Produit {$item['product_id']} non trouvé ou inactif");
                }
                
                $quantity = (int)$item['quantity'];
                
                // Vérification critique du stock
                if ((int)$product->stock < $quantity) {
                    throw new \Exception("Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock}, quantité demandée: {$quantity}");
                }
            }
            
            // ÉTAPE 2: Supprimer les anciens items (sans remettre le stock car c'était une commande non confirmée)
            $order->items()->delete();
            
            // ÉTAPE 3: Traitement des nouveaux items avec décrémentation du stock
            $totalPrice = 0;
            $processedProducts = []; // Pour éviter les doublons
            
            foreach ($cartItems as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    continue;
                }
                
                $productId = $item['product_id'];
                $quantity = (int)$item['quantity'];
                
                // Éviter les doublons dans le même panier
                if (isset($processedProducts[$productId])) {
                    $processedProducts[$productId] += $quantity;
                    $quantity = $processedProducts[$productId];
                } else {
                    $processedProducts[$productId] = $quantity;
                }
                
                $product = Product::where('id', $productId)
                    ->where('admin_id', $order->admin_id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();
                
                if (!$product) {
                    throw new \Exception("Produit {$productId} non trouvé ou inactif");
                }
                
                $unitPrice = (float)($item['unit_price'] ?? $product->price);
                $totalItemPrice = $quantity * $unitPrice;
                $totalPrice += $totalItemPrice;
                
                // Vérification finale du stock avant décrémentation
                if ((int)$product->stock < $quantity) {
                    throw new \Exception("Stock insuffisant pour {$product->name}. Stock disponible: {$product->stock}, quantité demandée: {$quantity}");
                }
                
                // Créer le nouvel item de commande
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalItemPrice,
                ]);
                
                // *** DÉCRÉMENTER LE STOCK DU PRODUIT - POINT CRITIQUE ***
                $oldStock = $product->stock;
                $newStock = (int)$product->stock - $quantity;
                
                // Sécurité supplémentaire
                if ($newStock < 0) {
                    throw new \Exception("Tentative de décrémentation du stock en négatif pour {$product->name}");
                }
                
                $product->stock = $newStock;
                $product->save();
                
                Log::info("STOCK DÉCRÉMENTÉ - Produit {$product->id} ({$product->name}): {$oldStock} → {$newStock} (-{$quantity}) | Commande {$order->id}");
            }
            
            // Le prix total est déjà défini dans confirmOrder()
            // On ne le modifie pas ici pour garder le prix confirmé par l'utilisateur
            
            DB::commit();
            
            Log::info("Commande {$order->id} confirmée avec succès - Stock mis à jour pour " . count($processedProducts) . " produit(s)");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la mise à jour des items de la commande {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtenir les informations de doublons pour une commande
     */
    protected function getDuplicateInfo($order)
    {
        if (!$order) {
            return [
                'has_duplicates' => false,
                'duplicates_count' => 0,
                'duplicates' => []
            ];
        }

        // Rechercher les doublons par téléphone
        $duplicates = Order::where('admin_id', $order->admin_id)
            ->where('id', '!=', $order->id)
            ->where(function($query) use ($order) {
                $query->where('customer_phone', $order->customer_phone);
                if ($order->customer_phone_2) {
                    $query->orWhere('customer_phone', $order->customer_phone_2)
                          ->orWhere('customer_phone_2', $order->customer_phone)
                          ->orWhere('customer_phone_2', $order->customer_phone_2);
                }
            })
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $duplicatesData = $duplicates->map(function($duplicate) {
            return [
                'id' => $duplicate->id,
                'status' => $duplicate->status,
                'customer_name' => $duplicate->customer_name,
                'customer_phone' => $duplicate->customer_phone,
                'customer_phone_2' => $duplicate->customer_phone_2,
                'total_price' => $duplicate->total_price,
                'created_at' => $duplicate->created_at->format('d/m/Y H:i'),
                'items_count' => $duplicate->items->count(),
                'items' => $duplicate->items->map(function($item) {
                    return [
                        'product_name' => $item->product ? $item->product->name : 'Produit supprimé',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price
                    ];
                })
            ];
        });

        return [
            'has_duplicates' => $duplicates->count() > 0,
            'duplicates_count' => $duplicates->count(),
            'duplicates' => $duplicatesData
        ];
    }

    /**
     * Formater les données d'une commande pour l'API
     */
    protected function formatOrderData($order)
    {
        if (!$order) {
            return null;
        }
        
        // Obtenir les informations de doublons
        $duplicateInfo = $this->getDuplicateInfo($order);
        
        return [
            'id' => $order->id,
            'status' => $order->status,
            'priority' => $order->priority,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_phone_2' => $order->customer_phone_2,
            'customer_governorate' => $order->customer_governorate,
            'customer_city' => $order->customer_city,
            'customer_address' => $order->customer_address,
            'total_price' => $order->total_price,
            'attempts_count' => $order->attempts_count,
            'daily_attempts_count' => $order->daily_attempts_count,
            'last_attempt_at' => $order->last_attempt_at ? $order->last_attempt_at->toISOString() : null,
            'scheduled_date' => $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : null,
            'is_assigned' => $order->is_assigned,
            'is_suspended' => $order->is_suspended,
            'suspension_reason' => $order->suspension_reason,
            'notes' => $order->notes,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'duplicate_info' => $duplicateInfo,
            'items' => $order->items ? $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'stock' => $item->product->stock,
                        'is_active' => $item->product->is_active
                    ] : null
                ];
            }) : []
        ];
    }
}