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
                    <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvel enlèvement
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
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
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['validated'] }}</h4>
                            <p class="text-muted mb-0">Validés</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-truck fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ $stats['picked_up'] }}</h4>
                            <p class="text-muted mb-0">Collectés</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des enlèvements -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-warehouse me-2"></i>
                        Liste des enlèvements
                    </h5>
                </div>
                <div class="card-body">
                    @if($pickups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Transporteur</th>
                                        <th>Adresse d'enlèvement</th>
                                        <th>Expéditions</th>
                                        <th>Statut</th>
                                        <th>Date de création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pickups as $pickup)
                                    <tr>
                                        <td>
                                            <strong>#{{ $pickup->id }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-truck me-2 text-primary"></i>
                                                <div>
                                                    <strong>{{ ucfirst($pickup->carrier_slug) }}</strong>
                                                    @if($pickup->deliveryConfiguration)
                                                        <br><small class="text-muted">{{ $pickup->deliveryConfiguration->integration_name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($pickup->pickupAddress)
                                                <strong>{{ $pickup->pickupAddress->name }}</strong>
                                                <br><small class="text-muted">{{ $pickup->pickupAddress->contact_name }}</small>
                                            @else
                                                <span class="text-muted">Adresse par défaut</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $pickup->shipments_count }}</span>
                                            expédition(s)
                                        </td>
                                        <td>
                                            @switch($pickup->status)
                                                @case('draft')
                                                    <span class="badge bg-warning">Brouillon</span>
                                                    @break
                                                @case('validated')
                                                    <span class="badge bg-success">Validé</span>
                                                    @break
                                                @case('picked_up')
                                                    <span class="badge bg-info">Collecté</span>
                                                    @break
                                                @case('problem')
                                                    <span class="badge bg-danger">Problème</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $pickup->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <small>{{ $pickup->created_at->format('d/m/Y H:i') }}</small>
                                            @if($pickup->pickup_date)
                                                <br><small class="text-muted">Prévu: {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Voir les détails -->
                                                <a href="{{ route('admin.delivery.pickups.show', $pickup) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($pickup->status === 'draft')
                                                    <!-- Valider si brouillon -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success" 
                                                            onclick="validatePickup({{ $pickup->id }})"
                                                            title="Valider l'enlèvement">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <!-- Supprimer si brouillon -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="deletePickup({{ $pickup->id }})"
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($pickup->status === 'validated')
                                                    <!-- Générer étiquettes -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info" 
                                                            onclick="generateLabels({{ $pickup->id }})"
                                                            title="Générer les étiquettes">
                                                        <i class="fas fa-tags"></i>
                                                    </button>
                                                    <!-- Rafraîchir statut -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary" 
                                                            onclick="refreshStatus({{ $pickup->id }})"
                                                            title="Rafraîchir le statut">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($pickups->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $pickups->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun enlèvement créé</h5>
                            <p class="text-muted mb-4">Commencez par créer votre premier enlèvement</p>
                            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer un enlèvement
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function validatePickup(pickupId) {
    if (!confirm('Êtes-vous sûr de vouloir valider cet enlèvement ?')) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/pickups/${pickupId}/validate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la validation');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function deletePickup(pickupId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet enlèvement ?')) {
        return;
    }
    
    fetch(`/admin/delivery/pickups/${pickupId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la suppression');
    });
}

function generateLabels(pickupId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/pickups/${pickupId}/labels`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la génération');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function refreshStatus(pickupId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/pickups/${pickupId}/refresh`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
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