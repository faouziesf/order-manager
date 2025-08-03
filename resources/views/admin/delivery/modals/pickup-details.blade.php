<!-- Modal Détails du Pickup - Adaptée au layout -->
<div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-labelledby="pickupDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius: var(--border-radius-lg); box-shadow: var(--shadow-xl); border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); border-bottom: none; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;">
                <h5 class="modal-title text-white fw-bold" id="pickupDetailsModalLabel">
                    <i class="fas fa-truck me-2"></i>
                    Détails de l'Enlèvement
                    <span x-show="selectedPickup" 
                          x-text="`#${selectedPickup?.id}`" 
                          class="text-white"
                          style="opacity: 0.9;"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" x-show="selectedPickup" style="padding: 2rem;">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card" style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%); border: 1px solid rgba(30, 64, 175, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <h6 class="card-title text-primary fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations Générales
                                </h6>
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted fw-medium">ID Enlèvement</small>
                                        <div class="fw-bold text-dark" x-text="`#${selectedPickup?.id}`">-</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted fw-medium">Statut</small>
                                        <div>
                                            <span x-show="selectedPickup" 
                                                  :class="getStatusBadgeClass(selectedPickup?.status)"
                                                  x-text="getStatusLabel(selectedPickup?.status)"
                                                  style="font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 6px;"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted fw-medium">Date d'enlèvement</small>
                                        <div class="fw-bold text-dark" x-text="formatDate(selectedPickup?.pickup_date)">-</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted fw-medium">Créé le</small>
                                        <div class="fw-bold text-dark" x-text="formatDateTime(selectedPickup?.created_at)">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <h6 class="card-title text-success fw-bold mb-3">
                                    <i class="fas fa-truck me-2"></i>
                                    Transporteur
                                </h6>
                                <div x-show="selectedPickup?.delivery_configuration">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i :class="getCarrierIcon(selectedPickup?.carrier_slug)" class="text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" x-text="selectedPickup?.delivery_configuration?.integration_name">-</div>
                                            <small class="text-muted fw-medium" x-text="getCarrierName(selectedPickup?.carrier_slug)">-</small>
                                        </div>
                                    </div>
                                    <div class="bg-white p-2 rounded" style="border: 1px solid rgba(16, 185, 129, 0.1);">
                                        <small class="text-muted">Configuration ID: </small>
                                        <code x-text="selectedPickup?.delivery_configuration?.id" style="background: rgba(16, 185, 129, 0.1); padding: 0.2rem 0.4rem; border-radius: 4px;">-</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%); border: 1px solid rgba(30, 64, 175, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                </div>
                                <div class="h4 text-primary mb-1 fw-bold" x-text="selectedPickup?.shipments?.length || 0">0</div>
                                <small class="text-muted fw-medium">Commandes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-weight-hanging fa-2x text-success mb-2"></i>
                                </div>
                                <div class="h4 text-success mb-1 fw-bold" x-text="`${getTotalWeight()} kg`">0 kg</div>
                                <small class="text-muted fw-medium">Poids Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, rgba(8, 145, 178, 0.05) 100%); border: 1px solid rgba(6, 182, 212, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                                </div>
                                <div class="h4 text-info mb-1 fw-bold" x-text="getTotalPieces()">0</div>
                                <small class="text-muted fw-medium">Nb Pièces</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(217, 119, 6, 0.05) 100%); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: var(--border-radius);">
                            <div class="card-body" style="padding: 1.5rem;">
                                <div class="mb-2">
                                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                                </div>
                                <div class="h4 text-warning mb-1 fw-bold" x-text="`${getTotalCOD()} TND`">0 TND</div>
                                <small class="text-muted fw-medium">COD Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="card" style="border: 1px solid var(--card-border); border-radius: var(--border-radius); overflow: hidden;">
                    <div class="card-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); border-bottom: 1px solid var(--card-border);">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-list me-2"></i>
                            Commandes Incluses
                            <span x-show="selectedPickup" 
                                  class="badge text-white ms-2" 
                                  x-text="selectedPickup?.shipments?.length || 0"
                                  style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);"></span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm mb-0">
                                <thead style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Commande</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Client</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Téléphone</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Adresse</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Montant</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Poids</th>
                                        <th style="border: none; padding: 0.75rem; font-weight: 600; color: var(--text-color);">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="shipment in selectedPickup?.shipments || []" :key="shipment.id">
                                        <tr style="transition: all 0.2s ease;" class="hover-row">
                                            <td style="padding: 0.75rem;">
                                                <strong class="text-primary" x-text="`#${shipment.order?.id}`">-</strong>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <div class="fw-bold" x-text="shipment.recipient_info?.name">-</div>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <div x-text="shipment.recipient_info?.phone">-</div>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <div x-text="shipment.recipient_info?.city">-</div>
                                                <small class="text-muted" x-text="shipment.recipient_info?.governorate">-</small>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <strong class="text-success" x-text="`${shipment.cod_amount} TND`">-</strong>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <span class="badge bg-secondary" x-text="`${shipment.weight} kg`">-</span>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <span class="badge bg-primary" 
                                                      x-text="shipment.status"
                                                      style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;">-</span>
                                            </td>
                                        </tr>
                                    </template>
                                    
                                    <!-- Message si aucune commande -->
                                    <tr x-show="!selectedPickup?.shipments || selectedPickup.shipments.length === 0">
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                            <h6 class="text-muted">Aucune commande</h6>
                                            <p class="text-muted mb-0">Cet enlèvement ne contient aucune commande</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Historique si disponible -->
                <div x-show="selectedPickup?.history && selectedPickup.history.length > 0" class="mt-4">
                    <div class="card" style="border: 1px solid var(--card-border); border-radius: var(--border-radius);">
                        <div class="card-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #f1f5f9 100%); border-bottom: 1px solid var(--card-border);">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-history me-2"></i>
                                Historique des Actions
                            </h6>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
                            <div class="timeline">
                                <template x-for="(event, index) in selectedPickup?.history || []" :key="event.id || index">
                                    <div class="timeline-item" :class="{ 'timeline-item-current': index === 0 }">
                                        <div class="timeline-marker" :class="getTimelineMarkerClass(event.action, index === 0)">
                                            <i :class="getHistoryIcon(event.action)"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold" x-text="event.action_label || event.action">Action</h6>
                                                    <p x-show="event.notes" class="mb-2 text-muted" x-text="event.notes">-</p>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user me-1 text-muted"></i>
                                                        <small class="text-muted" x-text="event.user_name || 'Système'">-</small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted fw-medium" x-text="formatDateTime(event.created_at)">-</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="background: rgba(248, 250, 252, 0.5); border-top: 1px solid var(--card-border); border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg); padding: 1.5rem;">
                <div x-show="selectedPickup" class="d-flex justify-content-between w-100">
                    <div class="d-flex gap-2">
                        <!-- Actions selon le statut -->
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-success"
                                @click="validatePickup(selectedPickup.id); $refs.closeBtn.click()"
                                style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-check me-2"></i>
                            Valider l'Enlèvement
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'validated'" 
                                class="btn btn-info"
                                @click="markAsPickedUp(selectedPickup.id); $refs.closeBtn.click()"
                                style="background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-truck me-2"></i>
                            Marquer comme Récupéré
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-outline-danger"
                                @click="deletePickup(selectedPickup.id); $refs.closeBtn.click()"
                                style="border: 2px solid var(--danger-color); color: var(--danger-color); border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-trash me-2"></i>
                            Supprimer
                        </button>
                        
                        <button x-show="selectedPickup?.status !== 'draft'" 
                                class="btn btn-outline-primary"
                                @click="printPickupReport(selectedPickup.id)"
                                style="border: 2px solid var(--primary-color); color: var(--primary-color); border-radius: var(--border-radius); font-weight: 500;">
                            <i class="fas fa-print me-2"></i>
                            Imprimer Rapport
                        </button>
                    </div>
                    
                    <button type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal" 
                            x-ref="closeBtn"
                            style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: var(--border-radius); font-weight: 500;">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styles */
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
    background: linear-gradient(to bottom, var(--primary-color), var(--card-border));
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--card-border);
    border: 3px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    z-index: 2;
    transition: all 0.3s ease;
}

