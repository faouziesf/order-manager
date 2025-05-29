@extends('layouts.admin')

@section('title', 'Nouvelle Commande')
@section('page-title', 'Nouvelle Commande')

@section('css')
<style>
    .form-container {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .split-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 24px;
        height: calc(100vh - 200px);
    }
    
    .left-panel {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow-y: auto;
    }
    
    .right-panel {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 0;
        height: fit-content;
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .form-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .section-title i {
        color: #6366f1;
        font-size: 1.2rem;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
        display: block;
    }
    
    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: #f9fafb;
    }
    
    .form-control:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    
    .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        background: #f9fafb;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    
    /* PANIER STYLES */
    .cart-container {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        margin-bottom: 24px;
    }
    
    .cart-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: between;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }
    
    .cart-header h5 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    
    .cart-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .cart-toggle:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .cart-summary {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .cart-body {
        padding: 20px;
        transition: all 0.3s ease;
        max-height: 600px;
        overflow-y: auto;
    }
    
    .cart-body.collapsed {
        max-height: 0;
        padding: 0 20px;
        overflow: hidden;
    }
    
    .product-search {
        position: relative;
        margin-bottom: 20px;
    }
    
    .product-search-input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: white;
    }
    
    .product-search-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    
    .product-search i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
    }
    
    .product-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .product-suggestion {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s ease;
    }
    
    .product-suggestion:hover {
        background: #f3f4f6;
    }
    
    .product-suggestion:last-child {
        border-bottom: none;
    }
    
    .suggestion-info h6 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }
    
    .suggestion-info small {
        color: #6b7280;
        font-size: 12px;
    }
    
    .suggestion-price {
        font-weight: 600;
        color: #10b981;
        font-family: 'JetBrains Mono', monospace;
    }
    
    .cart-items {
        space-y: 12px;
    }
    
    .cart-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .cart-item:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
    }
    
    .cart-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    
    .cart-item-info {
        flex: 1;
    }
    
    .cart-item-name {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .cart-item-price {
        color: #10b981;
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
    }
    
    .cart-item-remove {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 6px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .cart-item-remove:hover {
        background: #fecaca;
        transform: rotate(90deg);
    }
    
    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: white;
    }
    
    .quantity-btn {
        background: none;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        font-weight: 600;
        color: #6366f1;
        transition: all 0.2s ease;
        font-size: 16px;
        line-height: 1;
    }
    
    .quantity-btn:hover {
        background: #f3f4f6;
    }
    
    .quantity-btn:disabled {
        color: #d1d5db;
        cursor: not-allowed;
    }
    
    .quantity-input {
        border: none;
        text-align: center;
        width: 50px;
        padding: 8px 4px;
        font-weight: 600;
        color: #374151;
        background: transparent;
    }
    
    .quantity-input:focus {
        outline: none;
        background: #f9fafb;
    }
    
    .cart-item-total {
        font-weight: 700;
        color: #374151;
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
    }
    
    .new-product-form {
        background: #f0f9ff;
        border: 2px dashed #0ea5e9;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        display: none;
    }
    
    .new-product-form.show {
        display: block;
        animation: slideDown 0.3s ease;
    }
    
    .new-product-grid {
        display: grid;
        grid-template-columns: 1fr 120px;
        gap: 12px;
        margin-bottom: 12px;
    }
    
    .cart-total {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #10b981;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
        text-align: center;
    }
    
    .cart-total-label {
        font-size: 14px;
        color: #065f46;
        margin-bottom: 4px;
    }
    
    .cart-total-amount {
        font-size: 24px;
        font-weight: 700;
        color: #047857;
        font-family: 'JetBrains Mono', monospace;
    }
    
    .add-product-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    
    .add-product-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .add-new-product-btn {
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .add-new-product-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    }
    
    .submit-buttons {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        flex: 1;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .btn-secondary {
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        color: #374151;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-secondary:hover {
        background: #e5e7eb;
        color: #374151;
        text-decoration: none;
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
        border-radius: 8px;
        z-index: 100;
    }
    
    .loading-overlay.show {
        display: flex;
    }
    
    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Error states */
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc2626;
        background-color: #fef2f2;
    }
    
    .invalid-feedback {
        color: #dc2626;
        font-size: 12px;
        margin-top: 4px;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .split-layout {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .right-panel {
            position: static;
            max-height: none;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .form-grid-3 {
            grid-template-columns: 1fr;
        }
        
        .split-layout {
            height: auto;
        }
        
        .left-panel,
        .right-panel {
            height: auto;
            max-height: none;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 text-gradient mb-2">Nouvelle Commande</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Créez une nouvelle commande avec un panier interactif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.orders.store') }}" id="orderForm">
        @csrf
        
        <div class="split-layout">
            <!-- Left Panel - Customer Info -->
            <div class="left-panel">
                <!-- Section Client -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user"></i>
                        Informations Client
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customer_phone" class="form-label">
                                Téléphone <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control @error('customer_phone') is-invalid @enderror" 
                                   id="customer_phone" 
                                   name="customer_phone" 
                                   value="{{ old('customer_phone') }}" 
                                   required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone_2" class="form-label">Téléphone 2</label>
                            <input type="tel" 
                                   class="form-control @error('customer_phone_2') is-invalid @enderror" 
                                   id="customer_phone_2" 
                                   name="customer_phone_2" 
                                   value="{{ old('customer_phone_2') }}">
                            @error('customer_phone_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_name" class="form-label">Nom du client</label>
                        <input type="text" 
                               class="form-control @error('customer_name') is-invalid @enderror" 
                               id="customer_name" 
                               name="customer_name" 
                               value="{{ old('customer_name') }}">
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Section Adresse -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Adresse de Livraison
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customer_governorate" class="form-label">Gouvernorat</label>
                            <select class="form-select @error('customer_governorate') is-invalid @enderror" 
                                    id="customer_governorate" 
                                    name="customer_governorate">
                                <option value="">Sélectionner un gouvernorat</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('customer_governorate') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_governorate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_city" class="form-label">Ville</label>
                            <select class="form-select @error('customer_city') is-invalid @enderror" 
                                    id="customer_city" 
                                    name="customer_city" 
                                    disabled>
                                <option value="">Sélectionner une ville</option>
                            </select>
                            @error('customer_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_address" class="form-label">Adresse complète</label>
                        <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                  id="customer_address" 
                                  name="customer_address" 
                                  rows="3">{{ old('customer_address') }}</textarea>
                        @error('customer_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Section Commande -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-cog"></i>
                        Paramètres de la Commande
                    </div>
                    
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label for="status" class="form-label">
                                Statut <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="nouvelle" {{ old('status', 'nouvelle') == 'nouvelle' ? 'selected' : '' }}>
                                    Nouvelle
                                </option>
                                <option value="confirmée" {{ old('status') == 'confirmée' ? 'selected' : '' }}>
                                    Confirmée
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="form-label">
                                Priorité <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('priority') is-invalid @enderror" 
                                    id="priority" 
                                    name="priority" 
                                    required>
                                <option value="normale" {{ old('priority', 'normale') == 'normale' ? 'selected' : '' }}>
                                    Normale
                                </option>
                                <option value="urgente" {{ old('priority') == 'urgente' ? 'selected' : '' }}>
                                    Urgente
                                </option>
                                <option value="vip" {{ old('priority') == 'vip' ? 'selected' : '' }}>
                                    VIP
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_cost" class="form-label">Frais de Livraison (TND)</label>
                            <input type="number" 
                                   class="form-control @error('shipping_cost') is-invalid @enderror" 
                                   id="shipping_cost" 
                                   name="shipping_cost" 
                                   value="{{ old('shipping_cost', '0') }}" 
                                   step="0.001" 
                                   min="0">
                            @error('shipping_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section Notes -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-sticky-note"></i>
                        Notes
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes de la commande</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4" 
                                  placeholder="Notes internes, commentaires...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Right Panel - Cart -->
            <div class="right-panel">
                <!-- Cart Container -->
                <div class="cart-container">
                    <div class="cart-header" onclick="toggleCart()">
                        <h5>
                            <i class="fas fa-shopping-cart"></i>
                            Panier
                        </h5>
                        <div class="cart-summary">
                            <span id="cartCount">0 produit(s)</span>
                            <button type="button" class="cart-toggle" id="cartToggle">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="cart-body" id="cartBody">
                        <!-- Product Search -->
                        <div class="product-search">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   class="product-search-input" 
                                   id="productSearch" 
                                   placeholder="Rechercher un produit...">
                            <div class="product-suggestions" id="productSuggestions"></div>
                        </div>
                        
                        <!-- Add New Product Button -->
                        <button type="button" class="add-new-product-btn" onclick="toggleNewProductForm()">
                            <i class="fas fa-plus"></i>
                            Créer un nouveau produit
                        </button>
                        
                        <!-- New Product Form -->
                        <div class="new-product-form" id="newProductForm">
                            <div class="new-product-grid">
                                <input type="text" 
                                       class="form-control" 
                                       id="newProductName" 
                                       placeholder="Nom du produit">
                                <input type="number" 
                                       class="form-control" 
                                       id="newProductPrice" 
                                       placeholder="Prix" 
                                       step="0.001" 
                                       min="0">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm flex-1" onclick="addNewProduct()">
                                    <i class="fas fa-check me-1"></i>Ajouter
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="cancelNewProduct()">
                                    <i class="fas fa-times me-1"></i>Annuler
                                </button>
                            </div>
                        </div>
                        
                        <!-- Cart Items -->
                        <div class="cart-items" id="cartItems">
                            <div class="text-center text-muted py-4" id="emptyCart">
                                <i class="fas fa-shopping-cart fa-2x mb-3 opacity-50"></i>
                                <p>Votre panier est vide</p>
                                <small>Recherchez et ajoutez des produits</small>
                            </div>
                        </div>
                        
                        <!-- Cart Total -->
                        <div class="cart-total" id="cartTotal" style="display: none;">
                            <div class="cart-total-label">Total HT</div>
                            <div class="cart-total-amount">0.000 TND</div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="submit-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save me-2"></i>Créer la Commande
                    </button>
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary w-100">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Hidden Products Input -->
        <div id="productsInputs"></div>
    </form>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="pageLoader">
    <div class="spinner"></div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let productSearchTimeout;
    let cartCollapsed = false;
    
    // ================================
    // GESTION DU PANIER
    // ================================
    
    function toggleCart() {
        cartCollapsed = !cartCollapsed;
        const cartBody = $('#cartBody');
        const cartToggle = $('#cartToggle i');
        
        if (cartCollapsed) {
            cartBody.addClass('collapsed');
            cartToggle.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            cartBody.removeClass('collapsed');
            cartToggle.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    }
    
    function updateCartDisplay() {
        const cartItems = $('#cartItems');
        const emptyCart = $('#emptyCart');
        const cartTotal = $('#cartTotal');
        const cartCount = $('#cartCount');
        const submitBtn = $('#submitBtn');
        
        if (cart.length === 0) {
            emptyCart.show();
            cartTotal.hide();
            submitBtn.prop('disabled', true);
            cartCount.text('0 produit(s)');
        } else {
            emptyCart.hide();
            cartTotal.show();
            submitBtn.prop('disabled', false);
            cartCount.text(`${cart.length} produit(s)`);
            
            // Calculer le total
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
            });
            
            $('#cartTotal .cart-total-amount').text(total.toFixed(3) + ' TND');
        }
        
        // Mettre à jour l'affichage des items
        renderCartItems();
        updateHiddenInputs();
    }
    
    function renderCartItems() {
        const container = $('#cartItems');
        container.empty();
        
        if (cart.length === 0) {
            container.append(`
                <div class="text-center text-muted py-4" id="emptyCart">
                    <i class="fas fa-shopping-cart fa-2x mb-3 opacity-50"></i>
                    <p>Votre panier est vide</p>
                    <small>Recherchez et ajoutez des produits</small>
                </div>
            `);
            return;
        }
        
        cart.forEach((item, index) => {
            const itemHtml = `
                <div class="cart-item" data-index="${index}">
                    <div class="cart-item-header">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">${parseFloat(item.price).toFixed(3)} TND/unité</div>
                        </div>
                        <button type="button" class="cart-item-remove" onclick="removeFromCart(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-control">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(${index}, -1)" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" min="1" onchange="setQuantity(${index}, this.value)">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <div class="cart-item-total">${(item.price * item.quantity).toFixed(3)} TND</div>
                    </div>
                </div>
            `;
            container.append(itemHtml);
        });
    }
    
    function updateHiddenInputs() {
        const container = $('#productsInputs');
        container.empty();
        
        cart.forEach((item, index) => {
            container.append(`
                <input type="hidden" name="products[${index}][id]" value="${item.id}">
                <input type="hidden" name="products[${index}][name]" value="${item.name}">
                <input type="hidden" name="products[${index}][price]" value="${item.price}">
                <input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">
                ${item.is_new ? '<input type="hidden" name="products[' + index + '][is_new]" value="1">' : ''}
            `);
        });
    }
    
    // ================================
    // FONCTIONS PUBLIQUES DU PANIER
    // ================================
    
    window.addToCart = function(product) {
        const existingIndex = cart.findIndex(item => item.id === product.id);
        
        if (existingIndex >= 0) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: 1,
                is_new: product.is_new || false
            });
        }
        
        updateCartDisplay();
        showNotification(`${product.name} ajouté au panier`, 'success');
    };
    
    window.removeFromCart = function(index) {
        const item = cart[index];
        cart.splice(index, 1);
        updateCartDisplay();
        showNotification(`${item.name} retiré du panier`, 'info');
    };
    
    window.updateQuantity = function(index, change) {
        const newQuantity = cart[index].quantity + change;
        if (newQuantity >= 1) {
            cart[index].quantity = newQuantity;
            updateCartDisplay();
        }
    };
    
    window.setQuantity = function(index, quantity) {
        const qty = parseInt(quantity) || 1;
        if (qty >= 1) {
            cart[index].quantity = qty;
            updateCartDisplay();
        }
    };
    
    // ================================
    // RECHERCHE DE PRODUITS
    // ================================
    
    $('#productSearch').on('input', function() {
        const query = $(this).val().trim();
        
        clearTimeout(productSearchTimeout);
        
        if (query.length < 2) {
            $('#productSuggestions').hide().empty();
            return;
        }
        
        productSearchTimeout = setTimeout(() => {
            searchProducts(query);
        }, 300);
    });
    
    function searchProducts(query) {
        $.ajax({
            url: '{{ route("admin.orders.searchProducts") }}',
            method: 'GET',
            data: { search: query },
            success: function(products) {
                displayProductSuggestions(products);
            },
            error: function() {
                showNotification('Erreur lors de la recherche', 'error');
            }
        });
    }
    
    function displayProductSuggestions(products) {
        const container = $('#productSuggestions');
        container.empty();
        
        if (products.length === 0) {
            container.html('<div class="product-suggestion"><em>Aucun produit trouvé</em></div>');
        } else {
            products.forEach(product => {
                const suggestionHtml = `
                    <div class="product-suggestion" onclick="addToCart({id: ${product.id}, name: '${product.name}', price: ${product.price}})">
                        <div class="suggestion-info">
                            <h6>${product.name}</h6>
                            <small>Stock: ${product.stock}</small>
                        </div>
                        <div class="suggestion-price">${parseFloat(product.price).toFixed(3)} TND</div>
                    </div>
                `;
                container.append(suggestionHtml);
            });
        }
        
        container.show();
    }
    
    // Masquer les suggestions quand on clique ailleurs
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.product-search').length) {
            $('#productSuggestions').hide();
        }
    });
    
    // ================================
    // NOUVEAU PRODUIT
    // ================================
    
    window.toggleNewProductForm = function() {
        $('#newProductForm').toggleClass('show');
        if ($('#newProductForm').hasClass('show')) {
            $('#newProductName').focus();
        }
    };
    
    window.addNewProduct = function() {
        const name = $('#newProductName').val().trim();
        const price = parseFloat($('#newProductPrice').val()) || 0;
        
        if (!name) {
            showNotification('Veuillez saisir un nom de produit', 'warning');
            return;
        }
        
        if (price <= 0) {
            showNotification('Veuillez saisir un prix valide', 'warning');
            return;
        }
        
        const newProduct = {
            id: 'new_' + Date.now(),
            name: name,
            price: price,
            is_new: true
        };
        
        addToCart(newProduct);
        cancelNewProduct();
    };
    
    window.cancelNewProduct = function() {
        $('#newProductForm').removeClass('show');
        $('#newProductName, #newProductPrice').val('');
    };
    
    // ================================
    // GESTION DES ADRESSES
    // ================================
    
    $('#customer_governorate').on('change', function() {
        const regionId = $(this).val();
        const citySelect = $('#customer_city');
        
        citySelect.empty().append('<option value="">Chargement...</option>').prop('disabled', true);
        
        if (!regionId) {
            citySelect.empty().append('<option value="">Sélectionner une ville</option>');
            return;
        }
        
        $.ajax({
            url: '{{ route("admin.orders.getCities") }}',
            method: 'GET',
            data: { region_id: regionId },
            success: function(cities) {
                citySelect.empty().append('<option value="">Sélectionner une ville</option>');
                
                cities.forEach(city => {
                    citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                });
                
                citySelect.prop('disabled', false);
            },
            error: function() {
                citySelect.empty().append('<option value="">Erreur de chargement</option>');
                showNotification('Erreur lors du chargement des villes', 'error');
            }
        });
    });
    
    // ================================
    // VALIDATION DU FORMULAIRE
    // ================================
    
    $('#orderForm').on('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            showNotification('Veuillez ajouter au moins un produit au panier', 'warning');
            return false;
        }
        
        const status = $('#status').val();
        if (status === 'confirmée') {
            const requiredFields = ['customer_name', 'customer_governorate', 'customer_city', 'customer_address'];
            let hasError = false;
            
            requiredFields.forEach(field => {
                const input = $(`#${field}`);
                if (!input.val().trim()) {
                    input.addClass('is-invalid');
                    hasError = true;
                } else {
                    input.removeClass('is-invalid');
                }
            });
            
            if (hasError) {
                e.preventDefault();
                showNotification('Tous les champs client sont obligatoires pour une commande confirmée', 'warning');
                return false;
            }
        }
        
        // Montrer le loader
        $('#pageLoader').addClass('show');
    });
    
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
    
    // Initialiser l'affichage
    updateCartDisplay();
});
</script>
@endsection