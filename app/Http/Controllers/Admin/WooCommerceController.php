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
        $settings = $admin->woocommerceSettings ?? new WooCommerceSetting(['admin_id' => $admin->id]);
        $regions = Region::with('cities')->orderBy('name')->get();
        
        return view('admin.woocommerce.index', compact('settings', 'regions'));
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
            'is_active' => 'sometimes|boolean',
            'default_status' => 'required|in:nouvelle,confirmée',
            'default_priority' => 'required|in:normale,urgente,vip',
            'default_governorate_id' => 'nullable|exists:regions,id',
            'default_city_id' => 'nullable|exists:cities,id',
        ]);

        $admin = Auth::guard('admin')->user();
        $settings = $admin->woocommerceSettings ?? new WooCommerceSetting(['admin_id' => $admin->id]);
        
        // Mettre à jour les paramètres
        $settings->store_url = $request->store_url;
        $settings->consumer_key = $request->consumer_key;
        $settings->consumer_secret = $request->consumer_secret;
        $settings->is_active = $request->has('is_active');
        $settings->default_status = $request->default_status;
        $settings->default_priority = $request->default_priority;
        $settings->default_governorate_id = $request->default_governorate_id;
        $settings->default_city_id = $request->default_city_id;
        
        // Tester la connexion
        $testResult = $settings->testConnection();
        
        if (!$testResult['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Impossible de se connecter à WooCommerce: ' . $testResult['message']);
        }
        
        $settings->save();
        
        return redirect()->route('admin.woocommerce.index')
            ->with('success', 'Paramètres WooCommerce sauvegardés avec succès. ' . $testResult['message']);
    }

    /**
     * Synchronise manuellement les commandes depuis WooCommerce
     */
    public function sync()
    {
        $admin = Auth::guard('admin')->user();
        $settings = $admin->woocommerceSettings;
        
        if (!$settings || !$settings->is_active) {
            return redirect()->route('admin.woocommerce.index')
                ->with('error', 'L\'intégration WooCommerce n\'est pas activée.');
        }
        
        // Mettre à jour le statut de synchronisation
        $settings->sync_status = 'syncing';
        $settings->save();
        
        try {
            $result = $this->syncOrders($admin, $settings);
            
            $settings->sync_status = 'idle';
            $settings->last_sync_at = now();
            $settings->sync_error = null;
            $settings->save();
            
            return redirect()->route('admin.woocommerce.index')
                ->with('success', 'Synchronisation terminée. ' . $result['imported'] . ' nouvelles commandes importées.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation WooCommerce: ' . $e->getMessage());
            
            $settings->sync_status = 'error';
            $settings->sync_error = $e->getMessage();
            $settings->save();
            
            return redirect()->route('admin.woocommerce.index')
                ->with('error', 'Erreur lors de la synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour synchroniser les commandes (utilisable via CRON)
     */
    public function syncOrders($admin = null, $settings = null)
    {
        // Si appelé sans paramètres (via CRON), trouver tous les admins avec WooCommerce actif
        if (!$admin) {
            $settings = WooCommerceSetting::where('is_active', true)->get();
            
            $results = [
                'total_imported' => 0,
                'errors' => []
            ];
            
            foreach ($settings as $setting) {
                try {
                    $admin = $setting->admin;
                    $result = $this->syncOrders($admin, $setting);
                    $results['total_imported'] += $result['imported'];
                } catch (\Exception $e) {
                    $results['errors'][] = 'Admin #' . $admin->id . ': ' . $e->getMessage();
                    
                    $setting->sync_status = 'error';
                    $setting->sync_error = $e->getMessage();
                    $setting->save();
                }
            }
            
            return $results;
        }
        
        // Si on a des paramètres spécifiques pour un admin
        $client = $settings->getClient();
        
        if (!$client) {
            throw new \Exception('Paramètres de connexion WooCommerce non valides');
        }
        
        // Définir la date de la dernière synchronisation (ou hier par défaut)
        $lastSyncDate = $settings->last_sync_at ? $settings->last_sync_at : now()->subDay();
        
        // Récupérer les commandes depuis la dernière synchronisation
        $wooOrders = $client->get('orders', [
            'after' => $lastSyncDate->format('Y-m-d\TH:i:s'),
            'per_page' => 50,
            'status' => ['processing', 'completed'], // Personnalisez selon vos besoins
        ]);
        
        // Initialiser les compteurs
        $imported = 0;
        $skipped = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($wooOrders as $wooOrder) {
                // Vérifier si la commande existe déjà
                $existingOrder = Order::where('admin_id', $admin->id)
                    ->where('external_id', $wooOrder->id)
                    ->where('external_source', 'woocommerce')
                    ->first();
                
                if ($existingOrder) {
                    $skipped++;
                    continue;
                }
                
                // Créer la nouvelle commande
                $order = new Order();
                $order->admin_id = $admin->id;
                $order->external_id = $wooOrder->id;
                $order->external_source = 'woocommerce';
                
                // Informations client
                $order->customer_name = $wooOrder->billing->first_name . ' ' . $wooOrder->billing->last_name;
                $order->customer_phone = $wooOrder->billing->phone ?? null;
                $order->customer_email = $wooOrder->billing->email ?? null;
                
                // Adresse
                $addressParts = [
                    $wooOrder->billing->address_1,
                    $wooOrder->billing->address_2,
                    $wooOrder->billing->postcode,
                    $wooOrder->billing->city,
                    $wooOrder->billing->state,
                    $wooOrder->billing->country
                ];
                $order->customer_address = implode(', ', array_filter($addressParts));
                
                // Rechercher le gouvernorat et la ville
                $governorate = null;
                $city = null;
                
                if (!empty($wooOrder->billing->state)) {
                    $governorate = Region::where('name', 'like', '%' . $wooOrder->billing->state . '%')->first();
                }
                
                if (!empty($wooOrder->billing->city)) {
                    $cityQuery = City::where('name', 'like', '%' . $wooOrder->billing->city . '%');
                    if ($governorate) {
                        $cityQuery->where('region_id', $governorate->id);
                    }
                    $city = $cityQuery->first();
                }
                
                $order->customer_governorate = $governorate ? $governorate->id : $settings->default_governorate_id;
                $order->customer_city = $city ? $city->id : $settings->default_city_id;
                
                // Paramètres par défaut
                $order->status = $settings->default_status;
                $order->priority = $settings->default_priority;
                
                // Frais d'expédition
                $shippingTotal = 0;
                foreach ($wooOrder->shipping_lines as $shipping) {
                    $shippingTotal += $shipping->total;
                }
                $order->shipping_cost = $shippingTotal;
                
                // Notes
                $order->notes = "Commande WooCommerce #{$wooOrder->id}\nStatut: {$wooOrder->status}\nMéthode de paiement: {$wooOrder->payment_method_title}";
                
                // Enregistrer la commande
                $order->save();
                
                // Traiter les produits
                $orderTotal = 0;
                
                foreach ($wooOrder->line_items as $item) {
                    // Rechercher ou créer le produit
                    $product = Product::where('admin_id', $admin->id)
                        ->where('name', $item->name)
                        ->first();
                    
                    if (!$product) {
                        $product = new Product([
                            'admin_id' => $admin->id,
                            'name' => $item->name,
                            'price' => $item->price,
                            'stock' => 1000000, // Stock par défaut
                            'is_active' => true,
                            'needs_review' => true,
                        ]);
                        $product->save();
                    }
                    
                    // Ajouter le produit à la commande
                    $orderItem = $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total_price' => $item->total,
                    ]);
                    
                    $orderTotal += $orderItem->total_price;
                    
                    // Décrémenter le stock si nécessaire
                    if ($settings->default_status === 'confirmée') {
                        $product->decrementStock($item->quantity);
                    }
                }
                
                // Mettre à jour le total de la commande
                $order->total_price = $orderTotal;
                $order->save();
                
                // Enregistrer l'historique
                $order->recordHistory('création', 'Importé depuis WooCommerce');
                
                $imported++;
            }
            
            DB::commit();
            
            // Mettre à jour la date de dernière synchronisation
            $settings->last_sync_at = now();
            $settings->save();
            
            return [
                'imported' => $imported,
                'skipped' => $skipped
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}