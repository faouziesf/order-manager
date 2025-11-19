@extends('layouts.admin')

@section('title', 'Créer une Commande')
@section('page-title', 'Nouvelle Commande')

@section('css')
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    line-height: 1.6;
}

.create-order-container {
    max-width: 1400px;
    margin: 20px auto;
    padding: 0 15px;
}

.page-title {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
}

.page-title h1 {
    font-size: 28px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* LAYOUT GRID */
.order-grid {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 25px;
}

/* FORMULAIRE CLIENT */
.client-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-header {
    font-size: 20px;
    font-weight: 700;
    color: #1e3a8a;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid #e5e7eb;
    position: relative;
}

.section-header::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 80px;
    height: 3px;
    background: #3b82f6;
}

.form-row {
    margin-bottom: 20px;
}

.form-row.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-label .req {
    color: #ef4444;
    display: none;
}

.form-label .req.show {
    display: inline;
}

.form-control {
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.2s;
    background: #fafbfc;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control.error {
    border-color: #ef4444;
    background: #fef2f2;
}

.phone-wrapper {
    position: relative;
}

.phone-status {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
    font-size: 18px;
}

.phone-status.checking {
    display: block;
    color: #9ca3af;
}

.phone-status.duplicate {
    display: block;
    color: #f59e0b;
}

.phone-status.clean {
    display: block;
    color: #10b981;
}

.alert-box {
    margin-top: 15px;
    padding: 15px 20px;
    border-radius: 8px;
    display: none;
    font-size: 14px;
}

.alert-box.show {
    display: block;
}

.alert-box.warning {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    color: #92400e;
}

.alert-box.success {
    background: #d1fae5;
    border: 2px solid #10b981;
    color: #065f46;
}

.alert-actions {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-outline {
    background: white;
    color: #374151;
    border: 2px solid #d1d5db;
}

.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* CART PANEL */
.cart-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
}

