@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('css')
<style>
    .status-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
        cursor: pointer;
        text-decoration: none !important;
        display: block;
        position: relative;
        overflow: hidden;
    }

    .status-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--status-color);
    }

    .status-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12);
        border-color: var(--status-color);
    }

    .status-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .status-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        background: var(--status-color);
        color: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .status-content {
        flex: 1;
    }

    .status-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1;
    }

    .status-footer {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--status-color);
        font-size: 0.875rem;
        font-weight: 600;
    }

    /* Status Colors */
    .status-nouvelle { --status-color: #3b82f6; }
    .status-confirmee { --status-color: #10b981; }
    .status-datee { --status-color: #f59e0b; }
    .status-livree { --status-color: #8b5cf6; }
    .status-annulee { --status-color: #ef4444; }
    .status-total { --status-color: #6366f1; }
    .status-today { --status-color: #06b6d4; }

    .grid-status {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Quick Stats */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-stat-card {
        background: linear-gradient(135deg, var(--start-color), var(--end-color));
        border-radius: 12px;
        padding: 1.25rem;
        color: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .quick-stat-card .label {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .quick-stat-card .value {
        font-size: 1.875rem;
        font-weight: 700;
    }

    .stat-products { --start-color: #667eea; --end-color: #764ba2; }
    .stat-stock { --start-color: #f093fb; --end-color: #f5576c; }
    .stat-users { --start-color: #4facfe; --end-color: #00f2fe; }
    .stat-active { --start-color: #43e97b; --end-color: #38f9d7; }

    @media (max-width: 768px) {
        .grid-status {
            grid-template-columns: 1fr;
        }

        .status-value {
            font-size: 2rem;
        }

        .status-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Welcome Message -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark">Bonjour, {{ auth('admin')->user()->name }}!</h3>
        <p class="text-muted">Voici un aperçu de vos commandes</p>
    </div>

    <!-- Order Status Cards -->
    <div class="grid-status">
        <!-- Total Orders -->
        <a href="{{ route('admin.orders.index') }}" class="status-card status-total">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Total Commandes</div>
                    <div class="status-value">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Voir toutes les commandes</span>
            </div>
        </a>

        <!-- Today's Orders -->
        <a href="{{ route('admin.orders.index', ['date_from' => now()->format('Y-m-d')]) }}" class="status-card status-today">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Aujourd'hui</div>
                    <div class="status-value">{{ number_format($stats['orders_today'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Commandes du jour</span>
            </div>
        </a>

        <!-- Nouvelle Orders -->
        <a href="{{ route('admin.orders.index', ['status' => 'nouvelle']) }}" class="status-card status-nouvelle">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Nouvelles</div>
                    <div class="status-value">{{ number_format($ordersByStatus['nouvelle'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Commandes nouvelles</span>
            </div>
        </a>

        <!-- Confirmée Orders -->
        <a href="{{ route('admin.orders.index', ['status' => 'confirmée']) }}" class="status-card status-confirmee">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Confirmées</div>
                    <div class="status-value">{{ number_format($ordersByStatus['confirmée'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Commandes confirmées</span>
            </div>
        </a>

        <!-- Datée Orders -->
        <a href="{{ route('admin.orders.index', ['status' => 'datée']) }}" class="status-card status-datee">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Datées</div>
                    <div class="status-value">{{ number_format($ordersByStatus['datée'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Commandes datées</span>
            </div>
        </a>

        <!-- Livrée Orders -->
        <a href="{{ route('admin.orders.index', ['status' => 'livrée']) }}" class="status-card status-livree">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Livrées</div>
                    <div class="status-value">{{ number_format($ordersByStatus['livrée'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Commandes livrées</span>
            </div>
        </a>

        <!-- Annulée Orders -->
        <a href="{{ route('admin.orders.index', ['status' => 'annulée']) }}" class="status-card status-annulee">
            <div class="status-card-header">
                <div class="status-content">
                    <div class="status-label">Annulées</div>
                    <div class="status-value">{{ number_format($ordersByStatus['annulée'] ?? 0) }}</div>
                </div>
                <div class="status-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="status-footer">
                <i class="fas fa-arrow-right"></i>
                <span>Commandes annulées</span>
            </div>
        </a>
    </div>

    @if(auth('admin')->user()->role === \App\Models\Admin::ROLE_ADMIN)
    <!-- Quick Stats (Admin Only) -->
    <div class="quick-stats">
        <div class="quick-stat-card stat-products">
            <div class="label"><i class="fas fa-box me-2"></i>Produits</div>
            <div class="value">{{ number_format($stats['total_products'] ?? 0) }}</div>
            <small>{{ number_format($stats['active_products'] ?? 0) }} actifs</small>
        </div>

        <div class="quick-stat-card stat-stock">
            <div class="label"><i class="fas fa-exclamation-triangle me-2"></i>Alertes Stock</div>
            <div class="value">{{ number_format(($stats['low_stock_products'] ?? 0) + ($stats['out_of_stock_products'] ?? 0)) }}</div>
            <small>{{ $stats['low_stock_products'] ?? 0 }} faibles / {{ $stats['out_of_stock_products'] ?? 0 }} rupture</small>
        </div>

        <div class="quick-stat-card stat-users">
            <div class="label"><i class="fas fa-users me-2"></i>Employés</div>
            <div class="value">{{ number_format($stats['total_employees'] ?? 0) }}</div>
            <small>{{ number_format($stats['active_employees'] ?? 0) }} actifs</small>
        </div>

        <div class="quick-stat-card stat-active">
            <div class="label"><i class="fas fa-user-tie me-2"></i>Managers</div>
            <div class="value">{{ number_format($stats['total_managers'] ?? 0) }}</div>
            <small>{{ number_format($stats['active_managers'] ?? 0) }} actifs</small>
        </div>
    </div>
    @endif

    <!-- Recent Orders Table -->
    <div class="content-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-clock text-primary me-2"></i>
                Dernières Commandes
            </h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list me-2"></i>Voir tout
            </a>
        </div>

        @if(isset($recentOrders) && $recentOrders->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->customer_name ?? 'N/A' }}</td>
                        <td><a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a></td>
                        <td>
                            <span class="badge rounded-pill
                                @if($order->status === 'nouvelle') bg-primary
                                @elseif($order->status === 'confirmée') bg-success
                                @elseif($order->status === 'datée') bg-warning
                                @elseif($order->status === 'livrée') bg-info
                                @elseif($order->status === 'annulée') bg-danger
                                @else bg-secondary
                                @endif
                            ">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td><strong>{{ number_format($order->total_price, 3) }} TND</strong></td>
                        <td><small>{{ $order->created_at->diffForHumans() }}</small></td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
            <p>Aucune commande récente</p>
        </div>
        @endif
    </div>
</div>
@endsection
