<!-- Modal Détails de l'Expédition - Adaptée au layout -->
<div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-labelledby="shipmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); box-shadow: var(--shadow-xl); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-bottom: none; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
                <h5 class="modal-title text-white fw-bold" id="shipmentDetailsModalLabel">
                    <i class="fas fa-shipping-fast me-2"></i>
                    Détails de l'Expédition
                    <span x-show="selectedShipment" 
                          x-text="`#${selectedShipment?.id}`" 
                          class="text-white"
                          style="opacity: 0.9;"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" x-show="selectedShipment" style="padding: 2rem;">
                <!-- En-tête avec informations principales -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card" style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%); border: 1px solid rgba(30, 64, 175, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-barcode me-2"></i>
                                            Informations de Suivi
                                        </h6>
                                        <div class="mb-3">
                                            <small class="text-muted fw-medium">Numéro de suivi</small>
                                            <div class="fw-bold" style="font-family: 'JetBrains Mono', monospace;" x-text="selectedShipment?.pos_barcode || selectedShipment?.pos_reference || 'Non assigné'">-</div>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted fw-medium">ID Expédition</small>
                                            <div class="fw-bold text-primary" x-text="`#${selectedShipment?.id}`">-</div>
                                        </div>
                                        <div>
                                            <small class="text-muted fw-medium">Commande liée</small>
                                            <div class="fw-bold text-success">
                                                <a :href="`/admin/orders/${selectedShipment?.order?.id}`" 
                                                   target="_blank" 
                                                   class="text-decoration-none"
                                                   x-text="`#${selectedShipment?.order?.id}`">-</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-truck me-2"></i>
                                            Transporteur
                                        </h6>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3">
                                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <i :class="getCarrierIcon(selectedShipment?.carrier_slug)" class="text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" x-text="getCarrierName(selectedShipment?.carrier_slug)">-</div>
                                                <small class="text-muted fw-medium" x-text="selectedShipment?.pickup?.delivery_configuration?.integration_name">-</small>
                                            </div>
                                        </div>
                                        <div class="bg-white p-2 rounded" style="border: 1px solid rgba(30, 64, 175, 0.1);">
                                            <small class="text-muted">Référence interne:</small>
                                            <code x-text="selectedShipment?.pos_reference || 'N/A'" style="background: rgba(30, 64, 175, 0.1); padding: 0.2rem 0.4rem; border-radius: 4px;">-</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--border-radius); height: 100%;">
                            <div class="card-body d-flex flex-column justify-content-center" style="padding: 1.5rem;">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Statut Actuel
                                </h6>
                                <div x-show="selectedShipment" class="mb-3">
                                    <span class="badge text-white px-3 py-2" 
                                          style="font-size: 0.9rem; background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);"
                                          x-text="getStatusLabel(selectedShipment?.status)">-</span>
                                </div>
                                <div>
                                    <small class="text-muted fw-medium">
                                        Dernière MAJ: 
                                        <div class="fw-bold text-dark" x-text="getTimeSince(selectedShipment?.carrier_last_status_update || selectedShipment?.updated_at)">-</div>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations expédition -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                </div>
                                <div class="h4 text-success mb-1 fw-bold" x-text="`${selectedShipment?.cod_amount || 0} TND`">0 TND</div>
                                <small class="text-muted fw-medium">Montant COD</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, rgba(8, 145, 178, 0.05) 100%); border: 1px solid rgba(6, 182, 212, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-weight-hanging fa-2x text-info mb-2"></i>
                                </div>
                                <div class="h4 text-info mb-1 fw-bold" x-text="`${selectedShipment?.weight || 0} kg`">0 kg</div>
                                <small class="text-muted fw-medium">Poids</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(217, 119, 6, 0.05) 100%); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-boxes fa-2x text-warning mb-2"></i>
                                </div>
                                <div class="h4 text-warning mb-1 fw-bold" x-text="selectedShipment?.nb_pieces || 0">0</div>
                                <small class="text-muted fw-medium">Nb Pièces</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglets pour les détails -->
                <ul class="nav nav-tabs mb-4" id="shipmentDetailsTabs" role="tablist" style="border-color: var(--card-border);">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" 
                                id="recipient-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#recipient" 
                                type="button" 
                                role="tab"
                                style="color: var(--primary-color); border: none; border-bottom: 3px solid transparent; background: none; transition: all 0.3s ease;">
                            <i class="fas fa-user me-2"></i>
                            Destinataire
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" 
                                id="tracking-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#tracking" 
                                type="button" 
                                role="tab"
                                style="color: var(--text-muted); border: none; border-bottom: 3px solid transparent; background: none; transition: all 0.3s ease;">
                            <i class="fas fa-route me-2"></i>
                            Suivi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" 
                                id="order-details-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#order-details" 
                                type="button" 
                                role="tab"
                                style="color: var(--text-muted); border: none; border-bottom: 3px solid transparent; background: none; transition: all 0.3s ease;">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Commande
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" 
                                id="technical-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#technical" 
                                type="button" 
                                role="tab"
                                style="color: var(--text-muted); border: none; border-bottom: 3px solid transparent; background: none; transition: all 0.3s ease;">
                            <i class="fas fa-cogs me-2"></i>
                            Technique
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="shipmentDetailsTabsContent" style="border: 1px solid var(--card-border); border-radius: 0 0 var(--border-radius) var(--border-radius); min-height: 300px;">
                    <!-- Onglet Destinataire -->
                    <div class="tab-pane fade show active" id="recipient" role="tabpanel" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-address-card me-2"></i>
                                            Informations Contact
                                        </h6>
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td style="width: 100px;"><strong>Nom:</strong></td>
                                                <td x-text="selectedShipment?.recipient_info?.name" class="fw-bold">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Téléphone:</strong></td>
                                                <td>
                                                    <a :href="`tel:${selectedShipment?.recipient_info?.phone}`" 
                                                       x-text="selectedShipment?.recipient_info?.phone"
                                                       class="text-decoration-none fw-bold text-success">-</a>
                                                </td>
                                            </tr>
                                            <tr x-show="selectedShipment?.recipient_info?.phone_2">
                                                <td><strong>Téléphone 2:</strong></td>
                                                <td>
                                                    <a :href="`tel:${selectedShipment?.recipient_info?.phone_2}`" 
                                                       x-text="selectedShipment?.recipient_info?.phone_2"
                                                       class="text-decoration-none fw-bold text-success">-</a>
                                                </td>
                                            </tr>
                                            <tr x-show="selectedShipment?.recipient_info?.email">
                                                <td><strong>Email:</strong></td>
                                                <td>
                                                    <a :href="`mailto:${selectedShipment?.recipient_info?.email}`" 
                                                       x-text="selectedShipment?.recipient_info?.email"
                                                       class="text-decoration-none fw-bold text-primary">-</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            Adresse de Livraison
                                        </h6>
                                        <div class="bg-white p-3 rounded" style="border: 1px solid rgba(30, 64, 175, 0.1); min-height: 120px;">
                                            <div class="mb-2" x-text="selectedShipment?.recipient_info?.address">-</div>
                                            <div class="mt-3">
                                                <strong class="text-dark" x-text="selectedShipment?.recipient_info?.city">-</strong>
                                                <br>
                                                <span class="text-muted" x-text="selectedShipment?.recipient_info?.governorate">-</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <a :href="`https://maps.google.com/?q=${encodeURIComponent((selectedShipment?.recipient_info?.address || '') + ', ' + (selectedShipment?.recipient_info?.city || ''))}`" 
                                               target="_blank" 
                                               class="btn btn-outline-primary btn-sm"
                                               style="border-radius: var(--border-radius); font-weight: 500;">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                Voir sur Google Maps
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Suivi -->
                    <div class="tab-pane fade" id="tracking" role="tabpanel" style="padding: 2rem;">
                        <div class="tracking-history-container">
                            <!-- Suivi en temps réel -->
                            <div x-show="selectedShipment?.tracking_history && selectedShipment.tracking_history.length > 0">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h6 class="text-primary fw-bold mb-0">
                                        <i class="fas fa-route me-2"></i>
                                        Historique de Suivi
                                    </h6>
                                    <button class="btn btn-outline-primary btn-sm" 
                                            @click="refreshTracking(selectedShipment.id)"
                                            x-show="selectedShipment?.pos_barcode"
                                            style="border-radius: var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-sync me-2"></i>
                                        Actualiser
                                    </button>
                                </div>

                                <!-- Timeline de suivi -->
                                <div class="tracking-timeline">
                                    <template x-for="(event, index) in selectedShipment?.tracking_history || []" :key="event.id || index">
                                        <div class="timeline-item" :class="{ 'timeline-item-current': index === 0 }">
                                            <div class="timeline-marker" :class="getTimelineMarkerClass(event.status, index === 0)">
                                                <i :class="getTrackingIcon(event.status)"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-2 fw-bold" x-text="event.label || event.status_label">Mise à jour</h6>
                                                        <p class="text-muted mb-2" x-show="event.location" x-text="event.location">-</p>
                                                        <p class="mb-0 small text-muted" x-show="event.notes" x-text="event.notes">-</p>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted fw-medium" x-text="formatTrackingDate(event.timestamp || event.created_at)">-</small>
                                                        <br>
                                                        <span class="badge bg-secondary small" 
                                                              x-text="event.status"
                                                              style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;">-</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Message si aucun historique -->
                            <div x-show="!selectedShipment?.tracking_history || selectedShipment.tracking_history.length === 0" 
                                 class="text-center py-5">
                                <i class="fas fa-route fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                                <h6 class="text-muted">Aucun historique de suivi</h6>
                                <p class="text-muted mb-3">
                                    <span x-show="!selectedShipment?.pos_barcode">Numéro de suivi non encore assigné</span>
                                    <span x-show="selectedShipment?.pos_barcode">Historique en cours de récupération</span>
                                </p>
                                <button x-show="selectedShipment?.pos_barcode" 
                                        class="btn btn-outline-primary"
                                        @click="refreshTracking(selectedShipment.id)"
                                        style="border-radius: var(--border-radius); font-weight: 500;">
                                    <i class="fas fa-sync me-2"></i>
                                    Récupérer l'historique
                                </button>
                            </div>

                            <!-- Informations de suivi actuelles -->
                            <div x-show="selectedShipment?.pos_barcode" class="mt-4">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Informations de Suivi
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted fw-medium">Numéro de suivi:</small>
                                                <div class="bg-white p-2 rounded mt-1" style="border: 1px solid rgba(30, 64, 175, 0.1);">
                                                    <code x-text="selectedShipment?.pos_barcode" style="background: none; color: var(--primary-color); font-weight: bold;">-</code>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted fw-medium">Dernière vérification:</small>
                                                <div class="fw-bold text-dark mt-1" x-text="formatTrackingDate(selectedShipment?.carrier_last_status_update)">-</div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-medium">Lien de suivi:</small>
                                            <div class="mt-1">
                                                <a :href="getTrackingUrl(selectedShipment)" 
                                                   target="_blank" 
                                                   class="btn btn-outline-info btn-sm"
                                                   style="border-radius: var(--border-radius); font-weight: 500;">
                                                    <i class="fas fa-external-link-alt me-2"></i>
                                                    Suivre sur le site du transporteur
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Commande -->
                    <div class="tab-pane fade" id="order-details" role="tabpanel" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-shopping-cart me-2"></i>
                                            Détails de la Commande
                                        </h6>
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td style="width: 140px;"><strong>ID Commande:</strong></td>
                                                <td>
                                                    <a :href="`/admin/orders/${selectedShipment?.order?.id}`" 
                                                       target="_blank"
                                                       x-text="`#${selectedShipment?.order?.id}`"
                                                       class="text-decoration-none fw-bold text-primary">-</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date commande:</strong></td>
                                                <td class="fw-bold" x-text="formatDate(selectedShipment?.order?.created_at)">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Statut commande:</strong></td>
                                                <td>
                                                    <span class="badge text-white" 
                                                          x-text="selectedShipment?.order?.status"
                                                          style="background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%);">-</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Montant total:</strong></td>
                                                <td>
                                                    <strong class="text-success" x-text="`${selectedShipment?.order?.total_price} TND`">-</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-box me-2"></i>
                                            Contenu
                                        </h6>
                                        <div class="bg-white p-3 rounded" style="border: 1px solid rgba(30, 64, 175, 0.1); min-height: 100px;">
                                            <p class="mb-0" x-text="selectedShipment?.content_description || 'Description non disponible'">-</p>
                                        </div>
                                        
                                        <div x-show="selectedShipment?.delivery_notes" class="mt-3">
                                            <h6 class="text-warning fw-bold mb-2">
                                                <i class="fas fa-sticky-note me-2"></i>
                                                Notes de Livraison
                                            </h6>
                                            <div class="alert" style="background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%); border: 1px solid var(--warning-color); border-radius: var(--border-radius); padding: 0.75rem;">
                                                <small class="text-warning fw-medium" x-text="selectedShipment?.delivery_notes">-</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Technique -->
                    <div class="tab-pane fade" id="technical" role="tabpanel" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-database me-2"></i>
                                            Données Techniques
                                        </h6>
                                        <table class="table table-borderless table-sm" style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                                            <tr>
                                                <td style="width: 140px;"><strong>ID Expédition:</strong></td>
                                                <td x-text="selectedShipment?.id">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>ID Pickup:</strong></td>
                                                <td x-text="selectedShipment?.pickup_id || 'N/A'">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Code-barres POS:</strong></td>
                                                <td x-text="selectedShipment?.pos_barcode || 'N/A'">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Code-barres retour:</strong></td>
                                                <td x-text="selectedShipment?.return_barcode || 'N/A'">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Référence POS:</strong></td>
                                                <td x-text="selectedShipment?.pos_reference || 'N/A'">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Numéro commande:</strong></td>
                                                <td x-text="selectedShipment?.order_number || 'N/A'">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card" style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                                    <div class="card-body" style="padding: 1.5rem;">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-clock me-2"></i>
                                            Dates
                                        </h6>
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td style="width: 140px;"><strong>Créé le:</strong></td>
                                                <td class="fw-bold" x-text="formatDateTime(selectedShipment?.created_at)">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Mis à jour:</strong></td>
                                                <td class="fw-bold" x-text="formatDateTime(selectedShipment?.updated_at)">-</td>
                                            </tr>
                                            <tr x-show="selectedShipment?.pickup_date">
                                                <td><strong>Date enlèvement:</strong></td>
                                                <td class="fw-bold" x-text="formatDate(selectedShipment?.pickup_date)">-</td>
                                            </tr>
                                            <tr x-show="selectedShipment?.delivered_at">
                                                <td><strong>Livré le:</strong></td>
                                                <td class="fw-bold text-success" x-text="formatDateTime(selectedShipment?.delivered_at)">-</td>
                                            </tr>
                                            <tr x-show="selectedShipment?.carrier_last_status_update">
                                                <td><strong>Dernière MAJ transporteur:</strong></td>
                                                <td class="fw-bold text-info" x-text="formatDateTime(selectedShipment?.carrier_last_status_update)">-</td>
                                            </tr>
                                        </table>

                                        <!-- Données API du transporteur -->
                                        <div x-show="selectedShipment?.fparcel_data" class="mt-4">
                                            <h6 class="text-warning fw-bold mb-2">
                                                <i class="fas fa-code me-2"></i>
                                                Données API
                                            </h6>
                                            <div class="bg-dark p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                                <pre class="mb-0 text-light" 
                                                     style="font-size: 0.75rem; font-family: 'JetBrains Mono', monospace;" 
                                                     x-text="JSON.stringify(selectedShipment?.fparcel_data, null, 2)">-</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="background: rgba(248, 250, 252, 0.5); border-top: 1px solid var(--card-border); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); padding: 1.5rem;">
                <div x-show="selectedShipment" class="d-flex justify-content-between w-100">
                    <div class="d-flex gap-2">
                        <!-- Actions selon le statut -->
                        <button x-show="selectedShipment?.pos_barcode" 
                                class="btn btn-primary"
                                @click="trackShipment(selectedShipment.id); updateModalData()"
                                style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-sync me-2"></i>
                            Actualiser Suivi
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'in_transit'" 
                                class="btn btn-success"
                                @click="markAsDelivered(selectedShipment.id); $refs.closeBtn.click()"
                                style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-check me-2"></i>
                            Marquer Livré
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'delivered'" 
                                class="btn btn-outline-info"
                                @click="generateDeliveryProof()"
                                style="border: 2px solid var(--info-color); color: var(--info-color); border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-file-pdf me-2"></i>
                            Preuve de Livraison
                        </button>
                    </div>
                    
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal" 
                            x-ref="closeBtn"
                            style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour la modal des détails d'expédition */
