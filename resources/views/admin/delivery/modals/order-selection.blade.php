<!-- Modal de sélection des commandes pour pickup -->
<div class="modal fade" id="orderSelectionModal" tabindex="-1" aria-labelledby="orderSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="orderSelectionModalLabel">
                    <i class="fas fa-boxes me-2"></i>Sélectionner les commandes à expédier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Configuration sélectionnée -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <small class="text-muted">Transporteur</small>
                                        <div class="fw-bold" id="selected-carrier-name">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Configuration</small>
                                        <div class="fw-bold" id="selected-integration-name">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Date d'enlèvement</small>
                                        <div class="fw-bold" id="selected-pickup-date">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Statut</small>
                                        <span class="badge bg-success" id="selected-config-status">Actif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-primary mb-1" id="selection-summary-count">0</h4>
                                <small class="text-muted">commande(s) sélectionnée(s)</small>
                                <div class="mt-2">
                                    <small class="text-muted">Total: </small>
                                    <strong id="selection-summary-total">0.000 TND</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres et recherche -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="orderSearchInput" placeholder="Rechercher par nom, téléphone ou ID...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="governorateFilter">
                            <option value="">Tous les gouvernorats</option>
                            <!-- Options seront ajoutées dynamiquement -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="stockFilter">
                            <option value="">Tous</option>
                            <option value="available">Stock disponible</option>
                            <option value="issues">Problèmes de stock</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                            <i class="fas fa-check-square me-1"></i>Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                            <i class="fas fa-square me-1"></i>Tout désélectionner
                        </button>
                    </div>
                </div>

                <!-- Zone de chargement -->
                <div id="ordersLoadingIndicator" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <div class="mt-2 text-muted">Chargement des commandes...</div>
                </div>

                <!-- Tableau des commandes -->
                <div class="card" id="ordersTableContainer">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                            </div>
                                        </th>
                                        <th style="width: 80px;">ID</th>
                                        <th>Client</th>
                                        <th style="width: 120px;">Téléphone</th>
                                        <th style="width: 100px;">Montant</th>
                                        <th style="width: 80px;">Articles</th>
                                        <th style="width: 120px;">Gouvernorat</th>
                                        <th style="width: 100px;">Stock</th>
                                        <th style="width: 100px;">Créée le</th>
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <small id="paginationInfo">Affichage de 0 à 0 sur 0 résultats</small>
                    </div>
                    <nav aria-label="Pagination des commandes">
                        <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                            <!-- Pagination sera générée dynamiquement -->
                        </ul>
                    </nav>
                </div>

                <!-- Messages d'erreur -->
                <div id="selection-errors" class="alert alert-danger mt-3 d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3"></i>
                        <div id="selection-error-content"></div>
                    </div>
                </div>

                <!-- Messages d'information -->
                <div id="selection-info" class="alert alert-info mt-3 d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3"></i>
                        <div id="selection-info-content"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" id="confirmSelectionBtn" disabled>
                    <i class="fas fa-plus me-2"></i>Créer l'enlèvement
                </button>
            </div>
        </div>
    </div>
</div>

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
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-inbox me-2"></i>Aucune commande disponible
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = orders.map(order => {
            const isSelected = selectedOrders.has(order.id);
            const hasStockIssues = !order.can_be_shipped;
            
            return `
                <tr class="${isSelected ? 'table-primary' : ''} ${hasStockIssues ? 'table-warning' : ''}">
                    <td>
                        <div class="form-check">
                            <input class="form-check-input order-checkbox" type="checkbox" 
                                   value="${order.id}" ${isSelected ? 'checked' : ''}
                                   ${hasStockIssues ? 'disabled' : ''}>
                        </div>
                    </td>
                    <td><strong>#${order.id}</strong></td>
                    <td>
                        <div>${order.customer_name || '-'}</div>
                        <small class="text-muted">${order.customer_city || ''}</small>
                    </td>
                    <td>
                        <div>${order.customer_phone || '-'}</div>
                        ${order.customer_phone_2 ? `<small class="text-muted">${order.customer_phone_2}</small>` : ''}
                    </td>
                    <td><strong>${(order.total_price || 0).toFixed(3)} TND</strong></td>
                    <td><span class="badge bg-secondary">${order.items_count || 0}</span></td>
                    <td>${order.region_name || '-'}</td>
                    <td>
                        ${hasStockIssues ? 
                            '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Problèmes</span>' :
                            '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Disponible</span>'
                        }
                    </td>
                    <td>
                        <small>${new Date(order.created_at).toLocaleDateString('fr-FR')}</small>
                    </td>
                </tr>
            `;
        }).join('');

        // Ajouter les event listeners pour les checkboxes
        tbody.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handleOrderSelection);
        });

        updateSelectionSummary();
    }

    // Gérer la sélection d'une commande
    function handleOrderSelection(event) {
        const orderId = parseInt(event.target.value);
        const isChecked = event.target.checked;

        if (isChecked) {
            selectedOrders.add(orderId);
        } else {
            selectedOrders.delete(orderId);
        }

        updateSelectionSummary();
        updateSelectAllCheckbox();
    }

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

    // Sélectionner/désélectionner tout
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