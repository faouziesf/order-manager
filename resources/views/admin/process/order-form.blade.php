@section('title', 'Traitement de la commande #' . $order->id)

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --secondary-color: #858796;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
            --white: #ffffff;
            --card-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        /* ===== LAYOUT ===== */
        .order-process-container {
            max-width: 1400px;
            margin: 0 auto;
        }

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

        /* ===== STATUS BADGES ===== */
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

        /* ===== CARDS & CONTAINERS ===== */
        .card-container {
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .card-content {
            padding: 20px;
        }

        /* ===== FORMS ===== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
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

        .form-control.is-invalid+.error-message {
            display: block;
        }

        .conditional-form {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            display: none;
        }

        /* ===== PRODUCT SECTION ===== */
        .product-list {
            display: grid;
            gap: 15px;
            max-height: 400px;
            overflow-y: auto;
        }

        .product-item {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px;
            background: var(--light-bg);
            position: relative;
        }

        .product-item.new-product-form {
            border-style: dashed;
            background: var(--white);
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
            transition: opacity 0.2s;
        }

        .product-remove:hover {
            opacity: 0.7;
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
            background: var(--white);
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

        /* ===== BUTTONS ===== */
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: var(--light-bg);
            border-color: var(--border-color);
            color: var(--secondary-color);
        }

        .btn-add {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }

        .btn:hover {
            opacity: 0.9;
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
            color: var(--white);
        }

        .action-button:disabled {
            background: var(--secondary-color);
            cursor: not-allowed;
        }

        /* ===== CART SUMMARY ===== */
        .cart-summary {
            background: var(--light-bg);
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

        /* ===== ACTION SECTION ===== */
        .action-container {
            position: sticky;
            top: 80px;
        }

        .notes-textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* ===== HISTORY SECTION ===== */
        .history-container {
            display: none;
            margin-top: 30px;
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

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--white);
            border: 2px solid var(--primary-color);
            z-index: 1;
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
            background: var(--light-bg);
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

        .timeline-notes {
            font-size: 0.9rem;
            background: var(--white);
            padding: 8px 12px;
            border-radius: 4px;
            border-left: 3px solid var(--primary-color);
        }

        /* ===== NOTIFICATIONS ===== */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 350px;
            padding: 15px 20px;
            border-radius: 6px;
            background: var(--white);
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
            color: var(--white);
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

        /* ===== LOADING ===== */
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
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ===== SELECT2 CUSTOMIZATION ===== */
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

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .product-fields {
                grid-template-columns: 1fr;
            }

            .new-product-fields {
                grid-template-columns: 1fr;
            }
        }

        /* ===== UTILITY CLASSES ===== */
        .text-center {
            text-align: center;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="order-process-container">
        <div class="order-content">
            <!-- Left Column: Client Info & Products -->
            <div class="left-column">
                <!-- Client Info Form -->
                <div class="card-container">
                    <div class="card-header">
                        <h3 class="card-title">Informations client</h3>
                    </div>
                    <div class="card-content">
                        <form id="processForm" action="{{ route('admin.process.action', $order) }}" method="POST">
                            @csrf
                            <input type="hidden" name="queue" value="{{ $queueType }}">

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="customer_phone" class="required-field">Téléphone</label>
                                    <input type="tel" class="form-control" id="customer_phone" name="customer_phone"
                                        value="{{ old('customer_phone', $order->customer_phone) }}" required>
                                    <div class="error-message" id="phone_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="customer_phone_2">Téléphone 2</label>
                                    <input type="tel" class="form-control" id="customer_phone_2" name="customer_phone_2"
                                        value="{{ old('customer_phone_2', $order->customer_phone_2) }}">
                                </div>

                                <div class="form-group">
                                    <label for="customer_name">Nom du client</label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                                        value="{{ old('customer_name', $order->customer_name) }}">
                                    <div class="error-message" id="name_error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="shipping_cost">Frais de livraison (DT)</label>
                                    <input type="number" class="form-control" id="shipping_cost" name="shipping_cost"
                                        step="0.001" value="{{ old('shipping_cost', $order->shipping_cost) }}">
                                </div>

                                <div class="form-group">
                                    <label for="customer_governorate">Gouvernorat</label>
                                    <select class="form-control select2" id="customer_governorate"
                                        name="customer_governorate">
                                        <option value="">Sélectionner un gouvernorat</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}"
                                                {{ old('customer_governorate', $order->customer_governorate) == $region->id ? 'selected' : '' }}>
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
                                        <option value="nouvelle"
                                            {{ old('status', $order->status) == 'nouvelle' ? 'selected' : '' }}>Nouvelle
                                        </option>
                                        <option value="confirmée"
                                            {{ old('status', $order->status) == 'confirmée' ? 'selected' : '' }}>Confirmée
                                        </option>
                                        <option value="annulée"
                                            {{ old('status', $order->status) == 'annulée' ? 'selected' : '' }}>Annulée
                                        </option>
                                        <option value="datée"
                                            {{ old('status', $order->status) == 'datée' ? 'selected' : '' }}>Datée</option>
                                        <option value="en_route"
                                            {{ old('status', $order->status) == 'en_route' ? 'selected' : '' }}>En route
                                        </option>
                                        <option value="livrée"
                                            {{ old('status', $order->status) == 'livrée' ? 'selected' : '' }}>Livrée
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="priority">Priorité</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <option value="normale"
                                            {{ old('priority', $order->priority) == 'normale' ? 'selected' : '' }}>Normale
                                        </option>
                                        <option value="urgente"
                                            {{ old('priority', $order->priority) == 'urgente' ? 'selected' : '' }}>Urgente
                                        </option>
                                        <option value="vip"
                                            {{ old('priority', $order->priority) == 'vip' ? 'selected' : '' }}>VIP</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Conditional sections -->
                            <div id="confirmSection" class="conditional-form"
                                style="{{ old('status', $order->status) == 'confirmée' ? 'display: block;' : '' }}">
                                <label for="confirmed_price" class="required-field">Prix confirmé (DT)</label>
                                <input type="number" class="form-control" id="confirmed_price" name="confirmed_price"
                                    step="0.001" value="{{ old('confirmed_price', $order->confirmed_price) }}">
                                <div class="error-message" id="confirmed_price_error"></div>
                                <small class="text-muted">Veuillez confirmer le prix total de la commande</small>
                            </div>

                            <div id="dateSection" class="conditional-form"
                                style="{{ old('status', $order->status) == 'datée' ? 'display: block;' : '' }}">
                                <label for="scheduled_date" class="required-field">Date de livraison</label>
                                <input type="text" class="form-control flatpickr" id="scheduled_date"
                                    name="scheduled_date"
                                    value="{{ old('scheduled_date', $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : '') }}">
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
                                    <span
                                        id="totalDisplay">{{ number_format($order->total_price + $order->shipping_cost, 3) }}
                                        DT</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="card-container">
                    <div class="card-header">
                        <h3 class="card-title">Produits</h3>
                        <button type="button" class="btn btn-cancel" id="toggleProductsBtn">
                            <i class="fas fa-eye"></i> Afficher/masquer
                        </button>
                    </div>
                    <div class="card-content" id="productContent">
                        <div class="product-list" id="productList">
                            @php $lineIndex = 0; @endphp
                            @forelse($order->items as $item)
                                <div class="product-item" data-line="{{ $lineIndex }}">
                                    <button type="button" class="product-remove"
                                        data-line="{{ $lineIndex }}">×</button>
                                    <div class="product-fields">
                                        <div>
                                            <label for="product-{{ $lineIndex }}">Produit</label>
                                            <select class="form-control product-select" id="product-{{ $lineIndex }}"
                                                name="products[{{ $lineIndex }}][id]"
                                                data-line="{{ $lineIndex }}">
                                                <option value="">Sélectionner un produit</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-price="{{ $product->price }}"
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                                    </option>
                                                @endforeach
                                                <option value="new">➕ Nouveau produit</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="quantity-{{ $lineIndex }}">Quantité</label>
                                            <input type="number" class="form-control product-quantity"
                                                id="quantity-{{ $lineIndex }}"
                                                name="products[{{ $lineIndex }}][quantity]"
                                                data-line="{{ $lineIndex }}" value="{{ $item->quantity }}"
                                                min="1">
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
                                            <select class="form-control product-select" id="product-0"
                                                name="products[0][id]" data-line="0">
                                                <option value="">Sélectionner un produit</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-price="{{ $product->price }}">
                                                        {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                                    </option>
                                                @endforeach
                                                <option value="new">➕ Nouveau produit</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="quantity-0">Quantité</label>
                                            <input type="number" class="form-control product-quantity" id="quantity-0"
                                                name="products[0][quantity]" data-line="0" value="1"
                                                min="1">
                                        </div>
                                    </div>
                                    <div class="product-total" id="total-0">0.000 DT</div>
                                </div>
                            @endforelse

                            <!-- New product form -->
                            <div class="product-item new-product-form" id="newProductForm" style="display: none;">
                                <div class="new-product-fields">
                                    <div>
                                        <label for="new_product_name">Nom du nouveau produit</label>
                                        <input type="text" class="form-control" id="new_product_name"
                                            placeholder="Entrez le nom du produit">
                                    </div>
                                    <div>
                                        <label for="new_product_price">Prix (DT)</label>
                                        <input type="number" class="form-control" id="new_product_price" step="0.001"
                                            min="0" placeholder="0.000">
                                    </div>
                                </div>
                                <div class="new-product-actions">
                                    <button type="button" class="btn btn-cancel" id="cancelNewProduct">Annuler</button>
                                    <button type="button" class="btn btn-add" id="saveNewProduct">Ajouter</button>
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
                <div class="card-container action-container">
                    <div class="card-header">
                        <h3 class="card-title">Actions à effectuer</h3>
                    </div>
                    <div class="card-content">
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

                        <div class="form-group">
                            <label for="notes" class="required-field">Notes</label>
                            <textarea class="form-control notes-textarea" id="notes" name="notes"
                                placeholder="Expliquez la raison de cette action..."></textarea>
                            <div class="error-message" id="notes_error"></div>
                        </div>

                        <div class="text-center mb-3">
                            <div class="d-flex gap-2 align-items-center justify-content-center"
                                style="color: var(--secondary-color);">
                                <span><i class="fas fa-phone"></i></span>
                                <span><strong>{{ $order->attempts_count }}</strong> tentatives totales</span>
                                <span>|</span>
                                <span><strong>{{ $order->daily_attempts_count }}</strong> aujourd'hui</span>
                            </div>
                        </div>

                        <button type="button" class="action-button" id="submitAction">Enregistrer</button>
                    </div>
                </div>

                <!-- History toggle button -->
                <div class="card-container">
                    <div class="card-content text-center">
                        <button type="button" class="btn btn-cancel" id="toggleHistoryBtn">
                            <i class="fas fa-history"></i> Voir l'historique
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Section -->
        <div class="card-container history-container" id="historyContainer">
            <div class="card-header">
                <h3 class="card-title">Historique de la commande #{{ $order->id }}</h3>
                <button type="button" class="btn btn-cancel" id="closeHistory">
                    <i class="fas fa-times"></i> Fermer
                </button>
            </div>
            <div class="card-content">
                <div class="timeline" id="historyTimeline">
                    <div class="text-center py-2">
                        <div class="spinner" style="width: 30px; height: 30px;"></div>
                        <p class="mb-0 mt-2">Chargement de l'historique...</p>
                    </div>
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
        // ===== CONFIGURATION =====
        const OrderProcessor = {
            config: {
                orderId: {{ $order->id }},
                queueType: "{{ $queueType }}",
                productCounter: {{ $order->items->count() > 0 ? $order->items->count() : 1 }},
                currentNewProductLine: null,
                historyLoaded: false
            },

            // ===== INITIALIZATION =====
            init() {
                this.initializeComponents();
                this.setupEventListeners();
                this.updateCartSummary();
                this.loadInitialCities();
            },

            initializeComponents() {
                // Initialize Select2
                $('.select2').select2({
                    placeholder: "Sélectionner...",
                    allowClear: true
                });

                // Initialize Flatpickr
                flatpickr(".flatpickr", {
                    locale: "fr",
                    dateFormat: "Y-m-d",
                    minDate: "today"
                });

                // Initialize product selects
                $('.product-select').each(function() {
                    $(this).select2({
                        placeholder: "Sélectionner un produit...",
                        allowClear: true
                    });
                });
            },

            setupEventListeners() {
                // UI toggles
                $('#toggleProductsBtn').on('click', this.toggleProductsSection);
                $('#toggleHistoryBtn').on('click', this.toggleHistorySection.bind(this));
                $('#closeHistory').on('click', this.toggleHistorySection.bind(this));

                // Form changes
                $('#customer_governorate').on('change', (e) => this.loadCities(e.target.value));
                $('#shipping_cost').on('input', this.updateCartSummary.bind(this));
                $('#status').on('change', this.handleStatusChange);
                $('#actionType').on('change', this.handleActionChange);

                // Actions
                $('#submitAction').on('click', this.submitForm.bind(this));
                $('#addProductBtn').on('click', this.addProductLine.bind(this));
                $('#saveNewProduct').on('click', this.saveNewProduct.bind(this));
                $('#cancelNewProduct').on('click', this.cancelNewProduct.bind(this));

                // Delegated events for dynamic content
                $(document).on('change', '.product-select', this.handleProductChange.bind(this));
                $(document).on('change keyup', '.product-quantity', this.handleQuantityChange.bind(this));
                $(document).on('click', '.product-remove', this.handleProductRemove.bind(this));
            },

            loadInitialCities() {
                const governorate = $('#customer_governorate').val();
                if (governorate) {
                    this.loadCities(governorate, {{ $order->customer_city ?? 'null' }});
                }
            },

            // ===== UI TOGGLES =====
            toggleProductsSection() {
                const $content = $('#productContent');
                const $btn = $('#toggleProductsBtn');

                if ($content.is(':visible')) {
                    $content.slideUp();
                    $btn.html('<i class="fas fa-eye"></i> Afficher');
                } else {
                    $content.slideDown();
                    $btn.html('<i class="fas fa-eye-slash"></i> Masquer');
                }
            },

            toggleHistorySection() {
                const $container = $('#historyContainer');

                if ($container.is(':visible')) {
                    $container.slideUp();
                } else {
                    $container.slideDown();

                    if (!this.config.historyLoaded) {
                        this.loadOrderHistory();
                    }

                    setTimeout(() => {
                        $container[0].scrollIntoView({
                            behavior: 'smooth'
                        });
                    }, 300);
                }
            },

            // ===== DATA LOADING =====
            loadCities(regionId, selectedCity = null) {
                if (!regionId) return;

                const $citySelect = $('#customer_city');
                $citySelect.html('<option value="">Chargement...</option>').prop('disabled', true);

                fetch(`{{ route('admin.orders.getCities') }}?region_id=${regionId}`)
                    .then(response => response.json())
                    .then(cities => {
                        let html = '<option value="">Sélectionner une ville...</option>';

                        cities.forEach(city => {
                            const selected = selectedCity && city.id == selectedCity ? 'selected' : '';
                            html += `<option value="${city.id}" data-shipping-cost="${city.shipping_cost || 0}" ${selected}>
                                ${city.name}
                             </option>`;
                        });

                        $citySelect.html(html).prop('disabled', false).trigger('change');

                        // Update shipping cost if city is selected
                        const selectedOption = $citySelect.find('option:selected')[0];
                        if (selectedOption?.dataset.shippingCost) {
                            $('#shipping_cost').val(selectedOption.dataset.shippingCost);
                            this.updateCartSummary();
                        }
                    })
                    .catch(() => {
                        $citySelect.html('<option value="">Erreur de chargement</option>')
                            .prop('disabled', false);
                        this.showNotification('Erreur lors du chargement des villes', 'error');
                    });
            },

            loadOrderHistory() {
                const $timeline = $('#historyTimeline');

                $timeline.html(`
            <div class="text-center py-2">
                <div class="spinner" style="width: 30px; height: 30px;"></div>
                <p class="mb-0 mt-2">Chargement de l'historique...</p>
            </div>
        `);

                fetch("{{ route('admin.orders.history', $order) }}")
                    .then(response => response.text())
                    .then(html => {
                        $timeline.html(html);
                        this.config.historyLoaded = true;
                    })
                    .catch(() => {
                        $timeline.html(`
                    <div class="text-center py-2 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <p>Erreur lors du chargement de l'historique.</p>
                    </div>
                `);
                    });
            },

            // ===== FORM HANDLERS =====
            handleStatusChange() {
                const status = $(this).val();
                const $confirmSection = $('#confirmSection');
                const $dateSection = $('#dateSection');

                $confirmSection.toggle(status === 'confirmée');
                $dateSection.toggle(status === 'datée');
            },

            handleActionChange() {
                const action = $(this).val();
                const $submitButton = $('#submitAction');
                const $status = $('#status');

                const actionConfig = {
                    call: {
                        text: 'Enregistrer l\'appel',
                        color: 'var(--info-color)'
                    },
                    confirm: {
                        text: 'Confirmer la commande',
                        color: 'var(--success-color)',
                        status: 'confirmée'
                    },
                    cancel: {
                        text: 'Annuler la commande',
                        color: 'var(--danger-color)',
                        status: 'annulée'
                    },
                    schedule: {
                        text: 'Dater la commande',
                        color: 'var(--warning-color)',
                        status: 'datée'
                    }
                };

                const config = actionConfig[action] || {
                    text: 'Enregistrer',
                    color: 'var(--success-color)'
                };

                $submitButton.text(config.text).css('background-color', config.color);

                if (config.status) {
                    $status.val(config.status).trigger('change');
                }
            },

            // ===== PRODUCT HANDLERS =====
            handleProductChange(e) {
                const $select = $(e.target);
                const line = $select.data('line');
                const value = $select.val();

                if (value === 'new') {
                    this.config.currentNewProductLine = line;
                    $('#newProductForm').slideDown();
                    $select.val('').trigger('change');
                } else {
                    this.updateProductTotal(line);
                    this.updateCartSummary();
                }
            },

            handleQuantityChange(e) {
                const line = $(e.target).data('line');
                this.updateProductTotal(line);
                this.updateCartSummary();
            },

            handleProductRemove(e) {
                const $item = $(e.target).closest('.product-item');
                const line = $item.data('line');

                // If only one product line, just reset it
                if ($('.product-item:not(.new-product-form)').length <= 1) {
                    $(`#product-${line}`).val('').trigger('change');
                    $(`#quantity-${line}`).val(1);
                    $(`#total-${line}`).text('0.000 DT');
                    this.updateCartSummary();
                    return;
                }

                // Otherwise remove the line
                $item.fadeOut(300, () => {
                    $item.remove();
                    this.updateCartSummary();
                });
            },

            addProductLine() {
                const lineNumber = this.config.productCounter++;
                const $productList = $('#productList');
                const $newProductForm = $('#newProductForm');

                const template = `
            <div class="product-item" data-line="${lineNumber}">
                <button type="button" class="product-remove" data-line="${lineNumber}">×</button>
                <div class="product-fields">
                    <div>
                        <label for="product-${lineNumber}">Produit</label>
                        <select class="form-control product-select" id="product-${lineNumber}" 
                                name="products[${lineNumber}][id]" data-line="${lineNumber}">
                            <option value="">Sélectionner un produit</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                    {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                </option>
                            @endforeach
                            <option value="new">➕ Nouveau produit</option>
                        </select>
                    </div>
                    <div>
                        <label for="quantity-${lineNumber}">Quantité</label>
                        <input type="number" class="form-control product-quantity" 
                               id="quantity-${lineNumber}" name="products[${lineNumber}][quantity]" 
                               data-line="${lineNumber}" value="1" min="1">
                    </div>
                </div>
                <div class="product-total" id="total-${lineNumber}">0.000 DT</div>
            </div>
        `;

                const $newLine = $(template);
                $newLine.insertBefore($newProductForm);

                // Initialize Select2 for the new select
                $newLine.find('.product-select').select2({
                    placeholder: "Sélectionner un produit...",
                    allowClear: true
                });
            },

            saveNewProduct() {
                const name = $('#new_product_name').val().trim();
                const price = parseFloat($('#new_product_price').val());

                // Validation
                if (!name) {
                    this.showNotification('Le nom du produit est obligatoire', 'error');
                    return;
                }

                if (isNaN(price) || price <= 0) {
                    this.showNotification('Veuillez entrer un prix valide', 'error');
                    return;
                }

                if (this.config.currentNewProductLine === null) {
                    this.cancelNewProduct();
                    return;
                }

                // Create temporary ID and add to select
                const tempId = `new:${encodeURIComponent(name)}:${price}`;
                const $select = $(`#product-${this.config.currentNewProductLine}`);

                const $newOption = $(`<option value="${tempId}" selected>
                                ${name} - ${this.formatPrice(price)} DT [Nouveau]
                              </option>`);
                $newOption.data('price', price);

                $select.append($newOption).val(tempId).trigger('change');

                this.updateProductTotal(this.config.currentNewProductLine);
                this.updateCartSummary();
                this.cancelNewProduct();
                this.showNotification('Nouveau produit ajouté', 'success');
            },

            cancelNewProduct() {
                $('#newProductForm').slideUp();
                $('#new_product_name, #new_product_price').val('');
                this.config.currentNewProductLine = null;
            },

            // ===== CALCULATIONS =====
            updateProductTotal(line) {
                const $select = $(`#product-${line}`);
                const quantity = parseInt($(`#quantity-${line}`).val()) || 1;
                const $totalElement = $(`#total-${line}`);

                let price = 0;
                const value = $select.val();

                if (value) {
                    if (value.startsWith('new:')) {
                        const parts = value.split(':');
                        if (parts.length >= 3) {
                            price = parseFloat(parts[2]);
                        }
                    } else {
                        price = parseFloat($select.find('option:selected').data('price')) || 0;
                    }
                }

                const total = price * quantity;
                $totalElement.text(`${this.formatPrice(total)} DT`);
            },

            updateCartSummary() {
                let subtotal = 0;

                // Calculate subtotal
                $('.product-item:not(.new-product-form)').each((index, item) => {
                    const line = $(item).data('line');
                    const $select = $(`#product-${line}`);
                    const quantity = parseInt($(`#quantity-${line}`).val()) || 1;
                    const value = $select.val();

                    let price = 0;

                    if (value) {
                        if (value.startsWith('new:')) {
                            const parts = value.split(':');
                            if (parts.length >= 3) {
                                price = parseFloat(parts[2]);
                            }
                        } else {
                            price = parseFloat($select.find('option:selected').data('price')) || 0;
                        }
                        subtotal += price * quantity;
                    }
                });

                // Update displays
                const shipping = parseFloat($('#shipping_cost').val()) || 0;
                const total = subtotal + shipping;

                $('#subtotalDisplay').text(`${this.formatPrice(subtotal)} DT`);
                $('#shippingDisplay').text(`${this.formatPrice(shipping)} DT`);
                $('#totalDisplay').text(`${this.formatPrice(total)} DT`);

                // Update confirmed price if visible
                if ($('#confirmSection').is(':visible')) {
                    $('#confirmed_price').val(this.formatPrice(total));
                }
            },

            // ===== FORM VALIDATION & SUBMISSION =====
            validateForm() {
                const errors = {};
                const action = $('#actionType').val();
                const notes = $('#notes').val().trim();
                const status = $('#status').val();

                // Validate notes
                if (action && !notes) {
                    errors.notes = 'Les notes sont obligatoires';
                }

                // Validate based on status/action
                if (status === 'confirmée' || action === 'confirm') {
                    const requiredFields = {
                        customer_name: 'Le nom du client est obligatoire',
                        customer_phone: 'Le téléphone est obligatoire',
                        customer_governorate: 'Le gouvernorat est obligatoire',
                        customer_city: 'La ville est obligatoire',
                        customer_address: 'L\'adresse est obligatoire'
                    };

                    Object.entries(requiredFields).forEach(([field, message]) => {
                        if (!$(`#${field}`).val()) {
                            errors[field] = message;
                        }
                    });

                    if (!$('#confirmed_price').val()) {
                        errors.confirmed_price = 'Le prix confirmé est obligatoire';
                    }
                }

                if (status === 'datée' || action === 'schedule') {
                    if (!$('#scheduled_date').val()) {
                        errors.scheduled_date = 'La date programmée est obligatoire';
                    }
                }

                // Validate products
                const hasValidProducts = $('.product-select').toArray()
                    .some(select => $(select).val() && $(select).val() !== 'new');

                if (!hasValidProducts) {
                    errors.products = 'Veuillez sélectionner au moins un produit';
                }

                // Display errors
                $('.error-message').hide();
                $('.form-control').removeClass('is-invalid');

                Object.entries(errors).forEach(([field, message]) => {
                    $(`#${field}`).addClass('is-invalid');
                    $(`#${field}_error`).text(message).show();
                });

                return Object.keys(errors).length === 0;
            },

            submitForm() {
                if (!this.validateForm()) {
                    this.showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
                    return;
                }

                const $form = $('#processForm');
                const formData = new FormData($form[0]);
                const action = $('#actionType').val();
                const notes = $('#notes').val();

                // Add additional data
                if (action) formData.append('action', action);
                if (notes) formData.append('notes', notes);
                if (action === 'call') formData.append('increment_attempts', '1');

                // Add products data
                let productIndex = 0;
                $('.product-item:not(.new-product-form)').each((index, item) => {
                    const line = $(item).data('line');
                    const $select = $(`#product-${line}`);
                    const quantity = $(`#quantity-${line}`).val();
                    const value = $select.val();

                    if (value) {
                        if (value.startsWith('new:')) {
                            const parts = value.split(':');
                            if (parts.length >= 3) {
                                formData.append(`products[${productIndex}][is_new]`, '1');
                                formData.append(`products[${productIndex}][name]`, decodeURIComponent(parts[
                                1]));
                                formData.append(`products[${productIndex}][price]`, parts[2]);
                                formData.append(`products[${productIndex}][quantity]`, quantity);
                            }
                        } else {
                            formData.append(`products[${productIndex}][id]`, value);
                            formData.append(`products[${productIndex}][quantity]`, quantity);
                        }
                        productIndex++;
                    }
                });

                // Update UI
                const $submitButton = $('#submitAction');
                const originalText = $submitButton.text();

                $submitButton.prop('disabled', true)
                    .html(
                        '<div class="spinner" style="width: 20px; height: 20px; display: inline-block;"></div> Traitement...'
                        );

                $('#loadingOverlay').show();

                // Submit form
                fetch($form.attr('action'), {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Erreur lors de la soumission');
                        return response.text();
                    })
                    .then(() => {
                        window.location.href = `{{ route('admin.process.interface') }}#{{ $queueType }}`;
                    })
                    .catch(error => {
                        $('#loadingOverlay').hide();
                        this.showNotification(`Erreur lors de la soumission : ${error.message}`, 'error');
                        $submitButton.prop('disabled', false).text(originalText);
                    });
            },

            // ===== UTILITIES =====
            showNotification(message, type = 'info') {
                const $container = $('#notificationsContainer');
                const iconMap = {
                    success: 'check',
                    error: 'exclamation',
                    warning: 'exclamation-triangle',
                    info: 'info'
                };

                const $notification = $(`
            <div class="notification notification-${type}">
                <div class="notification-content">
                    <div class="notification-icon">
                        <i class="fas fa-${iconMap[type]}"></i>
                    </div>
                    <div class="notification-text">${message}</div>
                </div>
                <button class="notification-close">&times;</button>
            </div>
        `);

                $container.append($notification);

                // Show animation
                setTimeout(() => $notification.addClass('show'), 10);

                // Close handlers
                $notification.find('.notification-close').on('click', () => {
                    $notification.removeClass('show');
                    setTimeout(() => $notification.remove(), 300);
                });

                // Auto close
                setTimeout(() => {
                    if ($notification.parent().length) {
                        $notification.removeClass('show');
                        setTimeout(() => $notification.remove(), 300);
                    }
                }, 5000);
            },

            formatPrice(price) {
                return parseFloat(price || 0).toFixed(3);
            }
        };

        // Initialize when DOM is ready
        $(document).ready(() => OrderProcessor.init());
    </script>
@endsection
