@extends('layouts.admin')

@section('title', 'Retour en Stock')
@section('page-title', 'Interface de Retour en Stock')

@section('css')
<style>
    :root {
        --restock-primary: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --restock-success: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --restock-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --restock-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --restock-info: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --border-radius-xl: 24px;
        --border-radius-2xl: 32px;
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
    }

    /* Container principal */
    .restock-container {
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
    .restock-header {
        background: var(--restock-primary);
        padding: 1.5rem 2rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .restock-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .restock-icon {
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

    .restock-title {
        position: relative;
        z-index: 2;
        color: white;
        flex: 1;
        margin-left: 1.5rem;
    }

    .restock-title h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        margin-bottom: 0.5rem;
    }

    .restock-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    .restock-stats {
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

    /* Content */
    .restock-content {
        padding: 2rem;
        min-height: calc(100vh - 200px);
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
        border-left: 5px solid #10b981;
    }

    .order-card:hover {
        box-shadow: var(--shadow-elevated);
        transform: translateY(-4px);
    }

    .order-card-header {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
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
        background: #f0fdf4;
        border-radius: 8px;
        color: #059669;
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

    /* Section de disponibilité */
    .availability-info {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #bbf7d0;
    }

    .availability-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: #059669;
        font-weight: 600;
    }

    .availability-message {
        background: white;
        padding: 0.75rem;
        border-radius: 8px;
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.4;
        border-left: 4px solid #10b981;
    }

    /* Produits disponibles */
    .products-available {
        background: #f9fafb;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .products-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #374151;
        font-weight: 600;
    }

    .product-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border-left: 4px solid #10b981;
        transition: var(--transition-smooth);
    }

    .product-item:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transform: translateX(2px);
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    .product-details {
        color: #6b7280;
        font-size: 0.8rem;
    }

    .product-stock {
        background: #dcfce7;
        color: #059669;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Action Buttons */
    .order-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
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

    .btn-reactivate { background: var(--restock-primary); color: white; }
    .btn-edit { background: var(--restock-success); color: white; }

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
        color: #10b981;
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
        color: #10b981;
        animation: spin 1s linear infinite;
        margin-bottom: 1.5rem;
    }

    /* Toolbar */
    .restock-toolbar {
        background: white;
        padding: 1rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .refresh-btn {
        background: var(--restock-primary);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .refresh-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
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
    }

    @media (max-width: 768px) {
        .restock-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1.25rem 1.5rem;
        }

        .restock-title {
            margin-left: 0;
        }

        .restock-content {
            padding: 1.5rem;
        }

        .customer-info {
            grid-template-columns: 1fr;
        }

        .order-actions {
            grid-template-columns: 1fr;
        }

        .product-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .product-stock {
            align-self: flex-end;
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

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="restock-container">
    <!-- Header -->
    <div class="restock-header">
        <div class="restock-icon">
            <i class="fas fa-box-open"></i>
        </div>
        
        <div class="restock-title">
            <h1>Retour en Stock</h1>
            <p class="restock-subtitle">Commandes prêtes pour réactivation - Produits de nouveau disponibles</p>
        </div>
        
        <div class="restock-stats">
            <span class="stats-number" id="orders-count">0</span>
            <span class="stats-label">Commandes prêtes</span>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="restock-toolbar">
        <button class="refresh-btn" onclick="refreshOrders()">
            <i class="fas fa-sync-alt"></i>
            <span>Actualiser les commandes</span>
        </button>
    </div>

    <!-- Content -->
    <div class="restock-content">
        <!-- Loading State -->
        <div class="loading-orders fade-in" id="loading-state">
            <i class="fas fa-spinner loading-spinner"></i>
            <h3>Chargement en cours...</h3>
            <p>Recherche des commandes prêtes pour réactivation</p>
        </div>
        
        <!-- No Orders State -->
        <div class="no-orders fade-in" id="no-orders-state" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <h3>Aucune commande prête !</h3>
            <p>Toutes les commandes suspendues ont encore des problèmes de stock ou sont déjà traitées.</p>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid" id="orders-grid" style="display: none;">
            <!-- Les commandes seront chargées ici dynamiquement -->
        </div>
    </div>
</div>

<!-- Modal de réactivation -->
<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactivateModalLabel">
                    <i class="fas fa-play-circle"></i>
                    Réactiver la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Excellent !</strong> Tous les produits de la commande #<span id="reactivate-order-number">0</span> 
                    sont maintenant disponibles en stock et peuvent être traités.
                </div>
                
                <div class="form-group">
                    <label for="reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="reactivate-notes" rows="4" 
                              placeholder="Confirmez la réactivation (ex: Stock reconstitué, tous les produits disponibles, prêt pour traitement, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette commande sera remise dans le circuit normal de traitement.
                    </small>
                </div>
                
                <input type="hidden" id="reactivateOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitReactivate()">
                    <i class="fas fa-play-circle me-2"></i>Réactiver maintenant
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let orders = [];
    
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
        
        loadRestockOrders();
    }
    
    // =========================
    // CHARGEMENT DES COMMANDES
    // =========================
    
    function loadRestockOrders() {
        showLoading();
        
        $.get('/admin/process/restock/orders')
            .done(function(data) {
                console.log('Données reçues:', data);
                
                if (data.hasOrders && data.orders) {
                    if (Array.isArray(data.orders)) {
                        orders = data.orders;
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
            grid.html('<div class="no-orders"><i class="fas fa-inbox"></i><h3>Aucune commande prête</h3><p>Aucune commande suspendue n\'est prête pour réactivation</p></div>');
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
            let productsHtml = '';
            if (order.items && Array.isArray(order.items)) {
                productsHtml = `
                    <div class="products-available">
                        <div class="products-header">
                            <i class="fas fa-check-circle"></i>
                            <span>Produits maintenant disponibles (${order.items.length})</span>
                        </div>
                        ${order.items.map(item => `
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-name">${item.product?.name || 'Produit'}</div>
                                    <div class="product-details">Quantité commandée: ${item.quantity} × ${parseFloat(item.unit_price || 0).toFixed(3)} TND</div>
                                </div>
                                <div class="product-stock">
                                    <i class="fas fa-box"></i>
                                    ${item.product?.stock || 0} en stock
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            
            const card = $(`
                <div class="order-card slide-up" data-order-id="${order.id}">
                    <div class="order-card-header">
                        <div class="order-info">
                            <div class="order-id">
                                <i class="fas fa-box-open"></i>
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
                        
                        <div class="availability-info">
                            <div class="availability-header">
                                <i class="fas fa-check-circle"></i>
                                <span>Prête pour réactivation</span>
                            </div>
                            <div class="availability-message">
                                Tous les produits de cette commande sont maintenant disponibles en stock et actifs. 
                                Cette commande peut être réactivée et remise dans le circuit normal de traitement.
                            </div>
                        </div>
                        
                        ${productsHtml}
                        
                        <div class="order-actions">
                            <button class="action-btn btn-reactivate" onclick="showReactivateModal(${order.id})">
                                <i class="fas fa-play-circle"></i>
                                <span>Réactiver</span>
                            </button>
                            <button class="action-btn btn-edit" onclick="editOrder(${order.id})">
                                <i class="fas fa-edit"></i>
                                <span>Modifier</span>
                            </button>
                        </div>
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
    // ACTIONS
    // =========================
    
    window.refreshOrders = function() {
        loadRestockOrders();
    }
    
    window.showReactivateModal = function(orderId) {
        $('#reactivateOrderId').val(orderId);
        $('#reactivate-order-number').text(String(orderId).padStart(6, '0'));
        $('#reactivate-notes').val('');
        
        $('#reactivateModal').modal('show');
    };
    
    window.editOrder = function(orderId) {
        window.location.href = `/admin/orders/${orderId}/edit`;
    };
    
    window.submitReactivate = function() {
        const orderId = $('#reactivateOrderId').val();
        const notes = $('#reactivate-notes').val().trim();
        
        if (!notes) {
            showNotification('Veuillez saisir une raison pour la réactivation', 'error');
            return;
        }
        
        const submitBtn = $('#reactivateModal .btn-success');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Réactivation...');
        
        $.post(`/admin/process/action/${orderId}`, {
            action: 'reactivate',
            notes: notes,
            queue: 'restock'
        })
        .done(function(response) {
            $('#reactivateModal').modal('hide');
            showNotification('Commande réactivée avec succès !', 'success');
            
            setTimeout(() => {
                refreshOrders();
            }, 1000);
        })
        .fail(function(xhr) {
            let errorMessage = 'Erreur lors de la réactivation';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            showNotification(errorMessage, 'error');
        })
        .always(function() {
            submitBtn.prop('disabled', false).html(originalText);
        });
    };
    
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
    
    // Actualiser les commandes toutes les 30 secondes
    setInterval(() => {
        refreshOrders();
    }, 30000);
});
</script>
@endsection