<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    /**
     * Afficher la liste des produits avec filtres avancés et pagination configurable
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = $admin->products();
        
        // Filtres de base
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1');
        }
        
        if ($request->filled('stock')) {
            if ($request->stock === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            }
        }
        
        // Filtres avancés
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        
        if ($request->filled('stock_min')) {
            $query->where('stock', '>=', $request->stock_min);
        }
        
        if ($request->filled('stock_max')) {
            $query->where('stock', '<=', $request->stock_max);
        }
        
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }
        
        if ($request->filled('needs_review')) {
            $query->where('needs_review', $request->needs_review == '1');
        }
        
        // Tri
        $sort = $request->get('sort', 'created_at_desc');
        switch ($sort) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'reference_asc':
                $query->orderBy('reference', 'asc');
                break;
            case 'reference_desc':
                $query->orderBy('reference', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        // Pagination configurable
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 30, 50, 100];
        
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }
        
        $products = $query->paginate($perPage);
        
        // Si c'est une requête AJAX pour la recherche en temps réel
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.product-list', compact('products'))->render(),
                'pagination' => $products->appends(request()->query())->links()->render(),
                'total' => $products->total()
            ]);
        }
        
        return view('admin.products.index', compact('products', 'perPage'));
    }

    /**
     * Vue Kanban pour la gestion des stocks
     */
    public function kanban(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // Produits en rupture de stock
        $outOfStock = $admin->products()
            ->where('stock', '<=', 0)
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Produits avec stock faible (1-10)
        $lowStock = $admin->products()
            ->where('stock', '>', 0)
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->get();
        
        // Produits avec stock normal (11-50)
        $normalStock = $admin->products()
            ->where('stock', '>', 10)
            ->where('stock', '<=', 50)
            ->orderBy('stock', 'desc')
            ->get();
        
        // Produits avec stock élevé (>50)
        $highStock = $admin->products()
            ->where('stock', '>', 50)
            ->orderBy('stock', 'desc')
            ->get();
        
        return view('admin.products.kanban', compact('outOfStock', 'lowStock', 'normalStock', 'highStock'));
    }

    /**
     * API pour obtenir les statistiques en temps réel
     */
    public function getRealtimeStats()
    {
        $admin = Auth::guard('admin')->user();
        
        $stats = [
            'total' => $admin->products()->count(),
            'active' => $admin->products()->where('is_active', true)->count(),
            'inactive' => $admin->products()->where('is_active', false)->count(),
            'low_stock' => $admin->products()->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'out_of_stock' => $admin->products()->where('stock', '<=', 0)->count(),
            'needs_review' => $admin->products()->where('needs_review', true)->count(),
            'high_stock' => $admin->products()->where('stock', '>', 50)->count(),
        ];
        
        // Données pour les graphiques
        $stockDistribution = [
            'out_of_stock' => $stats['out_of_stock'],
            'low_stock' => $stats['low_stock'],
            'normal_stock' => $admin->products()->where('stock', '>', 10)->where('stock', '<=', 50)->count(),
            'high_stock' => $stats['high_stock']
        ];
        
        // Évolution des produits sur les 7 derniers jours
        $productsTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $productsTrend[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'count' => $admin->products()->whereDate('created_at', $date)->count()
            ];
        }
        
        return response()->json([
            'stats' => $stats,
            'stockDistribution' => $stockDistribution,
            'productsTrend' => $productsTrend
        ]);
    }

    /**
     * Recherche en temps réel (AJAX)
     */
    public function liveSearch(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête AJAX requise'], 400);
        }
        
        $admin = Auth::guard('admin')->user();
        $query = $admin->products();
        
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('reference', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $products = $query->limit(10)->get(['id', 'name', 'reference', 'price', 'stock', 'image', 'is_active']);
        
        return response()->json($products);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Générer la prochaine référence disponible
     */
    private function generateNextReference()
    {
        $admin = Auth::guard('admin')->user();
        $lastProduct = $admin->products()->orderBy('reference', 'desc')->first();
        
        if ($lastProduct && $lastProduct->reference) {
            return $lastProduct->reference + 1;
        }
        
        return 1001; // Commencer à 1001 pour avoir des références à 4 chiffres
    }

    /**
     * Créer un nouveau produit avec redimensionnement d'image
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'nullable|integer|unique:products,reference,NULL,id,admin_id,' . auth('admin')->id(),
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'reference.integer' => 'La référence doit être un nombre entier.',
            'reference.unique' => 'Cette référence existe déjà pour vos produits.',
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'stock.required' => 'La quantité en stock est obligatoire.',
            'stock.integer' => 'La quantité doit être un nombre entier.',
            'stock.min' => 'La quantité ne peut pas être négative.',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, gif.',
            'image.max' => 'L\'image ne peut pas dépasser 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            
            $product = new Product();
            $product->admin_id = $admin->id;
            $product->reference = $validated['reference'] ?? $this->generateNextReference();
            $product->name = $validated['name'];
            $product->price = $validated['price'];
            $product->stock = $validated['stock'];
            $product->description = $validated['description'];
            $product->is_active = $request->has('is_active');
            $product->needs_review = false;
            
            // Gestion de l'image avec redimensionnement
            if ($request->hasFile('image')) {
                $imagePath = $this->processAndStoreImage($request->file('image'));
                $product->image = $imagePath;
            }
            
            $product->save();

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit "' . $product->name . '" (Réf: ' . $product->reference . ') créé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du produit: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du produit.');
        }
    }

    /**
     * Traiter et redimensionner l'image
     */
    private function processAndStoreImage($file)
    {
        try {
            // Créer un nom unique pour le fichier
            $filename = uniqid() . '_' . time() . '.jpg';
            
            // Redimensionner l'image
            $image = Image::make($file);
            
            // Redimensionner en gardant les proportions (max 400x400)
            $image->resize(400, 400, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Empêche d'agrandir les petites images
            });
            
            // Optimiser la qualité (80% pour un bon compromis taille/qualité)
            $image->encode('jpg', 80);
            
            // Créer le chemin de stockage
            $path = 'products/' . $filename;
            
            // Sauvegarder l'image redimensionnée
            Storage::disk('public')->put($path, (string) $image);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement de l\'image: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Afficher un produit
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        
        $validated = $request->validate([
            'reference' => 'nullable|integer|unique:products,reference,' . $product->id . ',id,admin_id,' . auth('admin')->id(),
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'reference.integer' => 'La référence doit être un nombre entier.',
            'reference.unique' => 'Cette référence existe déjà pour vos produits.',
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'stock.required' => 'La quantité en stock est obligatoire.',
            'stock.integer' => 'La quantité doit être un nombre entier.',
            'stock.min' => 'La quantité ne peut pas être négative.',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, gif.',
            'image.max' => 'L\'image ne peut pas dépasser 5MB.',
        ]);

        try {
            DB::beginTransaction();
            
            $product->reference = $validated['reference'] ?? $product->reference;
            $product->name = $validated['name'];
            $product->price = $validated['price'];
            $product->stock = $validated['stock'];
            $product->description = $validated['description'];
            $product->is_active = $request->has('is_active');
            $product->needs_review = false;
            
            // Gestion de l'image
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                
                // Traiter et sauvegarder la nouvelle image
                $imagePath = $this->processAndStoreImage($request->file('image'));
                $product->image = $imagePath;
            }
            
            $product->save();

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit "' . $product->name . '" (Réf: ' . $product->reference . ') mis à jour avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du produit: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du produit.');
        }
    }

    /**
     * Supprimer un produit - AVEC PROTECTION
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        try {
            // Vérifier si le produit est utilisé dans des commandes
            if ($product->isUsedInOrders()) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer le produit "' . $product->name . '" car il est utilisé dans des commandes existantes.');
            }
            
            DB::beginTransaction();
            
            $productName = $product->name;
            $productRef = $product->reference;
            
            // Supprimer l'image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            
            $product->delete();

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit "' . $productName . '" (Réf: ' . $productRef . ') supprimé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du produit: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du produit.');
        }
    }

    /**
     * Afficher la page d'examen des nouveaux produits avec pagination configurable
     */
    public function reviewNewProducts(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 30, 50, 100];
        
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }
        
        $products = $admin->products()
            ->where('needs_review', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return view('admin.products.review', compact('products', 'perPage'));
    }

    /**
     * Marquer un produit comme examiné
     */
    public function markAsReviewed(Product $product)
    {
        $this->authorize('update', $product);
        
        try {
            $product->markAsReviewed();
            
            return redirect()->back()
                ->with('success', 'Le produit "' . $product->name . '" a été marqué comme examiné.');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage comme examiné: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Marquer tous les produits comme examinés
     */
    public function markAllAsReviewed()
    {
        try {
            $admin = Auth::guard('admin')->user();
            $products = $admin->products()->where('needs_review', true)->get();
            
            $count = 0;
            foreach ($products as $product) {
                if (Gate::allows('update', $product)) {
                    $product->markAsReviewed();
                    $count++;
                }
            }
            
            return redirect()->route('admin.products.index')
                ->with('success', $count . ' produit(s) marqué(s) comme examiné(s).');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage en lot: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Activer plusieurs produits (action groupée)
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string'
        ]);
        
        try {
            $productIds = array_filter(explode(',', $request->product_ids));
            
            if (empty($productIds)) {
                return redirect()->back()->with('error', 'Aucun produit sélectionné.');
            }
            
            $admin = Auth::guard('admin')->user();
            $updatedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($productIds as $productId) {
                $product = $admin->products()->find($productId);
                if ($product && Gate::allows('update', $product)) {
                    $product->update(['is_active' => true]);
                    $updatedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', $updatedCount . ' produit(s) activé(s) avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'activation en lot: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'activation des produits.');
        }
    }

    /**
     * Désactiver plusieurs produits (action groupée)
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string'
        ]);
        
        try {
            $productIds = array_filter(explode(',', $request->product_ids));
            
            if (empty($productIds)) {
                return redirect()->back()->with('error', 'Aucun produit sélectionné.');
            }
            
            $admin = Auth::guard('admin')->user();
            $updatedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($productIds as $productId) {
                $product = $admin->products()->find($productId);
                if ($product && Gate::allows('update', $product)) {
                    $product->update(['is_active' => false]);
                    $updatedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', $updatedCount . ' produit(s) désactivé(s) avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la désactivation en lot: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la désactivation des produits.');
        }
    }

    /**
     * Supprimer plusieurs produits (action groupée) - AVEC PROTECTION RENFORCÉE
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string'
        ]);
        
        try {
            $productIds = array_filter(explode(',', $request->product_ids));
            
            if (empty($productIds)) {
                return redirect()->back()->with('error', 'Aucun produit sélectionné.');
            }
            
            $admin = Auth::guard('admin')->user();
            $deletedCount = 0;
            $protectedProducts = [];
            $unauthorizedProducts = [];
            
            DB::beginTransaction();
            
            foreach ($productIds as $productId) {
                $product = $admin->products()->find($productId);
                
                // Vérifier si le produit existe et appartient à l'admin
                if (!$product) {
                    continue;
                }
                
                // Vérifier les autorisations
                if (!Gate::allows('delete', $product)) {
                    $unauthorizedProducts[] = $product->name . ' (Réf: ' . ($product->reference ?? 'N/A') . ')';
                    continue;
                }
                
                // Vérifier si le produit est utilisé dans des commandes - PROTECTION PRINCIPALE
                if ($product->isUsedInOrders()) {
                    $protectedProducts[] = $product->name . ' (Réf: ' . ($product->reference ?? 'N/A') . ')';
                    continue;
                }
                
                // Le produit peut être supprimé
                // Supprimer l'image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                
                $product->delete();
                $deletedCount++;
            }
            
            DB::commit();
            
            // Construire le message de réponse
            $message = '';
            
            if ($deletedCount > 0) {
                $message = $deletedCount . ' produit(s) supprimé(s) avec succès.';
            }
            
            if (!empty($protectedProducts)) {
                $protectedMessage = ' ' . count($protectedProducts) . ' produit(s) n\'ont pas pu être supprimé(s) car ils sont utilisés dans des commandes : ' . implode(', ', array_slice($protectedProducts, 0, 3));
                if (count($protectedProducts) > 3) {
                    $protectedMessage .= ' et ' . (count($protectedProducts) - 3) . ' autre(s)';
                }
                $message .= $protectedMessage . '.';
            }
            
            if (!empty($unauthorizedProducts)) {
                $message .= ' ' . count($unauthorizedProducts) . ' produit(s) non autorisé(s) ignoré(s).';
            }
            
            if ($deletedCount === 0 && (empty($protectedProducts) && empty($unauthorizedProducts))) {
                return redirect()->back()->with('error', 'Aucun produit n\'a pu être supprimé.');
            }
            
            $alertType = $deletedCount > 0 ? 'success' : 'warning';
            
            return redirect()->back()->with($alertType, trim($message));
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression en lot: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression des produits.');
        }
    }

    /**
     * API pour la recherche de produits (utilisé par d'autres modules)
     */
    public function searchProducts(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = $admin->products()->where('is_active', true);
        
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('reference', 'like', '%' . $request->q . '%');
            });
        }
        
        $products = $query->limit(10)->get(['id', 'name', 'reference', 'price', 'stock']);
        
        return response()->json($products);
    }

    /**
     * Obtenir les statistiques des produits
     */
    public function getStats()
    {
        $admin = Auth::guard('admin')->user();
        
        $stats = [
            'total' => $admin->products()->count(),
            'active' => $admin->products()->where('is_active', true)->count(),
            'inactive' => $admin->products()->where('is_active', false)->count(),
            'low_stock' => $admin->products()->where('stock', '<=', 10)->count(),
            'out_of_stock' => $admin->products()->where('stock', '<=', 0)->count(),
            'needs_review' => $admin->products()->where('needs_review', true)->count(),
        ];
        
        return response()->json($stats);
    }
}