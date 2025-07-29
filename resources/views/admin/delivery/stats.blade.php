@extends('layouts.admin')

@section('title', 'Statistiques de Livraison')

@section('content')
<div class="container-fluid" x-data="deliveryStats">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-primary me-2"></i>
                Statistiques de Livraison
            </h1>
            <p class="text-muted mb-0">Analyse détaillée de vos performances de livraison</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar me-1"></i>
                    <span x-text="periodLabels[selectedPeriod]"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click="changePeriod('7days')">7 derniers jours</a></li>
                    <li><a class="dropdown-item" href="#" @click="changePeriod('30days')">30 derniers jours</a></li>
                    <li><a class="dropdown-item" href="#" @click="changePeriod('3months')">3 derniers mois</a></li>
                    <li><a class="dropdown-item" href="#" @click="changePeriod('year')">Cette année</a></li>
                </ul>
            </div>
            <button class="btn btn-outline-secondary" @click="exportStats()">
                <i class="fas fa-download me-1"></i>
                Exporter
            </button>
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Métriques principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Expéditions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.total_shipments"></div>
                            <div class="small">
                                <span :class="stats.shipments_trend >= 0 ? 'text-success' : 'text-danger'">
                                    <i :class="stats.shipments_trend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    <span x-text="Math.abs(stats.shipments_trend)"></span>%
                                </span>
                                <span class="text-muted">vs période précédente</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Taux de Livraison
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="`${stats.delivery_rate}%`"></div>
                            <div class="small">
                                <span class="text-muted">
                                    <span x-text="stats.delivered_shipments"></span> sur <span x-text="stats.total_shipments"></span> livrées
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Délai Moyen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="`${stats.avg_delivery_time} jours`"></div>
                            <div class="small">
                                <span :class="stats.delivery_time_trend <= 0 ? 'text-success' : 'text-danger'">
                                    <i :class="stats.delivery_time_trend <= 0 ? 'fas fa-arrow-down' : 'fas fa-arrow-up'"></i>
                                    <span x-text="Math.abs(stats.delivery_time_trend)"></span>%
                                </span>
                                <span class="text-muted">vs période précédente</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Volume COD
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="`${stats.total_cod_amount} TND`"></div>
                            <div class="small">
                                <span :class="stats.cod_trend >= 0 ? 'text-success' : 'text-danger'">
                                    <i :class="stats.cod_trend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    <span x-text="Math.abs(stats.cod_trend)"></span>%
                                </span>
                                <span class="text-muted">vs période précédente</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <!-- Évolution des expéditions -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution des Expéditions</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <div class="dropdown-header">Options:</div>
                            <a class="dropdown-item" href="#" @click="changeChartType('line')">Graphique linéaire</a>
                            <a class="dropdown-item" href="#" @click="changeChartType('bar')">Graphique en barres</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div x-show="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <canvas id="shipmentsChart" x-show="!loading" width="100" height="40"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition par transporteur -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition par Transporteur</h6>
                </div>
                <div class="card-body">
                    <div x-show="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <canvas id="carriersChart" x-show="!loading" width="100" height="100"></canvas>
                    
                    <!-- Légende -->
                    <div class="mt-3" x-show="!loading">
                        <template x-for="carrier in carrierStats" :key="carrier.slug">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="carrier-color-indicator me-2" :style="`background-color: ${carrier.color}`"></div>
                                    <span x-text="carrier.name"></span>
                                </div>
                                <div class="text-end">
                                    <strong x-text="carrier.count"></strong>
                                    <br>
                                    <small class="text-muted" x-text="`${carrier.percentage}%`"></small>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux de détails -->
    <div class="row">
        <!-- Performance par région -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance par Région</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Gouvernorat</th>
                                    <th>Expéditions</th>
                                    <th>Taux de Livraison</th>
                                    <th>Délai Moyen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="region in regionStats.slice(0, 10)" :key="region.id">
                                    <tr>
                                        <td x-text="region.name"></td>
                                        <td>
                                            <strong x-text="region.shipments"></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 4px;">
                                                    <div class="progress-bar" 
                                                         :style="`width: ${region.delivery_rate}%; background-color: ${region.delivery_rate >= 80 ? '#28a745' : region.delivery_rate >= 60 ? '#ffc107' : '#dc3545'}`">
                                                    </div>
                                                </div>
                                                <span class="small" x-text="`${region.delivery_rate}%`"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span x-text="`${region.avg_delivery_time} j`"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analyse des problèmes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Analyse des Problèmes</h6>
                </div>
                <div class="card-body">
                    <!-- Types de problèmes -->
                    <div class="mb-4">
                        <h6 class="text-muted">Types de Problèmes</h6>
                        <template x-for="problem in problemStats" :key="problem.type">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span x-text="problem.label"></span>
                                    <br>
                                    <small class="text-muted" x-text="problem.description"></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger" x-text="problem.count"></span>
                                    <br>
                                    <small class="text-muted" x-text="`${problem.percentage}%`"></small>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Actions recommandées -->
                    <div>
                        <h6 class="text-primary">Actions Recommandées</h6>
                        <div class="list-group list-group-flush">
                            <template x-for="recommendation in recommendations" :key="recommendation.id">
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i :class="recommendation.icon" class="me-2 mt-1"></i>
                                        <div>
                                            <strong x-text="recommendation.title"></strong>
                                            <br>
                                            <small class="text-muted" x-text="recommendation.description"></small>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export et actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Export et Rapports</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="text-muted mb-3">
                                Exportez vos données de livraison pour analyse approfondie ou reporting externe.
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-outline-success" @click="exportData('excel')">
                                    <i class="fas fa-file-excel me-1"></i>
                                    Excel
                                </button>
                                <button class="btn btn-outline-danger" @click="exportData('pdf')">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    PDF
                                </button>
                                <button class="btn btn-outline-primary" @click="exportData('csv')">
                                    <i class="fas fa-file-csv me-1"></i>
                                    CSV
                                </button>
                                <button class="btn btn-outline-info" @click="scheduleReport()">
                                    <i class="fas fa-calendar me-1"></i>
                                    Programmer un Rapport
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" @click="refreshAllData()">
                                    <i class="fas fa-sync me-1"></i>
                                    Actualiser les Données
                                </button>
                                <button class="btn btn-outline-secondary" @click="configureKPIs()">
                                    <i class="fas fa-cogs me-1"></i>
                                    Configurer KPIs
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryStats', () => ({
        loading: true,
        selectedPeriod: '30days',
        chartType: 'line',
        periodLabels: {
            '7days': '7 derniers jours',
            '30days': '30 derniers jours',
            '3months': '3 derniers mois',
            'year': 'Cette année'
        },
        stats: {
            total_shipments: 0,
            delivered_shipments: 0,
            delivery_rate: 0,
            avg_delivery_time: 0,
            total_cod_amount: 0,
            shipments_trend: 0,
            delivery_time_trend: 0,
            cod_trend: 0
        },
        carrierStats: [],
        regionStats: [],
        problemStats: [],
        recommendations: [],
        shipmentsChart: null,
        carriersChart: null,

        init() {
            this.loadAllData();
        },

        async loadAllData() {
            this.loading = true;
            
            try {
                await Promise.all([
                    this.loadStats(),
                    this.loadCarrierStats(),
                    this.loadRegionStats(),
                    this.loadProblemStats(),
                    this.loadRecommendations()
                ]);
                
                // Initialiser les graphiques après le chargement des données
                this.$nextTick(() => {
                    this.initCharts();
                });
            } catch (error) {
                console.error('Erreur chargement données:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les statistiques',
                });
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            // TODO: Remplacer par un vrai appel API
            // const response = await axios.get('/admin/delivery/api/stats', { params: { period: this.selectedPeriod } });
            
            // Simulation de données
            this.stats = {
                total_shipments: Math.floor(Math.random() * 1000) + 500,
                delivered_shipments: Math.floor(Math.random() * 800) + 400,
                delivery_rate: (Math.random() * 20 + 75).toFixed(1),
                avg_delivery_time: (Math.random() * 2 + 2).toFixed(1),
                total_cod_amount: (Math.random() * 50000 + 10000).toFixed(3),
                shipments_trend: (Math.random() * 30 - 15).toFixed(1),
                delivery_time_trend: (Math.random() * 20 - 10).toFixed(1),
                cod_trend: (Math.random() * 40 - 20).toFixed(1)
            };
        },

        async loadCarrierStats() {
            this.carrierStats = [
                {
                    slug: 'jax_delivery',
                    name: 'JAX Delivery',
                    count: Math.floor(Math.random() * 400) + 200,
                    percentage: (Math.random() * 30 + 40).toFixed(1),
                    color: '#007bff'
                },
                {
                    slug: 'mes_colis',
                    name: 'Mes Colis Express',
                    count: Math.floor(Math.random() * 300) + 150,
                    percentage: (Math.random() * 30 + 25).toFixed(1),
                    color: '#28a745'
                }
            ];
        },

        async loadRegionStats() {
            const regions = ['Tunis', 'Ariana', 'Ben Arous', 'Sousse', 'Sfax', 'Nabeul', 'Monastir', 'Bizerte'];
            this.regionStats = regions.map((name, index) => ({
                id: index + 1,
                name,
                shipments: Math.floor(Math.random() * 100) + 20,
                delivery_rate: (Math.random() * 30 + 60).toFixed(0),
                avg_delivery_time: (Math.random() * 3 + 2).toFixed(1)
            }));
        },

        async loadProblemStats() {
            this.problemStats = [
                {
                    type: 'address_not_found',
                    label: 'Adresse introuvable',
                    description: 'Adresses incorrectes ou incomplètes',
                    count: Math.floor(Math.random() * 20) + 5,
                    percentage: (Math.random() * 10 + 5).toFixed(1)
                },
                {
                    type: 'customer_absent',
                    label: 'Client absent',
                    description: 'Client non disponible lors de la livraison',
                    count: Math.floor(Math.random() * 15) + 3,
                    percentage: (Math.random() * 8 + 3).toFixed(1)
                },
                {
                    type: 'damaged_package',
                    label: 'Colis endommagé',
                    description: 'Dommages pendant le transport',
                    count: Math.floor(Math.random() * 8) + 1,
                    percentage: (Math.random() * 5 + 1).toFixed(1)
                }
            ];
        },

        async loadRecommendations() {
            this.recommendations = [
                {
                    id: 1,
                    icon: 'fas fa-map-marker-alt text-warning',
                    title: 'Améliorer la qualité des adresses',
                    description: 'Ajouter une validation des adresses lors de la saisie des commandes'
                },
                {
                    id: 2,
                    icon: 'fas fa-phone text-info',
                    title: 'Confirmer les livraisons par SMS',
                    description: 'Envoyer un SMS de confirmation avant la livraison'
                },
                {
                    id: 3,
                    icon: 'fas fa-chart-line text-success',
                    title: 'Optimiser les créneaux de livraison',
                    description: 'Analyser les heures de livraison les plus efficaces'
                }
            ];
        },

        changePeriod(period) {
            this.selectedPeriod = period;
            this.loadAllData();
        },

        changeChartType(type) {
            this.chartType = type;
            this.updateShipmentsChart();
        },

        initCharts() {
            this.initShipmentsChart();
            this.initCarriersChart();
        },

        initShipmentsChart() {
            const ctx = document.getElementById('shipmentsChart');
            if (!ctx) return;

            // Générer des données factices pour la période
            const labels = [];
            const data = [];
            const days = this.selectedPeriod === '7days' ? 7 : this.selectedPeriod === '30days' ? 30 : 90;
            
            for (let i = days - 1; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }));
                data.push(Math.floor(Math.random() * 50) + 10);
            }

            this.shipmentsChart = new Chart(ctx, {
                type: this.chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Expéditions',
                        data: data,
                        backgroundColor: this.chartType === 'line' ? 'rgba(54, 162, 235, 0.1)' : 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: this.chartType === 'line'
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
                            display: false
                        }
                    }
                }
            });
        },

        initCarriersChart() {
            const ctx = document.getElementById('carriersChart');
            if (!ctx) return;

            this.carriersChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.carrierStats.map(c => c.name),
                    datasets: [{
                        data: this.carrierStats.map(c => c.count),
                        backgroundColor: this.carrierStats.map(c => c.color),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        updateShipmentsChart() {
            if (this.shipmentsChart) {
                this.shipmentsChart.destroy();
                this.initShipmentsChart();
            }
        },

        async refreshAllData() {
            await this.loadAllData();
            
            Swal.fire({
                icon: 'success',
                title: 'Données actualisées !',
                text: 'Les statistiques ont été mises à jour',
                showConfirmButton: false,
                timer: 2000
            });
        },

        async exportData(format) {
            Swal.fire({
                icon: 'info',
                title: 'Export en cours...',
                text: `Génération du fichier ${format.toUpperCase()}`,
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });

            // TODO: Implémenter l'export réel
            await new Promise(resolve => setTimeout(resolve, 2000));

            Swal.fire({
                icon: 'success',
                title: 'Export terminé !',
                text: `Fichier ${format.toUpperCase()} téléchargé`,
                showConfirmButton: false,
                timer: 2000
            });
        },

        async scheduleReport() {
            const { value: formValues } = await Swal.fire({
                title: 'Programmer un Rapport',
                html: `
                    <div class="mb-3">
                        <label class="form-label">Fréquence</label>
                        <select id="frequency" class="form-select">
                            <option value="daily">Quotidien</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly">Mensuel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email de destination</label>
                        <input id="email" type="email" class="form-control" placeholder="votre@email.com">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Programmer',
                cancelButtonText: 'Annuler',
                preConfirm: () => {
                    return {
                        frequency: document.getElementById('frequency').value,
                        email: document.getElementById('email').value
                    }
                }
            });

            if (formValues) {
                Swal.fire({
                    icon: 'success',
                    title: 'Rapport programmé !',
                    text: `Rapport ${formValues.frequency} programmé pour ${formValues.email}`,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        },

        async configureKPIs() {
            Swal.fire({
                icon: 'info',
                title: 'Configuration KPIs',
                text: 'Fonctionnalité en développement',
                showConfirmButton: true
            });
        },

        exportStats() {
            this.exportData('excel');
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.carrier-color-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.progress {
    height: 6px;
}

.list-group-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .h5 {
        font-size: 1.1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
}

/* Animation pour les métriques */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.h5 {
    animation: countUp 0.6s ease-out;
}

/* Scrollbar pour les tableaux sur mobile */
.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.table-responsive::-webkit-scrollbar {
    height: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush