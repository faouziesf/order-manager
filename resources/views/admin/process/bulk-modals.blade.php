{{-- resources/views/admin/process/bulk-modals.blade.php --}}

<!-- Modal Actions Groupées - Division -->
<div class="modal fade" id="bulkSplitModal" tabindex="-1" aria-labelledby="bulkSplitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkSplitModalLabel">
                    <i class="fas fa-cut"></i>
                    Division groupée
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-cut me-2"></i>
                    Vous êtes sur le point de diviser <strong><span id="bulk-split-count">0</span></strong> commande(s).
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important :</strong> Seules les commandes avec des produits disponibles peuvent être divisées. 
                    Une nouvelle commande sera créée pour chaque commande divisée.
                </div>
                
                <div class="form-group">
                    <label for="bulk-split-notes" class="form-label fw-bold">
                        <i class="fas fa-comment me-2"></i>
                        Raison de la division groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-split-notes" rows="4" 
                              placeholder="Expliquez la raison de cette division groupée (ex: Problème de stock résolu partiellement, optimisation des livraisons, etc.)" 
                              required></textarea>
                </div>
                
                <input type="hidden" id="bulk-split-orders" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitBulkSplit()">
                    <i class="fas fa-cut me-2"></i>Confirmer les divisions
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

<!-- Modal Actions Groupées - Suspension -->
<div class="modal fade" id="bulkSuspendModal" tabindex="-1" aria-labelledby="bulkSuspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkSuspendModalLabel">
                    <i class="fas fa-pause-circle"></i>
                    Suspension groupée
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-pause-circle me-2"></i>
                    Vous êtes sur le point de suspendre <strong><span id="bulk-suspend-count">0</span></strong> commande(s).
                </div>
                
                <div class="form-group">
                    <label for="bulk-suspend-notes" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-2"></i>
                        Raison de la suspension groupée <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="bulk-suspend-notes" rows="4" 
                              placeholder="Expliquez la raison de cette suspension groupée (ex: Problème technique général, réapprovisionnement en cours, etc.)" 
                              required></textarea>
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

<script>
// Fonctions pour les actions groupées
window.submitBulkSplit = function() {
    const orders = $('#bulk-split-orders').val();
    const notes = $('#bulk-split-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour cette division groupée', 'error');
        return;
    }
    
    // Logique de division groupée à implémenter
    console.log('Division groupée:', { orders, notes });
    $('#bulkSplitModal').modal('hide');
    showNotification('Division groupée en cours...', 'info');
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
    
    // Logique d'annulation groupée à implémenter
    console.log('Annulation groupée:', { orders, notes });
    $('#bulkCancelModal').modal('hide');
    showNotification('Annulation groupée en cours...', 'info');
};

window.submitBulkSuspend = function() {
    const orders = $('#bulk-suspend-orders').val();
    const notes = $('#bulk-suspend-notes').val().trim();
    
    if (!notes) {
        showNotification('Veuillez saisir une raison pour cette suspension groupée', 'error');
        return;
    }
    
    // Logique de suspension groupée à implémenter
    console.log('Suspension groupée:', { orders, notes });
    $('#bulkSuspendModal').modal('hide');
    showNotification('Suspension groupée en cours...', 'info');
};
</script>