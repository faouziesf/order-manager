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
        --info-gradient: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-elevated: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --border-radius-lg: 16px;
        --border-radius-xl: 20px;
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .page-container {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius-xl);
        box-shadow: var(--shadow-elevated);
        border: 1px solid var(--glass-border);
        margin: 1rem;
        overflow: hidden;
    }

    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        transform: rotate(15deg);
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 2;
    }

    .page-header .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
        position: relative;
        z-index: 2;
    }

    .page-header .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
    }

    .page-header .breadcrumb-item.active {
        color: white;
    }

    .main-content {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 2rem;
        padding: 2rem;
        min-height: 70vh;
    }

    @media (max-width: 1200px) {
        .main-content {
            grid-template-columns: 1fr;
        }
    }

    /* =========================
       SECTION CLIENT
    ========================= */
    .customer-section {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        height: fit-content;
    }

    .section-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
    }

    .section-header .icon {
        width: 40px;
        height: 40px;
        background: var(--primary-gradient);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
    }

    .section-content {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label .required {
        color: #ef4444;
        font-size: 0.9rem;
    }

    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.875rem 1rem;
        transition: var(--transition-smooth);
        font-size: 0.95rem;
        background: #fafafa;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: white;
        outline: none;
    }

    .form-control:invalid {
        border-color: #ef4444;
    }

    .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    .input-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    /* =========================
       SECTION PANIER
    ========================= */
    .cart-section {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: 2rem;
    }

    .cart-header {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
    }

    .cart-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cart-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        transition: var(--transition-smooth);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
    }

    .cart-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .cart-body {
        max-height: 600px;
        overflow-y: auto;
        transition: var(--transition-smooth);
    }

    .cart-body.collapsed {
        max-height: 0;
        overflow: hidden;
    }

    .product-search {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .search-input-group {
        position: relative;
    }

    .search-input-group input {
        padding-left: 3rem;
        background: white;
    }

    .search-input-group .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 1rem;
    }

    .product-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
    }

    .suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition-smooth);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .suggestion-item:hover {
        background: #f3f4f6;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .cart-items {
        padding: 1rem 2rem;
        min-height: 200px;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 12px;
        margin-bottom: 1rem;
        border: 1px solid #e5e7eb;
        transition: var(--transition-smooth);
    }

    .cart-item:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .item-price {
        color: #6b7280;
        font-size: 0.9rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        border-radius: 8px;
        padding: 0.25rem;
    }

    .quantity-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: #f3f4f6;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
        color: #6b7280;
    }

    .quantity-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        color: #374151;
    }

    .remove-item {
        background: #fef2f2;
        color: #ef4444;
        border: none;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .remove-item:hover {
        background: #fee2e2;
        transform: scale(1.1);
    }

    .cart-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
    }

    .cart-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* =========================
       SECTION TOTAL & CONTRÔLES
    ========================= */
    .cart-summary {
        padding: 1.5rem 2rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1.1rem;
        color: #374151;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .summary-label {
        color: #6b7280;
        font-weight: 500;
    }

    .summary-value {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        color: #374151;
    }

    .order-controls {
        padding: 2rem;
        background: white;
        border-top: 1px solid #e5e7eb;
    }

    .control-group {
        margin-bottom: 1.5rem;
    }

    .control-group:last-child {
        margin-bottom: 0;
    }

    .control-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
    }

    .status-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: var(--transition-smooth);
        font-weight: 500;
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
    }

    .status-badge::before {
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

    .status-badge:hover::before {
        opacity: 0.1;
    }

    .status-badge.active {
        color: white;
        transform: scale(1.05);
    }

    .status-nouvelle {
        background: #f3f4f6;
        color: #6b7280;
    }

    .status-nouvelle.active {
        background: var(--primary-gradient);
    }

    .status-confirmée {
        background: #ecfdf5;
        color: #059669;
    }

    .status-confirmée.active {
        background: var(--success-gradient);
    }

    .priority-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .priority-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: var(--transition-smooth);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .priority-badge.active {
        color: white;
        transform: scale(1.05);
    }

    .priority-normale {
        background: #f3f4f6;
        color: #6b7280;
    }

    .priority-normale.active {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .priority-urgente {
        background: #fef3c7;
        color: #d97706;
    }

    .priority-urgente.active {
        background: var(--warning-gradient);
    }

    .priority-vip {
        background: #fee2e2;
        color: #dc2626;
    }

    .priority-vip.active {
        background: var(--danger-gradient);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-save {
        flex: 1;
        background: var(--success-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-cancel {
        background: #f3f4f6;
        color: #6b7280;
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
        color: #374151;
        transform: translateY(-2px);
    }

    /* =========================
       ANIMATIONS
    ========================= */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .cart-item {
        animation: slideIn 0.3s ease-out;
    }

    .product-suggestions {
        animation: fadeIn 0.2s ease-out;
    }

    /* =========================
       LOADING STATES
    ========================= */
    .loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid transparent;
        border-top: 2px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* =========================
       RESPONSIVE
    ========================= */
    @media (max-width: 1200px) {
        .cart-section {
            position: static;
            margin-top: 2rem;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
            gap: 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
        }
        
        .section-content {
            padding: 1.5rem;
        }
        
        .input-group {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .status-badges,
        .priority-badges {
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="page-container">
    <!-- En-tête de page -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1><i class="fas fa-plus-circle me-3"></i>Créer une Nouvelle Commande</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mt-2">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-home me-1"></i>Accueil
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.orders.index') }}">Commandes</a>
                        </li>
                        <li class="breadcrumb-item active">Créer</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        <div class="main-content">
            <!-- Section Client -->
            <div class="customer-section">
                <div class="section-header">
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Informations Client</h3>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="customer_phone" class="form-label">
                            <i class="fas fa-phone me-1"></i>
                            Téléphone Principal
                            <span class="required">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control @error('customer_phone') is-invalid @enderror" 
                               id="customer_phone" 
                               name="customer_phone" 
                               value="{{ old('customer_phone') }}" 
                               placeholder="Ex: +216 XX XXX XXX"
                               required
                               autocomplete="tel">
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="customer_name" class="form-label">
                            <i class="fas fa-user me-1"></i>
                            Nom Complet
                        </label>
                        <input type="text" 
                               class="form-control @error('customer_name') is-invalid @enderror" 
                               id="customer_name" 
                               name="customer_name" 
                               value="{{ old('customer_name') }}" 
                               placeholder="Nom et prénom du client"
                               autocomplete="name">
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="customer_phone_2" class="form-label">
                            <i class="fas fa-phone-alt me-1"></i>
                            Téléphone Secondaire
                        </label>
                        <input type="tel" 
                               class="form-control @error('customer_phone_2') is-invalid @enderror" 
                               id="customer_phone_2" 
                               name="customer_phone_2" 
                               value="{{ old('customer_phone_2') }}" 
                               placeholder="Téléphone alternatif (optionnel)"
                               autocomplete="tel">
                        @error('customer_phone_2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="input-group">
                        <div class="form-group">
                            <label for="customer_governorate" class="form-label">
                                <i class="fas fa-map-marked-alt me-1"></i>
                                Gouvernorat
                            </label>
                            <select class="form-select @error('customer_governorate') is-invalid @enderror" 
                                    id="customer_governorate" 
                                    name="customer_governorate">
                                <option value="">Choisir un gouvernorat</option>
                                @if(isset($regions))
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('customer_governorate') == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_governorate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_city" class="form-label">
                                <i class="fas fa-city me-1"></i>
                                Ville
                            </label>
                            <select class="form-select @error('customer_city') is-invalid @enderror" 
                                    id="customer_city" 
                                    name="customer_city">
                                <option value="">Choisir une ville</option>
                            </select>
                            @error('customer_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customer_address" class="form-label">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Adresse Complète
                        </label>
                        <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                  id="customer_address" 
                                  name="customer_address" 
                                  rows="3" 
                                  placeholder="Adresse détaillée du client"
                                  autocomplete="street-address">{{ old('customer_address') }}</textarea>
                        @error('customer_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>
                            Commentaires
                        </label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Notes supplémentaires sur la commande">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section Panier -->
            <div class="cart-section">
                <div class="cart-header" onclick="toggleCart()">
                    <h3>
                        <i class="fas fa-shopping-cart"></i>
                        Panier (<span id="cart-count">0</span>)
                    </h3>
                    <button type="button" class="cart-toggle" id="cart-toggle">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>

                <div class="cart-body" id="cart-body">
                    <!-- Recherche de produits -->
                    <div class="product-search">
                        <div class="search-input-group">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="product-search" 
                                   placeholder="Rechercher un produit..." 
                                   autocomplete="off">
                            <div class="product-suggestions" id="product-suggestions"></div>
                        </div>
                    </div>

                    <!-- Liste des produits -->
                    <div class="cart-items" id="cart-items">
                        <div class="cart-empty" id="cart-empty">
                            <i class="fas fa-shopping-basket"></i>
                            <h5>Panier vide</h5>
                            <p>Recherchez et ajoutez des produits à votre commande</p>
                        </div>
                    </div>

                    <!-- Résumé du panier -->
                    <div class="cart-summary" id="cart-summary" style="display: none;">
                        <div class="summary-row">
                            <span class="summary-label">Sous-total:</span>
                            <span class="summary-value" id="subtotal">0.000 TND</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Frais de livraison:</span>
                            <span class="summary-value" id="shipping-cost">0.000 TND</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value" id="total">0.000 TND</span>
                        </div>
                    </div>
                </div>

                <!-- Contrôles de commande - EN DEHORS du panier collapsible -->
                <div class="order-controls">
                    <div class="control-group">
                        <label class="control-label">Statut de la commande</label>
                        <div class="status-badges">
                            <div class="status-badge status-nouvelle active" data-status="nouvelle">
                                <i class="fas fa-circle me-1"></i>Nouvelle
                            </div>
                            <div class="status-badge status-confirmée" data-status="confirmée">
                                <i class="fas fa-check-circle me-1"></i>Confirmée
                            </div>
                        </div>
                        <input type="hidden" name="status" id="status" value="nouvelle">
                    </div>

                    <div class="control-group">
                        <label class="control-label">Priorité</label>
                        <div class="priority-badges">
                            <div class="priority-badge priority-normale active" data-priority="normale">
                                <i class="fas fa-minus me-1"></i>Normale
                            </div>
                            <div class="priority-badge priority-urgente" data-priority="urgente">
                                <i class="fas fa-exclamation me-1"></i>Urgente
                            </div>
                            <div class="priority-badge priority-vip" data-priority="vip">
                                <i class="fas fa-crown me-1"></i>VIP
                            </div>
                        </div>
                        <input type="hidden" name="priority" id="priority" value="normale">
                    </div>

                    <div class="control-group">
                        <label for="employee_id" class="control-label">
                            <i class="fas fa-user-tie me-1"></i>
                            Assigner à un employé
                        </label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">Non assigné</option>
                            @if(Auth::guard('admin')->user()->employees()->where('is_active', true)->count() > 0)
                                @foreach(Auth::guard('admin')->user()->employees()->where('is_active', true)->get() as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('admin.orders.index') }}" class="btn-cancel">
                            <i class="fas fa-times me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn-save" id="save-btn">
                            <i class="fas fa-save me-1"></i>Créer la Commande
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Hidden inputs for cart data -->
<div id="cart-data" style="display: none;"></div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let products = [];
    let searchTimeout;
    let isCartCollapsed = false;

    // =========================
    // GESTION DU PANIER
    // =========================
    function toggleCart() {
        const cartBody = $('#cart-body');
        const cartToggle = $('#cart-toggle i');
        
        isCartCollapsed = !isCartCollapsed;
        
        if (isCartCollapsed) {
            cartBody.addClass('collapsed');
            cartToggle.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            cartBody.removeClass('collapsed');
            cartToggle.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    }

    // =========================
    // RECHERCHE DE PRODUITS
    // =========================
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (query.length >= 2) {
                searchProducts(query);
            } else {
                $('#product-suggestions').hide();
            }
        }, 300);
    });

    function searchProducts(query) {
        $.get('/admin/orders/search-products', { search: query })
            .done(function(data) {
                showProductSuggestions(data);
            })
            .fail(function() {
                console.error('Erreur lors de la recherche de produits');
            });
    }

    function showProductSuggestions(products) {
        const suggestions = $('#product-suggestions');
        suggestions.empty();

        if (products.length === 0) {
            suggestions.html('<div class="suggestion-item">Aucun produit trouvé</div>');
        } else {
            products.forEach(product => {
                const item = $(`
                    <div class="suggestion-item" data-product-id="${product.id}">
                        <div>
                            <strong>${product.name}</strong>
                            <br><small class="text-muted">Stock: ${product.stock}</small>
                        </div>
                        <div class="text-success fw-bold">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `);
                
                item.on('click', function() {
                    addToCart(product);
                    $('#product-search').val('');
                    suggestions.hide();
                });
                
                suggestions.append(item);
            });
        }

        suggestions.show();
    }

    // Masquer suggestions en cliquant ailleurs
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-input-group').length) {
            $('#product-suggestions').hide();
        }
    });

    // =========================
    // GESTION DU PANIER
    // =========================
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock
            });
        }
        
        updateCartDisplay();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateCartDisplay();
    }

    function updateQuantity(productId, newQuantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, Math.min(newQuantity, item.stock));
            updateCartDisplay();
        }
    }

    function updateCartDisplay() {
        const cartItems = $('#cart-items');
        const cartEmpty = $('#cart-empty');
        const cartSummary = $('#cart-summary');
        const cartCount = $('#cart-count');
        
        cartCount.text(cart.length);
        
        if (cart.length === 0) {
            cartEmpty.show();
            cartSummary.hide();
            cartItems.find('.cart-item').remove();
        } else {
            cartEmpty.hide();
            cartSummary.show();
            
            // Supprimer les anciens items
            cartItems.find('.cart-item').remove();
            
            // Ajouter les nouveaux items
            cart.forEach(item => {
                const cartItem = createCartItemElement(item);
                cartItems.append(cartItem);
            });
            
            updateCartSummary();
        }
        
        updateFormData();
    }

    function createCartItemElement(item) {
        const element = $(`
            <div class="cart-item" data-product-id="${item.id}">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">${item.price.toFixed(3)} TND × ${item.quantity}</div>
                </div>
                <div class="quantity-control">
                    <button type="button" class="quantity-btn minus" data-action="minus">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="${item.stock}">
                    <button type="button" class="quantity-btn plus" data-action="plus">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="remove-item" data-action="remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `);
        
        // Event listeners pour les contrôles
        element.find('.quantity-btn[data-action="minus"]').on('click', function() {
            updateQuantity(item.id, item.quantity - 1);
        });
        
        element.find('.quantity-btn[data-action="plus"]').on('click', function() {
            updateQuantity(item.id, item.quantity + 1);
        });
        
        element.find('.quantity-input').on('change', function() {
            const newQuantity = parseInt($(this).val()) || 1;
            updateQuantity(item.id, newQuantity);
        });
        
        element.find('.remove-item').on('click', function() {
            removeFromCart(item.id);
        });
        
        return element;
    }

    function updateCartSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 0; // À implémenter selon la logique métier
        const total = subtotal + shipping;
        
        $('#subtotal').text(subtotal.toFixed(3) + ' TND');
        $('#shipping-cost').text(shipping.toFixed(3) + ' TND');
        $('#total').text(total.toFixed(3) + ' TND');
    }

    function updateFormData() {
        const cartData = $('#cart-data');
        cartData.empty();
        
        cart.forEach((item, index) => {
            cartData.append(`
                <input type="hidden" name="products[${index}][id]" value="${item.id}">
                <input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">
            `);
        });
    }

    // =========================
    // GESTION DES BADGES
    // =========================
    $('.status-badge').on('click', function() {
        $('.status-badge').removeClass('active');
        $(this).addClass('active');
        $('#status').val($(this).data('status'));
    });

    $('.priority-badge').on('click', function() {
        $('.priority-badge').removeClass('active');
        $(this).addClass('active');
        $('#priority').val($(this).data('priority'));
    });

    // =========================
    // GESTION GÉOGRAPHIQUE
    // =========================
    $('#customer_governorate').on('change', function() {
        const regionId = $(this).val();
        const citySelect = $('#customer_city');
        
        citySelect.html('<option value="">Chargement...</option>').prop('disabled', true);
        
        if (regionId) {
            $.get('/admin/orders/get-cities', { region_id: regionId })
                .done(function(cities) {
                    citySelect.html('<option value="">Choisir une ville</option>');
                    cities.forEach(city => {
                        citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                    });
                })
                .fail(function() {
                    citySelect.html('<option value="">Erreur de chargement</option>');
                })
                .always(function() {
                    citySelect.prop('disabled', false);
                });
        } else {
            citySelect.html('<option value="">Choisir une ville</option>').prop('disabled', false);
        }
    });

    // =========================
    // VALIDATION DU FORMULAIRE
    // =========================
    $('#orderForm').on('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit à la commande.');
            return false;
        }
        
        const phone = $('#customer_phone').val().trim();
        if (!phone) {
            e.preventDefault();
            alert('Le numéro de téléphone principal est obligatoire.');
            $('#customer_phone').focus();
            return false;
        }
        
        // Désactiver le bouton pour éviter les double soumissions
        $('#save-btn').prop('disabled', true).addClass('loading');
    });

    // =========================
    // INITIALISATION
    // =========================
    // Rendre la fonction toggleCart globale
    window.toggleCart = toggleCart;
    
    // Focus sur le premier champ
    $('#customer_phone').focus();
});
</script>
@endsection