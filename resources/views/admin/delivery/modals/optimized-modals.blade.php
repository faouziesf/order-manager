<!-- ============================== -->
<!-- SYSTÈME DE MODALES OPTIMISÉ -->
<!-- Compatible avec le layout admin.blade.php -->
<!-- ============================== -->

<!-- Modal de sélection des commandes pour pickup -->
<div class="modal fade" id="orderSelectionModal" tabindex="-1" aria-labelledby="orderSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header gradient-primary">
                <h5 class="modal-title text-white" id="orderSelectionModalLabel">
                    <i class="fas fa-boxes me-2"></i>Sélectionner les commandes à expédier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Configuration sélectionnée -->
                <div class="config-summary-section">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="info-card">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Transporteur</label>
                                        <div class="info-value" id="selected-carrier-name">-</div>
                                    </div>
                                    <div class="info-item">
                                        <label>Configuration</label>
                                        <div class="info-value" id="selected-integration-name">-</div>
                                    </div>
                                    <div class="info-item">
                                        <label>Date d'enlèvement</label>
                                        <div class="info-value" id="selected-pickup-date">-</div>
                                    </div>
                                    <div class="info-item">
                                        <label>Statut</label>
                                        <span class="badge badge-success" id="selected-config-status">Actif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="summary-card">
                                <div class="summary-content">
                                    <div class="summary-number" id="selection-summary-count">0</div>
                                    <div class="summary-label">commande(s) sélectionnée(s)</div>
                                    <div class="summary-total">
                                        <span>Total: </span>
                                        <strong id="selection-summary-total">0.000 TND</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres et recherche -->
                <div class="filters-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Recherche</label>
                            <div class="search-input-group">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="orderSearchInput" placeholder="Nom, téléphone ou ID...">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Gouvernorat</label>
                            <select class="form-select" id="governorateFilter">
                                <option value="">Tous les gouvernorats</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Stock</label>
                            <select class="form-select" id="stockFilter">
                                <option value="">Tous</option>
                                <option value="available">Stock disponible</option>
                                <option value="issues">Problèmes de stock</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                                    <i class="fas fa-check-square me-1"></i>Tout sélectionner
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                                    <i class="fas fa-square me-1"></i>Désélectionner
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone de chargement -->
                <div id="ordersLoadingIndicator" class="loading-section d-none">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Chargement des commandes...</div>
                    </div>
                </div>

                <!-- Tableau des commandes -->
                <div class="table-section" id="ordersTableContainer">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-header">
                                <tr>
                                    <th class="col-checkbox">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                        </div>
                                    </th>
                                    <th class="col-id">ID</th>
                                    <th class="col-client">Client</th>
                                    <th class="col-phone">Téléphone</th>
                                    <th class="col-amount">Montant</th>
                                    <th class="col-items">Articles</th>
                                    <th class="col-governorate">Gouvernorat</th>
                                    <th class="col-stock">Stock</th>
                                    <th class="col-date">Créée le</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <!-- Les commandes seront ajoutées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-section">
                    <div class="pagination-info">
                        <small id="paginationInfo">Affichage de 0 à 0 sur 0 résultats</small>
                    </div>
                    <nav aria-label="Pagination des commandes">
                        <ul class="pagination pagination-sm" id="paginationContainer">
                            <!-- Pagination sera générée dynamiquement -->
                        </ul>
                    </nav>
                </div>

                <!-- Messages -->
                <div class="messages-section">
                    <div id="selection-errors" class="alert alert-danger d-none" role="alert">
                        <div class="alert-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div id="selection-error-content"></div>
                        </div>
                    </div>
                    <div id="selection-info" class="alert alert-info d-none" role="alert">
                        <div class="alert-content">
                            <i class="fas fa-info-circle"></i>
                            <div id="selection-info-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" id="confirmSelectionBtn" disabled>
                    <i class="fas fa-plus me-2"></i>Créer l'enlèvement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails du Pickup -->
<div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-labelledby="pickupDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header gradient-primary">
                <h5 class="modal-title text-white" id="pickupDetailsModalLabel">
                    <i class="fas fa-truck me-2"></i>Détails de l'Enlèvement
                    <span x-show="selectedPickup" x-text="`#${selectedPickup?.id}`" class="modal-subtitle"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0" x-show="selectedPickup">
                <!-- Informations générales -->
                <div class="info-section">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle me-2"></i>Informations Générales</h6>
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>ID</label>
                                        <div class="info-value" x-text="`#${selectedPickup?.id}`"></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Statut</label>
                                        <span x-show="selectedPickup" 
                                              :class="getStatusBadgeClass(selectedPickup?.status)"
                                              x-text="getStatusLabel(selectedPickup?.status)"></span>
                                    </div>
                                    <div class="info-item">
                                        <label>Date d'enlèvement</label>
                                        <div class="info-value" x-text="formatDate(selectedPickup?.pickup_date)"></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Créé le</label>
                                        <div class="info-value" x-text="formatDateTime(selectedPickup?.created_at)"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-truck me-2"></i>Transporteur</h6>
                                </div>
                                <div x-show="selectedPickup?.delivery_configuration" class="carrier-info">
                                    <div class="carrier-details">
                                        <i :class="getCarrierIcon(selectedPickup?.carrier_slug)" class="carrier-icon"></i>
                                        <div class="carrier-text">
                                            <div class="carrier-name" x-text="selectedPickup?.delivery_configuration?.integration_name"></div>
                                            <div class="carrier-subtitle" x-text="getCarrierName(selectedPickup?.carrier_slug)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="stats-section">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card stat-primary">
                                <div class="stat-number" x-text="selectedPickup?.shipments?.length || 0"></div>
                                <div class="stat-label">Commandes</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card stat-success">
                                <div class="stat-number" x-text="`${getTotalWeight()} kg`"></div>
                                <div class="stat-label">Poids Total</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card stat-info">
                                <div class="stat-number" x-text="getTotalPieces()"></div>
                                <div class="stat-label">Nb Pièces</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card stat-warning">
                                <div class="stat-number" x-text="`${getTotalCOD()} TND`"></div>
                                <div class="stat-label">COD Total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="table-section">
                    <div class="section-header">
                        <h6>
                            <i class="fas fa-list me-2"></i>Commandes Incluses
                            <span x-show="selectedPickup" 
                                  class="badge badge-primary" 
                                  x-text="selectedPickup?.shipments?.length || 0"></span>
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-header">
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
                                    <tr class="table-row">
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
                                            <span class="badge badge-primary" x-text="shipment.status"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historique -->
                <div x-show="selectedPickup?.history && selectedPickup.history.length > 0" class="history-section">
                    <div class="section-header">
                        <h6><i class="fas fa-history me-2"></i>Historique</h6>
                    </div>
                    <div class="timeline">
                        <template x-for="event in selectedPickup?.history || []" :key="event.id">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <strong x-text="event.action_label"></strong>
                                        <small x-text="formatDateTime(event.created_at)"></small>
                                    </div>
                                    <p x-show="event.notes" x-text="event.notes"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div x-show="selectedPickup" class="d-flex justify-content-between w-100">
                    <div class="action-buttons">
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-success"
                                @click="validatePickup(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-check me-1"></i>Valider l'Enlèvement
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'validated'" 
                                class="btn btn-info"
                                @click="markAsPickedUp(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-truck me-1"></i>Marquer comme Récupéré
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-outline-danger"
                                @click="deletePickup(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-trash me-1"></i>Supprimer
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

