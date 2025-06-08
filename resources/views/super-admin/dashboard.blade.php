@extends('layouts.super-admin')

@section('title', 'Tableau de bord')

@section('breadcrumb')
    <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item active">Tableau de bord</li>
    </ol>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Tableau de bord</h1>
            <p class="page-subtitle">Vue d'ensemble de votre plateforme Order Manager</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="refreshData">
                <i class="fas fa-sync-alt me-2"></i>Actualiser
            </button>
            <a href="{{ route('super-admin.analytics.index') }}" class="btn btn-primary">
                <i class="fas fa-chart-line me-2"></i>Analytics détaillées
            </a>
        </div>
    </div>
@endsection

@section('css')
<style>
    .quick-stats {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-radius: 15px;
        color: white;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .chart-card {
        height: 400px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .activity-item {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    
    .activity-item:hover {
        background: var(--light-color);
        transform: translateX(5px);
    }
    
    .activity-item.success { border-left-color: var(--success-color); }
    .activity-item.warning { border-left-color: var(--warning-color); }
    .activity-item.danger { border-left-color: var(--danger-color); }
    .activity-item.info { border-left-color: var(--info-color); }
    
    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .metric-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-3px);
    }
    
    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .metric-label {
        font-size: 0.875rem;
        color: var(--secondary-color);
    }
    
    .system-health {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .health-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--success-color);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .action-card {
        text-align: center;
        padding: 25px;
        border-radius: 12px;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        color: inherit;
        text-decoration: none;
    }
    
    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 15px;
        color: white;
    }
</style>
@endsection

