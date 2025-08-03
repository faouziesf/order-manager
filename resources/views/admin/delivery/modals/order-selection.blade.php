<!-- Modal de sélection des commandes pour pickup - Adaptée au layout -->
<div class="modal fade" id="orderSelectionModal" tabindex="-1" aria-labelledby="orderSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); box-shadow: var(--shadow-xl); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-bottom: none; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
                <h5 class="modal-title text-white fw-bold" id="orderSelectionModalLabel">
                    <i class="fas fa-boxes me-2"></i>
                    Sélectionner les commandes à expédier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" style="padding: 2rem;">
                <!-- Configuration sélectionnée -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card" style="border: 2px solid var(--info-color); border-radius: var(--border-radius); background: rgba(6, 182, 212, 0.05);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <h6 class="text-info fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Configuration Sélectionnée
                                </h6>
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Transporteur</small>
                                        <div class="fw-bold text-dark" id="selected-carrier-name">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted fw-medium">Configuration</small>
                                        <div class="fw-bold text-dark" id="selected-integration-name">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Date d'enlèvement</small>
                                        <div class="fw-bold text-dark" id="selected-pickup-date">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted fw-medium">Statut</small>
                                        <span class="badge bg-success" id="selected-config-status" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%) !important;">Actif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%); border: 1px solid rgba(30, 64, 175, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body text-center" style="padding: 1.5rem;">
                                <h4 class="text-primary mb-2 fw-bold" id="selection-summary-count" style="font-size: 2rem;">0</h4>
                                <small class="text-muted fw-medium">commande(s) sélectionnée(s)</small>
                                <div class="mt-2" style="border-top: 1px solid rgba(30, 64, 175, 0.1); padding-top: 0.75rem;">
                                    <small class="text-muted fw-medium">Total: </small>
                                    <strong class="text-primary" id="selection-summary-total">0.000 TND</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres et recherche -->
                <div class="card mb-4" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                    <div class="card-body" style="padding: 1.5rem;">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border: none; color: white;">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="orderSearchInput" 
                                           placeholder="Rechercher par nom, téléphone ou ID..."
                                           style="border: 2px solid var(--card-border); border-left: none; border-radius: 0 var(--border-radius) var(--border-radius) 0;">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" 
                                        id="governorateFilter"
                                        style="border: 2px solid var(--card-border); border-radius: var(--border-radius);">
                                    <option value="">Tous les gouvernorats</option>
                                    <!-- Options seront ajoutées dynamiquement -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" 
                                        id="stockFilter"
                                        style="border: 2px solid var(--card-border); border-radius: var(--border-radius);">
                                    <option value="">Tous</option>
                                    <option value="available">Stock disponible</option>
                                    <option value="issues">Problèmes de stock</option>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            id="selectAllBtn"
                                            style="border-radius: var(--border-radius) 0 0 var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-check-square me-1"></i>Tout sélectionner
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-sm" 
                                            id="deselectAllBtn"
                                            style="border-radius: 0 var(--border-radius) var(--border-radius) 0; font-weight: 500;">
                                        <i class="fas fa-square me-1"></i>Tout désélectionner
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone de chargement -->
                <div id="ordersLoadingIndicator" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h6 class="text-primary fw-bold">Chargement des commandes...</h6>
                    <p class="text-muted">Veuillez patienter</p>
                </div>

                <!-- Tableau des commandes -->
                <div class="card" id="ordersTableContainer" style="border: 1px solid var(--card-border); border-radius: var(--border-radius); overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0">
                                <thead style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="width: 50px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="selectAllCheckbox"
                                                       style="border: 2px solid var(--primary-color); border-radius: 4px;">
                                            </div>
                                        </th>
                                        <th style="width: 80px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">ID</th>
                                        <th style="border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Client</th>
                                        <th style="width: 120px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Téléphone</th>
                                        <th style="width: 100px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Montant</th>
                                        <th style="width: 80px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Articles</th>
                                        <th style="width: 120px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Gouvernorat</th>
                                        <th style="width: 100px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Stock</th>
                                        <th style="width: 100px; border: none; padding: 1rem 0.75rem; font-weight: 600; color: var(--text-color);">Créée le</th>
                                    </tr>
                                </thead>
                                <tbody id="ordersTableBody">
                                    <!-- Les commandes seront ajoutées dynamiquement -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        <small id="paginationInfo" class="fw-medium">Affichage de 0 à 0 sur 0 résultats</small>
                    </div>
                    <nav aria-label="Pagination des commandes">
                        <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                            <!-- Pagination sera générée dynamiquement -->
                        </ul>
                    </nav>
                </div>

                <!-- Messages d'erreur -->
                <div id="selection-errors" class="alert alert-danger mt-4 d-none" role="alert" style="background: linear-gradient(135deg, #fecaca 0%, #f87171 100%); border: 1px solid var(--danger-color); border-radius: var(--border-radius); border-left: 4px solid var(--danger-color);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 text-danger"></i>
                        <div id="selection-error-content" class="text-danger fw-medium"></div>
                    </div>
                </div>

                <!-- Messages d'information -->
                <div id="selection-info" class="alert alert-info mt-4 d-none" role="alert" style="background: linear-gradient(135deg, #cffafe 0%, #67e8f9 100%); border: 1px solid var(--info-color); border-radius: var(--border-radius); border-left: 4px solid var(--info-color);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 text-info"></i>
                        <div id="selection-info-content" class="text-info fw-medium"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="background: rgba(248, 250, 252, 0.5); border-top: 1px solid var(--card-border); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); padding: 1.5rem;">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal"
                            style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="button" 
                            class="btn btn-primary" 
                            id="confirmSelectionBtn" 
                            disabled
                            style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-plus me-2"></i>Créer l'enlèvement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques pour la modal de sélection */
