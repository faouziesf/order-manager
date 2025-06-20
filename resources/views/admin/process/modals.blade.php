{{-- MODAL TENTATIVE D'APPEL --}}
<div class="modal fade" id="callModal" tabindex="-1" aria-labelledby="callModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="callModalLabel">
                    <i class="fas fa-phone-slash text-warning me-2"></i>
                    Tentative d'appel - Ne répond pas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="call-notes" class="form-label">
                        <i class="fas fa-sticky-note me-2"></i>
                        Notes sur la tentative
                    </label>
                    <textarea class="form-control" id="call-notes" rows="4" 
                              placeholder="Décrivez ce qui s'est passé lors de l'appel..." required></textarea>
                    <div class="form-text">
                        Exemple: "Sonnerie mais pas de réponse", "Numéro occupé", "Éteint", etc.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="processAction('call', {notes: $('#call-notes').val()})">
                    <i class="fas fa-save me-2"></i>Enregistrer la tentative
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMATION --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Confirmer la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="confirm-price" class="form-label">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Prix confirmé
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" id="confirm-price" 
                                       placeholder="0.000" required>
                                <span class="input-group-text">TND</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-2"></i>
                                Statut après confirmation
                            </label>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-arrow-right me-2"></i>
                                La commande passera au statut <strong>"Confirmée"</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-notes" class="form-label">
                        <i class="fas fa-comment me-2"></i>
                        Notes de confirmation
                    </label>
                    <textarea class="form-control" id="confirm-notes" rows="3" 
                              placeholder="Informations supplémentaires sur la confirmation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="processAction('confirm', {confirmed_price: $('#confirm-price').val(), notes: $('#confirm-notes').val()})">
                    <i class="fas fa-check-circle me-2"></i>Confirmer la commande
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ANNULATION --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">
                    <i class="fas fa-times-circle text-danger me-2"></i>
                    Annuler la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention:</strong> Cette action annulera définitivement la commande.
                </div>
                
                <div class="form-group">
                    <label for="cancel-notes" class="form-label">
                        <i class="fas fa-sticky-note me-2"></i>
                        Raison de l'annulation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi cette commande est annulée..." required></textarea>
                    <div class="form-text">
                        Exemple: "Client a changé d'avis", "Produit non disponible", etc.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </button>
                <button type="button" class="btn btn-danger" onclick="processAction('cancel', {notes: $('#cancel-notes').val()})">
                    <i class="fas fa-times-circle me-2"></i>Annuler la commande
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PLANIFICATION --}}
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">
                    <i class="fas fa-calendar-plus text-info me-2"></i>
                    Planifier la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="schedule-date" class="form-label">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Date de rappel <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="schedule-date" required>
                    <div class="form-text">
                        Choisissez une date future pour rappeler ce client
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="schedule-notes" class="form-label">
                        <i class="fas fa-comment me-2"></i>
                        Notes de planification
                    </label>
                    <textarea class="form-control" id="schedule-notes" rows="3" 
                              placeholder="Raison de la planification et informations pour le rappel..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    La commande passera au statut <strong>"Datée"</strong> et apparaîtra dans la file datée à partir de la date choisie.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-info" onclick="processAction('schedule', {scheduled_date: $('#schedule-date').val(), notes: $('#schedule-notes').val()})">
                    <i class="fas fa-calendar-check me-2"></i>Planifier
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL RÉACTIVATION --}}
<div class="modal fade" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactivateModalLabel">
                    <i class="fas fa-play-circle text-success me-2"></i>
                    Réactiver la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Tous les produits sont maintenant en stock!</strong><br>
                    Cette commande peut être réactivée et retourner dans la file standard.
                </div>
                
                <div class="form-group">
                    <label for="reactivate-notes" class="form-label">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation
                    </label>
                    <textarea class="form-control" id="reactivate-notes" rows="3" 
                              placeholder="Notes sur la réactivation de cette commande..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-arrow-right me-2"></i>
                    La commande retournera au statut <strong>"Nouvelle"</strong> et ses compteurs de tentatives seront remis à zéro.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="processAction('reactivate', {notes: $('#reactivate-notes').val()})">
                    <i class="fas fa-play-circle me-2"></i>Réactiver définitivement
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HISTORIQUE --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history text-primary me-2"></i>
                    Historique de la commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="history-content">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                        <p>Chargement de l'historique...</p>
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

{{-- MODAL DOUBLONS --}}
<div class="modal fade" id="duplicatesModal" tabindex="-1" aria-labelledby="duplicatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duplicatesModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Commandes doublons détectées
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Attention:</strong> Ce client a plusieurs commandes dans le système. 
                    Vérifiez les détails ci-dessous avant de traiter.
                </div>
                
                <div id="duplicates-content">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                        <p>Chargement des doublons...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <a href="/admin/duplicates" class="btn btn-warning" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>Gérer les doublons
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour les modales */
.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-bottom: 1px solid #e5e7eb;
    border-radius: 15px 15px 0 0;
    padding: 1.25rem 1.5rem;
}

.modal-title {
    font-weight: 700;
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-radius: 0 0 15px 15px;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn {
    border-radius: 8px;
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Styles pour l'historique */
.history-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    transition: background-color 0.2s ease;
}

.history-item:hover {
    background-color: #f9fafb;
}

.history-item:last-child {
    border-bottom: none;
}

.history-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.history-icon.creation {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
}

.history-icon.modification {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1d4ed8;
}

.history-icon.tentative {
    background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
    color: #92400e;
}

.history-icon.confirmation {
    background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
    color: #166534;
}

.history-icon.annulation {
    background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
    color: #dc2626;
}

.history-content {
    flex: 1;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.history-action {
    font-weight: 700;
    color: #374151;
    font-size: 1.1rem;
}

.history-time {
    color: #6b7280;
    font-size: 0.875rem;
}

.history-user {
    color: #4b5563;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.history-notes {
    background: #f3f4f6;
    padding: 0.75rem;
    border-radius: 8px;
    border-left: 4px solid #6b7280;
    color: #374151;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* Styles pour les badges de statut */
.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.status-nouvelle {
    background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
    color: #5b21b6;
}

.badge.status-datée {
    background: linear-gradient(135deg, #fef3c7 0%, #fde047 100%);
    color: #92400e;
}

.badge.status-confirmée {
    background: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
    color: #166534;
}

.badge.status-ancienne {
    background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%);
    color: #9b2c2c;
}

.badge.status-annulée {
    background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
    color: #dc2626;
}

.badge.status-livrée {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

/* Animation pour les modales */
.modal.fade .modal-dialog {
    transform: translateY(-30px);
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-xl {
        max-width: 95%;
    }
    
    .history-item {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .history-icon {
        align-self: flex-start;
    }
    
    .history-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>