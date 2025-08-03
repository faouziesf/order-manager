{{-- MODALES POUR LE SYSTÈME DE LIVRAISON --}}

{{-- Modal Test de Connexion --}}
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnectionModalLabel">
                    <i class="fas fa-wifi me-2"></i>
                    Test de Connexion Transporteur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Informations de la configuration testée -->
                <div class="card bg-light border-0 mb-4" x-show="testConfig">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Configuration Testée</h6>
                                <div>
                                    <strong x-text="testConfig?.integration_name"></strong>
                                    <br>
                                    <small class="text-muted" x-text="testConfig?.carrier_name"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Informations</h6>
                                <div>
                                    <small>
                                        <strong>Compte:</strong> <span x-text="testConfig?.username"></span><br>
                                        <strong>Environnement:</strong> <span x-text="testConfig?.environment"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État du test -->
                <div class="d-flex align-items-center justify-content-center py-4" x-show="testInProgress">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Test en cours...</span>
                        </div>
                        <h5 class="text-primary">Test en cours...</h5>
                        <p class="text-muted mb-0">Connexion au transporteur</p>
                        <div class="progress mt-3" style="width: 300px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 :style="`width: ${testProgress}%`"
                                 :aria-valuenow="testProgress" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted mt-2 d-block" x-text="testMessage"></small>
                    </div>
                </div>

                <!-- Résultat du test -->
                <div x-show="testCompleted && !testInProgress">
                    <!-- Succès -->
                    <div x-show="testResult?.success" class="alert alert-success">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">
                                    <i class="fas fa-check me-1"></i>
                                    Connexion Réussie !
                                </h6>
                                <p class="mb-0" x-text="testResult?.message"></p>
                            </div>
                        </div>
                        
                        <!-- Détails du test réussi -->
                        <div x-show="testResult?.details" class="mt-3">
                            <hr>
                            <h6 class="text-success">Détails de la Connexion</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small>
                                        <strong>URL API:</strong> <span x-text="testResult?.details?.api_url"></span><br>
                                        <strong>Temps de test:</strong> <span x-text="formatTestTime(testResult?.details?.test_time)"></span>
                                    </small>
                                </div>
                                <div class="col-md-6" x-show="testResult?.details?.account_info">
                                    <small>
                                        <strong>Statut compte:</strong> <span x-text="testResult?.details?.account_info?.account_status"></span><br>
                                        <strong>Solde:</strong> <span x-text="testResult?.details?.account_info?.balance"></span> TND
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Échec -->
                    <div x-show="!testResult?.success" class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-1">
                                    <i class="fas fa-times me-1"></i>
                                    Échec de Connexion
                                </h6>
                                <p class="mb-0" x-text="testResult?.error || 'Erreur inconnue'"></p>
                            </div>
                        </div>
                        
                        <!-- Détails de l'erreur -->
                        <div x-show="testResult?.details" class="mt-3">
                            <hr>
                            <h6 class="text-danger">Informations de Débogage</h6>
                            <div class="bg-light p-2 rounded">
                                <small class="font-monospace">
                                    <strong>Code d'erreur:</strong> <span x-text="testResult?.details?.error_code || 'N/A'"></span><br>
                                    <strong>URL tentée:</strong> <span x-text="testResult?.details?.url || 'N/A'"></span><br>
                                    <strong>Statut HTTP:</strong> <span x-text="testResult?.details?.status_code || 'N/A'"></span>
                                </small>
                            </div>
                        </div>

                        <!-- Suggestions de résolution -->
                        <div class="mt-3">
                            <h6 class="text-danger">Solutions Possibles</h6>
                            <ul class="mb-0">
                                <li>Vérifiez vos identifiants de connexion</li>
                                <li>Assurez-vous que votre compte transporteur est actif</li>
                                <li>Contactez le support du transporteur si le problème persiste</li>
                                <li>Vérifiez votre connexion internet</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Historique des tests récents -->
                <div x-show="testHistory && testHistory.length > 0" class="mt-4">
                    <h6 class="text-muted">
                        <i class="fas fa-history me-1"></i>
                        Tests Récents
                    </h6>
                    <div class="list-group list-group-flush">
                        <template x-for="test in testHistory.slice(0, 3)" :key="test.id">
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i :class="test.success ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'" class="me-2"></i>
                                        <div>
                                            <span x-text="test.success ? 'Succès' : 'Échec'"></span>
                                            <span x-show="!test.success" class="text-muted">- <span x-text="test.error"></span></span>
                                        </div>
                                    </div>
                                    <small class="text-muted" x-text="formatTestTime(test.timestamp)"></small>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <!-- Bouton de re-test -->
                        <button x-show="testCompleted && !testInProgress" 
                                class="btn btn-outline-primary"
                                @click="retestConnection()">
                            <i class="fas fa-redo me-1"></i>
                            Retester
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <!-- Bouton d'activation si test réussi -->
                        <button x-show="testResult?.success && !testConfig?.is_active" 
                                class="btn btn-success"
                                @click="activateConfiguration()">
                            <i class="fas fa-power-off me-1"></i>
                            Activer la Configuration
                        </button>
                        
                        <!-- Bouton de fermeture -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <span x-show="testInProgress">Annuler</span>
                            <span x-show="!testInProgress">Fermer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sélection des Commandes --}}
