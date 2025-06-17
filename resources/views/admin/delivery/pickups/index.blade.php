@extends('layouts.admin')

@section('title', 'Gestion des enlèvements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-warehouse text-primary me-2"></i>
                        Gestion des enlèvements
                    </h1>
                    <p class="text-muted mb-0">Gérez vos enlèvements et suivez leur statut</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshPickups()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
                    <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvel enlèvement
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="fas fa-list-alt fa-2x text-secondary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $stats['draft'] }}</h4>
                            <p class="text-muted mb-0">Brouillons</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['validated'] }}</h4>
                            <p class="text-muted mb-0">Validés</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-truck fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['picked_up'] }}</h4>
                            <p class="text-muted mb-0">Récupérés</p>
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
                                Liste des enlèvements
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filtersModal">
                                    <i class="fas fa-filter me-2"></i>Filtres
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="trackAllShipments()">
                                        <i class="fas fa-sync me-2"></i>Suivi global
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="pickupsTable">
                        <!-- Le contenu sera chargé ici -->
                        @if($pickups->isNotEmpty())
                            @include('admin.delivery.pickups.table', ['pickups' => $pickups])
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun enlèvement trouvé</h5>
                                <p class="text-muted">Créez votre premier enlèvement pour commencer</p>
                                <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer un enlèvement
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($pickups->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $pickups->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des filtres -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>Filtres
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filtersForm">
                    <div class="mb-3">
                        <label for="filter_status" class="form-label">Statut</label>
                        <select class="form-select" id="filter_status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="validated">Validé</option>
                            <option value="picked_up">Récupéré</option>
                            <option value="problem">Problème</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filter_carrier" class="form-label">Transporteur</label>
                        <select class="form-select" id="filter_carrier" name="carrier">
                            <option value="">Tous les transporteurs</option>
                            <option value="fparcel">Fparcel</option>
                        </select>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="clearFilters()">Effacer</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Appliquer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirmer l'action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                Êtes-vous sûr de vouloir effectuer cette action ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmModalAction">Confirmer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentFilters = {};

$(document).ready(function() {
    // Initialisation
    console.log('Page des enlèvements chargée');
    
    // Auto-refresh toutes les 2 minutes
    setInterval(refreshPickups, 120000);
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
    
    loadPickups();
    bootstrap.Modal.getInstance(document.getElementById('filtersModal')).hide();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    currentFilters = {};
    loadPickups();
}

function loadPickups() {
    const params = new URLSearchParams(currentFilters);
    
    fetch(`{{ route('admin.delivery.pickups.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            document.getElementById('pickupsTable').innerHTML = data.html;
        }
        // Mettre à jour les statistiques si disponibles
        if (data.stats) {
            updateStats(data.stats);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du chargement des enlèvements');
    });
}

function updateStats(stats) {
    // Mettre à jour les cartes de statistiques
    // Cette fonction peut être étendue selon les besoins
}

function refreshPickups() {
    loadPickups();
    showNotification('info', 'Liste des enlèvements actualisée');
}

// Actions sur les enlèvements
function validatePickup(pickupId) {
    showConfirmModal(
        'Valider l\'enlèvement',
        'Êtes-vous sûr de vouloir valider cet enlèvement ? Cette action créera les expéditions avec le transporteur.',
        () => {
            performPickupAction(pickupId, 'validate', 'POST');
        }
    );
}

function refreshPickupStatus(pickupId) {
    performPickupAction(pickupId, 'refresh', 'POST');
}

function generateLabels(pickupId) {
    performPickupAction(pickupId, 'labels', 'POST', true); // true pour téléchargement
}

function generateManifest(pickupId) {
    performPickupAction(pickupId, 'manifest', 'POST', true); // true pour téléchargement
}

function deletePickup(pickupId) {
    showConfirmModal(
        'Supprimer l\'enlèvement',
        'Êtes-vous sûr de vouloir supprimer cet enlèvement ? Cette action est irréversible.',
        () => {
            performPickupAction(pickupId, '', 'DELETE');
        },
        'btn-danger'
    );
}

function performPickupAction(pickupId, action, method = 'POST', download = false) {
    let url = `/admin/delivery/pickups/${pickupId}`;
    if (action) {
        url += `/${action}`;
    }
    
    const btn = event?.target?.closest('button');
    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        // Restaurer le bouton après l'action
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, 3000);
    }
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (download && response.ok) {
            // Gérer le téléchargement
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
                
                if (method === 'DELETE') {
                    // Supprimer la ligne du tableau
                    const row = document.querySelector(`tr[data-pickup-id="${pickupId}"]`);
                    if (row) {
                        row.remove();
                    }
                } else {
                    // Recharger la liste
                    setTimeout(() => loadPickups(), 1500);
                }
            } else {
                showNotification('error', data.message);
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de l\'action');
    });
}

// Suivi global des expéditions
function trackAllShipments() {
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
            setTimeout(() => loadPickups(), 2000);
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

// Modal de confirmation
function showConfirmModal(title, message, callback, buttonClass = 'btn-primary') {
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalBody').textContent = message;
    
    const actionBtn = document.getElementById('confirmModalAction');
    actionBtn.className = `btn ${buttonClass}`;
    actionBtn.onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        callback();
    };
    
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
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

// Navigation vers le détail d'un enlèvement
function viewPickup(pickupId) {
    window.location.href = `/admin/delivery/pickups/${pickupId}`;
}
</script>
@endsection