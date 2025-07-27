@extends('layouts.admin')

@section('title', 'Examiner les nouveaux produits')

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
    .review-header {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
        text-align: center;
    }

    .review-title {
        color: var(--royal-blue-dark);
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .review-subtitle {
        color: var(--gray-500);
        font-size: 1rem;
    }

    /* Alert d'information */
    .info-alert {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid var(--warning);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .info-alert i {
        color: var(--warning);
        font-size: 1.25rem;
    }

    /* Actions globales */
    .global-actions {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #047857 0%, var(--success) 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
        color: white;
    }

    /* Cards des produits */
    .product-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--royal-blue-lighter);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.05);
    }

    .product-card:hover {
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.15);
        transform: translateY(-2px);
    }

    .product-card-header {
        background: linear-gradient(135deg, var(--royal-blue-bg) 0%, var(--gray-50) 100%);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--royal-blue-lighter);
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .product-status-badge {
        background: linear-gradient(135deg, var(--warning) 0%, #ea580c 100%);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .product-date {
        color: var(--gray-500);
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .product-card-body {
        padding: 1.5rem;
    }

    .product-info {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1.5rem;
        align-items: start;
    }

    .product-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid var(--royal-blue-lighter);
    }

    .product-placeholder {
        width: 80px;
        height: 80px;
        background: var(--gray-100);
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-500);
    }

    .product-details h5 {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--gray-900);
        font-size: 1.125rem;
    }

    .product-reference {
        font-size: 0.75rem;
        color: var(--royal-blue);
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .product-price {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--royal-blue-dark);
        margin-bottom: 0.5rem;
    }

    .product-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .product-stock {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        gap: 0.25rem;
    }

    .stock-good {
        background: #dcfce7;
        color: #166534;
    }

    .stock-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .stock-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-secondary {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .product-description {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-top: 0.75rem;
        line-height: 1.5;
    }

    /* Actions des produits */
    .product-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        min-width: 150px;
    }

    .btn-approve {
        background: var(--success);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .btn-approve:hover {
        background: #047857;
        color: white;
        transform: translateY(-1px);
    }

    .btn-edit {
        background: var(--warning);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .btn-edit:hover {
        background: #ea580c;
        color: white;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-delete {
        background: var(--danger);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .btn-delete:hover {
        background: #b91c1c;
        color: white;
        transform: translateY(-1px);
    }

    .btn-details {
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-200);
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
    }

    .btn-details:hover {
        background: var(--gray-200);
        color: var(--gray-900);
        text-decoration: none;
    }

    /* État vide */
    .empty-state {
        background: white;
        border-radius: 12px;
        padding: 4rem 2rem;
        text-align: center;
        border: 1px solid var(--royal-blue-lighter);
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--success);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: var(--gray-900);
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .empty-state p {
        color: var(--gray-500);
        margin-bottom: 2rem;
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
        text-decoration: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--royal-blue-dark) 0%, var(--royal-blue) 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.3);
        color: white;
        text-decoration: none;
    }

    /* Pagination */
    .pagination-wrapper {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-top: 2rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
    }

    .pagination-info {
        color: var(--gray-500);
        font-size: 0.875rem;
    }

    .form-select-sm {
        padding: 0.5rem;
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        font-size: 0.875rem;
        background: white;
    }

    .form-select-sm:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
    }

    /* Breadcrumb */
    .breadcrumb {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.1);
        border: 1px solid var(--royal-blue-lighter);
    }

    .breadcrumb-item a {
        color: var(--royal-blue);
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-item a:hover {
        color: var(--royal-blue-dark);
    }

    .breadcrumb-item.active {
        color: var(--gray-700);
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .review-header {
            padding: 1.5rem 1rem;
        }
        
        .product-info {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }
        
        .product-actions {
            flex-direction: row;
            justify-content: center;
            flex-wrap: wrap;
            min-width: auto;
        }
        
        .product-card-body {
            padding: 1rem;
        }
        
        .global-actions {
            padding: 1rem;
        }
    }

    @media (max-width: 640px) {
        .product-card-header {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }
        
        .product-actions {
            width: 100%;
        }
        
        .product-actions .btn {
            flex: 1;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="review-header">
        <div class="review-title">
            <i class="fas fa-eye text-primary me-2"></i>
            Examiner les Nouveaux Produits
        </div>
        <div class="review-subtitle">
            {{ $products->total() }} produit(s) nécessite(nt) votre validation
        </div>
    </div>

    <!-- Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Accueil
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.products.index') }}">Produits</a>
            </li>
            <li class="breadcrumb-item active">Examiner</li>
        </ol>
    </nav>

    @if($products->count() > 0)
        <!-- Alerte d'information -->
        <div class="info-alert">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Attention :</strong> Ces produits ont été créés automatiquement et nécessitent votre validation avant d'être mis en ligne.
            </div>
        </div>

        <!-- Actions globales -->
        <div class="global-actions">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-1">Actions Groupées</h5>
                    <p class="text-muted mb-0 small">Approuvez tous les produits en une seule action</p>
                </div>
                <form action="{{ route('admin.products.mark-all-reviewed') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-success" onclick="confirmMarkAll()">
                        <i class="fas fa-check-double me-2"></i>
                        Tout Approuver ({{ $products->total() }})
                    </button>
                </form>
            </div>
        </div>

        <!-- Contrôle de pagination -->
        @if($products->total() > 15)
            <div class="pagination-wrapper">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="pagination-info">
                        Affichage de {{ $products->firstItem() }} à {{ $products->lastItem() }} 
                        sur {{ $products->total() }} produits
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="form-label mb-0 me-2">Afficher :</label>
                        <select id="perPageSelect" class="form-select form-select-sm">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                            <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif

        <!-- Liste des produits à examiner -->
        @foreach($products as $product)
        <div class="product-card">
            <!-- Header de la carte -->
            <div class="product-card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="product-status-badge">
                        <i class="fas fa-star"></i>Nouveau
                    </span>
                    <span class="fw-medium">{{ $product->name }}</span>
                </div>
                <div class="product-date">
                    <i class="fas fa-clock"></i>
                    {{ $product->created_at->format('d/m/Y à H:i') }}
                </div>
            </div>

            <!-- Corps de la carte -->
            <div class="product-card-body">
                <div class="product-info">
                    <!-- Image -->
                    <div>
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="product-image">
                        @else
                            <div class="product-placeholder">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Détails -->
                    <div class="product-details">
                        <h5>{{ $product->name }}</h5>
                        
                        @if($product->reference)
                            <div class="product-reference">REF: {{ $product->reference }}</div>
                        @endif
                        
                        <div class="product-price">{{ number_format($product->price, 3) }} DT</div>
                        
                        <div class="product-meta">
                            @if($product->stock <= 0)
                                <span class="product-stock stock-danger">
                                    <i class="fas fa-times"></i>Rupture de stock
                                </span>
                            @elseif($product->stock <= 10)
                                <span class="product-stock stock-warning">
                                    <i class="fas fa-exclamation-triangle"></i>{{ $product->stock }} unités
                                </span>
                            @else
                                <span class="product-stock stock-good">
                                    <i class="fas fa-check"></i>{{ $product->stock }} unités
                                </span>
                            @endif
                            
                            @if($product->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-secondary">Inactif</span>
                            @endif
                        </div>

                        @if($product->description)
                            <div class="product-description">
                                {{ Str::limit($product->description, 120) }}
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="product-actions">
                        <form action="{{ route('admin.products.mark-reviewed', $product) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-approve w-100">
                                <i class="fas fa-check"></i>Approuver
                            </button>
                        </form>

                        <a href="{{ route('admin.products.edit', $product) }}" 
                           class="btn btn-edit w-100">
                            <i class="fas fa-edit"></i>Modifier
                        </a>

                        <button type="button" 
                                class="btn btn-delete w-100" 
                                onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                            <i class="fas fa-trash"></i>Supprimer
                        </button>

                        <button type="button" 
                                class="btn btn-details" 
                                data-bs-toggle="modal" 
                                data-bs-target="#productModal{{ $product->id }}">
                            <i class="fas fa-eye"></i>Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="d-flex justify-content-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif

    @else
        <!-- État vide -->
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <h3>Excellent travail !</h3>
            <p>Tous vos produits ont été examinés et approuvés.</p>
            <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Retour aux Produits
            </a>
        </div>
    @endif
</div>

<!-- Modales pour les détails des produits -->
@foreach($products as $product)
<div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $product->name }}
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
                                <td>{{ number_format($product->price, 3) }} DT</td>
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
                
                <form action="{{ route('admin.products.mark-reviewed', $product) }}" 
                      method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Approuver
                    </button>
                </form>
                
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
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                    Confirmer la suppression
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
@endsection

@section('scripts')
<script>
// Gestion de la pagination
$('#perPageSelect').on('change', function() {
    const perPage = $(this).val();
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
});

// Fonction pour confirmer la suppression
function confirmDelete(productId, productName) {
    $('#productNameToDelete').text(productName);
    $('#deleteForm').attr('action', '/admin/products/' + productId);
    $('#deleteModal').modal('show');
}

// Fonction pour confirmer l'approbation de tous les produits
function confirmMarkAll() {
    if (confirm('Êtes-vous sûr de vouloir approuver tous les produits ? Cette action marquera tous les produits comme examinés.')) {
        $('form[action="{{ route("admin.products.mark-all-reviewed") }}"]').submit();
    }
}

$(document).ready(function() {
    // Animation d'entrée pour les cartes
    $('.product-card').each(function(index) {
        $(this).css('opacity', '0').delay(index * 100).animate({
            opacity: 1
        }, 300);
    });
    
    // Feedback visuel lors des actions
    $('.btn-approve').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Approbation...');
    });
    
    // Raccourcis clavier
    $(document).on('keydown', function(e) {
        // Ctrl + A pour approuver tout
        if (e.ctrlKey && e.key === 'a' && {{ $products->count() }} > 0) {
            e.preventDefault();
            confirmMarkAll();
        }
        
        // Escape pour retourner à la liste
        if (e.key === 'Escape') {
            window.location.href = '{{ route("admin.products.index") }}';
        }
    });
});
</script>
@endsection