<div class="modal fade" id="orderSelectionModal" tabindex="-1" aria-labelledby="orderSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="orderSelectionModalLabel">
                    <i class="fas fa-boxes me-2"></i>Sélectionner les commandes à expédier
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Configuration sélectionnée -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <small class="text-muted">Transporteur</small>
                                        <div class="fw-bold" id="selected-carrier-name">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Configuration</small>
                                        <div class="fw-bold" id="selected-integration-name">-</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Date d'enlèvement</small>
                                        <div class="fw-bold" id="selected-pickup-date">-</div>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Statut</small>
                                        <span class="badge bg-success" id="selected-config-status">Actif</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-primary mb-1" id="selection-summary-count">0</h4>
                                <small class="text-muted">commande(s) sélectionnée(s)</small>
                                <div class="mt-2">
                                    <small class="text-muted">Total: </small>
                                    <strong id="selection-summary-total">0.000 TND</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres et recherche -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="orderSearchInput" placeholder="Rechercher par nom, téléphone ou ID...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="governorateFilter">
                            <option value="">Tous les gouvernorats</option>
                            <!-- Options seront ajoutées dynamiquement -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="stockFilter">
                            <option value="">Tous</option>
                            <option value="available">Stock disponible</option>
                            <option value="issues">Problèmes de stock</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                            <i class="fas fa-check-square me-1"></i>Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                            <i class="fas fa-square me-1"></i>Tout désélectionner
                        </button>
                    </div>
                </div>

                <!-- Zone de chargement -->
                <div id="ordersLoadingIndicator" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <div class="mt-2 text-muted">Chargement des commandes...</div>
                </div>

                <!-- Tableau des commandes -->
                <div class="card" id="ordersTableContainer">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                            </div>
                                        </th>
                                        <th style="width: 80px;">ID</th>
                                        <th>Client</th>
                                        <th style="width: 120px;">Téléphone</th>
                                        <th style="width: 100px;">Montant</th>
                                        <th style="width: 80px;">Articles</th>
                                        <th style="width: 120px;">Gouvernorat</th>
                                        <th style="width: 100px;">Stock</th>
                                        <th style="width: 100px;">Créée le</th>
                                    </tr>
                                </thead>
                                <tbody id="ordersTableBody">
                                    <!-- Les commandes seront ajoutées dynamiquement -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <small id="paginationInfo">Affichage de 0 à 0 sur 0 résultats</small>
                    </div>
                    <nav aria-label="Pagination des commandes">
                        <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                            <!-- Pagination sera générée dynamiquement -->
                        </ul>
                    </nav>
                </div>

                <!-- Messages d'erreur -->
                <div id="selection-errors" class="alert alert-danger mt-3 d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3"></i>
                        <div id="selection-error-content"></div>
                    </div>
                </div>

                <!-- Messages d'information -->
                <div id="selection-info" class="alert alert-info mt-3 d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3"></i>
                        <div id="selection-info-content"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" id="confirmSelectionBtn" disabled>
                    <i class="fas fa-plus me-2"></i>Créer l'enlèvement
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Détails du Pickup --}}
<div class="modal fade" id="pickupDetailsModal" tabindex="-1" aria-labelledby="pickupDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pickupDetailsModalLabel">
                    <i class="fas fa-truck me-2"></i>
                    Détails de l'Enlèvement
                    <span x-show="selectedPickup" x-text="`#${selectedPickup?.id}`" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" x-show="selectedPickup">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Informations Générales
                                </h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>ID:</strong> <span x-text="`#${selectedPickup?.id}`"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Statut:</strong>
                                        <span x-show="selectedPickup" 
                                              :class="getStatusBadgeClass(selectedPickup?.status)"
                                              x-text="getStatusLabel(selectedPickup?.status)"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Date d'enlèvement:</strong>
                                        <span x-text="formatDate(selectedPickup?.pickup_date)"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Créé le:</strong>
                                        <span x-text="formatDateTime(selectedPickup?.created_at)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-truck me-1"></i>
                                    Transporteur
                                </h6>
                                <div x-show="selectedPickup?.delivery_configuration">
                                    <div class="d-flex align-items-center mb-2">
                                        <i :class="getCarrierIcon(selectedPickup?.carrier_slug)" class="me-2"></i>
                                        <div>
                                            <div class="fw-bold" x-text="selectedPickup?.delivery_configuration?.integration_name"></div>
                                            <small class="text-muted" x-text="getCarrierName(selectedPickup?.carrier_slug)"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-primary mb-0" x-text="selectedPickup?.shipments?.length || 0"></div>
                            <small class="text-muted">Commandes</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-success mb-0" x-text="`${getTotalWeight()} kg`"></div>
                            <small class="text-muted">Poids Total</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-info mb-0" x-text="getTotalPieces()"></div>
                            <small class="text-muted">Nb Pièces</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-warning mb-0" x-text="`${getTotalCOD()} TND`"></div>
                            <small class="text-muted">COD Total</small>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-1"></i>
                            Commandes Incluses
                            <span x-show="selectedPickup" 
                                  class="badge bg-primary ms-2" 
                                  x-text="selectedPickup?.shipments?.length || 0"></span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Commande</th>
                                        <th>Client</th>
                                        <th>Téléphone</th>
                                        <th>Adresse</th>
                                        <th>Montant</th>
                                        <th>Poids</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="shipment in selectedPickup?.shipments || []" :key="shipment.id">
                                        <tr>
                                            <td>
                                                <strong x-text="`#${shipment.order?.id}`"></strong>
                                            </td>
                                            <td x-text="shipment.recipient_info?.name"></td>
                                            <td x-text="shipment.recipient_info?.phone"></td>
                                            <td>
                                                <div x-text="shipment.recipient_info?.city"></div>
                                                <small class="text-muted" x-text="shipment.recipient_info?.governorate"></small>
                                            </td>
                                            <td>
                                                <strong x-text="`${shipment.cod_amount} TND`"></strong>
                                            </td>
                                            <td x-text="`${shipment.weight} kg`"></td>
                                            <td>
                                                <span class="badge bg-primary" x-text="shipment.status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div x-show="selectedPickup" class="d-flex justify-content-between w-100">
                    <div>
                        <!-- Actions selon le statut -->
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-success"
                                @click="validatePickup(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-check me-1"></i>
                            Valider l'Enlèvement
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'validated'" 
                                class="btn btn-info"
                                @click="markAsPickedUp(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-truck me-1"></i>
                            Marquer comme Récupéré
                        </button>
                        
                        <button x-show="selectedPickup?.status === 'draft'" 
                                class="btn btn-outline-danger ms-2"
                                @click="deletePickup(selectedPickup.id); $refs.closeBtn.click()">
                            <i class="fas fa-trash me-1"></i>
                            Supprimer
                        </button>
                    </div>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" x-ref="closeBtn">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Détails de l'Expédition --}}