<!-- Modal de validation d'un pickup -->
<div class="modal fade" id="pickupValidationModal" tabindex="-1" aria-labelledby="pickupValidationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header gradient-warning">
                <h5 class="modal-title text-white" id="pickupValidationModalLabel">
                    <i class="fas fa-truck me-2"></i>Valider l'enlèvement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Informations du pickup -->
                <div class="info-section">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle me-2"></i>Informations générales</h6>
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Pickup ID</label>
                                        <div class="info-value" id="validation-pickup-id">-</div>
                                    </div>
                                    <div class="info-item">
                                        <label>Transporteur</label>
                                        <div class="info-value" id="validation-carrier-name">-</div>
                                    </div>
                                    <div class="info-item">
                                        <label>Date d'enlèvement</label>
                                        <div class="info-value" id="validation-pickup-date">-</div>
                                    </div>
                                    <div class="info-item">
                                        <label>Configuration</label>
                                        <div class="info-value" id="validation-integration-name">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-chart-bar me-2"></i>Résumé des expéditions</h6>
                                </div>
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-number text-primary" id="validation-orders-count">0</div>
                                        <div class="stat-label">Commandes</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-success" id="validation-total-pieces">0</div>
                                        <div class="stat-label">Pièces</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-warning" id="validation-total-weight">0 kg</div>
                                        <div class="stat-label">Poids total</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number text-info" id="validation-total-cod">0 TND</div>
                                        <div class="stat-label">Montant COD</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vérifications avant validation -->
                <div class="checks-section">
                    <div class="section-header">
                        <h6><i class="fas fa-check-circle me-2"></i>Vérifications automatiques</h6>
                    </div>
                    <div class="checks-container" id="validation-checks">
                        <!-- Les vérifications seront ajoutées dynamiquement -->
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="table-section">
                    <div class="section-header">
                        <h6><i class="fas fa-list me-2"></i>Commandes incluses</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-header">
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Montant</th>
                                    <th>Pièces</th>
                                    <th>Gouvernorat</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="validation-orders-list">
                                <!-- Les commandes seront ajoutées dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Alerte d'avertissement -->
                <div class="warning-section">
                    <div class="alert alert-warning" role="alert">
                        <div class="alert-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Attention !</strong><br>
                                Une fois validé, ce pickup sera envoyé au transporteur et ne pourra plus être modifié. 
                                Assurez-vous que toutes les informations sont correctes.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="messages-section">
                    <div id="validation-errors" class="alert alert-danger d-none" role="alert">
                        <div class="alert-content">
                            <i class="fas fa-times-circle"></i>
                            <div id="validation-error-content"></div>
                        </div>
                    </div>
                    <div id="validation-success" class="alert alert-success d-none" role="alert">
                        <div class="alert-content">
                            <i class="fas fa-check-circle"></i>
                            <div id="validation-success-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" id="confirmValidationBtn">
                    <i class="fas fa-paper-plane me-2"></i>Valider et envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails de l'Expédition -->
<div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-labelledby="shipmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header gradient-info">
                <h5 class="modal-title text-white" id="shipmentDetailsModalLabel">
                    <i class="fas fa-shipping-fast me-2"></i>Détails de l'Expédition
                    <span x-show="selectedShipment" x-text="`#${selectedShipment?.id}`" class="modal-subtitle"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0" x-show="selectedShipment">
                <!-- En-tête avec informations principales -->
                <div class="info-section">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-barcode me-2"></i>Informations de Suivi</h6>
                                </div>
                                <div class="tracking-info">
                                    <div class="tracking-item">
                                        <label>Numéro de suivi</label>
                                        <div class="tracking-number" x-text="selectedShipment?.pos_barcode || selectedShipment?.pos_reference || 'Non assigné'"></div>
                                    </div>
                                    <div class="tracking-item">
                                        <label>ID Expédition</label>
                                        <div class="tracking-value">#<span x-text="selectedShipment?.id"></span></div>
                                    </div>
                                    <div class="tracking-item">
                                        <label>Commande</label>
                                        <div class="tracking-value">#<span x-text="selectedShipment?.order?.id"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="status-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle me-2"></i>Statut Actuel</h6>
                                </div>
                                <div class="status-content">
                                    <div x-show="selectedShipment" class="status-badge-container">
                                        <!-- Status badge component would go here -->
                                    </div>
                                    <div class="status-update">
                                        <small>
                                            Dernière MAJ: <span x-text="getTimeSince(selectedShipment?.carrier_last_status_update || selectedShipment?.updated_at)"></span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques expédition -->
                <div class="stats-section">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="stat-card stat-success">
                                <div class="stat-number" x-text="`${selectedShipment?.cod_amount || 0} TND`"></div>
                                <div class="stat-label">Montant COD</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="stat-card stat-info">
                                <div class="stat-number" x-text="`${selectedShipment?.weight || 0} kg`"></div>
                                <div class="stat-label">Poids</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="stat-card stat-warning">
                                <div class="stat-number" x-text="selectedShipment?.nb_pieces || 0"></div>
                                <div class="stat-label">Nb Pièces</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglets pour les détails -->
                <div class="tabs-section">
                    <ul class="nav nav-tabs" id="shipmentDetailsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="recipient-tab" data-bs-toggle="tab" data-bs-target="#recipient" type="button" role="tab">
                                <i class="fas fa-user me-1"></i>Destinataire
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tracking-tab" data-bs-toggle="tab" data-bs-target="#tracking" type="button" role="tab">
                                <i class="fas fa-route me-1"></i>Suivi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="order-details-tab" data-bs-toggle="tab" data-bs-target="#order-details" type="button" role="tab">
                                <i class="fas fa-shopping-cart me-1"></i>Commande
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="technical-tab" data-bs-toggle="tab" data-bs-target="#technical" type="button" role="tab">
                                <i class="fas fa-cogs me-1"></i>Technique
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="shipmentDetailsTabsContent">
                        <!-- Onglet Destinataire -->
                        <div class="tab-pane fade show active" id="recipient" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="info-group">
                                            <h6>Informations Contact</h6>
                                            <div class="contact-info">
                                                <div class="contact-item">
                                                    <label>Nom</label>
                                                    <div x-text="selectedShipment?.recipient_info?.name"></div>
                                                </div>
                                                <div class="contact-item">
                                                    <label>Téléphone</label>
                                                    <a :href="`tel:${selectedShipment?.recipient_info?.phone}`" 
                                                       x-text="selectedShipment?.recipient_info?.phone"></a>
                                                </div>
                                                <div class="contact-item" x-show="selectedShipment?.recipient_info?.phone_2">
                                                    <label>Téléphone 2</label>
                                                    <a :href="`tel:${selectedShipment?.recipient_info?.phone_2}`" 
                                                       x-text="selectedShipment?.recipient_info?.phone_2"></a>
                                                </div>
                                                <div class="contact-item" x-show="selectedShipment?.recipient_info?.email">
                                                    <label>Email</label>
                                                    <a :href="`mailto:${selectedShipment?.recipient_info?.email}`" 
                                                       x-text="selectedShipment?.recipient_info?.email"></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="info-group">
                                            <h6>Adresse de Livraison</h6>
                                            <div class="address-info">
                                                <div class="address-content">
                                                    <div x-text="selectedShipment?.recipient_info?.address"></div>
                                                    <div class="address-location">
                                                        <strong x-text="selectedShipment?.recipient_info?.city"></strong>
                                                        <br>
                                                        <span x-text="selectedShipment?.recipient_info?.governorate"></span>
                                                    </div>
                                                </div>
                                                <div class="address-actions">
                                                    <a :href="`https://maps.google.com/?q=${encodeURIComponent(selectedShipment?.recipient_info?.address + ', ' + selectedShipment?.recipient_info?.city)}`" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-map-marker-alt me-1"></i>Voir sur Google Maps
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Suivi -->
                        <div class="tab-pane fade" id="tracking" role="tabpanel">
                            <div class="tab-content-inner">
                                <!-- Tracking history component would go here -->
                            </div>
                        </div>

                        <!-- Onglet Commande -->
                        <div class="tab-pane fade" id="order-details" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="info-group">
                                            <h6>Détails de la Commande</h6>
                                            <div class="order-details">
                                                <div class="detail-item">
                                                    <label>ID Commande</label>
                                                    <a :href="`/admin/orders/${selectedShipment?.order?.id}`" 
                                                       target="_blank"
                                                       x-text="`#${selectedShipment?.order?.id}`"></a>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Date commande</label>
                                                    <div x-text="formatDate(selectedShipment?.order?.created_at)"></div>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Statut commande</label>
                                                    <span class="badge badge-info" x-text="selectedShipment?.order?.status"></span>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Montant total</label>
                                                    <strong x-text="`${selectedShipment?.order?.total_price} TND`"></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="info-group">
                                            <h6>Contenu</h6>
                                            <div class="content-info">
                                                <p x-text="selectedShipment?.content_description || 'Description non disponible'"></p>
                                            </div>
                                            
                                            <div x-show="selectedShipment?.delivery_notes" class="delivery-notes">
                                                <h6>Notes de Livraison</h6>
                                                <div class="notes-content">
                                                    <small x-text="selectedShipment?.delivery_notes"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Technique -->
                        <div class="tab-pane fade" id="technical" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="info-group">
                                            <h6>Données Techniques</h6>
                                            <div class="technical-data">
                                                <div class="data-item">
                                                    <label>ID Expédition</label>
                                                    <code x-text="selectedShipment?.id"></code>
                                                </div>
                                                <div class="data-item">
                                                    <label>ID Pickup</label>
                                                    <code x-text="selectedShipment?.pickup_id || 'N/A'"></code>
                                                </div>
                                                <div class="data-item">
                                                    <label>Code-barres POS</label>
                                                    <code x-text="selectedShipment?.pos_barcode || 'N/A'"></code>
                                                </div>
                                                <div class="data-item">
                                                    <label>Code-barres retour</label>
                                                    <code x-text="selectedShipment?.return_barcode || 'N/A'"></code>
                                                </div>
                                                <div class="data-item">
                                                    <label>Référence POS</label>
                                                    <code x-text="selectedShipment?.pos_reference || 'N/A'"></code>
                                                </div>
                                                <div class="data-item">
                                                    <label>Numéro commande</label>
                                                    <code x-text="selectedShipment?.order_number || 'N/A'"></code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="info-group">
                                            <h6>Dates</h6>
                                            <div class="dates-info">
                                                <div class="date-item">
                                                    <label>Créé le</label>
                                                    <div x-text="formatDateTime(selectedShipment?.created_at)"></div>
                                                </div>
                                                <div class="date-item">
                                                    <label>Mis à jour</label>
                                                    <div x-text="formatDateTime(selectedShipment?.updated_at)"></div>
                                                </div>
                                                <div class="date-item" x-show="selectedShipment?.pickup_date">
                                                    <label>Date enlèvement</label>
                                                    <div x-text="formatDate(selectedShipment?.pickup_date)"></div>
                                                </div>
                                                <div class="date-item" x-show="selectedShipment?.delivered_at">
                                                    <label>Livré le</label>
                                                    <div x-text="formatDateTime(selectedShipment?.delivered_at)"></div>
                                                </div>
                                                <div class="date-item" x-show="selectedShipment?.carrier_last_status_update">
                                                    <label>Dernière MAJ transporteur</label>
                                                    <div x-text="formatDateTime(selectedShipment?.carrier_last_status_update)"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Données API du transporteur -->
                                        <div x-show="selectedShipment?.fparcel_data" class="info-group">
                                            <h6>Données API</h6>
                                            <div class="api-data">
                                                <pre x-text="JSON.stringify(selectedShipment?.fparcel_data, null, 2)"></pre>
                                            </div>
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
                    <div class="action-buttons">
                        <button x-show="selectedShipment?.pos_barcode" 
                                class="btn btn-primary"
                                @click="trackShipment(selectedShipment.id); updateModalData()">
                            <i class="fas fa-sync me-1"></i>Actualiser Suivi
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'in_transit'" 
                                class="btn btn-success"
                                @click="markAsDelivered(selectedShipment.id); $refs.closeBtn.click()">
                            <i class="fas fa-check me-1"></i>Marquer Livré
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'delivered'" 
                                class="btn btn-outline-info"
                                @click="generateDeliveryProof()">
                            <i class="fas fa-file-pdf me-1"></i>Preuve de Livraison
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

<!-- Modal Test de Connexion -->
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header gradient-info">
                <h5 class="modal-title text-white" id="testConnectionModalLabel">
                    <i class="fas fa-wifi me-2"></i>Test de Connexion Transporteur
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Informations de la configuration testée -->
                <div class="config-info-section" x-show="testConfig">
                    <div class="info-card">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="info-group">
                                    <h6>Configuration Testée</h6>
                                    <div class="config-details">
                                        <div class="config-name" x-text="testConfig?.integration_name"></div>
                                        <div class="config-carrier" x-text="testConfig?.carrier_name"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="info-group">
                                    <h6>Informations</h6>
                                    <div class="config-info">
                                        <div><strong>Compte:</strong> <span x-text="testConfig?.username"></span></div>
                                        <div><strong>Environnement:</strong> <span x-text="testConfig?.environment"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État du test -->
                <div class="test-progress-section" x-show="testInProgress">
                    <div class="test-progress-content">
                        <div class="test-spinner"></div>
                        <h5>Test en cours...</h5>
                        <p>Connexion au transporteur</p>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 :style="`width: ${testProgress}%`"
                                 :aria-valuenow="testProgress" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        <small x-text="testMessage"></small>
                    </div>
                </div>

                <!-- Résultat du test -->
                <div class="test-result-section" x-show="testCompleted && !testInProgress">
                    <!-- Succès -->
                    <div x-show="testResult?.success" class="alert alert-success">
                        <div class="alert-content">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h6>Connexion Réussie !</h6>
                                <p x-text="testResult?.message"></p>
                            </div>
                        </div>
                        
                        <div x-show="testResult?.details" class="test-details">
                            <h6>Détails de la Connexion</h6>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div><strong>URL API:</strong> <span x-text="testResult?.details?.api_url"></span></div>
                                    <div><strong>Temps de test:</strong> <span x-text="formatTestTime(testResult?.details?.test_time)"></span></div>
                                </div>
                                <div class="col-lg-6" x-show="testResult?.details?.account_info">
                                    <div><strong>Statut compte:</strong> <span x-text="testResult?.details?.account_info?.account_status"></span></div>
                                    <div><strong>Solde:</strong> <span x-text="testResult?.details?.account_info?.balance"></span> TND</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Échec -->
                    <div x-show="!testResult?.success" class="alert alert-danger">
                        <div class="alert-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <h6>Échec de Connexion</h6>
                                <p x-text="testResult?.error || 'Erreur inconnue'"></p>
                            </div>
                        </div>
                        
                        <div x-show="testResult?.details" class="test-details">
                            <h6>Informations de Débogage</h6>
                            <div class="debug-info">
                                <div><strong>Code d'erreur:</strong> <span x-text="testResult?.details?.error_code || 'N/A'"></span></div>
                                <div><strong>URL tentée:</strong> <span x-text="testResult?.details?.url || 'N/A'"></span></div>
                                <div><strong>Statut HTTP:</strong> <span x-text="testResult?.details?.status_code || 'N/A'"></span></div>
                            </div>
                        </div>

                        <div class="troubleshooting">
                            <h6>Solutions Possibles</h6>
                            <ul>
                                <li>Vérifiez vos identifiants de connexion</li>
                                <li>Assurez-vous que votre compte transporteur est actif</li>
                                <li>Contactez le support du transporteur si le problème persiste</li>
                                <li>Vérifiez votre connexion internet</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Historique des tests récents -->
                <div class="test-history-section" x-show="testHistory && testHistory.length > 0">
                    <div class="section-header">
                        <h6><i class="fas fa-history me-2"></i>Tests Récents</h6>
                    </div>
                    <div class="test-history-list">
                        <template x-for="test in testHistory.slice(0, 3)" :key="test.id">
                            <div class="history-item">
                                <div class="history-content">
                                    <i :class="test.success ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'"></i>
                                    <div class="history-details">
                                        <span x-text="test.success ? 'Succès' : 'Échec'"></span>
                                        <span x-show="!test.success" class="history-error">- <span x-text="test.error"></span></span>
                                    </div>
                                </div>
                                <small x-text="formatTestTime(test.timestamp)"></small>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div class="action-buttons">
                        <button x-show="testCompleted && !testInProgress" 
                                class="btn btn-outline-primary"
                                @click="retestConnection()">
                            <i class="fas fa-redo me-1"></i>Retester
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button x-show="testResult?.success && !testConfig?.is_active" 
                                class="btn btn-success"
                                @click="activateConfiguration()">
                            <i class="fas fa-power-off me-1"></i>Activer la Configuration
                        </button>
                        
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span x-show="testInProgress">Annuler</span>
                            <span x-show="!testInProgress">Fermer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des détails de suivi d'expédition -->
<div class="modal fade" id="trackingDetailsModal" tabindex="-1" aria-labelledby="trackingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header gradient-info">
                <h5 class="modal-title text-white" id="trackingDetailsModalLabel">
                    <i class="fas fa-route me-2"></i>Suivi détaillé de l'expédition
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Informations principales -->
                <div class="tracking-header-section">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle me-2"></i>Informations de l'expédition</h6>
                                </div>
                                <div class="tracking-info-grid">
                                    <div class="tracking-info-item">
                                        <label>Numéro de suivi</label>
                                        <div class="info-value" id="tracking-number">-</div>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Commande</label>
                                        <div class="info-value" id="tracking-order-id">-</div>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Transporteur</label>
                                        <div class="info-value" id="tracking-carrier">-</div>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Statut actuel</label>
                                        <span class="badge" id="tracking-current-status">-</span>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Poids</label>
                                        <div class="info-value" id="tracking-weight">-</div>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Nombre de pièces</label>
                                        <div class="info-value" id="tracking-pieces">-</div>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Montant COD</label>
                                        <div class="info-value" id="tracking-cod-amount">-</div>
                                    </div>
                                    <div class="tracking-info-item">
                                        <label>Date création</label>
                                        <div class="info-value" id="tracking-created-date">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="info-card">
                                <div class="card-header">
                                    <h6><i class="fas fa-user me-2"></i>Destinataire</h6>
                                </div>
                                <div class="recipient-info">
                                    <div class="recipient-name" id="tracking-recipient-name">-</div>
                                    <div class="recipient-contact">
                                        <i class="fas fa-phone"></i>
                                        <span id="tracking-recipient-phone">-</span>
                                    </div>
                                    <div class="recipient-contact" id="tracking-recipient-phone2-container" style="display: none;">
                                        <i class="fas fa-phone"></i>
                                        <span id="tracking-recipient-phone2">-</span>
                                    </div>
                                    <div class="recipient-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span id="tracking-recipient-address">-</span>
                                    </div>
                                    <div class="recipient-location">
                                        <i class="fas fa-map"></i>
                                        <span id="tracking-recipient-city">-</span>, 
                                        <span id="tracking-recipient-governorate">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="actions-section">
                    <div class="action-buttons-container">
                        <button type="button" class="btn btn-primary btn-sm" id="refreshTrackingBtn">
                            <i class="fas fa-sync me-1"></i>Actualiser le suivi
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="markDeliveredBtn" style="display: none;">
                            <i class="fas fa-check-circle me-1"></i>Marquer comme livré
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" id="reportAnomalyBtn">
                            <i class="fas fa-exclamation-triangle me-1"></i>Signaler une anomalie
                        </button>
                        <button type="button" class="btn btn-info btn-sm" id="contactClientBtn">
                            <i class="fas fa-phone-alt me-1"></i>Contacter le client
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" id="printLabelBtn" style="display: none;">
                            <i class="fas fa-print me-1"></i>Imprimer étiquette
                        </button>
                    </div>
                </div>

                <!-- Statut et progression -->
                <div class="progress-section">
                    <div class="section-header">
                        <h6><i class="fas fa-chart-line me-2"></i>Progression de la livraison</h6>
                    </div>
                    <div class="progress-content">
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" id="tracking-progress-bar" 
                                 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="tracking-steps" id="tracking-steps">
                            <!-- Les étapes seront ajoutées dynamiquement -->
                        </div>
                        
                        <div class="tracking-estimates">
                            <div class="estimate-item">
                                <label>Livraison estimée</label>
                                <div class="estimate-value" id="tracking-estimated-delivery">-</div>
                            </div>
                            <div class="estimate-item">
                                <label>Dernière mise à jour</label>
                                <div class="estimate-value" id="tracking-last-update">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique détaillé -->
                <div class="history-section">
                    <div class="section-header">
                        <h6><i class="fas fa-history me-2"></i>Historique de suivi</h6>
                        <small id="tracking-history-count">0 événement(s)</small>
                    </div>
                    <div class="timeline" id="tracking-history-timeline">
                        <!-- L'historique sera ajouté dynamiquement -->
                    </div>
                </div>

                <!-- Zone de messages -->
                <div id="tracking-messages" class="messages-section">
                    <!-- Messages d'erreur/succès -->
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-primary" id="openOrderDetailsBtn">
                    <i class="fas fa-eye me-2"></i>Voir la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- STYLES CSS OPTIMISÉS -->
<!-- ============================== -->
<style>
:root {
    --modal-primary: #1e40af;
    --modal-primary-dark: #1e3a8a;
    --modal-success: #10b981;
    --modal-warning: #f59e0b;
    --modal-danger: #ef4444;
    --modal-info: #06b6d4;
    --modal-secondary: #f8fafc;
    --modal-text: #374151;
    --modal-text-muted: #6b7280;
    --modal-border: #e5e7eb;
    --modal-radius: 12px;
    --modal-radius-sm: 8px;
    --modal-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    --modal-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===== MODALES BASE ===== */
.modal-content {
    border: none !important;
    border-radius: var(--modal-radius) !important;
    box-shadow: var(--modal-shadow) !important;
    overflow: hidden !important;
}

.modal-header {
    border: none !important;
    padding: 1.5rem !important;
    position: relative !important;
    overflow: hidden !important;
}

.modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    z-index: -1;
}

.gradient-primary {
    background: linear-gradient(135deg, var(--modal-primary) 0%, var(--modal-primary-dark) 100%) !important;
}

.gradient-success {
    background: linear-gradient(135deg, var(--modal-success) 0%, #059669 100%) !important;
}

.gradient-warning {
    background: linear-gradient(135deg, var(--modal-warning) 0%, #d97706 100%) !important;
}

.gradient-info {
    background: linear-gradient(135deg, var(--modal-info) 0%, #0891b2 100%) !important;
}

.modal-title {
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
}

.modal-subtitle {
    opacity: 0.9;
    font-weight: 500;
    margin-left: 0.5rem;
}

.modal-body {
    padding: 0 !important;
}

.modal-footer {
    border: none !important;
    padding: 1.5rem !important;
    background: var(--modal-secondary) !important;
}

/* ===== SECTIONS DANS LES MODALES ===== */
.config-summary-section,
.info-section,
.stats-section,
.filters-section,
.actions-section,
.checks-section,
.progress-section,
.history-section,
.warning-section,
.messages-section,
.table-section,
.pagination-section,
.tabs-section,
.config-info-section,
.test-progress-section,
.test-result-section,
.test-history-section,
.tracking-header-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--modal-border);
}

.config-summary-section:last-child,
.info-section:last-child,
.stats-section:last-child,
.filters-section:last-child,
.actions-section:last-child,
.checks-section:last-child,
.progress-section:last-child,
.history-section:last-child,
.warning-section:last-child,
.messages-section:last-child,
.table-section:last-child,
.pagination-section:last-child,
.tabs-section:last-child,
.config-info-section:last-child,
.test-progress-section:last-child,
.test-result-section:last-child,
.test-history-section:last-child,
.tracking-header-section:last-child {
    border-bottom: none;
}

.section-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-header h6 {
    margin: 0;
    font-weight: 600;
    color: var(--modal-text);
}

/* ===== CARTES D'INFORMATION ===== */
.info-card,
.summary-card,
.status-card {
    background: white;
    border: 1px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    overflow: hidden;
    transition: var(--modal-transition);
}

.info-card:hover,
.summary-card:hover,
.status-card:hover {
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--modal-border);
}

.card-header h6 {
    margin: 0;
    font-weight: 600;
    color: var(--modal-primary);
    font-size: 0.875rem;
}

/* ===== GRILLES D'INFORMATIONS ===== */
.info-grid,
.stats-grid,
.tracking-info-grid {
    display: grid;
    gap: 1rem;
    padding: 1.25rem;
}

.info-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}

.tracking-info-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.info-item,
.stat-item,
.tracking-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item label,
.stat-item label,
.tracking-info-item label {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--modal-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-value,
.stat-number,
.stat-label {
    font-weight: 600;
    color: var(--modal-text);
}

.info-value {
    font-size: 0.875rem;
}

.stat-number {
    font-size: 1.25rem;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--modal-text-muted);
}

/* ===== CARTES DE RÉSUMÉ ===== */
.summary-card {
    height: 100%;
    display: flex;
    align-items: center;
}

.summary-content {
    padding: 1.5rem;
    text-align: center;
    width: 100%;
}

.summary-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--modal-primary);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.summary-label {
    font-size: 0.875rem;
    color: var(--modal-text-muted);
    margin-bottom: 1rem;
}

.summary-total {
    font-size: 0.875rem;
    color: var(--modal-text);
}

/* ===== CARTES DE STATISTIQUES ===== */
.stat-card {
    background: white;
    border: 1px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    padding: 1.25rem;
    text-align: center;
    transition: var(--modal-transition);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    z-index: 1;
}

.stat-card.stat-primary::before {
    background: var(--modal-primary);
}

.stat-card.stat-success::before {
    background: var(--modal-success);
}

.stat-card.stat-info::before {
    background: var(--modal-info);
}

.stat-card.stat-warning::before {
    background: var(--modal-warning);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px -8px rgb(0 0 0 / 0.2);
}

.stat-card .stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-card.stat-primary .stat-number {
    color: var(--modal-primary);
}

.stat-card.stat-success .stat-number {
    color: var(--modal-success);
}

.stat-card.stat-info .stat-number {
    color: var(--modal-info);
}

.stat-card.stat-warning .stat-number {
    color: var(--modal-warning);
}

/* ===== INPUTS ET FILTRES ===== */
.search-input-group {
    position: relative;
}

.search-input-group i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--modal-text-muted);
    z-index: 2;
}

.search-input-group .form-control {
    padding-left: 2.5rem;
}

.form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--modal-text);
    margin-bottom: 0.5rem;
}

.form-control,
.form-select {
    border: 2px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    padding: 0.6rem 0.75rem;
    font-size: 0.875rem;
    transition: var(--modal-transition);
}

.form-control:focus,
.form-select:focus {
    border-color: var(--modal-primary);
    box-shadow: 0 0 0 0.15rem rgba(30, 64, 175, 0.25);
    outline: none;
}

