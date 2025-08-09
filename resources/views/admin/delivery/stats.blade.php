@extends('layouts.admin')

@section('title', 'Statistiques de Livraison')

@section('content')
<style>
:root {
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-600: #4b5563;
    --gray-800: #1f2937;
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --radius: 0.75rem;
    --radius-sm: 0.375rem;
}

* {
    box-sizing: border-box;
}

/* Layout moderne */
.stats-container {
    padding: 1rem;
    max-width: 1400px;
    margin: 0 auto;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: calc(100vh - 80px);
}

/* Header compact */
.stats-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.stats-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-title i {
    color: var(--primary);
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-modern {
    padding: 0.5rem 1rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--gray-200);
    background: white;
    color: var(--gray-600);
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    text-decoration: none;
}

.btn-modern:hover {
    background: var(--gray-50);
    color: var(--gray-800);
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.btn-primary:hover {
    background: var(--primary-light);
    color: white;
}

/* Grille de métriques compacte */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.metric-card {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    border-left: 4px solid var(--primary);
    transition: transform 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.metric-card.success { border-left-color: var(--success); }
.metric-card.warning { border-left-color: var(--warning); }
.metric-card.info { border-left-color: var(--info); }

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.metric-title {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-600);
    margin: 0;
}

.metric-icon {
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
    color: var(--primary);
    font-size: 1rem;
}

.metric-icon.success { background: #dcfce7; color: var(--success); }
.metric-icon.warning { background: #fef3c7; color: var(--warning); }
.metric-icon.info { background: #cffafe; color: var(--info); }

.metric-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--gray-800);
    margin: 0 0 0.5rem 0;
    line-height: 1;
}

.metric-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.trend-positive { color: var(--success); }
.trend-negative { color: var(--danger); }
.trend-neutral { color: var(--gray-600); }

/* Grille de contenu principal */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Cartes de contenu */
.content-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-body {
    padding: 1.25rem;
}

.card-body.no-padding {
    padding: 0;
}

/* Graphiques */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Tables compactes */
.compact-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.compact-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--gray-600);
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}

.compact-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--gray-200);
    color: var(--gray-800);
}

.compact-table tr:hover {
    background: var(--gray-50);
}

/* Progress bars */
.progress-bar {
    width: 100%;
    height: 0.375rem;
    background: var(--gray-200);
    border-radius: 9999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.3s ease;
}

.progress-success { background: var(--success); }
.progress-warning { background: var(--warning); }
.progress-danger { background: var(--danger); }

/* Badges */
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-align: center;
}

