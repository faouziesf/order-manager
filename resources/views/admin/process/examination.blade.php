@extends('layouts.admin')

@section('title', 'Examen des Commandes')
@section('page-title', 'Interface d\'Examen des Problèmes de Stock')

@section('css')
<style>
    :root {
        --examination-primary: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --examination-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --examination-warning: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --examination-danger: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        --examination-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --border-radius-xl: 24px;
        --border-radius-2xl: 32px;
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 100%);
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    /* Container principal */
    .examination-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-elevated);
        border: 1px solid var(--glass-border);
        margin: 0.5rem;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }

    /* Header */
    .examination-header {
        background: var(--examination-primary);
        padding: 1.5rem 2rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .examination-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .examination-icon {
        color: white;
        font-size: 3rem;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: var(--border-radius-xl);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .examination-title {
        position: relative;
        z-index: 2;
        color: white;
        flex: 1;
        margin-left: 1.5rem;
    }

    .examination-title h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        margin-bottom: 0.5rem;
    }

    .examination-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    .examination-stats {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: var(--border-radius-xl);
        padding: 1rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        text-align: center;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        display: block;
    }

    .stats-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    /* Toolbar avec filtres et actions */
    .examination-toolbar {
        background: white;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
    }

    .toolbar-left {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        flex: 1;
    }

    .toolbar-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    /* Filtres */
    .filters-section {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-control {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: white;
        min-width: 150px;
    }

    .filter-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        outline: none;
    }

    /* Sélection multiple et actions groupées */
    .bulk-actions {
        display: none;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        align-items: center;
        gap: 1rem;
        animation: slideDown 0.3s ease-out;
    }

    .bulk-actions.show {
        display: flex;
    }

    .selected-count {
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        font-size: 0.9rem;
    }

    .bulk-action-btn {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .bulk-action-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }

    /* Toggle vue */
    .view-toggle {
        display: flex;
        background: #f3f4f6;
        border-radius: 10px;
        padding: 0.25rem;
    }

    .view-btn {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #6b7280;
    }

    .view-btn.active {
        background: white;
        color: #f59e0b;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Content */
    .examination-content {
        padding: 2rem;
        min-height: calc(100vh - 300px);
    }

    /* Vue Grid (existante) */
    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
        gap: 1.5rem;
    }

    /* Vue Liste (nouvelle) */
    .orders-list {
        display: none;
        flex-direction: column;
        gap: 1rem;
    }

    .orders-list.active {
        display: flex;
    }

    .orders-grid.active {
        display: grid;
    }

    .list-item {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .list-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .list-item-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .list-item-checkbox {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid #d1d5db;
        cursor: pointer;
    }

    .list-item-checkbox:checked {
        background: #f59e0b;
        border-color: #f59e0b;
    }

    .list-item-info {
        flex: 1;
        display: grid;
        grid-template-columns: 150px 200px 150px 150px 1fr auto;
        gap: 1rem;
        align-items: center;
    }

    .list-item-body {
        padding: 1.5rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .problems-summary {
        background: #fef2f2;
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #ef4444;
    }

    .available-summary {
        background: #f0fdf4;
        border-radius: 8px;
        padding: 1rem;
        border-left: 4px solid #10b981;
    }

    /* Order Cards améliorées */
    .order-card {
        background: white;
        border-radius: var(--border-radius-xl);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: var(--transition-smooth);
        position: relative;
    }

    .order-card:hover {
        box-shadow: var(--shadow-elevated);
        transform: translateY(-4px);
    }

    .order-card-checkbox {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 2px solid #d1d5db;
        background: white;
        cursor: pointer;
        z-index: 10;
    }

    .order-card-checkbox:checked {
        background: #f59e0b;
        border-color: #f59e0b;
    }

    .order-card-header {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .order-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .order-id {
        font-size: 1.25rem;
        font-weight: 700;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .order-status {
        padding: 4px 12px;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-nouvelle { background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%); color: #5b21b6; }
    .status-datée { background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%); color: #92400e; }
    .status-confirmée { background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%); color: #166534; }

    .priority-badge {
        padding: 4px 8px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .priority-normale { background: #f3f4f6; color: #6b7280; }
    .priority-urgente { background: #fef3c7; color: #d97706; }
    .priority-vip { background: #fee2e2; color: #dc2626; }

    .order-card-body {
        padding: 1.5rem;
    }

    .customer-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .info-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 8px;
        color: #6b7280;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .info-content {
        flex: 1;
        min-width: 0;
    }

    .info-label {
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .info-value {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
        word-break: break-word;
    }

    /* Problem Items */
    .problem-items {
        background: #fef2f2;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #fecaca;
    }

    .problem-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #dc2626;
        font-weight: 600;
    }

    .problem-list {
        space-y: 0.75rem;
    }

    .problem-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border-left: 4px solid #ef4444;
    }

    .problem-item-info {
        flex: 1;
    }

    .problem-item-name {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .problem-reasons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .problem-reason {
        background: #fee2e2;
        color: #dc2626;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Available Items */
    .available-items {
        background: #f0fdf4;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #bbf7d0;
    }

    .available-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #059669;
        font-weight: 600;
    }

    .available-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem;
        background: white;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border-left: 4px solid #10b981;
    }

    .available-item-info {
        flex: 1;
    }

    .available-item-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .available-item-details {
        color: #6b7280;
        font-size: 0.8rem;
    }

    .available-item-price {
        font-weight: 600;
        color: #059669;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.9rem;
    }

    /* Action Buttons */
    .order-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 0.75rem 1.25rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        z-index: 0;
    }

    .action-btn:hover::before {
        transform: translateX(0);
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .action-btn span {
        position: relative;
        z-index: 1;
    }

    .btn-split { background: var(--examination-info); color: white; }
    .btn-edit { background: var(--examination-success); color: white; }
    .btn-cancel { background: var(--examination-warning); color: white; }
    .btn-suspend { background: var(--examination-danger); color: white; }

    /* Empty State */
    .no-orders {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    .no-orders i {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.5;
        color: #f59e0b;
    }

    .no-orders h3 {
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
        color: #374151;
    }

    .no-orders p {
        font-size: 1.1rem;
        max-width: 500px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Loading State */
    .loading-orders {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    .loading-spinner {
        font-size: 3rem;
        color: #f59e0b;
        animation: spin 1s linear infinite;
        margin-bottom: 1.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .orders-grid {
            grid-template-columns: 1fr;
        }
        
        .customer-info {
            grid-template-columns: 1fr;
        }
        
        .list-item-info {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .list-item-body {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .examination-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1.25rem 1.5rem;
        }

        .examination-title {
            margin-left: 0;
        }

        .examination-title h1 {
            font-size: 1.75rem;
        }

        .examination-content {
            padding: 1.5rem;
        }

        .examination-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .toolbar-left,
        .toolbar-right {
            width: 100%;
            justify-content: center;
        }

        .filters-section {
            flex-direction: column;
            width: 100%;
        }

        .filter-control {
            min-width: auto;
            width: 100%;
        }

        .order-card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .order-actions {
            flex-direction: column;
        }

        .action-btn {
            flex: none;
            min-width: auto;
        }

        .orders-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .bulk-actions {
            flex-wrap: wrap;
            justify-content: center;
        }
    }

    /* Animations */
    .fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    .slide-up {
        animation: slideUp 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .order-card {
        animation: slideUp 0.3s ease-out;
    }

    .list-item {
        animation: slideUp 0.3s ease-out;
    }
</style>
@endsection

@section('content')
<div class="examination-container">
    <!-- Header -->
    <div class="examination-header">
        <div class="examination-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <div class="examination-title">
            <h1>Interface d'Examen</h1>
            <p class="examination-subtitle">Commandes avec problèmes de stock ou produits inactifs</p>
        </div>
        
        <div class="examination-stats">
            <span class="stats-number" id="orders-count">0</span>
            <span class="stats-label">Commandes à examiner</span>
        </div>
    </div>

    <!-- Toolbar avec filtres et actions -->
    <div class="examination-toolbar">
        <div class="toolbar-left">
            <!-- Actions groupées -->
            <div class="bulk-actions" id="bulk-actions">
                <span class="selected-count" id="selected-count">0 sélectionnée(s)</span>
                <button class="bulk-action-btn" onclick="bulkSplit()">
                    <i class="fas fa-cut"></i>
                    Diviser sélectionnées
                </button>
                <button class="bulk-action-btn" onclick="bulkCancel()">
                    <i class="fas fa-times-circle"></i>
                    Annuler sélectionnées
                </button>
                <button class="bulk-action-btn" onclick="bulkSuspend()">
                    <i class="fas fa-pause-circle"></i>
                    Suspendre sélectionnées
                </button>
                <button class="bulk-action-btn" onclick="clearSelection()">
                    <i class="fas fa-times"></i>
                    Annuler sélection
                </button>
            </div>

            <!-- Filtres -->
            <div class="filters-section">
                <div class="filter-group">
                    <label class="filter-label">Statut</label>
                    <select class="filter-control" id="filter-status">
                        <option value="">Tous les statuts</option>
                        <option value="nouvelle">Nouvelle</option>
                        <option value="confirmée">Confirmée</option>
                        <option value="datée">Datée</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Priorité</label>
                    <select class="filter-control" id="filter-priority">
                        <option value="">Toutes priorités</option>
                        <option value="normale">Normale</option>
                        <option value="urgente">Urgente</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Produit</label>
                    <input type="text" class="filter-control" id="filter-product" placeholder="Nom du produit...">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Date de</label>
                    <input type="date" class="filter-control" id="filter-date-from">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Date à</label>
                    <input type="date" class="filter-control" id="filter-date-to">
                </div>

                <button class="action-btn btn-edit" onclick="applyFilters()" style="margin-top: 1.5rem;">
                    <i class="fas fa-filter"></i>
                    <span>Filtrer</span>
                </button>

                <button class="action-btn btn-cancel" onclick="clearFilters()" style="margin-top: 1.5rem;">
                    <i class="fas fa-times"></i>
                    <span>Effacer</span>
                </button>
            </div>
        </div>

        <div class="toolbar-right">
            <!-- Toggle de vue -->
            <div class="view-toggle">
                <button class="view-btn active" id="grid-view-btn" onclick="switchView('grid')">
                    <i class="fas fa-th"></i>
                    Grille
                </button>
                <button class="view-btn" id="list-view-btn" onclick="switchView('list')">
                    <i class="fas fa-list"></i>
                    Liste
                </button>
            </div>

            <!-- Bouton actualiser -->
            <button class="action-btn btn-edit" onclick="refreshOrders()">
                <i class="fas fa-sync-alt"></i>
                <span>Actualiser</span>
            </button>
        </div>
    </div>

    <!-- Content -->
    <div class="examination-content">
        <!-- Loading State -->
        <div class="loading-orders fade-in" id="loading-state">
            <i class="fas fa-spinner loading-spinner"></i>
            <h3>Chargement en cours...</h3>
            <p>Recherche des commandes avec problèmes de stock</p>
        </div>
        
        <!-- No Orders State -->
        <div class="no-orders fade-in" id="no-orders-state" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <h3>Aucun problème détecté !</h3>
            <p>Toutes les commandes ont des produits en stock et actifs. Excellent travail !</p>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid active" id="orders-grid" style="display: none;">
            <!-- Les commandes seront chargées ici dynamiquement -->
        </div>

        <!-- Orders List -->
        <div class="orders-list" id="orders-list" style="display: none;">
            <!-- La vue liste sera chargée ici dynamiquement -->
        </div>
    </div>
</div>

<!-- Modales -->
@include('admin.process.examination-modals')
@include('admin.process.bulk-modals')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let orders = [];
    let allOrders = []; // Pour la recherche et le filtrage
    let selectedOrders = [];
    let currentView = 'grid';
    let filters = {};
    
    // =========================
    // INITIALISATION
    // =========================
    
    function initialize() {
        if (typeof $ === 'undefined') {
            console.error('jQuery non chargé!');
            return;
        }
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            console.error('Token CSRF non trouvé!');
            return;
        }
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            }
        });
        
        setupEventListeners();
        loadExaminationOrders();
    }
    
    function setupEventListeners() {
        // Filtres en temps réel
        $('#filter-product').on('input', debounce(applyFilters, 500));
        
        // Sélection globale
        $(document).on('change', '.order-card-checkbox, .list-item-checkbox', updateSelection);
    }
    
    // =========================
    // CHARGEMENT DES COMMANDES
    // =========================
    
    function loadExaminationOrders() {
        showLoading();
        
        $.get('/admin/process/examination/orders')
            .done(function(data) {
                console.log('Données reçues:', data);
                
                if (data.hasOrders && data.orders) {
                    if (Array.isArray(data.orders)) {
                        allOrders = data.orders;
                        orders = [...allOrders];
                        applyFilters();
                        updateOrdersCount(data.total || orders.length);
                    } else {
                        console.error('Les données orders ne sont pas un tableau:', typeof data.orders, data.orders);
                        showNotification('Erreur: format de données invalide', 'error');
                        showNoOrders();
                        updateOrdersCount(0);
                    }
                } else {
                    showNoOrders();
                    updateOrdersCount(0);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Erreur lors du chargement:', {xhr, status, error});
                
                let errorMessage = 'Erreur lors du chargement des commandes';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showNotification(errorMessage, 'error');
                showNoOrders();
                updateOrdersCount(0);
            });
    }
    
    // =========================
    // GESTION DES VUES
    // =========================
    
    window.switchView = function(view) {
        currentView = view;
        
        $('.view-btn').removeClass('active');
        $(`#${view}-view-btn`).addClass('active');
        
        if (view === 'grid') {
            $('#orders-grid').addClass('active').show();
            $('#orders-list').removeClass('active').hide();
        } else {
            $('#orders-list').addClass('active').show();
            $('#orders-grid').removeClass('active').hide();
        }
        
        displayOrders(orders);
    }
    
    function displayOrders(ordersToDisplay) {
        if (currentView === 'grid') {
            displayGridView(ordersToDisplay);
        } else {
            displayListView(ordersToDisplay);
        }
        
        showOrdersGrid();
    }
    
    function displayGridView(ordersToDisplay) {
        const grid = $('#orders-grid');
        grid.empty();
        
        if (!Array.isArray(ordersToDisplay)) {
            console.error('displayGridView: orders n\'est pas un tableau:', typeof ordersToDisplay, ordersToDisplay);
            showNotification('Erreur: données invalides reçues du serveur', 'error');
            showNoOrders();
            return;
        }
        
        if (ordersToDisplay.length === 0) {
            grid.html('<div class="no-orders"><i class="fas fa-filter"></i><h3>Aucune commande trouvée</h3><p>Essayez de modifier vos filtres</p></div>');
            return;
        }
        
        ordersToDisplay.forEach((order, index) => {
            try {
                if (!order || !order.id) {
                    console.warn(`Commande ${index} invalide:`, order);
                    return;
                }
                
                const orderCard = createOrderCard(order);
                if (orderCard) {
                    grid.append(orderCard);
                }
            } catch (error) {
                console.error(`Erreur lors de la création de la carte pour la commande ${index}:`, error, order);
            }
        });
    }
    
    function displayListView(ordersToDisplay) {
        const list = $('#orders-list');
        list.empty();
        
        if (!Array.isArray(ordersToDisplay)) {
            console.error('displayListView: orders n\'est pas un tableau:', typeof ordersToDisplay, ordersToDisplay);
            showNotification('Erreur: données invalides reçues du serveur', 'error');
            showNoOrders();
            return;
        }
        
        if (ordersToDisplay.length === 0) {
            list.html('<div class="no-orders"><i class="fas fa-filter"></i><h3>Aucune commande trouvée</h3><p>Essayez de modifier vos filtres</p></div>');
            return;
        }
        
        ordersToDisplay.forEach((order, index) => {
            try {
                if (!order || !order.id) {
                    console.warn(`Commande ${index} invalide:`, order);
                    return;
                }
                
                const listItem = createListItem(order);
                if (listItem) {
                    list.append(listItem);
                }
            } catch (error) {
                console.error(`Erreur lors de la création de l'item liste pour la commande ${index}:`, error, order);
            }
        });
    }
    
    function createListItem(order) {
        try {
            const stockAnalysis = order.stock_analysis || {
                availableItems: [],
                unavailableItems: [],
                issues: [],
                hasIssues: false
            };
            
            const hasAvailableItems = stockAnalysis.availableItems && stockAnalysis.availableItems.length > 0;
            const hasUnavailableItems = stockAnalysis.unavailableItems && stockAnalysis.unavailableItems.length > 0;
            
            const isSelected = selectedOrders.includes(order.id);
            
            const item = $(`
                <div class="list-item" data-order-id="${order.id}">
                    <div class="list-item-header">
                        <input type="checkbox" class="list-item-checkbox" data-order-id="${order.id}" ${isSelected ? 'checked' : ''}>
                        <div class="list-item-info">
                            <div>
                                <strong>#${String(order.id).padStart(6, '0')}</strong>
                            </div>
                            <div>
                                <div class="order-status status-${order.status || 'nouvelle'}">${capitalizeFirst(order.status || 'nouvelle')}</div>
                            </div>
                            <div>
                                <div class="priority-badge priority-${order.priority || 'normale'}">${capitalizeFirst(order.priority || 'normale')}</div>
                            </div>
                            <div>
                                <strong>${order.customer_name || 'Non spécifié'}</strong><br>
                                <small>${order.customer_phone || 'N/A'}</small>
                            </div>
                            <div>
                                ${parseFloat(order.total_price || 0).toFixed(3)} TND<br>
                                <small>${formatDate(order.created_at)}</small>
                            </div>
                            <div class="order-actions">
                                ${hasAvailableItems ? `
                                    <button class="action-btn btn-split" onclick="showSplitModal(${order.id})" style="min-width: auto; padding: 0.5rem;">
                                        <i class="fas fa-cut"></i>
                                    </button>
                                ` : ''}
                                <button class="action-btn btn-edit" onclick="editOrder(${order.id})" style="min-width: auto; padding: 0.5rem;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn btn-cancel" onclick="showCancelModal(${order.id})" style="min-width: auto; padding: 0.5rem;">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="list-item-body">
                        ${hasUnavailableItems ? `
                            <div class="problems-summary">
                                <h6><i class="fas fa-exclamation-triangle"></i> ${stockAnalysis.unavailableItems.length} produit(s) problématique(s)</h6>
                                ${(stockAnalysis.issues || []).slice(0, 3).map(issue => `
                                    <div><strong>${issue.product_name || 'Produit inconnu'}</strong> - ${(issue.reasons || []).join(', ')}</div>
                                `).join('')}
                                ${stockAnalysis.issues && stockAnalysis.issues.length > 3 ? `<div><em>... et ${stockAnalysis.issues.length - 3} autre(s)</em></div>` : ''}
                            </div>
                        ` : ''}
                        ${hasAvailableItems ? `
                            <div class="available-summary">
                                <h6><i class="fas fa-check-circle"></i> ${stockAnalysis.availableItems.length} produit(s) disponible(s)</h6>
                                <p>Peut être divisée pour traitement immédiat</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `);
            
            return item;
        } catch (error) {
            console.error('Erreur dans createListItem:', error, order);
            return null;
        }
    }
    
    // =========================
    // GESTION DES FILTRES
    // =========================
    
    window.applyFilters = function() {
        filters = {
            status: $('#filter-status').val(),
            priority: $('#filter-priority').val(),
            product: $('#filter-product').val().toLowerCase().trim(),
            dateFrom: $('#filter-date-from').val(),
            dateTo: $('#filter-date-to').val()
        };
        
        let filteredOrders = allOrders.filter(order => {
            // Filtre par statut
            if (filters.status && order.status !== filters.status) {
                return false;
            }
            
            // Filtre par priorité
            if (filters.priority && order.priority !== filters.priority) {
                return false;
            }
            
            // Filtre par produit
            if (filters.product) {
                const hasProduct = (order.items || []).some(item => {
                    return item.product && item.product.name.toLowerCase().includes(filters.product);
                });
                if (!hasProduct) return false;
            }
            
            // Filtre par date
            if (filters.dateFrom) {
                const orderDate = new Date(order.created_at);
                const fromDate = new Date(filters.dateFrom);
                if (orderDate < fromDate) return false;
            }
            
            if (filters.dateTo) {
                const orderDate = new Date(order.created_at);
                const toDate = new Date(filters.dateTo);
                toDate.setHours(23, 59, 59, 999); // Fin de journée
                if (orderDate > toDate) return false;
            }
            
            return true;
        });
        
        orders = filteredOrders;
        displayOrders(orders);
        updateOrdersCount(orders.length);
    }
    
    window.clearFilters = function() {
        $('#filter-status').val('');
        $('#filter-priority').val('');
        $('#filter-product').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        
        filters = {};
        orders = [...allOrders];
        displayOrders(orders);
        updateOrdersCount(orders.length);
    }
    
    window.refreshOrders = function() {
        clearSelection();
        loadExaminationOrders();
    }
    
    // =========================
    // SÉLECTION ET ACTIONS GROUPÉES
    // =========================
    
    function updateSelection() {
        selectedOrders = [];
        $('.order-card-checkbox:checked, .list-item-checkbox:checked').each(function() {
            const orderId = parseInt($(this).data('order-id'));
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
            }
        });
        
        updateBulkActions();
    }
    
    function updateBulkActions() {
        const count = selectedOrders.length;
        $('#selected-count').text(`${count} sélectionnée${count > 1 ? 's' : ''}`);
        
        if (count > 0) {
            $('#bulk-actions').addClass('show');
        } else {
            $('#bulk-actions').removeClass('show');
        }
    }
    
    window.clearSelection = function() {
        selectedOrders = [];
        $('.order-card-checkbox, .list-item-checkbox').prop('checked', false);
        updateBulkActions();
    }
    
    window.bulkSplit = function() {
        if (selectedOrders.length === 0) {
            showNotification('Aucune commande sélectionnée', 'warning');
            return;
        }
        
        // Vérifier que toutes les commandes sélectionnées peuvent être divisées
        const eligibleOrders = selectedOrders.filter(orderId => {
            const order = orders.find(o => o.id === orderId);
            return order && order.stock_analysis && order.stock_analysis.availableItems && order.stock_analysis.availableItems.length > 0;
        });
        
        if (eligibleOrders.length === 0) {
            showNotification('Aucune des commandes sélectionnées ne peut être divisée (pas de produits disponibles)', 'warning');
            return;
        }
        
        if (eligibleOrders.length !== selectedOrders.length) {
            showNotification(`Seulement ${eligibleOrders.length} sur ${selectedOrders.length} commandes peuvent être divisées`, 'warning');
        }
        
        $('#bulk-split-count').text(eligibleOrders.length);
        $('#bulk-split-orders').val(eligibleOrders.join(','));
        $('#bulkSplitModal').modal('show');
    }
    
    window.bulkCancel = function() {
        if (selectedOrders.length === 0) {
            showNotification('Aucune commande sélectionnée', 'warning');
            return;
        }
        
        $('#bulk-cancel-count').text(selectedOrders.length);
        $('#bulk-cancel-orders').val(selectedOrders.join(','));
        $('#bulkCancelModal').modal('show');
    }
    
    window.bulkSuspend = function() {
        if (selectedOrders.length === 0) {
            showNotification('Aucune commande sélectionnée', 'warning');
            return;
        }
        
        $('#bulk-suspend-count').text(selectedOrders.length);
        $('#bulk-suspend-orders').val(selectedOrders.join(','));
        $('#bulkSuspendModal').modal('show');
    }
    
    // =========================
    // FONCTIONS RÉUTILISÉES
    // =========================
    
    function createOrderCard(order) {
        try {
            const stockAnalysis = order.stock_analysis || {
                availableItems: [],
                unavailableItems: [],
                issues: [],
                hasIssues: false
            };
            
            const orderItems = order.items || [];
            const hasAvailableItems = stockAnalysis.availableItems && stockAnalysis.availableItems.length > 0;
            const hasUnavailableItems = stockAnalysis.unavailableItems && stockAnalysis.unavailableItems.length > 0;
            const isSelected = selectedOrders.includes(order.id);
            
            let problemItemsHtml = '';
            if (hasUnavailableItems && stockAnalysis.issues) {
                problemItemsHtml = `
                    <div class="problem-items">
                        <div class="problem-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Produits problématiques (${stockAnalysis.unavailableItems.length})</span>
                        </div>
                        <div class="problem-list">
                            ${stockAnalysis.issues.map(issue => `
                                <div class="problem-item">
                                    <div class="problem-item-info">
                                        <div class="problem-item-name">${issue.product_name || 'Produit inconnu'}</div>
                                        <div class="problem-reasons">
                                            ${(issue.reasons || []).map(reason => `
                                                <span class="problem-reason">${reason}</span>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            let availableItemsHtml = '';
            if (hasAvailableItems) {
                availableItemsHtml = `
                    <div class="available-items">
                        <div class="available-header">
                            <i class="fas fa-check-circle"></i>
                            <span>Produits disponibles (${stockAnalysis.availableItems.length})</span>
                        </div>
                        ${stockAnalysis.availableItems.map(availableItem => {
                            const product = orderItems.find(i => i.id === availableItem.id);
                            return `
                                <div class="available-item">
                                    <div class="available-item-info">
                                        <div class="available-item-name">${product?.product?.name || 'Produit'}</div>
                                        <div class="available-item-details">Quantité: ${product?.quantity || 0} × ${parseFloat(product?.unit_price || 0).toFixed(3)} TND</div>
                                    </div>
                                    <div class="available-item-price">${parseFloat(product?.total_price || 0).toFixed(3)} TND</div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;
            }
            
            const actionsHtml = `
                <div class="order-actions">
                    ${hasAvailableItems ? `
                        <button class="action-btn btn-split" onclick="showSplitModal(${order.id})">
                            <i class="fas fa-cut"></i>
                            <span>Diviser</span>
                        </button>
                    ` : ''}
                    <button class="action-btn btn-edit" onclick="editOrder(${order.id})">
                        <i class="fas fa-edit"></i>
                        <span>Modifier</span>
                    </button>
                    <button class="action-btn btn-cancel" onclick="showCancelModal(${order.id})">
                        <i class="fas fa-times-circle"></i>
                        <span>Annuler</span>
                    </button>
                    ${!order.is_suspended ? `
                        <button class="action-btn btn-suspend" onclick="showSuspendModal(${order.id})">
                            <i class="fas fa-pause-circle"></i>
                            <span>Suspendre</span>
                        </button>
                    ` : `
                        <button class="action-btn btn-edit" onclick="showReactivateModal(${order.id})">
                            <i class="fas fa-play-circle"></i>
                            <span>Réactiver</span>
                        </button>
                    `}
                </div>
            `;
            
            const card = $(`
                <div class="order-card" data-order-id="${order.id}">
                    <input type="checkbox" class="order-card-checkbox" data-order-id="${order.id}" ${isSelected ? 'checked' : ''}>
                    <div class="order-card-header">
                        <div class="order-info">
                            <div class="order-id">
                                <i class="fas fa-shopping-basket"></i>
                                #${String(order.id).padStart(6, '0')}
                            </div>
                            <div class="order-status status-${order.status || 'nouvelle'}">${capitalizeFirst(order.status || 'nouvelle')}</div>
                            <div class="priority-badge priority-${order.priority || 'normale'}">${capitalizeFirst(order.priority || 'normale')}</div>
                        </div>
                    </div>
                    <div class="order-card-body">
                        <div class="customer-info">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Client</div>
                                    <div class="info-value">${order.customer_name || 'Non spécifié'}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Téléphone</div>
                                    <div class="info-value">${order.customer_phone || 'Non spécifié'}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Date de création</div>
                                    <div class="info-value">${formatDate(order.created_at)}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-euro-sign"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Total</div>
                                    <div class="info-value">${parseFloat(order.total_price || 0).toFixed(3)} TND</div>
                                </div>
                            </div>
                        </div>
                        
                        ${problemItemsHtml}
                        ${availableItemsHtml}
                        ${actionsHtml}
                    </div>
                </div>
            `);
            
            return card;
        } catch (error) {
            console.error('Erreur dans createOrderCard:', error, order);
            return null;
        }
    }
    
    // =========================
    // GESTION DES ÉTATS
    // =========================
    
    function showLoading() {
        $('#loading-state').show();
        $('#no-orders-state').hide();
        $('#orders-grid').hide();
        $('#orders-list').hide();
    }
    
    function showNoOrders() {
        $('#loading-state').hide();
        $('#no-orders-state').show();
        $('#orders-grid').hide();
        $('#orders-list').hide();
    }
    
    function showOrdersGrid() {
        $('#loading-state').hide();
        $('#no-orders-state').hide();
        if (currentView === 'grid') {
            $('#orders-grid').show();
            $('#orders-list').hide();
        } else {
            $('#orders-grid').hide();
            $('#orders-list').show();
        }
    }
    
    function updateOrdersCount(count) {
        $('#orders-count').text(count);
    }
    
    // =========================
    // ACTIONS INDIVIDUELLES
    // =========================
    
    window.showSplitModal = function(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;
        
        $('#splitOrderId').val(orderId);
        $('#split-order-number').text(String(orderId).padStart(6, '0'));
        $('#split-available-count').text(order.stock_analysis.availableItems.length);
        $('#split-problem-count').text(order.stock_analysis.unavailableItems.length);
        $('#split-notes').val('');
        
        $('#splitModal').modal('show');
    };
    
    window.showCancelModal = function(orderId) {
        $('#cancelOrderId').val(orderId);
        $('#cancel-order-number').text(String(orderId).padStart(6, '0'));
        $('#cancel-notes').val('');
        
        $('#cancelModal').modal('show');
    };
    
    window.showSuspendModal = function(orderId) {
        $('#suspendOrderId').val(orderId);
        $('#suspend-order-number').text(String(orderId).padStart(6, '0'));
        $('#suspend-notes').val('');
        
        $('#suspendModal').modal('show');
    };
    
    window.showReactivateModal = function(orderId) {
        $('#reactivateOrderId').val(orderId);
        $('#reactivate-order-number').text(String(orderId).padStart(6, '0'));
        $('#reactivate-notes').val('');
        
        $('#reactivateModal').modal('show');
    };
    
    window.editOrder = function(orderId) {
        window.location.href = `/admin/orders/${orderId}/edit`;
    };
    
    // =========================
    // SOUMISSION DES ACTIONS
    // =========================
    
    window.submitSplit = function() {
        const orderId = $('#splitOrderId').val();
        const notes = $('#split-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour la division', 'error');
            return;
        }
        
        const submitBtn = $('#splitModal .btn-primary');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Division...');
        
        $.post(`/admin/process/examination/split/${orderId}`, { notes: notes })
            .done(function(response) {
                $('#splitModal').modal('hide');
                showNotification(response.message, 'success');
                
                setTimeout(() => {
                    refreshOrders();
                }, 1000);
            })
            .fail(function(xhr) {
                let errorMessage = 'Erreur lors de la division';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification(errorMessage, 'error');
            })
            .always(function() {
                submitBtn.prop('disabled', false).html(originalText);
            });
    };
    
    window.submitCancel = function() {
        const orderId = $('#cancelOrderId').val();
        const notes = $('#cancel-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour l\'annulation', 'error');
            return;
        }
        
        processExaminationAction(orderId, 'cancel', notes, '#cancelModal');
    };
    
    window.submitSuspend = function() {
        const orderId = $('#suspendOrderId').val();
        const notes = $('#suspend-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour la suspension', 'error');
            return;
        }
        
        processExaminationAction(orderId, 'suspend', notes, '#suspendModal');
    };
    
    window.submitReactivate = function() {
        const orderId = $('#reactivateOrderId').val();
        const notes = $('#reactivate-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour la réactivation', 'error');
            return;
        }
        
        processExaminationAction(orderId, 'reactivate', notes, '#reactivateModal');
    };
    
    function processExaminationAction(orderId, action, notes, modalSelector) {
        const submitBtn = $(modalSelector + ' .btn-primary');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traitement...');
        
        $.post(`/admin/process/examination/action/${orderId}`, {
            action: action,
            notes: notes
        })
        .done(function(response) {
            $(modalSelector).modal('hide');
            showNotification(response.message, 'success');
            
            setTimeout(() => {
                refreshOrders();
            }, 1000);
        })
        .fail(function(xhr) {
            let errorMessage = 'Erreur lors du traitement';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showNotification(errorMessage, 'error');
        })
        .always(function() {
            submitBtn.prop('disabled', false).html(originalText);
        });
    }
    
    // =========================
    // UTILITAIRES
    // =========================
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) {
            return 'Aujourd\'hui';
        } else if (days === 1) {
            return 'Hier';
        } else if (days < 7) {
            return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
        } else {
            return date.toLocaleDateString('fr-FR');
        }
    }
    
    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 100px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        setTimeout(() => {
            alert.fadeOut(() => alert.remove());
        }, 5000);
    }
    
    // =========================
    // INITIALISATION
    // =========================
    
    initialize();
    
    // Actualiser les commandes toutes les 2 minutes
    setInterval(() => {
        if (selectedOrders.length === 0) { // Ne pas actualiser si des commandes sont sélectionnées
            refreshOrders();
        }
    }, 120000);
});
</script>
@endsection