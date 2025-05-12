@extends('layouts.admin')

@section('title', 'Modifier la Commande')

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

    body {
        background-color: var(--light-bg);
        font-family: 'Nunito', sans-serif;
    }

    /* Header complet */
    .edit-header {
        background: white;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .edit-header .top-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .edit-header .bottom-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .order-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .order-title h1 {
        font-size: 1.4rem;
        margin: 0;
        font-weight: 700;
        color: #5a5c69;
    }

    .status-priority-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .status-priority-wrapper select {
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.9rem;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-buttons .btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    /* Tag editor */
    .tag-section {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .current-tags {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .tag {
        background: #e9ecef;
        border-radius: 15px;
        padding: 4px 10px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tag .remove-tag {
        cursor: pointer;
        color: #6c757d;
    }

    .tag .remove-tag:hover {
        color: var(--danger-color);
    }

    .add-tag-input {
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.8rem;
        width: 120px;
    }

    /* Main content */
    .edit-container {
        max-width: 1400px;
        margin: 20px auto;
        display: grid;
        grid-template-columns: 1fr 450px;
        gap: 20px;
    }

    /* Client info section */
    .client-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        padding: 20px;
    }

    .section-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #5a5c69;
        margin: 0;
    }

    .client-form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .form-field {
        display: flex;
        flex-direction: column;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .form-field label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a5c69;
        margin-bottom: 4px;
    }

    .form-field input,
    .form-field select,
    .form-field textarea {
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .form-field textarea {
        resize: vertical;
        min-height: 80px;
    }

    .required::after {
        content: " *";
        color: var(--danger-color);
    }

    /* Cart summary */
    .cart-summary {
        margin-top: 20px;
        padding: 15px;
        background: linear-gradient(135deg, #f8f9fc 0%, #f5f7fa 100%);
        border-radius: 6px;
    }

    .cart-summary table {
        width: 100%;
        border-collapse: collapse;
    }

    .cart-summary td {
        padding: 6px 0;
        font-size: 0.9rem;
    }

    .cart-summary .total-row {
        font-weight: 700;
        font-size: 1.1rem;
        border-top: 2px solid var(--border-color);
        padding-top: 10px;
    }

    /* Action panel */
    .action-panel {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .cart-widget {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .cart-widget-header {
        background: #f8f9fc;
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
    }

    .cart-widget-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #5a5c69;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cart-widget-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .cart-widget-content.expanded {
        max-height: 600px;
        overflow-y: auto;
    }

    .product-line {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        display: grid;
        grid-template-columns: 2fr 100px 80px 40px;
        gap: 10px;
        align-items: center;
    }

    .product-line:last-child {
        border-bottom: none;
    }

    .product-select,
    .product-quantity {
        width: 100%;
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 6px 10px;
    }

    .line-total {
        text-align: right;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .add-product-btn {
        padding: 8px 15px;
        margin: 15px;
        border: 1px dashed #d1d3e2;
        background: white;
        color: var(--primary-color);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .add-product-btn:hover {
        background: #f8f9fc;
        border-color: var(--primary-color);
    }

    /* Status and action section */
    .status-action-widget {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        padding: 20px;
    }

    .action-selector {
        margin-bottom: 20px;
    }

    .action-selector label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a5c69;
        margin-bottom: 6px;
    }

    .action-selector select {
        width: 100%;
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .notes-section {
        margin-bottom: 20px;
    }

    .notes-section label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a5c69;
        margin-bottom: 6px;
    }

    .notes-section textarea {
        width: 100%;
        min-height: 80px;
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 8px 12px;
        resize: vertical;
        font-size: 0.9rem;
    }

    .conditional-section {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
    }

    .conditional-section label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a5c69;
        margin-bottom: 6px;
    }

    .conditional-section input {
        width: 100%;
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    /* Previous attempts section */
    .attempts-widget {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .attempts-stats {
        background: #f8f9fc;
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .attempts-stats h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #5a5c69;
        margin: 0;
    }

    .attempts-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .attempts-content.expanded {
        max-height: 400px;
        overflow-y: auto;
    }

    .attempt-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .attempt-item:last-child {
        border-bottom: none;
    }

    .attempt-date {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .attempt-notes {
        margin-top: 5px;
        font-size: 0.9rem;
        color: #495057;
    }

    /* History widget */
    .history-widget {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .history-header {
        background: #f8f9fc;
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .history-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #5a5c69;
        margin: 0;
    }

    .history-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .history-content.expanded {
        max-height: 600px;
        overflow-y: auto;
    }

    .history-item {
        padding: 12px 15px;
        border-left: 4px solid #e3e6f0;
        margin-left: 15px;
        margin-bottom: 10px;
    }

    .history-item.tentative {
        border-left-color: var(--info-color);
    }

    .history-item.confirmation {
        border-left-color: var(--success-color);
    }

    .history-item.annulation {
        border-left-color: var(--danger-color);
    }

    .history-item.datation {
        border-left-color: var(--warning-color);
    }

    .history-date {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .history-action {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }

    .history-user {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .history-notes {
        margin-top: 5px;
        font-size: 0.9rem;
        color: #495057;
    }

    /* Submit button */
    .submit-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .submit-btn {
        width: 100%;
        padding: 12px 20px;
        background: var(--success-color);
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .submit-btn:hover {
        background: #17a673;
    }

    .submit-btn:disabled {
        background: #d1d3e2;
        cursor: not-allowed;
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

    .priority-urgente {
        background: #fff3cd;
        color: #856404;
    }

    .priority-vip {
        background: #f8d7da;
        color: #721c24;
    }

    /* Notifications */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        z-index: 1000;
        opacity: 0;
        transition: all 0.3s ease;
        transform: translateY(-20px);
    }

    .notification.show {
        opacity: 1;
        transform: translateY(0);
    }

    .notification.success {
        background: var(--success-color);
    }

    .notification.error {
        background: var(--danger-color);
    }

    .notification.warning {
        background: var(--warning-color);
    }

    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Shortcuts modal */
    .shortcuts-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        padding: 20px;
        z-index: 2000;
        max-width: 400px;
        display: none;
    }

    .shortcuts-modal h3 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.25rem;
    }

    .shortcuts-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .shortcuts-list li {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .shortcut-key {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .edit-container {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .client-form {
            grid-template-columns: 1fr;
        }
        
        .product-line {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        
        .status-priority-wrapper {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>
@endsection

@section('content')
    <!-- Header complet -->
    <div class="edit-header">
        <div class="top-section">
            <div class="order-title">
                <h1>Modifier la commande #{{ $order->id ?? '---' }}</h1>
                <div class="status-priority-wrapper">
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="nouvelle" {{ $order->status == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                        <option value="confirmée" {{ $order->status == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                        <option value="annulée" {{ $order->status == 'annulée' ? 'selected' : '' }}>Annulée</option>
                        <option value="datée" {{ $order->status == 'datée' ? 'selected' : '' }}>Datée</option>
                        <option value="en_route" {{ $order->status == 'en_route' ? 'selected' : '' }}>En route</option>
                        <option value="livrée" {{ $order->status == 'livrée' ? 'selected' : '' }}>Livrée</option>
                    </select>
                    <select id="priority" name="priority" class="form-select form-select-sm">
                        <option value="normale" {{ $order->priority == 'normale' ? 'selected' : '' }}>Normale</option>
                        <option value="urgente" {{ $order->priority == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        <option value="vip" {{ $order->priority == 'vip' ? 'selected' : '' }}>VIP</option>
                    </select>
                </div>
            </div>
            <div class="action-buttons">
                <button type="button" class="btn btn-outline-primary" id="showHistory">
                    <i class="fas fa-history me-1"></i> Historique
                </button>
                <button type="button" class="btn btn-outline-primary" id="showShortcuts">
                    <i class="fas fa-question-circle me-1"></i>
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="bottom-section">
            <div class="tag-section">
                <label>Étiquettes:</label>
                <div class="current-tags" id="currentTags">
                    <!-- Les étiquettes seront ajoutées ici -->
                </div>
                <input type="text" class="add-tag-input" placeholder="Ajouter une étiquette..." id="addTagInput">
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="edit-container">
        <!-- Section client -->
        <div class="client-section">
            <div class="section-header">
                <h3>Informations client</h3>
            </div>
            <form id="editOrderForm" class="client-form" action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-field">
                    <label for="customer_name" class="required">Nom du client</label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ $order->customer_name }}" required>
                </div>
                <div class="form-field">
                    <label for="shipping_cost">Frais de livraison (DT)</label>
                    <input type="number" id="shipping_cost" name="shipping_cost" step="0.001" value="{{ $order->shipping_cost }}">
                </div>
                <div class="form-field">
                    <label for="customer_phone" class="required">Téléphone</label>
                    <input type="tel" id="customer_phone" name="customer_phone" value="{{ $order->customer_phone }}" required>
                </div>
                <div class="form-field">
                    <label for="customer_phone_2">Téléphone 2</label>
                    <input type="tel" id="customer_phone_2" name="customer_phone_2" value="{{ $order->customer_phone_2 }}">
                </div>
                <div class="form-field full-width">
                    <label for="customer_address">Adresse</label>
                    <textarea id="customer_address" name="customer_address">{{ $order->customer_address }}</textarea>
                </div>
                <div class="form-field">
                    <label for="customer_governorate">Gouvernorat</label>
                    <select id="customer_governorate" name="customer_governorate">
                        <option value="">Sélectionner...</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ $order->customer_governorate == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="customer_city">Ville</label>
                    <select id="customer_city" name="customer_city">
                        <option value="">Sélectionner d'abord un gouvernorat</option>
                    </select>
                </div>
            
                <!-- Résumé du panier -->
                <div class="cart-summary">
                    <table>
                        <tr>
                            <td>Sous-total:</td>
                            <td class="text-end" id="subtotal">{{ number_format($order->total_price, 3) }} DT</td>
                        </tr>
                        <tr>
                            <td>Frais de livraison:</td>
                            <td class="text-end" id="shippingDisplay">{{ number_format($order->shipping_cost, 3) }} DT</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total:</td>
                            <td class="text-end" id="totalAmount">{{ number_format($order->total_price + $order->shipping_cost, 3) }} DT</td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>

        <!-- Panneau d'action -->
        <div class="action-panel">
            <!-- Widget panier -->
            <div class="cart-widget">
                <div class="cart-widget-header" onclick="toggleWidget('cart')">
                    <h3>
                        <i class="fas fa-shopping-cart me-2"></i>
                        Panier (<span id="cartItemCount">{{ $order->items->count() }}</span> articles)
                    </h3>
                    <i class="fas fa-chevron-down" id="cartToggleIcon"></i>
                </div>
                <div class="cart-widget-content" id="cartContent">
                    <div id="productLines">
                        @php $lineIndex = 0; @endphp
                        @forelse($order->items as $item)
                            <div class="product-line" data-line="{{ $lineIndex }}">
                                <select class="product-select" data-line="{{ $lineIndex }}" name="products[{{ $lineIndex }}][id]" required>
                                    <option value="">Sélectionner un produit...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                        </option>
                                    @endforeach
                                    <option value="new">➕ Nouveau produit</option>
                                </select>
                                <input type="number" class="product-quantity" data-line="{{ $lineIndex }}" name="products[{{ $lineIndex }}][quantity]" value="{{ $item->quantity }}" min="1" required>
                                <div class="line-total" data-line="{{ $lineIndex }}">{{ number_format($item->total_price, 3) }} DT</div>
                                <button type="button" class="btn btn-link text-danger remove-line p-0">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @php $lineIndex++; @endphp
                        @empty
                            <div class="text-center p-3">Aucun produit dans cette commande</div>
                        @endforelse
                    </div>
                    <button type="button" class="add-product-btn" id="addProductBtn">
                        <i class="fas fa-plus me-1"></i> Ajouter un produit
                    </button>
                </div>
            </div>

            <!-- Widget tentatives précédentes -->
            <div class="attempts-widget">
                <div class="attempts-stats" onclick="toggleWidget('attempts')">
                    <h3>Tentatives d'appel</h3>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary" id="totalAttempts">{{ $order->attempts_count }} total</span>
                        <span class="badge bg-warning" id="dailyAttempts">{{ $order->daily_attempts_count }} aujourd'hui</span>
                        <i class="fas fa-chevron-down" id="attemptsToggleIcon"></i>
                    </div>
                </div>
                <div class="attempts-content" id="attemptsContent">
                    <div id="attemptsList">
                        <!-- Les tentatives seront listées ici -->
                    </div>
                </div>
            </div>

            <!-- Widget statut et action -->
            <div class="status-action-widget">
                <div class="action-selector">
                    <label for="actionType">Action à effectuer</label>
                    <select id="actionType" name="action" class="form-select">
                        <option value="">-- Choisir une action --</option>
                        <option value="call">Tentative d'appel</option>
                        <option value="confirm">Confirmer la commande</option>
                        <option value="cancel">Annuler la commande</option>
                        <option value="schedule">Dater la commande</option>
                    </select>
                </div>

                <div id="conditionalSection" style="display: none;">
                    <!-- Le contenu conditionnel sera inséré ici -->
                </div>

                <div class="notes-section">
                    <label for="notes" class="required">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Expliquez la raison de cette action..."></textarea>
                </div>

                <div class="submit-section">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span id="submitText">Enregistrer les modifications</span>
                    </button>
                </div>
            </div>

<!-- Widget historique -->
            <div class="history-widget">
                <div class="history-header" onclick="toggleWidget('history')">
                    <h3>Historique</h3>
                    <i class="fas fa-chevron-down" id="historyToggleIcon"></i>
                </div>
                <div class="history-content" id="historyContent">
                    <div id="historyList">
                        @if($order->history->count() > 0)
                            @foreach($order->history->sortByDesc('created_at') as $entry)
                                <div class="history-item {{ $entry->action }}">
                                    <div class="history-date">{{ $entry->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="history-action">
                                        @switch($entry->action)
                                            @case('création')
                                                <i class="fas fa-plus-circle"></i> Création
                                                @break
                                            @case('modification')
                                                <i class="fas fa-edit"></i> Modification
                                                @break
                                            @case('confirmation')
                                                <i class="fas fa-check-circle"></i> Confirmation
                                                @break
                                            @case('annulation')
                                                <i class="fas fa-times-circle"></i> Annulation
                                                @break
                                            @case('datation')
                                                <i class="fas fa-calendar"></i> Datation
                                                @break
                                            @case('tentative')
                                                <i class="fas fa-phone"></i> Tentative d'appel
                                                @break
                                            @default
                                                <i class="fas fa-history"></i> {{ ucfirst($entry->action) }}
                                        @endswitch
                                    </div>
                                    <div class="history-user">Par: {{ $entry->getUserName() }}</div>
                                    @if($entry->notes)
                                        <div class="history-notes">{{ $entry->notes }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center p-3">Aucun historique disponible</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal des raccourcis -->
    <div class="shortcuts-modal" id="shortcutsModal">
        <h3>Raccourcis clavier</h3>
        <ul class="shortcuts-list">
            <li>
                <span>Soumettre le formulaire</span>
                <span class="shortcut-key">Ctrl + S</span>
            </li>
            <li>
                <span>Afficher/masquer l'historique</span>
                <span class="shortcut-key">Ctrl + H</span>
            </li>
            <li>
                <span>Afficher/masquer le panier</span>
                <span class="shortcut-key">Ctrl + P</span>
            </li>
            <li>
                <span>Ajouter un produit</span>
                <span class="shortcut-key">Ctrl + A</span>
            </li>
            <li>
                <span>Retour à la liste</span>
                <span class="shortcut-key">Échap</span>
            </li>
        </ul>
        <button type="button" class="btn btn-sm btn-secondary mt-3" onclick="toggleShortcuts()">Fermer</button>
    </div>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Notification area -->
    <div id="notificationArea"></div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script>
    // Configuration globale
    const config = {
        orderId: {{ $order->id }},
        productCounter: {{ $order->items->count() }},
        cartExpanded: false,
        historyExpanded: false,
        attemptsExpanded: false,
        tags: []
    };

    // Initialisation au chargement de la page
    $(document).ready(function() {
        initializeApp();
        setupEventListeners();
        
        // Charger les villes si le gouvernorat est sélectionné
        if ($('#customer_governorate').val()) {
            loadCities($('#customer_governorate').val(), {{ $order->customer_city ?? 'null' }});
        }
    });

    // Initialisation de l'application
    function initializeApp() {
        // Initialiser Select2
        $('.form-field select').select2({
            theme: 'bootstrap',
            width: '100%'
        });
        
        // Initialiser le panier
        updateCartSummary();
        
        // Tenter de charger les tentatives
        loadAttempts();
    }

    // Configuration des écouteurs d'événements
    function setupEventListeners() {
        // Raccourcis clavier
        document.addEventListener('keydown', handleKeyboardShortcuts);

        // Boutons principaux
        document.getElementById('showHistory').addEventListener('click', () => toggleWidget('history'));
        document.getElementById('showShortcuts').addEventListener('click', toggleShortcuts);

        // Action selector
        document.getElementById('actionType').addEventListener('change', handleActionChange);

        // Gouvernorat/Ville
        document.getElementById('customer_governorate').addEventListener('change', function() {
            loadCities(this.value);
        });

        // Frais de livraison
        document.getElementById('shipping_cost').addEventListener('input', updateCartSummary);

        // Tags
        document.getElementById('addTagInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag(this.value);
                this.value = '';
            }
        });

        // Panier
        document.getElementById('addProductBtn').addEventListener('click', addProductLine);
        
        // Event delegation pour les lignes de produit dynamiques
        $(document).on('change', '.product-select, .product-quantity', function() {
            const line = $(this).data('line');
            updateLineTotal(line);
            updateCartSummary();
        });
        
        $(document).on('click', '.remove-line', function() {
            const line = $(this).closest('.product-line').data('line');
            removeLine(line);
        });

        // Soumission du formulaire
        document.getElementById('editOrderForm').addEventListener('submit', handleFormSubmit);
    }

    // Gestion des raccourcis clavier
    function handleKeyboardShortcuts(e) {
        if (e.ctrlKey) {
            switch(e.key.toLowerCase()) {
                case 's':
                    e.preventDefault();
                    document.getElementById('editOrderForm').requestSubmit();
                    break;
                case 'h':
                    e.preventDefault();
                    toggleWidget('history');
                    break;
                case 'p':
                    e.preventDefault();
                    toggleWidget('cart');
                    break;
                case 'a':
                    e.preventDefault();
                    if (config.cartExpanded) {
                        addProductLine();
                    }
                    break;
            }
        }
    }

    // Basculer l'affichage des widgets
    function toggleWidget(widgetName) {
        const contents = {
            cart: document.getElementById('cartContent'),
            history: document.getElementById('historyContent'),
            attempts: document.getElementById('attemptsContent')
        };
        
        const icons = {
            cart: document.getElementById('cartToggleIcon'),
            history: document.getElementById('historyToggleIcon'),
            attempts: document.getElementById('attemptsToggleIcon')
        };
        
        const widgetStates = {
            cart: 'cartExpanded',
            history: 'historyExpanded',
            attempts: 'attemptsExpanded'
        };
        
        const content = contents[widgetName];
        const icon = icons[widgetName];
        const stateKey = widgetStates[widgetName];
        
        if (!content || !icon) return;
        
        config[stateKey] = !config[stateKey];
        
        if (config[stateKey]) {
            content.classList.add('expanded');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            
            // Charger les données si nécessaire
            if (widgetName === 'attempts' && !content.dataset.loaded) {
                loadAttempts();
            }
        } else {
            content.classList.remove('expanded');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }

    // Gestion du changement d'action
    function handleActionChange() {
        const action = document.getElementById('actionType').value;
        const conditionalSection = document.getElementById('conditionalSection');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        
        // Effacer le contenu conditionnel
        conditionalSection.innerHTML = '';
        conditionalSection.style.display = 'none';
        
        // Réinitialiser le bouton
        submitBtn.className = 'submit-btn';
        submitText.textContent = 'Enregistrer les modifications';
        
        switch(action) {
            case 'call':
                submitText.textContent = 'Enregistrer l\'appel';
                submitBtn.style.background = 'var(--info-color)';
                break;
                
            case 'confirm':
                createConfirmSection(conditionalSection);
                submitText.textContent = 'Confirmer la commande';
                submitBtn.style.background = 'var(--success-color)';
                break;
                
            case 'cancel':
                submitText.textContent = 'Annuler la commande';
                submitBtn.style.background = 'var(--danger-color)';
                break;
                
            case 'schedule':
                createScheduleSection(conditionalSection);
                submitText.textContent = 'Programmer la commande';
                submitBtn.style.background = 'var(--warning-color)';
                break;
        }
    }

    // Créer la section de confirmation
    function createConfirmSection(container) {
        container.innerHTML = `
            <div class="conditional-section">
                <label for="confirmed_price" class="required">Prix confirmé (DT)</label>
                <input type="number" id="confirmed_price" name="confirmed_price" step="0.001" required>
                <small class="text-muted">Veuillez confirmer le prix total de la commande</small>
            </div>
        `;
        container.style.display = 'block';
        
        // Pré-remplir avec le total actuel
        const total = calculateTotal();
        container.querySelector('#confirmed_price').value = total.toFixed(3);
    }

    // Créer la section de programmation
    function createScheduleSection(container) {
        container.innerHTML = `
            <div class="conditional-section">
                <label for="scheduled_date" class="required">Date de livraison</label>
                <input type="text" id="scheduled_date" name="scheduled_date" class="flatpickr" required>
                <small class="text-muted">Sélectionnez la date de livraison programmée</small>
            </div>
        `;
        container.style.display = 'block';
        
        // Initialiser le sélecteur de date
        flatpickr(container.querySelector('.flatpickr'), {
            locale: 'fr',
            dateFormat: 'Y-m-d',
            minDate: 'today'
        });
    }
    
    // Ajouter une ligne de produit
    function addProductLine() {
        const productLines = document.getElementById('productLines');
        const lineNumber = config.productCounter++;
        const lineHtml = `
            <div class="product-line" data-line="${lineNumber}">
                <select class="product-select" data-line="${lineNumber}" name="products[${lineNumber}][id]" required>
                    <option value="">Sélectionner un produit...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                        </option>
                    @endforeach
                    <option value="new">➕ Nouveau produit</option>
                </select>
                <input type="number" class="product-quantity" data-line="${lineNumber}" name="products[${lineNumber}][quantity]" value="1" min="1" required>
                <div class="line-total" data-line="${lineNumber}">0.000 DT</div>
                <button type="button" class="btn btn-link text-danger remove-line p-0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        $(productLines).append(lineHtml);
        $(`.product-select[data-line="${lineNumber}"]`).select2({
            theme: 'bootstrap',
            width: '100%'
        });
        
        updateCartItemCount();
    }

    // Supprimer une ligne
    function removeLine(lineNumber) {
        $(`.product-line[data-line="${lineNumber}"]`).remove();
        updateCartSummary();
        updateCartItemCount();
    }

    // Mettre à jour le total d'une ligne
    function updateLineTotal(lineNumber) {
        const select = $(`.product-select[data-line="${lineNumber}"]`);
        const quantity = $(`.product-quantity[data-line="${lineNumber}"]`).val() || 1;
        const totalElement = $(`.line-total[data-line="${lineNumber}"]`);
        
        let price = 0;
        if (select.val() && !select.val().startsWith('new')) {
            price = select.find('option:selected').data('price') || 0;
        }
        
        const total = price * quantity;
        totalElement.text(formatPrice(total) + ' DT');
    }

    // Mettre à jour le résumé du panier
    function updateCartSummary() {
        let subtotal = 0;
        
        $('.product-line').each(function() {
            const line = $(this).data('line');
            const select = $(`.product-select[data-line="${line}"]`);
            const quantity = $(`.product-quantity[data-line="${line}"]`).val() || 1;
            
            let price = 0;
            if (select.val() && !select.val().startsWith('new')) {
                price = select.find('option:selected').data('price') || 0;
            }
            
            subtotal += price * quantity;
        });
        
        const shipping = parseFloat($('#shipping_cost').val()) || 0;
        const total = subtotal + shipping;
        
        $('#subtotal').text(formatPrice(subtotal) + ' DT');
        $('#shippingDisplay').text(formatPrice(shipping) + ' DT');
        $('#totalAmount').text(formatPrice(total) + ' DT');
    }

    // Mettre à jour le compteur d'articles
    function updateCartItemCount() {
        const count = $('.product-line').length;
        $('#cartItemCount').text(count);
    }

    // Calculer le total
    function calculateTotal() {
        let subtotal = 0;
        
        $('.product-line').each(function() {
            const line = $(this).data('line');
            const select = $(`.product-select[data-line="${line}"]`);
            const quantity = $(`.product-quantity[data-line="${line}"]`).val() || 1;
            
            let price = 0;
            if (select.val() && !select.val().startsWith('new')) {
                price = select.find('option:selected').data('price') || 0;
            }
            
            subtotal += price * quantity;
        });
        
        const shipping = parseFloat($('#shipping_cost').val()) || 0;
        return subtotal + shipping;
    }
    
    // Charger les villes
    function loadCities(regionId, selectedCity = null) {
        if (!regionId) return;
        
        const citySelect = $('#customer_city');
        citySelect.empty().append('<option value="">Chargement...</option>').prop('disabled', true);
        
        $.ajax({
            url: "{{ route('admin.orders.getCities') }}",
            data: { region_id: regionId },
            success: function(cities) {
                citySelect.empty().append('<option value="">Sélectionner une ville...</option>');
                
                cities.forEach(function(city) {
                    const selected = selectedCity && city.id == selectedCity;
                    citySelect.append(new Option(city.name, city.id, selected, selected));
                });
                
                citySelect.prop('disabled', false).trigger('change');
                
                // Si la ville a un frais de livraison, mettre à jour
                if (cities.length > 0 && cities[0].shipping_cost) {
                    $('#shipping_cost').val(cities[0].shipping_cost);
                    updateCartSummary();
                }
            },
            error: function() {
                citySelect.empty().append('<option value="">Erreur lors du chargement</option>');
                showNotification('Erreur lors du chargement des villes', 'error');
            }
        });
    }

    // Charger les tentatives d'appel
    function loadAttempts() {
        const attemptsList = document.getElementById('attemptsList');
        attemptsList.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin me-2"></i> Chargement...</div>';
        
        $.ajax({
            url: "{{ route('admin.orders.history', $order) }}",
            success: function(data) {
                // Filtrer pour n'obtenir que les tentatives d'appel
                const attempts = Array.from(data.match(/<div class="history-item tentative">[\s\S]*?<\/div><\/div>/g) || []);
                
                if (attempts.length > 0) {
                    attemptsList.innerHTML = attempts.join('');
                } else {
                    attemptsList.innerHTML = '<div class="text-center p-3">Aucune tentative d\'appel</div>';
                }
                
                // Marquer comme chargé
                document.getElementById('attemptsContent').dataset.loaded = 'true';
            },
            error: function() {
                attemptsList.innerHTML = '<div class="text-center text-danger p-3">Erreur lors du chargement</div>';
            }
        });
    }

    // Gestion des tags
    function addTag(tagName) {
        if (!tagName.trim() || config.tags.includes(tagName)) {
            return;
        }
        
        config.tags.push(tagName);
        
        const tagsContainer = document.getElementById('currentTags');
        const tagElement = document.createElement('div');
        tagElement.className = 'tag';
        tagElement.innerHTML = `
            ${tagName}
            <span class="remove-tag" onclick="removeTag('${tagName}')">×</span>
        `;
        
        tagsContainer.appendChild(tagElement);
    }

    function removeTag(tagName) {
        config.tags = config.tags.filter(tag => tag !== tagName);
        
        // Retirer visuellement
        const tags = document.querySelectorAll('.tag');
        tags.forEach(tag => {
            if (tag.textContent.trim().replace('×', '').trim() === tagName) {
                tag.remove();
            }
        });
    }

    // Gérer la soumission du formulaire
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const action = $('#actionType').val();
        const notes = $('#notes').val();
        
        // Si une action spécifique est sélectionnée, valider
        if (action) {
            // Validation des notes
            if (!notes.trim()) {
                showNotification('Les notes sont obligatoires', 'error');
                $('#notes').focus();
                return;
            }
            
            // Validation spécifique à l'action
            if (action === 'confirm') {
                const confirmedPrice = $('#confirmed_price').val();
                if (!confirmedPrice) {
                    showNotification('Le prix confirmé est obligatoire', 'error');
                    $('#confirmed_price').focus();
                    return;
                }
                
                if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                    !$('#customer_city').val() || !$('#customer_address').val()) {
                    showNotification('Tous les champs client sont obligatoires pour une confirmation', 'error');
                    return;
                }
            } else if (action === 'schedule') {
                if (!$('#scheduled_date').val()) {
                    showNotification('La date programmée est obligatoire', 'error');
                    $('#scheduled_date').focus();
                    return;
                }
            }
            
            // Ajouter les champs cachés
            if (action === 'call') {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'increment_attempts',
                    value: '1'
                }).appendTo('#editOrderForm');
            }
            
            $('<input>').attr({
                type: 'hidden',
                name: 'action',
                value: action
            }).appendTo('#editOrderForm');
            
            $('<input>').attr({
                type: 'hidden',
                name: 'notes',
                value: notes
            }).appendTo('#editOrderForm');
        }
        
        showLoading();
        
        // Soumettre le formulaire
        this.submit();
    }

    // Afficher/masquer le modal des raccourcis
    function toggleShortcuts() {
        const modal = document.getElementById('shortcutsModal');
        if (modal.style.display === 'block') {
            modal.style.display = 'none';
        } else {
            modal.style.display = 'block';
        }
    }

    // Afficher une notification
    function showNotification(message, type = 'info') {
        const area = document.getElementById('notificationArea');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        area.appendChild(notification);
        
        // Afficher avec animation
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Masquer après 3 secondes
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Fonction d'affichage du chargement
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // Formatter les prix
    function formatPrice(price) {
        return parseFloat(price || 0).toFixed(3);
    }
</script>
@endsection