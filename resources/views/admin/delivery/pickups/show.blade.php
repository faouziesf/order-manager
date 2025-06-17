@extends('layouts.admin')

@section('title', 'Enlèvement #' . $pickup->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.delivery.pickups.index') }}">Enlèvements</a></li>
                            <li class="breadcrumb-item active">Enlèvement #{{ $pickup->id }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-warehouse text-primary me-2"></i>
                        Enlèvement #{{ $pickup->id }}
                        <span class="badge {{ $pickup->status_badge_class }} ms-2">{{ $pickup->status_label }}</span>
                    </h1>
                    <p class="text-muted mb-0">{{ $pickup->carrier_display_name }} - {{ $pickup->shipment_count }} expédition(s)</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.delivery.pickups.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    @if($pickup->status === 'draft')
                        @can('validate', $pickup)
                            <button type="button" class="btn btn-success" onclick="validatePickup()">
                                <i class="fas fa-check me-2"></i>Valider l'enlèvement
                            </button>
                        @endcan
                    @endif
                    @if($pickup->status === 'validated' || $pickup->status === 'picked_up')
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-2"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                @can('generateLabels', $pickup)
                                    <li>
                                        <button class="dropdown-item" onclick="generateLabels()">
                                            <i class="fas fa-tags me-2"></i>Générer les étiquettes
                                        </button>
                                    </li>
                                @endcan
                                @can('generateManifest', $pickup)
                                    <li>
                                        <button class="dropdown-item" onclick="generateManifest()">
                                            <i class="fas fa-file-pdf me-2"></i>Générer le manifeste
                                        </button>
                                    </li>
                                @endcan
                                @can('refreshStatus', $pickup)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item" onclick="refreshStatus()">
                                            <i class="fas fa-sync-alt me-2"></i>Rafraîchir le statut
                                        </button>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Informations principales -->
                <div class="col-lg-4">
                    <!-- Carte d'information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations générales
                            </h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">ID Enlèvement:</dt>
                                <dd class="col-sm-7">#{{ $pickup->id }}</dd>

                                <dt class="col-sm-5">Transporteur:</dt>
                                <dd class="col-sm-7">
                                    <strong>{{ $pickup->carrier_display_name }}</strong>
                                    <br><small class="text-muted">{{ $pickup->deliveryConfiguration->integration_name }}</small>
                                </dd>

                                <dt class="col-sm-5">Statut:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge {{ $pickup->status_badge_class }}">{{ $pickup->status_label }}</span>
                                    @if($pickup->validated_at)
                                        <br><small class="text-muted">Validé le {{ $pickup->validated_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Créé le:</dt>
                                <dd class="col-sm-7">{{ $pickup->created_at->format('d/m/Y H:i') }}</dd>

                                @if($pickup->pickup_date)
                                    <dt class="col-sm-5">Date prévue:</dt>
                                    <dd class="col-sm-7">{{ $pickup->pickup_date->format('d/m/Y') }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Adresse d'enlèvement -->
                    @if($pickup->pickupAddress)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    Adresse d'enlèvement
                                </h5>
                            </div>
                            <div class="card-body">
                                <strong>{{ $pickup->pickupAddress->name }}</strong>
                                <br>{{ $pickup->pickupAddress->contact_name }}
                                <br>{{ $pickup->pickupAddress->address }}
                                @if($pickup->pickupAddress->city)
                                    <br>{{ $pickup->pickupAddress->city }}
                                @endif
                                @if($pickup->pickupAddress->postal_code)
                                    {{ $pickup->pickupAddress->postal_code }}
                                @endif
                                <br><i class="fas fa-phone me-1"></i>{{ $pickup->pickupAddress->phone }}
                                @if($pickup->pickupAddress->email)
                                    <br><i class="fas fa-envelope me-1"></i>{{ $pickup->pickupAddress->email }}
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Statistiques -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Statistiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary">{{ $pickup->shipment_count }}</h4>
                                    <small class="text-muted">Total expéditions</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success">{{ $pickup->validated_shipments_count }}</h4>
                                    <small class="text-muted">Validées</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-info">{{ $pickup->delivered_shipments_count }}</h4>
                                    <small class="text-muted">Livrées</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-warning">{{ number_format($pickup->total_value, 3) }} DT</h4>
                                    <small class="text-muted">Valeur totale</small>
                                </div>
                            </div>
                            @if($pickup->shipment_count > 0)
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between small">
                                        <span>Progression</span>
                                        <span>{{ $pickup->progress_percentage }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $pickup->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Liste des expéditions -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-shipping-fast me-2"></i>
                                Expéditions ({{ $pickup->shipments->count() }})
                            </h5>
                            @if($pickup->status === 'draft')
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addOrdersModal">
                                        <i class="fas fa-plus me-2"></i>Ajouter commandes
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($pickup->shipments->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Commande</th>
                                                <th>Client</th>
                                                <th>Téléphone</th>
                                                <th>Adresse</th>
                                                <th>Montant</th>
                                                <th>Code suivi</th>
                                                <th>Statut</th>
                                                @if($pickup->status === 'draft')
                                                    <th>Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pickup->shipments as $shipment)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.orders.show', $shipment->order) }}" target="_blank">
                                                            <strong>#{{ $shipment->order->id }}</strong>
                                                        </a>
                                                    </td>
                                                    <td>{{ $shipment->customer_name }}</td>
                                                    <td>
                                                        <span class="font-monospace">{{ $shipment->customer_phone }}</span>
                                                    </td>
                                                    <td>
                                                        <small>{{ Str::limit($shipment->customer_address, 30) }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>{{ number_format($shipment->value, 3) }} DT</strong>
                                                    </td>
                                                    <td>
                                                        @if($shipment->pos_barcode)
                                                            <code>{{ $shipment->pos_barcode }}</code>
                                                            @if($shipment->tracking_url)
                                                                <br><a href="{{ $shipment->tracking_url }}" target="_blank" class="small">
                                                                    <i class="fas fa-external-link-alt me-1"></i>Suivre
                                                                </a>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $shipment->status_badge_class }}">
                                                            {{ $shipment->status_label }}
                                                        </span>
                                                        @if($shipment->delivered_at)
                                                            <br><small class="text-success">{{ $shipment->delivered_at->format('d/m H:i') }}</small>
                                                        @endif
                                                    </td>
                                                    @if($pickup->status === 'draft')
                                                        <td>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    onclick="removeShipment({{ $shipment->id }})" 
                                                                    title="Supprimer de l'enlèvement">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-shipping-fast fa-2x text-muted mb-3"></i>
                                    <h6 class="text-muted">Aucune expédition</h6>
                                    @if($pickup->status === 'draft')
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOrdersModal">
                                            <i class="fas fa-plus me-2"></i>Ajouter des commandes
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter commandes -->
@if($pickup->status === 'draft')
    <div class="modal fade" id="addOrdersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Ajouter des commandes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="availableOrdersTable">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" onclick="addSelectedOrders()" disabled id="addOrdersBtn">
                        <i class="fas fa-plus me-2"></i>Ajouter les commandes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
const pickupId = {{ $pickup->id }};
const pickupStatus = '{{ $pickup->status }}';
let selectedOrdersToAdd = new Set();

$(document).ready(function() {
    console.log('Page de détail enlèvement chargée');
    
    // Charger les commandes disponibles si modal d'ajout
    $('#addOrdersModal').on('show.bs.modal', function() {
        loadAvailableOrders();
    });
});

// Actions sur l'enlèvement
function validatePickup() {
    if (!confirm('Êtes-vous sûr de vouloir valider cet enlèvement ? Cette action créera les expéditions avec le transporteur.')) {
        return;
    }
    
    performAction('validate', 'POST');
}

function generateLabels() {
    performAction('labels', 'POST', true);
}

function generateManifest() {
    performAction('manifest', 'POST', true);
}

function refreshStatus() {
    performAction('refresh', 'POST');
}

function performAction(action, method = 'POST', download = false) {
    const btn = event?.target?.closest('button');
    let originalHtml = '';
    
    if (btn) {
        originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
    }
    
    fetch(`/admin/delivery/pickups/${pickupId}/${action}`, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (download && response.ok) {
            const contentDisposition = response.headers.get('content-disposition');
            let filename = 'document.pdf';
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                if (filenameMatch) {
                    filename = filenameMatch[1];
                }
            }
            
            return response.blob().then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
                
                showNotification('success', 'Fichier téléchargé avec succès');
            });
        } else {
            return response.json();
        }
    })
    .then(data => {
        if (data && data.success !== undefined) {
            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', data.message);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de l\'action');
    })
    .finally(() => {
        if (btn) {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    });
}

// Gestion des expéditions
function removeShipment(shipmentId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette expédition de l\'enlèvement ?')) {
        return;
    }
    
    fetch(`/admin/delivery/pickups/${pickupId}/remove-shipment/${shipmentId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression');
    });
}

// Ajout de commandes
function loadAvailableOrders() {
    fetch(`{{ route('admin.delivery.preparation.orders') }}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.orders && data.orders.data) {
            renderAvailableOrders(data.orders.data);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('availableOrdersTable').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erreur lors du chargement des commandes
            </div>
        `;
    });
}

function renderAvailableOrders(orders) {
    if (!orders || orders.length === 0) {
        document.getElementById('availableOrdersTable').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                <h6 class="text-muted">Aucune commande disponible</h6>
            </div>
        `;
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="form-check-input" id="selectAllAvailable" onchange="toggleAllAvailableOrders()">
                        </th>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
    `;

    orders.forEach(order => {
        html += `
            <tr onclick="toggleAvailableOrderSelection(${order.id})">
                <td>
                    <input type="checkbox" class="form-check-input available-order-checkbox" 
                           data-order-id="${order.id}">
                </td>
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name || 'N/A'}</td>
                <td><strong>${parseFloat(order.total_price || 0).toFixed(3)} DT</strong></td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('availableOrdersTable').innerHTML = html;
}

