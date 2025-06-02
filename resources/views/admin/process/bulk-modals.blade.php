{{-- resources/views/admin/process/bulk-modals.blade.php --}}

<!-- Modal Division Groupée -->
<div class="modal fade" id="bulkSplitModal" tabindex="-1" aria-labelledby="bulkSplitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkSplitModalLabel">
                    <i class="fas fa-cut"></i>
                    Division groupée des commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Division groupée</strong><br>
                    Cette action va diviser <span class="fw-bold" id="bulk-split-count">0</span> commandes sélectionnées.
                    Pour chaque commande, une nouvelle commande sera créée avec les produits disponibles.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white text-center">
                                <i class="fas fa-check-circle me-2"></i>
                                Nouvelles commandes
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-success mb-0">✓</h4>
                                <small class="text-muted">Avec produits disponibles</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white text-center">
                                <i class="fas fa-pause-circle me-2"></i>
                                Commandes suspendues
                            </div>
                            <div class="card-body text-center">
                                <h4 class="text-warning mb-0">⏸</h4>
                                <small class="text-muted">Avec produits problématiques</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bulk-split-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Raison de la division groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-split-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous divisez ces commandes (ex: Rupture de stock massive, problème d'approvisionnement, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette note sera ajoutée à l'historique de toutes les commandes concernées.
                    </small>
                </div>
                
                <input type="hidden" id="bulk-split-orders" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitBulkSplit()">
                    <i class="fas fa-cut me-2"></i>Diviser les commandes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annulation Groupée -->
<div class="modal fade" id="bulkCancelModal" tabindex="-1" aria-labelledby="bulkCancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkCancelModalLabel">
                    <i class="fas fa-times-circle"></i>
                    Annulation groupée des commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action va définitivement annuler 
                    <span class="fw-bold" id="bulk-cancel-count">0</span> commandes sélectionnées.
                    Cette action ne peut pas être annulée.
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Temps de traitement :</strong> L'annulation groupée peut prendre quelques secondes selon le nombre de commandes.
                </div>
                
                <div class="form-group">
                    <label for="bulk-cancel-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de l'annulation groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi ces commandes sont annulées (ex: Problèmes de stock insurmontables, nettoyage de base, etc.)" 
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

<!-- Modal Suspension Groupée -->
<div class="modal fade" id="bulkSuspendModal" tabindex="-1" aria-labelledby="bulkSuspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkSuspendModalLabel">
                    <i class="fas fa-pause-circle"></i>
                    Suspension groupée des commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-pause-circle me-2"></i>
                    Cette action va suspendre temporairement <span class="fw-bold" id="bulk-suspend-count">0</span> commandes sélectionnées.
                    Elles pourront être réactivées plus tard depuis l'interface des commandes suspendues.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Suspension temporaire :</strong> Les commandes suspendues ne seront plus traitées dans les files standard jusqu'à leur réactivation manuelle.
                </div>
                
                <div class="form-group">
                    <label for="bulk-suspend-notes" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-2"></i>
                        Raison de la suspension groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-suspend-notes" rows="4" 
                              placeholder="Expliquez pourquoi ces commandes sont suspendues (ex: En attente de réapprovisionnement général, problème temporaire, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette raison sera visible dans l'interface des commandes suspendues pour toutes les commandes.
                    </small>
                </div>
                
                <input type="hidden" id="bulk-suspend-orders" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="submitBulkSuspend()">
                    <i class="fas fa-pause-circle me-2"></i>Suspendre les commandes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Réactivation Groupée (pour interface suspendues) -->
<div class="modal fade" id="bulkReactivateModal" tabindex="-1" aria-labelledby="bulkReactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkReactivateModalLabel">
                    <i class="fas fa-play-circle"></i>
                    Réactivation groupée des commandes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Cette action va réactiver <span class="fw-bold" id="bulk-reactivate-count">0</span> commandes sélectionnées
                    et les remettre dans le circuit normal de traitement.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Vérification :</strong> Assurez-vous que tous les problèmes de stock ont été résolus pour toutes les commandes sélectionnées.
                </div>
                
                <div class="form-group">
                    <label for="bulk-reactivate-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-reactivate-notes" rows="4" 
                              placeholder="Confirmez que les problèmes sont résolus (ex: Stock reconstitué globalement, produits réactivés, problèmes résolus, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Ces commandes seront remises dans les files standard après réactivation.
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

<!-- Modal de Progression pour Actions Groupées -->
<div class="modal fade" id="bulkProgressModal" tabindex="-1" aria-labelledby="bulkProgressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkProgressModalLabel">
                    <i class="fas fa-cogs"></i>
                    Traitement en cours...
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                </div>
                <h5 id="progress-message">Traitement des commandes en cours...</h5>
                <p class="text-muted mb-4" id="progress-detail">Veuillez patienter pendant le traitement des actions groupées.</p>
                
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="progress-bar"></div>
                </div>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-success fw-bold" id="progress-success">0</div>
                        <small class="text-muted">Réussies</small>
                    </div>
                    <div class="col-4">
                        <div class="text-danger fw-bold" id="progress-errors">0</div>
                        <small class="text-muted">Erreurs</small>
                    </div>
                    <div class="col-4">
                        <div class="text-info fw-bold" id="progress-total">0</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux modales d'actions groupées */
.modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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

.progress {
    background-color: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    transition: width 0.3s ease;
}

/* Animation pour les modales */
.modal.fade .modal-dialog {
    transform: scale(0.8) translateY(-50px);
    transition: all 0.3s ease;
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Styles pour la modal de progression */
#bulkProgressModal .modal-header {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Fonctions pour les actions groupées

window.submitBulkSplit = function() {
    const orders = $('#bulk-split-orders').val();
    const notes = $('#bulk-split-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la division groupée', 'error');
        return;
    }
    
    if (!orders) {
        showNotification('Aucune commande sélectionnée', 'error');
        return;
    }
    
    processBulkAction('/admin/process/examination/bulk-split', {
        order_ids: orders.split(','),
        notes: notes
    }, '#bulkSplitModal');
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
    
    processBulkAction('/admin/process/examination/bulk-cancel', {
        order_ids: orders.split(','),
        notes: notes
    }, '#bulkCancelModal');
};

window.submitBulkSuspend = function() {
    const orders = $('#bulk-suspend-orders').val();
    const notes = $('#bulk-suspend-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour la suspension groupée', 'error');
        return;
    }
    
    if (!orders) {
        showNotification('Aucune commande sélectionnée', 'error');
        return;
    }
    
    processBulkAction('/admin/process/examination/bulk-suspend', {
        order_ids: orders.split(','),
        notes: notes
    }, '#bulkSuspendModal');
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
    
    processBulkAction('/admin/process/suspended/bulk-reactivate', {
        order_ids: orders.split(','),
        notes: notes
    }, '#bulkReactivateModal');
};

