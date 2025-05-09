@extends('layouts.admin')

@section('title', 'Créer une Commande')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styles généraux */
    .card {
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    
    .card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
        transition: all 0.2s;
    }
    
    .product-line:hover {
        border-color: #4e73df;
        box-shadow: 0 0.15rem 0.5rem 0 rgba(78, 115, 223, 0.15);
        transform: translateY(-2px);
    }
    
    .remove-line {
        position: absolute;
        top: 5px;
        right: 5px;
        color: #e74a3b;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 2;
    }
    
    /* Status selector */
    .status-selector-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .status-selector {
        flex: 1;
        position: relative;
        cursor: pointer;
    }
    
    .status-selector input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    
    .status-selector label {
        display: block;
        padding: 10px;
        text-align: center;
        border-radius: 0.35rem;
        font-weight: 500;
        color: #6e707e;
        background-color: #f8f9fc;
        border: 1px solid #e3e6f0;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .status-selector input[type="radio"]:checked + label {
        background-color: #4e73df;
        color: white;
        border-color: #4e73df;
        box-shadow: 0 0.125rem 0.25rem 0 rgba(78, 115, 223, 0.2);
    }
    
    .status-selector:hover label {
        border-color: #4e73df;
    }
    
    /* Priority selectors */
    .priority-selector-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .priority-selector {
        flex: 1;
        position: relative;
        cursor: pointer;
    }
    
    .priority-selector input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    
    .priority-selector label {
        display: block;
        padding: 10px;
        text-align: center;
        border-radius: 0.35rem;
        font-weight: 500;
        color: #6e707e;
        background-color: #f8f9fc;
        border: 1px solid #e3e6f0;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .priority-selector input[type="radio"]:checked + label.priority-normale {
        background-color: #1cc88a;
        color: white;
        border-color: #1cc88a;
    }
    
    .priority-selector input[type="radio"]:checked + label.priority-urgente {
        background-color: #f6c23e;
        color: white;
        border-color: #f6c23e;
    }
    
    .priority-selector input[type="radio"]:checked + label.priority-vip {
        background-color: #e74a3b;
        color: white;
        border-color: #e74a3b;
    }
    
    /* Boutons */
    .btn-add-line {
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    
    .btn-add-line:hover {
        transform: translateY(-2px);
    }
    
    /* Résumé du panier */
    .cart-summary {
        background-color: #f8f9fc;
        border-radius: 0.5rem;
        padding: 15px;
        margin-top: 15px;
        box-shadow: 0 0.15rem 0.5rem 0 rgba(0, 0, 0, 0.05);
        border: 1px solid #e3e6f0;
    }
    
    .cart-total {
        font-size: 1.1rem;
        font-weight: 700;
        color: #4e73df;
    }
    
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
    
    /* Tooltips */
    .tooltip-icon {
        color: #4e73df;
        margin-left: 5px;
        cursor: pointer;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .animate-fadein {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    /* Badges */
    .status-badge {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    
    .status-nouvelle { background-color: #3498db; color: white; }
    .status-confirmée { background-color: #2ecc71; color: white; }
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
        <h1 class="h3 mb-0 text-gray-800">Nouvelle commande</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
    
    <form id="orderForm" action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Statut et priorité</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="font-weight-bold mb-2">Statut initial :</label>
                        <div class="status-selector-container">
                            <div class="status-selector">
                                <input type="radio" id="status-nouvelle" name="status" value="nouvelle" {{ old('status') == 'nouvelle' ? 'checked' : 'checked' }}>
                                <label for="status-nouvelle">Nouvelle</label>
                            </div>
                            <div class="status-selector">
                                <input type="radio" id="status-confirmée" name="status" value="confirmée" {{ old('status') == 'confirmée' ? 'checked' : '' }}>
                                <label for="status-confirmée">Confirmée</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold mb-2">Priorité :</label>
                        <div class="priority-selector-container">
                            <div class="priority-selector">
                                <input type="radio" id="priority-normale" name="priority" value="normale" {{ old('priority') == 'normale' ? 'checked' : 'checked' }}>
                                <label for="priority-normale" class="priority-normale">Normale</label>
                            </div>
                            <div class="priority-selector">
                                <input type="radio" id="priority-urgente" name="priority" value="urgente" {{ old('priority') == 'urgente' ? 'checked' : '' }}>
                                <label for="priority-urgente" class="priority-urgente">Urgente</label>
                            </div>
                            <div class="priority-selector">
                                <input type="radio" id="priority-vip" name="priority" value="vip" {{ old('priority') == 'vip' ? 'checked' : '' }}>
                                <label for="priority-vip" class="priority-vip">VIP</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Colonne gauche - Informations client et statut -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Informations client</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_phone" class="required-field">Téléphone</label>
                                    <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required>
                                    @error('customer_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_phone_2">Téléphone 2</label>
                                    <input type="text" class="form-control @error('customer_phone_2') is-invalid @enderror" id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2') }}">
                                    @error('customer_phone_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_name">Nom du client
                                <span class="required-field-indicator confirmation-required" style="display: none">*</span>
                                <i class="fas fa-info-circle tooltip-icon" data-toggle="tooltip" title="Le nom est obligatoire pour les commandes confirmées"></i>
                            </label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name') }}">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_governorate">Gouvernorat
                                        <span class="required-field-indicator confirmation-required" style="display: none">*</span>
                                    </label>
                                    <select class="form-control select2 @error('customer_governorate') is-invalid @enderror" id="customer_governorate" name="customer_governorate">
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
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_city">Ville
                                        <span class="required-field-indicator confirmation-required" style="display: none">*</span>
                                    </label>
                                    <select class="form-control select2 @error('customer_city') is-invalid @enderror" id="customer_city" name="customer_city" disabled>
                                        <option value="">Sélectionner d'abord un gouvernorat</option>
                                    </select>
                                    @error('customer_city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping_cost">Frais de livraison (DT)</label>
                                    <input type="number" step="0.001" class="form-control @error('shipping_cost') is-invalid @enderror" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', 0) }}">
                                    @error('shipping_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group confirmation-field" style="display: none;">
                                    <label for="confirmed_price">Prix confirmé (DT)
                                        <span class="required-field">*</span>
                                    </label>
                                    <input type="number" step="0.001" class="form-control @error('confirmed_price') is-invalid @enderror" id="confirmed_price" name="confirmed_price" value="{{ old('confirmed_price', 0) }}">
                                    @error('confirmed_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_address">Adresse détaillée
                                <span class="required-field-indicator confirmation-required" style="display: none">*</span>
                            </label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" id="customer_address" name="customer_address" rows="2">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="cart-summary mt-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span id="subtotal" class="font-weight-bold">0.000 DT</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frais de livraison:</span>
                                <span id="shipping" class="font-weight-bold">0.000 DT</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total:</span>
                                <span id="total" class="cart-total">0.000 DT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne droite - Produits -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Produits</h6>
                    </div>
                    <div class="card-body">
                        <div id="product-lines">
                            <!-- La première ligne de produit (non supprimable) -->
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
                        </div>
                        
                        <button type="button" class="btn btn-info btn-sm btn-add-line w-100">
                            <i class="fas fa-plus"></i> Ajouter un autre produit
                        </button>
                        
                        <!-- Message d'erreur pour les produits -->
                        @error('products')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                        
                        <div class="form-group mt-4 mb-0">
                            <button type="submit" class="btn btn-success btn-lg btn-block" id="submitButton">
                                <i class="fas fa-save"></i> Enregistrer la commande
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialiser les tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Loader pour les actions longues
        function showLoader() {
            $('#pageLoader').fadeIn(100);
        }
        
        function hideLoader() {
            $('#pageLoader').fadeOut(100);
        }
        
        // Afficher/masquer les champs obligatoires pour les commandes confirmées
        function toggleConfirmationFields() {
            if ($('input[name="status"]:checked').val() === 'confirmée') {
                $('.confirmation-required').show();
                $('.confirmation-field').show();
            } else {
                $('.confirmation-required').hide();
                $('.confirmation-field').hide();
            }
        }
        
        // Initialiser Select2
        initializeSelect2();
        
        // Exécuter au démarrage
        toggleConfirmationFields();
        
        // Gérer le changement de statut
        $('input[name="status"]').on('change', function() {
            toggleConfirmationFields();
        });
        
        // Compteur de lignes
        let lineCounter = 1;
        
        // Gérer le changement de gouvernorat
        $('#customer_governorate').on('change', function() {
            const regionId = $(this).val();
            const citySelect = $('#customer_city');
            
            citySelect.prop('disabled', true).empty().append('<option value="">Chargement...</option>');
            
            if (regionId) {
                showLoader();
                $.ajax({
                    url: "{{ route('admin.orders.getCities') }}",
                    data: { region_id: regionId },
                    success: function(cities) {
                        citySelect.empty().append('<option value="">Sélectionner une ville</option>');
                        
                        cities.forEach(function(city) {
                            citySelect.append(new Option(city.name, city.id, false, false));
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
            } else {
                citySelect.empty().append('<option value="">Sélectionner d\'abord un gouvernorat</option>');
                citySelect.prop('disabled', true);
            }
        });
        
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
                <div class="product-line animate-fadein" id="product-line-${lineCounter}">
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
            $(`#product-line-${lineIndex}`).fadeOut(300, function() {
                $(this).remove();
                // Mettre à jour le résumé du panier
                updateCartSummary();
            });
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
            
            // Mettre à jour le prix confirmé
            $('#confirmed_price').val(formatPrice(total));
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
            
            // Si le statut est "confirmée", vérifier que tous les champs client sont remplis
            if ($('input[name="status"]:checked').val() === 'confirmée') {
                if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                    !$('#customer_city').val() || !$('#customer_address').val()) {
                    e.preventDefault();
                    alert('Pour une commande confirmée, tous les champs client sont obligatoires.');
                    return false;
                }
            }
            
            // Désactiver le bouton de soumission pour éviter les soumissions multiples
            $('#submitButton').attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...');
            
            showLoader();
            
            return true;
        });
        
        // Mettre à jour le résumé initial
        updateCartSummary();
    });
</script>
@endsection