.cart-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h2 {
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-badge {
    background: rgba(255,255,255,0.3);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
}

.search-box {
    padding: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.search-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.suggestions-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 280px;
    overflow-y: auto;
    z-index: 100;
    display: none;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.suggestions-list.show {
    display: block;
}

.suggestion-item {
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}

.suggestion-item:hover {
    background: #f9fafb;
}

.suggestion-item .name {
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
}

.suggestion-item .ref {
    font-size: 12px;
    color: #3b82f6;
    background: #eff6ff;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
}

.suggestion-item .details {
    font-size: 13px;
    color: #6b7280;
    display: flex;
    justify-content: space-between;
    margin-top: 6px;
}

.cart-items {
    padding: 20px;
    min-height: 200px;
    max-height: 350px;
    overflow-y: auto;
    flex: 1;
}

.cart-empty {
    text-align: center;
    padding: 50px 20px;
    color: #9ca3af;
}

.cart-empty i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 10px;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.cart-item:hover {
    border-color: #e5e7eb;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-weight: 700;
    color: #111827;
    font-size: 14px;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-price {
    font-size: 13px;
    color: #6b7280;
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 4px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f3f4f6;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.qty-btn:hover {
    background: #3b82f6;
    color: white;
}

.qty-value {
    width: 40px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
}

.remove-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #fee2e2;
    color: #ef4444;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.remove-btn:hover {
    background: #ef4444;
    color: white;
}

.cart-summary {
    padding: 20px 25px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 15px;
}

.summary-row.total {
    font-size: 18px;
    font-weight: 700;
    color: #1e3a8a;
    padding-top: 12px;
    border-top: 2px solid #e5e7eb;
}

.cart-controls {
    padding: 20px 25px;
    background: white;
}

.control-group {
    margin-bottom: 20px;
}

.control-label {
    font-weight: 700;
    color: #374151;
    margin-bottom: 10px;
    font-size: 14px;
}

.status-selector {
    display: flex;
    gap: 10px;
}

.status-btn {
    flex: 1;
    padding: 12px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
    text-align: center;
}

.status-btn:hover {
    border-color: #3b82f6;
}

.status-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.status-btn.confirmee.active {
    background: #10b981;
    border-color: #10b981;
}

.price-override {
    margin-top: 15px;
    padding: 15px;
    background: #eff6ff;
    border: 2px solid #3b82f6;
    border-radius: 8px;
    display: none;
}

.price-override.show {
    display: block;
}

.employee-select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: white;
}

.employee-select:focus {
    outline: none;
    border-color: #3b82f6;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.btn {
    flex: 1;
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-submit {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.priority-badge {
    display: inline-block;
    padding: 6px 12px;
    background: #fbbf24;
    color: #78350f;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    margin-top: 10px;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 700px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    background: #1e3a8a;
    color: white;
    padding: 20px 25px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 18px;
    font-weight: 700;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 25px;
}

.history-item {
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 12px;
    border-left: 4px solid #3b82f6;
}

.history-item strong {
    color: #1e3a8a;
}

/* TOAST NOTIFICATION */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: #10b981;
    color: white;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    z-index: 10000;
    font-weight: 600;
    display: none;
    align-items: center;
    gap: 10px;
}

.toast.show {
    display: flex;
    animation: slideIn 0.3s ease;
}

.toast.error {
    background: #ef4444;
}

.toast.warning {
    background: #f59e0b;
}

.toast.info {
    background: #3b82f6;
}

@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .order-grid {
        grid-template-columns: 1fr;
    }

    .cart-section {
        position: relative;
        top: 0;
        max-height: none;
    }
}

@media (max-width: 768px) {
    .form-row.two-col {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .status-selector {
        flex-direction: column;
    }

    .form-control, .search-input, .employee-select {
        font-size: 16px; /* Prevent iOS zoom */
    }

    .btn {
        padding: 18px; /* Touch-friendly */
    }
}
</style>
@endsection

@section('content')
<div class="create-order-container">
    <div class="page-title">
        <h1><i class="fas fa-plus-circle"></i> Créer une Nouvelle Commande</h1>
    </div>

    <form id="orderForm" method="POST" action="{{ route('admin.orders.store') }}">
        @csrf
        <div class="order-grid">
            <!-- CLIENT FORM -->
            <div class="client-section">
                <h2 class="section-header"><i class="fas fa-user"></i> Informations Client</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Nom Complet
                            <span class="req" id="name-req">*</span>
                        </label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control"
                               value="{{ old('customer_name') }}" placeholder="Nom et prénom du client">
                        @error('customer_name')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row two-col">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Téléphone
                            <span class="req show">*</span>
                        </label>
                        <div class="phone-wrapper">
                            <input type="tel" name="customer_phone" id="customer_phone" class="form-control"
                                   value="{{ old('customer_phone') }}" placeholder="+216 XX XXX XXX" required>
                            <span class="phone-status" id="phone-status">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </div>
                        @error('customer_phone')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror

                        <div class="alert-box" id="duplicate-alert">
                            <div id="alert-message"></div>
                            <div class="alert-actions" id="alert-actions"></div>
                        </div>

                        <div class="priority-badge" id="priority-badge" style="display:none;">
                            <i class="fas fa-star"></i> Priorité Doublons
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone-alt"></i> Téléphone 2 (Optionnel)
                        </label>
                        <input type="tel" name="customer_phone_2" id="customer_phone_2" class="form-control"
                               value="{{ old('customer_phone_2') }}" placeholder="Téléphone alternatif">
                        @error('customer_phone_2')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row two-col">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marked-alt"></i> Gouvernorat
                            <span class="req" id="gov-req">*</span>
                        </label>
                        <select name="customer_governorate" id="customer_governorate" class="form-control">
                            <option value="">Sélectionner...</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('customer_governorate') == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_governorate')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-city"></i> Ville
                            <span class="req" id="city-req">*</span>
                        </label>
                        <select name="customer_city" id="customer_city" class="form-control">
                            <option value="">Sélectionner d'abord un gouvernorat...</option>
                        </select>
                        @error('customer_city')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Adresse
                            <span class="req" id="address-req">*</span>
                        </label>
                        <textarea name="customer_address" id="customer_address" class="form-control" rows="3"
                                  placeholder="Adresse complète de livraison">{{ old('customer_address') }}</textarea>
                        @error('customer_address')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i> Notes (Optionnel)
                        </label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"
                                  placeholder="Remarques ou commentaires">{{ old('notes') }}</textarea>
                        @error('notes')<div style="color:#ef4444;font-size:13px;margin-top:5px;">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- CART PANEL -->
            <div class="cart-section">
                <div class="cart-header">
                    <h2><i class="fas fa-shopping-cart"></i> Panier</h2>
                    <span class="cart-badge" id="cart-count">0</span>
                </div>

                <div class="search-box">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="product-search"
                               placeholder="Rechercher un produit..." autocomplete="off">
                        <div class="suggestions-list" id="suggestions"></div>
                    </div>
                </div>

                <div class="cart-items" id="cart-items">
                    <div class="cart-empty" id="cart-empty">
                        <i class="fas fa-shopping-basket"></i>
                        <div style="font-weight:700;font-size:16px;margin-bottom:5px;">Panier vide</div>
                        <div>Recherchez et ajoutez des produits</div>
                    </div>
                </div>

                <div class="cart-summary" id="cart-summary" style="display:none;">
                    <div class="summary-row">
                        <span>Sous-total:</span>
                        <span id="subtotal">0.000 TND</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="total">0.000 TND</span>
                    </div>
                </div>

                <div class="cart-controls">
                    <div class="control-group">
                        <div class="control-label"><i class="fas fa-flag"></i> Statut de la Commande</div>
                        <div class="status-selector">
                            <button type="button" class="status-btn active" data-status="nouvelle">
                                <i class="fas fa-circle"></i> Nouvelle
                            </button>
                            <button type="button" class="status-btn confirmee" data-status="confirmée">
                                <i class="fas fa-check-circle"></i> Confirmée
                            </button>
                        </div>
                        <input type="hidden" name="status" id="status" value="nouvelle">
                        <input type="hidden" name="priority" id="priority" value="normale">

                        <div class="price-override" id="price-override">
                            <label class="form-label" style="margin-bottom:8px;">
                                <i class="fas fa-coins"></i> Prix Total Personnalisé
                            </label>
                            <input type="number" name="total_price" id="total_price" class="form-control"
                                   step="0.001" min="0" placeholder="Laisser vide pour calcul auto">
                            <small style="color:#6b7280;font-size:12px;margin-top:8px;display:block;">
                                Laissez vide pour utiliser le total calculé automatiquement
                            </small>
                        </div>
                    </div>

                    @if(!Auth::guard('admin')->user()->isEmployee())
                    <div class="control-group">
                        <label class="control-label"><i class="fas fa-user-tie"></i> Assigner à un Employé</label>
                        <select name="employee_id" id="employee_id" class="employee-select">
                            <option value="">Aucun employé</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="action-buttons">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-submit" id="submit-btn">
                            <i class="fas fa-save"></i> Créer la Commande
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden products data -->
        <div id="cart-data"></div>
    </form>
</div>

<!-- History Modal -->
<div class="modal" id="historyModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Historique du Client</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modal-body">
            <div style="text-align:center;padding:40px 20px;color:#9ca3af;">
                <i class="fas fa-spinner fa-spin" style="font-size:32px;margin-bottom:15px;"></i>
                <div>Chargement...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ==================== VARIABLES GLOBALES ====================
let cart = [];
let phoneCheckTimeout;
let duplicatesFound = false;
let latestClientData = null;

// ==================== PHONE DUPLICATE CHECK ====================
$('#customer_phone').on('input', function() {
    const phone = $(this).val().trim();
    clearTimeout(phoneCheckTimeout);

    if (phone.length >= 8) {
        phoneCheckTimeout = setTimeout(() => checkPhone(phone), 600);
        $('#phone-status').removeClass('duplicate clean').addClass('checking').show();
    } else {
        resetPhoneCheck();
    }
});

function checkPhone(phone) {
    $.ajax({
        url: '/admin/orders/check-phone-duplicates',
        data: { phone: phone },
        success: function(response) {
            $('#phone-status').removeClass('checking');

            if (response.has_duplicates && response.total_orders > 0) {
                $('#phone-status').addClass('duplicate').html('<i class="fas fa-exclamation-triangle"></i>');
                showDuplicateAlert(response);
                duplicatesFound = true;
                setPriority('urgente');
            } else {
                $('#phone-status').addClass('clean').html('<i class="fas fa-check"></i>');
                showCleanAlert();
                duplicatesFound = false;
                setPriority('normale');
            }
        },
        error: function() {
            resetPhoneCheck();
        }
    });
}

function resetPhoneCheck() {
    $('#phone-status').removeClass('checking duplicate clean').hide();
    $('#duplicate-alert').removeClass('show');
    duplicatesFound = false;
    setPriority('normale');
}

function showDuplicateAlert(data) {
    $('#alert-message').html(`<strong><i class="fas fa-exclamation-triangle"></i> ${data.total_orders} commande(s) trouvée(s) pour ce numéro!</strong>`);
    $('#alert-actions').html(`
        <button type="button" class="btn-sm btn-primary" onclick="viewHistory()">
            <i class="fas fa-history"></i> Voir Historique
        </button>
        <button type="button" class="btn-sm btn-success" onclick="autoFill()">
            <i class="fas fa-fill-drip"></i> Pré-remplir
        </button>
        <button type="button" class="btn-sm btn-outline" onclick="dismissAlert()">
            <i class="fas fa-times"></i> Ignorer
        </button>
    `);
    $('#duplicate-alert').addClass('show warning');

    // Load latest order data for autofill
    loadLatestClientData($('#customer_phone').val());
}

function showCleanAlert() {
    $('#alert-message').html('<strong><i class="fas fa-check-circle"></i> Aucun doublon détecté</strong>');
    $('#alert-actions').empty();
    $('#duplicate-alert').addClass('show success');
    setTimeout(() => $('#duplicate-alert').removeClass('show'), 3000);
}

function dismissAlert() {
    $('#duplicate-alert').removeClass('show');
    setPriority('normale');
}

function setPriority(priority) {
    $('#priority').val(priority);
    if (priority === 'urgente') {
        $('#priority-badge').show();
    } else {
        $('#priority-badge').hide();
    }
}

// ==================== CLIENT HISTORY ====================
function loadLatestClientData(phone) {
    $.ajax({
        url: '/admin/orders/client-history',
        data: { phone: phone },
        success: function(response) {
            if (response.latest_order) {
                latestClientData = response.latest_order;
            }
        }
    });
}

function viewHistory() {
    const phone = $('#customer_phone').val().trim();
    $('#historyModal').addClass('show');
    $('#modal-body').html('<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:32px;"></i><div style="margin-top:15px;">Chargement...</div></div>');

    $.ajax({
        url: '/admin/orders/client-history',
        data: { phone: phone },
        success: function(response) {
            let html = '';
            if (response.orders && response.orders.length > 0) {
                html = `<div style="margin-bottom:20px;padding:15px;background:#eff6ff;border-radius:8px;color:#1e3a8a;font-weight:700;">
                    <i class="fas fa-info-circle"></i> ${response.orders.length} commande(s) trouvée(s)
                </div>`;

                response.orders.forEach(order => {
                    html += `
                        <div class="history-item">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <strong>Commande #${order.id}</strong>
                                <span style="padding:4px 10px;background:#10b981;color:white;border-radius:4px;font-size:12px;">${order.status}</span>
                            </div>
                            <div style="font-size:13px;color:#6b7280;">
                                <strong>Client:</strong> ${order.customer_name || 'N/A'}<br>
                                <strong>Montant:</strong> ${parseFloat(order.total_price).toFixed(3)} TND<br>
                                <strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString('fr-FR')}
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div style="text-align:center;padding:40px;color:#9ca3af;">Aucun historique trouvé</div>';
            }
            $('#modal-body').html(html);
        },
        error: function() {
            $('#modal-body').html('<div style="text-align:center;padding:40px;color:#ef4444;">Erreur de chargement</div>');
        }
    });
}

function autoFill() {
    if (!latestClientData) {
        showToast('warning', 'Aucune donnée disponible');
        return;
    }

    $('#customer_name').val(latestClientData.customer_name || '');
    $('#customer_phone_2').val(latestClientData.customer_phone_2 || '');
    $('#customer_address').val(latestClientData.customer_address || '');

    if (latestClientData.customer_governorate) {
        $('#customer_governorate').val(latestClientData.customer_governorate).trigger('change');
        setTimeout(() => {
            if (latestClientData.customer_city) {
                $('#customer_city').val(latestClientData.customer_city);
            }
        }, 800);
    }

    showToast('success', 'Données pré-remplies avec succès');
}

function closeModal() {
    $('#historyModal').removeClass('show');
}

// ==================== GOVERNORATE/CITY LOADING ====================
$('#customer_governorate').on('change', function() {
    const regionId = $(this).val();
    const citySelect = $('#customer_city');

    if (regionId) {
        citySelect.html('<option value="">Chargement...</option>');

        $.ajax({
            url: '/admin/orders/get-cities',
            data: { region_id: regionId },
            success: function(cities) {
                citySelect.html('<option value="">Sélectionner...</option>');
                cities.forEach(city => {
                    citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                });
            },
            error: function() {
                citySelect.html('<option value="">Erreur</option>');
            }
        });
    } else {
        citySelect.html('<option value="">Sélectionner d\'abord un gouvernorat...</option>');
    }
});

// ==================== PRODUCT SEARCH ====================
$('#product-search').on('input', function() {
    const query = $(this).val().trim();
    if (query.length >= 2) {
        searchProducts(query);
    } else {
        $('#suggestions').removeClass('show').empty();
    }
});

function searchProducts(query) {
    $.ajax({
        url: '/admin/orders/search-products',
        data: { search: query },
        success: function(products) {
            let html = '';
            if (products.length === 0) {
                html = '<div class="suggestion-item">Aucun produit trouvé</div>';
            } else {
                products.forEach(product => {
                    html += `
                        <div class="suggestion-item" onclick='addToCart(${JSON.stringify(product)})'>
                            <div class="name">${product.name}</div>
                            ${product.reference ? `<span class="ref">Réf: ${product.reference}</span>` : ''}
                            <div class="details">
                                <span>Stock: ${product.stock}</span>
                                <span style="color:#10b981;font-weight:700;">${parseFloat(product.price).toFixed(3)} TND</span>
                            </div>
                        </div>
                    `;
                });
            }
            $('#suggestions').html(html).addClass('show');
        }
    });
}

$(document).on('click', function(e) {
    if (!$(e.target).closest('.search-wrapper').length) {
        $('#suggestions').removeClass('show');
    }
});

// ==================== CART MANAGEMENT ====================
function addToCart(product) {
    const existing = cart.find(item => item.id === product.id);

    if (existing) {
        existing.quantity++;
        showToast('info', `Quantité augmentée pour ${product.name}`);
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            reference: product.reference,
            price: parseFloat(product.price),
            quantity: 1,
            stock: product.stock
        });
        showToast('success', `${product.name} ajouté au panier`);
    }

    updateCart();
    $('#product-search').val('');
    $('#suggestions').removeClass('show');
}

function updateCart() {
    const itemsContainer = $('#cart-items');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    $('#cart-count').text(totalItems);

    if (cart.length === 0) {
        $('#cart-empty').show();
        $('#cart-summary').hide();
        itemsContainer.find('.cart-item').remove();
    } else {
        $('#cart-empty').hide();
        $('#cart-summary').show();

        itemsContainer.find('.cart-item').remove();
        cart.forEach(item => {
            const itemHtml = `
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">
                            ${item.reference ? `Réf: ${item.reference} • ` : ''}
                            ${item.price.toFixed(3)} TND × ${item.quantity}
                        </div>
                    </div>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="changeQty(${item.id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <div class="qty-value">${item.quantity}</div>
                        <button type="button" class="qty-btn" onclick="changeQty(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button type="button" class="remove-btn" onclick="removeFromCart(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            $('#cart-empty').before(itemHtml);
        });

        updateSummary();
    }

    updateFormData();
}

function changeQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (item) {
        const newQty = item.quantity + delta;
        if (newQty >= 1 && newQty <= item.stock) {
            item.quantity = newQty;
            updateCart();
        } else if (newQty > item.stock) {
            showToast('warning', `Stock maximum: ${item.stock}`);
        }
    }
}

function removeFromCart(id) {
    const item = cart.find(i => i.id === id);
    if (item) {
        cart = cart.filter(i => i.id !== id);
        updateCart();
        showToast('info', `${item.name} retiré du panier`);
    }
}

function updateSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    $('#subtotal').text(subtotal.toFixed(3) + ' TND');
    $('#total').text(subtotal.toFixed(3) + ' TND');
}

function updateFormData() {
    const container = $('#cart-data').empty();
    cart.forEach((item, index) => {
        container.append(`<input type="hidden" name="products[${index}][id]" value="${item.id}">`);
        container.append(`<input type="hidden" name="products[${index}][quantity]" value="${item.quantity}">`);
    });
}

// ==================== STATUS MANAGEMENT ====================
$('.status-btn').on('click', function() {
    $('.status-btn').removeClass('active');
    $(this).addClass('active');
    const status = $(this).data('status');
    $('#status').val(status);

    if (status === 'confirmée') {
        $('#price-override').addClass('show');
        makeFieldsRequired();
        showToast('info', 'Statut confirmé: tous les champs sont obligatoires');
    } else {
        $('#price-override').removeClass('show');
        makeFieldsOptional();
    }
});

function makeFieldsRequired() {
    $('#name-req, #gov-req, #city-req, #address-req').addClass('show');
    $('#customer_name, #customer_governorate, #customer_city, #customer_address').prop('required', true);
}

function makeFieldsOptional() {
    $('#name-req, #gov-req, #city-req, #address-req').removeClass('show');
    $('#customer_name, #customer_governorate, #customer_city, #customer_address').prop('required', false);
}

// ==================== FORM VALIDATION ====================
$('#orderForm').on('submit', function(e) {
    const errors = [];
    const status = $('#status').val();

    // Phone required
    if (!$('#customer_phone').val().trim()) {
        errors.push('Le numéro de téléphone est obligatoire');
    }

    // Cart not empty
    if (cart.length === 0) {
        errors.push('Le panier est vide - Ajoutez au moins un produit');
    }

    // Confirmed order validations
    if (status === 'confirmée') {
        if (!$('#customer_name').val().trim()) {
            errors.push('Le nom complet est obligatoire pour une commande confirmée');
        }
        if (!$('#customer_governorate').val()) {
            errors.push('Le gouvernorat est obligatoire pour une commande confirmée');
        }
        if (!$('#customer_city').val()) {
            errors.push('La ville est obligatoire pour une commande confirmée');
        }
        if (!$('#customer_address').val().trim()) {
            errors.push('L\'adresse est obligatoire pour une commande confirmée');
        }

        // Stock check
        cart.forEach(item => {
            if (item.quantity > item.stock) {
                errors.push(`Stock insuffisant pour ${item.name}: ${item.quantity} demandée mais ${item.stock} disponible`);
            }
        });

        if (errors.length === 0) {
            if (!confirm('Confirmer cette commande?\n\nLe stock sera automatiquement déduit.')) {
                e.preventDefault();
                return false;
            }
        }
    }

    if (errors.length > 0) {
        e.preventDefault();
        showToast('error', errors.join('\n'));
        return false;
    }

    $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création en cours...');
    showToast('success', 'Création de la commande en cours...');
});

// ==================== TOAST NOTIFICATION ====================
function showToast(type, message) {
    const toast = $(`
        <div class="toast ${type}">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `);

    $('body').append(toast);
    setTimeout(() => toast.addClass('show'), 10);

    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, type === 'error' ? 6000 : 3000);
}

// ==================== INITIALIZATION ====================
$(document).ready(function() {
    console.log('Order creation page initialized');
    $('#customer_phone').focus();
    showToast('info', 'Saisissez un téléphone pour vérifier les doublons');
});
</script>
@endsection
