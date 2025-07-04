@extends('admin.layouts.app')

@section('title', 'Préparation d\'Enlèvement')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Préparation d'Enlèvement</h1>
            <p class="text-muted">Créez un nouvel enlèvement pour Jax Delivery</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Voir les Enlèvements
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Commandes Disponibles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['available_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Enlèvements en Brouillon
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['draft_pickups'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($configurations->count() === 0)
        <!-- Aucune configuration -->
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5 class="text-gray-600">Aucune Configuration Jax Delivery</h5>
                <p class="text-muted">Vous devez d'abord configurer Jax Delivery avant de créer des enlèvements.</p>
                <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Configurer Jax Delivery
                </a>
            </div>
        </div>
    @else
        <!-- Formulaire de préparation -->
        <form id="preparationForm">
            @csrf
            <div class="row">
                <!-- Configuration et paramètres -->
                <div class="col-md-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-cog"></i> Configuration
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="delivery_configuration_id">Configuration Jax Delivery *</label>
                                <select class="form-control" id="delivery_configuration_id" name="delivery_configuration_id" required>
                                    <option value="">Sélectionner une configuration</option>
                                    @foreach($configurations as $config)
                                        <option value="{{ $config->id }}">
                                            {{ $config->display_name }}
                                            ({{ ucfirst($config->environment) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Choisissez la configuration Jax à utiliser
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="pickup_date">Date d'Enlèvement (optionnel)</label>
                                <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                       min="{{ date('Y-m-d') }}">
                                <small class="form-text text-muted">
                                    Laisser vide pour enlèvement immédiat
                                </small>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Jax Delivery</strong> utilise automatiquement l'adresse configurée dans votre compte Jax.
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter"></i> Filtres
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="search">Rechercher</label>
                                <input type="text" class="form-control" id="search" 
                                       placeholder="N° commande, nom, téléphone...">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_from">Du</label>
                                        <input type="date" class="form-control" id="date_from">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_to">Au</label>
                                        <input type="date" class="form-control" id="date_to">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="min_amount">Montant min</label>
                                        <input type="number" class="form-control" id="min_amount" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_amount">Montant max</label>
                                        <input type="number" class="form-control" id="max_amount" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-block" onclick="loadOrders()">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-boxes"></i> Commandes Disponibles
                            </h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                    Tout Sélectionner
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNone()">
                                    Tout Désélectionner
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="ordersContainer">
                                <div class="text-center py-4">
                                    <i class="fas fa-search fa-2x text-gray-300 mb-3"></i>
                                    <p class="text-muted">Utilisez les filtres pour charger les commandes disponibles</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <span id="selectedCount" class="text-muted">0 commande(s) sélectionnée(s)</span>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-success" id="createPickupBtn" disabled>
                                        <i class="fas fa-plus"></i> Créer l'Enlèvement
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

@endsection

@push('scripts')
<script>
let selectedOrders = new Set();

// Charger les commandes avec filtres
function loadOrders() {
    const formData = new FormData();
    formData.append('search', document.getElementById('search').value);
    formData.append('date_from', document.getElementById('date_from').value);
    formData.append('date_to', document.getElementById('date_to').value);
    formData.append('min_amount', document.getElementById('min_amount').value);
    formData.append('max_amount', document.getElementById('max_amount').value);

    document.getElementById('ordersContainer').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p class="text-muted">Chargement des commandes...</p>
        </div>
    `;

    fetch('{{ route("admin.delivery.preparation.orders") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.html) {
                document.getElementById('ordersContainer').innerHTML = data.html;
            } else {
                // Fallback si pas de HTML
                renderOrdersFallback(data.orders);
            }
            updateSelectedCount();
        } else {
            document.getElementById('ordersContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('ordersContainer').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement des commandes
            </div>
        `;
    });
}

// Fallback pour afficher les commandes
function renderOrdersFallback(orders) {
    if (!orders || !orders.data || orders.data.length === 0) {
        document.getElementById('ordersContainer').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-gray-300 mb-3"></i>
                <p class="text-muted">Aucune commande trouvée avec ces critères</p>
            </div>
        `;
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()"></th>';
    html += '<th>N° Commande</th><th>Client</th><th>Montant</th><th>Date</th></tr></thead><tbody>';

    orders.data.forEach(order => {
        html += `<tr>
            <td><input type="checkbox" class="order-checkbox" value="${order.id}" onchange="toggleOrder(${order.id})"></td>
            <td><strong>#${order.id}</strong></td>
            <td>${order.customer_name}<br><small class="text-muted">${order.customer_phone}</small></td>
            <td><strong>${order.total_price} TND</strong></td>
            <td>${new Date(order.created_at).toLocaleDateString('fr-FR')}</td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    document.getElementById('ordersContainer').innerHTML = html;
}

// Sélectionner toutes les commandes
function selectAll() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        selectedOrders.add(parseInt(checkbox.value));
    });
    updateSelectedCount();
}

// Désélectionner toutes les commandes
function selectNone() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        selectedOrders.delete(parseInt(checkbox.value));
    });
    updateSelectedCount();
}

// Basculer sélection d'une commande
function toggleOrder(orderId) {
    const checkbox = document.querySelector(`input[value="${orderId}"]`);
    if (checkbox.checked) {
        selectedOrders.add(orderId);
    } else {
        selectedOrders.delete(orderId);
    }
    updateSelectedCount();
}

// Basculer tout sélectionner
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox.checked) {
        selectAll();
    } else {
        selectNone();
    }
}

// Mettre à jour le compteur
function updateSelectedCount() {
    const count = selectedOrders.size;
    document.getElementById('selectedCount').textContent = `${count} commande(s) sélectionnée(s)`;
    document.getElementById('createPickupBtn').disabled = count === 0;
}

// Soumettre le formulaire
document.getElementById('preparationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const configId = document.getElementById('delivery_configuration_id').value;
    const pickupDate = document.getElementById('pickup_date').value;
    
    if (!configId) {
        toastr.error('Veuillez sélectionner une configuration Jax Delivery');
        return;
    }
    
    if (selectedOrders.size === 0) {
        toastr.error('Veuillez sélectionner au moins une commande');
        return;
    }
    
    const formData = new FormData();
    formData.append('delivery_configuration_id', configId);
    if (pickupDate) {
        formData.append('pickup_date', pickupDate);
    }
    
    selectedOrders.forEach(orderId => {
        formData.append('order_ids[]', orderId);
    });
    
    const btn = document.getElementById('createPickupBtn');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
    btn.disabled = true;
    
    fetch('{{ route("admin.delivery.preparation.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = '{{ route("admin.delivery.pickups") }}';
            }
        } else {
            toastr.error(data.message);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Erreur lors de la création de l\'enlèvement');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
});

// Charger les commandes au démarrage
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
});
</script>
@endpush