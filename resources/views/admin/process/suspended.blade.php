@extends('layouts.admin')

@section('title', 'Commandes Suspendues')
@section('page-title', 'Gestion des Commandes Suspendues')

@section('css')
<style>
    :root {
        --suspended-primary: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        --suspended-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --suspended-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --suspended-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --suspended-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --border-radius-xl: 24px;
        --border-radius-2xl: 32px;
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    /* Container principal */
    .suspended-container {
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
    .suspended-header {
        background: var(--suspended-primary);
        padding: 1.5rem 2rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .suspended-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .suspended-icon {
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

    .suspended-title {
        position: relative;
        z-index: 2;
        color: white;
        flex: 1;
        margin-left: 1.5rem;
    }

    .suspended-title h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        margin-bottom: 0.5rem;
    }

    .suspended-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    .suspended-stats {
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
    .suspended-toolbar {
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

    /* Filtres optimisés pour desktop */
    .filters-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
        width: 100%;
        max-width: 1200px;
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
        min-width: 180px;
    }

    .filter-control:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        outline: none;
    }

    /* Actions groupées */
    .bulk-actions {
        display: none;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        align-items: center;
        gap: 1rem;
        animation: slideDown 0.3s ease-out;
        margin-bottom: 1rem;
        width: 100%;
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

    /* Content */
    .suspended-content {
        padding: 2rem;
        min-height: calc(100vh - 300px);
    }

    /* Vue en grille optimisée pour desktop */
    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
        gap: 1.5rem;
        max-width: none;
    }

    /* Order Cards */
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
        background: #8b5cf6;
        border-color: #8b5cf6;
    }

    .order-card-header {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
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

    /* Section de suspension */
    .suspension-info {
        background: #fef2f2;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #fecaca;
    }

    .suspension-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: #dc2626;
        font-weight: 600;
    }

    .suspension-reason {
        background: white;
        padding: 0.75rem;
        border-radius: 8px;
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.4;
        border-left: 4px solid #ef4444;
    }

    /* Indicateur de disponibilité des stocks */
    .stock-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .stock-status.available {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .stock-status.unavailable {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    /* Action Buttons */
    .order-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
    }

    .action-btn {
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: center;
        position: relative;
        overflow: hidden;
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

    .btn-reactivate { background: var(--suspended-success); color: white; }
    .btn-edit { background: var(--suspended-info); color: white; }
    .btn-cancel { background: var(--suspended-danger); color: white; }
    .btn-modify { background: var(--suspended-warning); color: white; }

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
        color: #8b5cf6;
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

    /* Responsive */
    @media (max-width: 1400px) {
        .orders-grid {
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        }
    }

    @media (max-width: 1200px) {
        .orders-grid {
            grid-template-columns: 1fr;
        }
        
        .filters-section {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .suspended-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1.25rem 1.5rem;
        }

        .suspended-title {
            margin-left: 0;
        }

        .suspended-content {
            padding: 1.5rem;
        }

        .suspended-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .filters-section {
            grid-template-columns: 1fr;
        }

        .customer-info {
            grid-template-columns: 1fr;
        }

        .order-actions {
            grid-template-columns: 1fr;
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

    /* Loading */
    .loading-orders {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        grid-column: 1 / -1;
    }

    .loading-spinner {
        font-size: 3rem;
        color: #8b5cf6;
        animation: spin 1s linear infinite;
        margin-bottom: 1.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="suspended-container">
    <!-- Header -->
    <div class="suspended-header">
        <div class="suspended-icon">
            <i class="fas fa-pause-circle"></i>
        </div>
        
        <div class="suspended-title">
            <h1>Commandes Suspendues</h1>
            <p class="suspended-subtitle">Gestion et réactivation des commandes suspendues</p>
        </div>
        
        <div class="suspended-stats">
            <span class="stats-number" id="orders-count">0</span>
            <span class="stats-label">Commandes suspendues</span>
        </div>
    </div>

    <!-- Toolbar avec filtres et actions -->
    <div class="suspended-toolbar">
        <!-- Actions groupées -->
        <div class="bulk-actions" id="bulk-actions">
            <span class="selected-count" id="selected-count">0 sélectionnée(s)</span>
            <button class="bulk-action-btn" onclick="bulkReactivate()">
                <i class="fas fa-play-circle"></i>
                Réactiver sélectionnées
            </button>
            <button class="bulk-action-btn" onclick="bulkCancel()">
                <i class="fas fa-times-circle"></i>
                Annuler sélectionnées
            </button>
            <button class="bulk-action-btn" onclick="clearSelection()">
                <i class="fas fa-times"></i>
                Annuler sélection
            </button>
        </div>

        <!-- Filtres -->
        <div class="filters-section">
            <div class="filter-group">
                <label class="filter-label">Recherche</label>
                <input type="text" class="filter-control" id="filter-search" placeholder="ID, nom, téléphone, raison...">
            </div>

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
                <label class="filter-label">Stock</label>
                <select class="filter-control" id="filter-stock">
                    <option value="">Tous</option>
                    <option value="yes">Avec problèmes de stock</option>
                    <option value="no">Sans problèmes de stock</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Date de</label>
                <input type="date" class="filter-control" id="filter-date-from">
            </div>

            <div class="filter-group">
                <label class="filter-label">Date à</label>
                <input type="date" class="filter-control" id="filter-date-to">
            </div>

            <div class="filter-group">
                <label class="filter-label">Tri</label>
                <select class="filter-control" id="filter-sort">
                    <option value="created_at_desc">Plus récentes</option>
                    <option value="created_at_asc">Plus anciennes</option>
                    <option value="customer_name_asc">Nom A-Z</option>
                    <option value="customer_name_desc">Nom Z-A</option>
                </select>
            </div>

            <div class="filter-group" style="display: flex; gap: 0.5rem; align-items: end;">
                <button class="action-btn btn-edit" onclick="applyFilters()" style="min-width: auto; padding: 0.6rem 1rem;">
                    <i class="fas fa-filter"></i>
                    <span>Filtrer</span>
                </button>
                <button class="action-btn btn-cancel" onclick="clearFilters()" style="min-width: auto; padding: 0.6rem 1rem;">
                    <i class="fas fa-times"></i>
                    <span>Effacer</span>
                </button>
                <button class="action-btn btn-reactivate" onclick="refreshOrders()" style="min-width: auto; padding: 0.6rem 1rem;">
                    <i class="fas fa-sync-alt"></i>
                    <span>Actualiser</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="suspended-content">
        <!-- Loading State -->
        <div class="loading-orders fade-in" id="loading-state">
            <i class="fas fa-spinner loading-spinner"></i>
            <h3>Chargement en cours...</h3>
            <p>Recherche des commandes suspendues</p>
        </div>
        
        <!-- No Orders State -->
        <div class="no-orders fade-in" id="no-orders-state" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <h3>Aucune commande suspendue !</h3>
            <p>Toutes les commandes sont actuellement actives. Excellent travail !</p>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid" id="orders-grid" style="display: none;">
            <!-- Les commandes seront chargées ici dynamiquement -->
        </div>
    </div>
</div>

<!-- Modales -->
@include('admin.process.suspended-modals')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let orders = [];
    let allOrders = [];
    let selectedOrders = [];
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
        loadSuspendedOrders();
    }
    
    function setupEventListeners() {
        // Filtres en temps réel
        $('#filter-search').on('input', debounce(applyFilters, 500));
        
        // Sélection globale
        $(document).on('change', '.order-card-checkbox', updateSelection);
    }
    
    // =========================
    // CHARGEMENT DES COMMANDES
    // =========================
    
    function loadSuspendedOrders() {
        showLoading();
        
        const params = new URLSearchParams(filters);
        
        $.get('/admin/process/suspended/orders?' + params.toString())
            .done(function(data) {
                console.log('Données reçues:', data);
                
                if (data.hasOrders && data.orders) {
                    if (Array.isArray(data.orders)) {
                        allOrders = data.orders;
                        orders = [...allOrders];
                        displayOrders(orders);
                        updateOrdersCount(data.total || orders.length);
                        showOrdersGrid();
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
    // AFFICHAGE DES COMMANDES
    // =========================
    
    function displayOrders(ordersToDisplay) {
        const grid = $('#orders-grid');
        grid.empty();
        
        if (!Array.isArray(ordersToDisplay)) {
            console.error('displayOrders: orders n\'est pas un tableau:', typeof ordersToDisplay, ordersToDisplay);
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
    
    function createOrderCard(order) {
        try {
            const isSelected = selectedOrders.includes(order.id);
            const canReactivate = order.can_reactivate || false;
            
            const stockStatusHtml = canReactivate ? `
                <div class="stock-status available">
                    <i class="fas fa-check-circle"></i>
                    Peut être réactivée - Tous les produits sont disponibles
                </div>
            ` : `
                <div class="stock-status unavailable">
                    <i class="fas fa-exclamation-triangle"></i>
                    Problème de stock - Vérification nécessaire
                </div>
            `;
            
            const actionsHtml = `
                <div class="order-actions">
                    ${canReactivate ? `
                        <button class="action-btn btn-reactivate" onclick="showReactivateModal(${order.id})">
                            <i class="fas fa-play-circle"></i>
                            <span>Réactiver</span>
                        </button>
                    ` : ''}
                    <button class="action-btn btn-edit" onclick="editOrder(${order.id})">
                        <i class="fas fa-edit"></i>
                        <span>Modifier</span>
                    </button>
                    <button class="action-btn btn-modify" onclick="showModifySuspensionModal(${order.id})">
                        <i class="fas fa-pen"></i>
                        <span>Modifier raison</span>
                    </button>
                    <button class="action-btn btn-cancel" onclick="showCancelModal(${order.id})">
                        <i class="fas fa-times-circle"></i>
                        <span>Annuler</span>
                    </button>
                </div>
            `;
            
            const card = $(`
                <div class="order-card" data-order-id="${order.id}">
                    <input type="checkbox" class="order-card-checkbox" data-order-id="${order.id}" ${isSelected ? 'checked' : ''}>
                    <div class="order-card-header">
                        <div class="order-info">
                            <div class="order-id">
                                <i class="fas fa-pause-circle"></i>
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
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Articles</div>
                                    <div class="info-value">${order.items_count || 0} produit(s)</div>
                                </div>
                            </div>
                        </div>
                        
                        ${stockStatusHtml}
                        
                        <div class="suspension-info">
                            <div class="suspension-header">
                                <i class="fas fa-pause-circle"></i>
                                <span>Raison de la suspension</span>
                            </div>
                            <div class="suspension-reason">
                                ${order.suspension_reason || 'Aucune raison spécifiée'}
                            </div>
                        </div>
                        
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
    // GESTION DES FILTRES
    // =========================
    
    window.applyFilters = function() {
        filters = {
            search: $('#filter-search').val().trim(),
            status: $('#filter-status').val(),
            priority: $('#filter-priority').val(),
            has_stock_issues: $('#filter-stock').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            sort: $('#filter-sort').val() || 'created_at_desc',
            order: $('#filter-sort').val() && $('#filter-sort').val().includes('_desc') ? 'desc' : 'asc'
        };
        
        loadSuspendedOrders();
    }
    
    window.clearFilters = function() {
        $('#filter-search').val('');
        $('#filter-status').val('');
        $('#filter-priority').val('');
        $('#filter-stock').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('#filter-sort').val('created_at_desc');
        
        filters = {};
        loadSuspendedOrders();
    }
    
    window.refreshOrders = function() {
        clearSelection();
        loadSuspendedOrders();
    }
    
    // =========================
    // SÉLECTION ET ACTIONS GROUPÉES
    // =========================
    
    function updateSelection() {
        selectedOrders = [];
        $('.order-card-checkbox:checked').each(function() {
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
        $('.order-card-checkbox').prop('checked', false);
        updateBulkActions();
    }
    
    window.bulkReactivate = function() {
        if (selectedOrders.length === 0) {
            showNotification('Aucune commande sélectionnée', 'warning');
            return;
        }
        
        // Vérifier que toutes les commandes sélectionnées peuvent être réactivées
        const eligibleOrders = selectedOrders.filter(orderId => {
            const order = orders.find(o => o.id === orderId);
            return order && order.can_reactivate;
        });
        
        if (eligibleOrders.length === 0) {
            showNotification('Aucune des commandes sélectionnées ne peut être réactivée (problèmes de stock)', 'warning');
            return;
        }
        
        if (eligibleOrders.length !== selectedOrders.length) {
            showNotification(`Seulement ${eligibleOrders.length} sur ${selectedOrders.length} commandes peuvent être réactivées`, 'warning');
        }
        
        $('#bulk-reactivate-count').text(eligibleOrders.length);
        $('#bulk-reactivate-orders').val(eligibleOrders.join(','));
        $('#bulkReactivateModal').modal('show');
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
    
    // =========================
    // ACTIONS INDIVIDUELLES
    // =========================
    
    window.showReactivateModal = function(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;
        
        $('#reactivateOrderId').val(orderId);
        $('#reactivate-order-number').text(String(orderId).padStart(6, '0'));
        $('#reactivate-notes').val('');
        
        $('#reactivateModal').modal('show');
    };
    
    window.showCancelModal = function(orderId) {
        $('#cancelOrderId').val(orderId);
        $('#cancel-order-number').text(String(orderId).padStart(6, '0'));
        $('#cancel-notes').val('');
        
        $('#cancelModal').modal('show');
    };
    
    window.showModifySuspensionModal = function(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;
        
        $('#modifyOrderId').val(orderId);
        $('#modify-order-number').text(String(orderId).padStart(6, '0'));
        $('#modify-current-reason').text(order.suspension_reason || 'Aucune raison spécifiée');
        $('#modify-new-reason').val(order.suspension_reason || '');
        $('#modify-notes').val('');
        
        $('#modifySuspensionModal').modal('show');
    };
    
    window.editOrder = function(orderId) {
        window.location.href = `/admin/orders/${orderId}/edit`;
    };
    
    // =========================
    // SOUMISSION DES ACTIONS
    // =========================
    
    window.submitReactivate = function() {
        const orderId = $('#reactivateOrderId').val();
        const notes = $('#reactivate-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour la réactivation', 'error');
            return;
        }
        
        processSuspendedAction(orderId, 'reactivate', notes, '#reactivateModal');
    };
    
    window.submitCancel = function() {
        const orderId = $('#cancelOrderId').val();
        const notes = $('#cancel-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour l\'annulation', 'error');
            return;
        }
        
        processSuspendedAction(orderId, 'cancel', notes, '#cancelModal');
    };
    
    window.submitModifySuspension = function() {
        const orderId = $('#modifyOrderId').val();
        const newReason = $('#modify-new-reason').val().trim();
        const notes = $('#modify-notes').val().trim();
        
        if (!newReason || !notes) {
            showNotification('Veuillez remplir tous les champs', 'error');
            return;
        }
        
        processSuspendedAction(orderId, 'edit_suspension', notes, '#modifySuspensionModal', {
            new_suspension_reason: newReason
        });
    };
    
    function processSuspendedAction(orderId, action, notes, modalSelector, extraData = {}) {
        const submitBtn = $(modalSelector + ' .btn-primary');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traitement...');
        
        const data = {
            action: action,
            notes: notes,
            ...extraData
        };
        
        $.post(`/admin/process/suspended/action/${orderId}`, data)
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
    // GESTION DES ÉTATS
    // =========================
    
    function showLoading() {
        $('#loading-state').show();
        $('#no-orders-state').hide();
        $('#orders-grid').hide();
    }
    
    function showNoOrders() {
        $('#loading-state').hide();
        $('#no-orders-state').show();
        $('#orders-grid').hide();
    }
    
    function showOrdersGrid() {
        $('#loading-state').hide();
        $('#no-orders-state').hide();
        $('#orders-grid').show();
    }
    
    function updateOrdersCount(count) {
        $('#orders-count').text(count);
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
        if (selectedOrders.length === 0) {
            refreshOrders();
        }
    }, 120000);
});
</script>
@endsection