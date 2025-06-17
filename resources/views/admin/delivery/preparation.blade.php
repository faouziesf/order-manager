@extends('layouts.admin')

@section('title', 'Préparation d\'enlèvement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-boxes text-primary me-2"></i>
                        Préparation d'enlèvement
                    </h1>
                    <p class="text-muted mb-0">Créez des enlèvements à partir de vos commandes confirmées</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshOrders()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
                    <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-primary">
                        <i class="fas fa-warehouse me-2"></i>Voir les enlèvements
                    </a>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-basket fa-2x text-primary mb-2"></i>
                            <h3 class="mb-1">{{ $stats['available_orders'] }}</h3>
                            <p class="text-muted mb-0">Commandes disponibles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-draft2digital fa-2x text-warning mb-2"></i>
                            <h3 class="mb-1">{{ $stats['draft_pickups'] }}</h3>
                            <p class="text-muted mb-0">Enlèvements en brouillon</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($configurations->isEmpty())
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fa-2x"></i>
                        <div>
                            <h5 class="mb-1">Aucun transporteur configuré</h5>
                            <p class="mb-2">Vous devez configurer au moins un transporteur pour créer des enlèvements.</p>
                            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-cog me-2"></i>Aller à la configuration
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <!-- Formulaire de création d'enlèvement -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus me-2"></i>
                                    Nouveau enlèvement
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="createPickupForm" onsubmit="createPickup(event)">
                                    <div class="mb-3">
                                        <label for="delivery_configuration_id" class="form-label">Transporteur *</label>
                                        <select class="form-select" id="delivery_configuration_id" name="delivery_configuration_id" required onchange="updateAddressOptions()">
                                            <option value="">Sélectionner un transporteur</option>
                                            @foreach($configurations as $config)
                                                <option value="{{ $config->id }}" data-supports-address="{{ $config->supportsPickupAddressSelection() ? '1' : '0' }}">
                                                    {{ $config->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3" id="pickup_address_section" style="display: none;">
                                        <label for="pickup_address_id" class="form-label">Adresse d'enlèvement</label>
                                        <select class="form-select" id="pickup_address_id" name="pickup_address_id">
                                            <option value="">Sélectionner une adresse</option>
                                            @foreach($pickupAddresses as $address)
                                                <option value="{{ $address->id }}" {{ $address->is_default ? 'selected' : '' }}>
                                                    {{ $address->name }} - {{ $address->contact_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($pickupAddresses->isEmpty())
                                            <div class="text-muted small mt-1">
                                                <a href="{{ route('admin.delivery.pickup-addresses.create') }}" target="_blank">
                                                    <i class="fas fa-plus me-1"></i>Ajouter une adresse
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="pickup_date" class="form-label">Date d'enlèvement (optionnel)</label>
                                        <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                               min="{{ date('Y-m-d') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Commandes sélectionnées</label>
                                        <div id="selectedOrdersPreview" class="border rounded p-2 bg-light">
                                            <p class="text-muted small mb-0">Aucune commande sélectionnée</p>
                                        </div>
                                        <input type="hidden" id="selectedOrderIds" name="order_ids">
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary" disabled id="createBtn">
                                            <i class="fas fa-plus me-2"></i>Créer l'enlèvement
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Commandes disponibles
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filtersModal">
                                        <i class="fas fa-filter me-2"></i>Filtres
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllOrders()">
                                        <i class="fas fa-check-square me-2"></i>Tout sélectionner
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                                        <i class="fas fa-times me-2"></i>Tout désélectionner
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="ordersTable">
                                    <!-- Le contenu sera chargé via AJAX -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal des filtres -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>Filtres avancés
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filtersForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_date_from" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="filter_date_from" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_date_to" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="filter_date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_min_amount" class="form-label">Montant minimum</label>
                                <input type="number" class="form-control" id="filter_min_amount" name="min_amount" step="0.001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_max_amount" class="form-label">Montant maximum</label>
                                <input type="number" class="form-control" id="filter_max_amount" name="max_amount" step="0.001">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="filter_search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="filter_search" name="search" 
                               placeholder="Nom, téléphone, ID commande...">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">Effacer</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Appliquer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedOrders = new Set();
let currentFilters = {};

$(document).ready(function() {
    loadOrders();
    updateAddressOptions();
});

// Gestion des transporteurs et adresses
function updateAddressOptions() {
    const configSelect = document.getElementById('delivery_configuration_id');
    const addressSection = document.getElementById('pickup_address_section');
    const addressSelect = document.getElementById('pickup_address_id');
    
    if (configSelect.value) {
        const selectedOption = configSelect.querySelector(`option[value="${configSelect.value}"]`);
        const supportsAddress = selectedOption.getAttribute('data-supports-address') === '1';
        
        if (supportsAddress) {
            addressSection.style.display = 'block';
            addressSelect.required = true;
        } else {
            addressSection.style.display = 'none';
            addressSelect.required = false;
            addressSelect.value = '';
        }
    } else {
        addressSection.style.display = 'none';
        addressSelect.required = false;
    }
}

// Chargement des commandes
function loadOrders() {
    const params = new URLSearchParams(currentFilters);
    
    fetch(`{{ route('admin.delivery.preparation.orders') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            document.getElementById('ordersTable').innerHTML = data.html;
        } else if (data.orders) {
            renderOrdersTable(data.orders.data);
        }
        updateSelectedOrdersUI();
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('ordersTable').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erreur lors du chargement des commandes
            </div>
        `;
    });
}

function renderOrdersTable(orders) {
    if (!orders || orders.length === 0) {
        document.getElementById('ordersTable').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune commande disponible</h5>
                <p class="text-muted">Toutes les commandes confirmées ont déjà été assignées à des enlèvements</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAllOrders()">
                        </th>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Montant</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
    `;

    orders.forEach(order => {
        const isSelected = selectedOrders.has(order.id);
        html += `
            <tr class="${isSelected ? 'table-primary' : ''}" onclick="toggleOrderSelection(${order.id})">
                <td>
                    <input type="checkbox" class="form-check-input order-checkbox" 
                           data-order-id="${order.id}" ${isSelected ? 'checked' : ''}>
                </td>
                <td>
                    <strong>#${order.id}</strong>
                    <br><small class="text-muted">${order.items?.length || 0} article(s)</small>
                </td>
                <td>
                    <strong>${order.customer_name || 'N/A'}</strong>
                </td>
                <td>
                    <span class="font-monospace">${order.customer_phone}</span>
                </td>
                <td>
                    <small>${order.customer_address || 'N/A'}</small>
                </td>
                <td>
                    <strong>${parseFloat(order.total_price || 0).toFixed(3)} DT</strong>
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

    document.getElementById('ordersTable').innerHTML = html;
}

// Gestion de la sélection des commandes
function toggleOrderSelection(orderId) {
    if (selectedOrders.has(orderId)) {
        selectedOrders.delete(orderId);
    } else {
        selectedOrders.add(orderId);
    }
    updateSelectedOrdersUI();
}

function toggleAllOrders() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    if (selectAllCheckbox.checked) {
        checkboxes.forEach(cb => {
            const orderId = parseInt(cb.getAttribute('data-order-id'));
            selectedOrders.add(orderId);
        });
    } else {
        selectedOrders.clear();
    }
    updateSelectedOrdersUI();
}

function selectAllOrders() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => {
        const orderId = parseInt(cb.getAttribute('data-order-id'));
        selectedOrders.add(orderId);
    });
    updateSelectedOrdersUI();
}

function clearSelection() {
    selectedOrders.clear();
    updateSelectedOrdersUI();
}

function updateSelectedOrdersUI() {
    const preview = document.getElementById('selectedOrdersPreview');
    const hiddenInput = document.getElementById('selectedOrderIds');
    const createBtn = document.getElementById('createBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    // Mettre à jour les checkboxes
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        const orderId = parseInt(cb.getAttribute('data-order-id'));
        cb.checked = selectedOrders.has(orderId);
        
        // Mettre à jour le style de la ligne
        const row = cb.closest('tr');
        if (selectedOrders.has(orderId)) {
            row.classList.add('table-primary');
        } else {
            row.classList.remove('table-primary');
        }
    });
    
    // Mettre à jour le checkbox "Tout sélectionner"
    const allCheckboxes = document.querySelectorAll('.order-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = allCheckboxes.length > 0 && checkedCheckboxes.length === allCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    }
    
    // Mettre à jour la prévisualisation
    if (selectedOrders.size === 0) {
        preview.innerHTML = '<p class="text-muted small mb-0">Aucune commande sélectionnée</p>';
        createBtn.disabled = true;
    } else {
        const orderIds = Array.from(selectedOrders);
        preview.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span><strong>${selectedOrders.size}</strong> commande(s) sélectionnée(s)</span>
                <button type="button" class="btn btn-link btn-sm p-0" onclick="clearSelection()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-1">
                <small class="text-muted">Commandes: ${orderIds.slice(0, 5).map(id => '#' + id).join(', ')}${orderIds.length > 5 ? '...' : ''}</small>
            </div>
        `;
        createBtn.disabled = false;
    }
    
    // Mettre à jour l'input caché
    hiddenInput.value = JSON.stringify(Array.from(selectedOrders));
}

// Création de l'enlèvement
function createPickup(event) {
    event.preventDefault();
    
    if (selectedOrders.size === 0) {
        showNotification('error', 'Veuillez sélectionner au moins une commande');
        return;
    }
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Ajouter les IDs des commandes sélectionnées
    formData.delete('order_ids');
    Array.from(selectedOrders).forEach(orderId => {
        formData.append('order_ids[]', orderId);
    });
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.delivery.preparation.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            if (data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1500);
            } else {
                // Réinitialiser le formulaire
                selectedOrders.clear();
                updateSelectedOrdersUI();
                form.reset();
                updateAddressOptions();
                loadOrders();
            }
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    showNotification('error', data.errors[key][0]);
                });
            } else {
                showNotification('error', data.message || 'Erreur lors de la création');
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la création de l\'enlèvement');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = selectedOrders.size === 0;
    });
}

// Filtres
function applyFilters() {
    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    
    currentFilters = {};
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            currentFilters[key] = value;
        }
    }
    
    loadOrders();
    bootstrap.Modal.getInstance(document.getElementById('filtersModal')).hide();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    currentFilters = {};
    loadOrders();
}

function refreshOrders() {
    loadOrders();
    showNotification('info', 'Liste des commandes actualisée');
}

// Fonction utilitaire pour les notifications
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const icon = type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="${icon} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection