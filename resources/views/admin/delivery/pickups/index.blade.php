@extends('layouts.admin')

@section('title', 'Gestion des Enlèvements')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Gestion des Enlèvements</h1>
            <p class="text-muted">Suivez et gérez vos enlèvements Jax Delivery</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel Enlèvement
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-secondary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Brouillons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['draft'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Validés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['validated'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Récupérés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['picked_up'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label for="status">Statut</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validé</option>
                        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Récupéré</option>
                        <option value="problem" {{ request('status') === 'problem' ? 'selected' : '' }}>Problème</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from">Date de création (du)</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to">Date de création (au)</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des enlèvements -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Enlèvements</h6>
        </div>
        <div class="card-body">
            @if($pickups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Configuration</th>
                                <th>Statut</th>
                                <th>Expéditions</th>
                                <th>Date Création</th>
                                <th>Date Validation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pickups as $pickup)
                            <tr>
                                <td>
                                    <strong>#{{ $pickup->id }}</strong>
                                    @if($pickup->pickup_date)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> {{ $pickup->pickup_date->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $pickup->deliveryConfiguration->integration_name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-truck"></i> Jax Delivery
                                            ({{ ucfirst($pickup->deliveryConfiguration->environment ?? 'N/A') }})
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $pickup->status_badge_class }}">
                                        {{ $pickup->status_label }}
                                    </span>
                                    @if($pickup->status === 'problem')
                                        <br>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            {{ $pickup->days_in_current_status }} jours
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <strong>{{ $pickup->shipment_count }}</strong> expédition(s)
                                        </div>
                                        @if($pickup->shipment_count > 0 && $pickup->status !== 'draft')
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $pickup->progress_percentage }}%">
                                                    {{ $pickup->progress_percentage }}%
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if($pickup->total_value > 0)
                                        <small class="text-muted">
                                            Valeur: {{ number_format($pickup->total_value, 3) }} TND
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        {{ $pickup->created_at->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ $pickup->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($pickup->validated_at)
                                        <div>
                                            {{ $pickup->validated_at->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">{{ $pickup->validated_at->format('H:i') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.delivery.pickups.show', $pickup) }}" 
                                           class="btn btn-sm btn-outline-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($pickup->status === 'draft')
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="validatePickup({{ $pickup->id }})" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePickup({{ $pickup->id }})" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif

                                        @if($pickup->status === 'validated')
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="refreshStatus({{ $pickup->id }})" title="Actualiser">
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
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $pickups->firstItem() }} à {{ $pickups->lastItem() }} 
                                sur {{ $pickups->total() }} enlèvements
                            </small>
                        </div>
                        <div>
                            {{ $pickups->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-warehouse fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucun enlèvement trouvé</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['status', 'date_from', 'date_to']))
                            Aucun enlèvement ne correspond à vos critères.
                        @else
                            Vous n'avez pas encore créé d'enlèvement.
                        @endif
                    </p>
                    <a href="{{ route('admin.delivery.preparation') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer un Enlèvement
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Valider un enlèvement
function validatePickup(pickupId) {
    if (confirm('Êtes-vous sûr de vouloir valider cet enlèvement ?\n\nCela créera les expéditions chez Jax Delivery.')) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(`/admin/delivery/pickups/${pickupId}/validate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        })
        .catch(error => {
            toastr.error('Erreur lors de la validation');
            console.error('Error:', error);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

// Supprimer un enlèvement
function deletePickup(pickupId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet enlèvement ?\n\nToutes les expéditions associées seront également supprimées.')) {
        fetch(`/admin/delivery/pickups/${pickupId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('Erreur lors de la suppression');
            console.error('Error:', error);
        });
    }
}

// Actualiser le statut
function refreshStatus(pickupId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/pickups/${pickupId}/refresh`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            location.reload();
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('Erreur lors de l\'actualisation');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}
</script>
@endpush