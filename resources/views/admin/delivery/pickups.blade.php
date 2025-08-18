@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid" x-data="pickupsManager">
    <!-- Header Simple -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">
                <i class="fas fa-warehouse me-2"></i>
                Gestion des Enlèvements
                <span class="badge bg-info ms-2" x-show="pickups.length > 0" x-text="pickups.length"></span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Livraisons</a></li>
                    <li class="breadcrumb-item active">Enlèvements</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" @click="loadPickups()" :disabled="loading">
                <i class="fas fa-sync me-1" :class="{ 'fa-spin': loading }"></i>
                Actualiser
            </button>
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
        </div>
    </div>

    <!-- Filtres Simples -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               placeholder="Rechercher..."
                               x-model="filters.search"
                               @input.debounce.500ms="applyFilters()"
                               :disabled="loading">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.status" @change="applyFilters()" :disabled="loading">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillons</option>
                        <option value="validated">Validés</option>
                        <option value="picked_up">Récupérés</option>
                        <option value="problem">Problèmes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.carrier" @change="applyFilters()" :disabled="loading">
                        <option value="">Tous transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" @click="clearFilters()" :disabled="loading">
                        <i class="fas fa-times me-1"></i>
                        Effacer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Brouillons
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.draft">
                                <span x-show="loading">...</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-edit fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Validés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.validated">
                                <span x-show="loading">...</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Récupérés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.picked_up">
                                <span x-show="loading">...</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-truck fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Problèmes
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.problems">
                                <span x-show="loading">...</span>
                            </div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Pickups -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-1"></i>
                    Enlèvements
                    <span x-show="!loading && pickups.length > 0" class="badge bg-primary ms-2" x-text="pickups.length"></span>
                    <span x-show="loading" class="spinner-border spinner-border-sm ms-2" role="status"></span>
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success" 
                            x-show="selectedPickups.length > 0" 
                            @click="validateSelected()"
                            :disabled="loading">
                        <i class="fas fa-check me-1"></i>
                        Valider (<span x-text="selectedPickups.length"></span>)
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <!-- État de Chargement -->
            <div x-show="loading" class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h5 class="text-muted mb-2">Chargement des enlèvements...</h5>
                <p class="text-muted small">Récupération des données...</p>
            </div>

            <!-- Message d'erreur -->
            <div x-show="error && !loading" class="alert alert-danger m-4 border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3 fa-2x text-danger"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">Erreur de chargement</h5>
                        <p class="mb-2" x-text="error"></p>
                        <button class="btn btn-sm btn-danger" @click="loadPickups()">
                            <i class="fas fa-redo me-1"></i>Réessayer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Aucun enlèvement -->
            <div x-show="!loading && !error && pickups.length === 0" class="text-center py-5 m-4">
                <div class="empty-state">
                    <i class="fas fa-warehouse fa-4x text-muted mb-4"></i>
                    <h5 class="text-muted mb-2">Aucun enlèvement trouvé</h5>
                    <p class="text-muted mb-4">
                        <span x-show="!hasFilters()">Créez votre premier enlèvement pour commencer</span>
                        <span x-show="hasFilters()">Aucun enlèvement ne correspond aux filtres</span>
                    </p>
                    <div x-show="!hasFilters()">
                        <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Créer un Enlèvement
                        </a>
                    </div>
                    <div x-show="hasFilters()">
                        <button class="btn btn-outline-secondary" @click="clearFilters()">
                            <i class="fas fa-times me-1"></i>
                            Effacer les filtres
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Desktop -->
            <div x-show="!loading && pickups.length > 0" class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               :checked="isAllSelected()"
                                               @change="toggleAllSelection()">
                                    </div>
                                </th>
                                <th>ID & Date</th>
                                <th>Transporteur</th>
                                <th>Date Enlèvement</th>
                                <th class="text-center">Commandes</th>
                                <th>Totaux</th>
                                <th>Statut</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="pickup in pickups" :key="pickup.id">
                                <tr class="pickup-row" @click="viewPickup(pickup)" style="cursor: pointer;">
                                    <td @click.stop>
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   :checked="isSelected(pickup.id)"
                                                   @change="toggleSelection(pickup.id)">
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-primary" x-text="`#${pickup.id}`"></strong>
                                            <br><small class="text-muted" x-text="formatDateTime(pickup.created_at)"></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i :class="getCarrierIcon(pickup.carrier_slug)" class="text-primary me-2"></i>
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
                                    <td class="text-center">
                                        <div class="h6 mb-0 text-primary" x-text="pickup.orders_count || 0"></div>
                                        <small class="text-muted">commandes</small>
                                    </td>
                                    <td>
                                        <div>
                                            <div><strong x-text="`${pickup.total_weight || 0} kg`"></strong></div>
                                            <small class="text-success" x-text="`${pickup.total_cod_amount || 0} TND`"></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getStatusBadgeClass(pickup.status)">
                                            <i :class="getStatusIcon(pickup.status)" class="me-1"></i>
                                            <span x-text="getStatusLabel(pickup.status)"></span>
                                        </span>
                                    </td>
                                    <td @click.stop>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    @click="viewPickup(pickup)"
                                                    title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <button x-show="pickup.can_be_validated" 
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
                                            
                                            <button x-show="pickup.can_be_deleted" 
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

            <!-- Cards Mobile -->
            <div x-show="!loading && pickups.length > 0" class="d-lg-none p-3">
                <template x-for="pickup in pickups" :key="pickup.id">
                    <div class="card mb-3 pickup-card border-0 shadow-sm" 
                         @click="viewPickup(pickup)"
                         style="cursor: pointer;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="form-check me-2" @click.stop>
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               :checked="isSelected(pickup.id)"
                                               @change="toggleSelection(pickup.id)">
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-primary" x-text="`#${pickup.id}`"></h6>
                                        <small class="text-muted" x-text="getCarrierName(pickup.carrier_slug)"></small>
                                    </div>
                                </div>
                                <span class="badge" :class="getStatusBadgeClass(pickup.status)">
                                    <span x-text="getStatusLabel(pickup.status)"></span>
                                </span>
                            </div>
                            
                            <div class="row text-center mb-3">
                                <div class="col-3">
                                    <div class="small text-muted">Commandes</div>
                                    <div class="fw-bold text-primary" x-text="pickup.orders_count || 0"></div>
                                </div>
                                <div class="col-3">
                                    <div class="small text-muted">Poids</div>
                                    <div class="fw-bold" x-text="`${pickup.total_weight || 0}kg`"></div>
                                </div>
                                <div class="col-3">
                                    <div class="small text-muted">COD</div>
                                    <div class="fw-bold text-success" x-text="`${pickup.total_cod_amount || 0}`"></div>
                                </div>
                                <div class="col-3">
                                    <div class="small text-muted">Date</div>
                                    <div class="fw-bold" x-text="formatDate(pickup.pickup_date)"></div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center" @click.stop>
                                <small class="text-muted">
                                    Créé <span x-text="formatDateTime(pickup.created_at)"></span>
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" @click="viewPickup(pickup)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button x-show="pickup.can_be_validated" 
                                            class="btn btn-outline-success" 
                                            @click="validatePickup(pickup.id)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <div x-show="!loading && pickups.length > 0" class="card-footer bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <span x-text="`${pickups.length} enlèvement(s) affiché(s)`"></span>
                    <span x-show="hasFilters()" class="ms-2">
                        <button class="btn btn-sm btn-link text-decoration-none p-0" @click="clearFilters()">
                            <i class="fas fa-times me-1"></i>effacer filtres
                        </button>
                    </span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <small class="text-muted">
                        Dernière actualisation: <span x-text="lastUpdateTime || 'Jamais'"></span>
                    </small>
                    <button class="btn btn-sm btn-outline-primary" @click="loadPickups()" :disabled="loading">
                        <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i>
                        <span class="d-none d-sm-inline">Actualiser</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails Pickup -->
    <div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-warehouse me-2"></i>
                        Détails Enlèvement <span x-show="selectedPickup" x-text="`#${selectedPickup?.id}`"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedPickup">
                    <!-- Informations de base -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Informations
                                    </h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td x-text="`#${selectedPickup?.id}`"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Statut:</strong></td>
                                            <td>
                                                <span class="badge" :class="getStatusBadgeClass(selectedPickup?.status)">
                                                    <span x-text="getStatusLabel(selectedPickup?.status)"></span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td x-text="formatDate(selectedPickup?.pickup_date)"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Créé:</strong></td>
                                            <td x-text="formatDateTime(selectedPickup?.created_at)"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-truck me-1"></i>
                                        Transporteur
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <i :class="getCarrierIcon(selectedPickup?.carrier_slug)" class="text-primary fa-2x me-3"></i>
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
                            <div class="p-3 border rounded bg-light">
                                <div class="h4 text-primary mb-0" x-text="selectedPickup?.orders_count || 0"></div>
                                <small class="text-muted">Commandes</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="h4 text-success mb-0" x-text="`${selectedPickup?.total_weight || 0} kg`"></div>
                                <small class="text-muted">Poids Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="h4 text-info mb-0" x-text="selectedPickup?.total_pieces || 0"></div>
                                <small class="text-muted">Nb Pièces</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="h4 text-warning mb-0" x-text="`${selectedPickup?.total_cod_amount || 0} TND`"></div>
                                <small class="text-muted">COD Total</small>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 mb-4 p-3 bg-light rounded">
                        <button x-show="selectedPickup && selectedPickup.can_be_validated" 
                                @click="validatePickup(selectedPickup.id)"
                                class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Valider
                        </button>
                        <button x-show="selectedPickup && selectedPickup.status === 'validated'" 
                                @click="markAsPickedUp(selectedPickup.id)"
                                class="btn btn-info">
                            <i class="fas fa-truck me-1"></i>Marquer récupéré
                        </button>
                        <button x-show="selectedPickup && selectedPickup.can_be_deleted" 
                                @click="deletePickup(selectedPickup.id)"
                                class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-1"></i>
                                Commandes Incluses
                                <span class="badge bg-primary ms-2" x-text="selectedPickup?.orders?.length || 0"></span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Commande</th>
                                            <th>Client</th>
                                            <th>Téléphone</th>
                                            <th>Ville</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="order in selectedPickup?.orders || []" :key="order.id">
                                            <tr>
                                                <td><strong class="text-primary" x-text="`#${order.id}`"></strong></td>
                                                <td x-text="order.customer_name"></td>
                                                <td x-text="order.customer_phone"></td>
                                                <td>
                                                    <div x-text="order.customer_city"></div>
                                                    <small class="text-muted" x-text="order.region_name"></small>
                                                </td>
                                                <td><strong class="text-success" x-text="`${order.total_price} TND`"></strong></td>
                                            </tr>
                                        </template>
                                        
                                        <tr x-show="!selectedPickup?.orders?.length">
                                            <td colspan="5" class="text-center py-3 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                Aucune commande dans cet enlèvement
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.pickup-row {
    transition: all 0.2s ease;
}

