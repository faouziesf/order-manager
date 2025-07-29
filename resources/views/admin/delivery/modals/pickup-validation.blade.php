<!-- Modal de validation d'un pickup -->
<div class="modal fade" id="pickupValidationModal" tabindex="-1" aria-labelledby="pickupValidationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="pickupValidationModalLabel">
                    <i class="fas fa-truck me-2"></i>Valider l'enlèvement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Informations du pickup -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">Pickup ID</small>
                                        <div class="fw-bold" id="validation-pickup-id">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Transporteur</small>
                                        <div class="fw-bold" id="validation-carrier-name">-</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">Date d'enlèvement</small>
                                        <div class="fw-bold" id="validation-pickup-date">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Configuration</small>
                                        <div class="fw-bold" id="validation-integration-name">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Résumé des expéditions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-1" id="validation-orders-count">0</h4>
                                            <small class="text-muted">Commandes</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-1" id="validation-total-pieces">0</h4>
                                        <small class="text-muted">Pièces</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h5 class="text-warning mb-1" id="validation-total-weight">0 kg</h5>
                                            <small class="text-muted">Poids total</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-info mb-1" id="validation-total-cod">0 TND</h5>
                                        <small class="text-muted">Montant COD</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vérifications avant validation -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Vérifications automatiques</h6>
                    </div>
                    <div class="card-body">
                        <div id="validation-checks">
                            <!-- Les vérifications seront ajoutées dynamiquement -->
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Commandes incluses</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Client</th>
                                        <th style="width: 120px;">Téléphone</th>
                                        <th style="width: 100px;">Montant</th>
                                        <th style="width: 80px;">Pièces</th>
                                        <th style="width: 100px;">Gouvernorat</th>
                                        <th style="width: 80px;">Statut</th>
                                    </tr>
                                </thead>
                                <tbody id="validation-orders-list">
                                    <!-- Les commandes seront ajoutées dynamiquement -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Alerte d'avertissement -->
                <div class="alert alert-warning mt-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                        <div>
                            <strong>Attention !</strong><br>
                            Une fois validé, ce pickup sera envoyé au transporteur et ne pourra plus être modifié. 
                            Assurez-vous que toutes les informations sont correctes.
                        </div>
                    </div>
                </div>

                <!-- Zone d'erreurs -->
                <div id="validation-errors" class="alert alert-danger d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle me-3 fs-4"></i>
                        <div id="validation-error-content"></div>
                    </div>
                </div>

                <!-- Zone de succès -->
                <div id="validation-success" class="alert alert-success d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4"></i>
                        <div id="validation-success-content"></div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('pickupValidationModal');
    const confirmBtn = document.getElementById('confirmValidationBtn');
    let currentPickupId = null;

    // Fonction pour ouvrir la modal avec les données du pickup
    window.openPickupValidationModal = function(pickupData) {
        currentPickupId = pickupData.id;
        
        // Remplir les informations générales
        document.getElementById('validation-pickup-id').textContent = '#' + pickupData.id;
        document.getElementById('validation-carrier-name').textContent = pickupData.carrier_name || '-';
        document.getElementById('validation-pickup-date').textContent = pickupData.pickup_date || '-';
        document.getElementById('validation-integration-name').textContent = pickupData.integration_name || '-';
        
        // Remplir les statistiques
        document.getElementById('validation-orders-count').textContent = pickupData.orders_count || 0;
        document.getElementById('validation-total-pieces').textContent = pickupData.total_pieces || 0;
        document.getElementById('validation-total-weight').textContent = (pickupData.total_weight || 0) + ' kg';
        document.getElementById('validation-total-cod').textContent = (pickupData.total_cod_amount || 0).toFixed(3) + ' TND';
        
        // Effectuer les vérifications
        performValidationChecks(pickupData);
        
        // Remplir la liste des commandes
        populateOrdersList(pickupData.orders || []);
        
        // Réinitialiser l'état des boutons et messages
        resetValidationState();
        
        // Ouvrir la modal
        new bootstrap.Modal(modal).show();
    };

    // Effectuer les vérifications automatiques
    function performValidationChecks(pickupData) {
        const checksContainer = document.getElementById('validation-checks');
        const checks = [
            {
                label: 'Configuration transporteur active',
                status: pickupData.can_be_validated,
                message: pickupData.can_be_validated ? 'Configuration active et valide' : 'Configuration inactive ou invalide'
            },
            {
                label: 'Commandes disponibles',
                status: (pickupData.orders_count || 0) > 0,
                message: (pickupData.orders_count || 0) > 0 ? `${pickupData.orders_count} commande(s) prête(s)` : 'Aucune commande disponible'
            },
            {
                label: 'Stock des produits',
                status: !pickupData.has_stock_issues,
                message: pickupData.has_stock_issues ? 'Problèmes de stock détectés' : 'Stock suffisant pour toutes les commandes'
            },
            {
                label: 'Date d\'enlèvement',
                status: !pickupData.is_overdue,
                message: pickupData.is_overdue ? `En retard de ${pickupData.days_overdue} jour(s)` : 'Date d\'enlèvement valide'
            }
        ];

        checksContainer.innerHTML = checks.map(check => `
            <div class="d-flex align-items-center mb-2">
                <i class="fas ${check.status ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'} me-3"></i>
                <div class="flex-grow-1">
                    <strong>${check.label}</strong><br>
                    <small class="text-muted">${check.message}</small>
                </div>
            </div>
        `).join('');

        // Activer/désactiver le bouton selon les vérifications
        const allChecksPass = checks.every(check => check.status);
        confirmBtn.disabled = !allChecksPass;
        
        if (!allChecksPass) {
            confirmBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Impossible de valider';
            confirmBtn.classList.remove('btn-success');
            confirmBtn.classList.add('btn-secondary');
        } else {
            confirmBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Valider et envoyer';
            confirmBtn.classList.remove('btn-secondary');
            confirmBtn.classList.add('btn-success');
        }
    }

    // Remplir la liste des commandes
    function populateOrdersList(orders) {
        const tbody = document.getElementById('validation-orders-list');
        
        if (orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox me-2"></i>Aucune commande trouvée
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr>
                <td><strong>#${order.id}</strong></td>
                <td>${order.customer_name || '-'}</td>
                <td>${order.customer_phone || '-'}</td>
                <td><strong>${(order.total_price || 0).toFixed(3)} TND</strong></td>
                <td><span class="badge bg-secondary">${order.items_count || 0}</span></td>
                <td>${order.region_name || '-'}</td>
                <td>
                    <span class="badge ${order.has_stock_issues ? 'bg-danger' : 'bg-success'}">
                        ${order.has_stock_issues ? 'Stock insuffisant' : 'Prêt'}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    // Réinitialiser l'état de validation
    function resetValidationState() {
        document.getElementById('validation-errors').classList.add('d-none');
        document.getElementById('validation-success').classList.add('d-none');
        confirmBtn.disabled = false;
    }

    // Confirmer la validation
    confirmBtn.addEventListener('click', function() {
        if (!currentPickupId || confirmBtn.disabled) return;

        // Afficher l'état de chargement
        const originalContent = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Validation en cours...';

        // Appel API pour valider le pickup
        axios.post(`/admin/delivery/pickups/${currentPickupId}/validate`)
            .then(response => {
                if (response.data.success) {
                    showValidationSuccess(response.data.message || 'Pickup validé avec succès');
                    
                    // Fermer la modal après 2 secondes
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(modal).hide();
                        if (typeof refreshPickupsList === 'function') {
                            refreshPickupsList();
                        }
                    }, 2000);
                } else {
                    showValidationError(response.data.message || 'Erreur lors de la validation');
                }
            })
            .catch(error => {
                console.error('Erreur validation pickup:', error);
                const message = error.response?.data?.message || 'Erreur de communication avec le serveur';
                showValidationError(message);
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalContent;
            });
    });

    // Afficher un message d'erreur
    function showValidationError(message) {
        const errorDiv = document.getElementById('validation-errors');
        document.getElementById('validation-error-content').textContent = message;
        errorDiv.classList.remove('d-none');
        document.getElementById('validation-success').classList.add('d-none');
    }

    // Afficher un message de succès
    function showValidationSuccess(message) {
        const successDiv = document.getElementById('validation-success');
        document.getElementById('validation-success-content').textContent = message;
        successDiv.classList.remove('d-none');
        document.getElementById('validation-errors').classList.add('d-none');
    }
});
</script>