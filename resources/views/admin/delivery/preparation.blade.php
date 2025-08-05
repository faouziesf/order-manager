@extends('layouts.admin')

@section('title', 'Préparation des Livraisons')

@section('content')
<style>
    :root {
        --royal-blue: #1e40af;
        --royal-blue-dark: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --light: #f8fafc;
        --dark: #374151;
        --border: #e5e7eb;
        --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 4px 8px rgba(0, 0, 0, 0.15);
        --radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: #f1f5f9;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .preparation-container {
        padding: 1rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ===== HEADER COMPACT ===== */
    .page-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        color: white;
        box-shadow: var(--shadow-lg);
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-subtitle {
        opacity: 0.9;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }

    /* ===== CARTES COMPACTES ===== */
    .card-compact {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: var(--transition);
    }

    .card-compact:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-1px);
    }

    .card-header-compact {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        color: white;
        padding: 0.75rem 1rem;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: none;
    }

    .card-body-compact {
        padding: 1rem;
    }

    /* ===== FORMULAIRES COMPACTS ===== */
    .form-group-compact {
        margin-bottom: 1rem;
    }

    .form-label-compact {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--dark);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control-compact {
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        border: 1px solid var(--border);
        font-size: 0.875rem;
        transition: var(--transition);
        width: 100%;
    }

    .form-control-compact:focus {
        border-color: var(--royal-blue);
        box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2);
        outline: none;
    }

    .form-select-compact {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border-radius: 6px;
        border: 1px solid var(--border);
        font-size: 0.875rem;
        background: white url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") no-repeat right 0.75rem center/16px 12px;
        width: 100%;
    }

    /* ===== BOUTONS MODERNES ===== */
    .btn-modern {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        text-align: center;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-modern:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-modern:disabled:hover {
        transform: none !important;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        color: white;
    }

    .btn-success-modern {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
    }

    .btn-warning-modern {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        color: white;
    }

    .btn-outline-modern {
        background: transparent;
        color: var(--royal-blue);
        border: 2px solid var(--royal-blue);
    }

    .btn-outline-modern:hover {
        background: var(--royal-blue);
        color: white;
    }

    /* ===== LISTE DES COMMANDES COMPACTE ===== */
    .order-item {
        background: white;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        transition: var(--transition);
        position: relative;
        will-change: transform;
    }

    .order-item:hover {
        box-shadow: var(--shadow);
        transform: translateY(-1px);
    }

    .order-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: all 0.2s ease;
        border-radius: 2px 0 0 2px;
    }

    .order-item.selected {
        border-color: var(--success);
        background: rgba(16, 185, 129, 0.05);
        border-width: 2px;
    }

    .order-item.selected::before {
        background: var(--success);
    }

    .order-item:hover::before {
        background: var(--royal-blue);
        opacity: 0.5;
    }

    .order-item.selected:hover::before {
        background: var(--success);
        opacity: 1;
    }

    .order-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .order-id {
        background: var(--royal-blue);
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .order-amount {
        background: var(--success);
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .order-info {
        font-size: 0.875rem;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .order-details {
        font-size: 0.75rem;
        color: #6b7280;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* ===== ALERTES COMPACTES ===== */
    .alert-compact {
        padding: 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-info-compact {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .alert-warning-compact {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .alert-success-compact {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-compact {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    /* ===== PAGINATION COMPACTE ===== */
    .pagination-compact {
        display: flex;
        justify-content: center;
        gap: 0.25rem;
        margin-top: 1rem;
    }

    .page-btn {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border);
        background: white;
        color: var(--royal-blue);
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.875rem;
        transition: var(--transition);
    }

    .page-btn:hover {
        background: var(--royal-blue);
        color: white;
        text-decoration: none;
    }

    .page-btn.active {
        background: var(--royal-blue);
        color: white;
        border-color: var(--royal-blue);
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===== MODAL MODERNE ===== */
    .modal-content-modern {
        border: none;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
    }

    .modal-header-modern {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-light) 100%);
        color: white;
        border-bottom: none;
        border-radius: var(--radius) var(--radius) 0 0;
        padding: 1rem 1.5rem;
    }

    .modal-body-modern {
        padding: 1.5rem;
    }

    .modal-footer-modern {
        background: #f8fafc;
        border-top: 1px solid var(--border);
        border-radius: 0 0 var(--radius) var(--radius);
        padding: 1rem 1.5rem;
    }

    /* ===== ÉTATS VIDES ===== */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h4 {
        margin-bottom: 0.5rem;
        color: var(--dark);
        font-size: 1.1rem;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    /* ===== LOADING ===== */
    .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid var(--royal-blue);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    .loading-text {
        animation: pulse 1.5s ease-in-out infinite;
        margin-top: 0.5rem;
    }

    /* ===== SÉLECTION ET RÉSUMÉ ===== */
    .config-info {
        background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
        border: 1px solid #93c5fd;
        border-radius: 6px;
        padding: 0.75rem;
        margin-top: 0.75rem;
    }

    .selection-summary {
        background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%);
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        padding: 1rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .selection-summary h6 {
        color: var(--success);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .checkbox-modern {
        width: 18px;
        height: 18px;
        accent-color: var(--success);
    }

    .checkbox-modern:focus {
        outline: 2px solid var(--royal-blue);
        outline-offset: 2px;
    }

    /* ===== BADGES MODERNES ===== */
    .badge-modern {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .badge-primary {
        background: var(--royal-blue);
        color: white;
    }

    .badge-success {
        background: var(--success);
        color: white;
    }

    .badge-warning {
        background: var(--warning);
        color: white;
    }

    .badge-danger {
        background: var(--danger);
        color: white;
    }

    /* ===== TOAST NOTIFICATIONS ===== */
    .toast-notification {
        border-left: 4px solid currentColor;
        padding: 12px 16px;
        font-weight: 500;
        backdrop-filter: blur(10px);
        transition: transform 0.2s ease;
        cursor: pointer;
    }

    .toast-notification:hover {
        transform: translateX(-5px);
    }

    /* ===== TABLES RESPONSIVES ===== */
    .table-responsive-modal {
        font-size: 0.875rem;
    }

    .table-responsive-modal td {
        padding: 8px 12px;
        border-top: 1px solid #e5e7eb;
    }

    .table-responsive-modal td:first-child {
        font-weight: 600;
        color: var(--dark);
        width: 40%;
    }

    /* ===== RESPONSIVE MOBILE ===== */
    @media (max-width: 768px) {
        .preparation-container {
            padding: 0.5rem;
        }

        .page-header {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .page-title {
            font-size: 1.25rem;
        }

        .header-actions {
            flex-direction: column;
        }

        .btn-header {
            justify-content: center;
        }

        .grid-responsive {
            display: block !important;
        }

        .grid-responsive > div {
            margin-bottom: 1rem;
        }

        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .order-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
        }

        .modal-dialog {
            margin: 0.5rem;
        }

        .card-body-compact {
            padding: 0.75rem;
        }

        .toast-notification {
            left: 10px;
            right: 10px;
            min-width: auto;
            max-width: none;
        }
        
        .order-item {
            padding: 12px;
            margin-bottom: 8px;
        }
        
        .selection-summary {
            padding: 12px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .btn-modern {
            padding: 0.6rem;
            font-size: 0.8rem;
        }

        .order-item {
            padding: 0.5rem;
        }

        .form-control-compact,
        .form-select-compact {
            font-size: 16px; /* Évite le zoom sur iOS */
        }
    }

    /* ===== ANIMATIONS ===== */
    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .slide-up {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
</style>

<div class="preparation-container fade-in">
    <!-- Header Principal -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-shipping-fast"></i>
            Préparation des Livraisons
        </h1>
        <p class="page-subtitle">Créer des enlèvements groupés pour vos commandes confirmées</p>
        <div class="header-actions">
            <a href="{{ route('admin.delivery.index') }}" class="btn-header">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn-header">
                <i class="fas fa-cog"></i>
                Configurations
            </a>
            <a href="{{ route('admin.delivery.pickups') }}" class="btn-header">
                <i class="fas fa-warehouse"></i>
                Enlèvements
            </a>
            <a href="{{ route('admin.delivery.test-system') }}" class="btn-header" target="_blank">
                <i class="fas fa-bug"></i>
                Diagnostic
            </a>
        </div>
    </div>

    @if($activeConfigurations->isEmpty())
        <!-- État : Aucune configuration -->
        <div class="empty-state fade-in">
            <i class="fas fa-exclamation-triangle text-warning"></i>
            <h4>Aucune Configuration Active</h4>
            <p>Vous devez configurer et activer au moins un transporteur avant de pouvoir préparer des livraisons.</p>
            
            <div class="alert-warning-compact">
                <i class="fas fa-info-circle"></i>
                Suivez ces étapes pour commencer
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0;">
                <div class="card-compact">
                    <div class="card-body-compact text-center">
                        <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                        <h6>1. Créer</h6>
                        <p class="small text-muted">Créez une configuration</p>
                    </div>
                </div>
                <div class="card-compact">
                    <div class="card-body-compact text-center">
                        <i class="fas fa-wifi fa-2x text-warning mb-2"></i>
                        <h6>2. Tester</h6>
                        <p class="small text-muted">Testez la connexion</p>
                    </div>
                </div>
                <div class="card-compact">
                    <div class="card-body-compact text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h6>3. Activer</h6>
                        <p class="small text-muted">Activez la config</p>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('admin.delivery.configuration.create') }}?carrier=jax_delivery" 
                   class="btn-modern btn-primary-modern">
                    <i class="fas fa-truck"></i>
                    JAX Delivery
                </a>
                <a href="{{ route('admin.delivery.configuration.create') }}?carrier=mes_colis" 
                   class="btn-modern btn-success-modern">
                    <i class="fas fa-shipping-fast"></i>
                    Mes Colis Express
                </a>
            </div>
        </div>
    @else
        <!-- Interface de préparation -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;" class="grid-responsive">
            <!-- Panel de configuration -->
            <div>
                <!-- Configuration transporteur -->
                <div class="card-compact mb-4">
                    <div class="card-header-compact">
                        <i class="fas fa-cog me-2"></i>
                        Configuration
                    </div>
                    <div class="card-body-compact">
                        <div class="form-group-compact">
                            <label class="form-label-compact">
                                Transporteur <span class="text-danger">*</span>
                            </label>
                            <select class="form-select-compact" id="delivery_configuration_id" required>
                                <option value="">Sélectionner un transporteur...</option>
                                @foreach($activeConfigurations as $config)
                                    <option value="{{ $config->id }}" 
                                            data-carrier="{{ $config->carrier_slug }}"
                                            data-name="{{ $config->integration_name }}">
                                        {{ $config->carrier_name }} - {{ $config->integration_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-compact">
                            <label class="form-label-compact">Date d'enlèvement</label>
                            <input type="date" 
                                   class="form-control-compact" 
                                   id="pickup_date" 
                                   value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   min="{{ date('Y-m-d') }}">
                        </div>

                        <div id="configInfo" class="config-info d-none">
                            <small>
                                <strong>Configuration sélectionnée :</strong><br>
                                <span id="configDetails"></span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Résumé sélection -->
                <div class="card-compact">
                    <div class="card-header-compact">
                        <i class="fas fa-check-square me-2"></i>
                        Sélection
                    </div>
                    <div class="card-body-compact">
                        <div id="selectionSummary">
                            <p class="text-muted text-center small">
                                <i class="fas fa-info-circle me-1"></i>
                                Aucune commande sélectionnée
                            </p>
                        </div>
                        
                        <div id="selectionActions" class="d-none">
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <button class="btn-modern btn-success-modern" onclick="createPickup()" id="createPickupBtn">
                                    <i class="fas fa-truck-pickup"></i>
                                    Créer l'Enlèvement
                                </button>
                                <button class="btn-modern btn-outline-modern" onclick="clearSelection()">
                                    <i class="fas fa-times"></i>
                                    Annuler la sélection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des commandes -->
            <div>
                <div class="card-compact">
                    <div class="card-header-compact">
                        <i class="fas fa-box me-2"></i>
                        Commandes Disponibles
                    </div>
                    <div class="card-body-compact">
                        <!-- Filtres et recherche -->
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; margin-bottom: 1rem;">
                            <input type="text" 
                                   class="form-control-compact" 
                                   id="searchOrders" 
                                   placeholder="Rechercher par nom, téléphone ou ID...">
                            <select class="form-select-compact" id="governorateFilter" style="min-width: 150px;">
                                <option value="">Tous gouvernorats</option>
                                @for($i = 1; $i <= 24; $i++)
                                    <option value="{{ $i }}">{{ ['Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan', 'Bizerte', 'Béja', 'Jendouba', 'Le Kef', 'Siliana', 'Kairouan', 'Kasserine', 'Sidi Bouzid', 'Sousse', 'Monastir', 'Mahdia', 'Sfax', 'Gafsa', 'Tozeur', 'Kebili', 'Gabès', 'Medenine', 'Tataouine'][$i-1] ?? "Gouvernorat $i" }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Actions de sélection multiple -->
                        <div id="bulkActions" style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" class="checkbox-modern" id="selectAll" onchange="toggleSelectAll()">
                                <label for="selectAll" class="small" style="margin: 0;">Tout sélectionner</label>
                            </div>
                            <button class="btn-modern btn-outline-modern" onclick="selectAllVisible()" style="font-size: 0.75rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-check-square"></i>
                                Tous visibles
                            </button>
                            <button class="btn-modern btn-outline-modern" onclick="clearSelection()" style="font-size: 0.75rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-times"></i>
                                Aucun
                            </button>
                        </div>

                        <!-- Container des commandes -->
                        <div id="ordersContainer">
                            <div class="empty-state">
                                <i class="fas fa-arrow-up"></i>
                                <p class="small">Sélectionnez une configuration pour voir les commandes</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div id="paginationContainer" class="d-none">
                            <div class="pagination-compact" id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="createPickupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-content-modern">
            <div class="modal-header-modern">
                <h5 class="modal-title">
                    <i class="fas fa-truck-pickup me-2"></i>
                    Confirmer la Création d'Enlèvement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body-modern">
                <div id="pickupConfirmation">
                    <!-- Le contenu sera généré dynamiquement -->
                </div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-modern btn-outline-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Annuler
                </button>
                <button type="button" class="btn-modern btn-success-modern" onclick="confirmCreatePickup()" id="confirmBtn">
                    <i class="fas fa-check"></i>
                    Confirmer la Création
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ===== VARIABLES GLOBALES =====
let selectedOrders = [];
let currentPage = 1;
let ordersData = [];
let isCreatingPickup = false; // Protection contre double soumission

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Preparation page initialized');
    initializeEventListeners();
    checkInitialState();
    
    // Vérifier la présence du token CSRF
    if (!document.querySelector('meta[name="csrf-token"]')) {
        console.error('❌ Token CSRF manquant !');
        showToast('danger', 'Token de sécurité manquant. Veuillez recharger la page.', 10000);
    }
});

function initializeEventListeners() {
    // Configuration change
    const configSelect = document.getElementById('delivery_configuration_id');
    if (configSelect) {
        configSelect.addEventListener('change', function() {
            const configId = this.value;
            if (configId) {
                showConfigInfo(this.options[this.selectedIndex]);
                loadOrders();
            } else {
                hideConfigInfo();
                clearOrders();
            }
        });
    }

    // Search input avec debounce
    const searchInput = document.getElementById('searchOrders');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            if (this.value.length >= 2 || this.value.length === 0) {
                currentPage = 1; // Reset page on search
                loadOrders();
            }
        }, 500));
    }

    // Governorate filter
    const govFilter = document.getElementById('governorateFilter');
    if (govFilter) {
        govFilter.addEventListener('change', function() {
            currentPage = 1; // Reset page on filter
            loadOrders();
        });
    }
}

function checkInitialState() {
    // Vérifier si une configuration est déjà sélectionnée
    const configSelect = document.getElementById('delivery_configuration_id');
    if (configSelect && configSelect.value) {
        showConfigInfo(configSelect.options[configSelect.selectedIndex]);
        loadOrders();
    }
}

// ===== UTILITAIRES =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showToast(type, message, duration = 5000) {
    // Supprimer les toasts existants
    document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `alert-${type}-compact toast-notification`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 500px;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'danger' ? 'exclamation-triangle' : 
                 type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    toast.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; color: inherit; font-size: 18px; cursor: pointer; margin-left: 10px;">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    if (duration > 0) {
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    }
}

// ===== GESTION CONFIG =====
function showConfigInfo(option) {
    const configInfo = document.getElementById('configInfo');
    const configDetails = document.getElementById('configDetails');
    
    if (configInfo && configDetails && option) {
        configDetails.innerHTML = `
            <strong>${option.getAttribute('data-name') || option.text}</strong><br>
            <small class="text-muted">Transporteur: ${option.text.split(' - ')[0]}</small>
        `;
        configInfo.classList.remove('d-none');
        configInfo.classList.add('slide-up');
    }
}

function hideConfigInfo() {
    const configInfo = document.getElementById('configInfo');
    if (configInfo) {
        configInfo.classList.add('d-none');
    }
}

function clearOrders() {
    const container = document.getElementById('ordersContainer');
    if (container) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-arrow-up"></i>
                <p class="small">Sélectionnez une configuration pour voir les commandes</p>
            </div>
        `;
    }
    selectedOrders = [];
    updateSelectionSummary();
}

// ===== CHARGEMENT COMMANDES =====
async function loadOrders() {
    const configId = document.getElementById('delivery_configuration_id')?.value;
    if (!configId) return;

    const container = document.getElementById('ordersContainer');
    if (!container) return;

    // Loading state
    container.innerHTML = `
        <div class="empty-state">
            <div class="loading-spinner"></div>
            <p class="small mt-2 loading-text">Chargement des commandes...</p>
        </div>
    `;

    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 20,
            search: document.getElementById('searchOrders')?.value || '',
            governorate: document.getElementById('governorateFilter')?.value || ''
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            throw new Error('Token CSRF manquant. Veuillez recharger la page.');
        }

        console.log('📡 Chargement commandes...', {
            configId,
            page: currentPage,
            search: document.getElementById('searchOrders')?.value,
            governorate: document.getElementById('governorateFilter')?.value
        });

        const response = await fetch(`{{ route('admin.delivery.preparation.orders') }}?${params}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erreur HTTP:', response.status, errorText);
            throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('📝 Données reçues:', data);

        if (data.success) {
            ordersData = data.orders || [];
            displayOrders(ordersData);
            updatePagination(data.pagination);
            
            console.log(`✅ ${ordersData.length} commandes chargées`);
        } else {
            throw new Error(data.message || 'Erreur de chargement des commandes');
        }
        
    } catch (error) {
        console.error('❌ Erreur chargement commandes:', error);
        container.innerHTML = `
            <div class="empty-state error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Erreur de Chargement</h4>
                <p>${error.message}</p>
                <button class="btn-modern btn-primary-modern" onclick="loadOrders()">
                    <i class="fas fa-redo"></i>
                    Réessayer
                </button>
            </div>
        `;
        showToast('danger', 'Erreur lors du chargement des commandes: ' + error.message);
    }
}

function displayOrders(orders) {
    const container = document.getElementById('ordersContainer');
    if (!container) return;
    
    if (!orders || orders.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h4>Aucune commande disponible</h4>
                <p class="small">Toutes les commandes confirmées ont déjà été expédiées ou sont suspendues.</p>
                <div class="alert-info-compact">
                    <i class="fas fa-info-circle"></i>
                    Les commandes doivent avoir le statut "confirmée" pour être expédiables.
                </div>
            </div>
        `;
        return;
    }

    let html = '';
    
    orders.forEach(order => {
        const isSelected = selectedOrders.includes(order.id);
        const totalPrice = parseFloat(order.total_price || 0);
        const itemsCount = order.items ? order.items.length : 0;
        
        html += `
            <div class="order-item ${isSelected ? 'selected' : ''}" data-order-id="${order.id}">
                <div class="order-header">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" 
                               class="checkbox-modern order-checkbox"
                               id="order_${order.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleOrderSelection(${order.id})">
                        <div>
                            <span class="order-id">#${order.id}</span>
                            <strong style="margin-left: 0.5rem;">${order.customer_name || 'Client'}</strong>
                        </div>
                    </div>
                    <div>
                        <span class="order-amount">${totalPrice.toFixed(2)} TND</span>
                    </div>
                </div>
                
                <div class="order-info">
                    <i class="fas fa-phone" style="width: 14px;"></i>
                    ${order.customer_phone || 'N/A'}${order.customer_phone_2 ? ' / ' + order.customer_phone_2 : ''}
                </div>
                
                <div class="order-info">
                    <i class="fas fa-map-marker-alt" style="width: 14px;"></i>
                    ${order.customer_address || 'Adresse non spécifiée'}, ${order.customer_city || order.region_name || 'Ville inconnue'}
                </div>
                
                <div class="order-details">
                    <span>
                        <i class="fas fa-box"></i>
                        ${itemsCount} produit(s)
                    </span>
                    <span><i class="fas fa-calendar"></i> ${new Date(order.created_at).toLocaleDateString('fr-FR')}</span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    updateSelectAllCheckbox();
}

// ===== SÉLECTION COMMANDES =====
function toggleOrderSelection(orderId) {
    const index = selectedOrders.indexOf(orderId);
    const orderItem = document.querySelector(`[data-order-id="${orderId}"]`);
    
    if (index > -1) {
        selectedOrders.splice(index, 1);
        orderItem?.classList.remove('selected');
    } else {
        selectedOrders.push(orderId);
        orderItem?.classList.add('selected');
    }
    
    updateSelectionSummary();
    updateSelectAllCheckbox();
    
    console.log(`📦 Commande ${orderId} ${index > -1 ? 'désélectionnée' : 'sélectionnée'}. Total: ${selectedOrders.length}`);
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    if (selectAllCheckbox?.checked) {
        // Sélectionner toutes les commandes visibles
        orderCheckboxes.forEach(checkbox => {
            const orderId = parseInt(checkbox.id.replace('order_', ''));
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
                checkbox.checked = true;
                document.querySelector(`[data-order-id="${orderId}"]`)?.classList.add('selected');
            }
        });
    } else {
        // Désélectionner toutes les commandes visibles
        orderCheckboxes.forEach(checkbox => {
            const orderId = parseInt(checkbox.id.replace('order_', ''));
            const index = selectedOrders.indexOf(orderId);
            if (index > -1) {
                selectedOrders.splice(index, 1);
                checkbox.checked = false;
                document.querySelector(`[data-order-id="${orderId}"]`)?.classList.remove('selected');
            }
        });
    }
    
    updateSelectionSummary();
    console.log(`📦 Sélection multiple: ${selectedOrders.length} commandes`);
}

function selectAllVisible() {
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    orderCheckboxes.forEach(checkbox => {
        const orderId = parseInt(checkbox.id.replace('order_', ''));
        if (!selectedOrders.includes(orderId)) {
            selectedOrders.push(orderId);
            checkbox.checked = true;
            document.querySelector(`[data-order-id="${orderId}"]`)?.classList.add('selected');
        }
    });
    updateSelectionSummary();
    updateSelectAllCheckbox();
    console.log(`📦 Toutes visibles sélectionnées: ${selectedOrders.length} commandes`);
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    if (!selectAllCheckbox || orderCheckboxes.length === 0) return;
    
    const checkedCount = Array.from(orderCheckboxes).filter(cb => cb.checked).length;
    
    if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCount === orderCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
}

function updateSelectionSummary() {
    const summaryContainer = document.getElementById('selectionSummary');
    const actionsContainer = document.getElementById('selectionActions');
    
    if (!summaryContainer || !actionsContainer) return;
    
    if (selectedOrders.length === 0) {
        summaryContainer.innerHTML = `
            <p class="text-muted text-center small">
                <i class="fas fa-info-circle me-1"></i>
                Aucune commande sélectionnée
            </p>
        `;
        actionsContainer.classList.add('d-none');
    } else {
        const selectedOrdersData = ordersData.filter(order => selectedOrders.includes(order.id));
        const totalAmount = selectedOrdersData.reduce((sum, order) => sum + parseFloat(order.total_price || 0), 0);
        
        summaryContainer.innerHTML = `
            <div class="selection-summary fade-in">
                <h6><i class="fas fa-check-circle"></i> ${selectedOrders.length} commande(s) sélectionnée(s)</h6>
                <p class="mb-0 small">
                    <strong>Total COD:</strong> ${totalAmount.toFixed(2)} TND
                </p>
            </div>
        `;
        actionsContainer.classList.remove('d-none');
    }
}

function clearSelection() {
    selectedOrders = [];
    updateSelectionSummary();
    
    // Décocher toutes les cases
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Décocher la case "Tout sélectionner"
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    
    // Retirer les styles de sélection
    document.querySelectorAll('.order-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    console.log('🗑️ Sélection effacée');
    showToast('info', 'Sélection effacée', 2000);
}

// ===== CRÉATION PICKUP SIMPLIFIÉE =====
function createPickup() {
    if (isCreatingPickup) {
        showToast('warning', 'Création en cours, veuillez patienter...');
        return;
    }
    
    if (selectedOrders.length === 0) {
        showToast('warning', 'Veuillez sélectionner au moins une commande');
        return;
    }
    
    if (selectedOrders.length > 50) {
        showToast('warning', 'Trop de commandes sélectionnées (maximum 50)');
        return;
    }
    
    const configId = document.getElementById('delivery_configuration_id')?.value;
    if (!configId) {
        showToast('warning', 'Veuillez sélectionner une configuration de transporteur');
        return;
    }
    
    // Préparer le modal de confirmation
    const selectedOrdersData = ordersData.filter(order => selectedOrders.includes(order.id));
    const totalAmount = selectedOrdersData.reduce((sum, order) => sum + parseFloat(order.total_price || 0), 0);
    const configOption = document.getElementById('delivery_configuration_id')?.selectedOptions[0];
    const pickupDate = document.getElementById('pickup_date')?.value;
    
    const confirmationContainer = document.getElementById('pickupConfirmation');
    if (confirmationContainer) {
        confirmationContainer.innerHTML = `
            <div class="alert-info-compact">
                <i class="fas fa-info-circle"></i>
                <strong>Résumé de l'enlèvement à créer :</strong>
            </div>
            
            <div style="margin: 1rem 0;">
                <table class="table table-sm table-responsive-modal">
                    <tbody>
                        <tr>
                            <td><strong>Transporteur :</strong></td>
                            <td>${configOption?.text || 'Non spécifié'}</td>
                        </tr>
                        <tr>
                            <td><strong>Date d'enlèvement :</strong></td>
                            <td>${new Date(pickupDate).toLocaleDateString('fr-FR')}</td>
                        </tr>
                        <tr>
                            <td><strong>Commandes :</strong></td>
                            <td><strong>${selectedOrders.length}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Total COD :</strong></td>
                            <td><strong style="color: var(--success);">${totalAmount.toFixed(2)} TND</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="alert-warning-compact">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention :</strong> Les commandes seront marquées comme <strong>expédiées</strong> et ne pourront plus être modifiées.
            </div>
        `;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('createPickupModal'));
    modal.show();
}

async function confirmCreatePickup() {
    if (isCreatingPickup) {
        console.log('⚠️ Création déjà en cours, abandon');
        return;
    }
    
    isCreatingPickup = true;
    
    const confirmBtn = document.getElementById('confirmBtn');
    const originalText = confirmBtn?.innerHTML;
    
    if (confirmBtn) {
        confirmBtn.innerHTML = '<div class="loading-spinner" style="width: 16px; height: 16px; margin-right: 0.5rem;"></div>Création en cours...';
        confirmBtn.disabled = true;
    }
    
    try {
        const configId = document.getElementById('delivery_configuration_id')?.value;
        const pickupDate = document.getElementById('pickup_date')?.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Validation côté client
        if (!configId) {
            throw new Error('Configuration de transporteur manquante');
        }
        
        if (!csrfToken) {
            throw new Error('Token de sécurité manquant. Veuillez recharger la page.');
        }
        
        if (selectedOrders.length === 0) {
            throw new Error('Aucune commande sélectionnée');
        }
        
        if (selectedOrders.length > 50) {
            throw new Error('Trop de commandes sélectionnées (maximum 50)');
        }
        
        const requestData = {
            delivery_configuration_id: parseInt(configId),
            order_ids: [...selectedOrders], // Copie du tableau
            pickup_date: pickupDate || null
        };
        
        console.log('🚀 Envoi de la requête de création:', requestData);
        
        const response = await fetch('{{ route('admin.delivery.preparation.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('📡 Statut de la réponse:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Réponse d\'erreur:', errorText);
            
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = JSON.parse(errorText);
                errorMessage = errorData.message || errorData.error || errorMessage;
                
                // Gestion des erreurs spécifiques
                if (errorData.errors) {
                    const validationErrors = Object.values(errorData.errors).flat().join(', ');
                    errorMessage += ': ' + validationErrors;
                }
            } catch (e) {
                // Si ce n'est pas du JSON, utiliser le texte brut
                if (errorText.includes('Session expired') || errorText.includes('CSRF') || errorText.includes('419')) {
                    errorMessage = 'Session expirée. Veuillez recharger la page.';
                } else if (errorText.includes('500') || errorText.includes('Internal Server Error')) {
                    errorMessage = 'Erreur serveur. Veuillez réessayer ou contacter le support.';
                }
            }
            
            throw new Error(errorMessage);
        }
        
        const data = await response.json();
        console.log('📝 Données de réponse:', data);
        
        if (data.success) {
            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createPickupModal'));
            modal?.hide();
            
            // Afficher le succès avec détails
            const successMessage = data.message || 'Enlèvement créé avec succès !';
            const details = data.data ? ` (${data.data.shipments_created} expéditions créées)` : '';
            showToast('success', successMessage + details, 6000);
            
            // Nettoyer la sélection
            selectedOrders = [];
            updateSelectionSummary();
            
            // Recharger les commandes après un délai
            setTimeout(() => {
                console.log('🔄 Rechargement des commandes...');
                loadOrders();
            }, 1500);
            
            // Redirection vers la page des enlèvements
            setTimeout(() => {
                console.log('🔄 Redirection vers les enlèvements...');
                window.location.href = '{{ route('admin.delivery.pickups') }}';
            }, 4000);
            
        } else {
            throw new Error(data.message || data.error || 'Échec de la création de l\'enlèvement');
        }
        
    } catch (error) {
        console.error('❌ Erreur création pickup:', error);
        
        let errorMessage = error.message;
        let shouldReload = false;
        
        // Messages d'erreur personnalisés
        if (errorMessage.includes('NetworkError') || errorMessage.includes('fetch')) {
            errorMessage = 'Erreur de connexion. Vérifiez votre connexion internet et réessayez.';
        } else if (errorMessage.includes('CSRF') || errorMessage.includes('Session') || errorMessage.includes('419')) {
            errorMessage = 'Session expirée. La page va se recharger automatiquement.';
            shouldReload = true;
        } else if (errorMessage.includes('déjà été expédiées') || errorMessage.includes('Integrity constraint') || errorMessage.includes('23000')) {
            errorMessage = 'Certaines commandes ont déjà été expédiées par un autre utilisateur. La page va se recharger.';
            shouldReload = true;
        } else if (errorMessage.includes('500') || errorMessage.includes('Internal Server Error')) {
            errorMessage = 'Erreur serveur. Veuillez réessayer dans quelques instants ou contacter le support technique.';
        }
        
        showToast('danger', errorMessage, shouldReload ? 8000 : 6000);
        
        // Recharger si nécessaire
        if (shouldReload) {
            setTimeout(() => window.location.reload(), 3000);
        }
        
    } finally {
        isCreatingPickup = false;
        
        if (confirmBtn) {
            confirmBtn.innerHTML = originalText || '<i class="fas fa-check"></i> Confirmer la Création';
            confirmBtn.disabled = false;
        }
    }
}

// ===== PAGINATION =====
function updatePagination(pagination) {
    const container = document.getElementById('paginationContainer');
    const paginationList = document.getElementById('pagination');
    
    if (!container || !paginationList || !pagination) return;
    
    if (pagination.last_page <= 1) {
        container.classList.add('d-none');
        return;
    }
    
    container.classList.remove('d-none');
    
    let html = '';
    
    // Bouton précédent
    html += `
        <button class="page-btn" 
                onclick="changePage(${pagination.current_page - 1})" 
                ${pagination.current_page <= 1 ? 'disabled' : ''}
                title="Page précédente">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Pages
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
    
    if (startPage > 1) {
        html += `<button class="page-btn" onclick="changePage(1)" title="Première page">1</button>`;
        if (startPage > 2) {
            html += `<button class="page-btn" disabled>...</button>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === pagination.current_page ? 'active' : ''}" 
                         onclick="changePage(${i})" title="Page ${i}">${i}</button>`;
    }
    
    if (endPage < pagination.last_page) {
        if (endPage < pagination.last_page - 1) {
            html += `<button class="page-btn" disabled>...</button>`;
        }
        html += `<button class="page-btn" onclick="changePage(${pagination.last_page})" title="Dernière page">${pagination.last_page}</button>`;
    }
    
    // Bouton suivant
    html += `
        <button class="page-btn" 
                onclick="changePage(${pagination.current_page + 1})" 
                ${pagination.current_page >= pagination.last_page ? 'disabled' : ''}
                title="Page suivante">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    paginationList.innerHTML = html;
}

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    console.log(`📄 Changement vers page ${page}`);
    loadOrders();
}

// ===== DEBUG ET DIAGNOSTICS =====
window.deliveryDebug = {
    selectedOrders,
    ordersData,
    currentPage,
    isCreatingPickup,
    
    // Fonction pour tester la connexion
    testConnection: async function() {
        try {
            const response = await fetch('{{ route('admin.delivery.test-system') }}');
            const data = await response.json();
            console.log('🔍 Diagnostic système:', data);
            return data;
        } catch (error) {
            console.error('❌ Erreur diagnostic:', error);
            return { error: error.message };
        }
    },
    
    // Fonction pour forcer le rechargement
    forceReload: function() {
        loadOrders();
    },
    
    // Fonction pour nettoyer l'état
    reset: function() {
        selectedOrders = [];
        currentPage = 1;
        ordersData = [];
        isCreatingPickup = false;
        updateSelectionSummary();
        clearOrders();
        console.log('🔄 État remis à zéro');
    }
};

console.log('✅ Scripts de préparation chargés et simplifiés');
console.log('🔧 Mode debug disponible via window.deliveryDebug');
</script>
@endsection