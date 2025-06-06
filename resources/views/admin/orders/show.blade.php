@extends('layouts.admin')

@section('title', 'Commande #' . $order->id)

@section('css')
<style>
    .order-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .order-status-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }

    .status-timeline {
        display: flex;
        justify-content: space-between;
        margin: 2rem 0;
        position: relative;
    }

    .status-timeline::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background: #e5e7eb;
        z-index: 1;
    }

    .timeline-step {
        background: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #e5e7eb;
        position: relative;
        z-index: 2;
        font-size: 0.9rem;
    }

    .timeline-step.active {
        border-color: #10b981;
        background: #10b981;
        color: white;
    }

    .timeline-step.completed {
        border-color: #6366f1;
        background: #6366f1;
        color: white;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .info-card h6 {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        border-bottom: 2px solid #f3f4f6;
        padding-bottom: 0.5rem;
    }

    .products-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .products-table .table {
        margin: 0;
    }

    .products-table .table thead th {
        background: #f8fafc;
        border: none;
        font-weight: 600;
        color: #374151;
        padding: 1rem;
    }

    .products-table .table tbody td {
        border: none;
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .priority-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        border: none;
    }

    .priority-normale {
        background: #e5e7eb;
        color: #374151;
    }

    .priority-urgente {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .priority-vip {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }

    .priority-doublé {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: white;
    }

    .history-timeline {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .history-item {
        position: relative;
        padding-left: 3rem;
        margin-bottom: 1.5rem;
    }

    .history-item::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 8px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6366f1;
        border: 2px solid white;
        box-shadow: 0 0 0 3px #e5e7eb;
    }

    .history-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 24px;
        bottom: -24px;
        width: 2px;
        background: #e5e7eb;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
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

    .duplicate-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(212, 161, 71, 0.1);
        border: 1px solid rgba(212, 161, 71, 0.3);
        border-radius: 12px;
        font-size: 0.875rem;
        color: #b8941f;
        font-weight: 500;
        margin-top: 1rem;
    }

    .duplicate-indicator i {
        color: #d4a147;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .status-timeline {
            flex-direction: column;
            gap: 1rem;
        }
        
        .status-timeline::before {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary mb-2">
            <i class="fas fa-arrow-left me-2"></i>Retour aux commandes
        </a>
        <h1 class="text-gradient mb-2">
            <i class="fas fa-shopping-basket me-2"></i>Commande #{{ $order->id }}
        </h1>
        <p class="text-muted mb-0">Détails complets de la commande</p>
    </div>
</div>

<!-- En-tête de la commande -->
<div class="order-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3 mb-3">
                <h2 class="mb-0">Commande #{{ $order->id }}</h2>
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
                        <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                @endswitch

                <span class="priority-badge priority-{{ $order->priority }}">
                    {{ ucfirst($order->priority) }}
                </span>
            </div>
            
            <p class="mb-1 opacity-75">
                <i class="fas fa-calendar me-2"></i>Créée le {{ $order->created_at->format('d/m/Y à H:i') }}
            </p>
            
            @if($order->scheduled_date)
                <p class="mb-0 opacity-75">
                    <i class="fas fa-clock me-2"></i>Programmée pour le {{ $order->scheduled_date->format('d/m/Y') }}
                </p>
            @endif
        </div>
        <div class="col-md-4 text-end">
            <div class="h3 mb-1">{{ number_format($order->total_price, 3) }} TND</div>
            <small class="opacity-75">Montant total</small>
        </div>
    </div>

    @if($order->is_duplicate)
        <div class="duplicate-indicator">
            <i class="fas fa-copy"></i>
            Cette commande fait partie d'un groupe de doublons
        </div>
    @endif
</div>

<!-- Grille d'informations -->
<div class="info-grid">
    <!-- Informations client -->
    <div class="info-card">
        <h6><i class="fas fa-user me-2"></i>Informations Client</h6>
        <div class="mb-3">
            <strong>Nom :</strong><br>
            {{ $order->customer_name ?: 'Non spécifié' }}
        </div>
        <div class="mb-3">
            <strong>Téléphone :</strong><br>
            {{ $order->customer_phone }}
            @if($order->customer_phone_2)
                <br><small class="text-muted">Tél. 2: {{ $order->customer_phone_2 }}</small>
            @endif
        </div>
        @if($order->customer_email)
            <div class="mb-3">
                <strong>Email :</strong><br>
                {{ $order->customer_email }}
            </div>
        @endif
        @if($order->customer_address)
            <div>
                <strong>Adresse :</strong><br>
                {{ $order->customer_address }}
                @if($order->customer_city || $order->customer_governorate)
                    <br><small class="text-muted">
                        {{ $order->customer_city }}
                        @if($order->customer_city && $order->customer_governorate), @endif
                        {{ $order->customer_governorate }}
                    </small>
                @endif
            </div>
        @endif
    </div>

    <!-- Statut et suivi -->
    <div class="info-card">
        <h6><i class="fas fa-truck me-2"></i>Statut et Suivi</h6>
        <div class="mb-3">
            <strong>Statut actuel :</strong><br>
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
            @endswitch
        </div>
        
        <div class="mb-3">
            <strong>Tentatives :</strong><br>
            {{ $order->attempts_count }} tentative(s) au total
            <br><small class="text-muted">{{ $order->daily_attempts_count }} aujourd'hui</small>
        </div>
        
        @if($order->last_attempt_at)
            <div class="mb-3">
                <strong>Dernière tentative :</strong><br>
                {{ $order->last_attempt_at->format('d/m/Y à H:i') }}
                <br><small class="text-muted">{{ $order->last_attempt_at->diffForHumans() }}</small>
            </div>
        @endif

        @if($order->is_assigned && $order->employee)
            <div>
                <strong>Assignée à :</strong><br>
                {{ $order->employee->name }}
            </div>
        @endif
    </div>

    <!-- Informations financières -->
    <div class="info-card">
        <h6><i class="fas fa-credit-card me-2"></i>Informations Financières</h6>
        <div class="mb-3">
            <strong>Total produits :</strong><br>
            {{ number_format($order->items->sum('total_price'), 3) }} TND
        </div>
        @if($order->shipping_cost > 0)
            <div class="mb-3">
                <strong>Frais de livraison :</strong><br>
                {{ number_format($order->shipping_cost, 3) }} TND
            </div>
        @endif
        <div class="mb-3">
            <strong>Total commande :</strong><br>
            <span class="h5 text-success">{{ number_format($order->total_price, 3) }} TND</span>
        </div>
        @if($order->confirmed_price)
            <div>
                <strong>Prix confirmé :</strong><br>
                {{ number_format($order->confirmed_price, 3) }} TND
            </div>
        @endif
    </div>

    <!-- Informations techniques -->
    <div class="info-card">
        <h6><i class="fas fa-cog me-2"></i>Informations Techniques</h6>
        @if($order->external_id && $order->external_source)
            <div class="mb-3">
                <strong>Source externe :</strong><br>
                {{ $order->external_source }} (ID: {{ $order->external_id }})
            </div>
        @endif
        
        @if($order->is_suspended)
            <div class="mb-3">
                <strong>État :</strong><br>
                <span class="badge bg-warning">Suspendue</span>
                @if($order->suspension_reason)
                    <br><small class="text-muted">{{ $order->suspension_reason }}</small>
                @endif
            </div>
        @endif

        <div class="mb-3">
            <strong>Priorité :</strong><br>
            <span class="priority-badge priority-{{ $order->priority }}">
                {{ ucfirst($order->priority) }}
            </span>
        </div>

        <div>
            <strong>Dernière mise à jour :</strong><br>
            {{ $order->updated_at->format('d/m/Y à H:i') }}
            <br><small class="text-muted">{{ $order->updated_at->diffForHumans() }}</small>
        </div>
    </div>
</div>

<!-- Liste des produits -->
<div class="products-table">
    <div class="d-flex justify-content-between align-items-center p-3 bg-light">
        <h5 class="mb-0">
            <i class="fas fa-box me-2"></i>Produits commandés ({{ $order->items->count() }})
        </h5>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name ?? 'Produit supprimé' }}</strong>
                            @if($item->product && $item->product->description)
                                <br><small class="text-muted">{{ Str::limit($item->product->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ number_format($item->unit_price, 3) }} TND</td>
                        <td>
                            <span class="badge bg-primary">{{ $item->quantity }}</span>
                        </td>
                        <td>
                            <strong>{{ number_format($item->total_price, 3) }} TND</strong>
                        </td>
                        <td>
                            @if($item->product)
                                @if($item->product->stock >= $item->quantity)
                                    <span class="badge bg-success">{{ $item->product->stock }} en stock</span>
                                @else
                                    <span class="badge bg-danger">{{ $item->product->stock }} en stock</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th colspan="3">Total</th>
                    <th>{{ number_format($order->items->sum('total_price'), 3) }} TND</th>
                    <th></th>
                </tr>
                @if($order->shipping_cost > 0)
                    <tr class="table-light">
                        <th colspan="3">Frais de livraison</th>
                        <th>{{ number_format($order->shipping_cost, 3) }} TND</th>
                        <th></th>
                    </tr>
                    <tr class="table-primary">
                        <th colspan="3">Total à payer</th>
                        <th>{{ number_format($order->total_price, 3) }} TND</th>
                        <th></th>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>

<!-- Notes -->
@if($order->notes)
    <div class="info-card">
        <h6><i class="fas fa-sticky-note me-2"></i>Notes</h6>
        <div class="bg-light p-3 rounded">
            {!! nl2br(e($order->notes)) !!}
        </div>
    </div>
@endif

<!-- Historique -->
<div class="history-timeline">
    <h5 class="mb-4">
        <i class="fas fa-history me-2"></i>Historique des actions ({{ $order->history->count() }})
    </h5>
    
    @forelse($order->history->sortByDesc('created_at') as $history)
        <div class="history-item">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $history->action)) }}</h6>
                    @if($history->notes)
                        <p class="mb-2 text-muted">{{ $history->notes }}</p>
                    @endif
                    @if($history->status_before && $history->status_after)
                        <div class="mb-2">
                            <span class="badge bg-secondary">{{ $history->status_before }}</span>
                            <i class="fas fa-arrow-right mx-2"></i>
                            <span class="badge bg-primary">{{ $history->status_after }}</span>
                        </div>
                    @endif
                    <small class="text-muted">
                        Par: {{ $history->getUserName() }} ({{ $history->user_type }})
                    </small>
                </div>
                <small class="text-muted">{{ $history->created_at->format('d/m/Y H:i') }}</small>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            <i class="fas fa-history fa-2x mb-3"></i>
            <p>Aucun historique disponible</p>
        </div>
    @endforelse
