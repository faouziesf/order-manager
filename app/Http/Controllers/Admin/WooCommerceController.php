<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\City;
use App\Models\WooCommerceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WooCommerceController extends Controller
{
    /**
     * Affiche la page de configuration WooCommerce
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        // Récupérer toutes les intégrations de cet admin
        $integrations = WooCommerceSetting::where('admin_id', $admin->id)->get();
        
        // Créer une nouvelle intégration si aucune n'existe
        $settings = new WooCommerceSetting(['admin_id' => $admin->id]);
        
        // Statistiques globales de synchronisation
        $syncStats = [
            'total_orders' => Order::where('admin_id', $admin->id)
                ->where('external_source', 'woocommerce')
                ->count(),
            'total_integrations' => $integrations->count(),
            'active_integrations' => $integrations->where('is_active', true)->count(),
        ];
        
        return view('admin.woocommerce.index', compact('settings', 'syncStats', 'integrations'));
    }

    /**
     * Enregistre ou met à jour les paramètres WooCommerce
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_url' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
        ]);

        $admin = Auth::guard('admin')->user();
        
        // Vérifier si une intégration existe déjà pour cette URL
        $settings = WooCommerceSetting::where('admin_id', $admin->id)
            ->where('store_url', rtrim($request->store_url, '/'))
            ->first();
            
        if (!$settings) {
            $settings = new WooCommerceSetting(['admin_id' => $admin->id]);
        }
        
        // Mettre à jour les paramètres
        $settings->store_url = rtrim($request->store_url, '/');
        $settings->consumer_key = $request->consumer_key;
        $settings->consumer_secret = $request->consumer_secret;
        
        // CORRECTION IMPORTANTE : Gestion correcte de la checkbox
        $settings->is_active = $request->has('is_active') && $request->boolean('is_active');
        
        // Tester la connexion avant de sauvegarder
        $testResult = $settings->testConnection();
        
        if (!$testResult['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Impossible de se connecter à WooCommerce: ' . $testResult['message']);
        }
        
        $settings->save();
        
        $message = 'Paramètres WooCommerce sauvegardés avec succès. ' . $testResult['message'];
        if ($settings->is_active) {
            $message .= ' Intégration activée !';
        }
        
        return redirect()->route('admin.woocommerce.index')
            ->with('success', $message);
    }

    /**
     * Désactive une intégration spécifique
     */
    public function toggleIntegration(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        $settings = WooCommerceSetting::where('admin_id', $admin->id)->findOrFail($id);
        
        $settings->is_active = !$settings->is_active;
        $settings->save();
        
        $status = $settings->is_active ? 'activée' : 'désactivée';
        
        return response()->json([
            'success' => true,
            'message' => "Intégration {$status} avec succès",
            'is_active' => $settings->is_active
        ]);
    }

    /**
     * Supprime une intégration
     */
    public function deleteIntegration($id)
    {
        $admin = Auth::guard('admin')->user();
        $settings = WooCommerceSetting::where('admin_id', $admin->id)->findOrFail($id);
        
        $storeUrl = parse_url($settings->store_url, PHP_URL_HOST);
        $settings->delete();
        
        return redirect()->route('admin.woocommerce.index')
            ->with('success', "Intégration avec {$storeUrl} supprimée avec succès");
    }

    /**
     * Test de connexion en temps réel
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'store_url' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
        ]);

        try {
            $client = new \Automattic\WooCommerce\Client(
                rtrim($request->store_url, '/'),
                $request->consumer_key,
                $request->consumer_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v3',
                    'timeout' => 30,
                ]
            );

            // Test simple de l'API
            $response = $client->get('');
            
            return response()->json([
                'success' => true,
                'message' => 'Connexion établie avec succès',
                'store_info' => [
                    'name' => $response->name ?? 'Boutique WooCommerce',
                    'version' => $response->version ?? 'Non spécifiée'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Synchronise manuellement les commandes depuis WooCommerce
     */
    public function sync()
    {
        $admin = Auth::guard('admin')->user();
        $activeSettings = WooCommerceSetting::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->get();
        
        if ($activeSettings->isEmpty()) {
            return redirect()->route('admin.woocommerce.index')
                ->with('error', 'Aucune intégration WooCommerce active.');
        }
        
        $totalImported = 0;
        $totalUpdated = 0;
        $errors = [];
        
        foreach ($activeSettings as $settings) {
            try {
                $result = $this->performSync($admin, $settings);
                $totalImported += $result['imported'];
                $totalUpdated += $result['updated'];
            } catch (\Exception $e) {
                $storeUrl = parse_url($settings->store_url, PHP_URL_HOST);
                $errors[] = "{$storeUrl}: " . $e->getMessage();
                Log::error('Erreur lors de la synchronisation WooCommerce: ' . $e->getMessage());
            }
        }
        
        $message = "Synchronisation terminée. {$totalImported} nouvelles commandes importées, {$totalUpdated} mises à jour.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', $errors);
        }
        
        return redirect()->route('admin.woocommerce.index')
            ->with($errors ? 'warning' : 'success', $message);
    }

    /**
     * Statistiques de synchronisation
     */
    public function syncStats()
    {
        $admin = Auth::guard('admin')->user();
        $integrations = WooCommerceSetting::where('admin_id', $admin->id)->get();
        
        $stats = [
            'total_integrations' => $integrations->count(),
            'active_integrations' => $integrations->where('is_active', true)->count(),
            'total_orders' => Order::where('admin_id', $admin->id)
                ->where('external_source', 'woocommerce')
                ->count(),
            'recent_orders' => Order::where('admin_id', $admin->id)
                ->where('external_source', 'woocommerce')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'integrations' => $integrations->map(function($integration) {
                return [
                    'id' => $integration->id,
                    'store_url' => parse_url($integration->store_url, PHP_URL_HOST),
                    'is_active' => $integration->is_active,
                    'sync_status' => $integration->sync_status,
                    'last_sync' => $integration->last_sync_at ? $integration->last_sync_at->diffForHumans() : 'Jamais',
                    'error' => $integration->sync_error
                ];
            })
        ];
        
        return response()->json($stats);
    }

    /**
     * Synchronisation automatique (appelée par le scheduler)
     */
    public function autoSync()
    {
        $activeSettings = WooCommerceSetting::where('is_active', true)->get();
        
        $results = [
            'total_imported' => 0,
            'total_updated' => 0,
            'errors' => []
        ];
        
        foreach ($activeSettings as $setting) {
            try {
                $admin = $setting->admin;
                $result = $this->performSync($admin, $setting);
                
                $results['total_imported'] += $result['imported'];
                $results['total_updated'] += $result['updated'];
                
                Log::info("WooCommerce auto-sync pour l'admin #{$admin->id}: {$result['imported']} importées, {$result['updated']} mises à jour");
                
            } catch (\Exception $e) {
                $error = "Admin #{$setting->admin_id}: " . $e->getMessage();
                $results['errors'][] = $error;
                
                Log::error("Erreur auto-sync WooCommerce: {$error}");
                
                $setting->update([
                    'sync_status' => 'error',
                    'sync_error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Synchronisation complète et améliorée
     */
    private function performSync($admin, $settings)
    {
        // Mettre à jour le statut de synchronisation
        $settings->update(['sync_status' => 'syncing']);
        
        try {
            $client = $settings->getClient();
            
            if (!$client) {
                throw new \Exception('Paramètres de connexion WooCommerce non valides');
            }
            
            $imported = 0;
            $updated = 0;
            $page = 1;
            $perPage = 50;
            
            // Définir la date de la dernière synchronisation
            $lastSyncDate = $settings->last_sync_at ? $settings->last_sync_at : now()->subDays(30);
            
            DB::beginTransaction();
            
            do {
                // Récupérer les commandes depuis WooCommerce
                $wooOrders = $client->get('orders', [
                    'modified_after' => $lastSyncDate->format('Y-m-d\TH:i:s'),
                    'per_page' => $perPage,
                    'page' => $page,
                    'status' => ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'],
                ]);
                
                foreach ($wooOrders as $wooOrder) {
                    $result = $this->syncOrder($admin, $settings, $wooOrder);
                    if ($result === 'imported') {
                        $imported++;
                    } elseif ($result === 'updated') {
                        $updated++;
                    }
                }
                
                $page++;
                
            } while (count($wooOrders) === $perPage);
            
            DB::commit();
            
            // Mettre à jour le statut de synchronisation
            $settings->update([
                'sync_status' => 'idle',
                'last_sync_at' => now(),
                'sync_error' => null
            ]);
            
            return [
                'imported' => $imported,
                'updated' => $updated
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $settings->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Synchronise une commande spécifique
     */
    private function syncOrder($admin, $settings, $wooOrder)
    {
        // Vérifier si la commande existe déjà
        $existingOrder = Order::where('admin_id', $admin->id)
            ->where('external_id', $wooOrder->id)
            ->where('external_source', 'woocommerce')
            ->first();
        
        if ($existingOrder) {
            // Mettre à jour la commande existante
            $this->updateExistingOrder($existingOrder, $wooOrder);
            return 'updated';
        } else {
            // Créer une nouvelle commande
            $this->createNewOrder($admin, $wooOrder);
            return 'imported';
        }
    }

    /**
     * Met à jour une commande existante
     */
    private function updateExistingOrder($order, $wooOrder)
    {
        $statusBefore = $order->status;
        
        // Mapper le statut WooCommerce vers Order Manager
        $newStatus = $this->mapWooCommerceStatusToOrderManager($wooOrder->status);
        
        // Mettre à jour les informations si nécessaire
        $order->update([
            'status' => $newStatus,
            'customer_email' => $wooOrder->billing->email ?? $order->customer_email,
            'notes' => $this->generateOrderNotes($wooOrder),
        ]);
        
        // Enregistrer l'historique si le statut a changé
        if ($statusBefore !== $newStatus) {
            $order->recordHistory(
                'modification',
                "Statut mis à jour depuis WooCommerce: {$wooOrder->status} → {$newStatus}",
                ['woocommerce_status' => $wooOrder->status],
                $statusBefore,
                $newStatus
            );
        }
    }

    /**
     * Crée une nouvelle commande
     */
    private function createNewOrder($admin, $wooOrder)
    {
        // Créer la nouvelle commande
        $order = new Order();
        $order->admin_id = $admin->id;
        $order->external_id = $wooOrder->id;
        $order->external_source = 'woocommerce';
        
        // Informations client
        $order->customer_name = trim($wooOrder->billing->first_name . ' ' . $wooOrder->billing->last_name);
        $order->customer_phone = $this->cleanPhoneNumber($wooOrder->billing->phone ?? '');
        $order->customer_email = $wooOrder->billing->email ?? null;
        
        // Adresse complète
        $addressParts = array_filter([
            $wooOrder->billing->address_1,
            $wooOrder->billing->address_2,
            $wooOrder->billing->postcode,
            $wooOrder->billing->city,
            $wooOrder->billing->state,
            $wooOrder->billing->country
        ]);
        $order->customer_address = implode(', ', $addressParts);
        
        // Assigner directement les données WooCommerce sans valeurs par défaut
        $this->assignLocationFromWooCommerce($order, $wooOrder);
        
        // Utiliser le statut mappé depuis WooCommerce
        $order->status = $this->mapWooCommerceStatusToOrderManager($wooOrder->status);
        $order->priority = 'normale'; // Seule valeur par défaut
        
        // Frais d'expédition
        $shippingTotal = 0;
        foreach ($wooOrder->shipping_lines as $shipping) {
            $shippingTotal += floatval($shipping->total);
        }
        $order->shipping_cost = $shippingTotal;
        
        // Notes détaillées
        $order->notes = $this->generateOrderNotes($wooOrder);
        
        // Enregistrer la commande
        $order->save();
        
        // Traiter les produits
        $this->processOrderItems($order, $wooOrder, $admin);
        
        // Enregistrer l'historique
        $order->recordHistory('création', 'Commande importée depuis WooCommerce');
        
        return $order;
    }

    /**
     * Traite les articles de la commande
     */
    private function processOrderItems($order, $wooOrder, $admin)
    {
        $orderTotal = 0;
        
        foreach ($wooOrder->line_items as $item) {
            // Rechercher ou créer le produit
            $product = Product::where('admin_id', $admin->id)
                ->where('name', $item->name)
                ->first();
            
            if (!$product) {
                $product = Product::create([
                    'admin_id' => $admin->id,
                    'name' => $item->name,
                    'price' => floatval($item->price),
                    'stock' => 1000000, // Stock par défaut
                    'is_active' => true,
                    'needs_review' => true,
                ]);
            }
            
            // Ajouter le produit à la commande
            $orderItem = $order->items()->create([
                'product_id' => $product->id,
                'quantity' => intval($item->quantity),
                'unit_price' => floatval($item->price),
                'total_price' => floatval($item->total),
            ]);
            
            $orderTotal += $orderItem->total_price;
        }
        
        // Mettre à jour le total de la commande
        $order->update(['total_price' => $orderTotal]);
    }

    /**
     * Assigne la localisation depuis WooCommerce directement
     */
    private function assignLocationFromWooCommerce($order, $wooOrder)
    {
        $governorate = null;
        $city = null;
        
        // Rechercher ou créer le gouvernorat
        if (!empty($wooOrder->billing->state)) {
            $governorate = Region::firstOrCreate(
                ['name' => $wooOrder->billing->state],
                ['name' => $wooOrder->billing->state]
            );
        }
        
        // Rechercher ou créer la ville
        if (!empty($wooOrder->billing->city)) {
            $city = City::firstOrCreate(
                [
                    'name' => $wooOrder->billing->city,
                    'region_id' => $governorate ? $governorate->id : null
                ],
                [
                    'name' => $wooOrder->billing->city,
                    'region_id' => $governorate ? $governorate->id : null,
                    'shipping_cost' => 0
                ]
            );
        }
        
        $order->customer_governorate = $governorate ? $governorate->id : null;
        $order->customer_city = $city ? $city->id : null;
    }

    /**
     * Génère les notes détaillées de la commande
     */
    private function generateOrderNotes($wooOrder)
    {
        $notes = [
            "Commande WooCommerce #{$wooOrder->id}",
            "Statut WooCommerce: {$wooOrder->status}",
            "Méthode de paiement: {$wooOrder->payment_method_title}",
            "Total: {$wooOrder->currency_symbol}{$wooOrder->total}",
            "Date de création: " . Carbon::parse($wooOrder->date_created)->format('d/m/Y H:i'),
        ];
        
        if (!empty($wooOrder->customer_note)) {
            $notes[] = "Note client: {$wooOrder->customer_note}";
        }
        
        return implode("\n", $notes);
    }

    /**
     * Nettoie et formate le numéro de téléphone
     */
    private function cleanPhoneNumber($phone)
    {
        // Supprimer tous les caractères non numériques sauf le +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // Si le numéro commence par 00, le remplacer par +
        if (substr($cleaned, 0, 2) === '00') {
            $cleaned = '+' . substr($cleaned, 2);
        }
        
        return $cleaned ?: '0000000000'; // Valeur par défaut si vide
    }

    /**
     * Mappe les statuts WooCommerce vers Order Manager
     * Seuls les statuts terminés/annulés gardent leur mapping, les autres deviennent "nouvelle"
     */
    private function mapWooCommerceStatusToOrderManager($wooStatus)
    {
        $statusMap = [
            // Statuts terminés - gardent leur mapping
            'completed' => 'livrée',
            
            // Statuts annulés - gardent leur mapping  
            'cancelled' => 'annulée',
            'refunded' => 'annulée',
            'failed' => 'annulée',
            
            // TOUS les autres statuts deviennent "nouvelle"
            'pending' => 'nouvelle',
            'processing' => 'nouvelle', 
            'on-hold' => 'nouvelle',
        ];
        
        // Si le statut n'est pas dans la map, il devient "nouvelle" par défaut
        return $statusMap[$wooStatus] ?? 'nouvelle';
    }
}