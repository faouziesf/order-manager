{{-- resources/views/admin/process/suspended-modals.blade.php --}}

<!-- Modal Réactivation de Commande Suspendue -->
<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactivateModalLabel">
                    <i class="fas fa-play-circle"></i>
                    Réactiver la commande suspendue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Réactivation de la commande #<span id="reactivate-order-number">0</span></strong><br>
                    Cette action va réactiver la commande et la remettre dans le circuit normal de traitement.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Vérification importante :</strong> Assurez-vous que tous les problèmes ayant causé la suspension ont été résolus avant de réactiver cette commande.
                </div>
                
                <div class="form-group">
                    <label for="reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="reactivate-notes" rows="4" 
                              placeholder="Confirmez que les problèmes sont résolus (ex: Stock reconstitué, produits réactivés, problème technique résolu, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette commande sera remise dans la file standard après réactivation et pourra être traitée normalement.
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

<!-- Modal Annulation de Commande Suspendue -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">
                    <i class="fas fa-times-circle"></i>
                    Annuler la commande suspendue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action va définitivement annuler la commande suspendue #<span id="cancel-order-number">0</span>. 
                    Cette action ne peut pas être annulée.
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Commande suspendue :</strong> Cette commande était déjà suspendue. L'annulation la retirera définitivement du système.
                </div>
                
                <div class="form-group">
                    <label for="cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi cette commande suspendue est définitivement annulée (ex: Problème irrésoluble, client injoignable, commande obsolète, etc.)" 
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

<!-- Modal Modification de la Raison de Suspension -->
<div class="modal fade" id="modifySuspensionModal" tabindex="-1" aria-labelledby="modifySuspensionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifySuspensionModalLabel">
                    <i class="fas fa-edit"></i>
                    Modifier la raison de suspension
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Modification de la suspension de la commande #<span id="modify-order-number">0</span></strong><br>
                    Cette action vous permet de mettre à jour la raison de suspension sans modifier le statut de la commande.
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-eye me-2"></i>
                        Raison actuelle de suspension
                    </label>
                    <div class="p-3 bg-light border rounded">
                        <em id="modify-current-reason">Chargement...</em>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="modify-new-reason" class="form-label fw-bold">
                        <i class="fas fa-edit me-2"></i>
                        Nouvelle raison de suspension <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="modify-new-reason" rows="3" 
                              placeholder="Saisissez la nouvelle raison de suspension..." 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette nouvelle raison remplacera l'ancienne dans l'affichage de la commande.
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="modify-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes sur la modification <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="modify-notes" rows="3" 
                              placeholder="Expliquez pourquoi vous modifiez la raison de suspension..." 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette note sera ajoutée à l'historique de la commande pour traçabilité.
                    </small>
                </div>
                
                <input type="hidden" id="modifyOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitModifySuspension()">
                    <i class="fas fa-save me-2"></i>Sauvegarder les modifications
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Réactivation Groupée des Commandes Suspendues -->
<div class="modal fade" id="bulkReactivateModal" tabindex="-1" aria-labelledby="bulkReactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkReactivateModalLabel">
                    <i class="fas fa-play-circle"></i>
                    Réactivation groupée des commandes suspendues
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Réactivation groupée</strong><br>
                    Cette action va réactiver <span class="fw-bold" id="bulk-reactivate-count">0</span> commandes suspendues sélectionnées
                    et les remettre dans le circuit normal de traitement.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Vérification importante :</strong> Assurez-vous que tous les problèmes ayant causé la suspension ont été résolus pour toutes les commandes sélectionnées.
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Seules les commandes sans problème de stock</strong> peuvent être réactivées. Les autres seront automatiquement exclues du traitement.
                </div>
                
                <div class="form-group">
                    <label for="bulk-reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-reactivate-notes" rows="4" 
                              placeholder="Confirmez que les problèmes sont résolus (ex: Stock reconstitué globalement, maintenance terminée, problèmes techniques résolus, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces commandes seront remises dans les files standard après réactivation et pourront être traitées normalement.
                    </small>
                </div>
                
                <input type="hidden" id="bulk-reactivate-orders" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitBulkReactivate()">
                    <i class="fas fa-play-circle me-2"></i>Réactiver les commandes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annulation Groupée des Commandes Suspendues -->