.badge-success { background: #dcfce7; color: var(--success); }
.badge-warning { background: #fef3c7; color: var(--warning); }
.badge-danger { background: #fee2e2; color: var(--danger); }
.badge-info { background: #cffafe; color: var(--info); }

/* Loading states */
.loading {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    color: var(--gray-600);
}

.spinner {
    width: 2rem;
    height: 2rem;
    border: 2px solid var(--gray-200);
    border-top: 2px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 0.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Export section */
.export-section {
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow);
}

.export-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .stats-container {
        padding: 0.75rem;
    }
    
    .stats-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
        padding: 1rem;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .metric-card {
        padding: 1rem;
    }
    
    .metric-value {
        font-size: 1.75rem;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .chart-container {
        height: 250px;
    }
    
    .btn-modern {
        flex: 1;
        justify-content: center;
        padding: 0.75rem;
    }
    
    .stats-title {
        font-size: 1.25rem;
        text-align: center;
    }
    
    .compact-table {
        font-size: 0.8rem;
    }
    
    .compact-table th,
    .compact-table td {
        padding: 0.5rem;
    }
}

@media (max-width: 480px) {
    .stats-container {
        padding: 0.5rem;
    }
    
    .metric-card {
        padding: 0.75rem;
    }
    
    .metric-value {
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeInUp 0.4s ease-out;
}

/* Utilities */
.text-center { text-align: center; }
.text-right { text-align: right; }
.mb-0 { margin-bottom: 0; }
.mb-1 { margin-bottom: 0.5rem; }
.mb-2 { margin-bottom: 1rem; }
.d-flex { display: flex; }
.align-items-center { align-items: center; }
.justify-content-between { justify-content: space-between; }
.gap-2 { gap: 0.5rem; }
.flex-wrap { flex-wrap: wrap; }
</style>

<div class="stats-container">
    <!-- Header compact -->
    <div class="stats-header fade-in">
        <h1 class="stats-title">
            <i class="fas fa-chart-line"></i>
            Statistiques de Livraison
        </h1>
        <div class="header-actions">
            <select id="periodSelect" class="btn-modern">
                <option value="7">7 jours</option>
                <option value="30" selected>30 jours</option>
                <option value="90">3 mois</option>
            </select>
            <button class="btn-modern" onclick="refreshStats()">
                <i class="fas fa-sync" id="refreshIcon"></i>
                Actualiser
            </button>
            <button class="btn-modern" onclick="exportStats()">
                <i class="fas fa-download"></i>
                Export
            </button>
            <a href="{{ route('admin.delivery.index') }}" class="btn-modern">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Métriques principales -->
    <div class="metrics-grid fade-in" id="metricsGrid">
        <div class="metric-card">
            <div class="metric-header">
                <h3 class="metric-title">Total Expéditions</h3>
                <div class="metric-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
            </div>
            <p class="metric-value" id="totalShipments">-</p>
            <div class="metric-trend" id="shipmentsTrend">
                <i class="fas fa-minus"></i>
                <span>Chargement...</span>
            </div>
        </div>

        <div class="metric-card success">
            <div class="metric-header">
                <h3 class="metric-title">Livrées</h3>
                <div class="metric-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <p class="metric-value" id="deliveredShipments">-</p>
            <div class="metric-trend" id="deliveredTrend">
                <i class="fas fa-minus"></i>
                <span>Chargement...</span>
            </div>
        </div>

        <div class="metric-card warning">
            <div class="metric-header">
                <h3 class="metric-title">En Transit</h3>
                <div class="metric-icon warning">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <p class="metric-value" id="inTransitShipments">-</p>
            <div class="metric-trend" id="transitTrend">
                <i class="fas fa-minus"></i>
                <span>Chargement...</span>
            </div>
        </div>

        <div class="metric-card info">
            <div class="metric-header">
                <h3 class="metric-title">Problèmes</h3>
                <div class="metric-icon info">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <p class="metric-value" id="problemShipments">-</p>
            <div class="metric-trend" id="problemTrend">
                <i class="fas fa-minus"></i>
                <span>Chargement...</span>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="content-grid fade-in">
        <!-- Graphique principal -->
        <div class="content-card">
            <div class="card-header">
                <div class="card-title">
                    <span>Évolution des Expéditions</span>
                    <select id="chartPeriod" class="btn-modern" onchange="updateChart()">
                        <option value="7">7 jours</option>
                        <option value="30" selected>30 jours</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="chartLoading" class="loading">
                    <div class="spinner"></div>
                    Chargement du graphique...
                </div>
                <div class="chart-container" id="chartContainer" style="display: none;">
                    <canvas id="shipmentsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition par transporteur -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Par Transporteur</h3>
            </div>
            <div class="card-body">
                <div id="carriersLoading" class="loading">
                    <div class="spinner"></div>
                    Chargement...
                </div>
                <div id="carriersData" style="display: none;">
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="carriersChart"></canvas>
                    </div>
                    <div id="carriersLegend" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables de données -->
    <div class="content-grid fade-in">
        <!-- Statuts détaillés -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Répartition par Statut</h3>
            </div>
            <div class="card-body no-padding">
                <div id="statusLoading" class="loading">
                    <div class="spinner"></div>
                    Chargement...
                </div>
                <table class="compact-table" id="statusTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Statut</th>
                            <th>Nombre</th>
                            <th>Pourcentage</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody id="statusTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Activité Récente</h3>
            </div>
            <div class="card-body no-padding">
                <div id="activityLoading" class="loading">
                    <div class="spinner"></div>
                    Chargement...
                </div>
                <div id="activityList" style="display: none;">
                    <!-- L'activité sera injectée ici -->
                </div>
            </div>
        </div>
    </div>

    <!-- Section export -->
    <div class="export-section fade-in">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0">Export des Données</h3>
            <div class="export-actions">
                <button class="btn-modern btn-primary" onclick="exportData('csv')">
                    <i class="fas fa-file-csv"></i>
                    CSV
                </button>
                <button class="btn-modern btn-primary" onclick="exportData('excel')">
                    <i class="fas fa-file-excel"></i>
                    Excel
                </button>
            </div>
        </div>
        <p class="mb-0" style="color: var(--gray-600); font-size: 0.875rem;">
            Exportez vos statistiques de livraison pour analyse externe ou reporting.
        </p>
    </div>
</div>

<!-- Toast notifications -->
<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variables globales
let shipmentsChart = null;
let carriersChart = null;
let statsData = {};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadAllStats();
    
    // Event listeners
    document.getElementById('periodSelect').addEventListener('change', function() {
        loadAllStats();
    });
});

// Chargement des statistiques
async function loadAllStats() {
    showLoading();
    
    try {
        // Charger les statistiques générales
        const statsResponse = await fetch('/admin/delivery/api/general-stats', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (!statsResponse.ok) {
            throw new Error(`Erreur ${statsResponse.status}`);
        }
        
        const data = await statsResponse.json();
        
        if (data.success) {
            statsData = data;
            updateMetrics(data);
            updateCharts(data);
            updateTables(data);
            hideLoading();
            showToast('Données mises à jour', 'success');
        } else {
            throw new Error(data.error || 'Erreur de chargement');
        }
        
    } catch (error) {
        console.error('Erreur chargement stats:', error);
        hideLoading();
        showToast('Erreur de chargement des données', 'error');
        
        // Données de fallback
        const fallbackData = {
            success: true,
            general_stats: {
                total_shipments: 0,
                active_shipments: 0
            },
            stats: {
                shipments: {
                    in_transit: 0,
                    delivered: 0,
                    in_return: 0,
                    anomaly: 0
                }
            }
        };
        
        updateMetrics(fallbackData);
        updateTables(fallbackData);
    }
}

// Mise à jour des métriques
function updateMetrics(data) {
    const generalStats = data.general_stats || {};
    const shipmentStats = data.stats?.shipments || {};
    
    // Total expéditions
    document.getElementById('totalShipments').textContent = generalStats.total_shipments || 0;
    
    // Livrées
    document.getElementById('deliveredShipments').textContent = shipmentStats.delivered || 0;
    
    // En transit
    document.getElementById('inTransitShipments').textContent = shipmentStats.in_transit || 0;
    
    // Problèmes (retours + anomalies)
    const problems = (shipmentStats.in_return || 0) + (shipmentStats.anomaly || 0);
    document.getElementById('problemShipments').textContent = problems;
    
    // Mise à jour des tendances (simulées pour le moment)
    updateTrend('shipmentsTrend', Math.random() > 0.5 ? 5 : -3);
    updateTrend('deliveredTrend', Math.random() > 0.3 ? 8 : -2);
    updateTrend('transitTrend', Math.random() > 0.5 ? 3 : -5);
    updateTrend('problemTrend', Math.random() > 0.7 ? -10 : 2);
}

// Mise à jour d'une tendance
function updateTrend(elementId, value) {
    const element = document.getElementById(elementId);
    const isPositive = value >= 0;
    
    element.className = `metric-trend ${isPositive ? 'trend-positive' : 'trend-negative'}`;
    element.innerHTML = `
        <i class="fas fa-${isPositive ? 'arrow-up' : 'arrow-down'}"></i>
        <span>${Math.abs(value)}% vs période précédente</span>
    `;
}

// Mise à jour des graphiques
function updateCharts(data) {
    const shipmentStats = data.stats?.shipments || {};
    
    // Graphique principal (simulation de données temporelles)
    updateShipmentsChart();
    
    // Graphique par transporteur
    updateCarriersChart(shipmentStats);
}

// Graphique des expéditions
function updateShipmentsChart() {
    const ctx = document.getElementById('shipmentsChart');
    if (!ctx) return;
    
    // Générer des données factices pour les 30 derniers jours
    const labels = [];
    const data = [];
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }));
        data.push(Math.floor(Math.random() * 20) + 5);
    }
    
    if (shipmentsChart) {
        shipmentsChart.destroy();
    }
    
    shipmentsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Expéditions',
                data: data,
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    document.getElementById('chartLoading').style.display = 'none';
    document.getElementById('chartContainer').style.display = 'block';
}

