@extends('layouts.admin')

@section('title', 'Gestion des Expéditions')

@section('content')
<div class="container-fluid" x-data="deliveryShipments">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shipping-fast text-primary me-2"></i>
                Gestion des Expéditions
            </h1>
            <p class="text-muted mb-0">Suivez et gérez vos expéditions en temps réel</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" @click="trackAllShipments()">
                <i class="fas fa-sync me-1"></i>
                Actualiser Tout
            </button>
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Créées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.created"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Transit
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.in_transit"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Livrées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.delivered"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Problèmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.problems"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                En Retour
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.in_return"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-undo fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.total"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <input type="text" 
                           class="form-control" 
                           x-model="searchFilter"
                           @input.debounce.300ms="loadShipments()"
                           placeholder="N° suivi, commande...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" x-model="statusFilter" @change="loadShipments()">
                        <option value="">Tous les statuts</option>
                        <option value="created">Créée</option>
                        <option value="validated">Validée</option>
                        <option value="picked_up_by_carrier">Récupérée</option>
                        <option value="in_transit">En Transit</option>
                        <option value="delivered">Livrée</option>
                        <option value="in_return">En Retour</option>
                        <option value="anomaly">Anomalie</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" x-model="carrierFilter" @change="loadShipments()">
                        <option value="">Tous transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis Express</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" 
                           class="form-control" 
                           x-model="dateFilter"
                           @change="loadShipments()">
                </div>
                <div class="col-md-2">
                    <select class="form-select" x-model="perPage" @change="loadShipments()">
                        <option value="25">25 par page</option>
                        <option value="50">50 par page</option>
                        <option value="100">100 par page</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-primary" @click="loadShipments()">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des expéditions -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-1"></i>
                Liste des Expéditions
                <span x-show="shipments.length > 0" class="badge bg-primary ms-2" x-text="totalShipments"></span>
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" 
                        @click="trackSelectedShipments()"
                        x-show="selectedShipments.length > 0">
                    <i class="fas fa-sync me-1"></i>
                    Actualiser Sélection (<span x-text="selectedShipments.length"></span>)
                </button>
                <button class="btn btn-sm btn-outline-primary" 
                        @click="exportShipments()"
                        x-show="shipments.length > 0">
                    <i class="fas fa-download me-1"></i>
                    Exporter
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Chargement -->
            <div x-show="loading" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="text-muted mt-2">Chargement des expéditions...</p>
            </div>

            <!-- Message vide -->
            <div x-show="!loading && shipments.length === 0" class="text-center py-5">
                <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune expédition trouvée</h5>
                <p class="text-muted">Créez votre premier enlèvement pour voir les expéditions ici.</p>
                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Créer un Enlèvement
                </a>
            </div>

            <!-- Table -->
            <div x-show="!loading && shipments.length > 0" class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" 
                                       class="form-check-input"
                                       @change="toggleAllShipments($event.target.checked)">
                            </th>
                            <th>Suivi</th>
                            <th>Commande</th>
                            <th>Client</th>
                            <th>Destination</th>
                            <th>Transporteur</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Dernière MAJ</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="shipment in shipments" :key="shipment.id">
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           class="form-check-input"
                                           :value="shipment.id"
                                           x-model="selectedShipments">
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold" x-text="shipment.pos_barcode || shipment.pos_reference || '-'"></span>
                                        <br>
                                        <small class="text-muted">ID: <span x-text="shipment.id"></span></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong x-text="`#${shipment.order?.id}`"></strong>
                                        <br>
                                        <small class="text-muted" x-text="formatDate(shipment.order?.created_at)"></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div x-text="shipment.recipient_info?.name"></div>
                                        <small class="text-muted" x-text="shipment.recipient_info?.phone"></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span x-text="shipment.recipient_info?.city"></span>
                                        <br>
                                        <small class="text-muted" x-text="shipment.recipient_info?.governorate"></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i :class="getCarrierIcon(shipment.carrier_slug)" class="me-2"></i>
                                        <small x-text="getCarrierName(shipment.carrier_slug)"></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong x-text="`${shipment.cod_amount} TND`"></strong>
                                        <br>
                                        <small class="text-muted" x-text="`${shipment.weight}kg`"></small>
                                    </div>
                                </td>
                                <td>
                                    @include('admin.delivery.components.shipment-status-badge', ['shipment' => 'shipment'])
                                </td>
                                <td>
                                    <small x-text="getTimeSince(shipment.carrier_last_status_update || shipment.updated_at)"></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info" 
                                                @click="showShipmentDetails(shipment.id)"
                                                title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-outline-primary" 
                                                @click="trackShipment(shipment.id)"
                                                title="Actualiser suivi"
                                                :disabled="!shipment.pos_barcode">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        
                                        <button x-show="shipment.status === 'in_transit'" 
                                                class="btn btn-sm btn-outline-success" 
                                                @click="markAsDelivered(shipment.id)"
                                                title="Marquer livré">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav x-show="shipments.length > 0 && pagination" class="mt-3 d-flex justify-content-between align-items-center px-3 pb-3">
                <div>
                    <small class="text-muted">
                        Affichage de <span x-text="((pagination?.current_page - 1) * pagination?.per_page) + 1"></span> 
                        à <span x-text="Math.min(pagination?.current_page * pagination?.per_page, pagination?.total)"></span> 
                        sur <span x-text="pagination?.total"></span> expéditions
                    </small>
                </div>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ 'disabled': pagination?.current_page <= 1 }">
                        <button class="page-link" @click="changePage(pagination?.current_page - 1)">Précédent</button>
                    </li>
                    
                    <template x-for="page in getPaginationPages()" :key="page">
                        <li class="page-item" :class="{ 'active': page === pagination?.current_page }">
                            <button class="page-link" @click="changePage(page)" x-text="page"></button>
                        </li>
                    </template>
                    
                    <li class="page-item" :class="{ 'disabled': pagination?.current_page >= pagination?.last_page }">
                        <button class="page-link" @click="changePage(pagination?.current_page + 1)">Suivant</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modales -->