.timeline-marker i {
    color: #fff;
    font-size: 12px;
}

.timeline-marker-current {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.25), var(--shadow-md);
    animation: pulse-timeline 2s infinite;
}

.timeline-marker.marker-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
}

.timeline-marker.marker-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
}

.timeline-marker.marker-danger {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
}

.timeline-marker.marker-info {
    background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%);
}

.timeline-content {
    background: rgba(248, 250, 252, 0.8);
    padding: 1.25rem;
    border-radius: var(--border-radius);  
    border-left: 4px solid var(--primary-color);
    margin-left: 15px;
    position: relative;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    background: rgba(248, 250, 252, 1);
    box-shadow: var(--shadow-md);
    transform: translateX(2px);
}

.timeline-content::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 15px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 8px 8px 8px 0;
    border-color: transparent rgba(248, 250, 252, 0.8) transparent transparent;
}

.timeline-item-current .timeline-content {
    border-left-color: var(--primary-color);
    background: rgba(30, 64, 175, 0.05);
}

.timeline-item-current .timeline-content::before {
    border-right-color: rgba(30, 64, 175, 0.05);
}

@keyframes pulse-timeline {
    0% {
        box-shadow: 0 0 0 0 rgba(30, 64, 175, 0.7), var(--shadow-md);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(30, 64, 175, 0), var(--shadow-md);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(30, 64, 175, 0), var(--shadow-md);
    }
}

