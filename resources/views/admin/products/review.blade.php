@extends('layouts.admin')

@section('title', 'Examiner les nouveaux produits')

@section('css')
<style>
    /* Variables CSS pour la cohérence */
    :root {
        --primary-color: #4f46e5;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --gray-900: #111827;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        --radius: 0.5rem;
    }

    body {
        background-color: var(--gray-50);
    }

    /* Header moderne */
    .review-header {
        background: white;
        border-radius: var(--radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        text-align: center;
    }

    .review-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .review-subtitle {
        color: var(--gray-500);
        font-size: 1rem;
    }

    /* Alert moderne */
    .review-alert {
        background: linear-gradient(45deg, #fef3c7, #fde68a);
        border: 1px solid #f59e0b;
        border-radius: var(--radius);
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .review-alert i {
        color: #d97706;
        font-size: 1.25rem;
    }

    /* Actions globales */
    .global-actions {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    /* Cards des produits */
    .product-review-card {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .product-review-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .product-card-header {
        background: var(--gray-50);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .product-status-badge {
        background: linear-gradient(45deg, var(--warning-color), #f59e0b);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .product-date {
        color: var(--gray-500);
        font-size: 0.875rem;
    }

    .product-card-body {
        padding: 1.5rem;
    }

    .product-info-grid {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1rem;
        align-items: center;
    }

    .product-image {
        width: 80px;
        height: 80px;
        border-radius: var(--radius);
        object-fit: cover;
        border: 1px solid var(--gray-200);
    }

    .product-placeholder {
        width: 80px;
        height: 80px;
        background: var(--gray-100);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-500);
    }

    .product-details h5 {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .product-price {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .product-stock {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
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

    .product-description {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-top: 0.5rem;
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
        background: var(--success-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius);
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-approve:hover {
        background: #059669;
        color: white;
        transform: translateY(-1px);
    }

    .btn-edit {
        background: var(--warning-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius);
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-edit:hover {
        background: #d97706;
        color: white;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-delete {
        background: var(--danger-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--radius);
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-delete:hover {
        background: #dc2626;
        color: white;
        transform: translateY(-1px);
    }

    .btn-details {
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-200);
        padding: 0.375rem 0.75rem;
        border-radius: var(--radius);
        font-size: 0.75rem;
        text-decoration: none;
        text-align: center;
    }

    .btn-details:hover {
        background: var(--gray-200);
        color: var(--gray-900);
        text-decoration: none;
    }

    /* État vide */
    .empty-state {
        background: white;
        border-radius: var(--radius);
        padding: 4rem 2rem;
        text-align: center;
        border: 1px solid var(--gray-200);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--success-color);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-500);
        margin-bottom: 2rem;
    }

    /* Pagination */
    .pagination-wrapper {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: var(--radius);
        margin-top: 2rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    .pagination-controls {
        display: flex;
        justify-content: between;
        align-items: center;
        gap: 1rem;
    }

    .pagination-info {
        color: var(--gray-500);
        font-size: 0.875rem;
    }

    .per-page-selector select {
        padding: 0.5rem;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        font-size: 0.875rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .review-header {
            padding: 1.5rem 1rem;
        }
        
        .product-info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }
        
        .product-actions {
            flex-direction: row;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .product-card-body {
            padding: 1rem;
        }
        
        .pagination-controls {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 640px) {
        .product-card-header {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }
        
        .product-actions {
            min-width: auto;
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
            Examiner les nouveaux produits
        </div>
        <div class="review-subtitle">
            {{ $products->total() }} produit(s) nécessite(nt) votre attention
        </div>
    </div>

    <!-- Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white rounded px-3 py-2 border">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Accueil
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.products.index') }}" class="text-decoration-none">Produits</a>
            </li>
            <li class="breadcrumb-item active">Examiner</li>
        </ol>
    </nav>

    @if($products->count() > 0)
        <!-- Alerte d'information -->
        <div class="review-alert">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Ces produits ont été créés automatiquement</strong> lors d'importations et nécessitent votre validation avant d'être mis en ligne.
            </div>
        </div>

        <!-- Actions globales -->
        <div class="global-actions">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Actions groupées</h5>
                    <p class="text-muted mb-0 small">Gérez tous les produits en une seule action</p>
                </div>
                <form action="{{ route('admin.products.mark-all-reviewed') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-success" onclick="confirmMarkAll()">
                        <i class="fas fa-check-double me-1"></i>
                        Tout approuver ({{ $products->total() }})
                    </button>
                </form>
            </div>
        </div>

        <!-- Contrôle de pagination en haut -->
        @if($products->total() > 15)
            <div class="pagination-wrapper">
                <div class="pagination-controls">
                    <div class="pagination-info">
                        Affichage de {{ $products->firstItem() }} à {{ $products->lastItem() }} 
                        sur {{ $products->total() }} produits à examiner
                    </div>
                    <div class="per-page-selector">
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

        <!-- Liste des produits -->
        @foreach($products as $product)
        <div class="product-review-card">
            <!-- Header de la carte -->
            <div class="product-card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="product-status-badge">
                        <i class="fas fa-star me-1"></i>Nouveau
                    </span>
                    <span class="fw-medium">{{ $product->name }}</span>
                </div>
                <div class="product-date">
                    <i class="fas fa-clock me-1"></i>
                    Créé le {{ $product->created_at->format('d/m/Y à H:i') }}
                </div>
            </div>

            <!-- Corps de la carte -->
            <div class="product-card-body">
                <div class="product-info-grid">
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
                        <div class="product-price">{{ number_format($product->price, 3) }} DT</div>
                        
                        <div>
                            @if($product->stock <= 0)
                                <span class="product-stock stock-danger">
                                    <i class="fas fa-times"></i>Rupture de stock
                                </span>
                            @elseif($product->stock <= 10)
                                <span class="product-stock stock-warning">
                                    <i class="fas fa-exclamation"></i>{{ $product->stock }} unités
                                </span>
                            @else
                                <span class="product-stock stock-good">
                                    <i class="fas fa-check"></i>{{ $product->stock }} unités
                                </span>
                            @endif
                            
                            @if($product->is_active)
                                <span class="badge bg-success ms-2">Actif</span>
                            @else
                                <span class="badge bg-secondary ms-2">Inactif</span>
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
                                <i class="fas fa-check me-1"></i>Approuver
                            </button>
                        </form>

                        <a href="{{ route('admin.products.edit', $product) }}" 
                           class="btn btn-edit w-100">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>

                        <button type="button" 
                                class="btn btn-delete w-100" 
                                onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>

                        <button type="button" 
                                class="btn btn-details" 
                                data-bs-toggle="modal" 
                                data-bs-target="#productModal{{ $product->id }}">
                            <i class="fas fa-eye me-1"></i>Détails
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
            <p>Tous vos produits ont été examinés. Il n'y a rien à valider pour le moment.</p>
            <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i>Retour à la liste des produits
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
                <h5 class="modal-title">{{ $product->name }}</h5>
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
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
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
                <h5 class="modal-title">Confirmer la suppression</h5>
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
                    <button type="submit" class="btn btn-danger">Supprimer</button>
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
    // Animation d'entrée progressive pour les cartes
    $('.product-review-card').each(function(index) {
        $(this).css('opacity', '0').delay(index * 100).animate({
            opacity: 1
        }, 300);
    });
    
    // Feedback visuel lors des actions
    $('.btn-approve').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Approbation...');
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