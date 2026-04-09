@extends('confirmi.layouts.app')
@section('title', 'Dashboard Employé')
@section('page-title', 'Mon Dashboard')

@section('css')
<style>
/* ═══ Dashboard hero ═══ */
.hero-banner {
    background: linear-gradient(135deg, var(--accent, #1e40af) 0%, #2563eb 40%, #6366f1 100%);
    border-radius: var(--radius-lg, 16px);
    padding: 2rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(30,64,175,.3);
}
.hero-banner::before {
    content: '';
    position: absolute;
    top: -60%; right: -15%;
    width: 350px; height: 350px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.hero-banner::after {
    content: '';
    position: absolute;
    bottom: -40%; left: -10%;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.hero-content { position: relative; z-index: 1; }
.hero-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .85rem 2rem;
    background: #fff;
    color: var(--accent, #1e40af);
    border: none;
    border-radius: var(--radius-lg, 16px);
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    transition: all .25s;
    box-shadow: 0 4px 15px rgba(0,0,0,.15);
}
.hero-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.2); color: var(--accent, #1e40af); }
.hero-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(4px);
    padding: .4rem .9rem;
    border-radius: 50px;
    font-size: .8rem;
    font-weight: 600;
}

/* ═══ Stat Tiles ═══ */
.pi-tile {
    background: var(--bg-card);
    border-radius: var(--radius-lg, 16px);
    padding: 1.25rem;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    transition: all .25s;
    position: relative;
    overflow: hidden;
}
.pi-tile:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.pi-tile-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.pi-tile-icon.t-orange { background: #fff7ed; color: #f97316; }
.pi-tile-icon.t-cyan   { background: #ecfeff; color: #06b6d4; }
.pi-tile-icon.t-green  { background: #f0fdf4; color: #10b981; }
.pi-tile-icon.t-red    { background: #fef2f2; color: #ef4444; }
.pi-tile-icon.t-purple { background: #faf5ff; color: #8b5cf6; }
.pi-tile-icon.t-blue   { background: #eff6ff; color: #3b82f6; }
.pi-tile-val { font-size: 1.7rem; font-weight: 800; line-height: 1.1; color: var(--text); }
.pi-tile-lbl { font-size: .78rem; color: var(--text-secondary); margin-top: .15rem; font-weight: 500; }

[data-theme="dark"] .pi-tile-icon.t-orange { background: rgba(249,115,22,.12); color: #fb923c; }
[data-theme="dark"] .pi-tile-icon.t-cyan   { background: rgba(6,182,212,.12); color: #22d3ee; }
[data-theme="dark"] .pi-tile-icon.t-green  { background: rgba(16,185,129,.12); color: #34d399; }
[data-theme="dark"] .pi-tile-icon.t-red    { background: rgba(239,68,68,.12); color: #f87171; }
[data-theme="dark"] .pi-tile-icon.t-purple { background: rgba(139,92,246,.12); color: #a78bfa; }
[data-theme="dark"] .pi-tile-icon.t-blue   { background: rgba(59,130,246,.12); color: #60a5fa; }

/* ═══ Objective ═══ */
.obj-bar {
    height: 10px;
    background: var(--border);
    border-radius: 20px;
    overflow: hidden;
}
.obj-fill {
    height: 100%;
    border-radius: 20px;
    transition: width .6s ease;
}

/* ═══ Rate Badge ═══ */
.rate-badge {
    width: 60px; height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    font-weight: 800;
}

/* ═══ Weekly Chart ═══ */
.w-chart {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    height: 110px;
    padding: .5rem 0;
}
.w-bar-group { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.w-bars { display: flex; gap: 2px; align-items: flex-end; height: 85px; }
.w-bar { width: 14px; border-radius: 4px 4px 0 0; min-height: 2px; transition: height .4s; }
.w-bar.ok { background: linear-gradient(to top, #10b981, #34d399); }
.w-bar.ko { background: linear-gradient(to top, #ef4444, #f87171); }
.w-day { font-size: .62rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; }

/* ═══ Quick Link Row ═══ */
.pi-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, var(--accent, #1e40af), #6366f1);
    color: #fff;
    border-radius: var(--radius-lg, 16px);
    text-decoration: none;
    transition: all .25s;
    box-shadow: 0 6px 16px rgba(30,64,175,.25);
}
.pi-link:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(30,64,175,.35); color: #fff; }

.qn-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
    padding: 1rem;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 14px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: .8rem;
    font-weight: 600;
    transition: all .25s;
}
.qn-btn:hover { border-color: var(--accent, #1e40af); color: var(--accent, #1e40af); transform: translateY(-3px); box-shadow: var(--shadow-md); }
.qn-btn i { font-size: 1.3rem; }

@media (max-width: 768px) {
    .hero-banner { padding: 1.5rem; border-radius: 16px; }
    .pi-tile-val { font-size: 1.35rem; }
    .w-bar { width: 10px; }
}
</style>
@endsection

@section('content')
@php
    $totalActive = $stats['pending'] + $stats['in_progress'];
    $rate = $stats['success_rate'];
    $rateHex = $rate >= 70 ? '#10b981' : ($rate >= 40 ? '#f59e0b' : '#ef4444');
    $ringBg = $rate >= 70 ? 'rgba(16,185,129,.12)' : ($rate >= 40 ? 'rgba(245,158,11,.12)' : 'rgba(239,68,68,.12)');
    $dailyTarget = $dailyTarget ?? 20;
    $todayTotal = $stats['completed_today'] + $stats['cancelled_today'];
    $targetPct = $dailyTarget > 0 ? min(100, round(($todayTotal / $dailyTarget) * 100)) : 0;
    $targetHex = $targetPct >= 100 ? '#10b981' : ($targetPct >= 60 ? '#f59e0b' : '#3b82f6');
@endphp

{{-- ═══ Hero CTA ═══ --}}
<div class="hero-banner mb-4">
    <div class="hero-content d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-chart-pie" style="font-size:1.6rem; opacity:.9;"></i>
                <h4 class="mb-0 fw-bold">Mon tableau de bord</h4>
            </div>
            <p class="mb-2" style="opacity:.85; font-size:.9rem;">
                @if($totalActive > 0)
                    {{ $totalActive }} commande(s) assignée(s) à traiter
                @else
                    Aucune commande en attente — excellent travail !
                @endif
            </p>
            <div class="d-flex gap-2 flex-wrap">
                @if($totalActive > 0)
                    <span class="hero-pill"><i class="fas fa-clock"></i> {{ $stats['pending'] }} en attente</span>
                    <span class="hero-pill"><i class="fas fa-spinner"></i> {{ $stats['in_progress'] }} en cours</span>
                    @if($stats['scheduled_callbacks'] > 0)
                        <span class="hero-pill"><i class="fas fa-calendar-check"></i> {{ $stats['scheduled_callbacks'] }} rappels</span>
                    @endif
                @else
                    <span class="hero-pill"><i class="fas fa-check-circle"></i> Tout traité</span>
                @endif
            </div>
        </div>
        <a href="{{ route('confirmi.employee.orders.index') }}" class="hero-btn">
            <i class="fas fa-list"></i>
            @if($totalActive > 0) Mes commandes ({{ $totalActive }}) @else Voir mes commandes @endif
        </a>
    </div>
</div>

{{-- ═══ KPI Tiles ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="pi-tile">
            <div class="d-flex align-items-center gap-3">
                <div class="pi-tile-icon t-orange"><i class="fas fa-phone-volume"></i></div>
                <div><div class="pi-tile-val">{{ $stats['pending'] }}</div><div class="pi-tile-lbl">En attente</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="pi-tile">
            <div class="d-flex align-items-center gap-3">
                <div class="pi-tile-icon t-cyan"><i class="fas fa-spinner"></i></div>
                <div><div class="pi-tile-val">{{ $stats['in_progress'] }}</div><div class="pi-tile-lbl">En cours</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="pi-tile">
            <div class="d-flex align-items-center gap-3">
                <div class="pi-tile-icon t-green"><i class="fas fa-check-circle"></i></div>
                <div><div class="pi-tile-val">{{ $stats['completed_today'] }}</div><div class="pi-tile-lbl">Confirmées</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="pi-tile">
            <div class="d-flex align-items-center gap-3">
                <div class="pi-tile-icon t-red"><i class="fas fa-times-circle"></i></div>
                <div><div class="pi-tile-val">{{ $stats['cancelled_today'] }}</div><div class="pi-tile-lbl">Annulées</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="pi-tile">
            <div class="d-flex align-items-center gap-3">
                <div class="pi-tile-icon t-purple"><i class="fas fa-calendar-check"></i></div>
                <div><div class="pi-tile-val">{{ $stats['scheduled_callbacks'] }}</div><div class="pi-tile-lbl">Rappels</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="pi-tile">
            <div class="d-flex align-items-center gap-3">
                <div class="pi-tile-icon t-blue"><i class="fas fa-phone-alt"></i></div>
                <div><div class="pi-tile-val">{{ $stats['total_attempts'] }}</div><div class="pi-tile-lbl">Total appels</div></div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Objectif + Taux ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="pi-tile">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-bullseye" style="color:{{ $targetHex }};"></i>
                    <span class="fw-bold" style="font-size:.88rem; color:var(--text);">Objectif du jour</span>
                </div>
                <span style="font-weight:800; font-size:.95rem; color:{{ $targetHex }};">{{ $todayTotal }}/{{ $dailyTarget }}</span>
            </div>
            <div class="obj-bar">
                <div class="obj-fill" style="width:{{ $targetPct }}%; background:{{ $targetHex }};"></div>
            </div>
            <div class="d-flex justify-content-between mt-2" style="font-size:.72rem; color:var(--text-secondary);">
                <span>{{ $stats['completed_today'] }} confirmées + {{ $stats['cancelled_today'] }} annulées</span>
                <span>{{ $targetPct }}%</span>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="pi-tile">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-trophy" style="color:{{ $rateHex }};"></i>
                    <span class="fw-bold" style="font-size:.88rem; color:var(--text);">Taux de réussite</span>
                </div>
                <div class="rate-badge" style="background:{{ $ringBg }}; color:{{ $rateHex }};">{{ $rate }}%</div>
            </div>
            <div class="d-flex justify-content-around text-center" style="font-size:.78rem;">
                <div>
                    <div style="font-weight:800; font-size:1.05rem; color:var(--success);">{{ $stats['total_completed'] }}</div>
                    <div style="color:var(--text-secondary);">Confirmées</div>
                </div>
                <div>
                    <div style="font-weight:800; font-size:1.05rem; color:var(--danger);">{{ $stats['total_cancelled'] }}</div>
                    <div style="color:var(--text-secondary);">Annulées</div>
                </div>
                <div>
                    <div style="font-weight:800; font-size:1.05rem; color:var(--accent-light);">{{ $stats['total_attempts'] }}</div>
                    <div style="color:var(--text-secondary);">Appels</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Performance 7 jours ═══ --}}
<div class="content-card mb-4">
    <div class="card-header-custom">
        <h6 class="mb-0"><i class="fas fa-chart-bar me-2" style="color:var(--accent);"></i>Performance 7 jours</h6>
        <span style="font-size:.72rem; color:var(--text-secondary);">
            <span style="display:inline-block; width:8px; height:8px; background:var(--success); border-radius:2px; margin-right:3px;"></span>Confirmées
            <span style="display:inline-block; width:8px; height:8px; background:var(--danger); border-radius:2px; margin:0 3px 0 10px;"></span>Annulées
        </span>
    </div>
    <div class="p-3">
        @php $maxVal = max(1, collect($weeklyStats)->max(fn($d) => $d['confirmed'] + $d['cancelled'])); @endphp
        <div class="w-chart">
            @foreach($weeklyStats as $day)
            <div class="w-bar-group">
                <div class="w-bars">
                    <div class="w-bar ok" style="height:{{ ($day['confirmed'] / $maxVal) * 85 }}px;" title="{{ $day['confirmed'] }} confirmées"></div>
                    <div class="w-bar ko" style="height:{{ ($day['cancelled'] / $maxVal) * 85 }}px;" title="{{ $day['cancelled'] }} annulées"></div>
                </div>
                <div class="w-day">{{ $day['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══ Accès rapide ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <a href="{{ route('confirmi.employee.orders.search') }}" class="pi-link">
            <div style="width:48px; height:48px; background:rgba(255,255,255,.15); border-radius:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-search" style="font-size:1.2rem;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700; font-size:.95rem;">Rechercher des commandes</div>
                <div style="opacity:.8; font-size:.8rem;">Parcourir les commandes non assignées</div>
            </div>
            <i class="fas fa-arrow-right" style="opacity:.7;"></i>
        </a>
    </div>
    <div class="col-lg-5">
        <div class="d-grid gap-2" style="grid-template-columns:1fr 1fr 1fr;">
            <a href="{{ route('confirmi.employee.products.index') }}" class="qn-btn"><i class="fas fa-boxes" style="color:var(--success);"></i>Produits</a>
            <a href="{{ route('confirmi.employee.orders.history') }}" class="qn-btn"><i class="fas fa-history" style="color:var(--success);"></i>Historique</a>
            <a href="{{ route('confirmi.employee.profile') }}" class="qn-btn"><i class="fas fa-chart-line" style="color:#8b5cf6;"></i>Performance</a>
        </div>
    </div>
</div>

{{-- ═══ Commandes actives ═══ --}}
<div class="content-card">
    <div class="card-header-custom">
        <h6 class="mb-0"><i class="fas fa-phone-volume me-2" style="color:var(--accent);"></i>Commandes à traiter</h6>
        <span class="badge" style="background:var(--accent); color:#fff; border-radius:10px; padding:.3rem .7rem; font-weight:700;">{{ $activeAssignments->count() }}</span>
    </div>
    @if($activeAssignments->count() > 0)
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Tentatives</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeAssignments as $assignment)
                <tr>
                    <td><strong style="color:var(--text);">#{{ $assignment->order->id ?? '-' }}</strong></td>
                    <td><small style="color:var(--text-secondary);">{{ $assignment->admin->shop_name ?? $assignment->admin->name ?? '-' }}</small></td>
                    <td style="color:var(--text);">{{ $assignment->order->customer_name ?? 'N/A' }}</td>
                    <td>
                        <a href="tel:{{ $assignment->order->customer_phone ?? '' }}" class="fw-semibold text-decoration-none" style="color:var(--accent);">
                            {{ $assignment->order->customer_phone ?? 'N/A' }}
                        </a>
                    </td>
                    <td><span class="badge" style="background:var(--border); color:var(--text); border-radius:8px;">{{ $assignment->attempts }}</span></td>
                    <td>
                        <span class="badge-status badge-{{ $assignment->status }}">
                            {{ $assignment->status === 'assigned' ? 'Assignée' : 'En cours' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('confirmi.employee.orders.show', $assignment) }}" class="btn btn-sm btn-royal">
                            <i class="fas fa-eye me-1"></i>Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x d-block mb-3" style="color:var(--text-secondary); opacity:.3;"></i>
        <p style="color:var(--text-secondary);" class="mb-0">Aucune commande à traiter pour le moment.</p>
        <small style="color:var(--text-secondary);">Les nouvelles assignations apparaîtront automatiquement.</small>
    </div>
    @endif
</div>
@endsection
