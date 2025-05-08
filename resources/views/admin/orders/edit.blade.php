@extends('layouts.admin')

@section('title', 'Modifier la Commande')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .product-card {
        border: 1px solid #f0f0f0;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
        transition: all 0.3s;
    }
    
    .product-card:hover {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .remove-product {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        cursor: pointer;
        color: #e74a3b;
    }
    
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
    
    .required-field::after {
        content: "*";
        color: red;
        margin-left: 4px;
    }
    
    .add-product-icon {
        background-color: #4e73df;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .add-product-icon:hover {
        transform: scale(1.1);
    }
    
    .cart-summary {
        background-color: #f8f9fc;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    
    .history-btn {
        position: absolute;
        top: 0.5rem;
        right: 2.5rem;
        cursor: pointer;
        color: #4e73df;
    }
    
    .status-badge {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Modifier la commande #{{ $order->id }}
            <span class="badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            <span class="badge priority-{{ $order->priority }}">{{ ucfirst($order->priority) }}</span>
        </h1>
        <div>
            <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#historyModal" data-order-id="{{ $order->id }}">
                <i class="fas fa-history"></i> Historique
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>
    
    <form id="orderForm" action="{{ route('admin.orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Informations client -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Informations du client</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="customer_phone" class="required-field">Numéro de téléphone</label>
                            <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_phone_2">Numéro de téléphone secondaire</label>
                            <input type="text" class="form-control @error('customer_phone_2') is-invalid @enderror" id="customer_phone_2" name="customer_phone_2" value="{{ old('customer_phone_2', $order->customer_phone_2) }}">
                            @error('customer_phone_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_name">Nom du client</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
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
                        
                        <div class="form-group">
                            <label for="customer_city">Ville</label>
                            <select class="form-control select2 @error('customer_city') is-invalid @enderror" id="customer_city" name="customer_city" {{ $order->customer_governorate ? '' : 'disabled' }}>
                                <option value="">Sélectionner d'abord un gouvernorat</option>
                            </select>
                            @error('customer_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_address">Adresse détaillée</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" id="customer_address" name="customer_address" rows="3">{{ old('customer_address', $order->customer_address) }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_cost">Frais de livraison (DT)</label>
                            <input type="number" step="0.001" class="form-control @error('shipping_cost') is-invalid @enderror" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', $order->shipping_cost) }}">
                            @error('shipping_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Notes et statut -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Statut et notes</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status" class="required-field">Statut</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="nouvelle" {{ old('status', $order->status) == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                                <option value="confirmée" {{ old('status', $order->status) == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                                <option value="annulée" {{ old('status', $order->status) == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                <option value="datée" {{ old('status', $order->status) == 'datée' ? 'selected' : '' }}>Datée</option>
                                <option value="en_route" {{ old('status', $order->status) == 'en_route' ? 'selected' : '' }}>En route</option>
                                <option value="livrée" {{ old('status', $order->status) == 'livrée' ? 'selected' : '' }}>Livrée</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Si "Confirmée" est sélectionné, tous les champs client sont obligatoires et le stock sera décrémenté.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="required-field">Priorité</label>
                            <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="normale" {{ old('priority', $order->priority) == 'normale' ? 'selected' : '' }}>Normale</option>
                                <option value="urgente" {{ old('priority', $order->priority) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                <option value="vip" {{ old('priority', $order->priority) == 'vip' ? 'selected' : '' }}>VIP</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Ces notes seront ajoutées à l'historique de la commande.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Produits et panier -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Produits</h6>
                        <div class="add-product-icon" id="addNewProductButton" title="Créer un nouveau produit">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="product_search">Rechercher et ajouter des produits</label>
                            <select class="form-control select2-products" id="product_search">
                                <option value="">Rechercher un produit...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-name="{{ $product->name }}">
                                        {{ $product->name }} - {{ number_format($product->price, 3) }} DT
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="selectedProducts" class="mt-4">
                            @if($order->items->count() > 0)
                                @foreach($order->items as $index => $item)
                                <div class="product-card" id="product-{{ $item->product_id }}" data-product-id="{{ $item->product_id }}">
                                    <div class="remove-product" data-product-id="{{ $item->product_id }}">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>{{ $item->product->name }}</h6>
                                            <p class="mb-0 text-muted">Prix unitaire: {{ number_format($item->unit_price, 3) }} DT</p>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="quantity-{{ $item->product_id }}">Quantité</label>
                                                <input type="number" class="form-control product-quantity" id="quantity-{{ $item->product_id }}" 
                                                       min="1" value="{{ $item->quantity }}" data-product-id="{{ $item->product_id }}" 
                                                       data-price="{{ $item->unit_price }}">
                                            </div>
                                            <p class="mb-0">Total: <span class="product-total" id="total-{{ $item->product_id }}">{{ number_format($item->total_price, 3) }} DT</span></p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="products[{{ $index }}][id]" value="{{ $item->product_id }}">
                                    <input type="hidden" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}">
                                </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted" id="noProductsMessage">
                                    Aucun produit sélectionné
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Résumé du panier -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Résumé de la commande</h6>
                    </div>
                    <div class="card-body">
                        <div class="cart-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span id="subtotal">{{ number_format($order->total_price, 3) }} DT</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frais de livraison:</span>
                                <span id="shipping">{{ number_format($order->shipping_cost, 3) }} DT</span>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total:</span>
                                <span id="total">{{ number_format($order->total_price + $order->shipping_cost, 3) }} DT</span>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-save"></i> Mettre à jour la commande
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Actions pour enregistrer une tentative -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Enregistrer une tentative</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.recordAttempt', $order) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="attempt_notes">Notes de la tentative</label>
                                <textarea class="form-control" id="attempt_notes" name="notes" rows="2" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-phone-alt"></i> Enregistrer la tentative
                            </button>
                        </form>
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
                <button type="button" class="btn btn-primary" id="saveNewProduct">Ajouter à la commande</button>
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
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Chargement...</span>
                    </div>
                </div>
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
<script>
    $(document).ready(function() {
        // Initialiser Select2
        $('.select2').select2({
            placeholder: "Sélectionner une option",
            allowClear: true
        });
        
        $('.select2-products').select2({
            placeholder: "Rechercher un produit...",
            allowClear: true
        });
        
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
        
        function loadCities(regionId, selectedCityId = null) {
            const citySelect = $('#customer_city');
            citySelect.prop('disabled', true).empty().append('<option value="">Chargement...</option>');
            
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
                }
            });
        }
        
        // Gestion des produits sélectionnés
        let selectedProducts = [];
        let nextTempId = -1;
        
        // Initialiser les produits depuis le formulaire
        $('.product-card').each(function() {
            const productId = $(this).data('product-id');
            const quantityInput = $(`#quantity-${productId}`);
            const quantity = parseInt(quantityInput.val());
            const price = parseFloat(quantityInput.data('price'));
            
            selectedProducts.push({
                id: productId,
                name: $(this).find('h6').text(),
                price: price,
                quantity: quantity
            });
        });
        
        // Cacher le message "Aucun produit" s'il y a des produits
        if (selectedProducts.length > 0) {
            $('#noProductsMessage').hide();
        }
        
        // Ajouter un produit existant
        $('#product_search').on('change', function() {
            const productId = $(this).val();
            
            if (productId) {
                const option = $(this).find('option:selected');
                const product = {
                    id: productId,
                    name: option.data('name'),
                    price: option.data('price'),
                    quantity: 1
                };
                
                addProductToSelection(product);
                $(this).val('').trigger('change');
            }
        });
        
        // Ajouter un nouveau produit via modal
        $('#addNewProductButton').click(function() {
            $('#new_product_name').val('');
            $('#new_product_price').val('');
            $('#newProductModal').modal('show');
        });
        
        $('#saveNewProduct').click(function() {
            const name = $('#new_product_name').val();
            const price = $('#new_product_price').val();
            
            if (name && price) {
                const product = {
                    id: nextTempId--,
                    name: name,
                    price: parseFloat(price),
                    quantity: 1,
                    is_new: true
                };
                
                addProductToSelection(product);
                $('#newProductModal').modal('hide');
            } else {
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });
        
        // Fonction pour ajouter un produit à la sélection
        function addProductToSelection(product) {
            // Vérifier si le produit existe déjà
            const existingIndex = selectedProducts.findIndex(p => p.id === product.id);
            
            if (existingIndex !== -1) {
                // Si le produit existe, incrémenter la quantité
                selectedProducts[existingIndex].quantity++;
                updateProductCard(selectedProducts[existingIndex]);
            } else {
                // Sinon, ajouter le nouveau produit
                selectedProducts.push(product);
                
                // Créer la carte du produit
                const productHtml = `
                    <div class="product-card" id="product-${product.id}" data-product-id="${product.id}">
                        <div class="remove-product" data-product-id="${product.id}">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>${product.name}</h6>
                                <p class="mb-0 text-muted">Prix unitaire: ${formatPrice(product.price)} DT</p>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity-${product.id}">Quantité</label>
                                    <input type="number" class="form-control product-quantity" id="quantity-${product.id}" 
                                           min="1" value="${product.quantity}" data-product-id="${product.id}" data-price="${product.price}">
                                </div>
                                <p class="mb-0">Total: <span class="product-total" id="total-${product.id}">${formatPrice(product.price * product.quantity)} DT</span></p>
                            </div>
                        </div>
                        <input type="hidden" name="products[${selectedProducts.length - 1}][id]" value="${product.id}">
                        <input type="hidden" name="products[${selectedProducts.length - 1}][quantity]" value="${product.quantity}">
                        ${product.is_new ? `
                            <input type="hidden" name="products[${selectedProducts.length - 1}][name]" value="${product.name}">
                            <input type="hidden" name="products[${selectedProducts.length - 1}][price]" value="${product.price}">
                        ` : ''}
                    </div>
                `;
                
                $('#selectedProducts').append(productHtml);
                $('#noProductsMessage').hide();
            }
            
            updateCartSummary();
        }
        
        // Mettre à jour la carte d'un produit
        function updateProductCard(product) {
            const card = $(`#product-${product.id}`);
            card.find(`#quantity-${product.id}`).val(product.quantity);
            card.find(`#total-${product.id}`).text(formatPrice(product.price * product.quantity) + ' DT');
            card.find(`input[name$="[quantity]"]`).val(product.quantity);
            
            updateCartSummary();
        }
        
        // Événement de changement de quantité
        $(document).on('change', '.product-quantity', function() {
            const productId = $(this).data('product-id');
            const quantity = parseInt($(this).val());
            
            if (quantity < 1) {
                $(this).val(1);
                return;
            }
            
            const productIndex = selectedProducts.findIndex(p => p.id == productId);
            if (productIndex !== -1) {
                selectedProducts[productIndex].quantity = quantity;
                updateProductCard(selectedProducts[productIndex]);
            }
        });
        
        // Supprimer un produit
        $(document).on('click', '.remove-product', function() {
            const productId = $(this).data('product-id');
            const productIndex = selectedProducts.findIndex(p => p.id == productId);
            
            if (productIndex !== -1) {
                selectedProducts.splice(productIndex, 1);
                $(`#product-${productId}`).remove();
                
                // Réindexer les inputs pour maintenir la séquence dans le formulaire
                selectedProducts.forEach((product, index) => {
                    const card = $(`#product-${product.id}`);
                    card.find(`input[name^="products["]`).each(function() {
                        const name = $(this).attr('name').replace(/products\[\d+\]/, `products[${index}]`);
                        $(this).attr('name', name);
                    });
                });
                
                updateCartSummary();
                
                if (selectedProducts.length === 0) {
                    $('#noProductsMessage').show();
                }
            }
        });
        
        // Mettre à jour le résumé du panier
        function updateCartSummary() {
            let subtotal = 0;
            selectedProducts.forEach(product => {
                subtotal += product.price * product.quantity;
            });
            
            const shipping = parseFloat($('#shipping_cost').val()) || 0;
            const total = subtotal + shipping;
            
            $('#subtotal').text(formatPrice(subtotal) + ' DT');
            $('#shipping').text(formatPrice(shipping) + ' DT');
            $('#total').text(formatPrice(total) + ' DT');
        }
        
        // Mettre à jour le résumé quand les frais de livraison changent
        $('#shipping_cost').on('change input', function() {
            updateCartSummary();
        });
        
        // Formatter les prix
        function formatPrice(price) {
            return price.toFixed(3);
        }
        
        // Valider le formulaire avant soumission
        $('#orderForm').on('submit', function(e) {
            // Vérifier qu'il y a au moins un produit
            if (selectedProducts.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins un produit à la commande.');
                return false;
            }
            
            // Si le statut est "confirmée", vérifier que tous les champs client sont remplis
            if ($('#status').val() === 'confirmée') {
                if (!$('#customer_name').val() || !$('#customer_governorate').val() || 
                    !$('#customer_city').val() || !$('#customer_address').val()) {
                    e.preventDefault();
                    alert('Pour une commande confirmée, tous les champs client sont obligatoires.');
                    return false;
                }
            }
            
            return true;
        });
        
        // Gestion du modal d'historique
        $('#historyModal').on('show.bs.modal', function (event) {
            const modal = $(this);
            
            // Charger l'historique
            $.ajax({
                url: "{{ route('admin.orders.history', $order) }}",
                success: function(data) {
                    modal.find('.modal-body').html(data);
                },
                error: function() {
                    modal.find('.modal-body').html('<div class="alert alert-danger">Erreur lors du chargement de l\'historique</div>');
                }
            });
        });
    });
</script>
@endsection