@section('content')
    <!-- Quick Stats Overview -->
    <div class="quick-stats">
        <div class="row g-0">
            <div class="col-md-3 text-center">
                <div class="metric-card bg-transparent border-0 text-white">
                    <div class="metric-value">{{ $stats['totalAdmins'] }}</div>
                    <div class="metric-label text-white-50">Total Admins</div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="metric-card bg-transparent border-0 text-white">
                    <div class="metric-value">{{ $stats['activeAdmins'] }}</div>
                    <div class="metric-label text-white-50">Actifs</div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="metric-card bg-transparent border-0 text-white">
                    <div class="metric-value">{{ number_format($stats['totalOrders']) }}</div>
                    <div class="metric-label text-white-50">Commandes</div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="metric-card bg-transparent border-0 text-white">
                    <div class="metric-value">{{ number_format($stats['totalRevenue']) }}€</div>
                    <div class="metric-label text-white-50">Revenus</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-content">
                    <div class="stats-text">
                        <div class="stats-number">{{ $stats['totalAdmins'] }}</div>
                        <div class="stats-label">Total Administrateurs</div>
                        @if($stats['adminGrowth'] != 0)
                            <small class="text-{{ $stats['adminGrowth'] > 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $stats['adminGrowth'] > 0 ? 'up' : 'down' }}"></i>
                                {{ abs($stats['adminGrowth']) }}% ce mois
                            </small>
                        @endif
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card success">
                <div class="stats-content">
                    <div class="stats-text">
                        <div class="stats-number">{{ $stats['activeAdmins'] }}</div>
                        <div class="stats-label">Administrateurs Actifs</div>
                        <small class="text-muted">
                            {{ $stats['totalAdmins'] > 0 ? round(($stats['activeAdmins'] / $stats['totalAdmins']) * 100, 1) : 0 }}% du total
                        </small>
                    </div>
                    <div class="stats-icon success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card warning">
                <div class="stats-content">
                    <div class="stats-text">
                        <div class="stats-number">{{ number_format($stats['totalOrders']) }}</div>
                        <div class="stats-label">Commandes Totales</div>
                        <small class="text-muted">{{ $stats['averageOrdersPerAdmin'] }} moy/admin</small>
                    </div>
                    <div class="stats-icon warning">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stats-card info">
                <div class="stats-content">
                    <div class="stats-text">
                        <div class="stats-number">{{ number_format($stats['totalRevenue']) }}€</div>
                        <div class="stats-label">Revenus Totaux</div>
                        <small class="text-muted">
                            {{ $stats['totalAdmins'] > 0 ? number_format($stats['totalRevenue'] / $stats['totalAdmins'], 0) : 0 }}€ moy/admin
                        </small>
                    </div>
                    <div class="stats-icon info">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes système -->
    @if($alerts->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Alertes Système
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($alerts as $alert)
                            <div class="alert alert-{{ $alert['type'] }} d-flex align-items-center" role="alert">
                                <i class="fas fa-{{ $alert['type'] === 'warning' ? 'exclamation-triangle' : ($alert['type'] === 'danger' ? 'times-circle' : 'info-circle') }} me-2"></i>
                                {{ $alert['message'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts and Data -->
    <div class="row g-4 mb-4">
        <!-- Graphique principal -->
        <div class="col-xl-8">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Activité de la Plateforme</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-chart="admins">Inscriptions</button>
                        <button type="button" class="btn btn-outline-primary" data-chart="orders">Commandes</button>
                        <button type="button" class="btn btn-outline-primary" data-chart="revenue">Revenus</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Health -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <div class="system-health">
                            <div class="health-indicator"></div>
                            État du Système
                        </div>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value text-primary">{{ $systemPerformance['cpu_usage'] }}%</div>
                                <div class="metric-label">CPU</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value text-success">{{ $systemPerformance['memory_usage'] }}%</div>
                                <div class="metric-label">Mémoire</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value text-warning">{{ $systemPerformance['disk_usage'] }}%</div>
                                <div class="metric-label">Disque</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value text-info">{{ $systemPerformance['response_time'] }}</div>
                                <div class="metric-label">Temps Rép.</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>Uptime: {{ $systemPerformance['uptime'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admins récents et Activité -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Administrateurs Récents</h5>
                    <a href="{{ route('super-admin.admins.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Voir tout
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentAdmins as $admin)
                        <div class="d-flex align-items-center mb-3">
                            <div class="user-avatar me-3">
                                {{ substr($admin->name, 0, 2) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $admin->name }}</h6>
                                <small class="text-muted">{{ $admin->shop_name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $admin->is_active ? 'success' : 'danger' }}">
                                    {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                                <div><small class="text-muted">{{ $admin->created_at->format('d/m/Y') }}</small></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun administrateur récent</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Activité Récente</h5>
                    <button class="btn btn-outline-secondary btn-sm" id="refreshActivity">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div id="recentActivity">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Chargement de l'activité...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('super-admin.admins.create') }}" class="action-card card">
                                <div class="action-icon bg-primary">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h6>Nouvel Admin</h6>
                                <small class="text-muted">Créer un nouvel administrateur</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('super-admin.analytics.index') }}" class="action-card card">
                                <div class="action-icon bg-info">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h6>Analytics</h6>
                                <small class="text-muted">Voir les analytics détaillées</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('super-admin.reports.index') }}" class="action-card card">
                                <div class="action-icon bg-success">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h6>Rapports</h6>
                                <small class="text-muted">Générer des rapports</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('super-admin.system.index') }}" class="action-card card">
                                <div class="action-icon bg-warning">
                                    <i class="fas fa-server"></i>
                                </div>
                                <h6>Système</h6>
                                <small class="text-muted">Monitoring et maintenance</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let mainChart;
    let chartData = @json($chartData ?? []);
    
    // Initialiser le graphique principal
    initMainChart();
    
    // Charger l'activité récente
    loadRecentActivity();
    
    // Actualisation automatique toutes les 5 minutes
    setInterval(function() {
        refreshStats();
        loadRecentActivity();
    }, 300000);
    
    // Gestionnaires d'événements
    document.getElementById('refreshData')?.addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualisation...';
        refreshStats();
        loadRecentActivity();
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Actualiser';
        }, 2000);
    });
    
    document.getElementById('refreshActivity')?.addEventListener('click', loadRecentActivity);
    
    // Changement de graphique
    document.querySelectorAll('[data-chart]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-chart]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const chartType = this.dataset.chart;
            updateChart(chartType);
        });
    });
    
    function initMainChart() {
        const ctx = document.getElementById('mainChart');
        if (!ctx) return;
        
        mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4
                    }
                }
            }
        });
        
        // Charger les données initiales
        updateChart('admins');
    }
    
    function updateChart(type) {
        if (!mainChart) return;
        
        // Simuler des données selon le type
        let data = generateChartData(type);
        
        mainChart.data.labels = data.labels;
        mainChart.data.datasets = data.datasets;
        mainChart.update();
    }
    
    function generateChartData(type) {
        const last30Days = [];
        for (let i = 29; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            last30Days.push(date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }));
        }
        
        switch (type) {
            case 'admins':
                return {
                    labels: last30Days,
                    datasets: [{
                        label: 'Nouvelles inscriptions',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 10) + 1),
                        borderColor: 'var(--primary-color)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true
                    }]
                };
            case 'orders':
                return {
                    labels: last30Days,
                    datasets: [{
                        label: 'Commandes',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 200) + 50),
                        borderColor: 'var(--success-color)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true
                    }]
                };
            case 'revenue':
                return {
                    labels: last30Days,
                    datasets: [{
                        label: 'Revenus (€)',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 5000) + 1000),
                        borderColor: 'var(--warning-color)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true
                    }]
                };
            default:
                return { labels: [], datasets: [] };
        }
    }
    
    function refreshStats() {
        // Simuler le rafraîchissement des statistiques
        console.log('Refreshing stats...');
    }
    
    function loadRecentActivity() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        // Simuler le chargement
        container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i><p class="text-muted">Chargement...</p></div>';
        
        setTimeout(() => {
            const activities = [
                {
                    type: 'success',
                    icon: 'fas fa-user-plus',
                    message: 'Nouvel admin inscrit: John Doe (Restaurant Le Gourmet)',
                    time: '2 minutes'
                },
                {
                    type: 'info',
                    icon: 'fas fa-shopping-cart',
                    message: '127 nouvelles commandes traitées',
                    time: '15 minutes'
                },
                {
                    type: 'warning',
                    icon: 'fas fa-exclamation-triangle',
                    message: 'Admin expire bientôt: Marie Claire (Café Central)',
                    time: '1 heure'
                },
                {
                    type: 'success',
                    icon: 'fas fa-check-circle',
                    message: 'Sauvegarde automatique effectuée',
                    time: '2 heures'
                }
            ];
            
            let html = '';
            activities.forEach(activity => {
                html += `
                    <div class="activity-item ${activity.type}">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="${activity.icon} text-${activity.type}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">${activity.message}</p>
                                <small class="text-muted">il y a ${activity.time}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }, 1000);
    }
});
</script>
@endsection