#shipmentDetailsModal .nav-tabs {
    border-bottom: 1px solid var(--card-border);
}

#shipmentDetailsModal .nav-tabs .nav-link {
    transition: all 0.3s ease;
    padding: 1rem 1.5rem;
    margin-bottom: -1px;
}

#shipmentDetailsModal .nav-tabs .nav-link:hover {
    color: var(--primary-color) !important;
    border-bottom-color: var(--primary-color) !important;
    background: rgba(30, 64, 175, 0.05);
}

#shipmentDetailsModal .nav-tabs .nav-link.active {
    color: var(--primary-color) !important;
    border-bottom: 3px solid var(--primary-color) !important;
    background: rgba(30, 64, 175, 0.05);
    font-weight: 600;
}

#shipmentDetailsModal .tab-content {
    border-top: none;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Timeline styles pour le suivi */
#shipmentDetailsModal .tracking-timeline {
    position: relative;
    padding-left: 30px;
    max-height: 400px;
    overflow-y: auto;
}

#shipmentDetailsModal .tracking-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--primary-color), var(--card-border));
}

#shipmentDetailsModal .timeline-item {
    position: relative;
    margin-bottom: 25px;
    padding-bottom: 20px;
}

#shipmentDetailsModal .timeline-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
}

#shipmentDetailsModal .timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--card-border);
    border: 3px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    z-index: 2;
    transition: all 0.3s ease;
}

#shipmentDetailsModal .timeline-marker i {
    color: #fff;
    font-size: 12px;
}

#shipmentDetailsModal .timeline-marker-current {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.25), var(--shadow-md);
    animation: pulse-timeline 2s infinite;
}

#shipmentDetailsModal .timeline-marker.marker-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
}

#shipmentDetailsModal .timeline-marker.marker-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
}

#shipmentDetailsModal .timeline-marker.marker-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
}

#shipmentDetailsModal .timeline-marker.marker-info {
    background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%);
}

#shipmentDetailsModal .timeline-content {
    background: rgba(248, 250, 252, 0.8);
    padding: 1.25rem;
    border-radius: var(--border-radius);  
    border-left: 4px solid var(--primary-color);
    margin-left: 15px;
    position: relative;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

#shipmentDetailsModal .timeline-content:hover {
    background: rgba(248, 250, 252, 1);
    box-shadow: var(--shadow-md);
    transform: translateX(2px);
}

#shipmentDetailsModal .timeline-content::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 15px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 8px 0;
    border-color: transparent rgba(248, 250, 252, 0.8) transparent transparent;
}

#shipmentDetailsModal .timeline-item-current .timeline-content {
    border-left-color: var(--primary-color);
    background: rgba(30, 64, 175, 0.05);
}

