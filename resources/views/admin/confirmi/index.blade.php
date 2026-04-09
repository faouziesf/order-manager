@extends('layouts.admin')

@section('title', 'Confirmation par Confirmi')

@section('css')
@include('admin.partials._shared-styles')
<style>
    /* ===== Hero Section ===== */
    .cfm-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
        border-radius: var(--radius-lg, 16px);
        padding: 56px 40px;
        text-align: center;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .cfm-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.12) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 40%);
        pointer-events: none;
    }
    .cfm-hero-icon {
        width: 88px; height: 88px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(12px);
        border-radius: 24px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 36px;
        margin-bottom: 20px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .cfm-hero h3 { font-weight: 800; font-size: 1.6rem; margin-bottom: 10px; }
    .cfm-hero p { opacity: 0.92; max-width: 520px; margin: 0 auto 28px; font-size: 15px; line-height: 1.6; }

    .cfm-features {
        display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 36px;
    }
    .cfm-feature {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 12px 22px;
        border-radius: 12px;
        font-size: 13px; font-weight: 600;
        background: rgba(255,255,255,0.13);
        border: 1px solid rgba(255,255,255,0.18);
        backdrop-filter: blur(8px);
        transition: background 0.2s;
    }
    .cfm-feature:hover { background: rgba(255,255,255,0.22); }
    .cfm-hero textarea {
        border-radius: 14px;
        border: 2px solid rgba(255,255,255,0.25);
        background: rgba(255,255,255,0.1);
        color: #fff;
        backdrop-filter: blur(10px);
        padding: 14px 16px;
        width: 100%;
        max-width: 440px;
        font-size: 14px;
        resize: vertical;
    }
    .cfm-hero textarea::placeholder { color: rgba(255,255,255,0.6); }
    .cfm-hero textarea:focus { outline: none; border-color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.15); }
    .cfm-hero-btn {
        display: inline-flex; align-items: center; gap: 10px;
        background: #fff; color: var(--primary);
        font-weight: 700; padding: 14px 36px; font-size: 15px;
        border: none; border-radius: 12px; cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .cfm-hero-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }

    /* ===== Pending State ===== */
    .cfm-pending { text-align: center; padding: 56px 32px; }
    .cfm-pending-spinner {
        width: 64px; height: 64px; border-radius: 50%;
        border: 4px solid var(--border);
        border-top-color: var(--primary);
        animation: cfm-spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    @keyframes cfm-spin { to { transform: rotate(360deg); } }
    .cfm-pending h4 { font-weight: 800; color: var(--text); margin-bottom: 8px; font-size: 1.2rem; }
    .cfm-pending p { color: var(--text-secondary); max-width: 420px; margin: 0 auto 20px; line-height: 1.6; }
    .cfm-pending-meta { color: var(--text-muted); font-size: 13px; }
    .cfm-pending-msg {
        margin-top: 16px; padding: 16px 20px;
        background: var(--bg-muted); border-radius: 12px;
        text-align: left; max-width: 480px; margin-left: auto; margin-right: auto;
    }
    .cfm-pending-msg small { color: var(--text-muted); font-weight: 600; }
    .cfm-pending-msg span { color: var(--text); }

    /* ===== Page Header ===== */
    .cfm-page-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 28px;
    }
    .cfm-page-title {
        font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    .cfm-page-title i { color: var(--primary); font-size: 1.2rem; }
    .cfm-page-subtitle { color: var(--text-secondary); margin: 4px 0 0; font-size: 14px; }

    /* ===== Today Overview Banner ===== */
    .cfm-overview {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 16px);
        padding: 24px 28px;
        margin-bottom: 24px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 20px;
        position: relative;
        overflow: hidden;
    }
    .cfm-overview::before {
        content: '';
        position: absolute; left: 0; top: 0; bottom: 0;
        width: 4px; background: linear-gradient(to bottom, var(--primary), var(--info));
    }
    .cfm-overview-label {
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 10px;
    }
    .cfm-overview-stats { display: flex; gap: 28px; align-items: baseline; flex-wrap: wrap; }
    .cfm-overview-num {
        font-size: 2rem; font-weight: 800; line-height: 1;
    }
    .cfm-overview-num.text-default { color: var(--text); }
    .cfm-overview-num.text-success { color: var(--success); }
    .cfm-overview-num.text-danger { color: var(--danger); }
    .cfm-overview-num-label { font-size: 12px; color: var(--text-muted); margin-left: 4px; }

    /* Donut */
    .cfm-donut {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .cfm-donut-inner {
        width: 52px; height: 52px; border-radius: 50%;
        background: var(--bg-card);
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; color: var(--success);
    }
    .cfm-donut-label { font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 4px; }

    /* ===== Stat Cards ===== */
    .cfm-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .cfm-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 12px);
        padding: 20px;
        display: flex; align-items: center; gap: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }
    .cfm-stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .cfm-stat-card::after {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
    }
    .cfm-stat-card.stat-primary::after { background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
    .cfm-stat-card.stat-warning::after { background: linear-gradient(90deg, var(--warning), #fcd34d); }
    .cfm-stat-card.stat-success::after { background: linear-gradient(90deg, var(--success), #6ee7b7); }
    .cfm-stat-card.stat-danger::after { background: linear-gradient(90deg, var(--danger), #fca5a5); }
    .cfm-stat-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; flex-shrink: 0;
    }
    .cfm-stat-icon.icon-primary { background: var(--primary-50); color: var(--primary); }
    .cfm-stat-icon.icon-warning { background: var(--warning-light); color: var(--warning); }
    .cfm-stat-icon.icon-success { background: var(--success-light); color: var(--success); }
    .cfm-stat-icon.icon-danger { background: var(--danger-light); color: var(--danger); }
    .cfm-stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text); line-height: 1; }
    .cfm-stat-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px; }

    @media (max-width: 992px) { .cfm-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .cfm-stats { grid-template-columns: 1fr; } }

    /* ===== Trend Cards Row ===== */
    .cfm-trends { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
    .cfm-trend-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 12px);
        padding: 22px;
        display: flex; align-items: center; gap: 16px;
        transition: box-shadow 0.2s;
    }
    .cfm-trend-card:hover { box-shadow: var(--shadow-md); }
    .cfm-trend-icon {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
    }
    .cfm-trend-icon.icon-success { background: var(--success-light); color: var(--success); }
    .cfm-trend-icon.icon-info { background: var(--info-light); color: var(--info); }
    .cfm-trend-meta { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .cfm-trend-value { font-size: 1.3rem; font-weight: 800; color: var(--text); margin-top: 2px; }
    .cfm-trend-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .cfm-trend-diff { font-weight: 700; }
    .cfm-trend-diff.up { color: var(--success); }
    .cfm-trend-diff.down { color: var(--danger); }
    @media (max-width: 768px) { .cfm-trends { grid-template-columns: 1fr; } }

    /* ===== Billing Strip ===== */
    .cfm-billing-strip {
        display: flex; align-items: center; gap: 28px;
        padding: 18px 24px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 12px);
        flex-wrap: wrap;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .cfm-billing-strip::before {
        content: '';
        position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
        background: var(--warning);
    }
    .cfm-billing-item { display: flex; flex-direction: column; gap: 2px; }
    .cfm-billing-item small { color: var(--text-muted); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .cfm-billing-item strong { font-size: 1.05rem; font-weight: 700; }
    .cfm-billing-item .val-warning { color: var(--warning); }
    .cfm-billing-item .val-danger { color: var(--danger); }
    .cfm-billing-item .val-default { color: var(--text); }

    /* ===== Subscription Card ===== */
    .cfm-subscription {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 12px);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .cfm-sub-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .cfm-sub-title {
        font-size: 1rem; font-weight: 700; color: var(--text);
        display: flex; align-items: center; gap: 8px;
    }
    .cfm-sub-title i { color: var(--primary); }
    .cfm-sub-badge {
        padding: 5px 14px; border-radius: 20px;
        font-size: 12px; font-weight: 700;
        background: var(--success-light); color: var(--success);
    }
    .cfm-sub-body { padding: 24px; }
    .cfm-sub-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .cfm-sub-item small { color: var(--text-muted); font-size: 12px; font-weight: 500; display: block; margin-bottom: 4px; }
    .cfm-sub-item .cfm-sub-val { font-size: 1.25rem; font-weight: 700; color: var(--text); }
    @media (max-width: 768px) { .cfm-sub-grid { grid-template-columns: 1fr; } }

    /* ===== Orders Section ===== */
    .cfm-orders-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
    }
    .cfm-orders-title { font-size: 1.1rem; font-weight: 800; color: var(--text); margin: 0; }
    .cfm-orders-actions { display: flex; gap: 8px; }
    .cfm-info-banner {
        padding: 16px 20px;
        background: var(--info-light);
        border-radius: var(--radius, 12px);
        border: 1px solid var(--border);
        color: var(--info);
        font-size: 14px;
        display: flex; align-items: flex-start; gap: 10px;
        line-height: 1.5;
    }
    .cfm-info-banner i { margin-top: 2px; flex-shrink: 0; }

    /* ===== Employee Performance ===== */
    .cfm-perf {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius, 12px);
        margin-top: 24px; overflow: hidden;
    }
    .cfm-perf-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 8px;
    }
    .cfm-perf-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
    .cfm-perf-title i { color: var(--primary); }
    .cfm-emp-row {
        display: flex; align-items: center; gap: 8px;
    }
    .cfm-progress-bar {
        width: 64px; height: 6px;
        background: var(--border);
        border-radius: 3px;
        overflow: hidden;
    }
    .cfm-progress-fill {
        height: 100%; border-radius: 3px;
        transition: width 0.5s ease;
    }

    /* ===== Rejected Alert ===== */
    .cfm-alert-rejected {
        margin-top: 20px; padding: 16px 20px;
        background: var(--warning-light);
        border-radius: var(--radius, 12px);
        border: 1px solid var(--border);
        color: var(--text);
        font-size: 14px;
        display: flex; align-items: flex-start; gap: 10px;
        line-height: 1.5;
    }
    .cfm-alert-rejected i { color: var(--warning); margin-top: 2px; flex-shrink: 0; }
</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="cfm-page-header">
    <div>
        <h1 class="cfm-page-title">
            <i class="fas fa-headset"></i>Confirmation par Confirmi
        </h1>
        <p class="cfm-page-subtitle">Service de confirmation de commandes par notre equipe dediee</p>
    </div>
</div>

@if($status === 'disabled')
    {{-- ═══════ NOT ACTIVATED ═══════ --}}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="cfm-hero">
                <div class="cfm-hero-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Activez la confirmation par Confirmi</h3>
                <p>
                    Notre equipe appelle vos clients pour confirmer les commandes avant l'expedition. Reduisez les retours et augmentez votre taux de livraison.
                </p>
                <div class="cfm-features">
                    <div class="cfm-feature"><i class="fas fa-phone-volume"></i> Confirmation telephonique</div>
                    <div class="cfm-feature"><i class="fas fa-chart-line"></i> Taux ameliore</div>
                    <div class="cfm-feature"><i class="fas fa-undo"></i> Moins de retours</div>
                </div>
                <form method="POST" action="{{ route('admin.confirmi.request') }}" style="position:relative; z-index:1;">
                    @csrf
                    <div style="margin: 0 auto 18px;">
                        <textarea name="message" class="form-control" rows="3" placeholder="Message optionnel (ex: volume estime, besoins specifiques...)"></textarea>
                    </div>
                    <button type="submit" class="cfm-hero-btn">
                        <i class="fas fa-paper-plane"></i> Demander l'activation
                    </button>
                </form>
            </div>
        </div>
    </div>

@elseif($status === 'pending')
    {{-- ═══════ PENDING ═══════ --}}
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="om-card cfm-pending">
                <div class="cfm-pending-spinner"></div>
                <h4>Demande en cours de traitement</h4>
                <p>Votre demande d'activation Confirmi est en cours. Vous serez notifie des que votre compte sera active.</p>
                @if($pendingRequest)
                    <div class="cfm-pending-meta">
                        <i class="fas fa-clock"></i> Envoyee le {{ $pendingRequest->created_at->format('d/m/Y a H:i') }}
                    </div>
                    @if($pendingRequest->admin_message)
                        <div class="cfm-pending-msg">
                            <small>Votre message :</small><br>
                            <span>{{ $pendingRequest->admin_message }}</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

@elseif($status === 'active')
    {{-- ═══════ ACTIVE DASHBOARD ═══════ --}}

    {{-- Today Overview --}}
    <div class="cfm-overview">
        <div>
            <div class="cfm-overview-label">Aujourd'hui</div>
            <div class="cfm-overview-stats">
                <div>
                    <span class="cfm-overview-num text-default">{{ $stats['today_total'] }}</span>
                    <span class="cfm-overview-num-label">nouvelles</span>
                </div>
                <div>
                    <span class="cfm-overview-num text-success">{{ $stats['today_confirmed'] }}</span>
                    <span class="cfm-overview-num-label">confirmees</span>
                </div>
                <div>
                    <span class="cfm-overview-num text-danger">{{ $stats['today_cancelled'] }}</span>
                    <span class="cfm-overview-num-label">annulees</span>
                </div>
            </div>
        </div>
        <div style="text-align:center;">
            <div class="cfm-donut" style="background:conic-gradient(var(--success) 0% {{ $stats['success_rate'] }}%, var(--border) {{ $stats['success_rate'] }}% 100%);">
                <div class="cfm-donut-inner">{{ $stats['success_rate'] }}%</div>
            </div>
            <div class="cfm-donut-label">Taux succes</div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="cfm-stats">
        <div class="cfm-stat-card stat-primary">
            <div class="cfm-stat-icon icon-primary"><i class="fas fa-box"></i></div>
            <div>
                <div class="cfm-stat-value">{{ $stats['total'] }}</div>
                <div class="cfm-stat-label">Total commandes</div>
            </div>
        </div>
        <div class="cfm-stat-card stat-warning">
            <div class="cfm-stat-icon icon-warning"><i class="fas fa-clock"></i></div>
            <div>
                <div class="cfm-stat-value">{{ $stats['pending'] + $stats['in_progress'] }}</div>
                <div class="cfm-stat-label">En cours</div>
            </div>
        </div>
        <div class="cfm-stat-card stat-success">
            <div class="cfm-stat-icon icon-success"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="cfm-stat-value">{{ $stats['confirmed'] }}</div>
                <div class="cfm-stat-label">Confirmees</div>
            </div>
        </div>
        <div class="cfm-stat-card stat-danger">
            <div class="cfm-stat-icon icon-danger"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="cfm-stat-value">{{ $stats['cancelled'] }}</div>
                <div class="cfm-stat-label">Annulees</div>
            </div>
        </div>
    </div>

    {{-- Weekly Trend + Avg Attempts --}}
    <div class="cfm-trends">
        <div class="cfm-trend-card">
            <div class="cfm-trend-icon icon-success"><i class="fas fa-arrow-trend-up"></i></div>
            <div>
                <div class="cfm-trend-meta">Cette semaine</div>
                <div class="cfm-trend-value">{{ $stats['week_confirmed'] }} confirmees</div>
                <div class="cfm-trend-sub">
                    Semaine derniere: {{ $stats['last_week_confirmed'] }}
                    @if($stats['last_week_confirmed'] > 0)
                        @php $weekDiff = round((($stats['week_confirmed'] - $stats['last_week_confirmed']) / $stats['last_week_confirmed']) * 100); @endphp
                        <span class="cfm-trend-diff {{ $weekDiff >= 0 ? 'up' : 'down' }}">
                            ({{ $weekDiff >= 0 ? '+' : '' }}{{ $weekDiff }}%)
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="cfm-trend-card">
            <div class="cfm-trend-icon icon-info"><i class="fas fa-phone"></i></div>
            <div>
                <div class="cfm-trend-meta">Moy. tentatives</div>
                <div class="cfm-trend-value">{{ $stats['avg_attempts'] }}</div>
                <div class="cfm-trend-sub">appels avant confirmation</div>
            </div>
        </div>
    </div>

    {{-- Billing Strip --}}
    <div class="cfm-billing-strip">
        <div class="cfm-billing-item">
            <small>Montant ce mois</small>
            <strong class="val-warning">{{ number_format($billing['month_total'], 3) }} DT</strong>
        </div>
        <div class="cfm-billing-item">
            <small>Impaye</small>
            <strong class="val-danger">{{ number_format($billing['unpaid'], 3) }} DT</strong>
        </div>
        <div class="cfm-billing-item">
            <small>Confirmees facturees</small>
            <strong class="val-default">{{ $billing['month_confirmed'] }}</strong>
        </div>
        <div class="cfm-billing-item">
            <small>Livrees facturees</small>
            <strong class="val-default">{{ $billing['month_delivered'] }}</strong>
        </div>
        <div style="margin-left:auto;">
            <a href="{{ route('admin.confirmi.billing') }}" class="om-btn om-btn-ghost om-btn-sm">
                <i class="fas fa-file-invoice-dollar"></i> Historique
            </a>
        </div>
    </div>

    {{-- Subscription Info --}}
    <div class="cfm-subscription">
        <div class="cfm-sub-header">
            <span class="cfm-sub-title"><i class="fas fa-gem"></i> Votre abonnement</span>
            <span class="cfm-sub-badge">Actif</span>
        </div>
        <div class="cfm-sub-body">
            <div class="cfm-sub-grid">
                <div class="cfm-sub-item">
                    <small>Tarif / commande confirmee</small>
                    <div class="cfm-sub-val">{{ number_format($admin->confirmi_rate_confirmed, 3) }} DT</div>
                </div>
                <div class="cfm-sub-item">
                    <small>Tarif / commande livree</small>
                    <div class="cfm-sub-val">{{ number_format($admin->confirmi_rate_delivered, 3) }} DT</div>
                </div>
                <div class="cfm-sub-item">
                    <small>Active depuis</small>
                    <div class="cfm-sub-val">{{ $admin->confirmi_activated_at ? $admin->confirmi_activated_at->format('d/m/Y') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders Link --}}
    <div class="cfm-orders-header">
        <h5 class="cfm-orders-title">Vos commandes Confirmi</h5>
        <div class="cfm-orders-actions">
            <a href="{{ route('admin.confirmi.orders') }}" class="om-btn om-btn-primary om-btn-sm">
                <i class="fas fa-list"></i> En cours
            </a>
            <a href="{{ route('admin.confirmi.history') }}" class="om-btn om-btn-ghost om-btn-sm">
                <i class="fas fa-history"></i> Historique
            </a>
        </div>
    </div>
    <div class="cfm-info-banner">
        <i class="fas fa-info-circle"></i>
        <span>Les commandes gerees par Confirmi sont en <strong>lecture seule</strong>. Une fois confirmees, vous ne pouvez plus les modifier.</span>
    </div>

    {{-- Employee Performance --}}
    @if($employeeStats && $employeeStats->count() > 0)
    <div class="cfm-perf">
        <div class="cfm-perf-header">
            <h3 class="cfm-perf-title"><i class="fas fa-chart-bar"></i> Performance des employes</h3>
        </div>
        <div class="table-responsive">
            <table class="om-table">
                <thead>
                    <tr>
                        <th>Employe</th>
                        <th>En file</th>
                        <th>Confirmees</th>
                        <th>Annulees</th>
                        <th>Total</th>
                        <th>Taux succes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employeeStats as $emp)
                    <tr>
                        <td>
                            <div class="cfm-emp-row">
                                <span class="om-avatar om-avatar-sm">{{ substr($emp->name, 0, 1) }}</span>
                                <span>{{ $emp->name }}</span>
                            </div>
                        </td>
                        <td><span class="om-badge-warning">{{ $emp->my_pending }}</span></td>
                        <td><span class="om-badge-success">{{ $emp->my_confirmed }}</span></td>
                        <td><span class="om-badge-danger">{{ $emp->my_cancelled }}</span></td>
                        <td><strong>{{ $emp->my_total }}</strong></td>
                        <td>
                            <div class="cfm-emp-row">
                                <div class="cfm-progress-bar">
                                    <div class="cfm-progress-fill" style="width:{{ $emp->success_rate }}%; background:{{ $emp->success_rate >= 70 ? 'var(--success)' : ($emp->success_rate >= 40 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                                </div>
                                <strong style="font-size:13px;">{{ $emp->success_rate }}%</strong>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endif

@if($latestRequest && $latestRequest->status === 'rejected')
    <div class="cfm-alert-rejected">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Derniere demande rejetee</strong> ({{ $latestRequest->processed_at?->format('d/m/Y') }})
            @if($latestRequest->response_message)
                <br><small>{{ $latestRequest->response_message }}</small>
            @endif
            <br><small>Vous pouvez soumettre une nouvelle demande.</small>
        </div>
    </div>
@endif
@endsection
