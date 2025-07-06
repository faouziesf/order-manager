@extends('layouts.admin')

@section('title', 'Expédition #' . $shipment->id)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.delivery.shipments') }}" class="btn btn-outline-secondary btn-sm mr-3">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <div>
                    <h1 class="h3 mb-0">Expédition #{{ $shipment->id }}</h1>
                    <p class="text-muted mb-0">
                        @if($shipment->pos_barcode)
                            Code de suivi: <code>{{ $shipment->pos_barcode }}</code>
                        @else
                            Aucun code de suivi généré
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="badge {{ $shipment->status_badge_class }} badge-lg">
                {{ $shipment->status_label }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Informations de l'expédition -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Informations de l'Expédition
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>ID Expédition:</strong>
                        </div>
                        <div class="col-sm-6">
                            #{{ $shipment->id }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Statut:</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="badge {{ $shipment->status_badge_class }}">
                                {{ $shipment->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Code de suivi:</strong>
                        </div>
                        <div class="col-sm-6">
                            @if($shipment->pos_barcode)
                                <code>{{ $shipment->pos_barcode }}</code>
                                @if($shipment->tracking_url)
                                    <br>
                                    <a href="{{ $shipment->tracking_url }}" target="_blank" 
                                       class="btn btn-outline-info btn-sm mt-1">
                                        <i class="fas fa-external-link-alt"></i> Suivre en ligne
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">Non généré</span>
                            @endif
                        </div>
                    </div>

                    @if($shipment->return_barcode)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Code retour:</strong>
                        </div>
                        <div class="col-sm-6">
                            <code>{{ $shipment->return_barcode }}</code>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Transporteur:</strong>
                        </div>
                        <div class="col-sm-6">
                            <i class="fas fa-truck text-primary"></i> Jax Delivery
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Poids:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $shipment->weight ?? 'N/A' }} kg
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Valeur:</strong>
                        </div>
                        <div class="col-sm-6">
                            <strong class="text-success">
                                {{ number_format($shipment->value ?? 0, 3) }} TND
                            </strong>
                        </div>
                    </div>

                    @if($shipment->cod_amount)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Montant COD:</strong>
                        </div>
                        <div class="col-sm-6">
                            <strong class="text-warning">
                                {{ number_format($shipment->cod_amount, 3) }} TND
                            </strong>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Nb. pièces:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $shipment->nb_pieces ?? 1 }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Créé le:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $shipment->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($shipment->delivered_at)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Livré le:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $shipment->delivered_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @endif

                    @if($shipment->carrier_last_status_update)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Dernière MAJ:</strong>
                        </div>
                        <div class="col-sm-6">
                            {{ $shipment->carrier_last_status_update->format('d/m/Y H:i') }}
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
                    @if($shipment->is_active)
                        <button type="button" class="btn btn-warning btn-block mb-2" 
                                onclick="trackShipment({{ $shipment->id }})">
                            <i class="fas fa-sync"></i> Actualiser le Statut
                        </button>
                        <button type="button" class="btn btn-success btn-block mb-2" 
                                onclick="markAsDelivered({{ $shipment->id }})">
                            <i class="fas fa-check"></i> Marquer comme Livré
                        </button>
                    @endif

                    @if($shipment->order)
                        <a href="{{ route('admin.orders.show', $shipment->order) }}" 
                           class="btn btn-info btn-block mb-2">
                            <i class="fas fa-eye"></i> Voir la Commande
                        </a>
                    @endif

                    @if($shipment->pickup)
                        <a href="{{ route('admin.delivery.pickups.show', $shipment->pickup) }}" 
                           class="btn btn-outline-info btn-block">
                            <i class="fas fa-warehouse"></i> Voir l'Enlèvement
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations de la commande et du client -->
        <div class="col-md-8">
            <!-- Informations du client -->
            @if($shipment->order)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user"></i> Informations de la Commande #{{ $shipment->order->id }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Client</h6>
                                <p>
                                    <strong>{{ $shipment->order->customer_name ?: 'N/A' }}</strong><br>
                                    @if($shipment->order->customer_phone)
                                        <i class="fas fa-phone text-primary"></i> {{ $shipment->order->customer_phone }}<br>
                                    @endif
                                    @if($shipment->order->customer_phone_2)
                                        <i class="fas fa-phone text-muted"></i> {{ $shipment->order->customer_phone_2 }}<br>
                                    @endif
                                    @if($shipment->order->customer_email)
                                        <i class="fas fa-envelope text-primary"></i> {{ $shipment->order->customer_email }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Commande</h6>
                                <p>
                                    <strong>Statut:</strong> 
                                    <span class="badge badge-info">{{ $shipment->order->status }}</span><br>
                                    <strong>Total:</strong> 
                                    <span class="text-success">{{ number_format($shipment->order->total_price, 3) }} TND</span><br>
                                    <strong>Créée le:</strong> {{ $shipment->order->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Adresse de livraison -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marker-alt"></i> Adresse de Livraison
                    </h6>
                </div>
                <div class="card-body">
                    @if($shipment->recipient_info)
                        @php $recipient = $shipment->recipient_info; @endphp
                        <div class="alert alert-light">
                            <h6><i class="fas fa-user"></i> {{ $recipient['name'] ?? $shipment->customer_name }}</h6>
                            @if(isset($recipient['phone']))
                                <p><i class="fas fa-phone text-primary"></i> {{ $recipient['phone'] }}</p>
                            @endif
                            @if(isset($recipient['address']))
                                <p><i class="fas fa-map-marker-alt text-danger"></i> {{ $recipient['address'] }}</p>
                            @endif
                            @if(isset($recipient['city']) || isset($recipient['governorate']))
                                <p>
                                    <i class="fas fa-city text-info"></i> 
                                    {{ $recipient['city'] ?? '' }}
                                    @if(isset($recipient['governorate']))
                                        - {{ $recipient['governorate'] }}
                                    @endif
                                </p>
                            @endif
                            @if(isset($recipient['email']))
                                <p><i class="fas fa-envelope text-primary"></i> {{ $recipient['email'] }}</p>
                            @endif
                        </div>
                    @elseif($shipment->order)
                        <div class="alert alert-light">
                            <h6><i class="fas fa-user"></i> {{ $shipment->order->customer_name ?: 'N/A' }}</h6>
                            @if($shipment->order->customer_phone)
                                <p><i class="fas fa-phone text-primary"></i> {{ $shipment->order->customer_phone }}</p>
                            @endif
                            @if($shipment->order->customer_address)
                                <p><i class="fas fa-map-marker-alt text-danger"></i> {{ $shipment->order->customer_address }}</p>
                            @endif
                            @if($shipment->order->customer_city || $shipment->order->customer_governorate)
                                <p>
                                    <i class="fas fa-city text-info"></i> 
                                    {{ $shipment->order->customer_city ?? '' }}
                                    @if($shipment->order->customer_governorate)
                                        - {{ $shipment->order->customer_governorate }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune information d'adresse disponible
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historique de suivi -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Historique de Suivi
                    </h6>
                </div>
                <div class="card-body">
                    @if($shipment->order)
                        @php 
                            $trackingHistory = $shipment->order->history()
                                ->where('tracking_number', $shipment->pos_barcode)
                                ->orWhere('action', 'LIKE', '%shipment%')
                                ->orWhere('action', 'LIKE', '%pickup%')
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp
                        
                        @if($trackingHistory->count() > 0)
                            <div class="timeline">
                                @foreach($trackingHistory as $entry)
                                <div class="timeline-item">
                                    <div class="timeline-marker 
                                        @if($entry->action === 'livraison') bg-success
                                        @elseif(str_contains($entry->action, 'pickup')) bg-primary
                                        @elseif(str_contains($entry->action, 'shipment')) bg-info
                                        @else bg-secondary
                                        @endif
                                    "></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ ucfirst(str_replace('_', ' ', $entry->action)) }}</h6>
                                        <p class="timeline-description">{{ $entry->notes }}</p>
                                        <small class="text-muted">
                                            {{ $entry->created_at->format('d/m/Y H:i') }}
                                            @if($entry->carrier_status_label)
                                                - Transporteur: {{ $entry->carrier_status_label }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-gray-300 mb-3"></i>
                                <p class="text-muted">Aucun historique de suivi disponible</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune commande associée à cette expédition
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    color: #007bff;
}

.timeline-description {
    margin-bottom: 5px;
    color: #6c757d;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
// Suivre l'expédition
function trackShipment(shipmentId) {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
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
</script>
@endpush