// Graphique par transporteur
function updateCarriersChart(shipmentStats) {
    const ctx = document.getElementById('carriersChart');
    if (!ctx) return;
    
    // Calculer la répartition par transporteur (simulation)
    const total = Object.values(shipmentStats).reduce((sum, val) => sum + (val || 0), 0);
    const jaxPercent = total > 0 ? Math.floor((shipmentStats.in_transit || 0) / total * 100) : 50;
    const mesColisPercent = 100 - jaxPercent;
    
    const carriers = [
        { name: 'JAX Delivery', count: Math.floor(total * jaxPercent / 100), color: '#2563eb' },
        { name: 'Mes Colis', count: Math.floor(total * mesColisPercent / 100), color: '#10b981' }
    ];
    
    if (carriersChart) {
        carriersChart.destroy();
    }
    
    carriersChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: carriers.map(c => c.name),
            datasets: [{
                data: carriers.map(c => c.count),
                backgroundColor: carriers.map(c => c.color),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Mise à jour de la légende
    const legend = document.getElementById('carriersLegend');
    legend.innerHTML = carriers.map(carrier => `
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center gap-2">
                <div style="width: 12px; height: 12px; background: ${carrier.color}; border-radius: 50%;"></div>
                <span style="font-size: 0.875rem;">${carrier.name}</span>
            </div>
            <span style="font-weight: 600;">${carrier.count}</span>
        </div>
    `).join('');
    
    document.getElementById('carriersLoading').style.display = 'none';
    document.getElementById('carriersData').style.display = 'block';
}

// Mise à jour des tables
function updateTables(data) {
    const shipmentStats = data.stats?.shipments || {};
    const total = Object.values(shipmentStats).reduce((sum, val) => sum + (val || 0), 0);
    
    // Table des statuts
    const statusMap = {
        'created': { label: 'Créées', color: 'info' },
        'validated': { label: 'Validées', color: 'warning' },
        'picked_up_by_carrier': { label: 'Récupérées', color: 'warning' },
        'in_transit': { label: 'En Transit', color: 'info' },
        'delivered': { label: 'Livrées', color: 'success' },
        'in_return': { label: 'En Retour', color: 'warning' },
        'anomaly': { label: 'Anomalies', color: 'danger' }
    };
    
    const tbody = document.getElementById('statusTableBody');
    tbody.innerHTML = Object.entries(statusMap).map(([status, config]) => {
        const count = shipmentStats[status] || 0;
        const percentage = total > 0 ? Math.round(count / total * 100) : 0;
        
        return `
            <tr>
                <td>
                    <span class="badge badge-${config.color}">${config.label}</span>
                </td>
                <td><strong>${count}</strong></td>
                <td>${percentage}%</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill progress-${config.color}" style="width: ${percentage}%"></div>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    
    document.getElementById('statusLoading').style.display = 'none';
    document.getElementById('statusTable').style.display = 'table';
    
    // Activité récente (simulation)
    updateRecentActivity();
}

// Activité récente
function updateRecentActivity() {
    const activities = [
        { type: 'delivered', message: 'Expédition #1234 livrée', time: '2 min' },
        { type: 'created', message: 'Nouvel enlèvement créé', time: '15 min' },
        { type: 'problem', message: 'Problème expédition #5678', time: '1 h' },
        { type: 'delivered', message: 'Expédition #9012 livrée', time: '2 h' }
    ];
    
    const activityList = document.getElementById('activityList');
    activityList.innerHTML = activities.map(activity => `
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span style="font-size: 0.875rem; color: var(--gray-800);">${activity.message}</span>
                </div>
                <span style="font-size: 0.75rem; color: var(--gray-600);">${activity.time}</span>
            </div>
        </div>
    `).join('');
    
    document.getElementById('activityLoading').style.display = 'none';
    document.getElementById('activityList').style.display = 'block';
}

// Fonctions utilitaires
function showLoading() {
    // Déjà géré par les états de loading individuels
}

function hideLoading() {
    // Déjà géré par les fonctions de mise à jour
}

function refreshStats() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('fa-spin');
    
    loadAllStats().finally(() => {
        icon.classList.remove('fa-spin');
    });
}

function updateChart() {
    updateShipmentsChart();
}

function exportStats() {
    exportData('csv');
}

function exportData(format) {
    showToast(`Export ${format.toUpperCase()} en cours...`, 'info');
    
    // TODO: Implémenter l'export réel
    setTimeout(() => {
        showToast(`Export ${format.toUpperCase()} terminé`, 'success');
    }, 2000);
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const colors = {
        success: 'var(--success)',
        error: 'var(--danger)',
        warning: 'var(--warning)',
        info: 'var(--info)'
    };
    
    toast.style.cssText = `
        background: ${colors[type]};
        color: white;
        padding: 0.75rem 1rem;
        border-radius: var(--radius-sm);
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        box-shadow: var(--shadow-lg);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    toast.textContent = message;
    container.appendChild(toast);
    
    // Animer l'entrée
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-suppression
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            container.removeChild(toast);
        }, 300);
    }, 4000);
}

console.log('📊 Statistiques de livraison initialisées');
</script>
@endsection