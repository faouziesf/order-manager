@extends('layouts.admin')

@section('title', 'Liste des produits')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .product-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .badge-stock {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .stock-low {
        background: linear-gradient(45deg, #dc3545, #e55a68);
    }
    
    .stock-medium {
        background: linear-gradient(45deg, #ffc107, #ffcd3a);
    }
    
    .stock-high {
        background: linear-gradient(45deg, #198754, #28a745);
    }
    
    .filters-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .filters-card .form-control,
    .filters-card .form-select {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 8px;
    }
    
    .stats-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 15px;
    }
    
    .btn-gradient {
        background: linear-gradient(45deg, #4e73df, #224abe);
        border: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-gradient:hover {
        background: linear-gradient(45deg, #224abe, #4e73df);
        transform: translateY(-1px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .table-actions {
        white-space: nowrap;
    }
    
    .price-display {
        font-weight: 600;
        color: #2c5282;
    }
    
    .bulk-actions {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1rem;
        display: none;
    }
    
    .bulk-actions.show {
        display: block;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .select-all-checkbox {
        transform: scale(1.2);
    }
    
    .row-checkbox {
        transform: scale(1.1);
    }
    
    .advanced-filters {
        background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        display: none;
    }
    
    .advanced-filters.show {
        display: block;
        animation: slideDown 0.3s ease;
    }
    
    .advanced-filters .form-control,
    .advanced-filters .form-select {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 8px;
        color: #333;
    }
    
    .selected-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .view-toggle .btn {
        background: transparent;
        border: 2px solid #4e73df;
        color: #4e73df;
        transition: all 0.3s ease;
    }
    
    .view-toggle .btn.active {
        background: #4e73df;
        color: white;
    }
    
    .view-toggle .btn:hover {
        background: #4e73df;
        color: white;
    }

    /* Styles pour la pagination moderne */
    .pagination-modern {
        --bs-pagination-padding-x: 0.75rem;
        --bs-pagination-padding-y: 0.5rem;
        --bs-pagination-font-size: 0.875rem;
        --bs-pagination-color: #4e73df;
        --bs-pagination-bg: #fff;
        --bs-pagination-border-width: 1px;
        --bs-pagination-border-color: #dee2e6;
        --bs-pagination-border-radius: 8px;
        --bs-pagination-hover-color: #224abe;
        --bs-pagination-hover-bg: #f8f9fc;
        --bs-pagination-hover-border-color: #dee2e6;
        --bs-pagination-focus-color: #224abe;
        --bs-pagination-focus-bg: #f8f9fc;
        --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        --bs-pagination-active-color: #fff;
        --bs-pagination-active-bg: #4e73df;
        --bs-pagination-active-border-color: #4e73df;
        --bs-pagination-disabled-color: #6c757d;
        --bs-pagination-disabled-bg: #fff;
        --bs-pagination-disabled-border-color: #dee2e6;
    }

    .pagination-modern .page-link {
        transition: all 0.3s ease;
        font-weight: 500;
        border-radius: 6px;
        margin: 0 2px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .pagination-modern .page-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    }

    .pagination-modern .page-item.active .page-link {
        background: linear-gradient(45deg, #4e73df, #224abe);
        border-color: #4e73df;
        box-shadow: 0 3px 6px rgba(78, 115, 223, 0.3);
    }

    .pagination-modern .page-item.disabled .page-link {
        opacity: 0.5;
        box-shadow: none;
    }

    .pagination-info {
        background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border-left: 4px solid #4e73df;
    }

    /* Animation pour les changements de page */
    .page-transition {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Style pour les sélecteurs */
    .form-select-sm {
        border-radius: 6px;
        border-color: #dee2e6;
        transition: all 0.3s ease;
    }

    .form-select-sm:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
    
    @media (max-width: 768px) {
        .product-image {
            width: 40px;
            height: 40px;
        }
        
        .stats-card .h4 {
            font-size: 1.2rem;
        }

        .pagination-modern {
            font-size: 0.75rem;
        }
        
        .pagination-modern .page-link {
            padding: 0.375rem 0.5rem;
            margin: 0 1px;
        }
        
        /* Masquer les numéros de page sur mobile, garder seulement prev/next */
        .pagination-modern .page-item:not(.page-item:has(.fa-chevron-left)):not(.page-item:has(.fa-chevron-right)):not(.page-item:has(.fa-angle-double-left)):not(.page-item:has(.fa-angle-double-right)):not(.active) {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .pagination-info {
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .col-md-6 {
            text-align: center;
        }
        
        /* Réorganiser sur très petits écrans */
        .pagination-modern .page-item:not(.page-item:has(.fa-chevron-left)):not(.page-item:has(.fa-chevron-right)):not(.active) {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-box-open me-2"></i>Gestion des Produits
        </h1>
        <p class="text-muted mb-0">Gérez votre catalogue de produits</p>
    </div>
    <div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-gradient text-white me-2">
            <i class="fas fa-plus me-2"></i>Nouveau Produit
        </a>
        @if(auth('admin')->user()->products()->where('needs_review', true)->count() > 0)
            <a href="{{ route('admin.products.review') }}" class="btn btn-warning">
                <i class="fas fa-eye me-2"></i>Examiner ({{ auth('admin')->user()->products()->where('needs_review', true)->count() }})
            </a>
        @endif
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Total Produits</div>
                        <div class="text-white h4">{{ $products->total() }}</div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Produits Actifs</div>
                        <div class="text-white h4">{{ auth('admin')->user()->products()->where('is_active', true)->count() }}</div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #ffc107, #fd7e14); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Stock Faible</div>
                        <div class="text-white h4">{{ auth('admin')->user()->products()->where('stock', '<=', 10)->count() }}</div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card" style="background: linear-gradient(135deg, #dc3545, #e74c3c); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Rupture Stock</div>
                        <div class="text-white h4">{{ auth('admin')->user()->products()->where('stock', '<=', 0)->count() }}</div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions groupées -->
<div class="bulk-actions" id="bulkActions">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <span class="selected-count me-3" id="selectedCount">0 produit(s) sélectionné(s)</span>
            <div class="btn-group">
                <button type="button" class="btn btn-light btn-sm" onclick="bulkAction('activate')">
                    <i class="fas fa-check me-1"></i>Activer
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="bulkAction('deactivate')">
                    <i class="fas fa-pause me-1"></i>Désactiver
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash me-1"></i>Supprimer
                </button>
            </div>
        </div>
        <button type="button" class="btn btn-outline-light btn-sm" onclick="clearSelection()">
            <i class="fas fa-times me-1"></i>Annuler la sélection
        </button>
    </div>
</div>

<!-- Filtres de base -->
<div class="card filters-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.products.index') }}" id="filtersForm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-white">Rechercher</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nom du produit..." 
                           value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label text-white">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label text-white">Stock</label>
                    <select name="stock" class="form-select">
                        <option value="">Tous</option>
                        <option value="in_stock" {{ request('stock') == 'in_stock' ? 'selected' : '' }}>En stock</option>
                        <option value="out_of_stock" {{ request('stock') == 'out_of_stock' ? 'selected' : '' }}>Rupture</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-search me-1"></i>Filtrer
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                        <button type="button" class="btn btn-outline-light" onclick="toggleAdvancedFilters()">
                            <i class="fas fa-sliders-h me-1"></i>Avancé
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Filtres avancés -->
<div class="advanced-filters" id="advancedFilters">
    <h6 class="mb-3">
        <i class="fas fa-filter me-2"></i>Filtres Avancés
    </h6>
    <form method="GET" action="{{ route('admin.products.index') }}" id="advancedFiltersForm">
        <!-- Conserver les filtres de base -->
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="stock" value="{{ request('stock') }}">
        
        <div class="row">
            <div class="col-md-3">
                <label class="form-label text-white">Prix minimum (DT)</label>
                <input type="number" name="price_min" class="form-control" 
                       placeholder="0.000" step="0.001" value="{{ request('price_min') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white">Prix maximum (DT)</label>
                <input type="number" name="price_max" class="form-control" 
                       placeholder="999.999" step="0.001" value="{{ request('price_max') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white">Stock minimum</label>
                <input type="number" name="stock_min" class="form-control" 
                       placeholder="0" value="{{ request('stock_min') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white">Stock maximum</label>
                <input type="number" name="stock_max" class="form-control" 
                       placeholder="999" value="{{ request('stock_max') }}">
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-3">
                <label class="form-label text-white">Date de création (de)</label>
                <input type="date" name="created_from" class="form-control" 
                       value="{{ request('created_from') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white">Date de création (à)</label>
                <input type="date" name="created_to" class="form-control" 
                       value="{{ request('created_to') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white">Nécessite examen</label>
                <select name="needs_review" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" {{ request('needs_review') == '1' ? 'selected' : '' }}>Oui</option>
                    <option value="0" {{ request('needs_review') == '0' ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label text-white">Trier par</label>
                <select name="sort" class="form-select">
                    <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Plus récent</option>
                    <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Plus ancien</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nom A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nom Z-A</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                    <option value="stock_asc" {{ request('sort') == 'stock_asc' ? 'selected' : '' }}>Stock croissant</option>
                    <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock décroissant</option>
                </select>
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-light">
                <i class="fas fa-search me-1"></i>Appliquer les filtres
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-light">
                <i class="fas fa-times me-1"></i>Réinitialiser tout
            </a>
        </div>
    </form>
</div>

<!-- Liste des produits -->
<div class="card product-card">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Liste des Produits
            </h5>
            <div class="btn-group view-toggle" role="group">
                <button type="button" class="btn btn-sm active" id="tableViewBtn" onclick="toggleView('table')">
                    <i class="fas fa-table me-1"></i>Table
                </button>
                <button type="button" class="btn btn-sm" id="gridViewBtn" onclick="toggleView('grid')">
                    <i class="fas fa-th me-1"></i>Grille
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Vue Table -->
        <div id="tableView">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" class="form-check-input select-all-checkbox" id="selectAll">
                                </th>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Date création</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" 
                                           value="{{ $product->id }}" name="selected_products[]">
                                </td>
                                
                                <td>
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="product-image">
                                    @else
                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->needs_review)
                                            <span class="badge bg-warning ms-2">
                                                <i class="fas fa-eye me-1"></i>À examiner
                                            </span>
                                        @endif
                                    </div>
                                    @if($product->description)
                                        <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                    @endif
                                </td>
                                
                                <td>
                                    <span class="price-display">{{ number_format($product->price, 3) }} DT</span>
                                </td>
                                
                                <td>
                                    @if($product->stock <= 0)
                                        <span class="badge stock-low">
                                            <i class="fas fa-times me-1"></i>Rupture
                                        </span>
                                    @elseif($product->stock <= 10)
                                        <span class="badge stock-medium">
                                            <i class="fas fa-exclamation me-1"></i>{{ $product->stock }} unités
                                        </span>
                                    @else
                                        <span class="badge stock-high">
                                            <i class="fas fa-check me-1"></i>{{ $product->stock }} unités
                                        </span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Actif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-pause me-1"></i>Inactif
                                        </span>
                                    @endif
                                </td>
                                
                                <td>
                                    <small>{{ $product->created_at->format('d/m/Y') }}</small>
                                </td>
                                
                                <td class="text-center table-actions">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#productModal{{ $product->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <a href="{{ route('admin.products.edit', $product) }}" 
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun produit trouvé</h4>
                    <p class="text-muted">Commencez par ajouter votre premier produit</p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-gradient text-white">
                        <i class="fas fa-plus me-2"></i>Ajouter un Produit
                    </a>
                </div>
            @endif
        </div>
        
        <!-- Vue Grille -->
        <div id="gridView" style="display: none;">
            @if($products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 product-card">
                            <div class="position-relative">
                                <input type="checkbox" class="form-check-input row-checkbox position-absolute top-0 start-0 m-2" 
                                       value="{{ $product->id }}" name="selected_products[]"
                                       style="z-index: 10;">
                                
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" 
                                         class="card-img-top" 
                                         style="height: 200px; object-fit: cover;"
                                         alt="{{ $product->name }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                                
                                @if($product->needs_review)
                                    <span class="position-absolute top-0 end-0 badge bg-warning m-2">
                                        À examiner
                                    </span>
                                @endif
                                
                                <span class="position-absolute bottom-0 end-0 m-2">
                                    @if($product->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="card-body">
                                <h6 class="card-title">{{ $product->name }}</h6>
                                <p class="price-display mb-2">{{ number_format($product->price, 3) }} DT</p>
                                
                                @if($product->stock <= 0)
                                    <span class="badge stock-low mb-2">Rupture de stock</span>
                                @elseif($product->stock <= 10)
                                    <span class="badge stock-medium mb-2">{{ $product->stock }} en stock</span>
                                @else
                                    <span class="badge stock-high mb-2">{{ $product->stock }} en stock</span>
                                @endif
                                
                                @if($product->description)
                                    <p class="card-text text-muted small">{{ Str::limit($product->description, 80) }}</p>
                                @endif
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#productModal{{ $product->id }}">
                                        <i class="fas fa-eye me-1"></i>Voir
                                    </button>
                                    
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Pagination améliorée -->
@if($products->hasPages())
    <div class="row mt-4">
        <div class="col-md-6 d-flex align-items-center">
            <div class="pagination-info">
                <span class="text-muted">
                    Affichage de {{ $products->firstItem() ?? 0 }} à {{ $products->lastItem() ?? 0 }} 
                    sur {{ $products->total() }} produits
                </span>
            </div>
        </div>
        
        <div class="col-md-6">
            <nav aria-label="Navigation des pages">
                <div class="d-flex justify-content-end align-items-center">
                    <!-- Informations de page -->
                    <div class="me-3">
                        <span class="text-muted small">
                            Page {{ $products->currentPage() }} sur {{ $products->lastPage() }}
                        </span>
                    </div>
                    
                    <!-- Navigation -->
                    <ul class="pagination pagination-modern mb-0">
                        {{-- Bouton Première page --}}
                        @if ($products->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->appends(request()->query())->url(1) }}" title="Première page">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Bouton Précédent --}}
                        @if ($products->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-left me-1"></i>Précédent
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->appends(request()->query())->previousPageUrl() }}">
                                    <i class="fas fa-chevron-left me-1"></i>Précédent
                                </a>
                            </li>
                        @endif

                        {{-- Numéros de pages --}}
                        @php
                            $start = max($products->currentPage() - 2, 1);
                            $end = min($start + 4, $products->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->appends(request()->query())->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $products->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $products->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endfor

                        @if($end < $products->lastPage())
                            @if($end < $products->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->appends(request()->query())->url($products->lastPage()) }}">{{ $products->lastPage() }}</a>
                            </li>
                        @endif

                        {{-- Bouton Suivant --}}
                        @if ($products->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->appends(request()->query())->nextPageUrl() }}">
                                    Suivant<i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    Suivant<i class="fas fa-chevron-right ms-1"></i>
                                </span>
                            </li>
                        @endif

                        {{-- Bouton Dernière page --}}
                        @if ($products->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $products->appends(request()->query())->url($products->lastPage()) }}" title="Dernière page">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-angle-double-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <!-- Navigation rapide -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
                <!-- Sélecteur de page rapide -->
                <div class="d-flex align-items-center me-3">
                    <label for="pageSelect" class="form-label me-2 mb-0 text-muted small">Aller à la page:</label>
                    <select class="form-select form-select-sm" id="pageSelect" style="width: auto;" onchange="goToPage(this.value)">
                        @for($i = 1; $i <= $products->lastPage(); $i++)
                            <option value="{{ $i }}" {{ $i == $products->currentPage() ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Sélecteur de nombre d'éléments par page -->
                <div class="d-flex align-items-center">
                    <label for="perPageSelect" class="form-label me-2 mb-0 text-muted small">Éléments par page:</label>
                    <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;" onchange="changePerPage(this.value)">
                        @foreach([10, 20, 50, 100] as $perPage)
                            <option value="{{ $perPage }}" {{ request('per_page', 20) == $perPage ? 'selected' : '' }}>
                                {{ $perPage }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modales pour voir les détails des produits -->
@foreach($products as $product)
<div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-box me-2"></i>{{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" 
                                 class="img-fluid rounded" 
                                 alt="{{ $product->name }}">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Prix:</strong></td>
                                <td class="price-display">{{ number_format($product->price, 3) }} DT</td>
                            </tr>
                            <tr>
                                <td><strong>Stock:</strong></td>
                                <td>{{ $product->stock }} unités</td>
                            </tr>
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Créé le:</strong></td>
                                <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Modifié le:</strong></td>
                                <td>{{ $product->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                        
                        @if($product->description)
                            <div class="mt-3">
                                <strong>Description:</strong>
                                <p class="mt-2">{{ $product->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Modifier
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le produit <strong id="productNameToDelete"></strong> ?</p>
                <p class="text-muted">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour actions groupées -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionTitle">Confirmer l'action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bulkActionMessage">
                <!-- Message dynamique -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmBulkAction">Confirmer</button>
            </div>
        </div>
    </div>
</div>

<!-- Formulaires cachés pour les actions groupées -->
<form id="bulkActivateForm" method="POST" action="{{ route('admin.products.bulk-activate') }}" style="display: none;">
    @csrf
    <input type="hidden" name="product_ids" id="activateProductIds">
</form>

<form id="bulkDeactivateForm" method="POST" action="{{ route('admin.products.bulk-deactivate') }}" style="display: none;">
    @csrf
    <input type="hidden" name="product_ids" id="deactivateProductIds">
</form>

<form id="bulkDeleteForm" method="POST" action="{{ route('admin.products.bulk-delete') }}" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="product_ids" id="deleteProductIds">
</form>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Désactiver DataTables pour permettre le fonctionnement normal de la pagination Laravel
    // $('#productsTable').DataTable({
    //     responsive: true,
    //     pageLength: 20,
    //     language: {
    //         url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/French.json'
    //     },
    //     columnDefs: [
    //         { orderable: false, targets: [0, 1, 7] },
    //         { responsivePriority: 1, targets: 2 },
    //         { responsivePriority: 2, targets: 7 }
    //     ]
    // });
    
    // Auto-submit des filtres de base
    $('#filtersForm input, #filtersForm select').on('change', function() {
        $('#filtersForm').submit();
    });
    
    // Auto-submit des filtres avancés
    $('#advancedFiltersForm input, #advancedFiltersForm select').on('change', function() {
        $('#advancedFiltersForm').submit();
    });
    
    // Gestion des checkboxes
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateBulkActions();
    });
    
    $(document).on('change', '.row-checkbox', function() {
        updateBulkActions();
        
        // Vérifier si tous sont sélectionnés
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Restaurer la vue sauvegardée au chargement
    const savedView = localStorage.getItem('productsView') || 'table';
    toggleView(savedView);

    // Ajouter une animation de transition lors du changement de page
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    if (tableView) tableView.classList.add('page-transition');
    if (gridView) gridView.classList.add('page-transition');
});

// Fonction pour basculer entre vue table et grille
function toggleView(view) {
    console.log('Basculer vers la vue:', view); // Debug
    
    if (view === 'table') {
        $('#tableView').show();
        $('#gridView').hide();
        $('#tableViewBtn').addClass('active');
        $('#gridViewBtn').removeClass('active');
        localStorage.setItem('productsView', 'table');
    } else {
        $('#tableView').hide();
        $('#gridView').show();
        $('#gridViewBtn').addClass('active');
        $('#tableViewBtn').removeClass('active');
        localStorage.setItem('productsView', 'grid');
    }
}

// Fonction pour afficher/masquer les filtres avancés
function toggleAdvancedFilters() {
    console.log('Toggle filtres avancés'); // Debug
    const $advancedFilters = $('#advancedFilters');
    
    if ($advancedFilters.hasClass('show')) {
        $advancedFilters.removeClass('show');
    } else {
        $advancedFilters.addClass('show');
    }
}

// Fonction pour mettre à jour les actions groupées
function updateBulkActions() {
    const selectedCount = $('.row-checkbox:checked').length;
    $('#selectedCount').text(selectedCount + ' produit(s) sélectionné(s)');
    
    if (selectedCount > 0) {
        $('#bulkActions').addClass('show');
    } else {
        $('#bulkActions').removeClass('show');
    }
}

// Fonction pour vider la sélection
function clearSelection() {
    $('.row-checkbox, #selectAll').prop('checked', false);
    updateBulkActions();
}

// Fonction pour les actions groupées
function bulkAction(action) {
    const selectedIds = $('.row-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Veuillez sélectionner au moins un produit.');
        return;
    }
    
    let title, message, formId, inputId;
    
    switch (action) {
        case 'activate':
            title = 'Activer les produits';
            message = `Êtes-vous sûr de vouloir activer ${selectedIds.length} produit(s) ?`;
            formId = 'bulkActivateForm';
            inputId = 'activateProductIds';
            $('#confirmBulkAction').removeClass('btn-danger').addClass('btn-primary');
            break;
        case 'deactivate':
            title = 'Désactiver les produits';
            message = `Êtes-vous sûr de vouloir désactiver ${selectedIds.length} produit(s) ?`;
            formId = 'bulkDeactivateForm';
            inputId = 'deactivateProductIds';
            $('#confirmBulkAction').removeClass('btn-danger').addClass('btn-primary');
            break;
        case 'delete':
            title = 'Supprimer les produits';
            message = `Êtes-vous sûr de vouloir supprimer ${selectedIds.length} produit(s) ? Cette action est irréversible.`;
            formId = 'bulkDeleteForm';
            inputId = 'deleteProductIds';
            $('#confirmBulkAction').removeClass('btn-primary').addClass('btn-danger');
            break;
    }
    
    $('#bulkActionTitle').text(title);
    $('#bulkActionMessage').text(message);
    $('#' + inputId).val(selectedIds.join(','));
    
    $('#confirmBulkAction').off('click').on('click', function() {
        $('#' + formId).submit();
    });
    
    $('#bulkActionModal').modal('show');
}

// Fonction pour confirmer la suppression individuelle
function confirmDelete(productId, productName) {
    $('#productNameToDelete').text(productName);
    $('#deleteForm').attr('action', '/admin/products/' + productId);
    $('#deleteModal').modal('show');
}

// Fonction pour aller à une page spécifique
function goToPage(page) {
    if (page && page !== '{{ $products->currentPage() }}') {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    }
}

// Fonction pour changer le nombre d'éléments par page
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset à la première page
    window.location.href = url.toString();
}

// Raccourcis clavier
$(document).on('keydown', function(e) {
    // Ctrl + A pour sélectionner tout
    if (e.ctrlKey && e.key === 'a' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        $('#selectAll').prop('checked', true).trigger('change');
    }
    
    // Escape pour annuler la sélection
    if (e.key === 'Escape') {
        clearSelection();
    }
    
    // F pour ouvrir les filtres avancés
    if (e.key === 'f' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        toggleAdvancedFilters();
    }

    // Navigation par clavier pour la pagination
    // Flèche gauche pour page précédente
    if (e.altKey && e.key === 'ArrowLeft') {
        e.preventDefault();
        const prevLink = document.querySelector('.pagination-modern .page-item a[href*="page={{ $products->currentPage() - 1 }}"]');
        if (prevLink) {
            window.location.href = prevLink.href;
        }
    }
    
    // Flèche droite pour page suivante
    if (e.altKey && e.key === 'ArrowRight') {
        e.preventDefault();
        const nextLink = document.querySelector('.pagination-modern .page-item a[href*="page={{ $products->currentPage() + 1 }}"]');
        if (nextLink) {
            window.location.href = nextLink.href;
        }
    }
    
    // Home pour première page
    if (e.altKey && e.key === 'Home') {
        e.preventDefault();
        const firstLink = document.querySelector('.pagination-modern .page-item a[href*="page=1"]');
        if (firstLink) {
            window.location.href = firstLink.href;
        }
    }
    
    // End pour dernière page
    if (e.altKey && e.key === 'End') {
        e.preventDefault();
        const lastLink = document.querySelector('.pagination-modern .page-item a[href*="page={{ $products->lastPage() }}"]');
        if (lastLink) {
            window.location.href = lastLink.href;
        }
    }
});

// Debug: Vérifier que les éléments existent
$(document).ready(function() {
    console.log('Elements trouvés:');
    console.log('- tableViewBtn:', $('#tableViewBtn').length);
    console.log('- gridViewBtn:', $('#gridViewBtn').length);
    console.log('- advancedFilters:', $('#advancedFilters').length);
    console.log('- tableView:', $('#tableView').length);
    console.log('- gridView:', $('#gridView').length);

    // Afficher les raccourcis clavier dans la console (pour les développeurs)
    console.log('Raccourcis clavier disponibles:');
    console.log('Ctrl + A : Sélectionner tous les produits');
    console.log('Escape : Annuler la sélection');
    console.log('F : Ouvrir/fermer filtres avancés');
    console.log('Alt + ← : Page précédente');
    console.log('Alt + → : Page suivante');
    console.log('Alt + Home : Première page');
    console.log('Alt + End : Dernière page');
});
</script>
@endsection