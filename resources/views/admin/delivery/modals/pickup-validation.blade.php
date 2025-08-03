<!-- Modal de validation d'un pickup - Adaptée au layout -->
<div class="modal fade" id="pickupValidationModal" tabindex="-1" aria-labelledby="pickupValidationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); box-shadow: var(--shadow-xl); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%); border-bottom: none; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
                <h5 class="modal-title text-white fw-bold" id="pickupValidationModalLabel">
                    <i class="fas fa-truck me-2"></i>
                    Valider l'enlèvement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" style="padding: 2rem;">
                <!-- Informations du pickup -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card" style="border: 2px solid var(--primary-color); border-radius: var(--border-radius); background: rgba(30, 64, 175, 0.05);">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-bottom: none; border-radius: var(--border-radius) var(--border-radius) 0 0;">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations générales
                                </h6>
                            </div>
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted fw-medium">Pickup ID</small>
                                        <div class="fw-bold text-dark" id="validation-pickup-id">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted fw-medium">Transporteur</small>
                                        <div class="fw-bold text-dark" id="validation-carrier-name">-</div>
                                    </div>
                                </div>
                                <hr style="border-color: rgba(30, 64, 175, 0.2);">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted fw-medium">Date d'enlèvement</small>
                                        <div class="fw-bold text-dark" id="validation-pickup-date">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted fw-medium">Configuration</small>
                                        <div class="fw-bold text-dark" id="validation-integration-name">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card" style="border: 2px solid var(--success-color); border-radius: var(--border-radius); background: rgba(16, 185, 129, 0.05);">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border-bottom: none; border-radius: var(--border-radius) var(--border-radius) 0 0;">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Résumé des expéditions
                                </h6>
                            </div>
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end" style="border-color: rgba(16, 185, 129, 0.2) !important;">
                                            <h4 class="text-primary mb-1 fw-bold" id="validation-orders-count">0</h4>
                                            <small class="text-muted fw-medium">Commandes</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-1 fw-bold" id="validation-total-pieces">0</h4>
                                        <small class="text-muted fw-medium">Pièces</small>
                                    </div>
                                </div>
                                <hr style="border-color: rgba(16, 185, 129, 0.2);">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end" style="border-color: rgba(16, 185, 129, 0.2) !important;">
                                            <h5 class="text-warning mb-1 fw-bold" id="validation-total-weight">0 kg</h5>
                                            <small class="text-muted fw-medium">Poids total</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-info mb-1 fw-bold" id="validation-total-cod">0 TND</h5>
                                        <small class="text-muted fw-medium">Montant COD</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vérifications avant validation -->
                <div class="card mb-4" style="border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); border-bottom: 1px solid var(--card-border);">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-check-circle me-2"></i>
                            Vérifications automatiques
                        </h6>
                    </div>
                    <div class="card-body" style="padding: 1.5rem;">
                        <div id="validation-checks">
                            <!-- Les vérifications seront ajoutées dynamiquement -->
                            <div class="d-flex align-items-center justify-content-center py-3">
                                <div class="spinner-border text-primary me-3" role="status">
                                    <span class="visually-hidden">Vérification...</span>
                                </div>
                                <span class="text-muted">Vérification en cours...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="card" style="border: 1px solid var(--card-border); border-radius: var(--border-radius); overflow: hidden;">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); border-bottom: 1px solid var(--card-border);">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-list me-2"></i>
                            Commandes incluses
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="width: 80px; border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">ID</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Client</th>
                                        <th style="width: 120px; border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Téléphone</th>
                                        <th style="width: 100px; border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Montant</th>
                                        <th style="width: 80px; border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Pièces</th>
                                        <th style="width: 100px; border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Gouvernorat</th>
                                        <th style="width: 80px; border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Statut</th>
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
                <div class="alert mt-4" role="alert" style="background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%); border: 1px solid var(--warning-color); border-radius: var(--border-radius); border-left: 4px solid var(--warning-color);">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-warning mb-2">
                                <i class="fas fa-warning me-2"></i>
                                Attention !
                            </h6>
                            <p class="text-warning mb-0">
                                Une fois validé, ce pickup sera envoyé au transporteur et ne pourra plus être modifié. 
                                Assurez-vous que toutes les informations sont correctes avant de procéder à la validation.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Zone d'erreurs -->
                <div id="validation-errors" 
                     class="alert alert-danger mt-4 d-none" 
                     role="alert" 
                     style="background: linear-gradient(135deg, #fecaca 0%, #f87171 100%); border: 1px solid var(--danger-color); border-radius: var(--border-radius); border-left: 4px solid var(--danger-color);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle me-3 text-danger fa-lg"></i>
                        <div id="validation-error-content" class="text-danger fw-medium"></div>
                    </div>
                </div>

                <!-- Zone de succès -->
                <div id="validation-success" 
                     class="alert alert-success mt-4 d-none" 
                     role="alert" 
                     style="background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%); border: 1px solid var(--success-color); border-radius: var(--border-radius); border-left: 4px solid var(--success-color);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 text-success fa-lg"></i>
                        <div id="validation-success-content" class="text-success fw-medium"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="background: rgba(248, 250, 252, 0.5); border-top: 1px solid var(--card-border); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); padding: 1.5rem;">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal"
                            style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="button" 
                            class="btn btn-success" 
                            id="confirmValidationBtn"
                            style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-paper-plane me-2"></i>Valider et envoyer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques pour la modal de validation */