/* Hover effects for table rows */
.hover-row:hover {
    background: rgba(30, 64, 175, 0.05) !important;
    transform: scale(1.001);
}

/* Card animations */
#pickupDetailsModal .card {
    animation: slideInUp 0.3s ease-out;
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

/* Button hover effects */
#pickupDetailsModal .btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

#pickupDetailsModal .btn-outline-danger:hover {
    background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
    color: white;
    border-color: var(--danger-color);
}

#pickupDetailsModal .btn-outline-primary:hover {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border-color: var(--primary-color);
}

/* Badge styles */
#pickupDetailsModal .badge {
    font-weight: 600;
    letter-spacing: 0.025em;
    border-radius: 6px;
    padding: 0.25rem 0.5rem;
}

/* Scrollbar personnalisée */
#pickupDetailsModal .table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

#pickupDetailsModal .table-responsive::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

#pickupDetailsModal .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
}

/* Responsive design */
@media (max-width: 768px) {
    #pickupDetailsModal .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }
    
    #pickupDetailsModal .modal-body {
        padding: 1.5rem 1rem;
    }
    
    #pickupDetailsModal .row .col-md-6,
    #pickupDetailsModal .row .col-md-3 {
        margin-bottom: 1rem;
    }
    
    #pickupDetailsModal .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    #pickupDetailsModal .d-flex.gap-2 {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    #pickupDetailsModal .table-responsive {
        font-size: 0.85rem;
    }
    
    #pickupDetailsModal .timeline {
        padding-left: 20px;
    }
    
    #pickupDetailsModal .timeline-marker {
        left: -15px;
        width: 24px;
        height: 24px;
    }
    
    #pickupDetailsModal .timeline-content {
        margin-left: 10px;
        padding: 1rem;
    }
}

/* Animation d'entrée pour la modal */
#pickupDetailsModal.show .modal-content {
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

/* États de chargement */
#pickupDetailsModal .loading {
    position: relative;
    pointer-events: none;
    opacity: 0.6;
}

#pickupDetailsModal .loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid transparent;
    border-top: 2px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Méthodes utilitaires pour la modal (à ajouter au composant Alpine principal)
