{{-- resources/views/admin/process/examination-modals.blade.php --}}

<!-- Modal Division de Commande -->
<div class="modal fade" id="splitModal" tabindex="-1" aria-labelledby="splitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="splitModalLabel">
                    <i class="fas fa-cut"></i>
                    Diviser la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Division de commande #<span id="split-order-number">0</span></strong><br>
                    Cette action va créer une nouvelle commande avec les produits disponibles et suspendre la commande actuelle avec les produits problématiques.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-check-circle me-2"></i>
                                Produits disponibles
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-success mb-0" id="split-available-count">0</h4>
                                <small class="text-muted">Nouvelle commande créée</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Produits problématiques
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-warning mb-0" id="split-problem-count">0</h4>
                                <small class="text-muted">Commande suspendue</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="split-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Raison de la division <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="split-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous divisez cette commande (ex: Rupture de stock sur certains produits, produits inactifs, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette note sera ajoutée à l'historique des deux commandes.
                    </small>
                </div>
                
                <input type="hidden" id="splitOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitSplit()">
                    <i class="fas fa-cut me-2"></i>Diviser la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annulation -->
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
                    <strong>Attention !</strong> Cette action va définitivement annuler la commande #<span id="cancel-order-number">0</span>. Cette action ne peut pas être annulée.
                </div>
                
                <div class="form-group">
                    <label for="cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi cette commande est annulée (ex: Problèmes de stock insurmontables, client injoignable, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces informations seront utiles pour les statistiques et l'amélioration du service.
                    </small>
                </div>
                
                <input type="hidden" id="cancelOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </button>
                <button type="button" class="btn btn-danger" onclick="submitCancel()">
                    <i class="fas fa-times-circle me-2"></i>Confirmer l'annulation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Suspension -->
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suspendModalLabel">
                    <i class="fas fa-pause-circle"></i>
                    Suspendre la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-pause-circle me-2"></i>
                    Cette action va suspendre temporairement la commande #<span id="suspend-order-number">0</span>. 
                    Elle pourra être réactivée plus tard depuis l'interface des commandes suspendues.
                </div>
                
                <div class="form-group">
                    <label for="suspend-notes" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-2"></i>
                        Raison de la suspension <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="suspend-notes" rows="4" 
                              placeholder="Expliquez pourquoi cette commande est suspendue (ex: En attente de réapprovisionnement, problème temporaire, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette raison sera visible dans l'interface des commandes suspendues.
                    </small>
                </div>
                
                <input type="hidden" id="suspendOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="submitSuspend()">
                    <i class="fas fa-pause-circle me-2"></i>Suspendre la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Réactivation -->
<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactivateModalLabel">
                    <i class="fas fa-play-circle"></i>
                    Réactiver la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Cette action va réactiver la commande #<span id="reactivate-order-number">0</span> 
                    et la remettre dans le circuit normal de traitement.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Vérification :</strong> Assurez-vous que tous les problèmes de stock ont été résolus avant de réactiver cette commande.
                </div>
                
                <div class="form-group">
                    <label for="reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="reactivate-notes" rows="4" 
                              placeholder="Confirmez que les problèmes sont résolus (ex: Stock reconstitué, produits réactivés, problème résolu, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette commande sera remise dans la file standard après réactivation.
                    </small>
                </div>
                
                <input type="hidden" id="reactivateOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitReactivate()">
                    <i class="fas fa-play-circle me-2"></i>Réactiver maintenant
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux modales d'examen */
.modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
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

.alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    border-left: 4px solid currentColor;
}

.alert-info {
    background: linear-gradient(135deg, #cffafe 0%, #67e8f9 100%);
    color: #0c4a6e;
    border-left-color: #06b6d4;
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

.alert-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
    color: #92400e;
    border-left-color: #f59e0b;
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

.btn-primary {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
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

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

.card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.card-header {
    font-weight: 600;
    border: none;
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
// Fonctions pour les modales d'examen

window.submitSplit = function() {
    const orderId = $('#splitOrderId').val();
    const notes = $('#split-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la division', 'error');
        return;
    }
    
    const submitBtn = $('#splitModal .btn-primary');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Division...');
    
    $.post(`/admin/process/examination/split/${orderId}`, { notes: notes })
        .done(function(response) {
            $('#splitModal').modal('hide');
            showNotification(response.message, 'success');
            
            setTimeout(() => {
                refreshOrders();
            }, 1000);
        })
        .fail(function(xhr) {
            let errorMessage = 'Erreur lors de la division';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showNotification(errorMessage, 'error');
        })
        .always(function() {
            submitBtn.prop('disabled', false).html(originalText);
        });
};

window.submitCancel = function() {
    const orderId = $('#cancelOrderId').val();
    const notes = $('#cancel-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour l\'annulation', 'error');
        return;
    }
    
    processExaminationAction(orderId, 'cancel', notes, '#cancelModal');
};

window.submitSuspend = function() {
    const orderId = $('#suspendOrderId').val();
    const notes = $('#suspend-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la suspension', 'error');
        return;
    }
    
    processExaminationAction(orderId, 'suspend', notes, '#suspendModal');
};

window.submitReactivate = function() {
    const orderId = $('#reactivateOrderId').val();
    const notes = $('#reactivate-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la réactivation', 'error');
        return;
    }
    
    processExaminationAction(orderId, 'reactivate', notes, '#reactivateModal');
};

function processExaminationAction(orderId, action, notes, modalSelector) {
    const submitBtn = $(modalSelector + ' .btn-primary, ' + modalSelector + ' .btn-success, ' + modalSelector + ' .btn-danger, ' + modalSelector + ' .btn-warning');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traitement...');
    
    $.post(`/admin/process/examination/action/${orderId}`, {
        action: action,
        notes: notes
    })
    .done(function(response) {
        $(modalSelector).modal('hide');
        showNotification(response.message, 'success');
        
        setTimeout(() => {
            refreshOrders();
        }, 1000);
    })
    .fail(function(xhr) {
        let errorMessage = 'Erreur lors du traitement';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showNotification(errorMessage, 'error');
    })
    .always(function() {
        submitBtn.prop('disabled', false).html(originalText);
    });
}
</script>