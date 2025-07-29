<!-- Modal des détails de suivi d'expédition -->
<div class="modal fade" id="trackingDetailsModal" tabindex="-1" aria-labelledby="trackingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="trackingDetailsModalLabel">
                    <i class="fas fa-route me-2"></i>Suivi détaillé de l'expédition
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Informations principales -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de l'expédition</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Numéro de suivi</small>
                                        <div class="fw-bold" id="tracking-number">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Commande</small>
                                        <div class="fw-bold" id="tracking-order-id">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Transporteur</small>
                                        <div class="fw-bold" id="tracking-carrier">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Statut actuel</small>
                                        <span class="badge" id="tracking-current-status">-</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Poids</small>
                                        <div class="fw-bold" id="tracking-weight">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Nombre de pièces</small>
                                        <div class="fw-bold" id="tracking-pieces">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Montant COD</small>
                                        <div class="fw-bold" id="tracking-cod-amount">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Date création</small>
                                        <div class="fw-bold" id="tracking-created-date">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Destinataire</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong id="tracking-recipient-name">-</strong>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-phone me-2 text-muted"></i>
                                    <span id="tracking-recipient-phone">-</span>
                                </div>
                                <div class="mb-2" id="tracking-recipient-phone2-container" style="display: none;">
                                    <i class="fas fa-phone me-2 text-muted"></i>
                                    <span id="tracking-recipient-phone2">-</span>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                    <span id="tracking-recipient-address">-</span>
                                </div>
                                <div>
                                    <i class="fas fa-map me-2 text-muted"></i>
                                    <span id="tracking-recipient-city">-</span>, 
                                    <span id="tracking-recipient-governorate">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
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
                        </div>
                    </div>
                </div>

                <!-- Statut et progression -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Progression de la livraison</h6>
                            </div>
                            <div class="card-body">
                                <!-- Barre de progression -->
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" id="tracking-progress-bar" 
                                         style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                
                                <!-- Étapes -->
                                <div class="d-flex justify-content-between align-items-center" id="tracking-steps">
                                    <!-- Les étapes seront ajoutées dynamiquement -->
                                </div>
                                
                                <!-- Informations de livraison estimée -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">Livraison estimée</small>
                                        <div class="fw-bold" id="tracking-estimated-delivery">-</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Dernière mise à jour</small>
                                        <div class="fw-bold" id="tracking-last-update">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique détaillé -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Historique de suivi</h6>
                                <small class="text-muted" id="tracking-history-count">0 événement(s)</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="timeline" id="tracking-history-timeline">
                                    <!-- L'historique sera ajouté dynamiquement -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zone de messages -->
                <div id="tracking-messages" class="mt-3">
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