.pickup-row:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateX(2px);
}

.pickup-card {
    transition: all 0.2s ease;
}

.pickup-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.empty-state {
    padding: 2rem;
}

.text-xs {
    font-size: 0.75rem;
}

.font-weight-bold {
    font-weight: 700;
}

.text-gray-800 {
    color: #5a5c69;
}

.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.modal-header {
    border-bottom: none;
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.badge {
    font-size: 0.7rem;
    padding: 0.35em 0.6em;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .btn-group .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pickupsManager', () => ({
        // État principal
        loading: false,
        error: null,
        pickups: [],
        selectedPickup: null,
        lastUpdateTime: null,
        
        // Filtres
        filters: {
            search: '',
            status: '',
            carrier: ''
        },
        
        // Sélections
        selectedPickups: [],
        
        // Stats
        stats: {
            draft: 0,
            validated: 0,
            picked_up: 0,
            problems: 0
        },

        // Initialisation
        async init() {
            console.log('🚀 Initialisation du gestionnaire de pickups');
            this.loadPickups();
        },

        // Chargement des pickups - MÉTHODE PRINCIPALE GARDÉE
        async loadPickups() {
            console.log('📡 Chargement des pickups...');
            
            this.loading = true;
            this.error = null;
            
            try {
                // Construire les paramètres de requête
                const params = {};
                if (this.filters.search?.trim()) {
                    params.search = this.filters.search.trim();
                }
                if (this.filters.status) {
                    params.status = this.filters.status;
                }
                if (this.filters.carrier) {
                    params.carrier = this.filters.carrier;
                }
                
                // Appel API
                const response = await axios.get('/admin/delivery/pickups/list', { 
                    params,
                    timeout: 15000 
                });
                
                console.log('📥 Réponse reçue:', response.data);
                
                if (response.data && response.data.success) {
                    this.pickups = response.data.pickups || [];
                    this.updateStats();
                    this.error = null;
                    this.lastUpdateTime = new Date().toLocaleTimeString('fr-FR');
                    
                    console.log(`✅ ${this.pickups.length} pickups chargés avec succès`);
                } else {
                    throw new Error(response.data?.error || 'Réponse API invalide');
                }
                
            } catch (error) {
                console.error('❌ Erreur chargement pickups:', error);
                
                let errorMessage = 'Erreur lors du chargement des pickups';
                
                if (error.code === 'ECONNABORTED') {
                    errorMessage = 'Timeout - L\'API met trop de temps à répondre';
                } else if (error.response) {
                    const status = error.response.status;
                    const data = error.response.data;
                    
                    switch (status) {
                        case 401:
                            errorMessage = 'Session expirée - Veuillez vous reconnecter';
                            break;
                        case 403:
                            errorMessage = 'Accès refusé';
                            break;
                        case 404:
                            errorMessage = 'Route API non trouvée';
                            break;
                        case 500:
                            errorMessage = `Erreur serveur: ${data?.message || data?.error || 'Erreur interne'}`;
                            break;
                        default:
                            errorMessage = `Erreur HTTP ${status}: ${data?.message || error.message}`;
                    }
                } else if (error.request) {
                    errorMessage = 'Impossible de contacter le serveur';
                } else {
                    errorMessage = error.message || 'Erreur inconnue';
                }
                
                this.error = errorMessage;
                this.pickups = [];
                this.updateStats();
                
            } finally {
                this.loading = false;
            }
        },

        // Gestion des filtres
        applyFilters() {
            console.log('🔍 Application des filtres:', this.filters);
            this.loadPickups();
        },
        
        hasFilters() {
            return !!(this.filters.search || this.filters.status || this.filters.carrier);
        },
        
        clearFilters() {
            console.log('🧹 Effacement des filtres');
            this.filters = {
                search: '',
                status: '',
                carrier: ''
            };
            this.loadPickups();
        },

        // Mise à jour des stats
        updateStats() {
            const stats = {
                draft: 0,
                validated: 0,
                picked_up: 0,
                problems: 0
            };
            
            this.pickups.forEach(pickup => {
                switch (pickup.status) {
                    case 'draft':
                        stats.draft++;
                        break;
                    case 'validated':
                        stats.validated++;
                        break;
                    case 'picked_up':
                        stats.picked_up++;
                        break;
                    case 'problem':
                        stats.problems++;
                        break;
                }
            });
            
            this.stats = stats;
            console.log('📊 Stats mises à jour:', stats);
        },

        // Gestion des sélections
        isSelected(pickupId) {
            return this.selectedPickups.includes(pickupId);
        },
        
        toggleSelection(pickupId) {
            const index = this.selectedPickups.indexOf(pickupId);
            if (index > -1) {
                this.selectedPickups.splice(index, 1);
            } else {
                this.selectedPickups.push(pickupId);
            }
        },
        
        isAllSelected() {
            return this.pickups.length > 0 && 
                   this.pickups.every(pickup => this.isSelected(pickup.id));
        },
        
        toggleAllSelection() {
            if (this.isAllSelected()) {
                this.selectedPickups = [];
            } else {
                this.selectedPickups = this.pickups.map(p => p.id);
            }
        },

        // Actions sur les pickups
        viewPickup(pickup) {
            console.log('👁️ Visualisation pickup #' + pickup.id);
            this.selectedPickup = pickup;
            const modal = new bootstrap.Modal(document.getElementById('pickupDetailsModal'));
            modal.show();
        },
        
        async validatePickup(pickupId) {
            if (!confirm('Valider cet enlèvement ? Il sera envoyé au transporteur et ne pourra plus être modifié.')) {
                return;
            }

            console.log('✅ Validation pickup #' + pickupId);

            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                
                if (response.data.success) {
                    console.log('✅ Pickup validé avec succès');
                    alert('✅ Enlèvement validé avec succès !');
                    this.loadPickups();
                    
                    // Mettre à jour le pickup sélectionné si c'est le même
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'validated';
                        this.selectedPickup.can_be_validated = false;
                        this.selectedPickup.can_be_edited = false;
                    }
                } else {
                    console.error('❌ Erreur validation:', response.data.error);
                    alert(`❌ Erreur: ${response.data.error}`);
                }
            } catch (error) {
                console.error('❌ Erreur validation:', error);
                alert('❌ Erreur lors de la validation: ' + error.message);
            }
        },
        
        async markAsPickedUp(pickupId) {
            console.log('🚛 Marquage récupération pickup #' + pickupId);
            
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/mark-picked-up`);
                
                if (response.data.success) {
                    console.log('✅ Pickup marqué récupéré');
                    alert('✅ Enlèvement marqué comme récupéré !');
                    this.loadPickups();
                    
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'picked_up';
                    }
                } else {
                    console.error('❌ Erreur marquage:', response.data.error);
                    alert(`❌ Erreur: ${response.data.error}`);
                }
            } catch (error) {
                console.error('❌ Erreur marquage:', error);
                alert('❌ Erreur lors du marquage: ' + error.message);
            }
        },
        
        async deletePickup(pickupId) {
            if (!confirm('Supprimer définitivement cet enlèvement ? Cette action est irréversible.')) {
                return;
            }

            console.log('🗑️ Suppression pickup #' + pickupId);

            try {
                await axios.delete(`/admin/delivery/pickups/${pickupId}`);
                
                console.log('✅ Pickup supprimé');
                alert('✅ Enlèvement supprimé avec succès !');
                this.loadPickups();
                
                // Fermer le modal si c'est le pickup sélectionné
                if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pickupDetailsModal'));
                    if (modal) modal.hide();
                }
            } catch (error) {
                console.error('❌ Erreur suppression:', error);
                alert('❌ Erreur lors de la suppression: ' + error.message);
            }
        },
        
        async validateSelected() {
            if (this.selectedPickups.length === 0) return;

            if (!confirm(`Valider ${this.selectedPickups.length} enlèvement(s) sélectionné(s) ?`)) return;

            console.log('✅ Validation en masse:', this.selectedPickups.length, 'pickups');

            try {
                const response = await axios.post('/admin/delivery/pickups/bulk-validate', {
                    pickup_ids: this.selectedPickups
                });
                
                if (response.data.success) {
                    console.log('✅ Validation groupée réussie');
                    alert(`✅ ${response.data.data?.validated || this.selectedPickups.length} enlèvement(s) validé(s) !`);
                    this.selectedPickups = [];
                    this.loadPickups();
                } else {
                    console.error('❌ Erreur validation groupée:', response.data.error);
                    alert(`❌ Erreur: ${response.data.error}`);
                }
            } catch (error) {
                console.error('❌ Erreur validation groupée:', error);
                alert('❌ Erreur lors de la validation groupée: ' + error.message);
            }
        },

        // Utilitaires d'affichage
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleDateString('fr-FR');
            } catch {
                return 'Date invalide';
            }
        },

        formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleString('fr-FR');
            } catch {
                return 'Date invalide';
            }
        },

        getRelativeDate(dateString) {
            if (!dateString) return '';
            
            try {
                const date = new Date(dateString);
                const now = new Date();
                const diffInDays = Math.floor((date - now) / (1000 * 60 * 60 * 24));
                
                if (diffInDays === 0) return 'Aujourd\'hui';
                if (diffInDays === 1) return 'Demain';
                if (diffInDays === -1) return 'Hier';
                if (diffInDays > 1) return `Dans ${diffInDays} jours`;
                if (diffInDays < -1) return `Il y a ${Math.abs(diffInDays)} jours`;
                
                return '';
            } catch {
                return '';
            }
        },

        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || carrierSlug || 'Transporteur inconnu';
        },

        getCarrierIcon(carrierSlug) {
            const icons = {
                'jax_delivery': 'fas fa-truck',
                'mes_colis': 'fas fa-shipping-fast'
            };
            return icons[carrierSlug] || 'fas fa-truck';
        },

        getStatusLabel(status) {
            const labels = {
                'draft': 'Brouillon',
                'validated': 'Validé',
                'picked_up': 'Récupéré',
                'problem': 'Problème'
            };
            return labels[status] || 'Statut inconnu';
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

        getStatusBadgeClass(status) {
            const classes = {
                'draft': 'bg-secondary',
                'validated': 'bg-success',
                'picked_up': 'bg-primary',
                'problem': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        }
    }));
});
</script>
</document_content>