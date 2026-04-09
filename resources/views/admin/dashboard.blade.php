@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('css')
@include('admin.partials._shared-styles')
<style>
    /* ============= WELCOME BANNER ============= */
    .dash-welcome {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 18px);
        padding: 1.75rem 2rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .dash-welcome::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--info), var(--success));
    }
    .dash-welcome h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 0.25rem;
        letter-spacing: -0.02em;
    }
    .dash-welcome p {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin: 0;
    }

    /* ============= STATUS CARDS ============= */
    .dash-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .dash-status {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 14px);
        padding: 1.25rem;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dash-status::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--sc);
        border-radius: 3px 3px 0 0;
    }
    .dash-status:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .dash-status-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .dash-status-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-sm, 10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: var(--sc);
        background: var(--sc-bg);
        flex-shrink: 0;
    }
    .dash-status-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
    }
    .dash-status-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text);
        line-height: 1;
        letter-spacing: -0.02em;
    }
    .dash-status-link {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--sc);
        margin-top: auto;
    }
    .dash-status-link i { font-size: 0.65rem; transition: transform 0.2s; }
    .dash-status:hover .dash-status-link i { transform: translateX(3px); }

    /* Status theme colors */
    .sc-total   { --sc: var(--primary);  --sc-bg: var(--primary-50, #eef2ff); }
    .sc-today   { --sc: #06b6d4;         --sc-bg: #ecfeff; }
    .sc-new     { --sc: var(--info);     --sc-bg: var(--info-light, #eff6ff); }
    .sc-confirm { --sc: var(--success);  --sc-bg: var(--success-light, #ecfdf5); }
    .sc-dated   { --sc: var(--warning);  --sc-bg: var(--warning-light, #fffbeb); }
    .sc-deliver { --sc: #8b5cf6;         --sc-bg: #f5f3ff; }
    .sc-cancel  { --sc: var(--danger);   --sc-bg: var(--danger-light, #fef2f2); }
    [data-theme="dark"] .sc-today  { --sc-bg: rgba(6,182,212,0.12); }
    [data-theme="dark"] .sc-deliver { --sc-bg: rgba(139,92,246,0.12); }

    /* ============= QUICK STATS ============= */
    .dash-quick {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .dash-qcard {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 14px);
        padding: 1.15rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dash-qcard:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .dash-qicon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm, 10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .dash-qicon.qi-products { background: var(--primary-50, #eef2ff); color: var(--primary); }
    .dash-qicon.qi-stock    { background: var(--danger-light); color: var(--danger); }
    .dash-qicon.qi-users    { background: var(--info-light); color: var(--info); }
    .dash-qicon.qi-managers { background: var(--success-light); color: var(--success); }
    .dash-qinfo { flex: 1; min-width: 0; }
    .dash-qlabel {
        font-size: 0.78rem;
        color: var(--text-secondary);
        font-weight: 500;
        margin-bottom: 0.15rem;
    }
    .dash-qvalue {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text);
        line-height: 1;
        letter-spacing: -0.02em;
    }
    .dash-qsub {
        font-size: 0.72rem;
        color: var(--text-muted, #94a3b8);
        margin-top: 0.2rem;
    }

    /* ============= RECENT ORDERS CARD ============= */
    .dash-recent {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 14px);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .dash-recent-head {
        padding: 1.15rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    .dash-recent-head h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dash-recent-head h5 i { color: var(--primary); font-size: 0.9rem; }

    .dash-orders-table { width: 100%; border-collapse: collapse; }
    .dash-orders-table thead th {
        background: var(--bg-muted);
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.7rem 1rem;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .dash-orders-table tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-light, #f1f5f9);
        vertical-align: middle;
        font-size: 0.85rem;
        color: var(--text);
    }
    .dash-orders-table tbody tr { transition: background 0.15s; }
    .dash-orders-table tbody tr:hover { background: var(--bg-card-hover, #f8fafc); }
    .dash-orders-table tbody tr:last-child td { border-bottom: none; }

    .dash-order-id {
        font-weight: 700;
        color: var(--primary);
        font-size: 0.82rem;
    }
    .dash-customer-name {
        font-weight: 600;
        color: var(--text);
        font-size: 0.85rem;
    }
    .dash-customer-phone {
        font-size: 0.78rem;
        color: var(--text-secondary);
    }
    .dash-order-price {
        font-weight: 700;
        color: var(--text);
    }
    .dash-order-date {
        font-size: 0.78rem;
        color: var(--text-muted, #94a3b8);
    }

    /* Status pill */
    .dash-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .dash-pill-new     { background: var(--info-light); color: var(--info); }
    .dash-pill-confirm { background: var(--success-light); color: var(--success); }
    .dash-pill-dated   { background: var(--warning-light); color: var(--warning); }
    .dash-pill-deliver { background: #f5f3ff; color: #8b5cf6; }
    .dash-pill-cancel  { background: var(--danger-light); color: var(--danger); }
    .dash-pill-default { background: var(--bg-muted); color: var(--text-secondary); }
    [data-theme="dark"] .dash-pill-deliver { background: rgba(139,92,246,0.12); }

    .dash-btn-view {
        width: 32px; height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm, 10px);
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--primary);
        font-size: 0.8rem;
        transition: background 0.15s, color 0.15s;
        text-decoration: none;
    }
    .dash-btn-view:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

    .dash-btn-all {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: var(--radius-sm, 10px);
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--primary);
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s;
    }
    .dash-btn-all:hover { background: var(--primary-50, #eef2ff); border-color: var(--primary); color: var(--primary); }

    /* ============= MOBILE ORDER CARDS ============= */
    .dash-mob-cards { display: none; }
    .dash-mob-card {
        display: block;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm, 10px);
        padding: 1rem;
        margin-bottom: 0.6rem;
        text-decoration: none !important;
        color: inherit;
        transition: box-shadow 0.2s;
    }
    .dash-mob-card:hover { box-shadow: var(--shadow-md); color: inherit; }
    .dash-mob-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .dash-mob-id { font-weight: 700; color: var(--primary); font-size: 0.82rem; }
    .dash-mob-date { font-size: 0.72rem; color: var(--text-muted); }
    .dash-mob-mid {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .dash-mob-name { font-weight: 600; font-size: 0.875rem; color: var(--text); }
    .dash-mob-phone { font-size: 0.78rem; color: var(--text-secondary); }
    .dash-mob-bot {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dash-mob-price { font-weight: 700; font-size: 0.875rem; color: var(--text); }

    /* ============= EMPTY STATE ============= */
    .dash-empty {
        text-align: center;
        padding: 3rem 2rem;
    }
    .dash-empty-icon {
        width: 72px; height: 72px;
        border-radius: var(--radius-lg, 18px);
        background: var(--bg-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.75rem;
        color: var(--text-muted);
    }
    .dash-empty p { color: var(--text-secondary); font-size: 0.9rem; margin: 0; }

    /* ============= RESPONSIVE ============= */
    @media (max-width: 992px) {
        .dash-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
        .dash-quick { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .dash-grid { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
        .dash-status { padding: 1rem; }
        .dash-status-value { font-size: 1.45rem; }
        .dash-status-icon { width: 38px; height: 38px; font-size: 0.95rem; }
        .dash-status-link { display: none; }
        .dash-status:hover { transform: none; }
        .dash-quick { grid-template-columns: repeat(2, 1fr); gap: 0.6rem; }
        .dash-qcard { padding: 0.9rem; gap: 0.75rem; }
        .dash-qicon { width: 40px; height: 40px; font-size: 1rem; }
        .dash-qvalue { font-size: 1.25rem; }
        .dash-welcome { padding: 1.25rem 1.25rem; }
        .dash-welcome h2 { font-size: 1.15rem; }
        .dash-recent-head { padding: 1rem 1.15rem; }
        /* Table → Mobile cards */
        .dash-desktop-table { display: none; }
        .dash-mob-cards { display: block; padding: 0 1rem 1rem; }
    }
    @media (max-width: 480px) {
        .dash-grid { gap: 0.5rem; }
        .dash-status { padding: 0.85rem; }
        .dash-status-value { font-size: 1.25rem; }
        .dash-status-label { font-size: 0.62rem; }
        .dash-status-icon { width: 34px; height: 34px; font-size: 0.85rem; }
        .dash-quick { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .dash-qvalue { font-size: 1.1rem; }
        .dash-welcome h2 { font-size: 1.05rem; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Welcome Banner -->
    <div class="dash-welcome om-animate">
        <h2>Bonjour, {{ auth('admin')->user()->name }} 👋</h2>
        <p>Voici un aperçu de vos commandes et activité</p>
    </div>

    <!-- Order Status Cards -->
    <div class="dash-grid">
        <a href="{{ route('admin.orders.index') }}" class="dash-status sc-total om-animate om-animate-delay-1">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Total Commandes</div>
                    <div class="dash-status-value">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
            <div class="dash-status-link">Voir tout <i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="{{ route('admin.orders.index', ['date_from' => now()->format('Y-m-d')]) }}" class="dash-status sc-today om-animate om-animate-delay-2">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Aujourd'hui</div>
                    <div class="dash-status-value">{{ number_format($stats['orders_today'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-calendar-day"></i></div>
            </div>
            <div class="dash-status-link">Du jour <i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'nouvelle']) }}" class="dash-status sc-new om-animate om-animate-delay-3">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Nouvelles</div>
                    <div class="dash-status-value">{{ number_format($ordersByStatus['nouvelle'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-plus-circle"></i></div>
            </div>
            <div class="dash-status-link">Voir <i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'confirmée']) }}" class="dash-status sc-confirm om-animate om-animate-delay-4">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Confirmées</div>
                    <div class="dash-status-value">{{ number_format($ordersByStatus['confirmée'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="dash-status-link">Voir <i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'datée']) }}" class="dash-status sc-dated om-animate">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Datées</div>
                    <div class="dash-status-value">{{ number_format($ordersByStatus['datée'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="dash-status-link">Voir <i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'livrée']) }}" class="dash-status sc-deliver om-animate">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Livrées</div>
                    <div class="dash-status-value">{{ number_format($ordersByStatus['livrée'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-truck"></i></div>
            </div>
            <div class="dash-status-link">Voir <i class="fas fa-arrow-right"></i></div>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'annulée']) }}" class="dash-status sc-cancel om-animate">
            <div class="dash-status-top">
                <div>
                    <div class="dash-status-label">Annulées</div>
                    <div class="dash-status-value">{{ number_format($ordersByStatus['annulée'] ?? 0) }}</div>
                </div>
                <div class="dash-status-icon"><i class="fas fa-times-circle"></i></div>
            </div>
            <div class="dash-status-link">Voir <i class="fas fa-arrow-right"></i></div>
        </a>
    </div>

    @if(auth('admin')->user()->role === \App\Models\Admin::ROLE_ADMIN)
    <!-- Quick Stats (Admin Only) -->
    <div class="dash-quick">
        <div class="dash-qcard om-animate">
            <div class="dash-qicon qi-products"><i class="fas fa-box"></i></div>
            <div class="dash-qinfo">
                <div class="dash-qlabel">Produits</div>
                <div class="dash-qvalue">{{ number_format($stats['total_products'] ?? 0) }}</div>
                <div class="dash-qsub">{{ number_format($stats['active_products'] ?? 0) }} actifs</div>
            </div>
        </div>
        <div class="dash-qcard om-animate">
            <div class="dash-qicon qi-stock"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="dash-qinfo">
                <div class="dash-qlabel">Alertes Stock</div>
                <div class="dash-qvalue">{{ number_format(($stats['low_stock_products'] ?? 0) + ($stats['out_of_stock_products'] ?? 0)) }}</div>
                <div class="dash-qsub">{{ $stats['low_stock_products'] ?? 0 }} faibles / {{ $stats['out_of_stock_products'] ?? 0 }} rupture</div>
            </div>
        </div>
        <div class="dash-qcard om-animate">
            <div class="dash-qicon qi-users"><i class="fas fa-users"></i></div>
            <div class="dash-qinfo">
                <div class="dash-qlabel">Employés</div>
                <div class="dash-qvalue">{{ number_format($stats['total_employees'] ?? 0) }}</div>
                <div class="dash-qsub">{{ number_format($stats['active_employees'] ?? 0) }} actifs</div>
            </div>
        </div>
        <div class="dash-qcard om-animate">
            <div class="dash-qicon qi-managers"><i class="fas fa-user-tie"></i></div>
            <div class="dash-qinfo">
                <div class="dash-qlabel">Managers</div>
                <div class="dash-qvalue">{{ number_format($stats['total_managers'] ?? 0) }}</div>
                <div class="dash-qsub">{{ number_format($stats['active_managers'] ?? 0) }} actifs</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Orders -->
    <div class="dash-recent om-animate">
        <div class="dash-recent-head">
            <h5><i class="fas fa-clock"></i> Dernières Commandes</h5>
            <a href="{{ route('admin.orders.index') }}" class="dash-btn-all">
                <span class="d-none d-sm-inline">Voir tout</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        @if(isset($recentOrders) && $recentOrders->count() > 0)
        <!-- Desktop Table -->
        <div class="table-responsive dash-desktop-table">
            <table class="dash-orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th class="d-none d-md-table-cell">Téléphone</th>
                        <th>Statut</th>
                        <th class="d-none d-lg-table-cell">Total</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td><span class="dash-order-id">#{{ $order->id }}</span></td>
                        <td>
                            <div class="dash-customer-name">{{ $order->customer_name ?? 'N/A' }}</div>
                            <div class="dash-customer-phone d-md-none">{{ $order->customer_phone }}</div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <a href="tel:{{ $order->customer_phone }}" class="text-decoration-none" style="color: var(--text-secondary);">{{ $order->customer_phone }}</a>
                        </td>
                        <td>
                            <span class="dash-pill
                                @if($order->status === 'nouvelle') dash-pill-new
                                @elseif($order->status === 'confirmée') dash-pill-confirm
                                @elseif($order->status === 'datée') dash-pill-dated
                                @elseif($order->status === 'annulée') dash-pill-cancel
                                @elseif($order->status === 'livrée') dash-pill-deliver
                                @else dash-pill-default
                                @endif
                            ">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="d-none d-lg-table-cell"><span class="dash-order-price">{{ number_format($order->total_price, 3) }} DT</span></td>
                        <td class="d-none d-md-table-cell"><span class="dash-order-date">{{ $order->created_at->diffForHumans() }}</span></td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="dash-btn-view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="dash-mob-cards">
            @foreach($recentOrders as $order)
            <a href="{{ route('admin.orders.show', $order->id) }}" class="dash-mob-card">
                <div class="dash-mob-top">
                    <span class="dash-mob-id">#{{ $order->id }}</span>
                    <span class="dash-mob-date">{{ $order->created_at->diffForHumans() }}</span>
                </div>
                <div class="dash-mob-mid">
                    <div>
                        <div class="dash-mob-name">{{ $order->customer_name ?? 'N/A' }}</div>
                        <div class="dash-mob-phone">{{ $order->customer_phone }}</div>
                    </div>
                    <span class="dash-pill
                        @if($order->status === 'nouvelle') dash-pill-new
                        @elseif($order->status === 'confirmée') dash-pill-confirm
                        @elseif($order->status === 'datée') dash-pill-dated
                        @elseif($order->status === 'annulée') dash-pill-cancel
                        @elseif($order->status === 'livrée') dash-pill-deliver
                        @else dash-pill-default
                        @endif
                    ">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="dash-mob-bot">
                    <span class="dash-mob-price">{{ number_format($order->total_price, 3) }} DT</span>
                    <i class="fas fa-chevron-right" style="font-size: 0.7rem; color: var(--text-muted);"></i>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="dash-empty">
            <div class="dash-empty-icon"><i class="fas fa-inbox"></i></div>
            <p>Aucune commande récente</p>
        </div>
        @endif
    </div>
</div>
@endsection
