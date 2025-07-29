<!-- Modal Détails du Pickup -->
<div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-labelledby="pickupDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pickupDetailsModalLabel">
                    <i class="fas fa-truck me-2"></i>
                    Détails de l'Enlèvement
                    <span x-show="selectedPickup" x-text="`#${selectedPickup?.id}`" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" x-show="selectedPickup">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Informations Générales
                                </h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>ID:</strong> <span x-text="`#${selectedPickup?.id}`"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Statut:</strong>
                                        <span x-show="selectedPickup" 
                                              :class="getStatusBadgeClass(selectedPickup?.status)"
                                              x-text="getStatusLabel(selectedPickup?.status)"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Date d'enlèvement:</strong>
                                        <span x-text="formatDate(selectedPickup?.pickup_date)"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Créé le:</strong>
                                        <span x-text="formatDateTime(selectedPickup?.created_at)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-truck me-1"></i>
                                    Transporteur
                                </h6>
                                <div x-show="selectedPickup?.delivery_configuration">
                                    <div class="d-flex align-items-center mb-2">
                                        <i :class="getCarrierIcon(selectedPickup?.carrier_slug)" class="me-2"></i>
                                        <div>
                                            <div class="fw-bold" x-text="selectedPickup?.delivery_configuration?.integration_name"></div>
                                            <small class="text-muted" x-text="getCarrierName(selectedPickup?.carrier_slug)"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-primary mb-0" x-text="selectedPickup?.shipments?.length || 0"></div>
                            <small class="text-muted">Commandes</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-success mb-0" x-text="`${getTotalWeight()} kg`"></div>
                            <small class="text-muted">Poids Total</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-info mb-0" x-text="getTotalPieces()"></div>
                            <small class="text-muted">Nb Pièces</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-warning mb-0" x-text="`${getTotalCOD()} TND`"></div>
                            <small class="text-muted">COD Total</small>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-1"></i>
                            Commandes Incluses
                            <span x-show="selectedPickup" 
                                  class="badge bg-primary ms-2" 
                                  x-text="selectedPickup?.shipments?.length || 0"></span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Commande</th>
                                        <th>Client</th>
                                        <th>Téléphone</th>
                                        <th>Adresse</th>
                                        <th>Montant</th>
                                        <th>Poids</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="shipment in selectedPickup?.shipments || []" :key="shipment.id">
                                        <tr>
                                            <td>
                                                <strong x-text="`#${shipment.order?.id}`"></strong>
                                            </td>
                                            <td x-text="shipment.recipient_info?.name"></td>
                                            <td x-text="shipment.recipient_info?.phone"></td>
                                            <td>
                                                <div x-text="shipment.recipient_info?.city"></div>
                                                <small class="text-muted" x-text="shipment.recipient_info?.governorate"></small>
                                            </td>
                                            <td>
                                                <strong x-text="`${shipment.cod_amount} TND`"></strong>
                                            </td>
                                            <td x-text="`${shipment.weight} kg`"></td>
                                            <td>
                                                <span class="badge bg-primary" x-text="shipment.status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Historique si disponible -->
                <div x-show="selectedPickup?.history && selectedPickup.history.length > 0" class="mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-1"></i>
                                Historique
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <template x-for="event in selectedPickup?.history || []" :key="event.id">
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <strong x-text="event.action_label"></strong>
                                                <small class="text-muted" x-text="formatDateTime(event.created_at)"></small>
                                            </div>
                                            <p x-show="event.notes" class="mb-0 text-muted" x-text="event.notes"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div x-show="selectedPickup" class="d-flex justify-content-between w-100">
                    <div>
                        <!-- Actions selon le statut -->
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-success"
                                @click="validatePickup(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-check me-1"></i>
                            Valider l'Enlèvement
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'validated'" 
                                class="btn btn-info"
                                @click="markAsPickedUp(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-truck me-1"></i>
                            Marquer comme Récupéré
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-outline-danger ms-2"
                                @click="deletePickup(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" x-ref="closeBtn">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #007bff;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>
@endpush

@push('scripts')
<script>
// Méthodes utilitaires pour la modal (à ajouter au composant Alpine principal)
function extendPickupMethods() {
    return {
        getStatusBadgeClass(status) {
            const classes = {
                'draft': 'badge bg-secondary',
                'validated': 'badge bg-success',
                'picked_up': 'badge bg-info',
                'problem': 'badge bg-danger'
            };
            return classes[status] || 'badge bg-secondary';
        },

        getStatusLabel(status) {
            const labels = {
                'draft': 'Brouillon',
                'validated': 'Validé',
                'picked_up': 'Récupéré',
                'problem': 'Problème'
            };
            return labels[status] || 'Inconnu';
        },

        getTotalWeight() {
            if (!this.selectedPickup?.shipments) return 0;
            return this.selectedPickup.shipments
                .reduce((total, shipment) => total + (parseFloat(shipment.weight) || 0), 0)
                .toFixed(2);
        },

        getTotalPieces() {
            if (!this.selectedPickup?.shipments) return 0;
            return this.selectedPickup.shipments
                .reduce((total, shipment) => total + (parseInt(shipment.nb_pieces) || 0), 0);
        },

        getTotalCOD() {
            if (!this.selectedPickup?.shipments) return 0;
            return this.selectedPickup.shipments
                .reduce((total, shipment) => total + (parseFloat(shipment.cod_amount) || 0), 0)
                .toFixed(3);
        }
    };
}
</script>
@endpush