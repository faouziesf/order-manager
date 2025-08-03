@extends('layouts.admin')

@section('title', 'Préparation des Livraisons')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shipping-fast text-primary me-2"></i>
                Préparation des Livraisons
            </h1>
            <p class="text-muted mb-0">Créer des enlèvements groupés</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
            <a href="{{ route('admin.delivery.configuration') }}" class="btn btn-outline-primary">
                <i class="fas fa-cog me-1"></i>
                Configurations
            </a>
        </div>
    </div>

    @if(isset($warningMessage) || $activeConfigurations->isEmpty())
        <!-- Aucune configuration active -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                        </div>
                        
                        <h4 class="text-muted mb-3">Aucune Configuration Active</h4>
                        
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ $warningMessage ?? 'Vous devez configurer et activer au moins un transporteur avant de pouvoir préparer des livraisons.' }}
                        </div>

                        <div class="row text-center mb-4">
                            <div class="col-md-4">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                        <h6>1. Créer</h6>
                                        <p class="small text-muted">Créez une configuration de transporteur</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-warning h-100">
                                    <div class="card-body">
                                        <i class="fas fa-wifi fa-2x text-warning mb-2"></i>
                                        <h6>2. Tester</h6>
                                        <p class="small text-muted">Testez la connexion avec l'API</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success h-100">
                                    <div class="card-body">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <h6>3. Activer</h6>
                                        <p class="small text-muted">Activez la configuration</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.delivery.configuration.create') }}?carrier=jax_delivery" 
                               class="btn btn-primary">
                                <i class="fas fa-truck me-1"></i>
                                Configurer JAX Delivery
                            </a>
                            <a href="{{ route('admin.delivery.configuration.create') }}?carrier=mes_colis" 
                               class="btn btn-success">
                                <i class="fas fa-shipping-fast me-1"></i>
                                Configurer Mes Colis
                            </a>
                        </div>

                        <hr class="my-4">

                        <p class="text-muted small">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            <strong>Besoin d'aide ?</strong> Consultez la documentation ou contactez le support technique.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Interface normale de préparation -->
        <div class="row">
            <!-- Sélection de la configuration -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog me-2"></i>
                            Configuration Transporteur
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="delivery_configuration_id" class="form-label">
                                Choisir la configuration <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="delivery_configuration_id" required>
                                <option value="">Sélectionner une configuration...</option>
                                @foreach($activeConfigurations as $config)
                                    <option value="{{ $config->id }}" 
                                            data-carrier="{{ $config->carrier_slug }}"
                                            data-name="{{ $config->integration_name }}">
                                        {{ $config->carrier_name }} - {{ $config->integration_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pickup_date" class="form-label">Date d'enlèvement</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="pickup_date" 
                                   value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   min="{{ date('Y-m-d') }}">
                        </div>

                        <div id="configInfo" class="alert alert-info d-none">
                            <small>
                                <strong>Configuration sélectionnée :</strong><br>
                                <span id="configDetails"></span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Résumé de sélection -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-check-square me-2"></i>
                            Commandes Sélectionnées
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="selectionSummary">
                            <p class="text-muted text-center">
                                <i class="fas fa-info-circle me-1"></i>
                                Aucune commande sélectionnée
                            </p>
                        </div>
                        
                        <div id="selectionActions" class="d-none">
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" onclick="createPickup()" id="createPickupBtn">
                                    <i class="fas fa-truck-pickup me-1"></i>
                                    Créer l'Enlèvement
                                </button>
                                <button class="btn btn-outline-secondary" onclick="clearSelection()">
                                    <i class="fas fa-times me-1"></i>
                                    Annuler la Sélection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des commandes -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-box me-2"></i>
                            Commandes Prêtes à Expédier
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Filtres -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" 
                                       class="form-control" 
                                       id="searchOrders" 
                                       placeholder="Rechercher par nom, téléphone ou ID...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="governorateFilter">
                                    <option value="">Tous les gouvernorats</option>
                                    <!-- Options dynamiques -->
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary w-100" onclick="loadOrders()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Liste des commandes -->
                        <div id="ordersContainer">
                            <div class="text-center py-4">
                                <p class="text-muted">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    Sélectionnez une configuration pour voir les commandes disponibles
                                </p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div id="paginationContainer" class="d-none">
                            <nav aria-label="Pagination des commandes">
                                <ul class="pagination justify-content-center" id="pagination">
                                    <!-- Pagination dynamique -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="createPickupModal" tabindex="-1" aria-labelledby="createPickupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPickupModalLabel">
                    <i class="fas fa-truck-pickup me-2"></i>
                    Confirmer la Création de l'Enlèvement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pickupConfirmation">
                    <!-- Contenu dynamique -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmCreatePickup()" id="confirmBtn">
                    <i class="fas fa-check me-1"></i>
                    Confirmer la Création
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedOrders = [];
let currentPage = 1;
let ordersData = [];

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements de configuration
    document.getElementById('delivery_configuration_id').addEventListener('change', function() {
        const configId = this.value;
        if (configId) {
            showConfigInfo(this.options[this.selectedIndex]);
            loadOrders();
        } else {
            hideConfigInfo();
            clearOrders();
        }
    });

    // Écouter les changements de recherche
    document.getElementById('searchOrders').addEventListener('input', function() {
        if (this.value.length >= 2 || this.value.length === 0) {
            loadOrders();
        }
    });

    document.getElementById('governorateFilter').addEventListener('change', function() {
        loadOrders();
    });
});

