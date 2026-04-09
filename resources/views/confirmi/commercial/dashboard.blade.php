@extends('confirmi.layouts.app')
@section('title', 'Dashboard Commercial')
@section('page-title', 'Dashboard Commercial')

@section('css')
<style>
.dash-banner {
    background: linear-gradient(135deg, var(--accent, #1e40af), #2563eb 60%, #6366f1);
    border-radius: var(--radius-lg); padding: 1.75rem 2rem;
    color: white; position: relative; overflow: hidden; margin-bottom: 1.25rem;
    box-shadow: 0 10px 25px -5px rgba(30,64,175,.25);
}
.dash-banner::before {
    content:''; position:absolute; top:-50%; right:-10%;
    width:300px; height:300px; background:rgba(255,255,255,.06);
    border-radius:50%;
}
.dash-banner h2 { font-weight:800; font-size:1.4rem; margin:0; position:relative; z-index:1; }
.dash-banner p { opacity:.85; font-size:.88rem; margin:.35rem 0 0; position:relative; z-index:1; }
.dash-banner-stats {
    display:flex; gap:1.5rem; margin-top:1rem; flex-wrap:wrap; position:relative; z-index:1;
}
.dash-banner-stat { text-align:center; }
.dash-banner-stat .val { font-size:1.6rem; font-weight:800; line-height:1; }
.dash-banner-stat .lbl { font-size:.7rem; opacity:.75; margin-top:.15rem; }

.progress-bar-thin {
    width:55px; height:5px; background:var(--border); border-radius:3px; overflow:hidden;
}
.progress-bar-thin .fill { height:100%; border-radius:3px; }

.live-item {
    display:flex; align-items:center; gap:.65rem;
    padding:.65rem .85rem; border-radius:var(--radius, 10px);
    background:var(--bg-hover); margin-bottom:.4rem;
    transition: all .2s; border: 1px solid transparent;
}
.live-item:hover { transform:translateX(3px); border-color: var(--border); box-shadow: var(--shadow); }
</style>
@endsection

@section('content')
{{-- ═══ Banner ═══ --}}
<div class="dash-banner">
    <h2><i class="fas fa-chart-pie me-2"></i>Tableau de bord</h2>
    <p>Vue d'ensemble de l'activité Confirmi</p>
    <div class="dash-banner-stats">
        <div class="dash-banner-stat">
            <div class="val" id="live-pending">{{ $stats['pendingAssignments'] }}</div>
            <div class="lbl">En attente</div>
        </div>
        <div class="dash-banner-stat">
            <div class="val" id="live-progress">{{ $stats['inProgressAssignments'] }}</div>
            <div class="lbl">En cours</div>
        </div>
        <div class="dash-banner-stat">
            <div class="val" id="live-today">{{ $stats['completedToday'] }}</div>
            <div class="lbl">Terminées aujourd'hui</div>
        </div>
        <div class="dash-banner-stat">
            <div class="val">{{ $stats['totalAssignments'] }}</div>
            <div class="lbl">Total</div>
        </div>
    </div>
</div>

{{-- ═══ Stat Cards Row ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-blue"><i class="fas fa-building"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['activeAdmins'] }}</div>
                    <div class="stat-label">Clients actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-green"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['activeEmployees'] }}<small style="font-size:.7rem;color:var(--text-secondary);">/{{ $stats['totalEmployees'] }}</small></div>
                    <div class="stat-label">Employés actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-orange"><i class="fas fa-inbox"></i></div>
                <div>
                    <div class="stat-value" id="live-requests">{{ $stats['pendingRequests'] }}</div>
                    <div class="stat-label">Demandes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon icon-purple"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pendingAssignments'] }}</div>
                    <div class="stat-label">Non assignées</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Chart + Quick Actions ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <h6><i class="fas fa-chart-bar me-2" style="color:var(--accent);"></i>Performance 7 jours</h6>
            </div>
            <div class="p-3">
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <h6><i class="fas fa-bolt me-2" style="color:var(--warning);"></i>Actions rapides</h6>
            </div>
            <div class="p-3 d-grid gap-2">
                <a href="{{ route('confirmi.commercial.orders.pending') }}" class="btn btn-royal">
                    <i class="fas fa-clock me-2"></i>En attente ({{ $stats['pendingAssignments'] }})
                </a>
                <a href="{{ route('confirmi.commercial.requests.index') }}" class="btn btn-outline-royal">
                    <i class="fas fa-inbox me-2"></i>Demandes ({{ $stats['pendingRequests'] }})
                </a>
                <a href="{{ route('confirmi.commercial.orders.index') }}" class="btn btn-outline-royal">
                    <i class="fas fa-list me-2"></i>Toutes les commandes
                </a>
                <a href="{{ route('confirmi.commercial.employees.index') }}" class="btn btn-outline-royal">
                    <i class="fas fa-users me-2"></i>Gérer l'équipe
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Unassigned Orders ═══ --}}
@if($unassignedOrders->count() > 0)
<div class="content-card mb-4">
    <div class="card-header-custom">
        <h6><i class="fas fa-exclamation-triangle me-2" style="color:var(--warning);"></i>Commandes non assignées</h6>
        <a href="{{ route('confirmi.commercial.orders.pending') }}" class="btn btn-sm btn-royal">Voir tout</a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($unassignedOrders as $assignment)
                <tr>
                    <td><strong>#{{ $assignment->order->id ?? 'N/A' }}</strong></td>
                    <td>{{ $assignment->admin->shop_name ?? $assignment->admin->name ?? 'N/A' }}</td>
                    <td>{{ $assignment->order->customer_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->order->customer_phone ?? 'N/A' }}</td>
                    <td><small>{{ $assignment->created_at->format('d/m H:i') }}</small></td>
                    <td>
                        <a href="{{ route('confirmi.commercial.orders.show', $assignment) }}" class="btn btn-sm btn-outline-royal"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ Employee Performance ═══ --}}