#orderSelectionModal .table tbody tr {
    transition: all 0.2s ease;
    cursor: pointer;
}

#orderSelectionModal .table tbody tr:hover {
    background: rgba(30, 64, 175, 0.05) !important;
    transform: scale(1.001);
}

#orderSelectionModal .table tbody tr.table-primary {
    background: rgba(30, 64, 175, 0.1) !important;
    border-left: 3px solid var(--primary-color);
}

#orderSelectionModal .table tbody tr.table-warning {
    background: rgba(255, 193, 7, 0.1) !important;
    cursor: not-allowed;
}

#orderSelectionModal .table tbody tr.table-warning td {
    opacity: 0.7;
}

/* Styles pour les checkboxes */
#orderSelectionModal .form-check-input {
    border: 2px solid var(--primary-color);
    border-radius: 4px;
    width: 1.1em;
    height: 1.1em;
}

#orderSelectionModal .form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Badges personnalisés */
#orderSelectionModal .badge.bg-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%) !important;
}

#orderSelectionModal .badge.bg-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%) !important;
}

#orderSelectionModal .badge.bg-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
}

/* Pagination personnalisée */
#orderSelectionModal .pagination .page-link {
    border: 1px solid var(--card-border);
    color: var(--primary-color);
    font-weight: 500;
    border-radius: var(--border-radius);
    margin: 0 2px;
}

#orderSelectionModal .pagination .page-link:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

#orderSelectionModal .pagination .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    border-color: var(--primary-color);
    color: white;
}

/* Animation des cartes */
#orderSelectionModal .card {
    animation: slideInUp 0.3s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scrollbar personnalisée */
#orderSelectionModal .table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

#orderSelectionModal .table-responsive::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

#orderSelectionModal .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
}

/* Effet hover pour les boutons */
#orderSelectionModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

