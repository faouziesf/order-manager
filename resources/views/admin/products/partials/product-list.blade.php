<!-- Liste des produits mise à jour -->
@if($products->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </th>
                    <th width="70">Image</th>
                    <th>Produit</th>
                    <th width="120">Prix</th>
                    <th width="100">Stock</th>
                    <th width="80">Statut</th>
                    <th width="100">Date</th>
                    <th width="120" class="text-center">Actions</th>
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
                                 class="product-image"
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 2px solid #dbeafe;">
                        @else
                            <div class="product-image-placeholder"
                                 style="width: 50px; height: 50px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </td>
                    
                    <td>
                        <div class="product-name" style="font-weight: 600; color: #111827; margin-bottom: 0.25rem;">
                            {{ $product->name }}
                        </div>
                        @if($product->reference)
                            <div class="product-reference" style="font-size: 0.75rem; color: #1e40af; font-weight: 500;">
                                REF: {{ $product->reference }}
                            </div>
                        @endif
                        @if($product->needs_review)
                            <span class="badge" style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <i class="fas fa-eye"></i> À examiner
                            </span>
                        @endif
                        @if($product->description)
                            <small class="text-muted d-block mt-1" style="color: #6b7280;">
                                {{ Str::limit($product->description, 50) }}
                            </small>
                        @endif
                    </td>
                    
                    <td>
                        <span class="product-price" style="font-weight: 700; color: #1e3a8a; font-size: 1rem;">
                            {{ number_format($product->price, 3) }} DT
                        </span>
                    </td>
                    
                    <td>
                        @php 
                            $badge = $product->stock_badge;
                            $badgeClass = 'badge';
                            $badgeStyle = '';
                            
                            switch($badge['class']) {
                                case 'badge-success':
                                    $badgeStyle = 'background: #dcfce7; color: #166534;';
                                    break;
                                case 'badge-warning':
                                    $badgeStyle = 'background: #fef3c7; color: #92400e;';
                                    break;
                                case 'badge-danger':
                                    $badgeStyle = 'background: #fee2e2; color: #991b1b;';
                                    break;
                                case 'badge-info':
                                    $badgeStyle = 'background: #dbeafe; color: #1e3a8a;';
                                    break;
                                default:
                                    $badgeStyle = 'background: #e5e7eb; color: #374151;';
                            }
                        @endphp
                        <span class="{{ $badgeClass }}" 
                              style="{{ $badgeStyle }} padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <i class="{{ $badge['icon'] }}"></i>
                            {{ $badge['text'] }}
                        </span>
                    </td>
                    
                    <td>
                        @if($product->is_active)
                            <span class="badge" style="background: #dcfce7; color: #166534; padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <i class="fas fa-check"></i> Actif
                            </span>
                        @else
                            <span class="badge" style="background: #e5e7eb; color: #374151; padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <i class="fas fa-pause"></i> Inactif
                            </span>
                        @endif
                    </td>
                    
                    <td>
                        <small class="text-muted" style="color: #6b7280;">
                            {{ $product->created_at->format('d/m/Y') }}
                        </small>
                    </td>
                    
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" 
                                    class="btn btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#productModal{{ $product->id }}"
                                    style="border: 1px solid #1e40af; color: #1e40af; padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 6px; transition: all 0.3s ease;">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <a href="{{ route('admin.products.edit', $product) }}" 
                               class="btn btn-sm"
                               style="border: 1px solid #d97706; color: #d97706; padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 6px; transition: all 0.3s ease; text-decoration: none;">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            @if(!$product->isUsedInOrders())
                                <button type="button" 
                                        class="btn btn-sm" 
                                        onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')"
                                        style="border: 1px solid #dc2626; color: #dc2626; padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 6px; transition: all 0.3s ease;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <button type="button" 
                                        class="btn btn-sm" 
                                        disabled
                                        title="Produit utilisé dans des commandes"
                                        style="border: 1px solid #9ca3af; color: #9ca3af; padding: 0.375rem 0.75rem; font-size: 0.75rem; border-radius: 6px; cursor: not-allowed;">
                                    <i class="fas fa-lock"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state" style="text-align: center; padding: 4rem 2rem; color: #6b7280;">
        <i class="fas fa-search" style="font-size: 4rem; color: #dbeafe; margin-bottom: 1rem;"></i>
        <h4 style="color: #111827; margin-bottom: 0.5rem;">Aucun produit trouvé</h4>
        <p style="color: #6b7280; margin-bottom: 2rem;">Aucun produit ne correspond à vos critères de recherche</p>
        <a href="{{ route('admin.products.create') }}" 
           class="btn" 
           style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); border: none; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
            <i class="fas fa-plus me-2"></i>Ajouter un Produit
        </a>
    </div>
@endif

<style>
/* Styles pour les boutons du tableau */
.btn-group .btn:hover {
    transform: translateY(-1px);
}

.btn-group .btn[style*="border: 1px solid #1e40af"]:hover {
    background: #1e40af !important;
    color: white !important;
}

.btn-group .btn[style*="border: 1px solid #d97706"]:hover {
    background: #d97706 !important;
    color: white !important;
}

.btn-group .btn[style*="border: 1px solid #dc2626"]:hover {
    background: #dc2626 !important;
    color: white !important;
}

/* Styles pour les images */
.product-image:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

/* Animation pour les lignes du tableau */
.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background-color: #f9fafb;
    transform: translateX(2px);
}

/* Responsive */
@media (max-width: 768px) {
    .product-image, .product-image-placeholder {
        width: 40px !important;
        height: 40px !important;
    }
    
    .btn-group {
        flex-direction: column;
        width: auto;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        border-radius: 4px !important;
    }
}
</style>