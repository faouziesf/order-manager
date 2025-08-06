@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid" x-data="pickupsManager">
    <!-- Header -->
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
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card shadow-sm mb-4">
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
                        <option value="">Tous transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" @click="refreshData()" :disabled="loading">
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

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm stat-card">
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
            <div class="card border-0 shadow-sm stat-card">
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
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Récupérés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.picked_up">0</div>
                        </div>
                        <div class="ms-3">
                            <div class="icon-circle bg-primary bg-opacity-10">
                                <i class="fas fa-truck fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm stat-card">
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

    <!-- Liste des enlèvements -->
    <div class="card shadow-sm">
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
                <div class="empty-state">
                    <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted mb-1">Aucun enlèvement trouvé</h6>
                    <p class="text-muted small mb-4">Créez votre premier enlèvement</p>
                    <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Créer un Enlèvement
                    </a>
                </div>
            </div>

            <!-- Table responsive pour desktop, cards pour mobile -->
            <div x-show="!loading && filteredPickups.length > 0">
                <!-- Desktop Table -->
                <div class="d-none d-lg-block">
                    <div class="table-responsive">
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
                                                    <br><small class="text-muted" x-text="formatDateTime(pickup.created_at)"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle bg-primary bg-opacity-10 me-2">
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
                                                        @click="viewPickup(pickup)"
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
                                                
                                                <button @click="printManifest(pickup)"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        title="Imprimer manifeste">
                                                    <i class="fas fa-print"></i>
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

                <!-- Mobile Cards -->
                <div class="d-lg-none p-3">
                    <template x-for="pickup in filteredPickups" :key="pickup.id">
                        <div class="card pickup-card mb-3" @click="viewPickup(pickup)">
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
                                            <h6 class="mb-0" x-text="`Enlèvement #${pickup.id}`"></h6>
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
                                        <div class="fw-bold text-primary" x-text="pickup.orders_count"></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="small text-muted">Poids</div>
                                        <div class="fw-bold" x-text="`${pickup.total_weight}kg`"></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="small text-muted">COD</div>
                                        <div class="fw-bold text-success" x-text="`${pickup.total_cod_amount}`"></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="small text-muted">Date</div>
                                        <div class="fw-bold" x-text="formatDate(pickup.pickup_date)"></div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center" @click.stop>
                                    <small class="text-muted">
                                        Créé le <span x-text="formatDateTime(pickup.created_at)"></span>
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" @click="viewPickup(pickup)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button x-show="pickup.status === 'draft'" 
                                                class="btn btn-outline-success" 
                                                @click="validatePickup(pickup.id)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button @click="printManifest(pickup)" class="btn btn-outline-secondary">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
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
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
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
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Informations
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
                            <div class="card bg-light border-0">
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

                    <!-- Actions rapides -->
                    <div class="d-flex flex-wrap gap-2 mb-4 p-3 bg-light rounded">
                        <button x-show="selectedPickup && selectedPickup.status === 'draft'" 
                                @click="validatePickup(selectedPickup.id)"
                                class="btn btn-success btn-sm">
                            <i class="fas fa-check me-1"></i>Valider
                        </button>
                        <button x-show="selectedPickup && selectedPickup.status === 'validated'" 
                                @click="markAsPickedUp(selectedPickup.id)"
                                class="btn btn-info btn-sm">
                            <i class="fas fa-truck me-1"></i>Marquer récupéré
                        </button>
                        <button @click="printManifest(selectedPickup)"
                                class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-print me-1"></i>Manifeste
                        </button>
                        <button x-show="selectedPickup && selectedPickup.status === 'draft'" 
                                @click="showAddOrdersModal = true"
                                class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-plus me-1"></i>Ajouter commandes
                        </button>
                        <button x-show="selectedPickup && selectedPickup.status === 'draft'" 
                                @click="deletePickup(selectedPickup.id)"
                                class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="card border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-1"></i>
                                Commandes Incluses
                                <span class="badge bg-primary ms-2" x-text="selectedPickup?.orders?.length || 0"></span>
                            </h6>
                            <input type="text" 
                                   x-model="orderSearchQuery"
                                   placeholder="Rechercher..."
                                   class="form-control form-control-sm" 
                                   style="width: 200px;">
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Commande</th>
                                            <th>Client</th>
                                            <th>Téléphone</th>
                                            <th>Ville</th>
                                            <th>Montant</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="order in filteredOrders" :key="order.id">
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
                                                    <button x-show="selectedPickup && selectedPickup.status === 'draft'" 
                                                            @click="removeOrderFromPickup(order.id)"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Retirer du pickup">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <tr x-show="filteredOrders.length === 0">
                                            <td colspan="6" class="text-center py-3 text-muted">
                                                Aucune commande trouvée
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
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Commandes -->
    <div class="modal fade" id="addOrdersModal" tabindex="-1" aria-hidden="true" x-show="showAddOrdersModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter des commandes</h5>
                    <button type="button" class="btn-close" @click="showAddOrdersModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" 
                               x-model="availableOrdersSearch"
                               @input.debounce.300ms="searchAvailableOrders()"
                               placeholder="Rechercher commandes disponibles..."
                               class="form-control">
                    </div>

                    <div class="order-list" style="max-height: 400px; overflow-y: auto;">
                        <template x-for="order in availableOrders" :key="order.id">
                            <div class="card mb-2 cursor-pointer hover-card" 
                                 @click="toggleOrderSelection(order)">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" 
                                                   :checked="selectedOrderIds.includes(order.id)"
                                                   class="form-check-input me-2">
                                            <div>
                                                <h6 class="mb-0" x-text="`#${order.id}`"></h6>
                                                <small class="text-muted">
                                                    <span x-text="order.customer_name"></span> - 
                                                    <span x-text="order.customer_phone"></span> - 
                                                    <span x-text="order.customer_city"></span>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold" x-text="`${order.total_price} TND`"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="me-auto">
                        <small class="text-muted">
                            <span x-text="selectedOrderIds.length"></span> commande(s) sélectionnée(s)
                        </small>
                    </div>
                    <button type="button" class="btn btn-secondary" @click="showAddOrdersModal = false">
                        Annuler
                    </button>
                    <button type="button" class="btn btn-primary" 
                            @click="addSelectedOrders()" 
                            :disabled="selectedOrderIds.length === 0">
                        Ajouter les commandes
                    </button>
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
    background: #6c757d;
    border: 2px solid rgba(108, 117, 125, 0.2);
}

