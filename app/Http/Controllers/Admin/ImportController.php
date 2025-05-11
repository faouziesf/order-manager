<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    /**
     * Affiche la vue d'importation
     */
    public function index()
    {
        $regions = Region::with('cities')->orderBy('name')->get();
        return view('admin.import.index', compact('regions'));
    }


    /**
     * Importe les commandes depuis un fichier XML
     */
    public function importXml(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:10240',
            'default_governorate' => 'nullable|exists:regions,id',
            'default_city' => 'nullable|exists:cities,id',
            'default_status' => 'required|in:nouvelle,confirmée',
            'default_priority' => 'required|in:normale,urgente,vip',
        ]);

        $admin = Auth::guard('admin')->user();
        $file = $request->file('xml_file');
        
        // Initialiser les compteurs
        $imported = 0;
        $failed = 0;
        $errors = [];
        
        try {
            // Charger le fichier XML
            $xml = simplexml_load_file($file->getPathname());
            
            DB::beginTransaction();
            
            // Parcourir les commandes
            foreach ($xml->order as $xmlOrder) {
                try {
                    // Extraire les données de base
                    $orderData = [
                        'admin_id' => $admin->id,
                        'customer_phone' => (string)$xmlOrder->phone,
                        'customer_name' => (string)$xmlOrder->name,
                        'customer_phone_2' => isset($xmlOrder->phone2) ? (string)$xmlOrder->phone2 : null,
                        'customer_address' => (string)$xmlOrder->address,
                        'shipping_cost' => isset($xmlOrder->shipping) ? (float)$xmlOrder->shipping : 0,
                        'notes' => isset($xmlOrder->notes) ? (string)$xmlOrder->notes : null,
                        'status' => $request->default_status,
                        'priority' => $request->default_priority,
                    ];
                    
                    // Validation
                    $validator = Validator::make($orderData, [
                        'customer_phone' => 'required|string|max:20',
                    ]);
                    
                    if ($validator->fails()) {
                        $failed++;
                        $errors[] = "Commande " . ($imported + $failed) . ": " . implode(', ', $validator->errors()->all());
                        continue;
                    }
                    
                    // Gérer le gouvernorat et la ville
                    $governorateId = null;
                    $cityId = null;
                    
                    if (isset($xmlOrder->region)) {
                        $region = Region::where('name', 'like', '%' . (string)$xmlOrder->region . '%')->first();
                        if ($region) {
                            $governorateId = $region->id;
                        }
                    }
                    
                    if (isset($xmlOrder->city)) {
                        $cityQuery = City::where('name', 'like', '%' . (string)$xmlOrder->city . '%');
                        if ($governorateId) {
                            $cityQuery->where('region_id', $governorateId);
                        }
                        $city = $cityQuery->first();
                        if ($city) {
                            $cityId = $city->id;
                        }
                    }
                    
                    $orderData['customer_governorate'] = $governorateId ?? $request->default_governorate;
                    $orderData['customer_city'] = $cityId ?? $request->default_city;
                    
                    // Créer la commande
                    $order = new Order($orderData);
                    $order->save();
                    
                    // Traiter les produits
                    $totalPrice = 0;
                    
                    if (isset($xmlOrder->products) && isset($xmlOrder->products->product)) {
                        foreach ($xmlOrder->products->product as $xmlProduct) {
                            $productName = (string)$xmlProduct->name;
                            $quantity = (int)($xmlProduct->quantity ?? 1);
                            $price = isset($xmlProduct->price) ? (float)$xmlProduct->price : null;
                            
                            // Rechercher le produit
                            $product = Product::where('admin_id', $admin->id)
                                ->where('name', $productName)
                                ->first();
                            
                            if (!$product && $price) {
                                // Créer un nouveau produit
                                $product = new Product([
                                    'admin_id' => $admin->id,
                                    'name' => $productName,
                                    'price' => $price,
                                    'stock' => 1000000,
                                    'is_active' => true,
                                    'needs_review' => true,
                                ]);
                                $product->save();

                                $new_products_created = true;
                            }
                            
                            if ($product) {
                                $itemPrice = $price ?? $product->price;
                                
                                // Ajouter le produit à la commande
                                $orderItem = $order->items()->create([
                                    'product_id' => $product->id,
                                    'quantity' => $quantity,
                                    'unit_price' => $itemPrice,
                                    'total_price' => $itemPrice * $quantity,
                                ]);
                                
                                $totalPrice += $orderItem->total_price;
                                
                                // Décrémenter le stock
                                if ($request->default_status === 'confirmée') {
                                    $product->decrementStock($quantity);
                                }
                            }
                        }
                    }
                    
                    // Mettre à jour le total
                    $order->total_price = $totalPrice;
                    $order->save();
                    
                    // Enregistrer l'historique
                    $order->recordHistory('création', 'Importé depuis XML');
                    
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Commande " . ($imported + $failed) . ": " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.import.index')
                ->with('success', "Importation terminée. $imported commandes importées, $failed échouées. " . 
                    (count($errors) > 0 ? "Erreurs: " . implode('; ', array_slice($errors, 0, 5)) . 
                    (count($errors) > 5 ? ' ...' : '') : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'importation XML: ' . $e->getMessage());
            
            return redirect()->route('admin.import.index')
                ->with('error', 'Une erreur est survenue lors de l\'importation: ' . $e->getMessage());
        }

        $message = "Importation terminée. $imported commandes importées, $failed échouées.";

        // Si de nouveaux produits ont été créés, ajouter un message
        if (isset($new_products_created) && $new_products_created) {
            $message .= " <strong>De nouveaux produits ont été créés et nécessitent votre attention.</strong>";
        }

        if (count($errors) > 0) {
            $message .= " Erreurs: " . implode('; ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? ' ...' : '');
        }

        return redirect()->route('admin.import.index')
            ->with('success', $message);

    }


    /**
     * Importe les commandes depuis un fichier CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'delimiter' => 'required|string|size:1',
            'default_governorate' => 'nullable|exists:regions,id',
            'default_city' => 'nullable|exists:cities,id',
            'default_status' => 'required|in:nouvelle,confirmée',
            'default_priority' => 'required|in:normale,urgente,vip',
        ]);

        $admin = Auth::guard('admin')->user();
        $file = $request->file('csv_file');
        $delimiter = $request->delimiter;
        
        // Ouvrir le fichier CSV
        $handle = fopen($file->getPathname(), 'r');
        
        // Lire l'en-tête
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            return redirect()->back()->with('error', 'Impossible de lire l\'en-tête du fichier CSV.');
        }
        
        // Convertir l'en-tête en minuscules et supprimer les espaces
        $header = array_map(function($item) {
            return strtolower(trim($item));
        }, $header);
        
        // Vérifier les colonnes obligatoires
        if (!in_array('telephone', $header) && !in_array('phone', $header) && !in_array('tel', $header)) {
            return redirect()->back()->with('error', 'Le fichier CSV doit contenir une colonne de téléphone (téléphone, phone ou tel).');
        }
        
        // Initialiser les compteurs
        $imported = 0;
        $failed = 0;
        $errors = [];
        
        // Traiter chaque ligne
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Si la ligne est vide, passer à la suivante
                if (count($row) === 0 || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }
                
                // Associer les valeurs à l'en-tête
                $data = [];
                foreach ($header as $index => $column) {
                    if (isset($row[$index])) {
                        $data[$column] = trim($row[$index]);
                    } else {
                        $data[$column] = null;
                    }
                }
                
                // Trouver la colonne de téléphone
                $phone_column = null;
                foreach (['telephone', 'phone', 'tel'] as $possible_column) {
                    if (in_array($possible_column, $header)) {
                        $phone_column = $possible_column;
                        break;
                    }
                }
                
                // Extraire les données de base de la commande
                $order_data = [
                    'admin_id' => $admin->id,
                    'customer_phone' => $data[$phone_column] ?? null,
                    'customer_name' => $data['nom'] ?? $data['name'] ?? $data['client'] ?? null,
                    'customer_phone_2' => $data['telephone2'] ?? $data['phone2'] ?? $data['tel2'] ?? null,
                    'customer_address' => $data['adresse'] ?? $data['address'] ?? null,
                    'shipping_cost' => $data['frais_livraison'] ?? $data['shipping'] ?? $data['livraison'] ?? 0,
                    'notes' => $data['notes'] ?? $data['remarques'] ?? $data['commentaire'] ?? null,
                    'status' => $request->default_status,
                    'priority' => $request->default_priority,
                ];
                
                // Validation de base
                $validator = Validator::make($order_data, [
                    'customer_phone' => 'required|string|max:20',
                ]);
                
                if ($validator->fails()) {
                    $failed++;
                    $errors[] = "Ligne " . ($imported + $failed) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }
                
                // Rechercher le gouvernorat et la ville
                $governorate_id = null;
                $city_id = null;
                
                // Chercher dans les données du CSV
                if (isset($data['gouvernorat']) || isset($data['region'])) {
                    $governorate_name = $data['gouvernorat'] ?? $data['region'] ?? null;
                    $region = Region::where('name', 'like', '%' . $governorate_name . '%')->first();
                    if ($region) {
                        $governorate_id = $region->id;
                    }
                }
                
                if (isset($data['ville']) || isset($data['city'])) {
                    $city_name = $data['ville'] ?? $data['city'] ?? null;
                    $city_query = City::where('name', 'like', '%' . $city_name . '%');
                    if ($governorate_id) {
                        $city_query->where('region_id', $governorate_id);
                    }
                    $city = $city_query->first();
                    if ($city) {
                        $city_id = $city->id;
                    }
                }
                
                // Utiliser les valeurs par défaut si non trouvées
                $order_data['customer_governorate'] = $governorate_id ?? $request->default_governorate;
                $order_data['customer_city'] = $city_id ?? $request->default_city;
                
                // Créer la commande
                $order = new Order($order_data);
                $order->save();
                
                // Traiter les produits
                $total_price = 0;
                $products_found = false;
                
                // Recherche des colonnes de produits (format: produit_1, produit_2, etc.)
                foreach ($header as $column) {
                    if (preg_match('/^produit[_\s]?(\d+)$/', $column, $matches) || 
                        preg_match('/^product[_\s]?(\d+)$/', $column, $matches)) {
                        
                        $product_name = $data[$column] ?? null;
                        if (empty($product_name)) continue;
                        
                        // Rechercher la quantité correspondante
                        $qty_column = 'quantite_' . $matches[1];
                        $qty_column_alt = 'quantity_' . $matches[1];
                        $qty_column_alt2 = 'qty_' . $matches[1];
                        
                        $quantity = $data[$qty_column] ?? $data[$qty_column_alt] ?? $data[$qty_column_alt2] ?? 1;
                        
                        // Rechercher le prix unitaire
                        $price_column = 'prix_' . $matches[1];
                        $price_column_alt = 'price_' . $matches[1];
                        
                        $price = $data[$price_column] ?? $data[$price_column_alt] ?? null;
                        
                        // Rechercher le produit dans la base ou en créer un nouveau
                        $product = Product::where('admin_id', $admin->id)
                            ->where('name', $product_name)
                            ->first();
                            
                        if (!$product && $price) {
                            // Créer un nouveau produit si le prix est spécifié
                            $product = new Product([
                                'admin_id' => $admin->id,
                                'name' => $product_name,
                                'price' => $price,
                                'stock' => 1000000, // Stock énorme par défaut
                                'is_active' => true,
                                'needs_review' => true,
                            ]);
                            $product->save();

                            $new_products_created = true;
                        }
                        
                        if ($product) {
                            $products_found = true;
                            $item_price = $price ?? $product->price;
                            
                            // Ajouter le produit à la commande
                            $order_item = $order->items()->create([
                                'product_id' => $product->id,
                                'quantity' => $quantity,
                                'unit_price' => $item_price,
                                'total_price' => $item_price * $quantity,
                            ]);
                            
                            $total_price += $order_item->total_price;
                            
                            // Décrémenter le stock si la commande est confirmée
                            if ($request->default_status === 'confirmée') {
                                $product->decrementStock($quantity);
                            }
                        }
                    }
                }
                
                // Mettre à jour le total de la commande
                $order->total_price = $total_price;
                $order->save();
                
                // Enregistrer l'historique
                $order->recordHistory('création', 'Importé depuis CSV');
                
                // Incrémenter le compteur de succès
                $imported++;
            }
            
            DB::commit();
            
            fclose($handle);
            
            return redirect()->route('admin.import.index')
                ->with('success', "Importation terminée. $imported commandes importées, $failed échouées. " . 
                    (count($errors) > 0 ? "Erreurs: " . implode('; ', array_slice($errors, 0, 5)) . 
                    (count($errors) > 5 ? ' ...' : '') : ''));
                    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'importation CSV: ' . $e->getMessage());
            
            return redirect()->route('admin.import.index')
                ->with('error', 'Une erreur est survenue lors de l\'importation: ' . $e->getMessage());
        }

        $message = "Importation terminée. $imported commandes importées, $failed échouées.";

        // Si de nouveaux produits ont été créés, ajouter un message
        if (isset($new_products_created) && $new_products_created) {
            $message .= " <strong>De nouveaux produits ont été créés et nécessitent votre attention.</strong>";
        }

        if (count($errors) > 0) {
            $message .= " Erreurs: " . implode('; ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? ' ...' : '');
        }

        return redirect()->route('admin.import.index')
            ->with('success', $message);

    }
}