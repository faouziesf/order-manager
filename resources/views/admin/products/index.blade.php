@extends('layouts.admin')

@section('title', 'Liste des produits')

@section('css')
<style>
    :root {
        --royal-blue: #1e40af;
        --royal-blue-dark: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-lighter: #dbeafe;
        --royal-blue-bg: #eff6ff;
        --success: #059669;
        --warning: #d97706;
        --danger: #dc2626;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    body {
        background: var(--gray-50);
    }

    /* Header */
    .page-header {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
    }

    .page-title {
        color: var(--royal-blue-dark);
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        color: var(--gray-500);
        margin: 0.5rem 0 0 0;
    }

    /* Statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.15);
    }

    .stat-card.primary {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
    }

    .stat-card.success {
        background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
        color: white;
    }

    .stat-card.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #ea580c 100%);
        color: white;
    }

    .stat-card.danger {
        background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
        color: white;
    }

    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    /* Boutons d'action */
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--royal-blue-dark) 0%, var(--royal-blue) 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
        color: white;
    }

    .btn-warning {
        background: var(--warning);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .btn-warning:hover {
        background: #ea580c;
        color: white;
        transform: translateY(-2px);
    }

    /* Actions groupées */
    .bulk-actions {
        background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        display: none;
        animation: slideDown 0.3s ease;
    }

    .bulk-actions.show {
        display: block;
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

    /* Filtres */
    .filters-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
    }

    .advanced-filters {
        background: linear-gradient(135deg, var(--royal-blue-bg) 0%, var(--gray-50) 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
        border: 2px dashed var(--royal-blue-lighter);
        display: none;
    }

    .advanced-filters.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    .form-control, .form-select {
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
        background: white;
        font-size: 0.875rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
        background: white;
    }

    .btn-outline-primary {
        border: 2px solid var(--royal-blue);
        color: var(--royal-blue);
        background: transparent;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .btn-outline-primary:hover {
        background: var(--royal-blue);
        color: white;
        transform: translateY(-1px);
    }

    /* Table des produits */
    .products-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
        overflow: hidden;
    }

    .products-card-header {
        background: linear-gradient(135deg, var(--royal-blue-bg) 0%, white 100%);
        padding: 1.5rem;
        border-bottom: 1px solid var(--royal-blue-lighter);
    }

    .table {
        margin: 0;
    }

    .table th {
        background: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        border: none;
        padding: 1rem 0.75rem;
        font-size: 0.875rem;
    }

    .table td {
        padding: 1rem 0.75rem;
        border-color: var(--gray-200);
        vertical-align: middle;
    }

    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid var(--royal-blue-lighter);
    }

    .product-image-placeholder {
        width: 50px;
        height: 50px;
        background: var(--gray-100);
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-500);
    }

    .product-name {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .product-reference {
        font-size: 0.75rem;
        color: var(--royal-blue);
        font-weight: 500;
    }

    .product-price {
        font-weight: 700;
        color: var(--royal-blue-dark);
        font-size: 1rem;
    }

    /* Badges */
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background: var(--royal-blue-lighter);
        color: var(--royal-blue-dark);
    }

    /* Boutons d'action dans le tableau */
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-outline-primary.btn-sm {
        border: 1px solid var(--royal-blue);
        color: var(--royal-blue);
    }

    .btn-outline-warning.btn-sm {
        border: 1px solid var(--warning);
        color: var(--warning);
    }

    .btn-outline-danger.btn-sm {
        border: 1px solid var(--danger);
        color: var(--danger);
    }

    .btn-sm:hover {
        transform: translateY(-1px);
    }

    /* Pagination */
    .pagination-wrapper {
        background: white;
        padding: 1.5rem;
        border-top: 1px solid var(--royal-blue-lighter);
    }

    .pagination {
        margin: 0;
    }

    .page-link {
        border: 1px solid var(--gray-200);
        color: var(--royal-blue);
        padding: 0.5rem 0.75rem;
        margin: 0 2px;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background: var(--royal-blue-bg);
        border-color: var(--royal-blue);
        color: var(--royal-blue-dark);
        transform: translateY(-1px);
    }

    .page-item.active .page-link {
        background: var(--royal-blue);
        border-color: var(--royal-blue);
        color: white;
    }

    .page-item.disabled .page-link {
        opacity: 0.5;
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-500);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--royal-blue-lighter);
        margin-bottom: 1rem;
    }

    /* Lignes protégées */
    .protected-row {
        background-color: #fafafa !important;
        opacity: 0.7;
    }
    
    .protected-row:hover {
        background-color: #f0f0f0 !important;
        transform: none !important;
    }
    
    .protected-row .product-name {
        color: var(--gray-500) !important;
    }
    
    input[type="checkbox"]:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Tooltip pour les éléments protégés */
    [title] {
        position: relative;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-primary, .btn-warning {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .filters-card {
            padding: 1rem;
        }

        .table-responsive {
            border-radius: 0;
        }

        .product-image, .product-image-placeholder {
            width: 40px;
            height: 40px;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .bulk-actions {
            padding: 1rem;
        }

        .pagination-wrapper {
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- En-tête de page -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-box-open me-2"></i>Catalogue Produits
                </h1>
                <p class="page-subtitle">Gérez votre inventaire et vos produits</p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau Produit
                </a>
                @if(auth('admin')->user()->products()->where('needs_review', true)->count() > 0)
                    <a href="{{ route('admin.products.review') }}" class="btn btn-warning">
                        <i class="fas fa-eye me-2"></i>Examiner ({{ auth('admin')->user()->products()->where('needs_review', true)->count() }})
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $products->total() }}</div>
                    <div class="stat-label">Total Produits</div>
                </div>
                <i class="fas fa-boxes stat-icon"></i>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ auth('admin')->user()->products()->where('is_active', true)->count() }}</div>
                    <div class="stat-label">Produits Actifs</div>
                </div>
                <i class="fas fa-check-circle stat-icon"></i>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ auth('admin')->user()->products()->where('stock', '>', 0)->where('stock', '<=', 10)->count() }}</div>
                    <div class="stat-label">Stock Faible</div>
                </div>
                <i class="fas fa-exclamation-triangle stat-icon"></i>
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ auth('admin')->user()->products()->where('stock', '<=', 0)->count() }}</div>
                    <div class="stat-label">Rupture Stock</div>
                </div>
                <i class="fas fa-times-circle stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- Actions groupées -->
    <div class="bulk-actions" id="bulkActions">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <span class="me-3" id="selectedCount">0 produit(s) sélectionné(s)</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-light btn-sm" onclick="bulkAction('activate')">
                        <i class="fas fa-check me-1"></i>Activer
                    </button>
                    <button type="button" class="btn btn-light btn-sm" onclick="bulkAction('deactivate')">
                        <i class="fas fa-pause me-1"></i>Désactiver
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-1"></i>Supprimer <small>(non protégés)</small>
                    </button>
                </div>
            </div>
            <button type="button" class="btn btn-outline-light btn-sm" onclick="clearSelection()">
                <i class="fas fa-times me-1"></i>Annuler
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-card">
        <form method="GET" action="{{ route('admin.products.index') }}" id="filtersForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Rechercher</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nom ou référence..." 
                           value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Stock</label>
                    <select name="stock" class="form-select">
                        <option value="">Tous</option>
                        <option value="in_stock" {{ request('stock') == 'in_stock' ? 'selected' : '' }}>En stock</option>
                        <option value="low_stock" {{ request('stock') == 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                        <option value="out_of_stock" {{ request('stock') == 'out_of_stock' ? 'selected' : '' }}>Rupture</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Trier par</label>
                    <select name="sort" class="form-select">
                        <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Plus récent</option>
                        <option value="reference_asc" {{ request('sort') == 'reference_asc' ? 'selected' : '' }}>Référence A-Z</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nom A-Z</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock décroissant</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleAdvancedFilters()">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtres avancés -->
            <div class="advanced-filters" id="advancedFilters">
                <h6 class="mb-3">
                    <i class="fas fa-filter me-2"></i>Filtres Avancés
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Prix min (DT)</label>
                        <input type="number" name="price_min" class="form-control" 
                               step="0.001" value="{{ request('price_min') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix max (DT)</label>
                        <input type="number" name="price_max" class="form-control" 
                               step="0.001" value="{{ request('price_max') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock min</label>
                        <input type="number" name="stock_min" class="form-control" 
                               value="{{ request('stock_min') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock max</label>
                        <input type="number" name="stock_max" class="form-control" 
                               value="{{ request('stock_max') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Appliquer
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des produits -->
    <div class="products-card">
        <div class="products-card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Liste des Produits
                <small class="text-muted ms-2">({{ $products->total() }} produits)</small>
            </h5>
        </div>
        
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th width="70">Image</th>
                            <th>Produit</th>
                            <th width="120">Prix</th>
                            <th width="100">Stock</th>
                            <th width="80">Statut</th>
                            <th width="100">Date</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                @if(!$product->isUsedInOrders())
                                    <input type="checkbox" class="form-check-input row-checkbox" 
                                           value="{{ $product->id }}" name="selected_products[]">
                                @else
                                    <input type="checkbox" class="form-check-input" 
                                           disabled 
                                           title="Produit utilisé dans des commandes">
                                    <i class="fas fa-lock text-muted ms-1" 
                                       title="Produit protégé car utilisé dans des commandes"></i>
                                @endif
                            </td>
                            
                            <td>
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            
                            <td>
                                <div class="product-name">{{ $product->name }}</div>
                                @if($product->reference)
                                    <div class="product-reference">REF: {{ $product->reference }}</div>
                                @endif
                                @if($product->needs_review)
                                    <span class="badge badge-warning mt-1">
                                        <i class="fas fa-eye"></i> À examiner
                                    </span>
                                @endif
                                @if($product->description)
                                    <small class="text-muted d-block mt-1">{{ Str::limit($product->description, 50) }}</small>
                                @endif
                            </td>
                            
                            <td>
                                <span class="product-price">{{ number_format($product->price, 3) }} DT</span>
                            </td>
                            
                            <td>
                                @php $badge = $product->stock_badge; @endphp
                                <span class="badge badge-{{ str_replace('badge-', '', $badge['class']) }}">
                                    <i class="{{ $badge['icon'] }}"></i>
                                    {{ $badge['text'] }}
                                </span>
                            </td>
                            
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Actif
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-pause"></i> Inactif
                                    </span>
                                @endif
                            </td>
                            
                            <td>
                                <small class="text-muted">{{ $product->created_at->format('d/m/Y') }}</small>
                            </td>
                            
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#productModal{{ $product->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if(!$product->isUsedInOrders())
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                disabled
                                                title="Produit utilisé dans des commandes">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="pagination-wrapper">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Affichage de {{ $products->firstItem() }} à {{ $products->lastItem() }} 
                            sur {{ $products->total() }} produits
                        </div>
                        <nav>
                            {{ $products->appends(request()->query())->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h4>Aucun produit trouvé</h4>
                <p>Aucun produit ne correspond à vos critères de recherche</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Ajouter un Produit
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modales pour voir les détails des produits -->
@foreach($products as $product)
<div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-box me-2"></i>{{ $product->name }}
                    @if($product->reference)
                        <small class="text-muted">(REF: {{ $product->reference }})</small>
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
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
                                <td><strong>Prix :</strong></td>
                                <td class="product-price">{{ number_format($product->price, 3) }} DT</td>
                            </tr>
                            <tr>
                                <td><strong>Stock :</strong></td>
                                <td>{{ $product->stock }} unités</td>
                            </tr>
                            <tr>
                                <td><strong>Statut :</strong></td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-secondary">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Créé le :</strong></td>
                                <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($product->isUsedInOrders())
                            <tr>
                                <td><strong>Commandes :</strong></td>
                                <td>{{ $product->getOrdersCount() }} commande(s)</td>
                            </tr>
                            @endif
                        </table>
                        
                        @if($product->description)
                            <div class="mt-3">
                                <strong>Description :</strong>
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
<script>
$(document).ready(function() {
    // Auto-submit des filtres
    $('#filtersForm input, #filtersForm select').on('change', function() {
        $('#filtersForm').submit();
    });
    
    // Gestion des checkboxes - AMÉLIORÉE
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        // Ne sélectionner que les checkboxes non désactivées
        $('.row-checkbox:not(:disabled)').prop('checked', isChecked);
        updateBulkActions();
    });
    
    $(document).on('change', '.row-checkbox', function() {
        updateBulkActions();
        
        // Vérifier si tous les checkboxes disponibles sont sélectionnés
        const totalAvailableCheckboxes = $('.row-checkbox:not(:disabled)').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        $('#selectAll').prop('checked', totalAvailableCheckboxes === checkedCheckboxes && totalAvailableCheckboxes > 0);
    });
    
    // Ajouter un avertissement visuel pour les produits protégés
    $('input[type="checkbox"]:disabled').closest('tr').addClass('protected-row');
});

function toggleAdvancedFilters() {
    const $filters = $('#advancedFilters');
    $filters.toggleClass('show');
}

function updateBulkActions() {
    const selectedCount = $('.row-checkbox:checked').length;
    const protectedCount = $('input[type="checkbox"]:disabled').length;
    
    let message = selectedCount + ' produit(s) sélectionné(s)';
    if (protectedCount > 0) {
        message += ` (${protectedCount} protégé(s))`;
    }
    
    $('#selectedCount').text(message);
    
    if (selectedCount > 0) {
        $('#bulkActions').addClass('show');
    } else {
        $('#bulkActions').removeClass('show');
    }
}

function clearSelection() {
    $('.row-checkbox:not(:disabled), #selectAll').prop('checked', false);
    updateBulkActions();
}

function bulkAction(action) {
    // Ne récupérer que les IDs des checkboxes cochées ET non désactivées
    const selectedIds = $('.row-checkbox:checked:not(:disabled)').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Veuillez sélectionner au moins un produit modifiable.');
        return;
    }
    
    let confirmMessage, formId, inputId;
    
    switch (action) {
        case 'activate':
            confirmMessage = `Activer ${selectedIds.length} produit(s) ?`;
            formId = 'bulkActivateForm';
            inputId = 'activateProductIds';
            break;
        case 'deactivate':
            confirmMessage = `Désactiver ${selectedIds.length} produit(s) ?`;
            formId = 'bulkDeactivateForm';
            inputId = 'deactivateProductIds';
            break;
        case 'delete':
            confirmMessage = `Supprimer ${selectedIds.length} produit(s) ?\n\nATTENTION: Cette action est irréversible.\nSeuls les produits non utilisés dans des commandes seront supprimés.`;
            formId = 'bulkDeleteForm';
            inputId = 'deleteProductIds';
            break;
    }
    
    if (confirm(confirmMessage)) {
        $('#' + inputId).val(selectedIds.join(','));
        $('#' + formId).submit();
    }
}

function confirmDelete(productId, productName) {
    $('#productNameToDelete').text(productName);
    $('#deleteForm').attr('action', '/admin/products/' + productId);
    $('#deleteModal').modal('show');
}
</script>
@endsection