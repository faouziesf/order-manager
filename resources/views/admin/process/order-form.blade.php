
@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Traitement commande #{{ $order->id }}
            <span class="badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            <span class="badge priority-{{ $order->priority }}">{{ ucfirst($order->priority) }}</span>
            <span class="badge bg-info">File {{ ucfirst($queueType) }}</span>
        </h1>
    </div>
    
    <!-- Action du formulaire -->
    <form id="orderForm" action="{{ route('admin.process.action', $order) }}" method="POST">
        @csrf
        <input type="hidden" name="queue" value="{{ $queueType }}">
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


@endsection