#shipmentDetailsModal .timeline-item-current .timeline-content::before {
    border-right-color: rgba(30, 64, 175, 0.05);
}

@keyframes pulse-timeline {
    0% {
        box-shadow: 0 0 0 0 rgba(30, 64, 175, 0.7), var(--shadow-md);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(30, 64, 175, 0), var(--shadow-md);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(30, 64, 175, 0), var(--shadow-md);
    }
}

/* Card animations */
#shipmentDetailsModal .card {
    animation: slideInUp 0.3s ease-out;
    transition: all 0.3s ease;
}

#shipmentDetailsModal .card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Button hover effects */
#shipmentDetailsModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

#shipmentDetailsModal .btn-outline-info:hover {
    background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%);
    color: white;
    border-color: var(--info-color);
}

/* Table styles */
#shipmentDetailsModal .table {
    font-size: 0.9rem;
}

#shipmentDetailsModal .table td {
    padding: 0.5rem 0;
    border: none;
    vertical-align: top;
}

/* Code styles */
#shipmentDetailsModal code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.85rem;
}

/* Scrollbar personnalisée */
#shipmentDetailsModal .tracking-timeline::-webkit-scrollbar,
#shipmentDetailsModal .bg-dark::-webkit-scrollbar {
    width: 6px;
}

