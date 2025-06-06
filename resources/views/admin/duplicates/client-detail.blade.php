@extends('layouts.admin')

@section('title', 'Détail Client - Commandes Doubles')

@section('css')
<style>
    .client-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .client-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .order-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 2px solid transparent;
    }

    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .order-card.duplicate {
        border-color: #d4a147;
    }

    .order-card.selected {
        border-color: #6366f1;
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.3);
    }

    .order-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 1px solid #e5e7eb;
    }

    .order-body {
        padding: 1.5rem;
    }

    .order-footer {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: between;
        align-items: center;
        gap: 1rem;
    }

    .badge-doublé {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
    }

    .product-list {
        max-height: 150px;
        overflow-y: auto;
        margin: 1rem 0;
    }

    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .selection-toolbar {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        display: none;
    }

    .selection-toolbar.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-select-order {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-select-order.selected {
        background: #6366f1;
        border-color: #6366f1;
        color: white;
    }

    .order-card {
        position: relative;
    }

    .compatibility-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .compatible {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .incompatible {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .timeline-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-top: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
        margin-bottom: 1rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 8px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6366f1;
        border: 2px solid white;
        box-shadow: 0 0 0 3px #e5e7eb;
    }

    .timeline-content {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        margin-left: 1rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 500;
        border: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-merge-selected {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-cancel-selected {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-select-all {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .btn-clear-selection {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/duplicates" class="btn btn-outline-primary mb-2">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
        <h1 class="text-gradient mb-2">
            <i class="fas fa-user me-2"></i>Détail Client - {{ $phone }}
        </h1>
        <p class="text-muted mb-0">Vue détaillée de toutes les commandes du client</p>
    </div>
</div>

<!-- En-tête Client -->
<div class="client-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-phone me-2"></i>{{ $phone }}
            </h2>
            @if($orders->first()->customer_name)
                <p class="mb-0 opacity-75 fs-5">{{ $orders->first()->customer_name }}</p>
            @endif
            @if($orders->first()->customer_address)
                <p class="mb-0 opacity-75">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $orders->first()->customer_address }}
                </p>
            @endif
        </div>
        <div class="col-md-4">
            <div class="client-stats">
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['total_orders'] }}</div>
                    <div class="stat-label">Total Commandes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['duplicate_orders'] }}</div>
                    <div class="stat-label">Commandes Doubles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ number_format($stats['total_spent'], 3) }} TND</div>
                    <div class="stat-label">Total Dépensé</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barre d'outils de sélection -->
<div class="selection-toolbar" id="selectionToolbar">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0">
                <i class="fas fa-check-square me-2"></i>
                <span id="selectedCount">0</span> commande(s) sélectionnée(s)
            </h6>
        </div>
        <div class="action-buttons">
            <button class="btn btn-action btn-select-all" id="btnSelectAll">
                <i class="fas fa-check-square"></i>Tout sélectionner
            </button>
            <button class="btn btn-action btn-clear-selection" id="btnClearSelection">
                <i class="fas fa-times"></i>Effacer sélection
            </button>
            <button class="btn btn-action btn-merge-selected" id="btnMergeSelected">
                <i class="fas fa-compress-arrows-alt"></i>Fusionner sélection
            </button>
            <button class="btn btn-action btn-cancel-selected" id="btnCancelSelected">
                <i class="fas fa-ban"></i>Annuler sélection
            </button>
        </div>
    </div>
</div>

