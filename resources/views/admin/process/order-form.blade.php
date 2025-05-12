@extends('layouts.admin')

@section('title', 'Traitement de la commande #' . $order->id)

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    :root {
        --primary-color: #4e73df;
        --success-color: #1cc88a;
        --danger-color: #e74a3b;
        --warning-color: #f6c23e;
        --info-color: #36b9cc;
        --secondary-color: #858796;
        --light-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    /* Main container */
    .order-process-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-nouvelle {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-confirmée {
        background: #d4edda;
        color: #155724;
    }

    .status-annulée {
        background: #f8d7da;
        color: #721c24;
    }

    .status-datée {
        background: #fff3cd;
        color: #856404;
    }

    .priority-normale {
        background: #e9ecef;
        color: #495057;
    }

    .priority-urgente {
        background: #fff3cd;
        color: #856404;
    }

    .priority-vip {
        background: #f8d7da;
        color: #721c24;
    }

    /* Header section */
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        background: white;
        z-index: 100;
    }

    .order-info-badges {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* Main content grid */
    .order-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
    }

    @media (max-width: 992px) {
        .order-content {
            grid-template-columns: 1fr;
        }
    }

    /* Client form styles */
    .client-form-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 20px;
    }

    .form-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        color: var(--primary-color);
    }

    .client-form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .required-field::after {
        content: " *";
        color: var(--danger-color);
    }

    .error-message {
        color: var(--danger-color);
        font-size: 0.8rem;
        margin-top: 5px;
        display: none;
    }

    .form-control.is-invalid {
        border-color: var(--danger-color);
    }

    .form-control.is-invalid + .error-message {
        display: block;
    }

    /* Cart summary */
    .cart-summary {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .cart-summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .cart-summary-total {
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        padding-top: 8px;
        border-top: 1px solid var(--border-color);
        margin-top: 8px;
    }

    /* Product section */
    .product-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .product-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-color);
    }

    .product-toggle-btn {
        background: none;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 6px 12px;
        cursor: pointer;
        color: var(--secondary-color);
        transition: all 0.2s;
    }

    .product-toggle-btn:hover {
        background: var(--light-bg);
    }

    .product-content {
        padding: 20px;
        max-height: 400px;
        overflow-y: auto;
    }

    .product-list {
        display: grid;
        gap: 15px;
    }

    .product-item {
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 12px;
        background: #f8f9fc;
        position: relative;
    }

    .product-item.new-product-form {
        border-style: dashed;
        background: #fff;
    }

    .product-item-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .product-remove {
        position: absolute;
        top: 8px;
        right: 8px;
        background: none;
        border: none;
        color: var(--danger-color);
        cursor: pointer;
        padding: 0;
        font-size: 1.2rem;
    }

    .product-fields {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 10px;
        align-items: center;
    }

    .product-total {
        text-align: right;
        font-weight: 600;
        margin-top: 8px;
        color: var(--primary-color);
    }

    .add-product-btn {
        background: white;
        border: 1px dashed var(--border-color);
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        color: var(--primary-color);
        transition: all 0.2s;
        width: 100%;
        margin-top: 10px;
    }

    .add-product-btn:hover {
        background: var(--light-bg);
    }

    /* Action section */
    .action-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 20px;
        position: sticky;
        top: 80px;
    }

    .action-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        color: var(--primary-color);
    }

    .action-options {
        margin-bottom: 20px;
    }

    .conditional-form {
        background: #f8f9fc;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        display: none;
    }

    .action-notes {
        margin-bottom: 20px;
    }

    .notes-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .action-button {
        width: 100%;
        padding: 12px 15px;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--success-color);
        color: white;
    }

    .action-button:hover {
        opacity: 0.9;
    }

    .action-button:disabled {
        background: var(--secondary-color);
        cursor: not-allowed;
    }

    /* Helper classes */
    .text-primary { color: var(--primary-color) !important; }
    .text-success { color: var(--success-color) !important; }
    .text-danger { color: var(--danger-color) !important; }
    .text-warning { color: var(--warning-color) !important; }
    .text-info { color: var(--info-color) !important; }
    .text-secondary { color: var(--secondary-color) !important; }

    .bg-light { background-color: var(--light-bg) !important; }

    .mt-2 { margin-top: 0.5rem !important; }
    .mb-2 { margin-bottom: 0.5rem !important; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mb-4 { margin-bottom: 1.5rem !important; }

    .py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
    .px-3 { padding-left: 1rem !important; padding-right: 1rem !important; }

    .d-flex { display: flex !important; }
    .flex-column { flex-direction: column !important; }
    .justify-content-between { justify-content: space-between !important; }
    .align-items-center { align-items: center !important; }
    .gap-2 { gap: 0.5rem !important; }

    /* History section */
    .history-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-top: 30px;
        display: none;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    .history-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-color);
    }

    .history-close {
        background: none;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 6px 12px;
        cursor: pointer;
        color: var(--secondary-color);
        transition: all 0.2s;
    }

    .history-close:hover {
        background: var(--light-bg);
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 10px;
        width: 2px;
        background-color: var(--border-color);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -30px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: white;
        border: 2px solid var(--primary-color);
        z-index: 1;
    }

    .timeline-item.tentative::before {
        border-color: var(--info-color);
    }

    .timeline-item.confirmation::before {
        border-color: var(--success-color);
    }

    .timeline-item.annulation::before {
        border-color: var(--danger-color);
    }

    .timeline-item.datation::before {
        border-color: var(--warning-color);
    }

    .timeline-content {
        background: #f8f9fc;
        border-radius: 6px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .timeline-title {
        font-weight: 600;
        margin: 0;
    }

    .timeline-date {
        font-size: 0.8rem;
        color: var(--secondary-color);
    }

    .timeline-user {
        font-size: 0.85rem;
        color: var(--secondary-color);
        margin-bottom: 8px;
    }

    .timeline-notes {
        font-size: 0.9rem;
        background: white;
        padding: 8px 12px;
        border-radius: 4px;
        border-left: 3px solid var(--primary-color);
    }

    /* Notifications */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        max-width: 350px;
        padding: 15px 20px;
        border-radius: 6px;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-100px);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .notification.show {
        transform: translateY(0);
        opacity: 1;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .notification-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .notification-success .notification-icon {
        background-color: var(--success-color);
    }

    .notification-error .notification-icon {
        background-color: var(--danger-color);
    }

    .notification-warning .notification-icon {
        background-color: var(--warning-color);
    }

    .notification-info .notification-icon {
        background-color: var(--info-color);
    }

    .notification-text {
        font-size: 0.9rem;
    }

    .notification-close {
        position: absolute;
        top: 8px;
        right: 8px;
        background: none;
        border: none;
        cursor: pointer;
        color: var(--secondary-color);
        font-size: 1.2rem;
    }

    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 5px solid var(--light-bg);
        border-top-color: var(--primary-color);
        animation: spin 1s infinite linear;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Select2 customization */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 36px;
        border: 1px solid #d1d3e2;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    /* New product form */
    .new-product-fields {
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: 10px;
    }

    .new-product-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
        gap: 10px;
    }

    .btn-cancel, .btn-add {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .btn-cancel {
        background: #f8f9fc;
        border: 1px solid var(--border-color);
        color: var(--secondary-color);
    }

    .btn-add {
        background: var(--primary-color);
        border: 1px solid var(--primary-color);
        color: white;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .client-form {
            grid-template-columns: 1fr;
        }
        
        .product-fields {
            grid-template-columns: 1fr;
        }
        
        .order-content {
            grid-template-columns: 1fr;
        }
        
        .new-product-fields {
            grid-template-columns: 1fr;
        }
    }
</style>


@section('content')
<div class="order-process-container">
    <!-- Header -->
    <div class="order-header">
        <div>
            <h1>Traitement commande #{{ $order->id }}</h1>
            <p class="text-secondary mb-0">File {{ ucfirst($queueType) }}</p>
        </div>
        <div class="order-info-badges">
            <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            <span class="status-badge priority-{{ $order->priority }}">{{ ucfirst($order->priority) }}</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleHistoryBtn">
                <i class="fas fa-history"></i> Historique
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="order-content">
        <!-- Left Column: Client Info & Products -->
        <div class="left-column">
            <!-- Client Info Form -->
            <div class="client-form-container">
                <h3 class="form-title">Informations client</h3>
                <form id="processForm" action="{{ route('admin.process.action', $order) }}" method="POST">
                    @csrf
                    <input type="hidden" name="queue" value="{{ $queueType }}">
                    
                    <div class="client-form">
                        <div class="form-group">
                            <label for="customer_phone" class="required-field">Téléphone</label>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                            <div class="error-message" id="phone_error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone_2">Téléphone 2</label>
                            <input type="tel" class="form-control" id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2', $order->customer_phone_2) }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_name">Nom du client</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}">
                            <div class="error-message" id="name_error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_cost">Frais de livraison (DT)</label>
                            <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" step="0.001" value="{{ old('shipping_cost', $order->shipping_cost) }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_governorate">Gouvernorat</label>
                            <select class="form-control select2" id="customer_governorate" name="customer_governorate">
                                <option value="">Sélectionner un gouvernorat</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ old('customer_governorate', $order->customer_governorate) == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="error-message" id="governorate_error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_city">Ville</label>
                            <select class="form-control select2" id="customer_city" name="customer_city">
                                <option value="">Sélectionner d'abord un gouvernorat</option>
                            </select>
                            <div class="error-message" id="city_error"></div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="customer_address">Adresse détaillée</label>
                            <textarea class="form-control" id="customer_address" name="customer_address" rows="2">{{ old('customer_address', $order->customer_address) }}</textarea>
                            <div class="error-message" id="address_error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut</label>
                            <select class="form-control" id="status" name="status">
                                <option value="nouvelle" {{ old('status', $order->status) == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                                <option value="confirmée" {{ old('status', $order->status) == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                                <option value="annulée" {{ old('status', $order->status) == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                <option value="datée" {{ old('status', $order->status) == 'datée' ? 'selected' : '' }}>Datée</option>
                                <option value="en_route" {{ old('status', $order->status) == 'en_route' ? 'selected' : '' }}>En route</option>
                                <option value="livrée" {{ old('status', $order->status) == 'livrée' ? 'selected' : '' }}>Livrée</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority">Priorité</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="normale" {{ old('priority', $order->priority) == 'normale' ? 'selected' : '' }}>Normale</option>
                                <option value="urgente" {{ old('priority', $order->priority) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                <option value="vip" {{ old('priority', $order->priority) == 'vip' ? 'selected' : '' }}>VIP</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Conditional sections -->
                    <div id="confirmSection" class="conditional-form mb-3" style="{{ old('status', $order->status) == 'confirmée' ? 'display: block;' : '' }}">
                        <label for="confirmed_price" class="required-field">Prix confirmé (DT)</label>
                        <input type="number" class="form-control" id="confirmed_price" name="confirmed_price" step="0.001" value="{{ old('confirmed_price', $order->confirmed_price) }}">
                        <div class="error-message" id="confirmed_price_error"></div>
                        <small class="text-muted">Veuillez confirmer le prix total de la commande</small>
                    </div>
                    
                    <div id="dateSection" class="conditional-form mb-3" style="{{ old('status', $order->status) == 'datée' ? 'display: block;' : '' }}">
                        <label for="scheduled_date" class="required-field">Date de livraison</label>
                        <input type="text" class="form-control flatpickr" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : '') }}">
                        <div class="error-message" id="scheduled_date_error"></div>
                        <small class="text-muted">Sélectionnez la date de livraison programmée</small>
                    </div>
                    
                    <!-- Cart summary -->
                    <div class="cart-summary">
                        <div class="cart-summary-item">
                            <span>Sous-total:</span>
                            <span id="subtotalDisplay">{{ number_format($order->total_price, 3) }} DT</span>
                        </div>
                        <div class="cart-summary-item">
                            <span>Frais de livraison:</span>
                            <span id="shippingDisplay">{{ number_format($order->shipping_cost, 3) }} DT</span>
                        </div>
                        <div class="cart-summary-total">
                            <span>Total:</span>
                            <span id="totalDisplay">{{ number_format($order->total_price + $order->shipping_cost, 3) }} DT</span>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Products Section -->
            <div class="product-container">
                <div class="product-header">
                    <h3>Produits</h3>
                    <button type="button" class="product-toggle-btn" id="toggleProductsBtn">
                        <i class="fas fa-eye"></i> Afficher/masquer
                    </button>
                </div>
                <div class="product-content" id="productContent">
                    <div class="product-list" id="productList">
                        @php $lineIndex = 0; @endphp
                        @forelse($order->items as $item)
                            <div class="product-item" data-line="{{ $lineIndex }}">
                                <button type="button" class="product-remove" data-line="{{ $lineIndex }}">×</button>
                                <div class="product-fields">
                                    <div>
                                        <label for="product-{{ $lineIndex }}">Produit</label>
                                        <select class="form-control product-select" id="product-{{ $lineIndex }}" name="products[{{ $lineIndex }}][id]" data-line="{{ $lineIndex }}">
                                            <option value="">Sélectionner un produit</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                                </option>
                                            @endforeach
                                            <option value="new">➕ Nouveau produit</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="quantity-{{ $lineIndex }}">Quantité</label>
                                        <input type="number" class="form-control product-quantity" id="quantity-{{ $lineIndex }}" name="products[{{ $lineIndex }}][quantity]" data-line="{{ $lineIndex }}" value="{{ $item->quantity }}" min="1">
                                    </div>
                                </div>
                                <div class="product-total" id="total-{{ $lineIndex }}">
                                    {{ number_format($item->total_price, 3) }} DT
                                </div>
                            </div>
                            @php $lineIndex++; @endphp
                        @empty
                            <div class="product-item" data-line="0">
                                <button type="button" class="product-remove" data-line="0">×</button>
                                <div class="product-fields">
                                    <div>
                                        <label for="product-0">Produit</label>
                                        <select class="form-control product-select" id="product-0" name="products[0][id]" data-line="0">
                                            <option value="">Sélectionner un produit</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                    {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                                </option>
                                            @endforeach
                                            <option value="new">➕ Nouveau produit</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="quantity-0">Quantité</label>
                                        <input type="number" class="form-control product-quantity" id="quantity-0" name="products[0][quantity]" data-line="0" value="1" min="1">
                                    </div>
                                </div>
                                <div class="product-total" id="total-0">
                                    0.000 DT
                                </div>
                            </div>
                        @endforelse
                        
                        <!-- New product form (initially hidden) -->
                        <div class="product-item new-product-form" id="newProductForm" style="display: none;">
                            <div class="new-product-fields">
                                <div>
                                    <label for="new_product_name">Nom du nouveau produit</label>
                                    <input type="text" class="form-control" id="new_product_name" placeholder="Entrez le nom du produit">
                                </div>
                                <div>
                                    <label for="new_product_price">Prix (DT)</label>
                                    <input type="number" class="form-control" id="new_product_price" step="0.001" min="0" placeholder="0.000">
                                </div>
                            </div>
                            <div class="new-product-actions">
                                <button type="button" class="btn-cancel" id="cancelNewProduct">Annuler</button>
                                <button type="button" class="btn-add" id="saveNewProduct">Ajouter</button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="add-product-btn" id="addProductBtn">
                        <i class="fas fa-plus"></i> Ajouter un produit
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Actions -->
        <div class="right-column">
            <div class="action-container">
                <h3 class="action-title">Actions à effectuer</h3>
                
                <div class="action-options">
                    <div class="form-group">
                        <label for="actionType">Action</label>
                        <select class="form-control" id="actionType" name="action">
                            <option value="">-- Choisir une action --</option>
                            <option value="call">Tentative d'appel</option>
                            <option value="confirm">Confirmer la commande</option>
                            <option value="cancel">Annuler la commande</option>
                            <option value="schedule">Dater la commande</option>
                        </select>
                        <div class="error-message" id="action_error"></div>
                    </div>
                </div>
                
                <div class="action-notes">
                    <label for="notes" class="required-field">Notes</label>
                    <textarea class="form-control notes-textarea" id="notes" name="notes" placeholder="Expliquez la raison de cette action..."></textarea>
                    <div class="error-message" id="notes_error"></div>
                </div>
                
                <div class="d-flex justify-content-center mb-3">
                    <div class="d-flex gap-2 align-items-center text-secondary">
                        <span><i class="fas fa-phone"></i></span>
                        <span><strong>{{ $order->attempts_count }}</strong> tentatives totales</span>
                        <span>|</span>
                        <span><strong>{{ $order->daily_attempts_count }}</strong> aujourd'hui</span>
                    </div>
                </div>
                
                <button type="button" class="action-button" id="submitAction">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
    
    <!-- History Section (initially hidden) -->
    <div class="history-container" id="historyContainer">
        <div class="history-header">
            <h3>Historique de la commande #{{ $order->id }}</h3>
            <button type="button" class="history-close" id="closeHistory">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>
        
        <div class="timeline" id="historyTimeline">
            <div class="text-center py-2">
                <div class="spinner" style="width: 30px; height: 30px;"></div>
                <p class="mb-0 mt-2">Chargement de l'historique...</p>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Container -->
<div id="notificationsContainer"></div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner"></div>
</div>
@endsection



@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script>
    // Configuration globale
    const config = {
        orderId: {{ $order->id }},
        queueType: "{{ $queueType }}",
        productCounter: {{ $order->items->count() > 0 ? $order->items->count() : 1 }},
        productsVisible: true,
        historyLoaded: false,
        currentNewProductLine: null
    };

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        initializeComponents();
        setupEventListeners();
        updateCartSummary();
        
        // Pré-charger les villes si un gouvernorat est sélectionné
        const governorate = document.getElementById('customer_governorate');
        if (governorate && governorate.value) {
            loadCities(governorate.value, {{ $order->customer_city ?? 'null' }});
        }
    });

    // Initialisation des composants
    function initializeComponents() {
        // Initialiser Select2
        $('.select2').select2({
            placeholder: "Sélectionner...",
            allowClear: true
        });
        
        // Initialiser Flatpickr pour les dates
        flatpickr(".flatpickr", {
            locale: "fr",
            dateFormat: "Y-m-d",
            minDate: "today"
        });
        
        // Initialiser les produits sélectionnés
        $('.product-select').each(function() {
            $(this).select2({
                placeholder: "Sélectionner un produit...",
                allowClear: true
            });
        });
    }

    // Configurer les écouteurs d'événements
    function setupEventListeners() {
        // Toggler les sections
        document.getElementById('toggleProductsBtn').addEventListener('click', toggleProductsSection);
        document.getElementById('toggleHistoryBtn').addEventListener('click', toggleHistorySection);
        document.getElementById('closeHistory').addEventListener('click', toggleHistorySection);
        
        // Gouvernorat/Ville
        document.getElementById('customer_governorate').addEventListener('change', function() {
            loadCities(this.value);
        });
        
        // Frais de livraison
        document.getElementById('shipping_cost').addEventListener('input', updateCartSummary);
        
        // Changement de statut
        document.getElementById('status').addEventListener('change', handleStatusChange);
        
        // Actions
        document.getElementById('actionType').addEventListener('change', handleActionChange);
        document.getElementById('submitAction').addEventListener('click', submitForm);
        
        // Produits
        document.getElementById('addProductBtn').addEventListener('click', addProductLine);
        document.getElementById('saveNewProduct').addEventListener('click', saveNewProduct);
        document.getElementById('cancelNewProduct').addEventListener('click', cancelNewProduct);
        
        // Délégation d'événements pour les produits dynamiques
        $(document).on('change', '.product-select', handleProductChange);
        $(document).on('change keyup', '.product-quantity', handleQuantityChange);
        $(document).on('click', '.product-remove', handleProductRemove);
    }
    
    // Afficher/Masquer la section produits
    function toggleProductsSection() {
        const productContent = document.getElementById('productContent');
        const toggleBtn = document.getElementById('toggleProductsBtn');
        
        if (productContent.style.display === 'none') {
            productContent.style.display = 'block';
            toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> Masquer';
            config.productsVisible = true;
        } else {
            productContent.style.display = 'none';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i> Afficher';
            config.productsVisible = false;
        }
    }
    
    // Afficher/Masquer la section historique
    function toggleHistorySection() {
        const historyContainer = document.getElementById('historyContainer');
        
        if (historyContainer.style.display === 'none' || historyContainer.style.display === '') {
            historyContainer.style.display = 'block';
            
            // Charger l'historique si pas encore fait
            if (!config.historyLoaded) {
                loadOrderHistory();
            }
            
            // Scrolling vers l'historique
            historyContainer.scrollIntoView({ behavior: 'smooth' });
        } else {
            historyContainer.style.display = 'none';
        }
    }
    
    // Charger les villes pour le gouvernorat sélectionné
    function loadCities(regionId, selectedCity = null) {
        if (!regionId) return;
        
        const citySelect = document.getElementById('customer_city');
        citySelect.innerHTML = '<option value="">Chargement...</option>';
        citySelect.disabled = true;
        
        fetch("{{ route('admin.orders.getCities') }}?region_id=" + regionId)
            .then(response => response.json())
            .then(cities => {
                citySelect.innerHTML = '<option value="">Sélectionner une ville...</option>';
                
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    option.selected = selectedCity && city.id == selectedCity;
                    option.dataset.shippingCost = city.shipping_cost || 0;
                    citySelect.appendChild(option);
                });
                
                citySelect.disabled = false;
                
                // Mettre à jour Select2
                $(citySelect).trigger('change');
                
                // Mettre à jour les frais de livraison si une ville est sélectionnée
                const selectedOption = citySelect.options[citySelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.shippingCost) {
                    document.getElementById('shipping_cost').value = selectedOption.dataset.shippingCost;
                    updateCartSummary();
                }
            })
            .catch(error => {
                citySelect.innerHTML = '<option value="">Erreur de chargement</option>';
                citySelect.disabled = false;
                showNotification('Erreur lors du chargement des villes', 'error');
            });
    }
    
    // Gérer le changement de statut
    function handleStatusChange() {
        const status = this.value;
        const confirmSection = document.getElementById('confirmSection');
        const dateSection = document.getElementById('dateSection');
        
        // Afficher/masquer les sections conditionnelles selon le statut
        if (status === 'confirmée') {
            confirmSection.style.display = 'block';
        } else {
            confirmSection.style.display = 'none';
        }
        
        if (status === 'datée') {
            dateSection.style.display = 'block';
        } else {
            dateSection.style.display = 'none';
        }
    }
    
    // Gérer le changement d'action
    function handleActionChange() {
        const action = this.value;
        const submitButton = document.getElementById('submitAction');
        
        // Réinitialiser les styles
        submitButton.style.backgroundColor = '';
        
        // Définir l'action sur le formulaire et ajuster le bouton en conséquence
        switch(action) {
            case 'call':
                submitButton.textContent = 'Enregistrer l\'appel';
                submitButton.style.backgroundColor = 'var(--info-color)';
                break;
                
            case 'confirm':
                submitButton.textContent = 'Confirmer la commande';
                submitButton.style.backgroundColor = 'var(--success-color)';
                
                // Sélectionner automatiquement le statut "confirmée"
                document.getElementById('status').value = 'confirmée';
                document.getElementById('status').dispatchEvent(new Event('change'));
                break;
                
            case 'cancel':
                submitButton.textContent = 'Annuler la commande';
                submitButton.style.backgroundColor = 'var(--danger-color)';
                
                // Sélectionner automatiquement le statut "annulée"
                document.getElementById('status').value = 'annulée';
                document.getElementById('status').dispatchEvent(new Event('change'));
                break;
                
            case 'schedule':
                submitButton.textContent = 'Dater la commande';
                submitButton.style.backgroundColor = 'var(--warning-color)';
                
                // Sélectionner automatiquement le statut "datée"
                document.getElementById('status').value = 'datée';
                document.getElementById('status').dispatchEvent(new Event('change'));
                break;
                
            default:
                submitButton.textContent = 'Enregistrer';
                submitButton.style.backgroundColor = 'var(--success-color)';
                break;
        }
    }
    
    // Gérer le changement de produit
    function handleProductChange() {
        const line = $(this).data('line');
        const value = $(this).val();
        
        if (value === 'new') {
            // Mémoriser la ligne en cours d'édition
            config.currentNewProductLine = line;
            
            // Afficher le formulaire d'ajout de produit
            $('#newProductForm').slideDown();
            
            // Réinitialiser la sélection
            $(this).val('').trigger('change');
        } else {
            updateProductTotal(line);
            updateCartSummary();
        }
    }
    
    // Gérer le changement de quantité
    function handleQuantityChange() {
        const line = $(this).data('line');
        updateProductTotal(line);
        updateCartSummary();
    }
    
    // Gérer la suppression d'un produit
    function handleProductRemove() {
        const line = $(this).data('line');
        
        // Si c'est la seule ligne de produit, juste réinitialiser
        if ($('.product-item').length <= 2) { // +1 pour le formulaire de nouveau produit
            const select = $(`#product-${line}`);
            const quantity = $(`#quantity-${line}`);
            
            select.val('').trigger('change');
            quantity.val(1);
quantity.val(1);
            $(`#total-${line}`).text('0.000 DT');
            updateCartSummary();
            return;
        }
        
        // Sinon, supprimer la ligne
        $(this).closest('.product-item').fadeOut(300, function() {
            $(this).remove();
            updateCartSummary();
        });
    }
    
    // Ajouter une ligne de produit
    function addProductLine() {
        const productList = document.getElementById('productList');
        const lineNumber = config.productCounter++;
        
        // Template de la nouvelle ligne
        const newLine = document.createElement('div');
        newLine.className = 'product-item';
        newLine.dataset.line = lineNumber;
        newLine.innerHTML = `
            <button type="button" class="product-remove" data-line="${lineNumber}">×</button>
            <div class="product-fields">
                <div>
                    <label for="product-${lineNumber}">Produit</label>
                    <select class="form-control product-select" id="product-${lineNumber}" name="products[${lineNumber}][id]" data-line="${lineNumber}">
                        <option value="">Sélectionner un produit</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                            </option>
                        @endforeach
                        <option value="new">➕ Nouveau produit</option>
                    </select>
                </div>
                <div>
                    <label for="quantity-${lineNumber}">Quantité</label>
                    <input type="number" class="form-control product-quantity" id="quantity-${lineNumber}" name="products[${lineNumber}][quantity]" data-line="${lineNumber}" value="1" min="1">
                </div>
            </div>
            <div class="product-total" id="total-${lineNumber}">
                0.000 DT
            </div>
        `;
        
        // Insérer avant le formulaire de nouveau produit
        const newProductForm = document.getElementById('newProductForm');
        productList.insertBefore(newLine, newProductForm);
        
        // Initialiser Select2 pour le nouveau select
        $(`#product-${lineNumber}`).select2({
            placeholder: "Sélectionner un produit...",
            allowClear: true
        });
    }
    
    // Annuler l'ajout d'un nouveau produit
    function cancelNewProduct() {
        // Cacher le formulaire
        $('#newProductForm').slideUp();
        
        // Réinitialiser les champs
        $('#new_product_name').val('');
        $('#new_product_price').val('');
        
        // Réinitialiser la ligne en cours
        config.currentNewProductLine = null;
    }
    
    // Sauvegarder un nouveau produit
    function saveNewProduct() {
        const name = $('#new_product_name').val().trim();
        const price = parseFloat($('#new_product_price').val());
        
        // Validation
        if (!name) {
            showNotification('Le nom du produit est obligatoire', 'error');
            return;
        }
        
        if (isNaN(price) || price <= 0) {
            showNotification('Veuillez entrer un prix valide', 'error');
            return;
        }
        
        // Si pas de ligne courante, ne rien faire
        if (config.currentNewProductLine === null) {
            cancelNewProduct();
            return;
        }
        
        // Créer un ID temporaire unique pour ce nouveau produit
        const tempId = 'new:' + encodeURIComponent(name) + ':' + price;
        
        // Ajouter l'option au select
        const selectElement = $(`#product-${config.currentNewProductLine}`);
        const newOption = new Option(`${name} - ${formatPrice(price)} DT [Nouveau]`, tempId, true, true);
        $(newOption).data('price', price);
        
        selectElement.append(newOption).val(tempId).trigger('change');
        
        // Mettre à jour le total
        updateProductTotal(config.currentNewProductLine);
        updateCartSummary();
        
        // Cacher et réinitialiser le formulaire
        cancelNewProduct();
        
        // Notification
        showNotification('Nouveau produit ajouté', 'success');
    }
    
    // Mettre à jour le total d'un produit
    function updateProductTotal(line) {
        const select = $(`#product-${line}`);
        const quantity = parseInt($(`#quantity-${line}`).val()) || 1;
        const totalElement = $(`#total-${line}`);
        
        let price = 0;
        
        if (select.val()) {
            if (select.val().startsWith('new:')) {
                // Pour les nouveaux produits
                const parts = select.val().split(':');
                if (parts.length >= 3) {
                    price = parseFloat(parts[2]);
                }
            } else {
                // Pour les produits existants
                price = parseFloat(select.find('option:selected').data('price')) || 0;
            }
        }
        
        const total = price * quantity;
        totalElement.text(formatPrice(total) + ' DT');
    }
    
    // Mettre à jour le résumé du panier
    function updateCartSummary() {
        let subtotal = 0;
        
        // Calculer le sous-total
        $('.product-item').each(function() {
            // Ignorer le formulaire de nouveau produit
            if ($(this).hasClass('new-product-form')) return;
            
            const line = $(this).data('line');
            const select = $(`#product-${line}`);
            const quantity = parseInt($(`#quantity-${line}`).val()) || 1;
            
            let price = 0;
            
            if (select.val()) {
                if (select.val().startsWith('new:')) {
                    // Pour les nouveaux produits
                    const parts = select.val().split(':');
                    if (parts.length >= 3) {
                        price = parseFloat(parts[2]);
                    }
                } else {
                    // Pour les produits existants
                    price = parseFloat(select.find('option:selected').data('price')) || 0;
                }
                
                subtotal += price * quantity;
            }
        });
        
        // Mettre à jour l'affichage
        const shipping = parseFloat($('#shipping_cost').val()) || 0;
        const total = subtotal + shipping;
        
        $('#subtotalDisplay').text(formatPrice(subtotal) + ' DT');
        $('#shippingDisplay').text(formatPrice(shipping) + ' DT');
        $('#totalDisplay').text(formatPrice(total) + ' DT');
        
        // Mettre à jour le prix confirmé si visible
        if ($('#confirmSection').is(':visible')) {
            $('#confirmed_price').val(formatPrice(total));
        }
    }
    
    // Charger l'historique des commandes
    function loadOrderHistory() {
        const timeline = document.getElementById('historyTimeline');
        
        // Afficher le chargement
        timeline.innerHTML = `
            <div class="text-center py-2">
                <div class="spinner" style="width: 30px; height: 30px;"></div>
                <p class="mb-0 mt-2">Chargement de l'historique...</p>
            </div>
        `;
        
        // Charger l'historique via AJAX
        fetch("{{ route('admin.orders.history', $order) }}")
            .then(response => response.text())
            .then(html => {
                timeline.innerHTML = html;
                config.historyLoaded = true;
            })
            .catch(error => {
                timeline.innerHTML = `
                    <div class="text-center py-2 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <p>Erreur lors du chargement de l'historique.</p>
                    </div>
                `;
            });
    }
    
    // Validation du formulaire
    function validateForm() {
        let isValid = true;
        let errors = {};
        
        // Récupérer les valeurs
        const action = $('#actionType').val();
        const notes = $('#notes').val().trim();
        const status = $('#status').val();
        
        // Valider les notes
        if (action && !notes) {
            errors.notes = 'Les notes sont obligatoires';
            isValid = false;
        }
        
        // Valider selon le statut ou l'action
        if (status === 'confirmée' || action === 'confirm') {
            // Champs obligatoires pour une confirmation
            const requiredFields = {
                'customer_name': 'Le nom du client est obligatoire',
                'customer_phone': 'Le téléphone est obligatoire',
                'customer_governorate': 'Le gouvernorat est obligatoire',
                'customer_city': 'La ville est obligatoire',
                'customer_address': 'L\'adresse est obligatoire'
            };
            
            for (const [field, message] of Object.entries(requiredFields)) {
                if (!$(`#${field}`).val()) {
                    errors[field] = message;
                    isValid = false;
                }
            }
            
            // Prix confirmé obligatoire
            if (!$('#confirmed_price').val()) {
                errors.confirmed_price = 'Le prix confirmé est obligatoire';
                isValid = false;
            }
        }
        
        // Valider si on date la commande
        if (status === 'datée' || action === 'schedule') {
            if (!$('#scheduled_date').val()) {
                errors.scheduled_date = 'La date programmée est obligatoire';
                isValid = false;
            }
        }
        
        // Valider le panier
        let hasValidProducts = false;
        $('.product-select').each(function() {
            if ($(this).val() && $(this).val() !== 'new') {
                hasValidProducts = true;
                return false; // sortir de la boucle
            }
        });
        
        if (!hasValidProducts) {
            errors.products = 'Veuillez sélectionner au moins un produit';
            isValid = false;
        }
        
        // Afficher les erreurs
        $('.error-message').hide(); // Masquer toutes les erreurs
        $('.form-control').removeClass('is-invalid');
        
        for (const [field, message] of Object.entries(errors)) {
            $(`#${field}`).addClass('is-invalid');
            $(`#${field}_error`).text(message).show();
        }
        
        return isValid;
    }
    
    // Soumission du formulaire
    function submitForm() {
        // Valider le formulaire
        if (!validateForm()) {
            showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
            return;
        }
        
        // Récupérer l'action et les notes
        const action = $('#actionType').val();
        const notes = $('#notes').val();
        
        // Préparer les données pour la soumission
        const form = document.getElementById('processForm');
        const formData = new FormData(form);
        
        // Ajouter l'action spécifique si choisie
        if (action) {
            formData.append('action', action);
        }
        
        // Ajouter les notes si présentes
        if (notes) {
            formData.append('notes', notes);
        }
        
        // Si l'action est "call", incrémenter les tentatives
        if (action === 'call') {
            formData.append('increment_attempts', '1');
        }
        
        // Collecter et ajouter les produits
        $('.product-item').each(function(index) {
            // Ignorer le formulaire de nouveau produit
            if ($(this).hasClass('new-product-form')) return;
            
            const line = $(this).data('line');
            const select = $(`#product-${line}`);
            const quantity = $(`#quantity-${line}`).val();
            
            if (select.val()) {
                if (select.val().startsWith('new:')) {
                    // Nouveau produit
                    const parts = select.val().split(':');
                    if (parts.length >= 3) {
                        formData.append(`products[${index}][is_new]`, '1');
                        formData.append(`products[${index}][name]`, decodeURIComponent(parts[1]));
                        formData.append(`products[${index}][price]`, parts[2]);
                        formData.append(`products[${index}][quantity]`, quantity);
                    }
                } else {
                    // Produit existant
                    formData.append(`products[${index}][id]`, select.val());
                    formData.append(`products[${index}][quantity]`, quantity);
                }
            }
        });
        
        // Désactiver le bouton de soumission
        const submitButton = document.getElementById('submitAction');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = '<div class="spinner" style="width: 20px; height: 20px; display: inline-block;"></div> Traitement...';
        
        // Afficher le chargement
        document.getElementById('loadingOverlay').style.display = 'flex';
        
        // Soumettre le formulaire via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la soumission');
            }
            return response.text();
        })
        .then(data => {
            // Rediriger vers l'interface de traitement
            window.location.href = "{{ route('admin.process.interface') }}#{{ $queueType }}";
        })
        .catch(error => {
            // Afficher l'erreur
            document.getElementById('loadingOverlay').style.display = 'none';
            showNotification('Erreur lors de la soumission : ' + error.message, 'error');
            
            // Réactiver le bouton
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    }
    
    // Afficher une notification
    function showNotification(message, type = 'info') {
        const container = document.getElementById('notificationsContainer');
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation' : 'info'}"></i>
                </div>
                <div class="notification-text">${message}</div>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        container.appendChild(notification);
        
        // Afficher avec animation
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Bouton de fermeture
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
        
        // Fermer automatiquement après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) { // Vérifier si la notification existe encore
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    // Formater un prix
    function formatPrice(price) {
        return parseFloat(price || 0).toFixed(3);
    }
</script>
@endsection