function showConfigInfo(option) {
    const configInfo = document.getElementById('configInfo');
    const configDetails = document.getElementById('configDetails');
    
    configDetails.innerHTML = `
        ${option.getAttribute('data-name')}<br>
        <small class="text-muted">Transporteur: ${option.text.split(' - ')[0]}</small>
    `;
    
    configInfo.classList.remove('d-none');
}

function hideConfigInfo() {
    document.getElementById('configInfo').classList.add('d-none');
}

function clearOrders() {
    document.getElementById('ordersContainer').innerHTML = `
        <div class="text-center py-4">
            <p class="text-muted">
                <i class="fas fa-arrow-up me-1"></i>
                Sélectionnez une configuration pour voir les commandes disponibles
            </p>
        </div>
    `;
    selectedOrders = [];
    updateSelectionSummary();
}

async function loadOrders() {
    const configId = document.getElementById('delivery_configuration_id').value;
    if (!configId) return;

    const container = document.getElementById('ordersContainer');
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="text-muted mt-2">Chargement des commandes...</p>
        </div>
    `;

    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 20,
            search: document.getElementById('searchOrders').value,
            governorate: document.getElementById('governorateFilter').value
        });

        const response = await fetch(`/admin/delivery/preparation/orders?${params}`);
        const data = await response.json();

        if (data.success) {
            ordersData = data.orders;
            displayOrders(data.orders);
            updatePagination(data.pagination);
        } else {
            throw new Error(data.message || 'Erreur lors du chargement');
        }
    } catch (error) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <p class="text-danger">Erreur lors du chargement des commandes</p>
                <button class="btn btn-outline-primary" onclick="loadOrders()">
                    <i class="fas fa-redo me-1"></i>
                    Réessayer
                </button>
            </div>
        `;
    }
}

