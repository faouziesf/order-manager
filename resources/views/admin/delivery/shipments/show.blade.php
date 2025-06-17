@extends('layouts.admin')

@section('title', 'Expédition #' . $shipment->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.delivery.shipments') }}">Expéditions</a>
                            </li>
                            <li class="breadcrumb-item active">Expédition #{{ $shipment->id }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-shipping-fast text-primary me-2"></i>
                        Expédition #{{ $shipment->id }}
                        @php
                            $statusConfig = [
                                'created' => ['badge' => 'bg-primary', 'icon' => 'fas fa-plus-circle', 'label' => 'Créée'],
                                'validated' => ['badge' => 'bg-info', 'icon' => 'fas fa-check-circle', 'label' => 'Validée'],
                                'picked_up_by_carrier' => ['badge' => 'bg-warning', 'icon' => 'fas fa-truck', 'label' => 'Récupérée'],
                                'in_transit' => ['badge' => 'bg-info', 'icon' => 'fas fa-road', 'label' => 'En transit'],
                                'delivered' => ['badge' => 'bg-success', 'icon' => 'fas fa-check-circle', 'label' => 'Livrée'],
                                'in_return' => ['badge' => 'bg-warning', 'icon' => 'fas fa-undo', 'label' => 'En retour'],
                                'anomaly' => ['badge' => 'bg-danger', 'icon' => 'fas fa-exclamation-triangle', 'label' => 'Anomalie'],
                                'cancelled' => ['badge' => 'bg-secondary', 'icon' => 'fas fa-times-circle', 'label' => 'Annulée']
                            ];
                            $config = $statusConfig[$shipment->status] ?? ['badge' => 'bg-secondary', 'icon' => 'fas fa-question', 'label' => ucfirst($shipment->status)];
                        @endphp
                        <span class="badge {{ $config['badge'] }} ms-2">
                            <i class="{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                        </span>
                    </h1>
                    <p class="text-muted mb-0">
                        @if($shipment->pos_barcode)
                            Code de suivi: <code class="bg-light px-2 py-1 rounded">{{ $shipment->pos_barcode }}</code>
                        @endif
                        @if($shipment->order)
                            • Commande #{{ $shipment->order->id }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.delivery.shipments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    @if($shipment->pos_barcode)
                        <button type="button" class="btn btn-outline-primary" onclick="trackShipment()">
                            <i class="fas fa-sync-alt me-2"></i>Actualiser le suivi
                        </button>
                    @endif
                    @if($shipment->status !== 'delivered' && $shipment->status !== 'cancelled')
                        <button type="button" class="btn btn-success" onclick="markAsDelivered()">
                            <i class="fas fa-check me-2"></i>Marquer comme livré
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Informations principales -->
                <div class="col-lg-8">
                    <!-- Informations de l'expédition -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations de l'expédition
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">ID Expédition:</dt>
                                        <dd class="col-sm-7">#{{ $shipment->id }}</dd>

                                        <dt class="col-sm-5">Commande:</dt>
                                        <dd class="col-sm-7">
                                            @if($shipment->order)
                                                <a href="{{ route('admin.orders.show', $shipment->order) }}" target="_blank">
                                                    #{{ $shipment->order->id }}
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        @if($shipment->order_number)
                                            <dt class="col-sm-5">N° Commande:</dt>
                                            <dd class="col-sm-7">{{ $shipment->order_number }}</dd>
                                        @endif

                                        @if($shipment->pos_barcode)
                                            <dt class="col-sm-5">Code de suivi:</dt>
                                            <dd class="col-sm-7">
                                                <code class="bg-light px-2 py-1 rounded">{{ $shipment->pos_barcode }}</code>
                                                @if($shipment->tracking_url)
                                                    <br><a href="{{ $shipment->tracking_url }}" target="_blank" class="small">
                                                        <i class="fas fa-external-link-alt me-1"></i>Suivre en ligne
                                                    </a>
                                                @endif
                                            </dd>
                                        @endif

                                        @if($shipment->return_barcode)
                                            <dt class="col-sm-5">Code retour:</dt>
                                            <dd class="col-sm-7">
                                                <code class="bg-warning bg-opacity-25 px-2 py-1 rounded">{{ $shipment->return_barcode }}</code>
                                            </dd>
                                        @endif
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Transporteur:</dt>
                                        <dd class="col-sm-7">
                                            @if($shipment->pickup && $shipment->pickup->deliveryConfiguration)
                                                <div>
                                                    <span class="badge bg-info">{{ ucfirst($shipment->pickup->carrier_slug) }}</span>
                                                    <br><small class="text-muted">{{ $shipment->pickup->deliveryConfiguration->integration_name }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Enlèvement:</dt>
                                        <dd class="col-sm-7">
                                            @if($shipment->pickup)
                                                <a href="{{ route('admin.delivery.pickups.show', $shipment->pickup) }}">
                                                    Enlèvement #{{ $shipment->pickup_id }}
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Valeur:</dt>
                                        <dd class="col-sm-7">
                                            <strong class="text-primary">{{ number_format($shipment->value ?? 0, 3) }} DT</strong>
                                            @if($shipment->cod_amount && $shipment->cod_amount != $shipment->value)
                                                <br><small class="text-muted">COD: {{ number_format($shipment->cod_amount, 3) }} DT</small>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Créé le:</dt>
                                        <dd class="col-sm-7">{{ $shipment->created_at->format('d/m/Y H:i') }}</dd>

                                        @if($shipment->delivered_at)
                                            <dt class="col-sm-5">Livré le:</dt>
                                            <dd class="col-sm-7">
                                                <span class="text-success fw-bold">{{ $shipment->delivered_at->format('d/m/Y H:i') }}</span>
                                            </dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations client -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                Informations du client
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Nom:</dt>
                                        <dd class="col-sm-8">{{ $shipment->customer_name ?: 'N/A' }}</dd>

                                        <dt class="col-sm-4">Téléphone:</dt>
                                        <dd class="col-sm-8">
                                            @if($shipment->customer_phone)
                                                <span class="font-monospace">{{ $shipment->customer_phone }}</span>
                                                <a href="tel:{{ $shipment->customer_phone }}" class="btn btn-sm btn-outline-primary ms-2">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Adresse:</dt>
                                        <dd class="col-sm-8">
                                            @if($shipment->customer_address)
                                                {{ $shipment->customer_address }}
                                                @if($shipment->customer_city)
                                                    <br><small class="text-muted">{{ $shipment->customer_city }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historique de suivi -->
                    @if(isset($trackingHistory) && count($trackingHistory) > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-route me-2"></i>
                                    Historique de suivi
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @foreach($trackingHistory as $entry)
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                @php
                                                    $markerClass = match($entry['status']) {
                                                        'delivered' => 'bg-success',
                                                        'in_transit', 'picked_up_by_carrier' => 'bg-info',
                                                        'anomaly' => 'bg-danger',
                                                        'in_return' => 'bg-warning',
                                                        default => 'bg-primary'
                                                    };
                                                @endphp
                                                <div class="timeline-marker-dot {{ $markerClass }}"></div>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">{{ $entry['label'] }}</h6>
                                                @if($entry['description'])
                                                    <p class="text-muted mb-1">{{ $entry['description'] }}</p>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $entry['created_at']->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions et informations secondaires -->
                <div class="col-lg-4">
                    <!-- Actions rapides -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Actions rapides
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($shipment->pos_barcode)
                                    <button type="button" class="btn btn-outline-primary" onclick="trackShipment()">
                                        <i class="fas fa-sync-alt me-2"></i>Actualiser le suivi
                                    </button>
                                @endif

                                @if($shipment->status !== 'delivered' && $shipment->status !== 'cancelled')
                                    <button type="button" class="btn btn-success" onclick="markAsDelivered()">
                                        <i class="fas fa-check me-2"></i>Marquer comme livré
                                    </button>
                                @endif

                                @if($shipment->pickup)
                                    <a href="{{ route('admin.delivery.pickups.show', $shipment->pickup) }}" class="btn btn-outline-info">
                                        <i class="fas fa-warehouse me-2"></i>Voir l'enlèvement
                                    </a>
                                @endif

                                @if($shipment->order)
                                    <a href="{{ route('admin.orders.show', $shipment->order) }}" class="btn btn-outline-secondary" target="_blank">
                                        <i class="fas fa-shopping-cart me-2"></i>Voir la commande
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Résumé -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Résumé
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-12 mb-3">
                                    <h4 class="text-primary mb-1">{{ number_format($shipment->value ?? 0, 3) }}</h4>
                                    <small class="text-muted">Valeur (DT)</small>
                                </div>
                            </div>
                            
                            @if($shipment->created_at && $shipment->delivered_at)
                                <hr>
                                <div class="text-center">
                                    <h5 class="text-info mb-1">{{ $shipment->created_at->diffInDays($shipment->delivered_at) }}</h5>
                                    <small class="text-muted">Jours de livraison</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informations techniques -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                Informations techniques
                            </h6>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <dt class="small">ID interne:</dt>
                                <dd class="small text-muted mb-2">#{{ $shipment->id }}</dd>

                                @if($shipment->carrier_last_status_update)
                                    <dt class="small">Dernière MAJ transporteur:</dt>
                                    <dd class="small text-muted mb-2">{{ $shipment->carrier_last_status_update->format('d/m/Y H:i') }}</dd>
                                @endif

                                <dt class="small">Créé le:</dt>
                                <dd class="small text-muted mb-2">{{ $shipment->created_at->format('d/m/Y H:i') }}</dd>

                                <dt class="small">Modifié le:</dt>
                                <dd class="small text-muted mb-0">{{ $shipment->updated_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 20px;
    height: calc(100% - 10px);
    width: 2px;
    background: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
}

.timeline-marker-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endsection

@section('scripts')
<script>
const shipmentId = {{ $shipment->id }};

function trackShipment() {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualisation...';
    btn.disabled = true;
    
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
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors du suivi');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function markAsDelivered() {
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
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur lors de la mise à jour');
    });
}

function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

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