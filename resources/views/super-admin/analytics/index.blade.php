@extends('layouts.super-admin')

@section('title', 'Analytics Avancées')

@section('css')
<style>
    .analytics-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .analytics-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #4e73df;
    }
    
    .metric-label {
        font-size: 0.875rem;
        color: #858796;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .chart-container {
        position: relative;
        height: 350px;
    }
    
    .tab-content {
        padding: 20px 0;
    }
    
    .nav-pills .nav-link {
        border-radius: 50px;
        padding: 10px 20px;
        margin: 0 5px;
    }
    
    .nav-pills .nav-link.active {
        background: linear-gradient(45deg, #4e73df, #224abe);
    }
    
    .progress-circle {
        width: 100px;
        height: 100px;
    }
    
    .metric-trend {
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .trend-up { color: #1cc88a; }
    .trend-down { color: #e74a3b; }
    .trend-neutral { color: #858796; }
    
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
</style>
@endsection

@section('content')
    <!-- En-tête avec navigation -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Analytics Avancées</h1>
            <p class="text-muted mb-0">Analyse détaillée des performances et de l'utilisation</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary" id="refreshAnalytics">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                <span class="sr-only">Options</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('super-admin.reports.index') }}">
                    <i class="fas fa-file-alt me-2"></i>Générer rapport
                </a></li>
                <li><a class="dropdown-item" href="#" id="exportData">
                    <i class="fas fa-download me-2"></i>Exporter données
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('super-admin.analytics.performance') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Performance détaillée
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Métriques principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card analytics-card h-100">
                <div class="card-body text-center">
                    <div class="metric-value" data-metric="totalAdmins">{{ $overviewStats['total_admins'] }}</div>
                    <div class="metric-label">Total Administrateurs</div>
                    <div class="metric-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +{{ $overviewStats['growth_rate'] }}% ce mois
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card analytics-card h-100">
                <div class="card-body text-center">
                    <div class="metric-value" data-metric="activeAdmins">{{ $overviewStats['active_admins'] }}</div>
                    <div class="metric-label">Administrateurs Actifs</div>
                    <div class="metric-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +{{ round(($overviewStats['active_admins'] / $overviewStats['total_admins']) * 100, 1) }}% du total
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card analytics-card h-100">
                <div class="card-body text-center">
                    <div class="metric-value" data-metric="totalRevenue">{{ number_format($overviewStats['total_revenue']) }}€</div>
                    <div class="metric-label">Revenus Totaux</div>
                    <div class="metric-trend trend-up">
                        <i class="fas fa-arrow-up"></i> Moy: {{ number_format($overviewStats['average_revenue_per_admin']) }}€/admin
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card analytics-card h-100">
                <div class="card-body text-center">
                    <div class="metric-value" data-metric="retentionRate">{{ $overviewStats['retention_rate'] }}%</div>
                    <div class="metric-label">Taux de Rétention</div>
                    <div class="metric-trend trend-{{ $overviewStats['churn_rate'] < 10 ? 'up' : 'down' }}">
                        <i class="fas fa-arrow-{{ $overviewStats['churn_rate'] < 10 ? 'up' : 'down' }}"></i> 
                        Churn: {{ $overviewStats['churn_rate'] }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation par onglets -->
    <div class="card">
        <div class="card-header border-0 bg-white">
            <ul class="nav nav-pills nav-fill" id="analyticsNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                        <i class="fas fa-chart-area me-2"></i>Vue d'ensemble
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="performance-tab" data-bs-toggle="pill" data-bs-target="#performance" type="button" role="tab">
                        <i class="fas fa-tachometer-alt me-2"></i>Performance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="usage-tab" data-bs-toggle="pill" data-bs-target="#usage" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Utilisation
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="trends-tab" data-bs-toggle="pill" data-bs-target="#trends" type="button" role="tab">
                        <i class="fas fa-chart-line me-2"></i>Tendances
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="geographic-tab" data-bs-toggle="pill" data-bs-target="#geographic" type="button" role="tab">
                        <i class="fas fa-globe me-2"></i>Géographique
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="analyticsTabContent">
                
                <!-- Onglet Vue d'ensemble -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Évolution des Inscriptions</h6>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary active" data-period="7">7j</button>
                                        <button class="btn btn-outline-primary" data-period="30">30j</button>
                                        <button class="btn btn-outline-primary" data-period="90">90j</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="registrationChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Répartition par Statut</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Performance -->
                <div class="tab-pane fade" id="performance" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Temps de Réponse Moyen</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="responseTimeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Utilisation des Ressources</h6>
                                </div>
                                <div class="card-body">
                                    <div class="analytics-grid">
                                        <div class="text-center">
                                            <div class="progress-circle mx-auto mb-2" data-percentage="65">
                                                <canvas width="100" height="100"></canvas>
                                            </div>
                                            <h6>CPU</h6>
                                            <span class="text-muted">65%</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="progress-circle mx-auto mb-2" data-percentage="78">
                                                <canvas width="100" height="100"></canvas>
                                            </div>
                                            <h6>Mémoire</h6>
                                            <span class="text-muted">78%</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="progress-circle mx-auto mb-2" data-percentage="45">
                                                <canvas width="100" height="100"></canvas>
                                            </div>
                                            <h6>Disque</h6>
                                            <span class="text-muted">45%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Utilisation -->
                <div class="tab-pane fade" id="usage" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Utilisateurs Actifs</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="activeUsersChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Fonctionnalités Populaires</h6>
                                </div>
                                <div class="card-body">
                                    <div id="featureUsage">
                                        <!-- Contenu chargé dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Tendances -->
                <div class="tab-pane fade" id="trends" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Tendances de Croissance</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="growthTrendsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Géographique -->
                <div class="tab-pane fade" id="geographic" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Répartition Géographique</h6>
                                </div>
                                <div class="card-body">
                                    <div id="worldMap" style="height: 400px;">
                                        <!-- Carte mondiale (utiliser une bibliothèque comme Leaflet ou Google Maps) -->
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <div class="text-center">
                                                <i class="fas fa-globe fa-3x mb-3"></i>
                                                <p>Carte géographique</p>
                                                <small>Intégration prévue avec Leaflet/Google Maps</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Top Pays</h6>
                                </div>
                                <div class="card-body">
                                    <div id="countryStats">
                                        <!-- Contenu chargé dynamiquement -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes et insights -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Insights et Recommandations</h6>
                </div>
                <div class="card-body">
                    <div id="insights" class="row">
                        <!-- Insights générés automatiquement -->
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
    // Variables globales pour les graphiques
    let charts = {};
    
    // Initialiser tous les graphiques
    initializeCharts();
    
    // Gestionnaires d'événements
    setupEventHandlers();
    
    // Charger les données initiales
    loadAnalyticsData();
    
    function initializeCharts() {
        // Graphique des inscriptions
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        charts.registration = new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Nouvelles inscriptions',
                    data: [],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
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
                }
            }
        });

        // Graphique de statut (donut)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        charts.status = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Actifs', 'Inactifs', 'Expirés'],
                datasets: [{
                    data: [{{ $overviewStats['active_admins'] }}, 
                           {{ $overviewStats['total_admins'] - $overviewStats['active_admins'] }}, 
                           0],
                    backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e']
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

        // Graphique temps de réponse
        const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
        charts.responseTime = new Chart(responseTimeCtx, {
            type: 'bar',
            data: {
                labels: ['00h', '04h', '08h', '12h', '16h', '20h'],
                datasets: [{
                    label: 'Temps de réponse (ms)',
                    data: [120, 105, 180, 165, 140, 125],
                    backgroundColor: 'rgba(54, 185, 204, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Milliseconds'
                        }
                    }
                }
            }
        });

        // Graphique utilisateurs actifs
        const activeUsersCtx = document.getElementById('activeUsersChart').getContext('2d');
        charts.activeUsers = new Chart(activeUsersCtx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                datasets: [
                    {
                        label: 'Utilisateurs actifs',
                        data: [45, 52, 48, 61, 58, 42, 38],
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Nouvelles sessions',
                        data: [25, 30, 28, 35, 32, 22, 20],
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique tendances de croissance
        const growthTrendsCtx = document.getElementById('growthTrendsChart').getContext('2d');
        charts.growthTrends = new Chart(growthTrendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                datasets: [
                    {
                        label: 'Nouveaux admins',
                        data: [12, 19, 15, 25, 22, 30],
                        borderColor: '#4e73df',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Taux de croissance (%)',
                        data: [5.2, 15.8, -21.1, 66.7, -12.0, 36.4],
                        borderColor: '#1cc88a',
                        type: 'bar',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    function setupEventHandlers() {
        // Actualiser les données
        document.getElementById('refreshAnalytics').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
            loadAnalyticsData();
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Actualiser';
            }, 2000);
        });

        // Changement de période
        document.querySelectorAll('[data-period]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const period = this.dataset.period;
                updateChartData(period);
            });
        });

        // Export des données
        document.getElementById('exportData').addEventListener('click', function(e) {
            e.preventDefault();
            exportAnalyticsData();
        });
    }

    function loadAnalyticsData() {
        // Charger l'utilisation des fonctionnalités
        loadFeatureUsage();
        
        // Charger les statistiques géographiques
        loadGeographicData();
        
        // Charger les insights
        loadInsights();
        
        // Initialiser les cercles de progression
        initProgressCircles();
    }

    function loadFeatureUsage() {
        const features = [
            { name: 'Gestion commandes', usage: 95, color: '#4e73df' },
            { name: 'Gestion employés', usage: 78, color: '#1cc88a' },
            { name: 'Rapports', usage: 65, color: '#f6c23e' },
            { name: 'Analytics', usage: 52, color: '#e74a3b' },
            { name: 'Paramètres', usage: 34, color: '#858796' }
        ];

        const container = document.getElementById('featureUsage');
        container.innerHTML = '';

        features.forEach(feature => {
            const html = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small font-weight-bold">${feature.name}</span>
                        <span class="small">${feature.usage}%</span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar" style="width: ${feature.usage}%; background-color: ${feature.color}"></div>
                    </div>
                </div>
            `;
            container.innerHTML += html;
        });
    }

    function loadGeographicData() {
        const countries = [
            { name: 'France', count: 45, flag: '🇫🇷' },
            { name: 'Belgique', count: 25, flag: '🇧🇪' },
            { name: 'Suisse', count: 20, flag: '🇨🇭' },
            { name: 'Canada', count: 15, flag: '🇨🇦' },
            { name: 'Autres', count: 23, flag: '🌍' }
        ];

        const container = document.getElementById('countryStats');
        container.innerHTML = '';

        countries.forEach(country => {
            const percentage = Math.round((country.count / 128) * 100);
            const html = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="me-2">${country.flag}</span>
                        <span class="small">${country.name}</span>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">${country.count}</div>
                        <small class="text-muted">${percentage}%</small>
                    </div>
                </div>
            `;
            container.innerHTML += html;
        });
    }

    function loadInsights() {
        const insights = [
            {
                type: 'success',
                icon: 'fas fa-arrow-up',
                title: 'Croissance positive',
                message: 'Les inscriptions ont augmenté de 15% ce mois-ci'
            },
            {
                type: 'warning',
                icon: 'fas fa-exclamation-triangle',
                title: 'Attention',
                message: '12% des admins n\'ont pas utilisé la plateforme depuis 30 jours'
            },
            {
                type: 'info',
                icon: 'fas fa-lightbulb',
                title: 'Recommandation',
                message: 'Considérer l\'ajout de nouvelles fonctionnalités analytics'
            }
        ];

        const container = document.getElementById('insights');
        container.innerHTML = '';

        insights.forEach(insight => {
            const html = `
                <div class="col-md-4 mb-3">
                    <div class="alert alert-${insight.type} border-left-${insight.type}">
                        <div class="d-flex align-items-center">
                            <i class="${insight.icon} me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">${insight.title}</h6>
                                <p class="mb-0 small">${insight.message}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += html;
        });
    }

    function initProgressCircles() {
        document.querySelectorAll('.progress-circle').forEach(circle => {
            const canvas = circle.querySelector('canvas');
            const ctx = canvas.getContext('2d');
            const percentage = parseInt(circle.dataset.percentage);
            
            drawProgressCircle(ctx, percentage);
        });
    }

    function drawProgressCircle(ctx, percentage) {
        const centerX = 50;
        const centerY = 50;
        const radius = 35;
        
        // Effacer le canvas
        ctx.clearRect(0, 0, 100, 100);
        
        // Cercle de fond
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.lineWidth = 8;
        ctx.strokeStyle = '#e3e6f0';
        ctx.stroke();
        
        // Cercle de progression
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, -Math.PI/2, (-Math.PI/2) + (2 * Math.PI * percentage/100));
        ctx.lineWidth = 8;
        ctx.strokeStyle = '#4e73df';
        ctx.lineCap = 'round';
        ctx.stroke();
        
        // Texte du pourcentage
        ctx.font = '14px Arial';
        ctx.fillStyle = '#5a5c69';
        ctx.textAlign = 'center';
        ctx.fillText(percentage + '%', centerX, centerY + 5);
    }

    function updateChartData(period) {
        // Simuler la mise à jour des données selon la période
        console.log('Mise à jour des données pour la période:', period);
    }

    function exportAnalyticsData() {
        // Simuler l'export des données
        const blob = new Blob(['Analytics data export'], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'analytics_export_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
});
</script>
@endsection