function toggleAvailableOrderSelection(orderId) {
    if (selectedOrdersToAdd.has(orderId)) {
        selectedOrdersToAdd.delete(orderId);
    } else {
        selectedOrdersToAdd.add(orderId);
    }
    updateAvailableOrdersUI();
}

function toggleAllAvailableOrders() {
    const selectAllCheckbox = document.getElementById('selectAllAvailable');
    const checkboxes = document.querySelectorAll('.available-order-checkbox');
    
    if (selectAllCheckbox.checked) {
        checkboxes.forEach(cb => {
            const orderId = parseInt(cb.getAttribute('data-order-id'));
            selectedOrdersToAdd.add(orderId);
        });
    } else {
        selectedOrdersToAdd.clear();
    }
    updateAvailableOrdersUI();
}

function updateAvailableOrdersUI() {
    const addBtn = document.getElementById('addOrdersBtn');
    
    // Mettre à jour les checkboxes
    document.querySelectorAll('.available-order-checkbox').forEach(cb => {
        const orderId = parseInt(cb.getAttribute('data-order-id'));
        cb.checked = selectedOrdersToAdd.has(orderId);
    });
    
    // Activer/désactiver le bouton d'ajout
    addBtn.disabled = selectedOrdersToAdd.size === 0;
    addBtn.innerHTML = selectedOrdersToAdd.size > 0 
        ? `<i class="fas fa-plus me-2"></i>Ajouter ${selectedOrdersToAdd.size} commande(s)`
        : '<i class="fas fa-plus me-2"></i>Ajouter les commandes';
}

function addSelectedOrders() {
    if (selectedOrdersToAdd.size === 0) {
        return;
    }
    
    const orderIds = Array.from(selectedOrdersToAdd);
    
    fetch(`/admin/delivery/pickups/${pickupId}/add-orders`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_ids: orderIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('addOrdersModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de l\'ajout des commandes');
    });
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