function displayOrders(orders) {
    const container = document.getElementById('ordersContainer');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune commande disponible</h5>
                <p class="text-muted">Toutes les commandes ont déjà été expédiées ou sont en attente de confirmation.</p>
            </div>
        `;
        return;
    }

    let html = '<div class="list-group">';
    
    orders.forEach(order => {
        const isSelected = selectedOrders.includes(order.id);
        const canBeShipped = order.can_be_shipped;
        
        html += `
            <div class="list-group-item ${isSelected ? 'border-success bg-light' : ''}" 
                 data-order-id="${order.id}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="form-check me-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="order_${order.id}"
                               ${isSelected ? 'checked' : ''}
                               ${!canBeShipped ? 'disabled' : ''}
                               onchange="toggleOrderSelection(${order.id})">
                    </div>
                    
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">
                                    <span class="badge bg-primary me-2">#${order.id}</span>
                                    ${order.customer_name}
                                </h6>
                                <p class="mb-1 text-muted small">
                                    <i class="fas fa-phone me-1"></i>
                                    ${order.customer_phone}
                                    ${order.customer_phone_2 ? ' / ' + order.customer_phone_2 : ''}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">
                                    ${parseFloat(order.total_price).toFixed(3)} TND
                                </span>
                                ${!canBeShipped ? '<span class="badge bg-warning ms-1">Stock insuffisant</span>' : ''}
                            </div>
                        </div>
                        
                        <p class="mb-1 text-muted small">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            ${order.customer_address}, ${order.customer_city}
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-box me-1"></i>
                                ${order.items ? order.items.length : 0} produit(s)
                            </small>
                            <small class="text-muted">
                                Créée le ${new Date(order.created_at).toLocaleDateString('fr-FR')}
                            </small>
                        </div>
                        
                        ${!canBeShipped && order.stock_issues ? `
                            <div class="alert alert-warning alert-sm mt-2 py-1 px-2">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    ${order.stock_issues.map(issue => issue.message).join(', ')}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function updatePagination(pagination) {
    const container = document.getElementById('paginationContainer');
    const paginationList = document.getElementById('pagination');
    
    if (pagination.last_page <= 1) {
        container.classList.add('d-none');
        return;
    }
    
    container.classList.remove('d-none');
    
    let html = '';
    
    // Bouton précédent
    html += `
        <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Pages
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (Math.abs(i - pagination.current_page) <= 2 || i === 1 || i === pagination.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
        } else if (Math.abs(i - pagination.current_page) === 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Bouton suivant
    html += `
        <li class="page-item ${pagination.current_page >= pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1})">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationList.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadOrders();
}

function toggleOrderSelection(orderId) {
    const index = selectedOrders.indexOf(orderId);
    if (index > -1) {
        selectedOrders.splice(index, 1);
    } else {
        selectedOrders.push(orderId);
    }
    updateSelectionSummary();
}

function updateSelectionSummary() {
    const summaryContainer = document.getElementById('selectionSummary');
    const actionsContainer = document.getElementById('selectionActions');
    
    if (selectedOrders.length === 0) {
        summaryContainer.innerHTML = `
            <p class="text-muted text-center">
                <i class="fas fa-info-circle me-1"></i>
                Aucune commande sélectionnée
            </p>
        `;
        actionsContainer.classList.add('d-none');
    } else {
        const selectedOrdersData = ordersData.filter(order => selectedOrders.includes(order.id));
        const totalAmount = selectedOrdersData.reduce((sum, order) => sum + parseFloat(order.total_price), 0);
        
        summaryContainer.innerHTML = `
            <div class="text-center">
                <h6 class="text-success">${selectedOrders.length} commande(s)</h6>
                <p class="mb-0">
                    <strong>Total COD:</strong> ${totalAmount.toFixed(3)} TND
                </p>
            </div>
        `;
        actionsContainer.classList.remove('d-none');
    }
}

function clearSelection() {
    selectedOrders = [];
    updateSelectionSummary();
    
    // Décocher toutes les cases
    document.querySelectorAll('input[type="checkbox"][id^="order_"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Retirer les styles de sélection
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.classList.remove('border-success', 'bg-light');
    });
}

function createPickup() {
    if (selectedOrders.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Aucune commande sélectionnée',
            text: 'Veuillez sélectionner au moins une commande.',
        });
        return;
    }
    
    const configId = document.getElementById('delivery_configuration_id').value;
    if (!configId) {
        Swal.fire({
            icon: 'warning',
            title: 'Configuration manquante',
            text: 'Veuillez sélectionner une configuration de transporteur.',
        });
        return;
    }
    
    // Préparer le modal de confirmation
    const selectedOrdersData = ordersData.filter(order => selectedOrders.includes(order.id));
    const totalAmount = selectedOrdersData.reduce((sum, order) => sum + parseFloat(order.total_price), 0);
    const configOption = document.getElementById('delivery_configuration_id').selectedOptions[0];
    const pickupDate = document.getElementById('pickup_date').value;
    
    document.getElementById('pickupConfirmation').innerHTML = `
        <div class="mb-3">
            <h6>Configuration sélectionnée :</h6>
            <p class="text-muted">${configOption.text}</p>
        </div>
        
        <div class="mb-3">
            <h6>Date d'enlèvement :</h6>
            <p class="text-muted">${new Date(pickupDate).toLocaleDateString('fr-FR')}</p>
        </div>
        
        <div class="mb-3">
            <h6>Commandes à expédier :</h6>
            <div class="bg-light p-2 rounded">
                <div class="row">
                    <div class="col-6"><strong>Nombre :</strong> ${selectedOrders.length}</div>
                    <div class="col-6"><strong>Total COD :</strong> ${totalAmount.toFixed(3)} TND</div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i>
            <small>L'enlèvement sera créé et les commandes seront marquées comme expédiées.</small>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('createPickupModal'));
    modal.show();
}

async function confirmCreatePickup() {
    const confirmBtn = document.getElementById('confirmBtn');
    const originalText = confirmBtn.innerHTML;
    
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Création...';
    confirmBtn.disabled = true;
    
    try {
        const response = await fetch('/admin/delivery/preparation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                delivery_configuration_id: document.getElementById('delivery_configuration_id').value,
                order_ids: selectedOrders,
                pickup_date: document.getElementById('pickup_date').value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createPickupModal')).hide();
            
            Swal.fire({
                icon: 'success',
                title: 'Enlèvement créé !',
                text: data.message,
                showConfirmButton: false,
                timer: 2000
            });
            
            setTimeout(() => {
                window.location.href = '/admin/delivery/pickups';
            }, 2000);
        } else {
            throw new Error(data.message || 'Erreur lors de la création');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message,
        });
        
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    }
}
</script>
@endpush

@push('styles')
<style>
.alert-sm {
    padding: 0.25rem 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.list-group-item {
    transition: all 0.2s ease-in-out;
}

.list-group-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.list-group-item.border-success {
    border-width: 2px !important;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.page-link {
    color: #0d6efd;
}

.page-link:hover {
    color: #0a58ca;
}

.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush