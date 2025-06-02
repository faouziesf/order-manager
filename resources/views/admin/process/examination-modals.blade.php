{{-- resources/views/admin/process/examination-modals.blade.php --}}

<!-- Modal Diviser la Commande -->
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
                    <strong>Action de division :</strong> Cette opération va créer une nouvelle commande avec uniquement les produits disponibles, 
                    et laisser les produits problématiques dans la commande originale qui sera suspendue.
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-success-subtle">
                            <div class="card-body text-center">
                                <h5 class="card-title text-success">
                                    <i class="fas fa-check-circle"></i>
                                    Nouvelle commande
                                </h5>
                                <p class="card-text">
                                    <span class="badge bg-success fs-6" id="split-available-count">0</span> produit(s) disponible(s)
                                </p>
                                <small class="text-muted">Statut: Nouvelle (prête pour traitement)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-warning-subtle">
                            <div class="card-body text-center">
                                <h5 class="card-title text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Commande originale
                                </h5>
                                <p class="card-text">
                                    <span class="badge bg-warning fs-6" id="split-problem-count">0</span> produit(s) problématique(s)
                                </p>
                                <small class="text-muted">Statut: Suspendue (en attente de stock)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="split-notes" class="form-label fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>
                        Raison de la division <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="split-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous divisez cette commande (ex: Certains produits en rupture de stock, client souhaite recevoir les articles disponibles rapidement, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette note sera ajoutée à l'historique des deux commandes.
                    </small>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important</h6>
                    <ul class="mb-0">
                        <li>La nouvelle commande aura un nouvel ID et sera traitée séparément</li>
                        <li>La commande originale sera automatiquement suspendue</li>
                        <li>Le client devra être informé de la division</li>
                        <li>Cette action ne peut pas être annulée</li>
                    </ul>
                </div>
                
                <input type="hidden" id="splitOrderId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitSplit()">
                    <i class="fas fa-cut me-2"></i>Confirmer la division
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

<!-- Modal Suspendre la Commande -->
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
                    Elle n'apparaîtra plus dans les files de traitement jusqu'à sa réactivation.
                </div>
                
                <div class="form-group">
                    <label for="suspend-notes" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-2"></i>
                        Raison de la suspension <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="suspend-notes" rows="4" 
                              placeholder="Expliquez pourquoi vous suspendez cette commande (ex: En attente de réapprovisionnement, problème technique, attente de validation client, etc.)" 
                              required></textarea>
                    <small class="form-text text-muted">
                        Cette note sera visible lors de la réactivation de la commande.
                    </small>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Information :</strong> Cette commande pourra être réactivée plus tard depuis cette même interface 
                    une fois les problèmes de stock résolus.
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
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Assurez-vous que tous les problèmes de stock ont été résolus avant de réactiver cette commande.
                    Le système vérifiera automatiquement la disponibilité des produits.
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

/* Cards dans les modales */
.card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.bg-success-subtle {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
}

.bg-warning-subtle {
    background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%) !important;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.badge {
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
}

.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
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
    
    .row {
        margin: 0;
    }
    
    .col-md-6 {
        padding: 0 0.5rem;
        margin-bottom: 1rem;
    }
}
</style>