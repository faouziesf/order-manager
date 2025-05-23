@extends('layouts.admin')

@section('title', 'Modifier la Commande')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Styles généraux */

    .history-section {
        display: none;
        max-height: 600px;
        overflow-y: auto;
        margin-top: 20px;
        padding: 15px;
        background-color: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1rem 0 rgba(58, 59, 69, 0.15);
    }

    .history-section.show {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .timeline {
        margin: 20px 0;
        padding: 0;
    }

    .timeline-container {
        position: relative;
        padding-left: 20px;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 25px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #4e73df;
        border: 3px solid white;
        box-shadow: 0 0 0 1px #4e73df;
    }

    .timeline-item-content {
        position: relative;
        padding: 15px;
        border-radius: 5px;
        background-color: #f8f9fc;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .timeline-item-date {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .timeline-item-title {
        margin-bottom: 10px;
        font-weight: 600;
    }

    .timeline-item-user {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .timeline-item-status {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .timeline-item-notes {
        margin-top: 10px;
    }

    .timeline-item-notes p {
        margin: 5px 0 0;
        font-size: 0.9rem;
        padding: 8px;
        background-color: #fff;
        border-radius: 3px;
        border-left: 3px solid #4e73df;
    }

    .timeline-item-changes {
        margin-top: 10px;
        font-size: 0.9rem;
    }

    .timeline-item-changes ul {
        margin: 5px 0 0;
        padding-left: 20px;
    }

    .card {
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 10px;
    }
    
    .required-field::after {
        content: "*";
        color: red;
        margin-left: 4px;
    }
    
    /* Styles Select2 */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-selection__rendered {
        line-height: 36px !important;
    }
    
    .select2-selection {
        height: 38px !important;
        border: 1px solid #d1d3e2 !important;
    }
    
    /* Ligne de produit */
    .product-line {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #f8f9fc;
        position: relative;
    }
    
    .product-line:hover {
        border-color: #d1d3e2;
        box-shadow: 0 0.15rem 0.5rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .remove-line {
        position: absolute;
        top: 5px;
        right: 5px;
        color: #e74a3b;
        font-size: 1.2rem;
        cursor: pointer;
    }
    
    /* Status selector */
    .status-selectors {
        display: flex;
        gap: 15px;
    }
    
    .status-selector {
        display: flex;
        align-items: center;
    }
    
    .status-selector .status-label {
        margin-right: 5px;
        font-weight: 500;
    }
    
    /* Boutons */
    .btn-add-line {
        margin-bottom: 15px;
    }
    
    /* Résumé du panier */
    .cart-summary {
        background-color: #f8f9fc;
        border-radius: 0.5rem;
        padding: 10px;
        margin-top: 10px;
    }
    
    /* Badges */
    .status-badge {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    
    .status-nouvelle { background-color: #3498db; color: white; }
    .status-confirmée { background-color: #2ecc71; color: white; }
    .status-annulée { background-color: #e74c3c; color: white; }
    .status-datée { background-color: #f39c12; color: white; }
    .status-en_route { background-color: #9b59b6; color: white; }
    .status-livrée { background-color: #27ae60; color: white; }
    
    .priority-normale { background-color: #95a5a6; color: white; }
    .priority-urgente { background-color: #e67e22; color: white; }
    .priority-vip { background-color: #c0392b; color: white; }
    
    /* Loader */
    .page-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        display: none;
    }
    
    /* Prix confirmé */
    .confirmed-price-section {
        background-color: #f0fff4;
        border: 1px solid #c6f6d5;
        border-radius: 0.35rem;
        padding: 10px;
        margin-top: 10px;
    }
    
    /* Section datée */
    .scheduled-date-section {
        background-color: #fef5e9;
        border: 1px solid #fbd38d;
        border-radius: 0.35rem;
        padding: 10px;
        margin-top: 10px;
        display: none;
    }
    
    /* Actions section */
    .actions-section {
        background-color: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .action-label {
        font-weight: 600;
        margin-bottom: 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Loader -->
    <div class="page-overlay" id="pageLoader">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Chargement...</span>
        </div>
    </div>

    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Modifier la commande #{{ $order->id }}
            <span class="badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            <span class="badge priority-{{ $order->priority }}">{{ ucfirst($order->priority) }}</span>
        </h1>
        <div>
            <button type="button" class="btn btn-info btn-sm" id="toggleHistoryBtn">
                <i class="fas fa-history"></i> Historique
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <form id="orderForm" action="{{ route('admin.orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Colonne gauche - Informations client et statut -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Informations client</h6>
                        <div class="status-selectors">
                            <div class="status-selector">
                                <span class="status-label">Statut:</span>
                                <select id="status" name="status" class="form-control form-control-sm" style="width:auto;">
                                    <option value="nouvelle" {{ old('status', $order->status) == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                                    <option value="confirmée" {{ old('status', $order->status) == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                                    <option value="annulée" {{ old('status', $order->status) == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                    <option value="datée" {{ old('status', $order->status) == 'datée' ? 'selected' : '' }}>Datée</option>
                                    <option value="en_route" {{ old('status', $order->status) == 'en_route' ? 'selected' : '' }}>En route</option>
                                    <option value="livrée" {{ old('status', $order->status) == 'livrée' ? 'selected' : '' }}>Livrée</option>
                                </select>
                            </div>
                            <div class="status-selector">
                                <span class="status-label">Priorité:</span>
                                <select id="priority" name="priority" class="form-control form-control-sm" style="width:auto;">
                                    <option value="normale" {{ old('priority', $order->priority) == 'normale' ? 'selected' : '' }}>Normale</option>
                                    <option value="urgente" {{ old('priority', $order->priority) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    <option value="vip" {{ old('priority', $order->priority) == 'vip' ? 'selected' : '' }}>VIP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_phone" class="required-field">Téléphone</label>
                                    <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                                    @error('customer_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_phone_2">Téléphone 2</label>
                                    <input type="text" class="form-control @error('customer_phone_2') is-invalid @enderror" id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2', $order->customer_phone_2) }}">
                                    @error('customer_phone_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_name">Nom du client</label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping_cost">Frais de livraison (DT)</label>
                                    <input type="number" step="0.001" class="form-control @error('shipping_cost') is-invalid @enderror" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', $order->shipping_cost) }}">
                                    @error('shipping_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_governorate">Gouvernorat</label>
                                    <select class="form-control select2 @error('customer_governorate') is-invalid @enderror" id="customer_governorate" name="customer_governorate">
                                        <option value="">Sélectionner un gouvernorat</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" {{ old('customer_governorate', $order->customer_governorate) == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_governorate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_city">Ville</label>
                                    <select class="form-control select2 @error('customer_city') is-invalid @enderror" id="customer_city" name="customer_city" {{ $order->customer_governorate ? '' : 'disabled' }}>
                                        <option value="">Sélectionner d'abord un gouvernorat</option>
                                    </select>
                                    @error('customer_city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_address">Adresse détaillée</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" id="customer_address" name="customer_address" rows="2">{{ old('customer_address', $order->customer_address) }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="cart-summary mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Sous-total:</span>
                                <span id="subtotal" class="font-weight-bold">{{ number_format($order->total_price, 3) }} DT</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Frais de livraison:</span>
                                <span id="shipping" class="font-weight-bold">{{ number_format($order->shipping_cost, 3) }} DT</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total:</span>
                                <span id="total" class="font-weight-bold">{{ number_format($order->total_price + $order->shipping_cost, 3) }} DT</span>
                            </div>
                        </div>
                        
                        <!-- Section prix confirmé (apparaît uniquement pour les commandes confirmées) -->
                        <div id="confirmed-price-section" class="confirmed-price-section mt-2" style="{{ $order->status == 'confirmée' || old('status') == 'confirmée' ? '' : 'display:none;' }}">
                            <div class="form-group mb-0">
                                <label for="confirmed_price" class="required-field">Prix confirmé (DT)</label>
                                <input type="number" step="0.001" class="form-control @error('confirmed_price') is-invalid @enderror" id="confirmed_price" name="confirmed_price" value="{{ old('confirmed_price', $order->confirmed_price ?? $order->total_price + $order->shipping_cost) }}">
                                <small class="form-text text-muted">Veuillez confirmer le prix total de la commande.</small>
                                @error('confirmed_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Section date programmée (apparaît uniquement pour les commandes datées) -->
                        <div id="scheduled-date-section" class="scheduled-date-section mt-2" style="{{ $order->status == 'datée' || old('status') == 'datée' ? '' : 'display:none;' }}">
                            <div class="form-group mb-0">
                                <label for="scheduled_date" class="required-field">Date programmée</label>
                                <input type="text" class="form-control flatpickr @error('scheduled_date') is-invalid @enderror" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : '') }}" placeholder="Sélectionner une date...">
                                <small class="form-text text-muted">Sélectionnez la date de livraison programmée.</small>
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne droite - Produits et actions -->
            <div class="col-lg-6">
                <!-- Section produits -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Produits</h6>
                    </div>
                    <div class="card-body">
                        <div id="product-lines">
                            @php $lineIndex = 0; @endphp
                            @forelse($order->items as $item)
                                <div class="product-line" id="product-line-{{ $lineIndex }}">
                                    @if($lineIndex > 0) <!-- Premier élément non supprimable -->
                                        <span class="remove-line" data-line="{{ $lineIndex }}">❌</span>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group">
                                                <label for="product-select-{{ $lineIndex }}">Produit <span class="text-danger">*</span></label>
                                                <select class="form-control product-select" id="product-select-{{ $lineIndex }}" name="products[{{ $lineIndex }}][id]" data-line="{{ $lineIndex }}" required>
                                                    <option value="">Sélectionner un produit</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                                        </option>
                                                    @endforeach
                                                    <option value="new">➕ Ajouter un nouveau produit</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="product-quantity-{{ $lineIndex }}">Quantité <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control product-quantity" id="product-quantity-{{ $lineIndex }}" name="products[{{ $lineIndex }}][quantity]" value="{{ $item->quantity }}" min="1" data-line="{{ $lineIndex }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="text-right mb-1">
                                                <span class="line-total" id="line-total-{{ $lineIndex }}">{{ number_format($item->total_price, 3) }} DT</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $lineIndex++; @endphp
                            @empty
                                <!-- Si pas de produits, afficher une ligne vide -->
                                <div class="product-line" id="product-line-0">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group">
                                                <label for="product-select-0">Produit <span class="text-danger">*</span></label>
                                                <select class="form-control product-select" id="product-select-0" name="products[0][id]" data-line="0" required>
                                                    <option value="">Sélectionner un produit</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                            {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                                        </option>
                                                    @endforeach
                                                    <option value="new">➕ Ajouter un nouveau produit</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="product-quantity-0">Quantité <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control product-quantity" id="product-quantity-0" name="products[0][quantity]" value="1" min="1" data-line="0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="text-right mb-1">
                                                <span class="line-total" id="line-total-0">0.000 DT</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $lineIndex = 1; @endphp
                            @endforelse
                        </div>
                        
                        <button type="button" class="btn btn-info btn-sm btn-add-line w-100">
                            <i class="fas fa-plus"></i> Ajouter un autre produit
                        </button>
                        
                        <!-- Message d'erreur pour les produits -->
                        @error('products')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Section actions -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions à effectuer</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="action_type" class="action-label">Action à effectuer</label>
                            <select class="form-control" id="action_type" name="action_type">
                                <option value="">-- Choisir une action --</option>
                                <option value="call">Tentative d'appel</option>
                                <option value="confirm">Confirmer la commande</option>
                                <option value="cancel">Annuler la commande</option>
                                <option value="schedule">Dater la commande</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes" class="required-field">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            <small class="form-text text-muted">Veuillez expliquer la raison de cette action.</small>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Champs cachés pour les actions -->
                        <input type="hidden" name="increment_attempts" id="increment_attempts" value="0">
                        
                        <div class="form-group mt-4 mb-0">
                            <button type="submit" class="btn btn-success btn-block" id="submitButton">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <div id="historySection" class="history-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="m-0">Historique de la commande #{{ $order->id }}</h4>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="closeHistoryBtn">
                <i class="fas fa-times"></i> Fermer
            </button>
        </div>
    
        @if($order->history->count() > 0)
            <div class="timeline">
                <div class="timeline-container">
                    @foreach($order->history->sortByDesc('created_at') as $entry)
                        <div class="timeline-item">
                            <div class="timeline-item-content">
                                <span class="timeline-item-date">{{ $entry->created_at->format('d/m/Y H:i') }}</span>
                                <h6 class="timeline-item-title">
                                    @switch($entry->action)
                                        @case('création')
                                            <span class="text-primary"><i class="fas fa-plus-circle"></i> Création</span>
                                            @break
                                        @case('modification')
                                            <span class="text-info"><i class="fas fa-edit"></i> Modification</span>
                                            @break
                                        @case('confirmation')
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Confirmation</span>
                                            @break
                                        @case('annulation')
                                            <span class="text-danger"><i class="fas fa-times-circle"></i> Annulation</span>
                                            @break
                                        @case('datation')
                                            <span class="text-warning"><i class="fas fa-calendar"></i> Datation</span>
                                            @break
                                        @case('tentative')
                                            <span class="text-info"><i class="fas fa-phone"></i> Tentative d'appel</span>
                                            @break
                                        @case('livraison')
                                            <span class="text-success"><i class="fas fa-truck"></i> Livraison</span>
                                            @break
                                        @default
                                            <span class="text-secondary"><i class="fas fa-history"></i> Action</span>
                                    @endswitch
                                </h6>
                                <p class="timeline-item-user">
                                    Par: <strong>{{ $entry->getUserName() }}</strong>
                                </p>
                                
                                @if($entry->status_before !== $entry->status_after)
                                    <p class="timeline-item-status">
                                        Statut: 
                                        <span class="badge status-{{ $entry->status_before }}">{{ ucfirst($entry->status_before) }}</span>
                                        →
                                        <span class="badge status-{{ $entry->status_after }}">{{ ucfirst($entry->status_after) }}</span>
                                    </p>
                                @endif
                                
                                @if($entry->notes)
                                    <div class="timeline-item-notes">
                                        <strong>Notes:</strong>
                                        <p>{{ $entry->notes }}</p>
                                    </div>
                                @endif
                                
                                @if($entry->changes)
                                    <div class="timeline-item-changes">
                                        <strong>Modifications:</strong>
                                        <ul>
                                        @foreach(json_decode($entry->changes, true) as $field => $change)
                                            <li>
                                                {{ $field }}: {{ $change['old'] ?? 'Non défini' }} → {{ $change['new'] ?? 'Non défini' }}
                                            </li>
                                        @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center text-muted">
                <p>Aucun historique disponible pour cette commande.</p>
            </div>
        @endif
    </div>

</div>

<!-- Modal pour créer un nouveau produit -->
<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProductModalLabel">Créer un nouveau produit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="current-line-index" value="">
                <div class="form-group">
                    <label for="new_product_name" class="required-field">Nom du produit</label>
                    <input type="text" class="form-control" id="new_product_name" required>
                </div>
                <div class="form-group">
                    <label for="new_product_price" class="required-field">Prix (DT)</label>
                    <input type="number" step="0.001" class="form-control" id="new_product_price" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveNewProduct">Ajouter</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Historique de la commande #{{ $order->id }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="historyModalBody">
                @if($order->history->count() > 0)
                    <div class="timeline">
                        <div class="timeline-container">
                            @foreach($order->history->sortByDesc('created_at') as $entry)
                                <div class="timeline-item">
                                    <div class="timeline-item-content">
                                        <span class="timeline-item-date">{{ $entry->created_at->format('d/m/Y H:i') }}</span>
                                        <h6 class="timeline-item-title">
                                            @switch($entry->action)
                                                @case('création')
                                                    <span class="text-primary"><i class="fas fa-plus-circle"></i> Création</span>
                                                    @break
                                                @case('modification')
                                                    <span class="text-info"><i class="fas fa-edit"></i> Modification</span>
                                                    @break
                                                @case('confirmation')
                                                    <span class="text-success"><i class="fas fa-check-circle"></i> Confirmation</span>
                                                    @break
                                                @case('annulation')
                                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Annulation</span>
                                                    @break
                                                @case('datation')
                                                    <span class="text-warning"><i class="fas fa-calendar"></i> Datation</span>
                                                    @break
                                                @case('tentative')
                                                    <span class="text-info"><i class="fas fa-phone"></i> Tentative d'appel</span>
                                                    @break
                                                @case('livraison')
                                                    <span class="text-success"><i class="fas fa-truck"></i> Livraison</span>
                                                    @break
                                                @default
                                                    <span class="text-secondary"><i class="fas fa-history"></i> Action</span>
                                            @endswitch
                                        </h6>
                                        <p class="timeline-item-user">
                                            Par: <strong>{{ $entry->getUserName() }}</strong>
                                        </p>
                                        
                                        @if($entry->status_before !== $entry->status_after)
                                            <p class="timeline-item-status">
                                                Statut: 
                                                <span class="badge status-{{ $entry->status_before }}">{{ ucfirst($entry->status_before) }}</span>
                                                →
                                                <span class="badge status-{{ $entry->status_after }}">{{ ucfirst($entry->status_after) }}</span>
                                            </p>
                                        @endif
                                        
                                        @if($entry->notes)
                                            <div class="timeline-item-notes">
                                                <strong>Notes:</strong>
                                                <p>{{ $entry->notes }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($entry->changes)
                                            <div class="timeline-item-changes">
                                                <strong>Modifications:</strong>
                                                <ul>
                                                @foreach(json_decode($entry->changes, true) as $field => $change)
                                                    <li>
                                                        {{ $field }}: {{ $change['old'] ?? 'Non défini' }} → {{ $change['new'] ?? 'Non défini' }}
                                                    </li>
                                                @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <p>Aucun historique disponible pour cette commande.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script>
    $(document).ready(function() {
        // Loader pour les actions longues
        function showLoader() {
            $('#pageLoader').fadeIn(100);
        }
        
        function hideLoader() {
            $('#pageLoader').fadeOut(100);
        }
        
        // Initialiser Select2
        initializeSelect2();
        
        // Initialiser Flatpickr (sélecteur de date)
        flatpickr(".flatpickr", {
            locale: "fr",
            dateFormat: "Y-m-d",
            minDate: "today",
            disableMobile: "true"
        });
        
        // Compteur de lignes
        let lineCounter = {{ $lineIndex }}; // Continuer la numérotation à partir du dernier index
        
        // Charger les villes pour le gouvernorat sélectionné initialement
        if ($('#customer_governorate').val()) {
            loadCities($('#customer_governorate').val(), {{ old('customer_city', $order->customer_city ?? 'null') }});
        }
        
        // Gérer le changement de gouvernorat
        $('#customer_governorate').on('change', function() {
            const regionId = $(this).val();
            if (regionId) {
                loadCities(regionId);
            } else {
                $('#customer_city').prop('disabled', true).empty().append('<option value="">Sélectionner d\'abord un gouvernorat</option>');
            }
        });
        
        // Gérer le changement de statut
        $('#status').on('change', function() {
            const status = $(this).val();
            
            // Gérer l'affichage du prix confirmé
            if (status === 'confirmée') {
                $('#confirmed-price-section').slideDown();
            } else {
                $('#confirmed-price-section').slideUp();
            }
            
            // Gérer l'affichage de la date programmée
            if (status === 'datée') {
                $('#scheduled-date-section').slideDown();
            } else {
                $('#scheduled-date-section').slideUp();
            }
        });
        
// Gérer le changement d'action
$('#action_type').on('change', function() {
                const action = $(this).val();
                let helpText = '';
                
                // Mettre à jour le texte d'aide selon l'action
                switch(action) {
                    case 'call':
                        helpText = 'Veuillez indiquer le résultat de l\'appel.';
                        // Incrémenter les tentatives d'appel
                        $('#increment_attempts').val(1);
                        break;
                    case 'confirm':
                        helpText = 'Ajoutez des informations complémentaires sur la confirmation.';
                        // Mettre à jour le statut
                        $('#status').val('confirmée').trigger('change');
                        break;
                    case 'cancel':
                        helpText = 'Veuillez indiquer la raison de l\'annulation.';
                        // Mettre à jour le statut
                        $('#status').val('annulée').trigger('change');
                        break;
                    case 'schedule':
                        helpText = 'Ajoutez des informations sur la livraison programmée.';
                        // Mettre à jour le statut
                        $('#status').val('datée').trigger('change');
                        break;
                    default:
                        helpText = 'Veuillez expliquer la raison de cette action.';
                        $('#increment_attempts').val(0);
                }
                
                // Mettre à jour le texte d'aide
                $('#notes').siblings('small').text(helpText);
            });
        
        // Charger les villes
        function loadCities(regionId, selectedCityId = null) {
            const citySelect = $('#customer_city');
            citySelect.prop('disabled', true).empty().append('<option value="">Chargement...</option>');
            
            showLoader();
            $.ajax({
                url: "{{ route('admin.orders.getCities') }}",
                data: { region_id: regionId },
                success: function(cities) {
                    citySelect.empty().append('<option value="">Sélectionner une ville</option>');
                    
                    cities.forEach(function(city) {
                        const selected = selectedCityId && city.id == selectedCityId;
                        citySelect.append(new Option(city.name, city.id, selected, selected));
                    });
                    
                    citySelect.prop('disabled', false).trigger('change');
                    
                    // Si la ville a un frais de livraison, mettre à jour
                    if (cities.length > 0 && cities[0].shipping_cost) {
                        $('#shipping_cost').val(cities[0].shipping_cost);
                        updateCartSummary();
                    }
                    
                    hideLoader();
                },
                error: function() {
                    hideLoader();
                    alert('Erreur lors du chargement des villes');
                }
            });
        }
        
        // Fonction pour initialiser Select2
        function initializeSelect2() {
            $('.select2').select2({
                placeholder: "Sélectionner une option",
                allowClear: true
            });
            
            $('.product-select').select2({
                placeholder: "Sélectionner un produit",
                allowClear: true
            });
        }
        
        // Ajouter une nouvelle ligne de produit
        $('.btn-add-line').on('click', function() {
            const newLineHtml = `
                <div class="product-line" id="product-line-${lineCounter}">
                    <span class="remove-line" data-line="${lineCounter}">❌</span>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="product-select-${lineCounter}">Produit <span class="text-danger">*</span></label>
                                <select class="form-control product-select" id="product-select-${lineCounter}" name="products[${lineCounter}][id]" data-line="${lineCounter}" required>
                                    <option value="">Sélectionner un produit</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                            {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                        </option>
                                    @endforeach
                                    <option value="new">➕ Ajouter un nouveau produit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="product-quantity-${lineCounter}">Quantité <span class="text-danger">*</span></label>
                                <input type="number" class="form-control product-quantity" id="product-quantity-${lineCounter}" name="products[${lineCounter}][quantity]" value="1" min="1" data-line="${lineCounter}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-right mb-1">
                                <span class="line-total" id="line-total-${lineCounter}">0.000 DT</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#product-lines').append(newLineHtml);
            
            // Initialiser Select2 pour la nouvelle ligne
            $(`#product-select-${lineCounter}`).select2({
                placeholder: "Sélectionner un produit",
                allowClear: true
            });
            
            // Incrémenter le compteur
            lineCounter++;
            
            // Mettre à jour le résumé du panier
            updateCartSummary();
        });
        
        // Supprimer une ligne
        $(document).on('click', '.remove-line', function() {
            const lineIndex = $(this).data('line');
            $(`#product-line-${lineIndex}`).remove();
            
            // Mettre à jour le résumé du panier
            updateCartSummary();
        });
        
        // Mettre à jour le total d'une ligne quand le produit ou la quantité change
        $(document).on('change', '.product-select', function() {
            const lineIndex = $(this).data('line');
            const selectedOption = $(this).find('option:selected');
            
            if (selectedOption.val() === 'new') {
                // Ouvrir la modal pour créer un nouveau produit
                $('#current-line-index').val(lineIndex);
                $('#new_product_name').val('');
                $('#new_product_price').val('');
                $('#newProductModal').modal('show');
                
                // Réinitialiser la sélection
                $(this).val('').trigger('change');
            } else if (selectedOption.val()) {
                // Mettre à jour le total de la ligne
                const price = selectedOption.data('price');
                const quantity = $(`#product-quantity-${lineIndex}`).val() || 1;
                const total = price * quantity;
                
                $(`#line-total-${lineIndex}`).text(formatPrice(total) + ' DT');
                
                // Mettre à jour le résumé du panier
                updateCartSummary();
            }
        });
        
        // Mettre à jour le total quand la quantité change
        $(document).on('change', '.product-quantity', function() {
            const lineIndex = $(this).data('line');
            const selectedOption = $(`#product-select-${lineIndex}`).find('option:selected');
            const quantity = $(this).val() || 1;
            
            if (selectedOption.val() && selectedOption.val() !== 'new') {
                const price = selectedOption.data('price');
                const total = price * quantity;
                
                $(`#line-total-${lineIndex}`).text(formatPrice(total) + ' DT');
                
                // Mettre à jour le résumé du panier
                updateCartSummary();
            }
        });
        
        // Enregistrer un nouveau produit via la modal
        $('#saveNewProduct').click(function() {
            const name = $('#new_product_name').val();
            const price = $('#new_product_price').val();
            const lineIndex = $('#current-line-index').val();
            
            if (name && price) {
                // Ajouter une nouvelle option au select de cette ligne
                const newOption = new Option(`${name} - ${formatPrice(price)} DT [Nouveau]`, `new:${name}:${price}`, true, true);
                $(newOption).data('price', parseFloat(price));
                
                // Ajouter les champs cachés pour le nouveau produit
                $(`#product-line-${lineIndex}`).append(`
                    <input type="hidden" name="products[${lineIndex}][is_new]" value="1">
                    <input type="hidden" name="products[${lineIndex}][name]" value="${name}">
                    <input type="hidden" name="products[${lineIndex}][price]" value="${price}">
                `);
                
                // Sélectionner ce produit
                $(`#product-select-${lineIndex}`).append(newOption).trigger('change');
                
                // Mettre à jour le total de la ligne
                const quantity = $(`#product-quantity-${lineIndex}`).val() || 1;
                const total = parseFloat(price) * quantity;
                
                $(`#line-total-${lineIndex}`).text(formatPrice(total) + ' DT');
                
                // Mettre à jour le résumé du panier
                updateCartSummary();
                
                // Fermer la modal
                $('#newProductModal').modal('hide');
            } else {
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });
        
        // Gérer le clic sur le bouton d'historique
        $('#showOrderHistory').click(function() {
            // Ouvrir la modal
            $('#historyModal').modal('show');
            
            // Charger le contenu
            showLoader();
            $.ajax({
                url: "{{ route('admin.orders.history', $order) }}",
                success: function(data) {
                    $('#historyModalBody').html(data);
                    hideLoader();
                },
                error: function() {
                    $('#historyModalBody').html('<div class="alert alert-danger">Erreur lors du chargement de l\'historique</div>');
                    hideLoader();
                }
            });
        });
        
        // Mettre à jour le résumé du panier
        function updateCartSummary() {
            let subtotal = 0;
            
            // Parcourir toutes les lignes
            $('.product-line').each(function() {
                const lineId = $(this).attr('id').replace('product-line-', '');
                const selectEl = $(`#product-select-${lineId}`);
                
                if (selectEl.val() && selectEl.val() !== 'new') {
                    let price;
                    
                    // Vérifier si c'est un nouveau produit (valeur commence par "new:")
                    if (selectEl.val().startsWith('new:')) {
                        const parts = selectEl.val().split(':');
                        price = parseFloat(parts[2]);
                    } else {
                        price = selectEl.find('option:selected').data('price');
                    }
                    
                    const quantity = $(`#product-quantity-${lineId}`).val() || 1;
                    subtotal += price * quantity;
                }
            });
            
            const shipping = parseFloat($('#shipping_cost').val()) || 0;
            const total = subtotal + shipping;
            
            $('#subtotal').text(formatPrice(subtotal) + ' DT');
            $('#shipping').text(formatPrice(shipping) + ' DT');
            $('#total').text(formatPrice(total) + ' DT');
            
            // Mettre à jour le prix confirmé aussi (si pas déjà défini)
            if ($('#confirmed_price').val() == '0' || $('#confirmed_price').val() == '') {
                $('#confirmed_price').val(formatPrice(total));
            }
        }
        
        // Mettre à jour le résumé quand les frais de livraison changent
        $('#shipping_cost').on('change input', function() {
            updateCartSummary();
        });
        
        // Formatter les prix
        function formatPrice(price) {
            return parseFloat(price).toFixed(3);
        }
        
        // Valider le formulaire avant soumission
        $('#orderForm').on('submit', function(e) {
            // Vérifier qu'il y a au moins un produit sélectionné
            let hasProducts = false;
            
            $('.product-select').each(function() {
                if ($(this).val() && $(this).val() !== 'new') {
                    hasProducts = true;
                    return false; // Sortir de la boucle
                }
            });
            
            if (!hasProducts) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un produit.');
                return false;
            }
            
            // Vérifier les champs nécessaires en fonction de l'action choisie
            const actionType = $('#action_type').val();
            const notes = $('#notes').val();
            
            if (actionType) {
                // Si une action est sélectionnée, les notes sont obligatoires
                if (!notes) {
                    e.preventDefault();
                    alert('Veuillez entrer des notes pour expliquer cette action.');
                    $('#notes').focus();
                    return false;
                }
                
                // Vérifications spécifiques selon l'action
                switch(actionType) {
                    case 'confirm':
                        // Vérifier que tous les champs client sont remplis
                        if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                            !$('#customer_city').val() || !$('#customer_address').val()) {
                            e.preventDefault();
                            alert('Pour une commande confirmée, tous les champs client sont obligatoires.');
                            return false;
                        }
                        
                        // Vérifier que le prix confirmé est bien renseigné
                        if (!$('#confirmed_price').val()) {
                            e.preventDefault();
                            alert('Pour une commande confirmée, le prix confirmé est obligatoire.');
                            return false;
                        }
                        break;
                        
                    case 'schedule':
                        // Vérifier que la date est renseignée
                        if (!$('#scheduled_date').val()) {
                            e.preventDefault();
                            alert('Pour une commande datée, la date de livraison est obligatoire.');
                            $('#scheduled_date').focus();
                            return false;
                        }
                        break;
                }
            } else {
                // Vérifications standard selon le statut sélectionné
                if ($('#status').val() === 'confirmée') {
                    if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                        !$('#customer_city').val() || !$('#customer_address').val()) {
                        e.preventDefault();
                        alert('Pour une commande confirmée, tous les champs client sont obligatoires.');
                        return false;
                    }
                    
                    // Vérifier que le prix confirmé est bien renseigné
                    if (!$('#confirmed_price').val()) {
                        e.preventDefault();
                        alert('Pour une commande confirmée, le prix confirmé est obligatoire.');
                        return false;
                    }
                }
                
                // Si le statut est "datée", vérifier que la date est renseignée
                if ($('#status').val() === 'datée' && !$('#scheduled_date').val()) {
                    e.preventDefault();
                    alert('Pour une commande datée, la date de livraison est obligatoire.');
                    return false;
                }
            }
            
            // Traitement de l'action "call" (tentative d'appel)
            if (actionType === 'call') {
                e.preventDefault();
                
                if (!notes) {
                    alert('Veuillez entrer des notes pour l\'appel.');
                    $('#notes').focus();
                    return false;
                }
                
                // Faire une requête séparée pour l'action d'appel
                showLoader();
                $.ajax({
                    url: "{{ route('admin.orders.recordAttempt', $order) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        notes: notes
                    },
                    success: function(response) {
                        hideLoader();
                        
                        // Afficher un message de succès
                        alert('Tentative d\'appel enregistrée avec succès.');
                        location.reload();
                    },
                    error: function() {
                        hideLoader();
                        alert('Erreur lors de l\'enregistrement de la tentative d\'appel.');
                    }
                });
                
                return false;
            }
            
            // Désactiver le bouton de soumission pour éviter les soumissions multiples
            $('#submitButton').attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traitement en cours...');
            
            showLoader();
            
            return true;
        });
        
        // Mettre à jour le résumé initial
        updateCartSummary();
    });
</script>
<script>
// Gestion de l'affichage de l'historique
$(document).ready(function() {
    // Afficher l'historique
    $('#toggleHistoryBtn').click(function() {
        $('#historySection').addClass('show');
        $('html, body').animate({
            scrollTop: $('#historySection').offset().top - 20
        }, 500);
    });
    
    // Masquer l'historique
    $('#closeHistoryBtn').click(function() {
        $('#historySection').removeClass('show');
    });
});
</script>
@endsection