@extends('layouts.admin')

@section('title', 'Modifier Commande')
@section('page-title', 'Modifier Commande #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>
                Modification de la commande #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations Client</h6>
                        
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_phone" name="customer_phone" 
                                   value="{{ old('customer_phone', $order->customer_phone) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nom du client</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                   value="{{ old('customer_name', $order->customer_name) }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="customer_phone_2" class="form-label">Téléphone 2</label>
                            <input type="text" class="form-control" id="customer_phone_2" name="customer_phone_2" 
                                   value="{{ old('customer_phone_2', $order->customer_phone_2) }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="customer_address" name="customer_address" rows="3">{{ old('customer_address', $order->customer_address) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Statut et Priorité</h6>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="nouvelle" {{ old('status', $order->status) == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                                <option value="confirmée" {{ old('status', $order->status) == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                                <option value="annulée" {{ old('status', $order->status) == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                <option value="datée" {{ old('status', $order->status) == 'datée' ? 'selected' : '' }}>Datée</option>
                                <option value="en_route" {{ old('status', $order->status) == 'en_route' ? 'selected' : '' }}>En Route</option>
                                <option value="livrée" {{ old('status', $order->status) == 'livrée' ? 'selected' : '' }}>Livrée</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priorité <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="normale" {{ old('priority', $order->priority) == 'normale' ? 'selected' : '' }}>Normale</option>
                                <option value="urgente" {{ old('priority', $order->priority) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                <option value="vip" {{ old('priority', $order->priority) == 'vip' ? 'selected' : '' }}>VIP</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="scheduled_date_group" style="display: none;">
                            <label for="scheduled_date" class="form-label">Date de livraison</label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" 
                                   value="{{ old('scheduled_date', $order->scheduled_date ? $order->scheduled_date->format('Y-m-d') : '') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmed_price" class="form-label">Prix confirmé</label>
                            <input type="number" step="0.001" class="form-control" id="confirmed_price" name="confirmed_price" 
                                   value="{{ old('confirmed_price', $order->confirmed_price) }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_cost" class="form-label">Frais de livraison</label>
                            <input type="number" step="0.001" class="form-control" id="shipping_cost" name="shipping_cost" 
                                   value="{{ old('shipping_cost', $order->shipping_cost) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Produits -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Produits de la commande</h6>
                        <div id="products-container">
                            @foreach($order->items as $index => $item)
                                <div class="product-row mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <label>Produit</label>
                                            <select name="products[{{ $index }}][id]" class="form-select">
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                            {{ $item->product_id == $product->id ? 'selected' : '' }}
                                                            data-price="{{ $product->price }}">
                                                        {{ $product->name }} - {{ $product->price }} TND
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Quantité</label>
                                            <input type="number" name="products[{{ $index }}][quantity]" 
                                                   class="form-control" min="1" 
                                                   value="{{ $item->quantity }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Prix unitaire</label>
                                            <input type="number" step="0.001" 
                                                   class="form-control product-price" 
                                                   value="{{ $item->unit_price }}" readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm d-block remove-product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="button" class="btn btn-success btn-sm" id="add-product">
                            <i class="fas fa-plus me-2"></i>Ajouter un produit
                        </button>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes de modification</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Décrivez les modifications apportées..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Afficher/masquer le champ date selon le statut
    $('#status').on('change', function() {
        if ($(this).val() === 'datée') {
            $('#scheduled_date_group').show();
            $('#scheduled_date').attr('required', true);
        } else {
            $('#scheduled_date_group').hide();
            $('#scheduled_date').attr('required', false);
        }
    });

    // Déclencher l'événement au chargement
    $('#status').trigger('change');
    
    // Gestion des produits
    let productIndex = {{ $order->items->count() }};
    
    $('#add-product').on('click', function() {
        const newProduct = `
            <div class="product-row mb-3 p-3 border rounded">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label>Produit</label>
                        <select name="products[${productIndex}][id]" class="form-select" required>
                            <option value="">Sélectionner un produit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                    {{ $product->name }} - {{ $product->price }} TND
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Quantité</label>
                        <input type="number" name="products[${productIndex}][quantity]" 
                               class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label>Prix unitaire</label>
                        <input type="number" step="0.001" 
                               class="form-control product-price" readonly>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm d-block remove-product">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#products-container').append(newProduct);
        productIndex++;
    });
    
    // Supprimer un produit
    $(document).on('click', '.remove-product', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('.product-row').remove();
        } else {
            alert('Une commande doit contenir au moins un produit');
        }
    });
    
    // Mettre à jour le prix quand on change de produit
    $(document).on('change', '.product-row select', function() {
        const price = $(this).find('option:selected').data('price');
        $(this).closest('.product-row').find('.product-price').val(price || 0);
    });
});
</script>
@endsection