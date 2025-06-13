@extends('layouts.admin')

@section('title', 'Gestion des Commandes Doubles')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('css')
<style>
    /* Variables CSS simples */
    :root {
        --primary: #6366f1;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-800: #1f2937;
        --white: #ffffff;
    }

    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    .container-fluid {
        max-width: 1600px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* Stats + Actions section */
    .stats-actions {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }

    .stats-grid {
        display: flex;
        gap: 1.5rem;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary), #4f46e5);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        text-align: center;
        min-width: 140px;
    }

    .stat-card.success {
        background: linear-gradient(135deg, var(--success), #059669);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
    }

    .stat-card.info {
        background: linear-gradient(135deg, var(--info), #1d4ed8);
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
    }

    .main-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn {
        padding: 0.625rem 1rem;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-outline {
        background: white;
        color: var(--gray-600);
        border: 2px solid var(--gray-200);
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Search toolbar */
    .search-toolbar {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .search-main {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1rem;
    }

    .search-box {
        flex: 1;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-600);
    }

    .search-actions {
        display: flex;
        gap: 0.75rem;
    }

    /* Filtres avancés */
    .advanced-filters {
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
        display: none;
    }

    .filters-row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 140px;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-600);
    }

    .filter-select, .filter-input {
        padding: 0.5rem;
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        font-size: 0.875rem;
    }

    /* Table */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--gray-800);
    }

    .badge {
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .table-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .table-responsive {
        max-height: calc(100vh - 400px);
        overflow-y: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: #f8fafc;
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-800);
        font-size: 0.75rem;
        text-transform: uppercase;
        border-bottom: 1px solid var(--gray-200);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .table tr:hover {
        background: #f8fafc;
    }

    .table tr.selected {
        background: rgba(99, 102, 241, 0.1);
    }

    /* Client info */
    .client-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .client-phone {
        font-weight: 600;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .client-name {
        font-size: 0.75rem;
        color: var(--gray-600);
        margin-left: 1.25rem;
    }

    /* Badges */
    .badge-count {
        background: var(--primary);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        text-align: center;
        min-width: 40px;
    }

    .amount-display {
        font-weight: 600;
        color: var(--success);
    }

    .date-display {
        font-size: 0.75rem;
        color: var(--gray-600);
    }

    .priority-badge {
        background: #d4a147;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .auto-merge-indicator {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Status badges */
    .status-indicators {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .status-summary {
        font-size: 0.75rem;
        color: var(--gray-600);
        background: var(--gray-100);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    .mergeable-indicator {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .non-mergeable-indicator {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Actions */
    .action-group {
        display: flex;
        gap: 0.375rem;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-action:hover {
        transform: translateY(-1px);
    }

    .btn-merge {
        background: var(--success);
        color: white;
    }

    .btn-review {
        background: var(--primary);
        color: white;
    }

    .btn-orders {
        background: var(--info);
        color: white;
    }

    .btn-detail {
        background: var(--warning);
        color: white;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--gray-600);
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
    }

    .modal-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-body {
        padding: 1.5rem;
        max-height: 60vh;
        overflow-y: auto;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
    }

    .modal-close:hover {
        background: var(--gray-100);
    }

    /* Orders list */
    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .order-item {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
    }

    .order-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .order-item-title {
        font-weight: 600;
    }

    .order-item-amount {
        font-weight: 600;
        color: var(--success);
    }

    .order-item-details {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    /* Loading */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--gray-200);
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 1rem;
        text-align: center;
        border-top: 1px solid var(--gray-200);
    }

    .pagination {
        display: inline-flex;
        gap: 0.25rem;
    }

    .page-link {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--gray-200);
        background: white;
        text-decoration: none;
        color: var(--gray-600);
        border-radius: 4px;
    }

    .page-link:hover {
        background: var(--gray-100);
    }

    .page-link.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-actions {
            flex-direction: column;
            gap: 1rem;
        }

        .stats-grid {
            flex-wrap: wrap;
            justify-content: center;
        }

        .search-main {
            flex-direction: column;
        }

        .filters-row {
            flex-direction: column;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .action-group {
            flex-direction: column;
            gap: 0.25rem;
        }
    }

    /* Checkbox styles */
    .form-check-input {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
    }

    /* Alert styles */
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .alert-dismissible .btn-close {
        margin-left: auto;
        background: none;
        border: none;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    @endif

    <!-- Stats + Actions -->
    <div class="stats-actions">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="totalDuplicates">{{ $stats['total_duplicates'] ?? 0 }}</div>
                <div class="stat-label">Doublons (Tous Statuts)</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-number" id="mergeableDuplicates">{{ $stats['mergeable_duplicates'] ?? 0 }}</div>
                <div class="stat-label">Fusionnables</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-number" id="mergedToday">{{ $stats['merged_today'] ?? 0 }}</div>
                <div class="stat-label">Fusionnées Aujourd'hui</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number" id="uniqueClients">{{ $stats['unique_clients'] ?? 0 }}</div>
                <div class="stat-label">Clients en Attente</div>
            </div>
        </div>
        
        <div class="main-actions">
            <button class="btn btn-warning" id="btnCheckDuplicates">
                <i class="fas fa-search"></i>
                Vérifier Doublons
            </button>
            <button class="btn btn-success" id="btnAutoMerge">
                <i class="fas fa-magic"></i>
                Fusion Auto
            </button>
            <button class="btn btn-outline" id="btnCleanData" title="Nettoyer les données incohérentes">
                <i class="fas fa-broom"></i>
                Nettoyer
            </button>
        </div>
    </div>

    <!-- Search Toolbar -->
    <div class="search-toolbar">
        <div class="search-main">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Rechercher un client par téléphone ou nom...">
            </div>
            
            <div class="search-actions">
                <button class="btn btn-outline" id="btnAdvancedFilters">
                    <i class="fas fa-filter"></i> Filtres
                </button>
                <button class="btn btn-success" id="btnBulkMerge" disabled>
                    <i class="fas fa-compress-arrows-alt"></i> Fusionner
                </button>
                <button class="btn btn-primary" id="btnBulkReview" disabled>
                    <i class="fas fa-check"></i> Examiner
                </button>
            </div>
        </div>
        
        <!-- Advanced Filters -->
        <div class="advanced-filters" id="advancedFilters">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label">Type de doublons:</label>
                    <select class="filter-select" id="duplicateType">
                        <option value="">Tous</option>
                        <option value="mergeable">Fusionnables seulement</option>
                        <option value="non_mergeable">Non-fusionnables seulement</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Min commandes:</label>
                    <select class="filter-select" id="minOrders">
                        <option value="">Toutes</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Montant min:</label>
                    <input type="number" class="filter-input" id="minAmount" placeholder="TND">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Trier par:</label>
                    <select class="filter-select" id="sortField">
                        <option value="latest_order_date">Date récente</option>
                        <option value="total_orders">Nb commandes</option>
                        <option value="total_amount">Montant total</option>
                        <option value="customer_phone">Téléphone</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Par page:</label>
                    <select class="filter-select" id="perPage">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-users"></i>
                Clients avec Commandes Doubles
                <span class="badge" id="totalResultsBadge">0</span>
            </div>
            <div class="table-info">
                <span id="resultsCount">Chargement...</span>
                <span id="selectedInfo" style="display: none;">
                    <i class="fas fa-check-square"></i>
                    <span id="selectedCount">0</span> sélectionné(s)
                </span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input" title="Sélectionner tout">
                        </th>
                        <th>Client</th>
                        <th>Commandes</th>
                        <th>Montant</th>
                        <th>Dernière</th>
                        <th>Statut</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="duplicatesTableBody">
                    <tr>
                        <td colspan="7" class="text-center">Chargement des données...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            <div id="paginationNav"></div>
        </div>
    </div>
</div>

<!-- Orders Modal -->
<div class="modal" id="ordersModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-list-alt"></i>
                Liste des Commandes
            </h5>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button class="btn btn-primary btn-sm" id="btnViewFullDetails">
                    <i class="fas fa-external-link-alt"></i> Détails Complets
                </button>
                <button class="modal-close" onclick="closeOrdersModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <div id="ordersModalBody">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Merge Modal -->
<div class="modal" id="mergeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-compress-arrows-alt"></i>
                Fusionner les Commandes
            </h5>
            <button class="modal-close" onclick="closeMergeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); color: #1e40af; border: 1px solid rgba(59, 130, 246, 0.3);">
                <i class="fas fa-info-circle"></i>
                Cette action va fusionner toutes les commandes fusionnables (nouvelle/datée) de ce client.
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Note de fusion:</label>
                <textarea id="mergeNote" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-200); border-radius: 6px;"
                          placeholder="Indiquez la raison de cette fusion..."></textarea>
            </div>
            
            <div id="mergePreview"></div>
            
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button class="btn btn-outline" onclick="closeMergeModal()">Annuler</button>
                <button class="btn btn-success" id="btnConfirmMerge">
                    <i class="fas fa-check"></i> Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <p>Traitement en cours...</p>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Global variables
let currentPage = 1;
let currentPhone = null;
let searchTimeout = null;
let selectedItems = new Set();

$(document).ready(function() {
    
    // Load initial data
    loadDuplicates();
    
    // Advanced filters toggle
    $('#btnAdvancedFilters').click(function() {
        const filters = $('#advancedFilters');
        if (filters.is(':visible')) {
            filters.slideUp();
            $(this).removeClass('active');
        } else {
            filters.slideDown();
            $(this).addClass('active');
        }
    });
    
    // Search input
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadDuplicates(1);
        }, 300);
    });
    
    // Filter changes
    $('#duplicateType, #minOrders, #minAmount, #sortField, #perPage').change(function() {
        loadDuplicates(1);
    });
    
    // Select all checkbox
    $('#selectAllCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('.item-checkbox').prop('checked', isChecked);
        
        selectedItems.clear();
        if (isChecked) {
            $('.item-checkbox').each(function() {
                selectedItems.add($(this).val());
                $(this).closest('tr').addClass('selected');
            });
        } else {
            $('.table tbody tr').removeClass('selected');
        }
        updateBulkActions();
    });
    
    // Bulk actions
    $('#btnBulkMerge').click(function() {
        if (selectedItems.size === 0) return;
        
        if (!confirm(`Fusionner ${selectedItems.size} groupe(s) de commandes ?`)) {
            return;
        }
        
        showLoading();
        
        const phones = Array.from(selectedItems);
        let completed = 0;
        
        phones.forEach(phone => {
            $.post('{{ route("admin.duplicates.merge") }}', {
                customer_phone: phone,
                note: 'Fusion groupée',
                _token: $('meta[name="csrf-token"]').attr('content')
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
        
        if (!confirm(`Marquer ${selectedItems.size} groupe(s) comme examiné(s) ? Cela va marquer TOUTES les commandes de ces clients comme examinées.`)) {
            return;
        }
        
        showLoading();
        
        const phones = Array.from(selectedItems);
        let completed = 0;
        let totalSuccess = 0;
        
        phones.forEach(phone => {
            $.post('{{ route("admin.duplicates.mark-reviewed") }}', {
                customer_phone: phone,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(function(response) {
                if (response.success) {
                    totalSuccess++;
                    // Retirer immédiatement la ligne de la table
                    $(`.table tbody tr[data-phone="${phone}"]`).fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            })
            .fail(function(xhr) {
                console.error(`Erreur pour ${phone}:`, xhr.responseText);
            })
            .always(() => {
                completed++;
                if (completed === phones.length) {
                    hideLoading();
                    
                    if (totalSuccess > 0) {
                        showSuccess(`${totalSuccess} groupe(s) marqué(s) comme examiné(s)`);
                        
                        // Réinitialiser les sélections
                        selectedItems.clear();
                        updateBulkActions();
                        
                        // Rafraîchir après un court délai
                        setTimeout(() => {
                            loadDuplicates(currentPage);
                            refreshStats();
                        }, 1000);
                    } else {
                        showError('Aucun groupe n\'a pu être traité');
                    }
                }
            });
        });
    });
    
    // Main actions
    $('#btnCheckDuplicates').click(function() {
        showLoading();
        
        $.post('{{ route("admin.duplicates.check") }}', {
            _token: $('meta[name="csrf-token"]').attr('content')
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
            showError('Erreur lors de la vérification');
        });
    });
    
    $('#btnAutoMerge').click(function() {
        showLoading();
        
        $.post('{{ route("admin.duplicates.auto-merge") }}', {
            _token: $('meta[name="csrf-token"]').attr('content')
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
    
    // NOUVEAU: Bouton de nettoyage des données
    $('#btnCleanData').click(function() {
        if (!confirm('Nettoyer les données incohérentes ? Cela va automatiquement marquer comme examinés les clients qui ont déjà des commandes traitées.')) {
            return;
        }
        
        showLoading();
        
        $.post('{{ route("admin.duplicates.clean-data") }}', {
            _token: $('meta[name="csrf-token"]').attr('content')
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
            showError('Erreur lors du nettoyage');
        });
    });
    
    // Confirm merge
    $('#btnConfirmMerge').click(function() {
        if (!currentPhone) return;
        
        const note = $('#mergeNote').val();
        
        showLoading();
        
        $.post('{{ route("admin.duplicates.merge") }}', {
            customer_phone: currentPhone,
            note: note,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            hideLoading();
            closeMergeModal();
            
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
    
    // Functions
    function loadDuplicates(page = 1) {
        const filters = {
            page: page,
            per_page: $('#perPage').val(),
            search: $('#searchInput').val(),
            duplicate_type: $('#duplicateType').val(),
            min_orders: $('#minOrders').val(),
            min_amount: $('#minAmount').val(),
            sort: $('#sortField').val(),
            direction: 'desc'
        };
        
        $.get('{{ route("admin.duplicates.get") }}', filters)
            .done(function(response) {
                renderTable(response);
                renderPagination(response);
                updateResultsCount(response);
                currentPage = page;
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur AJAX:', error, xhr.responseText);
                showError('Erreur lors du chargement des données: ' + (xhr.responseJSON?.message || error));
            });
    }
    
    function renderTable(data) {
        const tbody = $('#duplicatesTableBody');
        tbody.empty();
        selectedItems.clear();
        updateBulkActions();
        
        if (data.data.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h6>Aucune commande double trouvée</h6>
                            <p>Modifiez vos critères de recherche.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        
        data.data.forEach(function(duplicate) {
            const canAutoMerge = duplicate.can_auto_merge;
            const autoMergeIndicator = canAutoMerge 
                ? '<div class="auto-merge-indicator"><i class="fas fa-clock"></i> Éligible fusion auto</div>'
                : '';
            
            // Nouvel indicateur pour les commandes fusionnables/non-fusionnables
            const hasMergeable = duplicate.mergeable_orders && duplicate.mergeable_orders.length > 1;
            const hasNonMergeable = duplicate.non_mergeable_orders && duplicate.non_mergeable_orders.length > 0;
            
            let statusIndicators = '<div class="status-indicators">';
            if (hasMergeable) {
                statusIndicators += `<div class="mergeable-indicator"><i class="fas fa-compress-arrows-alt"></i> ${duplicate.mergeable_orders.length} fusionnables</div>`;
            }
            if (hasNonMergeable) {
                statusIndicators += `<div class="non-mergeable-indicator"><i class="fas fa-ban"></i> ${duplicate.non_mergeable_orders.length} non-fusionnables</div>`;
            }
            
            // Afficher les statuts présents
            if (duplicate.statuses) {
                statusIndicators += `<div class="status-summary">Statuts: ${duplicate.statuses}</div>`;
            }
            statusIndicators += '</div>';
            
            const row = `
                <tr data-phone="${duplicate.customer_phone}">
                    <td>
                        <input type="checkbox" class="item-checkbox" value="${duplicate.customer_phone}">
                    </td>
                    <td>
                        <div class="client-info">
                            <div class="client-phone">
                                <i class="fas fa-phone"></i> ${duplicate.customer_phone}
                            </div>
                            <div class="client-name">${duplicate.latest_order && duplicate.latest_order.customer_name ? duplicate.latest_order.customer_name : 'Nom non spécifié'}</div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-count">${duplicate.total_orders}</span>
                    </td>
                    <td>
                        <div class="amount-display">${parseFloat(duplicate.total_amount).toFixed(3)} TND</div>
                    </td>
                    <td>
                        <div class="date-display">${formatDate(duplicate.latest_order_date)}</div>
                        ${autoMergeIndicator}
                    </td>
                    <td>
                        ${statusIndicators}
                    </td>
                    <td>
                        <div class="action-group">
                            ${hasMergeable ? `<button class="btn-action btn-merge" onclick="openMergeModal('${duplicate.customer_phone}')" title="Fusionner">
                                <i class="fas fa-compress-arrows-alt"></i>
                            </button>` : ''}
                            <button class="btn-action btn-review" onclick="markAsReviewed('${duplicate.customer_phone}')" title="Marquer examiné">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn-action btn-orders" onclick="showOrdersModal('${duplicate.customer_phone}')" title="Liste commandes">
                                <i class="fas fa-list-alt"></i>
                            </button>
                            <button class="btn-action btn-detail" onclick="viewDetail('${duplicate.customer_phone}')" title="Détails complets">
                                <i class="fas fa-external-link-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Item checkbox events
        $('.item-checkbox').change(function() {
            const phone = $(this).val();
            const row = $(this).closest('tr');
            
            if ($(this).is(':checked')) {
                selectedItems.add(phone);
                row.addClass('selected');
            } else {
                selectedItems.delete(phone);
                row.removeClass('selected');
            }
            updateBulkActions();
        });
    }
    
    function updateResultsCount(data) {
        const count = data.total || 0;
        const text = count === 0 ? 'Aucun résultat' : 
                     count === 1 ? '1 résultat' : `${count} résultats`;
        $('#resultsCount').text(text);
        $('#totalResultsBadge').text(count);
    }
    
    function renderPagination(data) {
        const nav = $('#paginationNav');
        nav.empty();
        
        if (data.last_page <= 1) return;
        
        let pagination = '<div class="pagination">';
        
        if (data.current_page > 1) {
            pagination += `<a class="page-link" href="#" onclick="loadDuplicates(${data.current_page - 1})">
                <i class="fas fa-chevron-left"></i>
            </a>`;
        }
        
        for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
            const active = i === data.current_page ? 'active' : '';
            pagination += `<a class="page-link ${active}" href="#" onclick="loadDuplicates(${i})">${i}</a>`;
        }
        
        if (data.current_page < data.last_page) {
            pagination += `<a class="page-link" href="#" onclick="loadDuplicates(${data.current_page + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>`;
        }
        
        pagination += '</div>';
        nav.html(pagination);
    }
    
    function updateBulkActions() {
        const hasSelection = selectedItems.size > 0;
        $('#btnBulkMerge, #btnBulkReview').prop('disabled', !hasSelection);
        
        if (hasSelection) {
            $('#selectedInfo').show();
            $('#selectedCount').text(selectedItems.size);
        } else {
            $('#selectedInfo').hide();
        }
        
        $('#selectAllCheckbox').prop('checked', selectedItems.size > 0 && 
            selectedItems.size === $('.item-checkbox').length);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    }
    
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showSuccess(message) {
        showAlert('success', message);
    }
    
    function showError(message) {
        showAlert('danger', message);
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 4000);
    }
    
    function refreshStats() {
        $.get('{{ route("admin.duplicates.stats") }}')
            .done(function(stats) {
                // Mettre à jour les statistiques avec vérification
                $('#totalDuplicates').text(stats.total_duplicates || 0);
                $('#mergeableDuplicates').text(stats.mergeable_duplicates || 0);
                $('#mergedToday').text(stats.merged_today || 0);
                $('#uniqueClients').text(stats.unique_clients || 0);
                
                // Si plus de doublons, rafraîchir la liste
                if ((stats.total_duplicates || 0) === 0) {
                    const currentResultsCount = parseInt($('#totalResultsBadge').text());
                    if (currentResultsCount > 0) {
                        // Il y a une incohérence, forcer le rechargement de la liste
                        loadDuplicates(1);
                    }
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur lors du rafraîchissement des stats:', error);
            });
    }
    
    // Auto-refresh
    setInterval(function() {
        refreshStats();
        if (selectedItems.size === 0) {
            loadDuplicates(currentPage);
        }
    }, 120000);
});

// Global functions
function openMergeModal(phone) {
    currentPhone = phone;
    $('#mergeNote').val('');
    
    $.get('{{ route("admin.duplicates.history") }}', { customer_phone: phone })
        .done(function(response) {
            let preview = `<h6>Commandes fusionnables trouvées:</h6><div style="max-height: 200px; overflow-y: auto;">`;
            let mergeableCount = 0;
            
            if (response.orders) {
                response.orders.forEach(function(order) {
                    if ((order.status === 'nouvelle' || order.status === 'datée') && order.is_duplicate && !order.reviewed_for_duplicates) {
                        preview += `<div style="display: flex; justify-content: space-between; padding: 0.5rem; background: #f8fafc; margin-bottom: 0.25rem; border-radius: 4px;">
                            <span>Commande #${order.id} (${order.status})</span>
                            <strong>${parseFloat(order.total_price).toFixed(3)} TND</strong>
                        </div>`;
                        mergeableCount++;
                    }
                });
            }
            
            if (mergeableCount === 0) {
                preview += '<div class="text-center text-muted">Aucune commande fusionnable trouvée</div>';
            }
            
            preview += '</div>';
            
            $('#mergePreview').html(preview);
            $('#mergeModal').addClass('show');
        })
        .fail(function() {
            showError('Erreur lors du chargement des détails');
        });
}

function closeMergeModal() {
    $('#mergeModal').removeClass('show');
    currentPhone = null;
}

function markAsReviewed(phone) {
    if (!confirm('Marquer comme examiné ? Cela va marquer TOUTES les commandes de ce client comme examinées.')) return;
    
    // Afficher un indicateur de chargement sur la ligne concernée
    const row = $(`.table tbody tr[data-phone="${phone}"]`);
    const originalContent = row.html();
    row.html('<td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Traitement en cours...</td>');
    
    $.post('{{ route("admin.duplicates.mark-reviewed") }}', {
        customer_phone: phone,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            showSuccess(response.message);
            
            // Retirer immédiatement la ligne de la table
            row.fadeOut(300, function() {
                $(this).remove();
                
                // Mettre à jour le compteur de résultats
                const currentTotal = parseInt($('#totalResultsBadge').text()) - 1;
                $('#totalResultsBadge').text(Math.max(0, currentTotal));
                $('#resultsCount').text(currentTotal === 0 ? 'Aucun résultat' : 
                                      currentTotal === 1 ? '1 résultat' : `${currentTotal} résultats`);
                
                // Si plus de résultats, afficher le message vide
                if (currentTotal === 0) {
                    $('#duplicatesTableBody').html(`
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-search"></i>
                                    <h6>Aucune commande double trouvée</h6>
                                    <p>Tous les doublons ont été traités.</p>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            });
            
            // Rafraîchir les statistiques
            refreshStats();
            
            // Si le response indique qu'on doit rafraîchir, le faire après un délai
            if (response.should_refresh) {
                setTimeout(() => {
                    loadDuplicates(currentPage);
                }, 1000);
            }
        } else {
            // Restaurer le contenu original en cas d'erreur
            row.html(originalContent);
            showError(response.message || 'Erreur lors de la mise à jour');
        }
    })
    .fail(function(xhr) {
        // Restaurer le contenu original en cas d'erreur
        row.html(originalContent);
        
        let errorMessage = 'Erreur lors de la mise à jour';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showError(errorMessage);
    });
}

function showOrdersModal(phone) {
    window.currentModalPhone = phone;
    
    $.get('{{ route("admin.duplicates.history") }}', { customer_phone: phone })
        .done(function(response) {
            let content = '<div class="orders-list">';
            
            if (response.orders && response.orders.length > 0) {
                response.orders.forEach(function(order) {
                    const statusBadge = getStatusBadge(order.status);
                    const duplicateInfo = order.is_duplicate ? 
                        `<span style="color: #d4a147; font-weight: bold;">[DOUBLON${order.reviewed_for_duplicates ? ' - EXAMINÉ' : ''}]</span>` : '';
                    
                    content += `
                        <div class="order-item">
                            <div class="order-item-header">
                                <div class="order-item-title">Commande #${order.id} ${duplicateInfo}</div>
                                <div class="order-item-amount">${parseFloat(order.total_price).toFixed(3)} TND</div>
                            </div>
                            <div class="order-item-details">
                                <strong>Date:</strong> ${formatDateSimple(order.created_at)} | 
                                <strong>Statut:</strong> ${statusBadge} | 
                                <strong>Produits:</strong> ${order.items ? order.items.length : 0}
                            </div>
                        </div>
                    `;
                });
            } else {
                content += '<div class="text-center text-muted">Aucune commande trouvée</div>';
            }
            
            content += '</div>';
            
            $('#ordersModalBody').html(content);
            $('#ordersModal').addClass('show');
        })
        .fail(function() {
            showError('Erreur lors du chargement des commandes');
        });
}

function closeOrdersModal() {
    $('#ordersModal').removeClass('show');
}

function viewDetail(phone) {
    window.open('{{ route("admin.duplicates.detail", ":phone") }}'.replace(':phone', encodeURIComponent(phone)), '_blank');
}

$('#btnViewFullDetails').click(function() {
    if (window.currentModalPhone) {
        viewDetail(window.currentModalPhone);
    }
});

function getStatusBadge(status) {
    const badges = {
        'nouvelle': '<span style="background: var(--info); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Nouvelle</span>',
        'confirmée': '<span style="background: var(--success); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Confirmée</span>',
        'annulée': '<span style="background: var(--danger); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Annulée</span>',
        'datée': '<span style="background: var(--warning); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Datée</span>',
        'en_route': '<span style="background: var(--primary); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">En route</span>',
        'livrée': '<span style="background: var(--success); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Livrée</span>'
    };
    
    return badges[status] || `<span style="background: var(--gray-600); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">${status}</span>`;
}

function formatDateSimple(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Close modals when clicking outside
$(document).click(function(e) {
    if ($(e.target).is('.modal')) {
        $('.modal').removeClass('show');
    }
});

// Keyboard shortcuts
$(document).keydown(function(e) {
    if (e.key === 'Escape') {
        $('.modal').removeClass('show');
    }
});
</script>
@endsection