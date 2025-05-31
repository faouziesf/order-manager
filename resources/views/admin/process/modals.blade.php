{{-- resources/views/admin/process/modals.blade.php --}}

<!-- Modal Ne répond pas / Rappeler -->
<div class="modal fade" id="callModal" tabindex="-1" aria-labelledby="callModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="callModalLabel">
                    <i class="fas fa-phone-slash"></i>
                    Client ne répond pas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Cette action va incrémenter le compteur de tentatives et programmer un rappel automatique selon les paramètres configurés.
                </div>
                
                <div class="form-group">
                    <label for="call-notes" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-2"></i>
                        Notes de l'appel <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="call-notes" rows="4" 
                              placeholder="Décrivez brièvement la situation (ex: Téléphone éteint, pas de réponse, occupé, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces notes seront ajoutées à l'historique de la commande.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="submitCallAction()">
                    <i class="fas fa-save me-2"></i>Enregistrer et passer au suivant
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmer -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-check-circle"></i>
                    Confirmer la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Le client a confirmé sa commande. Veuillez vérifier les informations et définir le prix final.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="confirm-price" class="form-label fw-bold">
                                <i class="fas fa-tag me-2"></i>
                                Prix confirmé <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="confirm-price" 
                                       step="0.001" min="0" required 
                                       placeholder="0.000">
                                <span class="input-group-text">TND</span>
                            </div>
                            <small class="form-text text-muted">
                                Prix final négocié avec le client (frais de livraison inclus)
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="confirm-shipping" class="form-label fw-bold">
                                <i class="fas fa-truck me-2"></i>
                                Frais de livraison
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="confirm-shipping" 
                                       step="0.001" min="0" value="0" 
                                       placeholder="0.000">
                                <span class="input-group-text">TND</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de confirmation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="confirm-notes" rows="3" 
                              placeholder="Détails de la conversation avec le client, modifications apportées, etc." 
                              required></textarea>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle me-2"></i>Résumé de la commande</h6>
                    <div id="confirm-summary">
                        <!-- Le résumé sera généré par JavaScript -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitConfirmAction()">
                    <i class="fas fa-check-circle me-2"></i>Confirmer la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annuler -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">
                    <i class="fas fa-times-circle"></i>
                    Annuler la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action va définitivement annuler la commande. Cette action ne peut pas être annulée.
                </div>
                
                <div class="form-group">
                    <label for="cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi le client annule sa commande (prix trop élevé, changement d'avis, problème de livraison, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces informations seront utiles pour les statistiques et l'amélioration du service.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </button>
                <button type="button" class="btn btn-danger" onclick="submitCancelAction()">
                    <i class="fas fa-times-circle me-2"></i>Confirmer l'annulation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dater -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">
                    <i class="fas fa-calendar-plus"></i>
                    Programmer un rappel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Cette action va programmer la commande pour un rappel à une date spécifique et réinitialiser les compteurs de tentatives.
                </div>
                
                <div class="form-group mb-3">
                    <label for="schedule-date" class="form-label fw-bold">
                        <i class="fas fa-calendar me-2"></i>
                        Date de rappel <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="schedule-date" required>
                    <small class="form-text text-muted">
                        La commande réapparaîtra dans la file "Datée" à partir de cette date.
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="schedule-notes" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-2"></i>
                        Notes et instructions <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="schedule-notes" rows="4" 
                              placeholder="Pourquoi programmer ce rappel ? Instructions spéciales pour le prochain appel..." 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces notes aideront lors du prochain traitement de cette commande.
                    </small>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-redo me-2"></i>
                    <strong>Note importante :</strong> Les compteurs de tentatives seront remis à zéro après cette action.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-info" onclick="submitScheduleAction()">
                    <i class="fas fa-calendar-plus me-2"></i>Programmer le rappel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historique -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history"></i>
                    Historique de la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="history-content" >
                    <!-- L'historique sera chargé ici par JavaScript -->
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-3 text-muted">Chargement de l'historique...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux modales */
.modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 2rem;
    border: none;
    position: relative;
    overflow: hidden;
}

.modal-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 100%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    transform: rotate(15deg);
}

.modal-title {
    font-weight: 700;
    font-size: 1.25rem;
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
    position: relative;
    z-index: 2;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1.5rem 2rem;
    border: none;
    background: #f9fafb;
}

