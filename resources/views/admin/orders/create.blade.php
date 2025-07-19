@extends('layouts.admin')

@section('title', 'Créer une Commande')
@section('page-title', 'Créer une Nouvelle Commande')

@section('css')
<style>
    :root {
        --royal-blue: #1e3a8a;
        --royal-blue-light: #3b82f6;
        --royal-blue-dark: #1e40af;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --glass-bg: rgba(255, 255, 255, 0.98);
        --shadow: 0 2px 10px rgba(30, 58, 138, 0.08);
        --border-radius: 8px;
        --transition: all 0.2s ease;
    }

    body {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        font-family: 'Inter', sans-serif;
    }

    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* Header simplifié */
    .page-header {
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--royal-blue-dark) 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Layout optimisé en 2 colonnes */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .main-layout {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    /* Formulaire client simplifié */
    .client-form {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
        border: 1px solid rgba(30, 58, 138, 0.1);
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--royal-blue-dark);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-grid {
        display: grid;
        gap: 1rem;
    }

    .form-grid.two-cols {
        grid-template-columns: 1fr 1fr;
    }

    @media (max-width: 768px) {
        .form-grid.two-cols {
            grid-template-columns: 1fr;
        }
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--royal-blue-dark);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .form-label .required {
        color: var(--danger);
        font-size: 0.7rem;
    }

    .form-input {
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.75rem;
        font-size: 0.875rem;
        background: #fafbfc;
        transition: var(--transition);
        font-family: inherit;
    }

    .form-input:focus {
        border-color: var(--royal-blue-light);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background: white;
        outline: none;
    }

    .form-input.has-duplicates {
        border-color: var(--warning);
        background: rgba(245, 158, 11, 0.05);
    }

    /* Indicateur de téléphone optimisé */
    .phone-field {
        position: relative;
    }

    .phone-indicator {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
        display: none;
        z-index: 10;
    }

    .phone-indicator.checking {
        display: block;
        color: #6b7280;
        animation: pulse 1.5s infinite;
    }

    .phone-indicator.has-duplicates {
        display: block;
        color: var(--warning);
    }

    .phone-indicator.clean {
        display: block;
        color: var(--success);
    }

    /* Alert de doublons compact */
    .duplicate-alert {
        margin-top: 0.75rem;
        padding: 1rem;
        border-radius: 6px;
        border: 1px solid var(--warning);
        background: rgba(245, 158, 11, 0.05);
        display: none;
        animation: slideDown 0.3s ease;
    }

    .duplicate-alert.show {
        display: block;
    }

    .duplicate-alert-content {
        font-size: 0.85rem;
        color: #92400e;
        margin-bottom: 0.75rem;
    }

    .duplicate-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-small {
        padding: 0.4rem 0.8rem;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
    }

    .btn-royal {
        background: var(--royal-blue);
        color: white;
    }

    .btn-royal:hover {
        background: var(--royal-blue-dark);
        color: white;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-success:hover {
        background: #059669;
        color: white;
    }

    .btn-outline {
        background: white;
        color: var(--royal-blue);
        border: 1px solid var(--royal-blue);
    }

    .btn-outline:hover {
        background: var(--royal-blue);
        color: white;
    }

    /* Panier optimisé */
    .cart-panel {
        background: var(--glass-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid rgba(30, 58, 138, 0.1);
        position: sticky;
        top: 1rem;
        height: fit-content;
    }

    .cart-header {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        padding: 1rem 1.25rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cart-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    /* Recherche de produits */
    .product-search {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .search-group {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 0.85rem;
    }

    .search-input {
        width: 100%;
        padding: 0.7rem 0.7rem 0.7rem 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.85rem;
        background: white;
        transition: var(--transition);
    }

    .search-input:focus {
        border-color: var(--royal-blue-light);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 6px 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .suggestion {
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f9fafb;
        transition: var(--transition);
        font-size: 0.85rem;
    }

    .suggestion:hover {
        background: #f8fafc;
    }

    .suggestion:last-child {
        border-bottom: none;
    }

    .product-ref {
        font-family: monospace;
        font-size: 0.7rem;
        color: var(--royal-blue);
        background: rgba(30, 58, 138, 0.1);
        padding: 0.15rem 0.4rem;
        border-radius: 3px;
        margin-left: 0.5rem;
    }

    /* Items du panier */
    .cart-items {
        padding: 1rem;
        min-height: 120px;
        max-height: 300px;
        overflow-y: auto;
    }

    .cart-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: #6b7280;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        border: 1px solid #f1f5f9;
        transition: var(--transition);
    }

    .cart-item:hover {
        border-color: var(--royal-blue-light);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .item-info {
        flex: 1;
        min-width: 0;
    }

    .item-name {
        font-weight: 600;
        color: var(--royal-blue-dark);
        font-size: 0.85rem;
        margin-bottom: 0.2rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-price {
        color: #6b7280;
        font-size: 0.75rem;
        font-family: monospace;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background: white;
        border-radius: 4px;
        padding: 0.2rem;
        border: 1px solid #e2e8f0;
    }

    .qty-btn {
        width: 24px;
        height: 24px;
        border: none;
        background: #f3f4f6;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.7rem;
        color: #6b7280;
    }

    .qty-btn:hover {
        background: var(--royal-blue-light);
        color: white;
    }

    .qty-input {
        width: 35px;
        text-align: center;
        border: none;
        background: transparent;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--royal-blue-dark);
    }

    .remove-btn {
        background: #fef2f2;
        color: var(--danger);
        border: none;
        border-radius: 4px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.7rem;
    }

    .remove-btn:hover {
        background: #fee2e2;
    }

    /* Résumé et contrôles */
    .cart-summary {
        padding: 1rem;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 0.95rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e2e8f0;
        color: var(--royal-blue-dark);
    }

    .summary-value {
        font-family: monospace;
        font-weight: 600;
    }

    .order-controls {
        padding: 1rem;
        background: white;
        border-top: 1px solid #f1f5f9;
    }

    .control-section {
        margin-bottom: 1rem;
    }

    .control-label {
        font-weight: 600;
        color: var(--royal-blue-dark);
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
    }

    .status-options {
        display: flex;
        gap: 0.5rem;
    }

    .status-option {
        flex: 1;
        padding: 0.6rem;
        border: 2px solid transparent;
        border-radius: 6px;
        cursor: pointer;
        text-align: center;
        font-size: 0.8rem;
        font-weight: 600;
        transition: var(--transition);
    }

    .status-option.nouvelle {
        background: #f3f4f6;
        color: #6b7280;
    }

    .status-option.nouvelle.active {
        background: var(--royal-blue);
        color: white;
        border-color: var(--royal-blue-dark);
    }

    .status-option.confirmée {
        background: #ecfdf5;
        color: #059669;
    }

    .status-option.confirmée.active {
        background: var(--success);
        color: white;
        border-color: #059669;
    }

    .employee-select {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.85rem;
        background: white;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .btn-cancel {
        flex: 1;
        background: #f3f4f6;
        color: #6b7280;
        border: none;
        border-radius: 6px;
        padding: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .btn-save {
        flex: 2;
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* Priority badge automatique */
    .auto-priority {
        background: linear-gradient(135deg, #d4a147 0%, #b8941f 100%);
        color: white;
        padding: 0.3rem 0.7rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        animation: slideIn 0.3s ease;
    }

    /* Modal historique simplifié */
    .modal-content {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: var(--royal-blue);
        color: white;
        border: none;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .history-item {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 3px solid var(--royal-blue-light);
    }

    .history-item:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Champ prix total conditionnel */
    .total-price-field {
        margin-top: 1rem;
        padding: 1rem;
        background: #f0f9ff;
        border: 1px solid #0ea5e9;
        border-radius: 6px;
        display: none;
    }

    .total-price-field.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    /* Animations */
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0.75rem;
        }
        
        .page-header {
            padding: 1rem;
        }
        
        .client-form, .cart-panel {
            padding: 1rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .duplicate-actions {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> Créer une Nouvelle Commande</h1>
    </div>

    <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        <div class="main-layout">
            <!-- Formulaire Client -->
            <div class="client-form">
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Informations Client
                </div>
                
                <div class="form-grid">
                    <!-- Nom -->
                    <div class="form-field">
                        <label for="customer_name" class="form-label">
                            <i class="fas fa-user"></i> Nom Complet <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input @error('customer_name') is-invalid @enderror" 
                               id="customer_name" name="customer_name" value="{{ old('customer_name') }}" 
                               placeholder="Nom et prénom du client" required>
                        @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <!-- Téléphones -->
                    <div class="form-grid two-cols">
                        <div class="form-field">
                            <label for="customer_phone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone <span class="required">*</span>
                            </label>
                            <div class="phone-field">
                                <input type="tel" class="form-input @error('customer_phone') is-invalid @enderror" 
                                       id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" 
                                       placeholder="Ex: +216 XX XXX XXX" required>
                                <div class="phone-indicator" id="phone-indicator">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            
                            <!-- Alert de doublons -->
                            <div class="duplicate-alert" id="duplicate-alert">
                                <div class="duplicate-alert-content" id="duplicate-alert-content"></div>
                                <div class="duplicate-actions">
                                    <button type="button" class="btn-small btn-royal" id="view-history-btn">
                                        <i class="fas fa-history"></i> Historique
                                    </button>
                                    <button type="button" class="btn-small btn-success" id="fill-data-btn">
                                        <i class="fas fa-fill-drip"></i> Pré-remplir
                                    </button>
                                    <button type="button" class="btn-small btn-outline" onclick="dismissAlert()">
                                        <i class="fas fa-times"></i> Ignorer
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Priorité automatique -->
                            <div class="auto-priority" id="auto-priority" style="display: none;">
                                <i class="fas fa-copy"></i> Priorité: Doublons (automatique)
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="customer_phone_2" class="form-label">
                                <i class="fas fa-phone-alt"></i> Téléphone 2
                            </label>
                            <input type="tel" class="form-input @error('customer_phone_2') is-invalid @enderror" 
                                   id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2') }}" 
                                   placeholder="Téléphone alternatif">
                            @error('customer_phone_2') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <!-- Localisation -->
                    <div class="form-grid two-cols">
                        <div class="form-field">
                            <label for="customer_governorate" class="form-label">
                                <i class="fas fa-map-marked-alt"></i> Gouvernorat <span class="required">*</span>
                            </label>
                            <select class="form-input @error('customer_governorate') is-invalid @enderror" 
                                    id="customer_governorate" name="customer_governorate" required>
                                <option value="">Choisir...</option>
                                @if (isset($regions))
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" {{ old('customer_governorate') == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('customer_governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="form-field">
                            <label for="customer_city" class="form-label">
                                <i class="fas fa-city"></i> Ville <span class="required">*</span>
                            </label>
                            <select class="form-input @error('customer_city') is-invalid @enderror" 
                                    id="customer_city" name="customer_city" required>
                                <option value="">Choisir...</option>
                            </select>
                            @error('customer_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <!-- Adresse -->
                    <div class="form-field">
                        <label for="customer_address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Adresse <span class="required">*</span>
                        </label>
                        <textarea class="form-input @error('customer_address') is-invalid @enderror" 
                                  id="customer_address" name="customer_address" rows="2" 
                                  placeholder="Adresse complète" required>{{ old('customer_address') }}</textarea>
                        @error('customer_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-field">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note"></i> Notes
                        </label>
                        <textarea class="form-input @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" 
                                  placeholder="Commentaires sur la commande">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Panier -->
            <div class="cart-panel">
                <div class="cart-header">
                    <div class="cart-title">
                        <i class="fas fa-shopping-cart"></i> Panier
                        <span class="cart-count" id="cart-count">0</span>
                    </div>
                </div>
                
                <!-- Recherche produits -->
                <div class="product-search">
                    <div class="search-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="product-search" 
                               placeholder="Rechercher par nom ou référence...">
                        <div class="suggestions" id="suggestions"></div>
                    </div>
                </div>
                
                <!-- Items -->
                <div class="cart-items" id="cart-items">
                    <div class="cart-empty" id="cart-empty">
                        <i class="fas fa-shopping-basket" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5; color: var(--royal-blue);"></i>
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Panier vide</div>
                        <div style="font-size: 0.8rem;">Recherchez des produits pour commencer</div>
                    </div>
                </div>
                
                <!-- Résumé -->
                <div class="cart-summary" id="cart-summary" style="display: none;">
                    <div class="summary-row">
                        <span>Sous-total:</span>
                        <span class="summary-value" id="subtotal">0.000 TND</span>
                    </div>
                    <div class="summary-row">
                        <span>Total:</span>
                        <span class="summary-value" id="total">0.000 TND</span>
                    </div>
                </div>
                
                <!-- Contrôles -->
                <div class="order-controls">
                    <div class="control-section">
                        <div class="control-label">Statut</div>
                        <div class="status-options">
                            <div class="status-option nouvelle active" data-status="nouvelle">
                                <i class="fas fa-circle"></i> Nouvelle
                            </div>
                            <div class="status-option confirmée" data-status="confirmée">
                                <i class="fas fa-check-circle"></i> Confirmée
                            </div>
                        </div>
                        <input type="hidden" name="status" id="status" value="nouvelle">
                        <input type="hidden" name="priority" id="priority" value="normale">
                        
                        <!-- Champ prix total pour commandes confirmées -->
                        <div class="total-price-field" id="total-price-field">
                            <label for="total_price" class="control-label">
                                <i class="fas fa-euro-sign"></i> Prix Total Personnalisé
                            </label>
                            <input type="number" class="form-input" id="total_price" name="total_price" 
                                   step="0.001" min="0" placeholder="Laisser vide pour calcul automatique">
                            <small class="text-muted">Laissez vide pour utiliser le total calculé automatiquement</small>
                        </div>
                    </div>
                    
                    <div class="control-section">
                        <label for="employee_id" class="control-label">Assigner à</label>
                        <select class="employee-select" id="employee_id" name="employee_id">
                            <option value="">Aucun employé</option>
                            @if (isset($employees) && $employees->count() > 0)
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('admin.orders.index') }}" class="btn-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn-save" id="save-btn">
                            <i class="fas fa-save"></i> Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="cart-data" style="display: none;"></div>
    </form>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Historique Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="history-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Chargement...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let phoneTimeout;
    let hasExistingOrders = false;

    // =========================
    // VÉRIFICATION TÉLÉPHONE CORRIGÉE
    // =========================
    $('#customer_phone').on('input', function() {
        const phone = $(this).val().trim();
        clearTimeout(phoneTimeout);
        
        if (phone.length >= 8) {
            phoneTimeout = setTimeout(() => checkPhone(phone), 500);
        } else {
            resetPhone();
        }
    });

    function checkPhone(phone) {
        $('#phone-indicator').removeClass('has-duplicates clean').addClass('checking').show();
        
        $.get('/admin/orders/check-phone-duplicates', { phone })
            .done(function(response) {
                $('#phone-indicator').removeClass('checking');
                
                if (response.has_duplicates) {
                    $('#phone-indicator').addClass('has-duplicates').html('<i class="fas fa-exclamation-triangle"></i>');
                    $('#customer_phone').addClass('has-duplicates');
                    showDuplicateAlert(response);
                    setAutoPriority(true);
                } else {
                    $('#phone-indicator').addClass('clean').html('<i class="fas fa-check"></i>');
                    $('#customer_phone').removeClass('has-duplicates');
                    hideAlert();
                    setAutoPriority(false);
                }
            })
            .fail(function(xhr) {
                console.error('Erreur vérification téléphone:', xhr);
                resetPhone();
            });
    }

    function resetPhone() {
        $('#phone-indicator').removeClass('checking has-duplicates clean').hide();
        $('#customer_phone').removeClass('has-duplicates');
        hideAlert();
        setAutoPriority(false);
    }

    function showDuplicateAlert(response) {
        $('#duplicate-alert-content').html(
            `<strong>${response.total_orders} commande(s)</strong> trouvée(s) pour ce numéro.`
        );
        $('#duplicate-alert').addClass('show');
        hasExistingOrders = true;
    }

    function hideAlert() {
        $('#duplicate-alert').removeClass('show');
        hasExistingOrders = false;
    }

    function setAutoPriority(isDuplicate) {
        if (isDuplicate) {
            $('#priority').val('doublons');
            $('#auto-priority').show();
        } else {
            $('#priority').val('normale');
            $('#auto-priority').hide();
        }
    }

    window.dismissAlert = function() {
        hideAlert();
        setAutoPriority(false);
    };

    // =========================
    // HISTORIQUE ET PRÉ-REMPLISSAGE CORRIGÉS
    // =========================
    $('#view-history-btn').on('click', function() {
        const phone = $('#customer_phone').val().trim();
        if (phone) {
            loadHistory(phone);
            $('#historyModal').modal('show');
        }
    });

    $('#fill-data-btn').on('click', function() {
        const phone = $('#customer_phone').val().trim();
        if (phone) {
            $.get('/admin/orders/client-history', { phone })
                .done(function(response) {
                    if (response.latest_order) {
                        fillData(response.latest_order);
                        showNotification('success', 'Données pré-remplies !');
                    } else {
                        showNotification('warning', 'Aucune donnée à pré-remplir.');
                    }
                })
                .fail(function() {
                    showNotification('error', 'Erreur lors du chargement des données.');
                });
        }
    });

    function loadHistory(phone) {
        $('#history-content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        
        $.get('/admin/orders/client-history', { phone })
            .done(function(response) {
                let content = '';
                if (response.orders?.length) {
                    response.orders.forEach(order => {
                        content += `
                            <div class="history-item">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Commande #${order.id}</strong>
                                    <span class="badge bg-${getStatusColor(order.status)}">${order.status}</span>
                                </div>
                                <div class="small text-muted">
                                    ${order.customer_name || 'N/A'} • ${parseFloat(order.total_price).toFixed(3)} TND • ${new Date(order.created_at).toLocaleDateString('fr-FR')}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    content = '<div class="text-center py-4 text-muted">Aucun historique</div>';
                }
                $('#history-content').html(content);
            })
            .fail(function() {
                $('#history-content').html('<div class="alert alert-danger">Erreur de chargement</div>');
            });
    }

    function fillData(order) {
        $('#customer_name').val(order.customer_name || '');
        $('#customer_phone_2').val(order.customer_phone_2 || '');
        $('#customer_address').val(order.customer_address || '');
        if (order.customer_governorate) {
            $('#customer_governorate').val(order.customer_governorate).trigger('change');
            setTimeout(() => {
                if (order.customer_city) {
                    $('#customer_city').val(order.customer_city);
                }
            }, 500);
        }
    }

    function getStatusColor(status) {
        const colors = {
            'nouvelle': 'secondary',
            'confirmée': 'success',
            'annulée': 'danger',
            'datée': 'warning',
            'livrée': 'primary'
        };
        return colors[status] || 'secondary';
    }

    // =========================
    // RECHERCHE PRODUITS
    // =========================
    $('#product-search').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 2) {
            searchProducts(query);
        } else {
            $('#suggestions').hide();
        }
    });

    function searchProducts(query) {
        $.get('/admin/orders/search-products', { search: query })
            .done(data => showSuggestions(data))
            .fail(() => showSuggestions([]));
    }

    function showSuggestions(products) {
        const suggestions = $('#suggestions').empty();
        
        if (products.length === 0) {
            suggestions.html('<div class="suggestion">Aucun produit trouvé</div>');
        } else {
            products.forEach(product => {
                const item = $(`
                    <div class="suggestion d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${product.name}</strong>
                            ${product.reference ? `<span class="product-ref">${product.reference}</span>` : ''}
                            <br><small class="text-muted">Stock: ${product.stock}</small>
                        </div>
                        <div class="fw-bold text-success">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `).on('click', () => {
                    addToCart(product);
                    $('#product-search').val('');
                    suggestions.hide();
                });
                suggestions.append(item);
            });
        }
        
        suggestions.show();
    }

    $(document).on('click', e => {
        if (!$(e.target).closest('.search-group').length) {
            $('#suggestions').hide();
        }
    });

    // =========================
    // GESTION PANIER
    // =========================
    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                reference: product.reference,
                price: parseFloat(product.price),
                quantity: 1,
                stock: product.stock
            });
        }
        
        updateCart();
        showNotification('success', `${product.name} ajouté`);
    }

    function updateCart() {
        const items = $('#cart-items');
        const empty = $('#cart-empty');
        const summary = $('#cart-summary');
        
        $('#cart-count').text(cart.reduce((sum, item) => sum + item.quantity, 0));
        
        items.find('.cart-item').remove();
        
        if (cart.length === 0) {
            empty.show();
            summary.hide();
        } else {
            empty.hide();
            summary.show();
            
            cart.forEach(item => {
                items.append(createCartItem(item));
            });
            
            updateSummary();
        }
        
        updateFormData();
    }

    function createCartItem(item) {
        return $(`
            <div class="cart-item">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">${item.price.toFixed(3)} TND × ${item.quantity}</div>
                </div>
                <div class="quantity-control">
                    <button type="button" class="qty-btn minus"><i class="fas fa-minus"></i></button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" max="${item.stock}">
                    <button type="button" class="qty-btn plus"><i class="fas fa-plus"></i></button>
                </div>
                <button type="button" class="remove-btn"><i class="fas fa-trash"></i></button>
            </div>
        `).on('click', '.minus', () => updateQuantity(item.id, item.quantity - 1))
          .on('click', '.plus', () => updateQuantity(item.id, item.quantity + 1))
          .on('change', '.qty-input', function() { updateQuantity(item.id, parseInt($(this).val()) || 1); })
          .on('click', '.remove-btn', () => removeFromCart(item.id));
    }

    function updateQuantity(id, newQty) {
        const item = cart.find(i => i.id === id);
        if (item) {
            item.quantity = Math.max(1, Math.min(newQty, item.stock));
            updateCart();
        }
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        updateCart();
    }

    function updateSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        $('#subtotal').text(`${subtotal.toFixed(3)} TND`);
        $('#total').text(`${subtotal.toFixed(3)} TND`);
    }

    function updateFormData() {
        const data = $('#cart-data').empty();
        cart.forEach((item, index) => {
            data.append(`<input type="hidden" name="products[${index}][id]" value="${item.id}">`);
            data.append(`<input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">`);
        });
    }

    // =========================
    // STATUT ET PRIX TOTAL
    // =========================
    $('.status-option').on('click', function() {
        $('.status-option').removeClass('active');
        $(this).addClass('active');
        const status = $(this).data('status');
        $('#status').val(status);
        
        // Afficher/masquer le champ prix total
        if (status === 'confirmée') {
            $('#total-price-field').addClass('show');
        } else {
            $('#total-price-field').removeClass('show');
        }
    });

    // =========================
    // VALIDATION COMPLÈTE
    // =========================
    $('#customer_governorate').on('change', function() {
        const regionId = $(this).val();
        const citySelect = $('#customer_city');
        
        if (regionId) {
            $.get('/admin/orders/get-cities', { region_id: regionId })
                .done(cities => {
                    citySelect.html('<option value="">Choisir...</option>');
                    cities.forEach(city => {
                        citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                    });
                });
        } else {
            citySelect.html('<option value="">Choisir...</option>');
        }
    });

    $('#orderForm').on('submit', function(e) {
        const errors = [];
        
        // Validation des champs obligatoires
        if (!$('#customer_name').val().trim()) {
            errors.push('Nom complet requis');
        }
        
        if (!$('#customer_phone').val().trim()) {
            errors.push('Téléphone requis');
        }
        
        if (!$('#customer_governorate').val()) {
            errors.push('Gouvernorat requis');
        }
        
        if (!$('#customer_city').val()) {
            errors.push('Ville requise');
        }
        
        if (!$('#customer_address').val().trim()) {
            errors.push('Adresse requise');
        }
        
        if (cart.length === 0) {
            errors.push('Panier vide');
        }

        if (errors.length > 0) {
            e.preventDefault();
            showNotification('error', errors.join(', '));
            return false;
        }

        $('#save-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création...');
    });

    function showNotification(type, message) {
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b'
        };
        
        const notification = $(`
            <div style="position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 1rem; border-radius: 6px; color: white; font-weight: 600; background: ${colors[type]}; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        setTimeout(() => notification.fadeOut(() => notification.remove()), 3000);
    }

    $('#customer_phone').focus();
});
</script>
@endsection