<!-- Styles CSS pour la timeline -->
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
        max-height: 400px;
        overflow-y: auto;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 70px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 21px;
        top: 8px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
        z-index: 2;
    }

    .timeline-item.status-created::before { background-color: #6c757d; }
    .timeline-item.status-validated::before { background-color: #0d6efd; }
    .timeline-item.status-picked_up_by_carrier::before { background-color: #fd7e14; }
    .timeline-item.status-in_transit::before { background-color: #0dcaf0; }
    .timeline-item.status-delivered::before { background-color: #198754; }
    .timeline-item.status-anomaly::before { background-color: #dc3545; }
    .timeline-item.status-in_return::before { background-color: #ffc107; }

    .timeline-content {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .timeline-time {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .timeline-status {
        font-weight: 600;
        margin-bottom: 8px;
    }

    .timeline-location {
        font-size: 0.875rem;
        color: #495057;
        margin-bottom: 5px;
    }

    .timeline-notes {
        font-size: 0.875rem;
        color: #6c757d;
        font-style: italic;
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
        
        if (recipient.phone_2) {
            document.getElementById('tracking-recipient-phone2').textContent = recipient.phone_2;
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
                }
            })
            .catch(error => {
                console.error('Erreur suivi:', error);
                showMessage('Erreur de communication avec le transporteur', 'danger');
            });
    }

    // Remplir les détails de suivi
    function populateTrackingDetails(tracking) {
        // Mise à jour des informations générales
        document.getElementById('tracking-estimated-delivery').textContent = 
            tracking.estimated_delivery ? new Date(tracking.estimated_delivery).toLocaleDateString('fr-FR') : '-';
        document.getElementById('tracking-last-update').textContent = 
            tracking.last_update ? new Date(tracking.last_update).toLocaleString('fr-FR') : '-';

        // Mise à jour de la progression
        updateProgressBar(tracking.carrier_status);
        updateTrackingSteps(tracking.carrier_status);

        // Mise à jour de l'historique
        updateTrackingHistory(tracking.history || []);
    }

    // Mettre à jour le statut actuel
    function updateCurrentStatus(status, label) {
        const badge = document.getElementById('tracking-current-status');
        badge.textContent = label || status || '-';
        badge.className = 'badge ' + getStatusBadgeClass(status);
    }

    // Obtenir la classe CSS pour le badge de statut
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'created': return 'bg-secondary';
            case 'validated': return 'bg-primary';
            case 'picked_up_by_carrier': return 'bg-warning';
            case 'in_transit': return 'bg-info';
            case 'delivered': return 'bg-success';
            case 'cancelled': return 'bg-secondary';
            case 'in_return': return 'bg-warning';
            case 'anomaly': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    // Mettre à jour la barre de progression
    function updateProgressBar(status) {
        const progressBar = document.getElementById('tracking-progress-bar');
        let percentage = 0;
        let className = 'progress-bar';

        switch(status) {
            case 'created':
                percentage = 10;
                className += ' bg-secondary';
                break;
            case 'validated':
                percentage = 25;
                className += ' bg-primary';
                break;
            case 'picked_up_by_carrier':
                percentage = 50;
                className += ' bg-warning';
                break;
            case 'in_transit':
                percentage = 75;
                className += ' bg-info';
                break;
            case 'delivered':
                percentage = 100;
                className += ' bg-success';
                break;
            case 'anomaly':
            case 'in_return':
                percentage = 60;
                className += ' bg-danger';
                break;
            default:
                percentage = 0;
                className += ' bg-secondary';
        }

        progressBar.style.width = percentage + '%';
        progressBar.className = className;
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
            if (index <= currentIndex) {
                statusClass = index === currentIndex ? 'text-primary' : 'text-success';
            } else {
                statusClass = 'text-muted';
            }

            return `
                <div class="text-center">
                    <div class="mb-2">
                        <i class="fas ${step.icon} fs-4 ${statusClass}"></i>
                    </div>
                    <small class="${statusClass}">${step.label}</small>
                </div>
            `;
        }).join('');
    }

    // Mettre à jour l'historique de suivi
    function updateTrackingHistory(history) {
        const timeline = document.getElementById('tracking-history-timeline');
        const countElement = document.getElementById('tracking-history-count');
        
        if (!history || history.length === 0) {
            timeline.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-2"></i>Aucun historique de suivi disponible
                </div>
            `;
            countElement.textContent = '0 événement';
            return;
        }

        countElement.textContent = `${history.length} événement${history.length > 1 ? 's' : ''}`;

        timeline.innerHTML = history.map(event => `
            <div class="timeline-item status-${event.status || 'unknown'}">
                <div class="timeline-content">
                    <div class="timeline-time">
                        <i class="fas fa-clock me-1"></i>
                        ${event.timestamp ? new Date(event.timestamp).toLocaleString('fr-FR') : '-'}
                    </div>
                    <div class="timeline-status">
                        ${event.label || event.status || 'Mise à jour'}
                    </div>
                    ${event.location ? `
                        <div class="timeline-location">
                            <i class="fas fa-map-marker-alt me-1"></i>
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

    // Event listeners pour les boutons d'action
    refreshBtn.addEventListener('click', function() {
        if (currentShipment && currentShipment.tracking_number) {
            loadTrackingDetails(currentShipment.tracking_number);
        }
    });

    markDeliveredBtn.addEventListener('click', function() {
        if (!currentShipment) return;

        Swal.fire({
            title: 'Marquer comme livré ?',
            text: 'Cette action mettra à jour le statut de l\'expédition.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
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
            cancelButtonText: 'Annuler'
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Traitement...';

        axios.post(`/admin/delivery/shipments/${currentShipment.id}/mark-delivered`)
            .then(response => {
                if (response.data.success) {
                    showMessage('Expédition marquée comme livrée avec succès', 'success');
                    // Recharger les détails
                    loadTrackingDetails(currentShipment.tracking_number);
                    configureActionButtons({ ...currentShipment, status: 'delivered' });
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
                loadTrackingDetails(currentShipment.tracking_number);
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
        messagesContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'danger' ? 'fa-exclamation-triangle' : 'fa-info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    function showLoadingMessage(message) {
        const messagesContainer = document.getElementById('tracking-messages');
        messagesContainer.innerHTML = `
            <div class="alert alert-info" role="alert">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-3" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    ${message}
                </div>
            </div>
        `;
    }

    function clearMessages() {
        document.getElementById('tracking-messages').innerHTML = '';
    }
});
</script>