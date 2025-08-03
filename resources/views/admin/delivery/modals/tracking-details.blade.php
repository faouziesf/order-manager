<!-- Modal des détails de suivi d'expédition - Adaptée au layout -->
<div class="modal fade" id="trackingDetailsModal" tabindex="-1" aria-labelledby="trackingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); box-shadow: var(--shadow-xl); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%); border-bottom: none; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
                <h5 class="modal-title text-white fw-bold" id="trackingDetailsModalLabel">
                    <i class="fas fa-route me-2"></i>
                    Suivi détaillé de l'expédition
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" style="padding: 2rem;">
                <!-- Informations principales -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card" style="border: 2px solid var(--primary-color); border-radius: var(--border-radius); background: rgba(30, 64, 175, 0.05);">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-bottom: none; border-radius: var(--border-radius) var(--border-radius) 0 0;">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations de l'expédition
                                </h6>
                            </div>
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Numéro de suivi</small>
                                        <div class="fw-bold" style="font-family: 'JetBrains Mono', monospace;" id="tracking-number">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Commande</small>
                                        <div class="fw-bold text-primary" id="tracking-order-id">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Transporteur</small>
                                        <div class="fw-bold text-success" id="tracking-carrier">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Statut actuel</small>
                                        <span class="badge" id="tracking-current-status" style="background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%); color: white;">-</span>
                                    </div>
                                </div>
                                <hr style="border-color: rgba(30, 64, 175, 0.2);">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Poids</small>
                                        <div class="fw-bold" id="tracking-weight">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Nombre de pièces</small>
                                        <div class="fw-bold" id="tracking-pieces">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Montant COD</small>
                                        <div class="fw-bold text-success" id="tracking-cod-amount">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted fw-medium">Date création</small>
                                        <div class="fw-bold" id="tracking-created-date">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card" style="border: 2px solid var(--success-color); border-radius: var(--border-radius); background: rgba(16, 185, 129, 0.05); height: 100%;">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border-bottom: none; border-radius: var(--border-radius) var(--border-radius) 0 0;">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-user me-2"></i>
                                    Destinataire
                                </h6>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <strong class="text-dark" id="tracking-recipient-name">-</strong>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-phone me-2 text-success"></i>
                                    <a href="#" class="text-decoration-none fw-medium" id="tracking-recipient-phone">-</a>
                                </div>
                                <div class="mb-2" id="tracking-recipient-phone2-container" style="display: none;">
                                    <i class="fas fa-phone me-2 text-success"></i>
                                    <a href="#" class="text-decoration-none fw-medium" id="tracking-recipient-phone2">-</a>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                    <span class="fw-medium" id="tracking-recipient-address">-</span>
                                </div>
                                <div>
                                    <i class="fas fa-map me-2 text-info"></i>
                                    <span class="fw-medium">
                                        <span id="tracking-recipient-city">-</span>, 
                                        <span id="tracking-recipient-governorate">-</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card" style="background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(241, 245, 249, 0.8) 100%); border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="fas fa-bolt me-2"></i>
                                    Actions Rapides
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" 
                                            class="btn btn-primary btn-sm" 
                                            id="refreshTrackingBtn"
                                            style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-sync me-1"></i>Actualiser le suivi
                                    </button>
                                    <button type="button" 
                                            class="btn btn-success btn-sm" 
                                            id="markDeliveredBtn" 
                                            style="display: none; background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-check-circle me-1"></i>Marquer comme livré
                                    </button>
                                    <button type="button" 
                                            class="btn btn-warning btn-sm" 
                                            id="reportAnomalyBtn"
                                            style="background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Signaler une anomalie
                                    </button>
                                    <button type="button" 
                                            class="btn btn-info btn-sm" 
                                            id="contactClientBtn"
                                            style="background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-phone-alt me-1"></i>Contacter le client
                                    </button>
                                    <button type="button" 
                                            class="btn btn-secondary btn-sm" 
                                            id="printLabelBtn" 
                                            style="display: none; background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                                        <i class="fas fa-print me-1"></i>Imprimer étiquette
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statut et progression -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card" style="border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                            <div class="card-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); border-bottom: 1px solid var(--card-border);">
                                <h6 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Progression de la livraison
                                </h6>
                            </div>
                            <div class="card-body" style="padding: 2rem;">
                                <!-- Barre de progression moderne -->
                                <div class="progress mb-4" style="height: 12px; border-radius: 6px; background: rgba(0, 0, 0, 0.1);">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         id="tracking-progress-bar" 
                                         style="width: 0%; background: linear-gradient(90deg, var(--primary-color), var(--primary-light)); border-radius: 6px; transition: width 0.6s ease;" 
                                         aria-valuenow="0" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                
                                <!-- Étapes de progression -->
                                <div class="d-flex justify-content-between align-items-center mb-4" id="tracking-steps">
                                    <!-- Les étapes seront ajoutées dynamiquement -->
                                </div>
                                
                                <!-- Informations de livraison estimée -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="bg-white p-3 rounded" style="border: 1px solid rgba(30, 64, 175, 0.1);">
                                            <small class="text-muted fw-medium">Livraison estimée</small>
                                            <div class="fw-bold text-primary" id="tracking-estimated-delivery">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white p-3 rounded" style="border: 1px solid rgba(6, 182, 212, 0.1);">
                                            <small class="text-muted fw-medium">Dernière mise à jour</small>
                                            <div class="fw-bold text-info" id="tracking-last-update">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique détaillé -->
                <div class="row">
                    <div class="col-12">
                        <div class="card" style="border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); border-bottom: 1px solid var(--card-border);">
                                <h6 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-history me-2"></i>
                                    Historique de suivi
                                </h6>
                                <small class="text-muted fw-medium" id="tracking-history-count">0 événement(s)</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="tracking-timeline" id="tracking-history-timeline">
                                    <!-- L'historique sera ajouté dynamiquement -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone de messages -->
                <div id="tracking-messages" class="mt-4">
                    <!-- Messages d'erreur/succès -->
                </div>
            </div>
            
            <div class="modal-footer" style="background: rgba(248, 250, 252, 0.5); border-top: 1px solid var(--card-border); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); padding: 1.5rem;">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal"
                            style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                    <button type="button" 
                            class="btn btn-primary" 
                            id="openOrderDetailsBtn"
                            style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-eye me-2"></i>Voir la commande
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques pour la modal de suivi détaillé */
#trackingDetailsModal .timeline {
    position: relative;
    padding: 30px 0;
    max-height: 400px;
    overflow-y: auto;
}

#trackingDetailsModal .timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--info-color), var(--card-border));
}

#trackingDetailsModal .timeline-item {
    position: relative;
    margin-bottom: 35px;
    padding-left: 70px;
}

#trackingDetailsModal .timeline-item:last-child {
    margin-bottom: 0;
}

#trackingDetailsModal .timeline-item::before {
    content: '';
    position: absolute;
    left: 21px;
    top: 8px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px var(--card-border), var(--shadow-sm);
    z-index: 2;
    background-color: var(--card-border);
    transition: all 0.3s ease;
}

#trackingDetailsModal .timeline-item.status-created::before { 
    background: linear-gradient(135deg, #6c757d, #4b5563); 
    box-shadow: 0 0 0 2px #6c757d, var(--shadow-sm);
}
#trackingDetailsModal .timeline-item.status-validated::before { 
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); 
    box-shadow: 0 0 0 2px var(--primary-color), var(--shadow-sm);
}
#trackingDetailsModal .timeline-item.status-picked_up_by_carrier::before { 
    background: linear-gradient(135deg, var(--warning-color), #d97706); 
    box-shadow: 0 0 0 2px var(--warning-color), var(--shadow-sm);
}
#trackingDetailsModal .timeline-item.status-in_transit::before { 
    background: linear-gradient(135deg, var(--info-color), #0891b2); 
    box-shadow: 0 0 0 2px var(--info-color), var(--shadow-sm);
}
#trackingDetailsModal .timeline-item.status-delivered::before { 
    background: linear-gradient(135deg, var(--success-color), #059669); 
    box-shadow: 0 0 0 2px var(--success-color), var(--shadow-sm);
    animation: pulse-success 2s infinite;
}
#trackingDetailsModal .timeline-item.status-anomaly::before { 
    background: linear-gradient(135deg, var(--danger-color), #dc2626); 
    box-shadow: 0 0 0 2px var(--danger-color), var(--shadow-sm);
}
#trackingDetailsModal .timeline-item.status-in_return::before { 
    background: linear-gradient(135deg, var(--warning-color), #d97706); 
    box-shadow: 0 0 0 2px var(--warning-color), var(--shadow-sm);
}

