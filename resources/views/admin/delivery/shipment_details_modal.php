<!-- Modal Détails de l'Expédition -->
<div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-labelledby="shipmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipmentDetailsModalLabel">
                    <i class="fas fa-shipping-fast me-2"></i>
                    Détails de l'Expédition
                    <span x-show="selectedShipment" x-text="`#${selectedShipment?.id}`" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" x-show="selectedShipment">
                <!-- En-tête avec informations principales -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-barcode me-1"></i>
                                            Informations de Suivi
                                        </h6>
                                        <div>
                                            <strong>Numéro de suivi:</strong>
                                            <span x-text="selectedShipment?.pos_barcode || selectedShipment?.pos_reference || 'Non assigné'"></span>
                                            <br>
                                            <strong>ID Expédition:</strong> #<span x-text="selectedShipment?.id"></span>
                                            <br>
                                            <strong>Commande:</strong> #<span x-text="selectedShipment?.order?.id"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-truck me-1"></i>
                                            Transporteur
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <i :class="getCarrierIcon(selectedShipment?.carrier_slug)" class="me-2"></i>
                                            <div>
                                                <div class="fw-bold" x-text="getCarrierName(selectedShipment?.carrier_slug)"></div>
                                                <small class="text-muted" x-text="selectedShipment?.pickup?.delivery_configuration?.integration_name"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Statut Actuel
                                </h6>
                                <div x-show="selectedShipment">
                                    @include('admin.delivery.components.shipment-status-badge', ['shipment' => 'selectedShipment', 'showDetails' => true])
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Dernière MAJ: <span x-text="getTimeSince(selectedShipment?.carrier_last_status_update || selectedShipment?.updated_at)"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations expédition -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-success mb-0" x-text="`${selectedShipment?.cod_amount || 0} TND`"></div>
                            <small class="text-muted">Montant COD</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-info mb-0" x-text="`${selectedShipment?.weight || 0} kg`"></div>
                            <small class="text-muted">Poids</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-warning mb-0" x-text="selectedShipment?.nb_pieces || 0"></div>
                            <small class="text-muted">Nb Pièces</small>
                        </div>
                    </div>
                </div>

                <!-- Onglets pour les détails -->
                <ul class="nav nav-tabs" id="shipmentDetailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="recipient-tab" data-bs-toggle="tab" data-bs-target="#recipient" type="button" role="tab">
                            <i class="fas fa-user me-1"></i>
                            Destinataire
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tracking-tab" data-bs-toggle="tab" data-bs-target="#tracking" type="button" role="tab">
                            <i class="fas fa-route me-1"></i>
                            Suivi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="order-details-tab" data-bs-toggle="tab" data-bs-target="#order-details" type="button" role="tab">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Commande
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="technical-tab" data-bs-toggle="tab" data-bs-target="#technical" type="button" role="tab">
                            <i class="fas fa-cogs me-1"></i>
                            Technique
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="shipmentDetailsTabsContent">
                    <!-- Onglet Destinataire -->
                    <div class="tab-pane fade show active" id="recipient" role="tabpanel">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Informations Contact</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Nom:</strong></td>
                                            <td x-text="selectedShipment?.recipient_info?.name"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Téléphone:</strong></td>
                                            <td>
                                                <a :href="`tel:${selectedShipment?.recipient_info?.phone}`" 
                                                   x-text="selectedShipment?.recipient_info?.phone"></a>
                                            </td>
                                        </tr>
                                        <tr x-show="selectedShipment?.recipient_info?.phone_2">
                                            <td><strong>Téléphone 2:</strong></td>
                                            <td>
                                                <a :href="`tel:${selectedShipment?.recipient_info?.phone_2}`" 
                                                   x-text="selectedShipment?.recipient_info?.phone_2"></a>
                                            </td>
                                        </tr>
                                        <tr x-show="selectedShipment?.recipient_info?.email">
                                            <td><strong>Email:</strong></td>
                                            <td>
                                                <a :href="`mailto:${selectedShipment?.recipient_info?.email}`" 
                                                   x-text="selectedShipment?.recipient_info?.email"></a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Adresse de Livraison</h6>
                                    <div class="bg-light p-3 rounded">
                                        <div x-text="selectedShipment?.recipient_info?.address"></div>
                                        <div class="mt-2">
                                            <strong x-text="selectedShipment?.recipient_info?.city"></strong>
                                            <br>
                                            <span x-text="selectedShipment?.recipient_info?.governorate"></span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a :href="`https://maps.google.com/?q=${encodeURIComponent(selectedShipment?.recipient_info?.address + ', ' + selectedShipment?.recipient_info?.city)}`" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            Voir sur Google Maps
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Suivi -->
                    <div class="tab-pane fade" id="tracking" role="tabpanel">
                        <div class="p-3">
                            @include('admin.delivery.components.tracking-history', ['shipment' => 'selectedShipment'])
                        </div>
                    </div>

                    <!-- Onglet Commande -->
                    <div class="tab-pane fade" id="order-details" role="tabpanel">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Détails de la Commande</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>ID Commande:</strong></td>
                                            <td>
                                                <a :href="`/admin/orders/${selectedShipment?.order?.id}`" 
                                                   target="_blank"
                                                   x-text="`#${selectedShipment?.order?.id}`"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date commande:</strong></td>
                                            <td x-text="formatDate(selectedShipment?.order?.created_at)"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Statut commande:</strong></td>
                                            <td>
                                                <span class="badge bg-info" x-text="selectedShipment?.order?.status"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Montant total:</strong></td>
                                            <td>
                                                <strong x-text="`${selectedShipment?.order?.total_price} TND`"></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Contenu</h6>
                                    <div class="bg-light p-3 rounded">
                                        <p x-text="selectedShipment?.content_description || 'Description non disponible'"></p>
                                    </div>
                                    
                                    <div x-show="selectedShipment?.delivery_notes" class="mt-3">
                                        <h6 class="text-warning">Notes de Livraison</h6>
                                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                                            <small x-text="selectedShipment?.delivery_notes"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Technique -->
                    <div class="tab-pane fade" id="technical" role="tabpanel">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Données Techniques</h6>
                                    <table class="table table-sm font-monospace">
                                        <tr>
                                            <td><strong>ID Expédition:</strong></td>
                                            <td x-text="selectedShipment?.id"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>ID Pickup:</strong></td>
                                            <td x-text="selectedShipment?.pickup_id || 'N/A'"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Code-barres POS:</strong></td>
                                            <td x-text="selectedShipment?.pos_barcode || 'N/A'"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Code-barres retour:</strong></td>
                                            <td x-text="selectedShipment?.return_barcode || 'N/A'"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Référence POS:</strong></td>
                                            <td x-text="selectedShipment?.pos_reference || 'N/A'"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Numéro commande:</strong></td>
                                            <td x-text="selectedShipment?.order_number || 'N/A'"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Dates</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Créé le:</strong></td>
                                            <td x-text="formatDateTime(selectedShipment?.created_at)"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mis à jour:</strong></td>
                                            <td x-text="formatDateTime(selectedShipment?.updated_at)"></td>
                                        </tr>
                                        <tr x-show="selectedShipment?.pickup_date">
                                            <td><strong>Date enlèvement:</strong></td>
                                            <td x-text="formatDate(selectedShipment?.pickup_date)"></td>
                                        </tr>
                                        <tr x-show="selectedShipment?.delivered_at">
                                            <td><strong>Livré le:</strong></td>
                                            <td x-text="formatDateTime(selectedShipment?.delivered_at)"></td>
                                        </tr>
                                        <tr x-show="selectedShipment?.carrier_last_status_update">
                                            <td><strong>Dernière MAJ transporteur:</strong></td>
                                            <td x-text="formatDateTime(selectedShipment?.carrier_last_status_update)"></td>
                                        </tr>
                                    </table>

                                    <!-- Données API du transporteur -->
                                    <div x-show="selectedShipment?.fparcel_data" class="mt-3">
                                        <h6 class="text-primary">Données API</h6>
                                        <div class="bg-light p-2 rounded">
                                            <pre class="mb-0 small" x-text="JSON.stringify(selectedShipment?.fparcel_data, null, 2)"></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div x-show="selectedShipment" class="d-flex justify-content-between w-100">
                    <div class="d-flex gap-2">
                        <!-- Actions selon le statut -->
                        <button x-show="selectedShipment?.pos_barcode" 
                                class="btn btn-primary"
                                @click="trackShipment(selectedShipment.id); updateModalData()">
                            <i class="fas fa-sync me-1"></i>
                            Actualiser Suivi
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'in_transit'" 
                                class="btn btn-success"
                                @click="markAsDelivered(selectedShipment.id); $refs.closeBtn.click()">
                            <i class="fas fa-check me-1"></i>
                            Marquer Livré
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'delivered'" 
                                class="btn btn-outline-info"
                                @click="generateDeliveryProof()">
                            <i class="fas fa-file-pdf me-1"></i>
                            Preuve de Livraison
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