<div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-labelledby="shipmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipmentDetailsModalLabel">
                    <i class="fas fa-shipping-fast me-2"></i>
                    Détails de l'Expédition
                    <span x-show="selectedShipment" x-text="`#${selectedShipment?.id}`" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" x-show="selectedShipment">
                <!-- En-tête avec informations principales -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-barcode me-1"></i>
                                            Informations de Suivi
                                        </h6>
                                        <div>
                                            <strong>Numéro de suivi:</strong>
                                            <span x-text="selectedShipment?.pos_barcode || selectedShipment?.pos_reference || 'Non assigné'"></span>
                                            <br>
                                            <strong>ID Expédition:</strong> #<span x-text="selectedShipment?.id"></span>
                                            <br>
                                            <strong>Commande:</strong> #<span x-text="selectedShipment?.order?.id"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-truck me-1"></i>
                                            Transporteur
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <i :class="getCarrierIcon(selectedShipment?.carrier_slug)" class="me-2"></i>
                                            <div>
                                                <div class="fw-bold" x-text="getCarrierName(selectedShipment?.carrier_slug)"></div>
                                                <small class="text-muted" x-text="selectedShipment?.pickup?.delivery_configuration?.integration_name"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Statut Actuel
                                </h6>
                                <div x-show="selectedShipment">
                                    <span class="badge bg-primary" x-text="selectedShipment?.status"></span>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Dernière MAJ: <span x-text="getTimeSince(selectedShipment?.carrier_last_status_update || selectedShipment?.updated_at)"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations expédition -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-success mb-0" x-text="`${selectedShipment?.cod_amount || 0} TND`"></div>
                            <small class="text-muted">Montant COD</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-info mb-0" x-text="`${selectedShipment?.weight || 0} kg`"></div>
                            <small class="text-muted">Poids</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-warning mb-0" x-text="selectedShipment?.nb_pieces || 0"></div>
                            <small class="text-muted">Nb Pièces</small>
                        </div>
                    </div>
                </div>

                <!-- Onglets pour les détails -->
                <ul class="nav nav-tabs" id="shipmentDetailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="recipient-tab" data-bs-toggle="tab" data-bs-target="#recipient" type="button" role="tab">
                            <i class="fas fa-user me-1"></i>
                            Destinataire
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tracking-tab" data-bs-toggle="tab" data-bs-target="#tracking" type="button" role="tab">
                            <i class="fas fa-route me-1"></i>
                            Suivi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="order-details-tab" data-bs-toggle="tab" data-bs-target="#order-details" type="button" role="tab">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Commande
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="shipmentDetailsTabsContent">
                    <!-- Onglet Destinataire -->
                    <div class="tab-pane fade show active" id="recipient" role="tabpanel">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Informations Contact</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Nom:</strong></td>
                                            <td x-text="selectedShipment?.recipient_info?.name"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Téléphone:</strong></td>
                                            <td>
                                                <a :href="`tel:${selectedShipment?.recipient_info?.phone}`" 
                                                   x-text="selectedShipment?.recipient_info?.phone"></a>
                                            </td>
                                        </tr>
                                        <tr x-show="selectedShipment?.recipient_info?.phone_2">
                                            <td><strong>Téléphone 2:</strong></td>
                                            <td>
                                                <a :href="`tel:${selectedShipment?.recipient_info?.phone_2}`" 
                                                   x-text="selectedShipment?.recipient_info?.phone_2"></a>
                                            </td>
                                        </tr>
                                        <tr x-show="selectedShipment?.recipient_info?.email">
                                            <td><strong>Email:</strong></td>
                                            <td>
                                                <a :href="`mailto:${selectedShipment?.recipient_info?.email}`" 
                                                   x-text="selectedShipment?.recipient_info?.email"></a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Adresse de Livraison</h6>
                                    <div class="bg-light p-3 rounded">
                                        <div x-text="selectedShipment?.recipient_info?.address"></div>
                                        <div class="mt-2">
                                            <strong x-text="selectedShipment?.recipient_info?.city"></strong>
                                            <br>
                                            <span x-text="selectedShipment?.recipient_info?.governorate"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Suivi -->
                    <div class="tab-pane fade" id="tracking" role="tabpanel">
                        <div class="p-3">
                            <div class="text-center py-4">
                                <i class="fas fa-route fa-2x text-muted mb-2"></i>
                                <h6 class="text-muted">Historique de suivi</h6>
                                <p class="text-muted small">Les informations de suivi apparaîtront ici</p>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Commande -->
                    <div class="tab-pane fade" id="order-details" role="tabpanel">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Détails de la Commande</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>ID Commande:</strong></td>
                                            <td>
                                                <a :href="`/admin/orders/${selectedShipment?.order?.id}`" 
                                                   target="_blank"
                                                   x-text="`#${selectedShipment?.order?.id}`"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date commande:</strong></td>
                                            <td x-text="formatDate(selectedShipment?.order?.created_at)"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Statut commande:</strong></td>
                                            <td>
                                                <span class="badge bg-info" x-text="selectedShipment?.order?.status"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Montant total:</strong></td>
                                            <td>
                                                <strong x-text="`${selectedShipment?.order?.total_price} TND`"></strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Contenu</h6>
                                    <div class="bg-light p-3 rounded">
                                        <p x-text="selectedShipment?.content_description || 'Description non disponible'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div x-show="selectedShipment" class="d-flex justify-content-between w-100">
                    <div class="d-flex gap-2">
                        <!-- Actions selon le statut -->
                        <button x-show="selectedShipment?.pos_barcode" 
                                class="btn btn-primary"
                                @click="trackShipment(selectedShipment.id); updateModalData()">
                            <i class="fas fa-sync me-1"></i>
                            Actualiser Suivi
                        </button>
                        
                        <button x-show="selectedShipment?.status === 'in_transit'" 
                                class="btn btn-success"
                                @click="markAsDelivered(selectedShipment.id); $refs.closeBtn.click()">
                            <i class="fas fa-check me-1"></i>
                            Marquer Livré
                        </button>
                    </div>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" x-ref="closeBtn">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Validation du Pickup --}}
