@extends('layouts.admin')

@section('title', 'Livraison — Masafa Express')

@section('css')
<style>
    :root {
        --masafa-blue: #0f4c81;
        --masafa-blue-light: #1a73c8;
        --masafa-orange: #f97316;
        --royal-blue-light: #3b82f6;
        --royal-blue-lighter: #60a5fa;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #1f2937;
        --border: #e5e7eb;
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .delivery-dashboard {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        margin: 0.5rem;
        min-height: calc(100vh - 70px);
        overflow: hidden;
    }

    /* ===== HEADER MODERNE ===== */
    .dashboard-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        padding: 1.25rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: scale(1.5);
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .header-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-subtitle {
        opacity: 0.9;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 0.875rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        backdrop-filter: blur(10px);
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
    }

    .btn-header.btn-primary {
        background: white;
        color: var(--royal-blue);
    }

    .btn-header.btn-primary:hover {
        background: #f8fafc;
        color: var(--royal-blue);
    }

    /* ===== STATISTIQUES ULTRA-COMPACTES ===== */
    .stats-section {
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
    }

    .stat-card {
        background: white;
        padding: 0.875rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        text-align: center;
        transition: var(--transition);
        border-left: 3px solid transparent;
        min-height: 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--royal-blue-lighter), transparent);
        opacity: 0;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card.stat-primary { border-left-color: var(--royal-blue); }
    .stat-card.stat-success { border-left-color: var(--success); }
    .stat-card.stat-warning { border-left-color: var(--warning); }
    .stat-card.stat-info { border-left-color: var(--info); }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 0.25rem;
        display: block;
        line-height: 1;
    }

    .stat-label {
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        line-height: 1.1;
    }

    /* ===== SECTION TRANSPORTEURS MODERNE ===== */
    .carriers-section {
        padding: 1.25rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .carriers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
    }

    /* ===== CARTE TRANSPORTEUR ULTRA-MODERNE ===== */
    .carrier-card {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
        border: 1px solid var(--border);
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    }

    .carrier-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
    }

    .status-indicator {
        position: absolute;
        top: 0;
        right: 0;
        padding: 0.25rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        border-bottom-left-radius: var(--radius);
        z-index: 10;
    }

    .status-indicator.connected {
        background: var(--success);
        color: white;
    }

    .status-indicator.inactive {
        background: var(--warning);
        color: white;
    }

    .status-indicator.disconnected {
        background: var(--danger);
        color: white;
    }

    .carrier-header {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .carrier-logo {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        object-fit: contain;
        background: #f3f4f6;
        padding: 6px;
        flex-shrink: 0;
    }

    .carrier-info h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .carrier-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .carrier-stats {
        padding: 0.75rem 1rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        text-align: center;
        background: #f9fafb;
    }

    .carrier-stat {
        display: flex;
        flex-direction: column;
        padding: 0.25rem;
    }

    .carrier-stat-number {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--royal-blue);
        margin-bottom: 0.125rem;
        line-height: 1;
    }

    .carrier-stat-label {
        font-size: 0.65rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .carrier-actions {
        padding: 0.875rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        flex: 1;
        min-width: 0;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success), #059669);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
        color: white;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light));
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .btn-outline:hover {
        background: var(--royal-blue);
        color: white;
        border-color: var(--royal-blue);
    }

    /* ===== ÉTAT VIDE STYLÉ ===== */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6b7280;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: var(--radius);
        border: 2px dashed #d1d5db;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--royal-blue);
    }

    .empty-state h3 {
        margin-bottom: 0.5rem;
        color: var(--dark);
        font-size: 1rem;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }

    /* ===== RESPONSIVE ULTRA-OPTIMISÉ ===== */
    @media (max-width: 768px) {
        .delivery-dashboard {
            margin: 0.25rem;
            border-radius: 0;
        }

        .dashboard-header {
            padding: 1rem;
        }

        .header-title {
            font-size: 1.25rem;
        }

        .header-actions {
            justify-content: stretch;
        }

        .btn-header {
            flex: 1;
            justify-content: center;
            font-size: 0.75rem;
            padding: 0.5rem;
        }

        .stats-section {
            padding: 0.75rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .stat-card {
            padding: 0.75rem;
            min-height: 60px;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .carriers-section {
            padding: 0.75rem;
        }

        .carriers-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .carrier-header {
            padding: 0.75rem;
        }

        .carrier-logo {
            width: 32px;
            height: 32px;
        }

        .carrier-actions {
            padding: 0.75rem;
            flex-direction: column;
        }

        .btn {
            flex: none;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .carrier-stats {
            grid-template-columns: 1fr;
            gap: 0.25rem;
            text-align: left;
        }

        .carrier-stat {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0.375rem;
            background: white;
            border-radius: 4px;
        }

        .btn-header {
            font-size: 0.7rem;
            padding: 0.4rem;
        }

        .empty-state {
            padding: 1.5rem;
        }

        .empty-state i {
            font-size: 2rem;
        }
    }

    /* ===== ANIMATIONS FLUIDES ===== */
    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-updated {
        animation: pulse 0.6s ease;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); box-shadow: 0 0 20px rgba(30, 58, 138, 0.3); }
    }

    /* ===== NOTIFICATIONS TOAST ===== */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 280px;
        max-width: 400px;
        padding: 0.75rem;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        box-shadow: var(--shadow-md);
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toast.success { background: var(--success); }
    .toast.warning { background: var(--warning); }
    .toast.danger { background: var(--danger); }
    .toast.info { background: var(--info); }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
</style>
@endsection

@section('content')
<div id="toast-container" style="position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;"></div>

{{-- ===== PAGE HEADER ===== --}}
<div style="background:linear-gradient(135deg,#0f4c81 0%,#1a73c8 100%);border-radius:12px;padding:1.5rem 2rem;color:#fff;margin-bottom:1.5rem;position:relative;overflow:hidden;">
    <div style="position:absolute;right:-40px;top:-40px;width:160px;height:160px;background:rgba(255,255,255,.08);border-radius:50%;"></div>
    <div style="position:relative;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;margin:0;"><i class="fas fa-truck me-2"></i>Livraison — Masafa Express</h1>
            <p style="opacity:.85;margin:.25rem 0 0;font-size:.875rem;">Gérez l'envoi de vos commandes confirmées via l'API Masafa Express</p>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            @if($config && $config->api_token)
                @if($connectionOk)
                    <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .85rem;border-radius:50px;font-size:.8rem;font-weight:600;background:rgba(16,185,129,.2);color:#a7f3d0;"><i class="fas fa-circle" style="font-size:.55rem;"></i> Connecté</span>
                @else
                    <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .85rem;border-radius:50px;font-size:.8rem;font-weight:600;background:rgba(239,68,68,.2);color:#fca5a5;"><i class="fas fa-circle" style="font-size:.55rem;"></i> Hors ligne</span>
                @endif
                <button onclick="testConnection()" id="btn-test" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:8px;padding:.45rem 1rem;font-size:.82rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="fas fa-plug"></i> Tester connexion
                </button>
            @else
                <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .85rem;border-radius:50px;font-size:.8rem;font-weight:600;background:rgba(245,158,11,.2);color:#fde68a;"><i class="fas fa-exclamation-circle" style="font-size:.75rem;"></i> Non configuré</span>
            @endif
        </div>
    </div>
</div>

{{-- ===== SESSION ALERTS ===== --}}
@if(session('success'))
<div style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;border-radius:10px;padding:.9rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.65rem;font-size:.875rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:.9rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.65rem;font-size:.875rem;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- ===== STATS ROW ===== --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.5rem;">
    @php $cards = [
        ['Envoyées',          $stats['total_sent'],   '#1a73c8', '#dbeafe', '#1e40af'],
        ['En cours',          $stats['en_cours'],     '#f97316', '#ffedd5', '#9a3412'],
        ['Livrées',           $stats['livrees'],      '#10b981', '#d1fae5', '#065f46'],
        ['Retours',           $stats['en_retour'],    '#ef4444', '#fee2e2', '#991b1b'],
        ['Prêtes à envoyer',  $stats['pret_envoyer'], '#64748b', '#f1f5f9', '#1e293b'],
    ]; @endphp
    @foreach($cards as [$lbl,$val,$top,$bg,$fg])
    <div style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.08);border-top:3px solid {{ $top }};">
        <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">{{ $lbl }}</div>
        <div style="font-size:1.85rem;font-weight:700;color:{{ $fg }};line-height:1.1;margin-top:.25rem;">{{ $val }}</div>
    </div>
    @endforeach
    @if($masafaStats && isset($masafaStats['total_packages']))
    <div style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.08);border-top:3px solid #8b5cf6;">
        <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">API — Total colis</div>
        <div style="font-size:1.85rem;font-weight:700;color:#6d28d9;line-height:1.1;margin-top:.25rem;">{{ $masafaStats['total_packages'] }}</div>
    </div>
    @endif