function extendPickupMethods() {
    return {
        getStatusBadgeClass(status) {
            const classes = {
                'draft': 'badge text-white',
                'validated': 'badge text-white',
                'picked_up': 'badge text-white',
                'problem': 'badge text-white'
            };
            
            const bgStyles = {
                'draft': 'background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;',
                'validated': 'background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%) !important;',
                'picked_up': 'background: linear-gradient(135deg, var(--info-color) 0%, #0891b2 100%) !important;',
                'problem': 'background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%) !important;'
            };
            
            // Appliquer le style directement
            setTimeout(() => {
                const badges = document.querySelectorAll(`[x-text*="getStatusLabel"]`);
                badges.forEach(badge => {
                    if (badge.textContent === this.getStatusLabel(status)) {
                        badge.setAttribute('style', bgStyles[status] || bgStyles['draft']);
                    }
                });
            }, 0);
            
            return classes[status] || classes['draft'];
        },

        getStatusLabel(status) {
            const labels = {
                'draft': 'Brouillon',
                'validated': 'Validé',
                'picked_up': 'Récupéré',
                'problem': 'Problème'
            };
            return labels[status] || 'Inconnu';
        },

        getCarrierIcon(carrierSlug) {
            const icons = {
                'jax_delivery': 'fas fa-truck',
                'mes_colis': 'fas fa-shipping-fast'
            };
            return icons[carrierSlug] || 'fas fa-truck';
        },

        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || 'Transporteur inconnu';
        },

        getTotalWeight() {
            if (!this.selectedPickup?.shipments) return '0.00';
            return this.selectedPickup.shipments
                .reduce((total, shipment) => total + (parseFloat(shipment.weight) || 0), 0)
                .toFixed(2);
        },

        getTotalPieces() {
            if (!this.selectedPickup?.shipments) return 0;
            return this.selectedPickup.shipments
                .reduce((total, shipment) => total + (parseInt(shipment.nb_pieces) || 0), 0);
        },

        getTotalCOD() {
            if (!this.selectedPickup?.shipments) return '0.000';
            return this.selectedPickup.shipments
                .reduce((total, shipment) => total + (parseFloat(shipment.cod_amount) || 0), 0)
                .toFixed(3);
        },

        getHistoryIcon(action) {
            const icons = {
                'created': 'fas fa-plus',
                'validated': 'fas fa-check',
                'picked_up': 'fas fa-truck',
                'cancelled': 'fas fa-times',
                'updated': 'fas fa-edit'
            };
            return icons[action] || 'fas fa-info-circle';
        },

        getTimelineMarkerClass(action, isCurrent) {
            if (isCurrent) return 'timeline-marker-current';
            
            const classes = {
                'validated': 'marker-success',
                'picked_up': 'marker-info',
                'cancelled': 'marker-danger',
                'updated': 'marker-warning'
            };
            return classes[action] || '';
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        formatDateTime(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        async validatePickup(pickupId) {
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/validate`);
                if (response.data.success) {
                    this.showNotification('success', 'Enlèvement validé avec succès');
                    // Recharger les données
                    await this.refreshPickupData();
                } else {
                    this.showNotification('error', response.data.message || 'Erreur lors de la validation');
                }
            } catch (error) {
                this.showNotification('error', 'Erreur de communication avec le serveur');
                console.error('Erreur validation pickup:', error);
            }
        },

        async markAsPickedUp(pickupId) {
            try {
                const response = await axios.post(`/admin/delivery/pickups/${pickupId}/mark-picked-up`);
                if (response.data.success) {
                    this.showNotification('success', 'Enlèvement marqué comme récupéré');
                    // Recharger les données
                    await this.refreshPickupData();
                } else {
                    this.showNotification('error', response.data.message || 'Erreur lors de la mise à jour');
                }
            } catch (error) {
                this.showNotification('error', 'Erreur de communication avec le serveur');
                console.error('Erreur mark picked up:', error);
            }
        },

        async deletePickup(pickupId) {
            const result = await Swal.fire({
                title: 'Supprimer l\'enlèvement ?',
                text: 'Cette action est irréversible !',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await axios.delete(`/admin/delivery/pickups/${pickupId}`);
                if (response.data.success) {
                    this.showNotification('success', 'Enlèvement supprimé avec succès');
                    // Fermer la modal et recharger la liste
                    setTimeout(() => {
                        if (typeof refreshPickupsList === 'function') {
                            refreshPickupsList();
                        }
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showNotification('error', response.data.message || 'Erreur lors de la suppression');
                }
            } catch (error) {
                this.showNotification('error', 'Erreur de communication avec le serveur');
                console.error('Erreur suppression pickup:', error);
            }
        },

        async printPickupReport(pickupId) {
            try {
                window.open(`/admin/delivery/pickups/${pickupId}/report`, '_blank');
                this.showNotification('info', 'Ouverture du rapport dans un nouvel onglet');
            } catch (error) {
                this.showNotification('error', 'Erreur lors de l\'ouverture du rapport');
                console.error('Erreur impression rapport:', error);
            }
        },

        showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'success' ? 'fas fa-check-circle' : 
                        type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';

            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px; border-radius: var(--border-radius);';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="${icon} me-2"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    };
}
</script>