/* ===== BOUTONS D'ACTION ===== */
.action-buttons,
.action-buttons-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.btn {
    border-radius: var(--modal-radius-sm) !important;
    font-weight: 500 !important;
    transition: var(--modal-transition) !important;
    border: none !important;
    font-size: 0.875rem !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.btn-primary {
    background: linear-gradient(135deg, var(--modal-primary) 0%, var(--modal-primary-dark) 100%) !important;
    color: white !important;
}

.btn-success {
    background: linear-gradient(135deg, var(--modal-success) 0%, #059669 100%) !important;
    color: white !important;
}

.btn-danger {
    background: linear-gradient(135deg, var(--modal-danger) 0%, #dc2626 100%) !important;
    color: white !important;
}

.btn-warning {
    background: linear-gradient(135deg, var(--modal-warning) 0%, #d97706 100%) !important;
    color: white !important;
}

.btn-info {
    background: linear-gradient(135deg, var(--modal-info) 0%, #0891b2 100%) !important;
    color: white !important;
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
    color: white !important;
}

.btn-outline-primary {
    border: 2px solid var(--modal-primary) !important;
    color: var(--modal-primary) !important;
    background: transparent !important;
}

.btn-outline-primary:hover {
    background: var(--modal-primary) !important;
    color: white !important;
}

.btn-outline-secondary {
    border: 2px solid var(--modal-text-muted) !important;
    color: var(--modal-text-muted) !important;
    background: transparent !important;
}

.btn-outline-secondary:hover {
    background: var(--modal-text-muted) !important;
    color: white !important;
}

.btn-outline-danger {
    border: 2px solid var(--modal-danger) !important;
    color: var(--modal-danger) !important;
    background: transparent !important;
}

.btn-outline-danger:hover {
    background: var(--modal-danger) !important;
    color: white !important;
}

.btn-outline-info {
    border: 2px solid var(--modal-info) !important;
    color: var(--modal-info) !important;
    background: transparent !important;
}

.btn-outline-info:hover {
    background: var(--modal-info) !important;
    color: white !important;
}

/* ===== CHARGEMENT ===== */
.loading-section {
    padding: 3rem 1.5rem;
    text-align: center;
}

.loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid var(--modal-border);
    border-top: 3px solid var(--modal-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-text {
    color: var(--modal-text-muted);
    font-weight: 500;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===== TABLEAUX ===== */
.table {
    margin: 0 !important;
    font-size: 0.875rem !important;
}

.table-header th {
    background: linear-gradient(135deg, var(--modal-secondary) 0%, #f1f5f9 100%) !important;
    border: none !important;
    font-weight: 600 !important;
    color: var(--modal-text) !important;
    padding: 1rem 0.75rem !important;
    font-size: 0.8rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}

.table-row:hover {
    background: rgba(30, 64, 175, 0.05) !important;
    transform: scale(1.001) !important;
    transition: var(--modal-transition) !important;
}

.table tbody td {
    padding: 0.75rem !important;
    vertical-align: middle !important;
    border-top: 1px solid var(--modal-border) !important;
}

/* Colonnes spécifiques */
.col-checkbox { width: 50px; }
.col-id { width: 80px; }
.col-phone { width: 120px; }
.col-amount { width: 100px; }
.col-items { width: 80px; }
.col-governorate { width: 120px; }
.col-stock { width: 100px; }
.col-date { width: 100px; }

/* ===== PAGINATION ===== */
.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: var(--modal-secondary);
}

.pagination-info {
    color: var(--modal-text-muted);
    font-size: 0.875rem;
}

.pagination {
    margin: 0 !important;
}

.pagination .page-link {
    border: none !important;
    color: var(--modal-text) !important;
    padding: 0.5rem 0.75rem !important;
    margin: 0 0.125rem !important;
    border-radius: var(--modal-radius-sm) !important;
    transition: var(--modal-transition) !important;
}

.pagination .page-link:hover {
    background: var(--modal-primary) !important;
    color: white !important;
    transform: translateY(-1px) !important;
}

.pagination .page-item.active .page-link {
    background: var(--modal-primary) !important;
    color: white !important;
    box-shadow: 0 2px 4px rgba(30, 64, 175, 0.3) !important;
}

/* ===== BADGES ===== */
.badge {
    font-weight: 600 !important;
    letter-spacing: 0.025em !important;
    border-radius: 6px !important;
    padding: 0.35rem 0.65rem !important;
    font-size: 0.75rem !important;
}

.badge-primary {
    background: linear-gradient(135deg, var(--modal-primary) 0%, var(--modal-primary-dark) 100%) !important;
    color: white !important;
}

.badge-success {
    background: linear-gradient(135deg, var(--modal-success) 0%, #059669 100%) !important;
    color: white !important;
}

.badge-warning {
    background: linear-gradient(135deg, var(--modal-warning) 0%, #d97706 100%) !important;
    color: white !important;
}

.badge-danger {
    background: linear-gradient(135deg, var(--modal-danger) 0%, #dc2626 100%) !important;
    color: white !important;
}

.badge-info {
    background: linear-gradient(135deg, var(--modal-info) 0%, #0891b2 100%) !important;
    color: white !important;
}

.badge-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
    color: white !important;
}

/* ===== ALERTES ===== */
.alert {
    border: none !important;
    border-radius: var(--modal-radius-sm) !important;
    margin: 0 !important;
    position: relative !important;
    overflow: hidden !important;
}

.alert::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: currentColor;
    opacity: 0.6;
}

.alert-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.alert-content i {
    font-size: 1.25rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

.alert-success {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
    color: #166534 !important;
}

.alert-danger {
    background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%) !important;
    color: #991b1b !important;
}

.alert-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%) !important;
    color: #92400e !important;
}

.alert-info {
    background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%) !important;
    color: #0c4a6e !important;
}

/* ===== VÉRIFICATIONS ===== */
.checks-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.check-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: white;
    border: 1px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    transition: var(--modal-transition);
}

.check-item:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.check-item i {
    font-size: 1.25rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.check-content {
    flex: 1;
}

.check-label {
    font-weight: 600;
    color: var(--modal-text);
    margin-bottom: 0.25rem;
}

.check-message {
    font-size: 0.875rem;
    color: var(--modal-text-muted);
}

/* ===== ONGLETS ===== */
.nav-tabs {
    border: none !important;
    margin: 0 !important;
    background: var(--modal-secondary) !important;
    padding: 0 1.5rem !important;
}

.nav-tabs .nav-link {
    color: var(--modal-text-muted) !important;
    border: none !important;
    border-bottom: 3px solid transparent !important;
    background: transparent !important;
    padding: 1rem 1.5rem !important;
    font-weight: 500 !important;
    transition: var(--modal-transition) !important;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: var(--modal-primary) !important;
    color: var(--modal-primary) !important;
}

.nav-tabs .nav-link.active {
    color: var(--modal-primary) !important;
    border-bottom-color: var(--modal-primary) !important;
    background: transparent !important;
}

.tab-content {
    border: none !important;
    background: white !important;
}

.tab-content-inner {
    padding: 1.5rem;
}

/* ===== GROUPES D'INFORMATIONS DANS LES ONGLETS ===== */
.info-group {
    margin-bottom: 1.5rem;
}

.info-group:last-child {
    margin-bottom: 0;
}

.info-group h6 {
    color: var(--modal-primary);
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--modal-border);
}

/* Contact Info */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.contact-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.contact-item label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--modal-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.contact-item a {
    color: var(--modal-primary);
    text-decoration: none;
    font-weight: 500;
}

.contact-item a:hover {
    text-decoration: underline;
}

/* Address Info */
.address-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.address-content {
    padding: 1rem;
    background: var(--modal-secondary);
    border-radius: var(--modal-radius-sm);
    border: 1px solid var(--modal-border);
}

.address-location {
    margin-top: 0.5rem;
    font-weight: 500;
}

/* Order Details */
.order-details,
.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.detail-item {
    gap: 0.25rem;
}

.detail-item label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--modal-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-item a {
    color: var(--modal-primary);
    text-decoration: none;
    font-weight: 500;
}

/* Content Info */
.content-info {
    padding: 1rem;
    background: var(--modal-secondary);
    border-radius: var(--modal-radius-sm);
    border: 1px solid var(--modal-border);
}

.delivery-notes {
    margin-top: 1rem;
}

.delivery-notes h6 {
    color: var(--modal-warning);
    margin-bottom: 0.5rem;
    border-bottom: 2px solid var(--modal-warning);
}

.notes-content {
    padding: 0.75rem;
    background: rgba(245, 158, 11, 0.1);
    border-radius: var(--modal-radius-sm);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

/* Technical Data */
.technical-data,
.dates-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.data-item,
.date-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.data-item label,
.date-item label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--modal-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.data-item code {
    background: var(--modal-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.8rem;
    color: var(--modal-primary);
    border: 1px solid var(--modal-border);
}

/* API Data */
.api-data {
    background: var(--modal-secondary);
    border: 1px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    overflow: hidden;
}

.api-data pre {
    margin: 0;
    padding: 1rem;
    background: transparent;
    color: var(--modal-text);
    font-size: 0.8rem;
    max-height: 200px;
    overflow-y: auto;
}

/* ===== TIMELINE ===== */
.timeline {
    position: relative;
    padding: 1rem 0;
    max-height: 400px;
    overflow-y: auto;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--modal-border);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 4rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: var(--modal-primary);
    border: 3px solid white;
    box-shadow: 0 0 0 2px var(--modal-border);
    z-index: 2;
}

.timeline-content {
    background: white;
    border: 1px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border-left: 3px solid var(--modal-primary);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.timeline-header strong {
    color: var(--modal-text);
    font-weight: 600;
}

.timeline-header small {
    color: var(--modal-text-muted);
    font-size: 0.8rem;
}

.timeline-content p {
    margin: 0;
    color: var(--modal-text-muted);
    font-size: 0.875rem;
}

/* ===== TRACKING SPÉCIFIQUE ===== */
.tracking-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1.5rem 0;
}

.tracking-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    position: relative;
}

.tracking-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 1rem;
    left: 60%;
    right: -40%;
    height: 2px;
    background: var(--modal-border);
}

.tracking-step.completed:not(:last-child)::after {
    background: var(--modal-success);
}

.tracking-step-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--modal-border);
    color: white;
    font-size: 0.9rem;
    position: relative;
    z-index: 2;
}

.tracking-step.completed .tracking-step-icon {
    background: var(--modal-success);
}

.tracking-step.current .tracking-step-icon {
    background: var(--modal-primary);
    animation: pulse 2s infinite;
}

.tracking-step-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--modal-text-muted);
    text-align: center;
}

