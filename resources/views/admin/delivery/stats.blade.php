@extends('layouts.admin')

@section('title', 'Statistiques de livraison')

@section('css')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.stat-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.chart-container {
    position: relative;
    height: 300px;
}

.metric-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.metric-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.trend-indicator {
    font-size: 0.8rem;
    font-weight: 600;
}

.trend-up {
    color: #10b981;
}

.trend-down {
    color: #ef4444;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Statistiques de livraison
                    </h1>
                    <p class="text-muted mb-0">Tableau de bord et analytiques des performances de livraison</p>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select" id="periodSelect" onchange="changePeriod(this.value)">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 derniers jours</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 derniers jours</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 derniers jours</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>1 an</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
                </div>
            </div>

            <!-- KPIs principaux -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="kpi-grid">
                        <!-- Total expéditions -->
                        <div class="card stat-card border-primary">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-shipping-fast fa-2x text-primary"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-primary">{{ number_format($shipmentStats['total'] ?? 0) }}</div>
                                <div class="metric-label text-muted">Total expéditions</div>
                                @if(isset($shipmentStats['total_trend']))
                                    <div class="trend-indicator {{ $shipmentStats['total_trend'] >= 0 ? 'trend-up' : 'trend-down' }}">
                                        <i class="fas fa-{{ $shipmentStats['total_trend'] >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                        {{ abs($shipmentStats['total_trend']) }}% vs période précédente
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Taux de livraison -->
                        <div class="card stat-card border-success">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-success">{{ number_format($shipmentStats['delivery_rate'] ?? 0, 1) }}%</div>
                                <div class="metric-label text-muted">Taux de livraison</div>
                                <div class="small text-muted mt-2">
                                    {{ number_format($shipmentStats['delivered'] ?? 0) }} sur {{ number_format($shipmentStats['total'] ?? 0) }}
                                </div>
                            </div>
                        </div>

                        <!-- Délai moyen -->
                        <div class="card stat-card border-info">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-clock fa-2x text-info"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-info">{{ $shipmentStats['avg_delivery_time'] ?? 0 }}</div>
                                <div class="metric-label text-muted">Délai moyen (jours)</div>
                                @if(isset($shipmentStats['delivery_time_trend']))
                                    <div class="trend-indicator {{ $shipmentStats['delivery_time_trend'] <= 0 ? 'trend-up' : 'trend-down' }}">
                                        <i class="fas fa-{{ $shipmentStats['delivery_time_trend'] <= 0 ? 'arrow-down' : 'arrow-up' }} me-1"></i>
                                        {{ abs($shipmentStats['delivery_time_trend']) }} jour(s) vs précédent
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Valeur totale -->
                        <div class="card stat-card border-warning">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-coins fa-2x text-warning"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-warning">{{ number_format($shipmentStats['total_value'] ?? 0, 0) }}</div>
                                <div class="metric-label text-muted">Valeur totale (DT)</div>
                                <div class="small text-muted mt-2">
                                    Moyenne: {{ number_format($shipmentStats['avg_value'] ?? 0, 3) }} DT
                                </div>
                            </div>
                        </div>

                        <!-- Taux d'anomalies -->
                        <div class="card stat-card border-danger">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-danger">{{ number_format($shipmentStats['anomaly_rate'] ?? 0, 1) }}%</div>
                                <div class="metric-label text-muted">Taux d'anomalies</div>
                                <div class="small text-muted mt-2">
                                    {{ number_format($shipmentStats['anomalies'] ?? 0) }} anomalie(s)
                                </div>
                            </div>
                        </div>

                        <!-- Enlèvements actifs -->
                        <div class="card stat-card border-secondary">
                            <div class="card-body text-center">
                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-warehouse fa-2x text-secondary"></i>
                                    </div>
                                </div>
                                <div class="metric-value text-secondary">{{ number_format($pickupStats['active'] ?? 0) }}</div>
                                <div class="metric-label text-muted">Enlèvements actifs</div>
                                <div class="small text-muted mt-2">
                                    {{ number_format($pickupStats['total'] ?? 0) }} total
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mb-4">
                <!-- Évolution des expéditions -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Évolution des expéditions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="shipmentsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par statut -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Répartition par statut
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Performance par transporteur -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-truck me-2"></i>
                                Performance par transporteur
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($shipmentStats['by_carrier']) && count($shipmentStats['by_carrier']) > 0)
                                @foreach($shipmentStats['by_carrier'] as $carrier => $stats)
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">{{ ucfirst($carrier) }}</h6>
                                            <span class="badge bg-primary">{{ $stats['total'] }} expéditions</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <div class="h5 text-success mb-1">{{ $stats['delivery_rate'] }}%</div>
                                                    <small class="text-muted">Taux livraison</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <div class="h5 text-info mb-1">{{ $stats['avg_time'] }}j</div>
                                                    <small class="text-muted">Délai moyen</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <div class="h5 text-warning mb-1">{{ $stats['avg_value'] }}DT</div>
                                                    <small class="text-muted">Valeur moy.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-3">
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-truck fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune donnée de transporteur disponible</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Analyse des retours -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-undo me-2"></i>
                                Analyse des retours
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(isset($returnAnalysis) && count($returnAnalysis) > 0)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Taux de retour global</span>
                                        <span class="badge bg-{{ $returnAnalysis['rate'] > 10 ? 'danger' : ($returnAnalysis['rate'] > 5 ? 'warning' : 'success') }}">
                                            {{ $returnAnalysis['rate'] }}%
                                        </span>
                                    </div>
                                </div>

                                @foreach($returnAnalysis['reasons'] ?? [] as $reason => $count)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">{{ $reason }}</small>
                                            <small class="fw-bold">{{ $count }}</small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" style="width: {{ ($count / max($returnAnalysis['reasons']) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach

                                <hr class="my-3">
                                
                                <div class="text-center">
                                    <h6 class="text-primary">{{ $returnAnalysis['total_returns'] ?? 0 }}</h6>
                                    <small class="text-muted">Total retours</small>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-undo fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun retour enregistré</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau de bord temps réel -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de bord temps réel
                            </h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-circle me-1"></i>En direct
                                </span>
                                <small class="text-muted">Dernière MAJ: <span id="lastUpdate">{{ now()->format('H:i:s') }}</span></small>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(isset($dashboard) && count($dashboard) > 0)
                                <div class="row" id="realtimeDashboard">
                                    <!-- Expéditions du jour -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body text-center">
                                                <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                                                <h4 class="text-primary">{{ $dashboard['today_shipments'] ?? 0 }}</h4>
                                                <p class="text-muted mb-0">Expéditions aujourd'hui</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- En cours de traitement -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body text-center">
                                                <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                                                <h4 class="text-warning">{{ $dashboard['in_progress'] ?? 0 }}</h4>
                                                <p class="text-muted mb-0">En cours</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Livrées aujourd'hui -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body text-center">
                                                <i class="fas fa-check-double fa-2x text-success mb-2"></i>
                                                <h4 class="text-success">{{ $dashboard['today_delivered'] ?? 0 }}</h4>
                                                <p class="text-muted mb-0">Livrées aujourd'hui</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Alertes -->
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card bg-light border-0 mb-3">
                                            <div class="card-body text-center">
                                                <i class="fas fa-bell fa-2x text-danger mb-2"></i>
                                                <h4 class="text-danger">{{ $dashboard['alerts'] ?? 0 }}</h4>
                                                <p class="text-muted mb-0">Alertes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-tachometer-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tableau de bord en cours d'initialisation...</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let shipmentsChart, statusChart;

$(document).ready(function() {
    console.log('Page des statistiques chargée');
    
    initializeCharts();
    
    // Auto-refresh du tableau de bord toutes les 30 secondes
    setInterval(function() {
        if (!document.hidden) {
            refreshRealtimeDashboard();
        }
    }, 30000);
});

function initializeCharts() {
    // Graphique d'évolution des expéditions
    const shipmentsCtx = document.getElementById('shipmentsChart').getContext('2d');
    shipmentsChart = new Chart(shipmentsCtx, {
        type: 'line',
        data: {
            labels: @json($shipmentStats['timeline']['labels'] ?? []),
            datasets: [{
                label: 'Expéditions créées',
                data: @json($shipmentStats['timeline']['created'] ?? []),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Expéditions livrées',
                data: @json($shipmentStats['timeline']['delivered'] ?? []),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Graphique de répartition par statut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($shipmentStats['status_distribution']['labels'] ?? []),
            datasets: [{
                data: @json($shipmentStats['status_distribution']['data'] ?? []),
                backgroundColor: [
                    '#3b82f6', // created
                    '#f59e0b', // picked_up
                    '#06b6d4', // in_transit
                    '#10b981', // delivered
                    '#ef4444', // anomaly
                    '#6b7280'  // others
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function changePeriod(days) {
    const url = new URL(window.location);
    url.searchParams.set('days', days);
    window.location.href = url.toString();
}

function refreshStats() {
    showNotification('info', 'Actualisation des statistiques...');
    location.reload();
}

function refreshRealtimeDashboard() {
    fetch('{{ route("admin.delivery.api.stats") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.stats) {
            updateRealtimeDashboard(data.stats);
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
        }
    })
    .catch(error => {
        console.error('Erreur lors de l\'actualisation:', error);
    });
}

function updateRealtimeDashboard(stats) {
    // Mettre à jour les valeurs en temps réel
    const dashboard = document.getElementById('realtimeDashboard');
    if (dashboard && stats) {
        // Mise à jour des métriques temps réel
        // Cette fonction peut être étendue selon les données disponibles
    }
}

function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const icon = type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="${icon} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection