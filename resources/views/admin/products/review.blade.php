@extends('layouts.admin')

@section('title', 'Examiner les nouveaux produits')

@section('css')
<style>
    .review-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: calc(100vh - 160px);
        padding: 1rem 0;
    }
    
    .review-header {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .product-review-card {
        background: white;
        border-radius: 15px;
        margin-bottom: 2rem;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: none;
    }
    
    .product-review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .product-header {
        background: linear-gradient(45deg, #ffecd2 0%, #fcb69f 100%);
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .product-body {
        padding: 2rem;
    }
    
    .product-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-placeholder {
        width: 120px;
        height: 120px;
        background: #f7fafc;
        border: 2px dashed #cbd5e0;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .badge-new {
        background: linear-gradient(45deg, #f093fb, #f5576c);
        color: white;
    }
    
    .badge-warning {
        background: linear-gradient(45deg, #ffeaa7, #fdcb6e);
        color: #2d3436;
    }
    
    .badge-info {
        background: linear-gradient(45deg, #74b9ff, #0984e3);
        color: white;
    }
    
    .btn-approve {
        background: linear-gradient(45deg, #00b894, #00cec9);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-approve:hover {
        background: linear-gradient(45deg, #00cec9, #00b894);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 184, 148, 0.3);
        color: white;
    }
    
    .btn-edit {
        background: linear-gradient(45deg, #fdcb6e, #e17055);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-edit:hover {
        background: linear-gradient(45deg, #e17055, #fdcb6e);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(225, 112, 85, 0.3);
        color: white;
    }
    
    .btn-delete {
        background: linear-gradient(45deg, #e17055, #d63031);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-delete:hover {
        background: linear-gradient(45deg, #d63031, #e17055);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(214, 48, 49, 0.3);
        color: white;
    }
    
    .btn-gradient {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-gradient:hover {
        background: linear-gradient(45deg, #764ba2, #667eea);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .price-display {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c5282;
    }
    
    .stock-indicator {
        padding: 0.5rem 1rem;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .stock-good {
        background: #c6f6d5;
        color: #22543d;
    }
    
    .stock-warning {
        background: #fef5e7;
        color: #744210;
    }
    
    .stock-danger {
        background: #fed7e2;
        color: #742a2a;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .bulk-actions {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .warning-banner {
        background: linear-gradient(45deg, #ffeaa7, #fab1a0);
        color: #2d3436;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 600;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .review-container {
            padding: 1rem 0;
        }
        
        .product-body {
            padding: 1.5rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .action-buttons .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="review-container">
    <div class="container">
        <!-- Header -->
        <div class="review-header">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <i class="fas fa-search-plus fa-3x text-primary me-3"></i>
                <div>
                    <h1 class="mb-0">Examiner les Nouveaux Produits</h1>
                    <p class="text-muted mb-0">{{ $products->total() }} produit(s) nécessite(nt) votre attention</p>
                </div>
            </div>
            
            @if($products->count() > 0)
                <div class="warning-banner">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Ces produits ont été créés automatiquement lors d'importations et nécessitent votre validation
                </div>
            @endif
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.products.index') }}" class="text-white text-decoration-none">Produits</a>
                </li>
                <li class="breadcrumb-item text-white-50">Examiner</li>
            </ol>
        </nav>

        @if($products->count() > 0)
            <!-- Actions groupées -->
            <div class="bulk-actions">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2 text-primary"></i>Actions groupées
                    </h5>
                    <form action="{{ route('admin.products.mark-all-reviewed') }}" method="POST" id="markAllForm">
                        @csrf
                        <button type="button" class="btn btn-gradient" onclick="confirmMarkAll()">
                            <i class="fas fa-check-double me-2"></i>Marquer tout comme examiné
                        </button>
                    </form>
                </div>
                <small class="text-muted">Cette action marquera tous les produits de cette liste comme examinés</small>
            </div>

            <!-- Liste des produits -->
            @foreach($products as $product)
            <div class="card product-review-card">
                <!-- Header du produit -->
                <div class="product-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="status-badge badge-new me-3">
                                <i class="fas fa-star me-1"></i>Nouveau
                            </span>
                            <h5 class="mb-0">{{ $product->name }}</h5>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Créé le {{ $product->created_at->format('d/m/Y à H:i') }}
                        </small>
                    </div>
                </div>

                <!-- Corps du produit -->
                <div class="product-body">
                    <div class="row align-items-center">
                        <!-- Image -->
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            @else
                                <div class="product-placeholder">
                                    <i class="fas fa-image text-muted fa-2x"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Informations -->
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="small text-muted">Prix unitaire</label>
                                        <div class="price-display">{{ number_format($product->price, 3) }} DT</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="small text-muted">Stock disponible</label>
                                        <div>
                                            @if($product->stock <= 0)
                                                <span class="stock-indicator stock-danger">
                                                    <i class="fas fa-times me-1"></i>Rupture de stock
                                                </span>
                                            @elseif($product->stock <= 10)
                                                <span class="stock-indicator stock-warning">
                                                    <i class="fas fa-exclamation me-1"></i>{{ $product->stock }} unités
                                                </span>
                                            @else
                                                <span class="stock-indicator stock-good">
                                                    <i class="fas fa-check me-1"></i>{{ $product->stock }} unités
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($product->description)
                                <div class="mb-3">
                                    <label class="small text-muted">Description</label>
                                    <p class="mb-0">{{ Str::limit($product->description, 150) }}</p>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                @if($product->is_active)
                                    <span class="status-badge badge-info">
                                        <i class="fas fa-check me-1"></i>Actif
                                    </span>
                                @else
                                    <span class="status-badge badge-warning">
                                        <i class="fas fa-pause me-1"></i>Inactif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-md-4">
                            <div class="action-buttons">
                                <!-- Marquer comme examiné -->
                                <form action="{{ route('admin.products.mark-reviewed', $product) }}" 
                                      method="POST" 
                                      style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-approve" title="Approuver ce produit">
                                        <i class="fas fa-check me-1"></i>Approuver
                                    </button>
                                </form>

                                <!-- Modifier -->
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-edit"
                                   title="Modifier ce produit">
                                    <i class="fas fa-edit me-1"></i>Modifier
                                </a>

                                <!-- Supprimer -->
                                <button type="button" 
                                        class="btn btn-delete" 
                                        onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')"
                                        title="Supprimer ce produit">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#productModal{{ $product->id }}">
                                    <i class="fas fa-eye me-1"></i>Voir détails
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif

        @else
            <!-- État vide -->
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3 class="text-muted">Excellent travail !</h3>
                <p class="text-muted">Tous vos produits ont été examinés.</p>
                <a href="{{ route('admin.products.index') }}" class="btn btn-gradient">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste des produits
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
                    <span class="badge bg-warning ms-2">À examiner</span>
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
                
                <form action="{{ route('admin.products.mark-reviewed', $product) }}" 
                      method="POST" 
                      style="display: inline;">
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
@endsection

@section('scripts')
<script>
// Fonction pour confirmer la suppression
function confirmDelete(productId, productName) {
    $('#productNameToDelete').text(productName);
    $('#deleteForm').attr('action', '/admin/products/' + productId);
    $('#deleteModal').modal('show');
}

// Fonction pour confirmer le marquage de tous les produits
function confirmMarkAll() {
    if (confirm('Êtes-vous sûr de vouloir marquer tous les produits comme examinés ?')) {
        $('#markAllForm').submit();
    }
}

$(document).ready(function() {
    // Animation d'entrée pour les cartes
    $('.product-review-card').each(function(index) {
        $(this).delay(index * 100).animate({
            opacity: 1
        }, 300);
    });
    
    // Effet de feedback lors du clic sur les boutons d'action
    $('.btn-approve').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Approbation...');
    });
    
    $('.btn-edit').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Redirection...');
    });
    
    // Raccourcis clavier
    $(document).on('keydown', function(e) {
        // Ctrl + A pour marquer tout comme examiné
        if (e.ctrlKey && e.key === 'a' && {{ $products->count() }} > 0) {
            e.preventDefault();
            confirmMarkAll();
        }
    });
});

// Auto-refresh si aucun produit (pour détecter de nouveaux produits)
@if($products->count() === 0)
setTimeout(function() {
    location.reload();
}, 30000); // Refresh toutes les 30 secondes
@endif
</script>
@endsection