<!-- Grille des Commandes -->
<div class="orders-grid">
    @foreach($orders as $order)
        <div class="order-card {{ $order->is_duplicate ? 'duplicate' : '' }}" data-order-id="{{ $order->id }}">
            <div class="btn-select-order" data-order-id="{{ $order->id }}">
                <i class="fas fa-check" style="display: none;"></i>
            </div>
            
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">Commande #{{ $order->id }}</h6>
                        <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="text-end">
                        @switch($order->status)
                            @case('nouvelle')
                                <span class="badge bg-info">Nouvelle</span>
                                @break
                            @case('confirmée')
                                <span class="badge bg-success">Confirmée</span>
                                @break
                            @case('annulée')
                                <span class="badge bg-danger">Annulée</span>
                                @break
                            @case('datée')
                                <span class="badge bg-warning">Datée</span>
                                @break
                            @case('en_route')
                                <span class="badge bg-primary">En route</span>
                                @break
                            @case('livrée')
                                <span class="badge bg-success">Livrée</span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ $order->status }}</span>
                        @endswitch
                        
                        @if($order->is_duplicate)
                            <span class="badge badge-doublé ms-1">Doublé</span>
                        @endif
                        
                        @if($order->priority !== 'normale')
                            <span class="badge bg-danger ms-1">{{ ucfirst($order->priority) }}</span>
                        @endif
                    </div>
                </div>
                
                @if($order->scheduled_date)
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>Programmée pour: {{ $order->scheduled_date->format('d/m/Y') }}
                        </small>
                    </div>
                @endif
            </div>
            
            <div class="order-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Client:</strong><br>
                        <small>{{ $order->customer_name ?: 'N/A' }}</small>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Montant:</strong><br>
                        <span class="text-success fw-bold">{{ number_format($order->total_price, 3) }} TND</span>
                    </div>
                </div>
                
                @if($order->customer_address)
                    <div class="mb-3">
                        <strong>Adresse:</strong><br>
                        <small class="text-muted">{{ $order->customer_address }}</small>
                    </div>
                @endif
                
                <div class="mb-3">
                    <strong>Produits:</strong>
                    <div class="product-list">
                        @foreach($order->items as $item)
                            <div class="product-item">
                                <span>{{ $item->product->name ?? 'Produit supprimé' }}</span>
                                <span class="badge bg-light text-dark">{{ $item->quantity }}x</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                @if($order->notes)
                    <div class="mb-3">
                        <strong>Notes:</strong><br>
                        <small class="text-muted">{{ Str::limit($order->notes, 100) }}</small>
                    </div>
                @endif
                
                <!-- Indicateur de compatibilité pour fusion -->
                @if($order->is_duplicate && in_array($order->status, ['nouvelle', 'datée']))
                    <div class="compatibility-indicator compatible">
                        <i class="fas fa-check"></i>Fusionnable
                    </div>
                @elseif($order->is_duplicate)
                    <div class="compatibility-indicator incompatible">
                        <i class="fas fa-times"></i>Non fusionnable
                    </div>
                @endif
            </div>
            
            <div class="order-footer">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">Tentatives: {{ $order->attempts_count }}</span>
                    @if($order->last_attempt_at)
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>{{ $order->last_attempt_at->diffForHumans() }}
                        </small>
                    @endif
                </div>
                
                <a href="{{ route('admin.orders.show', $order->id) }}" 
                   class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        </div>
    @endforeach
</div>

