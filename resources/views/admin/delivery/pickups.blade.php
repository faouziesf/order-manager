@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">
                <i class="fas fa-warehouse me-2"></i>
                Gestion des Enlèvements
                <span class="badge bg-info ms-2" id="pickupsCount">0</span>
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
            <button class="btn btn-outline-primary" onclick="refreshData()">
                <i class="fas fa-sync" id="refreshIcon"></i>
                Actualiser
            </button>
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nouvel Enlèvement
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="ID, transporteur...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="draft">Brouillons</option>
                        <option value="validated">Validés</option>
                        <option value="picked_up">Récupérés</option>
                        <option value="problem">Problèmes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transporteur</label>
                    <select class="form-select" id="carrierFilter">
                        <option value="">Tous transporteurs</option>
                        <option value="jax_delivery">JAX Delivery</option>
                        <option value="mes_colis">Mes Colis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
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
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Brouillons</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="statDraft">0</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Validés</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="statValidated">0</div>
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Récupérés</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="statPickedUp">0</div>
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
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Problèmes</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="statProblems">0</div>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div id="alertContainer"></div>

    <!-- Contenu principal -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-1"></i>
                    Enlèvements
                </h6>
                <div id="bulkActions" style="display: none;">
                    <button class="btn btn-sm btn-success" onclick="bulkValidate()">
                        <i class="fas fa-check me-1"></i>
                        Valider (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <!-- Loading -->
            <div id="loadingState" class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <h5 class="text-muted mb-2">Chargement des enlèvements...</h5>
                <p class="text-muted small">Récupération des données...</p>
            </div>

            <!-- Empty state -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <i class="fas fa-warehouse fa-4x text-muted mb-4"></i>
                <h5 class="text-muted mb-2">Aucun enlèvement trouvé</h5>
                <p class="text-muted mb-4" id="emptyMessage">Créez votre premier enlèvement pour commencer</p>
                <div>
                    <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary" id="createFirstBtn">
                        <i class="fas fa-plus me-1"></i>
                        Créer un Enlèvement
                    </a>
                    <button class="btn btn-outline-secondary" onclick="clearFilters()" id="clearFiltersBtn" style="display: none;">
                        <i class="fas fa-times me-1"></i>
                        Effacer les filtres
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div id="pickupsTable" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>ID & Date</th>
                                <th>Transporteur</th>
                                <th>Date Enlèvement</th>
                                <th class="text-center">Commandes</th>
                                <th>Totaux</th>
                                <th>Statut</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pickupsTableBody">
                            <!-- Les données seront insérées ici -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="pickupModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-warehouse me-2"></i>
                        Détails Enlèvement <span id="modalPickupId"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Le contenu sera inséré ici -->
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

@section('css')
<style>
.pickup-row {
    transition: all 0.2s ease;
    cursor: pointer;
}

.pickup-row:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateX(2px);
}

.badge {
    font-size: 0.7rem;
    padding: 0.35em 0.6em;
}

