@extends('confirmi.layouts.app')
@section('title', 'Performance & Profil')
@section('page-title', 'Ma Performance')

@section('css')
<style>
/* ═══ Profile Header (process-interface gradient) ═══ */
.pi-profile-header {
    background: linear-gradient(135deg, var(--accent, #2563eb) 0%, #6366f1 40%, #7c3aed 100%);
    border-radius: 20px;
    padding: 2rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}
.pi-profile-header::before {
    content: '';
    position: absolute;
    top: -60%; right: -15%;
    width: 350px; height: 350px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.pi-avatar {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    border: 3px solid rgba(255,255,255,.3);
    flex-shrink: 0;
}

/* ═══ KPI Tiles ═══ */
.pi-kpi {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all .25s;
}
.pi-kpi:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.pi-kpi-ico {
    width: 46px; height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.pi-kpi-ico.k-green { background: #d1fae5; color: #059669; }
.pi-kpi-ico.k-red   { background: #fee2e2; color: #dc2626; }
.pi-kpi-ico.k-blue  { background: #ede9fe; color: #4f46e5; }
.pi-kpi-ico.k-amber { background: #fef3c7; color: #d97706; }
.pi-kpi-val { font-size: 1.75rem; font-weight: 800; line-height: 1; color: var(--text); }
.pi-kpi-lbl { font-size: .78rem; color: var(--text-secondary); margin-top: .2rem; font-weight: 500; }

.val-green  { color: var(--success); }
.val-red    { color: var(--danger); }
.val-amber  { color: #d97706; }
.val-purple { color: #7c3aed; }

[data-theme="dark"] .pi-kpi-ico.k-green  { background: rgba(16,185,129,.12); color: #34d399; }
[data-theme="dark"] .pi-kpi-ico.k-red    { background: rgba(239,68,68,.12); color: #f87171; }
[data-theme="dark"] .pi-kpi-ico.k-blue   { background: rgba(79,70,229,.12); color: #a78bfa; }
[data-theme="dark"] .pi-kpi-ico.k-amber  { background: rgba(217,119,6,.12); color: #fbbf24; }
[data-theme="dark"] .val-amber  { color: #fbbf24; }
[data-theme="dark"] .val-purple { color: #a78bfa; }

/* ═══ Performance Card ═══ */
.pi-perf {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

/* ═══ Chart Bars ═══ */
.c-bar-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
    min-width: 0;
}
.c-bar-track {
    width: 100%;
    max-width: 18px;
    height: 90px;
    background: var(--border);
    border-radius: 6px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.c-fill-ok {
    background: linear-gradient(to top, #10b981, #34d399);
    border-radius: 4px 4px 0 0;
    transition: height .5s ease;
}
.c-fill-ko {
    background: linear-gradient(to top, #ef4444, #f87171);
    transition: height .5s ease;
}
.c-day-lbl { font-size: .58rem; color: var(--text-secondary); white-space: nowrap; }
.c-day-val { font-size: .62rem; font-weight: 700; color: var(--text); }

/* ═══ Activity Journal ═══ */
.act-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .7rem 0;
    border-bottom: 1px solid var(--border);
}
.act-item:last-child { border-bottom: none; }
.act-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.act-dot.confirmed { background: var(--success); }
.act-dot.cancelled { background: var(--danger); }
.act-dot.delivered { background: var(--accent); }

/* ═══ Quick Tiles ═══ */
.pi-qtile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .4rem;
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
.pi-qtile:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-2px); box-shadow: var(--shadow-md); }
.pi-qtile i { font-size: 1.3rem; }

@media (max-width: 768px) {
    .pi-profile-header { padding: 1.5rem; }
    .pi-kpi-val { font-size: 1.4rem; }
    .c-bar-track { height: 65px; max-width: 14px; }
}
</style>
@endsection

@section('content')
{{-- ═══ Profile Header ═══ --}}
<div class="pi-profile-header mb-4">
    <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1;">
        <div class="pi-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
        <div>
            <h4 class="mb-0 fw-bold">{{ $user->name }}</h4>
            <div class="d-flex align-items-center gap-2 mt-1" style="opacity:.8;">
                <i class="fas fa-envelope fa-sm"></i>
                <span style="font-size:.85rem;">{{ $user->email }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1" style="opacity:.8;">
                <i class="fas fa-calendar fa-sm"></i>
                <span style="font-size:.85rem;">Membre depuis {{ $user->created_at->translatedFormat('M Y') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ═══ KPI Row — Today ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pi-kpi">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="pi-kpi-ico k-green"><i class="fas fa-check"></i></div>
            </div>
            <div class="pi-kpi-val val-green">{{ $todayConfirmed }}</div>
            <div class="pi-kpi-lbl">Confirmées aujourd'hui</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pi-kpi">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="pi-kpi-ico k-red"><i class="fas fa-times"></i></div>
            </div>
            <div class="pi-kpi-val val-red">{{ $todayCancelled }}</div>
            <div class="pi-kpi-lbl">Annulées aujourd'hui</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pi-kpi">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="pi-kpi-ico k-blue"><i class="fas fa-percentage"></i></div>
            </div>
            <div class="pi-kpi-val" style="color:var(--accent);">{{ $successRate }}%</div>
            <div class="pi-kpi-lbl">Taux de succès global</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pi-kpi">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="pi-kpi-ico k-amber"><i class="fas fa-tachometer-alt"></i></div>
            </div>
            <div class="pi-kpi-val val-amber">{{ $avgDaily }}</div>
            <div class="pi-kpi-lbl">Moyenne / jour (7j)</div>
        </div>
    </div>
</div>

{{-- ═══ Global Stats ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="pi-kpi text-center">
            <div class="pi-kpi-val val-green">{{ $totalConfirmed }}</div>
            <div class="pi-kpi-lbl">Total confirmées</div>
        </div>
    </div>
    <div class="col-4">
        <div class="pi-kpi text-center">
            <div class="pi-kpi-val val-red">{{ $totalCancelled }}</div>
            <div class="pi-kpi-lbl">Total annulées</div>
        </div>
    </div>
    <div class="col-4">
        <div class="pi-kpi text-center">
            <div class="pi-kpi-val val-purple">{{ $totalAttempts }}</div>
            <div class="pi-kpi-lbl">Total tentatives</div>
        </div>
    </div>
</div>

{{-- ═══ Success Rate Ring + 30-Day Chart ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="pi-perf text-center">
            <h6 class="fw-bold mb-3" style="color:var(--text);">Taux de Succès</h6>
            @php
                $ringColor = $successRate >= 70 ? '#10b981' : ($successRate >= 50 ? '#f59e0b' : '#ef4444');
                $dashLen = 364.4 * $successRate / 100;
            @endphp
            <svg width="140" height="140" viewBox="0 0 140 140">
                <circle cx="70" cy="70" r="58" fill="none" stroke="var(--border)" stroke-width="12"/>
                <circle cx="70" cy="70" r="58" fill="none"
                        stroke="{{ $ringColor }}" stroke-width="12" stroke-linecap="round"
                        stroke-dasharray="{{ $dashLen }} 364.4"
                        transform="rotate(-90 70 70)" />
                <text x="70" y="65" text-anchor="middle" font-size="28" font-weight="800" fill="{{ $ringColor }}">{{ $successRate }}%</text>
                <text x="70" y="85" text-anchor="middle" font-size="11" fill="var(--text-secondary)">succès</text>
            </svg>
            <div class="mt-3" style="font-size:.8rem; color:var(--text-secondary);">
                <i class="fas fa-info-circle"></i>
                {{ $totalConfirmed }} confirmées / {{ $totalConfirmed + $totalCancelled }} traitées
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="pi-perf">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0" style="color:var(--text);">Performance 30 jours</h6>
                <div class="d-flex gap-3" style="font-size:.75rem; color:var(--text-secondary);">
                    <span><i class="fas fa-circle fa-xs" style="color:var(--success);"></i> Confirmées</span>
                    <span><i class="fas fa-circle fa-xs" style="color:var(--danger);"></i> Annulées</span>
                </div>
            </div>
            @php $maxD = max(1, max(array_map(fn($d) => $d['confirmed'] + $d['cancelled'], $dailyStats))); @endphp
            <div class="d-flex align-items-end gap-1" style="overflow-x:auto; padding-bottom:4px;">
                @foreach($dailyStats as $day)
                    @php
                        $cH = $maxD > 0 ? ($day['confirmed'] / $maxD) * 100 : 0;
                        $xH = $maxD > 0 ? ($day['cancelled'] / $maxD) * 100 : 0;
                    @endphp
                    <div class="c-bar-group">
                        @if($day['confirmed'] + $day['cancelled'] > 0)
                            <div class="c-day-val">{{ $day['confirmed'] + $day['cancelled'] }}</div>
                        @endif
                        <div class="c-bar-track">
                            <div class="c-fill-ko" style="height:{{ $xH }}%;"></div>
                            <div class="c-fill-ok" style="height:{{ $cH }}%;"></div>
                        </div>
                        <div class="c-day-lbl">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ═══ Monthly Summary ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="pi-kpi">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="pi-kpi-lbl mb-1">30 jours — Confirmées</div>
                    <div class="pi-kpi-val val-green">{{ $monthlyConfirmed }}</div>
                </div>
                <div class="pi-kpi-ico k-green" style="width:52px; height:52px; font-size:1.3rem;">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="pi-kpi">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="pi-kpi-lbl mb-1">30 jours — Annulées</div>
                    <div class="pi-kpi-val val-red">{{ $monthlyCancelled }}</div>
                </div>
                <div class="pi-kpi-ico k-red" style="width:52px; height:52px; font-size:1.3rem;">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Recent Activity Journal ═══ --}}
<div class="pi-perf mb-4">
    <h6 class="fw-bold mb-3" style="color:var(--text);"><i class="fas fa-history me-2" style="color:var(--accent);"></i>Journal d'activité récente</h6>
    @forelse($recentActivity as $activity)
        <div class="act-item">
            <div class="act-dot {{ $activity->status }}"></div>
            <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:.88rem; color:var(--text);">
                    Commande #{{ $activity->order_id }}
                    @if($activity->order)
                        — {{ $activity->order->customer_name ?? 'Client' }}
                    @endif
                </div>
                <div style="font-size:.75rem; color:var(--text-secondary);">
                    {{ $activity->attempts }} tentative(s)
                    @if($activity->notes)
                        · {{ Str::limit($activity->notes, 50) }}
                    @endif
                </div>
            </div>
            <div class="text-end">
                <span class="badge-status badge-{{ $activity->status }}">
                    {{ match($activity->status) { 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', default => 'Livrée' } }}
                </span>
                <div style="font-size:.72rem; color:var(--text-secondary); margin-top:.2rem;">
                    {{ $activity->completed_at ? $activity->completed_at->diffForHumans() : '' }}
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-4" style="color:var(--text-secondary);">
            <i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:.3;"></i>
            Aucune activité récente
        </div>
    @endforelse
</div>

{{-- ═══ Quick Actions ═══ --}}
<div class="row g-3 mb-5">
    <div class="col-6 col-md-4">
        <a href="{{ route('confirmi.employee.dashboard') }}" class="pi-qtile">
            <i class="fas fa-tachometer-alt" style="color:var(--accent);"></i>
            Dashboard
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('confirmi.employee.products.index') }}" class="pi-qtile">
            <i class="fas fa-boxes" style="color:var(--accent-light);"></i>
            Catalogue produits
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('confirmi.employee.orders.history') }}" class="pi-qtile">
            <i class="fas fa-history" style="color:var(--warning);"></i>
            Historique
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.c-fill-ok, .c-fill-ko').forEach(function(el) {
        var h = el.style.height;
        el.style.height = '0%';
        requestAnimationFrame(function() {
            requestAnimationFrame(function() { el.style.height = h; });
        });
    });
});
</script>
@endsection
