@php
    // Déterminer si nous utilisons Alpine.js ou des données PHP
    $useAlpine = isset($shipment) && is_string($shipment);
@endphp

<div class="tracking-history-container">
    @if($useAlpine)
        {{-- Version Alpine.js --}}
        <div x-show="{{ $shipment }}?.tracking_history && {{ $shipment }}.tracking_history.length > 0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-primary mb-0">
                    <i class="fas fa-route me-1"></i>
                    Historique de Suivi
                </h6>
                <button class="btn btn-sm btn-outline-primary" 
                        @click="refreshTracking({{ $shipment }}.id)"
                        x-show="{{ $shipment }}?.pos_barcode">
                    <i class="fas fa-sync me-1"></i>
                    Actualiser
                </button>
            </div>

            <!-- Timeline de suivi -->
            <div class="tracking-timeline">
                <template x-for="(event, index) in {{ $shipment }}?.tracking_history || []" :key="event.id || index">
                    <div class="timeline-item" :class="{ 'timeline-item-current': index === 0 }">
                        <div class="timeline-marker" :class="getTimelineMarkerClass(event.status, index === 0)">
                            <i :class="getTrackingIcon(event.status)"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" x-text="event.label || event.status_label"></h6>
                                    <p class="text-muted mb-1" x-show="event.location" x-text="event.location"></p>
                                    <p class="mb-0 small" x-show="event.notes" x-text="event.notes"></p>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted" x-text="formatTrackingDate(event.timestamp || event.created_at)"></small>
                                    <br>
                                    <span class="badge bg-secondary small" x-text="event.status"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Message si aucun historique -->
        <div x-show="!{{ $shipment }}?.tracking_history || {{ $shipment }}.tracking_history.length === 0" 
             class="text-center py-4">
            <i class="fas fa-route fa-3x text-muted mb-3"></i>
            <h6 class="text-muted">Aucun historique de suivi</h6>
            <p class="text-muted mb-0">
                <span x-show="!{{ $shipment }}?.pos_barcode">Numéro de suivi non encore assigné</span>
                <span x-show="{{ $shipment }}?.pos_barcode">Historique en cours de récupération</span>
            </p>
            <button x-show="{{ $shipment }}?.pos_barcode" 
                    class="btn btn-outline-primary btn-sm mt-2"
                    @click="refreshTracking({{ $shipment }}.id)">
                <i class="fas fa-sync me-1"></i>
                Récupérer l'historique
            </button>
        </div>

        <!-- Informations de suivi actuelles -->
        <div x-show="{{ $shipment }}?.pos_barcode" class="mt-4">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Informations de Suivi
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small>
                                <strong>Numéro de suivi:</strong><br>
                                <code x-text="{{ $shipment }}?.pos_barcode"></code>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small>
                                <strong>Dernière vérification:</strong><br>
                                <span x-text="formatTrackingDate({{ $shipment }}?.carrier_last_status_update)"></span>
                            </small>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>
                            <strong>Lien de suivi:</strong><br>
                            <a :href="getTrackingUrl({{ $shipment }})" 
                               target="_blank" 
                               class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Suivre sur le site du transporteur
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Version PHP classique --}}
        @if($shipment && $shipment->tracking_history && count($shipment->tracking_history) > 0)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-primary mb-0">
                    <i class="fas fa-route me-1"></i>
                    Historique de Suivi
                </h6>
            </div>

            <div class="tracking-timeline">
                @foreach($shipment->tracking_history as $index => $event)
                    <div class="timeline-item {{ $index === 0 ? 'timeline-item-current' : '' }}">
                        <div class="timeline-marker {{ $index === 0 ? 'timeline-marker-current' : '' }}">
                            <i class="{{ $this->getTrackingIconClass($event['status']) }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $event['label'] ?? $event['status_label'] ?? 'Mise à jour' }}</h6>
                                    @if(!empty($event['location']))
                                        <p class="text-muted mb-1">{{ $event['location'] }}</p>
                                    @endif
                                    @if(!empty($event['notes']))
                                        <p class="mb-0 small">{{ $event['notes'] }}</p>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($event['timestamp'] ?? $event['created_at'])->format('d/m/Y H:i') }}</small>
                                    <br>
                                    <span class="badge bg-secondary small">{{ $event['status'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-route fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Aucun historique de suivi</h6>
                <p class="text-muted mb-0">
                    @if(!$shipment->pos_barcode)
                        Numéro de suivi non encore assigné
                    @else
                        Historique en cours de récupération
                    @endif
                </p>
            </div>
        @endif

        @if($shipment && $shipment->pos_barcode)
            <div class="mt-4">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Informations de Suivi
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>Numéro de suivi:</strong><br>
                                    <code>{{ $shipment->pos_barcode }}</code>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Dernière vérification:</strong><br>
                                    {{ $shipment->carrier_last_status_update ? $shipment->carrier_last_status_update->format('d/m/Y H:i') : 'Non vérifiée' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@push('styles')
<style>
.tracking-history-container {
    max-height: 500px;
    overflow-y: auto;
}

.tracking-timeline {
    position: relative;
    padding-left: 30px;
}

.tracking-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #6c757d);
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #6c757d;
    border: 3px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 2;
}

.timeline-marker i {
    color: #fff;
    font-size: 12px;
}

.timeline-marker-current,
.timeline-item-current .timeline-marker {
    background-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.25);
    animation: pulse-current 2s infinite;
}

.timeline-marker.marker-success {
    background-color: #28a745;
}

.timeline-marker.marker-warning {
    background-color: #ffc107;
}

.timeline-marker.marker-danger {
    background-color: #dc3545;
}

.timeline-marker.marker-info {
    background-color: #17a2b8;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin-left: 15px;
    position: relative;
}

.timeline-item-current .timeline-content {
    border-left-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}

.timeline-content::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 15px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 8px 0;
    border-color: transparent #f8f9fa transparent transparent;
}

.timeline-item-current .timeline-content::before {
    border-right-color: rgba(0, 123, 255, 0.05);
}

@keyframes pulse-current {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .tracking-timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -15px;
        width: 24px;
        height: 24px;
    }
    
    .timeline-content {
        margin-left: 10px;
        padding: 10px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Fonctions utilitaires pour le tracking (à intégrer dans le composant Alpine principal)
window.trackingHelpers = {
    getTrackingIcon(status) {
        const icons = {
            'created': 'fas fa-plus',
            'validated': 'fas fa-check',
            'picked_up_by_carrier': 'fas fa-truck-pickup',
            'in_transit': 'fas fa-truck-moving',
            'out_for_delivery': 'fas fa-door-open',
            'delivery_attempted': 'fas fa-bell',
            'delivered': 'fas fa-check-circle',
            'failed': 'fas fa-times',
            'returned': 'fas fa-undo',
            'cancelled': 'fas fa-ban',
            'anomaly': 'fas fa-exclamation-triangle'
        };
        return icons[status] || 'fas fa-info-circle';
    },

    getTimelineMarkerClass(status, isCurrent) {
        if (isCurrent) return 'timeline-marker-current';
        
        const classes = {
            'delivered': 'marker-success',
            'in_transit': 'marker-info',
            'out_for_delivery': 'marker-warning',
            'failed': 'marker-danger',
            'returned': 'marker-warning',
            'cancelled': 'marker-danger',
            'anomaly': 'marker-danger'
        };
        return classes[status] || '';
    },

    formatTrackingDate(dateString) {
        if (!dateString) return 'Date inconnue';
        
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) {
                return `Il y a ${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''}`;
            } else if (diffInMinutes < 1440) {
                const hours = Math.floor(diffInMinutes / 60);
                return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
            } else {
                return date.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        } catch (e) {
            return dateString;
        }
    },

    getTrackingUrl(shipment) {
        if (!shipment?.pos_barcode) return '#';
        
        const trackingNumber = shipment.pos_barcode;
        
        // URLs de suivi selon le transporteur
        switch (shipment.carrier_slug) {
            case 'jax_delivery':
                return `https://jax-delivery.com/track/${trackingNumber}`;
            case 'mes_colis':
                return `https://mescolis.tn/track/${trackingNumber}`;
            default:
                return '#';
        }
    }
};

// Méthodes à ajouter au composant Alpine principal
function extendWithTracking() {
    return {
        async refreshTracking(shipmentId) {
            try {
                const response = await axios.post(`/admin/delivery/shipments/${shipmentId}/track`);
                
                if (response.data.success) {
                    // Mettre à jour les données de l'expédition
                    await this.updateModalData();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Suivi mis à jour !',
                        text: 'L\'historique de suivi a été actualisé',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    throw new Error(response.data.message || 'Erreur de mise à jour');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de mettre à jour le suivi',
                });
            }
        },

        getTrackingIcon: window.trackingHelpers.getTrackingIcon,
        getTimelineMarkerClass: window.trackingHelpers.getTimelineMarkerClass,
        formatTrackingDate: window.trackingHelpers.formatTrackingDate,
        getTrackingUrl: window.trackingHelpers.getTrackingUrl
    };
}
</script>
@endpush