.btn-group .btn {
    border-radius: 4px;
    margin-right: 2px;
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

.loading-btn {
    pointer-events: none;
    opacity: 0.6;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.alert-auto-hide {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('🚀 [PICKUPS] Application initialisée');
    
    // Variables globales
    let pickups = [];
    let filteredPickups = [];
    let selectedPickups = [];
    let isLoading = false;
    
    // Configuration CSRF pour AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Initialisation
    loadPickups();
    setupEventListeners();
    
    // Configuration des filtres avec debounce
    let filterTimeout;
    $('#searchInput, #statusFilter, #carrierFilter').on('input change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            console.log('🔍 [PICKUPS] Application des filtres');
            applyFilters();
        }, 500);
    });
    
    function setupEventListeners() {
        console.log('⚙️ [PICKUPS] Configuration des événements');
        
        // Sélection globale
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.pickup-checkbox').prop('checked', isChecked);
            updateSelectedPickups();
        });
        
        // Gestion des sélections individuelles
        $(document).on('change', '.pickup-checkbox', function() {
            updateSelectedPickups();
        });
        
        // Gestion des clics sur les lignes
        $(document).on('click', '.pickup-row', function(e) {
            if ($(e.target).is('input, button, a') || $(e.target).closest('.btn-group').length) {
                return;
            }
            const pickupId = $(this).data('pickup-id');
            viewPickup(pickupId);
        });
    }
    
    // Fonction principale de chargement
    async function loadPickups() {
        console.log('📡 [PICKUPS] Chargement des pickups...');
        
        if (isLoading) return;
        isLoading = true;
        
        showLoading();
        clearError();
        
        try {
            const params = buildApiParams();
            console.log('📤 [PICKUPS] Paramètres API:', params);
            
            const response = await $.ajax({
                url: '/admin/delivery/pickups/list',
                method: 'GET',
                data: params,
                timeout: 15000
            });
            
            console.log('📥 [PICKUPS] Données reçues:', response);
            
            if (response.success) {
                pickups = response.pickups || [];
                filteredPickups = [...pickups];
                updateStats();
                renderPickups();
                showSuccess(`${pickups.length} enlèvement(s) chargé(s)`);
                console.log('✅ [PICKUPS] Chargement réussi:', pickups.length, 'pickups');
            } else {
                throw new Error(response.error || 'Réponse API invalide');
            }
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur chargement:', error);
            handleError(error, 'chargement des pickups');
            pickups = [];
            filteredPickups = [];
            updateStats();
            showEmptyState();
        } finally {
            isLoading = false;
            hideLoading();
        }
    }
    
    function buildApiParams() {
        const params = {};
        const search = $('#searchInput').val().trim();
        const status = $('#statusFilter').val();
        const carrier = $('#carrierFilter').val();
        
        if (search) params.search = search;
        if (status) params.status = status;
        if (carrier) params.carrier = carrier;
        
        return params;
    }
    
    function applyFilters() {
        const search = $('#searchInput').val().toLowerCase().trim();
        const status = $('#statusFilter').val();
        const carrier = $('#carrierFilter').val();
        
        filteredPickups = pickups.filter(pickup => {
            let matches = true;
            
            if (search) {
                matches = matches && (
                    pickup.id.toString().includes(search) ||
                    (pickup.carrier_slug && pickup.carrier_slug.toLowerCase().includes(search)) ||
                    (pickup.configuration_name && pickup.configuration_name.toLowerCase().includes(search))
                );
            }
            
            if (status) {
                matches = matches && pickup.status === status;
            }
            
            if (carrier) {
                matches = matches && pickup.carrier_slug === carrier;
            }
            
            return matches;
        });
        
        console.log('🔍 [PICKUPS] Filtres appliqués:', filteredPickups.length, 'résultats');
        renderPickups();
    }
    
    function renderPickups() {
        console.log('🎨 [PICKUPS] Rendu des pickups:', filteredPickups.length);
        
        const $tbody = $('#pickupsTableBody');
        const hasFilters = hasActiveFilters();
        
        if (filteredPickups.length === 0) {
            showEmptyState(hasFilters);
            return;
        }
        
        let html = '';
        filteredPickups.forEach(pickup => {
            html += renderPickupRow(pickup);
        });
        
        $tbody.html(html);
        $('#pickupsTable').show();
        $('#emptyState').hide();
        $('#pickupsCount').text(filteredPickups.length);
    }
    
    function renderPickupRow(pickup) {
        const statusClass = getStatusClass(pickup.status);
        const statusLabel = getStatusLabel(pickup.status);
        const statusIcon = getStatusIcon(pickup.status);
        const carrierName = getCarrierName(pickup.carrier_slug);
        const carrierIcon = getCarrierIcon(pickup.carrier_slug);
        
        return `
            <tr class="pickup-row" data-pickup-id="${pickup.id}">
                <td onclick="event.stopPropagation()">
                    <input type="checkbox" class="form-check-input pickup-checkbox" value="${pickup.id}">
                </td>
                <td>
                    <div>
                        <strong class="text-primary">#${pickup.id}</strong>
                        <br><small class="text-muted">${formatDateTime(pickup.created_at)}</small>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="${carrierIcon} text-primary me-2"></i>
                        <div>
                            <div class="fw-bold">${carrierName}</div>
                            <small class="text-muted">${pickup.configuration_name || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div>${formatDate(pickup.pickup_date)}</div>
                    <small class="text-muted">${getRelativeDate(pickup.pickup_date)}</small>
                </td>
                <td class="text-center">
                    <div class="h6 mb-0 text-primary">${pickup.orders_count || 0}</div>
                    <small class="text-muted">commandes</small>
                </td>
                <td>
                    <div>
                        <div><strong>${pickup.total_weight || 0} kg</strong></div>
                        <small class="text-success">${pickup.total_cod_amount || 0} TND</small>
                    </div>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        <i class="${statusIcon} me-1"></i>
                        ${statusLabel}
                    </span>
                </td>
                <td onclick="event.stopPropagation()">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewPickup(${pickup.id})" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        ${pickup.can_be_validated ? `
                            <button class="btn btn-outline-success" onclick="validatePickup(${pickup.id})" title="Valider">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        
                        ${pickup.status === 'validated' ? `
                            <button class="btn btn-outline-info" onclick="markAsPickedUp(${pickup.id})" title="Marquer récupéré">
                                <i class="fas fa-truck"></i>
                            </button>
                        ` : ''}
                        
                        ${pickup.can_be_deleted ? `
                            <button class="btn btn-outline-danger" onclick="deletePickup(${pickup.id})" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                        
                        <button class="btn btn-outline-warning" onclick="diagnosePickup(${pickup.id})" title="Diagnostiquer">
                            <i class="fas fa-stethoscope"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    function updateStats() {
        const stats = {
            draft: 0,
            validated: 0,
            picked_up: 0,
            problems: 0
        };
        
        pickups.forEach(pickup => {
            if (stats.hasOwnProperty(pickup.status)) {
                stats[pickup.status]++;
            }
        });
        
        $('#statDraft').text(stats.draft);
        $('#statValidated').text(stats.validated);
        $('#statPickedUp').text(stats.picked_up);
        $('#statProblems').text(stats.problems);
        
        console.log('📊 [PICKUPS] Stats mises à jour:', stats);
    }
    
    function updateSelectedPickups() {
        selectedPickups = $('.pickup-checkbox:checked').map(function() {
            return parseInt($(this).val());
        }).get();
        
        $('#selectedCount').text(selectedPickups.length);
        
        if (selectedPickups.length > 0) {
            $('#bulkActions').show();
        } else {
            $('#bulkActions').hide();
        }
        
        // Mettre à jour la case "tout sélectionner"
        const totalVisible = $('.pickup-checkbox').length;
        const totalSelected = selectedPickups.length;
        
        $('#selectAll').prop('indeterminate', totalSelected > 0 && totalSelected < totalVisible);
        $('#selectAll').prop('checked', totalSelected > 0 && totalSelected === totalVisible);
        
        console.log('🎯 [PICKUPS] Sélection mise à jour:', selectedPickups);
    }
    
    // Actions sur les pickups
    window.viewPickup = async function(pickupId) {
        console.log('👁️ [PICKUPS] Visualisation pickup:', pickupId);
        
        try {
            const pickup = pickups.find(p => p.id == pickupId);
            if (!pickup) {
                throw new Error('Pickup non trouvé');
            }
            
            $('#modalPickupId').text('#' + pickup.id);
            
            const modalContent = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Informations
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td><strong>ID:</strong></td><td>#${pickup.id}</td></tr>
                                    <tr><td><strong>Statut:</strong></td><td><span class="badge ${getStatusClass(pickup.status)}">${getStatusLabel(pickup.status)}</span></td></tr>
                                    <tr><td><strong>Date:</strong></td><td>${formatDate(pickup.pickup_date)}</td></tr>
                                    <tr><td><strong>Créé:</strong></td><td>${formatDateTime(pickup.created_at)}</td></tr>
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
                                    <i class="${getCarrierIcon(pickup.carrier_slug)} text-primary fa-2x me-3"></i>
                                    <div>
                                        <div class="fw-bold">${getCarrierName(pickup.carrier_slug)}</div>
                                        <small class="text-muted">${pickup.configuration_name || 'N/A'}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4 text-center">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="h4 text-primary mb-0">${pickup.orders_count || 0}</div>
                            <small class="text-muted">Commandes</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="h4 text-success mb-0">${pickup.total_weight || 0} kg</div>
                            <small class="text-muted">Poids Total</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="h4 text-info mb-0">${pickup.total_pieces || 0}</div>
                            <small class="text-muted">Nb Pièces</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="h4 text-warning mb-0">${pickup.total_cod_amount || 0} TND</div>
                            <small class="text-muted">COD Total</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4 p-3 bg-light rounded">
                    ${pickup.can_be_validated ? `
                        <button onclick="validatePickup(${pickup.id})" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Valider
                        </button>
                    ` : ''}
                    ${pickup.status === 'validated' ? `
                        <button onclick="markAsPickedUp(${pickup.id})" class="btn btn-info">
                            <i class="fas fa-truck me-1"></i>Marquer récupéré
                        </button>
                    ` : ''}
                    ${pickup.can_be_deleted ? `
                        <button onclick="deletePickup(${pickup.id})" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    ` : ''}
                    <button onclick="diagnosePickup(${pickup.id})" class="btn btn-outline-warning">
                        <i class="fas fa-stethoscope me-1"></i>Diagnostiquer
                    </button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-1"></i>
                            Commandes Incluses (${pickup.orders?.length || 0})
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
                                    ${pickup.orders && pickup.orders.length > 0 ? 
                                        pickup.orders.map(order => `
                                            <tr>
                                                <td><strong class="text-primary">#${order.id}</strong></td>
                                                <td>${order.customer_name || 'N/A'}</td>
                                                <td>${order.customer_phone || 'N/A'}</td>
                                                <td>
                                                    <div>${order.customer_city || 'N/A'}</div>
                                                    <small class="text-muted">${order.region_name || 'N/A'}</small>
                                                </td>
                                                <td><strong class="text-success">${order.total_price || 0} TND</strong></td>
                                            </tr>
                                        `).join('') :
                                        `<tr>
                                            <td colspan="5" class="text-center py-3 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                Aucune commande dans cet enlèvement
                                            </td>
                                        </tr>`
                                    }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            
            $('#modalContent').html(modalContent);
            $('#pickupModal').modal('show');
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur affichage modal:', error);
            showError('Erreur lors de l\'affichage des détails');
        }
    };
    
    window.validatePickup = async function(pickupId) {
        if (!confirm('Valider cet enlèvement ? Il sera envoyé au transporteur.')) {
            return;
        }
        
        console.log('✅ [PICKUPS] Validation pickup:', pickupId);
        
        const $btn = $(event.target).closest('button');
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        try {
            const response = await $.ajax({
                url: `/admin/delivery/pickups/${pickupId}/validate`,
                method: 'POST'
            });
            
            console.log('✅ [PICKUPS] Réponse validation:', response);
            
            if (response.success) {
                showSuccess(response.message || 'Pickup validé avec succès !');
                loadPickups();
                $('#pickupModal').modal('hide');
            } else {
                throw new Error(response.error || 'Erreur de validation');
            }
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur validation:', error);
            handleError(error, 'validation du pickup');
        } finally {
            $btn.html(originalHtml).prop('disabled', false);
        }
    };
    
    window.markAsPickedUp = async function(pickupId) {
        console.log('🚛 [PICKUPS] Marquage récupération pickup:', pickupId);
        
        const $btn = $(event.target).closest('button');
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        try {
            const response = await $.ajax({
                url: `/admin/delivery/pickups/${pickupId}/mark-picked-up`,
                method: 'POST'
            });
            
            console.log('✅ [PICKUPS] Réponse marquage:', response);
            
            if (response.success) {
                showSuccess(response.message || 'Pickup marqué comme récupéré !');
                loadPickups();
                $('#pickupModal').modal('hide');
            } else {
                throw new Error(response.error || 'Erreur de marquage');
            }
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur marquage:', error);
            handleError(error, 'marquage du pickup');
        } finally {
            $btn.html(originalHtml).prop('disabled', false);
        }
    };
    
    window.deletePickup = async function(pickupId) {
        if (!confirm('Supprimer définitivement cet enlèvement ?')) {
            return;
        }
        
        console.log('🗑️ [PICKUPS] Suppression pickup:', pickupId);
        
        const $btn = $(event.target).closest('button');
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        try {
            const response = await $.ajax({
                url: `/admin/delivery/pickups/${pickupId}`,
                method: 'DELETE'
            });
            
            console.log('✅ [PICKUPS] Réponse suppression:', response);
            
            if (response.success) {
                showSuccess(response.message || 'Pickup supprimé avec succès !');
                loadPickups();
                $('#pickupModal').modal('hide');
            } else {
                throw new Error(response.error || 'Erreur de suppression');
            }
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur suppression:', error);
            handleError(error, 'suppression du pickup');
        } finally {
            $btn.html(originalHtml).prop('disabled', false);
        }
    };
    
    window.diagnosePickup = async function(pickupId) {
        console.log('🔍 [PICKUPS] Diagnostic pickup:', pickupId);
        
        try {
            const response = await $.ajax({
                url: `/admin/delivery/pickups/${pickupId}/diagnose`,
                method: 'GET'
            });
            
            console.log('🔍 [PICKUPS] Résultat diagnostic:', response);
            
            if (response.success) {
                const diagnosis = response.diagnosis;
                showDiagnosisModal(diagnosis, pickupId);
            } else {
                throw new Error(response.error || 'Erreur de diagnostic');
            }
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur diagnostic:', error);
            handleError(error, 'diagnostic du pickup');
        }
    };
    
    function showDiagnosisModal(diagnosis, pickupId) {
        let message = `📋 DIAGNOSTIC PICKUP #${pickupId}\n\n`;
        message += `✅ Peut être validé: ${diagnosis.can_be_validated ? 'OUI' : 'NON'}\n\n`;
        
        if (diagnosis.validation_checks?.length > 0) {
            message += `🔍 VÉRIFICATIONS:\n`;
            diagnosis.validation_checks.forEach(check => {
                message += `• ${check}\n`;
            });
            message += `\n`;
        }
        
        if (diagnosis.recommendations?.length > 0) {
            message += `💡 RECOMMANDATIONS:\n`;
            diagnosis.recommendations.forEach(rec => {
                message += `• ${rec}\n`;
            });
            message += `\n`;
        }
        
        if (diagnosis.configuration_analysis?.length > 0) {
            message += `⚙️ CONFIGURATION:\n`;
            diagnosis.configuration_analysis.forEach(analysis => {
                message += `• ${analysis}\n`;
            });
        }
        
        alert(message);
    }
    
    window.bulkValidate = async function() {
        if (selectedPickups.length === 0) return;
        
        if (!confirm(`Valider ${selectedPickups.length} enlèvement(s) sélectionné(s) ?`)) {
            return;
        }
        
        console.log('🚀 [PICKUPS] Validation en masse:', selectedPickups);
        
        const $btn = $(event.target);
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Validation...').prop('disabled', true);
        
        try {
            const response = await $.ajax({
                url: '/admin/delivery/pickups/bulk-validate',
                method: 'POST',
                data: {
                    pickup_ids: selectedPickups
                }
            });
            
            console.log('✅ [PICKUPS] Réponse validation masse:', response);
            
            if (response.success) {
                showSuccess(response.message || 'Validation en masse réussie !');
                selectedPickups = [];
                updateSelectedPickups();
                loadPickups();
            } else {
                throw new Error(response.error || 'Erreur de validation en masse');
            }
            
        } catch (error) {
            console.error('❌ [PICKUPS] Erreur validation masse:', error);
            handleError(error, 'validation en masse');
        } finally {
            $btn.html(originalHtml).prop('disabled', false);
        }
    };
    
    window.refreshData = function() {
        console.log('🔄 [PICKUPS] Actualisation des données');
        $('#refreshIcon').addClass('fa-spin');
        
        loadPickups().finally(() => {
            $('#refreshIcon').removeClass('fa-spin');
        });
    };
    
    window.clearFilters = function() {
        console.log('🧹 [PICKUPS] Effacement des filtres');
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#carrierFilter').val('');
        filteredPickups = [...pickups];
        renderPickups();
    };
    
    // Fonctions utilitaires
    function hasActiveFilters() {
        return $('#searchInput').val().trim() || $('#statusFilter').val() || $('#carrierFilter').val();
    }
    
    function showLoading() {
        $('#loadingState').show();
        $('#pickupsTable').hide();
        $('#emptyState').hide();
    }
    
    function hideLoading() {
        $('#loadingState').hide();
    }
    
    function showEmptyState(hasFilters = false) {
        $('#pickupsTable').hide();
        $('#emptyState').show();
        
        if (hasFilters) {
            $('#emptyMessage').text('Aucun enlèvement ne correspond aux filtres');
            $('#createFirstBtn').hide();
            $('#clearFiltersBtn').show();
        } else {
            $('#emptyMessage').text('Créez votre premier enlèvement pour commencer');
            $('#createFirstBtn').show();
            $('#clearFiltersBtn').hide();
        }
    }
    
    function showSuccess(message) {
        showAlert('success', message);
        console.log('🎉 [PICKUPS] Succès:', message);
    }
    
    function showError(message) {
        showAlert('danger', message);
        console.error('💥 [PICKUPS] Erreur:', message);
    }
    
    function clearError() {
        $('#alertContainer').empty();
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show alert-auto-hide">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alertContainer').html(alert);
        
        // Auto-hide après 5 secondes pour les succès
        if (type === 'success') {
            setTimeout(() => {
                $('#alertContainer .alert').alert('close');
            }, 5000);
        }
    }
    
    function handleError(error, context) {
        let message = `Erreur lors du ${context}`;
        
        if (error.responseJSON?.error) {
            message = error.responseJSON.error;
        } else if (error.responseJSON?.message) {
            message = error.responseJSON.message;
        } else if (error.statusText && error.status) {
            message = `Erreur ${error.status}: ${error.statusText}`;
        } else if (error.message) {
            message = error.message;
        }
        
        showError(message);
        
        // Log détaillé pour debug
        console.error('🔍 [PICKUPS] Détails erreur:', {
            context,
            status: error.status,
            statusText: error.statusText,
            responseJSON: error.responseJSON,
            message: error.message
        });
    }
    
    // Fonctions d'affichage
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString('fr-FR');
        } catch {
            return 'Date invalide';
        }
    }
    
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleString('fr-FR');
        } catch {
            return 'Date invalide';
        }
    }
    
    function getRelativeDate(dateString) {
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
    }
    
    function getCarrierName(carrierSlug) {
        const names = {
            'jax_delivery': 'JAX Delivery',
            'mes_colis': 'Mes Colis Express'
        };
        return names[carrierSlug] || carrierSlug || 'Transporteur inconnu';
    }
    
    function getCarrierIcon(carrierSlug) {
        const icons = {
            'jax_delivery': 'fas fa-truck',
            'mes_colis': 'fas fa-shipping-fast'
        };
        return icons[carrierSlug] || 'fas fa-truck';
    }
    
    function getStatusLabel(status) {
        const labels = {
            'draft': 'Brouillon',
            'validated': 'Validé',
            'picked_up': 'Récupéré',
            'problem': 'Problème'
        };
        return labels[status] || 'Statut inconnu';
    }
    
    function getStatusIcon(status) {
        const icons = {
            'draft': 'fas fa-edit',
            'validated': 'fas fa-check',
            'picked_up': 'fas fa-truck',
            'problem': 'fas fa-exclamation-triangle'
        };
        return icons[status] || 'fas fa-question';
    }
    
    function getStatusClass(status) {
        const classes = {
            'draft': 'bg-secondary',
            'validated': 'bg-success',
            'picked_up': 'bg-info',
            'problem': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }
});
</script>
@endsection