#trackingDetailsModal .timeline-content {
    background: rgba(248, 250, 252, 0.9);
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--info-color);
    margin-left: 15px;
    position: relative;
    transition: all 0.3s ease;
}

#trackingDetailsModal .timeline-content:hover {
    background: rgba(248, 250, 252, 1);
    box-shadow: var(--shadow-md);
    transform: translateX(5px);
}

#trackingDetailsModal .timeline-content::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 16px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 8px 0;
    border-color: transparent rgba(248, 250, 252, 0.9) transparent transparent;
}

#trackingDetailsModal .timeline-time {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
}

#trackingDetailsModal .timeline-status {
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--text-color);
    font-size: 1rem;
}

#trackingDetailsModal .timeline-location {
    font-size: 0.875rem;
    color: var(--text-color);
    margin-bottom: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
}

#trackingDetailsModal .timeline-notes {
    font-size: 0.875rem;
    color: var(--text-muted);
    font-style: italic;
    line-height: 1.4;
}

/* Styles pour les étapes de progression */
#trackingDetailsModal #tracking-steps .step {
    text-align: center;
    flex: 1;
    position: relative;
}

#trackingDetailsModal #tracking-steps .step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: var(--card-border);
    color: white;
}

#trackingDetailsModal #tracking-steps .step-icon.active {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.2);
    animation: pulse-primary 2s infinite;
}

#trackingDetailsModal #tracking-steps .step-icon.completed {
    background: linear-gradient(135deg, var(--success-color), #059669);
}

#trackingDetailsModal #tracking-steps .step-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-muted);
}

#trackingDetailsModal #tracking-steps .step-label.active {
    color: var(--primary-color);
    font-weight: 600;
}

#trackingDetailsModal #tracking-steps .step-label.completed {
    color: var(--success-color);
    font-weight: 600;
}

/* Animations */
@keyframes pulse-success {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7), var(--shadow-sm); }
    70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0), var(--shadow-sm); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0), var(--shadow-sm); }
}

@keyframes pulse-primary {
    0% { box-shadow: 0 0 0 0 rgba(30, 64, 175, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(30, 64, 175, 0); }
    100% { box-shadow: 0 0 0 0 rgba(30, 64, 175, 0); }
}

/* Styles pour les boutons d'action */
#trackingDetailsModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

#trackingDetailsModal .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Card animations */
#trackingDetailsModal .card {
    animation: slideInUp 0.3s ease-out;
    transition: all 0.3s ease;
}

#trackingDetailsModal .card:hover {
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

/* Scrollbar personnalisée */
#trackingDetailsModal .timeline::-webkit-scrollbar {
    width: 6px;
}

#trackingDetailsModal .timeline::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

#trackingDetailsModal .timeline::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--info-color), #0891b2);
    border-radius: 3px;
}

/* États de chargement */
#trackingDetailsModal .loading {
    position: relative;
    pointer-events: none;
    opacity: 0.6;
}

#trackingDetailsModal .loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 768px) {
    #trackingDetailsModal .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
    
    #trackingDetailsModal .modal-body {
        padding: 1.5rem 1rem;
    }
    
    #trackingDetailsModal .row .col-md-8,
    #trackingDetailsModal .row .col-md-4,
    #trackingDetailsModal .row .col-md-6,
    #trackingDetailsModal .row .col-md-3 {
        margin-bottom: 1rem;
    }
    
    #trackingDetailsModal .d-flex.flex-wrap {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    #trackingDetailsModal .btn-sm {
        width: 100%;
        justify-content: center;
    }
    
    #trackingDetailsModal .timeline {
        padding-left: 50px;
    }
    
    #trackingDetailsModal .timeline-item {
        padding-left: 50px;
    }
    
    #trackingDetailsModal .timeline-content {
        margin-left: 10px;
        padding: 15px;
    }
    
    #trackingDetailsModal #tracking-steps {
        flex-direction: column;
        gap: 1rem;
    }
    
    #trackingDetailsModal #tracking-steps .step {
        display: flex;
        align-items: center;
        text-align: left;
        gap: 1rem;
    }
    
    #trackingDetailsModal #tracking-steps .step-icon {
        margin: 0;
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
}

