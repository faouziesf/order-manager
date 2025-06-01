@extends('layouts.admin')

@section('title', 'Gestion des stocks - Vue Kanban')

@section('css')
<style>
    .kanban-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: calc(100vh - 160px);
        padding: 1rem 0;
    }
    
    .kanban-header {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .kanban-column {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        min-height: 400px;
    }
    
    .column-header {
        padding: 1rem;
        font-weight: 600;
        color: white;
        text-align: center;
        position: relative;
    }
    
    .column-header.out-of-stock {
        background: linear-gradient(45deg, #e74c3c, #c0392b);
    }
    
    .column-header.low-stock {
        background: linear-gradient(45deg, #f39c12, #e67e22);
    }
    
    .column-header.normal-stock {
        background: linear-gradient(45deg, #3498db, #2980b9);
    }
    
    .column-header.high-stock {
        background: linear-gradient(45deg, #27ae60, #229954);
    }
    
    .column-count {
        position: absolute;
        top: 0.5rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
    }
    
    .column-body {
        padding: 0.5rem;
        max-height: 600px;
        overflow-y: auto;
    }
    
    .product-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-color: #4e73df;
    }
    
    .product-card:last-child {
        margin-bottom: 0;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .product-placeholder {
        width: 50px;
        height: 50px;
        background: #f7fafc;
        border: 2px dashed #cbd5e0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-details {
        flex: 1;
    }
    
    .product-name {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        color: #2d3748;
    }
    
    .product-price {
        color: #4e73df;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .product-stock {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        margin-top: 0.5rem;
    }
    
    .stock-out {
        background: #fee;
        color: #c53030;
    }
    
    .stock-low {
        background: #fffaf0;
        color: #dd6b20;
    }
    
    .stock-normal {
        background: #ebf8ff;
        color: #3182ce;
    }
    
    .stock-high {
        background: #f0fff4;
        color: #38a169;
    }
    
    .product-actions {
        display: flex;
        gap: 0.25rem;
        margin-top: 0.5rem;
    }
    
    .product-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }
    
    .empty-column {
        text-align: center;
        padding: 3rem 1rem;
        color: #a0aec0;
    }
    
    .empty-column i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .filter-pills {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }
    
    .filter-pill {
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .filter-pill:hover,
    .filter-pill.active {
        background: #4e73df;
        color: white;
        border-color: #4e73df;
        text-decoration: none;
    }
    
    .stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .droppable-zone {
        min-height: 100px;
        border: 2px dashed transparent;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .droppable-zone.drag-over {
        border-color: #4e73df;
        background: rgba(78, 115, 223, 0.1);
    }
    
    @media (max-width: 768px) {
        .kanban-board {
            grid-template-columns: 1fr;
        }
        
        .stats-summary {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filter-pills {
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="kanban-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="kanban-header">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <i class="fas fa-layer-group fa-3x text-primary me-3"></i>
                <div>
                    <h1 class="mb-0">Gestion des Stocks - Vue Kanban</h1>
                    <p class="text-muted mb-0">Organisez vos produits par niveau de stock</p>
                </div>
            </div>
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
                <li class="breadcrumb-item text-white-50">Vue Kanban</li>
            </ol>
        </nav>

        <!-- Actions rapides -->
        <div class="quick-actions">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2 text-warning"></i>Actions Rapides
                </h5>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-table me-1"></i>Vue Liste
                </a>
            </div>
            <div class="filter-pills">
                <a href="{{ route('admin.products.index', ['stock' => 'out_of_stock']) }}" class="filter-pill">
                    <i class="fas fa-times-circle me-1"></i>Gérer ruptures
                </a>
                <a href="{{ route('admin.products.index', ['stock' => 'low_stock']) }}" class="filter-pill">
                    <i class="fas fa-exclamation-triangle me-1"></i>Réapprovisionner
                </a>
                <a href="{{ route('admin.products.create') }}" class="filter-pill">
                    <i class="fas fa-plus me-1"></i>Nouveau produit
                </a>
                <button class="filter-pill" onclick="refreshKanban()">
                    <i class="fas fa-sync me-1"></i>Actualiser
                </button>
            </div>
        </div>

        <!-- Statistiques résumées -->
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-number text-danger">{{ $outOfStock->count() }}</div>
                <div class="stat-label">Ruptures de stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning">{{ $lowStock->count() }}</div>
                <div class="stat-label">Stock faible</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info">{{ $normalStock->count() }}</div>
                <div class="stat-label">Stock normal</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success">{{ $highStock->count() }}</div>
                <div class="stat-label">Stock élevé</div>
            </div>
        </div>

        <!-- Tableau Kanban -->
        <div class="kanban-board">
            <!-- Colonne Rupture de stock -->
            <div class="kanban-column">
                <div class="column-header out-of-stock">
                    <i class="fas fa-times-circle me-2"></i>Rupture de Stock
                    <div class="column-count">{{ $outOfStock->count() }}</div>
                </div>
                <div class="column-body droppable-zone" data-status="out_of_stock">
                    @forelse($outOfStock as $product)
                        <div class="product-card" draggable="true" data-product-id="{{ $product->id }}">
                            <div class="product-info">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-placeholder">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="product-details">
                                    <div class="product-name">{{ Str::limit($product->name, 25) }}</div>
                                    <div class="product-price">{{ number_format($product->price, 3) }} DT</div>
                                </div>
                            </div>
                            
                            <div class="product-stock stock-out">
                                <i class="fas fa-times me-1"></i>{{ $product->stock }} unités
                            </div>
                            
                            <div class="product-actions">
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#quickStockModal{{ $product->id }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-check-circle"></i>
                            <p>Aucune rupture de stock !</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Colonne Stock faible -->
            <div class="kanban-column">
                <div class="column-header low-stock">
                    <i class="fas fa-exclamation-triangle me-2"></i>Stock Faible (1-10)
                    <div class="column-count">{{ $lowStock->count() }}</div>
                </div>
                <div class="column-body droppable-zone" data-status="low_stock">
                    @forelse($lowStock as $product)
                        <div class="product-card" draggable="true" data-product-id="{{ $product->id }}">
                            <div class="product-info">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-placeholder">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="product-details">
                                    <div class="product-name">{{ Str::limit($product->name, 25) }}</div>
                                    <div class="product-price">{{ number_format($product->price, 3) }} DT</div>
                                </div>
                            </div>
                            
                            <div class="product-stock stock-low">
                                <i class="fas fa-exclamation me-1"></i>{{ $product->stock }} unités
                            </div>
                            
                            <div class="product-actions">
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#quickStockModal{{ $product->id }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-smile"></i>
                            <p>Aucun stock faible</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Colonne Stock normal -->
            <div class="kanban-column">
                <div class="column-header normal-stock">
                    <i class="fas fa-check me-2"></i>Stock Normal (11-50)
                    <div class="column-count">{{ $normalStock->count() }}</div>
                </div>
                <div class="column-body droppable-zone" data-status="normal_stock">
                    @forelse($normalStock as $product)
                        <div class="product-card" draggable="true" data-product-id="{{ $product->id }}">
                            <div class="product-info">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-placeholder">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="product-details">
                                    <div class="product-name">{{ Str::limit($product->name, 25) }}</div>
                                    <div class="product-price">{{ number_format($product->price, 3) }} DT</div>
                                </div>
                            </div>
                            
                            <div class="product-stock stock-normal">
                                <i class="fas fa-check me-1"></i>{{ $product->stock }} unités
                            </div>
                            
                            <div class="product-actions">
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#quickStockModal{{ $product->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-box"></i>
                            <p>Aucun produit en stock normal</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Colonne Stock élevé -->
            <div class="kanban-column">
                <div class="column-header high-stock">
                    <i class="fas fa-star me-2"></i>Stock Élevé (50+)
                    <div class="column-count">{{ $highStock->count() }}</div>
                </div>
                <div class="column-body droppable-zone" data-status="high_stock">
                    @forelse($highStock as $product)
                        <div class="product-card" draggable="true" data-product-id="{{ $product->id }}">
                            <div class="product-info">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-placeholder">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="product-details">
                                    <div class="product-name">{{ Str::limit($product->name, 25) }}</div>
                                    <div class="product-price">{{ number_format($product->price, 3) }} DT</div>
                                </div>
                            </div>
                            
                            <div class="product-stock stock-high">
                                <i class="fas fa-star me-1"></i>{{ $product->stock }} unités
                            </div>
                            
                            <div class="product-actions">
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-success btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#quickStockModal{{ $product->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-column">
                            <i class="fas fa-trophy"></i>
                            <p>Aucun produit en stock élevé</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales de modification rapide du stock -->
@foreach([$outOfStock, $lowStock, $normalStock, $highStock] as $collection)
    @foreach($collection as $product)
    <div class="modal fade" id="quickStockModal{{ $product->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Modifier le stock - {{ $product->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Stock actuel</label>
                                <input type="number" class="form-control" value="{{ $product->stock }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nouveau stock</label>
                                <input type="number" name="stock" class="form-control" 
                                       value="{{ $product->stock }}" min="0" required>
                            </div>
                        </div>
                        
                        <!-- Conserver les autres valeurs -->
                        <input type="hidden" name="name" value="{{ $product->name }}">
                        <input type="hidden" name="price" value="{{ $product->price }}">
                        <input type="hidden" name="description" value="{{ $product->description }}">
                        @if($product->is_active)
                            <input type="hidden" name="is_active" value="1">
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endforeach
@endsection

@section('scripts')
<script>
function refreshKanban() {
    window.location.reload();
}

$(document).ready(function() {
    // Fonctionnalité de drag and drop (optionnelle)
    let draggedElement = null;
    
    // Gestion du drag
    $(document).on('dragstart', '.product-card', function(e) {
        draggedElement = this;
        $(this).css('opacity', '0.5');
    });
    
    $(document).on('dragend', '.product-card', function(e) {
        $(this).css('opacity', '1');
        draggedElement = null;
    });
    
    // Gestion du drop
    $(document).on('dragover', '.droppable-zone', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $(document).on('dragleave', '.droppable-zone', function(e) {
        $(this).removeClass('drag-over');
    });
    
    $(document).on('drop', '.droppable-zone', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        if (draggedElement) {
            const productId = $(draggedElement).data('product-id');
            const newStatus = $(this).data('status');
            
            // Ici vous pouvez ajouter la logique pour mettre à jour le stock via AJAX
            // updateProductStock(productId, newStatus);
        }
    });
    
    // Animation d'entrée pour les cartes
    $('.product-card').each(function(index) {
        $(this).delay(index * 50).animate({
            opacity: 1
        }, 300);
    });
});

// Fonction pour mettre à jour le stock via AJAX (optionnelle)
function updateProductStock(productId, newStatus) {
    // Implémenter selon vos besoins
    console.log('Mise à jour du produit', productId, 'vers', newStatus);
}
</script>
@endsection