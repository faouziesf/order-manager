@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid" x-data="pickupsManager">
    <!-- Header Amélioré avec Debug -->
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
            <button class="btn btn-outline-info btn-sm" @click="toggleDebugMode()">
                <i class="fas fa-bug me-1"></i>
                <span x-text="debugMode ? 'Masquer Debug' : 'Debug'"></span>
            </button>
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

    <!-- Zone de Debug Extensif (améliorée) -->
    <div x-show="debugMode" class="alert alert-info mb-4 border-0 shadow-sm">
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">🔧 État du Système</h6>
                <div class="small">
                    <div class="mb-1">
                        <strong>Chargement:</strong> 
                        <span x-text="loading ? '⏳ En cours...' : '✅ Terminé'" 
                              :class="loading ? 'text-warning' : 'text-success'"></span>
                    </div>
                    <div class="mb-1">
                        <strong>Pickups chargés:</strong> 
                        <span x-text="pickups.length" class="badge bg-primary"></span>
                        <span x-show="originalData.length !== pickups.length" class="text-muted">
                            (filtré depuis <span x-text="originalData.length"></span>)
                        </span>
                    </div>
                    <div class="mb-1">
                        <strong>Dernière tentative:</strong> 
                        <span x-text="lastAttempt" class="text-muted"></span>
                    </div>
                    <div class="mb-1">
                        <strong>Tentatives échouées:</strong> 
                        <span x-text="failedAttempts" 
                              :class="failedAttempts > 0 ? 'text-danger' : 'text-success'"></span>
                    </div>
                    <div class="mb-1">
                        <strong>Mode fallback:</strong> 
                        <span x-text="useFallback ? '🔴 Activé' : '🟢 Désactivé'" 
                              :class="useFallback ? 'text-danger' : 'text-success'"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">🌐 Informations Réseau</h6>
                <div class="small">
                    <div class="mb-1">
                        <strong>URL API:</strong> 
                        <code class="text-primary" x-text="apiUrl"></code>
                    </div>
                    <div class="mb-1">
                        <strong>Temps de réponse:</strong> 
                        <span x-text="responseTime ? responseTime + ' ms' : 'N/A'" 
                              :class="responseTime > 2000 ? 'text-danger' : 'text-success'"></span>
                    </div>
                    <div class="mb-1">
                        <strong>Statut connexion:</strong> 
                        <span x-text="connectionStatus" 
                              :class="connectionStatus === 'Connecté' ? 'text-success' : 'text-danger'"></span>
                    </div>
                    <div class="mb-1">
                        <strong>Erreur actuelle:</strong>
                        <span x-show="error" x-text="error" class="text-danger"></span>
                        <span x-show="!error" class="text-success">Aucune</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Console de Debug -->
        <div class="mt-3">
            <h6 class="fw-bold mb-2">📋 Console de Debug</h6>
            <div class="bg-dark text-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                <template x-for="log in debugLogs.slice(-10)" :key="log.timestamp">
                    <div class="mb-1">
                        <span class="text-muted" x-text="log.timestamp"></span>
                        <span :class="log.level === 'error' ? 'text-danger' : log.level === 'warn' ? 'text-warning' : 'text-info'">
                            [<span x-text="log.level.toUpperCase()"></span>]
                        </span>
                        <span x-text="log.message"></span>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- Actions de Debug -->
        <div class="mt-3 d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-outline-info" @click="testApiConnection()">
                <i class="fas fa-flask me-1"></i>Test API
            </button>
            <button class="btn btn-sm btn-outline-warning" @click="toggleFallbackMode()">
                <i class="fas fa-shield-alt me-1"></i>Basculer Fallback
            </button>
            <button class="btn btn-sm btn-outline-success" @click="clearDebugLogs()">
                <i class="fas fa-broom me-1"></i>Vider Console
            </button>
            <button class="btn btn-sm btn-outline-secondary" @click="downloadDebugReport()">
                <i class="fas fa-download me-1"></i>Rapport Debug
            </button>
        </div>
    </div>

    <!-- Filtres Améliorés -->
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
                               placeholder="Rechercher un enlèvement..."
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
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-primary flex-fill" @click="loadPickups(true)" :disabled="loading">
                            <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i>
                        </button>
                        <button class="btn btn-outline-secondary" @click="clearFilters()" :disabled="loading">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Améliorées -->
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

    <!-- Zone de Status Global -->
    <div class="alert border-0 shadow-sm mb-4" 
         :class="getGlobalStatusClass()" 
         x-show="!loading">
        <div class="d-flex align-items-center">
            <i :class="getGlobalStatusIcon()" class="me-2 fa-lg"></i>
            <div class="flex-grow-1">
                <strong x-text="getGlobalStatusMessage()"></strong>
                <div class="small text-muted mt-1" x-text="getGlobalStatusDetails()"></div>
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
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" data-bs-toggle="dropdown"
                                :disabled="loading">
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
        
        <div class="card-body p-0">
            <!-- État de Chargement Amélioré -->
            <div x-show="loading" class="text-center py-5">
                <div class="mb-3">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
                <h5 class="text-muted mb-2">Chargement des enlèvements...</h5>
                <div class="progress mx-auto mb-3" style="max-width: 300px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 100%" 
                         x-bind:style="'width: ' + loadingProgress + '%'"></div>
                </div>
                <p class="text-muted small" x-text="loadingMessage">Récupération des données...</p>
            </div>

            <!-- Message d'erreur amélioré -->
            <div x-show="error && !loading && !useFallback" class="alert alert-danger m-4 border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3 fa-2x text-danger"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">
                            <strong>Erreur de chargement</strong>
                            <span class="badge bg-danger ms-2">
                                Tentative <span x-text="failedAttempts"></span>/3
                            </span>
                        </h5>
                        <p class="mb-2" x-text="error"></p>
                        <div class="small text-muted mb-3">
                            <div>Dernière tentative: <span x-text="lastAttempt"></span></div>
                            <div>Temps de réponse: <span x-text="responseTime ? responseTime + ' ms' : 'N/A'"></span></div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-danger" @click="retryLoad()">
                                <i class="fas fa-redo me-1"></i>Réessayer
                            </button>
                            <button class="btn btn-sm btn-warning" @click="toggleFallbackMode()">
                                <i class="fas fa-shield-alt me-1"></i>Mode Sécurisé
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" @click="downloadDebugReport()">
                                <i class="fas fa-bug me-1"></i>Rapport d'Erreur
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mode Fallback Activé -->
            <div x-show="useFallback && !loading" class="alert alert-warning m-4 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt me-3 fa-lg text-warning"></i>
                    <div class="flex-grow-1">
                        <strong>Mode Sécurisé Activé</strong>
                        <p class="mb-2 small">Affichage des données de démonstration car l'API principale n'est pas disponible.</p>
                        <button class="btn btn-sm btn-outline-warning" @click="toggleFallbackMode()">
                            <i class="fas fa-sync me-1"></i>Réessayer l'API Normale
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
                        <span x-show="hasFilters()">Aucun enlèvement ne correspond aux filtres appliqués</span>
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

            <!-- Table Desktop Améliorée -->
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
                                <tr class="pickup-row" 
                                    @click="viewPickup(pickup)"
                                    :class="pickup.error ? 'table-warning' : ''"
                                    style="cursor: pointer;">
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
                                                <div x-show="pickup.error" class="small text-warning mt-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Données partielles
                                                </div>
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
                                            
                                            <button x-show="pickup.status === 'draft' && !pickup.error" 
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
                                            
                                            <button x-show="pickup.status === 'draft' && !pickup.error" 
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

            <!-- Cards Mobile Améliorées -->
            <div x-show="!loading && pickups.length > 0" class="d-lg-none p-3">
                <template x-for="pickup in pickups" :key="pickup.id">
                    <div class="card mb-3 pickup-card border-0 shadow-sm" 
                         @click="viewPickup(pickup)"
                         :class="pickup.error ? 'border-warning' : ''"
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
                                        <div x-show="pickup.error" class="small text-warning mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Données partielles
                                        </div>
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
                                    <button x-show="pickup.status === 'draft' && !pickup.error" 
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

        <!-- Footer avec pagination améliorée -->
        <div x-show="!loading && pickups.length > 0" class="card-footer bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <span x-text="`${pickups.length} enlèvement(s) affiché(s)`"></span>
                    <span x-show="originalData.length !== pickups.length" class="ms-2">
                        <span class="text-primary">
                            (filtré depuis <span x-text="originalData.length"></span>)
                        </span>
                    </span>
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
                    <button class="btn btn-sm btn-outline-primary" @click="loadPickups(true)" :disabled="loading">
                        <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i>
                        <span class="d-none d-sm-inline">Actualiser</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails Pickup Amélioré -->
    <div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-warehouse me-2"></i>
                        Détails Enlèvement <span x-show="selectedPickup" x-text="`#${selectedPickup?.id}`"></span>
                        <span x-show="selectedPickup?.error" class="badge bg-warning ms-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>Données partielles
                        </span>
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
                        <button x-show="selectedPickup && selectedPickup.status === 'draft' && !selectedPickup.error" 
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
                        <button x-show="selectedPickup && selectedPickup.status === 'draft' && !selectedPickup.error" 
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
/* Styles existants améliorés */
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

