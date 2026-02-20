@extends('confirmi.layouts.app')
@section('title', 'Dashboard Commercial')
@section('page-title', 'Dashboard Commercial')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-blue"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pendingAssignments'] }}</div>
                    <div class="stat-label">En attente</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-purple"><i class="fas fa-spinner"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['inProgressAssignments'] }}</div>
                    <div class="stat-label">En cours</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['completedToday'] }}</div>
                    <div class="stat-label">Terminées aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-orange"><i class="fas fa-inbox"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pendingRequests'] }}</div>
                    <div class="stat-label">Demandes en attente</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-blue"><i class="fas fa-building"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['activeAdmins'] }}</div>
                    <div class="stat-label">Clients actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-green"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['activeEmployees'] }}/{{ $stats['totalEmployees'] }}</div>
                    <div class="stat-label">Employés actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="stat-icon icon-purple"><i class="fas fa-list"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['totalAssignments'] }}</div>
                    <div class="stat-label">Total commandes</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Chart -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <h6><i class="fas fa-chart-bar me-2 text-primary"></i>Performance 7 derniers jours</h6>
            </div>
            <div class="p-3">
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="content-card h-100">
            <div class="card-header-custom">
                <h6><i class="fas fa-bolt me-2 text-warning"></i>Actions rapides</h6>
            </div>
            <div class="p-3 d-grid gap-2">
                <a href="{{ route('confirmi.commercial.orders.pending') }}" class="btn btn-royal">
                    <i class="fas fa-clock me-2"></i>Commandes en attente ({{ $stats['pendingAssignments'] }})
                </a>
                <a href="{{ route('confirmi.commercial.requests.index') }}" class="btn btn-outline-royal">
                    <i class="fas fa-inbox me-2"></i>Demandes d'activation ({{ $stats['pendingRequests'] }})
                </a>
                <a href="{{ route('confirmi.commercial.orders.index') }}" class="btn btn-outline-royal">
                    <i class="fas fa-list me-2"></i>Toutes les commandes
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Unassigned Orders -->
@if($unassignedOrders->count() > 0)
<div class="content-card">
    <div class="card-header-custom">
        <h6><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Commandes non assignées</h6>
        <a href="{{ route('confirmi.commercial.orders.pending') }}" class="btn btn-sm btn-royal">Voir tout</a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client (Admin)</th>
                    <th>Destinataire</th>
                    <th>Téléphone</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unassignedOrders as $assignment)
                <tr>
                    <td><strong>#{{ $assignment->order->id ?? 'N/A' }}</strong></td>
                    <td>{{ $assignment->admin->name ?? 'N/A' }}</td>
                    <td>{{ $assignment->order->customer_name ?? 'N/A' }}</td>
                    <td>{{ $assignment->order->customer_phone ?? 'N/A' }}</td>
                    <td>{{ $assignment->created_at->format('d/m H:i') }}</td>
                    <td>
                        <a href="{{ route('confirmi.commercial.orders.show', $assignment) }}" class="btn btn-sm btn-royal">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('weeklyChart').getContext('2d');
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
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endsection