<div class="modal fade" id="pickupValidationModal" tabindex="-1" aria-labelledby="pickupValidationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="pickupValidationModalLabel">
                    <i class="fas fa-truck me-2"></i>Valider l'enlèvement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Informations du pickup -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">Pickup ID</small>
                                        <div class="fw-bold" id="validation-pickup-id">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Transporteur</small>
                                        <div class="fw-bold" id="validation-carrier-name">-</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">Date d'enlèvement</small>
                                        <div class="fw-bold" id="validation-pickup-date">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Configuration</small>
                                        <div class="fw-bold" id="validation-integration-name">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Résumé des expéditions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-1" id="validation-orders-count">0</h4>
                                            <small class="text-muted">Commandes</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-1" id="validation-total-pieces">0</h4>
                                        <small class="text-muted">Pièces</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h5 class="text-warning mb-1" id="validation-total-weight">0 kg</h5>
                                            <small class="text-muted">Poids total</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-info mb-1" id="validation-total-cod">0 TND</h5>
                                        <small class="text-muted">Montant COD</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vérifications avant validation -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Vérifications automatiques</h6>
                    </div>
                    <div class="card-body">
                        <div id="validation-checks">
                            <!-- Les vérifications seront ajoutées dynamiquement -->
                        </div>
                    </div>
                </div>

                <!-- Alerte d'avertissement -->
                <div class="alert alert-warning" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                        <div>
                            <strong>Attention !</strong><br>
                            Une fois validé, ce pickup sera envoyé au transporteur et ne pourra plus être modifié. 
                            Assurez-vous que toutes les informations sont correctes.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-success" id="confirmValidationBtn">
                    <i class="fas fa-paper-plane me-2"></i>Valider et envoyer
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Styles pour les modales */
.modal-dialog.modal-xl {
    max-width: 1200px;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
}

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
    margin-bottom: 20px;
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
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

