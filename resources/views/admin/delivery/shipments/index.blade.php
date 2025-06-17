@extends('layouts.admin')

@section('title', 'Gestion des expéditions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-shipping-fast text-primary me-2"></i>
                        Gestion des expéditions
                    </h1>
                    <p class="text-muted mb-0">Suivez et gérez toutes vos expéditions</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshAllShipments()">
                        <i class="fas fa-sync-alt me-2"></i>Suivi global
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="refreshShipments()">
                        <i class="fas fa-redo me-2"></i>Actualiser
                    </button>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="fas fa-list-alt fa-2x text-secondary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['created'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Créées</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-truck fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $stats['picked_up'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Récupérées</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-road fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ $stats['in_transit'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">En transit</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['delivered'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Livrées</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                            <h4 class="mb-1">{{ $stats['anomaly'] ?? 0 }}</h4>
                            <p class="text-muted mb-0">Anomalies</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et liste -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Liste des expéditions
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filtersModal">
                                    <i class="fas fa-filter me-2"></i>Filtres
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="exportShipments()">
                                        <i class="fas fa-download me-2"></i>Exporter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="shipmentsTable">
                        @if($shipments->isNotEmpty())
                            @include('admin.delivery.shipments.table', ['shipments' => $shipments])
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune expédition trouvée</h5>
                                <p class="text-muted">Les expéditions apparaîtront ici après validation des enlèvements</p>
                                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer un enlèvement
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($shipments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $shipments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des filtres -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                                <label for="filter_status" class="form-label">Statut</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="created">Créée</option>
                                    <option value="picked_up_by_carrier">Récupérée</option>
                                    <option value="in_transit">En transit</option>
                                    <option value="delivered">Livrée</option>
                                    <option value="in_return">En retour</option>
                                    <option value="anomaly">Anomalie</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_carrier" class="form-label">Transporteur</label>
                                <select class="form-select" id="filter_carrier" name="carrier">
                                    <option value="">Tous les transporteurs</option>
                                    <option value="fparcel">Fparcel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_tracking_code" class="form-label">Code de suivi</label>
                                <input type="text" class="form-control" id="filter_tracking_code" name="tracking_code" placeholder="Ex: TN20240101001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_order_number" class="form-label">Numéro de commande</label>
                                <input type="text" class="form-control" id="filter_order_number" name="order_number" placeholder="Ex: CMD-001">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_customer_name" class="form-label">Nom du client</label>
                                <input type="text" class="form-control" id="filter_customer_name" name="customer_name" placeholder="Nom du client">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_customer_phone" class="form-label">Téléphone du client</label>
                                <input type="text" class="form-control" id="filter_customer_phone" name="customer_phone" placeholder="Ex: +216 98 123 456">
                            </div>
                        </div>
                    </div>
                    
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
                                <label for="filter_min_value" class="form-label">Montant minimum (DT)</label>
                                <input type="number" class="form-control" id="filter_min_value" name="min_value" step="0.001" placeholder="0.000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filter_max_value" class="form-label">Montant maximum (DT)</label>
                                <input type="number" class="form-control" id="filter_max_value" name="max_value" step="0.001" placeholder="1000.000">
                            </div>
                        </div>
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

<!-- Modal de détails d'expédition -->
<div class="modal fade" id="shipmentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shipping-fast me-2"></i>
                    <span id="shipmentDetailsTitle">Détails de l'expédition</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="shipmentDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="trackShipmentBtn" onclick="trackCurrentShipment()">
                    <i class="fas fa-sync-alt me-2"></i>Actualiser le suivi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentFilters = {};
let currentShipmentId = null;

$(document).ready(function() {
    console.log('Page des expéditions chargée');
    
    // Auto-refresh toutes les 3 minutes
    setInterval(function() {
        if (!document.hidden) {
            refreshShipments();
        }
    }, 180000);
});

// Gestion des filtres
function applyFilters() {
    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    
    currentFilters = {};
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            currentFilters[key] = value;
        }
    }
    
    loadShipments();
    bootstrap.Modal.getInstance(document.getElementById('filtersModal')).hide();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    currentFilters = {};
    loadShipments();
}

function loadShipments() {
    const params = new URLSearchParams(currentFilters);
    
    fetch(`{{ route('admin.delivery.shipments') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            document.getElementById('shipmentsTable').innerHTML = data.html;
        }
        // Mettre à jour les statistiques si disponibles
        if (data.stats) {
            updateStats(data.stats);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du chargement des expéditions');
    });
}

function refreshShipments() {
    loadShipments();
    showNotification('info', 'Liste des expéditions actualisée');
}

// Actions sur les expéditions
function viewShipment(shipmentId) {
    currentShipmentId = shipmentId;
    
    document.getElementById('shipmentDetailsTitle').textContent = `Expédition #${shipmentId}`;
    document.getElementById('shipmentDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('shipmentDetailsModal')).show();
    
    // Charger les détails
    fetch(`/admin/delivery/shipments/${shipmentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('shipmentDetailsContent').innerHTML = html;
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('shipmentDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erreur lors du chargement des détails
            </div>
        `;
    });
}

function trackShipment(shipmentId) {
    const btn = event?.target?.closest('button');
    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 3000);
    }
    
    fetch(`/admin/delivery/shipments/${shipmentId}/track`, {
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
            setTimeout(() => loadShipments(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du suivi');
    });
}

function trackCurrentShipment() {
    if (currentShipmentId) {
        trackShipment(currentShipmentId);
    }
}

function markAsDelivered(shipmentId) {
    const notes = prompt('Notes sur la livraison (optionnel):');
    if (notes === null) return; // Utilisateur a annulé
    
    fetch(`/admin/delivery/shipments/${shipmentId}/mark-delivered`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => loadShipments(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    });
}

// Suivi global de toutes les expéditions
function refreshAllShipments() {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Suivi en cours...';
    btn.disabled = true;
    
    fetch('{{ route("admin.delivery.api.track-all") }}', {
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
            setTimeout(() => loadShipments(), 2000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du suivi global');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function exportShipments() {
    const params = new URLSearchParams(currentFilters);
    params.append('export', 'true');
    
    window.open(`{{ route('admin.delivery.shipments') }}?${params}`, '_blank');
}

function updateStats(stats) {
    // Mettre à jour les cartes de statistiques
    // Cette fonction peut être étendue selon les besoins
}

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