@extends('layouts.admin')

@section('title', 'Créer une Commande')
@section('page-title', 'Créer une Nouvelle Commande')

@section('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --doublons-gradient: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-elevated: 0 8px 25px -8px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --cart-width-expanded: 400px;
        --cart-width-collapsed: 230px;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
        margin: 0;
    }

    .page-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-elevated);
        margin: 1rem;
        overflow: hidden;
        min-height: calc(100vh - 2rem);
    }

    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .breadcrumb {
        background: transparent;
        margin: 0.5rem 0 0 0;
        padding: 0;
    }

    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: white;
    }

    /* Layout Principal */
    .main-content {
        display: grid;
        grid-template-columns: 1fr auto; 
        gap: 2rem;
        padding: 2rem;
    }

    @media (max-width: 1200px) {
        .main-content {
            grid-template-columns: 1fr;
        }
        .cart-section {
            width: auto !important;
            position: static; 
            margin-top: 1rem;
        }
    }

    /* Alerte de doublons */
    .duplicate-alert {
        margin: 1rem 0;
        padding: 1rem;
        border-radius: 8px;
        border: 2px solid;
        display: none;
        animation: slideIn 0.3s ease-out;
    }

    .duplicate-alert.warning {
        background: rgba(245, 158, 11, 0.1);
        border-color: #f59e0b;
        color: #92400e;
    }

    .duplicate-alert.danger {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        color: #dc2626;
    }

    .duplicate-alert.success {
        background: rgba(16, 185, 129, 0.1);
        border-color: #10b981;
        color: #059669;
    }

    .duplicate-alert-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .duplicate-alert-content {
        font-size: 0.875rem;
        line-height: 1.4;
    }

    .duplicate-alert-actions {
        margin-top: 0.75rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .duplicate-alert-actions .btn {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }

    /* Formulaire Client */
    .customer-form {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-header h3 { margin: 0; font-size: 1.125rem; font-weight: 600; color: #374151; }
    .form-header .icon { width: 32px; height: 32px; background: var(--primary-gradient); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.875rem; }
    .form-body { padding: 1.5rem; }
    .form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
    .form-row.cols-1 { grid-template-columns: 1fr; }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-group { display: flex; flex-direction: column; position: relative; }
    .form-label { font-weight: 600; color: #374151; margin-bottom: 0.375rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.375rem; }
    .form-label .required { color: #ef4444; font-size: 0.75rem; }
    .form-control { border: 2px solid #e5e7eb; border-radius: 8px; padding: 0.75rem; transition: var(--transition); font-size: 0.875rem; background: #fafafa; font-family: inherit; }
    .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); background: white; outline: none; }
    .form-control:invalid { border-color: #ef4444; }
    .form-control.has-duplicates { border-color: #f59e0b; background: rgba(245, 158, 11, 0.05); }
    .form-select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em; padding-right: 2.5rem; }

    /* Indicateur de vérification en temps réel */
    .phone-validation-indicator {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.875rem;
        display: none;
    }

    .phone-validation-indicator.checking {
        display: block;
        color: #6b7280;
    }

    .phone-validation-indicator.has-duplicates {
        display: block;
        color: #f59e0b;
    }

    .phone-validation-indicator.clean {
        display: block;
        color: #10b981;
    }

    /* Section Panier */
    .cart-section {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        height: fit-content;
        position: sticky;
        top: 1rem;
        width: var(--cart-width-expanded);
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .cart-section.cart-content-collapsed {
        width: var(--cart-width-collapsed);
    }

    .cart-header {
        background: var(--success-gradient);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        overflow: hidden;
    }

    .cart-header h3 { margin: 0; font-size: 1.125rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; white-space: nowrap; }
    .cart-toggle-btn { background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; padding: 0.25rem; line-height: 1; border-radius: 4px; }
    .cart-toggle-btn:hover { background: rgba(255,255,255,0.1); }

    .cart-body { max-height: 500px; overflow-y: auto; }
    .product-search { padding: 1.25rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
    .search-input-group { position: relative; }
    .search-input-group input { padding-left: 2.5rem; background: white; }
    .search-input-group .search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 0.875rem; }
    .product-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 1000; max-height: 200px; overflow-y: auto; }
    .suggestion-item { padding: 0.75rem; cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: var(--transition); display: flex; justify-content: space-between; align-items: center; }
    .suggestion-item:hover { background: #f3f4f6; }
    .suggestion-item:last-child { border-bottom: none; }
    .cart-items { padding: 1rem; min-height: 150px; }
    .cart-items.is-empty .cart-empty { display: block; }
    .cart-items:not(.is-empty) .cart-empty { display: none; }
    .cart-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #f9fafb; border-radius: 8px; margin-bottom: 0.75rem; border: 1px solid #e5e7eb; transition: var(--transition); }
    .cart-item:hover { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    .item-info { flex: 1; }
    .item-name { font-weight: 600; color: #374151; font-size: 0.875rem; margin-bottom: 0.25rem; }
    .item-price { color: #6b7280; font-size: 0.75rem; font-family: monospace; }
    .quantity-control { display: flex; align-items: center; gap: 0.5rem; background: white; border-radius: 6px; padding: 0.25rem; }
    .quantity-btn { width: 28px; height: 28px; border: none; background: #f3f4f6; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); color: #6b7280; font-size: 0.75rem; }
    .quantity-btn:hover { background: #e5e7eb; color: #374151; }
    .quantity-input { width: 40px; text-align: center; border: none; background: transparent; font-weight: 600; color: #374151; font-size: 0.875rem; }
    .remove-item { background: #fef2f2; color: #ef4444; border: none; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); font-size: 0.75rem; }
    .remove-item:hover { background: #fee2e2; }
    .cart-empty { text-align: center; padding: 2rem 1rem; color: #6b7280; }
    .cart-empty i { font-size: 2rem; margin-bottom: 0.75rem; opacity: 0.5; }
    .cart-summary { padding: 1.25rem; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.875rem; }
    .summary-row:last-child { margin-bottom: 0; font-weight: 700; font-size: 1rem; color: #374151; padding-top: 0.75rem; border-top: 1px solid #e5e7eb; }
    .summary-label { color: #6b7280; font-weight: 500; }
    .summary-value { font-family: monospace; font-weight: 600; color: #374151; }

    /* Contrôles de commande */
    .order-controls { padding: 1.5rem; background: white; border-top: 1px solid #e5e7eb; border-radius: 0 0 var(--border-radius) var(--border-radius); }
    .cart-body:visible ~ .order-controls, .cart-summary:visible ~ .order-controls { border-radius: 0; }
    .control-group { margin-bottom: 1.25rem; }
    .control-group:last-child { margin-bottom: 0; }
    .control-label { font-weight: 600; color: #374151; margin-bottom: 0.5rem; display: block; font-size: 0.875rem; }
    .status-badges, .priority-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .status-badge, .priority-badge { padding: 0.5rem 0.75rem; border-radius: 20px; border: 2px solid transparent; cursor: pointer; transition: var(--transition); font-weight: 500; font-size: 0.75rem; position: relative; overflow: hidden; }
    .status-badge.active, .priority-badge.active { color: white; transform: scale(1.05); }
    .status-nouvelle { background: #f3f4f6; color: #6b7280; }
    .status-nouvelle.active { background: var(--primary-gradient); }
    .status-confirmée { background: #ecfdf5; color: #059669; }
    .status-confirmée.active { background: var(--success-gradient); }
    .priority-normale { background: #f3f4f6; color: #6b7280; }
    .priority-normale.active { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); }
    .priority-urgente { background: #fef3c7; color: #d97706; }
    .priority-urgente.active { background: var(--warning-gradient); }
    .priority-vip { background: #fee2e2; color: #dc2626; }
    .priority-vip.active { background: var(--danger-gradient); }
    .priority-doublons { background: rgba(212, 161, 71, 0.1); color: #d4a147; }
    .priority-doublons.active { background: var(--doublons-gradient); }

    .action-buttons { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
    .btn-save { flex: 1; background: var(--success-gradient); color: white; border: none; border-radius: 8px; padding: 0.875rem 1.5rem; font-weight: 600; font-size: 0.875rem; cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
    .btn-save:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-cancel { background: #f3f4f6; color: #6b7280; border: none; border-radius: 8px; padding: 0.875rem 1.5rem; font-weight: 600; cursor: pointer; transition: var(--transition); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.875rem; }
    .btn-cancel:hover { background: #e5e7eb; color: #374151; }

    .btn-primary { background: var(--primary-gradient); color: white; border: none; border-radius: 6px; padding: 0.375rem 0.75rem; font-weight: 500; cursor: pointer; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; }
    .btn-outline { background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 6px; padding: 0.375rem 0.75rem; font-weight: 500; cursor: pointer; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; }

    /* Styles for controls when cart is collapsed */
    .cart-section.cart-content-collapsed .order-controls {
        padding: 1rem;
    }
    .cart-section.cart-content-collapsed .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    .cart-section.cart-content-collapsed .action-buttons .btn-save,
    .cart-section.cart-content-collapsed .action-buttons .btn-cancel {
        width: 100%;
    }

    /* Animations */
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .cart-item { animation: slideIn 0.3s ease-out; }
    .product-suggestions { animation: slideIn 0.2s ease-out; }
    .checking { animation: pulse 1.5s infinite; }

    /* Loading state */
    .loading { position: relative; pointer-events: none; opacity: 0.7; }
    .loading::after { content: ''; position: absolute; top: 50%; left: 50%; width: 16px; height: 16px; margin: -8px 0 0 -8px; border: 2px solid transparent; border-top: 2px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    /* Responsive */
    @media (max-width: 768px) {
        .main-content { padding: 1rem; gap: 1rem; }
        .page-header { padding: 1.25rem 1.5rem; }
        .form-body { padding: 1.25rem; }
        .form-row.cols-2 { grid-template-columns: 1fr; }
        .action-buttons,
        .cart-section.cart-content-collapsed .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }
        .cart-section.cart-content-collapsed .action-buttons .btn-save,
        .cart-section.cart-content-collapsed .action-buttons .btn-cancel,
        .action-buttons .btn-save,
        .action-buttons .btn-cancel {
             width: 100%;
        }
    }

    /* Checkbox styles */
    .form-check-input {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i>Créer une Nouvelle Commande</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Commandes</a></li>
                <li class="breadcrumb-item active">Créer</li>
            </ol>
        </nav>
    </div>

    <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        <div class="main-content">
            <div class="customer-form">
                <div class="form-header">
                    <div class="icon"><i class="fas fa-user"></i></div>
                    <h3>Informations Client</h3>
                </div>
                <div class="form-body">
                    <div class="form-row cols-1">
                        <div class="form-group">
                            <label for="customer_name" class="form-label"><i class="fas fa-user"></i> Nom Complet</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" placeholder="Nom et prénom du client" autocomplete="name">
                            @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label for="customer_phone" class="form-label"><i class="fas fa-phone"></i> Téléphone Principal <span class="required">*</span></label>
                            <div style="position: relative;">
                                <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Ex: +216 XX XXX XXX" required autocomplete="tel">
                                <div class="phone-validation-indicator" id="phone-indicator">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <!-- Alerte de doublons -->
                            <div class="duplicate-alert" id="duplicate-alert">
                                <div class="duplicate-alert-header">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span id="duplicate-alert-title">Doublons détectés</span>
                                </div>
                                <div class="duplicate-alert-content" id="duplicate-alert-content">
                                    <!-- Contenu dynamique -->
                                </div>
                                <div class="duplicate-alert-actions">
                                    <button type="button" class="btn btn-primary" id="view-duplicates-btn">
                                        <i class="fas fa-eye"></i>
                                        Voir les détails
                                    </button>
                                    <button type="button" class="btn btn-outline" onclick="dismissDuplicateAlert()">
                                        <i class="fas fa-times"></i>
                                        Ignorer
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="customer_phone_2" class="form-label"><i class="fas fa-phone-alt"></i> Téléphone Secondaire</label>
                            <div style="position: relative;">
                                <input type="text" class="form-control @error('customer_phone_2') is-invalid @enderror" id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2') }}" placeholder="Téléphone alternatif" autocomplete="tel">
                                <div class="phone-validation-indicator" id="phone2-indicator">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            @error('customer_phone_2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label for="customer_governorate" class="form-label"><i class="fas fa-map-marked-alt"></i> Gouvernorat</label>
                            <select class="form-select @error('customer_governorate') is-invalid @enderror" id="customer_governorate" name="customer_governorate">
                                <option value="">Choisir un gouvernorat</option>
                                @if (isset($regions))
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('customer_governorate') == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="customer_city" class="form-label"><i class="fas fa-city"></i> Ville</label>
                            <select class="form-select @error('customer_city') is-invalid @enderror" id="customer_city" name="customer_city"><option value="">Choisir une ville</option></select>
                            @error('customer_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row cols-1">
                        <div class="form-group">
                            <label for="customer_address" class="form-label"><i class="fas fa-map-marker-alt"></i> Adresse Complète</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" id="customer_address" name="customer_address" rows="3" placeholder="Adresse détaillée du client" autocomplete="street-address">{{ old('customer_address') }}</textarea>
                            @error('customer_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row cols-1">
                        <div class="form-group">
                            <label for="notes" class="form-label"><i class="fas fa-sticky-note"></i> Commentaires</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Notes supplémentaires sur la commande">{{ old('notes') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="cart-section">
                <div class="cart-header">
                    <h3>
                        <i class="fas fa-shopping-cart"></i>
                        <span>Panier </span>(<span id="cart-count">0</span>)
                    </h3>
                    <button type="button" class="cart-toggle-btn" id="cart-toggle-btn" aria-label="Réduire le panier" title="Réduire/Agrandir le panier">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
                <div class="cart-body">
                    <div class="product-search">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" id="product-search" placeholder="Rechercher un produit..." autocomplete="off">
                            <div class="product-suggestions" id="product-suggestions" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="cart-items" id="cart-items">
                        <div class="cart-empty" id="cart-empty">
                            <i class="fas fa-shopping-basket"></i>
                            <h5>Panier vide</h5>
                            <p>Recherchez et ajoutez des produits</p>
                        </div>
                    </div>
                </div>
                <div class="cart-summary" id="cart-summary" style="display: none;">
                    <div class="summary-row"><span class="summary-label">Sous-total:</span><span class="summary-value" id="subtotal">0.000 TND</span></div>
                    <div class="summary-row"><span class="summary-label">Frais de livraison:</span><span class="summary-value" id="shipping-cost">0.000 TND</span></div>
                    <div class="summary-row"><span class="summary-label">Total:</span><span class="summary-value" id="total">0.000 TND</span></div>
                </div>
                <div class="order-controls">
                    <div class="control-group">
                        <label class="control-label">Statut de la commande</label>
                        <div class="status-badges">
                            <div class="status-badge status-nouvelle active" data-status="nouvelle"><i class="fas fa-circle"></i> Nouvelle</div>
                            <div class="status-badge status-confirmée" data-status="confirmée"><i class="fas fa-check-circle"></i> Confirmée</div>
                        </div>
                        <input type="hidden" name="status" id="status" value="nouvelle">
                    </div>
                    <div class="control-group">
                        <label class="control-label">Priorité</label>
                        <div class="priority-badges">
                            <div class="priority-badge priority-normale active" data-priority="normale"><i class="fas fa-minus"></i> Normale</div>
                            <div class="priority-badge priority-urgente" data-priority="urgente"><i class="fas fa-exclamation"></i> Urgente</div>
                            <div class="priority-badge priority-vip" data-priority="vip"><i class="fas fa-crown"></i> VIP</div>
                        </div>
                        <input type="hidden" name="priority" id="priority" value="normale">
                    </div>
                    <div class="control-group">
                        <label for="employee_id" class="control-label"><i class="fas fa-user-tie"></i> Assigner à un employé</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">Non assigné</option>
                            @if (Auth::guard('admin')->user()->employees()->where('is_active', true)->count() > 0)
                                @foreach (Auth::guard('admin')->user()->employees()->where('is_active', true)->get() as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="action-buttons">
                        <a href="{{ route('admin.orders.index') }}" class="btn-cancel"><i class="fas fa-times"></i> Annuler</a>
                        <button type="submit" class="btn-save" id="save-btn"><i class="fas fa-save"></i> Créer la Commande</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="cart-data" style="display: none;"></div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let searchTimeout;
    let phoneCheckTimeout;
    let phone2CheckTimeout;

    const $cartSection = $('.cart-section'); 
    const $cartBody = $('.cart-body');
    const $cartSummary = $('#cart-summary');
    const $cartItemsContainer = $('#cart-items');
    const $cartEmptyMessage = $('#cart-empty');
    const $cartCount = $('#cart-count');
    const $orderControls = $('.order-controls');
    const $toggleCartBtn = $('#cart-toggle-btn');

    // =========================
    // VÉRIFICATION DES DOUBLONS EN TEMPS RÉEL
    // =========================
    $('#customer_phone').on('input', function() {
        const phone = $(this).val().trim();
        clearTimeout(phoneCheckTimeout);
        
        if (phone.length >= 8) {
            phoneCheckTimeout = setTimeout(() => {
                checkPhoneForDuplicates(phone, 'customer_phone');
            }, 500);
        } else {
            resetPhoneValidation('customer_phone');
        }
    });

    $('#customer_phone_2').on('input', function() {
        const phone = $(this).val().trim();
        clearTimeout(phone2CheckTimeout);
        
        if (phone.length >= 8) {
            phone2CheckTimeout = setTimeout(() => {
                checkPhoneForDuplicates(phone, 'customer_phone_2');
            }, 500);
        } else {
            resetPhoneValidation('customer_phone_2');
        }
    });

    function checkPhoneForDuplicates(phone, fieldType) {
        const indicator = fieldType === 'customer_phone' ? '#phone-indicator' : '#phone2-indicator';
        const input = fieldType === 'customer_phone' ? '#customer_phone' : '#customer_phone_2';
        
        // Afficher l'indicateur de vérification
        $(indicator).removeClass('has-duplicates clean').addClass('checking').show();
        
        $.get('/admin/duplicates/check-phone', { phone: phone })
            .done(function(response) {
                $(indicator).removeClass('checking');
                
                if (response.has_duplicates) {
                    $(indicator).addClass('has-duplicates').html('<i class="fas fa-exclamation-triangle"></i>');
                    $(input).addClass('has-duplicates');
                    
                    if (fieldType === 'customer_phone') {
                        showDuplicateAlert(response);
                    }
                } else {
                    $(indicator).addClass('clean').html('<i class="fas fa-check"></i>');
                    $(input).removeClass('has-duplicates');
                    
                    if (fieldType === 'customer_phone') {
                        hideDuplicateAlert();
                    }
                }
            })
            .fail(function() {
                $(indicator).removeClass('checking').hide();
                $(input).removeClass('has-duplicates');
            });
    }

    function resetPhoneValidation(fieldType) {
        const indicator = fieldType === 'customer_phone' ? '#phone-indicator' : '#phone2-indicator';
        const input = fieldType === 'customer_phone' ? '#customer_phone' : '#customer_phone_2';
        
        $(indicator).removeClass('checking has-duplicates clean').hide();
        $(input).removeClass('has-duplicates');
        
        if (fieldType === 'customer_phone') {
            hideDuplicateAlert();
        }
    }

    function showDuplicateAlert(response) {
        const alert = $('#duplicate-alert');
        const title = $('#duplicate-alert-title');
        const content = $('#duplicate-alert-content');
        const viewBtn = $('#view-duplicates-btn');
        
        // Déterminer le type d'alerte
        let alertClass = 'warning';
        if (response.marked_duplicates > 0) {
            alertClass = 'danger';
        }
        
        // Configurer l'alerte
        alert.removeClass('warning danger success').addClass(alertClass);
        
        if (response.marked_duplicates > 0) {
            title.text('⚠️ Doublons non révisés détectés');
            content.html(`
                <strong>${response.marked_duplicates} doublon(s) non révisé(s)</strong> trouvé(s) pour ce numéro.<br>
                <em>Ces commandes nécessitent votre attention avant de créer une nouvelle commande.</em>
            `);
        } else {
            title.text('⚠️ Commandes multiples détectées');
            content.html(`
                <strong>${response.total_orders} commande(s)</strong> trouvée(s) pour ce numéro.<br>
                <em>Vérifiez s'il s'agit de doublons avant de continuer.</em>
            `);
        }
        
        // Configurer le bouton de visualisation
        viewBtn.off('click').on('click', function() {
            const phone = $('#customer_phone').val().trim();
            window.open(`/admin/duplicates/detail/${encodeURIComponent(phone)}`, '_blank');
        });
        
        alert.show();
    }

    function hideDuplicateAlert() {
        $('#duplicate-alert').hide();
    }

    window.dismissDuplicateAlert = function() {
        hideDuplicateAlert();
    };

    // =========================
    // RECHERCHE DE PRODUITS
    // =========================
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(searchTimeout);
        $('#product-suggestions').hide();
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => searchProducts(query), 300);
        }
    });

    function searchProducts(query) {
        $.get('/admin/orders/search-products', { search: query })
            .done(data => showProductSuggestions(data))
            .fail(() => {
                console.error('Erreur lors de la recherche de produits');
                showProductSuggestions([]);
            });
    }

    function showProductSuggestions(products) {
        const $suggestions = $('#product-suggestions').empty();
        if (products.length === 0) {
            $suggestions.html('<div class="suggestion-item">Aucun produit trouvé</div>');
        } else {
            products.forEach(product => {
                const $item = $(`
                    <div class="suggestion-item" data-product-id="${product.id}">
                        <div><strong>${product.name}</strong><br><small class="text-muted">Stock: ${product.stock}</small></div>
                        <div class="text-success fw-bold">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `).on('click', function() {
                    addToCart(product);
                    $('#product-search').val('');
                    $suggestions.hide();
                });
                $suggestions.append($item);
            });
        }
        $suggestions.show();
    }

    $(document).on('click', e => {
        if (!$(e.target).closest('.search-input-group').length) $('#product-suggestions').hide();
    });

    // =========================
    // GESTION DU PANIER
    // =========================
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) existingItem.quantity += 1;
        else cart.push({ id: product.id, name: product.name, price: parseFloat(product.price), quantity: 1, stock: product.stock });
        updateCartDisplay();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateCartDisplay();
    }

    function updateQuantity(productId, newQuantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, Math.min(newQuantity, item.stock || Infinity));
            updateCartDisplay();
        }
    }

    function updateCartDisplay() {
        $cartCount.text(cart.reduce((sum, item) => sum + item.quantity, 0));
        $cartItemsContainer.find('.cart-item').remove();

        if (cart.length === 0) {
            $cartEmptyMessage.show();
            $cartItemsContainer.addClass('is-empty');
            if ($cartBody.is(':visible')) $cartSummary.hide(); 
        } else {
            $cartEmptyMessage.hide();
            $cartItemsContainer.removeClass('is-empty');
            cart.forEach(item => $cartItemsContainer.append(createCartItemElement(item)));
            if ($cartBody.is(':visible')) $cartSummary.show(); 
            updateCartSummary();
        }
        updateFormData();
    }

    function createCartItemElement(item) {
        return $(`
            <div class="cart-item" data-product-id="${item.id}">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">${item.price.toFixed(3)} TND × ${item.quantity}</div>
                </div>
                <div class="quantity-control">
                    <button type="button" class="quantity-btn minus" aria-label="Diminuer la quantité"><i class="fas fa-minus"></i></button>
                    <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="${item.stock || 999}" aria-label="Quantité">
                    <button type="button" class="quantity-btn plus" aria-label="Augmenter la quantité"><i class="fas fa-plus"></i></button>
                </div>
                <button type="button" class="remove-item" aria-label="Supprimer l'article"><i class="fas fa-trash"></i></button>
            </div>
        `).on('click', '.quantity-btn.minus', () => updateQuantity(item.id, item.quantity - 1))
          .on('click', '.quantity-btn.plus', () => updateQuantity(item.id, item.quantity + 1))
          .on('change keyup', '.quantity-input', function() { updateQuantity(item.id, parseInt($(this).val()) || 1); })
          .on('click', '.remove-item', () => removeFromCart(item.id));
    }

    function updateCartSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 0; 
        const total = subtotal + shipping;
        $('#subtotal').text(`${subtotal.toFixed(3)} TND`);
        $('#shipping-cost').text(`${shipping.toFixed(3)} TND`);
        $('#total').text(`${total.toFixed(3)} TND`);
    }

    function updateFormData() {
        const $cartData = $('#cart-data').empty();
        cart.forEach((item, index) => {
            $cartData.append(`<input type="hidden" name="products[${index}][id]" value="${item.id}">`);
            $cartData.append(`<input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">`);
        });
    }

    // =========================
    // GESTION DES BADGES
    // =========================
    $('.status-badge').on('click', function() {
        $('.status-badge').removeClass('active'); $(this).addClass('active');
        $('#status').val($(this).data('status'));
    });
    $('.priority-badge').on('click', function() {
        $('.priority-badge').removeClass('active'); $(this).addClass('active');
        $('#priority').val($(this).data('priority'));
    });

    // =========================
    // GESTION GÉOGRAPHIQUE
    // =========================
    $('#customer_governorate').on('change', function() {
        const regionId = $(this).val();
        const $citySelect = $('#customer_city').html('<option value="">Chargement...</option>').prop('disabled', true);
        if (regionId) {
            $.get('/admin/orders/get-cities', { region_id: regionId })
                .done(cities => {
                    $citySelect.html('<option value="">Choisir une ville</option>');
                    cities.forEach(city => $citySelect.append(`<option value="${city.id}">${city.name}</option>`));
                })
                .fail(() => $citySelect.html('<option value="">Erreur de chargement</option>'))
                .always(() => $citySelect.prop('disabled', false));
        } else {
            $citySelect.html('<option value="">Choisir une ville</option>').prop('disabled', false);
        }
    });

    // =========================
    // COLLAPSIBLE CART & SECTION RESIZE
    // =========================
    function updateOrderControlsBorderRadius() {
        $orderControls.css('border-radius', ($cartBody.is(':hidden') && $cartSummary.is(':hidden')) ? '0 0 var(--border-radius) var(--border-radius)' : '0');
    }
    
    var startCartCollapsed = false;

    if (startCartCollapsed) {
        $cartBody.hide();
        $cartSummary.hide();
        $cartSection.addClass('cart-content-collapsed');
        $toggleCartBtn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $toggleCartBtn.attr('aria-label', 'Agrandir le panier');
    }

    $toggleCartBtn.on('click', function() {
        const $icon = $(this).find('i');
        const isCurrentlyVisible = $cartBody.is(':visible');

        if (isCurrentlyVisible) { 
            $cartBody.slideUp(300);
            if (cart.length > 0) $cartSummary.slideUp(300); 
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $(this).attr('aria-label', 'Agrandir le panier');
            $cartSection.addClass('cart-content-collapsed'); 
        } else { 
            $cartBody.slideDown(300);
            if (cart.length > 0) $cartSummary.slideDown(300); 
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $(this).attr('aria-label', 'Réduire le panier');
            $cartSection.removeClass('cart-content-collapsed'); 
        }
        setTimeout(updateOrderControlsBorderRadius, 310); 
    });
    
    updateCartDisplay(); 
    updateOrderControlsBorderRadius();

    // =========================
    // VALIDATION DU FORMULAIRE
    // =========================
    $('#orderForm').on('submit', function(e) {
        const errors = [];
        const phone = $('#customer_phone').val().trim();
        if (!phone) { errors.push('Le numéro de téléphone principal est obligatoire.'); }
        if (cart.length === 0) { errors.push('Veuillez ajouter au moins un produit à la commande.'); }

        // Vérifier s'il y a des doublons non révisés
        if ($('#duplicate-alert').is(':visible') && $('#duplicate-alert').hasClass('danger')) {
            if (!confirm('⚠️ ATTENTION: Ce numéro possède des doublons non révisés.\n\nÊtes-vous sûr de vouloir créer une nouvelle commande ?\n\nIl est recommandé de traiter les doublons existants d\'abord.')) {
                e.preventDefault();
                return false;
            }
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert('Erreurs de validation:\n' + errors.join('\n'));
            $('#save-btn').prop('disabled', false).removeClass('loading');
            return false;
        }
        $('#save-btn').prop('disabled', true).addClass('loading');
        setTimeout(() => {
            if ($('#save-btn').hasClass('loading')) $('#save-btn').prop('disabled', false).removeClass('loading');
        }, 10000);
    });

    $('#customer_phone').focus();
});
</script>
@endsection