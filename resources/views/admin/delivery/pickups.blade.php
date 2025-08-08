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
            <button class="btn btn-outline-secondary" @click="refreshData()" :disabled="loading">
                <i class="fas fa-sync me-1" :class="{ 'fa-spin': loading }"></i>
                Actualiser
            </button>
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
        </div>
    </div>

    <!-- Zone de Debug (masquée en production) -->
    <div x-show="showDebug" class="alert alert-info mb-4">
        <h6>🔧 Informations de Debug:</h6>
        <div class="row">
            <div class="col-md-6">
                <small>
                    <strong>État de chargement:</strong> <span x-text="loading ? 'En cours...' : 'Terminé'"></span><br>
                    <strong>Nombre de pickups:</strong> <span x-text="pickups.length"></span><br>
                    <strong>Erreur:</strong> <span x-text="error || 'Aucune'"></span><br>
                    <strong>URL API:</strong> <span x-text="apiUrl"></span><br>
                </small>
            </div>
            <div class="col-md-6">
                <small>
                    <strong>Dernière tentative:</strong> <span x-text="lastAttempt"></span><br>
                    <strong>Tentatives échouées:</strong> <span x-text="failedAttempts"></span><br>
                    <strong>Mode fallback:</strong> <span x-text="useFallback ? 'Activé' : 'Désactivé'"></span><br>
                </small>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-info mt-2" @click="showDebug = false">Masquer Debug</button>
        <button class="btn btn-sm btn-outline-warning mt-2" @click="testApiConnection()">Test API</button>
        <button class="btn btn-sm btn-outline-danger mt-2" @click="toggleFallbackMode()">Basculer Fallback</button>
    </div>

    <!-- Filtres Simples -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               placeholder="Rechercher un enlèvement..."
                               x-model="filters.search"
                               @input.debounce.500ms="loadPickups()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.status" @change="loadPickups()">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillons</option>
                        <option value="validated">Validés</option>
                        <option value="picked_up">Récupérés</option>
                        <option value="problem">Problèmes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.carrier" @change="loadPickups()">
                        <option value="">Tous transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" @click="showDebug = !showDebug">
                        <i class="fas fa-bug me-1"></i>
                        Debug
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Simples -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Brouillons
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.draft">0</div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-edit fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Validés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.validated">0</div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Récupérés
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.picked_up">0</div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-truck fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Problèmes
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" x-text="stats.problems">0</div>
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
    <div class="card shadow">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-1"></i>
                    Enlèvements
                    <span x-show="pickups.length > 0" class="badge bg-primary ms-2" x-text="pickups.length"></span>
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success" 
                            x-show="selectedPickups.length > 0" 
                            @click="validateSelected()">
                        <i class="fas fa-check me-1"></i>
                        Valider (<span x-text="selectedPickups.length"></span>)
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" @click="exportPickups()">
                                <i class="fas fa-download me-2"></i>Exporter
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" @click="testApiConnection()">
                                <i class="fas fa-flask me-2"></i>Test API
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Chargement -->
            <div x-show="loading" class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted">Chargement des enlèvements...</p>
            </div>

            <!-- Message d'erreur avec options -->
            <div x-show="error && !loading" class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div class="flex-grow-1">
                        <strong>Erreur de chargement</strong>
                        <div class="small" x-text="error"></div>
                        <div class="small text-muted mt-1">
                            Tentatives: <span x-text="failedAttempts"></span>/3
                        </div>
                    </div>
                    <div class="btn-group ms-2">
                        <button class="btn btn-sm btn-outline-danger" @click="retryLoad()">
                            <i class="fas fa-redo me-1"></i>Réessayer
                        </button>
                        <button class="btn btn-sm btn-outline-warning" @click="useFallback = true; loadPickups()">
                            <i class="fas fa-shield-alt me-1"></i>Mode Sécurisé
                        </button>
                    </div>
                </div>
            </div>

            <!-- Aucun enlèvement -->
            <div x-show="!loading && !error && pickups.length === 0" class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted mb-2">Aucun enlèvement trouvé</h6>
                    <p class="text-muted small mb-4">
                        <span x-show="!hasFilters()">Créez votre premier enlèvement pour commencer</span>
                        <span x-show="hasFilters()">Aucun enlèvement ne correspond aux filtres</span>
                    </p>
                    <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary" x-show="!hasFilters()">
                        <i class="fas fa-plus me-1"></i>
                        Créer un Enlèvement
                    </a>
                    <button class="btn btn-outline-secondary" @click="clearFilters()" x-show="hasFilters()">
                        <i class="fas fa-times me-1"></i>
                        Effacer les filtres
                    </button>
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
                                <th>Commandes</th>
                                <th>Totaux</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="pickup in pickups" :key="pickup.id">
                                <tr class="pickup-row" @click="viewPickup(pickup)">
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
                                            <div class="pickup-indicator me-2" :class="'status-' + pickup.status"></div>
                                            <div>
                                                <strong class="text-primary" x-text="`#${pickup.id}`"></strong>
                                                <br><small class="text-muted" x-text="formatDateTime(pickup.created_at)"></small>
                                            </div>
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
                                    <td>
                                        <div class="text-center">
                                            <div class="h6 mb-0 text-primary" x-text="pickup.orders_count"></div>
                                            <small class="text-muted">commandes</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><strong x-text="`${pickup.total_weight} kg`"></strong></div>
                                            <small class="text-success" x-text="`${pickup.total_cod_amount} TND`"></small>
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

            <!-- Cards Mobile -->
            <div x-show="!loading && pickups.length > 0" class="d-lg-none">
                <template x-for="pickup in pickups" :key="pickup.id">
                    <div class="card mb-3 pickup-card" @click="viewPickup(pickup)">
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
                                    Créé <span x-text="formatDateTime(pickup.created_at)"></span>
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

        <!-- Footer avec pagination simple -->
        <div x-show="!loading && pickups.length > 0" class="card-footer bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <span x-text="`${pickups.length} enlèvement(s) affiché(s)`"></span>
                    <span x-show="hasFilters()" class="ms-2">
                        <button class="btn btn-sm btn-link text-decoration-none p-0" @click="clearFilters()">
                            (effacer filtres)
                        </button>
                    </span>
                </small>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" @click="loadPickups()" :disabled="loading">
                        <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i>
                        Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails Pickup Simplifié -->
    <div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
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
                            <div class="card bg-light">
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
                            <div class="card bg-light">
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
                        <button x-show="selectedPickup && selectedPickup.status === 'draft'" 
                                @click="validatePickup(selectedPickup.id)"
                                class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Valider
                        </button>
                        <button x-show="selectedPickup && selectedPickup.status === 'validated'" 
                                @click="markAsPickedUp(selectedPickup.id)"
                                class="btn btn-info">
                            <i class="fas fa-truck me-1"></i>Marquer récupéré
                        </button>
                        <button @click="printManifest(selectedPickup)" class="btn btn-outline-primary">
                            <i class="fas fa-print me-1"></i>Manifeste
                        </button>
                        <button x-show="selectedPickup && selectedPickup.status === 'draft'" 
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
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Styles simples et compatibles */
.pickup-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.pickup-indicator.status-draft { background-color: #6c757d; }
.pickup-indicator.status-validated { background-color: #198754; }
.pickup-indicator.status-picked_up { background-color: #0d6efd; }
.pickup-indicator.status-problem { background-color: #dc3545; }

.pickup-row {
    transition: background-color 0.2s ease;
    cursor: pointer;
}

.pickup-row:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.pickup-card {
    transition: transform 0.2s ease;
    cursor: pointer;
}

.pickup-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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

@media (max-width: 576px) {
    .btn-group .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
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
        
        // Debug
        showDebug: false,
        apiUrl: '/admin/delivery/pickups/list',
        lastAttempt: 'Jamais',
        failedAttempts: 0,
        useFallback: false,
        
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

        // Initialisation avec debug détaillé
        init() {
            console.log('🚀 [PICKUPS] Initialisation du gestionnaire de pickups');
            console.log('🚀 [PICKUPS] URL API:', this.apiUrl);
            console.log('🚀 [PICKUPS] Version Alpine.js:', Alpine.version || 'Inconnue');
            console.log('🚀 [PICKUPS] Axios disponible:', typeof axios !== 'undefined');
            
            this.loadPickups();
            
            // Auto-refresh toutes les 2 minutes
            setInterval(() => {
                if (!this.loading) {
                    console.log('🔄 [PICKUPS] Auto-refresh des données');
                    this.loadPickups(false);
                }
            }, 120000);
        },

        // Chargement des pickups avec debug exhaustif
        async loadPickups(showLoading = true) {
            console.log('📡 [PICKUPS] Début du chargement des pickups');
            console.log('📡 [PICKUPS] Paramètres:', {
                showLoading,
                filters: this.filters,
                useFallback: this.useFallback
            });
            
            if (showLoading) {
                this.loading = true;
                this.error = null;
            }
            
            this.lastAttempt = new Date().toLocaleTimeString();
            
            // Si mode fallback activé, utiliser les données de test
            if (this.useFallback) {
                console.log('⚠️ [PICKUPS] Mode fallback activé - utilisation des données de test');
                setTimeout(() => {
                    this.pickups = this.getFallbackData();
                    this.updateStats();
                    this.loading = false;
                    console.log('✅ [PICKUPS] Données fallback chargées:', this.pickups.length, 'pickups');
                }, 500);
                return;
            }
            
            try {
                console.log('🌐 [PICKUPS] Appel API vers:', this.apiUrl);
                
                const params = {};
                if (this.filters.search) params.search = this.filters.search;
                if (this.filters.status) params.status = this.filters.status;
                if (this.filters.carrier) params.carrier = this.filters.carrier;
                params.per_page = 50;
                
                console.log('🌐 [PICKUPS] Paramètres de requête:', params);
                
                const response = await axios.get(this.apiUrl, {
                    params: params,
                    timeout: 15000,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                console.log('✅ [PICKUPS] Réponse API reçue:', {
                    status: response.status,
                    success: response.data?.success,
                    pickups_count: response.data?.pickups?.length || 0
                });
                
                if (response.data && response.data.success) {
                    this.pickups = response.data.pickups || [];
                    this.updateStats();
                    this.error = null;
                    this.failedAttempts = 0;
                    
                    console.log('✅ [PICKUPS] Pickups chargés avec succès:', this.pickups.length);
                } else {
                    throw new Error(response.data?.error || 'Réponse API invalide');
                }
                
            } catch (error) {
                this.failedAttempts++;
                console.error('❌ [PICKUPS] Erreur chargement pickups:', {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data,
                    failedAttempts: this.failedAttempts
                });
                
                if (error.code === 'ECONNABORTED') {
                    this.error = 'Timeout: La requête a pris trop de temps (15s)';
                } else if (error.response) {
                    if (error.response.status === 404) {
                        this.error = `Route non trouvée (404): ${this.apiUrl}`;
                    } else if (error.response.status === 500) {
                        this.error = `Erreur serveur (500): ${error.response.data?.message || error.message}`;
                    } else {
                        this.error = `Erreur ${error.response.status}: ${error.response.data?.message || error.message}`;
                    }
                } else if (error.request) {
                    this.error = 'Erreur réseau: Impossible de contacter le serveur';
                } else {
                    this.error = error.message || 'Erreur inconnue';
                }
                
                // Après 3 tentatives échouées, passer automatiquement en mode fallback
                if (this.failedAttempts >= 3 && this.pickups.length === 0) {
                    console.log('⚠️ [PICKUPS] 3 tentatives échouées - passage automatique en mode fallback');
                    this.useFallback = true;
                    this.pickups = this.getFallbackData();
                    this.updateStats();
                    this.error = this.error + ' (Mode sécurisé activé)';
                }
                
            } finally {
                if (showLoading) {
                    this.loading = false;
                }
                console.log('🏁 [PICKUPS] Fin du chargement');
            }
        },

        // Test de connection API
        async testApiConnection() {
            console.log('🔧 [PICKUPS] Test de connexion API');
            
            try {
                const response = await axios.get('/admin/debug-auth');
                console.log('🔧 [PICKUPS] Test auth:', response.data);
                
                const testResponse = await axios.get(this.apiUrl + '?test=1');
                console.log('🔧 [PICKUPS] Test API:', testResponse.data);
                
                alert('Test API réussi ! Voir la console pour les détails.');
            } catch (error) {
                console.error('🔧 [PICKUPS] Erreur test API:', error);
                alert(`Erreur test API: ${error.message}\nVoir la console pour plus de détails.`);
            }
        },

        // Basculer le mode fallback
        toggleFallbackMode() {
            this.useFallback = !this.useFallback;
            console.log('🔧 [PICKUPS] Mode fallback:', this.useFallback ? 'ACTIVÉ' : 'DÉSACTIVÉ');
            this.failedAttempts = 0;
            this.loadPickups();
        },

        // Données de fallback pour les tests
        getFallbackData() {
            return [
                {
                    id: 1,
                    status: 'draft',
                    carrier_slug: 'jax_delivery',
                    configuration_name: 'Configuration Test JAX',
                    pickup_date: '2025-01-10',
                    created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
                    orders_count: 8,
                    total_weight: 12.5,
                    total_pieces: 15,
                    total_cod_amount: 456.750,
                    can_be_validated: true,
                    can_be_edited: true,
                    can_be_deleted: true,
                    orders: [
                        { 
                            id: 1001, 
                            customer_name: 'Ahmed Ben Ali', 
                            customer_phone: '20123456', 
                            customer_city: 'Tunis', 
                            region_name: 'Tunis', 
                            total_price: 89.900 
                        },
                        { 
                            id: 1002, 
                            customer_name: 'Fatma Trabelsi', 
                            customer_phone: '25987654', 
                            customer_city: 'Ariana', 
                            region_name: 'Ariana', 
                            total_price: 156.450 
                        }
                    ]
                },
                {
                    id: 2,
                    status: 'validated',
                    carrier_slug: 'mes_colis',
                    configuration_name: 'Configuration Test Mes Colis',
                    pickup_date: '2025-01-09',
                    created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
                    orders_count: 12,
                    total_weight: 18.3,
                    total_pieces: 24,
                    total_cod_amount: 789.200,
                    can_be_validated: false,
                    can_be_edited: false,
                    can_be_deleted: false,
                    orders: [
                        { 
                            id: 2001, 
                            customer_name: 'Mohamed Gharbi', 
                            customer_phone: '23456789', 
                            customer_city: 'Sfax', 
                            region_name: 'Sfax', 
                            total_price: 234.500 
                        }
                    ]
                },
                {
                    id: 3,
                    status: 'picked_up',
                    carrier_slug: 'jax_delivery',
                    configuration_name: 'Configuration Boutique Sud',
                    pickup_date: '2025-01-08',
                    created_at: new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString(),
                    orders_count: 5,
                    total_weight: 7.8,
                    total_pieces: 9,
                    total_cod_amount: 234.500,
                    can_be_validated: false,
                    can_be_edited: false,
                    can_be_deleted: false,
                    orders: []
                }
            ];
        },

        // Mise à jour des statistiques
        updateStats() {
            this.stats = {
                draft: this.pickups.filter(p => p.status === 'draft').length,
                validated: this.pickups.filter(p => p.status === 'validated').length,
                picked_up: this.pickups.filter(p => p.status === 'picked_up').length,
                problems: this.pickups.filter(p => p.status === 'problem').length
            };
            
            console.log('📊 [PICKUPS] Stats mises à jour:', this.stats);
        },

        // Actualisation
        refreshData() {
            console.log('🔄 [PICKUPS] Actualisation manuelle');
            this.failedAttempts = 0;
            this.loadPickups(true);
        },

        // Retry après erreur
        retryLoad() {
            console.log('🔄 [PICKUPS] Nouvelle tentative après erreur');
            this.error = null;
            this.loadPickups(true);
        },

        // Gestion des filtres
        hasFilters() {
            return this.filters.search || this.filters.status || this.filters.carrier;
        },

        clearFilters() {
            console.log('🧹 [PICKUPS] Nettoyage des filtres');
            this.filters = {
                search: '',
                status: '',
                carrier: ''
            };
            this.loadPickups();
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
            console.log('✅ [PICKUPS] Sélection:', this.selectedPickups);
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
            console.log('✅ [PICKUPS] Sélection tous:', this.selectedPickups);
        },

        // Visualiser un pickup
        viewPickup(pickup) {
            console.log('👁️ [PICKUPS] Visualisation pickup:', pickup.id);
            this.selectedPickup = pickup;
            const modal = new bootstrap.Modal(document.getElementById('pickupDetailsModal'));
            modal.show();
        },

        // Actions sur les pickups
        async validatePickup(pickupId) {
            if (!confirm('Valider cet enlèvement ? Il sera envoyé au transporteur et ne pourra plus être modifié.')) {
                return;
            }

            console.log('✅ [PICKUPS] Validation pickup:', pickupId);

            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                
                if (response.data.success) {
                    alert('Enlèvement validé avec succès');
                    this.loadPickups(false);
                    
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'validated';
                    }
                }
            } catch (error) {
                console.error('❌ [PICKUPS] Erreur validation:', error);
                alert('Erreur lors de la validation: ' + error.message);
            }
        },

        async markAsPickedUp(pickupId) {
            console.log('🚛 [PICKUPS] Marquage récupération:', pickupId);
            
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/mark-picked-up`);
                
                if (response.data.success) {
                    alert('Enlèvement marqué comme récupéré');
                    this.loadPickups(false);
                    
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'picked_up';
                    }
                }
            } catch (error) {
                console.error('❌ [PICKUPS] Erreur marquage:', error);
                alert('Erreur lors du marquage: ' + error.message);
            }
        },

        async deletePickup(pickupId) {
            if (!confirm('Supprimer définitivement cet enlèvement ?')) {
                return;
            }

            console.log('🗑️ [PICKUPS] Suppression pickup:', pickupId);

            try {
                await axios.delete(`/admin/delivery/pickups/${pickupId}`);
                
                alert('Enlèvement supprimé');
                this.loadPickups(false);
                
                // Fermer le modal si c'est le pickup sélectionné
                if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pickupDetailsModal'));
                    if (modal) modal.hide();
                }
            } catch (error) {
                console.error('❌ [PICKUPS] Erreur suppression:', error);
                alert('Erreur lors de la suppression: ' + error.message);
            }
        },

        // Validation en masse
        async validateSelected() {
            if (this.selectedPickups.length === 0) return;

            if (!confirm(`Valider ${this.selectedPickups.length} enlèvement(s) ?`)) return;

            console.log('✅ [PICKUPS] Validation en masse:', this.selectedPickups);

            try {
                await axios.post('/admin/delivery/pickups/bulk-validate', {
                    pickup_ids: this.selectedPickups
                });
                
                alert(`${this.selectedPickups.length} enlèvement(s) validé(s)`);
                this.selectedPickups = [];
                this.loadPickups(false);
            } catch (error) {
                console.error('❌ [PICKUPS] Erreur validation groupée:', error);
                alert('Erreur lors de la validation groupée: ' + error.message);
            }
        },

        // Impression
        printManifest(pickup) {
            console.log('🖨️ [PICKUPS] Impression manifeste:', pickup.id);
            
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
            console.log('📤 [PICKUPS] Export des pickups');
            window.open('/admin/delivery/pickups/export', '_blank');
        },

        // Utilitaires
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

        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || carrierSlug;
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
            return labels[status] || 'Inconnu';
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
@endpush