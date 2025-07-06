@extends('layouts.admin')

@section('title', 'Gestion des Expéditions')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Gestion des Expéditions</h1>
            <p class="text-muted">Suivez et gérez vos expéditions Jax Delivery</p>
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
                                Total Expéditions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $shipments->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
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
                                En Transit
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $shipments->where('status', 'in_transit')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                                Livrées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $shipments->where('status', 'delivered')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Problèmes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $shipments->whereIn('status', ['anomaly', 'in_return', 'cancelled'])->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                <div class="col-md-2">
                    <label for="status">Statut</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="created" {{ request('status') === 'created' ? 'selected' : '' }}>Créé</option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validé</option>
                        <option value="picked_up_by_carrier" {{ request('status') === 'picked_up_by_carrier' ? 'selected' : '' }}>Récupéré</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>En transit</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livré</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        <option value="in_return" {{ request('status') === 'in_return' ? 'selected' : '' }}>En retour</option>
                        <option value="anomaly" {{ request('status') === 'anomaly' ? 'selected' : '' }}>Anomalie</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tracking_code">Code de suivi</label>
                    <input type="text" name="tracking_code" id="tracking_code" class="form-control" 
                           value="{{ request('tracking_code') }}" placeholder="JAX_...">
                </div>
                <div class="col-md-2">
                    <label for="order_number">N° Commande</label>
                    <input type="text" name="order_number" id="order_number" class="form-control" 
                           value="{{ request('order_number') }}" placeholder="#123">
                </div>
                <div class="col-md-2">
                    <label for="customer_name">Client</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" 
                           value="{{ request('customer_name') }}" placeholder="Nom du client">
                </div>
                <div class="col-md-2">
                    <label for="date_from">Date (du)</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.delivery.shipments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des expéditions -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Expéditions</h6>
            <div>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="refreshAllStatus()">
                    <i class="fas fa-sync"></i> Actualiser Tous
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($shipments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Code Suivi</th>
                                <th>Commande</th>
                                <th>Client</th>
                                <th>Statut</th>
                                <th>Enlèvement</th>
                                <th>Montant</th>
                                <th>Date Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                            <tr>
                                <td>
                                    @if($shipment->pos_barcode)
                                        <code>{{ $shipment->pos_barcode }}</code>
                                        @if($shipment->tracking_url)
                                            <br>
                                            <a href="{{ $shipment->tracking_url }}" target="_blank" 
                                               class="btn btn-outline-info btn-xs">
                                                <i class="fas fa-external-link-alt"></i> Suivre
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>#{{ $shipment->order->id ?? $shipment->order_number }}</strong>
                                        @if($shipment->order)
                                            <br>
                                            <small class="text-muted">
                                                Créée le {{ $shipment->order->created_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $shipment->customer_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-phone"></i> {{ $shipment->customer_phone }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $shipment->status_badge_class }}">
                                        {{ $shipment->status_label }}
                                    </span>
                                    @if($shipment->days_in_transit)
                                        <br>
                                        <small class="text-muted">{{ $shipment->days_in_transit }} jour(s)</small>
                                    @endif
                                    @if($shipment->carrier_last_status_update)
                                        <br>
                                        <small class="text-muted">
                                            MAJ: {{ $shipment->carrier_last_status_update->format('d/m H:i') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($shipment->pickup)
                                        <a href="{{ route('admin.delivery.pickups.show', $shipment->pickup) }}" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-warehouse"></i> #{{ $shipment->pickup->id }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            {{ $shipment->pickup->status_label }}
                                        </small>
                                    @else
                                        <span class="text-muted">Non assigné</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-success">
                                        {{ number_format($shipment->value ?? 0, 3) }} TND
                                    </strong>
                                    @if($shipment->cod_amount && $shipment->cod_amount != $shipment->value)
                                        <br>
                                        <small class="text-muted">
                                            COD: {{ number_format($shipment->cod_amount, 3) }} TND
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        {{ $shipment->created_at->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ $shipment->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($shipment->order)
                                            <a href="{{ route('admin.orders.show', $shipment->order) }}" 
                                               class="btn btn-sm btn-outline-info" title="Voir commande">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        
                                        @if($shipment->is_active)
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="trackShipment({{ $shipment->id }})" title="Actualiser">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="markAsDelivered({{ $shipment->id }})" 
                                                    title="Marquer comme livré">
                                                <i class="fas fa-check"></i>
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
                @if($shipments->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $shipments->firstItem() }} à {{ $shipments->lastItem() }} 
                                sur {{ $shipments->total() }} expéditions
                            </small>
                        </div>
                        <div>
                            {{ $shipments->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shipping-fast fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucune expédition trouvée</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['status', 'tracking_code', 'order_number', 'customer_name', 'date_from']))
                            Aucune expédition ne correspond à vos critères.
                        @else
                            Vous n'avez pas encore d'expédition.
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
// Suivre une expédition spécifique
function trackShipment(shipmentId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`/admin/delivery/shipments/${shipmentId}/track`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message || 'Statut mis à jour');
            location.reload();
        } else {
            toastr.error(data.message || 'Erreur lors du suivi');
        }
    })
    .catch(error => {
        toastr.error('Erreur lors du suivi');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Marquer comme livré
function markAsDelivered(shipmentId) {
    const notes = prompt('Notes de livraison (optionnel):');
    if (notes !== null) { // L'utilisateur n'a pas annulé
        fetch(`/admin/delivery/shipments/${shipmentId}/mark-delivered`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notes: notes })
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
            toastr.error('Erreur lors de la mise à jour');
            console.error('Error:', error);
        });
    }
}

// Actualiser tous les statuts
function refreshAllStatus() {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
    btn.disabled = true;
    
    fetch('/admin/delivery/api/track-all', {
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