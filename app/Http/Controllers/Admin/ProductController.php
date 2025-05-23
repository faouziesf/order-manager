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
        
        $products = $query->orderBy('created_at', 'desc')->paginate(10);
        
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


}