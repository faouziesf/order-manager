@extends('layouts.admin')

@section('title', 'Commandes Non Assignées')
@section('page-title', 'Commandes Non Assignées')

@section('css')
<style>
    .assignment-container {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        border: 2px solid rgba(16, 185, 129, 0.2);
    }

    .employee-card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        cursor: pointer;
        text-align: center;
    }

    .employee-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .employee-card.selected {
        border-color: var(--success-color);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
    }

    .employee-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin: 0 auto 12px;
    }

    .employee-name {
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 4px;
    }

    .employee-stats {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .quick-assign-panel {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border-left: 4px solid var(--warning-color);
    }

    .selection-summary {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .unassigned-badge {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .priority-indicator {
        width: 4px;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 0 4px 4px 0;
    }

    .priority-vip .priority-indicator {
        background: var(--priority-vip);
    }

    .priority-urgente .priority-indicator {
        background: var(--priority-urgente);
    }

    .priority-normale .priority-indicator {
        background: var(--priority-normale);
    }

    .table tbody tr {
        position: relative;
    }

    .assign-mode .table tbody tr:hover {
        background: rgba(16, 185, 129, 0.05) !important;
        cursor: pointer;
    }

    .assign-mode .table tbody tr.selected {
        background: rgba(16, 185, 129, 0.1) !important;
        border-left: 4px solid var(--success-color);
    }

    .floating-assign-button {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        animation: pulse 2s infinite;
    }

    .floating-assign-button.show {
        display: flex;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 text-gradient mb-2">Commandes Non Assignées</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-user-times me-2"></i>
                {{ $stats['total_unassigned'] }} commandes en attente d'assignation
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" id="quickAssignMode">
                <i class="fas fa-magic me-2"></i>Assignation Rapide
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Toutes les Commandes
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="quick-stats">
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="stat-number">{{ $stats['total_unassigned'] }}</div>
            <div class="stat-label">Non Assignées</div>
        </div>
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
            <div class="stat-number">{{ $stats['new_unassigned'] }}</div>
            <div class="stat-label">Nouvelles</div>
        </div>
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stat-number">{{ $stats['urgent_unassigned'] }}</div>
            <div class="stat-label">Urgentes</div>
        </div>
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="stat-number">{{ $stats['vip_unassigned'] }}</div>
            <div class="stat-label">VIP</div>
        </div>
    </div>

    <!-- Panel d'assignation rapide (masqué par défaut) -->
    <div class="assignment-container" id="assignmentPanel" style="display: none;">
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-3">
                    <i class="fas fa-users me-2 text-success"></i>
                    Sélectionnez un employé
                </h5>
                <div class="row" id="employeesList">
                    @foreach(Auth::guard('admin')->user()->employees()->where('is_active', true)->get() as $employee)
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="employee-card" data-employee-id="{{ $employee->id }}">
                                <div class="employee-avatar">
                                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                                </div>
                                <div class="employee-name">{{ $employee->name }}</div>
                                <div class="employee-stats">
                                    {{ $employee->orders()->where('is_assigned', true)->count() }} commandes
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <div class="selection-summary">
                    <h6 class="mb-3">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Résumé de sélection
                    </h6>
                    <div id="selectionInfo">
                        <p class="text-muted mb-0">
                            <i class="fas fa-hand-pointer me-2"></i>
                            Sélectionnez un employé puis cliquez sur les commandes à assigner
                        </p>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success w-100" id="confirmAssignBtn" disabled>
                            <i class="fas fa-check me-2"></i>Confirmer l'assignation
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="cancelAssignBtn">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres de recherche -->
    <div class="search-container">
        <form method="GET" action="{{ route('admin.orders.unassigned') }}" id="filterForm">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search" class="form-label">
                        <i class="fas fa-search me-2"></i>Recherche
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="ID, nom, téléphone, adresse..."
                           autocomplete="off">
                </div>
                
                <div class="filter-group">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="nouvelle" {{ request('status') == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                        <option value="confirmée" {{ request('status') == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                        <option value="datée" {{ request('status') == 'datée' ? 'selected' : '' }}>Datée</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="priority" class="form-label">Priorité</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">Toutes</option>
                        <option value="vip" {{ request('priority') == 'vip' ? 'selected' : '' }}>VIP</option>
                        <option value="urgente" {{ request('priority') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        <option value="normale" {{ request('priority') == 'normale' ? 'selected' : '' }}>Normale</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                        <a href="{{ route('admin.orders.unassigned') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Tableau des commandes -->
    <div class="table-container position-relative">
        <div class="loading-overlay" id="tableLoader">
            <div class="spinner"></div>
        </div>

        <div class="table-responsive">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Prix Total</th>
                        <th>Statut</th>
                        <th>Priorité</th>
                        <th>Tentatives</th>
                        <th>Date Création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    @forelse($orders as $order)
                        <tr data-order-id="{{ $order->id }}" class="priority-{{ $order->priority }}">
                            <div class="priority-indicator"></div>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input order-checkbox" 
                                           type="checkbox" 
                                           value="{{ $order->id }}">
                                </div>
                            </td>
                            <td>
                                <span class="order-id">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name">
                                        {{ $order->customer_name ?: 'Non renseigné' }}
                                    </div>
                                    <div class="customer-phone">
                                        <i class="fas fa-phone me-1"></i>{{ $order->customer_phone }}
                                    </div>
                                    @if($order->customer_address)
                                        <div class="customer-address">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($order->customer_address, 30) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    {{ number_format($order->total_price, 3) }} TND
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $order->status }}">
                                    @switch($order->status)
                                        @case('nouvelle')
                                            <i class="fas fa-circle"></i>Nouvelle
                                            @break
                                        @case('confirmée')
                                            <i class="fas fa-check-circle"></i>Confirmée
                                            @break
                                        @case('datée')
                                            <i class="fas fa-calendar-alt"></i>Datée
                                            @break
                                        @default
                                            {{ ucfirst($order->status) }}
                                    @endswitch
                                </span>
                                <div class="mt-1">
                                    <span class="unassigned-badge">
                                        <i class="fas fa-user-times"></i>Non Assignée
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="priority-badge priority-{{ $order->priority }}">
                                    @switch($order->priority)
                                        @case('vip')
                                            <i class="fas fa-crown me-1"></i>VIP
                                            @break
                                        @case('urgente')
                                            <i class="fas fa-exclamation me-1"></i>Urgente
                                            @break
                                        @default
                                            <i class="fas fa-minus me-1"></i>Normale
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <div class="attempts-badge">
                                    {{ $order->attempts_count ?? 0 }}
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" 
                                            class="btn btn-action btn-edit" 
                                            title="Modifier"
                                            onclick="window.location='{{ route('admin.orders.edit', $order) }}'">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-action btn-history" 
                                            title="Historique"
                                            onclick="showOrderHistory({{ $order->id }})">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h5>Toutes les commandes sont assignées !</h5>
                                    <p>Excellente organisation ! Toutes vos commandes ont été assignées à vos employés.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="d-flex justify-content-center p-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Bouton flottant d'assignation -->
<button type="button" class="btn btn-success floating-assign-button" id="floatingAssignBtn">
    <i class="fas fa-user-plus"></i>
</button>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>Historique de la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyContent">
                <div class="text-center py-4">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let assignMode = false;
    let selectedEmployee = null;
    let selectedOrders = [];

    // ================================
    // MODE ASSIGNATION RAPIDE
    // ================================
    $('#quickAssignMode').on('click', function() {
        assignMode = !assignMode;
        
        if (assignMode) {
            enterAssignMode();
        } else {
            exitAssignMode();
        }
    });

    function enterAssignMode() {
        assignMode = true;
        $('#assignmentPanel').slideDown();
        $('#ordersTable').addClass('assign-mode');
        $('#quickAssignMode').removeClass('btn-success').addClass('btn-danger')
                           .html('<i class="fas fa-times me-2"></i>Quitter Mode Assignation');
        
        showNotification('Mode assignation activé - Sélectionnez un employé puis les commandes', 'info');
    }

    function exitAssignMode() {
        assignMode = false;
        selectedEmployee = null;
        selectedOrders = [];
        
        $('#assignmentPanel').slideUp();
        $('#ordersTable').removeClass('assign-mode');
        $('.employee-card').removeClass('selected');
        $('.order-checkbox').prop('checked', false);
        $('tbody tr').removeClass('selected');
        $('#floatingAssignBtn').removeClass('show');
        
        $('#quickAssignMode').removeClass('btn-danger').addClass('btn-success')
                           .html('<i class="fas fa-magic me-2"></i>Assignation Rapide');
        
        updateSelectionInfo();
    }

    // ================================
    // SÉLECTION EMPLOYÉ
    // ================================
    $('.employee-card').on('click', function() {
        if (!assignMode) return;
        
        $('.employee-card').removeClass('selected');
        $(this).addClass('selected');
        
        selectedEmployee = {
            id: $(this).data('employee-id'),
            name: $(this).find('.employee-name').text()
        };
        
        updateSelectionInfo();
        showNotification(`Employé sélectionné: ${selectedEmployee.name}`, 'success');
    });

    // ================================
    // SÉLECTION COMMANDES
    // ================================
    $('tbody').on('click', 'tr', function(e) {
        if (!assignMode || !selectedEmployee) return;
        if ($(e.target).is('input, button, a') || $(e.target).closest('button, a').length) return;
        
        const orderId = $(this).data('order-id');
        const checkbox = $(this).find('.order-checkbox');
        
        // Toggle sélection
        if (selectedOrders.includes(orderId)) {
            selectedOrders = selectedOrders.filter(id => id !== orderId);
            $(this).removeClass('selected');
            checkbox.prop('checked', false);
        } else {
            selectedOrders.push(orderId);
            $(this).addClass('selected');
            checkbox.prop('checked', true);
        }
        
        updateSelectionInfo();
        updateFloatingButton();
    });

    // Sélection via checkbox
    $('.order-checkbox').on('change', function(e) {
        e.stopPropagation();
        
        if (!assignMode || !selectedEmployee) {
            $(this).prop('checked', false);
            return;
        }
        
        const orderId = parseInt($(this).val());
        const row = $(this).closest('tr');
        
        if ($(this).is(':checked')) {
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
                row.addClass('selected');
            }
        } else {
            selectedOrders = selectedOrders.filter(id => id !== orderId);
            row.removeClass('selected');
        }
        
        updateSelectionInfo();
        updateFloatingButton();
    });

    // ================================
    // MISE À JOUR DE L'INTERFACE
    // ================================
    function updateSelectionInfo() {
        let html = '';
        
        if (!selectedEmployee) {
            html = `
                <p class="text-muted mb-0">
                    <i class="fas fa-hand-pointer me-2"></i>
                    Sélectionnez un employé puis cliquez sur les commandes à assigner
                </p>
            `;
        } else if (selectedOrders.length === 0) {
            html = `
                <div class="text-center">
                    <div class="mb-2">
                        <i class="fas fa-user-check text-success fa-2x"></i>
                    </div>
                    <p class="mb-1"><strong>Employé:</strong> ${selectedEmployee.name}</p>
                    <p class="text-muted mb-0">Cliquez sur les commandes à assigner</p>
                </div>
            `;
        } else {
            html = `
                <div class="text-center">
                    <div class="mb-2">
                        <i class="fas fa-clipboard-check text-primary fa-2x"></i>
                    </div>
                    <p class="mb-1"><strong>Employé:</strong> ${selectedEmployee.name}</p>
                    <p class="mb-1"><strong>Commandes:</strong> ${selectedOrders.length} sélectionnée(s)</p>
                    <div class="mt-2">
                        <small class="text-muted">
                            IDs: ${selectedOrders.map(id => `#${id.toString().padStart(6, '0')}`).join(', ')}
                        </small>
                    </div>
                </div>
            `;
        }
        
        $('#selectionInfo').html(html);
        $('#confirmAssignBtn').prop('disabled', !selectedEmployee || selectedOrders.length === 0);
    }

    function updateFloatingButton() {
        if (assignMode && selectedEmployee && selectedOrders.length > 0) {
            $('#floatingAssignBtn').addClass('show');
        } else {
            $('#floatingAssignBtn').removeClass('show');
        }
    }

    // ================================
    // CONFIRMATION ASSIGNATION
    // ================================
    $('#confirmAssignBtn, #floatingAssignBtn').on('click', function() {
        if (!selectedEmployee || selectedOrders.length === 0) return;
        
        const confirmation = confirm(
            `Confirmer l'assignation de ${selectedOrders.length} commande(s) à ${selectedEmployee.name} ?`
        );
        
        if (confirmation) {
            performAssignment();
        }
    });

    function performAssignment() {
        $.ajax({
            url: '{{ route("admin.orders.bulk-assign") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_ids: selectedOrders,
                employee_id: selectedEmployee.id
            },
            beforeSend: function() {
                $('#confirmAssignBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Assignation...');
            },
            success: function(response) {
                showNotification(response.message, 'success');
                
                // Supprimer les lignes assignées du tableau
                selectedOrders.forEach(orderId => {
                    $(`tr[data-order-id="${orderId}"]`).fadeOut(500, function() {
                        $(this).remove();
                        
                        // Vérifier s'il reste des commandes
                        if ($('#ordersTableBody tr:visible').length === 0) {
                            $('#ordersTableBody').html(`
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                            <h5>Toutes les commandes sont assignées !</h5>
                                            <p>Excellente organisation ! Toutes vos commandes ont été assignées.</p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                    });
                });
                
                // Réinitialiser la sélection
                selectedOrders = [];
                updateSelectionInfo();
                updateFloatingButton();
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showNotification(response.message || 'Erreur lors de l\'assignation', 'error');
            },
            complete: function() {
                $('#confirmAssignBtn').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Confirmer l\'assignation');
            }
        });
    }

    // ================================
    // BOUTONS D'ANNULATION
    // ================================
    $('#cancelAssignBtn').on('click', exitAssignMode);

    // ================================
    // RECHERCHE EN TEMPS RÉEL
    // ================================
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        searchTimeout = setTimeout(() => {
            if (query.length >= 2 || query.length === 0) {
                performSearch(query);
            }
        }, 500);
    });

    function performSearch(query) {
        $('#tableLoader').addClass('show');
        
        $.ajax({
            url: '{{ route("admin.orders.unassigned") }}',
            method: 'GET',
            data: {
                search: query,
                status: $('#status').val(),
                priority: $('#priority').val(),
                ajax: true
            },
            success: function(response) {
                if (response.orders) {
                    updateTable(response.orders, query);
                }
            },
            error: function() {
                showNotification('Erreur lors de la recherche', 'error');
            },
            complete: function() {
                $('#tableLoader').removeClass('show');
            }
        });
    }

    function updateTable(orders, searchQuery = '') {
        const tbody = $('#ordersTableBody');
        tbody.empty();

        if (orders.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h5>Aucun résultat</h5>
                            <p>Aucune commande non assignée ne correspond à votre recherche.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        orders.forEach(order => {
            const row = createOrderRow(order, searchQuery);
            tbody.append(row);
        });
    }

    function createOrderRow(order, searchQuery = '') {
        const highlightText = (text, query) => {
            if (!query || !text) return text;
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        };

        const statusIcons = {
            'nouvelle': 'fas fa-circle',
            'confirmée': 'fas fa-check-circle',
            'datée': 'fas fa-calendar-alt'
        };

        const priorityIcons = {
            'normale': 'fas fa-minus',
            'urgente': 'fas fa-exclamation',
            'vip': 'fas fa-crown'
        };

        return `
            <tr data-order-id="${order.id}" class="priority-${order.priority}">
                <div class="priority-indicator"></div>
                <td>
                    <div class="form-check">
                        <input class="form-check-input order-checkbox" 
                               type="checkbox" 
                               value="${order.id}">
                    </div>
                </td>
                <td>
                    <span class="order-id">#${String(order.id).padStart(6, '0')}</span>
                </td>
                <td>
                    <div class="customer-info">
                        <div class="customer-name">
                            ${highlightText(order.customer_name || 'Non renseigné', searchQuery)}
                        </div>
                        <div class="customer-phone">
                            <i class="fas fa-phone me-1"></i>${highlightText(order.customer_phone, searchQuery)}
                        </div>
                        ${order.customer_address ? `
                            <div class="customer-address">
                                <i class="fas fa-map-marker-alt me-1"></i>${highlightText(order.customer_address.substring(0, 30), searchQuery)}${order.customer_address.length > 30 ? '...' : ''}
                            </div>
                        ` : ''}
                    </div>
                </td>
                <td>
                    <div class="price-info">
                        ${parseFloat(order.total_price).toFixed(3)} TND
                    </div>
                </td>
                <td>
                    <span class="status-badge status-${order.status}">
                        <i class="${statusIcons[order.status]} me-1"></i>
                        ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                    </span>
                    <div class="mt-1">
                        <span class="unassigned-badge">
                            <i class="fas fa-user-times"></i>Non Assignée
                        </span>
                    </div>
                </td>
                <td>
                    <span class="priority-badge priority-${order.priority}">
                        <i class="${priorityIcons[order.priority]} me-1"></i>
                        ${order.priority.charAt(0).toUpperCase() + order.priority.slice(1)}
                    </span>
                </td>
                <td>
                    <div class="attempts-badge">
                        ${order.attempts_count || 0}
                    </div>
                </td>
                <td>
                    <div class="date-info">
                        ${new Date(order.created_at).toLocaleDateString('fr-FR')} ${new Date(order.created_at).toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}
                    </div>
                </td>
                <td>
                    <div class="action-buttons">
                        <button type="button" 
                                class="btn btn-action btn-edit" 
                                title="Modifier"
                                onclick="window.location='{{ route('admin.orders.edit', '') }}/${order.id}'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-action btn-history" 
                                title="Historique"
                                onclick="showOrderHistory(${order.id})">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    // ================================
    // UTILITAIRES
    // ================================
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(notification);
        
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }
});

function showOrderHistory(orderId) {
    $('#historyModal').modal('show');
    $('#historyContent').html(`
        <div class="text-center py-4">
            <div class="spinner"></div>
            <p class="mt-3 text-muted">Chargement de l'historique...</p>
        </div>
    `);
    
    $.ajax({
        url: `{{ route('admin.orders.history-modal', '') }}/${orderId}`,
        method: 'GET',
        success: function(response) {
            $('#historyContent').html(response);
        },
        error: function() {
            $('#historyContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erreur lors du chargement de l'historique
                </div>
            `);
        }
    });
}
</script>
@endsection