#shipmentDetailsModal .tracking-timeline::-webkit-scrollbar-track,
#shipmentDetailsModal .bg-dark::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

#shipmentDetailsModal .tracking-timeline::-webkit-scrollbar-thumb,
#shipmentDetailsModal .bg-dark::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
}

/* Responsive design */
@media (max-width: 768px) {
    #shipmentDetailsModal .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
    
    #shipmentDetailsModal .modal-body {
        padding: 1.5rem 1rem;
    }
    
    #shipmentDetailsModal .row .col-md-6,
    #shipmentDetailsModal .row .col-md-4 {
        margin-bottom: 1rem;
    }
    
    #shipmentDetailsModal .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
    }
    
    #shipmentDetailsModal .tab-content {
        padding: 1rem !important;
    }
    
    #shipmentDetailsModal .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    #shipmentDetailsModal .d-flex.gap-2 {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    #shipmentDetailsModal .timeline {
        padding-left: 20px;
    }
    
    #shipmentDetailsModal .timeline-marker {
        left: -15px;
        width: 24px;
        height: 24px;
    }
    
    #shipmentDetailsModal .timeline-content {
        margin-left: 10px;
        padding: 1rem;
    }
}

/* Animation d'entrée pour la modal */
#shipmentDetailsModal.show .modal-content {
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

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

        async refreshTracking(shipmentId) {
            try {
                const response = await axios.post(`/admin/delivery/shipments/${shipmentId}/track`);
                
                if (response.data.success) {
                    // Mettre à jour les données de l'expédition
                    await this.updateModalData();
                    
                    this.showNotification('success', 'Suivi mis à jour !');
                } else {
                    throw new Error(response.data.message || 'Erreur de mise à jour');
                }
            } catch (error) {
                this.showNotification('error', 'Impossible de mettre à jour le suivi');
                console.error('Erreur refresh tracking:', error);
            }
        },

        async trackShipment(shipmentId) {
            await this.refreshTracking(shipmentId);
        },

        async markAsDelivered(shipmentId) {
            try {
                const response = await axios.post(`/admin/delivery/shipments/${shipmentId}/mark-delivered`);
                if (response.data.success) {
                    this.showNotification('success', 'Expédition marquée comme livrée');
                    await this.updateModalData();
                } else {
                    this.showNotification('error', response.data.message || 'Erreur lors de la mise à jour');
                }
            } catch (error) {
                this.showNotification('error', 'Erreur de communication avec le serveur');
                console.error('Erreur mark delivered:', error);
            }
        },

        async generateDeliveryProof() {
            if (!this.selectedShipment?.id) return;
            
            try {
                window.open(`/admin/delivery/shipments/${this.selectedShipment.id}/delivery-proof`, '_blank');
                this.showNotification('info', 'Ouverture de la preuve de livraison dans un nouvel onglet');
            } catch (error) {
                this.showNotification('error', 'Erreur lors de l\'ouverture du document');
                console.error('Erreur génération preuve:', error);
            }
        },

        getCarrierIcon(carrierSlug) {
            const icons = {
                'jax_delivery': 'fas fa-truck',
                'mes_colis': 'fas fa-shipping-fast'
            };
            return icons[carrierSlug] || 'fas fa-truck';
        },

        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || 'Transporteur inconnu';
        },

        getStatusLabel(status) {
            const labels = {
                'created': 'Créée',
                'validated': 'Validée',
                'picked_up_by_carrier': 'Récupérée',
                'in_transit': 'En Transit',
                'delivered': 'Livrée',
                'cancelled': 'Annulée',
                'in_return': 'En Retour',
                'anomaly': 'Anomalie'
            };
            return labels[status] || 'Inconnu';
        },

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
        },

        getTimeSince(dateString) {
            if (!dateString) return 'Jamais';
            
            try {
                const date = new Date(dateString);
                const now = new Date();
                const diffInMinutes = Math.floor((now - date) / (1000 * 60));
                
                if (diffInMinutes < 1) return 'À l\'instant';
                if (diffInMinutes < 60) return `${diffInMinutes}min`;
                if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`;
                if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}j`;
                
                return date.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit'
                });
            } catch (e) {
                return dateString;
            }
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        formatDateTime(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
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

        showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'success' ? 'fas fa-check-circle' : 
                        type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';

            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px; border-radius: var(--border-radius);';
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
    };
}
</script>