function processBulkAction(url, data, modalSelector) {
    // Masquer la modal actuelle et afficher la modal de progression
    $(modalSelector).modal('hide');
    showProgressModal(data.order_ids.length);
    
    const submitBtn = $(modalSelector + ' .btn-primary, ' + modalSelector + ' .btn-success, ' + modalSelector + ' .btn-danger, ' + modalSelector + ' .btn-warning');
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
        updateProgressModal(response);
        
        setTimeout(() => {
            $('#bulkProgressModal').modal('hide');
            showNotification(response.message, 'success');
            
            // Actualiser la page
            setTimeout(() => {
                if (typeof refreshOrders === 'function') {
                    refreshOrders();
                } else {
                    window.location.reload();
                }
            }, 1000);
        }, 2000);
    })
    .fail(function(xhr) {
        $('#bulkProgressModal').modal('hide');
        
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

function showProgressModal(totalCount) {
    $('#progress-total').text(totalCount);
    $('#progress-success').text(0);
    $('#progress-errors').text(0);
    $('#progress-bar').css('width', '0%');
    $('#progress-message').text('Traitement des commandes en cours...');
    $('#progress-detail').text('Veuillez patienter pendant le traitement des actions groupées.');
    
    $('#bulkProgressModal').modal('show');
    
    // Simulation de progression
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 90) progress = 90;
        
        $('#progress-bar').css('width', progress + '%');
        
        if (progress >= 90) {
            clearInterval(interval);
        }
    }, 200);
}

function updateProgressModal(response) {
    $('#progress-bar').css('width', '100%');
    $('#progress-message').text('Traitement terminé');
    
    if (response.details) {
        $('#progress-success').text(response.details.success_count || 0);
        $('#progress-errors').text(response.details.error_count || 0);
        
        if (response.details.error_count > 0) {
            $('#progress-detail').text(`${response.details.success_count} réussie(s), ${response.details.error_count} erreur(s)`);
        } else {
            $('#progress-detail').text('Toutes les commandes ont été traitées avec succès');
        }
    } else {
        $('#progress-detail').text('Traitement terminé avec succès');
    }
}
</script>