<!-- Timeline des Actions -->
<div class="timeline-section">
    <h5 class="mb-4">
        <i class="fas fa-history me-2"></i>Historique des Actions
    </h5>
    
    <div class="timeline">
        @php
            $allHistory = collect();
            foreach($orders as $order) {
                foreach($order->history as $history) {
                    $history->order_id = $order->id;
                    $allHistory->push($history);
                }
            }
            $allHistory = $allHistory->sortByDesc('created_at');
        @endphp
        
        @forelse($allHistory as $history)
            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">{{ ucfirst(str_replace('_', ' ', $history->action)) }}</h6>
                        <small class="text-muted">{{ $history->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    
                    <p class="mb-2">
                        <strong>Commande #{{ $history->order_id }}</strong>
                        @if($history->notes)
                            <br>{{ $history->notes }}
                        @endif
                    </p>
                    
                    @if($history->user_type && $history->user_id)
                        <small class="text-muted">
                            Par: {{ $history->getUserName() }} ({{ $history->user_type }})
                        </small>
                    @endif
                    
                    @if($history->status_before && $history->status_after)
                        <div class="mt-2">
                            <span class="badge bg-secondary">{{ $history->status_before }}</span>
                            <i class="fas fa-arrow-right mx-2"></i>
                            <span class="badge bg-primary">{{ $history->status_after }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-muted">
                <i class="fas fa-history fa-2x mb-3"></i>
                <p>Aucun historique d'actions disponible</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Fusion Sélective -->
<div class="modal fade" id="selectiveMergeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-compress-arrows-alt me-2"></i>Fusion Sélective
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Attention:</strong> Cette action va fusionner les commandes sélectionnées. 
                    Assurez-vous qu'elles sont compatibles (même statut ou nouvelle/datée).
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Note de fusion:</label>
                    <textarea class="form-control" id="selectiveMergeNote" rows="3" 
                              placeholder="Raison de la fusion sélective..."></textarea>
                </div>
                
                <div id="selectedOrdersPreview">
                    <!-- Liste des commandes sélectionnées -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="btnConfirmSelectiveMerge">
                    <i class="fas fa-check me-2"></i>Confirmer la Fusion
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annulation Groupée -->
<div class="modal fade" id="cancelOrdersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ban me-2"></i>Annuler les Commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Vous êtes sur le point d'annuler <span id="cancelCount">0</span> commande(s). 
                    Cette action est irréversible.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Raison d'annulation:</label>
                    <textarea class="form-control" id="cancelReason" rows="3" 
                              placeholder="Raison de l'annulation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="btnConfirmCancel">
                    <i class="fas fa-ban me-2"></i>Confirmer l'Annulation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p class="mb-0">Traitement en cours...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let selectedOrders = new Set();
    
    // Sélection des commandes
    $('.btn-select-order').click(function(e) {
        e.stopPropagation();
        const orderId = $(this).data('order-id');
        const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
        
        if (selectedOrders.has(orderId)) {
            selectedOrders.delete(orderId);
            $(this).removeClass('selected');
            $(this).find('i').hide();
            orderCard.removeClass('selected');
        } else {
            selectedOrders.add(orderId);
            $(this).addClass('selected');
            $(this).find('i').show();
            orderCard.addClass('selected');
        }
        
        updateSelectionUI();
    });
    
    // Sélectionner tout
    $('#btnSelectAll').click(function() {
        $('.order-card').each(function() {
            const orderId = parseInt($(this).data('order-id'));
            const canMerge = $(this).hasClass('duplicate') && 
                           ($(this).find('.badge').text().includes('Nouvelle') || 
                            $(this).find('.badge').text().includes('Datée'));
            
            if (canMerge && !selectedOrders.has(orderId)) {
                selectedOrders.add(orderId);
                $(this).addClass('selected');
                $(this).find('.btn-select-order').addClass('selected');
                $(this).find('.btn-select-order i').show();
            }
        });
        
        updateSelectionUI();
    });
    
    // Effacer sélection
    $('#btnClearSelection').click(function() {
        selectedOrders.clear();
        $('.order-card').removeClass('selected');
        $('.btn-select-order').removeClass('selected');
        $('.btn-select-order i').hide();
        updateSelectionUI();
    });
    
    // Fusionner sélection
    $('#btnMergeSelected').click(function() {
        if (selectedOrders.size < 2) {
            showError('Veuillez sélectionner au moins 2 commandes');
            return;
        }
        
        const selectedOrdersArray = Array.from(selectedOrders);
        
        // Vérifier la compatibilité
        let compatible = true;
        const statuses = [];
        
        selectedOrdersArray.forEach(orderId => {
            const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
            const badgeText = orderCard.find('.badge').first().text();
            
            if (badgeText.includes('Nouvelle')) {
                statuses.push('nouvelle');
            } else if (badgeText.includes('Datée')) {
                statuses.push('datée');
            } else {
                compatible = false;
            }
        });
        
        if (!compatible) {
            showError('Seules les commandes nouvelles et datées peuvent être fusionnées');
            return;
        }
        
        // Générer l'aperçu
        let preview = '<h6>Commandes à fusionner:</h6><ul class="list-group">';
        selectedOrdersArray.forEach(orderId => {
            const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
            const orderNumber = orderCard.find('h6').text();
            const amount = orderCard.find('.text-success').text();
            
            preview += `<li class="list-group-item d-flex justify-content-between">
                <span>${orderNumber}</span>
                <strong>${amount}</strong>
            </li>`;
        });
        preview += '</ul>';
        
        $('#selectedOrdersPreview').html(preview);
        $('#selectiveMergeModal').modal('show');
    });
    
    // Annuler sélection
    $('#btnCancelSelected').click(function() {
        if (selectedOrders.size === 0) {
            showError('Veuillez sélectionner au moins une commande');
            return;
        }
        
        $('#cancelCount').text(selectedOrders.size);
        $('#cancelOrdersModal').modal('show');
    });
    
    // Confirmer fusion sélective
    $('#btnConfirmSelectiveMerge').click(function() {
        const note = $('#selectiveMergeNote').val();
        const orderIds = Array.from(selectedOrders);
        
        showLoading();
        
        $.post('/admin/duplicates/selective-merge', {
            order_ids: orderIds,
            note: note,
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            $('#selectiveMergeModal').modal('hide');
            
            if (response.success) {
                showSuccess(response.message);
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la fusion sélective');
        });
    });
    
    // Confirmer annulation
    $('#btnConfirmCancel').click(function() {
        const reason = $('#cancelReason').val();
        const orderIds = Array.from(selectedOrders);
        
        if (!reason.trim()) {
            showError('Veuillez indiquer une raison d\'annulation');
            return;
        }
        
        showLoading();
        
        // Annuler chaque commande individuellement
        let completed = 0;
        const total = orderIds.length;
        
        orderIds.forEach(orderId => {
            $.post('/admin/duplicates/cancel', {
                order_id: orderId,
                reason: reason,
                _token: '{{ csrf_token() }}'
            })
            .always(function() {
                completed++;
                if (completed === total) {
                    hideLoading();
                    $('#cancelOrdersModal').modal('hide');
                    showSuccess(`${total} commande(s) annulée(s) avec succès`);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            });
        });
    });
    
    function updateSelectionUI() {
        const count = selectedOrders.size;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#selectionToolbar').addClass('show');
        } else {
            $('#selectionToolbar').removeClass('show');
        }
        
        // Mettre à jour les boutons
        $('#btnMergeSelected').prop('disabled', count < 2);
        $('#btnCancelSelected').prop('disabled', count === 0);
    }
    
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showSuccess(message) {
        $('body').append(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="fas fa-check-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function showError(message) {
        $('body').append(`
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="fas fa-exclamation-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endsection