#orderSelectionModal .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Effet loading */
#orderSelectionModal .spinner-border {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 768px) {
    #orderSelectionModal .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
    
    #orderSelectionModal .modal-body {
        padding: 1.5rem 1rem;
    }
    
    #orderSelectionModal .row {
        margin-bottom: 1rem;
    }
    
    #orderSelectionModal .col-md-3,
    #orderSelectionModal .col-md-4,
    #orderSelectionModal .col-md-8 {
        margin-bottom: 1rem;
    }
    
    #orderSelectionModal .btn-group {
        width: 100%;
        flex-direction: column;
    }
    
    #orderSelectionModal .btn-group .btn {
        border-radius: var(--border-radius) !important;
        margin-bottom: 0.5rem;
    }
    
    #orderSelectionModal .table-responsive {
        font-size: 0.85rem;
    }
    
    #orderSelectionModal .pagination {
        justify-content: center;
        flex-wrap: wrap;
    }
}

/* Animation pour les messages */
#orderSelectionModal .alert {
    animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* État de chargement pour les lignes du tableau */
#orderSelectionModal tbody tr.loading {
    opacity: 0.5;
    pointer-events: none;
}

#orderSelectionModal tbody tr.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top: 2px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderSelectionModal');
    const confirmBtn = document.getElementById('confirmSelectionBtn');
    const searchInput = document.getElementById('orderSearchInput');
    const governorateFilter = document.getElementById('governorateFilter');
    const stockFilter = document.getElementById('stockFilter');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');

    let currentConfiguration = null;
    let selectedOrders = new Set();
    let allOrders = [];
    let filteredOrders = [];
    let currentPage = 1;
    let ordersPerPage = 50;
    let searchTimeout = null;

    // Fonction pour ouvrir la modal avec une configuration
    window.openOrderSelectionModal = function(configurationData) {
        currentConfiguration = configurationData;
        selectedOrders.clear();
        
        // Remplir les informations de configuration
        document.getElementById('selected-carrier-name').textContent = configurationData.carrier_name || '-';
        document.getElementById('selected-integration-name').textContent = configurationData.integration_name || '-';
        document.getElementById('selected-pickup-date').textContent = configurationData.pickup_date || new Date().toLocaleDateString('fr-FR');
        
        // Réinitialiser les filtres
        resetFilters();
        
        // Charger les commandes
        loadAvailableOrders();
        
        // Ouvrir la modal
        new bootstrap.Modal(modal).show();
    };

    // Charger les commandes disponibles
    function loadAvailableOrders(page = 1) {
        showLoadingIndicator(true);

        const params = new URLSearchParams({
            page: page,
            per_page: ordersPerPage,
            search: searchInput.value.trim(),
            governorate: governorateFilter.value,
            stock_filter: stockFilter.value
        });

        axios.get(`/admin/delivery/preparation/orders?${params}`)
            .then(response => {
                if (response.data.success) {
                    allOrders = response.data.orders;
                    updateOrdersTable(allOrders);
                    updatePagination(response.data.pagination);
                    updateGovernorateFilter(response.data.governorates || []);
                } else {
                    showError('Erreur lors du chargement des commandes');
                }
            })
            .catch(error => {
                console.error('Erreur chargement commandes:', error);
                showError('Erreur de communication avec le serveur');
            })
            .finally(() => {
                showLoadingIndicator(false);
            });
    }

    // Mettre à jour le tableau des commandes
    function updateOrdersTable(orders) {
        const tbody = document.getElementById('ordersTableBody');
        
        if (orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                        <h6 class="text-muted">Aucune commande disponible</h6>
                        <p class="text-muted mb-0">Aucune commande ne correspond à vos critères de recherche</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = orders.map(order => {
            const isSelected = selectedOrders.has(order.id);
            const hasStockIssues = !order.can_be_shipped;
            
            return `
                <tr class="${isSelected ? 'table-primary' : ''} ${hasStockIssues ? 'table-warning' : ''}" 
                    onclick="toggleOrderSelection(${order.id})" 
                    style="transition: all 0.2s ease;">
                    <td onclick="event.stopPropagation();">
                        <div class="form-check">
                            <input class="form-check-input order-checkbox" 
                                   type="checkbox" 
                                   value="${order.id}" 
                                   ${isSelected ? 'checked' : ''}
                                   ${hasStockIssues ? 'disabled' : ''}
                                   onchange="handleOrderSelection(event)">
                        </div>
                    </td>
                    <td><strong class="text-primary">#${order.id}</strong></td>
                    <td>
                        <div class="fw-bold">${order.customer_name || '-'}</div>
                        <small class="text-muted">${order.customer_city || ''}</small>
                    </td>
                    <td>
                        <div>${order.customer_phone || '-'}</div>
                        ${order.customer_phone_2 ? `<small class="text-muted">${order.customer_phone_2}</small>` : ''}
                    </td>
                    <td><strong class="text-success">${(order.total_price || 0).toFixed(3)} TND</strong></td>
                    <td><span class="badge bg-secondary">${order.items_count || 0}</span></td>
                    <td>${order.region_name || '-'}</td>
                    <td>
                        ${hasStockIssues ? 
                            '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Problèmes</span>' :
                            '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Disponible</span>'
                        }
                    </td>
                    <td>
                        <small class="text-muted">${new Date(order.created_at).toLocaleDateString('fr-FR')}</small>
                    </td>
                </tr>
            `;
        }).join('');

        updateSelectionSummary();
    }

    // Gérer la sélection d'une commande
    window.handleOrderSelection = function(event) {
        const orderId = parseInt(event.target.value);
        const isChecked = event.target.checked;

        if (isChecked) {
            selectedOrders.add(orderId);
        } else {
            selectedOrders.delete(orderId);
        }

        updateSelectionSummary();
        updateSelectAllCheckbox();
    };

    window.toggleOrderSelection = function(orderId) {
        const order = allOrders.find(o => o.id === orderId);
        if (!order || !order.can_be_shipped) return;

        if (selectedOrders.has(orderId)) {
            selectedOrders.delete(orderId);
        } else {
            selectedOrders.add(orderId);
        }

        updateOrdersTable(allOrders);
        updateSelectionSummary();
        updateSelectAllCheckbox();
    };

    // Mettre à jour le résumé de sélection
    function updateSelectionSummary() {
        const selectedCount = selectedOrders.size;
        const selectedOrdersData = allOrders.filter(order => selectedOrders.has(order.id));
        const totalAmount = selectedOrdersData.reduce((sum, order) => sum + (order.total_price || 0), 0);

        document.getElementById('selection-summary-count').textContent = selectedCount;
        document.getElementById('selection-summary-total').textContent = totalAmount.toFixed(3) + ' TND';

        confirmBtn.disabled = selectedCount === 0;
        
        if (selectedCount === 0) {
            confirmBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Créer l\'enlèvement';
        } else {
            confirmBtn.innerHTML = `<i class="fas fa-plus me-2"></i>Créer l'enlèvement (${selectedCount})`;
        }
    }

    // Mettre à jour la checkbox "Tout sélectionner"
    function updateSelectAllCheckbox() {
        const availableOrders = allOrders.filter(order => order.can_be_shipped);
        const selectedAvailable = availableOrders.filter(order => selectedOrders.has(order.id));
        
        if (availableOrders.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (selectedAvailable.length === availableOrders.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else if (selectedAvailable.length > 0) {
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        }
    }

    // Event listeners
    selectAllCheckbox.addEventListener('change', function() {
        const availableOrders = allOrders.filter(order => order.can_be_shipped);
        
        if (this.checked) {
            availableOrders.forEach(order => selectedOrders.add(order.id));
        } else {
            availableOrders.forEach(order => selectedOrders.delete(order.id));
        }
        
        updateOrdersTable(allOrders);
    });

    selectAllBtn.addEventListener('click', function() {
        const availableOrders = allOrders.filter(order => order.can_be_shipped);
        availableOrders.forEach(order => selectedOrders.add(order.id));
        updateOrdersTable(allOrders);
    });

    deselectAllBtn.addEventListener('click', function() {
        selectedOrders.clear();
        updateOrdersTable(allOrders);
    });

    // Filtres et recherche
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadAvailableOrders(currentPage);
        }, 500);
    });

    governorateFilter.addEventListener('change', function() {
        currentPage = 1;
        loadAvailableOrders(currentPage);
    });

    stockFilter.addEventListener('change', function() {
        currentPage = 1;
        loadAvailableOrders(currentPage);
    });

    // Confirmer la sélection
    confirmBtn.addEventListener('click', function() {
        if (selectedOrders.size === 0) return;

        const originalContent = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';

        const data = {
            delivery_configuration_id: currentConfiguration.id,
            order_ids: Array.from(selectedOrders),
            pickup_date: document.getElementById('selected-pickup-date').textContent
        };

        axios.post('/admin/delivery/preparation', data)
            .then(response => {
                if (response.data.success) {
                    showInfo('Pickup créé avec succès ! Redirection...');
                    
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(modal).hide();
                        if (typeof refreshPreparationPage === 'function') {
                            refreshPreparationPage();
                        }
                        // Rediriger vers la page des pickups
                        window.location.href = '/admin/delivery/pickups';
                    }, 1500);
                } else {
                    showError(response.data.message || 'Erreur lors de la création du pickup');
                }
            })
            .catch(error => {
                console.error('Erreur création pickup:', error);
                const message = error.response?.data?.message || 'Erreur de communication avec le serveur';
                showError(message);
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalContent;
            });
    });

    // Fonctions utilitaires
    function showLoadingIndicator(show) {
        document.getElementById('ordersLoadingIndicator').classList.toggle('d-none', !show);
        document.getElementById('ordersTableContainer').classList.toggle('d-none', show);
    }

    function showError(message) {
        const errorDiv = document.getElementById('selection-errors');
        document.getElementById('selection-error-content').textContent = message;
        errorDiv.classList.remove('d-none');
        document.getElementById('selection-info').classList.add('d-none');
    }

    function showInfo(message) {
        const infoDiv = document.getElementById('selection-info');
        document.getElementById('selection-info-content').textContent = message;
        infoDiv.classList.remove('d-none');
        document.getElementById('selection-errors').classList.add('d-none');
    }

    function resetFilters() {
        searchInput.value = '';
        governorateFilter.value = '';
        stockFilter.value = '';
        currentPage = 1;
    }

    function updateGovernorateFilter(governorates) {
        const currentValue = governorateFilter.value;
        governorateFilter.innerHTML = '<option value="">Tous les gouvernorats</option>';
        
        governorates.forEach(gov => {
            const option = document.createElement('option');
            option.value = gov.id;
            option.textContent = gov.name;
            if (gov.id == currentValue) option.selected = true;
            governorateFilter.appendChild(option);
        });
    }

    function updatePagination(pagination) {
        const container = document.getElementById('paginationContainer');
        const info = document.getElementById('paginationInfo');
        
        // Mettre à jour les informations
        const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
        const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
        info.textContent = `Affichage de ${start} à ${end} sur ${pagination.total} résultats`;
        
        // Générer la pagination
        let paginationHTML = '';
        
        if (pagination.current_page > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Précédent</a></li>`;
        }
        
        for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
            paginationHTML += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        
        if (pagination.current_page < pagination.last_page) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Suivant</a></li>`;
        }
        
        container.innerHTML = paginationHTML;
        
        // Ajouter les event listeners
        container.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page && page !== currentPage) {
                    currentPage = page;
                    loadAvailableOrders(page);
                }
            });
        });
    }
});
</script>