#pickupValidationModal .card {
    animation: slideInUp 0.3s ease-out;
    transition: all 0.3s ease;
}

#pickupValidationModal .card:hover {
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

/* Animation pour les vérifications */
.validation-check-item {
    animation: fadeInLeft 0.5s ease-out;
    transition: all 0.3s ease;
}

.validation-check-item:hover {
    background: rgba(248, 250, 252, 0.8);
    border-radius: var(--border-radius);
    padding: 0.5rem;
    margin: -0.5rem -0.5rem 0.5rem -0.5rem;
}

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

/* Styles pour les icônes de vérification */
.check-icon-success {
    color: var(--success-color);
    animation: checkSuccess 0.6s ease-in-out;
}

.check-icon-error {
    color: var(--danger-color);
    animation: checkError 0.6s ease-in-out;
}

.check-icon-warning {
    color: var(--warning-color);
    animation: checkWarning 0.6s ease-in-out;
}

@keyframes checkSuccess {
    0% { transform: scale(0) rotate(0deg); }
    50% { transform: scale(1.2) rotate(180deg); }
    100% { transform: scale(1) rotate(360deg); }
}

@keyframes checkError {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

@keyframes checkWarning {
    0% { transform: scale(0) rotate(-10deg); }
    50% { transform: scale(1.1) rotate(10deg); }
    100% { transform: scale(1) rotate(0deg); }
}

/* Hover effects pour les lignes du tableau */
#pickupValidationModal .table tbody tr {
    transition: all 0.2s ease;
}

#pickupValidationModal .table tbody tr:hover {
    background: rgba(30, 64, 175, 0.05) !important;
    transform: scale(1.001);
}

/* Badge personnalisé pour les statuts */
#pickupValidationModal .badge.bg-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%) !important;
}

#pickupValidationModal .badge.bg-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%) !important;
}

#pickupValidationModal .badge.bg-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%) !important;
}

/* Effet sur les boutons */
#pickupValidationModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

#pickupValidationModal .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Animation pour le bouton de validation */
#confirmValidationBtn.loading {
    position: relative;
    pointer-events: none;
}

#confirmValidationBtn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Scrollbar personnalisée */
#pickupValidationModal .table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

#pickupValidationModal .table-responsive::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

#pickupValidationModal .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
}

/* Responsive design */
@media (max-width: 768px) {
    #pickupValidationModal .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
    
    #pickupValidationModal .modal-body {
        padding: 1.5rem 1rem;
    }
    
    #pickupValidationModal .col-md-6 {
        margin-bottom: 1rem;
    }
    
    #pickupValidationModal .table-responsive {
        font-size: 0.85rem;
    }
    
    #pickupValidationModal .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    #pickupValidationModal .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Effet de pulsation pour les éléments importants */
.pulse-attention {
    animation: pulseAttention 2s infinite;
}

@keyframes pulseAttention {
    0% { 
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); 
    }
    70% { 
        box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); 
    }
    100% { 
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); 
    }
}

