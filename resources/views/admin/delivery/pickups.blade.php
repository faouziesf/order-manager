@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid" x-data="deliveryPickups">
    <!-- Header avec breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gradient">
                <i class="fas fa-warehouse text-primary me-2"></i>
                Gestion des Enlèvements
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Livraisons</a></li>
                    <li class="breadcrumb-item active">Enlèvements</li>
                </ol>
            </nav>
            <p class="text-muted mb-0">Gérez vos enlèvements de colis</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary animate-slide-up">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-success animate-slide-up">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.1s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Brouillons
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.draft">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-secondary bg-opacity-10">
                                <i class="fas fa-edit fa-2x text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.2s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Validés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.validated">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-success bg-opacity-10">
                                <i class="fas fa-check fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.3s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Récupérés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.picked_up">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-info bg-opacity-10">
                                <i class="fas fa-truck fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm animate-slide-up" style="animation-delay: 0.4s;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Problèmes
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.problems">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-danger bg-opacity-10">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card border-0 shadow-sm mb-4 animate-slide-up">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               placeholder="Rechercher un enlèvement..."
                               x-model="searchQuery"
                               @input.debounce.300ms="filterPickups()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="statusFilter" @change="filterPickups()">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillons</option>
                        <option value="validated">Validés</option>
                        <option value="picked_up">Récupérés</option>
                        <option value="problem">Problèmes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="carrierFilter" @change="filterPickups()">
                        <option value="">Tous les transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis Express</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" @click="refreshPickups()" :disabled="loading">
                        <span x-show="!loading">
                            <i class="fas fa-sync me-1"></i>
                            Actualiser
                        </span>
                        <span x-show="loading">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des enlèvements -->
    <div class="card border-0 shadow-sm animate-slide-up">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-1"></i>
                Enlèvements
                <span x-show="filteredPickups.length > 0" class="badge bg-primary ms-2" x-text="filteredPickups.length"></span>
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" @click="validateSelected()" x-show="selectedPickups.length > 0">
                    <i class="fas fa-check me-1"></i>
                    Valider Sélection
                </button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" @click="exportPickups()">
                            <i class="fas fa-download me-2"></i>Exporter
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" @click="bulkActions()">
                            <i class="fas fa-tasks me-2"></i>Actions groupées
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Chargement -->
            <div x-show="loading" class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted">Chargement des enlèvements...</p>
            </div>

            <!-- Aucun enlèvement -->
            <div x-show="!loading && filteredPickups.length === 0" class="text-center py-5">
                <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                <h6 class="text-muted mb-1">Aucun enlèvement trouvé</h6>
                <p class="text-muted small mb-4">Créez votre premier enlèvement pour commencer</p>
                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Créer un Enlèvement
                </a>
            </div>

            <!-- Tableau des enlèvements -->
            <div x-show="!loading && filteredPickups.length > 0" class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0" style="width: 50px;">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           :checked="isAllSelected()"
                                           @change="toggleAllSelection($event.target.checked)">
                                </div>
                            </th>
                            <th class="border-0">ID</th>
                            <th class="border-0">Transporteur</th>
                            <th class="border-0">Date</th>
                            <th class="border-0">Commandes</th>
                            <th class="border-0">Poids/COD</th>
                            <th class="border-0">Statut</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="pickup in filteredPickups" :key="pickup.id">
                            <tr :class="{ 'table-primary': isSelected(pickup.id) }" class="pickup-row">
                                <td @click.stop>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               :checked="isSelected(pickup.id)"
                                               @change="toggleSelection(pickup.id)">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="pickup-indicator me-2" :class="getStatusIndicatorClass(pickup.status)"></div>
                                        <div>
                                            <strong x-text="`#${pickup.id}`" class="text-primary"></strong>
                                            <br><small class="text-muted" x-text="pickup.created_at ? formatDateTime(pickup.created_at) : ''"></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary bg-opacity-10 me-2" style="width: 32px; height: 32px;">
                                            <i :class="getCarrierIcon(pickup.carrier_slug)" class="text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" x-text="getCarrierName(pickup.carrier_slug)"></div>
                                            <small class="text-muted" x-text="pickup.configuration_name"></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div x-text="formatDate(pickup.pickup_date)"></div>
                                    <small class="text-muted" x-text="getRelativeDate(pickup.pickup_date)"></small>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="h6 mb-0 text-primary" x-text="pickup.orders_count"></div>
                                        <small class="text-muted">commandes</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><strong x-text="`${pickup.total_weight} kg`"></strong></div>
                                        <small class="text-muted text-success" x-text="`${pickup.total_cod_amount} TND`"></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadgeClass(pickup.status)">
                                        <i :class="getStatusIcon(pickup.status)" class="me-1"></i>
                                        <span x-text="getStatusLabel(pickup.status)"></span>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                @click="viewPickup(pickup.id)"
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
                                                class="btn btn-sm btn-outline-info" 
                                                @click="markAsPickedUp(pickup.id)"
                                                title="Marquer récupéré">
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
        </div>

        <!-- Pagination -->
        <div x-show="!loading && filteredPickups.length > 0" class="card-footer bg-transparent">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" x-text="`Affichage de ${filteredPickups.length} enlèvement(s)`"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item">
                            <a class="page-link" href="#" @click.prevent="previousPage()">Précédent</a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link" href="#" x-text="currentPage"></a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#" @click.prevent="nextPage()">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal détails pickup -->
    <div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-warehouse me-2"></i>
                        Détails de l'Enlèvement
                        <span x-show="selectedPickup" x-text="`#${selectedPickup?.id}`"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedPickup">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Informations Générales
                                    </h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>ID:</strong> <span x-text="`#${selectedPickup?.id}`"></span>
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Statut:</strong>
                                            <span class="badge ms-1" :class="getStatusBadgeClass(selectedPickup?.status)">
                                                <span x-text="getStatusLabel(selectedPickup?.status)"></span>
                                            </span>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <strong>Date:</strong> <span x-text="formatDate(selectedPickup?.pickup_date)"></span>
                                        </div>
                                        <div class="col-sm-6 mt-2">
                                            <strong>Créé:</strong> <span x-text="formatDateTime(selectedPickup?.created_at)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-truck me-1"></i>
                                        Transporteur
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                                            <i :class="getCarrierIcon(selectedPickup?.carrier_slug)" class="text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold" x-text="getCarrierName(selectedPickup?.carrier_slug)"></div>
                                            <small class="text-muted" x-text="selectedPickup?.configuration_name"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-4 text-center">
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <div class="h4 text-primary mb-0" x-text="selectedPickup?.orders_count || 0"></div>
                                <small class="text-muted">Commandes</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <div class="h4 text-success mb-0" x-text="`${selectedPickup?.total_weight || 0} kg`"></div>
                                <small class="text-muted">Poids Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <div class="h4 text-info mb-0" x-text="selectedPickup?.total_pieces || 0"></div>
                                <small class="text-muted">Nb Pièces</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <div class="h4 text-warning mb-0" x-text="`${selectedPickup?.total_cod_amount || 0} TND`"></div>
                                <small class="text-muted">COD Total</small>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="card border-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-1"></i>
                                Commandes Incluses
                                <span class="badge bg-primary ms-2" x-text="selectedPickup?.orders?.length || 0"></span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Commande</th>
                                            <th>Client</th>
                                            <th>Téléphone</th>
                                            <th>Adresse</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="order in selectedPickup?.orders || []" :key="order.id">
                                            <tr>
                                                <td><strong x-text="`#${order.id}`"></strong></td>
                                                <td x-text="order.customer_name"></td>
                                                <td x-text="order.customer_phone"></td>
                                                <td>
                                                    <div x-text="order.customer_city"></div>
                                                    <small class="text-muted" x-text="order.region_name"></small>
                                                </td>
                                                <td><strong x-text="`${order.total_price} TND`"></strong></td>
                                                <td>
                                                    <span class="badge bg-primary" x-text="order.status"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0">
                    <div class="d-flex justify-content-between w-100">
                        <div>
                            <!-- Actions selon le statut -->
                            <button x-show="selectedPickup?.status === 'draft'" 
                                    class="btn btn-success"
                                    @click="validatePickup(selectedPickup.id); closeModal()">
                                <i class="fas fa-check me-1"></i>
                                Valider l'Enlèvement
                            </button>
                            
                            <button x-show="selectedPickup?.status === 'validated'" 
                                    class="btn btn-info"
                                    @click="markAsPickedUp(selectedPickup.id); closeModal()">
                                <i class="fas fa-truck me-1"></i>
                                Marquer Récupéré
                            </button>
                            
                            <button x-show="selectedPickup?.status === 'draft'" 
                                    class="btn btn-outline-danger ms-2"
                                    @click="deletePickup(selectedPickup.id); closeModal()">
                                <i class="fas fa-trash me-1"></i>
                                Supprimer
                            </button>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pickup-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .pickup-indicator.draft {
        background: var(--secondary-color);
        border: 2px solid var(--card-border);
    }

    .pickup-indicator.validated {
        background: var(--success-color);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    .pickup-indicator.picked_up {
        background: var(--info-color);
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2);
    }

    .pickup-indicator.problem {
        background: var(--danger-color);
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    .pickup-row {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pickup-row:hover {
        background: rgba(30, 64, 175, 0.03);
        transform: translateX(2px);
    }

    .pickup-row.table-primary {
        background-color: rgba(30, 64, 175, 0.1) !important;
    }

    .text-xs {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: var(--text-muted);
    }

    .breadcrumb-item a {
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb-item a:hover {
        color: var(--primary-color);
    }

    .input-group-text {
        background: var(--light-color);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .modal-content {
        border-radius: var(--border-radius-lg);
        overflow: hidden;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryPickups', () => ({
        loading: false,
        searchQuery: '',
        statusFilter: '',
        carrierFilter: '',
        currentPage: 1,
        pickups: [],
        filteredPickups: [],
        selectedPickups: [],
        selectedPickup: null,
        stats: {
            draft: 0,
            validated: 0,
            picked_up: 0,
            problems: 0
        },

        init() {
            this.loadPickups();
            this.loadStats();
            
            // Actualiser toutes les 30 secondes
            setInterval(() => {
                this.loadPickups(false);
                this.loadStats();
            }, 30000);
        },

        async loadPickups(showLoading = true) {
            if (showLoading) this.loading = true;
            
            try {
                // Simuler le chargement des enlèvements
                await new Promise(resolve => setTimeout(resolve, 800));
                
                // Données simulées - remplacer par un vrai appel API
                this.pickups = [
                    {
                        id: 1,
                        status: 'draft',
                        carrier_slug: 'jax_delivery',
                        configuration_name: 'Boutique Principale',
                        pickup_date: '2024-02-15',
                        created_at: new Date(Date.now() - 2 * 60 * 60 * 1000),
                        orders_count: 8,
                        total_weight: 12.5,
                        total_pieces: 15,
                        total_cod_amount: 456.750,
                        orders: [
                            { id: 1001, customer_name: 'Ahmed Ben Ali', customer_phone: '20123456', customer_city: 'Tunis', region_name: 'Tunis', total_price: 89.900, status: 'confirmée' },
                            { id: 1002, customer_name: 'Fatma Trabelsi', customer_phone: '25987654', customer_city: 'Ariana', region_name: 'Ariana', total_price: 156.450, status: 'confirmée' }
                        ]
                    },
                    {
                        id: 2,
                        status: 'validated',
                        carrier_slug: 'mes_colis',
                        configuration_name: 'Entrepôt Nord',
                        pickup_date: '2024-02-14',
                        created_at: new Date(Date.now() - 24 * 60 * 60 * 1000),
                        orders_count: 12,
                        total_weight: 18.3,
                        total_pieces: 24,
                        total_cod_amount: 789.200,
                        orders: []
                    },
                    {
                        id: 3,
                        status: 'picked_up',
                        carrier_slug: 'jax_delivery',
                        configuration_name: 'Boutique Sud',
                        pickup_date: '2024-02-13',
                        created_at: new Date(Date.now() - 48 * 60 * 60 * 1000),
                        orders_count: 5,
                        total_weight: 7.8,
                        total_pieces: 9,
                        total_cod_amount: 234.500,
                        orders: []
                    }
                ];
                
                this.filterPickups();
            } catch (error) {
                console.error('Erreur chargement enlèvements:', error);
                this.pickups = [];
                this.filteredPickups = [];
            } finally {
                if (showLoading) this.loading = false;
            }
        },

        async loadStats() {
            // Calculer les stats à partir des données
            this.stats = {
                draft: this.pickups.filter(p => p.status === 'draft').length,
                validated: this.pickups.filter(p => p.status === 'validated').length,
                picked_up: this.pickups.filter(p => p.status === 'picked_up').length,
                problems: this.pickups.filter(p => p.status === 'problem').length
            };
        },

        filterPickups() {
            let filtered = [...this.pickups];

            // Filtre de recherche
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(pickup => 
                    pickup.id.toString().includes(query) ||
                    pickup.configuration_name.toLowerCase().includes(query)
                );
            }

            // Filtre par statut
            if (this.statusFilter) {
                filtered = filtered.filter(pickup => pickup.status === this.statusFilter);
            }

            // Filtre par transporteur
            if (this.carrierFilter) {
                filtered = filtered.filter(pickup => pickup.carrier_slug === this.carrierFilter);
            }

            this.filteredPickups = filtered;
        },

        refreshPickups() {
            this.loadPickups();
        },

        // Méthodes de sélection
        isSelected(pickupId) {
            return this.selectedPickups.includes(pickupId);
        },

        toggleSelection(pickupId) {
            if (this.isSelected(pickupId)) {
                this.selectedPickups = this.selectedPickups.filter(id => id !== pickupId);
            } else {
                this.selectedPickups.push(pickupId);
            }
        },

        isAllSelected() {
            return this.filteredPickups.length > 0 && 
                   this.filteredPickups.every(pickup => this.isSelected(pickup.id));
        },

        toggleAllSelection(checked) {
            if (checked) {
                this.selectedPickups = [...new Set([...this.selectedPickups, ...this.filteredPickups.map(p => p.id)])];
            } else {
                const idsToRemove = this.filteredPickups.map(p => p.id);
                this.selectedPickups = this.selectedPickups.filter(id => !idsToRemove.includes(id));
            }
        },

        // Actions sur les enlèvements
        viewPickup(pickupId) {
            this.selectedPickup = this.pickups.find(p => p.id === pickupId);
            const modal = new bootstrap.Modal(document.getElementById('pickupDetailsModal'));
            modal.show();
        },

        closeModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('pickupDetailsModal'));
            if (modal) modal.hide();
        },

        async validatePickup(pickupId) {
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enlèvement validé !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadPickups(false);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de valider l\'enlèvement'
                });
            }
        },

        async markAsPickedUp(pickupId) {
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/picked-up`);
                
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Enlèvement récupéré !',
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.loadPickups(false);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de marquer comme récupéré'
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
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/admin/delivery/pickups/${pickupId}`);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Supprimé !',
                        text: 'Enlèvement supprimé avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    this.loadPickups(false);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de supprimer l\'enlèvement'
                    });
                }
            }
        },

        async validateSelected() {
            if (this.selectedPickups.length === 0) return;

            const result = await Swal.fire({
                title: `Valider ${this.selectedPickups.length} enlèvement(s) ?`,
                text: 'Les enlèvements seront envoyés aux transporteurs',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler'
            });

            if (result.isConfirmed) {
                try {
                    await axios.post('/admin/delivery/pickups/bulk-validate', {
                        pickup_ids: this.selectedPickups
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Enlèvements validés !',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    this.selectedPickups = [];
                    this.loadPickups(false);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de valider les enlèvements'
                    });
                }
            }
        },

        // Méthodes utilitaires
        getStatusIndicatorClass(status) {
            return status || 'draft';
        },

        getStatusBadgeClass(status) {
            const classes = {
                'draft': 'bg-secondary',
                'validated': 'bg-success',
                'picked_up': 'bg-info',
                'problem': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        },

        getStatusIcon(status) {
            const icons = {
                'draft': 'fas fa-edit',
                'validated': 'fas fa-check',
                'picked_up': 'fas fa-truck',
                'problem': 'fas fa-exclamation-triangle'
            };
            return icons[status] || 'fas fa-question';
        },

        getStatusLabel(status) {
            const labels = {
                'draft': 'Brouillon',
                'validated': 'Validé',
                'picked_up': 'Récupéré',
                'problem': 'Problème'
            };
            return labels[status] || 'Inconnu';
        },

        getCarrierIcon(carrierSlug) {
            const icons = {
                'jax_delivery': 'fas fa-truck',
                'mes_colis': 'fas fa-shipping-fast'
            };
            return icons[carrierSlug] || 'fas fa-truck';
        },

        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || 'Inconnu';
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR');
        },

        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString('fr-FR');
        },

        getRelativeDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInDays = Math.floor((date - now) / (1000 * 60 * 60 * 24));
            
            if (diffInDays === 0) return 'Aujourd\'hui';
            if (diffInDays === 1) return 'Demain';
            if (diffInDays === -1) return 'Hier';
            if (diffInDays > 1) return `Dans ${diffInDays} jours`;
            if (diffInDays < -1) return `Il y a ${Math.abs(diffInDays)} jours`;
            
            return '';
        },

        // Pagination
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            this.currentPage++;
        },

        // Actions supplémentaires
        exportPickups() {
            Swal.fire({
                icon: 'info',
                title: 'Fonctionnalité à venir',
                text: 'L\'export sera bientôt disponible'
            });
        },

        bulkActions() {
            Swal.fire({
                icon: 'info',
                title: 'Actions groupées',
                text: 'Sélectionnez des enlèvements pour voir les actions disponibles'
            });
        }
    }));
});
</script>
@endpush