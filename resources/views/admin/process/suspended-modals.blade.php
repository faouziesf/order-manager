{{-- resources/views/admin/process/suspended-modals.blade.php --}}

<!-- Modal Réactiver la Commande -->
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
                    <i class="fas fa-play-circle me-2"></i>
                    Cette action va réactiver la commande #<span id="reactivate-order-number">0</span> et la remettre 
                    dans le circuit de traitement normal.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Vérification effectuée :</strong> Tous les produits de cette commande sont maintenant disponibles en stock et actifs.
                </div>
                
                <div class="form-group">
                    <label for="reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="reactivate-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous réactivez cette commande (ex: Stock reconstitué, problème résolu, validation client obtenue, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces notes confirmeront que les problèmes ont été résolus.
                    </small>
                </div>
                
                <input type="hidden" id="reactivateOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitReactivate()">
                    <i class="fas fa-play-circle me-2"></i>Réactiver la commande
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annuler la Commande -->
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
                    <strong>Attention !</strong> Cette action va définitivement annuler la commande #<span id="cancel-order-number">0</span>. 
                    Cette action ne peut pas être annulée.
                </div>
                
                <div class="form-group">
                    <label for="cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous annulez cette commande (ex: Produits définitivement en rupture, client a changé d'avis, problème de livraison, etc.)" 
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

<!-- Modal Modifier la Raison de Suspension -->
<div class="modal fade" id="modifySuspensionModal" tabindex="-1" aria-labelledby="modifySuspensionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifySuspensionModalLabel">
                    <i class="fas fa-pen"></i>
                    Modifier la raison de suspension
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-pen me-2"></i>
                    Modification de la raison de suspension pour la commande #<span id="modify-order-number">0</span>
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-history me-2"></i>
                        Raison actuelle
                    </label>
                    <div class="alert alert-light border">
                        <span id="modify-current-reason">-</span>
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
                        Ces notes seront ajoutées à l'historique de la commande.
                    </small>
                </div>
                
                <input type="hidden" id="modifyOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="submitModifySuspension()">
                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Actions Groupées - Réactivation -->
<div class="modal fade" id="bulkReactivateModal" tabindex="-1" aria-labelledby="bulkReactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkReactivateModalLabel">
                    <i class="fas fa-play-circle"></i>
                    Réactivation groupée
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-play-circle me-2"></i>
                    Vous êtes sur le point de réactiver <strong><span id="bulk-reactivate-count">0</span></strong> commande(s).
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important :</strong> Seules les commandes sans problème de stock peuvent être réactivées. 
                    Le système a vérifié que tous les produits sont disponibles.
                </div>
                
                <div class="form-group">
                    <label for="bulk-reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-reactivate-notes" rows="4" 
                              placeholder="Expliquez la raison de cette réactivation groupée (ex: Réapprovisionnement massif, résolution d'un problème technique, etc.)" 
                              required></textarea>
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

<!-- Modal Actions Groupées - Annulation -->
<div class="modal fade" id="bulkCancelModal" tabindex="-1" aria-labelledby="bulkCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkCancelModalLabel">
                    <i class="fas fa-times-circle"></i>
                    Annulation groupée
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Vous êtes sur le point d'annuler définitivement <strong><span id="bulk-cancel-count">0</span></strong> commande(s). 
                    Cette action ne peut pas être annulée.
                </div>
                
                <div class="form-group">
                    <label for="bulk-cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous annulez ces commandes (ex: Arrêt définitif de produits, changement de stratégie, etc.)" 
                              required></textarea>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Rappel :</strong> Cette action affectera toutes les commandes sélectionnées et sera enregistrée dans l'historique de chaque commande.
                </div>
                
                <input type="hidden" id="bulk-cancel-orders" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </button>
                <button type="button" class="btn btn-danger" onclick="submitBulkCancel()">
                    <i class="fas fa-times-circle me-2"></i>Confirmer les annulations
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux modales suspendues */
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

.alert-light {
    background: #f8f9fa;
    color: #6c757d;
    border-left-color: #dee2e6;
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

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

/* Animation pour les modales */
.modal.fade .modal-dialog {
    transform: scale(0.8) translateY(-50px);
    transition: all 0.3s ease;
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1.25rem 1.5rem;
    }
}
</style>

<script>
// Fonctions pour traiter les actions des modales suspendues

window.submitBulkReactivate = function() {
    const orders = $('#bulk-reactivate-orders').val();
    const notes = $('#bulk-reactivate-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir des notes pour cette réactivation groupée', 'error');
        return;
    }
    
    const submitBtn = $('#bulkReactivateModal .btn-success');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Réactivation...');
    
    $.post('/admin/process/suspended/bulk-reactivate', {
        order_ids: orders.split(','),
        notes: notes
    })
    .done(function(response) {
        $('#bulkReactivateModal').modal('hide');
        showNotification(response.message, 'success');
        
        setTimeout(() => {
            refreshOrders();
        }, 1000);
    })
    .fail(function(xhr) {
        let errorMessage = 'Erreur lors de la réactivation groupée';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showNotification(errorMessage, 'error');
    })
    .always(function() {
        submitBtn.prop('disabled', false).html(originalText);
    });
};

window.submitBulkCancel = function() {
    const orders = $('#bulk-cancel-orders').val();
    const notes = $('#bulk-cancel-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour cette annulation groupée', 'error');
        return;
    }
    
    // Demander confirmation
    if (!confirm('Êtes-vous sûr de vouloir annuler définitivement ces commandes ?')) {
        return;
    }
    
    const submitBtn = $('#bulkCancelModal .btn-danger');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Annulation...');
    
    $.post('/admin/process/suspended/bulk-cancel', {
        order_ids: orders.split(','),
        notes: notes
    })
    .done(function(response) {
        $('#bulkCancelModal').modal('hide');
        showNotification(response.message, 'success');
        
        setTimeout(() => {
            refreshOrders();
        }, 1000);
    })
    .fail(function(xhr) {
        let errorMessage = 'Erreur lors de l\'annulation groupée';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        showNotification(errorMessage, 'error');
    })
    .always(function() {
        submitBtn.prop('disabled', false).html(originalText);
    });
};
</script>