@push('scripts')
<script>
// Méthodes à ajouter au composant Alpine principal pour la modal shipment
function extendWithShipmentDetails() {
    return {
        async updateModalData() {
            // Recharger les données de l'expédition sélectionnée
            if (this.selectedShipment?.id) {
                try {
                    const response = await axios.get(`/admin/delivery/shipments/${this.selectedShipment.id}`);
                    if (response.data.success) {
                        this.selectedShipment = response.data.shipment;
                    }
                } catch (error) {
                    console.error('Erreur rechargement expédition:', error);
                }
            }
        },

        async generateDeliveryProof() {
            if (!this.selectedShipment?.id) return;
            
            Swal.fire({
                icon: 'info',
                title: 'Génération en cours...',
                text: 'Génération de la preuve de livraison',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // TODO: Implémenter la génération de preuve de livraison
                await new Promise(resolve => setTimeout(resolve, 2000)); // Simulation
                
                Swal.fire({
                    icon: 'success',
                    title: 'Preuve générée !',
                    text: 'La preuve de livraison a été téléchargée',
                    showConfirmButton: false,
                    timer: 2000
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de générer la preuve de livraison',
                });
            }
        },

        formatDateTime(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #007bff;
    border-color: transparent;
}

.tab-content {
    border: 1px solid #dee2e6;
    border-top: none;
    min-height: 300px;
}

pre {
    max-height: 200px;
    overflow-y: auto;
}

.font-monospace {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}
</style>
@endpush