@extends('layouts.admin')

@section('title', 'Gestion des Commandes Doubles')

@section('css')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        transition: transform 0.2s ease;
    }

    .stats-card:hover {
        transform: translateY(-2px);
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stats-label {
        font-size: 0.85rem;
        opacity: 0.9;
        font-weight: 500;
    }

    .search-toolbar {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }

    .search-main {
        position: relative;
        margin-bottom: 1rem;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: #f9fafb;
    }

    .search-input:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 1.1rem;
    }

    .search-loading {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }

    .search-results-count {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .filters-advanced {
        display: none;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        animation: slideDown 0.3s ease;
    }

    .filters-advanced.show {
        display: block;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 500px;
        }
    }

    .action-toolbar {
        display: flex;
        justify-content: between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .btn-toolbar {
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .btn-toolbar:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary-action {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .btn-success-action {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-warning-action {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .main-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }

    .table-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table th {
        background: #f8fafc;
        border: none;
        font-weight: 600;
        color: #374151;
        padding: 0.875rem 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        border: none;
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background: rgba(99, 102, 241, 0.02);
    }

    .priority-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .priority-doublé {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: white;
    }

    .priority-normale {
        background: #e5e7eb;
        color: #374151;
    }

    .priority-urgente {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .priority-vip {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }

    .action-group {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 0.8rem;
        border: none;
        transition: all 0.2s ease;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-merge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-review {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .btn-history {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-detail {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
    }

    .auto-merge-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.5rem;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 12px;
        font-size: 0.7rem;
        color: #059669;
        font-weight: 500;
        margin-top: 0.25rem;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        padding: 1.5rem;
        background: #f8fafc;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-toolbar {
            padding: 1rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .action-group {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-gradient mb-2">
            <i class="fas fa-copy me-2"></i>Commandes Doubles
        </h1>
        <p class="text-muted mb-0">Gestion et fusion des commandes en double</p>
    </div>
    
    <div class="d-flex gap-2">
        <button class="btn btn-toolbar btn-warning-action" id="btnCheckDuplicates">
            <i class="fas fa-search"></i>Vérifier
        </button>
        <button class="btn btn-toolbar btn-success-action" id="btnAutoMerge">
            <i class="fas fa-magic"></i>Fusion Auto
        </button>
    </div>
</div>

<!-- Dashboard Statistics -->
<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stats-number" id="totalDuplicates">{{ $stats['total_duplicates'] }}</div>
        <div class="stats-label">Non examinées</div>
    </div>
    <div class="stats-card">
        <div class="stats-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stats-number" id="mergedToday">{{ $stats['merged_today'] }}</div>
        <div class="stats-label">Fusionnées aujourd'hui</div>
    </div>
    <div class="stats-card">
        <div class="stats-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stats-number" id="pendingReview">{{ $stats['pending_review'] }}</div>
        <div class="stats-label">Clients en attente</div>
    </div>
    <div class="stats-card">
        <div class="stats-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stats-number" id="autoMergeDelay">{{ $stats['auto_merge_delay'] }}h</div>
        <div class="stats-label">Délai auto-fusion</div>
    </div>
</div>

<!-- Search and Filters -->
<div class="search-toolbar">
    <div class="search-main">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Rechercher par numéro de téléphone...">
        <div class="search-loading" id="searchLoading">
            <div class="spinner" style="width: 20px; height: 20px;"></div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center">
        <div class="search-results-count" id="resultsCount">
            <!-- Nombre de résultats affiché ici -->
        </div>
        <button class="btn btn-outline-primary btn-sm" id="btnAdvancedFilters">
            <i class="fas fa-filter me-2"></i>Filtres avancés
        </button>
    </div>
    
    <!-- Filtres avancés -->
    <div class="filters-advanced" id="advancedFilters">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Min. commandes:</label>
                <select class="form-select form-select-sm" id="minOrders">
                    <option value="">Toutes</option>
                    <option value="2">2+</option>
                    <option value="3">3+</option>
                    <option value="4">4+</option>
                    <option value="5">5+</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Montant min.:</label>
                <input type="number" class="form-control form-control-sm" id="minAmount" 
                       placeholder="0.000">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trier par:</label>
                <select class="form-select form-select-sm" id="sortField">
                    <option value="latest_order_date">Dernière commande</option>
                    <option value="total_orders">Nombre commandes</option>
                    <option value="total_amount">Montant total</option>
                    <option value="customer_phone">Téléphone</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Direction:</label>
                <select class="form-select form-select-sm" id="sortDirection">
                    <option value="desc">Décroissant</option>
                    <option value="asc">Croissant</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Actions Toolbar -->
<div class="action-toolbar">
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Actions groupées:</span>
        <button class="btn btn-toolbar btn-success-action" id="btnBulkMerge" disabled>
            <i class="fas fa-compress-arrows-alt"></i>Fusionner tout
        </button>
        <button class="btn btn-toolbar btn-primary-action" id="btnBulkReview" disabled>
            <i class="fas fa-check"></i>Marquer examiné
        </button>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Affichage:</span>
        <select class="form-select form-select-sm" id="perPage" style="width: auto;">
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
        <span class="text-muted">par page</span>
    </div>
</div>

<!-- Main Table -->
<div class="main-table">
    <div class="table-header">
        <h6 class="mb-0">
            <i class="fas fa-list me-2"></i>Commandes Doubles
        </h6>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm" id="btnSelectAll">
                <i class="fas fa-check-square me-1"></i>Tout sélectionner
            </button>
            <button class="btn btn-outline-secondary btn-sm" id="btnRefresh">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="selectAllCheckbox">
                    </th>
                    <th>Client</th>
                    <th>Commandes</th>
                    <th>Montant</th>
                    <th>Dernière</th>
                    <th>Priorité</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody id="duplicatesTableBody">
                <!-- Contenu chargé via AJAX -->
            </tbody>
        </table>
    </div>
    
    <div class="pagination-wrapper">
        <nav id="paginationNav">
            <!-- Pagination chargée via AJAX -->
        </nav>
    </div>
</div>

<!-- Modal Historique Client -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>Historique Client
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyModalBody">
                <!-- Contenu chargé via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Fusion -->
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-compress-arrows-alt me-2"></i>Fusionner les Commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Cette action va fusionner toutes les commandes de ce client.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Note de fusion:</label>
                    <textarea class="form-control" id="mergeNote" rows="3" 
                              placeholder="Raison de la fusion..."></textarea>
                </div>
                
                <div id="mergePreview">
                    <!-- Aperçu des commandes à fusionner -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="btnConfirmMerge">
                    <i class="fas fa-check me-2"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p class="mb-0">Traitement en cours...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentPhone = null;
    let searchTimeout = null;
    let selectedItems = new Set();
    
    // Charger les données initiales
    loadDuplicates();
    
    // Recherche en temps réel
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        $('#searchLoading').show();
        
        searchTimeout = setTimeout(() => {
            loadDuplicates(1);
            $('#searchLoading').hide();
        }, 500);
    });
    
    // Filtres avancés
    $('#btnAdvancedFilters').click(function() {
        $('#advancedFilters').toggleClass('show');
        const icon = $(this).find('i');
        if ($('#advancedFilters').hasClass('show')) {
            icon.removeClass('fa-filter').addClass('fa-filter-circle-xmark');
        } else {
            icon.removeClass('fa-filter-circle-xmark').addClass('fa-filter');
        }
    });
    
    // Événements de filtres
    $('#minOrders, #minAmount, #sortField, #sortDirection').change(function() {
        loadDuplicates(1);
    });
    
    // Fonction pour charger les doublons
    function loadDuplicates(page = 1) {
        const filters = {
            page: page,
            per_page: $('#perPage').val(),
            search: $('#searchInput').val(),
            min_orders: $('#minOrders').val(),
            min_amount: $('#minAmount').val(),
            sort: $('#sortField').val(),
            direction: $('#sortDirection').val()
        };
        
        $.get('/admin/duplicates/get', filters)
            .done(function(response) {
                renderTable(response);
                renderPagination(response);
                updateResultsCount(response);
                currentPage = page;
            })
            .fail(function() {
                showError('Erreur lors du chargement des données');
            });
    }
    
    // Rendu du tableau
    function renderTable(data) {
        const tbody = $('#duplicatesTableBody');
        tbody.empty();
        selectedItems.clear();
        updateBulkActions();
        
        if (data.data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h6>Aucune commande double trouvée</h6>
                            <p class="text-muted">Essayez de modifier vos filtres</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        
        data.data.forEach(function(duplicate) {
            const canAutoMerge = duplicate.can_auto_merge;
            const autoMergeIndicator = canAutoMerge 
                ? '<div class="auto-merge-indicator"><i class="fas fa-clock"></i>Éligible fusion auto</div>'
                : '';
            
            const row = `
                <tr data-phone="${duplicate.customer_phone}">
                    <td>
                        <input type="checkbox" class="item-checkbox" 
                               value="${duplicate.customer_phone}">
                    </td>
                    <td>
                        <div>
                            <strong>${duplicate.customer_phone}</strong>
                            <br><small class="text-muted">${duplicate.latest_order.customer_name || 'N/A'}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-primary">${duplicate.total_orders}</span>
                    </td>
                    <td>
                        <strong>${parseFloat(duplicate.total_amount).toFixed(3)} TND</strong>
                    </td>
                    <td>
                        <small>${formatDate(duplicate.latest_order_date)}</small>
                        ${autoMergeIndicator}
                    </td>
                    <td>
                        <span class="priority-badge priority-doublé">
                            <i class="fas fa-copy"></i>Doublé
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <button class="btn btn-action btn-merge" 
                                    onclick="openMergeModal('${duplicate.customer_phone}')"
                                    title="Fusionner">
                                <i class="fas fa-compress-arrows-alt"></i>
                            </button>
                            <button class="btn btn-action btn-review" 
                                    onclick="markAsReviewed('${duplicate.customer_phone}')"
                                    title="Marquer examiné">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-action btn-history" 
                                    onclick="showHistory('${duplicate.customer_phone}')"
                                    title="Historique">
                                <i class="fas fa-history"></i>
                            </button>
                            <button class="btn btn-action btn-detail" 
                                    onclick="viewDetail('${duplicate.customer_phone}')"
                                    title="Détail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Événements checkboxes
        $('.item-checkbox').change(function() {
            const phone = $(this).val();
            if ($(this).is(':checked')) {
                selectedItems.add(phone);
            } else {
                selectedItems.delete(phone);
            }
            updateBulkActions();
        });
    }
    
    // Mise à jour du compteur de résultats
    function updateResultsCount(data) {
        const count = data.total || 0;
        const text = count === 0 ? 'Aucun résultat' : 
                     count === 1 ? '1 résultat' : `${count} résultats`;
        $('#resultsCount').text(text);
    }
    
    // Rendu de la pagination
    function renderPagination(data) {
        const nav = $('#paginationNav');
        nav.empty();
        
        if (data.last_page <= 1) return;
        
        let pagination = '<ul class="pagination pagination-sm">';
        
        if (data.current_page > 1) {
            pagination += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDuplicates(${data.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        }
        
        for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
            const active = i === data.current_page ? 'active' : '';
            pagination += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadDuplicates(${i})">${i}</a>
            </li>`;
        }
        
        if (data.current_page < data.last_page) {
            pagination += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadDuplicates(${data.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        }
        
        pagination += '</ul>';
        nav.html(pagination);
    }
    
    // Mise à jour des actions groupées
    function updateBulkActions() {
        const hasSelection = selectedItems.size > 0;
        $('#btnBulkMerge, #btnBulkReview').prop('disabled', !hasSelection);
        
        $('#selectAllCheckbox').prop('checked', selectedItems.size > 0 && 
            selectedItems.size === $('.item-checkbox').length);
    }
    
    // Sélectionner tout
    $('#selectAllCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('.item-checkbox').prop('checked', isChecked);
        
        selectedItems.clear();
        if (isChecked) {
            $('.item-checkbox').each(function() {
                selectedItems.add($(this).val());
            });
        }
        updateBulkActions();
    });
    
    // Actions groupées
    $('#btnBulkMerge').click(function() {
        if (selectedItems.size === 0) return;
        
        if (!confirm(`Fusionner ${selectedItems.size} groupe(s) de commandes ?`)) {
            return;
        }
        
        showLoading();
        
        const phones = Array.from(selectedItems);
        let completed = 0;
        
        phones.forEach(phone => {
            $.post('/admin/duplicates/merge', {
                customer_phone: phone,
                note: 'Fusion groupée',
                _token: '{{ csrf_token() }}'
            })
            .always(() => {
                completed++;
                if (completed === phones.length) {
                    hideLoading();
                    showSuccess(`${phones.length} groupe(s) fusionné(s)`);
                    loadDuplicates(currentPage);
                    refreshStats();
                }
            });
        });
    });
    
    $('#btnBulkReview').click(function() {
        if (selectedItems.size === 0) return;
        
        showLoading();
        
        const phones = Array.from(selectedItems);
        let completed = 0;
        
        phones.forEach(phone => {
            $.post('/admin/duplicates/mark-reviewed', {
                customer_phone: phone,
                _token: '{{ csrf_token() }}'
            })
            .always(() => {
                completed++;
                if (completed === phones.length) {
                    hideLoading();
                    showSuccess(`${phones.length} groupe(s) marqué(s) comme examiné(s)`);
                    loadDuplicates(currentPage);
                    refreshStats();
                }
            });
        });
    });
    
    // Vérifier les doublons
    $('#btnCheckDuplicates').click(function() {
        showLoading();
        
        $.post('/admin/duplicates/check', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(response.message);
                loadDuplicates(currentPage);
                refreshStats();
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la vérification des doublons');
        });
    });
    
    // Fusion automatique
    $('#btnAutoMerge').click(function() {
        if (!confirm('Déclencher la fusion automatique des commandes éligibles ?')) {
            return;
        }
        
        showLoading();
        
        $.post('/admin/duplicates/auto-merge', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(response.message);
                loadDuplicates(currentPage);
                refreshStats();
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la fusion automatique');
        });
    });
    
    // Actualiser
    $('#btnRefresh').click(function() {
        loadDuplicates(currentPage);
    });
    
    // Changement de pagination
    $('#perPage').change(function() {
        loadDuplicates(1);
    });
    
    // Confirmer fusion
    $('#btnConfirmMerge').click(function() {
        if (!currentPhone) return;
        
        const note = $('#mergeNote').val();
        
        showLoading();
        
        $.post('/admin/duplicates/merge', {
            customer_phone: currentPhone,
            note: note,
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            hideLoading();
            $('#mergeModal').modal('hide');
            
            if (response.success) {
                showSuccess(response.message);
                loadDuplicates(currentPage);
                refreshStats();
            } else {
                showError(response.message);
            }
        })
        .fail(function() {
            hideLoading();
            showError('Erreur lors de la fusion');
        });
    });
    
    // Fonctions utilitaires
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showSuccess(message) {
        $('body').append(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="fas fa-check-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function showError(message) {
        $('body').append(`
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                <i class="fas fa-exclamation-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function refreshStats() {
        $.get('/admin/duplicates/stats')
            .done(function(stats) {
                $('#totalDuplicates').text(stats.total_duplicates);
                $('#mergedToday').text(stats.merged_today);
                $('#pendingReview').text(stats.pending_review);
            });
    }
    
    // Actualisation automatique toutes les 60 secondes
    setInterval(function() {
        loadDuplicates(currentPage);
        refreshStats();
    }, 60000);
});

// Fonctions globales pour les actions
function openMergeModal(phone) {
    currentPhone = phone;
    $('#mergeNote').val('');
    
    $.get('/admin/duplicates/history', { customer_phone: phone })
        .done(function(response) {
            let preview = '<h6>Commandes à fusionner:</h6><ul>';
            response.orders.forEach(function(order) {
                if (order.status === 'nouvelle' && order.is_duplicate && !order.reviewed_for_duplicates) {
                    preview += `<li>Commande #${order.id} - ${parseFloat(order.total_price).toFixed(3)} TND</li>`;
                }
            });
            preview += '</ul>';
            
            $('#mergePreview').html(preview);
            $('#mergeModal').modal('show');
        })
        .fail(function() {
            showError('Erreur lors du chargement des détails');
        });
}

function markAsReviewed(phone) {
    if (!confirm('Marquer ces commandes comme examinées ?')) {
        return;
    }
    
    showLoading();
    
    $.post('/admin/duplicates/mark-reviewed', {
        customer_phone: phone,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        hideLoading();
        if (response.success) {
            showSuccess(response.message);
            loadDuplicates(currentPage);
            refreshStats();
        } else {
            showError(response.message);
        }
    })
    .fail(function() {
        hideLoading();
        showError('Erreur lors du marquage');
    });
}

function showHistory(phone) {
    $.get('/admin/duplicates/history', { customer_phone: phone })
        .done(function(response) {
            let content = `
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Total commandes:</strong><br>
                        <span class="badge bg-primary">${response.total_orders}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Total dépensé:</strong><br>
                        <span class="badge bg-success">${parseFloat(response.total_spent).toFixed(3)} TND</span>
                    </div>
                </div>
                
                <h6>Historique des commandes:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            response.orders.forEach(function(order) {
                const statusBadge = getStatusBadge(order.status);
                const priorityBadge = getPriorityBadge(order.priority, order.is_duplicate);
                
                content += `
                    <tr>
                        <td>#${order.id}</td>
                        <td>${formatDate(order.created_at)}</td>
                        <td>${statusBadge}</td>
                        <td>${priorityBadge}</td>
                        <td>${parseFloat(order.total_price).toFixed(3)} TND</td>
                        <td>
                            <a href="/admin/orders/${order.id}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            content += '</tbody></table></div>';
            
            if (response.merge_history.length > 0) {
                content += '<h6 class="mt-3">Historique des fusions:</h6>';
                response.merge_history.forEach(function(merge) {
                    content += `<div class="alert alert-info">${merge.note}</div>`;
                });
            }
            
            $('#historyModalBody').html(content);
            $('#historyModal').modal('show');
        })
        .fail(function() {
            showError('Erreur lors du chargement de l\'historique');
        });
}

function viewDetail(phone) {
    window.open(`/admin/duplicates/detail/${encodeURIComponent(phone)}`, '_blank');
}

function getStatusBadge(status) {
    const badges = {
        'nouvelle': '<span class="badge bg-info">Nouvelle</span>',
        'confirmée': '<span class="badge bg-success">Confirmée</span>',
        'annulée': '<span class="badge bg-danger">Annulée</span>',
        'datée': '<span class="badge bg-warning">Datée</span>',
        'en_route': '<span class="badge bg-primary">En route</span>',
        'livrée': '<span class="badge bg-success">Livrée</span>'
    };
    
    return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
}

function getPriorityBadge(priority, isDuplicate) {
    if (isDuplicate) {
        return '<span class="priority-badge priority-doublé"><i class="fas fa-copy"></i> Doublé</span>';
    }
    
    const badges = {
        'normale': '<span class="priority-badge priority-normale">Normale</span>',
        'urgente': '<span class="priority-badge priority-urgente">Urgente</span>',
        'vip': '<span class="priority-badge priority-vip">VIP</span>'
    };
    
    return badges[priority] || `<span class="priority-badge priority-normale">${priority}</span>`;
}

// Variables globales
let currentPhone = null;

function showLoading() {
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

function showSuccess(message) {
    $('body').append(`
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

function showError(message) {
    $('body').append(`
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-exclamation-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

function refreshStats() {
    $.get('/admin/duplicates/stats')
        .done(function(stats) {
            $('#totalDuplicates').text(stats.total_duplicates);
            $('#mergedToday').text(stats.merged_today);
            $('#pendingReview').text(stats.pending_review);
        });
}

function loadDuplicates(page = 1) {
    const filters = {
        page: page,
        per_page: $('#perPage').val(),
        search: $('#searchInput').val(),
        min_orders: $('#minOrders').val(),
        min_amount: $('#minAmount').val(),
        sort: $('#sortField').val(),
        direction: $('#sortDirection').val()
    };
    
    $.get('/admin/duplicates/get', filters)
        .done(function(response) {
            renderTable(response);
            renderPagination(response);
            updateResultsCount(response);
            currentPage = page;
        })
        .fail(function() {
            showError('Erreur lors du chargement des données');
        });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>
@endsection