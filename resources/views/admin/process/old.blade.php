@extends('layouts.admin')

@section('title', 'Traitement des commandes - File Standard')

@section('css')
<style>
    .process-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .order-info-card {
        margin-bottom: 20px;
    }
    
    .order-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .order-meta-item {
        display: flex;
        align-items: center;
    }
    
    .order-meta-icon {
        margin-right: 8px;
        font-size: 0.9rem;
        color: #4e73df;
    }
    
    .order-meta-text {
        font-size: 0.85rem;
    }
    
    .order-meta-value {
        font-weight: 600;
    }
    
    .action-card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        margin-bottom: 20px;
    }
    
    .action-card-header {
        background-color: #f8f9fc;
        padding: 12px 15px;
        border-bottom: 1px solid #e3e6f0;
        font-weight: 600;
    }
    
    .action-card-body {
        padding: 15px;
    }
    
    .action-buttons {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .action-btn {
        flex: 1;
        margin: 0 5px;
        font-weight: 600;
        padding: 12px;
    }
    
    .badge-attempts {
        background-color: #4e73df;
    }
    
    .product-list {
        margin-bottom: 20px;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .product-quantity {
        font-weight: 600;
        margin-right: 10px;
        font-size: 0.95rem;
    }
    
    .product-name {
        flex: 1;
    }
    
    .product-price {
        font-weight: 600;
        margin-left: 10px;
    }
    
    .action-form {
        display: none;
    }
    
    .action-form.active {
        display: block;
    }
    
    .queue-nav {
        margin-bottom: 20px;
    }
    
    .queue-tab {
        padding: 10px 20px;
        margin-right: 5px;
        border-radius: 5px 5px 0 0;
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

<div class="queue-nav">
    <div class="d-flex">
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
</div>

<div class="row">
    <!-- Colonne d'informations sur la commande -->
    <div class="col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Commande #{{ $next->id }}
                </h6>
            </div>
            <div class="card-body">
                <div class="order-meta">
                    <div class="order-meta-item">
                        <div class="order-meta-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="order-meta-text">
                            Créée le <span class="order-meta-value">{{ $next->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="order-meta-item">
                        <div class="order-meta-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="order-meta-text">
                            Tentatives: <span class="order-meta-value">{{ $next->attempts_count }}</span>
                            <span class="badge badge-attempts">{{ $next->daily_attempts_count }} aujourd'hui</span>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="font-weight-bold">Informations client:</h6>
                    <div class="mb-2">
                        <i class="fas fa-user text-primary mr-2"></i>
                        {{ $next->customer_name ?: 'Nom non renseigné' }}
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-phone text-primary mr-2"></i>
                        {{ $next->customer_phone }}
                        @if($next->customer_phone_2)
                            <br>
                            <i class="fas fa-phone text-muted mr-2"></i>
                            {{ $next->customer_phone_2 }}
                        @endif
                    </div>
                    <div class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                        @if($next->region)
                            {{ $next->region->name }}
                        @endif
                        
                        @if($next->city)
                            - {{ $next->city->name }}
                        @endif
                        
                        @if($next->customer_address)
                            <br>
                            <span class="ml-4">{{ $next->customer_address }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="font-weight-bold">Produits commandés:</h6>
                    <div class="product-list">
                        @foreach($next->items as $item)
                            <div class="product-item">
                                <div class="product-quantity">
                                    x{{ $item->quantity }}
                                </div>
                                <div class="product-name">
                                    {{ $item->product->name }}
                                </div>
                                <div class="product-price">
                                    {{ number_format($item->unit_price, 3) }} TND
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-between font-weight-bold mt-3">
                        <div>Total produits:</div>
                        <div>{{ number_format($next->total_price, 3) }} TND</div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-2">
                        <div>Frais de livraison:</div>
                        <div>{{ number_format($next->shipping_cost, 3) }} TND</div>
                    </div>
                    
                    <div class="d-flex justify-content-between font-weight-bold mt-3 pt-2 border-top">
                        <div>Total commande:</div>
                        <div>{{ number_format($next->total_price + $next->shipping_cost, 3) }} TND</div>
                    </div>
                </div>
                
                @if($next->notes)
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="font-weight-bold">Notes:</h6>
                        <div class="p-2 bg-light rounded">
                            {{ $next->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Colonne d'actions -->
    <div class="col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Actions disponibles
                </h6>
            </div>
            <div class="card-body">
                <div class="action-buttons mb-4">
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
                
                <!-- Formulaire de confirmation -->
                <div id="confirm-form" class="action-form">
                    <form action="{{ route('admin.process.action', $next->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="confirm">
                        <input type="hidden" name="queue" value="standard">
                        
                        <div class="action-card">
                            <div class="action-card-header">
                                <i class="fas fa-check mr-1"></i> Confirmer la commande
                            </div>
                            <div class="action-card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_name">Nom du client <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $next->customer_name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_phone_2">Téléphone secondaire</label>
                                            <input type="text" class="form-control" id="customer_phone_2" name="customer_phone_2" value="{{ $next->customer_phone_2 }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer_governorate">Gouvernorat <span class="text-danger">*</span></label>
                                            <select class="form-control" id="customer_governorate" name="customer_governorate" required>
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
                                            <label for="customer_city">Ville <span class="text-danger">*</span></label>
                                            <select class="form-control" id="customer_city" name="customer_city" required>
                                                <option value="">-- Sélectionner d'abord un gouvernorat --</option>
                                                @if($next->city)
                                                    <option value="{{ $next->city->id }}" selected>{{ $next->city->name }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="customer_address">Adresse <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="customer_address" name="customer_address" rows="2" required>{{ $next->customer_address }}</textarea>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="confirmed_price">Prix confirmé <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="confirmed_price" name="confirmed_price" 
                                            step="0.001" min="0" value="{{ $next->total_price + $next->shipping_cost }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">TND</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_notes">Note <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="confirm_notes" name="notes" rows="3" required></textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check mr-1"></i> Confirmer la commande
                                    </button>
                                    <button type="button" class="btn btn-secondary cancel-action">
                                        <i class="fas fa-times mr-1"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Formulaire d'annulation -->
                <div id="cancel-form" class="action-form">
                    <form action="{{ route('admin.process.action', $next->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="queue" value="standard">
                        
                        <div class="action-card">
                            <div class="action-card-header">
                                <i class="fas fa-times mr-1"></i> Annuler la commande
                            </div>
                            <div class="action-card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Veuillez indiquer la raison de l'annulation. Cette action est irréversible.
                                </div>
                                
                                <div class="form-group">
                                    <label for="cancel_notes">Raison de l'annulation <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="cancel_notes" name="notes" rows="3" required></textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times mr-1"></i> Annuler la commande
                                    </button>
                                    <button type="button" class="btn btn-secondary cancel-action">
                                        <i class="fas fa-arrow-left mr-1"></i> Retour
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Formulaire de datation -->
                <div id="date-form" class="action-form">
                    <form action="{{ route('admin.process.action', $next->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="date">
                        <input type="hidden" name="queue" value="standard">
                        
                        <div class="action-card">
                            <div class="action-card-header">
                                <i class="fas fa-calendar-alt mr-1"></i> Programmer la commande
                            </div>
                            <div class="action-card-body">
                                <div class="form-group mb-3">
                                    <label for="scheduled_date">Date de rappel <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="date_notes">Note <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="date_notes" name="notes" rows="3" required></textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-calendar-check mr-1"></i> Programmer
                                    </button>
                                    <button type="button" class="btn btn-secondary cancel-action">
                                        <i class="fas fa-arrow-left mr-1"></i> Retour
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Formulaire de rappel -->
                <div id="callback-form" class="action-form">
                    <form action="{{ route('admin.process.action', $next->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="callback">
                        <input type="hidden" name="queue" value="standard">
                        
                        <div class="action-card">
                            <div class="action-card-header">
                                <i class="fas fa-phone mr-1"></i> Ne répond pas / À rappeler
                            </div>
                            <div class="action-card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Cette action incrémentera le compteur de tentatives et retirera temporairement la commande de la file.
                                </div>
                                
                                <div class="form-group">
                                    <label for="callback_notes">Note <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="callback_notes" name="notes" rows="3" required></textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-phone mr-1"></i> Enregistrer la tentative
                                    </button>
                                    <button type="button" class="btn btn-secondary cancel-action">
                                        <i class="fas fa-arrow-left mr-1"></i> Retour
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons d'action
    const actionButtons = document.querySelectorAll('.action-btn');
    const actionForms = document.querySelectorAll('.action-form');
    const cancelButtons = document.querySelectorAll('.cancel-action');
    
    // Fonction pour afficher un formulaire d'action
    function showActionForm(actionType) {
        // Cacher tous les formulaires
        actionForms.forEach(form => {
            form.classList.remove('active');
        });
        
        // Afficher le formulaire correspondant
        const formToShow = document.getElementById(actionType + '-form');
        if (formToShow) {
            formToShow.classList.add('active');
        }
    }
    
    // Écouter les clics sur les boutons d'action
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            showActionForm(action);
        });
    });
    
    // Écouter les clics sur les boutons d'annulation
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Cacher tous les formulaires
            actionForms.forEach(form => {
                form.classList.remove('active');
            });
        });
    });
    
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