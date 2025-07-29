@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid" x-data="deliveryPickups">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-truck text-primary me-2"></i>
                Gestion des Enlèvements
            </h1>
            <p class="text-muted mb-0">Validez et suivez vos enlèvements</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Brouillon
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.draft"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Validés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.validated"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Récupérés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.picked_up"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Problèmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.problem"></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                <div class="col-md-3">
                    <select class="form-select" x-model="statusFilter" @change="loadPickups()">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillon</option>
                        <option value="validated">Validé</option>
                        <option value="picked_up">Récupéré</option>
                        <option value="problem">Problème</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="carrierFilter" @change="loadPickups()">
                        <option value="">Tous les transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis Express</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" 
                           class="form-control" 
                           x-model="dateFilter"
                           @change="loadPickups()"
                           placeholder="Date d'enlèvement">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100" @click="loadPickups()">
                        <i class="fas fa-sync me-1"></i>
                        Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des pickups -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-1"></i>
                Liste des Enlèvements
            </h6>
        </div>
        <div class="card-body p-0">
            <!-- Chargement -->
            <div x-show="loading" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="text-muted mt-2">Chargement...</p>
            </div>

            <!-- Message vide -->
            <div x-show="!loading && pickups.length === 0" class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun enlèvement trouvé</h5>
                <p class="text-muted">Créez votre premier enlèvement pour commencer.</p>
                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Créer un Enlèvement
                </a>
            </div>

            <!-- Table -->
            <div x-show="!loading && pickups.length > 0" class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Transporteur</th>
                            <th>Date d'Enlèvement</th>
                            <th>Commandes</th>
                            <th>Montant Total</th>
                            <th>Statut</th>
                            <th>Créé</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="pickup in pickups" :key="pickup.id">
                            <tr>
                                <td>
                                    <strong x-text="`#${pickup.id}`"></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i :class="getCarrierIcon(pickup.carrier_slug)" class="me-2"></i>
                                        <div>
                                            <div x-text="pickup.delivery_configuration?.integration_name"></div>
                                            <small class="text-muted" x-text="getCarrierName(pickup.carrier_slug)"></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span x-text="formatDate(pickup.pickup_date)"></span>
                                        <br>
                                        <small :class="getDateClass(pickup.pickup_date)" x-text="getDateStatus(pickup.pickup_date)"></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="h6 mb-0" x-text="pickup.shipments?.length || 0"></div>
                                        <small class="text-muted">commandes</small>
                                    </div>
                                </td>
                                <td>
                                    <strong x-text="`${pickup.total_cod_amount || 0} TND`"></strong>
                                </td>
                                <td>
                                    @include('admin.delivery.components.pickup-status-badge', ['pickup' => 'pickup'])
                                </td>
                                <td>
                                    <small x-text="formatDateTime(pickup.created_at)"></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info" 
                                                @click="showPickupDetails(pickup.id)"
                                                title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button x-show="pickup.status === 'draft'" 
                                                class="btn btn-sm btn-outline-success" 
                                                @click="validatePickup(pickup.id)"
                                                title="Valider">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button x-show="pickup.status === 'validated'" 
                                                class="btn btn-sm btn-outline-primary" 
                                                @click="markAsPickedUp(pickup.id)"
                                                title="Marquer comme récupéré">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                        
                                        <button x-show="pickup.status === 'draft'" 
                                                class="btn btn-sm btn-outline-danger" 
                                                @click="deletePickup(pickup.id)"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav x-show="pickups.length > 0" class="mt-3 d-flex justify-content-center">
                <ul class="pagination">
                    <li class="page-item" :class="{ 'disabled': currentPage <= 1 }">
                        <button class="page-link" @click="changePage(currentPage - 1)">Précédent</button>
                    </li>
                    
                    <template x-for="page in [1, 2, 3, 4, 5]" :key="page">
                        <li x-show="page <= totalPages" class="page-item" :class="{ 'active': page === currentPage }">
                            <button class="page-link" @click="changePage(page)" x-text="page"></button>
                        </li>
                    </template>
                    
                    <li class="page-item" :class="{ 'disabled': currentPage >= totalPages }">
                        <button class="page-link" @click="changePage(currentPage + 1)">Suivant</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal de détails du pickup -->