@include('admin.delivery.modals.shipment-details')
@include('admin.delivery.modals.tracking-details')
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryShipments', () => ({
        loading: false,
        shipments: [],
        selectedShipments: [],
        selectedShipment: null,
        stats: {
            created: 0,
            in_transit: 0,
            delivered: 0,
            problems: 0,
            in_return: 0,
            total: 0
        },
        
        // Filtres
        searchFilter: '',
        statusFilter: '',
        carrierFilter: '',
        dateFilter: '',
        perPage: 25,
        
        // Pagination
        pagination: null,
        totalShipments: 0,

        init() {
            this.loadShipments();
            this.loadStats();
            
            // Auto-refresh toutes les 2 minutes
            setInterval(() => {
                this.loadShipments();
                this.loadStats();
            }, 120000);
        },

        async loadShipments() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    search: this.searchFilter,
                    status: this.statusFilter,
                    carrier: this.carrierFilter,
                    date: this.dateFilter,
                    per_page: this.perPage,
                    page: this.pagination?.current_page || 1
                });

                const response = await axios.get(`{{ route('admin.delivery.shipments') }}?${params}`);
                
                if (response.data) {
                    this.shipments = response.data.data || response.data.shipments || [];
                    this.pagination = response.data.pagination || response.data;
                    this.totalShipments = response.data.total || this.shipments.length;
                }
            } catch (error) {
                console.error('Erreur chargement expéditions:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les expéditions',
                });
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                // Calculer les stats à partir des expéditions
                this.stats = {
                    created: this.shipments.filter(s => s.status === 'created').length,
                    in_transit: this.shipments.filter(s => ['validated', 'picked_up_by_carrier', 'in_transit'].includes(s.status)).length,
                    delivered: this.shipments.filter(s => s.status === 'delivered').length,
                    problems: this.shipments.filter(s => s.status === 'anomaly').length,
                    in_return: this.shipments.filter(s => s.status === 'in_return').length,
                    total: this.shipments.length
                };
            } catch (error) {
                console.error('Erreur calcul stats:', error);
            }
        },

        changePage(page) {
            if (page >= 1 && page <= (this.pagination?.last_page || 1)) {
                this.pagination.current_page = page;
                this.loadShipments();
            }
        },

        getPaginationPages() {
            if (!this.pagination) return [];
            
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const pages = [];
            
            for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                pages.push(i);
            }
            
            return pages;
        },

        clearFilters() {
            this.searchFilter = '';
            this.statusFilter = '';
            this.carrierFilter = '';
            this.dateFilter = '';
            this.loadShipments();
        },

        toggleAllShipments(checked) {
            if (checked) {
                this.selectedShipments = this.shipments.map(s => s.id);
            } else {
                this.selectedShipments = [];
            }
        },

        async showShipmentDetails(shipmentId) {
            try {
                const response = await axios.get(`{{ route('admin.delivery.shipments.show', '') }}/${shipmentId}`);
                
                if (response.data.success) {
                    this.selectedShipment = response.data.shipment;
                    
                    const modal = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));
                    modal.show();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les détails',
                });
            }
        },

        async trackShipment(shipmentId) {
            try {
                const response = await axios.post(`{{ route('admin.delivery.shipments.track', '') }}/${shipmentId}`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Suivi mis à jour !',
                        text: 'Le statut a été actualisé',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadShipments();
                } else {
                    throw new Error(response.data.message || 'Erreur de suivi');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de suivi',
                    text: error.response?.data?.message || error.message || 'Impossible de mettre à jour le suivi',
                });
            }
        },

        async trackSelectedShipments() {
            if (this.selectedShipments.length === 0) return;

            this.loading = true;
            let successCount = 0;
            let errorCount = 0;

            for (const shipmentId of this.selectedShipments) {
                try {
                    await this.trackShipment(shipmentId);
                    successCount++;
                } catch (error) {
                    errorCount++;
                }
            }

            this.loading = false;
            this.selectedShipments = [];

            Swal.fire({
                icon: errorCount === 0 ? 'success' : 'warning',
                title: 'Suivi terminé',
                html: `
                    <p><strong>${successCount}</strong> expédition(s) mise(s) à jour</p>
                    ${errorCount > 0 ? `<p><strong>${errorCount}</strong> erreur(s)</p>` : ''}
                `,
            });

            this.loadShipments();
        },

        async markAsDelivered(shipmentId) {
            const result = await Swal.fire({
                title: 'Marquer comme livré ?',
                text: 'Confirmez que cette expédition a été livrée',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmer',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await axios.post(`{{ route('admin.delivery.shipments.mark-delivered', '') }}/${shipmentId}`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Livraison confirmée !',
                        text: 'L\'expédition est marquée comme livrée',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadShipments();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de marquer comme livré',
                });
            }
        },

        async trackAllShipments() {
            const result = await Swal.fire({
                title: 'Actualiser toutes les expéditions ?',
                text: 'Cette opération peut prendre quelques minutes',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Actualiser',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            this.loading = true;

            try {
                const response = await axios.post('{{ route("admin.delivery.api.track-all") }}');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Actualisation terminée !',
                    text: 'Toutes les expéditions ont été mises à jour',
                    showConfirmButton: false,
                    timer: 3000
                });
                
                this.loadShipments();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible d\'actualiser toutes les expéditions',
                });
            } finally {
                this.loading = false;
            }
        },

        exportShipments() {
            // TODO: Implémenter l'export
            Swal.fire({
                icon: 'info',
                title: 'Export en développement',
                text: 'Fonctionnalité à venir',
            });
        },

        // Utilitaires
        getCarrierIcon(carrierSlug) {
            return carrierSlug === 'jax_delivery' ? 'fas fa-truck text-primary' : 'fas fa-shipping-fast text-success';
        },

        getCarrierName(carrierSlug) {
            return carrierSlug === 'jax_delivery' ? 'JAX Delivery' : 'Mes Colis Express';
        },

        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('fr-FR');
        },

        getTimeSince(dateString) {
            if (!dateString) return '';
            
            const now = new Date();
            const date = new Date(dateString);
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) return `${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`;
            return `${Math.floor(diffInMinutes / 1440)}j`;
        }
    }));
});
</script>
@endpush