@extends('layouts.admin')

@section('title', 'Traitement des commandes - File Standard')

@section('css')
<style>
    .process-card {
        margin-bottom: 20px;
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .process-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 15px;
        font-weight: bold;
    }
    
    .customer-info {
        padding: 15px;
        background-color: #f8f9fc;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    
    .customer-info-item {
        margin-bottom: 8px;
    }
    
    .customer-info-item i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
        color: #4e73df;
    }
    
    .action-btn {
        margin-right: 5px;
        margin-bottom: 10px;
        width: 120px;
    }
    
    .product-item {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .product-item:last-child {
        border-bottom: none;
    }
    
    .product-name {
        flex: 1;
    }
    
    .product-quantity {
        font-weight: bold;
        margin-right: 10px;
    }
    
    .product-price {
        font-weight: bold;
        color: #4e73df;
    }
    
    .totals {
        margin-top: 15px;
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .total-label {
        font-weight: bold;
    }
    
    .total-value {
        font-weight: bold;
        color: #4e73df;
    }
    
    .meta-info {
        display: flex;
        justify-content: space-between;
        padding: 8px 15px;
        background-color: #eaecf4;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 0.875rem;
    }
    
    .queue-tabs {
        margin-bottom: 20px;
    }
    
    .queue-tab {
        padding: 10px 20px;
        border-radius: 5px;
        margin-right: 5px;
        font-weight: 500;
    }
    
    .queue-tab.active {
        background-color: #4e73df;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        Traitement des commandes
        <span class="badge bg-primary">File Standard</span>
    </h1>
    
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
        </a>
    </div>
</div>

<!-- Onglets de navigation entre les files -->
<div class="queue-tabs">
    <a href="{{ route('admin.process.standard') }}" class="queue-tab {{ request()->routeIs('admin.process.standard') ? 'active' : 'btn-light' }}">
        <i class="fas fa-list mr-1"></i> File Standard
    </a>
    <a href="{{ route('admin.process.dated') }}" class="queue-tab {{ request()->routeIs('admin.process.dated') ? 'active' : 'btn-light' }}">
        <i class="fas fa-calendar-alt mr-1"></i> File Datée
    </a>
    <a href="{{ route('admin.process.old') }}" class="queue-tab {{ request()->routeIs('admin.process.old') ? 'active' : 'btn-light' }}">
        <i class="fas fa-history mr-1"></i> File Ancienne
    </a>
</div>

@if(!$next)
    <div class="alert alert-info">
        <i class="fas fa-mug-hot mr-2"></i> Il n'y a pas de commandes à traiter dans cette file actuellement. 
        Prenez une pause café ou essayez une autre file.
    </div>
@else
<!-- Meta informations -->
<div class="meta-info">
    <div>
        <i class="fas fa-clock mr-1"></i> Créée le {{ $next->created_at->format('d/m/Y H:i') }}
    </div>
    <div>
        <i class="fas fa-phone mr-1"></i> Tentatives: <b>{{ $next->attempts_count }}</b> 
        <span class="badge bg-primary ml-1">{{ $next->daily_attempts_count }} aujourd'hui</span>
    </div>
</div>

<div class="row">
    <!-- Colonne d'informations client et actions -->
    <div class="col-lg-6">
        <div class="card process-card">
            <div class="process-header">
                <i class="fas fa-user mr-1"></i> Informations client 
                <span class="badge bg-primary ml-2">Commande #{{ $next->id }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.process.action', $next->id) }}" method="POST" id="orderActionForm">
                    @csrf
                    <input type="hidden" name="action" id="orderAction" value="">
                    <input type="hidden" name="queue" value="standard">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_name">Nom du client <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $next->customer_name }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_phone">Téléphone principal</label>
                                <input type="text" class="form-control" id="customer_phone" value="{{ $next->customer_phone }}" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_phone_2">Téléphone secondaire</label>
                                <input type="text" class="form-control" id="customer_phone_2" name="customer_phone_2" value="{{ $next->customer_phone_2 }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmed_price">Prix confirmé <span class="text-danger confirm-required">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="confirmed_price" name="confirmed_price" 
                                        step="0.001" min="0" value="{{ $next->total_price + $next->shipping_cost }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">TND</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_governorate">Gouvernorat <span class="text-danger confirm-required">*</span></label>
                                <select class="form-control" id="customer_governorate" name="customer_governorate">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ $next->customer_governorate == $region->id ? 'selected' : '' }}>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_city">Ville <span class="text-danger confirm-required">*</span></label>
                                <select class="form-control" id="customer_city" name="customer_city">
                                    <option value="">-- Sélectionner d'abord un gouvernorat --</option>
                                    @if($next->city)
                                        <option value="{{ $next->city->id }}" selected>{{ $next->city->name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="customer_address">Adresse <span class="text-danger confirm-required">*</span></label>
                        <textarea class="form-control" id="customer_address" name="customer_address" rows="2">{{ $next->customer_address }}</textarea>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label for="notes">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" required></textarea>
                    </div>
                    
                    <!-- Champs conditionnels -->
                    <div id="date-fields" style="display: none;" class="mb-3">
                        <div class="form-group">
                            <label for="scheduled_date">Date programmée <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-success action-btn" data-action="confirm">
                            <i class="fas fa-check mr-1"></i> Confirmer
                        </button>
                        <button type="button" class="btn btn-danger action-btn" data-action="cancel">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-warning action-btn" data-action="date">
                            <i class="fas fa-calendar-alt mr-1"></i> Dater
                        </button>
                        <button type="button" class="btn btn-info action-btn" data-action="callback">
                            <i class="fas fa-phone mr-1"></i> Rappeler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Colonne des produits -->
    <div class="col-lg-6">
        <div class="card process-card">
            <div class="process-header">
                <i class="fas fa-shopping-cart mr-1"></i> Panier
            </div>
            <div class="card-body">
                <div class="products-list">
                    @foreach($next->items as $item)
                        <div class="product-item">
                            <div class="product-quantity">x{{ $item->quantity }}</div>
                            <div class="product-name">{{ $item->product->name }}</div>
                            <div class="product-price">{{ number_format($item->unit_price, 3) }} TND</div>
                        </div>
                    @endforeach
                </div>
                
                <div class="totals">
                    <div class="total-row">
                        <div class="total-label">Total produits:</div>
                        <div class="total-value">{{ number_format($next->total_price, 3) }} TND</div>
                    </div>
                    <div class="total-row">
                        <div class="total-label">Frais de livraison:</div>
                        <div class="total-value">{{ number_format($next->shipping_cost, 3) }} TND</div>
                    </div>
                    <div class="total-row">
                        <div class="total-label">Total:</div>
                        <div class="total-value">{{ number_format($next->total_price + $next->shipping_cost, 3) }} TND</div>
                    </div>
                </div>
                
                @if($next->notes)
                    <div class="mt-3 pt-3 border-top">
                        <h6><i class="fas fa-sticky-note mr-1"></i> Notes existantes:</h6>
                        <div class="p-2 bg-light rounded">
                            {{ $next->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des actions
    const actionButtons = document.querySelectorAll('.action-btn');
    const orderActionForm = document.getElementById('orderActionForm');
    const orderActionInput = document.getElementById('orderAction');
    const dateFields = document.getElementById('date-fields');
    const confirmRequiredFields = document.querySelectorAll('.confirm-required');
    
    if (actionButtons.length) {
        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                orderActionInput.value = action;
                
                // Masquer/afficher les champs selon l'action
                if (action === 'date') {
                    dateFields.style.display = 'block';
                    document.getElementById('scheduled_date').required = true;
                    
                    // Rendre les champs d'adresse optionnels
                    confirmRequiredFields.forEach(field => {
                        field.classList.add('d-none');
                    });
                } else if (action === 'confirm') {
                    dateFields.style.display = 'none';
                    document.getElementById('scheduled_date').required = false;
                    
                    // Rendre les champs d'adresse obligatoires
                    confirmRequiredFields.forEach(field => {
                        field.classList.remove('d-none');
                    });
                } else {
                    dateFields.style.display = 'none';
                    document.getElementById('scheduled_date').required = false;
                    
                    // Rendre les champs d'adresse optionnels
                    confirmRequiredFields.forEach(field => {
                        field.classList.add('d-none');
                    });
                }
                
                // Soumettre le formulaire avec une confirmation pour certaines actions
                if (action === 'cancel') {
                    if (confirm('Êtes-vous sûr de vouloir annuler cette commande?')) {
                        orderActionForm.submit();
                    }
                } else {
                    orderActionForm.submit();
                }
            });
        });
    }
    
    // Gestion des gouvernorats et villes
    const governorateSelect = document.getElementById('customer_governorate');
    const citySelect = document.getElementById('customer_city');
    
    if (governorateSelect && citySelect) {
        governorateSelect.addEventListener('change', function() {
            const regionId = this.value;
            
            // Réinitialiser le select des villes
            citySelect.innerHTML = '<option value="">-- Sélectionner une ville --</option>';
            
            if (regionId) {
                // Charger les villes via AJAX
                fetch(`/admin/get-cities?region_id=${regionId}`)
                    .then(response => response.json())
                    .then(cities => {
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erreur lors du chargement des villes:', error));
            }
        });
    }
});
</script>
@endsection