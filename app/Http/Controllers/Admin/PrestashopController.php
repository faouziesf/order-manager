<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\City;
use App\Models\PrestashopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrestashopController extends Controller
{
    /**
     * Affiche la page de configuration PrestaShop
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        // Récupérer toutes les intégrations de cet admin
        $integrations = PrestashopSetting::where('admin_id', $admin->id)->get();

        // Créer une nouvelle intégration si aucune n'existe
        $settings = new PrestashopSetting(['admin_id' => $admin->id]);

        // Statistiques globales de synchronisation
        $syncStats = [
            'total_orders' => Order::where('admin_id', $admin->id)
                ->where('external_source', 'prestashop')
                ->count(),
            'total_integrations' => $integrations->count(),
            'active_integrations' => $integrations->where('is_active', true)->count(),
        ];

        return view('admin.prestashop.index', compact('settings', 'syncStats', 'integrations'));
    }

    /**
     * Enregistre ou met à jour les paramètres PrestaShop
     */
    public function store(Request $request)
    {
        $request->validate([
            'shop_url' => 'required|string',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'access_token' => 'nullable|string',
        ]);

        $admin = Auth::guard('admin')->user();

        // Nettoyer l'URL
        $shopUrl = strtolower(trim($request->shop_url));
        $shopUrl = preg_replace('#^https?://#', '', $shopUrl);
        $shopUrl = rtrim($shopUrl, '/');

        // Vérifier si une intégration existe déjà pour cette URL
        $settings = PrestashopSetting::where('admin_id', $admin->id)
            ->where('shop_url', $shopUrl)
            ->first();

        if (!$settings) {
            $settings = new PrestashopSetting(['admin_id' => $admin->id]);
        }

        // Mettre à jour les paramètres
        $settings->shop_url = $shopUrl;
        $settings->api_key = $request->api_key;
        $settings->api_secret = $request->api_secret;
        $settings->access_token = $request->access_token;

        // Gestion correcte de la checkbox
        $settings->is_active = $request->has('is_active') && $request->boolean('is_active');

        // Tester la connexion avant de sauvegarder
        $testResult = $settings->testConnection();

        if (!$testResult['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Impossible de se connecter à PrestaShop: ' . $testResult['message']);
        }

        $settings->save();

        $message = 'Paramètres PrestaShop sauvegardés avec succès. ' . $testResult['message'];
        if ($settings->is_active) {
            $message .= ' Intégration activée !';
        }

        return redirect()->route('admin.prestashop.index')
            ->with('success', $message);
    }

    /**
     * Désactive une intégration spécifique
     */
    public function toggleIntegration(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        $settings = PrestashopSetting::where('admin_id', $admin->id)->findOrFail($id);

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
        $settings = PrestashopSetting::where('admin_id', $admin->id)->findOrFail($id);

        $shopUrl = $settings->shop_url;
        $settings->delete();

        return redirect()->route('admin.prestashop.index')
            ->with('success', "Intégration avec {$shopUrl} supprimée avec succès");
    }

    /**
     * Test de connexion en temps réel
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'shop_url' => 'required|string',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        try {
            // Créer un objet temporaire pour tester
            $tempSettings = new PrestashopSetting([
                'shop_url' => $request->shop_url,
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
                'access_token' => $request->access_token,
            ]);

            $result = $tempSettings->testConnection();

            if ($result['success']) {
                return response()->json($result);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Synchronise manuellement les commandes depuis PrestaShop
     */
    public function sync()
    {
        $admin = Auth::guard('admin')->user();
        $activeSettings = PrestashopSetting::where('admin_id', $admin->id)
            ->where('is_active', true)
            ->get();

        if ($activeSettings->isEmpty()) {
            return redirect()->route('admin.prestashop.index')
                ->with('error', 'Aucune intégration PrestaShop active.');
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
                $errors[] = "{$settings->shop_url}: " . $e->getMessage();
                Log::error('Erreur lors de la synchronisation PrestaShop: ' . $e->getMessage());
            }
        }

        $message = "Synchronisation terminée. {$totalImported} nouvelles commandes importées, {$totalUpdated} mises à jour.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', $errors);
        }

        return redirect()->route('admin.prestashop.index')
            ->with($errors ? 'warning' : 'success', $message);
    }

    /**
     * Statistiques de synchronisation
     */
    public function syncStats()
    {
        $admin = Auth::guard('admin')->user();
        $integrations = PrestashopSetting::where('admin_id', $admin->id)->get();

        $stats = [
            'total_integrations' => $integrations->count(),
            'active_integrations' => $integrations->where('is_active', true)->count(),
            'total_orders' => Order::where('admin_id', $admin->id)
                ->where('external_source', 'prestashop')
                ->count(),
            'recent_orders' => Order::where('admin_id', $admin->id)
                ->where('external_source', 'prestashop')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'integrations' => $integrations->map(function($integration) {
                return [
                    'id' => $integration->id,
                    'shop_url' => $integration->shop_url,
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
        $activeSettings = PrestashopSetting::where('is_active', true)->get();

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

                Log::info("PrestaShop auto-sync pour l'admin #{$admin->id}: {$result['imported']} importées, {$result['updated']} mises à jour");

            } catch (\Exception $e) {
                $error = "Admin #{$setting->admin_id}: " . $e->getMessage();
                $results['errors'][] = $error;

                Log::error("Erreur auto-sync PrestaShop: {$error}");

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
            if (!$settings->access_token) {
                throw new \Exception('Access token PrestaShop manquant');
            }

            $imported = 0;
            $updated = 0;

            // Définir la date de la dernière synchronisation
            $lastSyncDate = $settings->last_sync_at ? $settings->last_sync_at : now()->subDays(30);

            DB::beginTransaction();

            // Récupérer les commandes depuis PrestaShop
            $result = $settings->fetchOrders(250);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $prestashopOrders = $result['orders'];

            foreach ($prestashopOrders as $prestashopOrder) {
                // Filtrer par date de modification
                $updatedAt = Carbon::parse($prestashopOrder['updated_at']);
                if ($updatedAt->lt($lastSyncDate)) {
                    continue;
                }

                $syncResult = $this->syncOrder($admin, $settings, $prestashopOrder);
                if ($syncResult === 'imported') {
                    $imported++;
                } elseif ($syncResult === 'updated') {
                    $updated++;
                }
            }

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
    private function syncOrder($admin, $settings, $prestashopOrder)
    {
        // Vérifier si la commande existe déjà
        $existingOrder = Order::where('admin_id', $admin->id)
            ->where('external_id', $prestashopOrder['id'])
            ->where('external_source', 'prestashop')
            ->first();

        if ($existingOrder) {
            // Mettre à jour la commande existante
            $this->updateExistingOrder($existingOrder, $prestashopOrder);
            return 'updated';
        } else {
            // Créer une nouvelle commande
            $this->createNewOrder($admin, $prestashopOrder);
            return 'imported';
        }
    }

    /**
     * Met à jour une commande existante
     */
    private function updateExistingOrder($order, $prestashopOrder)
    {
        $statusBefore = $order->status;

        // Mapper le statut PrestaShop vers Order Manager
        $newStatus = $this->mapPrestaShopStatusToOrderManager($prestashopOrder['financial_status'], $prestashopOrder['fulfillment_status'] ?? null);

        // Mettre à jour les informations si nécessaire
        $order->update([
            'status' => $newStatus,
            'customer_email' => $prestashopOrder['email'] ?? $order->customer_email,
            'notes' => $this->generateOrderNotes($prestashopOrder),
        ]);

        // Enregistrer l'historique si le statut a changé
        if ($statusBefore !== $newStatus) {
            $order->recordHistory(
                'modification',
                "Statut mis à jour depuis PrestaShop: {$prestashopOrder['financial_status']}/{$prestashopOrder['fulfillment_status']} → {$newStatus}",
                ['prestashop_financial_status' => $prestashopOrder['financial_status'], 'prestashop_fulfillment_status' => $prestashopOrder['fulfillment_status'] ?? null],
                $statusBefore,
                $newStatus
            );
        }
    }

    /**
     * Crée une nouvelle commande
     */
    private function createNewOrder($admin, $prestashopOrder)
    {
        // Créer la nouvelle commande
        $order = new Order();
        $order->admin_id = $admin->id;
        $order->external_id = $prestashopOrder['id'];
        $order->external_source = 'prestashop';

        // Informations client
        $billing = $prestashopOrder['billing_address'] ?? $prestashopOrder['shipping_address'] ?? [];
        $order->customer_name = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
        $order->customer_phone = $this->cleanPhoneNumber($billing['phone'] ?? $prestashopOrder['phone'] ?? '');
        $order->customer_email = $prestashopOrder['email'] ?? null;

        // Adresse complète
        $addressParts = array_filter([
            $billing['address1'] ?? '',
            $billing['address2'] ?? '',
            $billing['zip'] ?? '',
            $billing['city'] ?? '',
            $billing['province'] ?? '',
            $billing['country'] ?? ''
        ]);
        $order->customer_address = implode(', ', $addressParts);

        // Assigner directement les données PrestaShop sans valeurs par défaut
        $this->assignLocationFromPrestaShop($order, $prestashopOrder);

        // Utiliser le statut mappé depuis PrestaShop
        $order->status = $this->mapPrestaShopStatusToOrderManager($prestashopOrder['financial_status'], $prestashopOrder['fulfillment_status'] ?? null);
        $order->priority = 'normale'; // Seule valeur par défaut

        // Frais d'expédition
        $shippingTotal = 0;
        foreach ($prestashopOrder['shipping_lines'] ?? [] as $shipping) {
            $shippingTotal += floatval($shipping['price'] ?? 0);
        }
        $order->shipping_cost = $shippingTotal;

        // Notes détaillées
        $order->notes = $this->generateOrderNotes($prestashopOrder);

        // Enregistrer la commande
        $order->save();

        // Traiter les produits
        $this->processOrderItems($order, $prestashopOrder, $admin);

        // Enregistrer l'historique
        $order->recordHistory('création', 'Commande importée depuis PrestaShop');

        return $order;
    }

    /**
     * Traite les articles de la commande
     */
    private function processOrderItems($order, $prestashopOrder, $admin)
    {
        $orderTotal = 0;

        foreach ($prestashopOrder['line_items'] ?? [] as $item) {
            // Rechercher ou créer le produit
            $product = Product::where('admin_id', $admin->id)
                ->where('name', $item['name'])
                ->first();

            if (!$product) {
                $product = Product::create([
                    'admin_id' => $admin->id,
                    'name' => $item['name'],
                    'price' => floatval($item['price']),
                    'stock' => 1000000, // Stock par défaut
                    'is_active' => true,
                    'needs_review' => true,
                ]);
            }

            // Ajouter le produit à la commande
            $orderItem = $order->items()->create([
                'product_id' => $product->id,
                'quantity' => intval($item['quantity']),
                'unit_price' => floatval($item['price']),
                'total_price' => floatval($item['price']) * intval($item['quantity']),
            ]);

            $orderTotal += $orderItem->total_price;
        }

        // Mettre à jour le total de la commande
        $order->update(['total_price' => $orderTotal]);
    }

    /**
     * Assigne la localisation depuis PrestaShop directement
     */
    private function assignLocationFromPrestaShop($order, $prestashopOrder)
    {
        $billing = $prestashopOrder['billing_address'] ?? $prestashopOrder['shipping_address'] ?? [];
        $governorate = null;
        $city = null;

        // Rechercher ou créer le gouvernorat
        if (!empty($billing['province'])) {
            $governorate = Region::firstOrCreate(
                ['name' => $billing['province']],
                ['name' => $billing['province']]
            );
        }

        // Rechercher ou créer la ville
        if (!empty($billing['city'])) {
            $city = City::firstOrCreate(
                [
                    'name' => $billing['city'],
                    'region_id' => $governorate ? $governorate->id : null
                ],
                [
                    'name' => $billing['city'],
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
    private function generateOrderNotes($prestashopOrder)
    {
        $orderNumber = $prestashopOrder['order_number'] ?? $prestashopOrder['id'];
        $fulfillmentStatus = $prestashopOrder['fulfillment_status'] ?? 'unfulfilled';

        $notes = [
            "Commande PrestaShop #{$orderNumber}",
            "Statut financier: {$prestashopOrder['financial_status']}",
            "Statut de livraison: {$fulfillmentStatus}",
            "Total: {$prestashopOrder['currency']} {$prestashopOrder['total_price']}",
            "Date de création: " . Carbon::parse($prestashopOrder['created_at'])->format('d/m/Y H:i'),
        ];

        if (!empty($prestashopOrder['note'])) {
            $notes[] = "Note: {$prestashopOrder['note']}";
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
     * Mappe les statuts PrestaShop vers Order Manager
     */
    private function mapPrestaShopStatusToOrderManager($financialStatus, $fulfillmentStatus)
    {
        // Si la commande est livrée
        if ($fulfillmentStatus === 'fulfilled') {
            return 'livrée';
        }

        // Si la commande est annulée ou remboursée
        if ($financialStatus === 'refunded' || $financialStatus === 'voided') {
            return 'annulée';
        }

        // TOUS les autres statuts deviennent "nouvelle"
        return 'nouvelle';
    }
}