/* Navigation tabs personnalisée */
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #007bff;
    border-color: transparent;
}

.tab-content {
    border: 1px solid #dee2e6;
    border-top: none;
    min-height: 300px;
}

/* Progress bar pour test de connexion */
.progress {
    height: 8px;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Table responsive dans les modales */
.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}

.table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
}

/* Badges de statut */
.badge {
    font-size: 0.75em;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal.fade .modal-dialog {
    animation: fadeInUp 0.3s ease-out;
}

/* Scroll personnalisé pour les modales */
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #999;
}
</style>
@endpush

@push('scripts')
<script>
// Scripts communs pour toutes les modales de livraison
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des modales Bootstrap
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });

    // Fonctions utilitaires globales pour les modales
    window.deliveryModals = {
        // Formatter les dates
        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },

        // Formatter les dates et heures
        formatDateTime(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        // Formatter le temps écoulé
        formatTimeSince(dateString) {
            if (!dateString) return 'Jamais';
            
            const now = new Date();
            const date = new Date(dateString);
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'À l\'instant';
            if (diffInMinutes < 60) return `${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`;
            return `${Math.floor(diffInMinutes / 1440)}j`;
        },

        // Obtenir l'icône du transporteur
        getCarrierIcon(carrierSlug) {
            const icons = {
                'jax_delivery': 'fas fa-truck text-primary',
                'mes_colis': 'fas fa-shipping-fast text-success'
            };
            return icons[carrierSlug] || 'fas fa-truck text-secondary';
        },

        // Obtenir le nom du transporteur
        getCarrierName(carrierSlug) {
            const names = {
                'jax_delivery': 'JAX Delivery',
                'mes_colis': 'Mes Colis Express'
            };
            return names[carrierSlug] || 'Transporteur inconnu';
        },

        // Afficher une notification
        showNotification(message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';
            
            const iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            }[type] || 'fa-info-circle';
            
            const alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 100px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('body').append(alert);
            
            setTimeout(() => {
                alert.fadeOut(() => alert.remove());
            }, type === 'error' ? 8000 : 5000);
        }
    };

    // Gestion des erreurs AJAX globales pour les modales
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        if (jqXHR.status === 422) {
            // Erreurs de validation
            const response = jqXHR.responseJSON;
            if (response && response.errors) {
                const errors = Object.values(response.errors).flat();
                window.deliveryModals.showNotification(errors.join('<br>'), 'error');
            }
        } else if (jqXHR.status >= 500) {
            window.deliveryModals.showNotification('Erreur serveur. Veuillez réessayer.', 'error');
        } else if (jqXHR.status === 404) {
            window.deliveryModals.showNotification('Ressource non trouvée.', 'error');
        }
    });

    // Configuration CSRF pour toutes les requêtes AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
</script>
@endpush