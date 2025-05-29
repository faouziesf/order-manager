@extends('layouts.admin')

@section('title', 'Créer une Commande')
@section('page-title', 'Créer une Nouvelle Commande')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Nouvelle Commande
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.orders.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations Client</h6>
                        
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                                   id="customer_phone" name="customer_phone" 
                                   value="{{ old('customer_phone') }}" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nom du client</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                   id="customer_name" name="customer_name" 
                                   value="{{ old('customer_name') }}">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="customer_phone_2" class="form-label">Téléphone 2</label>
                            <input type="text" class="form-control" id="customer_phone_2" name="customer_phone_2" 
                                   value="{{ old('customer_phone_2') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="customer_address" name="customer_address" rows="3">{{ old('customer_address') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Statut et Priorité</h6>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="nouvelle" {{ old('status', 'nouvelle') == 'nouvelle' ? 'selected' : '' }}>Nouvelle</option>
                                <option value="confirmée" {{ old('status') == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priorité <span class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="normale" {{ old('priority', 'normale') == 'normale' ? 'selected' : '' }}>Normale</option>
                                <option value="urgente" {{ old('priority') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                <option value="vip" {{ old('priority') == 'vip' ? 'selected' : '' }}>VIP</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_cost" class="form-label">Frais de livraison</label>
                            <input type="number" step="0.001" class="form-control" id="shipping_cost" name="shipping_cost" 
                                   value="{{ old('shipping_cost', 0) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Produits -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6>Produits de la commande <span class="text-danger">*</span></h6>
                        <div id="products-container">
                            <div class="product-row mb-3 p-3 border rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label>Produit</label>
                                        <select name="products[0][id]" class="form-select" required>
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
                                        <input type="number" name="products[0][quantity]" 
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
                        </div>
                        
                        <button type="button" class="btn btn-success btn-sm" id="add-product">
                            <i class="fas fa-plus me-2"></i>Ajouter un produit
                        </button>
                        
                        @error('products')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Notes additionnelles...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Créer la commande
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
    let productIndex = 1;
    
    // Ajouter un produit
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