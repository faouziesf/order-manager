<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = $admin->products();
        
        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1');
        }
        
        // Filtre par stock
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

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $admin = Auth::guard('admin')->user();
        
        $product = new Product();
        $product->admin_id = $admin->id;
        $product->name = $request->name;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->description = $request->description;
        $product->is_active = $request->has('is_active');
        
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }
        
        $product->save();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $product->name = $request->name;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->description = $request->description;
        $product->is_active = $request->has('is_active');
        
        // Important: Marquer le produit comme examiné lorsqu'il est mis à jour
        $product->needs_review = false;
        
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }
        
        $product->save();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        // Supprimer l'image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }

    /**
     * Afficher et permettre l'examen des nouveaux produits créés automatiquement
     */
    public function reviewNewProducts()
    {
        $admin = Auth::guard('admin')->user();
        $products = $admin->products()->where('needs_review', true)->paginate(10);
        
        return view('admin.products.review', compact('products'));
    }

    /**
     * Marquer un produit comme examiné
     */
    public function markAsReviewed(Product $product)
    {
        $this->authorize('update', $product);
        
        $product->markAsReviewed();
        
        return redirect()->back()->with('success', 'Le produit a été marqué comme examiné.');
    }

    /**
     * Marquer tous les produits comme examinés
     */
    public function markAllAsReviewed()
    {
        $admin = Auth::guard('admin')->user();
        $products = $admin->products()->where('needs_review', true)->get();
        
        foreach ($products as $product) {
            if (Gate::allows('update', $product)) {
                $product->markAsReviewed();
            }
        }
        
        return redirect()->route('admin.products.index')->with('success', 'Tous les produits ont été marqués comme examinés.');
    }

    /**
     * Actions groupées - Activer plusieurs produits
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string'
        ]);
        
        $productIds = explode(',', $request->product_ids);
        $admin = Auth::guard('admin')->user();
        
        $updatedCount = 0;
        foreach ($productIds as $productId) {
            $product = $admin->products()->find($productId);
            if ($product && Gate::allows('update', $product)) {
                $product->update(['is_active' => true]);
                $updatedCount++;
            }
        }
        
        return redirect()->route('admin.products.index')
            ->with('success', "{$updatedCount} produit(s) activé(s) avec succès.");
    }

    /**
     * Actions groupées - Désactiver plusieurs produits
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string'
        ]);
        
        $productIds = explode(',', $request->product_ids);
        $admin = Auth::guard('admin')->user();
        
        $updatedCount = 0;
        foreach ($productIds as $productId) {
            $product = $admin->products()->find($productId);
            if ($product && Gate::allows('update', $product)) {
                $product->update(['is_active' => false]);
                $updatedCount++;
            }
        }
        
        return redirect()->route('admin.products.index')
            ->with('success', "{$updatedCount} produit(s) désactivé(s) avec succès.");
    }

    /**
     * Actions groupées - Supprimer plusieurs produits
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string'
        ]);
        
        $productIds = explode(',', $request->product_ids);
        $admin = Auth::guard('admin')->user();
        
        $deletedCount = 0;
        foreach ($productIds as $productId) {
            $product = $admin->products()->find($productId);
            if ($product && Gate::allows('delete', $product)) {
                // Supprimer l'image
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->delete();
                $deletedCount++;
            }
        }
        
        return redirect()->route('admin.products.index')
            ->with('success', "{$deletedCount} produit(s) supprimé(s) avec succès.");
    }
}