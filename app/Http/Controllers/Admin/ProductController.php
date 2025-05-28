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

class ProductController extends Controller
{
    /**
     * Afficher la liste des produits avec filtres avancés
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = $admin->products();
        
        // Filtres de base
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1');
        }
        
        if ($request->filled('stock')) {
            if ($request->stock === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock === 'out_of_stock') {
                $query->where('stock', '<=', 0);
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
        
        $products = $query->paginate(20);
        
        return view('admin.products.index', compact('products'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Créer un nouveau produit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
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
            'image.max' => 'L\'image ne peut pas dépasser 2MB.',
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            
            $product = new Product();
            $product->admin_id = $admin->id;
            $product->name = $validated['name'];
            $product->price = $validated['price'];
            $product->stock = $validated['stock'];
            $product->description = $validated['description'];
            $product->is_active = $request->has('is_active');
            $product->needs_review = false; // Produit créé manuellement, pas besoin d'examen
            
            // Gestion de l'image
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image = $imagePath;
            }
            
            $product->save();

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit "' . $product->name . '" créé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du produit: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du produit.');
        }
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
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
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
            'image.max' => 'L\'image ne peut pas dépasser 2MB.',
        ]);

        try {
            DB::beginTransaction();
            
            $product->name = $validated['name'];
            $product->price = $validated['price'];
            $product->stock = $validated['stock'];
            $product->description = $validated['description'];
            $product->is_active = $request->has('is_active');
            
            // Marquer le produit comme examiné lorsqu'il est mis à jour manuellement
            $product->needs_review = false;
            
            // Gestion de l'image
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                
                $imagePath = $request->file('image')->store('products', 'public');
                $product->image = $imagePath;
            }
            
            $product->save();

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit "' . $product->name . '" mis à jour avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du produit: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du produit.');
        }
    }

    /**
     * Supprimer un produit
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        try {
            DB::beginTransaction();
            
            $productName = $product->name;
            
            // Supprimer l'image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            
            $product->delete();

            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Produit "' . $productName . '" supprimé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du produit: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du produit.');
        }
    }

    /**
     * Afficher la page d'examen des nouveaux produits
     */
    public function reviewNewProducts()
    {
        $admin = Auth::guard('admin')->user();
        $products = $admin->products()
            ->where('needs_review', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.products.review', compact('products'));
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
            
            return redirect()->route('admin.products.index')
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
            
            return redirect()->route('admin.products.index')
                ->with('success', $updatedCount . ' produit(s) désactivé(s) avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la désactivation en lot: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la désactivation des produits.');
        }
    }

    /**
     * Supprimer plusieurs produits (action groupée)
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
            
            DB::beginTransaction();
            
            foreach ($productIds as $productId) {
                $product = $admin->products()->find($productId);
                if ($product && Gate::allows('delete', $product)) {
                    // Supprimer l'image
                    if ($product->image && Storage::disk('public')->exists($product->image)) {
                        Storage::disk('public')->delete($product->image);
                    }
                    $product->delete();
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.products.index')
                ->with('success', $deletedCount . ' produit(s) supprimé(s) avec succès.');
                
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
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        
        $products = $query->limit(10)->get(['id', 'name', 'price', 'stock']);
        
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