.form-control {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.input-group-text {
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    border-left: none;
    color: #6b7280;
    font-weight: 600;
}

.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    border-left: 4px solid currentColor;
}

.alert-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
    color: #92400e;
    border-left-color: #f59e0b;
}

.alert-success {
    background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
    color: #166534;
    border-left-color: #10b981;
}

.alert-danger {
    background: linear-gradient(135deg, #fecaca 0%, #f87171 100%);
    color: #991b1b;
    border-left-color: #ef4444;
}

.alert-info {
    background: linear-gradient(135deg, #cffafe 0%, #67e8f9 100%);
    color: #0c4a6e;
    border-left-color: #06b6d4;
}

.btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 10px 20px;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

/* Styles pour l'historique */
.history-entry {
    border-left: 3px solid #667eea !important;
    padding-left: 1rem !important;
    margin-bottom: 1.5rem !important;
    background: #f8fafc;
    border-radius: 8px;
    padding: 1rem;
}

.history-entry h6 {
    color: #667eea;
    font-weight: 700;
}

.history-entry .bg-light {
    background: #e2e8f0 !important;
    border-radius: 8px;
    padding: 0.75rem;
    border-left: 3px solid #cbd5e1;
}

/* Animation pour les modales */
.modal.fade .modal-dialog {
    transform: scale(0.8) translateY(-50px);
    transition: all 0.3s ease;
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}
</style>

<script>
// Fonctions pour traiter les actions des modales

function submitCallAction() {
    const notes = $('#call-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir des notes pour cette tentative', 'error');
        return;
    }
    
    const formData = {
        notes: notes
    };
    
    processAction('call', formData);
}

function submitConfirmAction() {
    const price = $('#confirm-price').val();
    const shipping = $('#confirm-shipping').val() || 0;
    const notes = $('#confirm-notes').val().trim();
    
    if (!price || !notes) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    if (parseFloat(price) <= 0) {
        showNotification('Le prix confirmé doit être supérieur à 0', 'error');
        return;
    }
    
    const formData = {
        confirmed_price: parseFloat(price),
        shipping_cost: parseFloat(shipping),
        notes: notes
    };
    
    processAction('confirm', formData);
}

function submitCancelAction() {
    const notes = $('#cancel-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez indiquer la raison de l\'annulation', 'error');
        return;
    }
    
    // Demander confirmation
    if (!confirm('Êtes-vous sûr de vouloir annuler définitivement cette commande ?')) {
        return;
    }
    
    const formData = {
        notes: notes
    };
    
    processAction('cancel', formData);
}

function submitScheduleAction() {
    const date = $('#schedule-date').val();
    const notes = $('#schedule-notes').val().trim();
    
    if (!date || !notes) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }
    
    // Vérifier que la date est dans le futur
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate <= today) {
        showNotification('La date de rappel doit être dans le futur', 'error');
        return;
    }
    
    const formData = {
        scheduled_date: date,
        notes: notes
    };
    
    processAction('schedule', formData);
}

// Mise à jour automatique du résumé de confirmation
$(document).ready(function() {
    $('#confirmModal').on('show.bs.modal', function() {
        updateConfirmSummary();
    });
    
    function updateConfirmSummary() {
        if (!window.cartItems || cartItems.length === 0) {
            $('#confirm-summary').html('<p class="text-muted">Aucun produit dans le panier</p>');
            return;
        }
        
        let summary = '<ul class="list-unstyled mb-0">';
        let total = 0;
        
        cartItems.forEach(item => {
            const itemTotal = item.quantity * item.unit_price;
            total += itemTotal;
            
            summary += `
                <li class="d-flex justify-content-between py-1">
                    <span>${item.product?.name || 'Produit'} × ${item.quantity}</span>
                    <span class="fw-bold">${itemTotal.toFixed(3)} TND</span>
                </li>
            `;
        });
        
        summary += `
            <li class="d-flex justify-content-between py-2 border-top mt-2 fw-bold">
                <span>Total calculé:</span>
                <span>${total.toFixed(3)} TND</span>
            </li>
        `;
        
        summary += '</ul>';
        
        $('#confirm-summary').html(summary);
    }
});
</script>