.pickup-indicator.validated {
    background: #198754;
    box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.2);
}

.pickup-indicator.picked_up {
    background: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
}

.pickup-indicator.problem {
    background: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
}

.pickup-row {
    transition: all 0.2s ease;
    cursor: pointer;
}

.pickup-row:hover {
    background: rgba(13, 110, 253, 0.03);
}

.pickup-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.pickup-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.hover-card:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.text-xs {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}

.stat-card {
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.empty-state {
    padding: 2rem;
}

.cursor-pointer {
    cursor: pointer;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.6rem;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

.badge.bg-success {
    background-color: #198754 !important;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.order-list {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
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
        margin: 0.5rem;
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
        // États
        loading: false,
        pickups: [],
        filteredPickups: [],
        selectedPickup: null,
        availableOrders: [],
        
        // Filtres
        searchQuery: '',
        statusFilter: '',
        carrierFilter: '',
        orderSearchQuery: '',
        availableOrdersSearch: '',
        
        // Modals
        showDetailsModal: false,
        showAddOrdersModal: false,
        
        // Sélections
        selectedPickups: [],
        selectedOrderIds: [],
        
        // Pagination
        currentPage: 1,
        
        // Stats
        stats: {
            draft: 0,
            validated: 0,
            picked_up: 0,
            problems: 0
        },

        // Computed
        get filteredOrders() {
            if (!this.selectedPickup || !this.selectedPickup.orders) return [];
            
            if (!this.orderSearchQuery) return this.selectedPickup.orders;
            
            const query = this.orderSearchQuery.toLowerCase();
            return this.selectedPickup.orders.filter(order => 
                order.id.toString().includes(query) ||
                order.customer_name.toLowerCase().includes(query) ||
                order.customer_phone.includes(query)
            );
        },

        // Initialisation
        init() {
            this.loadPickups();
            
            // Auto-refresh toutes les 30 secondes
            setInterval(() => {
                this.loadPickups(false);
            }, 30000);
        },

        // Chargement des données
        async loadPickups(showLoading = true) {
            if (showLoading) this.loading = true;
            
            try {
                const response = await axios.get('/admin/delivery/pickups/list');
                
                if (response.data.success) {
                    this.pickups = response.data.pickups || [];
                    this.filterPickups();
                    this.updateStats();
                }
            } catch (error) {
                console.error('Erreur chargement pickups:', error);
                this.showError('Impossible de charger les enlèvements');
                // Données de test si erreur
                this.pickups = this.generateTestData();
                this.filterPickups();
                this.updateStats();
            } finally {
                if (showLoading) this.loading = false;
            }
        },

        generateTestData() {
            return [
                {
                    id: 1,
                    status: 'draft',
                    carrier_slug: 'jax_delivery',
                    configuration_name: 'Boutique Principale',
                    pickup_date: '2024-02-15',
                    created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
                    orders_count: 8,
                    total_weight: 12.5,
                    total_pieces: 15,
                    total_cod_amount: 456.750,
                    orders: [
                        { 
                            id: 1001, 
                            customer_name: 'Ahmed Ben Ali', 
                            customer_phone: '20123456', 
                            customer_city: 'Tunis', 
                            region_name: 'Tunis', 
                            total_price: 89.900, 
                            status: 'confirmée' 
                        },
                        { 
                            id: 1002, 
                            customer_name: 'Fatma Trabelsi', 
                            customer_phone: '25987654', 
                            customer_city: 'Ariana', 
                            region_name: 'Ariana', 
                            total_price: 156.450, 
                            status: 'confirmée' 
                        }
                    ]
                },
                {
                    id: 2,
                    status: 'validated',
                    carrier_slug: 'mes_colis',
                    configuration_name: 'Entrepôt Nord',
                    pickup_date: '2024-02-14',
                    created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
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
                    created_at: new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString(),
                    orders_count: 5,
                    total_weight: 7.8,
                    total_pieces: 9,
                    total_cod_amount: 234.500,
                    orders: []
                }
            ];
        },

        async searchAvailableOrders() {
            try {
                const response = await axios.get('/admin/delivery/api/available-orders', {
                    params: { search: this.availableOrdersSearch }
                });
                
                if (response.data.success) {
                    this.availableOrders = response.data.orders || [];
                } else {
                    // Données de test
                    this.availableOrders = [
                        { id: 2001, customer_name: 'Ali Mansouri', customer_phone: '21234567', customer_city: 'Sfax', total_price: 125.0 },
                        { id: 2002, customer_name: 'Leila Bouaziz', customer_phone: '23456789', customer_city: 'Sousse', total_price: 89.5 },
                    ];
                }
            } catch (error) {
                console.error('Erreur recherche commandes:', error);
                this.availableOrders = [];
            }
        },

        // Filtrage
        filterPickups() {
            let filtered = [...this.pickups];

            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(pickup => 
                    pickup.id.toString().includes(query) ||
                    pickup.configuration_name.toLowerCase().includes(query)
                );
            }

            if (this.statusFilter) {
                filtered = filtered.filter(pickup => pickup.status === this.statusFilter);
            }

            if (this.carrierFilter) {
                filtered = filtered.filter(pickup => pickup.carrier_slug === this.carrierFilter);
            }

            this.filteredPickups = filtered;
        },

        updateStats() {
            this.stats = {
                draft: this.pickups.filter(p => p.status === 'draft').length,
                validated: this.pickups.filter(p => p.status === 'validated').length,
                picked_up: this.pickups.filter(p => p.status === 'picked_up').length,
                problems: this.pickups.filter(p => p.status === 'problem').length
            };
        },

        refreshData() {
            this.loadPickups();
        },

        // Actions sur les pickups
        viewPickup(pickup) {
            this.selectedPickup = pickup;
            const modal = new bootstrap.Modal(document.getElementById('pickupDetailsModal'));
            modal.show();
        },

        async validatePickup(pickupId) {
            if (!confirm('Valider cet enlèvement ? Il sera envoyé au transporteur et ne pourra plus être modifié.')) {
                return;
            }

            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                
                if (response.data.success) {
                    this.showSuccess('Enlèvement validé avec succès');
                    this.loadPickups(false);
                    
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'validated';
                    }
                }
            } catch (error) {
                console.error('Erreur validation:', error);
                this.showError('Impossible de valider l\'enlèvement');
            }
        },

        async markAsPickedUp(pickupId) {
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/mark-picked-up`);
                
                if (response.data.success) {
                    this.showSuccess('Enlèvement marqué comme récupéré');
                    this.loadPickups(false);
                    
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'picked_up';
                    }
                }
            } catch (error) {
                console.error('Erreur marquage:', error);
                this.showError('Impossible de marquer comme récupéré');
            }
        },

        async deletePickup(pickupId) {
            if (!confirm('Supprimer définitivement cet enlèvement ?')) {
                return;
            }

            try {
                await axios.delete(`/admin/delivery/pickups/${pickupId}`);
                
                this.showSuccess('Enlèvement supprimé');
                this.loadPickups(false);
                
                // Fermer le modal si c'est le pickup sélectionné
                if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pickupDetailsModal'));
                    if (modal) modal.hide();
                }
            } catch (error) {
                console.error('Erreur suppression:', error);
                this.showError('Impossible de supprimer l\'enlèvement');
            }
        },

        async removeOrderFromPickup(orderId) {
            if (!confirm('Retirer cette commande de l\'enlèvement ?')) {
                return;
            }

            try {
                await axios.delete(`/admin/delivery/pickups/${this.selectedPickup.id}/orders/${orderId}`);
                
                // Mettre à jour localement
                this.selectedPickup.orders = this.selectedPickup.orders.filter(o => o.id !== orderId);
                this.selectedPickup.orders_count--;
                
                this.showSuccess('Commande retirée de l\'enlèvement');
                this.loadPickups(false);
            } catch (error) {
                console.error('Erreur suppression commande:', error);
                this.showError('Impossible de retirer la commande');
            }
        },

        async addSelectedOrders() {
            if (this.selectedOrderIds.length === 0) return;

            try {
                await axios.post(`/admin/delivery/pickups/${this.selectedPickup.id}/add-orders`, {
                    order_ids: this.selectedOrderIds
                });
                
                this.showSuccess(`${this.selectedOrderIds.length} commande(s) ajoutée(s)`);
                this.selectedOrderIds = [];
                this.showAddOrdersModal = false;
                
                // Recharger les détails
                this.loadPickups(false);
            } catch (error) {
                console.error('Erreur ajout commandes:', error);
                this.showError('Impossible d\'ajouter les commandes');
            }
        },

        // Sélection
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

        toggleOrderSelection(order) {
            const index = this.selectedOrderIds.indexOf(order.id);
            if (index > -1) {
                this.selectedOrderIds.splice(index, 1);
            } else {
                this.selectedOrderIds.push(order.id);
            }
        },

        // Actions groupées
        async validateSelected() {
            if (this.selectedPickups.length === 0) return;

            if (!confirm(`Valider ${this.selectedPickups.length} enlèvement(s) ?`)) return;

            try {
                await axios.post('/admin/delivery/pickups/bulk-validate', {
                    pickup_ids: this.selectedPickups
                });
                
                this.showSuccess('Enlèvements validés');
                this.selectedPickups = [];
                this.loadPickups(false);
            } catch (error) {
                console.error('Erreur validation groupée:', error);
                this.showError('Impossible de valider les enlèvements');
            }
        },

        // Impression
        printManifest(pickup) {
            const manifestWindow = window.open('', '_blank');
            const manifestHtml = this.generateManifestHtml(pickup);
            
            manifestWindow.document.write(manifestHtml);
            manifestWindow.document.close();
            manifestWindow.print();
        },

        generateManifestHtml(pickup) {
            const orders = pickup.orders || [];
            const date = new Date().toLocaleDateString('fr-FR');
            
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Manifeste - Enlèvement #${pickup.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0d6efd; padding-bottom: 20px; }
                        .info { display: flex; justify-content: space-between; margin-bottom: 30px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #0d6efd; color: white; }
                        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>MANIFESTE D'ENLÈVEMENT</h1>
                        <p>Enlèvement #${pickup.id} - ${this.getCarrierName(pickup.carrier_slug)}</p>
                    </div>
                    
                    <div class="info">
                        <div>
                            <strong>Date:</strong> ${this.formatDate(pickup.pickup_date)}<br>
                            <strong>Configuration:</strong> ${pickup.configuration_name}
                        </div>
                        <div>
                            <strong>Commandes:</strong> ${pickup.orders_count}<br>
                            <strong>Poids total:</strong> ${pickup.total_weight} kg
                        </div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Client</th>
                                <th>Téléphone</th>
                                <th>Ville</th>
                                <th>Montant (TND)</th>
                                <th>Signature</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${orders.map(order => `
                                <tr>
                                    <td>#${order.id}</td>
                                    <td>${order.customer_name}</td>
                                    <td>${order.customer_phone}</td>
                                    <td>${order.customer_city}</td>
                                    <td>${order.total_price}</td>
                                    <td style="width: 100px; height: 30px;"></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    
                    <div class="footer">
                        <p>Document généré le ${date}</p>
                        <p>Signature transporteur: _________________________</p>
                    </div>
                </body>
                </html>
            `;
        },

        // Export
        exportPickups() {
            window.open('/admin/delivery/pickups/export', '_blank');
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

        // Utilitaires
        getStatusIndicatorClass(status) {
            return status || 'draft';
        },

        getStatusBadgeClass(status) {
            const classes = {
                'draft': 'bg-secondary',
                'validated': 'bg-success',
                'picked_up': 'bg-primary',
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
            return icons[status] || 'fas fa-warehouse';
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
            return names[carrierSlug] || carrierSlug;
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

        // Notifications
        showSuccess(message) {
            // Utiliser les toasts Bootstrap ou une autre librairie
            alert('Succès: ' + message);
        },

        showError(message) {
            alert('Erreur: ' + message);
        }
    }));
});
</script>
@endpush