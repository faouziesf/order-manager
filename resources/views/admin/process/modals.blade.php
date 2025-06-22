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
                        Notes sur la tentative <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="call-notes" rows="4" 
                              placeholder="Décrivez ce qui s'est passé lors de l'appel..." required></textarea>
                    <div class="form-text">
                        Exemple: "Sonnerie mais pas de réponse", "Numéro occupé", "Éteint", "Demande de rappeler plus tard", etc.
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Action:</strong> Cette action va incrémenter le compteur de tentatives et marquer l'heure de la dernière tentative.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="submitCallAction()">
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
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention:</strong> Assurez-vous que tous les champs client sont remplis et que le panier contient les bons produits avant de confirmer.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="confirm-price" class="form-label">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Prix total de la commande (TND) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.001" class="form-control" id="confirm-price" 
                                       placeholder="0.000" required min="0.001">
                                <span class="input-group-text">TND</span>
                            </div>
                            <div class="form-text">Prix total final négocié avec le client (incluant tout)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-2"></i>
                                Changements
                            </label>
                            <div class="alert alert-success mb-0">
                                <small>
                                    <i class="fas fa-arrow-right me-1"></i>Statut: <strong>Confirmée</strong><br>
                                    <i class="fas fa-minus me-1"></i>Stock sera décrémenté automatiquement<br>
                                    <i class="fas fa-money-bill me-1"></i>Prix total sera mis à jour
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-notes" class="form-label">
                        <i class="fas fa-comment me-2"></i>
                        Notes de confirmation (optionnel)
                    </label>
                    <textarea class="form-control" id="confirm-notes" rows="3" 
                              placeholder="Informations supplémentaires sur la confirmation..."></textarea>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Rappel:</strong> Vérifiez que tous les champs obligatoires du client sont remplis dans le formulaire principal avant de confirmer.
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
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention:</strong> Cette action changera définitivement le statut de la commande à "Annulée".
                </div>
                
                <div class="form-group">
                    <label for="cancel-notes" class="form-label">
                        <i class="fas fa-sticky-note me-2"></i>
                        Raison de l'annulation <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="cancel-notes" rows="4" 
                              placeholder="Expliquez pourquoi cette commande est annulée..." required></textarea>
                    <div class="form-text">
                        Exemple: "Client a changé d'avis", "Produit non disponible", "Adresse incorrecte", etc.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </button>
                <button type="button" class="btn btn-danger" onclick="submitCancelAction()">
                    <i class="fas fa-times-circle me-2"></i>Annuler définitivement
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
                    Dater la commande
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
                        La commande apparaîtra dans la file "Datée" à partir de cette date
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="schedule-notes" class="form-label">
                        <i class="fas fa-comment me-2"></i>
                        Notes de planification (optionnel)
                    </label>
                    <textarea class="form-control" id="schedule-notes" rows="3" 
                              placeholder="Raison de la planification et informations pour le rappel..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Action:</strong> La commande passera au statut <strong>"Datée"</strong> et ses compteurs de tentatives seront remis à zéro.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-info" onclick="submitScheduleAction()">
                    <i class="fas fa-calendar-check me-2"></i>Dater la commande
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
                    <strong>Stock disponible!</strong><br>
                    Tous les produits de cette commande sont maintenant en stock et peuvent être traités normalement.
                </div>
                
                <div class="form-group mb-3">
                    <label for="reactivate-notes" class="form-label">
                        <i class="fas fa-comment me-2"></i>
                        Notes de réactivation (optionnel)
                    </label>
                    <textarea class="form-control" id="reactivate-notes" rows="3" 
                              placeholder="Notes sur la réactivation de cette commande..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-arrow-right me-2"></i>
                    <strong>Action:</strong> La commande retournera au statut <strong>"Nouvelle"</strong> et ne sera plus suspendue. Ses compteurs de tentatives seront remis à zéro.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitReactivateAction()">
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
                    Vérifiez les détails ci-dessous avant de traiter cette commande.
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
/* Timeline pour l'historique */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 100%);
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 8px;
    z-index: 2;
}

.timeline-marker-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    margin-left: 15px;
}