<div class="modal fade" id="bulkCancelModal" tabindex="-1" aria-labelledby="bulkCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkCancelModalLabel">
                    <i class="fas fa-times-circle"></i>
                    Annulation groupée des commandes suspendues
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action va définitivement annuler 
                    <span class="fw-bold" id="bulk-cancel-count">0</span> commandes suspendues sélectionnées.
                    Cette action ne peut pas être annulée.
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Commandes suspendues :</strong> Ces commandes étaient déjà suspendues. L'annulation les retirera définitivement du système.
                </div>
                
                <div class="form-group">
                    <label for="bulk-cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi ces commandes suspendues sont définitivement annulées (ex: Nettoyage de base, commandes obsolètes, problèmes irrésolus, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces informations seront utiles pour les statistiques et l'amélioration du service.
                    </small>
                </div>
                
                <input type="hidden" id="bulk-cancel-orders" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </button>
                <button type="button" class="btn btn-danger" onclick="submitBulkCancel()">
                    <i class="fas fa-times-circle me-2"></i>Confirmer l'annulation groupée
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux modales de commandes suspendues */
.modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
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
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

.bg-light {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
    border: 1px solid #e5e7eb !important;
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
// Fonctions pour les modales des commandes suspendues

window.submitReactivate = function() {
    const orderId = $('#reactivateOrderId').val();
    const notes = $('#reactivate-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la réactivation', 'error');
        return;
    }
    
    processSuspendedAction(orderId, 'reactivate', notes, '#reactivateModal');
};

window.submitCancel = function() {
    const orderId = $('#cancelOrderId').val();
    const notes = $('#cancel-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour l\'annulation', 'error');
        return;
    }
    
    processSuspendedAction(orderId, 'cancel', notes, '#cancelModal');
};

window.submitModifySuspension = function() {
    const orderId = $('#modifyOrderId').val();
    const newReason = $('#modify-new-reason').val().trim();
    const notes = $('#modify-notes').val().trim();
    
    if (!newReason || !notes) {
        showNotification('Veuillez remplir tous les champs', 'error');
        return;
    }
    
    processSuspendedAction(orderId, 'edit_suspension', notes, '#modifySuspensionModal', {
        new_suspension_reason: newReason
    });
};

window.submitBulkReactivate = function() {
    const orders = $('#bulk-reactivate-orders').val();
    const notes = $('#bulk-reactivate-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la réactivation groupée', 'error');
        return;
    }
    
    if (!orders) {
        showNotification('Aucune commande sélectionnée', 'error');
        return;
    }
    
    processBulkSuspendedAction('/admin/process/suspended/bulk-reactivate', {
        order_ids: orders.split(','),
        notes: notes
    }, '#bulkReactivateModal');
};

window.submitBulkCancel = function() {
    const orders = $('#bulk-cancel-orders').val();
    const notes = $('#bulk-cancel-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour l\'annulation groupée', 'error');
        return;
    }
    
    if (!orders) {
        showNotification('Aucune commande sélectionnée', 'error');
        return;
    }
    
    processBulkSuspendedAction('/admin/process/suspended/bulk-cancel', {
        order_ids: orders.split(','),
        notes: notes
    }, '#bulkCancelModal');
};

function processSuspendedAction(orderId, action, notes, modalSelector, extraData = {}) {
    const submitBtn = $(modalSelector + ' .btn-primary, ' + modalSelector + ' .btn-success, ' + modalSelector + ' .btn-danger');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Traitement...');
    
    const data = {
        action: action,
        notes: notes,
        ...extraData
    };
    
    $.post(`/admin/process/suspended/action/${orderId}`, data)
    .done(function(response) {
        $(modalSelector).modal('hide');
        showNotification(response.message, 'success');
        
        setTimeout(() => {
            if (typeof refreshOrders === 'function') {
                refreshOrders();
            } else {
                window.location.reload();
            }
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

function processBulkSuspendedAction(url, data, modalSelector) {
    // Masquer la modal actuelle et afficher la modal de progression si elle existe
    $(modalSelector).modal('hide');
    
    if ($('#bulkProgressModal').length) {
        showProgressModal(data.order_ids.length);
    }
    
    const submitBtn = $(modalSelector + ' .btn-primary, ' + modalSelector + ' .btn-success, ' + modalSelector + ' .btn-danger');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(response) {
        if ($('#bulkProgressModal').length) {
            updateProgressModal(response);
            
            setTimeout(() => {
                $('#bulkProgressModal').modal('hide');
                showNotification(response.message, 'success');
                
                setTimeout(() => {
                    if (typeof refreshOrders === 'function') {
                        refreshOrders();
                    } else {
                        window.location.reload();
                    }
                }, 1000);
            }, 2000);
        } else {
            showNotification(response.message, 'success');
            
            setTimeout(() => {
                if (typeof refreshOrders === 'function') {
                    refreshOrders();
                } else {
                    window.location.reload();
                }
            }, 1000);
        }
    })
    .fail(function(xhr) {
        if ($('#bulkProgressModal').length) {
            $('#bulkProgressModal').modal('hide');
        }
        
        let errorMessage = 'Erreur lors du traitement groupé';
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