/* Animation d'entrée pour la modal */
#pickupValidationModal.show .modal-content {
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
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('pickupValidationModal');
    const confirmBtn = document.getElementById('confirmValidationBtn');
    let currentPickupId = null;
    let validationChecks = [];

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
        
        // Définir les vérifications
        validationChecks = [
            {
                id: 'config_check',
                label: 'Configuration transporteur active',
                status: pickupData.can_be_validated || false,
                message: pickupData.can_be_validated ? 
                    'Configuration active et valide' : 
                    'Configuration inactive ou invalide',
                icon: 'fas fa-cog'
            },
            {
                id: 'orders_check',
                label: 'Commandes disponibles',
                status: (pickupData.orders_count || 0) > 0,
                message: (pickupData.orders_count || 0) > 0 ? 
                    `${pickupData.orders_count} commande(s) prête(s)` : 
                    'Aucune commande disponible',
                icon: 'fas fa-shopping-cart'
            },
            {
                id: 'stock_check',
                label: 'Stock des produits',
                status: !pickupData.has_stock_issues,
                message: pickupData.has_stock_issues ? 
                    'Problèmes de stock détectés' : 
                    'Stock suffisant pour toutes les commandes',
                icon: 'fas fa-boxes'
            },
            {
                id: 'date_check',
                label: 'Date d\'enlèvement',
                status: !pickupData.is_overdue,
                message: pickupData.is_overdue ? 
                    `En retard de ${pickupData.days_overdue} jour(s)` : 
                    'Date d\'enlèvement valide',
                icon: 'fas fa-calendar-alt'
            },
            {
                id: 'connection_check',
                label: 'Connexion transporteur',
                status: pickupData.carrier_connection_ok || false,
                message: pickupData.carrier_connection_ok ? 
                    'Connexion au transporteur fonctionnelle' : 
                    'Vérification de la connexion transporteur...',
                icon: 'fas fa-wifi'
            }
        ];

        // Afficher les vérifications avec animation
        checksContainer.innerHTML = '<div class="row"></div>';
        const row = checksContainer.querySelector('.row');

        validationChecks.forEach((check, index) => {
            setTimeout(() => {
                const checkElement = createCheckElement(check);
                row.appendChild(checkElement);
            }, index * 200);
        });

        // Mettre à jour l'état du bouton de validation après toutes les vérifications
        setTimeout(() => {
            updateValidationButton();
        }, validationChecks.length * 200 + 500);
    }

    // Créer un élément de vérification
    function createCheckElement(check) {
        const col = document.createElement('div');
        col.className = 'col-md-6 mb-3';

        const iconClass = check.status ? 'check-icon-success' : 'check-icon-error';
        const iconName = check.status ? 'fas fa-check-circle' : 'fas fa-times-circle';
        
        col.innerHTML = `
            <div class="validation-check-item d-flex align-items-start">
                <div class="me-3">
                    <i class="${iconName} ${iconClass} fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-1">
                        <i class="${check.icon} me-2 text-muted"></i>
                        <strong class="text-dark">${check.label}</strong>
                    </div>
                    <small class="text-muted">${check.message}</small>
                </div>
            </div>
        `;

        return col;
    }

    // Mettre à jour l'état du bouton de validation
    function updateValidationButton() {
        const allChecksPass = validationChecks.every(check => check.status);
        
        confirmBtn.disabled = !allChecksPass;
        
        if (!allChecksPass) {
            confirmBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Impossible de valider';
            confirmBtn.classList.remove('btn-success');
            confirmBtn.classList.add('btn-secondary');
            confirmBtn.style.background = 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)';
        } else {
            confirmBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Valider et envoyer';
            confirmBtn.classList.remove('btn-secondary');
            confirmBtn.classList.add('btn-success');
            confirmBtn.style.background = 'linear-gradient(135deg, var(--success-color) 0%, #059669 100%)';
        }
    }

    // Remplir la liste des commandes
    function populateOrdersList(orders) {
        const tbody = document.getElementById('validation-orders-list');
        
        if (orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                        <h6 class="text-muted">Aucune commande trouvée</h6>
                        <p class="text-muted mb-0">Cet enlèvement ne contient aucune commande</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = orders.map((order, index) => {
            const hasStockIssues = order.has_stock_issues || !order.can_be_shipped;
            const delay = index * 100;
            
            return `
                <tr style="animation: fadeInUp 0.5s ease-out ${delay}ms both;">
                    <td style="padding: 0.75rem;"><strong class="text-primary">#${order.id}</strong></td>
                    <td style="padding: 0.75rem;">${order.customer_name || '-'}</td>
                    <td style="padding: 0.75rem;">${order.customer_phone || '-'}</td>
                    <td style="padding: 0.75rem;"><strong class="text-success">${(order.total_price || 0).toFixed(3)} TND</strong></td>
                    <td style="padding: 0.75rem;"><span class="badge bg-secondary">${order.items_count || 0}</span></td>
                    <td style="padding: 0.75rem;">${order.region_name || '-'}</td>
                    <td style="padding: 0.75rem;">
                        <span class="badge ${hasStockIssues ? 'bg-danger' : 'bg-success'}">
                            <i class="fas ${hasStockIssues ? 'fa-exclamation-triangle' : 'fa-check'} me-1"></i>
                            ${hasStockIssues ? 'Stock insuffisant' : 'Prêt'}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Réinitialiser l'état de validation
    function resetValidationState() {
        document.getElementById('validation-errors').classList.add('d-none');
        document.getElementById('validation-success').classList.add('d-none');
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('loading');
    }

    // Confirmer la validation
    confirmBtn.addEventListener('click', function() {
        if (!currentPickupId || confirmBtn.disabled) return;

        // Afficher l'état de chargement
        const originalContent = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.classList.add('loading');
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
                        // Recharger la page pour mettre à jour les données
                        window.location.reload();
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
                confirmBtn.classList.remove('loading');
                confirmBtn.innerHTML = originalContent;
            });
    });

    // Afficher un message d'erreur
    function showValidationError(message) {
        const errorDiv = document.getElementById('validation-errors');
        document.getElementById('validation-error-content').textContent = message;
        errorDiv.classList.remove('d-none');
        document.getElementById('validation-success').classList.add('d-none');
        
        // Scroll vers l'erreur
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Afficher un message de succès
    function showValidationSuccess(message) {
        const successDiv = document.getElementById('validation-success');
        document.getElementById('validation-success-content').textContent = message;
        successDiv.classList.remove('d-none');
        document.getElementById('validation-errors').classList.add('d-none');
        
        // Scroll vers le succès
        successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Animation CSS pour les lignes du tableau
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
});
</script>