.tracking-step.completed .tracking-step-label,
.tracking-step.current .tracking-step-label {
    color: var(--modal-text);
    font-weight: 600;
}

.tracking-estimates {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.estimate-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.estimate-item label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--modal-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.estimate-value {
    font-weight: 600;
    color: var(--modal-text);
}

/* ===== TEST DE CONNEXION ===== */
.test-progress-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    text-align: center;
}

.test-spinner {
    width: 3rem;
    height: 3rem;
    border: 4px solid var(--modal-border);
    border-top: 4px solid var(--modal-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.test-progress-content h5 {
    color: var(--modal-primary);
    margin: 0;
}

.test-progress-content p {
    color: var(--modal-text-muted);
    margin: 0;
}

.test-progress-content .progress {
    width: 300px;
    height: 8px;
    background: var(--modal-border);
    border-radius: 4px;
    overflow: hidden;
}

.test-progress-content small {
    color: var(--modal-text-muted);
    font-size: 0.8rem;
}

.test-details {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--modal-border);
}

.test-details h6 {
    color: var(--modal-text);
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.debug-info {
    background: var(--modal-secondary);
    padding: 1rem;
    border-radius: var(--modal-radius-sm);
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.8rem;
    color: var(--modal-text);
    border: 1px solid var(--modal-border);
}

.troubleshooting {
    margin-top: 1rem;
}

.troubleshooting h6 {
    color: var(--modal-danger);
    margin-bottom: 0.75rem;
}

.troubleshooting ul {
    margin: 0;
    padding-left: 1.25rem;
}

.troubleshooting li {
    margin-bottom: 0.5rem;
    color: var(--modal-text);
}

/* ===== HISTORIQUE DES TESTS ===== */
.test-history-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: white;
    border: 1px solid var(--modal-border);
    border-radius: var(--modal-radius-sm);
    transition: var(--modal-transition);
}

.history-item:hover {
    background: rgba(30, 64, 175, 0.05);
    transform: translateX(2px);
}

.history-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.history-content i {
    font-size: 1rem;
}

.history-details {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.history-error {
    color: var(--modal-text-muted);
    font-size: 0.8rem;
}

.history-item small {
    color: var(--modal-text-muted);
    font-size: 0.8rem;
}

/* ===== TRANSPORTEUR INFO ===== */
.carrier-info {
    padding: 1.25rem;
}

.carrier-details {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.carrier-icon {
    font-size: 1.5rem;
    color: var(--modal-primary);
    width: 2rem;
    text-align: center;
}

.carrier-text {
    flex: 1;
}

.carrier-name {
    font-weight: 600;
    color: var(--modal-text);
    margin-bottom: 0.25rem;
}

.carrier-subtitle {
    font-size: 0.875rem;
    color: var(--modal-text-muted);
}

/* ===== RECIPIENT INFO ===== */
.recipient-info {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.recipient-name {
    font-weight: 600;
    color: var(--modal-text);
    font-size: 1rem;
}

.recipient-contact,
.recipient-address,
.recipient-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--modal-text);
}

.recipient-contact i,
.recipient-address i,
.recipient-location i {
    color: var(--modal-text-muted);
    width: 1rem;
    text-align: center;
}

.recipient-contact a {
    color: var(--modal-primary);
    text-decoration: none;
}

.recipient-contact a:hover {
    text-decoration: underline;
}

/* ===== TRACKING HEADER ===== */
.tracking-info {
    padding: 1.25rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.tracking-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.tracking-item label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--modal-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.tracking-number {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-weight: 600;
    color: var(--modal-primary);
    font-size: 1rem;
}

.tracking-value {
    font-weight: 600;
    color: var(--modal-text);
}

/* ===== STATUS CARD ===== */
.status-content {
    padding: 1.25rem;
    text-align: center;
}

.status-badge-container {
    margin-bottom: 1rem;
}

.status-update {
    color: var(--modal-text-muted);
    font-size: 0.8rem;
}

/* ===== CONFIGURATION INFO ===== */
.config-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.config-name {
    font-weight: 600;
    color: var(--modal-text);
    font-size: 1rem;
}

.config-carrier {
    font-size: 0.875rem;
    color: var(--modal-text-muted);
}

.config-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.875rem;
}

/* ===== ANIMATIONS ===== */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(30, 64, 175, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(30, 64, 175, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(30, 64, 175, 0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .tracking-info-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons,
    .action-buttons-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .tracking-steps {
        flex-direction: column;
        gap: 1rem;
    }
    
    .tracking-step:not(:last-child)::after {
        display: none;
    }
    
    .config-summary-section,
    .info-section,
    .stats-section,
    .filters-section,
    .actions-section,
    .checks-section,
    .progress-section,
    .history-section,
    .warning-section,
    .messages-section,
    .table-section,
    .pagination-section,
    .tabs-section,
    .config-info-section,
    .test-progress-section,
    .test-result-section,
    .test-history-section,
    .tracking-header-section {
        padding: 1rem;
    }
    
    .modal-header,
    .modal-footer {
        padding: 1rem !important;
    }
    
    .tab-content-inner {
        padding: 1rem;
    }
    
    .summary-number {
        font-size: 1.5rem;
    }
    
    .stat-card .stat-number {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .pagination-section {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    
    .nav-tabs {
        padding: 0 !important;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem !important;
        font-size: 0.8rem !important;
    }
    
    .modal-dialog {
        margin: 0.5rem !important;
    }
}

/* ===== PRINT STYLES ===== */
@media print {
    .modal {
        position: static !important;
        background: none !important;
        padding: 0 !important;
    }
    
    .modal-dialog {
        max-width: none !important;
        margin: 0 !important;
        box-shadow: none !important;
    }
    
    .modal-header,
    .modal-footer {
        display: none !important;
    }
    
    .btn,
    .action-buttons,
    .action-buttons-container {
        display: none !important;
    }
    
    .modal-body {
        padding: 1rem !important;
    }
}

/* ===== ACCESSIBILITÉ ===== */
.modal:focus {
    outline: none;
}

.btn:focus,
.form-control:focus,
.form-select:focus {
    outline: 2px solid var(--modal-primary);
    outline-offset: 2px;
}

.nav-link:focus {
    outline: 2px solid var(--modal-primary);
    outline-offset: -2px;
}

/* ===== ÉTATS DE CHARGEMENT ===== */
.btn.loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 1rem;
    height: 1rem;
    margin: -0.5rem 0 0 -0.5rem;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.btn.loading .btn-text {
    opacity: 0;
}

/* ===== SCROLLBAR PERSONNALISÉ ===== */
.modal-body::-webkit-scrollbar,
.table-responsive::-webkit-scrollbar,
.timeline::-webkit-scrollbar,
.api-data pre::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.modal-body::-webkit-scrollbar-track,
.table-responsive::-webkit-scrollbar-track,
.timeline::-webkit-scrollbar-track,
.api-data pre::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb,
.table-responsive::-webkit-scrollbar-thumb,
.timeline::-webkit-scrollbar-thumb,
.api-data pre::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--modal-primary), var(--modal-primary-dark));
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover,
.table-responsive::-webkit-scrollbar-thumb:hover,
.timeline::-webkit-scrollbar-thumb:hover,
.api-data pre::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, var(--modal-primary-dark), var(--modal-primary));
}

/* Pour Firefox */
.modal-body,
.table-responsive,
.timeline,
.api-data pre {
    scrollbar-width: thin;
    scrollbar-color: var(--modal-primary) rgba(0, 0, 0, 0.05);
}

/* ===== DARK MODE SUPPORT ===== */
@media (prefers-color-scheme: dark) {
    .modal-content {
        background: #1f2937 !important;
        color: #f9fafb !important;
    }
    
    .info-card,
    .summary-card,
    .status-card,
    .stat-card {
        background: #374151 !important;
        border-color: #4b5563 !important;
        color: #f9fafb !important;
    }
    
    .form-control,
    .form-select {
        background: #374151 !important;
        border-color: #4b5563 !important;
        color: #f9fafb !important;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: var(--modal-primary) !important;
        background: #374151 !important;
    }
    
    .table {
        color: #f9fafb !important;
    }
    
    .table-header th {
        background: #374151 !important;
        color: #f9fafb !important;
    }
    
    .timeline-content {
        background: #374151 !important;
        border-color: #4b5563 !important;
        color: #f9fafb !important;
    }
}
</style>

<!-- ============================== -->
<!-- JAVASCRIPT OPTIMISÉ -->
<!-- ============================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== CONFIGURATION GLOBALE =====
    const MODAL_CONFIG = {
        backdrop: 'static',
        keyboard: true,
        focus: true
    };

    const API_ENDPOINTS = {
        orders: '/admin/delivery/preparation/orders',
        pickup: '/admin/delivery/preparation',
        validate: '/admin/delivery/pickups/{id}/validate',
        shipment: '/admin/delivery/shipments/{id}',
        track: '/admin/delivery/shipments/{id}/track',
        test: '/admin/delivery/configuration/{id}/test'
    };

    // ===== GESTIONNAIRE GLOBAL DES MODALES =====
    class ModalManager {
        constructor() {
            this.activeModals = new Set();
            this.currentData = {};
            this.setupEventListeners();
        }

        setupEventListeners() {
            // Nettoyer les modales lors de la fermeture
            document.addEventListener('hidden.bs.modal', (e) => {
                this.cleanupModal(e.target);
            });

            // Prévenir les conflits de backdrop
            document.addEventListener('show.bs.modal', (e) => {
                this.handleModalOpen(e.target);
            });

            // Gestion des raccourcis clavier
            document.addEventListener('keydown', (e) => {
                this.handleKeyboardShortcuts(e);
            });
        }

        handleModalOpen(modal) {
            this.activeModals.add(modal.id);
            // Désactiver le scroll du body
            document.body.style.overflow = 'hidden';
        }

        cleanupModal(modal) {
            this.activeModals.delete(modal.id);
            
            // Nettoyer les données
            if (this.currentData[modal.id]) {
                delete this.currentData[modal.id];
            }

            // Réactiver le scroll si aucune modale n'est ouverte
            if (this.activeModals.size === 0) {
                document.body.style.overflow = '';
                
                // Nettoyage complet des backdrops
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                }, 100);
            }
        }

        handleKeyboardShortcuts(e) {
            // Échapper pour fermer la modale active
            if (e.key === 'Escape' && this.activeModals.size > 0) {
                const lastModal = Array.from(this.activeModals).pop();
                const modalElement = document.getElementById(lastModal);
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }
            }
        }

        openModal(modalId, data = null) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                console.error(`Modal ${modalId} not found`);
                return;
            }

            if (data) {
                this.currentData[modalId] = data;
            }

            const bsModal = new bootstrap.Modal(modal, MODAL_CONFIG);
            bsModal.show();
        }

        closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        }

        getData(modalId) {
            return this.currentData[modalId] || null;
        }

        setData(modalId, data) {
            this.currentData[modalId] = data;
        }
    }

    // ===== UTILITAIRES =====
    class Utils {
        static formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('fr-FR');
        }

        static formatDateTime(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleString('fr-FR');
        }

        static formatCurrency(amount) {
            if (!amount) return '0.000 TND';
            return `${parseFloat(amount).toFixed(3)} TND`;
        }

        static debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        static showNotification(type, message, duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
            
            const icon = type === 'success' ? 'check-circle' : 
                        type === 'danger' ? 'exclamation-triangle' : 
                        type === 'warning' ? 'exclamation-triangle' : 
                        'info-circle';
            
            notification.innerHTML = `
                <div class="alert-content">
                    <i class="fas fa-${icon}"></i>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            // Auto-suppression
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, duration);
        }

        static async apiRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            };

            const mergedOptions = { ...defaultOptions, ...options };
            
            try {
                const response = await fetch(url, mergedOptions);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || `HTTP ${response.status}`);
                }
                
                return data;
            } catch (error) {
                console.error('API Request failed:', error);
                throw error;
            }
        }

        static setButtonLoading(button, loading = true) {
            if (loading) {
                button.dataset.originalText = button.innerHTML;
                button.disabled = true;
                button.classList.add('loading');
                
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin';
                }
            } else {
                button.disabled = false;
                button.classList.remove('loading');
                
                if (button.dataset.originalText) {
                    button.innerHTML = button.dataset.originalText;
                    delete button.dataset.originalText;
                }
            }
        }
    }

    // ===== GESTION DES SÉLECTIONS DE COMMANDES =====
    class OrderSelectionManager {
        constructor(modalManager) {
            this.modalManager = modalManager;
            this.selectedOrders = new Set();
            this.allOrders = [];
            this.currentPage = 1;
            this.ordersPerPage = 50;
            this.searchTimeout = null;
            this.currentConfiguration = null;

            this.initializeElements();
            this.setupEventListeners();
        }

        initializeElements() {
            this.modal = document.getElementById('orderSelectionModal');
            this.confirmBtn = document.getElementById('confirmSelectionBtn');
            this.searchInput = document.getElementById('orderSearchInput');
            this.governorateFilter = document.getElementById('governorateFilter');
            this.stockFilter = document.getElementById('stockFilter');
            this.selectAllBtn = document.getElementById('selectAllBtn');
            this.deselectAllBtn = document.getElementById('deselectAllBtn');
            this.selectAllCheckbox = document.getElementById('selectAllCheckbox');
            this.tableBody = document.getElementById('ordersTableBody');
            this.loadingIndicator = document.getElementById('ordersLoadingIndicator');
            this.tableContainer = document.getElementById('ordersTableContainer');
        }

        setupEventListeners() {
            // Recherche avec debounce
            this.searchInput?.addEventListener('input', Utils.debounce(() => {
                this.currentPage = 1;
                this.loadOrders();
            }, 500));

            // Filtres
            this.governorateFilter?.addEventListener('change', () => {
                this.currentPage = 1;
                this.loadOrders();
            });

            this.stockFilter?.addEventListener('change', () => {
                this.currentPage = 1;
                this.loadOrders();
            });

            // Sélection
            this.selectAllBtn?.addEventListener('click', () => this.selectAllOrders());
            this.deselectAllBtn?.addEventListener('click', () => this.deselectAllOrders());
            this.selectAllCheckbox?.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.selectAllOrders();
                } else {
                    this.deselectAllOrders();
                }
            });

            // Confirmation
            this.confirmBtn?.addEventListener('click', () => this.confirmSelection());
        }

        open(configurationData) {
            this.currentConfiguration = configurationData;
            this.selectedOrders.clear();
            
            // Remplir les informations de configuration
            this.populateConfigurationInfo(configurationData);
            
            // Réinitialiser les filtres
            this.resetFilters();
            
            // Charger les commandes
            this.loadOrders();
            
            // Ouvrir la modal
            this.modalManager.openModal('orderSelectionModal', configurationData);
        }

        populateConfigurationInfo(config) {
            const elements = {
                'selected-carrier-name': config.carrier_name || '-',
                'selected-integration-name': config.integration_name || '-',
                'selected-pickup-date': config.pickup_date || new Date().toLocaleDateString('fr-FR')
            };

            Object.entries(elements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            });
        }

        async loadOrders(page = 1) {
            this.showLoading(true);

            const params = new URLSearchParams({
                page: page,
                per_page: this.ordersPerPage,
                search: this.searchInput?.value.trim() || '',
                governorate: this.governorateFilter?.value || '',
                stock_filter: this.stockFilter?.value || ''
            });

            try {
                const data = await Utils.apiRequest(`${API_ENDPOINTS.orders}?${params}`);
                
                if (data.success) {
                    this.allOrders = data.orders;
                    this.updateOrdersTable(data.orders);
                    this.updatePagination(data.pagination);
                    this.updateGovernorateFilter(data.governorates || []);
                } else {
                    this.showError('Erreur lors du chargement des commandes');
                }
            } catch (error) {
                console.error('Erreur chargement commandes:', error);
                this.showError('Erreur de communication avec le serveur');
            } finally {
                this.showLoading(false);
            }
        }

        updateOrdersTable(orders) {
            if (!this.tableBody) return;

            if (orders.length === 0) {
                this.tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox me-2"></i>Aucune commande disponible
                        </td>
                    </tr>
                `;
                return;
            }

            this.tableBody.innerHTML = orders.map(order => {
                const isSelected = this.selectedOrders.has(order.id);
                const hasStockIssues = !order.can_be_shipped;
                
                return `
                    <tr class="table-row ${isSelected ? 'table-primary' : ''} ${hasStockIssues ? 'table-warning' : ''}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input order-checkbox" type="checkbox" 
                                       value="${order.id}" ${isSelected ? 'checked' : ''}
                                       ${hasStockIssues ? 'disabled' : ''}>
                            </div>
                        </td>
                        <td><strong>#${order.id}</strong></td>
                        <td>
                            <div>${order.customer_name || '-'}</div>
                            <small class="text-muted">${order.customer_city || ''}</small>
                        </td>
                        <td>
                            <div>${order.customer_phone || '-'}</div>
                            ${order.customer_phone_2 ? `<small class="text-muted">${order.customer_phone_2}</small>` : ''}
                        </td>
                        <td><strong>${Utils.formatCurrency(order.total_price)}</strong></td>
                        <td><span class="badge badge-secondary">${order.items_count || 0}</span></td>
                        <td>${order.region_name || '-'}</td>
                        <td>
                            ${hasStockIssues ? 
                                '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle me-1"></i>Problèmes</span>' :
                                '<span class="badge badge-success"><i class="fas fa-check me-1"></i>Disponible</span>'
                            }
                        </td>
                        <td>
                            <small>${Utils.formatDate(order.created_at)}</small>
                        </td>
                    </tr>
                `;
            }).join('');

            // Ajouter les event listeners pour les checkboxes
            this.tableBody.querySelectorAll('.order-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', (e) => this.handleOrderSelection(e));
            });

            this.updateSelectionSummary();
        }

        handleOrderSelection(event) {
            const orderId = parseInt(event.target.value);
            const isChecked = event.target.checked;

            if (isChecked) {
                this.selectedOrders.add(orderId);
            } else {
                this.selectedOrders.delete(orderId);
            }

            this.updateSelectionSummary();
            this.updateSelectAllCheckbox();
        }

        updateSelectionSummary() {
            const selectedCount = this.selectedOrders.size;
            const selectedOrdersData = this.allOrders.filter(order => this.selectedOrders.has(order.id));
            const totalAmount = selectedOrdersData.reduce((sum, order) => sum + (order.total_price || 0), 0);

            const countElement = document.getElementById('selection-summary-count');
            const totalElement = document.getElementById('selection-summary-total');

            if (countElement) countElement.textContent = selectedCount;
            if (totalElement) totalElement.textContent = Utils.formatCurrency(totalAmount);

            if (this.confirmBtn) {
                this.confirmBtn.disabled = selectedCount === 0;
                this.confirmBtn.innerHTML = selectedCount === 0 ? 
                    '<i class="fas fa-plus me-2"></i>Créer l\'enlèvement' :
                    `<i class="fas fa-plus me-2"></i>Créer l'enlèvement (${selectedCount})`;
            }
        }

        updateSelectAllCheckbox() {
            if (!this.selectAllCheckbox) return;

            const availableOrders = this.allOrders.filter(order => order.can_be_shipped);
            const selectedAvailable = availableOrders.filter(order => this.selectedOrders.has(order.id));
            
            if (availableOrders.length === 0) {
                this.selectAllCheckbox.indeterminate = false;
                this.selectAllCheckbox.checked = false;
            } else if (selectedAvailable.length === availableOrders.length) {
                this.selectAllCheckbox.indeterminate = false;
                this.selectAllCheckbox.checked = true;
            } else if (selectedAvailable.length > 0) {
                this.selectAllCheckbox.indeterminate = true;
            } else {
                this.selectAllCheckbox.indeterminate = false;
                this.selectAllCheckbox.checked = false;
            }
        }

        selectAllOrders() {
            const availableOrders = this.allOrders.filter(order => order.can_be_shipped);
            availableOrders.forEach(order => this.selectedOrders.add(order.id));
            this.updateOrdersTable(this.allOrders);
        }

        deselectAllOrders() {
            this.selectedOrders.clear();
            this.updateOrdersTable(this.allOrders);
        }

        async confirmSelection() {
            if (this.selectedOrders.size === 0) return;

            Utils.setButtonLoading(this.confirmBtn, true);

            const data = {
                delivery_configuration_id: this.currentConfiguration.id,
                order_ids: Array.from(this.selectedOrders),
                pickup_date: document.getElementById('selected-pickup-date')?.textContent
            };

            try {
                const response = await Utils.apiRequest(API_ENDPOINTS.pickup, {
                    method: 'POST',
                    body: JSON.stringify(data)
                });

                if (response.success) {
                    this.showInfo('Pickup créé avec succès ! Redirection...');
                    
                    setTimeout(() => {
                        this.modalManager.closeModal('orderSelectionModal');
                        if (typeof refreshPreparationPage === 'function') {
                            refreshPreparationPage();
                        }
                        window.location.href = '/admin/delivery/pickups';
                    }, 1500);
                } else {
                    this.showError(response.message || 'Erreur lors de la création du pickup');
                }
            } catch (error) {
                console.error('Erreur création pickup:', error);
                this.showError(error.message || 'Erreur de communication avec le serveur');
            } finally {
                Utils.setButtonLoading(this.confirmBtn, false);
            }
        }

        updatePagination(pagination) {
            const container = document.getElementById('paginationContainer');
            const info = document.getElementById('paginationInfo');
            
            if (!container || !info) return;

            // Mettre à jour les informations
            const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            info.textContent = `Affichage de ${start} à ${end} sur ${pagination.total} résultats`;
            
            // Générer la pagination
            let paginationHTML = '';
            
            if (pagination.current_page > 1) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Précédent</a></li>`;
            }
            
            for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
                paginationHTML += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }
            
            if (pagination.current_page < pagination.last_page) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Suivant</a></li>`;
            }
            
            container.innerHTML = paginationHTML;
            
            // Ajouter les event listeners
            container.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = parseInt(e.target.dataset.page);
                    if (page && page !== this.currentPage) {
                        this.currentPage = page;
                        this.loadOrders(page);
                    }
                });
            });
        }

        updateGovernorateFilter(governorates) {
            if (!this.governorateFilter) return;

            const currentValue = this.governorateFilter.value;
            this.governorateFilter.innerHTML = '<option value="">Tous les gouvernorats</option>';
            
            governorates.forEach(gov => {
                const option = document.createElement('option');
                option.value = gov.id;
                option.textContent = gov.name;
                if (gov.id == currentValue) option.selected = true;
                this.governorateFilter.appendChild(option);
            });
        }

        resetFilters() {
            if (this.searchInput) this.searchInput.value = '';
            if (this.governorateFilter) this.governorateFilter.value = '';
            if (this.stockFilter) this.stockFilter.value = '';
            this.currentPage = 1;
        }

        showLoading(show) {
            if (this.loadingIndicator) {
                this.loadingIndicator.classList.toggle('d-none', !show);
            }
            if (this.tableContainer) {
                this.tableContainer.classList.toggle('d-none', show);
            }
        }

        showError(message) {
            const errorDiv = document.getElementById('selection-errors');
            const errorContent = document.getElementById('selection-error-content');
            const infoDiv = document.getElementById('selection-info');
            
            if (errorContent) errorContent.textContent = message;
            if (errorDiv) errorDiv.classList.remove('d-none');
            if (infoDiv) infoDiv.classList.add('d-none');
        }

        showInfo(message) {
            const infoDiv = document.getElementById('selection-info');
            const infoContent = document.getElementById('selection-info-content');
            const errorDiv = document.getElementById('selection-errors');
            
            if (infoContent) infoContent.textContent = message;
            if (infoDiv) infoDiv.classList.remove('d-none');
            if (errorDiv) errorDiv.classList.add('d-none');
        }
    }

    // ===== INITIALISATION =====
    const modalManager = new ModalManager();
    const orderSelectionManager = new OrderSelectionManager(modalManager);

    // ===== EXPOSITION GLOBALE =====
    window.modalManager = modalManager;
    window.orderSelectionManager = orderSelectionManager;
    
    // Fonctions globales pour compatibilité
    window.openOrderSelectionModal = (data) => orderSelectionManager.open(data);
    
    // Utilitaires globaux
    window.Utils = Utils;
    
    console.log('✅ Système de modales optimisé initialisé');
});
</script>