/* Animations pour les états de chargement */
.progress-bar-striped.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% {
        background-position: 1rem 0;
    }
    100% {
        background-position: 0 0;
    }
}

/* Style pour les données avec erreur */
.table-warning td {
    border-color: #ffeaa7;
    background-color: rgba(255, 193, 7, 0.1);
}

.border-warning {
    border-color: #ffc107 !important;
}

/* Amélioration des modales */
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

/* Style du debug */
.alert-info {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 1px solid #2196f3;
    color: #0d47a1;
}

/* Responsive amélioré */
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

/* Loading states améliorés */
.spinner-border {
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

/* Amélioration des badges */
.badge {
    font-size: 0.7rem;
    padding: 0.35em 0.6em;
}

/* Style pour les boutons disabled */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pickupsManager', () => ({
        // ========================================
        // ÉTAT PRINCIPAL AMÉLIORÉ
        // ========================================
        
        // État de base
        loading: false,
        error: null,
        pickups: [],
        originalData: [], // Pour conserver les données non filtrées
        selectedPickup: null,
        
        // Debug avancé
        debugMode: false,
        debugLogs: [],
        apiUrl: '/admin/delivery/pickups/list',
        lastAttempt: 'Jamais',
        failedAttempts: 0,
        useFallback: false,
        responseTime: null,
        connectionStatus: 'Non testé',
        loadingProgress: 0,
        loadingMessage: 'Récupération des données...',
        lastUpdateTime: null,
        
        // Filtres améliorés
        filters: {
            search: '',
            status: '',
            carrier: ''
        },
        
        // Sélections
        selectedPickups: [],
        
        // Stats dynamiques
        stats: {
            draft: 0,
            validated: 0,
            picked_up: 0,
            problems: 0
        },

        // ========================================
        // INITIALISATION AVEC DIAGNOSTIC COMPLET
        // ========================================
        
        async init() {
            this.addDebugLog('info', '🚀 Initialisation du gestionnaire de pickups');
            this.addDebugLog('info', `📍 URL API: ${this.apiUrl}`);
            this.addDebugLog('info', `📊 Version Alpine.js: ${Alpine.version || 'Inconnue'}`);
            this.addDebugLog('info', `🌐 Axios disponible: ${typeof axios !== 'undefined'}`);
            
            // Diagnostic initial
            await this.performInitialDiagnostic();
            
            // Chargement initial
            this.loadPickups();
            
            // Auto-refresh amélioré avec gestion intelligente
            this.setupAutoRefresh();
            
            // Écouter les événements de connexion
            this.setupConnectionListeners();
            
            this.addDebugLog('success', '✅ Initialisation terminée avec succès');
        },

        // ========================================
        // MÉTHODES DE DIAGNOSTIC ÉTENDUES
        // ========================================
        
        async performInitialDiagnostic() {
            this.addDebugLog('info', '🔍 Début du diagnostic initial');
            
            try {
                // Test 1: Connexion réseau
                this.addDebugLog('info', '📡 Test de connectivité réseau');
                if (navigator.onLine) {
                    this.connectionStatus = 'Connecté';
                    this.addDebugLog('success', '✅ Connexion réseau OK');
                } else {
                    this.connectionStatus = 'Hors ligne';
                    this.addDebugLog('warn', '⚠️ Aucune connexion réseau détectée');
                }
                
                // Test 2: Authentification
                this.addDebugLog('info', '🔐 Vérification de l\'authentification');
                const authResponse = await axios.get('/admin/debug-auth');
                if (authResponse.data.is_authenticated) {
                    this.addDebugLog('success', `✅ Authentifié comme: ${authResponse.data.admin_name} (ID: ${authResponse.data.admin_id})`);
                } else {
                    this.addDebugLog('error', '❌ Problème d\'authentification détecté');
                }
                
                // Test 3: Disponibilité de l'API
                this.addDebugLog('info', '🧪 Test de l\'API pickups');
                const testResponse = await axios.get(this.apiUrl + '?test=1', {
                    timeout: 5000
                });
                
                if (testResponse.data.success) {
                    this.addDebugLog('success', `✅ API fonctionnelle - ${testResponse.data.test_results.count} pickups trouvés`);
                    this.responseTime = testResponse.data.test_results.response_time_ms;
                } else {
                    this.addDebugLog('error', '❌ API non fonctionnelle');
                }
                
            } catch (error) {
                this.addDebugLog('error', `❌ Erreur pendant le diagnostic: ${error.message}`);
                
                if (error.code === 'ECONNABORTED') {
                    this.addDebugLog('warn', '⏰ Timeout durant le diagnostic - l\'API est peut-être lente');
                } else if (error.response?.status === 401) {
                    this.addDebugLog('error', '🔒 Problème d\'authentification détecté');
                } else if (error.response?.status === 404) {
                    this.addDebugLog('error', '🔍 Route API non trouvée');
                } else if (!navigator.onLine) {
                    this.addDebugLog('error', '🌐 Pas de connexion internet');
                }
            }
        },

        // ========================================
        // CHARGEMENT DES PICKUPS - LOGIQUE COMPLÈTEMENT REFACTORISÉE
        // ========================================
        
        async loadPickups(showLoading = true) {
            const startTime = performance.now();
            
            this.addDebugLog('info', '📡 === DÉBUT DU CHARGEMENT DES PICKUPS ===');
            this.addDebugLog('info', `📊 Paramètres: showLoading=${showLoading}, useFallback=${this.useFallback}`);
            this.addDebugLog('info', `🔍 Filtres actifs: ${JSON.stringify(this.filters)}`);
            
            if (showLoading) {
                this.loading = true;
                this.error = null;
                this.loadingProgress = 0;
                this.loadingMessage = 'Initialisation...';
            }
            
            this.lastAttempt = new Date().toLocaleTimeString('fr-FR');
            
            // Si mode fallback activé, utiliser les données de test
            if (this.useFallback) {
                this.addDebugLog('warn', '🔄 Mode fallback activé - utilisation des données de démonstration');
                return this.loadFallbackData();
            }
            
            try {
                // Étape 1: Préparation de la requête
                this.updateLoadingProgress(10, 'Préparation de la requête...');
                
                const params = this.buildApiParams();
                this.addDebugLog('info', `📝 Paramètres de requête: ${JSON.stringify(params)}`);
                
                // Étape 2: Envoi de la requête
                this.updateLoadingProgress(25, 'Envoi de la requête...');
                
                const config = {
                    timeout: 15000,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                };
                
                this.addDebugLog('info', '🌐 Envoi de la requête vers l\'API...');
                const response = await axios.get(this.apiUrl, { params, ...config });
                
                // Étape 3: Traitement de la réponse
                this.updateLoadingProgress(80, 'Traitement de la réponse...');
                
                const endTime = performance.now();
                this.responseTime = Math.round(endTime - startTime);
                
                this.addDebugLog('success', `✅ Réponse reçue en ${this.responseTime}ms`);
                this.addDebugLog('info', `📊 Statut: ${response.status}, Taille: ${JSON.stringify(response.data).length} chars`);
                
                if (response.data && response.data.success) {
                    // Étape 4: Validation et stockage des données
                    this.updateLoadingProgress(90, 'Validation des données...');
                    
                    const pickupsData = this.validateAndProcessPickupsData(response.data.pickups || []);
                    
                    this.originalData = [...pickupsData];
                    this.pickups = this.applyClientSideFilters(pickupsData);
                    this.updateStats();
                    
                    this.error = null;
                    this.failedAttempts = 0;
                    this.connectionStatus = 'Connecté';
                    this.lastUpdateTime = new Date().toLocaleTimeString('fr-FR');
                    
                    // Log détaillé des données reçues
                    this.addDebugLog('success', `📦 ${pickupsData.length} pickups chargés avec succès`);
                    this.addDebugLog('info', `📊 Stats: ${this.stats.draft} brouillons, ${this.stats.validated} validés, ${this.stats.picked_up} récupérés`);
                    
                    if (response.data.debug_info) {
                        this.addDebugLog('info', `🔧 Debug API: ${JSON.stringify(response.data.debug_info)}`);
                    }
                    
                    // Vérifier la qualité des données
                    this.checkDataQuality(pickupsData);
                    
                } else {
                    throw new Error(response.data?.error || response.data?.message || 'Réponse API invalide');
                }
                
            } catch (error) {
                this.handleLoadingError(error, startTime);
            } finally {
                this.updateLoadingProgress(100, 'Terminé');
                
                if (showLoading) {
                    // Petit délai pour que l'utilisateur voit la completion
                    setTimeout(() => {
                        this.loading = false;
                        this.loadingProgress = 0;
                    }, 200);
                }
                
                this.addDebugLog('info', '🏁 === FIN DU CHARGEMENT DES PICKUPS ===');
            }
        },

        // ========================================
        // MÉTHODES UTILITAIRES POUR LE CHARGEMENT
        // ========================================
        
        buildApiParams() {
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
            
            params.per_page = 50; // Limite raisonnable
            
            return params;
        },
        
        validateAndProcessPickupsData(rawPickups) {
            if (!Array.isArray(rawPickups)) {
                this.addDebugLog('warn', '⚠️ Données pickups non valides - ce n\'est pas un tableau');
                return [];
            }
            
            const validPickups = [];
            let errorsCount = 0;
            
            rawPickups.forEach((pickup, index) => {
                try {
                    // Validation des champs essentiels
                    const processedPickup = {
                        id: pickup.id || `temp_${index}`,
                        status: pickup.status || 'unknown',
                        carrier_slug: pickup.carrier_slug || 'unknown',
                        configuration_name: pickup.configuration_name || 'Configuration inconnue',
                        pickup_date: pickup.pickup_date || null,
                        created_at: pickup.created_at || new Date().toISOString(),
                        orders_count: Math.max(0, pickup.orders_count || 0),
                        total_weight: Math.max(0, pickup.total_weight || 0),
                        total_pieces: Math.max(0, pickup.total_pieces || 0),
                        total_cod_amount: Math.max(0, pickup.total_cod_amount || 0),
                        orders: Array.isArray(pickup.orders) ? pickup.orders : [],
                        can_be_validated: pickup.can_be_validated || false,
                        can_be_edited: pickup.can_be_edited || false,
                        can_be_deleted: pickup.can_be_deleted || false,
                        error: pickup.error || null, // Marquer les erreurs de l'API
                    };
                    
                    validPickups.push(processedPickup);
                    
                } catch (validationError) {
                    errorsCount++;
                    this.addDebugLog('warn', `⚠️ Erreur validation pickup index ${index}: ${validationError.message}`);
                }
            });
            
            if (errorsCount > 0) {
                this.addDebugLog('warn', `⚠️ ${errorsCount} pickup(s) ont eu des erreurs de validation`);
            }
            
            return validPickups;
        },
        
        checkDataQuality(pickups) {
            const qualityIssues = [];
            
            // Vérifier les pickups avec des erreurs
            const pickupsWithErrors = pickups.filter(p => p.error);
            if (pickupsWithErrors.length > 0) {
                qualityIssues.push(`${pickupsWithErrors.length} pickup(s) ont des données partielles`);
            }
            
            // Vérifier les pickups sans commandes
            const emptyPickups = pickups.filter(p => p.orders_count === 0);
            if (emptyPickups.length > 0) {
                qualityIssues.push(`${emptyPickups.length} pickup(s) sans commandes`);
            }
            
            // Vérifier les configurations manquantes
            const missingConfigs = pickups.filter(p => p.configuration_name === 'Configuration inconnue');
            if (missingConfigs.length > 0) {
                qualityIssues.push(`${missingConfigs.length} pickup(s) avec configuration inconnue`);
            }
            
            if (qualityIssues.length > 0) {
                this.addDebugLog('warn', `⚠️ Problèmes de qualité détectés: ${qualityIssues.join(', ')}`);
            } else {
                this.addDebugLog('success', '✅ Qualité des données: Excellente');
            }
        },
        
        handleLoadingError(error, startTime) {
            this.failedAttempts++;
            const errorTime = Math.round(performance.now() - startTime);
            this.responseTime = errorTime;
            this.connectionStatus = 'Erreur';
            
            let errorMessage = 'Erreur inconnue';
            let errorLevel = 'error';
            
            if (error.code === 'ECONNABORTED') {
                errorMessage = `Timeout après ${errorTime}ms - L'API met trop de temps à répondre`;
                errorLevel = 'warn';
            } else if (error.response) {
                const status = error.response.status;
                const data = error.response.data;
                
                switch (status) {
                    case 401:
                        errorMessage = 'Session expirée - Veuillez vous reconnecter';
                        break;
                    case 403:
                        errorMessage = 'Accès refusé - Permissions insuffisantes';
                        break;
                    case 404:
                        errorMessage = `Route API non trouvée: ${this.apiUrl}`;
                        break;
                    case 422:
                        errorMessage = `Données invalides: ${data?.message || 'Erreur de validation'}`;
                        break;
                    case 500:
                        errorMessage = `Erreur serveur: ${data?.message || data?.error || 'Erreur interne'}`;
                        break;
                    default:
                        errorMessage = `Erreur HTTP ${status}: ${data?.message || error.message}`;
                }
            } else if (error.request) {
                errorMessage = 'Impossible de contacter le serveur - Vérifiez votre connexion';
            } else {
                errorMessage = error.message || 'Erreur lors de la configuration de la requête';
            }
            
            this.error = errorMessage;
            
            this.addDebugLog(errorLevel, `❌ Erreur chargement (tentative ${this.failedAttempts}): ${errorMessage}`);
            this.addDebugLog('info', `⏱️ Temps écoulé: ${errorTime}ms`);
            
            // Après 3 tentatives échouées, proposer automatiquement le mode fallback
            if (this.failedAttempts >= 3 && !this.useFallback) {
                this.addDebugLog('warn', '🔄 3 tentatives échouées - Activation automatique du mode fallback');
                this.useFallback = true;
                this.loadFallbackData();
            }
        },
        
        updateLoadingProgress(progress, message) {
            this.loadingProgress = Math.min(progress, 100);
            this.loadingMessage = message;
        },

        // ========================================
        // DONNÉES DE FALLBACK AMÉLIORÉES
        // ========================================
        
        loadFallbackData() {
            this.addDebugLog('info', '🔄 Chargement des données de démonstration');
            
            setTimeout(() => {
                const fallbackPickups = this.getFallbackData();
                this.originalData = [...fallbackPickups];
                this.pickups = this.applyClientSideFilters(fallbackPickups);
                this.updateStats();
                
                this.loading = false;
                this.connectionStatus = 'Mode Démonstration';
                this.lastUpdateTime = new Date().toLocaleTimeString('fr-FR');
                
                this.addDebugLog('success', `✅ ${fallbackPickups.length} pickups de démonstration chargés`);
            }, 1000); // Simuler un délai de chargement
        },
        
        getFallbackData() {
            return [
                {
                    id: 1,
                    status: 'draft',
                    carrier_slug: 'jax_delivery',
                    configuration_name: 'Configuration Test JAX',
                    pickup_date: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().split('T')[0],
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
                    pickup_date: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString().split('T')[0],
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
                    pickup_date: new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString().split('T')[0],
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

        // ========================================
        // GESTION DES FILTRES AMÉLIORÉE
        // ========================================
        
        applyFilters() {
            this.addDebugLog('info', `🔍 Application des filtres: ${JSON.stringify(this.filters)}`);
            
            // Réappliquer les filtres côté client si on a déjà des données
            if (this.originalData.length > 0) {
                this.pickups = this.applyClientSideFilters(this.originalData);
                this.updateStats();
                this.addDebugLog('info', `📊 Filtres appliqués: ${this.pickups.length}/${this.originalData.length} pickups affichés`);
            } else {
                // Recharger depuis l'API avec les nouveaux filtres
                this.loadPickups();
            }
        },
        
        applyClientSideFilters(data) {
            let filtered = [...data];
            
            // Filtre par recherche
            if (this.filters.search?.trim()) {
                const search = this.filters.search.trim().toLowerCase();
                filtered = filtered.filter(pickup => 
                    pickup.id.toString().includes(search) ||
                    pickup.configuration_name.toLowerCase().includes(search) ||
                    pickup.carrier_slug.toLowerCase().includes(search)
                );
            }
            
            // Filtre par statut
            if (this.filters.status) {
                filtered = filtered.filter(pickup => pickup.status === this.filters.status);
            }
            
            // Filtre par transporteur
            if (this.filters.carrier) {
                filtered = filtered.filter(pickup => pickup.carrier_slug === this.filters.carrier);
            }
            
            return filtered;
        },
        
        hasFilters() {
            return !!(this.filters.search || this.filters.status || this.filters.carrier);
        },
        
        clearFilters() {
            this.addDebugLog('info', '🧹 Effacement de tous les filtres');
            this.filters = {
                search: '',
                status: '',
                carrier: ''
            };
            this.applyFilters();
        },

        // ========================================
        // GESTION DES STATISTIQUES
        // ========================================
        
        updateStats() {
            // Calculer les stats à partir des données filtrées actuelles
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
            
            this.addDebugLog('info', `📊 Stats mises à jour: ${JSON.stringify(stats)}`);
        },

        // ========================================
        // MÉTHODES D'AUTO-REFRESH ET CONNEXION
        // ========================================
        
        setupAutoRefresh() {
            // Auto-refresh intelligent - plus fréquent si des pickups en brouillon
            setInterval(() => {
                if (!this.loading && !this.useFallback) {
                    const hasDrafts = this.stats.draft > 0;
                    const shouldRefresh = hasDrafts || Math.random() < 0.1; // 10% de chance si pas de brouillons
                    
                    if (shouldRefresh) {
                        this.addDebugLog('info', '🔄 Auto-refresh déclenché');
                        this.loadPickups(false); // Refresh silencieux
                    }
                }
            }, 120000); // Toutes les 2 minutes
        },
        
        setupConnectionListeners() {
            // Écouter les changements de connexion
            window.addEventListener('online', () => {
                this.addDebugLog('success', '🌐 Connexion rétablie');
                this.connectionStatus = 'Connecté';
                if (this.useFallback) {
                    this.addDebugLog('info', '🔄 Tentative de sortie du mode fallback');
                    this.useFallback = false;
                    this.loadPickups();
                }
            });
            
            window.addEventListener('offline', () => {
                this.addDebugLog('warn', '🌐 Connexion perdue');
                this.connectionStatus = 'Hors ligne';
            });
        },

        // ========================================
        // MÉTHODES DE DEBUG AMÉLIORÉES
        // ========================================
        
        addDebugLog(level, message) {
            const timestamp = new Date().toLocaleTimeString('fr-FR', { 
                hour12: false, 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                fractionalSecondDigits: 3 
            });
            
            this.debugLogs.push({
                timestamp,
                level,
                message
            });
            
            // Garder seulement les 50 derniers logs
            if (this.debugLogs.length > 50) {
                this.debugLogs = this.debugLogs.slice(-50);
            }
            
            // Log aussi dans la console du navigateur
            const consoleMethod = level === 'error' ? 'error' : level === 'warn' ? 'warn' : 'log';
            console[consoleMethod](`[PICKUPS ${level.toUpperCase()}] ${message}`);
        },
        
        toggleDebugMode() {
            this.debugMode = !this.debugMode;
            this.addDebugLog('info', `🔧 Mode debug ${this.debugMode ? 'activé' : 'désactivé'}`);
        },
        
        clearDebugLogs() {
            this.debugLogs = [];
            this.addDebugLog('info', '🧹 Console de debug vidée');
        },
        
        downloadDebugReport() {
            const report = {
                timestamp: new Date().toISOString(),
                system_info: {
                    user_agent: navigator.userAgent,
                    online: navigator.onLine,
                    language: navigator.language,
                    url: window.location.href
                },
                app_state: {
                    loading: this.loading,
                    error: this.error,
                    failed_attempts: this.failedAttempts,
                    use_fallback: this.useFallback,
                    connection_status: this.connectionStatus,
                    response_time: this.responseTime,
                    pickups_count: this.pickups.length,
                    original_data_count: this.originalData.length,
                    stats: this.stats,
                    filters: this.filters
                },
                debug_logs: this.debugLogs.slice(-20), // 20 derniers logs
                api_url: this.apiUrl
            };
            
            const blob = new Blob([JSON.stringify(report, null, 2)], { 
                type: 'application/json' 
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `pickups_debug_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            this.addDebugLog('info', '📥 Rapport de debug téléchargé');
        },

        // ========================================
        // MÉTHODES D'ÉTAT GLOBAL
        // ========================================
        
        getGlobalStatusClass() {
            if (this.error) return 'alert-danger';
            if (this.useFallback) return 'alert-warning';
            if (this.stats.problems > 0) return 'alert-warning';
            if (this.stats.draft > 0) return 'alert-info';
            return 'alert-success';
        },
        
        getGlobalStatusIcon() {
            if (this.error) return 'fas fa-exclamation-circle';
            if (this.useFallback) return 'fas fa-shield-alt';
            if (this.stats.problems > 0) return 'fas fa-exclamation-triangle';
            if (this.stats.draft > 0) return 'fas fa-clock';
            return 'fas fa-check-circle';
        },
        
        getGlobalStatusMessage() {
            if (this.error) return 'Problème de connexion détecté';
            if (this.useFallback) return 'Mode démonstration activé';
            if (this.stats.problems > 0) return `${this.stats.problems} pickup(s) avec des problèmes`;
            if (this.stats.draft > 0) return `${this.stats.draft} pickup(s) en attente de validation`;
            return 'Tous les enlèvements sont à jour';
        },
        
        getGlobalStatusDetails() {
            if (this.error) return 'Vérifiez votre connexion ou contactez l\'administrateur';
            if (this.useFallback) return 'Données de démonstration uniquement';
            if (this.stats.problems > 0) return 'Vérifiez les pickups marqués avec des problèmes';
            if (this.stats.draft > 0) return 'Certains pickups peuvent être validés';
            return `Dernière mise à jour: ${this.lastUpdateTime || 'Jamais'}`;
        },

        // ========================================
        // MÉTHODES D'ACTIONS PRINCIPALES
        // ========================================
        
        refreshData() {
            this.addDebugLog('info', '🔄 Actualisation manuelle demandée');
            this.failedAttempts = 0;
            this.error = null;
            this.loadPickups(true);
        },
        
        retryLoad() {
            this.addDebugLog('info', '🔄 Nouvelle tentative après erreur');
            this.error = null;
            this.loadPickups(true);
        },
        
        toggleFallbackMode() {
            this.useFallback = !this.useFallback;
            this.addDebugLog('info', `🔄 Mode fallback ${this.useFallback ? 'activé' : 'désactivé'}`);
            this.failedAttempts = 0;
            this.loadPickups(true);
        },
        
        async testApiConnection() {
            this.addDebugLog('info', '🧪 Test de connexion API demandé');
            
            try {
                // Test auth d'abord
                const authResponse = await axios.get('/admin/debug-auth', { timeout: 5000 });
                this.addDebugLog('success', `✅ Auth test: ${authResponse.data.admin_name}`);
                
                // Test API pickups
                const testResponse = await axios.get(this.apiUrl + '?test=1', { timeout: 10000 });
                
                if (testResponse.data.success) {
                    this.addDebugLog('success', `✅ API test réussi: ${testResponse.data.test_results.count} pickups, ${testResponse.data.test_results.response_time_ms}ms`);
                    this.connectionStatus = 'Connecté';
                    alert('✅ Test API réussi ! Voir la console de debug pour les détails.');
                } else {
                    this.addDebugLog('error', `❌ API test échoué: ${testResponse.data.message}`);
                    alert('❌ Test API échoué. Voir la console de debug pour les détails.');
                }
                
            } catch (error) {
                this.addDebugLog('error', `❌ Erreur test API: ${error.message}`);
                alert(`❌ Erreur test API: ${error.message}\nVoir la console de debug pour plus de détails.`);
            }
        },

        // ========================================
        // GESTION DES SÉLECTIONS
        // ========================================
        
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
            this.addDebugLog('info', `✅ Sélection mise à jour: ${this.selectedPickups.length} pickup(s)`);
        },
        
        isAllSelected() {
            return this.pickups.length > 0 && 
                   this.pickups.every(pickup => this.isSelected(pickup.id));
        },
        
        toggleAllSelection() {
            if (this.isAllSelected()) {
                this.selectedPickups = [];
                this.addDebugLog('info', '❌ Toutes les sélections supprimées');
            } else {
                this.selectedPickups = this.pickups.map(p => p.id);
                this.addDebugLog('info', `✅ Tous les pickups sélectionnés: ${this.selectedPickups.length}`);
            }
        },

        // ========================================
        // ACTIONS SUR LES PICKUPS
        // ========================================
        
        viewPickup(pickup) {
            this.addDebugLog('info', `👁️ Visualisation pickup #${pickup.id}`);
            this.selectedPickup = pickup;
            const modal = new bootstrap.Modal(document.getElementById('pickupDetailsModal'));
            modal.show();
        },
        
        async validatePickup(pickupId) {
            if (!confirm('Valider cet enlèvement ? Il sera envoyé au transporteur et ne pourra plus être modifié.')) {
                return;
            }

            this.addDebugLog('info', `✅ Validation pickup #${pickupId}`);

            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                
                if (response.data.success) {
                    this.addDebugLog('success', `✅ Pickup #${pickupId} validé avec succès`);
                    alert('✅ Enlèvement validé avec succès');
                    this.loadPickups(false);
                    
                    // Mettre à jour le pickup sélectionné si c'est le même
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'validated';
                        this.selectedPickup.can_be_validated = false;
                        this.selectedPickup.can_be_edited = false;
                    }
                } else {
                    this.addDebugLog('error', `❌ Erreur validation pickup #${pickupId}: ${response.data.error}`);
                    alert(`❌ Erreur: ${response.data.error}`);
                }
            } catch (error) {
                this.addDebugLog('error', `❌ Erreur validation pickup #${pickupId}: ${error.message}`);
                alert('❌ Erreur lors de la validation: ' + error.message);
            }
        },
        
        async markAsPickedUp(pickupId) {
            this.addDebugLog('info', `🚛 Marquage récupération pickup #${pickupId}`);
            
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/mark-picked-up`);
                
                if (response.data.success) {
                    this.addDebugLog('success', `✅ Pickup #${pickupId} marqué récupéré`);
                    alert('✅ Enlèvement marqué comme récupéré');
                    this.loadPickups(false);
                    
                    if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                        this.selectedPickup.status = 'picked_up';
                    }
                } else {
                    this.addDebugLog('error', `❌ Erreur marquage pickup #${pickupId}: ${response.data.error}`);
                    alert(`❌ Erreur: ${response.data.error}`);
                }
            } catch (error) {
                this.addDebugLog('error', `❌ Erreur marquage pickup #${pickupId}: ${error.message}`);
                alert('❌ Erreur lors du marquage: ' + error.message);
            }
        },
        
        async deletePickup(pickupId) {
            if (!confirm('Supprimer définitivement cet enlèvement ? Cette action est irréversible.')) {
                return;
            }

            this.addDebugLog('info', `🗑️ Suppression pickup #${pickupId}`);

            try {
                await axios.delete(`/admin/delivery/pickups/${pickupId}`);
                
                this.addDebugLog('success', `✅ Pickup #${pickupId} supprimé`);
                alert('✅ Enlèvement supprimé avec succès');
                this.loadPickups(false);
                
                // Fermer le modal si c'est le pickup sélectionné
                if (this.selectedPickup && this.selectedPickup.id === pickupId) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pickupDetailsModal'));
                    if (modal) modal.hide();
                }
            } catch (error) {
                this.addDebugLog('error', `❌ Erreur suppression pickup #${pickupId}: ${error.message}`);
                alert('❌ Erreur lors de la suppression: ' + error.message);
            }
        },
        
        async validateSelected() {
            if (this.selectedPickups.length === 0) return;

            if (!confirm(`Valider ${this.selectedPickups.length} enlèvement(s) sélectionné(s) ?`)) return;

            this.addDebugLog('info', `✅ Validation en masse: ${this.selectedPickups.length} pickups`);

            try {
                const response = await axios.post('/admin/delivery/pickups/bulk-validate', {
                    pickup_ids: this.selectedPickups
                });
                
                if (response.data.success) {
                    this.addDebugLog('success', `✅ ${response.data.data.validated} pickup(s) validé(s)`);
                    alert(`✅ ${response.data.data.validated} enlèvement(s) validé(s)`);
                    this.selectedPickups = [];
                    this.loadPickups(false);
                } else {
                    this.addDebugLog('error', `❌ Erreur validation groupée: ${response.data.error}`);
                    alert(`❌ Erreur: ${response.data.error}`);
                }
            } catch (error) {
                this.addDebugLog('error', `❌ Erreur validation groupée: ${error.message}`);
                alert('❌ Erreur lors de la validation groupée: ' + error.message);
            }
        },

        // ========================================
        // UTILITAIRES D'AFFICHAGE
        // ========================================
        
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
        },

        // ========================================
        // MÉTHODES D'EXPORT ET IMPRESSION
        // ========================================
        
        exportPickups() {
            this.addDebugLog('info', '📤 Export des pickups');
            window.open('/admin/delivery/pickups/export', '_blank');
        },
        
        printManifest(pickup) {
            this.addDebugLog('info', `🖨️ Impression manifeste pickup #${pickup.id}`);
            
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
                        <p>Document généré le ${date} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                        <p>Signature transporteur: _________________________</p>
                        <p style="margin-top: 20px; font-size: 10px;">
                            Mode: ${this.useFallback ? 'Démonstration' : 'Production'} | 
                            Version: ${this.connectionStatus}
                        </p>
                    </div>
                </body>
                </html>
            `;
        }
    }));
});
</script>