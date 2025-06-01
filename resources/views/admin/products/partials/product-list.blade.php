<!-- Vue Table -->
<div id="tableView">
    @if($products->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th width="30">
                            <input type="checkbox" class="form-check-input select-all-checkbox" id="selectAll">
                        </th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" 
                                   value="{{ $product->id }}" name="selected_products[]">
                        </td>
                        
                        <td>
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            @else
                                <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        
                        <td>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                @if($product->needs_review)
                                    <span class="badge bg-warning ms-2">
                                        <i class="fas fa-eye me-1"></i>À examiner
                                    </span>
                                @endif
                            </div>
                            @if($product->description)
                                <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                            @endif
                        </td>
                        
                        <td>
                            <span class="price-display">{{ number_format($product->price, 3) }} DT</span>
                        </td>
                        
                        <td>
                            @if($product->stock <= 0)
                                <span class="badge stock-low">
                                    <i class="fas fa-times me-1"></i>Rupture
                                </span>
                            @elseif($product->stock <= 10)
                                <span class="badge stock-medium">
                                    <i class="fas fa-exclamation me-1"></i>{{ $product->stock }} unités
                                </span>
                            @else
                                <span class="badge stock-high">
                                    <i class="fas fa-check me-1"></i>{{ $product->stock }} unités
                                </span>
                            @endif
                        </td>
                        
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Actif
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-pause me-1"></i>Inactif
                                </span>
                            @endif
                        </td>
                        
                        <td>
                            <small>{{ $product->created_at->format('d/m/Y') }}</small>
                        </td>
                        
                        <td class="text-center table-actions">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#productModal{{ $product->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Aucun produit trouvé</h4>
            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
        </div>
    @endif
</div>