@include('admin.delivery.modals.pickup-details')
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryPickups', () => ({
        loading: false,
        pickups: [],
        stats: {
            draft: 0,
            validated: 0,
            picked_up: 0,
            problem: 0
        },
        statusFilter: '',
        carrierFilter: '',
        dateFilter: '',
        currentPage: 1,
        totalPages: 1,
        selectedPickup: null,

        init() {
            this.loadPickups();
            this.loadStats();
            
            // Actualiser automatiquement toutes les 30 secondes
            setInterval(() => {
                this.loadPickups();
                this.loadStats();
            }, 30000);
        },

        async loadPickups() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    status: this.statusFilter,
                    carrier: this.carrierFilter,
                    date: this.dateFilter
                });

                const response = await axios.get(`{{ route('admin.delivery.pickups') }}?${params}`);
                
                if (response.data) {
                    this.pickups = response.data.data || response.data.pickups || [];
                    this.totalPages = response.data.last_page || 1;
                    this.currentPage = response.data.current_page || 1;
                }
            } catch (error) {
                console.error('Erreur chargement pickups:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les enlèvements',
                });
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                // Calculer les stats à partir des pickups actuels
                this.stats = {
                    draft: this.pickups.filter(p => p.status === 'draft').length,
                    validated: this.pickups.filter(p => p.status === 'validated').length,
                    picked_up: this.pickups.filter(p => p.status === 'picked_up').length,
                    problem: this.pickups.filter(p => p.status === 'problem').length
                };
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        },

        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadPickups();
            }
        },

        async showPickupDetails(pickupId) {
            try {
                const response = await axios.get(`{{ route('admin.delivery.pickups.show', '') }}/${pickupId}/details`);
                
                if (response.data.success) {
                    this.selectedPickup = response.data.pickup;
                    
                    // Ouvrir la modal
                    const modal = new bootstrap.Modal(document.getElementById('pickupDetailsModal'));
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

        async validatePickup(pickupId) {
            const result = await Swal.fire({
                title: 'Valider l\'enlèvement ?',
                text: 'Cette action enverra les commandes au transporteur',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            this.loading = true;

            try {
                const response = await axios.post(`{{ route('admin.delivery.pickups.validate', '') }}/${pickupId}`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enlèvement validé !',
                        text: 'Les commandes ont été envoyées au transporteur',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadPickups();
                } else {
                    throw new Error(response.data.message || 'Erreur de validation');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de validation',
                    text: error.response?.data?.message || error.message || 'Impossible de valider l\'enlèvement',
                });
            } finally {
                this.loading = false;
            }
        },

        async markAsPickedUp(pickupId) {
            const result = await Swal.fire({
                title: 'Marquer comme récupéré ?',
                text: 'Confirmez que le transporteur a récupéré les colis',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmer',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                // Note: Cette route n'est pas encore implémentée dans le contrôleur
                // Elle sera ajoutée dans les phases suivantes
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/mark-picked-up`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Statut mis à jour !',
                        text: 'L\'enlèvement est marqué comme récupéré',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadPickups();
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de mettre à jour le statut',
                });
            }
        },

        async deletePickup(pickupId) {
            const result = await Swal.fire({
                title: 'Supprimer l\'enlèvement ?',
                text: 'Cette action est irréversible !',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Supprimer',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await axios.delete(`{{ route('admin.delivery.pickups.destroy', '') }}/${pickupId}`);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Supprimé !',
                    text: 'L\'enlèvement a été supprimé',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                this.loadPickups();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.response?.data?.message || 'Impossible de supprimer l\'enlèvement',
                });
            }
        },

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

        formatDateTime(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getDateStatus(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            const today = new Date();
            const diffTime = date.getTime() - today.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) return `En retard de ${Math.abs(diffDays)} jour(s)`;
            if (diffDays === 0) return 'Aujourd\'hui';
            if (diffDays === 1) return 'Demain';
            return `Dans ${diffDays} jours`;
        },

        getDateClass(dateString) {
            if (!dateString) return 'text-muted';
            
            const date = new Date(dateString);
            const today = new Date();
            const diffTime = date.getTime() - today.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) return 'text-danger';
            if (diffDays === 0) return 'text-warning';
            return 'text-muted';
        }
    }));
});
</script>
@endpush