</div>

<div class="row g-4">
{{-- ===== LEFT: ORDERS ===== --}}
<div class="col-lg-8">
<div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:1.5rem;">

    {{-- Tabs header --}}
    <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
        <div style="display:flex;gap:.25rem;">
            <button id="tab-btn-ready" onclick="switchTab('ready')"
                style="padding:.45rem 1rem;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:.85rem;background:#0f4c81;color:#fff;">
                <i class="fas fa-clock me-1"></i> Prêtes <span id="badge-ready" style="background:rgba(255,255,255,.25);border-radius:50px;padding:.1rem .5rem;font-size:.75rem;margin-left:.25rem;">{{ count($readyOrders) }}</span>
            </button>
            <button id="tab-btn-sent" onclick="switchTab('sent')"
                style="padding:.45rem 1rem;border:1.5px solid #e2e8f0;border-radius:8px;cursor:pointer;font-weight:600;font-size:.85rem;background:#fff;color:#64748b;">
                <i class="fas fa-check me-1"></i> Envoyées ({{ $sentOrders->total() }})
            </button>
        </div>
        <div id="bulk-action-bar" style="display:none;">
            <button onclick="sendSelected()" id="btn-send-bulk"
                style="background:#10b981;color:#fff;border:none;border-radius:8px;padding:.45rem 1.1rem;font-weight:600;font-size:.82rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;">
                <i class="fas fa-paper-plane"></i> Envoyer sélectionnées
            </button>
        </div>
    </div>

    {{-- TAB: READY ORDERS --}}
    <div id="tab-ready">
        @if(count($readyOrders) === 0)
        <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
            <i class="fas fa-box-open" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            <p style="margin:0;font-size:.875rem;">Aucune commande confirmée en attente d'envoi.</p>
        </div>
        @else
        <div style="padding:.6rem 1.25rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:.75rem;">
            <input type="checkbox" id="check-all" onchange="toggleAll(this)" style="width:16px;height:16px;cursor:pointer;">
            <label for="check-all" style="font-size:.82rem;font-weight:600;color:#64748b;cursor:pointer;margin:0;">Tout sélectionner</label>
            <span style="margin-left:auto;font-size:.78rem;color:#94a3b8;">{{ count($readyOrders) }} commande(s)</span>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;width:36px;"></th>
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;">#</th>
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;">Client</th>
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;">Gouvernorat</th>
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;">Montant</th>
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($readyOrders as $order)
                <tr id="row-{{ $order->id }}" style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.65rem 1rem;"><input type="checkbox" class="order-chk" value="{{ $order->id }}" onchange="onCheckChange()" style="width:15px;height:15px;cursor:pointer;"></td>
                    <td style="padding:.65rem 1rem;font-weight:600;">#{{ $order->id }}</td>
                    <td style="padding:.65rem 1rem;">
                        <div style="font-weight:600;">{{ $order->customer_name }}</div>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ $order->customer_phone }}</div>
                    </td>
                    <td style="padding:.65rem 1rem;font-size:.85rem;">{{ $order->customer_governorate ?? '—' }}</td>
                    <td style="padding:.65rem 1rem;font-weight:700;">{{ number_format($order->total_price, 2) }} TND</td>
                    <td style="padding:.65rem 1rem;">
                        <button onclick="sendOrder({{ $order->id }}, this)"
                            style="background:#10b981;color:#fff;border:none;border-radius:7px;padding:.38rem .85rem;font-size:.8rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.35rem;">
                            <i class="fas fa-paper-plane"></i> Envoyer
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

    {{-- TAB: SENT ORDERS --}}
    <div id="tab-sent" style="display:none;">
        @if($sentOrders->total() === 0)
        <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
            <i class="fas fa-shipping-fast" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            <p style="margin:0;font-size:.875rem;">Aucune commande encore envoyée via Masafa Express.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            <thead>
                <tr style="background:#f8fafc;">
                    @foreach(['#','Client','N° de suivi','Statut','Expédié le','Sync'] as $h)
                    <th style="padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sentOrders as $order)
                @php
                    $stMap = ['expédiée'=>['#dbeafe','#1e40af','Expédiée'],'en_transit'=>['#ffedd5','#9a3412','En transit'],'tentative_livraison'=>['#fef3c7','#92400e','Tentative'],'livrée'=>['#d1fae5','#065f46','Livrée'],'en_retour'=>['#fee2e2','#991b1b','Retour'],'échec_livraison'=>['#fee2e2','#991b1b','Échec']];
                    [$stBg,$stClr,$stLbl] = $stMap[$order->status] ?? ['#f1f5f9','#475569',$order->status];
                @endphp
                <tr id="sent-row-{{ $order->id }}" style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.65rem 1rem;font-weight:600;">#{{ $order->id }}</td>
                    <td style="padding:.65rem 1rem;">
                        <div style="font-weight:600;">{{ $order->customer_name }}</div>
                        <div style="font-size:.78rem;color:#94a3b8;">{{ $order->customer_phone }}</div>
                    </td>
                    <td style="padding:.65rem 1rem;">
                        <span onclick="copyTracking('{{ $order->tracking_number }}', this)"
                              style="font-family:'Courier New',monospace;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:.2rem .5rem;font-size:.8rem;cursor:pointer;"
                              title="Cliquer pour copier">{{ $order->tracking_number }}</span>
                    </td>
                    <td style="padding:.65rem 1rem;">
                        <span id="status-{{ $order->id }}" style="display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600;background:{{ $stBg }};color:{{ $stClr }};">{{ $stLbl }}</span>
                    </td>
                    <td style="padding:.65rem 1rem;font-size:.8rem;color:#94a3b8;">{{ $order->shipped_at ? $order->shipped_at->format('d/m/Y') : '—' }}</td>
                    <td style="padding:.65rem 1rem;">
                        <button onclick="syncStatus({{ $order->id }}, this)"
                            style="background:#fff;color:#1e293b;border:1.5px solid #e2e8f0;border-radius:7px;padding:.38rem .7rem;font-size:.8rem;cursor:pointer;"
                            title="Synchroniser le statut depuis Masafa Express">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @if($sentOrders->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid #e2e8f0;">{{ $sentOrders->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- card --}}
</div>{{-- col-lg-8 --}}

{{-- ===== RIGHT: OAUTH-STYLE CONNECT PANEL ===== --}}
<div class="col-lg-4">

@if($config && $config->api_token)
{{-- ============================================================ --}}
{{-- CONNECTED STATE — show linked account info                   --}}
{{-- ============================================================ --}}
<div style="background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.09);margin-bottom:1rem;overflow:hidden;">

    {{-- Green success header --}}
    <div style="background:linear-gradient(135deg,#065f46,#059669);padding:1.1rem 1.4rem;display:flex;align-items:center;gap:.8rem;">
        <div style="width:34px;height:34px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-check" style="color:#fff;font-size:.95rem;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="color:#fff;font-weight:700;font-size:.9rem;">Compte lié</div>
            <div style="color:rgba(255,255,255,.8);font-size:.75rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Masafa Express connecté avec succès</div>
        </div>
        <button onclick="deleteConfig()" title="Délier le compte"
            style="flex-shrink:0;background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:7px;padding:.3rem .65rem;font-size:.78rem;cursor:pointer;white-space:nowrap;">
            <i class="fas fa-unlink me-1"></i>Délier
        </button>
    </div>

    {{-- Account info --}}
    <div style="padding:1.25rem 1.4rem;">
        @php
            $userName  = $config->masafa_user_name  ?? 'Client Masafa';
            $userEmail = $config->masafa_user_email ?? '';
            $initials  = collect(explode(' ', $userName))->map(fn($w)=>strtoupper(mb_substr($w,0,1)))->take(2)->implode('');
        @endphp
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.1rem;padding-bottom:1rem;border-bottom:1px solid #f1f5f9;">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#0f4c81,#1a73c8);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="color:#fff;font-weight:700;font-size:1.05rem;">{{ $initials ?: 'ME' }}</span>
            </div>
            <div style="min-width:0;">
                <div style="font-weight:700;font-size:.9rem;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $userName }}</div>
                @if($userEmail)
                <div style="font-size:.8rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $userEmail }}</div>
                @endif
                <div style="display:inline-flex;align-items:center;gap:.3rem;margin-top:.3rem;background:#d1fae5;color:#065f46;border-radius:20px;padding:.15rem .55rem;font-size:.72rem;font-weight:600;">
                    <i class="fas fa-circle" style="font-size:.45rem;"></i> Connecté
                </div>
            </div>
        </div>

        {{-- Advanced settings (collapsible) --}}
        <button type="button" onclick="toggleAdvanced()" id="adv-btn"
            style="background:none;border:none;color:#1a73c8;font-size:.82rem;font-weight:600;cursor:pointer;padding:0;display:flex;align-items:center;gap:.35rem;width:100%;margin-bottom:.75rem;">
            <i id="adv-icon" class="fas fa-chevron-right" style="font-size:.7rem;transition:transform .2s;"></i>
            Paramètres d'expédition
        </button>
        <div id="adv-fields" style="display:none;">
            <form method="POST" action="{{ route('admin.delivery.config.save') }}" id="masafa-settings-form">
                @csrf
                <input type="hidden" name="api_token" value="{{ $config->api_token }}">

                {{-- Pickup address selector --}}
                <div style="margin-bottom:.85rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;">
                        <label style="font-weight:600;font-size:.82rem;color:#374151;">Adresse de ramassage (pickup)</label>
                        <button type="button" onclick="loadPickupAddresses()" id="btn-reload-addr"
                            style="background:none;border:none;color:#1a73c8;font-size:.75rem;cursor:pointer;padding:0;display:flex;align-items:center;gap:.25rem;">
                            <i class="fas fa-sync-alt" style="font-size:.7rem;"></i> Rafraîchir
                        </button>
                    </div>

                    {{-- Loading state --}}
                    <div id="addr-loading" style="display:none;text-align:center;padding:.6rem;color:#94a3b8;font-size:.8rem;">
                        <i class="fas fa-circle-notch fa-spin me-1"></i>Chargement…
                    </div>

                    {{-- Dropdown --}}
                    <select id="pickup-addr-select" name="masafa_client_id"
                        onchange="onPickupAddressChange(this)"
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:.5rem .85rem;font-size:.85rem;box-sizing:border-box;background:#fff;cursor:pointer;display:none;">
                        <option value="">— Sélectionner une adresse —</option>
                    </select>

                    {{-- Fallback if no addresses loaded yet --}}
                    <input id="pickup-addr-fallback" type="text" name="masafa_client_id"
                           value="{{ $config->masafa_client_id ?? '' }}"
                           placeholder="ID adresse (ex: 42) — cliquez Rafraîchir pour charger la liste"
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:.5rem .85rem;font-size:.85rem;box-sizing:border-box;">

                    {{-- Address detail card --}}
                    <div id="addr-detail" style="display:none;margin-top:.5rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:.6rem .85rem;font-size:.8rem;color:#0c4a6e;">
                        <div id="addr-detail-name" style="font-weight:700;"></div>
                        <div id="addr-detail-info" style="opacity:.85;margin-top:.15rem;"></div>
                    </div>
                </div>

                <div style="margin-bottom:.75rem;">
                    <label style="display:block;font-weight:600;font-size:.82rem;margin-bottom:.3rem;color:#374151;">Nom du point de ramassage</label>
                    <input id="pickup-name-input" type="text" name="pickup_name" value="{{ $config->pickup_name ?? '' }}"
                           placeholder="ex: Boutique principale"
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:.5rem .85rem;font-size:.85rem;box-sizing:border-box;">
                </div>
                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.9rem;">
                    <input type="hidden" name="auto_send" value="0">
                    <input type="checkbox" name="auto_send" value="1" id="auto-send"
                           {{ $config->auto_send ? 'checked' : '' }}
                           style="width:15px;height:15px;cursor:pointer;">
                    <label for="auto-send" style="font-weight:600;font-size:.82rem;cursor:pointer;margin:0;color:#374151;">Envoi automatique</label>
                </div>
                <button type="submit"
                    style="width:100%;background:#0f4c81;color:#fff;border:none;border-radius:8px;padding:.55rem;font-weight:600;font-size:.85rem;cursor:pointer;">
                    <i class="fas fa-save me-1"></i>Enregistrer les paramètres
                </button>
            </form>
        </div>

        <button onclick="testConnection()" id="btn-test"
            style="margin-top:.75rem;width:100%;background:#fff;color:#1a73c8;border:1.5px solid #1a73c8;border-radius:9px;padding:.55rem;font-weight:600;font-size:.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.45rem;">
            <i class="fas fa-plug"></i> Tester la connexion
        </button>
    </div>
</div>

@else
{{-- ============================================================ --}}
{{-- NOT CONNECTED STATE — Google-style sign-in card             --}}
{{-- ============================================================ --}}
<div style="background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.1);margin-bottom:1rem;overflow:hidden;">

    {{-- Brand header --}}
    <div style="background:linear-gradient(135deg,#0f4c81,#1a73c8);padding:2rem 1.5rem 1.5rem;text-align:center;">
        <div style="width:56px;height:56px;background:rgba(255,255,255,.18);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto .8rem;">
            <i class="fas fa-truck" style="color:#fff;font-size:1.4rem;"></i>
        </div>
        <div style="color:#fff;font-weight:700;font-size:1.1rem;margin-bottom:.3rem;">Masafa Express</div>
        <div style="color:rgba(255,255,255,.75);font-size:.82rem;">Connectez-vous à votre compte pour activer la livraison</div>
    </div>

    {{-- Login form --}}
    <div style="padding:1.5rem;">
        <div id="connect-error" style="display:none;background:#fee2e2;color:#991b1b;border-radius:8px;padding:.75rem 1rem;font-size:.84rem;margin-bottom:1rem;gap:.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="connect-error-msg"></span>
        </div>

        <div style="margin-bottom:.85rem;">
            <label style="display:block;font-weight:600;font-size:.83rem;margin-bottom:.35rem;color:#374151;">Adresse email</label>
            <input id="masafa-email" type="email" autocomplete="email"
                   placeholder="votre@email.com"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:9px;padding:.6rem 1rem;font-size:.88rem;box-sizing:border-box;transition:border .15s;"
                   onfocus="this.style.borderColor='#1a73c8'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.83rem;margin-bottom:.35rem;color:#374151;">Mot de passe</label>
            <div style="position:relative;">
                <input id="masafa-password" type="password" autocomplete="current-password"
                       placeholder="••••••••"
                       style="width:100%;border:1.5px solid #e2e8f0;border-radius:9px;padding:.6rem 2.5rem .6rem 1rem;font-size:.88rem;box-sizing:border-box;transition:border .15s;"
                       onfocus="this.style.borderColor='#1a73c8'" onblur="this.style.borderColor='#e2e8f0'"
                       onkeydown="if(event.key==='Enter') connectMasafa()">
                <button type="button" onclick="togglePasswordVisibility()" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;">
                    <i id="pw-eye" class="fas fa-eye" style="font-size:.85rem;"></i>
                </button>
            </div>
        </div>

        {{-- Main CTA button --}}
        <button type="button" onclick="connectMasafa()" id="btn-masafa-connect"
            style="width:100%;background:linear-gradient(135deg,#0f4c81,#1a73c8);color:#fff;border:none;border-radius:10px;padding:.8rem 1rem;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.6rem;letter-spacing:.01em;box-shadow:0 2px 8px rgba(15,76,129,.35);transition:opacity .15s;">
            <i class="fas fa-sign-in-alt"></i>
            Se connecter avec Masafa Express
        </button>

        <div style="text-align:center;margin-top:1rem;color:#94a3b8;font-size:.75rem;">
            Vos identifiants sont uniquement utilisés pour lier votre compte.<br>
            Ils ne sont jamais stockés dans cet espace.
        </div>
    </div>
</div>
@endif

{{-- ---- API STATS (if connected) ---- --}}
@if($masafaStats)
<div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:1.25rem 1.5rem;">
    <div style="font-weight:700;font-size:.85rem;margin-bottom:.85rem;color:#1e293b;"><i class="fas fa-chart-bar me-2" style="color:#1a73c8;"></i>Statistiques API</div>
    @foreach($masafaStats as $key => $val)
    @if(is_scalar($val))
    <div style="display:flex;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid #f1f5f9;font-size:.82rem;">
        <span style="color:#64748b;">{{ ucwords(str_replace('_',' ',$key)) }}</span>
        <span style="font-weight:700;">{{ $val }}</span>
    </div>
    @endif
    @endforeach
</div>
@endif

</div>{{-- col-lg-4 --}}

</div>{{-- row --}}

@endsection

@section('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ===== TOAST =====
function showToast(type, msg) {
    const c = document.getElementById('toast-container');
    if (!c) return;
    const t = document.createElement('div');
    const [bg,cl,ico] = type==='success'
        ? ['#d1fae5','#065f46','check-circle']
        : type==='error'
        ? ['#fee2e2','#991b1b','exclamation-circle']
        : ['#dbeafe','#1e40af','info-circle'];
    t.style.cssText = `background:${bg};color:${cl};border-radius:10px;padding:.85rem 1.25rem;min-width:280px;display:flex;align-items:center;gap:.65rem;font-size:.875rem;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,.12);animation:slideIn .3s ease;`;
    t.innerHTML = `<i class="fas fa-${ico}"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => { t.style.animation='slideOut .3s ease'; setTimeout(()=>t.remove(),300); }, 4000);
}

// ===== TABS =====
function switchTab(tab) {
    ['ready','sent'].forEach(t => {
        document.getElementById('tab-'+t).style.display = t===tab ? 'block' : 'none';
        const btn = document.getElementById('tab-btn-'+t);
        if (btn) {
            btn.style.background = t===tab ? '#0f4c81' : '#fff';
            btn.style.color = t===tab ? '#fff' : '#64748b';
            btn.style.border = t===tab ? 'none' : '1.5px solid #e2e8f0';
        }
    });
}

// ===== CHECKBOX SELECT ALL =====
function toggleAll(master) {
    document.querySelectorAll('.order-chk').forEach(cb => cb.checked = master.checked);
    onCheckChange();
}
function onCheckChange() {
    const checked = document.querySelectorAll('.order-chk:checked').length;
    const bar = document.getElementById('bulk-action-bar');
    if (bar) bar.style.display = checked > 0 ? 'block' : 'none';
}

// ===== PASSWORD VISIBILITY TOGGLE (login form) =====
function togglePasswordVisibility() {
    const inp = document.getElementById('masafa-password');
    const eye = document.getElementById('pw-eye');
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    eye.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

// ===== ADVANCED FIELDS TOGGLE =====
let _pickupLoaded = false;
function toggleAdvanced() {
    const fields = document.getElementById('adv-fields');
    const icon   = document.getElementById('adv-icon');
    if (!fields) return;
    const open = fields.style.display === 'none';
    fields.style.display = open ? 'block' : 'none';
    if (icon) icon.style.transform = open ? 'rotate(90deg)' : 'rotate(0deg)';
    if (open && !_pickupLoaded) loadPickupAddresses();
}

// ===== PICKUP ADDRESS LOADER =====
let _pickupAddressData = [];
async function loadPickupAddresses() {
    const loading  = document.getElementById('addr-loading');
    const select   = document.getElementById('pickup-addr-select');
    const fallback = document.getElementById('pickup-addr-fallback');
    const reloadBtn = document.getElementById('btn-reload-addr');
    if (!select) return;

    if (loading)   loading.style.display   = 'block';
    if (select)    select.style.display    = 'none';
    if (fallback)  fallback.style.display  = 'none';
    if (reloadBtn) reloadBtn.disabled      = true;

    try {
        const r = await fetch('{{ route("admin.delivery.pickup-addresses") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const d = await r.json();

        if (loading) loading.style.display = 'none';
        if (reloadBtn) reloadBtn.disabled  = false;

        if (d.success && d.data && d.data.length > 0) {
            _pickupLoaded = true;
            _pickupAddressData = d.data;

            // Disable fallback so only select sends the value
            if (fallback) { fallback.disabled = true; fallback.style.display = 'none'; }

            // Populate select
            const currentId = fallback ? fallback.value : '';
            select.innerHTML = '<option value="">— Sélectionner une adresse de ramassage —</option>';
            d.data.forEach(addr => {
                const opt = document.createElement('option');
                opt.value = addr.id;
                opt.textContent = addr.name + (addr.is_default ? ' ★ (défaut)' : '') + ' — ' + addr.gouvernorat;
                opt.selected    = String(addr.id) === String(currentId);
                select.appendChild(opt);
            });
            select.style.display = 'block';

            // Show detail for current selection
            if (select.value) onPickupAddressChange(select);
        } else {
            // No addresses: show fallback text input
            if (fallback) { fallback.disabled = false; fallback.style.display = 'block'; }
            showToast('info', d.data && d.data.length === 0
                ? 'Aucune adresse de ramassage trouvée. Ajoutez-en une dans votre espace Masafa Express.'
                : (d.message ?? 'Impossible de charger les adresses.'));
        }
    } catch(e) {
        if (loading)   loading.style.display  = 'none';
        if (fallback)  { fallback.disabled = false; fallback.style.display = 'block'; }
        if (reloadBtn) reloadBtn.disabled      = false;
        showToast('error', 'Erreur lors du chargement des adresses.');
    }
}

function onPickupAddressChange(select) {
    const id     = select.value;
    const detail = document.getElementById('addr-detail');
    const dName  = document.getElementById('addr-detail-name');
    const dInfo  = document.getElementById('addr-detail-info');
    const nameInput = document.getElementById('pickup-name-input');

    if (!id || !detail) return;

    const addr = _pickupAddressData.find(a => String(a.id) === String(id));
    if (!addr) { if (detail) detail.style.display = 'none'; return; }

    if (dName) dName.textContent = addr.name + (addr.is_default ? ' ★' : '');
    if (dInfo) dInfo.textContent = [addr.address, addr.delegation, addr.gouvernorat].filter(Boolean).join(', ')
                                 + (addr.phone ? ' · ' + addr.phone : '');
    if (detail) detail.style.display = 'block';

    // Auto-fill pickup name if empty
    if (nameInput && !nameInput.value) nameInput.value = addr.name;
}

// ===== CONNECT WITH MASAFA EXPRESS CREDENTIALS =====
async function connectMasafa() {
    const email    = document.getElementById('masafa-email')?.value?.trim();
    const password = document.getElementById('masafa-password')?.value;
    const errBox   = document.getElementById('connect-error');
    const errMsg   = document.getElementById('connect-error-msg');

    if (errBox) errBox.style.display = 'none';

    if (!email)    { showError('Veuillez saisir votre adresse email.'); return; }
    if (!password) { showError('Veuillez saisir votre mot de passe.'); return; }

    const btn  = document.getElementById('btn-masafa-connect');
    const orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> Connexion en cours…';
    btn.disabled  = true;
    btn.style.opacity = '.7';

    try {
        const r = await fetch('{{ route("admin.delivery.connect") }}', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ email, password })
        });
        const d = await r.json();

        if (d.success) {
            showToast('success', d.message ?? 'Compte Masafa Express lié !');
            setTimeout(() => location.reload(), 1200);
        } else {
            showError(d.message ?? 'Identifiants incorrects.');
            btn.innerHTML = orig; btn.disabled = false; btn.style.opacity = '1';
        }
    } catch(e) {
        showError('Erreur réseau. Vérifiez que les deux applications sont démarrées.');
        btn.innerHTML = orig; btn.disabled = false; btn.style.opacity = '1';
    }

    function showError(msg) {
        if (errBox) { errMsg.textContent = msg; errBox.style.display = 'flex'; }
        else showToast('error', msg);
    }
}

// ===== TEST CONNECTION =====
async function testConnection() {
    const btn = document.getElementById('btn-test');
    if (btn) { btn.innerHTML = '<span class="spinner"></span> Test...'; btn.disabled = true; }
    try {
        const r = await fetch('{{ route("admin.delivery.test-connection") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const d = await r.json();
        showToast(d.success ? 'success' : 'error', d.message ?? (d.success ? 'Connexion réussie !' : 'Connexion échouée'));
    } catch(e) { showToast('error', 'Erreur réseau: ' + e.message); }
    finally {
        if (btn) { btn.innerHTML = '<i class="fas fa-plug"></i> Tester connexion'; btn.disabled = false; }
    }
}

// ===== SEND SINGLE ORDER =====
async function sendOrder(orderId, btn) {
    if (!confirm('Envoyer la commande #'+orderId+' à Masafa Express ?')) return;
    const orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>'; btn.disabled = true;
    try {
        const r = await fetch(`/admin/delivery/orders/${orderId}/send`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (d.success) {
            showToast('success', `Colis envoyé ! Suivi: ${d.tracking_number ?? 'N/A'}`);
            const row = document.getElementById('row-'+orderId);
            if (row) { row.style.transition='opacity .4s'; row.style.opacity='0'; setTimeout(()=>row.remove(), 400); }
            const badge = document.getElementById('badge-ready');
            if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent)-1);
        } else {
            showToast('error', d.message ?? 'Erreur lors de l\'envoi');
            btn.innerHTML = orig; btn.disabled = false;
        }
    } catch(e) { showToast('error', 'Erreur réseau'); btn.innerHTML = orig; btn.disabled = false; }
}

// ===== SEND BULK =====
async function sendSelected() {
    const ids = [...document.querySelectorAll('.order-chk:checked')].map(cb => cb.value);
    if (!ids.length) return;
    if (!confirm(`Envoyer ${ids.length} commande(s) à Masafa Express ?`)) return;
    const btn = document.getElementById('btn-send-bulk');
    if (btn) { btn.innerHTML = '<span class="spinner"></span> Envoi...'; btn.disabled = true; }
    try {
        const r = await fetch('/admin/delivery/orders/send-bulk', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_ids: ids })
        });
        const d = await r.json();
        showToast(d.results?.errors===0 ? 'success' : 'info', d.message ?? 'Envoi terminé');
        if (d.results?.details) {
            d.results.details.forEach(item => {
                if (item.ok) {
                    const row = document.getElementById('row-'+item.order_id);
                    if (row) { row.style.opacity='0'; setTimeout(()=>row.remove(),400); }
                }
            });
        }
        const badge = document.getElementById('badge-ready');
        if (badge && d.results?.success) badge.textContent = Math.max(0, parseInt(badge.textContent) - d.results.success);
    } catch(e) { showToast('error', 'Erreur réseau'); }
    finally { if (btn) { btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer sélectionnées'; btn.disabled = false; } }
}

// ===== SYNC STATUS =====
async function syncStatus(orderId, btn) {
    const orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner" style="border-color:rgba(0,0,0,.15);border-top-color:#1a73c8;"></span>';
    btn.disabled = true;
    try {
        const r = await fetch(`/admin/delivery/orders/${orderId}/sync-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (d.success) {
            showToast('success', 'Statut mis à jour: ' + (d.order_status ?? ''));
            if (d.order_status) {
                const stMap = { 'expédiée':['#dbeafe','#1e40af','Expédiée'],'en_transit':['#ffedd5','#9a3412','En transit'],'tentative_livraison':['#fef3c7','#92400e','Tentative'],'livrée':['#d1fae5','#065f46','Livrée'],'en_retour':['#fee2e2','#991b1b','Retour'],'échec_livraison':['#fee2e2','#991b1b','Échec'] };
                const el = document.getElementById('status-'+orderId);
                if (el && stMap[d.order_status]) {
                    const [bg,cl,lbl] = stMap[d.order_status];
                    el.style.background = bg; el.style.color = cl; el.textContent = lbl;
                }
            }
        } else { showToast('error', d.message ?? 'Impossible de synchroniser'); }
    } catch(e) { showToast('error', 'Erreur réseau'); }
    finally { btn.innerHTML = orig; btn.disabled = false; }
}

// ===== COPY TRACKING =====
function copyTracking(tracking, el) {
    navigator.clipboard?.writeText(tracking).then(() => {
        const orig = el.textContent;
        el.textContent = '✓ Copié!';
        setTimeout(() => el.textContent = orig, 1500);
    });
}

// ===== DELETE CONFIG =====
async function deleteConfig() {
    if (!confirm('Supprimer la configuration Masafa Express ? Cette action est irréversible.')) return;
    try {
        const r = await fetch('/admin/delivery/config', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (d.success) { showToast('success', 'Configuration supprimée.'); setTimeout(()=>location.reload(), 1200); }
        else showToast('error', d.message ?? 'Erreur');
    } catch(e) { showToast('error', 'Erreur réseau'); }
}

// ===== INLINE SPINNER STYLE =====
const _s = document.createElement('style');
_s.textContent = '.spinner{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes slideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}';
document.head.appendChild(_s);

console.log('✅ Masafa Express Delivery loaded');
</script>
@endsection