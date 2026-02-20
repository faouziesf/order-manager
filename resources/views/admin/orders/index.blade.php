@extends('layouts.admin')

@section('title', 'Gestion des Commandes')
@section('page-title', 'Gestion des Commandes')

@section('css')
<style>
    :root {
        --status-nouvelle: #6b7280;
        --status-confirmee: #10b981;
        --status-annulee: #ef4444;
        --status-datee: #f59e0b;
        --status-en-route: #06b6d4;
        --status-livree: #8b5cf6;
        --priority-normale: #6b7280;
        --priority-urgente: #f59e0b;
        --priority-vip: #ef4444;
    }

    /* OPTIMISÉ DESKTOP: Espaces réduits */
    .search-container {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-radius: 12px; /* Réduit de 16px */
        padding: 18px; /* Réduit de 24px */
        margin-bottom: 18px; /* Réduit de 24px */
        border: 1px solid rgba(102, 126, 234, 0.1);
    }

    .advanced-filters {
        display: none;
        margin-top: 15px; /* Réduit de 20px */
        padding-top: 15px; /* Réduit de 20px */
        border-top: 1px solid #e5e7eb;
    }

    .advanced-filters.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .filter-row {
        display: flex;
        gap: 12px; /* Réduit de 16px */
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        flex: 1;
        min-width: 180px; /* Réduit de 200px */
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Réduit de 200px */
        gap: 12px; /* Réduit de 16px */
        margin-bottom: 18px; /* Réduit de 24px */
    }

    .stat-card {
        background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
        border-radius: 10px; /* Réduit de 12px */
        padding: 15px; /* Réduit de 20px */
        text-align: center;
        border: 1px solid rgba(102, 126, 234, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px; /* Réduit de 4px */
        background: var(--gradient-bg);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* Réduit l'ombre */
    }

    .stat-number {
        font-size: 1.75rem; /* Réduit de 2rem */
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 3px; /* Réduit de 4px */
    }

    .stat-label {
        color: var(--text-muted);
        font-weight: 500;
        font-size: 0.85rem; /* Réduit de 0.9rem */
    }

    .table-container {
        background: white;
        border-radius: 12px; /* Réduit de 16px */
        overflow: hidden;
        box-shadow: 0 3px 5px rgba(0, 0, 0, 0.05); /* Réduit l'ombre */
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: none;
        padding: 12px 10px; /* Réduit de 16px 12px */
        font-weight: 600;
        color: var(--text-color);
        white-space: nowrap;
        font-size: 0.8rem; /* Réduit pour gagner espace */
    }

    .table tbody td {
        padding: 12px 10px; /* Réduit de 16px 12px */
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem; /* Réduit pour gagner espace */
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: rgba(102, 126, 234, 0.02);
        transform: translateX(2px);
    }

    /* NOUVEAU: Indicateur de doublons */
    .duplicate-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: white;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 700;
        margin-left: 0.25rem;
        box-shadow: 0 2px 4px rgba(212, 161, 71, 0.3);
    }

    .duplicate-indicator:hover {
        transform: scale(1.05);
        cursor: pointer;
    }

    .status-badge {
        padding: 5px 10px; /* Réduit de 6px 12px */
        border-radius: 16px; /* Réduit de 20px */
        font-size: 0.75rem; /* Réduit de 0.8rem */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px; /* Réduit de 6px */
    }

    .status-nouvelle { background: rgba(107, 114, 128, 0.1); color: var(--status-nouvelle); }
    .status-confirmée { background: rgba(16, 185, 129, 0.1); color: var(--status-confirmee); }
    .status-annulée { background: rgba(239, 68, 68, 0.1); color: var(--status-annulee); }
    .status-datée { background: rgba(245, 158, 11, 0.1); color: var(--status-datee); }
    .status-en_route { background: rgba(6, 182, 212, 0.1); color: var(--status-en-route); }
    .status-livrée { background: rgba(139, 92, 246, 0.1); color: var(--status-livree); }

    .priority-badge {
        padding: 3px 7px; /* Réduit de 4px 8px */
        border-radius: 10px; /* Réduit de 12px */
        font-size: 0.7rem; /* Réduit de 0.75rem */
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-normale { background: rgba(107, 114, 128, 0.1); color: var(--priority-normale); }
    .priority-urgente { background: rgba(245, 158, 11, 0.1); color: var(--priority-urgente); }
    .priority-vip { background: rgba(239, 68, 68, 0.1); color: var(--priority-vip); }

    .action-buttons {
        display: flex;
        gap: 4px; /* Réduit de 6px */
        align-items: center;
    }

    .btn-action {
        width: 32px; /* Réduit de 36px */
        height: 32px; /* Réduit de 36px */
        border-radius: 6px; /* Réduit de 8px */
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        font-size: 0.8rem; /* Réduit */
    }

    .btn-action::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: currentColor;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .btn-action:hover::before {
        opacity: 0.1;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15); /* Réduit l'ombre */
    }

    .btn-edit {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .btn-delete {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .btn-history {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    /* NOUVEAU: Bouton duplicates */
    .btn-duplicates {
        background: rgba(212, 161, 71, 0.1);
        color: #d4a147;
    }

    .btn-show {
        background: rgba(6, 182, 212, 0.1);
        color: #06b6d4;
    }

    .order-id {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 0.8rem; /* Réduit */
    }

    .customer-info {
        display: flex;
        flex-direction: column;
        gap: 1px; /* Réduit de 2px */
    }

    .customer-name {
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.8rem; /* Réduit */
    }

    .customer-phone {
        font-size: 0.75rem; /* Réduit de 0.85rem */
        color: var(--text-muted);
        font-family: 'JetBrains Mono', monospace;
    }

    .customer-address {
        font-size: 0.7rem; /* Réduit de 0.8rem */
        color: var(--text-muted);
        max-width: 180px; /* Réduit de 200px */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .price-info {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: var(--success-color);
        font-size: 0.8rem; /* Réduit */
    }

    .attempts-badge {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
        padding: 3px 7px; /* Réduit de 4px 8px */
        border-radius: 10px; /* Réduit de 12px */
        font-size: 0.7rem; /* Réduit de 0.75rem */
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
    }

    .date-info {
        font-size: 0.75rem; /* Réduit de 0.85rem */
        color: var(--text-muted);
    }

    /* Mode assignation */
    .assignment-mode {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
        border: 2px solid rgba(16, 185, 129, 0.3);
        border-radius: 10px; /* Réduit de 12px */
        padding: 12px 16px; /* Réduit de 16px 20px */
        margin-bottom: 15px; /* Réduit de 20px */
        display: none;
    }

    .assignment-mode.active {
        display: block;
        animation: slideDown 0.3s ease;
    }

    .assignment-mode h6 {
        color: #059669;
        margin-bottom: 10px; /* Réduit de 12px */
    }

    .table tbody tr.selected {
        background: rgba(16, 185, 129, 0.1) !important;
        border-left: 4px solid #10b981;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 12px; /* Réduit de 16px */
        backdrop-filter: blur(2px);
    }

    .loading-overlay.show {
        display: flex;
    }

    .spinner {
        width: 32px; /* Réduit de 40px */
        height: 32px; /* Réduit de 40px */
        border: 3px solid #f3f4f6; /* Réduit de 4px */
        border-top: 3px solid var(--primary-color); /* Réduit de 4px */
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Modal amélioré pour l'historique */
    .modal-content {
        border: none;
        border-radius: 10px; /* Réduit de 12px */
        box-shadow: 0 6px 20px -6px rgba(0, 0, 0, 0.12); /* Réduit l'ombre */
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 1.25rem; /* Réduit de 1.25rem 1.5rem */
    }

    .modal-header .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem; /* Réduit de 0.75rem */
        font-size: 1rem; /* Réduit */
    }

    .modal-header .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        opacity: 1;
        width: 28px; /* Réduit de 32px */
        height: 28px; /* Réduit de 32px */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-header .btn-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .modal-body {
        padding: 1.25rem; /* Réduit de 1.5rem */
    }

    /* NOUVEAU: Modal pour duplicates */
    .duplicates-modal .modal-dialog {
        max-width: 800px;
    }

    .duplicates-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .duplicate-order-item {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }

    .duplicate-order-item:hover {
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .duplicate-order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .duplicate-order-id {
        font-weight: 600;
        color: #374151;
    }

    .duplicate-order-status {
        font-size: 0.75rem;
    }

    .duplicate-order-details {
        font-size: 0.8rem;
        color: #6b7280;
    }

    /* Amélioration du Modal Historique */
    .history-timeline {
        position: relative;
        padding: 0;
    }

    .history-item {
        position: relative;
        padding: 1.25rem 0; /* Réduit de 1.5rem */
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: flex-start;
        gap: 0.875rem; /* Réduit de 1rem */
    }

    .history-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .history-item:first-child {
        padding-top: 0;
    }

    .history-icon {
        width: 36px; /* Réduit de 40px */
        height: 36px; /* Réduit de 40px */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem; /* Réduit de 1rem */
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .history-icon.status-change {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .history-icon.call-attempt {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .history-icon.creation {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .history-icon.assignment {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .history-content {
        flex: 1;
        min-width: 0;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.4rem; /* Réduit de 0.5rem */
        flex-wrap: wrap;
        gap: 0.4rem; /* Réduit de 0.5rem */
    }

    .history-title {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem; /* Réduit de 0.95rem */
        margin: 0;
    }

    .history-date {
        font-size: 0.75rem; /* Réduit de 0.8rem */
        color: #6b7280;
        font-family: 'JetBrains Mono', monospace;
    }

    .history-description {
        color: #6b7280;
        font-size: 0.8rem; /* Réduit de 0.875rem */
        line-height: 1.4; /* Réduit de 1.5 */
        margin: 0;
    }

    .history-details {
        margin-top: 0.625rem; /* Réduit de 0.75rem */
        background: #f8fafc;
        padding: 0.625rem; /* Réduit de 0.75rem */
        border-radius: 6px; /* Réduit de 8px */
        border-left: 3px solid #e5e7eb;
    }

    .history-details .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.4rem; /* Réduit de 0.5rem */
    }

    .history-details .detail-row:last-child {
        margin-bottom: 0;
    }

    .detail-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.75rem; /* Réduit de 0.8rem */
    }

    .detail-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem; /* Réduit de 0.8rem */
        color: #6b7280;
    }

    .history-empty {
        text-align: center;
        padding: 2.5rem 1rem; /* Réduit de 3rem */
        color: #6b7280;
    }

    .history-empty i {
        font-size: 2.5rem; /* Réduit de 3rem */
        margin-bottom: 0.875rem; /* Réduit de 1rem */
        opacity: 0.3;
    }

    /* Animations pour l'historique */
    .history-item {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
        }
        
        .filter-group {
            min-width: 100%;
        }
        
        .quick-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .table-responsive {
            font-size: 0.8rem; /* Réduit de 0.85rem */
        }

        .history-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .history-details .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.2rem; /* Réduit de 0.25rem */
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- En-tête avec actions principales -->
    <div class="d-flex justify-content-between align-items-center mb-3"> <!-- OPTIMISÉ: mb-4 → mb-3 -->
        <div>
            <h2 class="h4 text-gradient mb-1"> <!-- OPTIMISÉ: h3 → h4, mb-2 → mb-1 -->
                <i class="fas fa-shopping-cart me-2"></i>Gestion des Commandes
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;"> <!-- OPTIMISÉ: Taille réduite -->
                <i class="fas fa-info-circle me-2"></i>
                Total: {{ $totalOrders }} commandes
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm"> <!-- OPTIMISÉ: btn-sm -->
                <i class="fas fa-plus me-2"></i>Nouvelle Commande
            </a>
            @php
                $isEmployee = auth('admin')->user()->isEmployee();
            @endphp
            @if($isEmployee)
                <button type="button" class="btn btn-success btn-sm" id="selfAssignmentModeBtn"> <!-- OPTIMISÉ: btn-sm -->
                    <i class="fas fa-user-check me-2"></i>Auto-Assignation
                </button>
            @else
                <button type="button" class="btn btn-success btn-sm" id="assignmentModeBtn"> <!-- OPTIMISÉ: btn-sm -->
                    <i class="fas fa-user-plus me-2"></i>Mode Assignation
                </button>
            @endif
            <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()"> <!-- OPTIMISÉ: btn-sm -->
                <i class="fas fa-sync-alt me-2"></i>Actualiser
            </button>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="quick-stats">
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
            <div class="stat-number">{{ $newOrders }}</div>
            <div class="stat-label">Nouvelles</div>
        </div>
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stat-number">{{ $confirmedOrders }}</div>
            <div class="stat-label">Confirmées</div>
        </div>
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="stat-number">{{ $scheduledOrders }}</div>
            <div class="stat-label">Datées</div>
        </div>
        <div class="stat-card" style="--gradient-bg: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
            <div class="stat-number">{{ $orders->total() }}</div>
            <div class="stat-label">Total Affiché</div>
        </div>
    </div>

    <!-- Mode assignation pour Admins/Managers -->
    <div class="assignment-mode" id="assignmentMode">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6><i class="fas fa-user-check me-2"></i>Mode Assignation Activé</h6>
                <p class="mb-0 text-muted" style="font-size: 0.8rem;">Sélectionnez les commandes non assignées et choisissez un employé</p> <!-- OPTIMISÉ: Taille réduite -->
            </div>
            <div class="d-flex align-items-center gap-2"> <!-- OPTIMISÉ: gap-3 → gap-2 -->
                <select class="form-select form-select-sm" id="assignEmployee" style="width: 180px;"> <!-- OPTIMISÉ: 200px → 180px + form-select-sm -->
                    <option value="">Choisir un employé</option>
                    @php
                        $currentUser = Auth::guard('admin')->user();
                        // Si c'est un manager, récupérer les employés de son créateur (l'admin parent)
                        $searchAdminId = ($currentUser->role === \App\Models\Admin::ROLE_MANAGER && $currentUser->created_by)
                            ? $currentUser->created_by
                            : $currentUser->id;

                        $availableEmployees = \App\Models\Admin::where('role', \App\Models\Admin::ROLE_EMPLOYEE)
                            ->where('created_by', $searchAdminId)
                            ->where('is_active', true)
                            ->get();

                        // Debug - À retirer après test
                        \Log::info('Assignment mode debug', [
                            'current_user_id' => $currentUser->id,
                            'current_user_role' => $currentUser->role,
                            'current_user_created_by' => $currentUser->created_by,
                            'search_admin_id' => $searchAdminId,
                            'employees_count' => $availableEmployees->count(),
                            'employees' => $availableEmployees->pluck('name', 'id')->toArray()
                        ]);
                    @endphp
                    @if($availableEmployees->count() === 0)
                        <option value="" disabled>Aucun employé disponible (Admin ID: {{ $searchAdminId }})</option>
                    @endif
                    @foreach($availableEmployees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-success btn-sm" id="performAssignment" disabled> <!-- OPTIMISÉ: btn-sm -->
                    <i class="fas fa-check me-2"></i>Assigner (<span id="selectedCount">0</span>)
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="cancelAssignment"> <!-- OPTIMISÉ: btn-sm -->
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Mode auto-assignation pour Employés -->
    <div class="assignment-mode" id="selfAssignmentMode" style="display:none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6><i class="fas fa-user-check me-2"></i>Mode Auto-Assignation Activé</h6>
                <p class="mb-0 text-muted" style="font-size: 0.8rem;">Sélectionnez les commandes non assignées pour vous les assigner</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-success btn-sm" id="performSelfAssignment" disabled>
                    <i class="fas fa-check me-2"></i>M'assigner (<span id="selectedCountSelf">0</span>)
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="cancelSelfAssignment">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="search-container">
        <form method="GET" action="{{ route('admin.orders.index') }}" id="filterForm">
            <!-- Recherche principale -->
            <div class="row">
                <div class="col-md-8">
                    <label for="search" class="form-label" style="font-size: 0.85rem;"> <!-- OPTIMISÉ: Taille réduite -->
                        <i class="fas fa-search me-2"></i>Recherche
                    </label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="ID, nom, téléphone, adresse..."
                           autocomplete="off"> <!-- OPTIMISÉ: form-control-sm -->
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"> <!-- OPTIMISÉ: btn-sm -->
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleAdvancedFilters"> <!-- OPTIMISÉ: btn-sm -->
                        <i class="fas fa-filter me-2"></i>Filtres Avancés
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-danger btn-sm"> <!-- OPTIMISÉ: btn-sm -->
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </div>

            <!-- Filtres avancés (masqués par défaut) -->
            <div class="advanced-filters" id="advancedFilters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="status" class="form-label" style="font-size: 0.8rem;">Statut</label> <!-- OPTIMISÉ: Taille réduite -->
                        <select class="form-select form-select-sm" id="status" name="status"> <!-- OPTIMISÉ: form-select-sm -->
                            <option value="">Tous les statuts</option>
                            <option value="nouvelle" {{ request('status') == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                            <option value="confirmée" {{ request('status') == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                            <option value="annulée" {{ request('status') == 'annulée' ? 'selected' : '' }}>Annulée</option>
                            <option value="datée" {{ request('status') == 'datée' ? 'selected' : '' }}>Datée</option>
                            <option value="en_route" {{ request('status') == 'en_route' ? 'selected' : '' }}>En Route</option>
                            <option value="livrée" {{ request('status') == 'livrée' ? 'selected' : '' }}>Livrée</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="priority" class="form-label" style="font-size: 0.8rem;">Priorité</label> <!-- OPTIMISÉ: Taille réduite -->
                        <select class="form-select form-select-sm" id="priority" name="priority"> <!-- OPTIMISÉ: form-select-sm -->
                            <option value="">Toutes les priorités</option>
                            <option value="normale" {{ request('priority') == 'normale' ? 'selected' : '' }}>Normale</option>
                            <option value="urgente" {{ request('priority') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            <option value="vip" {{ request('priority') == 'vip' ? 'selected' : '' }}>VIP</option>
                            <option value="Doublon" {{ request('priority') == 'Doublon' ? 'selected' : '' }}>Doublon</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date_from" class="form-label" style="font-size: 0.8rem;">Date Début</label> <!-- OPTIMISÉ: Taille réduite -->
                        <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}"> <!-- OPTIMISÉ: form-control-sm -->
                    </div>

                    <div class="filter-group">
                        <label for="date_to" class="form-label" style="font-size: 0.8rem;">Date Fin</label> <!-- OPTIMISÉ: Taille réduite -->
                        <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}"> <!-- OPTIMISÉ: form-control-sm -->
                    </div>

                    <div class="filter-group">
                        <label for="assigned" class="form-label" style="font-size: 0.8rem;">Assignation</label> <!-- OPTIMISÉ: Taille réduite -->
                        <select class="form-select form-select-sm" id="assigned" name="assigned"> <!-- OPTIMISÉ: form-select-sm -->
                            <option value="">Toutes</option>
                            <option value="yes" {{ request('assigned') == 'yes' ? 'selected' : '' }}>Assignées</option>
                            <option value="no" {{ request('assigned') == 'no' ? 'selected' : '' }}>Non Assignées</option>
                        </select>
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
            <table class="table table-sm" id="ordersTable"> <!-- OPTIMISÉ: table-sm -->
                <thead>
                    <tr>
                        <th width="40" id="selectAllColumn" style="display: none;"> <!-- OPTIMISÉ: width réduit -->
                            <div class="form-check">
                                <input class="form-check-input form-check-input-sm" type="checkbox" id="selectAll"> <!-- OPTIMISÉ: form-check-input-sm -->
                            </div>
                        </th>
                        <th style="width: 80px;"> <!-- OPTIMISÉ: Largeur fixe réduite -->
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                ID
                                @if(request('sort') === 'id')
                                    <i class="fas fa-sort-{{ request('order') === 'desc' ? 'down' : 'up' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 200px;">Client</th> <!-- OPTIMISÉ: Largeur réduite -->
                        <th style="width: 100px;">Prix Total</th> <!-- OPTIMISÉ: Largeur réduite -->
                        <th style="width: 120px;">Statut</th> <!-- OPTIMISÉ: Largeur réduite -->
                        <th style="width: 100px;">Priorité</th> <!-- OPTIMISÉ: Largeur réduite -->
                        <th style="width: 80px;">Tentatives</th> <!-- OPTIMISÉ: Largeur réduite -->
                        <th style="width: 100px;"> <!-- OPTIMISÉ: Largeur réduite -->
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Date Création
                                @if(request('sort') === 'created_at')
                                    <i class="fas fa-sort-{{ request('order') === 'desc' ? 'down' : 'up' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th style="width: 160px;">Actions</th> <!-- OPTIMISÉ: Largeur réduite -->
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    @forelse($orders as $order)
                        <tr data-order-id="{{ $order->id }}" class="{{ !$order->is_assigned ? 'unassigned-order' : '' }}">
                            <td class="select-column" style="display: none;">
                                <div class="form-check">
                                    <input class="form-check-input form-check-input-sm order-checkbox" 
                                           type="checkbox" 
                                           value="{{ $order->id }}"
                                           {{ $order->is_assigned ? 'disabled' : '' }}> <!-- OPTIMISÉ: form-check-input-sm -->
                                </div>
                            </td>
                            <td>
                                <span class="order-id">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                                {{-- NOUVEAU: Indicateur de doublon --}}
                                @if($order->is_duplicate)
                                    <span class="duplicate-indicator" 
                                          onclick="showDuplicatesModal('{{ $order->customer_phone }}')"
                                          title="Cette commande a des doublons - Cliquez pour voir">
                                        <i class="fas fa-copy"></i>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name">
                                        {{ $order->customer_name ?: 'Non renseigné' }}
                                    </div>
                                    <div class="customer-phone">
                                        <i class="fas fa-phone me-1"></i>{{ $order->customer_phone }}
                                        @if($order->customer_phone_2)
                                            <br><i class="fas fa-phone me-1"></i>{{ $order->customer_phone_2 }}
                                        @endif
                                    </div>
                                    @if($order->customer_address)
                                        <div class="customer-address" title="{{ $order->customer_address }}">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $order->customer_address }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="price-info">
                                    {{ number_format($order->total_price, 3) }} TND
                                </div>
                                @if($order->confirmed_price && $order->confirmed_price != $order->total_price)
                                    <div class="text-success" style="font-size: 0.7rem;"> <!-- OPTIMISÉ: Taille réduite -->
                                        Confirmé: {{ number_format($order->confirmed_price, 3) }} TND
                                    </div>
                                @endif
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
                                        @case('annulée')
                                            <i class="fas fa-times-circle"></i>Annulée
                                            @break
                                        @case('datée')
                                            <i class="fas fa-calendar-alt"></i>Datée
                                            @break
                                        @case('en_route')
                                            <i class="fas fa-shipping-fast"></i>En Route
                                            @break
                                        @case('livrée')
                                            <i class="fas fa-gift"></i>Livrée
                                            @break
                                        @default
                                            {{ ucfirst($order->status) }}
                                    @endswitch
                                </span>
                                @if($order->is_assigned)
                                    <div class="mt-1">
                                        <small class="text-success" style="font-size: 0.7rem;">
                                            <i class="fas fa-user-check me-1"></i>Assignée
                                        </small>
                                    </div>
                                @endif
                                @if($order->confirmiAssignment && !in_array($order->confirmiAssignment->status, ['cancelled']))
                                    <div class="mt-1">
                                        <small class="text-primary" style="font-size: 0.7rem;" title="Gérée par l'équipe Confirmi">
                                            <i class="fas fa-headset me-1"></i>Confirmi
                                        </small>
                                    </div>
                                @endif
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
                                @if($order->daily_attempts_count > 0)
                                    <div class="mt-1">
                                        <small class="text-muted" style="font-size: 0.65rem;"> <!-- OPTIMISÉ: Taille réduite -->
                                            Aujourd'hui: {{ $order->daily_attempts_count }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="date-info">
                                    {{ $order->created_at->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ $order->created_at->format('H:i') }}</small> <!-- OPTIMISÉ: Taille réduite -->
                                </div>
                                @if($order->scheduled_date)
                                    <div class="mt-1">
                                        <small class="text-warning" style="font-size: 0.65rem;"> <!-- OPTIMISÉ: Taille réduite -->
                                            <i class="fas fa-calendar me-1"></i>{{ $order->scheduled_date->format('d/m/Y') }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    {{-- NOUVEAU: Bouton show --}}
                                    <button type="button" 
                                            class="btn btn-action btn-show" 
                                            title="Voir"
                                            onclick="window.open('/admin/orders/{{ $order->id }}', '_blank')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-action btn-edit" 
                                            title="Modifier"
                                            onclick="window.location='/admin/orders/{{ $order->id }}/edit'">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    {{-- NOUVEAU: Bouton duplicates (si applicable) --}}
                                    @if($order->is_duplicate)
                                        <button type="button" 
                                                class="btn btn-action btn-duplicates" 
                                                title="Voir doublons"
                                                onclick="window.open('/admin/duplicates/detail/{{ urlencode($order->customer_phone) }}', '_blank')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    @endif
                                    
                                    <button type="button" 
                                            class="btn btn-action btn-history" 
                                            title="Historique"
                                            onclick="showOrderHistory({{ $order->id }})">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-action btn-delete" 
                                            title="Supprimer"
                                            onclick="confirmDelete({{ $order->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4"> <!-- OPTIMISÉ: Padding réduit -->
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i> <!-- OPTIMISÉ: fa-3x → fa-2x -->
                                    <h6>Aucune commande trouvée</h6> <!-- OPTIMISÉ: h5 → h6 -->
                                    <p style="font-size: 0.85rem;">Aucune commande ne correspond à vos critères de recherche.</p> <!-- OPTIMISÉ: Taille réduite -->
                                    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm mt-2"> <!-- OPTIMISÉ: mt-3 → mt-2 + btn-sm -->
                                        <i class="fas fa-plus me-2"></i>Créer une nouvelle commande
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination corrigée -->
        @if($orders->hasPages())
            <div class="d-flex justify-content-between align-items-center p-2 bg-light"> <!-- OPTIMISÉ: p-3 → p-2 -->
                <div class="text-muted" style="font-size: 0.8rem;"> <!-- OPTIMISÉ: Taille réduite -->
                    Affichage de {{ $orders->firstItem() ?? 0 }} à {{ $orders->lastItem() ?? 0 }} 
                    sur {{ $orders->total() }} résultats
                </div>
                <div>
                    {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- NOUVEAU: Modal pour voir les doublons --}}
<div class="modal fade duplicates-modal" id="duplicatesModal" tabindex="-1" aria-labelledby="duplicatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duplicatesModalLabel">
                    <i class="fas fa-copy me-2"></i>Commandes en doublon
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Client:</strong> <span id="duplicateClientPhone"></span>
                </div>
                <div class="duplicates-list" id="duplicatesList">
                    <!-- Contenu chargé via AJAX -->
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-primary btn-sm" id="btnViewFullDuplicates">
                        <i class="fas fa-external-link-alt me-2"></i>Voir détails complets
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history me-2"></i>Historique de la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="historyContent">
                <div class="text-center py-4">
                    <div class="spinner"></div>
                    <p class="mt-3 text-muted">Chargement de l'historique...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette commande ?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-warning me-2"></i>Cette action est irréversible.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button> <!-- OPTIMISÉ: btn-sm -->
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"> <!-- OPTIMISÉ: btn-sm -->
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let assignmentMode = false;
    let selectedOrders = [];

    // ================================
    // FILTRES AVANCÉS
    // ================================
    $('#toggleAdvancedFilters').on('click', function() {
        const filters = $('#advancedFilters');
        const icon = $(this).find('i');
        
        if (filters.hasClass('show')) {
            filters.removeClass('show');
            icon.removeClass('fa-filter-circle-xmark').addClass('fa-filter');
            $(this).removeClass('btn-primary').addClass('btn-outline-secondary');
        } else {
            filters.addClass('show');
            icon.removeClass('fa-filter').addClass('fa-filter-circle-xmark');
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        }
    });

    // Auto-show advanced filters if any filter is active
    if ($('#status').val() || $('#priority').val() || $('#date_from').val() || $('#date_to').val() || $('#assigned').val()) {
        $('#toggleAdvancedFilters').click();
    }

    // ================================
    // MODE ASSIGNATION
    // ================================
    $('#assignmentModeBtn').on('click', function() {
        assignmentMode = !assignmentMode;
        
        if (assignmentMode) {
            enterAssignmentMode();
        } else {
            exitAssignmentMode();
        }
    });

    function enterAssignmentMode() {
        // Changer le bouton
        $('#assignmentModeBtn').removeClass('btn-success').addClass('btn-danger')
                              .html('<i class="fas fa-times me-2"></i>Quitter Assignation');
        
        // Afficher le panel d'assignation
        $('#assignmentMode').addClass('active');
        
        // Afficher les colonnes de sélection
        $('#selectAllColumn').show();
        $('.select-column').show();
        
        // Masquer les commandes déjà assignées
        $('tr[data-order-id]').each(function() {
            if (!$(this).hasClass('unassigned-order')) {
                $(this).hide();
            }
        });
        
        showNotification('Mode assignation activé - Sélectionnez les commandes non assignées', 'info');
    }

    function exitAssignmentMode() {
        // Réinitialiser le bouton
        $('#assignmentModeBtn').removeClass('btn-danger').addClass('btn-success')
                              .html('<i class="fas fa-user-plus me-2"></i>Mode Assignation');
        
        // Masquer le panel
        $('#assignmentMode').removeClass('active');
        
        // Masquer les colonnes de sélection
        $('#selectAllColumn').hide();
        $('.select-column').hide();
        
        // Réafficher toutes les commandes
        $('tr[data-order-id]').show();
        
        // Déselectionner tout
        $('.order-checkbox').prop('checked', false);
        $('tr').removeClass('selected');
        selectedOrders = [];
        updateSelectedCount();
        
        showNotification('Mode assignation désactivé', 'info');
    }

    // ================================
    // SÉLECTION DES COMMANDES
    // ================================
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.order-checkbox:not(:disabled):visible').prop('checked', isChecked);
        updateSelection();
    });

    $(document).on('change', '.order-checkbox', function() {
        updateSelection();
        updateSelectAllState();
    });

    function updateSelection() {
        selectedOrders = [];
        $('.order-checkbox:checked').each(function() {
            selectedOrders.push($(this).val());
            $(this).closest('tr').addClass('selected');
        });
        
        $('.order-checkbox:not(:checked)').each(function() {
            $(this).closest('tr').removeClass('selected');
        });
        
        updateSelectedCount();
    }

    function updateSelectAllState() {
        const total = $('.order-checkbox:not(:disabled):visible').length;
        const checked = $('.order-checkbox:not(:disabled):visible:checked').length;
        
        $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
        $('#selectAll').prop('checked', checked === total && total > 0);
    }

    function updateSelectedCount() {
        $('#selectedCount').text(selectedOrders.length);
        $('#performAssignment').prop('disabled', selectedOrders.length === 0 || !$('#assignEmployee').val());
    }

    // ================================
    // ASSIGNATION
    // ================================
    $('#assignEmployee').on('change', function() {
        updateSelectedCount();
    });

    $('#performAssignment').on('click', function() {
        const employeeId = $('#assignEmployee').val();
        const employeeName = $('#assignEmployee option:selected').text();
        
        if (!employeeId || selectedOrders.length === 0) {
            showNotification('Veuillez sélectionner un employé et au moins une commande', 'warning');
            return;
        }
        
        if (confirm(`Assigner ${selectedOrders.length} commande(s) à ${employeeName} ?`)) {
            performAssignment(selectedOrders, employeeId);
        }
    });

    function performAssignment(orderIds, employeeId) {
        $.ajax({
            url: '/admin/orders/bulk-assign',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_ids: orderIds,
                employee_id: employeeId
            },
            beforeSend: function() {
                $('#performAssignment').prop('disabled', true)
                                     .html('<i class="fas fa-spinner fa-spin me-2"></i>Assignation...');
            },
            success: function(response) {
                showNotification(response.message || 'Commandes assignées avec succès', 'success');
                
                // Supprimer les lignes assignées
                orderIds.forEach(function(orderId) {
                    $(`tr[data-order-id="${orderId}"]`).fadeOut(500, function() {
                        $(this).remove();
                    });
                });
                
                // Réinitialiser la sélection
                selectedOrders = [];
                updateSelectedCount();
                $('#assignEmployee').val('');
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showNotification(response.message || 'Erreur lors de l\'assignation', 'error');
            },
            complete: function() {
                $('#performAssignment').prop('disabled', false)
                                     .html('<i class="fas fa-check me-2"></i>Assigner (<span id="selectedCount">0</span>)');
            }
        });
    }

    $('#cancelAssignment').on('click', function() {
        exitAssignmentMode();
    });

    // ================================
    // MODE AUTO-ASSIGNATION POUR EMPLOYÉS
    // ================================
    $('#selfAssignmentModeBtn').on('click', function() {
        assignmentMode = !assignmentMode;

        if (assignmentMode) {
            enterSelfAssignmentMode();
        } else {
            exitSelfAssignmentMode();
        }
    });

    function enterSelfAssignmentMode() {
        // Changer le bouton
        $('#selfAssignmentModeBtn').removeClass('btn-success').addClass('btn-danger')
                              .html('<i class="fas fa-times me-2"></i>Quitter Auto-Assignation');

        // Afficher le panel d'auto-assignation
        $('#selfAssignmentMode').addClass('active');

        // Afficher les colonnes de sélection
        $('#selectAllColumn').show();
        $('.select-column').show();

        // Masquer les commandes déjà assignées
        $('tr[data-order-id]').each(function() {
            if (!$(this).hasClass('unassigned-order')) {
                $(this).hide();
            }
        });

        showNotification('Mode auto-assignation activé - Sélectionnez les commandes à vous assigner', 'info');
    }

    function exitSelfAssignmentMode() {
        // Réinitialiser le bouton
        $('#selfAssignmentModeBtn').removeClass('btn-danger').addClass('btn-success')
                              .html('<i class="fas fa-user-check me-2"></i>Auto-Assignation');

        // Masquer le panel
        $('#selfAssignmentMode').removeClass('active');

        // Masquer les colonnes de sélection
        $('#selectAllColumn').hide();
        $('.select-column').hide();

        // Réafficher toutes les commandes
        $('tr[data-order-id]').show();

        // Déselectionner tout
        $('.order-checkbox').prop('checked', false);
        $('tr').removeClass('selected');
        selectedOrders = [];
        updateSelectedCountSelf();

        showNotification('Mode auto-assignation désactivé', 'info');
    }

    function updateSelectedCountSelf() {
        $('#selectedCountSelf').text(selectedOrders.length);
        $('#performSelfAssignment').prop('disabled', selectedOrders.length === 0);
    }

    // Mettre à jour le compteur pour l'auto-assignation
    $(document).on('change', '.order-checkbox', function() {
        updateSelection();
        updateSelectAllState();
        updateSelectedCountSelf();
    });

    $('#performSelfAssignment').on('click', function() {
        if (selectedOrders.length === 0) {
            showNotification('Veuillez sélectionner au moins une commande', 'warning');
            return;
        }

        if (confirm(`Vous assigner ${selectedOrders.length} commande(s) ?`)) {
            performSelfAssignment(selectedOrders);
        }
    });

    function performSelfAssignment(orderIds) {
        $.ajax({
            url: '/admin/orders/bulk-assign',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_ids: orderIds,
                employee_id: '{{ auth("admin")->id() }}' // ID de l'employé connecté
            },
            beforeSend: function() {
                $('#performSelfAssignment').prop('disabled', true)
                                     .html('<i class="fas fa-spinner fa-spin me-2"></i>Assignation...');
            },
            success: function(response) {
                showNotification(response.message || 'Commandes auto-assignées avec succès', 'success');

                // Supprimer les lignes assignées
                orderIds.forEach(function(orderId) {
                    $(`tr[data-order-id="${orderId}"]`).fadeOut(500, function() {
                        $(this).remove();
                    });
                });

                // Réinitialiser la sélection
                selectedOrders = [];
                updateSelectedCountSelf();
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showNotification(response.message || 'Erreur lors de l\'auto-assignation', 'error');
            },
            complete: function() {
                $('#performSelfAssignment').prop('disabled', false)
                                     .html('<i class="fas fa-check me-2"></i>M\'assigner (<span id="selectedCountSelf">0</span>)');
            }
        });
    }

    $('#cancelSelfAssignment').on('click', function() {
        exitSelfAssignmentMode();
    });

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
        showTableLoader();
        
        const formData = new FormData(document.getElementById('filterForm'));
        formData.set('ajax', '1');
        
        $.ajax({
            url: '/admin/orders',
            method: 'GET',
            data: Object.fromEntries(formData),
            success: function(response) {
                if (response.orders) {
                    updateTable(response.orders, query);
                }
            },
            error: function() {
                showNotification('Erreur lors de la recherche', 'error');
            },
            complete: function() {
                hideTableLoader();
            }
        });
    }

    function updateTable(orders, searchQuery = '') {
        // Cette fonction peut être implémentée pour mettre à jour le tableau via AJAX
        // Pour simplifier, on recharge la page avec les nouveaux paramètres
        location.reload();
    }

    // ================================
    // UTILITAIRES
    // ================================
    function showTableLoader() {
        $('#tableLoader').addClass('show');
    }

    function hideTableLoader() {
        $('#tableLoader').removeClass('show');
    }

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

// ================================
// NOUVELLES FONCTIONS POUR DOUBLONS
// ================================

// NOUVEAU: Fonction pour afficher les doublons
function showDuplicatesModal(customerPhone) {
    $('#duplicateClientPhone').text(customerPhone);
    $('#duplicatesModal').modal('show');
    
    // Charger les doublons via AJAX
    $('#duplicatesList').html(`
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2 mb-0 text-muted">Chargement des doublons...</p>
        </div>
    `);
    
    $.ajax({
        url: '/admin/duplicates/history',
        method: 'GET',
        data: { customer_phone: customerPhone },
        success: function(response) {
            if (response.orders && response.orders.length > 0) {
                let content = '';
                response.orders.forEach(function(order) {
                    const statusBadge = getStatusBadge(order.status);
                    const isDuplicate = order.is_duplicate ? '<span class="badge bg-warning ms-2">Doublon</span>' : '';
                    
                    content += `
                        <div class="duplicate-order-item">
                            <div class="duplicate-order-header">
                                <div class="duplicate-order-id">Commande #${order.id}${isDuplicate}</div>
                                <div class="duplicate-order-status">${statusBadge}</div>
                            </div>
                            <div class="duplicate-order-details">
                                <strong>Date:</strong> ${formatDateSimple(order.created_at)} | 
                                <strong>Montant:</strong> ${parseFloat(order.total_price).toFixed(3)} TND | 
                                <strong>Produits:</strong> ${order.items ? order.items.length : 0}
                                ${order.notes ? '<br><strong>Notes:</strong> ' + order.notes.substring(0, 100) + (order.notes.length > 100 ? '...' : '') : ''}
                            </div>
                        </div>
                    `;
                });
                
                $('#duplicatesList').html(content);
                
                // Configurer le bouton pour voir les détails complets
                $('#btnViewFullDuplicates').off('click').on('click', function() {
                    window.open(`/admin/duplicates/detail/${encodeURIComponent(customerPhone)}`, '_blank');
                });
            } else {
                $('#duplicatesList').html(`
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <h6>Aucun doublon trouvé</h6>
                        <p>Aucune commande en doublon pour ce client.</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#duplicatesList').html(`
                <div class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h6>Erreur de chargement</h6>
                    <p>Impossible de charger les doublons.</p>
                </div>
            `);
        }
    });
}

// ================================
// FONCTIONS GLOBALES
// ================================
function showOrderHistory(orderId) {
    $('#historyModal').modal('show');
    
    $.ajax({
        url: `/admin/orders/${orderId}/history-modal`,
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

function confirmDelete(orderId) {
    $('#deleteForm').attr('action', '/admin/orders/' + orderId);
    $('#deleteModal').modal('show');
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

function formatDateSimple(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}
</script>
@endsection