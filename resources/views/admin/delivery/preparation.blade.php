@extends('layouts.admin')

@section('title', 'Préparation d\'Enlèvement')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Préparation d'Enlèvement</h1>
            <p class="text-muted">Créez un nouvel enlèvement Jax Delivery</p>
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
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Commandes Disponibles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['available_orders'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['draft_pickups'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Configuration et Filtres -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog"></i> Configuration de l'Enlèvement
                    </h6>
                </div>
                <div class="card-body">
                    <form id="pickupForm">
                        @csrf
                        
                        <!-- Sélection de la configuration -->
                        <div class="form-group">
                            <label for="delivery_configuration_id">Configuration Jax Delivery *</label>
                            <select class="form-control" id="delivery_configuration_id" name="delivery_configuration_id" required>
                                <option value="">Sélectionnez une configuration</option>
                                @foreach($configurations as $config)
                                    <option value="{{ $config->id }}">
                                        {{ $config->display_name }}
                                        ({{ ucfirst($config->environment) }})
                                    </option>
                                @endforeach
                            </select>
                            @if($configurations->isEmpty())
                                <small class="form-text text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Aucune configuration active trouvée.
                                    <a href="{{ route('admin.delivery.configuration') }}">Créer une configuration</a>
                                </small>
                            @endif
                        </div>

                        <!-- Date d'enlèvement -->
                        <div class="form-group">
                            <label for="pickup_date">Date d'enlèvement (optionnel)</label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                   min="{{ date('Y-m-d') }}">
                            <small class="form-text text-muted">
                                Si non spécifiée, l'enlèvement sera programmé automatiquement
                            </small>
                        </div>

                        <!-- Filtres pour les commandes -->
                        <div class="alert alert-info">
                            <strong><i class="fas fa-filter"></i> Filtres des Commandes</strong>
                        </div>

                        <div class="form-group">
                            <label for="date_from">Date de commande (du)</label>
                            <input type="date" class="form-control filter-input" id="date_from" name="date_from">
                        </div>

                        <div class="form-group">
                            <label for="date_to">Date de commande (au)</label>
                            <input type="date" class="form-control filter-input" id="date_to" name="date_to">
                        </div>

                        <div class="form-group">
                            <label for="min_amount">Montant minimum (TND)</label>
                            <input type="number" step="0.001" class="form-control filter-input" 
                                   id="min_amount" name="min_amount" placeholder="0.000">
                        </div>

                        <div class="form-group">
                            <label for="max_amount">Montant maximum (TND)</label>
                            <input type="number" step="0.001" class="form-control filter-input" 
                                   id="max_amount" name="max_amount" placeholder="1000.000">
                        </div>

                        <div class="form-group">
                            <label for="search">Recherche</label>
                            <input type="text" class="form-control filter-input" id="search" name="search" 
                                   placeholder="Nom, téléphone, adresse...">
                        </div>

                        <button type="button" class="btn btn-info btn-block" id="loadOrdersBtn">
                            <i class="fas fa-search"></i> Charger les Commandes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des commandes -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shopping-cart"></i> Commandes Disponibles
                    </h6>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                            <i class="fas fa-check-square"></i> Tout Sélectionner
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                            <i class="fas fa-square"></i> Tout Désélectionner
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="ordersContainer">
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted">
                                Configurez votre enlèvement et cliquez sur "Charger les Commandes" 
                                pour voir les commandes disponibles.
                            </p>
                        </div>
                    </div>

                    <!-- Bouton de création -->
                    <div class="text-center mt-4" id="createPickupSection" style="display: none;">
                        <hr>
                        <div class="alert alert-success">
                            <strong id="selectedCount">0</strong> commande(s) sélectionnée(s)
                        </div>
                        <button type="button" class="btn btn-success btn-lg" id="createPickupBtn">
                            <i class="fas fa-plus"></i> Créer l'Enlèvement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let selectedOrders = [];

$(document).ready(function() {
    // Charger les commandes
    $('#loadOrdersBtn').click(function() {
        loadOrders();
    });

    // Filtres en temps réel
    $('.filter-input').on('input change', function() {
        if ($('#ordersContainer .table').length > 0) {
            loadOrders();
        }
    });

    // Sélection/désélection
    $('#selectAllBtn').click(function() {
        $('.order-checkbox').prop('checked', true).trigger('change');
    });

    $('#deselectAllBtn').click(function() {
        $('.order-checkbox').prop('checked', false).trigger('change');
    });

    // Créer l'enlèvement
    $('#createPickupBtn').click(function() {
        createPickup();
    });
});

function loadOrders() {
    const btn = $('#loadOrdersBtn');
    const originalHtml = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Chargement...').prop('disabled', true);
    
    const formData = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        min_amount: $('#min_amount').val(),
        max_amount: $('#max_amount').val(),
        search: $('#search').val()
    };
    
    $.get('{{ route("admin.delivery.preparation.orders") }}', formData)
        .done(function(response) {
            if (response.success) {
                if (response.html) {
                    $('#ordersContainer').html(response.html);
                } else if (response.orders && response.orders.data) {
                    renderOrdersTable(response.orders);
                }
                
                // Réinitialiser les sélections
                selectedOrders = [];
                updateCreateButton();
                
                // Attacher les events
                attachOrderEvents();
                
                toastr.success(`${response.total || 0} commandes chargées`);
            } else {
                toastr.error(response.message || 'Erreur lors du chargement');
            }
        })
        .fail(function() {
            toastr.error('Erreur lors du chargement des commandes');
            $('#ordersContainer').html('<div class="alert alert-danger">Erreur lors du chargement</div>');
        })
        .always(function() {
            btn.html(originalHtml).prop('disabled', false);
        });
}

function renderOrdersTable(orders) {
    if (!orders.data || orders.data.length === 0) {
        $('#ordersContainer').html(`
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-gray-300 mb-3"></i>
                <p class="text-muted">Aucune commande trouvée avec ces critères</p>
            </div>
        `);
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAllCheckbox">
                        </th>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Montant</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    orders.data.forEach(function(order) {
        html += `
            <tr>
                <td>
                    <input type="checkbox" class="order-checkbox" value="${order.id}">
                </td>
                <td>
                    <strong>#${order.id}</strong>
                </td>
                <td>
                    <div>
                        <strong>${order.customer_name || 'N/A'}</strong>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-phone"></i> ${order.customer_phone || 'N/A'}
                        </small>
                    </div>
                </td>
                <td>
                    <small>${(order.customer_address || '').substring(0, 50)}${(order.customer_address || '').length > 50 ? '...' : ''}</small>
                    <br>
                    <small class="text-muted">${order.customer_governorate || ''}</small>
                </td>
                <td>
                    <strong class="text-success">${parseFloat(order.total_price || 0).toFixed(3)} TND</strong>
                </td>
                <td>
                    <small>${new Date(order.created_at).toLocaleDateString('fr-FR')}</small>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    $('#ordersContainer').html(html);
}

function attachOrderEvents() {
    // Checkbox individuel
    $(document).off('change', '.order-checkbox').on('change', '.order-checkbox', function() {
        const orderId = parseInt($(this).val());
        
        if ($(this).is(':checked')) {
            if (!selectedOrders.includes(orderId)) {
                selectedOrders.push(orderId);
            }
        } else {
            selectedOrders = selectedOrders.filter(id => id !== orderId);
        }
        
        updateCreateButton();
    });
    
    // Checkbox "select all"
    $(document).off('change', '#selectAllCheckbox').on('change', '#selectAllCheckbox', function() {
        const isChecked = $(this).is(':checked');
        $('.order-checkbox').prop('checked', isChecked).trigger('change');
    });
}

function updateCreateButton() {
    $('#selectedCount').text(selectedOrders.length);
    
    if (selectedOrders.length > 0) {
        $('#createPickupSection').show();
    } else {
        $('#createPickupSection').hide();
    }
}

function createPickup() {
    if (selectedOrders.length === 0) {
        toastr.warning('Veuillez sélectionner au moins une commande');
        return;
    }
    
    const configId = $('#delivery_configuration_id').val();
    if (!configId) {
        toastr.warning('Veuillez sélectionner une configuration Jax Delivery');
        return;
    }
    
    const btn = $('#createPickupBtn');
    const originalHtml = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Création...').prop('disabled', true);
    
    const formData = {
        _token: '{{ csrf_token() }}',
        delivery_configuration_id: configId,
        pickup_date: $('#pickup_date').val(),
        order_ids: selectedOrders
    };
    
    $.post('{{ route("admin.delivery.preparation.store") }}', formData)
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
                
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    // Réinitialiser le formulaire
                    selectedOrders = [];
                    updateCreateButton();
                    loadOrders();
                }
            } else {
                toastr.error(response.message || 'Erreur lors de la création');
            }
        })
        .fail(function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                toastr.error(errors.join('<br>'));
            } else {
                toastr.error('Erreur lors de la création de l\'enlèvement');
            }
        })
        .always(function() {
            btn.html(originalHtml).prop('disabled', false);
        });
}
</script>
@endpush