@if($employeeStats->count() > 0)
<div class="content-card mb-4">
    <div class="card-header-custom">
        <h6><i class="fas fa-chart-line me-2" style="color:var(--success);"></i>Performance des employés</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>En file</th>
                    <th>Confirmées</th>
                    <th>Annulées</th>
                    <th>Total</th>
                    <th>Taux succès</th>
                    <th>Moy. tent.</th>
                    <th>Aujourd'hui</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employeeStats as $emp)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:30px;height:30px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;">{{ strtoupper(substr($emp->name, 0, 1)) }}</div>
                            <div>
                                <strong style="font-size:.82rem;">{{ $emp->name }}</strong>
                                <br><small style="color:var(--text-secondary);font-size:.7rem;">{{ $emp->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-status badge-pending">{{ $emp->pending_orders }}</span></td>
                    <td><span class="badge-status badge-confirmed">{{ $emp->confirmed_orders }}</span></td>
                    <td><span class="badge-status badge-cancelled">{{ $emp->cancelled_orders }}</span></td>
                    <td><strong>{{ $emp->total_orders }}</strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress-bar-thin">
                                <div class="fill" style="width:{{ $emp->success_rate }}%;background:{{ $emp->success_rate >= 70 ? 'var(--success)' : ($emp->success_rate >= 40 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                            </div>
                            <small class="fw-bold" style="font-size:.75rem;">{{ $emp->success_rate }}%</small>
                        </div>
                    </td>
                    <td>{{ number_format($emp->avg_attempts ?? 0, 1) }}</td>
                    <td><span class="badge-status badge-assigned">{{ $emp->today_completed }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ Real-Time Activity Feed ═══ --}}
<div class="content-card" id="live-feed">
    <div class="card-header-custom">
        <h6><i class="fas fa-broadcast-tower me-2" style="color:var(--danger);"></i>Activité en temps réel</h6>
        <small style="color:var(--text-secondary);" id="live-timestamp">--:--:--</small>
    </div>
    <div id="live-activity" class="p-3">
        <div class="text-center py-3" style="color:var(--text-secondary);">
            <i class="fas fa-spinner fa-spin me-2"></i>Chargement...
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('weeklyChart').getContext('2d');
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const textColor = isDark ? '#94a3b8' : '#64748b';

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($weeklyStats)->pluck('date')) !!},
        datasets: [{
            label: 'Nouvelles',
            data: {!! json_encode(collect($weeklyStats)->pluck('new')) !!},
            backgroundColor: 'rgba(37,99,235,0.2)',
            borderColor: '#2563eb',
            borderWidth: 2,
            borderRadius: 6,
        }, {
            label: 'Terminées',
            data: {!! json_encode(collect($weeklyStats)->pluck('completed')) !!},
            backgroundColor: 'rgba(16,185,129,0.2)',
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { color: textColor } } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
            x: { ticks: { color: textColor }, grid: { color: gridColor } }
        }
    }
});

function fetchLiveStats() {
    fetch('{{ route("confirmi.dashboard.live-stats") }}')
        .then(r => r.json())
        .then(data => {
            document.getElementById('live-timestamp').textContent = data.timestamp;
            var el;
            if (el = document.getElementById('live-pending')) el.textContent = data.pendingAssignments;
            if (el = document.getElementById('live-progress')) el.textContent = data.inProgressAssignments;
            if (el = document.getElementById('live-today')) el.textContent = data.completedToday;
            if (el = document.getElementById('live-requests')) el.textContent = data.pendingRequests;

            const feed = document.getElementById('live-activity');
            if (data.recentCompleted.length === 0) {
                feed.innerHTML = '<div class="text-center py-3" style="color:var(--text-secondary);">Aucune activité aujourd\'hui</div>';
                return;
            }
            let html = '';
            data.recentCompleted.forEach(a => {
                const isOk = a.status === 'confirmed';
                const color = isOk ? 'var(--success)' : 'var(--danger)';
                const icon = isOk ? 'fa-check-circle' : 'fa-times-circle';
                const label = isOk ? 'Confirmée' : 'Annulée';
                const badgeCls = isOk ? 'badge-confirmed' : 'badge-cancelled';
                html += `<div class="live-item">
                    <i class="fas ${icon}" style="color:${color};font-size:1rem;"></i>
                    <div style="flex:1;min-width:0;">
                        <strong style="font-size:.82rem;color:var(--text);">#${a.order_id}</strong>
                        <span style="color:var(--text-secondary);font-size:.82rem;"> — ${a.customer}</span>
                        <br><small style="color:var(--text-muted);font-size:.72rem;">${a.employee} · ${a.time}</small>
                    </div>
                    <span class="badge-status ${badgeCls}">${label}</span>
                </div>`;
            });
            feed.innerHTML = html;
        })
        .catch(() => {});
}

fetchLiveStats();
setInterval(fetchLiveStats, 15000);
</script>
@endsection
