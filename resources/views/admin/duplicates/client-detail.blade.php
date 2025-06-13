@extends('layouts.admin')

@section('title', 'Détails des Doublons - ' . $phone)

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
<style>
    :root {
        --primary: #6366f1;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --doublons: #d4a147;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-800: #1f2937;
        --white: #ffffff;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --border-radius: 12px;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .page-header {
        background: linear-gradient(135deg, var(--doublons) 0%, #b8941f 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .phone-display {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        text-align: center;
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-icon.orders { background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); }
    .stat-icon.duplicates { background: linear-gradient(135deg, var(--doublons) 0%, #b8941f 100%); }
    .stat-icon.mergeable { background: linear-gradient(135deg, var(--success) 0%, #059669 100%); }
    .stat-icon.non-mergeable { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); }
    .stat-icon.money { background: linear-gradient(135deg, var(--success) 0%, #059669 100%); }
    .stat-icon.cancelled { background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.875rem;
        font-weight: 500;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .orders-section, .analysis-section {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .order-card {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-100);
        transition: background 0.2s ease;
    }

    .order-card:hover {
        background: #f8fafc;
    }

    .order-card:last-child {
        border-bottom: none;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .order-info h4 {
        margin: 0 0 0.5rem 0;
        color: var(--gray-800);
        font-size: 1.125rem;
    }

    .order-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-nouvelle { background: rgba(59, 130, 246, 0.1); color: var(--info); }
    .status-confirmée { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .status-annulée { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .status-datée { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .status-en_route { background: rgba(6, 182, 212, 0.1); color: #06b6d4; }
    .status-livrée { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .priority-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    .priority-normale { background: var(--gray-200); color: var(--gray-600); }
    .priority-urgente { background: rgba(245, 158, 11, 0.2); color: var(--warning); }
    .priority-vip { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }
    .priority-doublons { background: rgba(212, 161, 71, 0.2); color: var(--doublons); }

    .duplicate-indicator {
        background: rgba(212, 161, 71, 0.1);
        border: 1px solid rgba(212, 161, 71, 0.3);
        color: var(--doublons);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }

    .mergeable-indicator {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: var(--success);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }

    .non-mergeable-indicator {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: var(--danger);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }

    .products-list {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--gray-200);
        font-size: 0.875rem;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-name {
        font-weight: 500;
        color: var(--gray-800);
    }

    .product-quantity {
        color: var(--gray-600);
        margin: 0 0.5rem;
    }

    .product-price {
        font-weight: 600;
        color: var(--success);
        font-family: monospace;
    }

    .order-notes {
        background: #fffbeb;
        border: 1px solid #fbbf24;
        border-radius: 6px;
        padding: 0.75rem;
        margin-top: 1rem;
        font-size: 0.875rem;
        color: #92400e;
    }

    .order-notes .notes-icon {
        color: #f59e0b;
        margin-right: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: none;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }

    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-warning { background: var(--warning); color: white; }
    .btn-danger { background: var(--danger); color: white; }
    .btn-outline { background: white; color: var(--gray-600); border: 1px solid var(--gray-300); }

    .top-products {
        padding: 1.5rem;
    }

    .product-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .product-stat:last-child {
        border-bottom: none;
    }

    .product-stat-name {
        font-weight: 500;
        color: var(--gray-800);
        font-size: 0.875rem;
    }

    .product-stat-value {
        font-size: 0.75rem;
        color: var(--gray-600);
    }

    .merge-summary {
        background: #f0f9ff;
        border: 1px solid #0ea5e9;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .merge-summary h5 {
        color: #0369a1;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--gray-200);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
        background: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: var(--shadow);
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 1rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid white;
        box-shadow: 0 0 0 2px var(--gray-200);
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .timeline-action {
        font-weight: 600;
        color: var(--gray-800);
    }

    .timeline-date {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .timeline-notes {
        color: var(--gray-600);
        font-size: 0.875rem;
        line-height: 1.4;
    }

    .bulk-actions {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }

    .bulk-actions h4 {
        margin: 0 0 1rem 0;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .bulk-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .selected-orders {
        background: #f0f9ff;
        border: 1px solid #0ea5e9;
        border-radius: 6px;
        padding: 0.75rem;
        margin: 1rem 0;
        font-size: 0.875rem;
        color: #0369a1;
    }

    /* Status breakdown */
    .status-breakdown {
        padding: 1.5rem;
        border-top: 1px solid var(--gray-200);
    }

    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .status-item:last-child {
        border-bottom: none;
    }

    .status-name {
        font-weight: 500;
        color: var(--gray-800);
        font-size: 0.875rem;
    }

    .status-stats {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        font-size: 0.75rem;
        color: var(--gray-600);
    }

    .status-count {
        font-weight: 600;
        color: var(--gray-800);
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .action-buttons {
            justify-content: center;
        }
        
        .bulk-buttons {
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 1rem;">

    <!-- En-tête -->
    <div class="page-header">
        <div class="header-content">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <a href="{{ route('admin.duplicates.index') }}" class="btn btn-outline" style="background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.3); color: white; margin-bottom: 1rem;">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                    <div class="phone-display">
                        <i class="fas fa-phone"></i>
                        {{ $phone }}
                    </div>
                    <p style="margin: 0; opacity: 0.8;">
                        <i class="fas fa-copy me-2"></i>
                        Gestion détaillée des commandes doubles (tous statuts)
                    </p>
                </div>
                <div class="text-end">
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $stats['total_orders'] }}</div>
                    <div style="opacity: 0.8; font-size: 0.875rem;">Commandes trouvées</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques étendues -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-number">{{ $stats['total_orders'] }}</div>
            <div class="stat-label">Total Commandes</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon duplicates">
                <i class="fas fa-copy"></i>
            </div>
            <div class="stat-number">{{ $stats['duplicate_orders'] }}</div>
            <div class="stat-label">Doublons Détectés</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon mergeable">
                <i class="fas fa-compress-arrows-alt"></i>
            </div>
            <div class="stat-number">{{ $stats['mergeable_duplicates'] ?? 0 }}</div>
            <div class="stat-label">Doublons Fusionnables</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon non-mergeable">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-number">{{ $stats['non_mergeable_duplicates'] ?? 0 }}</div>
            <div class="stat-label">Non-Fusionnables</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon money">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-number">{{ number_format($stats['total_spent'], 0) }}</div>
            <div class="stat-label">TND Dépensés</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon cancelled">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-number">{{ $stats['cancelled_orders'] }}</div>
            <div class="stat-label">Annulées</div>
        </div>
    </div>

    <!-- Actions groupées - Mise à jour pour ne traiter que les fusionnables -->
    @php
        $mergeableOrders = $orders->whereIn('status', ['nouvelle', 'datée'])->where('is_duplicate', true)->where('reviewed_for_duplicates', false);
    @endphp
    
    @if($mergeableOrders->count() > 1)
    <div class="bulk-actions">
        <h4>
            <i class="fas fa-tasks"></i>
            Actions Groupées
        </h4>
        <div class="alert" style="background: rgba(59, 130, 246, 0.1); color: #1e40af; border: 1px solid rgba(59, 130, 246, 0.3); margin-bottom: 1rem;">
            <i class="fas fa-info-circle"></i>
            Seules les commandes "nouvelle" et "datée" peuvent être fusionnées. Les autres statuts seront marqués comme examinés.
        </div>
        <div class="bulk-buttons">
            <button class="btn btn-success" onclick="mergeSelectedOrders()">
                <i class="fas fa-compress-arrows-alt"></i>
                Fusionner les Sélectionnées ({{ $mergeableOrders->count() }} éligibles)
            </button>
            <button class="btn btn-warning" onclick="markSelectedAsReviewed()">
                <i class="fas fa-check"></i>
                Marquer Toutes comme Examinées
            </button>
            <button class="btn btn-danger" onclick="cancelSelectedOrders()">
                <i class="fas fa-times"></i>
                Annuler les Sélectionnées
            </button>
        </div>
        <div class="selected-orders" id="selected-summary" style="display: none;">
            <i class="fas fa-info-circle"></i>
            <span id="selected-count">0</span> commande(s) sélectionnée(s)
        </div>
    </div>
    @endif

    <!-- Contenu principal -->
    <div class="content-grid">
        <!-- Liste des commandes -->
        <div class="orders-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-list"></i>
                    Commandes ({{ $orders->count() }})
                </h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline btn-sm" onclick="selectAllDuplicates()">
                        <i class="fas fa-check-square"></i>
                        Sélectionner tous les doublons
                    </button>
                </div>
            </div>

            @foreach($orders as $order)
            <div class="order-card" data-order-id="{{ $order->id }}">
                <div class="order-header">
                    <div class="order-info">
                        <h4>
                            @if($order->is_duplicate && !$order->reviewed_for_duplicates)
                                <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" style="margin-right: 0.5rem;">
                            @endif
                            Commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                            <span class="status-badge status-{{ $order->status }}">
                                {{ ucfirst($order->status) }}
                            </span>
                            <span class="priority-badge priority-{{ $order->priority }}">
                                {{ ucfirst($order->priority) }}
                            </span>
                        </h4>
                        
                        @if($order->is_duplicate)
                            <div class="duplicate-indicator">
                                <i class="fas fa-copy"></i>
                                @if($order->reviewed_for_duplicates)
                                    Doublon examiné
                                @else
                                    Doublon en attente d'examen
                                @endif
                            </div>
                            
                            {{-- Indicateur de fusionnabilité --}}
                            @if(in_array($order->status, ['nouvelle', 'datée']))
                                <div class="mergeable-indicator">
                                    <i class="fas fa-compress-arrows-alt"></i>
                                    Commande fusionnable
                                </div>
                            @else
                                <div class="non-mergeable-indicator">
                                    <i class="fas fa-ban"></i>
                                    Non fusionnable (statut: {{ $order->status }})
                                </div>
                            @endif
                        @endif
                    </div>
                    
                    <div class="text-end">
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--success);">
                            {{ number_format($order->total_price, 3) }} TND
                        </div>
                        <div style="font-size: 0.75rem; color: var(--gray-600);">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                <div class="order-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        {{ $order->customer_name ?: 'Nom non spécifié' }}
                    </div>
                    @if($order->customer_phone_2)
                        <div class="meta-item">
                            <i class="fas fa-phone-alt"></i>
                            {{ $order->customer_phone_2 }}
                        </div>
                    @endif
                    @if($order->customer_address)
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ Str::limit($order->customer_address, 50) }}
                        </div>
                    @endif
                    @if($order->employee)
                        <div class="meta-item">
                            <i class="fas fa-user-tie"></i>
                            {{ $order->employee->name }}
                        </div>
                    @endif
                </div>

                @if($order->items->count() > 0)
                <div class="products-list">
                    <strong style="font-size: 0.875rem; color: var(--gray-800); margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-box"></i>
                        Produits ({{ $order->items->count() }})
                    </strong>
                    @foreach($order->items as $item)
                        <div class="product-item">
                            <span class="product-name">{{ $item->product->name ?? 'Produit supprimé' }}</span>
                            <span class="product-quantity">× {{ $item->quantity }}</span>
                            <span class="product-price">{{ number_format($item->total_price, 3) }} TND</span>
                        </div>
                    @endforeach
                </div>
                @endif

                @if($order->notes)
                <div class="order-notes">
                    <i class="fas fa-sticky-note notes-icon"></i>
                    {{ $order->notes }}
                </div>
                @endif

                <div class="action-buttons">
                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Modifier
                    </a>
                    @if($order->is_duplicate && !$order->reviewed_for_duplicates)
                        <button class="btn btn-success" onclick="markAsReviewed('{{ $order->customer_phone }}')">
                            <i class="fas fa-check"></i>
                            Marquer Examiné
                        </button>
                        @if(in_array($order->status, ['nouvelle', 'datée']))
                            <button class="btn btn-warning" onclick="showMergeInfo('{{ $order->customer_phone }}')">
                                <i class="fas fa-compress-arrows-alt"></i>
                                Fusionner
                            </button>
                        @endif
                        <button class="btn btn-danger" onclick="cancelOrder({{ $order->id }})">
                            <i class="fas fa-times"></i>
                            Annuler
                        </button>
                    @endif
                    <button class="btn btn-outline" onclick="showOrderHistory({{ $order->id }})">
                        <i class="fas fa-history"></i>
                        Historique
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Analyse et produits populaires -->
        <div class="analysis-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Analyse Client
                </h3>
            </div>
            
            <div style="padding: 1.5rem;">
                <h5 style="margin: 0 0 1rem 0; color: var(--gray-800);">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Période d'activité
                </h5>
                <div style="font-size: 0.875rem; color: var(--gray-600); line-height: 1.6;">
                    <div><strong>Première commande :</strong> {{ \Carbon\Carbon::parse($stats['first_order'])->format('d/m/Y') }}</div>
                    <div><strong>Dernière commande :</strong> {{ \Carbon\Carbon::parse($stats['last_order'])->format('d/m/Y') }}</div>
                    <div><strong>Panier moyen :</strong> {{ number_format($stats['avg_order_value'], 2) }} TND</div>
                </div>
            </div>

            {{-- Nouvelle section: Répartition par statut --}}
            @if(isset($stats['status_breakdown']) && !empty($stats['status_breakdown']))
            <div class="status-breakdown">
                <h5 style="margin: 0 0 1rem 0; color: var(--gray-800);">
                    <i class="fas fa-chart-pie me-2"></i>
                    Répartition par statut
                </h5>
                @foreach($stats['status_breakdown'] as $status => $statusInfo)
                    <div class="status-item">
                        <div class="status-name">
                            <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span>
                        </div>
                        <div class="status-stats">
                            <div class="status-count">{{ $statusInfo['count'] }} commande(s)</div>
                            <div>{{ number_format($statusInfo['total_value'], 2) }} TND</div>
                            @if($statusInfo['duplicate_count'] > 0)
                                <div style="color: var(--doublons);">{{ $statusInfo['duplicate_count'] }} doublon(s)</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            @if(!empty($topProducts))
            <div class="top-products">
                <h5 style="margin: 0 0 1rem 0; color: var(--gray-800);">
                    <i class="fas fa-star me-2"></i>
                    Produits les plus commandés
                </h5>
                @foreach($topProducts as $productName => $stats)
                    <div class="product-stat">
                        <div>
                            <div class="product-stat-name">{{ $productName }}</div>
                            <div class="product-stat-value">{{ $stats['orders_count'] }} commande(s)</div>
                        </div>
                        <div class="text-end">
                            <div style="font-weight: 600; color: var(--gray-800);">{{ $stats['quantity'] }}×</div>
                            <div class="product-stat-value">{{ number_format($stats['total_value'], 2) }} TND</div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Résumé des fusions si disponible -->
            @if($orders->where('notes')->count() > 0)
            <div style="padding: 1.5rem; border-top: 1px solid var(--gray-200);">
                <h5 style="margin: 0 0 1rem 0; color: var(--gray-800);">
                    <i class="fas fa-compress-arrows-alt me-2"></i>
                    Historique des fusions
                </h5>
                <div id="merge-history">
                    <!-- Chargé via AJAX -->
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i>
                        Chargement...
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let selectedOrders = new Set();

    // Charger l'historique des fusions
    loadMergeHistory();

    // Gestion des checkboxes
    $('.order-checkbox').change(function() {
        const orderId = $(this).val();
        if ($(this).is(':checked')) {
            selectedOrders.add(orderId);
        } else {
            selectedOrders.delete(orderId);
        }
        updateSelectedSummary();
    });

    function updateSelectedSummary() {
        const count = selectedOrders.size;
        if (count > 0) {
            $('#selected-summary').show();
            $('#selected-count').text(count);
        } else {
            $('#selected-summary').hide();
        }
    }

    function loadMergeHistory() {
        $.get('{{ route("admin.duplicates.history") }}', {
            customer_phone: '{{ $phone }}'
        })
        .done(function(response) {
            if (response.merge_history && response.merge_history.length > 0) {
                let html = '<div class="timeline">';
                response.merge_history.forEach(function(event) {
                    html += `
                        <div class="timeline-item">
                            <div class="timeline-header">
                                <div class="timeline-action">${event.action || 'Fusion'}</div>
                                <div class="timeline-date">${new Date(event.date).toLocaleDateString('fr-FR')}</div>
                            </div>
                            <div class="timeline-notes">${event.notes || event.note || 'Fusion de commandes'}</div>
                        </div>
                    `;
                });
                html += '</div>';
                $('#merge-history').html(html);
            } else {
                $('#merge-history').html('<div class="text-center text-muted">Aucun historique de fusion</div>');
            }
        })
        .fail(function() {
            $('#merge-history').html('<div class="text-center text-danger">Erreur lors du chargement</div>');
        });
    }

    // Fonctions globales
    window.selectAllDuplicates = function() {
        $('.order-checkbox').prop('checked', true).trigger('change');
    };

    window.mergeSelectedOrders = function() {
        // Compter seulement les commandes fusionnables sélectionnées
        const mergeableSelected = Array.from(selectedOrders).filter(orderId => {
            const orderCard = $(`.order-card[data-order-id="${orderId}"]`);
            return orderCard.find('.mergeable-indicator').length > 0;
        });

        if (mergeableSelected.length < 2) {
            alert('Veuillez sélectionner au moins 2 commandes fusionnables (nouvelle/datée) pour la fusion.');
            return;
        }

        if (!confirm(`Fusionner ${mergeableSelected.length} commandes fusionnables sélectionnées ?`)) {
            return;
        }

        const note = prompt('Note pour cette fusion (optionnel):');
        
        $.post('{{ route("admin.duplicates.selective-merge") }}', {
            order_ids: mergeableSelected,
            note: note,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                alert('Fusion réalisée avec succès !');
                location.reload();
            } else {
                alert('Erreur: ' + response.message);
            }
        })
        .fail(function() {
            alert('Erreur lors de la fusion');
        });
    };

    window.markSelectedAsReviewed = function() {
        if (selectedOrders.size === 0) {
            alert('Aucune commande sélectionnée.');
            return;
        }

        if (!confirm(`Marquer ${selectedOrders.size} commande(s) comme examinée(s) ?`)) {
            return;
        }

        $.post('{{ route("admin.duplicates.mark-reviewed") }}', {
            customer_phone: '{{ $phone }}',
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                alert('Commandes marquées comme examinées !');
                location.reload();
            } else {
                alert('Erreur: ' + response.message);
            }
        })
        .fail(function() {
            alert('Erreur lors du marquage');
        });
    };

    window.cancelSelectedOrders = function() {
        if (selectedOrders.size === 0) {
            alert('Aucune commande sélectionnée.');
            return;
        }

        const reason = prompt('Raison de l\'annulation:');
        if (!reason) return;

        if (!confirm(`Annuler ${selectedOrders.size} commande(s) sélectionnée(s) ?`)) {
            return;
        }

        // Annuler chaque commande individuellement
        let completed = 0;
        Array.from(selectedOrders).forEach(orderId => {
            $.post('{{ route("admin.duplicates.cancel") }}', {
                order_id: orderId,
                reason: reason,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .always(() => {
                completed++;
                if (completed === selectedOrders.size) {
                    alert('Commandes annulées !');
                    location.reload();
                }
            });
        });
    };

    window.markAsReviewed = function(phone) {
        if (!confirm('Marquer toutes les commandes de ce client comme examinées ?')) {
            return;
        }

        $.post('{{ route("admin.duplicates.mark-reviewed") }}', {
            customer_phone: phone,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                alert('Commandes marquées comme examinées !');
                location.reload();
            } else {
                alert('Erreur: ' + response.message);
            }
        })
        .fail(function() {
            alert('Erreur lors du marquage');
        });
    };

    window.showMergeInfo = function(phone) {
        // Afficher les détails des commandes fusionnables
        $.get('{{ route("admin.duplicates.history") }}', {
            customer_phone: phone
        })
        .done(function(response) {
            const mergeableOrders = response.orders.filter(order => 
                (order.status === 'nouvelle' || order.status === 'datée') && 
                order.is_duplicate && 
                !order.reviewed_for_duplicates
            );

            if (mergeableOrders.length < 2) {
                alert('Moins de 2 commandes fusionnables trouvées.');
                return;
            }

            let message = `Commandes fusionnables trouvées (${mergeableOrders.length}):\n\n`;
            mergeableOrders.forEach(order => {
                message += `- Commande #${order.id} (${order.status}) - ${parseFloat(order.total_price).toFixed(3)} TND\n`;
            });
            message += '\nVoulez-vous procéder à la fusion ?';

            if (confirm(message)) {
                const note = prompt('Note pour cette fusion (optionnel):');
                
                $.post('{{ route("admin.duplicates.merge") }}', {
                    customer_phone: phone,
                    note: note,
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(response) {
                    if (response.success) {
                        alert('Fusion réalisée avec succès !');
                        location.reload();
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                })
                .fail(function() {
                    alert('Erreur lors de la fusion');
                });
            }
        })
        .fail(function() {
            alert('Erreur lors du chargement des détails');
        });
    };

    window.cancelOrder = function(orderId) {
        const reason = prompt('Raison de l\'annulation:');
        if (!reason) return;

        $.post('{{ route("admin.duplicates.cancel") }}', {
            order_id: orderId,
            reason: reason,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                alert('Commande annulée !');
                location.reload();
            } else {
                alert('Erreur: ' + response.message);
            }
        })
        .fail(function() {
            alert('Erreur lors de l\'annulation');
        });
    };

    window.showOrderHistory = function(orderId) {
        // Implémenter modal d'historique si nécessaire
        window.open(`/admin/orders/${orderId}/history`, '_blank');
    };
});
</script>
@endsection