@extends('layouts.admin')

@section('title', 'Enlèvement #' . $pickup->id)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.delivery.pickups') }}" class="btn btn-outline-secondary btn-sm mr-3">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <div>
                    <h1 class="h3 mb-0">Enlèvement #{{ $pickup->id }}</h1>
                    <p class="text-muted mb-0">
                        Créé le {{ $pickup->created_at->format('d/m/Y à H:i') }}
                        @if($pickup->pickup_date)
                            • Enlèvement prévu le {{ $pickup->pickup_date->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge {{ $pickup->status_badge_class }} badge-lg">
                {{ $pickup->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Informations de l'enlèvement -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Statut:</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="badge {{ $pickup->status_badge_class }}">
                                {{ $pickup->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Configuration:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $pickup->deliveryConfiguration->integration_name ?? 'N/A' }}
                            <br>
                            <small class="text-muted">
                                Jax Delivery ({{ ucfirst($pickup->deliveryConfiguration->environment ?? 'N/A') }})
                            </small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Expéditions:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $pickup->shipment_count }} expédition(s)
                        </div>
                    </div>

                    @if($pickup->total_value > 0)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Valeur totale:</strong>
                        </div>
                        <div class="col-sm-6">
                            <strong class="text-success">{{ number_format($pickup->total_value, 3) }} TND</strong>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Créé le:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $pickup->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($pickup->validated_at)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Validé le:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $pickup->validated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endif

                    @if($pickup->pickup_date)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Date prévue:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $pickup->pickup_date->format('d/m/Y') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i> Actions
                    </h6>
                </div>
                <div class="card-body">
                    @if($pickup->status === 'draft')
                        <button type="button" class="btn btn-success btn-block mb-2" 
                                onclick="validatePickup({{ $pickup->id }})">
                            <i class="fas fa-check"></i> Valider l'Enlèvement
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-block" 
                                onclick="deletePickup({{ $pickup->id }})">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    @endif

                    @if($pickup->status === 'validated')
                        <button type="button" class="btn btn-primary btn-block mb-2" 
                                onclick="refreshStatus({{ $pickup->id }})">
                            <i class="fas fa-sync"></i> Actualiser les Statuts
                        </button>
                    @endif

                    @if(in_array($pickup->status, ['validated', 'picked_up']))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Jax Delivery</strong> gère automatiquement les étiquettes et manifestes.
                            Consultez votre espace Jax pour les télécharger.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Liste des expéditions -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shipping-fast"></i> Expéditions ({{ $pickup->shipments->count() }})
                    </h6>
                    @if($pickup->shipments->count() > 0 && $pickup->status !== 'draft')
                        <div class="progress" style="width: 200px; height: 25px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $pickup->progress_percentage }}%">
                                {{ $pickup->progress_percentage }}% livrées
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($pickup->shipments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Client</th>
                                        <th>Code Suivi</th>
                                        <th>Statut</th>
                                        <th>Montant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pickup->shipments as $shipment)
                                    <tr>
                                        <td>
                                            <strong>#{{ $shipment->order->id }}</strong>
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
                                            @if($shipment->pos_barcode && $shipment->pos_barcode !== 'PENDING_' . substr($shipment->pos_barcode, 8))
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
                                            <span class="badge {{ $shipment->status_badge_class }}">
                                                {{ $shipment->status_label }}
                                            </span>
                                            @if($shipment->days_in_transit)
                                                <br>
                                                <small class="text-muted">{{ $shipment->days_in_transit }} jour(s)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($shipment->order->total_price, 3) }} TND
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.orders.show', $shipment->order) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Voir commande">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($shipment->is_active)
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
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted">Aucune expédition dans cet enlèvement</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Valider l'enlèvement
function validatePickup(pickupId) {
    if (confirm('Êtes-vous sûr de vouloir valider cet enlèvement ?\n\nCela créera les expéditions chez Jax Delivery et vous ne pourrez plus le modifier.')) {
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validation...';
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

// Supprimer l'enlèvement
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
                window.location.href = '{{ route("admin.delivery.pickups") }}';
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

// Actualiser les statuts
function refreshStatus(pickupId) {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
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
</script>
@endpush