/* Animation d'entrée pour la modal */
#trackingDetailsModal.show .modal-content {
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

/* Message d'état vide */
#trackingDetailsModal .empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
}

#trackingDetailsModal .empty-state i {
    font-size: 4rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}

#trackingDetailsModal .empty-state h6 {
    margin-bottom: 0.5rem;
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('trackingDetailsModal');
    const refreshBtn = document.getElementById('refreshTrackingBtn');
    const markDeliveredBtn = document.getElementById('markDeliveredBtn');
    const reportAnomalyBtn = document.getElementById('reportAnomalyBtn');
    const contactClientBtn = document.getElementById('contactClientBtn');
    const printLabelBtn = document.getElementById('printLabelBtn');
    const openOrderBtn = document.getElementById('openOrderDetailsBtn');

    let currentShipment = null;
    let currentTrackingData = null;

    // Fonction pour ouvrir la modal avec les données d'expédition
    window.openTrackingDetailsModal = function(shipmentData) {
        currentShipment = shipmentData;
        
        // Remplir les informations de base
        populateBasicInfo(shipmentData);
        
        // Charger les détails de suivi
        loadTrackingDetails(shipmentData.tracking_number || shipmentData.pos_barcode);
        
        // Ouvrir la modal
        new bootstrap.Modal(modal).show();
    };

    // Remplir les informations de base
    function populateBasicInfo(shipment) {
        document.getElementById('tracking-number').textContent = shipment.tracking_number || shipment.pos_barcode || '-';
        document.getElementById('tracking-order-id').textContent = shipment.order_id ? `#${shipment.order_id}` : '-';
        document.getElementById('tracking-carrier').textContent = shipment.carrier_name || '-';
        document.getElementById('tracking-weight').textContent = shipment.weight ? `${shipment.weight} kg` : '-';
        document.getElementById('tracking-pieces').textContent = shipment.nb_pieces || '-';
        document.getElementById('tracking-cod-amount').textContent = shipment.cod_amount ? `${shipment.cod_amount.toFixed(3)} TND` : '-';
        document.getElementById('tracking-created-date').textContent = shipment.created_at ? 
            new Date(shipment.created_at).toLocaleDateString('fr-FR') : '-';

        // Informations du destinataire
        const recipient = shipment.recipient_info || {};
        document.getElementById('tracking-recipient-name').textContent = recipient.name || '-';
        document.getElementById('tracking-recipient-phone').textContent = recipient.phone || '-';
        document.getElementById('tracking-recipient-phone').href = recipient.phone ? `tel:${recipient.phone}` : '#';
        
        if (recipient.phone_2) {
            document.getElementById('tracking-recipient-phone2').textContent = recipient.phone_2;
            document.getElementById('tracking-recipient-phone2').href = `tel:${recipient.phone_2}`;
            document.getElementById('tracking-recipient-phone2-container').style.display = 'block';
        } else {
            document.getElementById('tracking-recipient-phone2-container').style.display = 'none';
        }
        
        document.getElementById('tracking-recipient-address').textContent = recipient.address || '-';
        document.getElementById('tracking-recipient-city').textContent = recipient.city || '-';
        document.getElementById('tracking-recipient-governorate').textContent = recipient.governorate || '-';

        // Statut actuel
        updateCurrentStatus(shipment.status, shipment.status_label);
        
        // Configurer les boutons selon le statut
        configureActionButtons(shipment);
    }

    // Charger les détails de suivi
    function loadTrackingDetails(trackingNumber) {
        if (!trackingNumber) {
            showMessage('Aucun numéro de suivi disponible', 'warning');
            showEmptyTrackingState();
            return;
        }

        showLoadingMessage('Chargement des détails de suivi...');

        axios.post(`/admin/delivery/shipments/${currentShipment.id}/track`)
            .then(response => {
                if (response.data.success) {
                    currentTrackingData = response.data.tracking;
                    populateTrackingDetails(response.data.tracking);
                    clearMessages();
                } else {
                    showMessage(response.data.message || 'Erreur lors du suivi', 'danger');
                    showEmptyTrackingState();
                }
            })
            .catch(error => {
                console.error('Erreur suivi:', error);
                showMessage('Erreur de communication avec le transporteur', 'danger');
                showEmptyTrackingState();
            });
    }

    // Remplir les détails de suivi
    function populateTrackingDetails(tracking) {
        // Mise à jour des informations générales
        document.getElementById('tracking-estimated-delivery').textContent = 
            tracking.estimated_delivery ? new Date(tracking.estimated_delivery).toLocaleDateString('fr-FR') : 'Non estimée';
        document.getElementById('tracking-last-update').textContent = 
            tracking.last_update ? new Date(tracking.last_update).toLocaleString('fr-FR') : 'Jamais';

        // Mise à jour de la progression
        updateProgressBar(tracking.carrier_status || currentShipment.status);
        updateTrackingSteps(tracking.carrier_status || currentShipment.status);

        // Mise à jour de l'historique
        updateTrackingHistory(tracking.history || []);
    }

    // Mettre à jour le statut actuel
    function updateCurrentStatus(status, label) {
        const badge = document.getElementById('tracking-current-status');
        badge.textContent = label || getStatusLabel(status) || status || 'Inconnu';
        badge.className = 'badge ' + getStatusBadgeClass(status);
    }

    // Obtenir la classe CSS pour le badge de statut
    function getStatusBadgeClass(status) {
        const styles = {
            'created': 'text-white',
            'validated': 'text-white',
            'picked_up_by_carrier': 'text-white',
            'in_transit': 'text-white',
            'delivered': 'text-white',
            'cancelled': 'text-white',
            'in_return': 'text-white',
            'anomaly': 'text-white'
        };

        const bgStyles = {
            'created': 'background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;',
            'validated': 'background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;',
            'picked_up_by_carrier': 'background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%) !important;',
            'in_transit': 'background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%) !important;',
            'delivered': 'background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%) !important;',
            'cancelled': 'background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;',
            'in_return': 'background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%) !important;',
            'anomaly': 'background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%) !important;'
        };

        // Appliquer le style directement
        setTimeout(() => {
            const badge = document.getElementById('tracking-current-status');
            if (badge) {
                badge.setAttribute('style', bgStyles[status] || bgStyles['created']);
            }
        }, 0);

        return styles[status] || styles['created'];
    }

    // Obtenir le label du statut
    function getStatusLabel(status) {
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
    }

    // Mettre à jour la barre de progression
    function updateProgressBar(status) {
        const progressBar = document.getElementById('tracking-progress-bar');
        let percentage = 0;

        switch(status) {
            case 'created':
                percentage = 10;
                break;
            case 'validated':
                percentage = 25;
                break;
            case 'picked_up_by_carrier':
                percentage = 50;
                break;
            case 'in_transit':
                percentage = 75;
                break;
            case 'delivered':
                percentage = 100;
                break;
            case 'anomaly':
            case 'in_return':
                percentage = 60;
                progressBar.style.background = 'linear-gradient(90deg, var(--danger-color), #dc2626)';
                break;
            default:
                percentage = 0;
        }

        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
    }

    // Mettre à jour les étapes de suivi
    function updateTrackingSteps(currentStatus) {
        const stepsContainer = document.getElementById('tracking-steps');
        const steps = [
            { key: 'created', icon: 'fa-plus', label: 'Créé' },
            { key: 'validated', icon: 'fa-check', label: 'Validé' },
            { key: 'picked_up_by_carrier', icon: 'fa-truck', label: 'Récupéré' },
            { key: 'in_transit', icon: 'fa-route', label: 'En transit' },
            { key: 'delivered', icon: 'fa-check-circle', label: 'Livré' }
        ];

        const stepOrder = ['created', 'validated', 'picked_up_by_carrier', 'in_transit', 'delivered'];
        const currentIndex = stepOrder.indexOf(currentStatus);

        stepsContainer.innerHTML = steps.map((step, index) => {
            let statusClass = '';
            let iconClass = '';
            let labelClass = '';

            if (index < currentIndex) {
                statusClass = 'completed';
                iconClass = 'step-icon completed';
                labelClass = 'step-label completed';
            } else if (index === currentIndex) {
                statusClass = 'active';
                iconClass = 'step-icon active';
                labelClass = 'step-label active';
            } else {
                iconClass = 'step-icon';
                labelClass = 'step-label';
            }

            return `
                <div class="step ${statusClass}">
                    <div class="${iconClass}">
                        <i class="fas ${step.icon}"></i>
                    </div>
                    <small class="${labelClass}">${step.label}</small>
                </div>
            `;
        }).join('');
    }

    // Mettre à jour l'historique de suivi
    function updateTrackingHistory(history) {
        const timeline = document.getElementById('tracking-history-timeline');
        const countElement = document.getElementById('tracking-history-count');
        
        if (!history || history.length === 0) {
            showEmptyTrackingState();
            countElement.textContent = '0 événement';
            return;
        }

        countElement.textContent = `${history.length} événement${history.length > 1 ? 's' : ''}`;

        timeline.innerHTML = history.map((event, index) => `
            <div class="timeline-item status-${event.status || 'unknown'}" style="animation: fadeInLeft 0.5s ease-out ${index * 0.1}s both;">
                <div class="timeline-content">
                    <div class="timeline-time">
                        <i class="fas fa-clock me-2"></i>
                        ${event.timestamp ? new Date(event.timestamp).toLocaleString('fr-FR') : 'Date inconnue'}
                    </div>
                    <div class="timeline-status">
                        ${event.label || event.status || 'Mise à jour'}
                    </div>
                    ${event.location ? `
                        <div class="timeline-location">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            ${event.location}
                        </div>
                    ` : ''}
                    ${event.notes ? `
                        <div class="timeline-notes">
                            ${event.notes}
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    // Afficher un état vide pour le suivi
    function showEmptyTrackingState() {
        const timeline = document.getElementById('tracking-history-timeline');
        timeline.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-route"></i>
                <h6>Aucun historique de suivi</h6>
                <p class="mb-0">
                    ${!currentShipment?.pos_barcode ? 
                        'Numéro de suivi non encore assigné' : 
                        'Historique en cours de récupération'
                    }
                </p>
                ${currentShipment?.pos_barcode ? `
                    <button class="btn btn-outline-primary mt-3" onclick="loadTrackingDetails('${currentShipment.pos_barcode}')">
                        <i class="fas fa-sync me-2"></i>
                        Récupérer l'historique
                    </button>
                ` : ''}
            </div>
        `;
    }

    // Configurer les boutons d'action
    function configureActionButtons(shipment) {
        // Marquer comme livré (seulement si en transit)
        if (['in_transit', 'picked_up_by_carrier'].includes(shipment.status)) {
            markDeliveredBtn.style.display = 'inline-block';
        } else {
            markDeliveredBtn.style.display = 'none';
        }

        // Imprimer étiquette (si supporté par le transporteur)
        if (shipment.supports_label_generation) {
            printLabelBtn.style.display = 'inline-block';
        } else {
            printLabelBtn.style.display = 'none';
        }
    }

    // Event listeners
    refreshBtn.addEventListener('click', function() {
        if (currentShipment && (currentShipment.tracking_number || currentShipment.pos_barcode)) {
            loadTrackingDetails(currentShipment.tracking_number || currentShipment.pos_barcode);
        }
    });

    markDeliveredBtn.addEventListener('click', function() {
        if (!currentShipment) return;

        Swal.fire({
            title: 'Marquer comme livré ?',
            text: 'Cette action mettra à jour le statut de l\'expédition.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: 'var(--success-color)',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, marquer comme livré',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                markAsDelivered();
            }
        });
    });

    reportAnomalyBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Signaler une anomalie',
            input: 'textarea',
            inputLabel: 'Décrivez le problème rencontré :',
            inputPlaceholder: 'Ex: Adresse incorrecte, client injoignable, etc.',
            showCancelButton: true,
            confirmButtonText: 'Signaler',
            cancelButtonText: 'Annuler',
            confirmButtonColor: 'var(--warning-color)'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                reportAnomaly(result.value);
            }
        });
    });

    contactClientBtn.addEventListener('click', function() {
        const recipient = currentShipment?.recipient_info;
        if (recipient && recipient.phone) {
            window.open(`tel:${recipient.phone}`);
        } else {
            showMessage('Numéro de téléphone non disponible', 'warning');
        }
    });

    printLabelBtn.addEventListener('click', function() {
        if (currentShipment) {
            window.open(`/admin/delivery/shipments/${currentShipment.id}/label`, '_blank');
        }
    });

    openOrderBtn.addEventListener('click', function() {
        if (currentShipment && currentShipment.order_id) {
            window.open(`/admin/orders/${currentShipment.order_id}`, '_blank');
        }
    });

    // Marquer comme livré
    function markAsDelivered() {
        const btn = markDeliveredBtn;
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Traitement...';

        axios.post(`/admin/delivery/shipments/${currentShipment.id}/mark-delivered`)
            .then(response => {
                if (response.data.success) {
                    showMessage('Expédition marquée comme livrée avec succès', 'success');
                    // Recharger les détails
                    setTimeout(() => {
                        loadTrackingDetails(currentShipment.tracking_number || currentShipment.pos_barcode);
                        configureActionButtons({ ...currentShipment, status: 'delivered' });
                    }, 1000);
                } else {
                    showMessage(response.data.message || 'Erreur lors de la mise à jour', 'danger');
                }
            })
            .catch(error => {
                console.error('Erreur marquage livré:', error);
                showMessage('Erreur de communication avec le serveur', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('loading');
                btn.innerHTML = originalContent;
            });
    }

    // Signaler une anomalie
    function reportAnomaly(reason) {
        axios.post(`/admin/delivery/shipments/${currentShipment.id}/report-anomaly`, {
            reason: reason
        })
        .then(response => {
            if (response.data.success) {
                showMessage('Anomalie signalée avec succès', 'success');
                setTimeout(() => {
                    loadTrackingDetails(currentShipment.tracking_number || currentShipment.pos_barcode);
                }, 1000);
            } else {
                showMessage(response.data.message || 'Erreur lors du signalement', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur signalement anomalie:', error);
            showMessage('Erreur de communication avec le serveur', 'danger');
        });
    }

    // Fonctions utilitaires pour les messages
    function showMessage(message, type) {
        const messagesContainer = document.getElementById('tracking-messages');
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'danger' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const bgStyle = type === 'success' ? 'background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%); border: 1px solid var(--success-color); border-left: 4px solid var(--success-color);' :
                       type === 'danger' ? 'background: linear-gradient(135deg, #fecaca 0%, #f87171 100%); border: 1px solid var(--danger-color); border-left: 4px solid var(--danger-color);' :
                       type === 'warning' ? 'background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%); border: 1px solid var(--warning-color); border-left: 4px solid var(--warning-color);' :
                       'background: linear-gradient(135deg, #cffafe 0%, #67e8f9 100%); border: 1px solid var(--info-color); border-left: 4px solid var(--info-color);';
        
        const icon = type === 'success' ? 'fas fa-check-circle' : 
                    type === 'danger' ? 'fas fa-exclamation-triangle' : 
                    type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
        
        messagesContainer.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="${bgStyle} border-radius: var(--border-radius);">
                <div class="d-flex align-items-center">
                    <i class="${icon} me-3 text-${type === 'danger' ? 'danger' : type}"></i>
                    <div class="text-${type === 'danger' ? 'danger' : type} fw-medium">${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    function showLoadingMessage(message) {
        const messagesContainer = document.getElementById('tracking-messages');
        messagesContainer.innerHTML = `
            <div class="alert alert-info" role="alert" style="background: linear-gradient(135deg, #cffafe 0%, #67e8f9 100%); border: 1px solid var(--info-color); border-radius: var(--border-radius);">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-3 text-info" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <div class="text-info fw-medium">${message}</div>
                </div>
            </div>
        `;
    }

    function clearMessages() {
        document.getElementById('tracking-messages').innerHTML = '';
    }

    // Animation CSS pour les éléments
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    `;
    document.head.appendChild(style);
});
</script>