.timeline-content .card {
    border-left: 3px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.timeline-content .card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

/* Classes de statut pour les badges */
.status-nouvelle { background: #e0e7ff; color: #5b21b6; }
.status-datée { background: #fef3c7; color: #92400e; }
.status-confirmée { background: #dcfce7; color: #166534; }
.status-ancienne { background: #fed7d7; color: #9b2c2c; }
.status-annulée { background: #fee2e2; color: #dc2626; }
.status-livrée { background: #dcfce7; color: #166534; }
</style>

<script>
// Fonctions pour soumettre les actions avec validation - CORRIGÉES

function submitCallAction() {
    const notes = $('#call-notes').val().trim();
    
    if (!notes || notes.length < 3) {
        showNotification('Veuillez saisir des notes d\'au moins 3 caractères', 'error');
        $('#call-notes').focus();
        return;
    }
    
    processAction('call', { notes: notes });
}

function submitConfirmAction() {
    const price = $('#confirm-price').val();
    const notes = $('#confirm-notes').val().trim();
    
    // Validation du prix
    if (!price || parseFloat(price) <= 0) {
        showNotification('Veuillez saisir un prix valide supérieur à 0', 'error');
        $('#confirm-price').focus();
        return;
    }
    
    // Validation des champs requis côté client
    const customerName = $('#customer_name').val().trim();
    const customerGovernorate = $('#customer_governorate').val();
    const customerCity = $('#customer_city').val();
    const customerAddress = $('#customer_address').val().trim();
    
    if (!customerName) {
        showNotification('Le nom du client est obligatoire', 'error');
        $('#customer_name').focus();
        return;
    }
    
    if (!customerGovernorate) {
        showNotification('Le gouvernorat est obligatoire', 'error');
        $('#customer_governorate').focus();
        return;
    }
    
    if (!customerCity) {
        showNotification('La ville est obligatoire', 'error');
        $('#customer_city').focus();
        return;
    }
    
    if (!customerAddress || customerAddress.length < 5) {
        showNotification('L\'adresse doit contenir au moins 5 caractères', 'error');
        $('#customer_address').focus();
        return;
    }
    
    // Validation du panier
    if (!cartItems || cartItems.length === 0) {
        showNotification('Veuillez ajouter au moins un produit au panier', 'error');
        return;
    }
    
    // Vérification du stock disponible
    let stockError = false;
    let stockMessage = '';
    
    cartItems.forEach(item => {
        if (item.product && item.product.stock < item.quantity) {
            stockError = true;
            stockMessage = `Stock insuffisant pour ${item.product.name}. Stock disponible: ${item.product.stock}, quantité demandée: ${item.quantity}`;
        }
    });
    
    if (stockError) {
        showNotification(stockMessage, 'error');
        return;
    }
    
    processAction('confirm', { 
        confirmed_price: parseFloat(price),
        notes: notes
    });
}

function submitCancelAction() {
    const notes = $('#cancel-notes').val().trim();
    
    if (!notes || notes.length < 3) {
        showNotification('Veuillez indiquer la raison de l\'annulation (minimum 3 caractères)', 'error');
        $('#cancel-notes').focus();
        return;
    }
    
    processAction('cancel', { notes: notes });
}

function submitScheduleAction() {
    const date = $('#schedule-date').val();
    const notes = $('#schedule-notes').val().trim();
    
    if (!date) {
        showNotification('Veuillez sélectionner une date', 'error');
        $('#schedule-date').focus();
        return;
    }
    
    // Vérifier que la date n'est pas dans le passé
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showNotification('La date ne peut pas être dans le passé', 'error');
        $('#schedule-date').focus();
        return;
    }
    
    processAction('schedule', { 
        scheduled_date: date,
        notes: notes
    });
}

function submitReactivateAction() {
    const notes = $('#reactivate-notes').val().trim();
    
    processAction('reactivate', { 
        notes: notes
    });
}

// Initialiser les dates minimales au chargement
$(document).ready(function() {
    // Date minimum pour la planification = aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    $('#schedule-date').attr('min', today);
    
    // Vider les champs des modales à leur ouverture
    $('.modal').on('show.bs.modal', function() {
        $(this).find('textarea, input[type="text"], input[type="number"], input[type="date"]').val('');
    });
    
    // Calculer le prix automatiquement pour la confirmation
    $('#confirmModal').on('show.bs.modal', function() {
        if (cartItems && cartItems.length > 0) {
            const total = cartItems.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0);
            $('#confirm-price').val(total.toFixed(3));
        }
    });
    
    // Validation en temps réel pour le prix de confirmation
    $('#confirm-price').on('input', function() {
        const value = parseFloat($(this).val());
        const submitBtn = $(this).closest('.modal').find('.btn-success');
        
        if (value > 0) {
            submitBtn.prop('disabled', false);
            $(this).removeClass('is-invalid');
        } else {
            submitBtn.prop('disabled', true);
            $(this).addClass('is-invalid');
        }
    });
});
</script>