</div>

<!-- Actions -->
<div class="action-buttons">
    <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-action btn-primary">
        <i class="fas fa-edit"></i>Modifier
    </a>
    
    @if($order->status === 'nouvelle')
        <button class="btn btn-action btn-success" onclick="confirmOrder({{ $order->id }})">
            <i class="fas fa-check"></i>Confirmer
        </button>
    @endif
    
    @if(in_array($order->status, ['nouvelle', 'confirmée']))
        <button class="btn btn-action btn-warning" onclick="scheduleOrder({{ $order->id }})">
            <i class="fas fa-calendar"></i>Programmer
        </button>
        
        <button class="btn btn-action btn-danger" onclick="cancelOrder({{ $order->id }})">
            <i class="fas fa-times"></i>Annuler
        </button>
    @endif
    
    @if($order->is_duplicate)
        <a href="/admin/duplicates/detail/{{ $order->customer_phone }}" class="btn btn-action" 
           style="background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%); color: white;">
            <i class="fas fa-copy"></i>Voir doublons
        </a>
    @endif
    
    <button class="btn btn-action btn-info" onclick="printOrder({{ $order->id }})">
        <i class="fas fa-print"></i>Imprimer
    </button>
</div>
@endsection

@section('scripts')
<script>
function confirmOrder(orderId) {
    if (confirm('Confirmer cette commande ?')) {
        // Logique de confirmation
        window.location.href = `/admin/orders/${orderId}/confirm`;
    }
}

function scheduleOrder(orderId) {
    const date = prompt('Date de programmation (YYYY-MM-DD):');
    if (date) {
        // Logique de programmation
        window.location.href = `/admin/orders/${orderId}/schedule?date=${date}`;
    }
}

function cancelOrder(orderId) {
    const reason = prompt('Raison d\'annulation:');
    if (reason) {
        // Logique d'annulation
        window.location.href = `/admin/orders/${orderId}/cancel?reason=${reason}`;
    }
}

function printOrder(orderId) {
    window.open(`/admin/orders/${orderId}/print`, '_blank');
}
</script>
@endsection